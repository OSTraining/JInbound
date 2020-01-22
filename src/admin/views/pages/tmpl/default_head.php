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

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

?>
<tr>
    <th width="1%" class="nowrap hidden-phone">
        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
    </th>
    <th width="1%" class="nowrap hidden-phone">
        <?php echo JText::_('COM_JINBOUND_ID'); ?>
    </th>
    <th style="min-width:24px;">
        &nbsp;
    </th>
    <th>
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_LANDINGPAGE_NAME', 'Page.name', $listDirn,
            $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_PUBLISHED', 'Page.published', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CAMPAIGN', 'campaign_name', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CATEGORY', 'Page.category', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_LAYOUT', 'Page.layout', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_VIEWS', 'Page.hits', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_SUBMISSIONS', 'submissions', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_LEADS', 'contact_submissions', $listDirn, $listOrder); ?>
    </th>
    <th class="hidden-phone">
        <?php echo JHtml::_($this->sortFunction, 'COM_JINBOUND_CONVERSIONS', 'conversions', $listDirn, $listOrder); ?>
    </th>
</tr>
