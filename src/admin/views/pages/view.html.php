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

defined('JPATH_PLATFORM') or die;

class JInboundViewPages extends JInboundListView
{
    /**
     * @param null $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $filter = (array)$this->app->getUserStateFromRequest(
            $this->get('State')->get('context') . '.filter',
            'filter',
            array(),
            'array'
        );

        foreach (array('categories', 'campaigns') as $var) {
            $single = JInboundInflector::singularize($var);
            $$var   = $this->get(ucwords($var) . 'Options');

            if (count($$var) <= 1) {
                JFactory::getApplication()
                    ->enqueueMessage(JText::_('COM_JINBOUND_NO_' . strtoupper($var) . '_YET'), 'warning');
            }

            $this->addFilter(
                JText::_('COM_JINBOUND_SELECT_' . strtoupper($single)),
                'filter[' . $single . ']',
                $$var,
                array_key_exists($single, $filter) ? $filter[$single] : ''
            );
        }

        parent::display($tpl);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function addToolBar()
    {
        if (JFactory::getUser()->authorise('core.create', JInboundHelper::COM . '.report')) {
            JToolBarHelper::custom(
                'reports.exportpages',
                'download.png',
                'download_f2.png',
                'COM_JINBOUND_EXPORT_PAGES',
                false
            );
        }

        parent::addToolBar();
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return string[]
     */
    protected function getSortFields()
    {
        return array(
            'Page.name'      => JText::_('COM_JINBOUND_LANDINGPAGE_NAME'),
            'Page.published' => JText::_('COM_JINBOUND_PUBLISHED'),
            'Page.category'  => JText::_('COM_JINBOUND_CATEGORY'),
            'Page.hits'      => JText::_('COM_JINBOUND_VIEWS'),
            'submissions'    => JText::_('COM_JINBOUND_SUBMISSIONS'),
            'conversions'    => JText::_('COM_JINBOUND_CONVERSIONS')
        );
    }
}
