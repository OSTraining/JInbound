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

JHtml::_('jinbound.leadupdate');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$context    = 'com_jinbound.contact.' . $this->item->id;
$canEdit    = $user->authorise('core.edit', $context);
$canCheckin = $user->authorise('core.manage',
        'com_checkin') || $this->item->checked_out == $userId || $this->item->checked_out == 0;
$canEditOwn = $user->authorise('core.edit.own', $context) && $this->item->created_by == $userId;
$canChange  = $user->authorise('core.edit.state', $context) && $canCheckin;

?>
<div class="row-fluid">
    <div class="span12">
        <div class="row-fluid">
            <div class="span12">

                <div class="row-fluid">
                    <div class="span12 well">
                        <div class="row-fluid">
                            <?php
                            $this->_currentFieldset = $this->form->getFieldset('default');
                            foreach ($this->_currentFieldset as $field) :
                                ?>
                                <div class="span6">
                                    <?php
                                    $this->_currentField = $field;
                                    echo $this->loadTemplate('edit_field');
                                    ?>
                                </div>
                            <?php
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function($, d) {
        $(function() {
            $(d.body).on('jinboundleadupdate', function(e, response) {
                if (!(response && response.success)) {
                    return;
                }
                var cid = response.request.campaign_id, container = $('.current-statuses-' + cid);
                if (!container.length) {
                    return;
                }
                var html = '<div class="row-fluid"><div class="span4 status-name"></div><div class="span3 status-date"></div><div class="span4 status-author"></div></div>';
                container.empty();
                $(response.list[cid]).each(function(i, el) {
                    console.log(el);
                    var inner = $(html);
                    inner.find('.status-name').text(el.name);
                    inner.find('.status-date').text(el.created);
                    inner.find('.status-author').text(el.created_by_name);
                    container.append(inner);
                });
            });
        });
    })(jQuery, document);
</script>
