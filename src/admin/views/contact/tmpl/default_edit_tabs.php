<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2020 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of jInbound.
 *
 * jInbound is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * jInbound is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with jInbound.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('JPATH_PLATFORM') or die;

?>
    <div id="jinbound_default_tabset">
<?php
echo JHtml::_('jinbound.startTabSet', 'jinbound_default_tabs', array('active' => 'profile_tab'));
$templates = array(
    'default'   => false,
    'campaigns' => 'campaigns'
);
foreach ($this->form->getFieldsets() as $name => $fieldset) :
    $template = array_key_exists($name, $templates) ? $templates[$name] : 'fields';
    if ($template) :
        $fieldsetLabel = $fieldset->label ?: sprintf('COM_JINBOUND_LEAD_FIELDSET_%s', $name);
        echo JHtml::_(
            'jinbound.addTab',
            'jinbound_default_tabs',
            $name . '_tab',
            JText::_($fieldsetLabel, true)
        );
        $this->_currentFieldsetName = $name;
        echo $this->loadTemplate("edit_tabs_page_$template");
        unset($this->_currentFieldsetName);
        echo JHtml::_('jinbound.endTab');
    endif;

    $tabs = array();
    if ('details' === $name) :
        $tabs[] = 'forms';
    else :
        if ('campaigns' === $name) :
            $tabs[] = 'notes';
            $tabs[] = 'tracks';
        endif;
    endif;
    if (!empty($tabs)) :
        foreach ($tabs as $tab) :
            echo JHtml::_(
                'jinbound.addTab',
                'jinbound_default_tabs',
                $tab . '_tab',
                JText::_('COM_JINBOUND_LEAD_FIELDSET_' . $tab, true)
            );
            echo $this->loadTemplate("edit_tabs_page_$tab");
            echo JHtml::_('jinbound.endTab');
        endforeach;
    endif;
endforeach;
echo JHtml::_('jinbound.endTabSet');
