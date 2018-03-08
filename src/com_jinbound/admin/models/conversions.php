<?php
/**
 * @package             JInbound
 * @subpackage          com_jinbound
 * @ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR . '/components/com_jinbound/helpers/jinbound.php');
JInbound::registerLibrary('JInboundListModel', 'models/basemodellist');

/**
 * This models supports retrieving lists of conversions.
 *
 * @package        JInbound
 * @subpackage     com_jinbound
 */
class JInboundModelConversions extends JInboundListModel
{
    /**
     * Model context string.
     *
     * @var        string
     */
    public    $_context = 'com_jinbound.conversions';
    protected $context  = 'com_jinbound.conversions';

    /**
     * Constructor.
     *
     * @param       array   An optional associative array of configuration settings.
     *
     * @see         JController
     */
    function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Conversion.published'
            ,
                'Conversion.created'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);
        // load the filter values
        $filters = (array)$this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
        $this->setState('filter', $filters);

        $app    = JFactory::getApplication();
        $format = $app->input->get('format', '', 'cmd');
        $end    = ('json' == $format ? '.json' : '');

        foreach (array('start', 'end', 'priority', 'status') as $var) {
            $value = array_key_exists($var, $filters)
                ? $filters[$var]
                : $this->getUserStateFromRequest($this->context . '.filter.' . $var . $end, 'filter_' . $var, '',
                    'string');
            $this->setState('filter.' . $var, $value);
        }
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param    string $id A prefix for the store id.
     *
     * @return    string        A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . serialize($this->getState('filter.start'));
        $id .= ':' . serialize($this->getState('filter.end'));
        $id .= ':' . serialize($this->getState('filter.priority'));
        $id .= ':' . serialize($this->getState('filter.status'));

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();

        // select columns
        $query = $db->getQuery(true)
            ->select('Conversion.*')
            ->from('#__jinbound_conversions AS Conversion');

        // add author to query
        $this->appendAuthorToQuery($query, 'Conversion');
        // filter query
        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Conversion', 'id', array(
            'first_name',
            'last_name'
        ));
        $this->filterPublished($query, $this->getState('filter.published'), 'Conversion');

        $value = $this->getState('filter.start');
        if (!empty($value)) {
            try {
                $date = new DateTime($value);
            } catch (Exception $e) {
                $date = false;
            }
            if ($date) {
                $query->where('Conversion.created > ' . $db->quote($date->format('Y-m-d h:i:s')));
            }
        }

        $value = $this->getState('filter.end');
        if (!empty($value)) {
            try {
                $date = new DateTime($value);
            } catch (Exception $e) {
                $date = false;
            }
            if ($date) {
                $query->where('Conversion.created < ' . $db->quote($date->format('Y-m-d h:i:s')));
            }
        }

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Conversion.created');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
