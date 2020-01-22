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

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

class JInboundViewReports extends JInboundCsvView
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.create', 'com_jinbound.report')) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        /** @var JInboundModelReports $model */
        $model = JInboundBaseModel::getInstance('Reports', 'JInboundModel');
        $state = $this->get('State');
        if (is_array($state) && !empty($state)) {
            foreach ($state as $key => $value) {
                $model->setState($key, $value);
            }
        }
        switch ($this->getLayout()) {
            case 'leads':
                $leads = $model->getRecentContacts();
                $data  = array();
                $extra = array();
                if (!empty($leads)) {
                    foreach ($leads as $idx => $lead) {
                        $formdata = new Registry();
                        $formdata->loadString($lead->formdata);
                        $leads[$idx]->formdata = $formdata->toArray();
                        if (array_key_exists('lead', $lead->formdata)
                            && is_array($lead->formdata['lead'])
                        ) {
                            $extra = array_values(
                                array_unique(
                                    array_merge($extra, array_keys($lead->formdata['lead']))
                                )
                            );
                        }
                    }
                    if (!empty($extra)) {
                        foreach ($leads as $idx => $lead) {
                            foreach ($extra as $col) {
                                $value = '';
                                if (array_key_exists('lead', $lead->formdata)
                                    && is_array($lead->formdata['lead'])
                                    && array_key_exists($col, $lead->formdata['lead'])
                                ) {
                                    $value = $lead->formdata['lead'][$col];
                                }

                                $leads[$idx]->$col = $value;
                            }

                            unset($leads[$idx]->formdata);
                            $data[] = $lead;
                        }
                    }
                }
                $this->data = $data;
                break;

            case 'pages':
                $this->data = $model->getTopPages();
                break;

            default:
                throw new Exception(JText::_('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND'), 400);
        }
        $this->filename = $this->getLayout() . '-report';

        parent::display($tpl);
    }
}
