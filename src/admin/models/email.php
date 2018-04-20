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
 * This models supports retrieving an email.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelEmail extends JInboundAdminModel
{
    public $context = 'com_jinbound.email';

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.' . $this->name, $this->name,
            array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        // remove the sidebar stuff if layout isn't "a" or empty
        $template = strtolower(JFactory::getApplication()->input->get('set', $form->getValue('layout', 'A'), 'cmd'));
        if (!empty($template) && 'a' !== $template) {
            if (1 == JString::strlen($template)) {
                $template = JString::strtoupper($template);
            }
            $form->setValue('layout', null, $template);
        }
        // check published permissions
        if (!JFactory::getUser()->authorise('core.edit.state', 'com_jinbound.email')) {
            $form->setFieldAttribute('published', 'readonly', 'true');
        }

        return $form;
    }
}
