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

if (JFactory::getUser()
    ->authorise('core.create', JInboundHelper::COM . '.' . JInboundInflector::singularize($this->viewName))) :
    ?>
    <div class="jinbound-empty">
        <div class="row">
            <div class="span4 offset4">
                <a class="btn btn-large btn-block"
                   href="<?php echo JInboundHelperUrl::task(JInboundInflector::singularize($this->viewName) . '.add'); ?>">
                    <i class="icon-plus-sign"></i>
                    <span><?php echo JText::_('COM_JINBOUND_' . strtoupper(JInboundInflector::singularize($this->viewName)) . '_ADD_NEW'); ?></span>
                </a>
            </div>
        </div>
    </div>
<?php

endif;
