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
            <div class="well">
                <?php echo JHtml::_('jinbound.startSlider', 'leadSlider', array('active' => 'leadslider-0')); ?>
                <?php if (!empty($this->item->conversions)) : ?>
                    <?php foreach (array_reverse($this->item->conversions) as $i => $data) : ?>
                        <?php echo JHtml::_('jinbound.addSlide', 'leadSlider',
                            JInboundHelper::userDate($data->created) . ' | ' . $data->page_name, 'leadslider-' . $i); ?>
                        <table class="table table-striped">
                            <?php if (array_key_exists('lead', $data->formdata)) {
                                foreach ($data->formdata['lead'] as $key => $value) : ?>
                                    <tr>
                                        <td><?php echo $this->escape($key); ?></td>
                                        <td><?php echo $this->renderFormField($data->page_id, $key, $value); ?>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            } ?>
                        </table>
                        <?php echo JHtml::_('jinbound.endSlide'); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php echo JHtml::_('jinbound.endSlider'); ?>
            </div>
        </div>
    </div>
</fieldset>
