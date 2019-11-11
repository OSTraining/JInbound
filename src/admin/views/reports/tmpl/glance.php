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

$show_link = ('reports' == JFactory::getApplication()->input->get('view'));

?>
<div id="jinbound-reports-glance" class="row-fluid">
    <!-- visits -->
    <div class="span2 text-center">
        <h3 id="jinbound-reports-glance-views"><?php echo $this->getVisitCount(); ?></h3>
        <?php if ($show_link) : ?>
            <a href="<?php echo JInboundHelperUrl::view('reports', false, array(
                'layout'       => 'chart',
                'filter_chart' => 'views'
            )) ?>"><?php echo JText::_('COM_JINBOUND_LANDING_PAGE_VIEWS'); ?></a>
        <?php else : ?>
            <span><?php echo JText::_('COM_JINBOUND_LANDING_PAGE_VIEWS'); ?></span>
        <?php endif; ?>
    </div>
    <!-- arrow -->
    <div class="span1 text-center">
        <h3><img src="<?php echo JInboundHelperUrl::media() . '/images/summary_arrows.png'; ?>"/></h3>
    </div>
    <!-- leads -->
    <div class="span1 text-center">
        <h3 id="jinbound-reports-glance-leads"><?php echo $this->getLeadCount(); ?></h3>
        <?php if ($show_link) : ?>
            <a href="<?php echo JInboundHelperUrl::view('reports', false, array(
                'layout'       => 'chart',
                'filter_chart' => 'leads'
            )) ?>"><?php echo JText::_('COM_JINBOUND_LEADS'); ?></a>
        <?php else : ?>
            <span><?php echo JText::_('COM_JINBOUND_LEADS'); ?></span>
        <?php endif; ?>
    </div>
    <!-- arrow -->
    <div class="span1 text-center">
        <h3><img src="<?php echo JInboundHelperUrl::media() . '/images/summary_arrows.png'; ?>"/></h3>
    </div>
    <!-- views to leads -->
    <div class="span2 text-center">
        <h3 id="jinbound-reports-glance-views-to-leads"><?php echo $this->getViewsToLeads(); ?> %</h3>
        <?php if ($show_link) : ?>
            <a href="<?php echo JInboundHelperUrl::view('reports', false, array(
                'layout'       => 'chart',
                'filter_chart' => 'viewstoleads'
            )) ?>"><?php echo JText::_('COM_JINBOUND_VIEWS_TO_LEADS'); ?></a>
        <?php else : ?>
            <span><?php echo JText::_('COM_JINBOUND_VIEWS_TO_LEADS'); ?></span>
        <?php endif; ?>
    </div>
    <!-- arrow -->
    <div class="span1 text-center">
        <h3><img src="<?php echo JInboundHelperUrl::media() . '/images/summary_arrows.png'; ?>"/></h3>
    </div>
    <!-- customers -->
    <div class="span1 text-center">
        <h3 id="jinbound-reports-glance-conversion-count"><?php echo $this->getConversionCount(); ?></h3>
        <?php if ($show_link) : ?>
            <a href="<?php echo JInboundHelperUrl::view('reports', false, array(
                'layout'       => 'chart',
                'filter_chart' => 'conversioncount'
            )) ?>"><?php echo JText::_('COM_JINBOUND_GOAL_COMPLETIONS'); ?></a>
        <?php else : ?>
            <span><?php echo JText::_('COM_JINBOUND_GOAL_COMPLETIONS'); ?></span>
        <?php endif; ?>
    </div>
    <!-- arrow -->
    <div class="span1 text-center">
        <h3><img src="<?php echo JInboundHelperUrl::media() . '/images/summary_arrows.png'; ?>"/></h3>
    </div>
    <!-- conversions -->
    <div class="span2 text-center">
        <h3 id="jinbound-reports-glance-conversion-rate"><?php echo $this->getConversionRate(); ?> %</h3>
        <?php if ($show_link) : ?>
            <a href="<?php echo JInboundHelperUrl::view('reports', false, array(
                'layout'       => 'chart',
                'filter_chart' => 'conversionrate'
            )) ?>"><?php echo JText::_('COM_JINBOUND_VIEWS_TO_GOAL_COMPLETIONS'); ?></a>
        <?php else : ?>
            <span><?php echo JText::_('COM_JINBOUND_VIEWS_TO_GOAL_COMPLETIONS'); ?></span>
        <?php endif; ?>
    </div>
</div>
