<?php
/**
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR . '/components/com_jinbound/helpers/jinbound.php');
JInbound::registerHelper('url');

/**
 * Utility class for JInbound
 *
 * @static
 * @package		JInbound
 * @subpackage	com_jinbound
 */
abstract class JHtmlJInbound
{
	private function _stateSelect($state, $id, $campaign_id, $value, $options, $canChange)
	{
		$attr = 'class="change_' . $state . ' input-small" data-id="' . intval($id) . '" data-campaign="' . intval($campaign_id) . '"';
		if (!$canChange) {
			$attr .= ' disabled="disabled"';
		}
		
		return JHtml::_('select.genericlist', $options, 'change_' . $state . '[' . $id . ']', $attr, 'id', 'name', $value);
	}
	
	public function priority($id, $priority_id, $campaign_id, $prefix, $canChange) {
		static $options;
		
		if (is_null($options)) {
			// get the priorities
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
				->select('Priority.id')
				->select('Priority.name')
				->from('#__jinbound_priorities AS Priority')
				->where('Priority.published = 1')
			);
			
			try {
				$options = $db->loadObjectList();
				if (!is_array($options) || empty($options)) {
					throw new Exception('Empty');
				}
			}
			catch (Exception $e) {
				$options = array();
			}
		}
		
		echo JHtmlJInbound::_stateSelect('priority', $id, $campaign_id, $priority_id, $options, $canChange);
	}
	
	public function status($id, $status_id, $campaign_id, $prefix, $canChange) {
		static $options;
		
		if (is_null($options)) {
			// get the statuses
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
				->select('Status.id')
				->select('Status.name')
				->from('#__jinbound_lead_statuses AS Status')
				->where('Status.published = 1')
				->order('Status.ordering ASC')
			);
			
			try {
				$options = $db->loadObjectList();
				if (!is_array($options) || empty($options)) {
					throw new Exception('Empty');
				}
			}
			catch (Exception $e) {
				$options = array();
			}
		}
		
		echo JHtmlJInbound::_stateSelect('status', $id, $campaign_id, $status_id, $options, $canChange);
	}
	
	public function leadupdate() {
		static $loaded;
		
		if (is_null($loaded)) {
			$document = JFactory::getDocument();
			$document->addScript(JInboundHelperUrl::media() . '/js/leadupdate.js');
			$loaded = true;
		}
	}
	
	public function formdata($id, $formname, $formdata, $script = true) {
		if (!is_a($formdata, 'JRegistry')) {
			$registry = new JRegistry();
			if (is_object($formdata)) {
				$registry->loadObject($formdata);
			}
			else if (is_array($formdata)) {
				$registry->loadArray($formdata);
			}
			else if (is_string($formdata)) {
				$registry->loadString($formdata);
			}
			else {
				return;
			}
			$data = $registry->toArray();
		}
		else {
			$data = $formdata->toArray();
		}
		
		$filter = JFilterInput::getInstance();
		
		?>
			<div class="formdata">
<?php if ($script) : ?>
				<a href="#" class="formdata-modal"><?php echo $filter->clean($formname); ?></a>
<?php else : ?>
				<h3><?php echo $filter->clean($formname); ?></h3>
<?php endif; ?>
				<div class="formdata-container<?php if ($script) : ?> hide<?php endif; ?>">
					<div class="formdata-data">
						<h4><?php echo JText::_('COM_JINBOUND_FORM_INFORMATION'); ?></h4>
						<div class="well">
							<table class="table table-striped">
								<?php if (array_key_exists('lead', $data)) foreach ($data['lead'] as $key => $value) : ?>
								<tr>
									<td><?php echo $filter->clean($key); ?></td>
									<td><?php echo $filter->clean(print_r($value, 1)); ?></td>
								</tr>
								<?php endforeach; ?>
							</table>
						</div>
					</div>
				</div>
			</div>
		<?php
		
		// add script once
		static $scripted;
		
		if (!is_null($scripted)) {
			return;
		}
		
		$scripted = true;
		
		$doc = JFactory::getDocument();
		// if no scripts can be added, bail
		if (!$script || !method_exists($doc, 'addScriptDeclaration')) {
			return;
		}
		JHtml::_('behavior.modal');
		// build script
		$source = <<<EOF
(function($){
	$(document).ready(function(){
		$('.formdata-modal').click(function(e){
			try {
				console.log('opening modal');
			}
			catch (err) {
			}
			var data = $(e.target).parent().find('.formdata-data');
			if (data.length) {
				SqueezeBox.setContent('adopt', data[0]);
			}
			e.preventDefault();
			e.stopPropagation();
		});
	});
})(jQuery);
EOF
;
		$doc->addScriptDeclaration($source);
	}
	
	public function leadnotes($id) {
		static $notes;
		
		if (is_null($notes)) {
			$notes = array();
			$document = JFactory::getDocument();
			$document->addScript(JInboundHelperUrl::media() . '/js/leadnotes.js');
			
			JText::script('COM_JINBOUND_CONFIRM_DELETE');
		}
		
		$id  = (int) $id;
		$key = "lead_$id";
		if (!array_key_exists($key, $notes)) {
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
				->select('Note.*, User.name AS author')
				->from('#__jinbound_notes AS Note')
				->leftJoin('#__users AS User ON Note.created_by = User.id')
				->where('Note.lead_id = ' . $id)
				->group('Note.id')
			);
			
			try {
				$notes[$key] = $db->loadObjectList();
				if (!is_array($notes[$key])) {
					throw new Exception('Empty');
				}
			}
			catch (Exception $e) {
				$notes[$key] = array();
			}
		}
		
		$filter = JFilterInput::getInstance();
		
		?>
		<div class="leadnotes btn-group">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="leadnotes-count"><?php echo count($notes[$key]); ?></span> <i class="icon-pencil"> </i> <span class="carat"></span></a>
			<div class="dropdown-menu pull-right" data-stopPropagation="true">
				<div class="leadnotes-block" data-stopPropagation="true">
					<div class="leadnotes-notes" data-stopPropagation="true">
<?php if (!empty($notes[$key])) : foreach ($notes[$key] as $note) : ?>
						<div class="leadnote alert" data-stopPropagation="true">
							<a class="close" data-dismiss="alert" data-noteid="<?php echo $note->id; ?>" data-leadid="<?php echo $note->lead_id; ?>" href="#"
								onclick="(function(){return confirm(Joomla.JText._('COM_JINBOUND_CONFIRM_DELETE'));})();"
							>&times;</a>
							<span class="label" data-stopPropagation="true"><?php echo $note->created; ?></span> <?php echo $filter->clean($note->author, 'string'); ?>
							<div class="leadnote-text" data-stopPropagation="true"><?php echo $filter->clean($note->text, 'string'); ?></div>
						</div>
<?php endforeach; endif; ?>
					</div>
					<div class="leadnotes-form-container" data-stopPropagation="true">
						<fieldset class="well" data-stopPropagation="true">
							<textarea class="leadnotes-new-text input-block-level" data-stopPropagation="true"></textarea>
							<input type="hidden" name="lead_id" value="<?php echo $id; ?>" />
							<button type="button" class="leadnotes-submit btn btn-primary pull-right" data-stopPropagation="true"><i class="icon-ok"> </i> <?php echo JText::_('JAPPLY'); ?> </button>
						</fieldset>
					</div>
				</div>
			</div>
		</div>
		<?php
		
	}
	
	public static function startSlider($selector = 'myAccordian', $params = array()) {
		if (JInbound::version()->isCompatible('3.1.0')) {
			JHtml::_('bootstrap.framework');
			return JHtml::_('bootstrap.startAccordion', $selector, $params);
		}
		else {
			return JHtml::_('sliders.start', $selector, $params);
		}
	}
	
	public static function endSlider() {
		if (JInbound::version()->isCompatible('3.1.0')) {
			return JHtml::_('bootstrap.endTabSet');
		}
		else {
			return JHtml::_('sliders.end');
		}
	}
	
	public static function addSlide($selector, $text, $id, $class = '') {
		if (JInbound::version()->isCompatible('3.1.0')) {
			return JHtml::_('bootstrap.addSlide', $selector, $text, $id, $class);
		}
		else {
			return JHtml::_('sliders.panel', $text, $id);
		}
	}
	
	public static function endSlide() {
		if (JInbound::version()->isCompatible('3.1.0')) {
			return JHtml::_('bootstrap.endSlide');
		}
		else {
			return '';
		}
	}
	
	public static function startTabSet($tabSetName, $options = array()) {
		if (JInbound::version()->isCompatible('3.1.0')) {
			JHtml::_('bootstrap.framework');
			return JHtml::_('bootstrap.startTabSet', $tabSetName, $options);
		}
		else {
			return JHtml::_('tabs.start', $tabSetName, $options);
		}
	}
	
	public static function addTab($tabSetName, $tabName, $tabLabel) {
		if (JInbound::version()->isCompatible('3.1.0')) {
			return JHtml::_('bootstrap.addTab', $tabSetName, $tabName, $tabLabel);
		}
		else {
			return JHtml::_('tabs.panel', $tabLabel, $tabName);
		}
	}
	
	public static function endTab() {
		if (JInbound::version()->isCompatible('3.1.0')) {
			return JHtml::_('bootstrap.endTab');
		}
		else {
			return '';
		}
	}
	
	public static function endTabSet() {
		if (JInbound::version()->isCompatible('3.1.0')) {
			return JHtml::_('bootstrap.endTabSet');
		}
		else {
			return JHtml::_('tabs.end');
		}
	}
	
	public static function isfinal($value, $i, $prefix = '', $enabled = true, $checkbox='cb') {
		$states = array(
				1       => array('unsetFinal',        'COM_JINBOUND_FINAL', 'COM_JINBOUND_HTML_UNSETFINAL_ITEM',      'COM_JINBOUND_FINAL',     false,  'default',              'default'),
				0       => array('setFinal',          '',                     'COM_JINBOUND_HTML_SETFINAL_ITEM',    '',                     false,  'unfeatured',   'unfeatured'),
		);
		return JHtml::_('jgrid.state', $states, $value, $i, $prefix, $enabled, true, $checkbox);
	}
	
	public static function isactive($value, $i, $prefix = '', $enabled = true, $checkbox='cb') {
		$states = array(
				1       => array('unsetActive',        'COM_JINBOUND_ACTIVE', 'COM_JINBOUND_HTML_UNSETACTIVE_ITEM',      'COM_JINBOUND_ACTIVE',     false,  'default',              'default'),
				0       => array('setActive',          '',                     'COM_JINBOUND_HTML_SETACTIVE_ITEM',    '',                     false,  'unfeatured',   'unfeatured'),
		);
		return JHtml::_('jgrid.state', $states, $value, $i, $prefix, $enabled, true, $checkbox);
	}
}
