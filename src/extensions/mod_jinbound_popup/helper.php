<?php
/**
 * @package             JInbound
 * @subpackage          mod_jinbound_popup
 **********************************************
 * JInbound
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

defined('_JEXEC') or die;

// load required classes
JLoader::register('JInbound', JPATH_ADMINISTRATOR . '/components/com_jinbound/libraries/jinbound.php');
JInbound::registerHelper('form');
JInbound::registerHelper('module');
JInbound::registerHelper('url');

abstract class modJinboundPopupHelper
{
    static $assets_set = false;

    static public function getFormData(&$module, &$params)
    {
        // initialise
        $campaign_id = (int)$params->get('campaignid', 0);
        $form_id     = (int)$params->get('formid', 0);
        if (empty($form_id) || empty($campaign_id)) {
            return false;
        }
        return (object)array(
            'campaign_id'         => $campaign_id,
            'form_id'             => $form_id,
            'page_name'           => preg_replace('/^mod_/', '', $module->module),
            'notification_email'  => $params->get('notification_email', ''),
            'after_submit_sendto' => $params->get('after_submit_sendto', 'message'),
            'menu_item'           => $params->get('menu_item', ''),
            'send_to_url'         => $params->get('send_to_url', ''),
            'sendto_message'      => $params->get('sendto_message', ''),
            'return_url'          => $params->get('return_url', JUri::root(false))
        );
    }

    static public function getForm(&$module, &$params)
    {
        return JInboundHelperForm::getJinboundForm((int)$params->get('formid', 0),
            array('control' => 'mod_jinbound_popup_' . $module->id));
    }

    static public function addHtmlAssets()
    {
        if (!static::$assets_set) {
            $document = JFactory::getDocument();
            $script   = 'popup.js';
            if (!JInbound::version()->isCompatible('3.0.0')) {
                $script = 'popup.legacy.js';
            }
            if (method_exists($document, 'addScript')) {
                $document->addScript(JUri::root() . 'media/mod_jinbound_popup/js/' . $script);
            }
            static::$assets_set = true;
        }
    }

    static public function showForm()
    {
        $show = false;
        if (class_exists('plgSystemJinbound')) {
            $cookie = plgSystemJinbound::getCookieUser();

        }
        return $show;
    }
}