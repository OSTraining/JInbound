<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 */

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

class JInboundControllerLead extends JInboundBaseController
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.page';

    /**
     * @return void
     * @throws Exception
     */
    public function save()
    {
        /**
         * @var JInboundModelPage $pageModel
         * @var JApplicationSite  $app
         */
        $app        = JFactory::getApplication();
        $db         = JFactory::getDbo();
        $dispatcher = JEventDispatcher::getInstance();
        $pageModel  = $this->getModel('Page', 'JInboundModel');
        $pageModel->getState();

        $redirect     = JUri::current();
        $pageId       = $app->input->post->getInt('page_id');
        $token        = $app->input->getCmd('token');
        $userStateKey = $token ?: 'com_jinbound.page.data';

        $formVar = 'jform';
        if (!empty($token)) {
            $formVar = preg_replace('/^(.*?)\.(.*?)\.(.*?)$/', '${1}_${3}', $token);
        }
        $rawData        = $app->input->post->get($formVar, array(), 'array');
        $contactData    = array();
        $conversionData = $pageId ? array('page_id' => $pageId) : array();

        JPluginHelper::importPlugin('content');

        if (!(class_exists('plgSystemJInbound') && method_exists('plgSystemJInbound', 'getCookieUser'))) {
            throw new RuntimeException('Class not found.', 404);
        }

        $lead = empty($rawData['lead']) ? null : new JInput($rawData['lead']);
        if (!$lead) {
            throw new RuntimeException('Raw lead data not found', 404);
        }

        $email      = $lead->getString('email');
        $first_name = $lead->getString('first_name');
        $last_name  = $lead->getString('last_name');
        if (!($email && $first_name && $last_name)) {
            throw new RuntimeException('Minimum required lead information is missing', 500);
        }

        if ($token) {
            // non-page form info comes from a token in the session

            //$sessionData = JFactory::getSession()->get($token, false);
            if (!is_object($sessionData)) {
                $tokenParts = explode('.', $token);
                if (count($tokenParts) == 3) {
                    list($moduleName, $type, $moduleId) = $tokenParts;
                    $helper = JPATH_ROOT . '/modules/' . $moduleName . '/helper.php';
                    $class  = str_replace(' ', '', ucwords(str_replace('_', ' ', $moduleName))) . 'Helper';
                    $method = 'get' . ucwords($type) . 'Data';
                    if (!class_exists($class)) {
                        if (file_exists($helper)) {
                            require_once $helper;
                        }
                    }

                    if (class_exists($class) && method_exists($class, $method)) {
                        try {
                            $module = JinboundHelperModule::getModuleObject($moduleId);
                            $params = $module->params instanceof Registry
                                ? $module->params
                                : new Registry($module->params);

                            $sessionData = call_user_func_array(array($class, $method), array($module, $params));

                        } catch (Exception $e) {
                            $app->enqueueMessage($e->getMessage(), 'error');
                        }
                    }
                }
            }

            if (!is_object($sessionData)) {
                throw new RuntimeException("No data found for token", 404);
            }


            $after_submit_sendto = $sessionData->after_submit_sendto;
            $campaignId          = $sessionData->campaign_id;
            $formId              = $sessionData->form_id;
            $menu_item           = $sessionData->menu_item;
            $notification_email  = $sessionData->notification_email;
            $page_name           = $sessionData->page_name;
            $send_to_url         = $sessionData->send_to_url;
            $sendto_message      = $sessionData->sendto_message;

            $pageModel->setState('form.id', $formId);
            $form = $pageModel->getForm();

        } else {
            // Page oriented form comes from the page model
            $page = $pageModel->getItem($pageId);
            $form = $pageModel->getForm();

            $redirectVars = array(
                'option' => 'com_jinbound',
                'view'   => 'page',
                'id'     => $pageId,
                'Itemid' => (int)$app->input->get('Itemid', 0)
            );
            $redirect     = JRoute::_('index.php?' . http_build_query($redirectVars));

            if (empty($page->id)) {
                throw new Exception(JText::_('COM_JINBOUND_NO_PAGE_FOUND'), 404);
            }

            $after_submit_sendto = $page->after_submit_sendto;
            $campaignId          = $page->campaign;
            $formId              = $page->formid;
            $menu_item           = $page->menu_item;
            $notification_email  = $page->notification_email;
            $page_name           = $page->formname;
            $send_to_url         = $page->send_to_url;
            $sendto_message      = $page->sendto_message;
        }

        if ($pageModel->validate($form, $rawData) === false) {
            if ($errors = $pageModel->getErrors()) {
                $app->enqueueMessage(join('<br/>', $errors));
            }
            $app->setUserState($userStateKey, $rawData);
            $app->redirect($redirect, false);
        }

        $formFields = JInboundHelperForm::getFields($formId);

        /*
         * before saving the data for this contact/conversion
         * first check who this user is
         * it may be a guest that has never registered
         * it may be a user that has an account but is not logged in
         * it may be a user that is currently logged in
         * it may be a v1.0 lead that has no user associated
         */
        $user = JFactory::getUser();
        // this user is not logged in - try to figure out who they are
        if (!($userId = $user->get('id'))) {
            $userId = plgSystemJInbound::getCookieUser();
            if (is_array($userId)) {
                $userId = array_shift($userId);
            }
        }
        // this user either has no account or has not been tracked yet
        // determine from the user's email if they exist
        if (empty($userId)) {
            // found a core contact for this user
            $userId = (int)$db->setQuery(
                $db->getQuery(true)
                    ->select('id')
                    ->from('#__users')
                    ->where('email = ' . $db->quote($email))
            )
                ->loadResult();
        }

        /*
         * at this point either the user is identified by an id or not
         * if the user exists, simply look up the contact id to associate
         * if the user does not exist, then determine if there's a match
         * if there is a match, and the user could not be determined, hook the contact
         */

        // there may not be a contact for this person
        $contactId = 0;
        if ($userId) {
            // Find a core contact for this user
            $contactId = (int)$db->setQuery(
                $db->getQuery(true)
                    ->select('id')
                    ->from('#__contact_details')
                    ->where('user_id = ' . $db->quote($userId))
            )
                ->loadResult();
        }

        /*
         * if there is no contact id because either the user couldn't be determined
         * or there's not a contact associated with the found user, do a lookup by email
         */
        if (empty($contactId)) {
            // Find a core contact for this user
            $contactId = (int)$db->setQuery(
                $db->getQuery(true)
                    ->select('id')
                    ->from('#__contact_details')
                    ->where('email_to = ' . $db->quote($email))
            )
                ->loadResult();
        }

        // it's possible that the contact gives a user id but we should only need that if it's not set yet
        if (!$userId && $contactId) {
            // Find a core contact for this user
            $userId = (int)$db->setQuery(
                $db->getQuery(true)
                    ->select('user_id')
                    ->from('#__contact_details')
                    ->where('id = ' . $db->quote($contactId))
            )
                ->loadResult();
        }

        /*
         * find the jinbound contact record for this user
         * first check user id, then contact id, and finally email
         * no contact means that a new one needs to be created
         * otherwise the existing CONTACT record must be updated
         * however the CONVERSION data will be saved to a new record
         */
        $whereAtoms = array(
            'email = ' . $db->quote($email),
        );
        if ($userId) {
            $whereAtoms[] = 'user_id = ' . $db->quote($userId);
        }
        if ($contactId) {
            $whereAtoms[] = 'core_contact_id = ' . $db->quote($contactId);
        }

        $contact = JTable::getInstance('Contact', 'JInboundTable');

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__jinbound_contacts')
            ->where($whereAtoms, 'OR');

        if ($jinboundContactId = (int)$db->setQuery($query)->loadResult()) {
            $contact->load($contactData['id'] = $jinboundContactId);
        }

        $isNew = empty($contact->id);

        // Fill in the base required fields
        $contactData['email']      = $email;
        $contactData['first_name'] = $first_name;
        $contactData['last_name']  = $last_name;
        $contactData['cookie']     = plgSystemJInbound::getCookieValue();
        if ($userId && empty($contact->user_id)) {
            $contactData['user_id'] = $userId;
        }
        if ($contactId && empty($contact->core_contact_id)) {
            $contactData['core_contact_id'] = $contactId;
        }
        if ($isNew) {
            $contactData['published'] = 1;
        }

        // some of these may not be set
        foreach (
            array(
                'address'   => array('address'),
                'suburb'    => array('suburb', 'city'),
                'state'     => array('state'),
                'country'   => array('country'),
                'postcode'  => array('postcode', 'zip', 'zipcode', 'zip_code'),
                'telephone' => array('telephone', 'phone', 'phone_number', 'phonenumber', 'number'),
                'company'   => array('company', 'companyname', 'company_name'),
                'website'   => array('webpage', 'website', 'web', 'url')
            )
            as $var => $keys
        ) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $rawData['lead']) && !empty($rawData['lead'][$key])) {
                    $contactData[$var] = $lead->getString($key);
                    break;
                }
            }
        }

        if (!$contact->bind($contactData)) {
            throw new RuntimeException('Error binding contact data', 500);
        }

        if (!$contact->check()) {
            throw new RuntimeException('Error checking contact data', 500);
        }

        $result = $dispatcher->trigger('onContentBeforeSave', array('com_jinbound.contact', &$contact, $isNew));
        if (in_array(false, $result, true)) {
            throw new RuntimeException('Could not save: ' . $contact->getError(), 500);
        }

        if (!$contact->store()) {
            throw new RuntimeException('Error saving contact data', 500);
        }

        $dispatcher->trigger('onContentAfterSave', array('com_jinbound.contact', &$contact, $isNew));

        if (empty($contact->id)) {
            throw new RuntimeException('Error finding contact id', 500);
        }

        $priorityId = JInboundHelperPriority::getDefaultPriority();
        $statusId   = JInboundHelperStatus::getDefaultStatus();

        $db->setQuery(
            $db->getQuery(true)
                ->delete('#__jinbound_contacts_campaigns')
                ->where(
                    array(
                        'contact_id = ' . $db->quote($contact->id),
                        'campaign_id = ' . $db->quote($campaignId)
                    )
                )
        )
            ->execute();

        $insertObject = (object)array(
            'contact_id'  => $contact->id,
            'campaign_id' => $campaignId
        );
        $db->insertObject('#__jinbound_contacts_campaigns', $insertObject);


        JInboundHelperStatus::setContactStatusForCampaign($statusId, $contact->id, $campaignId, $userId);
        JInboundHelperPriority::setContactPriorityForCampaign($priorityId, $contact->id, $campaignId, $userId);

        $conversion = JTable::getInstance('Conversion', 'JInboundTable');
        if (empty($conversion)) {
            throw new RuntimeException('Class not found');
        }

        $conversionData['contact_id'] = $contact->id;
        $conversionData['formdata']   = json_encode($rawData);
        $conversionData['published']  = 1;

        if (!$conversion->bind($conversionData)) {
            throw new RuntimeException('Error binding conversion data', 500);
        }

        if (!$conversion->check()) {
            throw new RuntimeException('Error checking conversion data', 500);
        }

        $result = $dispatcher->trigger('onContentBeforeSave', array('com_jinbound.conversion', &$conversion, true));
        if (in_array(false, $result, true)) {
            $this->throwError('Conversion Failed', $dispatcher->getErrors(), $userStateKey, $rawData, $pageId);
            return;
        }

        // store the data
        if (!$conversion->store()) {
            $this->throwError('Conversion data not saved', $conversion->getErrors(), $userStateKey, $rawData, $pageId);
            return;
        }

        $dispatcher->trigger('onContentAfterSave', array('com_jinbound.conversion', &$conversion, true));

        $sub = $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_subscriptions')
                ->where('contact_id = ' . (int)$contact->id)
        )
            ->loadResult();

        if (empty($sub)) {
            $insertObject = (object)array(
                'contact_id' => $contact->id,
                'enabled'    => 1
            );
            $db->insertObject('#__jinbound_subscriptions', $insertObject);
        }

        if (!empty($notification_email)) {
            $campaign_name = $db->setQuery(
                $db->getQuery(true)
                    ->select('name')->from('#__jinbound_campaigns')
                    ->where('id = ' . (int)$campaignId)
            )
                ->loadResult();

            $html = array('<table>');
            foreach ($rawData['lead'] as $key => $val) {
                $title = empty($formFields[$key]) ? $key : $formFields[$key]->title;
                $html  = array_merge(
                    $html,
                    array(
                        '	<tr>',
                        '		<td>',
                        '			' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                        '		</td>',
                        '		<td>',
                        '			' . nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8')),
                        '		</td>',
                        '	</tr>'
                    )
                );
            }
            $html[] = '</table>';

            $notification_email = explode(',', $notification_email);

            $subject = JText::_('COM_JINBOUND_NOTIFICATION_EMAIL_SUBJECT');
            $dispatcher->trigger(
                'onJinboundBeforeNotificationEmail',
                array(&$notification_email, &$subject, &$html, $contact, $conversion)
            );

            $body = JText::sprintf(
                'COM_JINBOUND_NOTIFICATION_EMAIL_BODY',
                $campaign_name,
                $page_name,
                implode("\n", $html)
            );

            $mailer = JFactory::getMailer();
            $mailer->IsHTML(true);
            $mailer->setSubject($subject);
            $mailer->setBody($body);
            foreach ($notification_email as $email) {
                $mailer->addRecipient($email);
            }
            $mailer->Send();
        }

        $message  = null;
        $redirect = null;
        switch ($after_submit_sendto) {
            case 'menuitem':
                if (!empty($menu_item)) {
                    $redirect = JRoute::_('index.php?Itemid=' . $menu_item);
                }
                break;

            case 'url':
                if (!empty($send_to_url)) {
                    $redirect = JRoute::_($send_to_url);
                }
                break;

            case 'message':
                if (!empty($sendto_message)) {
                    $message = $sendto_message;
                }
                break;
        }
        if (!$redirect) {
            $redirect = JUri::root();
        }

        $app->setUserState('com_jinbound.page.data', array());

        $app->redirect($redirect, $message, 'message');
    }

    /**
     * @param string $text
     * @param array  $errors
     * @param string $userStateKey
     * @param array  $pageData
     * @param int    $pageId
     *
     * @throws Exception
     */
    protected function throwError($text, array $errors, $userStateKey = null, $pageData = null, $pageId = null)
    {
        $app = JFactory::getApplication();

        $app->enqueueMessage(
            sprintf('<h4>%s</h4>%s', $text, join('<br/>', $errors)),
            'warning'
        );

        if ($pageData && $userStateKey) {
            $app->setUserState($userStateKey, $pageData);
        }

        if ($pageId) {
            $query = array(
                'option' => 'com_jinbound',
                'view'   => 'page',
                'id'     => $pageId
            );
            if ($itemid = $app->input->getInt('Itemid')) {
                $query['Itemid'] = $itemid;
            }
            $app->redirect(JRoute::_('index.php?' . http_build_query($query), false));
        }

        $app->redirect(JRoute::_('index.php', false));
    }
}
