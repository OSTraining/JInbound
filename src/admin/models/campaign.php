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

defined('JPATH_PLATFORM') or die();

class JInboundModelCampaign extends JInboundAdminModel
{
    public $context = 'com_jinbound.campaign';

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return JForm|boolean
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            $this->option . '.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form) {
            if (!JFactory::getUser()->authorise('core.edit.state', 'com_jinbound.campaign')) {
                $form->setFieldAttribute('published', 'readonly', 'true');
            }

            return $form;
        }

        return false;
    }
}
