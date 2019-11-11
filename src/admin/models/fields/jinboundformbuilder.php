<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

if (!defined('JINB_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

class JFormFieldJinboundFormBuilder extends JFormField
{
    protected $type = 'JinboundFormBuilder';

    /**
     * This method is used in the form display to show extra data
     *
     */
    public function getSidebar()
    {
        // return template html
        return $this->getView()->loadTemplate('sidebar');
    }

    /**
     * get the available form fields
     *
     * TODO: make this better later
     */
    public function getFormFields()
    {
        $fields     = array(
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_FIRST_NAME'),
                'id'    => 'first_name',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_LAST_NAME'),
                'id'    => 'last_name',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_EMAIL'),
                'id'    => 'email',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_WEBSITE'),
                'id'    => 'website',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_COMPANY_NAME'),
                'id'    => 'company_name',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_PHONE_NUMBER'),
                'id'    => 'phone_number',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_ADDRESS'),
                'id'    => 'address',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_SUBURB'),
                'id'    => 'suburb',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_STATE'),
                'id'    => 'state',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_COUNTRY'),
                'id'    => 'country',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_POSTCODE'),
                'id'    => 'postcode',
                'type'  => 'text',
                'multi' => 0
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_TEXT'),
                'id'    => 'text',
                'type'  => 'text',
                'multi' => 1
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_TEXTAREA'),
                'id'    => 'textarea',
                'type'  => 'textarea',
                'multi' => 1
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_CHECKBOXES'),
                'id'    => 'checkboxes',
                'type'  => 'checkboxes',
                'multi' => 1
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_RADIO'),
                'id'    => 'radio',
                'type'  => 'radio',
                'multi' => 1
            ),
            (object)array(
                'name'  => JText::_('COM_JINBOUND_PAGE_FIELD_SELECT'),
                'id'    => 'select',
                'type'  => 'list',
                'multi' => 1
            )
        );
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onJinboundFormbuilderFields', array(&$fields));
        return $fields;
    }

    /**
     * Builds the input element for the form builder
     *
     * (non-PHPdoc)
     * @see JFormField::getInput()
     */
    protected function getInput()
    {
        // return template html
        return $this->getView()->loadTemplate();
    }

    /**
     * gets a new instance of the base field view
     *
     * @return JInboundFieldView
     */
    protected function getView()
    {
        $viewConfig     = array('template_path' => dirname(__FILE__) . '/formbuilder');
        $view           = new JInboundFieldView($viewConfig);
        $view->input    = $this;
        $view->value    = $this->getFormValue();
        $view->input_id = $view->escape($this->id);
        $dispatcher     = JDispatcher::getInstance();
        $dispatcher->trigger('onJinboundFormbuilderView', array(&$view));
        return $view;
    }

    /**
     * public method to fetch the value
     *
     * TODO finish this
     */
    public function getFormValue()
    {
        if (!($this->value instanceof Registry)) {
            $reg = new Registry();
            if (is_array($this->value)) {
                $reg->loadArray($this->value);
            } else {
                if (is_object($this->value)) {
                    $reg->loadObject($this->value);
                } else {
                    if (is_string($this->value)) {
                        $reg->loadString($this->value);
                    }
                }
            }
            $this->value = $reg;
        }
        foreach (array('first_name', 'last_name', 'email') as $field) {
            $def = $this->value->get($field, false);
            if (!$def) {
                $this->value->set($field, json_decode(json_encode(array(
                    'title'    => JText::_('COM_JINBOUND_PAGE_FIELD_' . $field)
                ,
                    'name'     => $field
                ,
                    'enabled'  => 1
                ,
                    'required' => 1
                ))));
            } else {
                if (is_object($def) && $def instanceof Registry) {
                    $def->set('enabled', 1);
                    $def->set('required', 1);
                } else {
                    if (is_object($def)) {
                        $def->enabled  = 1;
                        $def->required = 1;
                    } else {
                        if (is_array($def)) {
                            $def['enabled']  = 1;
                            $def['required'] = 1;
                        }
                    }
                }
            }
        }

        $ordering = $this->value->get('__ordering');

        if (empty($ordering)) {
            return $this->value->toArray();
        }

        if (!is_array($ordering)) {
            $ordering = explode('|', $ordering);
        }

        $unordered = $this->value->toArray();
        $ordered   = new Registry();
        foreach ($ordering as $key) {
            if (array_key_exists($key, $unordered)) {
                $ordered->set($key, $unordered[$key]);
            }
        }
        foreach ($unordered as $key => $value) {
            if (!array_key_exists($key, $ordered)) {
                $ordered->set($key, $value);
            }
        }

        $this->value = $ordered;

        return $this->value->toArray();
    }
}
