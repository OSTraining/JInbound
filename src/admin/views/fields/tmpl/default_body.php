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

$user               = JFactory::getUser();
$userId             = $user->get('id');
$listOrder          = $this->state->get('list.ordering');
$listDirn           = $this->state->get('list.direction');
$saveOrder          = ($listOrder == 'Field.id');
$trashed            = (-2 == $this->state->get('filter.published'));
$core               = array('first_name', 'last_name', 'email');

if (JInboundHelper::version()->isCompatible('3.0')) {
    JHtml::_('dropdown.init');
}

if (!empty($this->items)) :
    foreach ($this->items as $i => $item):
        $isCore = in_array($item->name, $core);
        $canEdit    = $user->authorise('core.edit', 'com_jinbound.field.' . $item->id);
        $canCheckin = $user->authorise('core.manage',
                'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
        $canEditOwn = $user->authorise('core.edit.own',
                'com_jinbound.field.' . $item->id) && $item->created_by == $userId;
        $canChange  = $user->authorise('core.edit.state', 'com_jinbound.field.' . $item->id) && $canCheckin && !$isCore;
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="hidden-phone">
                <?php echo $isCore ? '&nbsp;' : JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="center hidden-phone">
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'fields.', $canChange, 'cb'); ?>
            </td>
            <td class="has-context">
                <div class="pull-left">
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->author_name, $item->checked_out_time,
                            'fields.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit || $canEditOwn) : ?>
                        <a href="<?php echo JInboundHelperUrl::_(array('task' => 'field.edit', 'id' => $item->id)); ?>">
                            <?php echo JInboundHelperFilter::escape($item->title); ?>
                        </a>
                    <?php else : ?>
                        <?php echo JInboundHelperFilter::escape($item->title); ?>
                    <?php endif; ?>
                </div>
                <?php if (JInboundHelper::version()->isCompatible('3.0')) : ?>
                    <div class="pull-left"><?php

                        JHtml::_('dropdown.edit', $item->id, 'field.');
                        if ($canChange || $item->checked_out) :
                            JHtml::_('dropdown.divider');
                            if ($canChange) :
                                JHtml::_('dropdown.' . ($item->published ? 'un' : '') . 'publish', 'cb' . $i,
                                    'fields.');
                            endif;
                            if ($item->checked_out) :
                                JHtml::_('dropdown.checkin', 'cb' . $i, 'fields.');
                            endif;
                            if ($canChange) :
                                JHtml::_('dropdown.' . ($trashed ? 'un' : '') . 'trash', 'cb' . $i, 'fields.');
                            endif;
                        endif;

                        echo JHtml::_('dropdown.render');

                        ?></div>
                <?php endif; ?>
            </td>
            <td>
                <?php echo JInboundHelperFilter::escape($item->name); ?>
            </td>
            <td>
                <?php echo JInboundHelperFilter::escape($item->type); ?>
            </td>
            <td class="nowrap hidden-phone hidden-tablet">
                <?php echo $item->id; ?>
            </td>
        </tr>
    <?php endforeach;
endif;
