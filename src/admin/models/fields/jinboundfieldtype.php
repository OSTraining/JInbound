<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2020 Joomlashack.com. All rights reserved
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

JFormHelper::loadFieldClass('list');

class JFormFieldJInboundFieldType extends JFormFieldList
{
    public $type = 'Jinboundfieldtype';

    protected function getOptions()
    {
        $dispatcher = JDispatcher::getInstance();
        // initialize our array for the field types
        // we're holding them here because we want to be able to sort them later
        $types = array();
        // ignored field types
        $ignored = array();
        if ($this->element['ignoredfields']) {
            $ignored = explode(',', (string)$this->element['ignoredfields']);
        }
        // paths to search for files
        $paths = array(
            JPATH_LIBRARIES . '/joomla/form/fields'
        ,
            JInboundHelperPath::library() . '/fields'
        );
        // files containing field classes
        $files = array();
        // trigger plugins
        $dispatcher->trigger('onJInboundBeforeListFieldTypes', array(&$types, &$ignored, &$paths, &$files));
        // get files from the paths
        foreach ($paths as $path) {
            if (!JFolder::exists($path)) {
                continue;
            }
            $search = JFolder::files($path, '.php$');
            if (is_array($search)) {
                $files = array_merge($files, $search);
            }
        }
        // go ahead & loop through our found fields, and add them to the stack if they're not being ignored
        if (!empty($files)) {
            foreach ($files as $filename) {
                $name = preg_replace('/^(.*?)\.(.*)$/', '\1', $filename);
                if (in_array($name, $ignored)) {
                    continue;
                }
                $types[] = $name;
            }
        }
        // sort field types alphabetically
        asort($types);
        $types = array_values($types);
        $list  = array();
        // loop these types & create options
        for ($i = 0; $i < count($types); $i++) {
            $list[] = JHtml::_('select.option', $types[$i], $types[$i]);
        }
        // send back our select list
        return array_merge(parent::getOptions(), $list);
    }
}
