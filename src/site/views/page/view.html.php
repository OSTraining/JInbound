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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

class JInboundViewPage extends JInboundItemView
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        parent::display($tpl);

        if ($this->item->id == 0 || (int)$this->item->published != 1) {
            throw new Exception(JText::_('COM_JINBOUND_NOT_FOUND'), 404);
        }

        // increase the hit count
        if (!method_exists($this->item, 'hit')) {
            $table = JTable::getInstance('Page', 'JInboundTable');
            $table->load($this->item->id);
            $table->hit();

        } else {
            $this->item->hit();
        }

        $doc = JFactory::getDocument();

        if (!empty($this->item->metatitle)) {
            $this->setTitle($this->item->metatitle);
        }

        if (!empty($this->item->metadescription)) {
            $doc->setDescription($this->item->metadescription);
        }

        if (!empty($this->item->css)) {
            $doc->addStyleDeclaration($this->item->css);
        }

        // add image constraints
        $this->addImageConstraint('large');
        $this->addImageConstraint('medium', 800);
        $this->addImageConstraint('small', 320);
    }

    protected function addImageConstraint($size, $screen = 0, $selector = '#jinbound_component .jinbound_image')
    {
        $tmpl = "{$selector} {\n%s\n}";
        if ($screen) {
            $tmpl = "@media (max-width: {$screen}px) {\n{$tmpl}\n}";
        }
        $styles = array();
        foreach (array('width', 'height') as $var) {
            $prop  = 'image_size_' . $size . '_' . $var;
            $value = intval(property_exists($this->item, $prop) ? $this->item->$prop : 0);
            if ($value) {
                $styles[] = "max-{$var}: {$value}px;";
            }
        }
        if (!empty($styles)) {
            $declaration = sprintf($tmpl, implode("\n", $styles));
            JFactory::getDocument()->addStyleDeclaration($declaration);
        }
    }

    public function prepareItem()
    {
        // fix for legacy
        $form = JTable::getInstance('Form', 'JInboundTable');
        $form->load($this->item->formid);
        $this->item->formname = $form->title;
        // trigger content events
        JPluginHelper::importPlugin('content');
        // "fix" for content plugins
        foreach (array('maintext', 'sidebartext', 'template') as $text) {
            $this->item->text = $this->item->$text;
            // get dispatcher and trigger the event
            $dispatcher = JDispatcher::getInstance();
            $params     = new Registry();
            $dispatcher->trigger('onContentPrepare', array('com_jinbound.page', &$this->item, &$params, 0));
            $this->item->$text = $this->item->text;
            unset($this->item->text);
        }
    }

    public function setDocument()
    {
        parent::setDocument();
        // add behaviors
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');
        // add script
        $document = JFactory::getDocument();
        $document->addScript(JInboundHelperUrl::media() . '/js/jinbound.js');
    }

    function renderCustomLayout()
    {
        // get the custom layout data & replace all the tags
        $text = $this->item->template;

        $tags     = array();
        $basetags = array("heading", "subheading", "maintext", "sidebartext");
        foreach ($basetags as $basetag) {
            if (!property_exists($this->item, $basetag)) {
                continue;
            }
            $tags[$basetag] = $this->item->$basetag;
        }

        if (!empty($this->item->image)) {
            $tags['image'] = $this->loadTemplate('image', 'default');
        } else {
            $tags['image'] = '';
        }

        // load these from templates
        $tags['form:open']  = $this->loadTemplate('form_open', 'default');
        $tags['form:close'] = $this->loadTemplate('form_close', 'default');
        $tags['submit']     = $this->loadTemplate('form_submit', 'default');
        $tags['form']       = $this->loadTemplate('form', 'default');

        $fullAddress = array(
            'address'  => ''
        ,
            'suburb'   => ''
        ,
            'state'    => ''
        ,
            'country'  => ''
        ,
            'postcode' => ''
        );

        foreach ($this->form->getFieldset('lead') as $key => $field) {
            $tagKey                  = str_replace('jform_lead_', '', $key);
            $this->_currentField     = $field;
            $tags['form:' . $tagKey] = $this->loadTemplate('form_field');

            if (array_key_exists($tagKey, $fullAddress)) {
                $fullAddress[$tagKey] = $tags['form:' . $tagKey];
            }
        }
        $tags['form:fulladdress'] = implode("", array_values($fullAddress));

        // replace tags
        foreach ($tags as $tag => $value) {
            if (false === JString::strpos($text, $tag)) {
                continue;
            }
            $text = implode($value, explode('{' . $tag . '}', $text));
        }

        return $text;
    }
}
