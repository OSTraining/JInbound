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
        if (JFactory::getUser()->authorise('core.admin', JInboundHelper::COM)) {
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
