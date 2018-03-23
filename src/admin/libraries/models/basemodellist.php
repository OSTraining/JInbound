<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base list model class
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundListModel extends JModelList
{
    /**
     * Model context string.
     *
     * @var        string
     */
    public $_context = 'com_jinbound';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var        string
     */
    protected $_extension = 'com_jinbound';

    private $_registryColumns = null;

    public function getItems()
    {
        $items = parent::getItems();
        // if we have no columns to alter, we're done
        if (!is_array($this->_registryColumns) || empty($this->_registryColumns)) {
            return $items;
        }
        // alter items, if any, to convert json data to registries
        if (is_array($items) && !empty($items)) {
            foreach ($items as $idx => $item) {
                foreach ($this->_registryColumns as $col) {
                    if (!property_exists($item, $col)) {
                        continue;
                    }
                    $registry = new JRegistry();
                    $registry->loadString($items[$idx]->$col);
                    $items[$idx]->$col = $registry;
                }
            }
        }
        return $items;
    }

    /**
     * The core "state" jhtml crap uses "archive" and we don't need that
     *
     */
    public function getPublishedStatus()
    {
        $list = array(
            JHtml::_('select.option', '', JText::_('COM_JINBOUND_SELECT_PUBLISHED'))
        ,
            JHtml::_('select.option', '1', JText::_('COM_JINBOUND_SELECT_PUBLISHED_OPTION_PUBLISHED'))
        ,
            JHtml::_('select.option', '0', JText::_('COM_JINBOUND_SELECT_PUBLISHED_OPTION_UNPUBLISHED'))
        ,
            JHtml::_('select.option', '-2', JText::_('COM_JINBOUND_SELECT_PUBLISHED_OPTION_TRASHED'))
        );
        return $list;
    }

    public function getOptionsFromQuery($query, $defaultText)
    {
        $default = json_decode(json_encode(array(
            'value' => ''
        ,
            'text'  => $defaultText
        )));
        // Create a new query object.
        $db = $this->getDbo();
        $db->setQuery($query);
        try {
            $options = $db->loadObjectList();
            array_unshift($options, $default);
            return $options;
        } catch (Exception $e) {
            return array($default);
        }
    }

    /**
     * give public read access to the model's context
     *
     */
    public function getContext()
    {
        return (string)$this->_context;
    }

    public function appendAuthorToQuery(&$query, $tablename, $created_by = 'created_by')
    {
        // clean our table name
        $tablename = JFilterInput::getInstance()->clean($tablename, 'cmd');
        // get our dbo for cleaning texts
        $db = JFactory::getDbo();
        // clean the created_by column
        $created_by = $db->quoteName($created_by);
        // our default names for guest & system
        $guest  = JText::_('COM_JINBOUND_AUTHOR_GUEST');
        $system = JText::_('COM_JINBOUND_AUTHOR_SYSTEM');
        // full column selection
        $column = $tablename . '.' . $created_by;
        // join over author
        $query
            ->select('IF(' . $column . '=0,' . $db->Quote($guest) . ',IF(' . $column . '=-1,' . $db->Quote($system) . ',Author.name)) AS author_name')
            ->select('IF(' . $column . '=0,' . $db->Quote(strtolower($guest)) . ',IF(' . $column . '=-1,' . $db->Quote(strtolower($system)) . ',Author.username)) AS author_username')
            ->leftJoin("#__users AS Author ON Author.id = {$column}");
    }

    public function filterSearchQuery(&$query, $search, $tablename, $pk = 'id', $columns = array())
    {
        // clean our variables
        $filter    = JFilterInput::getInstance();
        $tablename = $filter->clean($tablename, 'cmd');
        $pk        = $filter->clean($pk, 'cmd');
        // get our dbo for cleaning texts
        $db = JFactory::getDbo();
        if (empty($search)) {
            return;
        }
        // search by primary key
        if (0 === stripos($search, $pk . ':')) {
            $query->where($tablename . '.' . $pk . ' = ' . (int)substr($search, 3));
            return;
        }
        // search by column text
        $search = $db->quote('%' . (method_exists($db, 'escape') ? $db->escape($search, true) : $db->getEscaped($search,
                true)) . '%', false);
        $where  = array();
        if (!empty($columns)) {
            foreach ($columns as &$column) {
                $column  = $filter->clean($column, 'cmd');
                $where[] = (false === strpos($column, '.') ? $tablename . '.' : '') . $column . ' LIKE ' . $search;
            }
            $query->where('(' . implode(' OR ', $where) . ')');
        }
    }

    public function filterPublished(&$query, $status, $tablename, $column = 'published')
    {
        // clean our variables
        $filter    = JFilterInput::getInstance();
        $tablename = $filter->clean($tablename, 'cmd');
        $column    = $filter->clean($column, 'cmd');
        $col       = $tablename . '.' . $column;
        // get our dbo for cleaning texts
        $db = JFactory::getDbo();
        // search by column text
        switch ($status) {
            default:
            case '':
                $query->where("($col = 1 OR $col = 0)");
                break;
            case 0:
            case 1:
            case 2:
            case -1:
            case -2:
                $query->where("$col = $status");
                break;
        }
    }

    /**
     * @param string $formName
     * @param string $dataFile
     * @param bool   $asset
     *
     * @return JForm
     * @throws Exception
     */
    public function getPermissions($formName = null, $dataFile = null, $asset = true)
    {
        $dataFile = $dataFile ?: $this->getName();
        $formName = $formName ?: 'com_jinbound.' . $dataFile;

        try {
            $form = JForm::getInstance($formName, $dataFile);
            if ($form instanceof JForm) {
                if ($asset) {
                    $asset = is_string($asset) ? $asset : $formName;

                    $db = JFactory::getDbo();
                    $db->setQuery(
                        $db->getQuery(true)
                            ->select('id, rules')
                            ->from('#__assets')
                            ->where('name = ' . $db->quote($asset))
                    );
                    $rules = $db->loadObject();
                    if (!empty($rules)) {
                        $form->bind(array('asset_id' => $rules->id, 'rules' => $rules->rules));
                    }
                }
            }
            return $form;

        } catch (Exception $e) {
        }

        return null;
    }

    /**
     * Method to get a form object.
     *
     * @param   string  $name    The name of the form.
     * @param   string  $source  The form source. Can be XML string if file flag is set to false.
     * @param   array   $options Optional array of options for the form creation.
     * @param   boolean $clear   Optional argument to force load a new form.
     * @param   string  $xpath   An optional xpath to search for the fields.
     *
     * @return  mixed  JForm object on success, False on error.
     *
     * @see     JForm
     * @since   3.2
     */
    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
        // Handle the optional arguments.
        $options['control'] = JArrayHelper::getValue($options, 'control', false);

        // Create a signature hash.
        $hash = md5($source . serialize($options));

        // Check if we can use a previously loaded form.
        if (isset($this->_forms[$hash]) && !$clear) {
            return $this->_forms[$hash];
        }

        // Get the form.
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

        try {
            $form = JForm::getInstance($name, $source, $options, false, $xpath);

            if (isset($options['load_data']) && $options['load_data']) {
                // Get the data for the form.
                $data = $this->loadFormData();
            } else {
                $data = array();
            }

            // Allow for additional modification of the form, and events to be triggered.
            // We pass the data because plugins may require it.
            $this->preprocessForm($form, $data);

            // Load the data into the form after the plugins have operated.
            $form->bind($data);
        } catch (Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Store the form for later.
        $this->_forms[$hash] = $form;

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     *
     * @since    3.2
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->context);

        if (!is_object($data)) {
            $data = new stdClass();
        }

        // Pre-fill the list options
        if (!property_exists($data, 'list')) {
            $data->list = array(
                'direction' => $this->state->{'list.direction'},
                'limit'     => $this->state->{'list.limit'},
                'ordering'  => $this->state->{'list.ordering'},
                'start'     => $this->state->{'list.start'}
            );
        }

        return $data;
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   JForm  $form  A JForm object.
     * @param   mixed  $data  The data expected for the form.
     * @param   string $group The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   3.2
     * @throws  Exception if there is an error in the form event.
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        // Import the appropriate plugin group.
        JPluginHelper::importPlugin($group);

        // Get the dispatcher.
        $dispatcher = JDispatcher::getInstance();

        // Trigger the form preparation event.
        $results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

        // Check for errors encountered while preparing the form.
        if (count($results) && in_array(false, $results, true)) {
            // Get the last error.
            $error = $dispatcher->getError();

            if (!($error instanceof Exception)) {
                throw new Exception($error);
            }
        }
    }

    public function getActiveFilters()
    {
        if (method_exists('JModelList', 'getActiveFilters')) {
            return parent::getActiveFilters();
        }
        return array();
    }

    public function getFilterForm($data = array(), $loadData = true)
    {
        $formFile = JPATH_ADMINISTRATOR . '/components/com_jinbound/models/forms/filter_' . $this->name . '.xml';
        if (method_exists('JModelList', 'getFilterForm') && JFile::exists($formFile)) {
            return parent::getFilterForm($data, $loadData);
        }
        return null;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);
        // @deprecated
        $this->setState('filter.extension', $this->_extension);
        // force some state based on user permissions
        $user = JFactory::getUser();
        // set the params in the state
        $this->setState('params', JInbound::config());
        // load the filter values
        $filters = (array)$this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
        $this->setState('filter', $filters);
        // set the published status based on permissions and filters
        $published = array_key_exists('published', $filters)
            ? $filters['published']
            : $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
        if (!$user->authorise('core.edit.state', JInbound::COM)
            && !$user->authorise('core.edit', JInbound::COM)) {
            // filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', 1);
        } else {
            $this->setState('filter.published', $published);
        }
        // set the search
        $search = array_key_exists('search', $filters)
            ? $filters['search']
            : $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

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
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter'));

        return parent::getStoreId($id);
    }
}
