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

abstract class JInboundHelperPriority
{
    public static function getDefaultPriority()
    {
        static $default;

        if (is_null($default)) {
            $db = JFactory::getDbo();

            $default = $db->setQuery($db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from('#__jinbound_priorities')
                ->order($db->quoteName('ordering'))
            )->loadResult();

            if (is_null($default)) {
                $default = false;
            }
        }

        return $default;
    }

    public static function setContactPriorityForCampaign($priority_id, $contact_id, $campaign_id, $user_id = null)
    {
        $db   = JFactory::getDbo();
        $date = JFactory::getDate()->toSql();
        // save the status
        return $db->setQuery($db->getQuery(true)
            ->insert('#__jinbound_contacts_priorities')
            ->columns(array(
                'priority_id'
            ,
                'campaign_id'
            ,
                'contact_id'
            ,
                'created'
            ,
                'created_by'
            ))
            ->values($db->quote($priority_id)
                . ', ' . $db->quote($campaign_id)
                . ', ' . $db->quote($contact_id)
                . ', ' . $db->quote($date)
                . ', ' . $db->quote(JFactory::getUser($user_id)->get('id'))
            )
        )->query();
    }

    public static function getSelectOptions()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id AS value, name AS text')
            ->from('#__jinbound_priorities')
            ->where('published = 1')
            ->order('ordering ASC');

        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (Exception $e) {
            $options = array();
        }

        return $options;
    }
}
