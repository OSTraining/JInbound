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

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = ($listOrder == 'Email.id');
?>
<tr>
    <th width="1%" class="nowrap hidden-phone">
        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
    </th>
    <th width="15%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CAMPAIGN_NAME', 'Campaign.name', $listDirn,
            $listOrder); ?>
    </th>
    <th width="15%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_EMAIL_NAME', 'Email.name', $listDirn, $listOrder); ?>
    </th>
    <th width="15%" class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_EMAIL_SUBJECT', 'Campaign.subject', $listDirn,
            $listOrder); ?>
    </th>
    <th width="5%" class="nowrap hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CAMPAIGN_ACTIVE', 'Email.published', $listDirn,
            $listOrder); ?>
    </th>
    <th width="5%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_EMAIL_TYPE', 'Email.type', $listDirn, $listOrder); ?>
    </th>
    <th width="5%" class="nowrap hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CAMPAIGN_SCHEDULE', 'Email.sendafter', $listDirn,
            $listOrder); ?>
    </th>
    <th width="1%" class="nowrap hidden-phone">
        <?php echo JText::_('COM_JINBOUND_ID'); ?>
    </th>
</tr>
