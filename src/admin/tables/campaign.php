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

class JInboundTableCampaign extends JInboundTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_campaigns', 'id', $db);
    }

    public function load($keys = null, $reset = true)
    {
        $load = parent::load($keys, $reset);

        if (is_string($this->params)) {
            $this->params = json_decode($this->params);
        }

        return $load;
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params'])) {
            $registry = new JRegistry;
            if (is_array($array['params'])) {
                $registry->loadArray($array['params']);
            } else {
                if (is_string($array['params'])) {
                    $registry->loadString($array['params']);
                } else {
                    if (is_object($array['params'])) {
                        $registry->loadArray((array)$array['params']);
                    }
                }
            }
            $array['params'] = (string)$registry;
        }
        return parent::bind($array, $ignore);
    }

    /**
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.campaign.' . (int)$this->$k;
    }

    /**
     * @param JTable $table
     * @param null   $id
     *
     * @return int
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        /** @var JTableAsset $asset */
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_jinbound.campaigns');
        return $asset->id;
    }
}
