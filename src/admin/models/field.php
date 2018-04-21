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
 * This models supports retrieving lists of fields.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelField extends JInboundAdminModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.field';

    /**
     * @var string
     */
    protected $event_after_save = 'onJInboundAfterSave';

    /**
     * @var string
     */
    protected $event_before_save = 'onJInboundBeforeSave';

    public function getTable($type = 'Field', $prefix = 'JInboundTable', $config = array())
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            $this->option . '.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form) {
            return $form;
        }
        return false;
    }
}
