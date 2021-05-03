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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

abstract class JInboundHelperForm
{
    /**
     * @var string[]
     */
    protected static $requiredFields = array('first_name', 'last_name', 'email');

    /**
     * @var object[][]
     */
    protected static $formFields = array();

    /**
     * @param int   $formId
     * @param array $formOptions
     *
     * @return JForm
     * @throws Exception
     */
    public static function getJinboundForm($formId, $formOptions = array())
    {
        if (empty($formId)) {
            return null;
        }

        JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_jinbound/models/forms');
        try {
            $options = array_merge(array('control' => 'jform'), $formOptions);
            $form    = JForm::getInstance(
                'jinbound_form_module_' . md5(serialize($options)),
                '<form><!-- --></form>',
                $options
            );
        } catch (Exception $e) {
            return null;
        }

        /** @var JInboundModelPage $model */
        $model = JModelLegacy::getInstance('Page', 'JInboundModel');

        // add fields to form
        $fields = static::getFields($formId);
        $model->addFieldsToForm($fields, $form, JText::_('COM_JINBOUND_FIELDSET_LEAD'));

        // sanity checks
        if (empty($fields) || !($form instanceof JForm)) {
            return null;
        }

        return $form;
    }

    /**
     * @param int $id
     *
     * @return object[]
     */
    public static function getFields($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return null;
        }

        $key = 'form_' . $id;
        if (empty(static::$formFields[$key])) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('Field.*')
                ->from('#__jinbound_fields AS Field')
                ->leftJoin('#__jinbound_form_fields AS Xref ON Xref.field_id = Field.id')
                ->leftJoin('#__jinbound_forms AS Form ON Xref.form_id = Form.id')
                ->where(
                    array(
                        'Field.published = 1',
                        'Form.published = 1',
                        'Xref.form_id = ' . $id
                    )
                )
                ->group('Field.id')
                ->order('Xref.ordering ASC');

            $fields = $db->setQuery($query)->loadObjectList('name');
            foreach ($fields as $field) {
                $params        = new Registry($field->params);
                $field->params = $params->toArray();
                $field->reg    = $params;
            }
            static::$formFields[$key] = $fields;
        }

        return static::$formFields[$key];
    }

    /**
     * @param string $name
     * @param string $data
     * @param string $asset
     *
     * @return JForm
     * @throws Exception
     */
    public static function getForm($name, $data, $asset = false)
    {
        JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_jinbound/models/forms');

        $form = JForm::getInstance($name, $data);
        if ($form instanceof JForm) {
            if ($asset) {
                $db = JFactory::getDbo();
                $db->setQuery(
                    $db->getQuery(true)
                        ->select('id, rules')
                        ->from('#__assets')
                        ->where('name = ' . $db->Quote($asset))
                );

                $rules = $db->loadObject();
                if (!empty($rules)) {
                    $form->bind(array('asset_id' => $rules->id, 'rules' => $rules->rules));
                }
            }

            return $form;
        }

        throw new Exception(JText::_('JERROR_NOT_A_FORM'));
    }

    /**
     * @param string $name
     * @param int    $formid
     *
     * @return object
     * @throws Exception
     */
    public static function getField($name, $formid)
    {
        $app    = JFactory::getApplication();
        $fields = static::getFields($formid);
        if (empty($fields)) {
            if (JDEBUG) {
                $app->enqueueMessage('[DBG][' . __METHOD__ . '] Fields empty');
            }
            return null;
        }

        $realname = preg_replace('/^jform\[lead\]\[(.*?)\](\[\])?$/', '$1', $name);
        if (JDEBUG) {
            $app->enqueueMessage(
                sprintf(
                    '[DBG][%s] Got real name "%s" from field name "%s"',
                    __METHOD__,
                    htmlspecialchars($realname, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                )
            );
        }

        if (isset($fields[$realname])) {
            return $fields[$realname];
        }

        if (JDEBUG) {
            $app->enqueueMessage('[DBG][' . __METHOD__ . '] Could not find field');
        }

        return null;
    }

    /**
     * Determines if old forms need to be migrated
     *
     * @return boolean
     */
    public static function needsMigration()
    {
        $db = JFactory::getDbo();

        $old = $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_pages')
                ->where('formid = 0')
                ->where('formbuilder <> ' . $db->quote(''))
        )
            ->loadColumn();

        $new = $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_forms')
            . ' UNION '
            . $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_fields')
        )
            ->loadColumn();

        return !empty($old) && empty($new);
    }

    /**
     * @return string
     */
    public static function getMigrationWarning()
    {
        return JText::sprintf('COM_JINBOUND_NEEDS_FORM_MIGRATION', JInboundHelperUrl::task('forms.migrate', false));
    }

    /**
     * @return bool
     */
    public static function needsDefaultFields()
    {
        $fields = JInboundHelperForm::getDefaultFields();

        return count($fields) < 3;
    }

    /**
     * @return object[]
     */
    public static function getDefaultFields()
    {
        $db = JFactory::getDbo();

        $defaultFields = array_map(array($db, 'quote'), static::$requiredFields);

        $fields = $db->setQuery(
            $db->getQuery(true)
                ->select('*')
                ->from('#__jinbound_fields')
                ->where(
                    array(
                        sprintf('name IN (%s)', join(',', $defaultFields)),
                        'published = 1'
                    )
                )
        )
            ->loadObjectList();

        return $fields;
    }

    /**
     * return void
     */
    public static function installDefaultForms()
    {
        /** @var JInboundModelForms $formsModel */
        $formsModel = JInboundBaseModel::getInstance('Forms', 'JInboundModel');
        if ($formsModel->getItems()) {
            return;
        }

        $db = JFactory::getDbo();

        $fields = $db->setQuery(
            $db->getQuery(true)
                ->select('*')
                ->from('#__jinbound_fields')
                ->where('published = 1')
        )
            ->loadObjectList();

        foreach (array('simple', 'detailed') as $form) {
            $data = array(
                'title'      => JText::_('COM_JINBOUND_DEFAULT_FORM_' . strtoupper($form)),
                'published'  => '1',
                'formfields' => array()
            );

            foreach ($fields as $field) {
                if ('simple' == $form && !in_array($field->name, static::$requiredFields)) {
                    continue;
                }
                $data['formfields'][] = $field->id;
            }

            /** @var JInboundModelForm $formModel */
            $formModel = JInboundBaseModel::getInstance('Form', 'JInboundModel');
            $formModel->save($data);
        }
    }

    /**
     * @return void
     */
    public static function installDefaultFields()
    {
        $db = JFactory::getDbo();

        $existing = static::getAllFields();
        $defaults = array(
            'first_name'   => array(),
            'last_name'    => array(),
            'email'        => array('type' => 'email'),
            'website'      => array('type' => 'url'),
            'company_name' => array(),
            'phone_number' => array('type' => 'tel'),
            'address'      => array('type' => 'textarea'),
            'suburb'       => array(),
            'state'        => array(),
            'country'      => array(),
            'postcode'     => array()
        );

        foreach ($defaults as $fieldname => $extra) {
            $exists = false;
            foreach ($existing as $field) {
                if ($field->name === $fieldname) {
                    $exists = true;
                    if (!$field->published) {
                        $db->setQuery(
                            $db->getQuery(true)
                                ->update('#__jinbound_fields')
                                ->set('published = 1')
                                ->where('id = ' . intval($field->id))
                        )
                            ->execute();
                    }
                    break;
                }
            }
            if ($exists) {
                continue;
            }

            $data = array_merge(array(
                'title'       => JText::_('COM_JINBOUND_PAGE_FIELD_' . strtoupper($fieldname)),
                'name'        => $fieldname,
                'type'        => 'text',
                'description' => '',
                'published'   => 1,
                'params'      => array(
                    'attrs'     => array(
                        'key'   => array(),
                        'value' => array()
                    ),
                    'opts'      => array(
                        'key'   => array(),
                        'value' => array()
                    ),
                    'required'  => (int)in_array($fieldname, static::$requiredFields),
                    'classname' => 'input-block-level'
                )
            ), $extra);
            if ('email' == $fieldname) {
                $data['params']['attrs']['key'][]   = 'validate';
                $data['params']['attrs']['value'][] = 'email';
            }

            /** @var JInboundModelField $fieldModel */
            $fieldModel = JInboundBaseModel::getInstance('Field', 'JInboundModel');
            $fieldModel->save($data);
        }
    }

    /**
     * @return object[]
     */
    public static function getAllFields()
    {
        $db = JFactory::getDbo();

        $fields = $db->setQuery(
            $db->getQuery(true)
                ->select('*')
                ->from('#__jinbound_fields')
        )
            ->loadObjectList();

        return $fields;
    }
}
