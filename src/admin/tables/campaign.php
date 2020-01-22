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

use Joomla\Registry\Registry;

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
            $registry = new Registry();
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
