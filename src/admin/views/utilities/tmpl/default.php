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

?>
    <h2><?php echo JText::_('COM_JINBOUND_UTILITIES'); ?></h2>
    <div class="row-fluid">
        <div class="span12">
            <ul class="unstyled">
                <li><a href="<?php echo JInboundHelperUrl::_(array('option'    => 'com_categories',
                                                                   'extension' => JInboundHelper::COM
                    )); ?>"><?php echo JText::_('COM_CATEGORIES'); ?></a></li>
                <li><a href="<?php echo JInboundHelperUrl::view('campaigns',
                        false); ?>"><?php echo JText::_('COM_JINBOUND_CAMPAIGNS_MANAGER'); ?></a></li>
                <li><a href="<?php echo JInboundHelperUrl::view('statuses',
                        false); ?>"><?php echo JText::_('COM_JINBOUND_STATUSES'); ?></a></li>
                <li><a href="<?php echo JInboundHelperUrl::view('priorities',
                        false); ?>"><?php echo JText::_('COM_JINBOUND_PRIORITIES'); ?></a></li>
            </ul>
        </div>
    </div>
<?php echo $this->loadTemplate('footer'); ?>
