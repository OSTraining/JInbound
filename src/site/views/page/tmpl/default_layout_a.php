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

?>
<?php if (!empty($this->item->subheading)) : ?>
    <div class="row-fluid">
        <div class="span12">
            <h2><?php echo $this->escape($this->item->subheading); ?></h2>
        </div>
    </div>
<?php endif; ?>
<div class="row-fluid">
    <div class="span3">
        <div class="jinbound-image row-fluid">
            <?php echo $this->loadTemplate('image'); ?>
        </div>
        <div class="jinbound-sidebar row-fluid">
            <?php echo $this->loadTemplate('sidebar'); ?>
        </div>
    </div>
    <div class="span9">
        <div class="row-fluid">
            <div class="span8">
                <div class="row-fluid">
                    <?php echo $this->loadTemplate('body'); ?>
                </div>
                <div class="row-fluid">
                    <?php echo $this->loadTemplate('social'); ?>
                </div>
            </div>
            <div class="span4 well">
                <?php echo $this->loadTemplate('form'); ?>
            </div>
        </div>
    </div>
</div>
