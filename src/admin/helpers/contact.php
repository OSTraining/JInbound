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

defined('JPATH_PLATFORM') or die;

abstract class JInboundHelperContact
{
    public static function getContactEmails($contactId)
    {
        $db = JFactory::getDbo();
        try {
            $emails = $db->setQuery($db->getQuery(true)
                ->select('Record.*, Version.subject, Version.htmlbody, Version.plainbody, Email.campaign_id')
                ->from('#__jinbound_emails_records AS Record')
                ->where('Record.lead_id = ' . $db->quote($contactId))
                ->leftJoin('#__jinbound_emails_versions AS Version ON Version.id = Record.version_id')
                ->leftJoin('#__jinbound_emails AS Email ON Record.email_id = Email.id')
                ->group('Record.id')
            )->loadObjectList();
        } catch (Exception $e) {
            $emails = array();
        }

        return $emails;
    }

    /**
     * Load conversions for a given contact id
     *
     * @param int $contactId
     *
     * @return object[]
     */
    public static function getContactConversions($contactId)
    {
        $db = JFactory::getDbo();
        try {
            $conversions = $db->setQuery(
                $db->getQuery(true)
                    ->select(
                        array(
                            'Conversion.*',
                            'Page.name AS page_name',
                            sprintf(
                                'IF(Conversion.created_by = 0, %s, User.name) AS created_by_name',
                                $db->quote('guest')
                            )
                        )
                    )
                    ->from('#__jinbound_conversions AS Conversion')
                    ->leftJoin('#__jinbound_pages AS Page ON Page.id = Conversion.page_id')
                    ->leftJoin('#__users AS User ON User.id = Conversion.created_by')
                    ->where('Conversion.contact_id = ' . (int)$contactId)
                    ->group('Conversion.id')
            )
                ->loadObjectList();

            if ($conversions) {
                foreach ($conversions as &$conversion) {
                    $conversion->formdata = (array)json_decode($conversion->formdata);
                }
            }

        } catch (Exception $e) {
            $conversions = array();
        }

        return $conversions;
    }

    /**
     * Load campaigns for a given contact
     *
     * @param int  $contactId
     * @param bool $previous
     */
    public static function getContactCampaigns($contactId, $previous = false)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('Campaign.*')
            ->from('#__jinbound_campaigns AS Campaign');
        // previous entries are those that have statuses but not campaigns
        if ($previous) {
            $query
                ->where('Campaign.id NOT IN(('
                    . $db->getQuery(true)
                        ->select('DISTINCT ContactCampaigns.campaign_id')
                        ->from('#__jinbound_contacts_campaigns AS ContactCampaigns')
                        ->where('ContactCampaigns.contact_id = ' . $db->quote($contactId))
                    . '))'
                )
                ->where('Campaign.id IN(('
                    . $db->getQuery(true)
                        ->select('DISTINCT ContactStatuses.campaign_id')
                        ->from('#__jinbound_contacts_statuses AS ContactStatuses')
                        ->where('ContactStatuses.contact_id = ' . $db->quote($contactId))
                    . '))'
                );
        } // current campaigns
        else {
            $query->where('Campaign.id IN(('
                . $db->getQuery(true)
                    ->select('ContactCampaigns.campaign_id')
                    ->from('#__jinbound_contacts_campaigns AS ContactCampaigns')
                    ->where('ContactCampaigns.contact_id = ' . $db->quote($contactId))
                . '))'
            );
        }
        try {
            $campaigns = $db->setQuery($query)->loadObjectList();
        } catch (Exception $e) {
            $campaigns = array();
        }
        return $campaigns;
    }

    public static function getContactStatuses($contactId)
    {
        $db = JFactory::getDbo();
        try {
            $statuses = $db->setQuery($db->getQuery(true)
                ->select('ContactStatus.*, Status.name, Status.description')
                ->select(sprintf('IF(ContactStatus.created_by = 0, %s, User.name) AS created_by_name',
                    $db->quote('guest')))
                ->from('#__jinbound_contacts_statuses AS ContactStatus')
                ->leftJoin('#__jinbound_lead_statuses AS Status ON Status.id = ContactStatus.status_id')
                ->leftJoin('#__users AS User ON User.id = ContactStatus.created_by')
                ->where('ContactStatus.contact_id = ' . $db->quote($contactId))
                ->order('ContactStatus.created DESC')
            )->loadObjectList();
            if (empty($statuses)) {
                throw new Exception('empty');
            }
        } catch (Exception $e) {
            return array();
        }
        $list = array();
        foreach ($statuses as $status) {
            $key = $status->campaign_id;
            if (!array_key_exists($key, $list)) {
                $list[$key] = array();
            }
            $list[$key][] = $status;
        }
        return $list;
    }

    public static function getContactPriorities($contactId)
    {
        $db = JFactory::getDbo();
        try {
            $priorities = $db->setQuery($db->getQuery(true)
                ->select('ContactPriority.*, Priority.name, Priority.description')
                ->select(sprintf('IF(ContactPriority.created_by = 0, %s, User.name) AS created_by_name',
                    $db->quote('guest')))
                ->from('#__jinbound_contacts_priorities AS ContactPriority')
                ->leftJoin('#__jinbound_priorities AS Priority ON Priority.id = ContactPriority.priority_id')
                ->leftJoin('#__users AS User ON User.id = ContactPriority.created_by')
                ->where('ContactPriority.contact_id = ' . $db->quote($contactId))
                ->order('ContactPriority.created DESC')
            )->loadObjectList();
            if (empty($priorities)) {
                throw new Exception('empty');
            }
        } catch (Exception $e) {
            return array();
        }
        $list = array();
        foreach ($priorities as $priority) {
            $key = $priority->campaign_id;
            if (!array_key_exists($key, $list)) {
                $list[$key] = array();
            }
            $list[$key][] = $priority;
        }
        return $list;
    }
}
