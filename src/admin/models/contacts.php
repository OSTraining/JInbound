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
 * This models supports retrieving lists of contacts.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelContacts extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.contacts';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Contact.published',
                'latest',
                'full_name',
                'Contact.email',
                'Contact.created',
                'Priority.name',
                'Priority.name',
                'Campaign.name',
                'Status.name'
            );
        }

        parent::__construct($config);
    }

    /**
     * @return object[]
     */
    public function getItems()
    {
        $items = parent::getItems();
        if (!empty($items)) {
            foreach ($items as &$item) {
                $item->conversions        = JInboundHelperContact::getContactConversions($item->id);
                $item->campaigns          = JInboundHelperContact::getContactCampaigns($item->id);
                $item->previous_campaigns = JInboundHelperContact::getContactCampaigns($item->id, true);
                $item->statuses           = JInboundHelperContact::getContactStatuses($item->id);
                $item->priorities         = JInboundHelperContact::getContactPriorities($item->id);

                $item->forms = array();
                if (!empty($item->conversions)) {
                    foreach ($item->conversions as $conversion) {
                        $item->forms[$conversion->page_id] = $conversion->page_name;
                    }
                }

                $item->tracks = array();
            }
        }

        return $items;
    }

    /**
     * @return object[]
     */
    public function getCampaignsOptions()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('Campaign.id AS value, Campaign.name as text')
            ->from('#__jinbound_campaigns AS Campaign')
            ->where('Campaign.published = 1')
            ->group('Campaign.id');

        $options = $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_CAMPAIGN'));

        return $options;
    }

    public function getPagesOptions()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('Page.id AS value, Page.name as text')
            ->from('#__jinbound_pages AS Page')
            ->where('Page.published = 1')
            ->group('Page.id');
        return $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_PAGE'));
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);
        // load the filter values
        $filters = (array)$this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
        $this->setState('filter', $filters);

        $app    = JFactory::getApplication();
        $format = $app->input->get('format', '', 'cmd');
        $root   = ('json' == $format ? 'json.' : 'filter.');

        foreach (array('start', 'end', 'campaign', 'page', 'priority', 'status') as $var) {
            $value = array_key_exists($var, $filters)
                ? $filters[$var]
                : $this->getUserStateFromRequest($this->context . '.' . $root . $var, 'filter_' . $var, '', 'string');
            $this->setState('filter.' . $var, $value);
        }
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param    string $id A prefix for the store id.
     *
     * @return    string        A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.start'));
        $id .= ':' . serialize($this->getState('filter.end'));
        $id .= ':' . serialize($this->getState('filter.campaign'));
        $id .= ':' . serialize($this->getState('filter.page'));
        $id .= ':' . serialize($this->getState('filter.priority'));
        $id .= ':' . serialize($this->getState('filter.status'));

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        foreach (array('start', 'end', 'campaign', 'page', 'priority', 'status') as $filter) {
            $$filter = $this->getState("filter.$filter");
            if (is_object($$filter)) {
                $$filter = '';
            }
        }
        if (is_array($campaign)) {
            JArrayHelper::toInteger($campaign);
        }

        $db           = $this->getDbo();
        $join         = $db->getQuery(true);
        $listOrdering = $this->getState('list.ordering', 'Contact.created');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));

        // start preparing a subquery that will be joined to the main query
        // that determines the latest conversion by a contact
        $on = array('c1.contact_id = c2.contact_id', 'c1.created < c2.created');
        // add in page filter
        if (!empty($page)) {
            $on[] = 'c2.page_id = ' . (int)$page;
        }
        if (!empty($campaign)) {
            if (is_array($campaign)) {
                $on[] = 'c2.page_id IN ((SELECT id FROM #__jinbound_pages WHERE campaign IN(' . implode(',',
                        $campaign) . ')))';
            } else {
                $on[] = 'c2.page_id IN ((SELECT id FROM #__jinbound_pages WHERE campaign = ' . ((int)$campaign) . '))';
            }
        }
        // create the join for latest
        $join
            ->select('c1.*')
            ->from('#__jinbound_conversions AS c1')
            ->leftJoin('#__jinbound_conversions AS c2 ON ' . implode(' AND ', $on))
            ->where('c2.contact_id IS NULL');

        // select columns
        $query = $db->getQuery(true)
            ->select('Contact.*')
            ->select('CONCAT_WS(' . $db->quote(' ') . ', Contact.first_name, Contact.last_name) AS full_name')
            ->from('#__jinbound_contacts AS Contact')
            // get the latest form
            ->select('Latest.created AS latest')
            ->select('Latest.id AS latest_conversion_id')
            ->select('Latest.page_id AS latest_conversion_page_id')
            ->select('LatestPage.name AS latest_conversion_page_name')
            ->select('LatestForm.title AS latest_conversion_page_formname')
            ->leftJoin('(' . $join . ') AS Latest ON (Latest.contact_id = Contact.id)')
            ->leftJoin('#__jinbound_pages AS LatestPage ON LatestPage.id = Latest.page_id')
            ->leftJoin('#__jinbound_forms AS LatestForm ON LatestPage.formid = LatestForm.id')
            //->where('LatestPage.id IS NOT NULL') // causes leads made in admin to disappear
            ->group('Contact.id');

        // filter pages
        if (!empty($page)) {
            $query->where('LatestPage.id = ' . (int)$page);
        }

        // filter campaigns
        if (!empty($campaign)) {
            if (is_array($campaign)) {
                $query->leftJoin('#__jinbound_contacts_campaigns AS ContactCampaign ON ContactCampaign.contact_id = Contact.id AND ContactCampaign.campaign_id IN(' . implode(',',
                        $campaign) . ')');
            } else {
                $query->leftJoin('#__jinbound_contacts_campaigns AS ContactCampaign ON ContactCampaign.contact_id = Contact.id AND ContactCampaign.campaign_id = ' . (int)$campaign);
            }
            $query->where('ContactCampaign.campaign_id IS NOT NULL');
        } else {
            if ('Campaign.name' === $listOrdering) {
                $query->leftJoin('#__jinbound_contacts_campaigns AS ContactCampaign ON ContactCampaign.contact_id = Contact.id');
                $query->leftJoin('#__jinbound_campaigns AS Campaign ON ContactCampaign.campaign_id = Campaign.id');
            }
        }

        // filter by status
        if (!empty($status)) {
            $query->leftJoin('#__jinbound_contacts_statuses AS ContactStatus ON ContactStatus.contact_id = Contact.id AND ContactStatus.status_id = ' . (int)$status);
            $query->where('ContactStatus.status_id IS NOT NULL');
        } else {
            if ('Status.name' === $listOrdering) {
                $query->leftJoin('( SELECT s1.* FROM #__jinbound_contacts_statuses AS s1 LEFT JOIN #__jinbound_contacts_statuses AS s2 ON s1.contact_id = s2.contact_id AND s1.created < s2.created WHERE s2.contact_id IS NULL) AS ContactStatus ON ContactStatus.contact_id = Contact.id');
                $query->leftJoin('#__jinbound_lead_statuses AS Status ON ContactStatus.status_id = Status.id');
            }
        }

        // filter by priority
        if (!empty($priority)) {
            $query->leftJoin('#__jinbound_contacts_priorities AS ContactPriority ON ContactPriority.contact_id = Contact.id AND ContactPriority.priority_id = ' . (int)$priority);
            $query->where('ContactPriority.priority_id IS NOT NULL');
        } else {
            if ('Priority.name' === $listOrdering) {
                $query->leftJoin('( SELECT p1.* FROM #__jinbound_contacts_priorities AS p1 LEFT JOIN #__jinbound_contacts_priorities AS p2 ON p1.contact_id = p2.contact_id AND p1.created < p2.created WHERE p2.contact_id IS NULL) AS ContactPriority ON ContactPriority.contact_id = Contact.id');
                $query->leftJoin('#__jinbound_priorities AS Priority ON ContactPriority.priority_id = Priority.id');
            }
        }

        // add author to query
        //$this->appendAuthorToQuery($query, 'Contact');
        // filter query
        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Contact', 'id', array(
            'first_name',
            'last_name'
        ));
        $this->filterPublished($query, $this->getState('filter.published'), 'Contact');

        if (!empty($start)) {
            try {
                $startdate = new DateTime($start);
            } catch (Exception $e) {
                $startdate = false;
            }
            if ($startdate) {
                $query->where('Contact.created > ' . $db->quote($startdate->format('Y-m-d h:i:s')));
            }
        }

        if (!empty($end)) {
            try {
                $enddate = new DateTime($end);
            } catch (Exception $e) {
                $enddate = false;
            }
            if ($enddate) {
                $query->where('Contact.created < ' . $db->quote($enddate->format('Y-m-d h:i:s')));
            }
        }

        // Add the list ordering clause.
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
