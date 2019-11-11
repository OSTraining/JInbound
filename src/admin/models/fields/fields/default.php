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

defined('_JEXEC') or die;

?>
<table class="jinboundfields table table-striped">
    <thead>
    <tr>
        <th width="1%">&nbsp;</th>
        <th><?php echo JText::_('COM_JINBOUND_FIELD_TITLE'); ?></th>
        <th width="15%" class="nowrap"><?php echo JText::_('JGRID_HEADING_ORDERING'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->fields as $field) : ?>
        <tr>
            <td>
                <?php if ($field->core) : ?>
                    <i class="icon-lock"></i><input name="<?php echo $this->escape($this->input_name); ?>[]"
                                                    type="hidden" value="<?php echo $this->escape($field->id); ?>"/>
                <?php else : ?>
                    <input id="<?php echo $this->escape($this->input_id . $field->id); ?>"
                           name="<?php echo $this->escape($this->input_name); ?>[]" type="checkbox"
                           value="<?php echo $this->escape($field->id); ?>"<?php echo $field->extra; ?>/>
                <?php endif; ?>
            </td>
            <td><?php echo $this->escape($field->title); ?></td>
            <td class="nowrap">
                <div class="jinboundfields_ordering btn-group">
                    <input type="button" class="jinboundfields_ordering_up btn" value="↑"/>
                    <input type="button" class="jinboundfields_ordering_down btn" value="↓"/>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
