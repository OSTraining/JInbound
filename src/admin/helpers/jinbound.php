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

abstract class JInbound
{
    /**
     * @deprecated v3.0.0
     */
    const COM = 'com_jinbound';

    const VERSION = '@ant_version_number@';

    private static $_actions = array(
        'core.admin',
        'core.manage',
        'core.create',
        'core.create.private',
        'core.edit',
        'core.edit.own',
        'core.edit.state',
        'core.delete',
        'core.moderate'
    );

    /**
     * Loads jQuery and Bootstrap
     */
    public static function loadJsFramework()
    {
        $app    = JFactory::getApplication();
        $doc    = JFactory::getDocument();
        $canAdd = method_exists($doc, 'addStyleSheet');
        $ext    = (JInbound::config("debug", 0) ? '.min' : '');
        $sfx    = $app->isAdmin() ? 'back' : 'front';
        if ($canAdd) {
            if (JInbound::version()->isCompatible('3.0.0')) {
                JHtml::_('behavior.framework', true);
                JHtml::_('jquery.ui', array('core', 'sortable', 'tabs'));
                JHtml::_('bootstrap.tooltip');
            } else {
                JHtml::_('behavior.tooltip', '.hasTip');
            }
            if (JInbound::config("load_jquery_$sfx", 1)) {
                $doc->addScript(JInboundHelperUrl::media() . '/js/jquery-1.9.1.min.js');
            }
            if (JInbound::config("load_jquery_ui_$sfx", 1)) {
                $doc->addStyleSheet(JInboundHelperUrl::media() . '/ui/css/jinbound_component/jquery-ui-1.10.1.custom' . $ext . '.css');
                $doc->addScript(JInboundHelperUrl::media() . '/ui/js/jquery-ui-1.10.1.custom' . $ext . '.js');
            }
            if (JInbound::config("load_bootstrap_$sfx", 1)) {
                $doc->addStyleSheet(JInboundHelperUrl::media() . '/bootstrap/css/bootstrap.css');
                $doc->addStyleSheet(JInboundHelperUrl::media() . '/bootstrap/css/bootstrap-responsive.css');
                $doc->addScript(JInboundHelperUrl::media() . '/bootstrap/js/bootstrap' . $ext . '.js');
            }
        }
    }

    /**
     * static method to get either the component parameters,
     * or when a key is supplied the value of that key
     * if val is supplied (with a key) def() is used instead of get()
     *
     * @param  string $key
     * @param  mixed  $val
     *
     * @return mixed
     */
    public static function config($key = null, $val = null)
    {
        static $params;
        if (!isset($params)) {
            $app = JFactory::getApplication();
            // get the params, either from the helper or the application
            if ($app->isAdmin() || JInbound::COM != $app->input->get('option', '', 'cmd')) {
                $params = JComponentHelper::getParams(JInbound::COM);
            } else {
                $params = $app->getParams();
            }
        }
        // if we don't have a key, return the entire params object
        if (is_null($key) || empty($key)) {
            return $params;
        }
        // return the param value, with optional def
        if (is_null($val)) {
            return $params->get($key);
        }
        return $params->def($key, $val);
    }

    /**
     * gets an instance of JVersion
     *
     */
    public static function version()
    {
        static $version;
        if (is_null($version)) {
            $version = new JVersion;
        }
        return $version;
    }

    /**
     * static method to register a library
     *
     * @param string $class
     * @param string $file
     */
    public static function registerLibrary($class, $file)
    {
        static $libraries;
        if (!is_array($libraries)) {
            $libraries = array();
        }
        if (array_key_exists($class, $libraries)) {
            return;
        }
        if (false === JString::strpos($file, '.php')) {
            $file = "$file.php";
        }
        JLoader::register($class, JInboundHelperPath::library($file));
    }

    /**
     * static method to keep track of debug info
     *
     * @param  string $name
     * @param  mixed  $data
     *
     * @return array
     */
    public static function debugger($name = null, $data = null)
    {
        static $debug;
        if (!is_array($debug)) {
            $debug = array();
        }
        if (!is_null($name)) {
            $debug[$name] = $data;
        }
        return $debug;
    }

    /**
     * static method to aide in debugging
     *
     * @param  mixed  $data
     * @param  string $fin
     *
     * @return mixed
     */
    public static function debug($data, $fin = 'echo')
    {
        if (!JInbound::config("debug", 0)) {
            return '';
        }
        $e      = new Exception;
        $output = "<pre>\n" . htmlspecialchars(print_r($data, 1)) . "\n\n" . $e->getTraceAsString() . "\n</pre>\n";
        switch ($fin) {
            case 'return':
                return $output;
            case 'die'   :
                echo $output;
                die();
            case 'echo'  :
            default      :
                echo $output;
                return;
        }
    }

    public static function getActions($categoryId = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        $assetName = JInbound::COM . (empty($categoryId) ? '' : '.category.' . (int)$categoryId);

        foreach (self::$_actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function userDate($utc_date)
    {
        static $offset;
        static $timezone;
        if (is_null($offset)) {
            $config = JFactory::getConfig();
            $offset = $config->get('offset');
        }
        if (is_null($timezone)) {
            $user     = JFactory::getUser();
            $timezone = $user->getParam('timezone', $offset);
        }
        $date = JFactory::getDate($utc_date, 'UTC');
        $date->setTimezone(new DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s', true, false);
    }
}
