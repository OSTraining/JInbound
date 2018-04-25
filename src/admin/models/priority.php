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

class JInboundModelPriority extends JInboundAdminModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.priority';

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return bool|JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option . '.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if (empty($form)) {
            return false;
        }

        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        if (!$app->isClient('administrator')) {
            $form->setFieldAttribute('published', 'type', 'hidden');
            $form->setFieldAttribute('published', 'default', '1');
            $form->setValue('published', '1');

        } else {
            if (!$user->authorise('core.edit.state', 'com_jinbound.priority')) {
                $form->setFieldAttribute('published', 'readonly', 'true');
            }
        }

        return $form;
    }
}
