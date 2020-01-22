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

?>
<?php
if (!empty($this->messages)) :
    $messageclass = JInboundHelper::version()->isCompatible('3.0.0') ? 'alert alert-message' : 'm pre_message';
    foreach ($this->messages as $message) : ?>
        <div class="<?php echo $messageclass; ?>">
            <?php echo $message; ?>
        </div>
    <?php
    endforeach;
endif;
?>
<table class="table table-striped">
    <thead>
    <tr>
        <th><?php echo JText::_('COM_JINBOUND_INSTALL_EXTENSION'); ?></th>
        <th class="hidden-phone"><?php echo JText::_('COM_JINBOUND_INSTALL_EXTENSION_TYPE'); ?></th>
        <th><?php echo JText::_('COM_JINBOUND_INSTALL_EXTENSION_INSTALLED'); ?></th>
        <th class="hidden-phone"><?php echo JText::_('COM_JINBOUND_INSTALL_EXTENSION_VERSION'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php

    foreach ($this->extensions as $extension) :
        $link = false;
        if ($extension->installed) {
            if ('plugin' == $extension->type) {
                $link = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extension->extension_id;
            } else {
                if ('component' == $extension->type) {
                    $link = 'index.php?option=' . $extension->id;
                } else {
                    if ('template' == $extension->type) {
                        $link = 'index.php?option=com_templates&view=template&id=' . $extension->extension_id;
                    }
                }
            }
        }

        ?>
        <tr>
            <td><?php echo $link ? '<a href="' . $link . '">' . $extension->name . '</a>' : $this->escape($extension->name); ?></td>
            <td class="hidden-phone"><?php echo $this->escape($extension->type); ?></td>
            <td><img
                    src="../media/jinbound/images/install-<?php echo $this->escape($extension->installed ? 'success' : 'failed'); ?>.png"/>
            </td>
            <td class="hidden-phone"><?php echo $this->escape($extension->version); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
