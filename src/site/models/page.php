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
        // Get the form.
        $form = $this->loadForm(
            'com_jinbound.lead_front',
            'lead_front',
            array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }

        $formId = (int)$this->getState('form.id');
        if (!$formId) {
            if ($pageId = (int)$this->getState('page.id')) {
                if ($page = $this->getItem($pageId)) {
                    $formId = $page->formid;
                }
            }
        }

        if ($formId) {
            $fields = JInboundHelperForm::getFields($formId);
            if (empty($fields)) {
                return $form;
            }

            $this->addFieldsToForm($fields, $form, JText::_('COM_JINBOUND_FIELDSET_LEAD'));

            return $form;
        }

        throw new Exception(sprintf('Unable to find requested form (%s)', $formId));
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

    /**
     * @throws Exception
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        $pageId = $app->input->getInt('page_id');
        $this->setState('page.id', $pageId);

        parent::populateState();
    }
}
