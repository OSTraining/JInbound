<?php
/**
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR . "/components/com_jinbound/helpers/jinbound.php");
JInbound::registerLibrary('JInboundAssetTable', 'tables/asset');

class JInboundTablePriority extends JInboundAssetTable
{

	function __construct(&$db) {
		parent::__construct('#__jinbound_priorities', 'id', $db);
	}
	
	/**
	 * Redefined asset name, as we support action control
	 */
	protected function _getAssetName() {
		$k = $this->_tbl_key;
		return 'com_jinbound.priority.'.(int) $this->$k;
	}
	
	/**
	 * We provide our global ACL as parent
	 * @see JTable::_getAssetParentId()
	 */
	protected function _compat_getAssetParentId($table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_jinbound.priority');
		return $asset->id;
	}
}