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

?>

    <div id="jinbound_default_tabset">
<?php
echo JHtml::_('jinbound.startTabSet', 'jinbound_default_tabs', array('active' => 'profile_tab'));
$templates = array(
    'default'   => false
,
    'campaigns' => 'campaigns'
);
foreach ($this->form->getFieldsets() as $name => $fieldset) {
    $template = array_key_exists($name, $templates) ? $templates[$name] : 'fields';
    if ($template) {
        echo JHtml::_('jinbound.addTab', 'jinbound_default_tabs', $name . '_tab',
            JText::_('COM_JINBOUND_LEAD_FIELDSET_' . $name, true));
        $this->_currentFieldsetName = $name;
        echo $this->loadTemplate("edit_tabs_page_$template");
        unset($this->_currentFieldsetName);
        echo JHtml::_('jinbound.endTab');
    }
    $tabs = array();
    if ('details' === $name) {
        $tabs[] = 'forms';
    } else {
        if ('campaigns' === $name) {
            $tabs[] = 'notes';
            $tabs[] = 'tracks';
        }
    }
    if (!empty($tabs)) {
        foreach ($tabs as $tab) {
            echo JHtml::_('jinbound.addTab', 'jinbound_default_tabs', $tab . '_tab',
                JText::_('COM_JINBOUND_LEAD_FIELDSET_' . $tab, true));
            echo $this->loadTemplate("edit_tabs_page_$tab");
            echo JHtml::_('jinbound.endTab');
        }
    }
}
echo JHtml::_('jinbound.endTabSet');
