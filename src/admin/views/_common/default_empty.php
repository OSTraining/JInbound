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
