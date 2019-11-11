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
$saveOrder = ($listOrder == 'Priority.ordering');
?>
<tr>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('searchtools.sort', '', 'Priority.ordering', $listDirn, $listOrder,
            null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
    </th>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th width="1%" class="nowrap center">
        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'Priority.published', $listDirn,
            $listOrder); ?>
    </th>
    <th class="title">
        <?php echo JHtml::_('searchtools.sort', 'COM_JINBOUND_NAME', 'Priority.name', $listDirn,
            $listOrder); ?>
    </th>
    <th width="25%" class="hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'COM_JINBOUND_DESCRIPTION',
            'Priority.description', $listDirn, $listOrder); ?>
    </th>
    <th width="1%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'Priority.id', $listDirn,
            $listOrder); ?>
    </th>
</tr>
