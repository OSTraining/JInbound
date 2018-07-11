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

?>
    <h2><?php echo JText::_('COM_JINBOUND_UTILITIES'); ?></h2>
    <div class="row-fluid">
        <div class="span12">
            <ul class="unstyled">
                <li><a href="<?php echo JInboundHelperUrl::_(array('option'    => 'com_categories',
                                                                   'extension' => JInboundHelper::COM
                    )); ?>"><?php echo JText::_('COM_CATEGORIES'); ?></a></li>
                <li><a href="<?php echo JInboundHelperUrl::view('campaigns',
                        false); ?>"><?php echo JText::_('COM_JINBOUND_CAMPAIGNS_MANAGER'); ?></a></li>
                <li><a href="<?php echo JInboundHelperUrl::view('statuses',
                        false); ?>"><?php echo JText::_('COM_JINBOUND_STATUSES'); ?></a></li>
                <li><a href="<?php echo JInboundHelperUrl::view('priorities',
                        false); ?>"><?php echo JText::_('COM_JINBOUND_PRIORITIES'); ?></a></li>
            </ul>
        </div>
    </div>
<?php echo $this->loadTemplate('footer'); ?>
