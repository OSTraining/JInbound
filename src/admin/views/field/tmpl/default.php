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

echo $this->loadTemplate('edit');
?>
<script type="text/javascript">
    (function($, d) {
        $(d).ready(function() {
            $(d).on('change', '#jform_type', function(e) {
                var o = $('div[data-id="jform_params_opts"]');
                if (o.length) {
                    switch ($(e.target).find(':selected').val()) {
                        case 'checkbox':
                        case 'checkboxes':
                        case 'list':
                        case 'radio':
                        case 'groupedlist':
                            o.show();
                            break;
                        default:
                            o.hide();
                            break;
                    }
                }
            });
            $('#jform_type').trigger('click');
        });
    })(jQuery, document);
</script>
