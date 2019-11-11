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

$id    = $this->escape($this->input->id) . '_' . $this->_currentField->id;
$name  = $this->escape($this->input->name . '[' . $this->_currentField->id . '][' . $this->optionsInputName . ']');
$value = array_key_exists($this->_currentField->id, $this->value) ? $this->value[$this->_currentField->id] : array();

$this->_optname = $name;

?>
<div class="row-fluid">
    <label
        for="<?php echo $id; ?>_options"><?php echo JText::_('COM_JINBOUND_FIELD_' . strtoupper($this->optionsInputName)); ?></label>
</div>
<div class="row-fluid formbuilder-field-options well">
    <div class="span12">

        <div class="formbuilder-option formbuilder-default-option">
            <?php
            $this->_optnamevalue  = '';
            $this->_optvaluevalue = '';
            echo $this->loadTemplate('sidebar_field_option');
            ?>
        </div>


        <div class="formbuilder-field-options-stage">
            <?php
            if (array_key_exists($this->optionsInputName, $value)) {
                foreach ($value[$this->optionsInputName]['name'] as $k => $v) :
                    if (empty($v)) {
                        continue;
                    }
                    $this->_optnamevalue  = $v;
                    $this->_optvaluevalue = $value[$this->optionsInputName]['value'][$k];
                    ?>
                    <div class="formbuilder-option">
                        <?php echo $this->loadTemplate('sidebar_field_option'); ?>
                    </div>
                <?php endforeach;
            } ?>
            <div class="formbuilder-option">
                <?php
                $this->_optnamevalue  = '';
                $this->_optvaluevalue = '';
                echo $this->loadTemplate('sidebar_field_option');
                ?>
            </div>
        </div>
    </div>
</div>
