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

defined('JPATH_PLATFORM') or die;

/**
 * This models supports retrieving lists of statuses.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelStatuses extends JInboundListModel
{
    protected $context  = 'com_jinbound.statuses';

    /**
     * Constructor.
     *
     * @param       array   An optional associative array of configuration settings.
     *
     * @see         JController
     */
    function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Status.name'
            ,
                'Status.published'
            ,
                'Status.default'
            ,
                'Status.active'
            ,
                'Status.final'
            ,
                'Status.ordering'
            ,
                'Status.description'
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        // main query
        $query = $db->getQuery(true)
            // Select the required fields from the table.
            ->select('Status.*')
            ->from('#__jinbound_lead_statuses AS Status');
        // add author to query
        $this->appendAuthorToQuery($query, 'Status');
        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Status', 'id',
            array('name', 'description'));
        $this->filterPublished($query, $this->getState('filter.published'), 'Status');

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Status.name');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
