<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

$this->optionsInputName = 'options';

JText::script('COM_JINBOUND_CANNOT_REMOVE_LAST_OPTION');

?>
    <script type="text/javascript">
        (function($) {
            $(function() {
                var clone, before, parent;
                try {
                    $('#<?php echo $this->input->id; ?>_sidebar').tabs();
                }
                catch (err) {
                    console.log(err);
                }

                var fixOrdering = function() {
                    var order = [];
                    $("#<?php echo $this->input->id; ?>_elements li").each(function(idx, el) {
                        order.push($(el).attr('data-id'));
                    });
                    $("#<?php echo $this->input->id; ?>_ordering").val(order.join('|'));
                };

                $("#<?php echo $this->input->id; ?>_elements, #<?php echo $this->input->id; ?>_fields").sortable({
                    connectWith: ".<?php echo $this->input->id; ?>_connected",
                    revert     : false,
                    placeholder: "ui-state-highlight",
                    cancel     : ".ui-state-disabled",
                    helper     : 'clone',
                    start      : function(event, ui) {
                        console.log('Starting sort');

                        var orig = $(ui.item);
                        console.log(orig);
                        console.log(orig.attr('data-multi'));

                        parent = orig.parent();
                        console.log(parent);
                        console.log(parent.attr('id'));

                        clone = before = false;

                        if (orig.attr('data-multi') && !parent.attr('id').match(/_elements$/)) {
                            orig.show();
                            clone = orig.clone();
                            before = orig.prev();
                        }

                        console.log(clone);
                        console.log(before);
                        console.log('Started sort');
                    },
                    stop       : function(event, ui) {
                        console.log('Stopping sort');

                        var orig = $(ui.item);
                        console.log(orig);
                        console.log(orig.parent());

                        if (orig.parent().attr('id') == parent.attr('id')) {
                            console.log('Stopped sort');
                            fixOrdering();
                            return;
                        }

                        if (orig && orig.attr('data-multi')) {
                            var id = orig.attr('data-id'), reg = /_[0-9]*$/, del = false;
                            console.log(id);
                            if (id.match(reg)) {
                                console.log('Found increment');
                                id = id.replace(reg, '');
                                del = (0 != orig.parent().find('[data-id=' + id + ']').length);
                            }
                            else {
                                console.log('No increment');
                                var formid = id, form, num;

                                num = parseInt(orig.parent().find('[data-id^=' + id + ']').length, 10)
                                console.log(num);

                                id = id + '_' + num;
                                console.log(id);

                                form = $('#<?php echo $this->input->id; ?>_' + id);
                                console.log(form);

                                if (0 == form.length) {
                                    console.log('No form!');

                                    var oldform = $('#<?php echo $this->input->id; ?>_' + formid);
                                    console.log(oldform);

                                    form = oldform.clone();
                                    console.log(form);

                                    form.attr('id', '<?php echo $this->input->id; ?>_' + id);
                                    form.find('label').each(function(idx, el) {
                                        console.log($(el));
                                        $(el).attr('for', $(el).attr('for') + '_' + num);
                                    });
                                    form.find('input').each(function(idx, el) {
                                        console.log($(el));
                                        $(el).attr('id', $(el).attr('id') + '_' + num);
                                        $(el).attr('name', $(el).attr('name').replace('[' + formid + ']', '[' + formid + '_' + num + ']'));
                                    });
                                    oldform.after(form);
                                    oldform.find('input[name*=enabled]').val(0);
                                }
                            }

                            if (del) {
                                orig.remove();
                            }
                            else {
                                orig.attr('data-id', id);
                            }
                        }

                        if (before && before.length) {
                            before.after(clone);
                        }
                        else if (clone) {
                            parent.prepend(clone);
                        }

                        fixOrdering();

                        console.log('Stopped sort');
                    }
                });
                $("#<?php echo $this->input->id; ?>_elements").on("sortreceive", function(e, ui) {
                    console.log('Receiving sort - elements');
                    $('#<?php echo $this->input->id; ?>_' + ui.item.attr('data-id') + '_enabled').val(1);
                    fixOrdering();
                });
                $("#<?php echo $this->input->id; ?>_fields").on("sortreceive", function(e, ui) {
                    console.log('Receiving sort - fields');
                    $(ui.item).removeClass('btn-primary');
                    $('#<?php echo $this->input->id; ?>_' + ui.item.attr('data-id') + '_enabled').val(0);
                    fixOrdering();
                });
                $(document).on('click', "#<?php echo $this->input->id; ?>_elements .btn-block", function(e) {
                    $("#<?php echo $this->input->id; ?>_elements .btn-block.btn-primary").removeClass('btn-primary');
                    $(this).addClass('btn-primary');
                    var tabs = $('#<?php echo $this->input->id; ?>_sidebar');
                    if ('function' == typeof tabs.tabs) {
                        tabs.tabs('refresh');
                        tabs.tabs('option', 'active', -1);
                    }
                    $('.<?php echo $this->input->id; ?>_fieldsettingsform').hide();
                    $('#<?php echo $this->input->id; ?>_' + $(this).attr('data-id')).show();
                });
                $(document).on('change', ".<?php echo $this->input->id; ?>_fieldsettingsform input[name*=title]", function(e) {
                    var title = $(this).val(), data = $(this).attr('name').replace(/^.*?\[([^\]]*?)\]\[title\]$/, '$1');
                    if (data) {
                        var btn = $('#<?php echo $this->input->id; ?>_elements [data-id=' + data + ']');
                        console.log(btn);
                        btn.text(title);
                    }
                });

                fixOrdering();

                console.log('Hiding default');
                $('.formbuilder-default-option').hide();

                $(document).on('click', '.formbuilder-option-add', function(e) {
                    console.log('adding option');
                    $(e.target).closest('.formbuilder-option').parent().append($(e.target).closest('.formbuilder-field-options').find('.formbuilder-default-option').clone().show().removeClass('formbuilder-default-option'));
                });

                $(document).on('click', '.formbuilder-option-del', function(e) {
                    console.log('removing option');
                    var opt = $(this).closest('.formbuilder-option');
                    if (!(opt.closest('.formbuilder-field-options-stage').find('.formbuilder-option').length > 1)) {
                        alert(Joomla.JText._('COM_JINBOUND_CANNOT_REMOVE_LAST_OPTION'));
                        return;
                    }
                    opt.empty().remove();
                });

                $(document).on('click', '.formbuilder-option-move', function(e) {
                    console.log('moving option');
                    var opt = $(this).closest('.formbuilder-option'), target;
                    if ($(this).hasClass('formbuilder-option-up')) {
                        console.log('moving option up');
                        target = opt.prev('.formbuilder-field-options-stage .formbuilder-option');
                        console.log(target);
                        if (target.length) {
                            target.before(opt);
                        }
                    }
                    else {
                        console.log('moving option down');
                        target = opt.next('.formbuilder-field-options-stage .formbuilder-option');
                        console.log(target);
                        if (target.length) {
                            target.after(opt);
                        }
                    }
                });
            });
        })(jQuery);
    </script>

    <div id="<?php echo $this->input->id; ?>" class="container-fluid">
        <ul id="<?php echo $this->input->id; ?>_elements" class="well <?php echo $this->input->id; ?>_connected">
            <?php
            if (!empty($this->value)) :
                foreach ($this->value as $ename => $element) :
                    if (!is_array($element) || (is_array($element) && 0 == $element['enabled']) || '__ordering' == $ename) :
                        continue;
                    endif;
                    switch ($ename) :
                        case 'first_name':
                        case 'last_name':
                        case 'email':
                            $class = ' ui-state-disabled';
                            break;
                        default:
                            $class = '';
                    endswitch;
                    ?>
                    <li class="btn btn-block<?php echo $class; ?>"
                        data-id="<?php echo $this->escape($ename); ?>"><?php echo $this->escape($element['title']); ?></li>
                <?php

                endforeach;
            endif;

            ?>
        </ul>
    </div>

    <div>
        <input id="<?php echo $this->input->id; ?>_value" name="<?php echo $this->escape($this->input->name); ?>"
               type="<?php echo (JInboundHelper::config("debug", 0)) ? 'text' : 'hidden'; ?>" value=""/>
        <input id="<?php echo $this->input->id; ?>_ordering"
               name="<?php echo $this->escape($this->input->name . '[__ordering]'); ?>"
               type="<?php echo (JInboundHelper::config("debug", 0)) ? 'text' : 'hidden'; ?>"
               value="<?php echo $this->escape((array_key_exists('__ordering',
                   $this->value) ? $this->value['__ordering'] : '')); ?>"/>
    </div>
<?php if (JInboundHelper::config("debug", 0)) : ?>
    <h4>Values:</h4>
    <pre><?php print_r($this->value); ?></pre>
<?php endif; ?>
