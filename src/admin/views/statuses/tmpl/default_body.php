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
$saveOrder = ($listOrder == 'Status.ordering');
$trashed   = (-2 == $this->state->get('filter.published'));

if (JInboundHelper::version()->isCompatible('3.0')) {
    JHtml::_('dropdown.init');
}


if (!empty($this->items)) :
    foreach ($this->items as $i => $item) :
        $this->_itemNum = $i;

        $canCheckin = $user->authorise('core.manage',
                'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
        $canEdit    = $user->authorise('core.edit', JInboundHelper::COM . '.status') && $canCheckin;
        $canChange  = $user->authorise('core.edit.state', JInboundHelper::COM . '.status') && $canCheckin;
        $canEditOwn = $user->authorise('core.edit.own',
                JInboundHelper::COM . '.status') && $item->created_by == $userId && $canCheckin;
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="hidden-phone">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->id; ?>
            </td>
            <td class="nowrap has-context">
                <div class="pull-left">
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->author_name, $item->checked_out_time,
                            'statuses.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit || ($canEditOwn && $item->created_by == $user->id)) : ?>
                        <a href="<?php echo JInboundHelperUrl::edit('status', $item->id); ?>">
                            <?php echo $this->escape($item->name); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape($item->name); ?>
                    <?php endif; ?>
                </div>
                <?php $this->currentItem = $item;
                echo $this->loadTemplate('list_dropdown'); ?>
            </td>
            <td class="hidden-phone">
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'statuses.', $canChange, 'cb'); ?>
            </td>
            <td class="hidden-phone">
                <?php echo JHtml::_('jgrid.isdefault', $item->default, $i, 'statuses.', !$item->default && $canChange,
                    'cb'); ?>
            </td>
            <td class="hidden-phone">
                <?php echo JHtml::_('jinbound.isactive', $item->active, $i, 'statuses.', $canChange, 'cb'); ?>
            </td>
            <td class="hidden-phone">
                <?php echo JHtml::_('jinbound.isfinal', $item->final, $i, 'statuses.', $canChange, 'cb'); ?>
            </td>
            <td class="order">
                <?php if ($canChange) : ?>
                    <?php if ($saveOrder) : ?>
                        <span><?php echo $this->pagination->orderUpIcon($i, 0 == $i, 'statuses.orderup',
                                'JLIB_HTML_MOVE_UP', $item->ordering); ?></span>
                        <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, false,
                                'statuses.orderdown', 'JLIB_HTML_MOVE_DOWN', $item->ordering); ?></span>
                    <?php endif; ?>
                    <?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $item->ordering; ?>" <?php echo $disabled ?>
                           class="text-area-order input-mini"/>
                <?php else : ?>
                    <?php echo $item->ordering; ?>
                <?php endif; ?>
            </td>
            <td class="hidden-phone hidden-tablet">
                <?php echo $this->escape($item->description); ?>
            </td>
        </tr>
    <?php endforeach;
endif;
