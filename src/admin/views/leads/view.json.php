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

$e = new Exception(__FILE__);
JLog::add('JInboundViewLeads is deprecated. ' . $e->getTraceAsString(), JLog::WARNING, 'deprecated');

JInbound::registerLibrary('JInboundJsonListView', 'views/jsonviewlist');

class JInboundViewLeads extends JInboundJsonListView
{
    public function display($tpl = null, $safeparams = null)
    {
        $this->items = $this->get('Items');
        if (!empty($this->items)) {
            foreach ($this->items as &$item) {
                $item->url      = JInboundHelperUrl::edit('lead', $item->id);
                $item->page_url = JInboundHelperUrl::edit('page', $item->page_id);
            }
        }
        parent::display($tpl, $safeparams);
    }
}
