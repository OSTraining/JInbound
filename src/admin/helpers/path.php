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

abstract class JInboundHelperPath
{
    /**
     * static method to get the media path
     *
     * @return string
     */
    public static function media($file = '')
    {
        return self::_buildPath(JPATH_ROOT . '/media/jinbound', $file);
    }

    static private function _buildPath($root, $file = '')
    {
        return $root . (empty($file) ? '' : "/$file");
    }

    /**
     * static method to get the site path
     *
     * @return string
     */
    public static function site($file = '')
    {
        return self::_buildPath(JPATH_ROOT . '/components/' . JInboundHelper::COM, $file);
    }

    /**
     * static method to get the helper path
     *
     * @return string
     */
    public static function helper($helper = null)
    {
        static $base;

        if (empty($base)) {
            $base = self::admin('helpers');
        }

        $file = '';
        if (!empty($helper)) {
            jimport('joomla.filesystem.file');
            $file = preg_replace('/[^a-z]/', '', $helper) . '.php';
        }

        return self::_buildPath($base, $file);
    }

    /**
     * static method to get the admin path
     *
     * @return string
     */
    public static function admin($file = '')
    {
        return self::_buildPath(JPATH_ADMINISTRATOR . '/components/' . JInboundHelper::COM, $file);
    }

    /**
     * static method to get the library path
     *
     * @return string
     */
    public static function library($file = '')
    {
        return self::_buildPath(self::admin('libraries'), $file);
    }
}
