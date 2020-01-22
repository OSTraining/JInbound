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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class JInboundControllerContact extends JInboundFormController
{
    /**
     * @param string $key
     * @param string $urlVar
     *
     * @return bool
     * @throws Exception
     */
    public function edit($key = 'id', $urlVar = 'id')
    {
        if (!JFactory::getUser()->authorise('core.manage', 'com_jinbound.contact')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        return parent::edit($key, $urlVar);
    }

    public function status()
    {
        $this->changeContact('status');
    }

    /**
     * @param string $how
     *
     * @throws Exception
     */
    protected function changeContact($how)
    {
        $app      = JFactory::getApplication();
        $id       = $app->input->get('id');
        $campaign = $app->input->get('campaign_id');
        $value    = $app->input->get('value');
        $model    = $this->getModel();
        $model->$how($id, $campaign, $value);
    }

    public function priority()
    {
        $this->changeContact('priority');
    }

    /**
     * @param JModelLegacy $model
     * @param array        $validData
     */
    protected function postSaveHook(JModelLegacy $model, $validData = array())
    {
        // only operate on valid records
        $contact = (int)$model->getState('contact.id');
        if ($contact) {
            // clear this contact's campaigns
            $db = JFactory::getDbo();
            $db->setQuery(
                $db->getQuery(true)
                    ->delete('#__jinbound_contacts_campaigns')
                    ->where('contact_id = ' . $db->quote($contact))
            )
                ->execute();

            // Get a list of all active campaign ID's
            $query         = $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_campaigns')
                ->where('published = 1');
            $all_campaigns = $db->setQuery($query)->loadColumn();

            // ensure campaigns is an array
            $campaigns = is_array($validData['_campaigns'])
                ? $validData['_campaigns']
                : (empty($validData['_campaigns']) ? array() : array($validData['_campaigns']));
            ArrayHelper::toInteger($campaigns);

            // Get a list of active campaign ID's to which the lead does not belong
            //  either because the lead never belonged to that campaign or was removed
            //  from the campaign.
            $removed_campaigns = array_diff($all_campaigns, $campaigns);

            // re-add to the desired campaigns
            if (!empty($campaigns)) {
                $query = $db->getQuery(true)
                    ->insert('#__jinbound_contacts_campaigns')
                    ->columns(array('contact_id', 'campaign_id'));
                foreach ($campaigns as $campaign) {
                    $query->values($contact . ',' . $campaign);
                }

                $db->setQuery($query)->execute();

                // find campaigns this contact has no status for yet
                $new_campaigns = $db->setQuery(
                    $db->getQuery(true)
                        ->select('campaign_id')
                        ->from('#__jinbound_contacts_campaigns')
                        ->where(
                            sprintf(
                                'campaign_id NOT IN (%s)',
                                $db->getQuery(true)
                                    ->select('DISTINCT campaign_id')
                                    ->from('#__jinbound_contacts_statuses')
                                    ->where('contact_id = ' . $contact)
                            )
                        )
                )->loadColumn();

                if (!empty($new_campaigns)) {
                    // this user does not have a status for these campaigns - add a default status for each
                    $status_id = JInboundHelperStatus::getDefaultStatus();
                    sort($new_campaigns);

                    foreach (array_unique($new_campaigns) as $new_campaign) {
                        JInboundHelperStatus::setContactStatusForCampaign($status_id, $contact, $new_campaign);
                    }
                }

                // find campaigns this contact has no priority for yet
                $new_campaigns = $db->setQuery(
                    $db->getQuery(true)
                        ->select('campaign_id')
                        ->from('#__jinbound_contacts_campaigns')
                        ->where(
                            sprintf(
                                'campaign_id NOT IN (%s)',
                                $db->getQuery(true)
                                    ->select('DISTINCT campaign_id')
                                    ->from('#__jinbound_contacts_priorities')
                                    ->where('contact_id = ' . $contact)
                            )
                        )
                )
                    ->loadColumn();

                // this user does not have a status for these campaigns - add a default status for each
                if (!empty($new_campaigns)) {
                    // the default status
                    $priority_id = JInboundHelperPriority::getDefaultPriority();
                    sort($new_campaigns);

                    foreach (array_unique($new_campaigns) as $new_campaign) {
                        JInboundHelperPriority::setContactPriorityForCampaign($priority_id, $contact, $new_campaign);
                    }
                }

                // Get contact information and create a formdata array
                $contact_info = $model->getItem($contact);
                $formdata     = array(
                    'lead' => array(
                        'first_name' => $contact_info->first_name,
                        'last_name'  => $contact_info->last_name,
                        'email'      => $contact_info->email
                    )
                );

                // Get a list of active pages associated with each added campaign
                //  along with a list of conversions associated with those pages
                foreach ($campaigns as $campaign) {
                    $pages = $this->getPages($campaign);

                    $pageIds = array();
                    foreach ($pages as $page) {
                        $pageIds[] = $page->id;
                    }

                    $conversions = $this->getConversions($contact, $pageIds);

                    if (empty($conversions)) {
                        // No conversions, create one
                        if (!empty($pages)) {
                            // We can only create a valid conversion record if there is
                            //  a page associated with this campaign

                            $page_id = $pages[0]->id;
                            $form_id = $pages[0]->formid;

                            // Get the current date/time to store with conversion record for created
                            $now = "'" . JFactory::getDate() . "'";

                            // Get a base date/time to store for modified and checked_out_time
                            $never = "'0000-00-00 00:00:00'";

                            // Insert a new conversion record
                            $insertConversion = (object)array(
                                'page_id'    => $page_id,
                                'contact_id' => $contact,
                                'published'  => 1,
                                'created'    => $now,
                                'created_by' => JFactory::getUser()->id,
                                'formdata'   => json_encode($formdata)
                            );
                            $db->insertObject('#__jinbound_conversions', $insertConversion);
                        }
                    }
                }
            }

            if (!empty($removed_campaigns)) {
                /*
                 * There are removed campaigns, we need to also remove their conversions if they exist
                 * Get a list of active pages associated with each added campaign
                 * along with a list of conversions associated with those pages
                 */
                foreach ($removed_campaigns as $campaign) {
                    $pages = $this->getPages($campaign);

                    $pageIds = array();
                    foreach ($pages as $page) {
                        $pageIds[] = $page->id;
                    }

                    $conversions = $this->getConversions($contact, $pageIds);

                    if (!empty($conversions)) {
                        // Remove the conversions associated with the removed campaigns
                        $db->setQuery(
                            $db->getQuery(true)
                                ->delete('#__jinbound_conversions')
                                ->where(
                                    sprintf(
                                        'id IN (%s)',
                                        implode(',', $conversions)
                                    )
                                )
                        )->execute();
                    }
                }
            }
        }
    }

    /**
     * @param int $campaign
     *
     * @return object[]
     */
    protected function getPages($campaign)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id, formid')
            ->from('#__jinbound_pages')
            ->where(
                array(
                    'campaign = ' . (int)$campaign,
                    'published = 1'
                )
            );
        $pages = $db->setQuery($query)->loadObjectList();

        return $pages;
    }

    /**
     * @param int   $contact
     * @param int[] $pageIds
     *
     * @return int[]
     */
    protected function getConversions($contact, array $pageIds)
    {
        $db = JFactory::getDbo();

        if ($pageIds) {
            $pageIds     = array_filter(array_map('intval', $pageIds));
            $query       = $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_conversions')
                ->where(
                    array(
                        'contact_id = ' . (int)$contact,
                        sprintf('page_id IN (%s)', implode(',', $pageIds))
                    )
                );
            $conversions = $db->setQuery($query)->loadColumn();
        }

        return empty($conversions) ? $conversions : array();
    }
}
