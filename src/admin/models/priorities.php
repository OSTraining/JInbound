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
 * This models supports retrieving lists of locations.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelPriorities extends JInboundListModel
{
    protected $context  = 'com_jinbound.priorities';

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
                'Priority.name'
            ,
                'Priority.status'
            ,
                'Priority.ordering'
            ,
                'Priority.description'
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
            ->select('Priority.*')
            ->from('#__jinbound_priorities AS Priority');
        // add author to query
        $this->appendAuthorToQuery($query, 'Priority');
        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Priority', 'id',
            array('name', 'description'));
        $this->filterPublished($query, $this->getState('filter.published'), 'Priority');

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Priority.name');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }


}
