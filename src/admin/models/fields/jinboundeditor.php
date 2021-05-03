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

JFormHelper::loadFieldClass('editor');

class JFormFieldJinboundEditor extends JFormFieldEditor
{
    public $type = 'JinboundEditor';

    public function getTags()
    {
        // Initialize variables.
        $tags = array();

        foreach ($this->element->children() as $tag) {
            // Only add <tag /> elements.
            if ($tag->getName() != 'tag') {
                continue;
            }

            // Create a new option object based on the <option /> element.
            $tmp        = new stdClass;
            $tmp->value = (string)$tag['value'];

            // Set some option attributes.
            $tmp->class = (string)$tag['class'];

            // Add the option object to the result set.
            $tags[] = $tmp;
        }

        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onJinboundEditorTags', array(&$this, &$tags));

        reset($tags);

        return $tags;
    }

    /**
     * This method is used in the form display to show extra data
     *
     */
    public function getSidebar()
    {
        $view = $this->getView();
        // set data
        $view->input = $this;
        // return template html
        return $view->loadTemplate();
    }

    /**
     * gets a new instance of the base field view
     *
     * @return JInboundFieldView
     */
    protected function getView()
    {
        $viewConfig = array('template_path' => dirname(__FILE__) . '/editor');
        $view       = new JInboundFieldView($viewConfig);
        return $view;
    }
}
