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
