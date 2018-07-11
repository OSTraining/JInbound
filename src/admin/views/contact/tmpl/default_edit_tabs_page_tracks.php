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
