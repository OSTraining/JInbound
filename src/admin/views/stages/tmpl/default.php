<?php
/**
 * @package             JInbound
 * @subpackage          com_jinbound
 **********************************************
 * JInbound
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

echo '<h2>Lead Statuses</h2>';

?>


<form action="<?php echo JURI::base() . 'index.php?option=com_jinbound&view=stages'; ?>" method="post" name="adminForm"
      id="adminForm">

    <?php

    $floatButtons = JInbound::version()->isCompatible('3.0');

    ?>


    <table class="adminlist table table-striped">
        <thead><?php echo $this->loadTemplate('head'); ?>
        </thead>
        <tfoot></tfoot>
        <tbody><?php echo $this->loadTemplate('body'); ?></tbody>
    </table>

    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>