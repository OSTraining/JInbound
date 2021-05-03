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

defined('JPATH_PLATFORM') or die;

abstract class JInboundHelperStatus
{
    public static function getDefaultStatus()
    {
        static $default;

        if (is_null($default)) {
            $db = JFactory::getDbo();

            $default = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from('#__jinbound_lead_statuses')
                    ->where(
                        array(
                            $db->quoteName('default') . ' = 1',
                            $db->quoteName('published') . ' = 1'
                        )
                    )
            )->loadResult();

            if (is_null($default)) {
                $default = false;
            }
        }

        return $default;
    }

    public static function getFinalStatus()
    {
        static $final;

        if (is_null($final)) {
            $db = JFactory::getDbo();

            $final = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from('#__jinbound_lead_statuses')
                    ->where(
                        array(
                            $db->quoteName('final') . ' = 1',
                            $db->quoteName('published') . ' = 1'
                        )
                    )
            )->loadResult();

            if (is_null($final)) {
                $final = false;
            }
        }

        return $final;
    }

    public static function setContactStatusForCampaign($status_id, $contact_id, $campaign_id, $user_id = null)
    {
        $dispatcher = JEventDispatcher::getInstance();
        $db         = JFactory::getDbo();

        // before we save the status, check if this user has this status already - don't duplicate the last status!
        $current = $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('status_id'))
                ->from('#__jinbound_contacts_statuses')
                ->where(
                    array(
                        $db->quoteName('campaign_id') . '=' . $db->quote($campaign_id),
                        $db->quoteName('contact_id') . '=' . $db->quote($contact_id)
                    )
                )
                ->order($db->quoteName('created') . ' DESC')
        )->loadResult();

        if ((int)$current === (int)$status_id) {
            return true;
        }

        $insertObject = (object)array(
            'status_id'   => $status_id,
            'campaign_id' => $campaign_id,
            'contact_id'  => $contact_id,
            'created'     => JFactory::getDate()->toSql(),
            'created_by'  => JFactory::getUser($user_id)->get('id')
        );
        $success      = $db->insertObject('#__jinbound_contacts_statuses', $insertObject);

        $result = $dispatcher->trigger(
            'onJInboundChangeState',
            array(
                'com_jinbound.contact.status',
                $campaign_id,
                array($contact_id),
                $status_id
            )
        );

        return $success && !in_array(false, $result, true);
    }


    public static function getSelectOptions($final = false)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id AS value, name AS text')
            ->from('#__jinbound_lead_statuses')
            ->where('published = 1')
            ->order('ordering ASC');

        if ($final) {
            $query->where('final = 1');
        }

        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (Exception $e) {
            $options = array();
        }

        return $options;
    }
}
