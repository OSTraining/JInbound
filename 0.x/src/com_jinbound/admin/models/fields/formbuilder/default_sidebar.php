<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

$id     = $this->escape($this->input->id);
$values = $this->input->value;
if (is_object($values) && method_exists($values, 'toArray')) {
	$values = $values->toArray();
}
if (!is_array($values)) {
	$values = array();
}
?>
<div id="<?php echo $id; ?>_sidebar">
	<ul class="nav nav-tabs">
		<li><a href="<?php echo $this->escape('#' . $this->input->id); ?>_fieldlist" class="active"><?php echo JText::_('COM_JINBOUND_ADD_A_FIELD'); ?></a></li>
		<li><a href="<?php echo $this->escape('#' . $this->input->id); ?>_fieldsettings"><?php echo JText::_('COM_JINBOUND_FIELD_SETTINGS'); ?></a></li>
	</ul>
	<div class="tab-content">
		<div id="<?php echo $id; ?>_fieldlist" class="tab-pane active">
			<ul id="<?php echo $id; ?>_fields" class="unstyled <?php echo $id; ?>_connected">
<?php

foreach ($this->input->getFormFields() as $field) :
	if (0 == $field->multi && array_key_exists($field->id, $values) && 1 == $values[$field->id]['enabled']) {
		continue;
	}

?>
				<li class="btn btn-block" data-id="<?php echo $this->escape($field->id); ?>"<?php if ((int) $field->multi) : ?> data-multi="true"<?php endif; ?>><?php echo $this->escape($field->name); ?></li>
<?php

endforeach;
?>
			</ul>
		</div>
		<div id="<?php echo $id; ?>_fieldsettings" class="tab-pane">
			<div class="container-fluid">
<?php foreach ($this->input->getFormFields() as $field) : ?>
				<div class="row hide <?php echo $id; ?>_fieldsettingsform container-fluid" id="<?php echo $id; ?>_<?php echo $this->escape($field->id); ?>">
					<?php
						$this->_currentField = $field;
						echo $this->loadTemplate('sidebar_field');
					?>
				</div>
<?php

if (0 != $field->multi) :
	$i = 1;
	while (array_key_exists($field->id . '_' . $i, $values)) :
		$newField = json_decode(json_encode($field));
		$newField->id .= '_' . $i;
		
		?>
		<div class="row hide <?php echo $id; ?>_fieldsettingsform container-fluid" id="<?php echo $id; ?>_<?php echo $this->escape($newField->id); ?>">
			<?php
				$this->_currentField = $newField;
				echo $this->loadTemplate('sidebar_field');
			?>
		</div>
<?php
		
		$i++;
	endwhile;
endif;

?>

<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
