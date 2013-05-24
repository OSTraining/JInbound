<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

$id = $this->escape($this->input->id) . '_' . $this->_currentField->id;
$name = $this->escape($this->input->name . '[' . $this->_currentField->id . ']');
$values = $this->input->value;
if (is_object($values) && method_exists($values, 'toArray')) {
	$values = $values->toArray();
}
if (!is_array($values)) {
	$values = array();
}

if (array_key_exists($this->_currentField->id, $values)) {
	$value = $values[$this->_currentField->id];
	if (!array_key_exists('required', $value)) {
		$value['required'] = 0;
	}
	if (!array_key_exists('enabled', $value)) {
		$value['enabled'] = 0;
	}
}
else {
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
		<input id="<?php echo $id; ?>_title" class="input-medium" type="text" name="<?php echo $name; ?>[title]" value="<?php echo $this->escape($value['title']); ?>" />
	</div>
	
	<?php if ($this->_currentField->multi) : ?>
	<div class="row-fluid">
		<label for="<?php echo $id; ?>_name"><?php echo JText::_('COM_JINBOUND_FIELD_NAME'); ?></label>
	</div>
	<div class="row-fluid">
		<input id="<?php echo $id; ?>_name" class="input-medium" type="text" name="<?php echo $name; ?>[name]" value="<?php echo $this->escape('' . @$value['name']); ?>" />
	</div>
	<?php endif; ?>
	
	<?php if ($showAdvanced) : ?>
	<div class="row-fluid">
		<label for="<?php echo $id; ?>_required"><?php echo JText::_('COM_JINBOUND_FIELD_REQUIRED'); ?></label>
	</div>
	<div class="row-fluid">
		<?php echo JHtml::_('select.genericlist', array(JHtml::_('select.option', '1', JText::_('JYES')), JHtml::_('select.option', '0', JText::_('JNO'))), $name . '[required]', 'class="input-medium"', 'value', 'text', $value['required'], $id . '_required'); ?>
	</div>
	<div>
		<input id="<?php echo $id; ?>_enabled" type="<?php echo (defined('JDEBUG') && JDEBUG ? 'text' : 'hidden'); ?>" name="<?php echo $name; ?>[enabled]" value="<?php echo (int) $value['enabled']; ?>" />
	</div>
	
	<?php else : ?>
		<input id="<?php echo $id; ?>_required" type="hidden" name="<?php echo $name; ?>[required]" value="1" />
		<input id="<?php echo $id; ?>_enabled" type="hidden" name="<?php echo $name; ?>[enabled]" value="1" />
	<?php endif; ?>
	
	<?php echo $this->loadTemplate('sidebar_field_' . $this->_currentField->type); ?>
	<?php if (defined('JDEBUG') && JDEBUG) : ?>
	<div class="row-fluid">
		<h4>Value:</h4>
		<pre><?php print_r($value); ?></pre>
	</div>
	<?php endif; ?>