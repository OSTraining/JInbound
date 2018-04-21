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
class JInboundModelCampaigns extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context  = 'com_jinbound.campaigns';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Campaign.name',
                'Campaign.published',
                'Campaign.created'
            );
        }

        parent::__construct($config);
    }

    public function getStatusOptions()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('Status.name AS text, Status.id AS value')
            ->from('#__jinbound_lead_statuses AS Status')
            ->where('Status.published = 1')
            ->order('Status.name ASC');
        return $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_STATUS'));
    }

    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();

        // main query
        $query = $db->getQuery(true)
            // Select the required fields from the table.
            ->select('Campaign.*')
            ->from('#__jinbound_campaigns AS Campaign');

        $this->appendAuthorToQuery($query, 'Campaign');
        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Campaign', 'id', array('name'));
        $this->filterPublished($query, $this->getState('filter.published'), 'Campaign');

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Campaign.name');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
