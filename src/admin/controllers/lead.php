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

$e = new Exception(__FILE__);
JLog::add('JInboundControllerLead is deprecated. ' . $e->getTraceAsString(), JLog::WARNING, 'deprecated');

class JInboundControllerLead extends JInboundFormController
{
    public function save($key = null, $urlVar = null)
    {
        $app  = JFactory::getApplication();
        $data = $app->input->post->get('jform', array(), 'array');
        if (array_key_exists('formdata', $data)) {
            unset($formdata);
        }
        $data['formdata'] = json_encode($data);
        return parent::save($key, $urlVar);
    }

    public function status()
    {
        $this->_changeLead('status');
    }

    private function _changeLead($how)
    {
        $app   = JFactory::getApplication();
        $id    = $app->input->get('id');
        $value = $app->input->get('value');
        $model = $this->getModel();
        $model->$how($id, $value);
    }

    public function priority()
    {
        $this->_changeLead('priority');
    }
}
