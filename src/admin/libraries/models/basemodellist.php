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
     * @var string
     */
    protected $context = 'com_jinbound';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var        string
     */
    protected $_extension = 'com_jinbound';

    /**
     * @var string[]
     */
    protected $registryColumns = null;

    /**
     * @return object[]
     */
    public function getItems()
    {
        $items = parent::getItems();
        // if we have no columns to alter, we're done
        if (!is_array($this->registryColumns) || empty($this->registryColumns)) {
            return $items;
        }
        // alter items, if any, to convert json data to registries
        if (is_array($items) && !empty($items)) {
            foreach ($items as $idx => $item) {
                foreach ($this->registryColumns as $col) {
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

    /**
     * @param string|JDatabaseQuery $query
     * @param string                $defaultText
     *
     * @return object[]
     */
    public function getOptionsFromQuery($query, $defaultText)
    {
        $default = json_decode(
            json_encode(
                array(
                    'value' => '',
                    'text'  => $defaultText
                )
            )
        );

        try {
            $db      = $this->getDbo();
            $options = $db->setQuery($query)->loadObjectList();

            array_unshift($options, $default);

        } catch (Exception $e) {
            $options = array($default);
        }

        return $options;
    }

    /**
     * @param JDatabaseQuery $query
     * @param string         $tableName
     * @param string         $created_by
     */
    public function appendAuthorToQuery(JDatabaseQuery $query, $tableName, $created_by = 'created_by')
    {
        $db = JFactory::getDbo();

        $tableName  = JFilterInput::getInstance()->clean($tableName, 'cmd');
        $guest      = JText::_('COM_JINBOUND_AUTHOR_GUEST');
        $system     = JText::_('COM_JINBOUND_AUTHOR_SYSTEM');

        $column = $db->quoteName($tableName . '.' . $created_by);

        $query
            ->select(
                array(
                    sprintf(
                        'IF(%s=0,%s,IF(%s=-1,%s,Author.name)) AS author_name',
                        $column,
                        $db->quote($guest),
                        $column,
                        $db->quote($system)
                    ),
                    sprintf(
                        'IF(%s=0,%s,IF(%s=-1,%s,Author.username)) AS author_username',
                        $column,
                        $db->quote(strtolower($guest)),
                        $column,
                        $db->quote(strtolower($system))
                    )
                )
            )
            ->leftJoin("#__users AS Author ON Author.id = {$column}");
    }

    /**
     * @param JDatabaseQuery $query
     * @param string         $search
     * @param string         $tableName
     * @param string         $pk
     * @param string[]       $columns
     */
    public function filterSearchQuery(JDatabaseQuery $query, $search, $tableName, $pk = 'id', $columns = array())
    {
        if (empty($search)) {
            return;
        }

        $db     = JFactory::getDbo();
        $filter = JFilterInput::getInstance();

        $tableName = $filter->clean($tableName, 'cmd');
        $pk        = $filter->clean($pk, 'cmd');

        if (stripos($search, $pk . ':') === 0) {
            $query->where($tableName . '.' . $pk . ' = ' . (int)substr($search, 3));

            return;
        }

        $search = $db->quote('%' . $search . '%');

        if ((array)$columns) {
            $where = array();
            foreach ($columns as $column) {
                $column  = $filter->clean($column, 'cmd');
                $column  = (strpos($column, '.') === false ? $tableName . '.' : '') . $column;
                $where[] = sprintf('%s LIKE %s', $db->quoteName($column), $search);
            }

            $query->where(sprintf('(%s)', implode(' OR ', $where)));
        }
    }

    /**
     * @param JDatabaseQuery $query
     * @param string         $status
     * @param string         $tableName
     * @param string         $column
     */
    public function filterPublished(JDatabaseQuery $query, $status, $tableName, $column = 'published')
    {
        $filter = JFilterInput::getInstance();

        $tableName = $filter->clean($tableName, 'cmd');
        $column    = $filter->clean($column, 'cmd');
        $col       = $tableName . '.' . $column;

        if ($status == '') {
            $query->where(sprintf('(%1$s = 1 OR %1$s = 0)', $col));
        } else {
            $query->where("$col = $status");
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
        $this->setState('params', JInboundHelper::config());
        // load the filter values
        $filters = (array)$this->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
        $this->setState('filter', $filters);
        // set the published status based on permissions and filters
        $published = array_key_exists('published', $filters)
            ? $filters['published']
            : $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
        if (!$user->authorise('core.edit.state', JInboundHelper::COM)
            && !$user->authorise('core.edit', JInboundHelper::COM)) {
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
     * @param string $id A prefix for the store id.
     *
     * @return string        A store id.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter'));

        return parent::getStoreId($id);
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return (string)$this->context;
    }
}
