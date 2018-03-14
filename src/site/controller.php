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

JLoader::register('JInboundBaseController',
    JPATH_ADMINISTRATOR . '/components/com_jinbound/libraries/controllers/basecontroller.php');
JLoader::register('JInbound', JPATH_ADMINISTRATOR . '/components/com_jinbound/helpers/jinbound.php');

class JInboundController extends JInboundBaseController
{
    function display($cachable = false, $urlparams = false)
    {
        $app  = JFactory::getApplication();
        $view = $app->input->get('view', 'page', 'cmd');
        $app->input->set('view', $view);
        if ('page' !== $view) {
            return parent::display($cachable);
        }
        $pop = $app->input->get('pop', array(), 'raw');
        if (is_array($pop) && !empty($pop)) {
            $state = $app->getUserState('com_jinbound.page.data');
            if (is_object($state)) {
                $state->lead = $pop;
            } else {
                if (is_array($state)) {
                    $state['lead'] = $pop;
                }
            }
            $app->setUserState('com_jinbound.page.data', $state);
        }
        return parent::display($cachable);
    }

    /**
     * controller action to run cron tasks
     *
     * TODO
     */
    function cron()
    {
        $out = JInbound::config("debug", 0);
        // send reports emails
        if ($out) {
            echo "<h2>Sending reports</h2>\n";
        }
        require_once JPATH_ADMINISTRATOR . '/components/com_jinbound/models/reports.php';
        $model = $this->getModel('Reports', 'JInboundModel');
        $model->send();
        // handle sending campaign emails
        if ($out) {
            echo "<h2>Sending campaigns</h2>\n";
        }
        require_once JPATH_ADMINISTRATOR . '/components/com_jinbound/models/emails.php';
        $model = $this->getModel('Emails', 'JInboundModel');
        $model->send();
        // handle old tracks
        $debug    = (int)JInbound::config('debug', 0);
        $history  = (int)JInbound::config('history', 365);
        $interval = $debug ? 'SECOND' : 'DAY';
        if (0 < $history) {
            $db = JFactory::getDbo();
            $db->setQuery($db->getQuery(true)
                ->delete('#__jinbound_tracks')
                ->where("created < DATE_SUB(NOW(), INTERVAL $history $interval)")
            )->query();
            if ($debug) {
                $count = $db->getAffectedRows();
                echo "\n<h4>Clearing old Tracks...</h4>";
                echo "\n<p>Removed $count tracks!</p>\n";
            }
        }
        // exit
        jexit();
    }

    /**
     * Disables all emails sent from jinbound to this user
     *
     */
    function unsubscribe()
    {
        $app  = JFactory::getApplication();
        $db   = JFactory::getDbo();
        $menu = $app->getMenu('site')->getDefault()->id;
        // default message sent to user
        $redirect = JRoute::_('index.php?Itemid=' . $menu, false);
        // find the contact based on email
        $email = trim('' . $app->input->get('email', '', 'string'));
        // if no email, bail
        if (empty($email)) {
            $app->redirect($redirect, JText::_('COM_JINBOUND_UNSUBSCRIBE_FAILED_NO_EMAIL'), 'error');
            jexit();
        }
        // lookup contact based on email
        $db->setQuery($db->getQuery(true)
            ->select('id')
            ->from('#__jinbound_contacts')
            ->where('email = ' . $db->quote($email))
        );
        try {
            $contact_id = (int)$db->loadResult();
            if (empty($contact_id)) {
                throw new Exception('COM_JINBOUND_UNSUBSCRIBE_FAILED_NO_CONTACT');
            }
        } catch (Exception $e) {
            $app->redirect($redirect, JText::_($e->getMessage()), 'error');
            jexit();
        }
        // blind delete this contact & rebuild entry
        $db->setQuery($db->getQuery(true)
            ->delete('#__jinbound_subscriptions')
            ->where("contact_id = $contact_id")
        );
        try {
            $db->query();
        } catch (Exception $e) {
            $app->redirect($redirect, JText::_($e->getMessage()), 'error');
            jexit();
        }
        $db->setQuery($db->getQuery(true)
            ->insert('#__jinbound_subscriptions')
            ->columns(array('contact_id', 'enabled'))
            ->values("$contact_id, 0")
        );
        try {
            $db->query();
        } catch (Exception $e) {
            $app->redirect($redirect, JText::_($e->getMessage()), 'error');
            jexit();
        }
        // handle exit
        $app->redirect($redirect, JText::_('COM_JINBOUND_UNSUBSCRIBED'), 'message');
        jexit();
    }

    function landingpageurl()
    {
        $id   = JFactory::getApplication()->input->get('id', array(), 'array');
        $data = array('links' => array());
        if (!empty($id)) {
            JInbound::registerHelper('url');
            if (!is_array($id)) {
                $id = array($id);
            }
            foreach ($id as $i) {
                $link   = array();
                $nonsef = JInboundHelperUrl::view('page', false, array('id' => $i));
                // Before continuing make sure we had an Itemid
                if (!preg_match('/Itemid\=[1-9][0-9]*?/', $nonsef)) {
                    $link['error'] = JText::_('COM_JINBOUND_NEEDS_MENU');
                } else {
                    $sef            = JInboundHelperUrl::view('page', true, array('id' => $i));
                    $link['nonsef'] = JInboundHelperUrl::toFull($nonsef);
                    $link['sef']    = JInboundHelperUrl::toFull($sef);
                    $link['root']   = JURI::root();
                    $link['rel']    = array('nonsef' => $nonsef, 'sef' => $sef);
                }
                $link['id']      = $i;
                $data['links'][] = $link;
            }
            if (1 == count($id)) {
                $data = array_shift($data['links']);
            }
        } else {
            $data['error'] = JText::_('COM_JINBOUND_NOT_FOUND');
        }
        $data['request'] = array('id' => $id);

        echo json_encode($data);
        die;
    }
}
