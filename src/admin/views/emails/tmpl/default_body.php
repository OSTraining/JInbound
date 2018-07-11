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

$user           = JFactory::getUser();
$userId         = $user->get('id');
$listOrder      = $this->state->get('list.ordering');
$listDirn       = $this->state->get('list.direction');
$saveOrder      = ($listOrder == 'Email.id');
$trashed        = (-2 == $this->state->get('filter.published'));
$colors         = array('success', 'warning', 'info');

if (JInboundHelper::version()->isCompatible('3.0')) {
    JHtml::_('dropdown.init');
}


if (!empty($this->items)) :
    $lastCampaign = false;
    $lastColor  = current($colors);
    foreach ($this->items as $i => $item) :
        $this->_itemNum = $i;
        $params = $item->params;
        if (!$params instanceof JRegistry) {
            $reg = new JRegistry();
            $reg->loadString($params);
            $params = $reg;
        }

        $canCheckin = $user->authorise('core.manage',
                'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
        $canEdit    = $user->authorise('core.edit', JInboundHelper::COM . '.email') && $canCheckin;
        $canChange  = $user->authorise('core.edit.state', JInboundHelper::COM . '.email') && $canCheckin;
        $canEditOwn = $user->authorise('core.edit.own',
                JInboundHelper::COM . '.email') && $item->created_by == $userId && $canCheckin;

        if ($lastCampaign !== $item->campaign_name) {
            $name = $this->escape($item->campaign_name);
            if (false === next($colors)) {
                reset($colors);
            }
        } else {
            $name = '&nbsp;';
        }
        $lastCampaign = $item->campaign_name;
        $lastColor    = current($colors);


        ?>
        <tr class="row<?php echo $i % 2; ?> <?php echo $lastColor; ?>">
            <td class="hidden-phone">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="">
                <?php echo $name; ?>
            </td>
            <td class="nowrap has-context">
                <div class="pull-left">
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->author_name, $item->checked_out_time,
                            'emails.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit || ($canEditOwn && $item->created_by == $user->id)) : ?>
                        <a href="<?php echo JInboundHelperUrl::edit('email', $item->id); ?>">
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
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'emails.', $canChange, 'cb'); ?>
            </td>
            <td class="nowrap">
                <?php echo JText::_('COM_JINBOUND_EMAIL_TYPE_' . $this->escape(strtoupper($item->type))); ?>
            </td>
            <td class="hidden-phone">
                <?php
                if ('report' === $item->type) {
                    echo $this->escape(ucwords(strtolower($params->get('reports_frequency', '1 WEEK'))));
                } else {
                    echo JText::sprintf('COM_JINBOUND_EMAIL_SCHEDULE', (int)$item->sendafter);
                }
                ?>
            </td>
            <td class="hidden-phone">
                <?php echo $item->id; ?>
            </td>
        </tr>
    <?php endforeach;
endif;
