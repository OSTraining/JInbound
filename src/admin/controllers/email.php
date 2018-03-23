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

class JInboundControllerEmail extends JInboundFormController
{
    /**
     * @throws Exception
     */
    public function test()
    {
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $response = new Exception('done', 0);

        try {
            $app        = JFactory::getApplication();
            $dispatcher = JEventDispatcher::getInstance();

            /** @var JInboundModelEmails $emailModel */
            $emailModel = JModelLegacy::getInstance('Emails', 'JInboundModel');

            $to        = $app->input->getString('to');
            $fromname  = $app->input->getString('fromname');
            $fromemail = $app->input->getString('fromemail');
            $subject   = $app->input->getString('subject');
            $htmlbody  = $app->input->get('htmlbody', '', 'raw');
            $plainbody = $app->input->getString('plainbody');
            $type      = $app->input->getString('type');

            foreach (array('to', 'fromname', 'fromemail', 'subject', 'type') as $var) {
                if (empty($$var)) {
                    throw new Exception("Variable $var cannot be empty");
                }
            }

            $result = new stdClass();
            switch ($type) {
                case 'report':
                    $tags = array(
                        'reports.goals.count',
                        'reports.goals.percent',
                        'reports.leads.count',
                        'reports.leads.percent',
                        'reports.leads.list',
                        'reports.pages.hits',
                        'reports.pages.list',
                        'reports.pages.top.name',
                        'reports.pages.top.url',
                        'reports.pages.lowest.name',
                        'reports.pages.lowest.url',
                        'reports.date.start',
                        'reports.date.end'
                    );
                    $dispatcher->trigger('onJInboundReportEmailTags', array(&$tags));
                    $result->date          = (object)array(
                        'start' => '2015-01-01 00:00:00',
                        'end'   => '2015-01-07 23:59:59'
                    );
                    $result->goals         = (object)array('count' => 201, 'percent' => 11.0);
                    $result->leads         = (object)array(
                        'count'   => 302,
                        'percent' => 0.0,
                        'list'    => '<table>'
                            . '<thead><tr>'
                            . '<th>' . JText::_('COM_JINBOUND_NAME') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_DATE') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_FORM_CONVERTED_ON') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_LANDING_PAGE_NAME') . '</th>'
                            . '</tr></thead>'
                            . '<tbody>'
                            . '<tr>'
                            . '<td>John Smith</td><td>2015-01-05 12:34:56</td>'
                            . '<td>My Form</td><td>Example Page</td>'
                            . '</tr>'
                            . '<tr>'
                            . '<td>Martha Jones</td><td>2015-01-05 01:23:45</td>'
                            . '<td>My Form</td><td>Example Page</td>'
                            . '</tr>'
                            . '<tr>'
                            . '<td>Rose Tyler</td><td>2015-01-04 16:27:02</td>'
                            . '<td>My Form</td><td>Example Page</td>'
                            . '</tr>'
                            . '<tr>'
                            . '<td>Amy Pond</td><td>2015-01-03 08:11:43</td>'
                            . '<td>My Form</td><td>Example Page</td>'
                            . '</tr>'
                            . '<tr>'
                            . '<td>Rory Williams</td><td>2015-01-02 11:51:16</td>'
                            . '<td>My Form</td><td>Example Page</td>'
                            . '</tr>'
                            . '</tbody>'
                            . '</table>'
                    );
                    $result->pages         = (object)array(
                        'hits' => 1902,
                        'list' => '<table>'
                            . '<thead><tr>'
                            . '<th>' . JText::_('COM_JINBOUND_LANDING_PAGE_NAME') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_VISITS') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_SUBMISSIONS') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_LEADS') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_GOAL_COMPLETIONS') . '</th>'
                            . '<th>' . JText::_('COM_JINBOUND_GOAL_COMPLETION_RATE') . '</th>'
                            . '</tr></thead>'
                            . '<tbody>'
                            . '<tr>'
                            . '<td>Example Page</td>'
                            . '<td>1902</td>'
                            . '<td>341</td>'
                            . '<td>302</td>'
                            . '<td>201</td>'
                            . '<td>11.0%</td>'
                            . '</tr>'
                            . '</tbody>'
                            . '</table>'
                    );
                    $result->pages->top    = (object)array('name' => 'Example Page', 'url' => 'http://example.com');
                    $result->pages->bottom = (object)array('name' => 'Example Page', 'url' => 'http://example.com');
                    break;

                case 'campaign':
                default:
                    $tags                     = array('email.campaign_name', 'email.form_name');
                    $result->lead             = new stdClass();
                    $result->lead->first_name = 'Howard';
                    $result->lead->last_name  = 'Moon';
                    $result->lead->email      = $to;
                    $result->campaign_name    = 'Test Campaign';
                    $result->form_name        = 'Test Form';
                    break;
            }

            $params = new JRegistry();

            $dispatcher->trigger('onContentBeforeDisplay', array('com_jinbound.email', &$result, &$params, 0));

            $htmlbody  = $emailModel->replaceTags($htmlbody, $result, $tags);
            $plainbody = $emailModel->replaceTags($plainbody, $result, $tags);

            $dispatcher->trigger('onContentAfterDisplay', array('com_jinbound.email', &$result, &$params, 0));

            $mail = JFactory::getMailer();
            $mail->ClearAllRecipients();
            $mail->SetFrom($fromemail, $fromname);
            $mail->addRecipient($to, 'Test Email');
            $mail->setSubject($subject);
            $mail->setBody($htmlbody);
            $mail->IsHTML(true);
            $mail->AltBody = $plainbody;

            $sent = $mail->Send();
            if ($sent instanceof Exception) {
                throw $sent;

            } elseif (empty($sent)) {
                throw new Exception('Cannot send email', 500);
            }

        } catch (Exception $response) {
            if (!$response->getCode()) {
                $response = new Exception($response->getMessage(), 500);
            }

        } catch (Throwable $response) {
            if (!$response->getCode()) {
                $response = new Exception($response->getMessage(), 500);
            }
        }

        $output = array(
            'code'    => $response->getCode(),
            'message' => $response->getMessage()
        );

        echo json_encode($output);

        jexit();

    }

    /**
     * @param string $key
     * @param string $urlVar
     *
     * @return bool
     * @throws Exception
     */
    public function edit($key = 'id', $urlVar = 'id')
    {
        if (!JFactory::getUser()->authorise('core.manage', 'com_jinbound.email')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        return parent::edit($key, $urlVar);
    }

    /**
     * @param string $recordId
     * @param string $urlVar
     *
     * @return string
     * @throws Exception
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'set')
    {
        $set    = JFactory::getApplication()->input->get('set', 'a', 'cmd');
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);
        $append .= '&set=' . $set;

        return $append;
    }
}
