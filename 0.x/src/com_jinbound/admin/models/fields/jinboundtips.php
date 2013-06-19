<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
 @ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('hidden');

JLoader::register('JInboundFieldView', JPATH_ADMINISTRATOR.'/components/com_jinbound/libraries/views/fieldview.php');

class JFormFieldJinboundTips extends JFormFieldHidden
{
	protected $type = 'JinboundTips';
	
	/**
	 * don't output an input
	 * 
	 * (non-PHPdoc)
	 * @see JFormFieldHidden::getInput()
	 */
	protected function getInput() {
		return '';
	}
	
	/**
	 * don't output a label
	 * 
	 * (non-PHPdoc)
	 * @see JFormFieldHidden::getLabel()
	 */
	protected function getLabel() {
		return '';
	}
	
	/**
	 * This method is used in the form display to show extra data
	 * 
	 */
	public function getSidebar() {
		$view = $this->getView();
		// set data
		$view->input = $this;
		// return template html
		return $view->loadTemplate();
	}
	
	/**
	 * gets a new instance of the base field view
	 * 
	 * @return JInboundFieldView
	 */
	protected function getView() {
		$viewConfig = array('template_path' => dirname(__FILE__) . '/tips');
		$view = new JInboundFieldView($viewConfig);
		return $view;
	}
}