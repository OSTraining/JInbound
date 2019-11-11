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

class JInboundTableEmail extends JInboundTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_emails', 'id', $db);
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
        // convert params to an object
        $registry = new Registry();
        if (is_string($this->params)) {
            $registry->loadString($this->params);
        } else {
            if (is_array($this->params)) {
                $registry->loadArray($this->params);
            } else {
                if (is_object($this->params)) {
                    $registry->loadObject($this->params);
                }
            }
        }
        $this->params = $registry;

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
                }
            }
            $array['params'] = (string)$registry;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * @param bool $updateNulls
     *
     * @return bool
     * @throws Exception
     */
    public function store($updateNulls = false)
    {
        $app = JFactory::getApplication();
        // we have to determine if this email is new or not
        $isNew = empty($this->id);
        // if it is new, we can simply save it and insert a new record into the versions table
        if ($isNew) {
            // save this email first
            $store = parent::store($updateNulls);
            $this->_db->setQuery(
                'INSERT INTO #__jinbound_emails_versions'
                . ' (email_id, subject, htmlbody, plainbody)'
                . ' SELECT id, subject, htmlbody, plainbody FROM #__jinbound_emails WHERE id = ' . $this->id
            );

            try {
                $this->_db->execute();

            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');

                return $store;
            }

            return $store;

        } else {
            // if any of the texts in this version of the email differ
            // we have to insert a new version of the email

            // pull the original from the database
            $this->_db->setQuery(
                $this->_db->getQuery(true)
                    ->select('subject, htmlbody, plainbody')
                    ->from('#__jinbound_emails')
                    ->where('id = ' . $this->id)
            );

            try {
                $original = $this->_db->loadObject();

            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');

                return parent::store($updateNulls);
            }

            $store = parent::store($updateNulls);

            // compare the original to the new
            // if the old matches, just store & bail
            if ($original->subject == $this->subject
                && $original->htmlbody == $this->htmlbody
                && $original->plainbody == $this->plainbody
            ) {
                return $store;
            }

            // there is a difference - insert a new version record before store
            $this->_db->setQuery(
                'INSERT INTO #__jinbound_emails_versions'
                . ' (email_id, subject, htmlbody, plainbody)'
                . ' SELECT id, subject, htmlbody, plainbody FROM #__jinbound_emails WHERE id = ' . $this->id
            );

            try {
                $this->_db->execute();
            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
                return $store;
            }

            return $store;
        }
    }

    /**
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.email.' . (int)$this->$k;
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
        $asset->loadByName('com_jinbound.emails');
        return $asset->id;
    }
}
