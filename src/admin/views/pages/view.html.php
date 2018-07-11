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

class JInboundViewPages extends JInboundListView
{
    /**
     * @param null $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $filter = (array)$this->app->getUserStateFromRequest(
            $this->get('State')->get('context') . '.filter',
            'filter',
            array(),
            'array'
        );

        foreach (array('categories', 'campaigns') as $var) {
            $single = JInboundInflector::singularize($var);
            $$var   = $this->get(ucwords($var) . 'Options');

            if (count($$var) <= 1) {
                JFactory::getApplication()
                    ->enqueueMessage(JText::_('COM_JINBOUND_NO_' . strtoupper($var) . '_YET'), 'warning');
            }

            $this->addFilter(
                JText::_('COM_JINBOUND_SELECT_' . strtoupper($single)),
                'filter[' . $single . ']',
                $$var,
                array_key_exists($single, $filter) ? $filter[$single] : ''
            );
        }

        parent::display($tpl);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function addToolBar()
    {
        if (JFactory::getUser()->authorise('core.create', JInboundHelper::COM . '.report')) {
            JToolBarHelper::custom(
                'reports.exportpages',
                'download.png',
                'download_f2.png',
                'COM_JINBOUND_EXPORT_PAGES',
                false
            );
        }

        parent::addToolBar();
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return string[]
     */
    protected function getSortFields()
    {
        return array(
            'Page.name'      => JText::_('COM_JINBOUND_LANDINGPAGE_NAME'),
            'Page.published' => JText::_('COM_JINBOUND_PUBLISHED'),
            'Page.category'  => JText::_('COM_JINBOUND_CATEGORY'),
            'Page.hits'      => JText::_('COM_JINBOUND_VIEWS'),
            'submissions'    => JText::_('COM_JINBOUND_SUBMISSIONS'),
            'conversions'    => JText::_('COM_JINBOUND_CONVERSIONS')
        );
    }
}
