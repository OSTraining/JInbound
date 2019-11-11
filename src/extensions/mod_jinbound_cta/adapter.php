<?php
/**
 * @package             jInbound
 * @subpackage          mod_jinbound_cta
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

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

abstract class ModJInboundCTAAdapter
{
    /**
     * Parameter prefix, one of '', 'c1_', 'c2_', 'c3_'
     *
     * @var string
     */
    public $pfx = '';
    /**
     * Module parameters
     *
     * @var Registry
     */
    protected $params;

    /**
     * Constructor
     *
     * @param Registry $params
     */
    function __construct(Registry $params)
    {
        $this->params = $params;
    }

    /**
     * Adapters must have a render method that sends back the data to render
     *
     * @return string
     */
    abstract public function render();
}
