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

class JInboundViewContacts extends JInboundListView
{
    /**
     * @param string $tpl
     * @param bool   $safeparams
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var JInboundModelContacts $model */
        $model = $this->getModel();

        $campaigns = $model->getCampaignsOptions();

        if ($errors = $model->getErrors()) {
            throw new Exception(implode('<br />', $errors), 500);
        }

        if (!$campaigns) {
            $this->app->enqueueMessage(JText::_('COM_JINBOUND_NO_CAMPAIGNS_YET'), 'warning');
        }

        parent::display($tpl);
    }

    public function addToolBar()
    {
        $icon = 'export';
        if (JInboundHelper::version()->isCompatible('3.0.0')) {
            $icon = 'download';
        }
        // export icons
        if (JFactory::getUser()->authorise('core.create', JInboundHelper::COM . '.report')) {
            JToolBarHelper::custom('reports.exportleads', "{$icon}.png", "{$icon}_f2.png", 'COM_JINBOUND_EXPORT_LEADS',
                false);
        }
        parent::addToolBar();
    }
}
