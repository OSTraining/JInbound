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

class JInboundViewContacts extends JInboundJsonListView
{
    public function display($tpl = null, $safeparams = null)
    {
        $this->items = $this->get('Items');
        if (!empty($this->items)) {
            foreach ($this->items as &$item) {
                $item->url      = JInboundHelperUrl::edit('contact', $item->id);
                $item->page_url = JInboundHelperUrl::edit('page', $item->latest_conversion_page_id);
                $item->created  = JInboundHelper::userDate($item->created);
                $item->latest   = JInboundHelper::userDate($item->latest);
            }
            // do not send track info in json format
            // TODO just don't pull the data in the model
            unset($item->tracks);
        }
        parent::display($tpl, $safeparams);
    }
}
