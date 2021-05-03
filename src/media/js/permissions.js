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

;(function($) {
    $.fn.permissions = function() {
        $(this)
            .attr('onchange', null)
            .on('change', function(event) {
                var icon = document.getElementById('icon_' + this.id);
                icon.removeAttribute('class');
                icon.setAttribute('style', 'background: url(../media/system/images/modal/spinner.gif); display: inline-block; width: 16px; height: 16px');

                var id = this.id.replace('rules_', '');
                var lastUnderscoreIndex = id.lastIndexOf('_');

                var formData = {
                    option: 'com_jinbound',
                    task  : 'saverules',
                    comp  : 'com_jinbound.' + $(this.form.asset).val(),
                    action: id.substring(0, lastUnderscoreIndex),
                    rule  : id.substring(lastUnderscoreIndex + 1),
                    value : $(this).val(),
                    title : $(this).attr('title')
                };

                $.post(this.form.action, formData)
                    .fail(function(jqXHR, textStatus, error) {
                        // Remove the spinning icon.
                        icon.removeAttribute('style');

                        Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));

                        window.scrollTo(0, 0);

                        icon.setAttribute('class', 'icon-cancel');

                    })
                    .done(function(response) {
                        // Remove the spinning icon.
                        icon.removeAttribute('style');

                        if (response.data) {
                            // Check if everything is OK
                            if (response.data.result === true) {
                                icon.setAttribute('class', 'icon-save');

                                console.log($(event.target));

                                $(event.target).parents().next("td").find("span")
                                    .removeClass()
                                    .addClass(response['data']['class'])
                                    .html(response.data.text);
                            }
                        }

                        // Render messages, if any. There are only message in case of errors.
                        if (typeof response.messages === 'object' && response.messages !== null) {
                            Joomla.renderMessages(response.messages);

                            if (response.data && response.data.result === true) {
                                icon.setAttribute('class', 'icon-save');
                            }
                            else {
                                icon.setAttribute('class', 'icon-cancel');
                            }
                            window.scrollTo(0, 0);
                        }
                    });
            });
    }
})(jQuery);
