<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2020 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of jInbound.
 *
 * jInbound is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * jInbound is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with jInbound.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('JPATH_PLATFORM') or die;

class JInboundListView extends JInboundView
{
    /**
     * @var object
     */
    protected $items;

    /**
     * @var JPagination
     */
    protected $pagination;

    /**
     * @var string
     */
    protected $ordering = null;

    /**
     * @var JForm
     */
    protected $permissions = null;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var string
     */
    protected $currentFilter = null;

    /**
     * @var JForm
     */
    public $filterForm = null;

    /**
     * @var array
     */
    public $activeFilters = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()
            ->authorise('core.manage', 'com_jinbound.' . strtolower(JInboundInflector::singularize($this->_name)))) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var JInboundListModel $model */
        $model = $this->getModel();

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->permissions   = $model->getPermissions();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode('<br />', $errors), 500);
        }

        $this->ordering = array(0 => array());
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                if (!(property_exists($item, 'ordering') || property_exists($item, 'lft'))) {
                    break;
                }
                $this->ordering[0][] = $item->id;
            }
        }

        $publishedOptions = $this->get('PublishedStatus');
        if (!empty($publishedOptions)) {
            $this->addFilter(
                JText::_('COM_JINBOUND_SELECT_PUBLISHED'),
                'filter[published]',
                $publishedOptions,
                $this->state->get('filter.published')
            );
        }

        parent::display($tpl);
    }

    public function addFilter($label, $name, $options, $default, $trim = true)
    {
        $filter = (object)array(
            'label'   => $label,
            'name'    => $name,
            'options' => $options,
            'default' => $default,
            'trim'    => $trim
        );

        $this->filters[] = $filter;

        return $this->filters;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function renderFilters()
    {
        if (empty($this->filters)) {
            return '';
        }
        if (class_exists('JHtmlSidebar')) {
            foreach ($this->filters as $filter) {
                if (empty($filter->options)) {
                    continue;
                }
                if ($filter->trim) {
                    array_shift($filter->options);
                }
                $options = JHtml::_('select.options', $filter->options, 'value', 'text', $filter->default, true);
                JHtmlSidebar::addFilter($filter->label, $filter->name, $options);
            }
            return '';
        }

        $html = array();
        foreach ($this->filters as $filter) {
            if (empty($filter->options)) {
                continue;
            }

            $this->currentFilter = JHtml::_(
                'select.genericlist',
                $filter->options,
                $filter->name,
                sprintf('id="%s" class="listbox" onchange="this.form.submit()"', $filter->name),
                'value',
                'text',
                $filter->default
            );

            $html[] = $this->loadTemplate('filter', 'default');
        }

        return implode("\n", $html);
    }

    /**
     * @throws Exception
     */
    public function addToolBar()
    {
        // only fire in administrator, and only once
        if (!JFactory::getApplication()->isAdmin()) {
            return;
        }

        static $set;

        if (is_null($set)) {
            $single       = strtolower(JInboundInflector::singularize($this->_name));
            $user         = JFactory::getUser();
            $canCreate    = $user->authorise('core.create', JInboundHelper::COM . ".$single");
            $canDelete    = $user->authorise('core.delete', JInboundHelper::COM . ".$single");
            $canEdit      = $user->authorise('core.edit', JInboundHelper::COM . ".$single");
            $canEditOwn   = $user->authorise('core.edit.own', JInboundHelper::COM . ".$single");
            $canEditState = $user->authorise('core.edit.state', JInboundHelper::COM . ".$single");
            // set the toolbar title
            $title = strtoupper(JInboundHelper::COM . '_' . $this->_name . '_MANAGER');
            $class = 'jinbound-' . strtolower($this->_name);
            if ('contacts' === $this->_name) {
                $title = strtoupper(JInboundHelper::COM . '_LEADS_MANAGER');
                $class = 'jinbound-leads';
            }
            if ($canCreate) {
                JToolBarHelper::addNew($single . '.add', 'JTOOLBAR_NEW');
            }
            if ($canEdit || $canEditOwn) {
                JToolBarHelper::editList($single . '.edit', 'JTOOLBAR_EDIT');
                JToolBarHelper::divider();
            }
            if ($canEditState) {
                JToolBarHelper::publish($this->_name . '.publish', 'JTOOLBAR_PUBLISH', true);
                JToolBarHelper::unpublish($this->_name . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);
                JToolBarHelper::checkin($this->_name . '.checkin');
                JToolBarHelper::divider();
            }
            if ($this->state->get('filter.published') == -2 && $canDelete) {
                JToolBarHelper::deleteList('', $this->_name . '.delete', 'JTOOLBAR_EMPTY_TRASH');
            } else {
                if ($canEditState) {
                    JToolBarHelper::trash($this->_name . '.trash');
                    JToolBarHelper::divider();
                }
            }
            // add parent toolbar
            parent::addToolBar();

            JToolBarHelper::title(JText::_($title), $class);
        }
        $set = true;
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array();
    }
}
