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

use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

if (!defined('JINB_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

if (!class_exists('ModJInboundCTAHelper')) {
    if (!file_exists($helper = JPATH_ROOT . '/modules/mod_jinbound_cta/helper.php')) {
        return false;
    }
    require_once $helper;
}

class JFormFieldModJInboundCTACondition extends JFormField
{
    public $type = 'ModJInboundCTACondition';

    protected function getInput()
    {
        $this->insertScript();
        $html = array();
        // start main element
        $html[] = '<div id="' . $this->id . '">';
        // add new buttons
        $html[]  = '<div id="' . $this->id . '_buttons" class="btn-group">';
        $buttons = ModJInboundCTAHelper::getButtonData();
        foreach (array('isnew', 'status', 'priority', 'campaign') as $button) {
            $html[] = $this->getButton("MOD_JINBOUND_CTA_$button", array_merge($buttons[$button], array(
                'group' => $this->fieldname
            )));
        }
        $html[] = '</div>';
        // start rendered inputs
        $html[] = '<div id="' . $this->id . '_controls" class="condition_controls">';
        $html[] = '</div>';
        // close main element
        $html[] = '</div>';
        return implode($html);
    }

    protected function insertScript()
    {
        // more than one field may load this script, so only load once
        global $mod_jinbound_cta_script_loaded;
        // only this field cares about these
        static $mod_jinbound_cta_css_loaded;
        static $mod_jinbound_cta_url_set;
        // init
        $doc = JFactory::getDocument();
        // check that this document type supports scripting
        if (!method_exists($doc, 'addScript')) {
            return;
        }
        // only add main script once
        if (is_null($mod_jinbound_cta_script_loaded)) {
            JHtml::_('jquery.framework');
            HTMLHelper::_('script', 'mod_jinbound_cta/admin.js', array('relative' => true));

            $mod_jinbound_cta_script_loaded = true;
        }
        if (is_null($mod_jinbound_cta_css_loaded)) {
            HTMLHelper::_('stylesheet', 'mod_jinbound_cta/modjinboundctacondition.css', array('relative' => true));

            $mod_jinbound_cta_css_loaded = true;
        }
        if (is_null($mod_jinbound_cta_url_set)) {
            $url = JURI::root(false) . 'index.php?option=com_ajax&module=jinbound_cta&method=getField&format=json';
            $doc->addScriptDeclaration("window.ModJInboundCTAConditionURL = '$url';");
            JFactory::getLanguage()->load('com_ajax', JPATH_ROOT);
            JText::script('COM_AJAX_MODULE_NOT_ACCESSIBLE');
            JText::script('MOD_JINBOUND_CTA_PUBLISH_AND_SAVE_FIRST');
        }
        $value = is_string($this->value) ? $this->value : json_encode($this->value);
        if (empty($value)) {
            $value = '{}';
        }
        $defaults = json_encode(ModJInboundCTAHelper::getDefaultEmptyFields($this->value));
        if (empty($defaults)) {
            $defaults = '[]';
        }
        $doc->addScriptDeclaration("jQuery(document).ready(function(){"
            . "window.ModJInboundCTAConditionURLs = $defaults;"
            . "window.ModJInboundCTACondition('{$this->id}', {$value});"
            . "});");
    }

    protected function getButton($text, $data = array())
    {
        $attributes = array();
        foreach ($data as $key => $value) {
            $attributes[] = 'data-' . $key . '="' . $value . '"';
        }
        $button = '<button class="btn jgrid" type="button" %s> <span class="icon-save-new state icon-16-newarticle"></span> %s </button>';
        return sprintf($button, implode(' ', $attributes), JText::_($text));
    }
}
