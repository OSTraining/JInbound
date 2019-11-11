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
