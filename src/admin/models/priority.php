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

class JInboundModelPriority extends JInboundAdminModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.priority';

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

        if (empty($form)) {
            return false;
        }

        $app = JFactory::getApplication();
        $user = JFactory::getUser();

        if (!$app->isClient('administrator')) {
            $form->setFieldAttribute('published', 'type', 'hidden');
            $form->setFieldAttribute('published', 'default', '1');
            $form->setValue('published', '1');

        } else {
            if (!$user->authorise('core.edit.state', 'com_jinbound.priority')) {
                $form->setFieldAttribute('published', 'readonly', 'true');
            }
        }

        return $form;
    }
}
