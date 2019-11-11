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

$floatButtons = JInboundHelper::version()->isCompatible('3.0');

$clearScript = "document.getElementById('filter_search').value='';jQuery('#filter-bar').find('select,input').val('');this.form.submit();";

if ('component' != JFactory::getApplication()->input->get('tmpl', 'default')) :

    ?>
    <fieldset id="filter-bar">
        <div class="filter-search fltlft btn-group pull-left input-append">
            <label class="filter-search-lbl element-invisible"
                   for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search" class="input-medium"
                   value="<?php echo JInboundHelperFilter::escape($this->state->get('filter.search')); ?>"
                   title="<?php echo JText::_('COM_JINBOUND_FILTER_SEARCH_DESC'); ?>"
                   placeholder="<?php echo JText::_('COM_JINBOUND_FILTER_SEARCH_DESC'); ?>"/>

            <?php if (!$floatButtons) : ?>
                <button type="submit" class="btn btn-primary"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <button type="button" class="btn"
                        onclick="<?php echo $clearScript; ?>"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
            <?php endif; ?>

        </div>

        <?php if ($floatButtons) : ?>
            <div class="btn-group pull-left hidden-phone">
                <button type="submit" class="btn btn-primary tip hasTooltip"
                        title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                <button type="button" class="btn tip hasTooltip" onclick="<?php echo $clearScript; ?>"
                        title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
            </div>
            <?php if (property_exists($this, 'pagination') && is_object($this->pagination)) : ?>
                <div class="btn-group pull-right hidden-phone">
                    <label for="limit"
                           class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php echo $this->renderFilters(); ?>
    </fieldset>
    <div class="clr"></div>

<?php

endif;
