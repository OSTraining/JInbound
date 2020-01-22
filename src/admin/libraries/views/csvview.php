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

class JInboundCsvView extends JInboundBaseView
{
    public function display($tpl = null)
    {
        $data = array();
        if (property_exists($this, 'data')) {
            $data = $this->data;
        }
        $fileName = $this->_name;
        if (property_exists($this, 'filename')) {
            $fileName = $this->filename;
        }
        $date = new DateTime();
        $date = $date->format('Y-m-d');

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$fileName-$date.csv\";");
        header("Content-Transfer-Encoding: binary");

        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        if (empty($data)) {
            jexit();
        }

        $out = fopen('php://output', 'w');

        $headers = array_keys(get_object_vars($data[0]));
        fputcsv($out, $headers);

        foreach ($data as $item) {
            $cols = array();
            foreach ($headers as $col) {
                $cols[] = (is_object($item->$col) || is_array($item->$col) ? json_encode($item->$col) : $item->$col);
            }
            fputcsv($out, $cols);
        }

        fclose($out);
        jexit();
    }
}
