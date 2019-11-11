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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

if ($this->item->socialmedia) {
    // render a module position here
    $modules = JModuleHelper::getModules('jinbound_social');

    if (!empty($modules)) {
        foreach ($modules as $module) {
            $mparams = new Registry();
            $mparams->loadString($module->params);
            echo JModuleHelper::renderModule($module, $mparams->toArray());
        }
    }
}
