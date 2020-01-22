<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2020 Joomlashack.com. All rights reserved
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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

require_once dirname(__FILE__) . '/contacts.php';
require_once dirname(__FILE__) . '/emails.php';
require_once dirname(__FILE__) . '/pages.php';

/**
 * This models supports retrieving reports
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelReports extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.reports';

    protected $frequencies = array(
        '1 DAY',
        '1 WEEK',
        '2 WEEK',
        '1 MONTH',
        '2 MONTH',
        '3 MONTH',
        '6 MONTH',
        '1 YEAR'
    );

    /**
     * @var JObject
     */
    protected $pageModelState = null;

    /**
     * @var JObject
     */
    protected $contactModelState = null;

    /**
     * @var int
     */
    protected static $conversionCount = null;

    /**
     * @var string
     */
    protected static $conversionRate = null;

    /**
     * @var object
     */
    protected static $dateRange = null;

    /**
     * @var string
     */
    protected static $viewsToLeads = null;

    public function getFilterForm($data = array(), $loadData = true)
    {
        // Disable this because there is no filter form for reports
    }

    /**
     * @return object[]
     * @throws Exception
     */
    public function getRecentContacts()
    {
        $app   = JFactory::getApplication();
        $db    = $this->getDbo();
        $input = $app->input;

        $start     = $input->getString('filter_start');
        $end       = $input->getString('filter_end');
        $campaign  = $input->getString('filter_campaign');
        $page      = $input->getString('filter_page');
        $status    = $input->getString('filter_status');
        $priority  = $input->getString('filter_priority');
        $published = $input->getString('filter_published');

        $query = $db->getQuery(true)
            ->select(
                array(
                    'Contact.id AS id',
                    'Contact.created AS date',
                    'CONCAT_WS(' . $db->quote(' ') . ', Contact.first_name, Contact.last_name) AS name',
                    'Conversion.formdata AS formdata',
                    'Page.id AS page_id',
                    'Page.formname AS formname',
                    'Contact.website AS website'
                )
            )
            ->from('#__jinbound_contacts AS Contact')
            ->leftJoin('#__jinbound_conversions AS Conversion ON Contact.id = Conversion.contact_id')
            ->leftJoin('#__jinbound_pages AS Page ON Conversion.page_id = Page.id')
            ->where(
                array(
                    'Contact.published = 1',
                    'Conversion.published = 1',
                    'Page.published = 1'
                )
            )
            ->group(
                array(
                    'Contact.id',
                    'Conversion.id'
                )
            )
            ->order('Contact.created ASC');

        if ($campaign) {
            $query->where(
                sprintf(
                    'Contact.id IN (%s)',
                    $db->getQuery(true)
                        ->select('ContactCampaign.contact_id')
                        ->from('#__jinbound_contacts_campaigns AS ContactCampaign')
                        ->where(
                            array(
                                'ContactCampaign.campaign_id = ' . (int)$campaign,
                                'ContactCampaign.enabled = 1'
                            )
                        )
                )
            );
        }

        if ($page) {
            $query->where('Page.id = ' . (int)$page);
        }

        if ($status) {
            // join in only the latest status
            $query->leftJoin(
                sprintf(
                    '(%s) AS ContactStatus ON ContactStatus.campaign_id = Page.campaign AND ContactStatus.contact_id = Contact.id',
                    $db->getQuery(true)
                        ->select('s1.*')
                        ->from('#__jinbound_contacts_statuses AS s1')
                        ->leftJoin('#__jinbound_contacts_statuses AS s2 ON s1.contact_id = s2.contact_id AND s1.campaign_id = s2.campaign_id AND s1.created < s2.created')
                        ->where('s2.contact_id IS NULL')
                )
            )->where('ContactStatus.status_id = ' . (int)$status);
        }

        if (!empty($priority)) {
            // join in only the latest priority
            $query->leftJoin(
                sprintf(
                    '(%s) AS ContactPriority ON ContactPriority.campaign_id = Page.campaign AND ContactPriority.contact_id = Contact.id',
                    $db->getQuery(true)
                        ->select('p1.*')
                        ->from('#__jinbound_contacts_priorities AS p1')
                        ->leftJoin('#__jinbound_contacts_priorities AS p2 ON p1.contact_id = p2.contact_id AND p1.campaign_id = p2.campaign_id AND p1.created < p2.created')
                        ->where('p2.contact_id IS NULL')
                )
            )->where('ContactPriority.priority_id = ' . (int)$priority);
        }

        if (is_numeric($published)) {
            $query->where('Contact.published = ' . (int)$published);
        }

        if ($start) {
            try {
                $start = new DateTime($start);
                if ($start) {
                    $query->where('Conversion.created > ' . $db->quote($start->format('Y-m-d h:i:s')));
                }

            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        if ($end) {
            try {
                $end = new DateTime($end);
                if ($end) {
                    $query->where('Conversion.created < ' . $db->quote($end->format('Y-m-d h:i:s')));
                }

            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        try {
            $contacts = $db->setQuery($query)->loadObjectList();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $contacts = array();
        }

        return $contacts;
    }

    /**
     * Gets a list of the top landing pages
     *
     * @return array
     * @throws Exception
     */
    public function getTopPages()
    {
        $app      = JFactory::getApplication();
        $input    = $app->input;
        $start    = $input->get('filter_start', '', 'string');
        $end      = $input->get('filter_end', '', 'string');
        $campaign = $input->get('filter_campaign', '', 'string');
        $page     = $input->get('filter_page', '', 'string');
        $query    = $this->getDbo()->getQuery(true)
            ->select(
                array(
                    'Page.id AS id',
                    'Page.name AS name',
                    'Page.hits AS hits',
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
            ->from('#__jinbound_pages AS Page')
            ->leftJoin('#__categories AS Category ON Category.id = Page.category')
            ->leftJoin('#__jinbound_campaigns AS Campaign ON Campaign.id = Page.campaign')
            ->leftJoin('#__jinbound_conversions AS Submission ON Submission.page_id = Page.id AND Submission.published = 1')
            ->leftJoin(
                sprintf(
                    '(%s) AS Conversion ON Conversion.campaign_id = Campaign.id AND Conversion.contact_id = Submission.contact_id AND Conversion.status_id IN (%s)',
                    $this->getDbo()
                        ->getQuery(true)
                        ->select('s1.*')
                        ->from('#__jinbound_contacts_statuses AS s1')
                        ->leftJoin('#__jinbound_contacts_statuses AS s2 ON s1.contact_id = s2.contact_id AND s1.campaign_id = s2.campaign_id AND s1.created < s2.created')
                        ->where('s2.contact_id IS NULL'),
                    $this->getDbo()
                        ->getQuery(true)
                        ->select('Status.id')
                        ->from('#__jinbound_lead_statuses AS Status')
                        ->where('Status.final = 1')
                        ->where('Status.published = 1')
                )
            )
            ->group('Page.id')
            ->order('conversion_rate DESC')
            ->order('Page.hits DESC');

        if (!empty($campaign)) {
            $query->where('Campaign.id = ' . (int)$campaign);
        }

        if (!empty($page)) {
            $query->where('Page.id = ' . (int)$page);
        }

        if (!empty($start)) {
            try {
                $start = new DateTime($start);
                if ($start) {
                    $query->where('Contact.created > ' . $this->getDbo()->quote($start->format('Y-m-d h:i:s')));
                }
            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        if (!empty($end)) {
            try {
                $end = new DateTime($end);
                if ($end) {
                    $query->where('Contact.created > ' . $this->getDbo()->quote($end->format('Y-m-d h:i:s')));
                }
            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        try {
            $pages = $this->getDbo()->setQuery($query)->loadObjectList();
        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $pages = array();
        }
        return $pages;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getConversionCount()
    {
        if (static::$conversionCount === null) {
            static::$conversionCount = 0;

            $this->getDbo()->setQuery(
                $this->getDbo()->getQuery(true)
                    ->select('COUNT(Lead.id) AS conversions')
                    ->from('#__jinbound_leads AS Lead')
                    ->innerJoin('#__jinbound_lead_statuses AS Status ON Lead.status_id = Status.id AND Status.final = 1')
                    ->where('Lead.published = 1')
            );

            try {
                static::$conversionCount = (int)$this->getDbo()->loadResult();

            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return static::$conversionCount;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getConversionRate()
    {
        if (static::$conversionRate === null) {
            $count = (int)$this->getConversionsCount(); //$this->getConversionCount();
            $hits  = (int)$this->getVisitCount();
            if (0 < $hits) {
                $rate = ($count / $hits) * 100;
            } else {
                $rate = 0;
            }

            static::$conversionRate = number_format($rate, 2);
        }

        return static::$conversionRate;
    }

    /**
     * gets the number of active conversions
     *
     * @return integer
     */
    public function getConversionsCount()
    {
        $state       = $this->getState();
        $start       = $state->get('filter.start', null);
        $end         = $state->get('filter.end', null);
        $conversions = $this->getConversionsByDate($start, $end);
        $count       = 0;
        foreach ($conversions as $conversion) {
            $count += (int)$conversion[1];
        }
        return $count;
    }

    /**
     * @param string $start
     * @param string $end
     *
     * @return array
     */
    public function getConversionsByDate($start = null, $end = null)
    {
        $dates = $this->getDateRanges($start, $end);
        $inner = $this->getDbo()->getQuery(true)
            ->select('a.*,c.final')
            ->from('#__jinbound_contacts_statuses AS a')
            ->leftJoin('#__jinbound_contacts_statuses AS b ON a.contact_id = b.contact_id AND a.campaign_id = b.campaign_id AND a.created < b.created')
            ->innerJoin('#__jinbound_lead_statuses AS c ON c.id = a.status_id')
            ->where('b.contact_id IS NULL');
        $query = $this->getDbo()->getQuery(true)
            ->select('DATE(created) AS day, COUNT(contact_id) AS num')->where('final = 1')
            ->group('day');

        if (!empty($start)) {
            try {
                $startdate = new DateTime($start);
                $query->where('created > ' . $this->getDbo()->quote($startdate->format('Y-m-d H:i:s')));

            } catch (Exception $ex) {
                // ignore badly formed date string
            }
        }

        if (!empty($end)) {
            try {
                $enddate = new DateTime($end);
                $query->where('created < ' . $this->getDbo()->quote($enddate->format('Y-m-d H:i:s')));

            } catch (Exception $ex) {
                // ignore badly formed date string
            }
        }

        $campaign = $this->getState('filter.campaign');
        if (!empty($campaign)) {
            $inner->where('a.campaign_id = ' . (int)$campaign);
        }

        $page = $this->getState('filter.page');
        if (!empty($page)) {
            $inner->where(
                sprintf(
                    'a.campaign_id IN (%s)',
                    $this->getDbo()->getQuery(true)
                        ->select('p.campaign')
                        ->from('#__jinbound_pages AS p')
                        ->where('p.id = ' . (int)$page)
                )
            );
        }

        $days = $this->getDbo()
            ->setQuery($query->from('(' . $inner . ') AS t'))
            ->loadObjectList();

        $data = array();
        foreach ($dates as $date) {
            $entry = array($date . ' 00:00:00');
            $count = 0;
            foreach ($days as $day) {
                if ($day->day == $date) {
                    $count += (int)$day->num;
                    break;
                }
            }
            $entry[] = $count;
            $data[]  = $entry;
        }

        return $data;
    }

    /**
     * @param string $start
     * @param string $end
     *
     * @return array
     */
    private function getDateRanges($start = null, $end = null)
    {
        if (static::$dateRange === null) {
            $unionQueries = array(
                $this->getDbo()->getQuery(true)->select('day AS created')->from('#__jinbound_landing_pages_hits'),
                $this->getDbo()->getQuery(true)->select('created')->from('#__jinbound_contacts'),
                $this->getDbo()->getQuery(true)->select('created')->from('#__jinbound_conversions'),
                $this->getDbo()->getQuery(true)->select('created')->from('#__jinbound_contacts_priorities'),
                $this->getDbo()->getQuery(true)->select('created')->from('#__jinbound_contacts_statuses'),
            );

            static::$dateRange = $this->getDbo()->setQuery(
                $this->getDbo()->getQuery(true)
                    ->select('MIN(t.created) AS start, NOW() AS end')
                    ->from(sprintf('(%s) as t', join(' UNION ', $unionQueries)))
            )->loadObject();
        }

        $tz          = new DateTimeZone('UTC');
        $date        = new stdClass;
        $date->start = new DateTime($start ?: static::$dateRange->start, $tz);
        $date->end   = new DateTime($end ?: static::$dateRange->end, $tz);
        $date->end->modify('+1 day');

        $dates = array();
        while ($date->start < $date->end) {
            $dates[] = $date->start->format('Y-m-d');
            $date->start->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Gets the total number of hits for all landing pages
     *
     * @return integer
     * @throws Exception
     */
    public function getVisitCount()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('SUM(PageHits.hits)')
            ->from('#__jinbound_landing_pages_hits AS PageHits')
            ->leftJoin('#__jinbound_pages AS Page ON Page.id = PageHits.page_id')
            ->where('Page.published = 1');


        try {
            $count = $this->getDbo()->setQuery($query)->loadResult();

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            $count = 0;
        }

        return (int)$count;
    }

    public function getPublishedStatus()
    {
        return false;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getViewsToLeads()
    {
        if (static::$viewsToLeads === null) {
            $count = (int)$this->getContactsCount();
            $hits  = (int)$this->getVisitCount();
            if (0 < $hits) {
                $rate = ($count / $hits) * 100;

            } else {
                $rate = 0;
            }

            static::$viewsToLeads = number_format($rate, 2);
        }

        return static::$viewsToLeads;
    }

    /**
     * Gets the number of active contacts
     *
     * @return int
     * @throws Exception
     */
    public function getContactsCount()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('COUNT(DISTINCT Contact.id)')
            ->from('#__jinbound_contacts AS Contact')
            ->leftJoin(
                sprintf(
                    '(%s) AS ContactStatus ON (ContactStatus.contact_id = Contact.id)',
                    $this->getDbo()->getQuery(true)
                        ->select('s1.*')
                        ->from('#__jinbound_contacts_statuses AS s1')
                        ->leftJoin('#__jinbound_contacts_statuses AS s2 ON s1.contact_id = s2.contact_id AND s1.campaign_id = s2.campaign_id AND s1.created < s2.created')
                        ->where('s2.contact_id IS NULL')
                )
            )
            ->leftJoin('#__jinbound_lead_statuses AS Status ON ContactStatus.status_id = Status.id')
            ->where('(Status.active = 1 OR Status.active IS NULL)')// users with no status are probably new and something went wonky
            ->where('Contact.published = 1');

        try {
            $count = $this->getDbo()->setQuery($query)->loadResult();

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            $count = 0;
        }

        return (int)$count;
    }

    /**
     * return void
     */
    public function send()
    {
        $out = JInboundHelper::config("debug", 0);
        // only send if configured to
        if (!JInboundHelper::config('send_reports', 1)) {
            if ($out) {
                echo "<p>Not sending reports - disabled in config</p>";
            }
            return;
        }

        $db = JFactory::getDbo();
        try {
            $emailrecords = $db->setQuery(
                $db->getQuery(true)
                    ->select('*')
                    ->from('#__jinbound_emails')
                    ->where(
                        array(
                            'published = 1',
                            $db->quoteName('type') . ' = ' . $db->quote('report')
                        )
                    )
            )->loadObjectList();

        } catch (Exception $e) {
            if ($out) {
                echo "<p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
            }

            return;
        }

        if (empty($emailrecords)) {
            if ($out) {
                echo "<p>No report emails found</p>";
            }

            return;
        }

        /** @var JInboundModelEmails $emailModel */
        $emailModel = JModelLegacy::getInstance('Emails', 'JInboundModel');

        foreach ($emailrecords as $emailrecord) {
            if (empty($emailrecord->params)) {
                if ($out) {
                    echo "<p>Email has no params...</p>";
                }

                continue;
            }
            $emailrecord->params = new Registry($emailrecord->params);

            $emails = array_filter(
                array_map(
                    'trim',
                    explode(',', $emailrecord->params->get('recipients'))
                )
            );

            // only send if there are emails
            if (empty($emails)) {
                if ($out) {
                    echo "<p>Email has no recipients...</p>";
                }
                continue;
            }

            // quote emails for use in db query
            $quoted = array_map(array($db, 'quote'), $emails);

            $frequency = $emailrecord->params->get('reports_frequency');
            if (!$frequency || !in_array($frequency, $this->frequencies)) {
                if ($out) {
                    echo "<p>Invalid frequency...</p>";
                }
                continue;
            }

            $limit = (int)JInboundHelper::config('cron_max_reports', 0);
            $query = $db->getQuery(true)
                ->select('email')
                ->from('#__jinbound_reports_emails')
                ->where('email_id = ' . intval($emailrecord->id))
                ->where("created > (NOW() - INTERVAL $frequency)");

            $records = $db->setQuery($query, 0, $limit)->loadColumn();

            // if there are no records, we can send
            // otherwise skip
            $sendto = array();
            foreach ($emails as $email) {
                if (in_array($email, $records)) {
                    continue;
                }
                $sendto[] = $email;
            }

            // only send if sendto isn't empty
            if (empty($sendto)) {
                if ($out) {
                    echo "<p>Cannot send to any recipients yet...</p>";
                }
                continue;
            }

            $data    = $this->getReportEmailData($emailrecord);
            $tags    = $this->getReportEmailTags($emailrecord);
            $subject = $emailrecord->subject;

            $htmlbody  = $emailModel->replaceTags($emailrecord->htmlbody, $data, $tags);
            $plainbody = $emailModel->replaceTags($emailrecord->plainbody, $data, $tags);

            // send emails
            $mailer = JFactory::getMailer();
            $mailer->ClearAllRecipients();
            $mailer->setSubject($subject);
            $mailer->setBody($htmlbody);
            $mailer->IsHtml(true);
            $mailer->AltBody = $plainbody;

            if ($out) {
                echo "<p>Attempting to send the following email:</p>\n";
                echo "<h4>$subject</h4>";
                echo $htmlbody;
            }

            // #641 if one recipient succeeds, the whole thing should succeed
            foreach ($sendto as $recipient) {
                try {
                    $mailer->ClearAllRecipients();
                    $mailer->addRecipient($recipient);
                    $send_response = $mailer->send();
                    if ($send_response === false) {
                        throw new Exception('Unspecified error sending mail!', 500);

                    } elseif ($send_response instanceof JError) {
                        throw new Exception($send_response->getMessage(), 500);

                    } else {
                        $query = $db->getQuery(true)
                            ->insert('#__jinbound_reports_emails')
                            ->columns(array('email', 'email_id', 'created'))
                            ->values($db->quote($recipient) . ', ' . intval($emailrecord->id) . ', NOW()');

                        $db->setQuery($query)->execute();
                    }

                } catch (Exception $e) {
                    if ($out) {
                        echo "<p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
                    }
                    return;
                }

            }
        }
    }

    /**
     * @param string $email
     *
     * @return object
     */
    public function getReportEmailData($email)
    {
        $out        = JInboundHelper::config("debug", 0);
        $dispatcher = JEventDispatcher::getInstance();
        $start_date = new DateTime();
        $end_date   = new DateTime();

        $start_date->modify('-' . $email->params->get('reports_frequency'));
        $start     = $start_date->format('Y-m-d H:i:s');
        $end       = $end_date->format('Y-m-d H:i:s');
        $campaigns = $email->params->get('campaigns');
        if (!is_array($campaigns)) {
            $campaigns = explode(',', $campaigns);
        }

        if ($out) {
            echo "<p>Reporting on data from '$start' to '$end'...</p>";
        }
        $lead_filters = array(
            'filter'          => array(
                'start'    => $start,
                'end'      => $end,
                'campaign' => $campaigns
            ),
            'filter.start'    => $start,
            'filter.end'      => $end,
            'filter.campaign' => $campaigns,
            'list'            => array(
                'limit'     => 10,
                'ordering'  => 'Contact.created',
                'direction' => 'DESC'
            ),
            'list.limit'      => 10,
            'list.ordering'   => 'Contact.created',
            'list.direction'  => 'DESC'
        );

        $page_filters                  = array_merge($lead_filters, array());
        $page_filters['list.ordering'] = $page_filters['list']['ordering'] = 'hits';

        $page_list_data = $this->getPagesArrayForEmail($page_filters);
        $lead_list_data = $this->getLeadsArrayForEmail($lead_filters);

        $top_pages_data = array_merge(array(), $page_list_data);
        $top            = array(
            'name' => '',
            'url'  => ''
        );
        if (!empty($top_pages_data)) {
            $toppage     = array_shift($top_pages_data);
            $top['name'] = $toppage->name;
            $top['url']  = JInboundHelperUrl::toFull(
                JInboundHelperUrl::view('page', true, array('id' => $toppage->id))
            );
        }
        $lowest = array_merge(array(), $top);
        if (!empty($top_pages_data)) {
            $lowestpage     = array_pop($top_pages_data);
            $lowest['name'] = $lowestpage->name;
            $lowest['url']  = JInboundHelperUrl::toFull(
                JInboundHelperUrl::view('page', true, array('id' => $lowestpage->id))
            );
        }

        $hits_data       = $this->getLandingPageHits($start, $end);
        $lead_data       = $this->getLeadsByCreationDate($start, $end);
        $conv_data       = $this->getConversionsByDate($start, $end);
        $views           = 0;
        $leads           = 0;
        $conversions     = 0;
        $views_to_leads  = 0;
        $conversion_rate = 0;
        // add values
        foreach ($hits_data as $hit) {
            $views += (int)$hit[1];
        }
        foreach ($lead_data as $lead) {
            $leads += (int)$lead[1];
        }
        foreach ($conv_data as $conversion) {
            $conversions += (int)$conversion[1];
        }
        // calc percents
        if (0 < $views) {
            $views_to_leads  = ($leads / $views) * 100;
            $conversion_rate = ($conversions / $views) * 100;
        }
        $views_to_leads  = number_format($views_to_leads, 2) . '%';
        $conversion_rate = number_format($conversion_rate, 2) . '%';
        $debug           = array('filters' => '', 'pagestate' => 'null', 'leadstate' => 'null');
        if ($this->pageModelState) {
            $debug['pagestate'] = json_encode($this->pageModelState);
        }
        if ($this->contactModelState) {
            $debug['leadstate'] = json_encode($this->contactModelState);
        }
        $data = array(
            'goals' => array(
                'count'   => $conversions,
                'percent' => $conversion_rate
            ),
            'leads' => array(
                'count'   => $leads,
                'list'    => $this->getEmailLeadsList(is_array($lead_list_data) ? $lead_list_data : array()),
                'percent' => $views_to_leads
            ),
            'pages' => array(
                'hits'   => $views,
                'list'   => $this->getEmailPagesList(is_array($page_list_data) ? $page_list_data : array()),
                'top'    => $top,
                'lowest' => $lowest
            ),
            'date'  => array(
                'start' => $start,
                'end'   => $end
            ),
            'debug' => $debug
        );

        $dispatcher->trigger('onJInboundReportEmailData', array(&$data));

        return json_decode(json_encode($data));
    }

    /**
     * @param array $filters
     *
     * @return object[]
     */
    public function getPagesArrayForEmail(array $filters = array())
    {
        $model = new JInboundModelPages();
        $model->getState();
        if (!empty($filters)) {
            foreach ($filters as $filter => $value) {
                $model->getState($filter, $value);
                $model->setState($filter, $value);
            }
        }
        $this->pageModelState = $model->getState();

        return $model->getItems();
    }

    /**
     * @param array $filters
     *
     * @return object[]
     */
    public function getLeadsArrayForEmail(array $filters = array())
    {
        $model = new JInboundModelContacts();
        $model->getState();
        if (!empty($filters)) {
            foreach ($filters as $filter => $value) {
                $model->getState($filter, $value);
                $model->setState($filter, $value);
            }
        }
        $this->contactModelState = $model->getState();

        return $model->getItems();
    }

    /**
     * @param string $start
     * @param string $end
     *
     * @return array
     */
    public function getLandingPageHits($start = null, $end = null)
    {
        $dates = $this->getDateRanges($start, $end);
        $query = $this->getDbo()->getQuery(true)
            ->select('PageHit.day, SUM(PageHit.hits) AS hits')
            ->from('#__jinbound_landing_pages_hits AS PageHit')
            ->group('PageHit.day');
        if (!empty($start)) {
            $query->where('PageHit.day >= ' . $this->getDbo()->quote($start));
        }
        if (!empty($end)) {
            $query->where('PageHit.day <= ' . $this->getDbo()->quote($end));
        }
        $campaign = $this->getState('filter.campaign');
        if (!empty($campaign)) {
            $query->where(
                sprintf(
                    'PageHit.page_id IN (%s)',
                    $this->getDbo()->getQuery(true)
                        ->select('Page.id')
                        ->from('#__jinbound_pages AS Page')
                        ->where('Page.campaign = ' . (int)$campaign)
                )
            );
        }
        $page = $this->getState('filter.page');
        if (!empty($page)) {
            $query->where('PageHit.page_id = ' . (int)$page);
        }
        $days = $this->getDbo()->setQuery($query)->loadObjectList();

        $data = array();
        foreach ($dates as $date) {
            $entry = array($date . ' 00:00:00');
            $count = 0;
            foreach ($days as $day) {
                if ($day->day == $date) {
                    $count = (int)$day->hits;
                    break;
                }
            }
            reset($days);
            $entry[] = $count;
            $data[]  = $entry;
        }
        return $data;
    }

    /**
     * @param string $start
     * @param string $end
     *
     * @return array
     */
    public function getLeadsByCreationDate($start = null, $end = null)
    {
        $dates = $this->getDateRanges($start, $end);
        $query = $this->getDbo()->getQuery(true)
            ->select('DATE(Contact.created) AS day, COUNT(Contact.id) AS total')
            ->from('#__jinbound_contacts AS Contact')
            ->group('day');

        if (!empty($start)) {
            try {
                $startdate = new DateTime($start);
                $query->where('Contact.created > ' . $this->getDbo()->quote($startdate->format('Y-m-d H:i:s')));

            } catch (Exception $ex) {
                // nothing
            }
        }

        if (!empty($end)) {
            try {
                $enddate = new DateTime($end);
                $query->where('Contact.created < ' . $this->getDbo()->quote($enddate->format('Y-m-d H:i:s')));

            } catch (Exception $ex) {
                // nothing
            }
        }

        $campaign = $this->getState('filter.campaign');
        if (!empty($campaign)) {
            $query->where(
                sprintf(
                    'Contact.id IN (%s)',
                    $this->getDbo()->getQuery(true)
                        ->select('ContactCampaign.contact_id')
                        ->from('#__jinbound_contacts_campaigns AS ContactCampaign')
                        ->where(
                            array(
                                'ContactCampaign.campaign_id = ' . (int)$campaign,
                                'ContactCampaign.enabled = 1'
                            )
                        )
                )
            );
        }

        $page = $this->getState('filter.page');
        if (!empty($page)) {
            $query->where(
                sprintf(
                    'Contact.id IN ((%s))',
                    $this->getDbo()->getQuery(true)
                        ->select('Conversion.contact_id')
                        ->from('#__jinbound_conversions AS Conversion')
                        ->where('Conversion.page_id = ' . (int)$page)

                )
            );
        }

        $days = $this->getDbo()->setQuery($query)->loadObjectList();
        $data = array();
        foreach ($dates as $date) {
            $entry = array($date . ' 00:00:00');
            $count = 0;
            foreach ($days as $day) {
                if ($day->day == $date) {
                    $count += (int)$day->total;
                    break;
                }
            }
            reset($days);
            $entry[] = $count;
            $data[]  = $entry;
        }

        return $data;
    }

    /**
     * @param array $leads
     *
     * @return string
     */
    public function getEmailLeadsList(array $leads = array())
    {
        $table   = array();
        $table[] = '<table>';
        $table[] = '<thead>';
        $table[] = '<tr>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_NAME') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_DATE') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_FORM_CONVERTED_ON') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_LANDING_PAGE_NAME') . '</th>';
        $table[] = '</tr>';
        $table[] = '</thead>';
        $table[] = '<tbody>';
        if (empty($leads)) {
            $table[] = '<tr><td colspan="4">' . JText::_('COM_JINBOUND_NOT_FOUND') . '</td></tr>';
        } else {
            foreach ($leads as $lead) {
                $table[] = '<tr>';
                // name
                $table[] = '<td>';
                $table[] = JInboundHelperFilter::escape($lead->first_name . ' ' . $lead->last_name);
                $table[] = '</td>';

                $table[] = '<td>';
                $table[] = $lead->latest ? $lead->latest : $lead->created;
                $table[] = '</td>';

                $table[] = '<td>';
                if (!empty($lead->latest_conversion_page_formname)) {
                    $table[] = JInboundHelperFilter::escape($lead->latest_conversion_page_formname);
                }
                $table[] = '</td>';

                $table[] = '<td>';
                if (!empty($lead->latest_conversion_page_name)) {
                    $table[] = JInboundHelperFilter::escape($lead->latest_conversion_page_name);
                }
                $table[] = '</td>';

                $table[] = '</tr>';
            }
        }
        $table[] = '</tbody>';
        $table[] = '</table>';

        return implode("\n", $table);
    }

    /**
     * @param array $pages
     *
     * @return string
     */
    public function getEmailPagesList(array $pages = array())
    {
        $table   = array();
        $table[] = '<table>';
        $table[] = '<thead>';
        $table[] = '<tr>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_LANDING_PAGE_NAME') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_VISITS') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_SUBMISSIONS') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_LEADS') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_GOAL_COMPLETIONS') . '</th>';
        $table[] = '<th>' . JText::_('COM_JINBOUND_GOAL_COMPLETION_RATE') . '</th>';
        $table[] = '</tr>';
        $table[] = '</thead>';
        $table[] = '<tbody>';
        if (empty($pages)) {
            $table[] = '<tr><td colspan="6">' . JText::_('COM_JINBOUND_NOT_FOUND') . '</td></tr>';
        } else {
            foreach ($pages as $page) {
                $table[] = '<tr>';
                // name
                $table[] = '<td>';
                if (!empty($page->name)) {
                    $table[] = JInboundHelperFilter::escape($page->name);
                }
                $table[] = '</td>';

                $table[] = '<td>' . $page->hits . '</td>';
                $table[] = '<td>' . $page->submissions . '</td>';
                $table[] = '<td>' . $page->contact_submissions . '</td>';
                $table[] = '<td>' . $page->conversions . '</td>';
                $table[] = '<td>' . $page->conversion_rate . '</td>';

                $table[] = '</tr>';
            }
        }
        $table[] = '</tbody>';
        $table[] = '</table>';

        return implode("\n", $table);
    }

    /**
     * @param string $email
     *
     * @return array
     */
    public function getReportEmailTags($email = null)
    {
        $dispatcher = JEventDispatcher::getInstance();
        $tags       = array(
            'reports.goals.count',
            'reports.goals.percent',
            'reports.leads.count',
            'reports.leads.list',
            'reports.leads.percent',
            'reports.pages.hits',
            'reports.pages.list',
            'reports.pages.top.name',
            'reports.pages.top.url',
            'reports.pages.lowest.name',
            'reports.pages.lowest.url',
            'reports.date.start',
            'reports.date.end',
            'reports.debug.filters',
            'reports.debug.leadstate',
            'reports.debug.pagestate'
        );
        $dispatcher->trigger('onJInboundReportEmailTags', array(&$tags, $email));

        return $tags;
    }

    /**
     * @param string $start
     * @param string $end
     *
     * @return string
     */
    public function getTickString($start = null, $end = null)
    {
        $dates = $this->getDateRanges($start, $end);
        if (empty($dates)) {
            return '1 day';
        }

        try {
            $start_date = new DateTime(reset($dates));
            $end_date   = new DateTime(end($dates));

        } catch (Exception $ex) {
            return '1 day';
        }

        $diff = abs(intval($start_date->format('U')) - intval($end_date->format('U')));
        $day  = 60 * 60 * 24;
        $year = $day * 365;
        // 2 weeks or less - 1 day
        if ($day * 14 >= $diff) {
            return '1 day';
        }
        // 2 weeks to a month - 2 day
        if ($day * 31 >= $diff) {
            return '2 day';
        }
        // month to 3 months - 1 week
        if ($day * 90 >= $diff) {
            return '1 week';
        }
        // 3 months to 6 months - 2 weeks
        if ($day * 180 >= $diff) {
            return '2 week';
        }
        // 3 months to 2 years - 1 month
        if ($year * 2 >= $diff) {
            return '1 month';
        }
        // 2 years to 4 years - 2 months
        if ($year * 4 >= $diff) {
            return '2 month';
        }
        // 4 years to 6 years - 3 months
        if ($year * 6 >= $diff) {
            return '3 month';
        }
        // 6+ years - 1 year
        if ($year * 6 < $diff) {
            return '1 year';
        }

        // no idea - use old default - this should never ever happen :)
        return '1 day';
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $filters = array(
            'page',
            'campaign',
            'start',
            'end',
            'status',
            'priority',
            'chart'
        );

        foreach ($filters as $filter) {
            $this->setState(
                'filter.' . $filter,
                $this->getUserStateFromRequest(
                    $this->context . '.filter.' . $filter,
                    'filter_' . $filter,
                    '',
                    'string'
                )
            );
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
        $id .= ':' . serialize($this->getState('filter.campaign'));
        $id .= ':' . serialize($this->getState('filter.page'));
        $id .= ':' . serialize($this->getState('filter.start'));
        $id .= ':' . serialize($this->getState('filter.end'));
        $id .= ':' . serialize($this->getState('filter.status'));
        $id .= ':' . serialize($this->getState('filter.priority'));
        $id .= ':' . serialize($this->getState('filter.chart'));

        return parent::getStoreId($id);
    }

    /**
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->getDbo()->getQuery(true)
            ->select('1')
            ->from('#__jinbound_pages AS Page');

        return $query;
    }
}
