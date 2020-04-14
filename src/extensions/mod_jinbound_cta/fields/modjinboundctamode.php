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

use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

if (!defined('JINB_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

JFormHelper::loadFieldClass('list');

class JFormFieldModJInboundCTAMode extends JFormFieldList
{
    public $type = 'ModJInboundCTAMode';

    protected function getInput()
    {
        $this->insertScript();
        return parent::getInput();
    }

    protected function insertScript()
    {
        global $mod_jinbound_cta_script_loaded;
        if (is_null($mod_jinbound_cta_script_loaded)) {
            JHtml::_('jquery.framework');
            HTMLHelper::_('script', 'mod_jinbound_cta/admin.js', array('relative' => true));

            $mod_jinbound_cta_script_loaded = true;
        }
    }
}
