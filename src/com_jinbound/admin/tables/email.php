<?php
/**
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR . "/components/com_jinbound/helpers/jinbound.php");
JInbound::registerLibrary('JInboundAssetTable', 'tables/asset');

class JInboundTableEmail extends JInboundAssetTable
{

	function __construct(&$db) {
		parent::__construct('#__jinbound_emails', 'id', $db);
	}
	
	/**
	 * Redefined asset name, as we support action control
	 */
	protected function _getAssetName() {
		$k = $this->_tbl_key;
		return 'com_jinbound.email.'.(int) $this->$k;
	}
	
	/**
	 * We provide our global ACL as parent
	 * @see JTable::_getAssetParentId()
	 */
	protected function _compat_getAssetParentId($table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_jinbound.email');
		return $asset->id;
	}
	
	public function load($keys = null, $reset = true) {
		// load
		$load = parent::load($keys, $reset);
		// convert params to an object
		$registry = new JRegistry;
		if (is_string($this->params)) {
			$registry->loadString($this->params);
		}
		else if (is_array($this->params)) {
			$registry->loadArray($this->params);
		}
		else if (is_object($this->params)) {
			$registry->loadObject($this->params);
		}
		$this->params = $registry;
		return $load;
	}
	
	public function bind($array, $ignore = '') {
		if (isset($array['params'])) {
			$registry = new JRegistry;
			if (is_array($array['params'])) {
				$registry->loadArray($array['params']);
			}
			else if (is_string($array['params'])) {
				$registry->loadString($array['params']);
			}
			else if (is_object($array['params'])) {
				
			}
			$array['params'] = (string) $registry;
		}
		return parent::bind($array, $ignore);
	}
	
	/**
	 * override to save versions
	 *
	 * (non-PHPdoc)
	 * @see JInboundTable::store()
	 */
	public function store($updateNulls = false) {
		$app = JFactory::getApplication();
		// we have to determine if this email is new or not
		$isNew = empty($this->id);
		// if it is new, we can simply save it and insert a new record into the versions table
		if ($isNew) {
			// save this email first
			$store = parent::store($updateNulls);
			// now run a query to insert the values from this email into the versions table
			$this->_db->setQuery('INSERT INTO #__jinbound_emails_versions (email_id, subject, htmlbody, plainbody) SELECT id, subject, htmlbody, plainbody FROM #__jinbound_emails WHERE id = ' . $this->id);
			try {
				$this->_db->query();
			}
			catch (Exception $e) {
				$app->enqueueMessage($e->getMassage(), 'error');
				return $store;
			}
			// now return the store result
			return $store;
		}
		// if it isn't new, we have to pull the previous version and check the texts
		else {
			// if any of the texts in this version of the email differ
			// we have to insert a new version of the email
			
			// pull the original from the database
			$this->_db->setQuery('SELECT subject, htmlbody, plainbody FROM #__jinbound_emails WHERE id = ' . $this->id);
			try {
				$original = $this->_db->loadObject();
			}
			catch (Exception $e) {
				$app->enqueueMessage($e->getMassage(), 'error');
				return parent::store($updateNulls);
			}
			// go ahead and store the new, then update versions to reflect
			$store = parent::store($updateNulls);
			// compare the original to the new
			// if the old matches, just store & bail
			if ($original->subject == $this->subject && $original->htmlbody == $this->htmlbody && $original->plainbody == $this->plainbody) {
				return $store;
			}
			// there is a difference - insert a new version record before store
			$this->_db->setQuery('INSERT INTO #__jinbound_emails_versions (email_id, subject, htmlbody, plainbody) SELECT id, subject, htmlbody, plainbody FROM #__jinbound_emails WHERE id = ' . $this->id);
			try {
				$this->_db->query();
			}
			catch (Exception $e) {
				$app->enqueueMessage($e->getMassage(), 'error');
				return $store;
			}
			// now return the store result
			return $store;
		}
	}
}