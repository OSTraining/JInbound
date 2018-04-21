<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
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
