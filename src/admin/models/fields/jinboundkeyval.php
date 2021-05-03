<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

jimport('joomla.form.formfield');
jimport('joomla.form.helper');

class JFormFieldJInboundKeyVal extends JFormField
{
    public $type = 'Jinboundkeyval';

    protected function getInput()
    {
        // add our texts
        JText::script('COM_JINBOUND_JINBOUNDKEYVAL_EMPTY');
        JText::script('COM_JINBOUND_JINBOUNDKEYVAL_EMPTY_REMOVE');
        JText::script('COM_JINBOUND_JINBOUNDKEYVAL_ERROR');
        // prep the value - it SHOULD be an array, but who knows - maybe it won't be?
        // this is just some defensive coding, really - there's a slim to none chance this code will EVER be accessed!
        if (!is_array($this->value)) {
            if (false !== strpos((string)$this->value, ',')) {
                $this->value = explode(',', (string)$this->value);
            } else {
                if (false !== strpos((string)$this->value, '|')) {
                    $this->value = explode('|', (string)$this->value);
                } else {
                    if (!empty($this->value)) {
                        $this->value = (array)$this->value;
                    } else {
                        $this->value = array();
                    }
                }
            }
        }
        // yikes, sometimes the value get mangled (like a bad save/recovery)
        if (is_array($this->value) && 2 == count($this->value)
            && array_key_exists('key', $this->value) && is_array($this->value['key'])
            && array_key_exists('value', $this->value) && is_array($this->value['value'])) {
            $values = array();
            foreach ($this->value['key'] as $i => $key) {
                if (empty($key)) {
                    continue;
                }
                $values[$key] = $this->value['value'][$i];
            }
            $this->value = $values;
        }

        $filter = JFilterInput::getInstance();
        // get class for this element
        $class = $this->element['class'] ? ' ' . $filter->clean((string)$this->element['class']) : '';
        // get labels
        $keylabel   = $this->element['keylabel'] ? $filter->clean(JText::_((string)$this->element['keylabel'])) : '';
        $valuelabel = $this->element['valuelabel'] ? $filter->clean(JText::_((string)$this->element['valuelabel'])) : '';
        // start constructing the html
        $html = array();
        // start the main element
        $html[] = '<div class="jinboundkeyval' . $class . '">';
        // create the key/value labels, if applicable
        if (!empty($keylabel) && !empty($valuelabel)) {
            $html[] = '<div class="jinboundkeyval_labels">';
            $html[] = '	<span>' . $keylabel . '</span>';
            $html[] = '	<span>' . $valuelabel . '</span>';
            $html[] = '</div>';
        }
        // build our base element (for cloning later)
        $html[] = $this->_getDefaultRuleBlock();
        // start the stage
        $html[] = '<div class="jinboundkeyval_stage">';
        // add values
        if (!empty($this->value)) {
            foreach ($this->value as $key => $value) {
                // add the block for this pair
                $html[] = $this->_getRuleBlock($key, $value);
            }
        }
        // add blank
        $html[] = $this->_getRuleBlock();
        // end the stage
        $html[] = '</div>';
        // end the main element
        $html[] = '</div>';

        HTMLHelper::_('script', 'jinbound/keyval.js', array('relative' => true));
        HTMLHelper::_('stylesheet', 'jinbound/keyval.css', array('relative' => true));

        return implode("\n", $html);
    }

    private function _getDefaultRuleBlock()
    {
        return sprintf('<div class="jinboundkeyval_default" style="display:none">%s</div>', $this->_getRuleBlock());
    }

    private function _getRuleBlock($key = '', $val = '')
    {
        // get our fields
        $useKey     = $this->_getInput($key, 'key');
        $useValue   = $this->_getInput($val, 'value');
        $useButtons = $this->_getButtons();
        // build & return html
        return sprintf('<div class="jinboundkeyval_block">%s</div>',
            '<span class="jinboundkeyval_inputs">' . $useKey . ' ' . $useValue . '</span> ' . $useButtons);
    }

    private function _getInput($value, $name)
    {
        $filter = JFilterInput::getInstance();
        return '<input class="jinboundkeyval_' . $filter->clean($name) . '" name="' . $filter->clean($this->name) . '[' . $filter->clean($name) . '][]" type="text" value="' . $filter->clean($value) . '" />';
    }

    private function _getButtons()
    {
        // build the buttons
        $button  = '<input type="button" class="jinboundkeyval_%s" value=" %s " />';
        $buttons = sprintf($button, 'add', '+') . ' ' . sprintf($button, 'sub', '-');
        // if this element has the ordering attribute, add ordering buttons too
        if ($this->element['ordering']) {
            $buttons .= ' ' . sprintf($button, 'up', '↑') . ' ' . sprintf($button, 'down', '↓');
        }
        // return the buttons
        return $buttons;
    }

}
