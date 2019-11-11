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

defined('JPATH_PLATFORM') or die;

class JInboundTableContact extends JInboundTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_contacts', 'id', $db);
    }

    public function delete($pk = null)
    {
        if ($result = parent::delete($pk)) {
            $tables = array(
                '#__jinbound_contacts_campaigns'  => 'contact_id',
                '#__jinbound_conversions'         => 'contact_id',
                '#__jinbound_contacts_statuses'   => 'contact_id',
                '#__jinbound_contacts_priorities' => 'contact_id',
                '#__jinbound_emails_records'      => 'lead_id',
                '#__jinbound_notes'               => 'lead_id',
                '#__jinbound_subscriptions'       => 'contact_id'
            );

            $db = $this->getDbo();
            foreach ($tables as $table => $key) {
                $db->setQuery(
                    $db->getQuery(true)
                        ->delete($table)
                        ->where($db->quoteName($key) . ' = ' . $this->id)
                )
                    ->execute();
            }

            return true;
        }

        return false;
    }

    /**
     * Redefined asset name, as we support action control
     *
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.contact.' . (int)$this->$k;
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
        $asset->loadByName('com_jinbound.contacts');
        return $asset->id;
    }
}
