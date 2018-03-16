<?php
/**
 * @package        jInbound
 * @subpackage     com_jinbound
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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

JHtml::_('behavior.tooltip');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

$permissions = $this->loadTemplate('permissions');
$useTabs     = $permissions && JFactory::getUser()->authorise('core.admin', 'com_jinbound');
?>
    <div id="jinbound_component" class="row-fluid <?php echo $this->viewClass; ?>">
        <?php
        $mainAttributes = array(
            'id' => 'j-main-container'
        );
        if (!empty($this->sidebar)) :
            $mainAttributes['class'] = 'span10';
            ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo $this->sidebar; ?>
            </div>
        <?php
        endif;
        ?>
        <div <?php echo ArrayHelper::toString($mainAttributes); ?>>
            <?php
            if ($useTabs) :
                echo JHtml::_('jinbound.startTabSet', 'jinbound_default_tabs', array('active' => 'content_tab'));

                echo JHtml::_(
                    'jinbound.addTab',
                    'jinbound_default_tabs',
                    'content_tab',
                    JText::_('JTOOLBAR_EDIT', true)
                );
            endif;
            ?>
            <form action="<?php echo JInboundHelperUrl::view($this->viewName); ?>"
                  method="post"
                  name="adminForm"
                  id="adminForm">
                <?php echo $this->loadTemplate('list_top'); ?>
                <div class="row-fluid">
                    <?php
                    echo JLayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this),
                        null,
                        array('debug' => false)
                    );

                    if (empty($this->items)) :
                        echo $this->loadTemplate('empty');

                    else :
                        ?>
                        <table class="adminlist table table-striped">
                            <thead><?php echo $this->loadTemplate('head'); ?></thead>
                            <tfoot><?php echo $this->loadTemplate('foot'); ?></tfoot>
                            <tbody><?php echo $this->loadTemplate('body'); ?></tbody>
                        </table>
                    <?php
                    endif;
                    ?>
                    <div>
                        <input type="hidden" name="task" value=""/>
                        <input type="hidden" name="boxchecked" value="0"/>
                        <?php echo JHtml::_('form.token'); ?>
                    </div>
                </div>
            </form>
            <?php
            if ($useTabs) :
                echo JHtml::_('jinbound.endTab');
                echo JHtml::_(
                    'jinbound.addTab',
                    'jinbound_default_tabs',
                    'permissions_tab',
                    JText::_('JCONFIG_PERMISSIONS_LABEL', true)
                );

                echo $permissions;
                echo JHtml::_('jinbound.endTab');

                echo JHtml::_('jinbound.endTabSet');
            endif;

            echo $this->loadTemplate('footer');
            ?>
        </div>
    </div>
<?php
if (JInbound::config("debug", 0)) :
    ?>
    <div class="row-fluid">
        <h3>State</h3>
        <pre><?php echo $this->escape(print_r($this->state, 1)); ?></pre>
        <h3>Items</h3>
        <pre><?php echo $this->escape(print_r($this->items, 1)); ?></pre>
    </div>
<?php
endif;
