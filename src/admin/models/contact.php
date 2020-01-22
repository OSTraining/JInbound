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

JPluginHelper::importPlugin('content');
JPluginHelper::importPlugin('jinbound');

/**
 * This models supports retrieving a contact.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelContact extends JInboundAdminModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.contact';

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            $this->option . '.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if (empty($form)) {
            return false;
        }
        $campaigns = JInboundHelperContact::getContactCampaigns($form->getValue('id'));
        $value     = array();

        if (is_array($campaigns) && !empty($campaigns)) {
            foreach ($campaigns as $campaign) {
                $value[] = $campaign->id;
            }
        }

        $form->setValue('_campaigns', null, $value);
        // check published permissions
        if (!JFactory::getUser()->authorise('core.edit.state', 'com_jinbound.contact')) {
            $form->setFieldAttribute('published', 'readonly', 'true');
        }
        // return the form
        return $form;
    }

    /**
     * set the lead status details for an item
     *
     * @param int $contact_id
     * @param int $campaign_id
     * @param int $status_id
     *
     * @return mixed
     */
    public function status($contact_id, $campaign_id, $status_id, $creator = null)
    {
        return JInboundHelperStatus::setContactStatusForCampaign($status_id, $contact_id, $campaign_id, $creator);
    }

    /**
     * @param int $contact_id
     * @param int $campaign_id
     * @param int $priority_id
     * @param int $creator
     *
     * @return bool
     */
    public function priority($contact_id, $campaign_id, $priority_id, $creator = null)
    {
        $dispatcher = JEventDispatcher::getInstance();

        $db = JFactory::getDbo();

        $insertObject = (object)array(
            'priority_id' => $priority_id,
            'campaign_id' => $campaign_id,
            'contact_id'  => $contact_id,
            'created'     => JFactory::getDate()->toSql(),
            'created_by'  => JFactory::getUser($creator)->id
        );

        $return = $db->insertObject('#__jinbound_contacts_priorities', $insertObject);

        $result = $dispatcher->trigger(
            'onJInboundChangeState',
            array(
                'com_jinbound.contact.priority',
                $campaign_id,
                array($contact_id),
                $priority_id
            )
        );

        if (is_array($result) && in_array(false, $result, true)) {
            return false;
        }

        return $return;
    }

    /**
     * @param int $id
     *
     * @return object[]
     */
    public function getNotes($id = null)
    {
        if (property_exists($this, 'item') && $this->item && $this->item->id == $id) {
            $item = $this->item;

        } else {
            $item = $this->getItem($id);
        }

        $db = JFactory::getDbo();

        try {
            $notes = $db->setQuery(
                $db->getQuery(true)
                    ->select('Note.id, Note.created, Note.text, User.name AS author')
                    ->from('#__jinbound_notes AS Note')
                    ->leftJoin('#__users AS User ON User.id = Note.created_by')
                    ->where(
                        array(
                            'Note.published = 1',
                            'Note.lead_id = ' . (int)$item->id
                        )
                    )
                    ->group('Note.id')
            )->loadObjectList();

        } catch (Exception $e) {
            // ignore
        }

        return (array)$notes;
    }

    /**
     * @param int $id
     *
     * @return bool|JObject
     */
    public function getItem($id = null)
    {
        $item = parent::getItem($id);
        $db   = JFactory::getDbo();

        $item->campaigns          = array();
        $item->conversions        = array();
        $item->emails             = array();
        $item->previous_campaigns = array();
        $item->priorities         = array();
        $item->statuses           = array();

        if ($item->id) {
            $item->campaigns          = JInboundHelperContact::getContactCampaigns($item->id);
            $item->conversions        = JInboundHelperContact::getContactConversions($item->id);
            $item->emails             = JInboundHelperContact::getContactEmails($item->id);
            $item->previous_campaigns = JInboundHelperContact::getContactCampaigns($item->id, true);
            $item->priorities         = JInboundHelperContact::getContactPriorities($item->id);
            $item->statuses           = JInboundHelperContact::getContactStatuses($item->id);
        }

        // add tracks
        try {
            $item->tracks = $db->setQuery(
                $db->getQuery(true)
                    ->select('Track.*')
                    ->from('#__jinbound_tracks AS Track')
                    ->where('Track.cookie = ' . $db->quote($item->cookie))
                    ->order('Track.created DESC')
            )->loadObjectList();

        } catch (Exception $e) {
            $item->tracks = array();
        }

        return $item;
    }
}
