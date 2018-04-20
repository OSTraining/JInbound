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

defined('JPATH_PLATFORM') or die;

class JInboundControllerLead extends JInboundBaseController
{
    protected $context  = 'com_jinbound.page';

    public function save()
    {
        $app        = JFactory::getApplication();
        $db         = JFactory::getDbo();
        $dispatcher = JDispatcher::getInstance();
        $page_id    = $app->input->post->get('page_id', 0, 'int');
        $token      = $app->input->get('token', '', 'cmd');
        $raw_param  = 'jform';
        if (!empty($token)) {
            $raw_param = preg_replace('/^(.*?)\.(.*?)\.(.*?)$/', '${1}_${3}', $token);
        }
        $raw_data        = $app->input->post->get($raw_param, array(), 'array');
        $contact_data    = array();
        $conversion_data = array('page_id' => $page_id);
        // import content plugins
        JPluginHelper::importPlugin('content');
        // ensure the plugin we need is available
        // TODO move these methods to a helper?
        if (!(class_exists('plgSystemJInbound') && method_exists('plgSystemJInbound', 'getCookieUser'))) {
            throw new RuntimeException('Class not found.', 404);
        }
        // go ahead and quickly validate that the core required data came in
        if (!(array_key_exists('lead', $raw_data) && is_array($raw_data['lead']))) {
            throw new RuntimeException('Raw lead data not found', 404);
        }
        foreach (array('email', 'first_name', 'last_name') as $var) {
            if (!array_key_exists($var, $raw_data['lead'])) {
                throw new RuntimeException("Variable $var not set", 500);
            }
            $$var = $raw_data['lead'][$var];
            if (empty($$var)) {
                throw new RuntimeException("Variable $var empty", 500);
            }
        }

        // check for a token - this means a non-page form is being processed
        if (!empty($token)) {
            $session_data = JFactory::getSession()->get($token, false);
            // no data for this token - try to load it
            if (!is_object($session_data)) {
                $token_parts = explode('.', $token);
                if (3 === count($token_parts)) {
                    list($module_name, $type, $module_id) = $token;
                    $helper = JPATH_ROOT . '/modules/' . $module_name . '/helper.php';
                    $class  = str_replace(' ', '', ucwords(str_replace('_', ' ', $module_name))) . 'Helper';
                    $method = 'get' . ucwords($type) . 'Data';
                    if (!class_exists($class)) {
                        if (file_exists($helper)) {
                            require_once $helper;
                        }
                    }
                    if (class_exists($class) && method_exists($class, $method)) {
                        try {
                            $module = JinboundHelperModule::getModuleObject($module_id);
                            $params = new JRegistry();
                            $params->loadString($module->params);
                            $session_data = call_user_func_array(array($class, $method), array($module, $params));
                        } catch (Exception $ex) {
                            $app->enqueueMessage($e->getMessage(), 'error');
                        }
                    }
                }
            }
            if (!is_object($session_data)) {
                throw new RuntimeException("No data found for token", 404);
            }
            // assign variables
            $campaign_id         = $session_data->campaign_id;
            $form_id             = $session_data->form_id;
            $page_name           = $session_data->page_name;
            $notification_email  = $session_data->notification_email;
            $after_submit_sendto = $session_data->after_submit_sendto;
            $menu_item           = $session_data->menu_item;
            $send_to_url         = $session_data->send_to_url;
            $sendto_message      = $session_data->sendto_message;
            // fetch the fields for this form
            $formfields = JInboundHelperForm::getFields($form_id);
        } else {
            // get a page model so we can pull the formbuilder variable from it
            $model               = $this->getModel('Page', 'JInboundModel');
            $page                = $model->getItem($page_id);
            $form                = $model->getForm();
            $form_id             = $page->formid;
            $campaign_id         = $page->campaign;
            $page_name           = $page->formname;
            $notification_email  = $page->notification_email;
            $after_submit_sendto = $page->after_submit_sendto;
            $menu_item           = $page->menu_item;
            $send_to_url         = $page->send_to_url;
            $sendto_message      = $page->sendto_message;
            if (false === $model->validate($form, $raw_data)) {
                $errors = $model->getErrors();
                for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                    if ($errors[$i] instanceof Exception) {
                        $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                    } else {
                        $app->enqueueMessage($errors[$i], 'warning');
                    }
                }
                $app->setUserState('com_jinbound.page.data', $raw_data);
                $app->redirect(JRoute::_('index.php?option=com_jinbound&view=page&id=' . $page_id . '&Itemid=' . (int)$app->input->get('Itemid',
                        0), false));
                return false;
            }
            $redirect = JRoute::_('index.php?option=com_jinbound&id=' . $page->id);
            if (!$page || empty($page->id)) {
                throw new Exception(JText::_('COM_JINBOUND_NO_PAGE_FOUND'), 404);
                jexit();
            }
            // fetch the fields for this form
            $formfields = JInboundHelperForm::getFields($page->formid);
        }
        // build data from formbuilder
        foreach ($formfields as $formfield) {
            if (array_key_exists($formfield->name, $raw_data['lead'])) {
                $conversion_data[$formfield->name] = $raw_data['lead'][$formfield->name];
            }
        }
        // before saving the data for this contact/conversion
        // first check who this user is
        // it may be a guest that has never registered
        // it may be a user that has an account but is not logged in
        // it may be a user that is currently logged in
        // it may be a v1.0 lead that has no user associated
        $user = JFactory::getUser();
        // this user is not logged in - try to figure out who they are
        if (!($user_id = $user->get('id'))) {
            $user_id = plgSystemJInbound::getCookieUser();
            if (is_array($user_id)) {
                $user_id = array_shift($user_id);
            }
        }
        // this user either has no account or has not been tracked yet
        // determine from the user's email if they exist
        if (empty($user_id)) {
            // found a core contact for this user
            $user_id = (int)$db->setQuery($db->getQuery(true)
                ->select('id')->from('#__users')->where('email = ' . $db->quote($email))
            )->loadResult();
        }
        // at this point either the user is identified by an id or not
        // if the user exists, simply look up the contact id to associate
        // if the user does not exist, then determine if there's a match
        // if there is a match, and the user could not be determined, hook the contact

        // there may not be a contact for this person
        $contact_id = 0;
        // there is a user - find the contact id from this
        if ($user_id) {
            // found a core contact for this user
            $contact_id = (int)$db->setQuery($db->getQuery(true)
                ->select('id')->from('#__contact_details')->where('user_id = ' . $db->quote($user_id))
            )->loadResult();
        }

        // if there is no contact id because either the user couldn't be determined
        // or there's not a contact associated with the found user, do a lookup by email
        if (empty($contact_id)) {
            // found a core contact for this user
            $contact_id = (int)$db->setQuery($db->getQuery(true)
                ->select('id')->from('#__contact_details')->where('email_to = ' . $db->quote($email))
            )->loadResult();
        }

        // it's possible that the contact gives a user id but we should only need that if it's not set yet
        if (empty($user_id) && !empty($contact_id)) {
            // found a core contact for this user
            $user_id = (int)$db->setQuery($db->getQuery(true)
                ->select('user_id')->from('#__contact_details')->where('id = ' . $db->quote($contact_id))
            )->loadResult();
        }

        // find the jinbound contact record for this user
        // first check user id, then contact id, and finally email
        // no contact means that a new one needs to be created
        // otherwise the existing CONTACT record must be updated
        // however the CONVERSION data will be saved to a new record
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__jinbound_contacts')
            ->where('email = ' . $db->quote($email));
        // add a lookup by user id (if found)
        if (!empty($user_id)) {
            $query->where('user_id = ' . $db->quote($user_id), ' OR ');
        }
        // add a lookup by core contact id (if found)
        if (!empty($contact_id)) {
            $query->where('core_contact_id = ' . $db->quote($contact_id), ' OR ');
        }

        // use this id to determine if the jinbound contact needs to be loaded or not
        $jinbound_contact_id = (int)$db->setQuery($query)->loadResult();

        // fetch a JTable instance for the jinbound contact and optionally load the existing record
        $contact = JTable::getInstance('Contact', 'JInboundTable');
        if (empty($contact)) {
            throw new RuntimeException('Class not found');
        }
        if (!empty($jinbound_contact_id)) {
            $contact->load($contact_data['id'] = $jinbound_contact_id);
        }
        $isNew = empty($contact->id);

        // fill in the data to be bound to the jinbound contact
        $contact_data['email']      = $email;
        $contact_data['first_name'] = $first_name;
        $contact_data['last_name']  = $last_name;
        $contact_data['cookie']     = plgSystemJInbound::getCookieValue();
        // only fill in the FK columns if the new values are not empty and the old ones are
        if ($user_id && empty($contact->user_id)) {
            $contact_data['user_id'] = $user_id;
        }
        if ($contact_id && empty($contact->core_contact_id)) {
            $contact_data['core_contact_id'] = $contact_id;
        }
        // some of these may not be set
        foreach (array(
                     'address'   => array('address'),
                     'suburb'    => array('suburb', 'city'),
                     'state'     => array('state'),
                     'country'   => array('country'),
                     'postcode'  => array('postcode', 'zip', 'zipcode', 'zip_code'),
                     'telephone' => array('telephone', 'phone', 'phone_number', 'phonenumber', 'number'),
                     'company'   => array('company', 'companyname', 'company_name'),
                     'website'   => array('webpage', 'website', 'web', 'url')
                 ) as $var => $keys) {
            $$var = '';
            if ($contact->id) {
                $$var = $contact->$var;
            }
            // update from the raw data
            foreach ($keys as $key) {
                if (array_key_exists($key, $raw_data['lead']) && !empty($raw_data['lead'][$key])) {
                    $$var = $raw_data['lead'][$key];
                    break;
                }
            }
            $contact_data[$var] = $$var;
        }
        // handle new records
        if (empty($contact->id)) {
            $contact_data['published'] = 1;
        }

        // bind the data to the contact table
        if (!$contact->bind($contact_data)) {
            throw new RuntimeException('Error binding contact data', 500);
        }
        // check the data
        if (!$contact->check()) {
            throw new RuntimeException('Error checking contact data', 500);
        }
        // fire before save event
        $result = $dispatcher->trigger('onContentBeforeSave', array('com_jinbound.contact', &$contact, $isNew));
        if (in_array(false, $result, true)) {
            throw new RuntimeException('Could not save: ' . $contact->getError(), 500);
        }
        // store the data
        if (!$contact->store()) {
            throw new RuntimeException('Error saving contact data', 500);
        }
        // fire after save event
        $dispatcher->trigger('onContentAfterSave', array('com_jinbound.contact', &$contact, $isNew));

        // ensure there's a jinbound contact id now
        if (empty($contact->id)) {
            throw new RuntimeException('Error finding contact id', 500);
        }

        // get the default priority
        $priority_id = JInboundHelperPriority::getDefaultPriority();

        // get the default status
        $status_id = JInboundHelperStatus::getDefaultStatus();

        // remove this contact from this page's campaign, if necessary
        // TODO integrate fix for #214

        $db->setQuery($db->getQuery(true)
            ->delete('#__jinbound_contacts_campaigns')
            ->where('contact_id = ' . $db->quote($contact->id))
            ->where('campaign_id = ' . $db->quote($campaign_id))
        )->query();

        // attach the contact to this campaign
        $db->setQuery($db->getQuery(true)
            ->insert('#__jinbound_contacts_campaigns')
            ->columns(array('contact_id', 'campaign_id'))
            ->values($db->quote($contact->id) . ', ' . $db->quote($campaign_id))
        )->query();

        // save the status
        JInboundHelperStatus::setContactStatusForCampaign($status_id, $contact->id, $campaign_id, $user_id);

        // save the priority
        JInboundHelperPriority::setContactPriorityForCampaign($priority_id, $contact->id, $campaign_id, $user_id);


        // no matter what, always save a NEW conversion record
        $conversion = JTable::getInstance('Conversion', 'JInboundTable');
        if (empty($conversion)) {
            throw new RuntimeException('Class not found');
        }
        // set conversion data
        $conversion_data['contact_id'] = $contact->id;
        $conversion_data['formdata']   = json_encode($raw_data);
        $conversion_data['published']  = 1;

        // bind the data to the contact table
        if (!$conversion->bind($conversion_data)) {
            throw new RuntimeException('Error binding conversion data', 500);
        }
        // check the data
        if (!$conversion->check()) {
            throw new RuntimeException('Error checking conversion data', 500);
        }
        // fire before save event
        $result = $dispatcher->trigger('onContentBeforeSave', array('com_jinbound.conversion', &$conversion, true));
        if (in_array(false, $result, true)) {
            $app->enqueueMessage('Could not save: ' . $conversion->getError(), 'warning');
            $app->setUserState('com_jinbound.page.data', $raw_data);
            $app->redirect(JRoute::_('index.php?option=com_jinbound&view=page&id=' . $page_id . '&Itemid=' . (int)$app->input->get('Itemid',
                    0), false));
            return false;
        }
        // store the data
        if (!$conversion->store()) {
            throw new RuntimeException('Error saving conversion data', 500);
        }
        // fire after save event
        $dispatcher->trigger('onContentAfterSave', array('com_jinbound.conversion', &$conversion, true));

        // subscribe contact
        $sub = $db->setQuery($db->getQuery(true)
            ->select('id')
            ->from('#__jinbound_subscriptions')
            ->where('contact_id = ' . (int)$contact->id)
        )->loadResult();

        if (empty($sub)) {
            $db->setQuery($db->getQuery(true)
                ->insert('#__jinbound_subscriptions')
                ->columns(array('contact_id', 'enabled'))
                ->values(((int)$contact->id) . ', 1')
            )->query();
        }

        // alert if necessary
        if (!empty($notification_email)) {
            $campaign_name = $db->setQuery($db->getQuery(true)
                ->select('name')->from('#__jinbound_campaigns')
                ->where('id = ' . (int)$campaign_id)
            )->loadResult();
            $html          = array();
            $html[]        = '<table>';
            foreach ($raw_data['lead'] as $key => $val) {
                $html[] = '	<tr>';
                $html[] = '		<td>';
                $html[] = '			' . htmlspecialchars(JText::_($raw_data[$key]['title']), ENT_QUOTES, 'UTF-8');
                $html[] = '		</td>';
                $html[] = '		<td>';
                $html[] = '			' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
                $html[] = '		</td>';
                $html[] = '	</tr>';
            }
            $html[]             = '</table>';
            $notification_email = explode(',', $notification_email);
            $subject            = JText::_('COM_JINBOUND_NOTIFICATION_EMAIL_SUBJECT');
            $dispatcher->trigger('onJinboundBeforeNotificationEmail',
                array(&$notification_email, &$subject, &$html, $contact, $conversion));
            $body   = JText::sprintf('COM_JINBOUND_NOTIFICATION_EMAIL_BODY', $campaign_name, $page_name,
                implode("\n", $html));
            $mailer = JFactory::getMailer();
            $mailer->IsHTML(true);
            $mailer->setSubject($subject);
            $mailer->setBody($body);
            foreach ($notification_email as $email) {
                $mailer->addRecipient($email);
            }
            $mailer->Send();
        }

        // build the redirect
        $message = '';
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
            default:
                $redirect = JURI::root();
                break;
        }

        $app->setUserState('com_jinbound.page.data', array());

        if (empty($message)) {
            $app->redirect($redirect);
        } else {
            $app->redirect($redirect, $message, 'message');
        }

        $app->close();
    }
}
