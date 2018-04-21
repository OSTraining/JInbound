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

class JInboundModelPriorities extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.priorities';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Priority.name',
                'Priority.status',
                'Priority.ordering',
                'Priority.description'
            );
        }

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('Priority.*')
            ->from('#__jinbound_priorities AS Priority');

        $this->appendAuthorToQuery($query, 'Priority');

        $this->filterSearchQuery(
            $query,
            $this->getState('filter.search'),
            'Priority',
            'id',
            array('name', 'description')
        );

        $this->filterPublished($query, $this->getState('filter.published'), 'Priority');

        $listOrdering = $this->getState('list.ordering', 'Priority.name');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
