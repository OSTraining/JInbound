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

defined('_JEXEC') or die;

class JInboundViewEmail extends JInboundItemView
{
    /**
     * @var object
     */
    protected $emailtags = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var JInboundModelReports $reports_model */
        $reports_model = JInboundBaseModel::getInstance('Reports', 'JInboundModel');
        $reports_tags  = $reports_model->getReportEmailTags();

        $reports_tips = JText::_('COM_JINBOUND_TIPS_REPORTS_TAGS');
        if (!empty($reports_tags)) {
            $reports_tips .= '<ul>';
            foreach ($reports_tags as $tag) {
                $reports_tips .= '<li>{%' . JInboundHelperFilter::escape($tag) . '%}</li>';
            }
            $reports_tips .= '</ul>';
        }

        $this->emailtags = (object)array(
            'campaign' => JText::_('COM_JINBOUND_TIPS_JFORM_EMAIL_TIPS'),
            'report'   => $reports_tips
        );

        parent::display($tpl);
    }

    public function addToolBar()
    {
        parent::addToolBar();

        JToolbarHelper::custom('email.test', 'mail.png', 'mail_f2.png', 'COM_JINBOUND_EMAIL_TEST', false);
    }
}
