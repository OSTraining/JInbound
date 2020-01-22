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

class JInboundAdminModel extends JModelAdmin
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound';

    public $option = 'com_jinbound';

    /**
     * @var string[]
     */
    protected $registryColumns = null;

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return bool|JForm
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_jinbound.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form) {
            return $form;
        }

        return false;
    }

    public function getTable($type = null, $prefix = 'JInboundTable', $config = array())
    {
        if (empty($type)) {
            $type = $this->name;
        }
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return (string)$this->context;
    }

    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache($this->option);
        parent::cleanCache('_system');
        parent::cleanCache($group, $client_id);
    }

    /**
     * @return bool|JObject
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState(
            'com_jinbound.edit.' . strtolower($this->name) . '.data',
            array()
        );
        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * @param null $id
     *
     * @return bool|JObject
     */
    public function getItem($id = null)
    {
        $item = parent::getItem($id);
        // if we have no columns to alter, we're done
        if (!is_array($this->registryColumns) || empty($this->registryColumns)) {
            return $item;
        }

        foreach ($this->registryColumns as $col) {
            if (!property_exists($item, $col)) {
                continue;
            }

            $registry = new Registry();
            $registry->loadString($item->$col);
            $item->$col = $registry;
        }

        return $item;
    }
}
