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
                'published',
                'campaign',
                'page',
                'status',
                'priority',
                'start',
                'end'
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
                if ($item->conversions) {
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

    /**
     * @return object[]
     */
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
     * @param string $ordering
     * @param string $direction
     *
     * @return void
     * @throws Exception
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
                serialize($this->getState('filter.start')),
                serialize($this->getState('filter.end')),
                serialize($this->getState('filter.campaign')),
                serialize($this->getState('filter.page')),
                serialize($this->getState('filter.priority')),
                serialize($this->getState('filter.status'))
            )
        );

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $listOrdering = $this->getState('list.ordering', 'Contact.created');
        $listDirn     = $this->getState('list.direction', 'ASC');

        $on = array(
            'c1.contact_id = c2.contact_id',
            'c1.created < c2.created'
        );
        if (!empty($page)) {
            $on[] = 'c2.page_id = ' . (int)$page;
        }

        $campaign = array_filter(array_map('intval', (array)$this->getState('filter.campaign')));
        if ($campaign) {
            $on[] = sprintf(
                'c2.page_id IN (SELECT id FROM #__jinbound_pages WHERE campaign IN (%s))',
                join(',', $campaign)
            );
        }

        $conversionQuery = $db->getQuery(true)
            ->select('c1.*')
            ->from('#__jinbound_conversions AS c1')
            ->leftJoin('#__jinbound_conversions AS c2 ON ' . join(' AND ', $on))
            ->where('c2.contact_id IS NULL');

        $query = $db->getQuery(true)
            ->select(
                array(
                    'Contact.*',
                    sprintf('CONCAT_WS(%s, Contact.first_name, Contact.last_name) AS full_name', $db->quote(' ')),
                    'Latest.created AS latest',
                    'Latest.id AS latest_conversion_id',
                    'Latest.page_id AS latest_conversion_page_id',
                    'LatestPage.name AS latest_conversion_page_name',
                    'LatestForm.title AS latest_conversion_page_formname'
                )
            )
            ->from('#__jinbound_contacts AS Contact')
            ->leftJoin(sprintf('(%s) AS Latest ON (Latest.contact_id = Contact.id)', $conversionQuery))
            ->leftJoin('#__jinbound_pages AS LatestPage ON LatestPage.id = Latest.page_id')
            ->leftJoin('#__jinbound_forms AS LatestForm ON LatestPage.formid = LatestForm.id')
            ->group('Contact.id');

        if ($page = (int)$this->getState('filter.page')) {
            $query->where('LatestPage.id = ' . $page);
        }

        // Campaign Filter
        if ($campaign) {
            $on = array(
                'ContactCampaign.contact_id = Contact.id',
                sprintf('ContactCampaign.campaign_id IN(%s)', join(',', $campaign))
            );

            $query->leftJoin(sprintf('#__jinbound_contacts_campaigns AS ContactCampaign ON %s', join(' AND ', $on)))
                ->where('ContactCampaign.campaign_id IS NOT NULL');

        } elseif ($listOrdering == 'Campaign.name') {
            $query->leftJoin('#__jinbound_contacts_campaigns AS ContactCampaign'
                . ' ON ContactCampaign.contact_id = Contact.id')
                ->leftJoin('#__jinbound_campaigns AS Campaign ON ContactCampaign.campaign_id = Campaign.id');
        }

        // Status filter
        if ($status = (int)$this->getState('filter.status')) {
            $on = array(
                'ContactStatus.contact_id = Contact.id',
                'ContactStatus.status_id = ' . $status
            );
            $query->leftJoin(sprintf('#__jinbound_contacts_statuses AS ContactStatus ON %s', join(' AND ', $on)))
                ->where('ContactStatus.status_id IS NOT NULL');

        } elseif ($listOrdering == 'Status.name') {
            $statusQuery = $db->getQuery(true)
                ->select('s1.*')
                ->from('#__jinbound_contacts_statuses AS s1')
                ->leftJoin(
                    '#__jinbound_contacts_statuses AS s2'
                    . ' ON s1.contact_id = s2.contact_id AND s1.created < s2.created'
                )
                ->where('s2.contact_id IS NULL');

            $query->leftJoin(
                sprintf(
                    '(%s) AS ContactStatus ON ContactStatus.contact_id = Contact.id',
                    $statusQuery
                )
            )
                ->leftJoin('#__jinbound_lead_statuses AS Status ON ContactStatus.status_id = Status.id');
        }

        // Priority Filter
        if ($priority = (int)$this->getState('filter.priority')) {
            $on = array(
                'ContactPriority.contact_id = Contact.id',
                'ContactPriority.priority_id = ' . $priority
            );

            $query->leftJoin(sprintf('#__jinbound_contacts_priorities AS ContactPriority ON %s', join(' AND ', $on)))
                ->where('ContactPriority.priority_id IS NOT NULL');

        } elseif ($listOrdering == 'Priority.name') {
            $on = array(
                'p1.contact_id = p2.contact_id',
                'p1.created < p2.created'
            );

            $priorityQuery = $db->getQuery(true)
                ->select('p1.*')
                ->from('#__jinbound_contacts_priorities AS p1')
                ->leftJoin(sprintf('#__jinbound_contacts_priorities AS p2 ON %s', join(' AND ', $on)))
                ->where('p2.contact_id IS NULL');

            $query->leftJoin(
                sprintf(
                    '(%s) AS ContactPriority ON ContactPriority.contact_id = Contact.id',
                    $priorityQuery
                )
            )
                ->leftJoin('#__jinbound_priorities AS Priority ON ContactPriority.priority_id = Priority.id');
        }

        $this->filterSearchQuery(
            $query,
            $this->getState('filter.search'),
            'Contact',
            'id',
            array(
                'first_name',
                'last_name'
            )
        );
        $this->filterPublished($query, $this->getState('filter.published'), 'Contact');

        if ($start = $this->getState('filter.start')) {
            try {
                $startdate = new DateTime($start);

                if ($startdate) {
                    $query->where('Contact.created > ' . $db->quote($startdate->format('Y-m-d h:i:s')));
                }

            } catch (Exception $e) {
                // ignore badly formed date
            }
        }

        if ($end = $this->getState('filter.end')) {
            try {
                $enddate = new DateTime($end);

                if ($enddate) {
                    $query->where('Contact.created < ' . $db->quote($enddate->format('Y-m-d h:i:s')));
                }

            } catch (Exception $e) {
                // Ignore badly formed date
            }
        }

        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
