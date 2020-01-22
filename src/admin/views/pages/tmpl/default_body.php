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

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = ($listOrder == 'Page.id');
$trashed   = (-2 == $this->state->get('filter.published'));

if (JInboundHelper::version()->isCompatible('3.0')) {
    JHtml::_('dropdown.init');
}


if (!empty($this->items)) :
    foreach ($this->items as $i => $item) :
        $this->_itemNum = $i;

        $canCheckin = $user->authorise('core.manage',
                'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
        $canEdit    = $user->authorise('core.edit', JInboundHelper::COM . '.page') && $canCheckin;
        $canChange  = $user->authorise('core.edit.state', JInboundHelper::COM . '.page') && $canCheckin;
        $canEditOwn = $user->authorise('core.edit.own',
                JInboundHelper::COM . '.page') && $item->created_by == $userId && $canCheckin;
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="hidden-phone">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->id; ?>
            </td>
            <td id="landingpagelink_<?php echo $item->id; ?>">
            </td>
            <td class="nowrap has-context">
                <div class="pull-left">
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->author_name, $item->checked_out_time,
                            'pages.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit || ($canEditOwn && $item->created_by == $user->id)) : ?>
                        <a href="<?php echo JInboundHelperUrl::edit('page', $item->id); ?>">
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
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'pages.', $canChange, 'cb'); ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->campaign_name; ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->category_name; ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->layout; ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->hits; ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->submissions; ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->contact_submissions; ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->conversions; ?>
            </td>
        </tr>
    <?php endforeach;
endif;
