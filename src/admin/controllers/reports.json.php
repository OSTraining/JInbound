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

class JInboundControllerReports extends JInboundBaseController
{
    /**
     * @return void
     */
    public function plot()
    {
        /** @var JInboundModelReports $model */
        $model = $this->getModel('Reports');

        $data = array();
        try {
            $state               = $model->getState();
            $start               = $state->get('filter.start', null);
            $end                 = $state->get('filter.end', null);
            $data['tick']        = $model->getTickString($start, $end);
            $data['hits']        = $model->getLandingPageHits($start, $end);
            $data['leads']       = $model->getLeadsByCreationDate($start, $end);
            $data['conversions'] = $model->getConversionsByDate($start, $end);
            foreach ($data['leads'] as $i => $lead) {
                unset($data['leads'][$i]->tracks);
            }
        } catch (Exception $e) {
            $this->send403($e);
        }

        $this->sendJson($data);
    }

    /**
     * @param Exception $exception
     */
    private function send403(Exception $exception)
    {
        if (!headers_sent()) {
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' 403 Forbidden');
        }

        $this->sendJson(array('error' => $exception->getMessage()));
    }

    /**
     * @param mixed $data
     * @param bool  $headers
     *
     * @return void
     */
    private function sendJson($data, $headers = true)
    {
        if ($headers) {
            header('Content-Type: application/json');
        }

        echo json_encode($data);
        jexit();
    }

    /**
     * @return void
     */
    public function glance()
    {
        /** @var JinboundModelReports $model */
        $model = $this->getModel('Reports');

        try {
            $state       = $model->getState();
            $start       = $state->get('filter.start', null);
            $end         = $state->get('filter.end', null);
            $hits        = $model->getLandingPageHits($start, $end);
            $leads       = $model->getLeadsByCreationDate($start, $end);
            $conversions = $model->getConversionsByDate($start, $end);

            $data = array(
                'views'            => 0,
                'leads'            => 0,
                'views-to-leads'   => 0,
                'conversion-count' => 0,
                'conversion-rate'  => 0,
                '__raw'            => array(
                    'hits'        => $hits,
                    'leads'       => $leads,
                    'conversions' => $conversions,
                    'start'       => $start,
                    'end'         => $end
                )
            );

            foreach ($hits as $hit) {
                $data['views'] += (int)$hit[1];
            }

            foreach ($leads as $lead) {
                $data['leads'] += (int)$lead[1];
            }

            foreach ($conversions as $conversion) {
                $data['conversion-count'] += (int)$conversion[1];
            }

            if (0 < $data['views']) {
                $data['views-to-leads']  = ($data['leads'] / $data['views']) * 100;
                $data['conversion-rate'] = ($data['conversion-count'] / $data['views']) * 100;
            }

            $data['views-to-leads']  = number_format($data['views-to-leads'], 2) . '%';
            $data['conversion-rate'] = number_format($data['conversion-rate'], 2) . '%';

        } catch (Exception $e) {
            $this->send403($e);
        }

        $this->sendJson($data);
    }
}
