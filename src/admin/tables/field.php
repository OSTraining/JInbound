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

class JInboundTableField extends JInboundTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_fields', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (array_key_exists('params', $array) && is_array($array['params'])) {
            /*
             * we have to do some extra crap here, to avoid a lot of extra coding in other parts of the application
             * due to the way the UI is coded (can't be helped without massive amounts of js)
             * we get a pretty funky array for attrs and opts
             * to "fix" this, we convert those here into simple key => value pairs
             * instead of having 2 arrays, one for keys & one for values
             * now, we could very well use php functions to accomplish this, but we want to ensure
             * that the data is preserved correctly for later use
             */
            foreach (array('attrs', 'opts') as $param) {
                if (empty($array['params'][$param])
                    || !is_array($array['params'][$param])) {
                    continue;
                }

                $realValues = array();

                if (array_key_exists('key', $array['params'][$param])
                    && !empty($array['params'][$param]['key'])
                    && array_key_exists('value', $array['params'][$param])
                ) {
                    foreach ($array['params'][$param]['key'] as $i => $key) {
                        // make sure we have a corresponding value
                        if (!array_key_exists($i, $array['params'][$param]['value'])) {
                            continue;
                        }
                        $value = $array['params'][$param]['value'][$i];

                        if ($key === '') {
                            continue;
                        }

                        $realValues[$key] = $value;
                    }
                }

                $array['params'][$param] = $realValues;
            }

            $array['params'] = json_encode($array['params']);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.field.' . (int)$this->$k;
    }

    /**
     * @return string
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * @param JTable|null $table
     * @param null        $id
     *
     * @return int
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        /** @var JTableAsset $asset */
        $asset = JTable::getInstance('Asset');
        $asset->loadByName('com_jinbound.fields');

        return $asset->id;
    }
}
