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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class JInboundControllerStatuses extends JControllerAdmin
{
    /**
     * @return void
     * @throws Exception
     */
    public function setDefault()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();

        $pks = $app->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new Exception(JText::_('COM_JINBOUND_NO_STATUS_SELECTED'));
            }

            ArrayHelper::toInteger($pks);

            // Pop off the first element.
            $id    = array_shift($pks);
            $model = $this->getModel();
            $model->setDefault($id);
            $this->setMessage(JText::_('COM_JINBOUND_SUCCESS_DEFAULT_STATUS_SET'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        $this->setRedirect('index.php?option=com_jinbound&view=statuses');
    }

    public function getModel($name = 'Status', $prefix = 'JInboundModel')
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function unsetDefault()
    {
        $this->setMessage(JText::_('COM_JINBOUND_SUCCESS_SEFAULT_CANNOT_BE_UNSET'));
        $this->setRedirect('index.php?option=com_jinbound&view=statuses');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function setFinal()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();

        $pks = $app->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new Exception(JText::_('COM_JINBOUND_NO_STATUS_SELECTED'));
            }

            ArrayHelper::toInteger($pks);

            // Pop off the first element.
            $id    = array_shift($pks);
            $model = $this->getModel();
            $model->setFinal($id);
            $this->setMessage(JText::_('COM_JINBOUND_SUCCESS_FINAL_STATUS_SET'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        $this->setRedirect('index.php?option=com_jinbound&view=statuses');
    }

    public function unsetFinal()
    {
        $this->setMessage(JText::_('COM_JINBOUND_SUCCESS_FINAL_CANNOT_BE_UNSET'));
        $this->setRedirect('index.php?option=com_jinbound&view=statuses');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function setActive()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app= JFactory::getApplication();

        $pks = $app->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new Exception(JText::_('COM_JINBOUND_NO_STATUS_SELECTED'));
            }

            ArrayHelper::toInteger($pks);

            // Pop off the first element.
            $id    = array_shift($pks);
            $model = $this->getModel();
            $model->setActive($id);
            $this->setMessage(JText::_('COM_JINBOUND_SUCCESS_ACTIVE_STATUS_SET'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        $this->setRedirect('index.php?option=com_jinbound&view=statuses');
    }

    public function unsetActive()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();

        $pks = $app->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new Exception(JText::_('COM_JINBOUND_NO_STATUS_SELECTED'));
            }

            ArrayHelper::toInteger($pks);

            // Pop off the first element.
            $id    = array_shift($pks);
            $model = $this->getModel();
            $model->unsetActive($id);
            $this->setMessage(JText::_('COM_JINBOUND_SUCCESS_ACTIVE_STATUS_SET'));

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        $this->setRedirect('index.php?option=com_jinbound&view=statuses');
    }
}
