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

$user       = JFactory::getUser();
$userId     = $user->get('id');
$context    = 'com_jinbound.contact.' . $this->item->id;
$canEdit    = $user->authorise('core.edit', $context);
$canCheckin = $user->authorise('core.manage',
        'com_checkin') || $this->item->checked_out == $userId || $this->item->checked_out == 0;
$canEditOwn = $user->authorise('core.edit.own', $context) && $this->item->created_by == $userId;
$canChange  = $user->authorise('core.edit.state', $context) && $canCheckin;

?>
<fieldset class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <h4><?php echo JText::_('COM_JINBOUND_CURRENT_LEAD_NURTURING_CAMPAIGNS'); ?></h4>
            <div class="well">
                <?php if (empty($this->item->campaigns)) : ?>
                    <div class="alert alert-error"><?php echo JText::_('COM_JINBOUND_NO_CAMPAIGNS'); ?></div>
                <?php else : ?>
                    <?php foreach ($this->item->campaigns as $i => $campaign) : ?>
                        <h3><?php echo $this->escape($campaign->name); ?></h3>
                        <div class="row-fluid current-priority current-priority-<?php echo $campaign->id; ?>">
                            <div class="span6"><?php echo JText::_('COM_JINBOUND_CURRENT_PRIORITY'); ?></div>
                            <div class="span5"><?php
                                echo JHtml::_('jinbound.priority', $this->item->id,
                                    $this->item->priorities[$campaign->id][0]->priority_id, $campaign->id, 'contacts.',
                                    $canChange);
                                ?></div>
                        </div>
                        <div class="row-fluid current-status current-status-<?php echo $campaign->id; ?>">
                            <div class="span6"><?php echo JText::_('COM_JINBOUND_CURRENT_STATUS'); ?></div>
                            <div class="span5"><?php
                                echo JHtml::_('jinbound.status', $this->item->id,
                                    $this->item->statuses[$campaign->id][0]->status_id, $campaign->id, 'contacts.',
                                    $canChange);
                                ?></div>
                        </div>
                        <div class="row-fluid">
                            <div class="span12 current-statuses current-statuses-<?php echo $campaign->id; ?>">
                                <?php foreach ($this->item->statuses[$campaign->id] as $status) : ?>
                                    <div class="row-fluid">
                                        <div class="span4 status-name"><?php echo $this->escape($status->name); ?></div>
                                        <div
                                            class="span3 status-date"><?php echo JInboundHelper::userDate($status->created); ?></div>
                                        <div
                                            class="span4 status-author"><?php echo $this->escape($status->created_by_name); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php
                        $this->_currentCampaignId = $campaign->id;
                        echo $this->loadTemplate('edit_tabs_page_campaigns_emails');
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="row-fluid">
                    <div
                        class="alert alert-error"><?php echo JText::_('COM_JINBOUND_WARNING_GET_PERMISSION_BEFORE_ADDING_TO_CAMPAIGN'); ?></div>
                </div>
                <div class="row-fluid">
                    <div class="span12">
                        <?php
                        $this->_currentFieldset = $this->form->getFieldset('campaigns');
                        echo $this->loadTemplate('edit_fields');
                        ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($this->item->previous_campaigns)) : ?>

                <h4><?php echo JText::_('COM_JINBOUND_PREVIOUS_LEAD_NURTURING_CAMPAIGNS'); ?></h4>
                <div class="well">
                    <?php foreach ($this->item->previous_campaigns as $i => $campaign) : ?>
                        <h3><?php echo $this->escape($campaign->name); ?></h3>
                        <?php foreach ($this->item->statuses[$campaign->id] as $status) : ?>
                            <div class="row-fluid">
                                <div class="span6"><?php echo $this->escape($status->name); ?></div>
                                <div class="span3"><?php echo JInboundHelper::userDate($status->created); ?></div>
                                <div class="span2"><?php echo $this->escape($status->created_by); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php
                        $this->_currentCampaignId = $campaign->id;
                        echo $this->loadTemplate('edit_tabs_page_campaigns_emails');
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</fieldset>
