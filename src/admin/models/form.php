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

/**
 * This models supports retrieving lists of forms.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelForm extends JInboundAdminModel
{
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.' . $this->name, $this->name,
            array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Method to override the parent getItem method to add the field xref data
     */
    public function getItem($id = null)
    {
        // load the item
        $item = parent::getItem($id);
        // we may not have a form
        if ($item) {
            // initialize the database object
            $db = JFactory::getDbo();
            // load the data from the xref table from the database
            $db->setQuery('SELECT CAST(GROUP_CONCAT(field_id ORDER BY ordering ASC SEPARATOR "|") AS CHAR) AS fields FROM #__jinbound_form_fields WHERE form_id = ' . intval($item->id) . ' GROUP BY form_id');
            $fields = $db->loadResult();
            if (empty($fields)) {
                $fields = '';
            }
            // append fields to the item
            $item->formfields = $fields;
        }
        // return the item
        return $item;
    }

    public function setDefault($id = 0)
    {
        // Initialise variables.
        $user = JFactory::getUser();
        $db   = $this->getDbo();

        // Access checks.
        if (!$user->authorise('core.edit.state', 'com_jinbound')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
        }

        $table = JTable::getInstance('Form', 'JInboundTable');
        if (!$table->load((int)$id)) {
            throw new Exception(JText::_('COM_JINBOUND_ERROR_FORM_NOT_FOUND'));
        }

        // Reset the default field
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__jinbound_forms'))
                ->set($db->quoteName('default') . ' = 0')
                ->where($db->quoteName('type') . ' = ' . $db->quote($table->type))
                ->where($db->quoteName('default') . ' = 1')
        );

        try {
            if (!$db->query()) {
                throw new Exception($db->getErrorMsg());
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Set the new default form
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__jinbound_forms'))
                ->set($db->quoteName('default') . ' = 1')
                ->where($db->quoteName('id') . ' = ' . (int)$id)
        );

        try {
            if (!$db->query()) {
                throw new Exception($db->getErrorMsg());
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Clean the cache.
        $this->cleanCache();

        return true;
    }

    public function unsetDefault($id = 0)
    {
        // Initialise variables.
        $user = JFactory::getUser();
        $db   = $this->getDbo();

        // Access checks.
        if (!$user->authorise('core.edit.state', 'com_jinbound')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
        }

        $table = JTable::getInstance('Form', 'JInboundTable');
        if (!$table->load((int)$id)) {
            throw new Exception(JText::_('COM_JINBOUND_ERROR_FORM_NOT_FOUND'));
        }

        // Set the new default form
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__jinbound_forms'))
                ->set($db->quoteName('default') . ' = 0')
                ->where($db->quoteName('id') . ' = ' . (int)$id)
        );

        try {
            if (!$db->query()) {
                throw new Exception($db->getErrorMsg());
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // Clean the cache.
        $this->cleanCache();

        return true;
    }
}
