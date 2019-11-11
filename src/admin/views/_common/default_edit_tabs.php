<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
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

$fieldsets = $this->form->getFieldsets();
if (isset($fieldsets['default'])) {
    unset($fieldsets['default']);
}

if ($fieldsets) :
    ?>
    <div id="jinbound_default_tabset">
        <?php
        echo JHtml::_(
            'jinbound.startTabSet',
            'jinbound_default_tabs',
            array('active' => (isset($this->default_tab) ? $this->default_tab : 'content_tab'))
        );

        foreach ($fieldsets as $name => $fieldset) :
            $label = $fieldset->label
                ?: sprintf('COM_JINBOUND_%s_FIELDSET_%s', $this->getName(), $name);
            echo JHtml::_('jinbound.addTab', 'jinbound_default_tabs', $name . '_tab', JText::_($label, true));
            ?>
            <fieldset class="container-fluid">
                <div class="row-fluid">
                    <div class="span8">
                        <?php
                        $well = false;
                        foreach ($this->form->getFieldset($name) as $field) :
                            $label = trim($field->label . '');
                            if (empty($label)) :
                                echo $field->input;

                            else :
                                $this->_currentField = $field;
                                echo $this->loadTemplate('edit_field');
                            endif;
                            if (empty($well) && method_exists($field, 'getSidebar')) :
                                $well = $field->getSidebar();
                            endif;
                        endforeach;
                        ?>
                    </div>
                    <?php
                    if (!empty($well)) :
                        ?>
                        <div class="span4 well">
                            <?php echo $well; ?>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>
            </fieldset>
            <?php
            echo JHtml::_('jinbound.endTab');
        endforeach;

        echo JHtml::_('jinbound.endTabSet');
        ?>
    </div>
<?php
endif;
