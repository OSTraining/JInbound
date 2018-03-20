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

class JInboundControllerReports extends JControllerAdmin
{
    public function getModel($name = 'Reports', $prefix = 'JInboundModel')
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function exportleads()
    {
        $this->export('leads');
    }

    protected function export($layout)
    {
        $input  = JFactory::getApplication()->input;
        $params = array(
            'format'           => 'csv'
        ,
            'layout'           => $layout
        ,
            'filter_start'     => $input->get('filter_start', '', 'string')
        ,
            'filter_end'       => $input->get('filter_end', '', 'string')
        ,
            'filter_campaign'  => $input->get('filter_campaign', '', 'string')
        ,
            'filter_page'      => $input->get('filter_page', '', 'string')
        ,
            'filter_status'    => $input->get('filter_status', '', 'string')
        ,
            'filter_priority'  => $input->get('filter_priority', '', 'string')
        ,
            'filter_published' => $input->get('filter_published', '', 'string')
        );
        $this->setRedirect(JInboundHelperUrl::view('reports', false, $params));
    }

    public function exportpages()
    {
        $this->export('pages');
    }
}
