<?php
/**
 * @package             JInbound
 * @subpackage          com_jinbound
 **********************************************
 * JInbound
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

JLoader::register('JInbound', JPATH_ADMINISTRATOR . "/components/com_jinbound/helpers/jinbound.php");
JInbound::registerLibrary('JInboundAssetTable', 'tables/asset');

class JInboundTablePage extends JInboundAssetTable
{
    function __construct(&$db)
    {
        parent::__construct('#__jinbound_pages', 'id', $db);
    }

    function check()
    {
        // Check for valid names.
        if (trim($this->name) == '') {
            $this->setError(JText::_('COM_JINBOUND_WARNING_PROVIDE_VALID_NAME'));
            return false;
        }

        // prevent duplicates of the name
        try {
            $dupes = $this->_db->setQuery($this->_db->getQuery(true)
                ->select('id')
                ->from($this->_tbl)
                ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($this->name))
                ->where($this->_db->quoteName('id') . ' <> ' . intval($this->id))
            )->loadColumn();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        if (!empty($dupes)) {
            $this->setError(JText::_('COM_JINBOUND_WARNING_DUPLICATE_NAMES'));
            return false;
        }

        if (empty($this->alias)) {
            $this->alias = $this->name;
        }
        $this->alias = JApplication::stringURLSafe($this->alias);
        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
        }

        return parent::check();
    }

    public function store($updateNulls = false)
    {
        // Verify that the alias is unique
        $table = JTable::getInstance('Page', 'JInboundTable');
        if ($table->load(array('alias'    => $this->alias,
                               'category' => $this->category
            )) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('COM_JINBOUND_ERROR_UNIQUE_ALIAS'));
            return false;
        }
        // Attempt to store the user data.
        return parent::store($updateNulls);
    }

    /**
     * overload hit for tracking hits per day
     *
     * @param type $pk
     *
     * @return boolean
     */
    public function hit($pk = null)
    {
        $id = (int)$pk;
        if (empty($id)) {
            $id = (int)$this->id;
        }
        $date = JFactory::getDate()->format('Y-m-d');
        try {
            $record = $this->_db->setQuery($this->_db->getQuery(true)
                ->select('day')->select('hits')
                ->from('#__jinbound_landing_pages_hits')
                ->where('day = ' . $this->_db->quote($date))
                ->where('page_id = ' . $this->_db->quote($id))
            )->loadObject();
            if (empty($record)) {
                $query = $this->_db->getQuery(true)
                    ->insert('#__jinbound_landing_pages_hits')
                    ->columns(array('day', 'page_id', 'hits'))
                    ->values($this->_db->quote($date) . ', ' . $id . ', 1');
            } else {
                $query = $this->_db->getQuery(true)
                    ->update('#__jinbound_landing_pages_hits')
                    ->set('hits = hits + 1')
                    ->where('day = ' . $this->_db->quote($date))
                    ->where('page_id = ' . $this->_db->quote($id));
            }
            $this->_db->setQuery($query)->query();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
        return parent::hit($pk);
    }

    /**
     * Redefined asset name, as we support action control
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.page.' . (int)$this->$k;
    }

    /**
     * We provide our global ACL as parent
     *
     * @see JTable::_getAssetParentId()
     */
    protected function _compat_getAssetParentId($table = null, $id = null)
    {
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_jinbound.page');
        return $asset->id;
    }
}