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

$id   = $this->escape($this->input_id) . '_' . $this->_currentField->id;
$name = $this->escape($this->input->name . '[' . $this->_currentField->id . ']');

if (array_key_exists($this->_currentField->id, $this->value)) {
    $value = $this->value[$this->_currentField->id];
    if (!array_key_exists('required', $value)) {
        $value['required'] = 0;
    }
    if (!array_key_exists('enabled', $value)) {
        $value['enabled'] = 0;
    }
} else {
    $value = array('title' => $this->_currentField->name, 'name' => '', 'required' => 0, 'enabled' => 0);
}

switch ($this->_currentField->id) {
    case 'first_name':
    case 'last_name':
    case 'email':
        $showAdvanced = false;
        break;
    default:
        $showAdvanced = true;
        break;
}

?>
    <div class="row-fluid">
        <label for="<?php echo $id; ?>_title"><?php echo JText::_('COM_JINBOUND_FIELD_TITLE'); ?></label>
    </div>
    <div class="row-fluid">
        <input id="<?php echo $id; ?>_title" class="input-medium" type="text" name="<?php echo $name; ?>[title]"
               value="<?php echo $this->escape($value['title']); ?>"/>
    </div>

<?php if ($this->_currentField->multi) : ?>
    <div class="row-fluid">
        <label for="<?php echo $id; ?>_name"><?php echo JText::_('COM_JINBOUND_FIELD_NAME'); ?></label>
    </div>
    <div class="row-fluid">
        <input id="<?php echo $id; ?>_name" class="input-medium" type="text" name="<?php echo $name; ?>[name]"
               value="<?php echo $this->escape('' . @$value['name']); ?>"/>
    </div>
<?php endif; ?>

<?php if ($showAdvanced) : ?>
    <div class="row-fluid">
        <label for="<?php echo $id; ?>_required"><?php echo JText::_('COM_JINBOUND_FIELD_REQUIRED'); ?></label>
    </div>
    <div class="row-fluid">
        <?php echo JHtml::_('select.genericlist',
            array(JHtml::_('select.option', '1', JText::_('JYES')), JHtml::_('select.option', '0', JText::_('JNO'))),
            $name . '[required]', 'class="input-medium"', 'value', 'text', $value['required'], $id . '_required'); ?>
    </div>
    <div>
        <input id="<?php echo $id; ?>_enabled" type="<?php echo(JInboundHelper::config("debug", 0) ? 'text' : 'hidden'); ?>"
               name="<?php echo $name; ?>[enabled]" value="<?php echo (int)$value['enabled']; ?>"/>
    </div>

<?php else : ?>
    <input id="<?php echo $id; ?>_required" type="hidden" name="<?php echo $name; ?>[required]" value="1"/>
    <input id="<?php echo $id; ?>_enabled" type="hidden" name="<?php echo $name; ?>[enabled]" value="1"/>
<?php endif; ?>

    <input id="<?php echo $id; ?>_type" type="hidden" name="<?php echo $name; ?>[type]"
           value="<?php echo $this->escape($this->_currentField->type); ?>"/>

<?php
echo $this->loadTemplate('sidebar_field_' . $this->_currentField->type);

$this->optionsInputName = 'attributes';
echo $this->loadTemplate('sidebar_field_options');
$this->optionsInputName = 'options';
?>
<?php if (JInboundHelper::config("debug", 0)) : ?>
    <div class="row-fluid">
        <h4>Value:</h4>
        <pre><?php print_r($value); ?></pre>
    </div>
<?php endif; ?>
