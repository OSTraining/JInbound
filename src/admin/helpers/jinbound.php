<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2020 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of jInbound.
 *
 * jInbound is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * jInbound is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with jInbound.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('JPATH_PLATFORM') or die;

abstract class JInboundHelper extends JHelperContent
{
    /**
     * @deprecated v3.0.0
     */
    const COM = 'com_jinbound';

    const VERSION = '@ant_version_number@';

    public static $extension = 'com_jinbound';

    /**
     * @return void
     * @throws Exception
     */
    public static function loadJsFramework()
    {
        $app    = JFactory::getApplication();
        $doc    = JFactory::getDocument();
        $canAdd = method_exists($doc, 'addStyleSheet');
        $ext    = (JInboundHelper::config("debug", 0) ? '.min' : '');
        $sfx    = $app->isClient('administrator') ? 'back' : 'front';
        if ($canAdd) {
            if (JInboundHelper::version()->isCompatible('3.0.0')) {
                JHtml::_('behavior.framework', true);
                JHtml::_('jquery.ui', array('core', 'sortable', 'tabs'));
                JHtml::_('bootstrap.tooltip');
            } else {
                JHtml::_('behavior.tooltip', '.hasTip');
            }
            if (JInboundHelper::config("load_jquery_$sfx", 1)) {
                $doc->addScript(JInboundHelperUrl::media() . '/js/jquery-1.9.1.min.js');
            }
            if (JInboundHelper::config("load_jquery_ui_$sfx", 1)) {
                $doc->addStyleSheet(JInboundHelperUrl::media() . '/ui/css/jinbound_component/jquery-ui-1.10.1.custom' . $ext . '.css');
                $doc->addScript(JInboundHelperUrl::media() . '/ui/js/jquery-ui-1.10.1.custom' . $ext . '.js');
            }
            if (JInboundHelper::config("load_bootstrap_$sfx", 1)) {
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
     * @throws Exception
     */
    public static function config($key = null, $val = null)
    {
        static $params;
        if (!isset($params)) {
            $app = JFactory::getApplication();
            // get the params, either from the helper or the application
            if ($app->isClient('administrator') || $app->input->get('option', '', 'cmd') != 'com_jinbound') {
                $params = JComponentHelper::getParams('com_jinbound');

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
     * @return string
     * @throws Exception
     */
    public static function debug($data, $fin = 'echo')
    {
        if (!JInboundHelper::config("debug", 0)) {
            return '';
        }
        $e      = new Exception;
        $output = "<pre>\n" . htmlspecialchars(print_r($data, 1)) . "\n\n" . $e->getTraceAsString() . "\n</pre>\n";
        switch ($fin) {
            case 'return':
                return $output;
            case 'die':
                echo $output;
                die();
            case 'echo':
            default:
                echo $output;
        }

        return '';
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

    public static function getActions($component = 'com_jinbound', $section = '', $id = 0)
    {
        return parent::getActions($component, $section, $id);
    }

    /**
     * Configure the Linkbar.
     *
     * @param   string $vName The name of the active view.
     *
     * @return  void
     * @throws Exception
     */
    public static function addSubmenu($vName)
    {
        JInboundHelper::registerLibrary('JInboundView', 'views/baseview');
        $comView = new JInboundView();
        $comView->addMenuBar();
    }
}
