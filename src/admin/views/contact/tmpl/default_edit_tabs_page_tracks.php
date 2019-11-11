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

defined('JPATH_PLATFORM') or die;

?>
<fieldset class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <?php if (empty($this->item->tracks)) : ?>
                <div class="alert alert-warning"><?php echo JText::_('COM_JINBOUND_NO_TRACKS_FOUND'); ?></div>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><?php echo JText::_('COM_JINBOUND_URL'); ?></th>
                        <th><?php echo JText::_('COM_JINBOUND_VISIT_DATE'); ?></th>
                        <th><?php echo JText::_('COM_JINBOUND_USER'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->item->tracks as $i => $track) : if (20 < $i) {
                        break;
                    } ?>
                        <tr>
                            <td><?php echo $this->escape($track->url); ?></td>
                            <td><?php echo JInboundHelper::userDate($track->created); ?></td>
                            <td>
                                <i class="hasTip hasTooltip icon-<?php echo($track->current_user_id ? 'user' : 'warning'); ?>"
                                   title="<?php echo JText::_('COM_JINBOUND_' . ($track->current_user_id ? 'USER' : 'AUTHOR_GUEST')); ?>"> </i>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</fieldset>
