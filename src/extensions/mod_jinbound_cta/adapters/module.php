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

defined('_JEXEC') or die;

class ModJInboundCTAModuleAdapter extends ModJInboundCTAAdapter
{
    /**
     * Renders a module
     *
     * @return string
     */
    public function render()
    {
        $id       = $this->params->get($this->pfx . 'mode_module');
        $renderer = JFactory::getDocument()->loadRenderer('module');
        $module   = $this->getModule($id);
        $params   = array('style' => 'none');
        if (is_object($module)) {
            echo $renderer->render($module, $params);
        }
    }

    protected function getModule($id)
    {
        if (!$id) {
            return false;
        }
        $db = JFactory::getDbo();
        return $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from('#__modules')
            ->where('id = ' . intval($id))
        )->loadObject();
    }
}
