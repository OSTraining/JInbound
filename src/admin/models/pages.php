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
 * This models supports retrieving lists of pages.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelPages extends JInboundListModel
{
    protected $context  = 'com_jinbound.pages';

    private $_registryColumns = array('formbuilder');

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
                'Page.name'
            ,
                'Page.published'
            ,
                'Page.category'
            ,
                'Page.hits'
            ,
                'submissions'
            ,
                'conversions'
            ,
                'conversion_rate'
            );
        }

        parent::__construct($config);
    }

    public function getCategoriesOptions()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('Category.id AS value, Category.title as text')
            ->from('#__categories AS Category')
            ->where('Category.published = 1')
            ->where('Category.extension = ' . $this->getDbo()->quote(JInbound::COM))
            ->order('Category.lft ASC')
            ->order('Category.title ASC')
            ->group('Category.id');
        return $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_CATEGORY'));
    }

    public function getCampaignsOptions()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('Campaign.id AS value, Campaign.name as text')
            ->from('#__jinbound_campaigns AS Campaign')
            ->where('Campaign.published = 1')
            ->group('Campaign.id');
        return $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_CAMPAIGN'));
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $app    = JFactory::getApplication();
        $format = $app->input->get('format', '', 'cmd');
        // load the filter values
        $filters = $this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
        // don't let your filters grow up to be objects?
        if (is_object($filters)) {
            $filters = (array)$filters;
        }
        // still not an array? forget it
        if (!is_array($filters)) {
            $filters = array();
        }
        $this->setState('filter', $filters);

        foreach (array('category', 'campaign') as $var) {
            $value = array_key_exists($var, $filters)
                ? $filters[$var]
                : $this->getUserStateFromRequest($this->context . '.filter.' . $var, 'filter_' . $var, '', 'string');
            $this->setState('filter.' . $var, $value);
        }

        foreach (array('start', 'end', 'page') as $var) {
            $value = array_key_exists($var, $filters)
                ? $filters[$var]
                : $this->getUserStateFromRequest($this->context . '.filter.' . $var, 'filter_' . $var, '', 'string');
            if ('json' != $format) {
                $value = '';
            }
            $this->setState('filter.' . $var, $value);
        }

        $this->setState('layout.json', 'json' == $format);
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
        $id .= ':' . $this->getState('filter.category');
        $id .= ':' . $this->getState('filter.campaign');
        $id .= ':' . $this->getState('filter.page');
        $id .= ':' . $this->getState('filter.start');
        $id .= ':' . $this->getState('filter.end');
        $id .= ':' . $this->getState('layout.json');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        // filters
        $start     = $this->getState('filter.start');
        $end       = $this->getState('filter.end');
        $startdate = false;
        $enddate   = false;
        if (!empty($start) && is_string($start)) {
            try {
                $startdate = new DateTime($start);
            } catch (Exception $e) {
                $startdate = false;
            }
        }

        if (!empty($end) && is_string($end)) {
            try {
                $enddate = new DateTime($end);
            } catch (Exception $e) {
                $enddate = false;
            }
        }

        $cols = array(
            'id',
            'asset_id',
            'layout',
            'heading',
            'subheading',
            'socialmedia',
            'maintext',
            'sidebartext',
            'alias',
            'name',
            'image',
            'imagealttext',
            'category',
            'metatitle',
            'metadescription',
            'formid',
            'formbuilder',
            'campaign',
            'converts_on_another_form',
            'converts_on_same_campaign',
            'submit_text',
            'notify_form_submits',
            'notification_email',
            'after_submit_sendto',
            'menu_item',
            'send_to_url',
            'sendto_message',
            'template',
            'css',
            'ga',
            'ga_code',
            'published',
            'created',
            'created_by',
            'modified',
            'modified_by',
            'checked_out',
            'checked_out_time'
        );
        // main query
        $query = $db->getQuery(true)
            ->select('Page.' . implode(', Page.', $cols))
            ->select('Category.title AS category_name')
            ->select('Campaign.name AS campaign_name')
            ->select('COUNT(DISTINCT Submission.contact_id) AS contact_submissions')
            ->select('GROUP_CONCAT(DISTINCT Submission.contact_id) AS contact_submission_ids')
            ->select('COUNT(DISTINCT Submission.id) AS submissions')
            ->select('GROUP_CONCAT(DISTINCT Submission.id) AS submission_ids')
            ->select('COUNT(DISTINCT Conversion.contact_id) AS conversions')
            ->select('GROUP_CONCAT(DISTINCT Conversion.contact_id) AS conversion_ids')
            ->select('ROUND(IF(COUNT(DISTINCT Submission.contact_id) > 0, (COUNT(DISTINCT Conversion.contact_id) / COUNT(DISTINCT Submission.contact_id)) * 100, 0), 2) AS conversion_rate')
            ->from('#__jinbound_pages AS Page')
            ->leftJoin('#__categories AS Category ON Category.id = Page.category')
            ->leftJoin('#__jinbound_campaigns AS Campaign ON Campaign.id = Page.campaign')
            ->leftJoin('#__jinbound_conversions AS Submission ON Submission.page_id = Page.id AND Submission.published = 1')
            ->leftJoin('('
                . $db->getQuery(true)
                    ->select('s1.*')
                    ->from('#__jinbound_contacts_statuses AS s1')
                    ->leftJoin('#__jinbound_contacts_statuses AS s2 ON s1.contact_id = s2.contact_id AND s1.campaign_id = s2.campaign_id AND s1.created < s2.created')
                    ->where('s2.contact_id IS NULL')
                . ') AS Conversion ON Conversion.campaign_id = Campaign.id AND Conversion.contact_id = Submission.contact_id AND Conversion.status_id IN (('
                . $db->getQuery(true)
                    ->select('Status.id')
                    ->from('#__jinbound_lead_statuses AS Status')
                    ->where('Status.final = 1')
                    ->where('Status.published = 1')
                . '))')
            ->group('Page.id');

        // add author to query
        $this->appendAuthorToQuery($query, 'Page');
        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Page', 'id',
            array('name', 'Category.title'));
        $this->filterPublished($query, $this->getState('filter.published'), 'Page');
        // campaign filter
        $filter = $this->getState('filter.campaign');
        if (!empty($filter)) {
            if (is_array($filter)) {
                JArrayHelper::toInteger($filter);
                $query->where('Page.campaign IN(' . implode(',', $filter) . ')');
            } else {
                $query->where('Page.campaign = ' . (int)$filter);
            }
        }
        // category filter
        $filter = $this->getState('filter.category');
        if (!empty($filter)) {
            $query->where('Page.category = ' . (int)$filter);
        }
        $filter = $this->getState('filter.page');
        if (!empty($filter)) {
            $query->where('Page.id = ' . (int)$filter);
        }

        // load all hits or just some?
        $allhits = true;
        $hitjoin = array();
        if (!empty($startdate)) {
            $allhits   = false;
            $d         = $db->quote($startdate->format('Y-m-d h:i:s'));
            $hitjoin[] = 'PageHits.day >= ' . $d;
            $query->where('Submission.created > ' . $d);
        }

        if (!empty($enddate)) {
            $allhits   = false;
            $d         = $db->quote($enddate->format('Y-m-d h:i:s'));
            $hitjoin[] = 'PageHits.day <= ' . $d;
            $query->where('Submission.created < ' . $d);
        }

        if ($allhits) {
            $query->select('Page.hits');
        } else {
            $query->select('('
                . $db->getQuery(true)
                    ->select('SUM(PageHits.hits)')
                    ->from('#__jinbound_landing_pages_hits AS PageHits')
                    ->where('PageHits.page_id = Page.id')
                    ->where('(' . implode(' AND ', $hitjoin) . ')')
                . ') AS hits');
        }

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Page.name');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
