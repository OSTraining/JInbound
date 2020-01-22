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
