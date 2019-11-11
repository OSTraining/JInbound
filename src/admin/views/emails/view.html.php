<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
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

require_once JINB_ADMIN . '/models/pages.php';

class JInboundViewEmails extends JInboundListView
{
    function display($tpl = null, $safeparams = false)
    {
        $model     = new JInboundModelPages(array());
        $campaigns = $model->getCampaignsOptions();
        // if we don't have any categories yet, warn the user
        // there's always going to be one option in this list
        if (1 >= count($campaigns)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_JINBOUND_NO_CAMPAIGNS_YET'), 'warning');
        }
        // set advice text
        $this->adviceText = JText::_('COM_JINBOUND_LEAD_MANAGER_RANDOM_ADVICE_' . rand(1, 5));

        // set filters
        $filter = (array)$this->app->getUserStateFromRequest($this->get('State')->get('context') . '.filter', 'filter',
            array(), 'array');
        $this->addFilter(JText::_('COM_JINBOUND_SELECT_STATUS'), 'filter[status]', $this->get('StatusOptions'),
            array_key_exists('status', $filter) ? $filter['status'] : '', false);

        // display
        return parent::display($tpl, $safeparams);
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'Campaign.name'   => JText::_('COM_JINBOUND_CAMPAIGN_NAME')
        ,
            'Email.name'      => JText::_('COM_JINBOUND_EMAIL_NAME')
        ,
            'Email.published' => JText::_('COM_JINBOUND_CAMPAIGN_ACTIVE')
        ,
            'Email.sendafter' => JText::_('COM_JINBOUND_CAMPAIGN_SCHEDULE')
        ,
            'Email.subject' => JText::_('COM_JINBOUND_EMAIL_SUBJECT')
        );
    }
}
