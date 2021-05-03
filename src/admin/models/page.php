<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

use Joomla\String\StringHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This models supports retrieving a location.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelPage extends JInboundAdminModel
{
    protected $context = 'com_jinbound.page';

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return bool|JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option . '.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if (!$form) {
            return false;
        }

        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        // remove the sidebar stuff if layout isn't "a" or empty
        $template = strtolower($app->input->get('set', $form->getValue('layout', 'A'), 'cmd'));
        if (!empty($template) && 'a' !== $template) {
            if (StringHelper::strlen($template) == 1) {
                $template = StringHelper::strtoupper($template);
            }
            $form->setValue('layout', null, $template);
        }

        if (!$user->authorise('core.edit.state', 'com_jinbound.page')) {
            $form->setFieldAttribute('published', 'readonly', 'true');
        }

        return $form;
    }
}
