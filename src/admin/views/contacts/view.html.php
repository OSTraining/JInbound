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
     * @param string $tpl
     * @param bool   $safeparams
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var JInboundModelContacts $model */
        $model = $this->getModel();

        $campaigns = $model->getCampaignsOptions();

        if ($errors = $model->getErrors()) {
            throw new Exception(implode('<br />', $errors), 500);
        }

        if (!$campaigns) {
            $this->app->enqueueMessage(JText::_('COM_JINBOUND_NO_CAMPAIGNS_YET'), 'warning');
        }

        parent::display($tpl);
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
