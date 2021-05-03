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

$trashed  = (-2 == $this->state->get('filter.published'));
$itemName = JInboundInflector::singularize($this->getName());
$listName = JInboundInflector::pluralize($this->getName());

$user       = JFactory::getUser();
$userId     = $user->get('id');
$canCheckin = $user->authorise('core.manage',
        'com_checkin') || $this->currentItem->checked_out == $userId || $this->currentItem->checked_out == 0;
$canEdit    = $user->authorise('core.edit', JInboundHelper::COM . ".$itemName") && $canCheckin;
$canChange  = $user->authorise('core.edit.state', JInboundHelper::COM . ".$itemName") && $canCheckin;
$canEditOwn = $user->authorise('core.edit.own',
        JInboundHelper::COM . ".$itemName") && $this->currentItem->created_by == $userId && $canCheckin;

if (JInboundHelper::version()->isCompatible('3.0') && ($canEdit || $canEditOwn || $canChange)) : ?>
    <div class="pull-left">
        <?php
        if ($canEdit || $canEditOwn) {
            JHtml::_('dropdown.edit', $this->currentItem->id, $itemName . '.');
            JHtml::_('dropdown.divider');
        }
        if ($canChange) {
            JHtml::_('dropdown.' . ($this->currentItem->published ? 'un' : '') . 'publish', 'cb' . $this->_itemNum,
                $listName . '.');
        }
        if ($canCheckin && $this->currentItem->checked_out) {
            JHtml::_('dropdown.checkin', 'cb' . $this->_itemNum, $listName . '.');
        }
        if ($canChange) {
            JHtml::_('dropdown.' . ($trashed ? 'un' : '') . 'trash', 'cb' . $this->_itemNum, $listName . '.');
        }

        echo JHtml::_('dropdown.render');

        ?>
    </div>
<?php

endif;
