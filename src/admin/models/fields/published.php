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

JFormHelper::loadFieldClass('Radio');

class JinboundFormFieldPublished extends JFormFieldRadio
{
    public $type = 'Jinboundpublished';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            if (!$this->class) {
                $this->class = 'btn-group btn-group-yesno';
            }

            return true;
        }

        return false;
    }

    protected function getOptions()
    {
        $options = array(
            JHtml::_('select.option', 1, JText::_('COM_JINBOUND_PUBLISHED')),
            JHtml::_('select.option', 0, JText::_('COM_JINBOUND_UNPUBLISHED'))
        );

        return array_merge(parent::getOptions(), $options);
    }
}
