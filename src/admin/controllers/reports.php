<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
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
