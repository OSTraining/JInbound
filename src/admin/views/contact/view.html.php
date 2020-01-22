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

class JInboundViewContact extends JInboundItemView
{
    function display($tpl = null, $safeparams = false)
    {
        $this->notes = $this->get('Notes');
        if (!$this->hasCampaigns()) {
            $this->app->enqueueMessage(JText::_('COM_JINBOUND_NO_CAMPAIGNS_YET_ERROR'), 'error');
            $this->app->redirect(JRoute::_('index.php?option=com_jinbound&view=contacts', false));
        }
        return parent::display($tpl, $safeparams);
    }

    public function hasCampaigns()
    {
        $db        = JFactory::getDbo();
        $campaigns = $db->setQuery($db->getQuery(true)
            ->select('Campaign.id AS value, Campaign.name as text')
            ->from('#__jinbound_campaigns AS Campaign')
            ->where('Campaign.published = 1')
            ->group('Campaign.id')
        )->loadObjectList();
        return !empty($campaigns);
    }

    public function renderFormField($page_id, $field, $value)
    {
        $html       = array();
        $fromplugin = '';
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onJinboundFormbuilderRenderValue', array(&$fromplugin, $page_id, $field, $value));
        if (!empty($fromplugin)) {
            return $fromplugin;
        }
        if (is_object($value) || is_array($value)) {
            $array = (array)$value;
            if (1 === count($array)) {
                $html[] = $this->escape(array_shift($array));
            } else {
                $html[] = '<ul>';
                foreach ($array as $k => $v) {
                    $html[] = '<li>' . $this->escape($v) . '</li>';
                }
                $html[] = '</ul>';
            }
        } else {
            $html[] = $this->escape($value);
        }
        return implode("\n", $html);
    }
}
