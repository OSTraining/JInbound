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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

abstract class JInboundHelperModule
{
    /**
     * Fetches the module object needed to operate
     *
     * @return stdClass
     * @throws UnexpectedValueException
     */
    public static function getModuleObject($module_id = null)
    {
        // init
        $input  = JFactory::getApplication()->input;
        $db     = JFactory::getDbo();
        $id     = is_null($module_id) ? $input->getInt('id', 0) : $module_id;
        $return = base64_decode($input->getBase64('return_url', base64_encode(JUri::root(true))));
        // there must be a module id to continue
        if (empty($id)) {
            throw new UnexpectedValueException('Module not found');
        }
        // load the module by title
        $title = $db->setQuery($db->getQuery(true)
            ->select('title')
            ->from('#__modules')
            ->where('id = ' . $id)
        )->loadResult();
        if (empty($title)) {
            throw new UnexpectedValueException('Module not found');
        }
        // use the module helper to load the module object
        $module = JModuleHelper::getModule('mod_jinbound_form', $title);
        if ($module->id != $id) {
            throw new UnexpectedValueException('Module not found (' . $module->id . ', ' . $id . ')');
        }
        // fix the params
        if (!$module->params instanceof Registry) {
            $module->params = new Registry($module->params);
        }
        // set return url if desired
        if (!empty($return)) {
            $module->params->set('return_url', $return);
        }
        return $module;
    }
}
