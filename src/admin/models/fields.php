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

/**
 * This model supports retrieving lists of fields.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelFields extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.fields';

    /**
     * Constructor.
     *
     * @param       array   An optional associative array of configuration settings.
     *
     * @see         JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Field.id',
                'Field.title',
                'Field.type',
                'Field.formtype',
                'Field.created_by',
                'Field.published'
            );
        }

        parent::__construct($config);
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        // force only published fields on frontend
        if (!JFactory::getApplication()->isClient('administrator')) {
            $this->setState('filter.published', 1);
        }

        $this->setState('filter.access', true);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getStoreId($id = '')
    {
        $id = join(
            ':',
            array(
                $id,
                'com_jinbound',
                $this->getState('filter.published'),
                $this->getState('filter.access'),
                $this->getState('filter.parentId')
            )
        );

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select($this->getState('list.select', 'Field.*'))
            ->from('#__jinbound_fields AS Field');

        $this->appendAuthorToQuery($query, 'Field');

        $published = $this->getState('filter.published');
        if ($published == '') {
            $query->where('(Field.published = 0 OR Field.published = 1)');
        } elseif (is_numeric($published)) {
            $query->where('Field.published = ' . (int)$published);
        }

        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Field', 'id', array('title', 'name'));

        $type = $this->getState('filter.formtype');
        if (is_numeric($type)) {
            $query->where('Field.formtype = ' . (int)$type);
        }

        $listOrdering = $this->getState('list.ordering', 'Field.title');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        $query->group('Field.id');

        return $query;
    }
}
