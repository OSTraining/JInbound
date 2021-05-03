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

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

?>
<tr>
    <th width="1%" class="nowrap hidden-phone">
        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
    </th>
    <th>
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_NAME', 'full_name', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_EMAIL', 'Contact.email', $listDirn, $listOrder); ?>
    </th>
    <th width="5%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_PUBLISHED', 'Contact.published', $listDirn,
            $listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_LEAD_DATE', 'Contact.created', $listDirn, $listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_LEAD_PRIORITY', 'Priority.name', $listDirn,
            $listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CAMPAIGN', 'Campaign.name', $listDirn, $listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_LEAD_STATUS', 'Status.name', $listDirn, $listOrder); ?>
    </th>
    <th width="10%">
        <?php echo JText::_('COM_JINBOUND_LEAD_NOTE'); ?>
    </th>
    <th width="1%" class="nowrap hidden-phone">
        <?php echo JText::_('COM_JINBOUND_ID'); ?>
    </th>
</tr>
