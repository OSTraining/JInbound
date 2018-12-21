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

JText::script('COM_JINBOUND_EMPTY_CUSTOM_TEMPLATE');
JText::script('COM_JINBOUND_LINK');
JText::script('COM_JINBOUND_NON_SEF_LINK');
JText::script('COM_JINBOUND_SEF_LINK');

echo $this->loadTemplate('edit');

?>
<script type="text/javascript">
    window.jinboundlayouttags = {};
    <?php foreach ($this->layouttags as $tag => $value) : ?>
    window.jinboundlayouttags.form_<?php echo $tag; ?> = '<?php echo JInboundHelperFilter::escape_js($value); ?>';
    <?php endforeach; ?>
    (function($) {
        $(document).ready(function() {
            <?php if ($this->item->id) : ?>
            $.ajax('../index.php?option=com_jinbound&task=landingpageurl&id=<?php echo (int)$this->item->id; ?>', {
                dataType: 'json',
                success : function(response) {
                    if (response) {
                        var row = $('#jform_alias').closest('.row-fluid');
                        if (response.error) {
                            row.after($('<div class="row-fluid"><div class="span12">' + response.error + '</div></div>'));
                        }
                        else if (response.sef && response.nonsef) {
                            if (response.sef === response.nonsef) {
                                var link = '<a href="' + response.sef + '" target="_blank">' + response.sef + '</a>';
                                row.after($('<div class="row-fluid"><div class="span2">' + Joomla.JText._('COM_JINBOUND_LINK') + '</div><div class="span9 offset1">' + link + '</div></div>'));
                            }
                            else {
                                var sef = '<a href="' + response.sef + '" target="_blank">' + response.sef + '</a>';
                                var non = '<a href="' + response.nonsef + '" target="_blank">' + response.nonsef + '</a>';
                                row.after($('<div class="row-fluid"><div class="span2">' + Joomla.JText._('COM_JINBOUND_NON_SEF_LINK') + '</div><div class="span9 offset1">' + non + '</div></div>'));
                                row.after($('<div class="row-fluid"><div class="span2">' + Joomla.JText._('COM_JINBOUND_SEF_LINK') + '</div><div class="span9 offset1">' + sef + '</div></div>'));
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
            var hideSidebar = function() {
                var row = $('#jform_sidebartext-lbl').closest('.row-fluid'), d = [4], hide = true, tabs, tab;
                switch ($('#jform_layout').find(':checked').val()) {
                    case '0':
                        d = [];
                    case 'A':
                        row.show();
                        break;
                    default:
                        row.hide();
                        break;
                }
                // check for tabs
                tabs = $('#jinbound_default_tabsTabs');
                tab = 'li';
                if (!tabs.length) {
                    tabs = $('#jinbound_default_tabs');
                    tab = 'dt.tabs';
                }
                if ('function' == typeof $().tab) {
                    $('.nav-tabs a').click(function(e) {
                        e.preventDefault();
                        $(this).tab('show');
                    });
                }
                else if ('function' == typeof $().tabs) {
                    $('#jinbound_default_tabsTabs').tabs("option", "disabled", d);
                    hide = false;
                }
                if (hide) {
                    try {
                        if (d.length) {
                            tabs.find(tab)[d[0]].hide();
                        }
                        else {
                            tabs.find(tab).show();
                        }
                    }
                    catch (err) {
                        try {
                            console.log(err);
                        }
                        catch (err2) {
                        }
                    }
                }

                $('.jinbound_legacy #jform_layout').find('.active').removeClass('active');
                $('.jinbound_legacy #jform_layout').find('.btn-success').removeClass('btn-success');
                $('.jinbound_legacy #jform_layout').find(':checked').next().addClass('active').addClass('btn-success')
            };

            hideSidebar();
            $('#jform_layout').find('input').change(hideSidebar);
            $('.jinbound_bootstrap #jform_layout .btn').click(hideSidebar);

            $('#jform_formid').change(function() {
                var v = $(this).find(':selected').val(), k = 'form_' + v, h = '';
                if ('undefined' !== typeof window.jinboundlayouttags[k]) {
                    h += window.jinboundlayouttags[k];
                }
                $('#jform_template_tags').empty().html(h);
            });
            $('#jform_formid').trigger('change');

        });
    })(jQuery);

    Joomla.submitbutton = function(task) {
        <?php echo $this->form->getField('template')->save(); ?>;
        if ('undefined' != typeof tinyMCE) {
            try {
                tinyMCE.execCommand('mceToggleEditor', false, 'jform_template');
            }
            catch (err) {
                console.log(err);
                return;
            }
        }
        var template = document.getElementById('jform_template');
        var radio = document.getElementById('jform_layout4');
        if (template && radio && radio.checked && !template.value.match(/\{form.*?}/g)) {
            alert(Joomla.JText._('COM_JINBOUND_EMPTY_CUSTOM_TEMPLATE'));
            return false;
        }
        Joomla.submitform(task, document.getElementById('adminForm'));
    };

</script>
