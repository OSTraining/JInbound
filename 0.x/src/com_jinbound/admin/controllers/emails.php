<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.component.controlleradmin');

class JInboundControllerEmails extends JControllerAdmin
{
	public function getModel($name='Email', $prefix = 'JInboundModel') {
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}