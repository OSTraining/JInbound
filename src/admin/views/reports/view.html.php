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

class JInboundViewReports extends JInboundListView
{
    /**
     * @var string
     */
    protected $filter_change_code = null;

    /**
     * @var string
     */
    protected $campaign_filter = null;

    /**
     * @var string
     */
    protected $page_filter = null;

    /**
     * @var string
     */
    protected $priority_filter = null;

    /**
     * @var string
     */
    protected $status_filter = null;

    /**
     * @var bool
     */
    protected static $setToolbar = true;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.manage', 'com_jinbound.report')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        // Need to init state before parent method gets around to it
        $this->state = $this->getModel()->getState();

        $this->filter_change_code = $this->getReportFormFilterChangeCode();
        $this->campaign_filter    = $this->getCampaignFilter();
        $this->page_filter        = $this->getPageFilter();
        $this->priority_filter    = $this->getPriorityFilter();
        $this->status_filter      = $this->getStatusFilter();

        // @TODO: This is odd - what's really happening here?
        ob_start();
        parent::display($tpl);
        $display = ob_get_contents();
        ob_end_clean();

        JHtml::_('script', 'media/jinbound/jqplot/excanvas.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/jquery.jqplot.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/plugins/jqplot.dateAxisRenderer.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/plugins/jqplot.canvasTextRenderer.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/plugins/jqplot.categoryAxisRenderer.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/plugins/jqplot.barRenderer.min.js');
        JHtml::_('script', 'media/jinbound/jqplot/plugins/jqplot.highlighter.min.js');
        JHtml::_('stylesheet', 'media/jinbound/jqplot/jquery.jqplot.min.css');

        echo $display;
    }

    /**
     * @return string
     */
    public function getReportFormFilterChangeCode()
    {
        return "window.fetchReports("
            . "window.jinbound_leads_start, "
            . "window.jinbound_leads_limit, "
            . "jQuery('#filter_start').val(), "
            . "jQuery('#filter_end').val(), "
            . "jQuery('#filter_campaign').find(':selected').val(), "
            . "jQuery('#filter_page').find(':selected').val(), "
            . "jQuery('#filter_priority').find(':selected').val(), "
            . "jQuery('#filter_status').find(':selected').val()"
            . ");";
    }

    /**
     * @return string
     */
    public function getCampaignFilter()
    {
        $db      = JFactory::getDbo();
        $options = $db->setQuery(
            $db->getQuery(true)
                ->select('id AS value, name AS text')
                ->from('#__jinbound_campaigns')
                ->order('name ASC')
        )
            ->loadObjectList();
        array_unshift($options, (object)array('value' => '', 'text' => JText::_('COM_JINBOUND_SELECT_CAMPAIGN')));

        return JHtml::_(
            'select.genericlist',
            $options,
            'filter_campaign',
            array(
                'list.attr'   => array(
                    'onchange' => $this->filter_change_code
                ),
                'list.select' => $this->state->get('filter.campaign')
            )
        );
    }

    /**
     * @return string
     */
    public function getPageFilter()
    {
        $db      = JFactory::getDbo();
        $options = $db->setQuery(
            $db->getQuery(true)
                ->select('id AS value, name AS text')
                ->from('#__jinbound_pages')
                ->order('name ASC')
        )
            ->loadObjectList();
        array_unshift($options, (object)array('value' => '', 'text' => JText::_('COM_JINBOUND_SELECT_PAGE')));

        return JHtml::_(
            'select.genericlist',
            $options,
            'filter_page',
            array(
                'list.attr'   => array(
                    'onchange' => $this->filter_change_code
                ),
                'list.select' => $this->state->get('filter.page')
            )
        );
    }

    /**
     * @return string
     */
    public function getPriorityFilter()
    {
        $options = JInboundHelperPriority::getSelectOptions();
        array_unshift($options, (object)array('value' => '', 'text' => JText::_('COM_JINBOUND_SELECT_PRIORITY')));

        return JHtml::_(
            'select.genericlist',
            $options,
            'filter_priority',
            array(
                'list.attr'   => array(
                    'onchange' => $this->filter_change_code
                ),
                'list.select' => $this->state->get('filter.priority')
            )
        );
    }

    /**
     * @return string
     */
    public function getStatusFilter()
    {
        $options = JInboundHelperStatus::getSelectOptions();
        array_unshift($options, (object)array('value' => '', 'text' => JText::_('COM_JINBOUND_SELECT_STATUS')));

        return JHtml::_(
            'select.genericlist',
            $options,
            'filter_status',
            array(
                'list.attr'   => array(
                    'onchange' => $this->filter_change_code
                ),
                'list.select' => $this->state->get('filter.status')
            )
        );
    }

    /**
     * @param string $method
     * @param array  $state
     *
     * @return mixed
     * @throws Exception
     */
    protected function callModelMethod($method, array $state = array())
    {
        $model = JInboundBaseModel::getInstance('Reports', 'JInboundModel');

        if (method_exists($model, $method)) {
            $model->getState('init.state');
            foreach ($state as $key => $value) {
                $model->setState($key, $value);
            }

            return $model->$method();
        }

        JFactory::getApplication()->enqueueMessage('Unknown method - ' . $method, 'error');
        return null;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getVisitCount()
    {
        return $this->callModelMethod('getVisitCount');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getViewsToLeads()
    {
        return $this->callModelMethod('getViewsToLeads');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getLeadCount()
    {
        return $this->callModelMethod('getContactsCount');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getConversionCount()
    {
        return $this->callModelMethod('getConversionsCount');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getConversionRate()
    {
        return $this->callModelMethod('getConversionRate');
    }

    /**
     * @throws Exception
     */
    public function addToolBar()
    {
        $app = JFactory::getApplication();
        if ($app->isClient('administrator') && static::$setToolbar) {
            $layout = $app->input->get('layout');

            if ($layout != 'chart') {
                $icon = 'export';
                if (JInboundHelper::version()->isCompatible('3.0.0')) {
                    $icon = 'download';
                }

                if (JFactory::getUser()->authorise('core.create', 'com_jinbound.report')) {
                    JToolBarHelper::custom(
                        $this->_name . '.exportleads',
                        "{$icon}.png",
                        "{$icon}_f2.png",
                        'COM_JINBOUND_EXPORT_LEADS',
                        false
                    );
                }

                // skip parent and go to grandparent so we don't have the normal list view icons like "new" and "save"
                // @TODO: This indicates questionable inheritance structure
                $gpview = new JInboundView(array());
                $gpview->addToolbar();
            }

            static::$setToolbar = false;

            // set the title (because we're skipping the list view's addToolBar later)
            JToolBarHelper::title(JText::_('COM_JINBOUND_REPORTS'), 'jinbound-reports');
        }
    }
}
