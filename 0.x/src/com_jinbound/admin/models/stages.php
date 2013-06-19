<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR.'/components/com_jinbound/helpers/jinbound.php');
JInbound::registerLibrary('JInboundListModel', 'models/basemodellist');

/**
 * This models supports retrieving lists of locations.
 *
 * @package		JInbound
 * @subpackage	com_jinbound
 */
class JInboundModelStages extends JInboundListModel
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $context  = 'com_jinbound.stages';
	
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();

		// main query
		$query = $db->getQuery(true)
		// Select the required fields from the table.
		->select('Stage.*')
		->from('#__jinbound_stages AS Stage')
		;
		// add author to query
		$this->appendAuthorToQuery($query, 'Stage');
		$this->filterSearchQuery($query, $this->getState('filter.search'), 'Stage');

		// Add the list ordering clause.
		$orderCol = trim($this->state->get('list.ordering'));
		$orderDirn = trim($this->state->get('list.direction'));
		if (strlen($orderCol)) {
			$query->order($db->getEscaped($orderCol.' '.$orderDirn));
		}

		return $query;
	}



}