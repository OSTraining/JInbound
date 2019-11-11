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

if (!property_exists($this->item, 'layout') || !in_array(strtolower($this->item->layout),
        array('a', 'b', 'c', 'd', 'custom', '0'))) :
    $this->item->layout = 'a';
endif;

if ('0' == $this->item->layout || 'custom' == $this->item->layout) :
    echo $this->loadTemplate('layout_custom');
else :

    ?>
    <div class="row-fluid">
        <div class="span12">
            <h1><?php echo $this->escape($this->item->heading); ?></h1>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <?php echo $this->loadTemplate('layout_' . strtolower($this->item->layout)); ?>
        </div>
    </div>
<?php

endif;
