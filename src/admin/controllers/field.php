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

class JInboundControllerField extends JInboundFormController
{
    /**
     * @param string $key
     * @param string $urlVar
     *
     * @return bool
     * @throws Exception
     */
    public function edit($key = 'id', $urlVar = 'id')
    {
        if (!JFactory::getUser()->authorise('core.manage', 'com_jinbound.field')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        return parent::edit($key, $urlVar);
    }
}
