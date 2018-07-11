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

defined('_JEXEC') or die;

if (!empty($this->item->emails)) :
    ?>
    <div class="row-fluid">
        <div class="span12 well">
            <h4><?php echo JText::_('COM_JINBOUND_EMAIL_HISTORY'); ?></h4>
            <?php
            foreach ($this->item->emails as $email) :
                if ($email->campaign_id && $this->_currentCampaignId != $email->campaign_id) :
                    continue;
                endif;
                ?>
                <div class="row-fluid">
                    <div class="span12">
                        <h5><?php echo $this->escape($email->subject); ?></h5>
                        <h6><?php echo JInboundHelper::userDate($email->sent); ?></h6>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
endif;
