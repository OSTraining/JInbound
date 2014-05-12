<?php
/**
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR . "/components/com_jinbound/helpers/jinbound.php");
JInbound::registerLibrary('JInboundTable', 'table');

class JInboundTableContact extends JInboundTable
{
	function __construct(&$db)
	{
		parent::__construct('#__jinbound_contacts', 'id', $db);
	}
}
