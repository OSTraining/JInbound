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

if (!defined('JINB_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

class JFormFieldJInboundFormFields extends JFormField
{
    public $type = 'Jinboundformfields';

    protected function getInput()
    {
        // text
        JText::script('COM_JINBOUND_JINBOUNDFORMFIELD_ERROR');
        JText::script('COM_JINBOUND_JINBOUNDFORMFIELD_NOSORTABLE');
        // prep the value - it SHOULD be an array, but who knows - maybe it won't be?
        // this is just some defensive coding, really - there's a slim to none chance this code will EVER be accessed!
        $this->prepareValue();
        // load the published fields - we'll sort them into two groups later
        $model = JInboundBaseModel::getInstance('Fields', 'JInboundModel');
        // TODO: why does setState not work?!
        $model->setState('filter.published', '1');
        $fields = $model->getItems();
        // make sure we actually HAVE fields to add :)
        if (empty($fields)) {
            return '<div>' . JText::_('COM_JINBOUND_FORMFIELDS_NO_FIELDS') . '</div>';
        }
        // load up the core filter, to save keystrokes later
        $filter = JFilterInput::getInstance();
        // start our arrays for sorting between the two
        $available = '';
        $assigned  = '';
        // loop the value first, so we can maintain ordering
        if (!empty($this->value)) {
            foreach ($this->value as $fid) {
                // now loop the fields and assign them as they're found
                foreach ($fields as $field) {
                    if ($field->id == $fid) {
                        $assigned[] = $field;
                    }
                }
            }
        }
        // now get the available array
        foreach ($fields as $field) {
            // since we're looping here, go ahead and set an extra variable for the formtype class
            $field->_formtypeclass = 'jinboundformfieldformtype';
            // go ahead and just add this to available if our value is empty
            if (empty($this->value)) {
                $available[] = $field;
                continue;
            }
            // check this field against our array of values
            if (!in_array($field->id, $this->value)) {
                $available[] = $field;
            }
        }
        // ok, we have each sorted - start constructing the html
        $html = array();
        // start by opening our element div
        $html[] = '<div class="jinboundformfields jinboundformfieldsclear">';
        // we need the field lists inside other containers so we can add text labels (#329)
        $html[] = '<div class="jinboundformfieldslist">';
        // add the header to this list
        $html[] = '<h4>' . JText::_('COM_JINBOUND_FORMFIELDS_AVAILABLE') . '</h4>';
        // loop through the available fields and create new tags for each
        // take note we're adding the ul tag regardless so we maintain the 2 lists
        $html[] = '<ul class="jinboundformfieldsavailable jinboundformfieldssortable">';
        if (!empty($available)) {
            foreach ($available as $field) {
                // start this field element
                $html[] = '<li class="jinboundformfield ' . $field->_formtypeclass . '" style="' . $this->_getIconStyle($this->_getIcon($field->type)) . '">';
                // add the text
                $html[] = $filter->clean($field->title, 'string');
                // also add a hidden input element so we can keep track of this element's id
                $html[] = '<input type="hidden" value="' . $field->id . '" />';
                // end this field element
                $html[] = '</li>';
            }
        }
        // end the available fields element
        $html[] = '</ul>';
        // add some extra text
        $html[] = '<p class="jinboundformfieldsdesc">' . JText::_('COM_JINBOUND_FORMFIELDS_AVAILABLE_DESC') . '</p>';
        // end the container
        $html[] = '</div>';
        // open another div for the available fields
        $html[] = '<div class="jinboundformfieldslist">';
        // add the header to this list
        $html[] = '<h4>' . JText::_('COM_JINBOUND_FORMFIELDS_ASSIGNED') . '</h4>';
        // start the list element
        $html[] = '<ul class="jinboundformfieldsassigned jinboundformfieldssortable">';
        // loop through the assigned fields and create new tags for each
        if (!empty($assigned)) {
            foreach ($assigned as $field) {
                // start this field element
                $html[] = '<li class="jinboundformfield ' . $field->_formtypeclass . '" style="' . $this->_getIconStyle($this->_getIcon($field->type)) . '">';
                // for now just add the text
                $html[] = $filter->clean($field->title, 'string');
                // also add a hidden input element so we can keep track of this element's id
                $html[] = '<input type="hidden" value="' . intval($field->id) . '" />';
                // end this field element
                $html[] = '</li>';
            }
        }
        // end the assigned fields element
        $html[] = '</ul>';
        // add some extra text
        $html[] = '<p class="jinboundformfieldsdesc">' . JText::_('COM_JINBOUND_FORMFIELDS_ASSIGNED_DESC') . '</p>';
        // end the container
        $html[] = '</div>';
        // it's not good to have this here, but in the interest of keeping things from breaking add a clearin element
        $html[] = '<div class="jinboundformfieldsclear"><!-- --></div>';
        // go ahead and append a hidden input that will act as our main field
        $html[] = '<input type="' . (JDEBUG ? 'text' : 'hidden') . '" class="jinboundformfieldsinput" name="' . $filter->clean($this->name) . '" value="' . $filter->clean('' . implode('|',
                    $this->value)) . '" />';
        // end the main element
        $html[] = '</div>';

        if (JInboundHelper::version()->isCompatible('3.0.0')) {
            JHtml::_('jquery.ui', array('core', 'sortable'));
        }
        // load the javascript that controls the drag & drop
        JFactory::getDocument()->addScript(rtrim(JUri::root(), '/') . '/media/jinbound/js/formfield.js');
        // load the stylesheet that controls the display of this field
        JFactory::getDocument()->addStyleSheet(rtrim(JUri::root(), '/') . '/media/jinbound/css/formfield.css');
        // return the html to the form
        return implode("\n", $html);
    }

    private function prepareValue()
    {
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
    }

    private function _getIconStyle($icon)
    {
        return 'background-image:url(' . $icon . ');background-repeat:no-repeat;background-position:2px center;';
    }

    private function _getIcon($field)
    {
        static $icons;
        $relpath  = '/images/fields';
        $base     = JInboundHelperUrl::media() . $relpath;
        $iconpath = JPATH_ROOT . '/media/jinbound' . $relpath;
        if (is_null($icons)) {
            $icons = array();
            if (JFolder::exists($iconpath)) {
                // grab the available icons
                $icons = JFolder::files($iconpath, '.png$');
            }
        }
        $icon = "icon-{$field}.png";
        if (in_array($icon, $icons)) {
            return $base . '/' . $icon;
        }
        return $base . '/icon-unknown.png';
    }
}
