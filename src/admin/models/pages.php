<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class JInboundModelPages extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.pages';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Page.name',
                'Page.published',
                'Page.category',
                'Page.hits',
                'submissions',
                'conversions',
                'conversion_rate'
            );
        }

        parent::__construct($config);
    }

    /**
     * @return object[]
     */
    public function getCategoriesOptions()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('Category.id AS value, Category.title as text')
            ->from('#__categories AS Category')
            ->where(
                array(
                    'Category.published = 1',
                    'Category.extension = ' . $db->quote('com_jinbound')
                )
            )
            ->order(
                array(
                    'Category.lft ASC',
                    'Category.title ASC'
                )
            )
            ->group('Category.id');

        $options = $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_CATEGORY'));

        return $options;
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

        return $this->getOptionsFromQuery($query, JText::_('COM_JINBOUND_SELECT_CAMPAIGN'));
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

        $app = JFactory::getApplication();

        $format = $app->input->get('format', '', 'cmd');

        $filters = (array)$this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
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
     * @param string $id
     *
     * @return string
     */
    protected function getStoreId($id = '')
    {
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
        $db = $this->getDbo();

        if ($start = (string)$this->getState('filter.start')) {
            try {
                $startdate = new DateTime($start);
            } catch (Exception $e) {
                $startdate = null;
            }
        }

        if ($end = (string)$this->getState('filter.end')) {
            try {
                $enddate = new DateTime($end);
            } catch (Exception $e) {
                $enddate = false;
            }
        }

        $cols = array_map(
            function ($row) {
                return 'Page.' . $row;
            },
            array(
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
            )
        );

        $contactQuery = $db->getQuery(true)
            ->select('s1.*')
            ->from('#__jinbound_contacts_statuses AS s1')
            ->leftJoin('#__jinbound_contacts_statuses AS s2 ON s1.contact_id = s2.contact_id AND s1.campaign_id = s2.campaign_id AND s1.created < s2.created')
            ->where('s2.contact_id IS NULL');

        $leadQuery = $db->getQuery(true)
            ->select('Status.id')
            ->from('#__jinbound_lead_statuses AS Status')
            ->where('Status.final = 1')
            ->where('Status.published = 1');

        $query = $db->getQuery(true)
            ->select(
                array_merge(
                    $cols,
                    array(
                        'Category.title AS category_name',
                        'Campaign.name AS campaign_name',
                        'COUNT(DISTINCT Submission.contact_id) AS contact_submissions',
                        'GROUP_CONCAT(DISTINCT Submission.contact_id) AS contact_submission_ids',
                        'COUNT(DISTINCT Submission.id) AS submissions',
                        'GROUP_CONCAT(DISTINCT Submission.id) AS submission_ids',
                        'COUNT(DISTINCT Conversion.contact_id) AS conversions',
                        'GROUP_CONCAT(DISTINCT Conversion.contact_id) AS conversion_ids',
                        'ROUND(IF(COUNT(DISTINCT Submission.contact_id) > 0, (COUNT(DISTINCT Conversion.contact_id) / COUNT(DISTINCT Submission.contact_id)) * 100, 0), 2) AS conversion_rate'
                    )
                )
            )
            ->from('#__jinbound_pages AS Page')
            ->leftJoin('#__categories AS Category ON Category.id = Page.category')
            ->leftJoin('#__jinbound_campaigns AS Campaign ON Campaign.id = Page.campaign')
            ->leftJoin('#__jinbound_conversions AS Submission ON Submission.page_id = Page.id AND Submission.published = 1')
            ->leftJoin(
                sprintf(
                    '(%s) AS Conversion ON Conversion.campaign_id = Campaign.id AND Conversion.contact_id = Submission.contact_id AND Conversion.status_id IN (%s)',
                    $contactQuery,
                    $leadQuery
                )
            )
            ->group('Page.id');

        $this->appendAuthorToQuery($query, 'Page');

        $this->filterSearchQuery(
            $query,
            $this->getState('filter.search'),
            'Page',
            'id',
            array('name', 'Category.title')
        );

        $this->filterPublished($query, $this->getState('filter.published'), 'Page');

        if ($filter = $this->getState('filter.campaign')) {
            if (is_array($filter)) {
                ArrayHelper::toInteger($filter);
                $query->where(sprintf('Page.campaign IN(%s)', implode(',', $filter)));

            } else {
                $query->where('Page.campaign = ' . (int)$filter);
            }
        }

        if ($filter = $this->getState('filter.category')) {
            $query->where('Page.category = ' . (int)$filter);
        }

        if ($filter = $this->getState('filter.page')) {
            $query->where('Page.id = ' . (int)$filter);
        }

        $hitSelect = 'Page.hits';
        if (!empty($startdate) || !empty($enddate)) {
            $hits = array();
            if ($startdate instanceof DateTime) {
                $d      = $db->quote($startdate->format('Y-m-d h:i:s'));
                $hits[] = 'PageHits.day >= ' . $d;
                $query->where('Submission.created > ' . $d);
            }

            if ($enddate instanceof DateTime) {
                $d      = $db->quote($enddate->format('Y-m-d h:i:s'));
                $hits[] = 'PageHits.day <= ' . $d;
                $query->where('Submission.created < ' . $d);
            }

            if ($hits) {
                $hitSelect = sprintf(
                    '(%s) AS hits',
                    $db->getQuery(true)
                        ->select('SUM(PageHits.hits)')
                        ->from('#__jinbound_landing_pages_hits AS PageHits')
                        ->where('PageHits.page_id = Page.id')
                        ->where('(' . implode(' AND ', $hits) . ')')
                );
            }
        }
        $query->select($hitSelect);

        $listOrdering = $this->getState('list.ordering', 'Page.name');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
