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

class JInboundViewContacts extends JInboundListView
{
    /**
     *
     *
     * (non-PHPdoc)
     * @see JInboundListView::display()
     */
    function display($tpl = null, $safeparams = false)
    {
        $campaigns  = $this->get('CampaignsOptions');
        $pages      = $this->get('PagesOptions');
        $statuses   = JInboundHelperStatus::getSelectOptions();
        $priorities = JInboundHelperPriority::getSelectOptions();
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode('<br />', $errors), 500);
        }
        if (1 >= count($campaigns)) {
            $this->app->enqueueMessage(JText::_('COM_JINBOUND_NO_CAMPAIGNS_YET'), 'warning');
        }
        if (!JInboundHelper::version()->isCompatible('3.0.0')) {
            array_unshift($statuses, (object)array('value' => '', 'text' => JText::_('COM_JINBOUND_SELECT_STATUS')));
            array_unshift($priorities,
                (object)array('value' => '', 'text' => JText::_('COM_JINBOUND_SELECT_PRIORITY')));
        }

        $filter = (array)$this->app->getUserStateFromRequest('com_jinbound.contacts.filter', 'filter', array(),
            'array');

        $this->addFilter(JText::_('COM_JINBOUND_SELECT_CAMPAIGN'), 'filter[campaign]', $campaigns,
            array_key_exists('campaign', $filter) ? $filter['campaign'] : '');
        $this->addFilter(JText::_('COM_JINBOUND_SELECT_PAGE'), 'filter[page]', $pages,
            array_key_exists('page', $filter) ? $filter['page'] : '');
        $this->addFilter(JText::_('COM_JINBOUND_SELECT_STATUS'), 'filter[status]', $statuses,
            array_key_exists('status', $filter) ? $filter['status'] : '', false);
        $this->addFilter(JText::_('COM_JINBOUND_SELECT_PRIORITY'), 'filter[priority]', $priorities,
            array_key_exists('priority', $filter) ? $filter['priority'] : '', false);

        return parent::display($tpl, $safeparams);
    }

    public function addToolBar()
    {
        $icon = 'export';
        if (JInboundHelper::version()->isCompatible('3.0.0')) {
            $icon = 'download';
        }
        // export icons
        if (JFactory::getUser()->authorise('core.create', JInboundHelper::COM . '.report')) {
            JToolBarHelper::custom('reports.exportleads', "{$icon}.png", "{$icon}_f2.png", 'COM_JINBOUND_EXPORT_LEADS',
                false);
        }
        parent::addToolBar();
    }
}
