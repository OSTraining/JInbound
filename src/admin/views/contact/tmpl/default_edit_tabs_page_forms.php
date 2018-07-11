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
