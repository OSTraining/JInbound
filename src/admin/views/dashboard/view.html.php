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

class JInboundViewDashboard extends JInboundView
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();

        // get original data for layout and template
        $tmpl   = $app->input->getCmd('tmpl');
        $layout = $app->input->getCmd('layout');

        // get a reports view & load it's output
        require_once realpath(__DIR__ . '/../reports/view.html.php');

        $app->input->set('tmpl', 'component');
        $app->input->set('layout', 'default');
        $app->setUserState('list.limit', 10);
        $app->setUserState('list.start', 0);
        $reportView = new JInboundViewReports();

        $this->reports = (object)array(
            'glance'       => $reportView->loadTemplate(null, 'glance'),
            'script'       => $reportView->loadTemplate('script', 'default'),
            'top_pages'    => $reportView->loadTemplate('pages', 'top'),
            'recent_leads' => $reportView->loadTemplate('leads', 'recent')
        );

        // reset template and layout data
        $app->input->set('tmpl', $tmpl);
        $app->input->set('layout', $layout);

        parent::display($tpl);
    }

    /**
     * used to add administrator toolbar
     */
    public function addToolBar()
    {
        parent::addToolBar();
        if (JFactory::getUser()->authorise('core.admin', JInbound::COM)) {
            JToolbarHelper::custom('reset', 'refresh.png', 'refresh_f2.png', 'COM_JINBOUND_RESET', false);
        }
    }

    /**
     * Standard rendering of dashboard buttons
     *
     * @param string $view
     * @param string $image
     * @param string $title
     *
     * @return string
     */
    protected function renderButton($view, $image, $title)
    {
        $user = JFactory::getUser();

        $class = 'span3 btn text-center';
        if ($user->authorise('core.manage', 'com_jinbound.' . $view)) {
            $href = 'index.php?option=com_jinbound&view=' . $view;
        } else {
            $href  = sprintf("javascript:alert('%s');", JText::_('JERROR_ALERTNOAUTHOR'));
            $class .= ' disabled';
        }

        $text = sprintf(
            '<span class="btn-text">%s</span><span class="row text-center">%s</span>',
            $title,
            JHtml::_('image', 'jinbound/' . $image, null, 'class="img-rounded"', true)
        );

        return JHtml::_('link', $href, $text, sprintf('class="%s"', $class));
    }
}
