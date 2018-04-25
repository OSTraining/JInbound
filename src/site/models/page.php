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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class JInboundModelPage extends JInboundAdminModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.page';

    protected $data = null;

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return bool|JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        $app = JFactory::getApplication();

        // Get the form.
        $form = $this->loadForm(
            'com_jinbound.lead_front',
            'lead_front',
            array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }

        $formid = intval($this->getItem($app->input->get('page_id', 0, 'int'))->formid);

        $fields = JInboundHelperForm::getFields($formid);
        if (empty($fields)) {
            return $form;
        }

        $this->addFieldsToForm($fields, $form, JText::_('COM_JINBOUND_FIELDSET_LEAD'));

        return $form;
    }

    /**
     * @param object[] $fields
     * @param JForm    $form
     * @param string   $label
     *
     * @return void
     * @throws Exception
     */
    public function addFieldsToForm($fields, $form, $label)
    {
        // our custom fields should not have the following as extras
        $banned = array('name', 'type', 'default', 'label', 'description', 'class', 'classname');

        $xml = new SimpleXMLElement('<form></form>');

        $xmlFields = $xml->addChild('fields');
        $xmlFields->addAttribute('name', 'lead');

        $xmlFieldset = $xmlFields->addChild('fieldset');
        $xmlFieldset->addAttribute('name', 'lead');
        $xmlFieldset->addAttribute('label', $label);

        foreach ($fields as $field) {
            $thisbanned = array_merge(array(), $banned);

            $xmlField = $xmlFieldset->addChild('field');
            $xmlField->addAttribute('name', $field->name);
            $xmlField->addAttribute('type', $field->type);
            $xmlField->addAttribute('default', $field->default);
            $xmlField->addAttribute('label', $field->title);
            $xmlField->addAttribute('description', $field->description);

            $classes = array();
            if (($isEmail = ('email' === $field->name || 'email' === $field->type))) {
                $xmlField->addAttribute('validate', 'email');
                $classes[]    = 'validate-email';
                $thisbanned[] = 'validate';
            }

            if (array_key_exists('classname', $field->params) && !empty($field->params['classname'])) {
                $parts   = explode(' ', $field->params['classname']);
                $classes = array_merge($classes, $parts);
            }
            if (!empty($classes)) {
                $xmlField->addAttribute('class', implode(' ', $classes));
            }
            // required fields
            if (array_key_exists('required', $field->params) && is_numeric(trim($field->params['required']))) {
                $xmlField->addAttribute('required', $field->params['required']);
                $thisbanned[] = 'required';
            }

            $transpose = false;
            $blank     = false;

            // handle extra attributes
            if (array_key_exists('attrs', $field->params)
                && !empty($field->params['attrs'])
                && is_array($field->params['attrs'])
            ) {
                foreach ($field->params['attrs'] as $key => $value) {
                    if (empty($key) || in_array($key, $thisbanned)) {
                        continue;
                    }
                    switch ($key) {
                        case 'transpose':
                        case 'mirror':
                            $$key = ('true' == strtolower($value) || '1' == "$value" || 'yes' == strtolower($value));
                            break;

                        case 'blank':
                            $blank = $value;
                            break;
                    }

                    $xmlField->addAttribute($key, $value);
                    $thisbanned[] = $key;
                }
            }

            if (array_key_exists('opts', $field->params)
                && !empty($field->params['opts'])
                && is_array($field->params['opts'])
            ) {
                if ($blank) {
                    $xmlOption = $xmlField->addChild('option', JText::_($blank));
                    $xmlOption->addAttribute('value', '');
                }

                foreach ($field->params['opts'] as $key => $value) {
                    if (empty($key)) {
                        continue;
                    }

                    $xmlOption = $xmlField->addChild('option', ($transpose ? $key : $value));
                    $xmlOption->addAttribute('value', ($transpose ? $value : $key));
                }
            }
        }

        JEventDispatcher::getInstance()->trigger('onJinboundFormbuilderDisplay', array(&$xml));

        $form->load($xml, false);

        $formData = $this->loadFormData();
        $form->bind($formData);
    }

    /**
     * @return object
     * @throws Exception
     */
    protected function loadFormData()
    {
        if ($this->data === null) {
            $app = JFactory::getApplication();

            $this->data = ArrayHelper::toObject((array)$app->getUserState('com_jinbound.page.data', array()));
        }

        return $this->data;
    }
}
