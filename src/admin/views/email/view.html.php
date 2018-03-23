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

defined('_JEXEC') or die;

class JInboundViewEmail extends JInboundItemView
{
    /**
     * @var object
     */
    protected $emailtags = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var JInboundModelReports $reports_model */
        $reports_model = JInboundBaseModel::getInstance('Reports', 'JInboundModel');
        $reports_tags  = $reports_model->getReportEmailTags();

        $reports_tips = JText::_('COM_JINBOUND_TIPS_REPORTS_TAGS');
        if (!empty($reports_tags)) {
            $reports_tips .= '<ul>';
            foreach ($reports_tags as $tag) {
                $reports_tips .= '<li>{%' . JInboundHelperFilter::escape($tag) . '%}</li>';
            }
            $reports_tips .= '</ul>';
        }

        $this->emailtags = (object)array(
            'campaign' => JText::_('COM_JINBOUND_TIPS_JFORM_EMAIL_TIPS'),
            'report'   => $reports_tips
        );

        parent::display($tpl);
    }

    public function addToolBar()
    {
        parent::addToolBar();

        JToolbarHelper::custom('email.test', 'mail.png', 'mail_f2.png', 'COM_JINBOUND_EMAIL_TEST', false);
    }
}
