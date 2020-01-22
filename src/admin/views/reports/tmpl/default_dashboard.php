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
    <div class="row-fluid">
        <!-- start the container -->
        <div class="well span8 offset2">
            <!-- Report Heading -->
            <div class="row-fluid">
                <div class="span12">
                    <h3 class="text-center"><?php echo JText::_('COM_JINBOUND_AT_A_GLANCE'); ?></h3>
                </div>
            </div>
            <?php echo $this->loadTemplate(null, 'glance'); ?>
        </div>
    </div>
    <div class="row-fluid">
        <!-- start the container -->
        <div class="well span8 offset2">
            <div class="row-fluid">
                <div class="span12">
                    <div id="jinbound-reports-graph" style="width:100%;height:300px"></div>
                </div>
            </div>
        </div>
    </div>
<?php
echo $this->loadTemplate('leads', 'recent');
echo $this->loadTemplate('pages', 'top');
