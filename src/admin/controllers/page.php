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

class JInboundControllerPage extends JInboundPageController
{
    /**
     * @param string $key
     * @param string $urlVar
     *
     * @return bool
     * @throws Exception
     */
    public function edit($key = 'id', $urlVar = 'id')
    {
        if (!JFactory::getUser()->authorise('core.manage', 'com_jinbound.page')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }
        $model  = $this->getModel('Pages', 'JInboundModel');
        $canAdd = true;
        foreach (array('categories', 'campaigns') as $var) {
            $single = JInboundInflector::singularize($var);
            $method = 'get' . ucwords($var) . 'Options';
            $$var   = $model->$method();
            // if we don't have any categories yet, warn the user
            // there's always going to be one option in this list
            if (1 >= count($$var)) {
                JFactory::getApplication()
                    ->enqueueMessage(JText::_('COM_JINBOUND_NO_' . strtoupper($var) . '_YET'), 'error');
                $canAdd = false;
            }
        }
        if (!$canAdd) {
            $this->redirect(JInboundHelperUrl::view('pages'));
            jexit();
        }
        return parent::edit($key, $urlVar);
    }

    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'set')
    {
        $set    = JFactory::getApplication()->input->get('set', 'a', 'cmd');
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);
        $append .= '&set=' . $set;
        return $append;
    }
}
