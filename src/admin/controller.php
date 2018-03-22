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

class JInboundController extends JInboundBaseController
{
    protected $default_view = 'dashboard';

    /**
     * @return void
     * @throws Exception
     */
    public function reset()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        if (!JFactory::getUser()->authorise('core.admin', 'com_jinbound')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $db      = JFactory::getDbo();
        $app     = JFactory::getApplication();
        $errors  = array();
        $queries = array(
            'TRUNCATE TABLE #__jinbound_contacts',
            'TRUNCATE TABLE #__jinbound_contacts_campaigns',
            'TRUNCATE TABLE #__jinbound_contacts_priorities',
            'TRUNCATE TABLE #__jinbound_contacts_statuses',
            'TRUNCATE TABLE #__jinbound_conversions',
            'TRUNCATE TABLE #__jinbound_emails_records',
            'TRUNCATE TABLE #__jinbound_emails_versions',
            'TRUNCATE TABLE #__jinbound_landing_pages_hits',
            'TRUNCATE TABLE #__jinbound_notes',
            'TRUNCATE TABLE #__jinbound_subscriptions',
            'TRUNCATE TABLE #__jinbound_tracks',
            'TRUNCATE TABLE #__jinbound_users_tracks',
            'UPDATE #__jinbound_pages SET hits = 0 WHERE 1'
        );

        foreach ($queries as $query) {
            try {
                $db->setQuery($query)->execute();

            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        if (!empty($errors)) {
            $app->enqueueMessage(JText::sprintf('COM_JINBOUND_RESET_FAILED', implode('<br>', $errors)), 'error');
        } else {
            $app->enqueueMessage(JText::_('COM_JINBOUND_RESET_SUCCESS'));
        }
        $app->redirect(JInboundHelperUrl::_());
    }

    public function rss()
    {
        // feed whitelist
        $feeds = array(
            'feed' => array('url' => 'https://jinbound.com/blog/feed/rss.html', 'showDescription' => false)
        ,
            'news' => array('url' => 'https://jinbound.com/news/?format=feed', 'showDescription' => false)
        );
        $app   = JFactory::getApplication();
        // check type
        $var = $app->input->get('type');
        if (!in_array($var, array_keys($feeds))) {
            echo 'No data found';
            $app->close();
        }
        $feed = $feeds[$var];
        $app->input->set('layout', 'rss');
        // get RSS view and display its contents
        try {
            $rss                  = new JInboundRSSView();
            $rss->showDetails     = array_key_exists('showDetails', $feed) ? $feed['showDetails'] : false;
            $rss->showDescription = array_key_exists('showDescription', $feed) ? $feed['showDescription'] : true;
            $rss->url             = $feed['url'];
            $rss->getFeed($feed['url']);
            echo $rss->loadTemplate(null, 'rss');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $app->close();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function saverules()
    {
        try {
            if (!JSession::checkToken('get')) {
                throw new Exception(JText::_('JINVALID_TOKEN'));
            }

            $app = JFactory::getApplication();

            /** @var ConfigModelApplication $model */
            JLoader::registerPrefix('Config', JPATH_COMPONENT);
            JLoader::registerPrefix('Config', JPATH_ROOT . '/components/com_config');
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_config/model', 'ConfigModel');

            $model = JModelLegacy::getInstance('Application', 'ConfigModel');

            $permission = array(
                'component' => $app->input->getCmd('comp'),
                'action'    => $app->input->get('action'),
                'rule'      => $app->input->get('rule'),
                'value'     => $app->input->get('value'),
                'title'     => $app->input->get('title', '', 'RAW')
            );

            $result = new JResponseJson($model->storePermissions($permission));

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        if (empty($result)) {
            $result = new JResponseJson();
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        jexit();
    }
}
