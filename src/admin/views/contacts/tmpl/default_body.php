<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 */

defined('JPATH_PLATFORM') or die;
$config          = JFactory::getConfig();
$user            = JFactory::getUser();
$userId          = $user->get('id');
$listOrder       = $this->state->get('list.ordering');
$listDirn        = $this->state->get('list.direction');
$saveOrder       = ($listOrder == 'Conversion.id');
$trashed         = (-2 == $this->state->get('filter.published'));
$canEditCampaign = $user->authorise('core.edit', JInboundHelper::COM . '.campaign');

if (JInboundHelper::version()->isCompatible('3.0')) {
    JHtml::_('dropdown.init');
}

JHtml::_('jinbound.leadupdate');

if (!empty($this->items)) :
    foreach ($this->items as $i => $item) :
        $this->_itemNum = $i;

        // combine rows so we can keep the campaigns aligned
        $rowSpan = '';
        $rowsNum = count($item->campaigns);
        // 3.x template uses nth row for styling, so let's fake it up
        // more than one row? use a rowspan
        if (2 <= $rowsNum) {
            // double and subtract 1 to get the "number" of rows
            $rowsNum = (2 * $rowsNum) - 1;
            $rowSpan = ' rowspan="' . $rowsNum . '"';
        }
        $rowData = array();
        foreach ($item->campaigns as $campaign) {
            $rowData[] = array(
                'campaign' => $campaign
            ,
                'priority' => array_key_exists($campaign->id,
                    $item->priorities) ? $item->priorities[$campaign->id] : null
            ,
                'status'   => array_key_exists($campaign->id, $item->statuses) ? $item->statuses[$campaign->id] : null
            );
        }

        $firstRow = array_shift($rowData);

        $canCheckin = $user->authorise('core.manage',
                'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
        $canEdit    = $user->authorise('core.edit', JInboundHelper::COM . '.contact') && $canCheckin;
        $canChange  = $user->authorise('core.edit.state', JInboundHelper::COM . '.contact') && $canCheckin;
        $canEditOwn = $user->authorise('core.edit.own',
                JInboundHelper::COM . '.contact') && $item->created_by == $userId && $canCheckin;
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="hidden-phone"<?php echo $rowSpan; ?>>
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="nowrap has-context"<?php echo $rowSpan; ?>>
                <div class="pull-left">
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->full_name, $item->checked_out_time,
                            'contacts.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit || ($canEditOwn && $item->created_by == $user->id)) : ?>
                        <a href="<?php echo JInboundHelperUrl::edit('contact', $item->id); ?>">
                            <?php echo $this->escape($item->full_name); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape($item->full_name); ?>
                    <?php endif; ?>
                </div>
                <?php $this->currentItem = $item;
                echo $this->loadTemplate('list_dropdown'); ?>
            </td>
            <td class="nowrap"<?php echo $rowSpan; ?>>
                <a href="mailto:<?php echo $this->escape($item->email); ?>"><?php echo $this->escape($item->email); ?></a>
            </td>
            <td class="hidden-phone"<?php echo $rowSpan; ?>>
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'contacts.', $canChange, 'cb'); ?>
            </td>
            <td class="hidden-phone hidden-tablet"<?php echo $rowSpan; ?>>
                <?php echo JInboundHelper::userDate($item->created); ?>
            </td>
            <td class="hidden-phone">
                <?php echo empty($firstRow['priority'][0]) ? '' : JHtml::_('jinbound.priority', $item->id,
                    $firstRow['priority'][0]->priority_id, $firstRow['campaign']->id, 'contacts.', $canChange); ?>
            </td>
            <td class="nowrap hidden-phone">
                <?php if (is_object($firstRow['campaign'])) : ?><a
                    href="<?php echo JInboundHelperUrl::task('campaign.edit', false,
                        array('id' => $firstRow['campaign']->id)); ?>"><?php echo $this->escape($firstRow['campaign']->name); ?></a><?php endif; ?>
            </td>
            <td class="hidden-phone">
                <?php echo empty($firstRow['status'][0]) ? '' : JHtml::_('jinbound.status', $item->id,
                    $firstRow['status'][0]->status_id, $firstRow['campaign']->id, 'contacts.', $canChange); ?>
            </td>
            <td class="hidden-phone"<?php echo $rowSpan; ?>>
                <?php echo JHtml::_('jinbound.leadnotes', $item->id, $canChange); ?>
            </td>
            <td class="hidden-phone"<?php echo $rowSpan; ?>>
                <?php echo $item->id; ?>
            </td>
        </tr>
        <?php if (!empty($rowData)) : foreach ($rowData as $row) : ?>
        <tr>
            <td class="hidden-phone hidden-tablet hidden-desktop" colspan="3"><!--
			This is here for nth child css striping only
		--></td>
        </tr>
        <tr>
            <td class="hidden-phone">
                <?php echo empty($row['priority'][0]) ? '' : JHtml::_('jinbound.priority', $item->id,
                    $row['priority'][0]->priority_id, $row['campaign']->id, 'contacts.', $canChange); ?>
            </td>
            <td class="nowrap hidden-phone">
                <?php if ($canEditCampaign) : ?>
                    <a href="<?php echo JInboundHelperUrl::task('campaign.edit', false,
                        array('id' => $row['campaign']->id)); ?>"><?php echo $this->escape($row['campaign']->name); ?></a>
                <?php else : ?>
                    <?php echo $this->escape($row['campaign']->name); ?>
                <?php endif; ?>
            </td>
            <td class="hidden-phone">
                <?php echo empty($row['status'][0]) ? '' : JHtml::_('jinbound.status', $item->id,
                    $row['status'][0]->status_id, $row['campaign']->id, 'contacts.', $canChange); ?>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    <?php endforeach;
endif;
