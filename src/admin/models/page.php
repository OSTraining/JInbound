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

use Joomla\String\StringHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This models supports retrieving a location.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelPage extends JInboundAdminModel
{
    protected $context = 'com_jinbound.page';

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

        if (!$form) {
            return false;
        }

        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        // remove the sidebar stuff if layout isn't "a" or empty
        $template = strtolower($app->input->get('set', $form->getValue('layout', 'A'), 'cmd'));
        if (!empty($template) && 'a' !== $template) {
            if (StringHelper::strlen($template) == 1) {
                $template = StringHelper::strtoupper($template);
            }
            $form->setValue('layout', null, $template);
        }

        if (!$user->authorise('core.edit.state', 'com_jinbound.page')) {
            $form->setFieldAttribute('published', 'readonly', 'true');
        }

        return $form;
    }
}
