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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

class JInboundTableConversion extends JInboundTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_conversions', 'id', $db);
    }

    /**
     * @param mixed $keys
     * @param bool  $reset
     *
     * @return bool
     */
    public function load($keys = null, $reset = true)
    {
        // load
        $load = parent::load($keys, $reset);
        // convert formdata to an object
        $registry = new Registry();
        if (is_string($this->formdata)) {
            $registry->loadString($this->formdata);
        } else {
            if (is_array($this->formdata)) {
                $registry->loadArray($this->formdata);
            } else {
                if (is_object($this->formdata)) {
                    $registry->loadObject($this->formdata);
                }
            }
        }
        $this->formdata = $registry;

        return $load;
    }

    /**
     * @param array|object $array
     * @param string       $ignore
     *
     * @return bool
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['formdata'])) {
            $registry = new Registry();
            if (is_array($array['formdata'])) {
                $registry->loadArray($array['formdata']);
            } else {
                if (is_string($array['formdata'])) {
                    $registry->loadString($array['formdata']);
                }
            }
            $array['formdata'] = (string)$registry;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Redefined asset name, as we support action control
     *
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.conversion.' . (int)$this->$k;
    }

    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        /** @var JTableAsset $asset */
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_jinbound.conversions');
        return $asset->id;
    }
}
