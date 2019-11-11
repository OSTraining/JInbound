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
            <div class="pull-right">
                <?php echo JHtml::_('jinbound.leadnotes', $this->item->id, true); ?>
            </div>
            <div class="well">
                <table id="jinbound_leadnotes_table" class="table table-striped">
                    <tbody>
                    <?php if (!empty($this->notes)) : foreach ($this->notes as $note) : ?>
                        <tr>
                            <td><span class="label"><?php echo JInboundHelper::userDate($note->created); ?></span></td>
                            <td class="note"><?php echo $this->escape($note->author); ?></td>
                            <td class="note"><?php echo $this->escape($note->text); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td>
                                <div
                                    class="alert alert-error"><?php echo JText::_('COM_JINBOUND_NO_NOTES_FOUND'); ?></div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</fieldset>
