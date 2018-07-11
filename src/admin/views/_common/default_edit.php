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

$app = JFactory::getApplication();

?>
    <div id="jinbound_component" class="row-fluid <?php echo $this->viewClass; ?>">
        <form action="<?php echo JInboundHelperUrl::_(); ?>" method="post" id="adminForm" name="adminForm"
              class="form-validate" enctype="multipart/form-data">
            <fieldset>
                <?php echo $this->loadTemplate('edit_default'); ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="id" value="<?php echo (int)@$this->item->id; ?>"/>
                <input type="hidden" name="function" value="<?php echo $app->input->getCmd('function'); ?>"/>
                <?php
                if ('component' == $app->input->getCmd('tmpl')) :
                    ?>
                    <input type="hidden" name="tmpl" value="component"/>
                    <input type="hidden" name="layout" value="modal"/>
                <?php
                endif;
                echo JHtml::_('form.token');
                ?>
            </fieldset>
            <?php echo $this->loadTemplate('edit_tabs'); ?>
        </form>
    </div>
<?php
echo $this->loadTemplate('footer');

if (JInboundHelper::config("debug", 0)) :
    ?>
    <div class="row-fluid">
        <h3>Item:</h3>
        <pre><?php htmlspecialchars(print_r($this->item)); ?></pre>
    </div>
<?php
endif;
