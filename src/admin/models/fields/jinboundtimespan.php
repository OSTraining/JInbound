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

if (!defined('JINB_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

JFormHelper::loadFieldClass('text');

class JFormFieldJinboundTimespan extends JFormFieldText
{
    protected $type = 'JinboundTimespan';

    /**
     * Builds the input element
     *
     * (non-PHPdoc)
     * @see JFormField::getInput()
     */
    protected function getInput()
    {
        $view        = $this->getView();
        $view->input = parent::getInput();
        return $view->loadTemplate();
    }

    /**
     * gets a new instance of the base field view
     *
     * @return JInboundFieldView
     */
    protected function getView()
    {
        $viewConfig = array('template_path' => dirname(__FILE__) . '/timespan');
        $view       = new JInboundFieldView($viewConfig);
        return $view;
    }
}
