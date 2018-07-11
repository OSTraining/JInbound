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
