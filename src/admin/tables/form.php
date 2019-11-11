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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class JInboundTableForm extends JInboundTable
{
    protected $formFieldsCache = null;

    public function __construct(&$db)
    {
        parent::__construct('#__jinbound_forms', 'id', $db);
    }

    /**
     * @param bool $updateNulls
     *
     * @return bool
     * @throws Exception
     */
    public function store($updateNulls = false)
    {
        if (property_exists($this, 'formfields')) {
            $this->formFieldsCache = $this->formfields;
            unset($this->formfields);
        }

        $store = parent::store($updateNulls);

        // handle xref but only if we have an id already ;)
        // if store was successful, there should now be an assigned id
        if ($store) {
            if (empty($this->formFieldsCache)) {
                /*
                 * fetch these from the request
                 * we currently have no need to get this data from anywhere else
                 * but eventually we may need to
                 * unfortunately, we have to extract this data from JForm, so we have to fetch via JForm array
                 */
                $jform = JFactory::getApplication()->input->get('jform', array(), null);
                // if for some dumb reason jform isn't an array we should account for this
                // as well, this variable may not be set
                if (is_array($jform) && array_key_exists('formfields', $jform)) {
                    // we may receive an array from the request
                    // this is doubtful, but let's go ahead and account for it anyways
                    $this->formFieldsCache = $jform['formfields'];

                } else {
                    return $store;
                }
            }

            if (is_array($this->formFieldsCache)) {
                $this->formFieldsCache = implode('|', $this->formFieldsCache);
            } else {
                // account for non-string variables by making it empty,
                // but only if the passed variable cannot be converted to a string
                if (!is_string($this->formFieldsCache)) {
                    try {
                        $this->formFieldsCache = (string)$this->formFieldsCache;

                    } catch (Exception $e) {
                        // ouch, failed converting to string - just make it blank
                        $this->formFieldsCache = '';
                    }
                }
            }

            // now we need to convert our string back into an array and inject the records
            $formfields = explode('|', $this->formFieldsCache);
            if (!empty($formfields)) {
                // go ahead and purge the existing records
                $this->_db->setQuery(
                    $this->_db->getQuery(true)
                        ->delete('#__jinbound_form_fields')
                        ->where('form_id=' . intval($this->id))
                )
                    ->execute();

                // force our fields to be integers, unique, and only values
                ArrayHelper::toInteger($formfields);
                $formfields = array_unique(array_values($formfields));
                $insert     = $this->_db->getQuery(true)
                    ->insert('#__jinbound_form_fields')
                    ->columns(array('form_id', 'field_id', 'ordering'));
                $query      = false;
                // walk the array and convert to INSERT snippets
                // we're using for() instead of foreach() here so we have proper ordering :)
                for ($i = 0; $i < count($formfields); $i++) {
                    $query = true;
                    $insert->values(intval($this->id) . ", " . $formfields[$i] . ", $i");
                }
                if ($query) {
                    // inject the new records
                    $this->_db->setQuery($insert)->execute();
                }
            }
        }

        return $store;
    }

    public function bind($array, $ignore = '')
    {
        // make sure fields get set
        if (array_key_exists('formfields', $array)) {
            $this->formFieldsCache = $array['formfields'];
            unset($array['formfields']);
        }
        return parent::bind($array, $ignore);
    }

    /**
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jinbound.form.' . (int)$this->$k;
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
        $asset->loadByName('com_jinbound.forms');

        return $asset->id;
    }
}
