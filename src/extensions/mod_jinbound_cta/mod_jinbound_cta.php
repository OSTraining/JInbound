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

defined('_JEXEC') or die;

// check that jinbound is installed
$jinbound_base = JPATH_ADMINISTRATOR . '/components/com_jinbound';
if (!is_dir($jinbound_base)) {
    return false;
}

// include additional classes
require_once dirname(__FILE__) . '/adapter.php';
require_once dirname(__FILE__) . '/helper.php';

// this module requires the jinbound system plugin
if (!class_exists('plgSystemJInbound')) {
    return false;
}

// since this module renders other modules, don't let it render itself
if (ModJInboundCTAHelper::$running) {
    return false;
}
ModJInboundCTAHelper::$running = true;

$sfx = JFilterInput::getInstance()->clean($params->get('moduleclass_sfx', ''));

// load required classes
JLoader::register('JInbound', "$jinbound_base/libraries/jinbound.php");

// render module
require JModuleHelper::getLayoutPath('mod_jinbound_cta', $params->get('layout', 'default'));

// finished rendering
ModJInboundCTAHelper::$running = false;
