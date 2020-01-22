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

class JInboundControllerContact extends JInboundBaseController
{
    public function status()
    {
        $this->_changeContact('status');
    }

    private function _changeContact($how)
    {
        $app      = JFactory::getApplication();
        $id       = $app->input->getInt('id');
        $campaign = $app->input->getInt('campaign_id');
        $value    = $app->input->getInt('value');
        $model    = $this->getModel('Contact', 'JInboundModel', array('ignore_request' => true));

        $result   = $model->$how($id, $campaign, $value);

        $list     = array();
        switch ($how) {
            case 'priority':
                $list = JInboundHelperContact::getContactPriorities($id);
                break;

            case 'status':
                $statuses  = JInboundHelperContact::getContactStatuses($id);
                $campaigns = JInboundHelperContact::getContactCampaigns($id);
                $list      = array();
                if (!empty($campaigns)) {
                    foreach ($campaigns as $c) {
                        if (array_key_exists($c->id, $statuses)) {
                            $list[$c->id] = $statuses[$c->id];
                        }
                    }
                }
                break;
        }

        $plugin_results = JEventDispatcher::getInstance()
            ->trigger('onJInboundAfterJsonChangeState', array(
                $how,
                $id,
                $campaign,
                $value,
                $result
            ));

        echo json_encode(array(
            'success' => $result,
            'list'    => $list,
            'request' => array(
                'contact_id'  => $id,
                'campaign_id' => $campaign,
                "{$how}_id"   => $value
            ),
            'plugin'  => $plugin_results
        ));

        jexit();
    }

    public function priority()
    {
        $this->_changeContact('priority');
    }
}
