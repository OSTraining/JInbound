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

defined('JPATH_PLATFORM') or die;

class JInboundTablePage extends JInboundTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_pages', 'id', $db);
    }

    public function check()
    {
        if (trim($this->name) == '') {
            $this->setError(JText::_('COM_JINBOUND_WARNING_PROVIDE_VALID_NAME'));

            return false;
        }

        // prevent duplicates of the name
        try {
            $dupes = $this->_db->setQuery(
                $this->_db->getQuery(true)
                    ->select('id')
                    ->from($this->_tbl)
                    ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($this->name))
                    ->where($this->_db->quoteName('id') . ' <> ' . intval($this->id))
            )
                ->loadColumn();

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
        $this->alias = JApplicationHelper::stringURLSafe($this->alias);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
        }

        return parent::check();
    }

    public function store($updateNulls = false)
    {
        // Verify that the alias is unique
        $table = JTable::getInstance('Page', 'JInboundTable');
        if ($table->load(array('alias' => $this->alias, 'category' => $this->category))
            && ($table->id != $this->id || $this->id == 0)
        ) {
            $this->setError(JText::_('COM_JINBOUND_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        if (!empty($this->formid)) {
            $db = $this->getDbo();

            $this->formname = $db->setQuery(
                $db->getQuery(true)
                    ->select('title')
                    ->from('#__jinbound_forms')
                    ->where('id = ' . (int)$this->formid)
            )
                ->loadResult();
        }

        return parent::store($updateNulls);
    }

    /**
     * overload hit for tracking hits per day
     *
     * @param int $pk
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
            $record = $this->_db->setQuery(
                $this->_db->getQuery(true)
                    ->select('day')->select('hits')
                    ->from('#__jinbound_landing_pages_hits')
                    ->where('day = ' . $this->_db->quote($date))
                    ->where('page_id = ' . $this->_db->quote($id))
            )
                ->loadObject();

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
            $this->_db->setQuery($query)->execute();

        } catch (Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return parent::hit($pk);
    }

    /**
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.page.' . (int)$this->$k;
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
        $asset->loadByName('com_jinbound.pages');
        return $asset->id;
    }
}
