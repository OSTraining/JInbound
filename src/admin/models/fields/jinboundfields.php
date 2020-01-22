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

class JFormFieldJInboundFields extends JFormField
{
    public $type = 'Jinboundfields';

    protected function getInput()
    {
        // prepare the value
        $this->prepareValue();
        // load the published fields using db, not model, so we get all
        $db     = JFactory::getDbo();
        $fields = $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from('#__jinbound_fields')
            ->where('published = 1')
        )->loadObjectList();
        // make sure we actually HAVE fields to add :)
        if (empty($fields)) {
            return '<div>' . JText::_('COM_JINBOUND_FORMFIELDS_NO_FIELDS') . '</div>';
        }
        // fix our ordering
        $ordered = array();
        foreach ($this->value as $fieldid) {
            foreach ($fields as $field) {
                if ($field->id == $fieldid) {
                    $ordered[] = $field;
                    break;
                }
            }
        }
        foreach ($fields as $field) {
            if (!in_array($field->id, $this->value)) {
                $ordered[] = $field;
            }
        }

        $cores = array('first_name', 'last_name', 'email');

        foreach ($ordered as $idx => $field) {
            $extra   = '';
            $checked = in_array($field->id, $this->value);
            $which   = array_search($field->name, $cores);
            $core    = in_array($field->name, $cores);
            if (is_numeric($which) && array_key_exists($which, $cores)) {
                unset($cores[$which]);
            }
            if ($checked || $core) {
                $extra .= ' checked="checked"';
            }
            if ($core) {
                $extra .= ' readonly="true" style="display: none !important"';
            }
            $ordered[$idx]->core  = $core;
            $ordered[$idx]->extra = $extra;
        }
        // load scripts
        JText::script('COM_JINBOUND_JINBOUNDFORMFIELD_ERROR');
        JText::script('COM_JINBOUND_JINBOUNDFORMFIELD_NOSORTABLE');
        $doc = JFactory::getDocument();
        $doc->addScript(JUri::root() . '/media/jinbound/js/field.js');
        // load the stylesheet that controls the display of this field
        $doc->addStyleSheet(JUri::root() . '/media/jinbound/css/field.css');
        // load the view
        $view             = $this->getView();
        $view->input_id   = $this->id;
        $view->input_name = $this->name;
        $view->fields     = $ordered;
        $view->value      = $this->value;

        return $view->loadTemplate();
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

    /**
     * gets a new instance of the base field view
     *
     * @return JInboundFieldView
     */
    protected function getView()
    {
        $viewConfig = array('template_path' => dirname(__FILE__) . '/fields');
        $view       = new JInboundFieldView($viewConfig);
        return $view;
    }
}
