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

defined('JPATH_PLATFORM') or die;

/**
 * This models supports retrieving a lead status.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelStatus extends JInboundAdminModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.status';

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option . '.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form) {
            if (!JFactory::getUser()->authorise('core.edit.state', 'com_jinbound.status')) {
                $form->setFieldAttribute('published', 'readonly', 'true');
            }

            return $form;
        }

        return false;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws Exception
     */
    public function setDefault($id = 0)
    {
        $user = JFactory::getUser();
        $db   = $this->getDbo();

        if (!$user->authorise('core.edit.state', 'com_jinbound')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
        }

        $status = JTable::getInstance('Status', 'JInboundTable');
        if (!$status->load((int)$id)) {
            throw new Exception(JText::_('COM_JINBOUND_ERROR_STATUS_NOT_FOUND'));
        }

        // Reset the home fields for the client_id.
        $db->setQuery('UPDATE #__jinbound_lead_statuses SET `default` = 0 WHERE 1');

        if (!$db->execute()) {
            throw new Exception($db->getErrorMsg());
        }

        // Set the new home style.
        $db->setQuery('UPDATE #__jinbound_lead_statuses SET `default` = 1 WHERE id = ' . (int)$id);

        if (!$db->execute()) {
            throw new Exception($db->getErrorMsg());
        }

        $this->cleanCache();

        return true;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws Exception
     */
    public function setFinal($id = 0)
    {
        $user = JFactory::getUser();
        $db   = $this->getDbo();

        if (!$user->authorise('core.edit.state', 'com_jinbound')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
        }

        $status = JTable::getInstance('Status', 'JInboundTable');
        if (!$status->load((int)$id)) {
            throw new Exception(JText::_('COM_JINBOUND_ERROR_STATUS_NOT_FOUND'));
        }

        // Reset the home fields for the client_id.
        $db->setQuery('UPDATE #__jinbound_lead_statuses SET final = 0 WHERE 1');

        if (!$db->execute()) {
            throw new Exception($db->getErrorMsg());
        }

        // Set the new home style.
        $db->setQuery('UPDATE #__jinbound_lead_statuses SET final = 1 WHERE id = ' . (int)$id);

        if (!$db->execute()) {
            throw new Exception($db->getErrorMsg());
        }

        $this->cleanCache();

        return true;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws Exception
     */
    public function setActive($id = 0)
    {
        $user = JFactory::getUser();
        $db   = $this->getDbo();

        if (!$user->authorise('core.edit.state', 'com_jinbound')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
        }

        $status = JTable::getInstance('Status', 'JInboundTable');
        if (!$status->load((int)$id)) {
            throw new Exception(JText::_('COM_JINBOUND_ERROR_STATUS_NOT_FOUND'));
        }

        // Set the active status
        $db->setQuery('UPDATE #__jinbound_lead_statuses SET active = 1 WHERE id = ' . (int)$id);

        if (!$db->execute()) {
            throw new Exception($db->getErrorMsg());
        }

        $this->cleanCache();

        return true;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws Exception
     */
    public function unsetActive($id = 0)
    {
        $user = JFactory::getUser();
        $db   = $this->getDbo();

        if (!$user->authorise('core.edit.state', 'com_jinbound')) {
            throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
        }

        $status = JTable::getInstance('Status', 'JInboundTable');
        if (!$status->load((int)$id)) {
            throw new Exception(JText::_('COM_JINBOUND_ERROR_STATUS_NOT_FOUND'));
        }

        // Set the active status
        $db->setQuery('UPDATE #__jinbound_lead_statuses SET active = 0 WHERE id = ' . (int)$id);

        if (!$db->execute()) {
            throw new Exception($db->getErrorMsg());
        }

        $this->cleanCache();

        return true;
    }
}
