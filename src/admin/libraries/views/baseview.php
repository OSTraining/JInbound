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

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Extension\Licensed;
use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

class JInboundBaseView extends JViewLegacy
{
    /**
     * @var JApplicationCms
     */
    public $app = null;

    /**
     * @var Licensed
     */
    protected $extension = null;

    /**
     * @var string
     */
    public $sortFunction = null;

    /**
     * jInboundBaseView constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->app       = JFactory::getApplication();
        $this->extension = Factory::getExtension('com_jinbound', 'component');

        // set the layout paths, in order of importance
        $root            = $this->app->isAdmin() ? JInboundHelperPath::admin() : JInboundHelperPath::site();
        $layout_override = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/com_jinbound/' . $this->getName();
        $common_override = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/com_jinbound/_common';

        $this->addTemplatePath($root . '/views/_common');
        $this->addTemplatePath($root . '/views/' . $this->getName() . '/tmpl');

        if (is_dir($layout_override)) {
            $this->addTemplatePath($layout_override);
        }
        if (is_dir($common_override)) {
            $this->addTemplatePath($common_override);
        }

        $this->sortFunction = 'searchtools.sort';
    }

    /**
     * @param string $tpl
     * @param string $layout
     *
     * @return string
     * @throws Exception
     */
    public function loadTemplate($tpl = null, $layout = null)
    {
        $oldLayout = $this->getLayout();
        if ($layout) {
            $this->setLayout($layout);
        }

        $return = parent::loadTemplate($tpl);
        $this->setLayout($oldLayout);

        return $return;
    }
}

class JInboundView extends JInboundBaseView
{
    public static $option = 'com_jinbound';

    public $viewItemName = '';

    public $sidebarItems = null;

    /**
     * @var string
     */
    public $viewClass = null;

    /**
     * @var string
     */
    public $viewName = null;

    /**
     * @var string
     */
    public $tpl = null;

    /**
     * @var JObject
     */
    protected $state = null;

    /**
     * @var string
     */
    protected $context = null;

    /**
     * @var Registry
     */
    public $params = null;

    /**
     * @var bool
     */
    public $raw = false;

    /**
     * @var Registry
     */
    public $cparams = null;

    /**
     * @var string
     */
    public $pageclass_sfx = null;

    /**
     * @var bool
     */
    public $show_page_heading = false;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $profiler = JProfiler::getInstance('Application');
        $profiler->mark('onJInboundViewDisplayStart');

        $this->viewClass = 'jinbound_component';
        if (JInbound::version()->isCompatible('3.0.0')) {
            $this->viewClass .= ' jinbound_bootstrap';
        } else {
            $this->viewClass .= ' jinbound_legacy';
        }
        // add the view as a class as well
        $this->viewClass .= ' jinbound_view_' . JInboundHelperFilter::escape($this->_name);
        $this->viewName  = $this->getName();

        // are we in component view?
        $this->tpl = 'component' == $this->app->input->getCmd('tmpl');
        if ($this->app->isClient('administrator')) {
            $this->addToolbar();
            $this->addMenuBar();
        } else {
            // Not admin, Initialise variables
            $model = $this->getModel();

            $this->state         = $model->getState();
            $this->context       = $model->getContext();
            $this->params        = $this->state->get('params') ?: new Registry(); // these are page params only... ?
            $this->raw           = ($this->app->input->getCmd('format') == 'raw');
            $this->cparams       = JComponentHelper::getParams('com_jinbound');
            $this->pageclass_sfx = $this->escape($this->params->get('pageclass_sfx'));

            // show heading?
            if ($menuparams = $this->state->get('parameters.menu')) {
                $this->show_page_heading = $menuparams->get('show_page_heading');
            } else {
                $this->show_page_heading = $this->state->get('show_page_heading');
            }
        }

        $this->_prepareDocument();

        $profiler->mark('onJInboundViewDisplayEnd');

        parent::display($tpl);
    }

    /**
     * used to add administrator toolbar
     */
    public function addToolBar()
    {
        if ($this->app->isClient('administrator')) {
            // set the default title
            $name = ('contacts' === $this->_name ? 'leads' : $this->_name);
            JToolBarHelper::title(JText::_(strtoupper(JInbound::COM . '_' . $name)), 'jinbound-' . strtolower($name));

            if (JFactory::getUser()->authorise('core.manage', JInbound::COM)) {
                JToolBarHelper::preferences(JInbound::COM);
            }

            JToolBarHelper::divider();
        }
    }

    public function addMenuBar()
    {

        // only fire in administrator
        if (!$this->app->isClient('administrator')) {
            return;
        }

        $vName  = $this->app->input->get('view', '', 'cmd');
        $task   = $this->app->input->get('task', '', 'cmd');
        $option = $this->app->input->get('option', '', 'cmd');

        if (empty($vName)) {
            $vName = 'dashboard';
        }

        $vName = strtolower($vName);
        // Dashboard
        $this->addSubMenuEntry(
            JText::_('COM_JINBOUND_DASHBOARD'),
            JInboundHelperUrl::_(),
            $option == 'com_jinbound' && in_array($vName, array('', 'dashboard')) && '' == $task
        );

        $subMenuItems = array(
            'campaigns'  => 'CAMPAIGNS_MANAGER',
            'emails'     => 'LEAD_NURTURING_MANAGER',
            'pages'      => 'PAGES',
            'contacts'   => 'LEADS',
            'reports'    => 'REPORTS',
            'statuses'   => 'STATUSES',
            'priorities' => 'PRIORITIES',
            'forms'      => 'FORMS',
            'fields'     => 'FIELDS'
        );

        if (defined('JDEBUG') && JDEBUG) {
            $subMenuItems['tracks'] = 'TRACKS';
        }

        foreach ($subMenuItems as $sub => $txt) {
            $single = JInboundInflector::singularize($sub);
            if (!JFactory::getUser()->authorise('core.manage', JInbound::COM . '.' . $single)) {
                continue;
            }
            $label  = JText::_(strtoupper(JInbound::COM . "_$txt"));
            $href   = JInboundHelperUrl::_(array('view' => $sub));
            $active = ($vName == $sub && JInbound::COM == $option);
            if ('statuses' === $sub) {
                if (JInbound::version()->isCompatible('3.0.0')) {
                    $this->addSubMenuEntry('<hr style="padding:0;margin:0"/>', 'javascript:', false);
                }
                $this->addSubMenuEntry(
                    JText::_('JCATEGORIES'),
                    JInboundHelperUrl::_(
                        array(
                            'option'    => 'com_categories',
                            'view'      => 'categories',
                            'extension' => 'com_jinbound'
                        )
                    ),
                    ('categories' == $vName && 'com_categories' == $option)
                );
            }
            $this->addSubMenuEntry($label, $href, $active);
        }

        // trigger a plugin event to allow other extensions to add their own views
        JEventDispatcher::getInstance()->trigger('onJinboundBeforeMenuBar', array(&$this));

        $this->renderSidebar();
    }

    public function addSubMenuEntry($label, $href, $active)
    {
        if (!is_array($this->sidebarItems)) {
            $this->sidebarItems = array();
        }
        $this->sidebarItems[] = array($label, $href, $active);
    }

    public function renderSidebar()
    {
        if (!empty($this->sidebarItems)) {
            foreach ($this->sidebarItems as $sidebarItem) {
                list($label, $href, $active) = $sidebarItem;
                if (class_exists('JHtmlSidebar')) {
                    JHtmlSidebar::addEntry($label, $href, $active);
                } else {
                    JSubMenuHelper::addEntry($label, $href, $active);
                }
            }
        }
        $this->sidebar = false;
        if (class_exists('JHtmlSidebar')) {
            $this->sidebar = JHtmlSidebar::render();
        }
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {

        $doc    = JFactory::getDocument();
        $canAdd = method_exists($doc, 'addStyleSheet');
        JInbound::loadJsFramework();

        // we don't want to run this whole function in admin,
        // but there's still a bit we need - specifically, styles for header icons
        // if we're in admin, just load the stylesheet and bail
        if ($this->app->isClient('administrator')) {
            if ($canAdd) {
                $doc->addStyleSheet(JInboundHelperUrl::media() . '/css/admin.stylesheet.css');
            }
            return;
        }

        $doc->addStyleSheet(JInboundHelperUrl::media() . '/css/stylesheet.css');

        $menus   = $this->app->getMenu();
        $pathway = $this->app->getPathway();
        $title   = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_JINBOUND_DEFAULT_PAGE_TITLE'));
        }
        $title = $this->params->get('page_title', '');
        if (empty($title)) {
            $title = $this->app->get('sitename');
        } elseif ($this->app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        } elseif ($this->app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
        }
        $this->document->setTitle($title);

        // set the path using another class method so we can override in each view
        $path = $this->getBreadcrumbs($menu);
        // add the crumbs, if there are any
        if (!empty($path)) {
            foreach ($path as $item) {
                $pathway->addItem($item['title'], $item['url']);
            }
        }
    }

    /**
     * This should be overridden in each parent class!
     *
     * @param array
     *
     * @return array
     */
    public function getBreadcrumbs(&$menu)
    {
        return array();
    }

    /**
     * @return string
     */
    public function renderFilters()
    {
        // STUB
        return '';
    }

    /**
     * @param string $title
     * @param string $url
     *
     * @return array
     */
    public function getCrumb($title, $url = '')
    {
        return array('title' => $title, 'url' => $url);
    }
}
