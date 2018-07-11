<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
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
