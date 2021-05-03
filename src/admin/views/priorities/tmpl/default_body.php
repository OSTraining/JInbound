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

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = ($listOrder == 'Priority.ordering' && strtolower($listDirn) == 'asc');
$trashed   = ($this->state->get('filter.published') == -2);
$ordering  = ($listOrder == 'Priority.ordering');

$canCreate  = $user->authorise('core.create', 'com_jinbound');
$canEdit    = $user->authorise('core.edit', 'com_jinbound');
$canCheckin = $user->authorise('core.manage', 'com_checkin')
    || $item->checked_out == $user->get('id')
    || $item->checked_out == 0;
$canChange  = $user->authorise('core.edit.state', 'com_jinbound') && $canCheckin;
$canOrder   = $user->authorise('core.edit.state', 'com_jinbound.priority');

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_jinbound&task=priorities.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'adminlist', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}


if (!empty($this->items)) :
    foreach ($this->items as $i => $item) :
        $orderkey = array_search($item->id, $this->ordering[0]);
        ?>
        <tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id ?>">
            <td class="order nowrap center hidden-phone">
                <?php
                $iconClass = '';
                if (!$canChange) :
                    $iconClass = ' inactive';
                elseif (!$saveOrder) :
                    $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                endif;
                ?>
                <span class="sortable-handler<?php echo $iconClass ?>">
                    <i class="icon-menu"></i>
                </span>
                <?php
                if ($canChange && $saveOrder) :
                    ?>
                    <input type="text"
                           style="display:none"
                           name="order[]"
                           size="5"
                           value="<?php echo $orderkey + 1; ?>"/>
                <?php
                endif;
                ?>
            </td>

            <td class="center hidden-phone">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="center">
                <?php
                echo JHtml::_(
                    'jgrid.published',
                    $item->published,
                    $i,
                    'priorities.',
                    $canChange,
                    'cb'
                );
                ?>
            </td>

            <td>
                <?php
                if ($item->checked_out) :
                    echo JHtml::_(
                        'jgrid.checkedout',
                        $i,
                        $item->editor,
                        $item->checked_out_time,
                        'items.',
                        $canCheckin
                    );
                endif;
                if ($canEdit) :
                    echo JHtml::_(
                        'link',
                        JRoute::_('index.php?option=com_jinbound&task=priority.edit&id=' . (int)$item->id),
                        $this->escape($item->name)
                    );
                else :
                    echo $this->escape($item->name);
                endif;
                ?>
            </td>

            <td class="hidden-phone">
                <span class="small"><?php echo nl2br($this->escape($item->description)); ?></span>
            </td>

            <td class="center hidden-phone">
                <?php echo (int)$item->id; ?>
            </td>
        </tr>
    <?php
    endforeach;
endif;
