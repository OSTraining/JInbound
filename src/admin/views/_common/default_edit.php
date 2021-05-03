<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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
