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
<div id="<?php echo $this->input->id; ?>_sidebar">
    <ul class="nav nav-tabs">
        <li><a href="<?php echo $this->escape('#' . $this->input->id); ?>_fieldlist"
               class="active"><?php echo JText::_('COM_JINBOUND_ADD_A_FIELD'); ?></a></li>
        <li>
            <a href="<?php echo $this->escape('#' . $this->input->id); ?>_fieldsettings"><?php echo JText::_('COM_JINBOUND_FIELD_SETTINGS'); ?></a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="<?php echo $this->input->id; ?>_fieldlist" class="tab-pane active">
            <ul id="<?php echo $this->input->id; ?>_fields" class="unstyled <?php echo $this->input->id; ?>_connected">
                <?php

                foreach ($this->input->getFormFields() as $field) :
                    if (0 == $field->multi && array_key_exists($field->id,
                            $this->value) && 1 == $this->value[$field->id]['enabled']) {
                        continue;
                    }

                    ?>
                    <li class="btn btn-block"
                        data-id="<?php echo $this->escape($field->id); ?>"<?php if ((int)$field->multi) : ?> data-multi="true"<?php endif; ?>><?php echo $this->escape($field->name); ?></li>
                <?php

                endforeach;
                ?>
            </ul>
        </div>
        <div id="<?php echo $this->input->id; ?>_fieldsettings" class="tab-pane">
            <div class="container-fluid">
                <?php foreach ($this->input->getFormFields() as $field) : ?>
                    <div class="row hide <?php echo $this->input->id; ?>_fieldsettingsform container-fluid"
                         id="<?php echo $this->input->id; ?>_<?php echo $this->escape($field->id); ?>">
                        <?php
                        $this->_currentField = $field;
                        echo $this->loadTemplate('sidebar_field');
                        ?>
                    </div>
                    <?php

                    if (0 != $field->multi) :
                        $i = 1;
                        while (array_key_exists($field->id . '_' . $i, $this->value)) :
                            $newField = json_decode(json_encode($field));
                            $newField->id .= '_' . $i;

                            ?>
                            <div class="row hide <?php echo $this->input->id; ?>_fieldsettingsform container-fluid"
                                 id="<?php echo $this->input->id; ?>_<?php echo $this->escape($newField->id); ?>">
                                <?php
                                $this->_currentField = $newField;
                                echo $this->loadTemplate('sidebar_field');
                                ?>
                            </div>
                            <?php

                            $i++;
                        endwhile;
                    endif;

                    ?>

                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
