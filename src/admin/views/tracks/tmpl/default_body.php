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

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

if (!empty($this->items)) :
    foreach ($this->items as $i => $item) :
        $this->_itemNum = $i;
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="nowrap">
                <?php echo $this->escape($item->cookie); ?>
            </td>
            <td class="hidden-phone hidden-tablet">
                <?php echo $this->escape($item->detected_user_id); ?>
            </td>
            <td class="hidden-phone hidden-tablet">
                <?php echo $this->escape($item->current_user_id); ?>
            </td>
            <td class="hidden-phone hidden-tablet">
                <?php echo $this->escape($item->user_agent); ?>
            </td>
            <td class="hidden-phone hidden-tablet">
                <?php echo $this->escape($item->created); ?>
            </td>
            <td class="nowrap">
                <?php echo $this->escape($item->ip); ?>
            </td>
            <td class="hidden-phone">
                <?php echo $this->escape($item->session_id); ?>
            </td>
            <td class="hidden-phone">
                <?php echo $this->escape($item->type); ?>
            </td>
            <td class="nowrap hidden-phone">
                <?php echo $this->escape($item->url); ?>
            </td>
            <td class="hidden-phone">
                <?php echo $this->escape($item->id); ?>
            </td>
        </tr>
    <?php endforeach;
endif;
