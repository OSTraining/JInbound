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

defined('JPATH_PLATFORM') or die();

class JInboundModelTracks extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.tracks';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Track.id',
                'Track.cookie',
                'Track.user_agent',
                'Track.created',
                'Track.ip',
                'Track.session_id',
                'Track.type',
                'Track.url'
            );
        }

        parent::__construct($config);
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @return void
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $filters = $this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
        $this->setState('filter', $filters);

        $app = JFactory::getApplication();

        $format = $app->input->get('format', '', 'cmd');
        $end    = ('json' == $format ? '.json' : '');

        foreach (array('start', 'end') as $var) {
            $value = array_key_exists($var, $filters)
                ? $filters[$var]
                : $this->getUserStateFromRequest(
                    $this->context . '.filter.' . $var . $end,
                    'filter_' . $var,
                    '',
                    'string'
                );
            $this->setState('filter.' . $var, $value);
        }
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . serialize($this->getState('filter.start'));
        $id .= ':' . serialize($this->getState('filter.end'));

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('Track.*')
            ->from('#__jinbound_tracks AS Track');

        $this->filterSearchQuery(
            $query,
            $this->getState('filter.search'),
            'Track',
            'id',
            array('id', 'cookie', 'user_agent', 'url', 'ip')
        );

        if ($value = $this->getState('filter.start')) {
            try {
                $date = new DateTime($value);
                if ($date) {
                    $query->where('Track.created > ' . $db->quote($date->format('Y-m-d h:i:s')));
                }

            } catch (Exception $e) {
            }
        }

        if ($value = $this->getState('filter.end')) {
            try {
                $date = new DateTime($value);
                if ($date) {
                    $query->where('Track.created < ' . $db->quote($date->format('Y-m-d h:i:s')));
                }

            } catch (Exception $e) {
            }
        }

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Track.created');
        $listDirn     = $db->escape($this->getState('list.direction', 'DESC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
