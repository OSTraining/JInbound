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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class plgUserJInbound extends JPlugin
{
    /**
     * @var bool
     */
    protected static $enabled = false;

    /**
     * @var string[]
     */
    protected $contexts = array(
        'com_users.profile',
        'com_users.user',
        'com_users.registration',
        'com_admin.profile'
    );

    /**
     * Constructor
     *
     * @param JEventDispatcher $subject
     * @param array            $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $jinbound = JPATH_ADMINISTRATOR . '/components/com_jinbound';

        static::$enabled = is_dir($jinbound);

        $this->loadLanguage('plg_user_jinbound.sys');

        if (!static::$enabled) {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_USER_JINBOUND_COMPONENT_NOT_INSTALLED'));
        }
    }

    /**
     * @param string $context The context for the data
     * @param object $data    user data
     *
     * @return    bool
     */
    public function onContentPrepareData($context, $data)
    {
        if (!in_array($context, $this->contexts)) {
            return true;
        }

        if (is_object($data)) {
            $userId = isset($data->id) ? $data->id : 0;

            if (!isset($data->jinbound) and $userId > 0) {
                // Load the profile data from the database.
                $db = JFactory::getDbo();
                $db->setQuery(
                    $db->getQuery(true)
                        ->select('profile_key, profile_value')
                        ->from('#__user_profiles')
                        ->where(
                            array(
                                'user_id = ' . (int)$userId,
                                'profile_key LIKE ' . $db->quote('jinbound.%')
                            )
                        )
                        ->order('ordering ASC')
                );
                $profiles = $db->loadObjectList();

                // Check for a database error.
                if ($db->getErrorNum()) {
                    $this->_subject->setError($db->getErrorMsg());
                    return false;
                }

                $data->jinbound = array();
                foreach ($profiles as $profile) {
                    $key = str_replace('jinbound.', '', $profile->user_id);

                    $data->jinbound[$key] = $profile->profile_key;
                }
            }
        }

        return true;
    }

    /**
     * Saves the user data to the profiles table
     *
     * @param array  $data
     * @param bool   $isNew
     * @param bool   $result
     * @param string $error
     *
     * @return void
     * @throws Exception
     */
    public function onUserAfterSave($data, $isNew, $result, $error)
    {
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        if ($userId && $result && !empty($data['jinbound'])) {
            try {
                $db = JFactory::getDbo();
                $db->setQuery(
                    $db->getQuery(true)
                        ->delete('#__user_profiles')
                        ->where(
                            array(
                                'user_id = ' . (int)$userId,
                                'profile_key LIKE ' . $db->quote('jinbound.%')
                            )
                        )
                );

                if (!$db->execute()) {
                    throw new Exception($db->getErrorMsg());
                }

                $tuples = array();
                $order  = 1;
                foreach ($data['jinbound'] as $k => $v) {
                    $tuples[] = sprintf(
                        '(%s, %s, %s, %s)',
                        $userId,
                        $db->quote('jinbound.' . $k),
                        $db->quote($v),
                        $order++
                    );
                }

                $db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));

                if (!$db->execute()) {
                    throw new Exception($db->getErrorMsg());
                }

            } catch (Exception $e) {
                $this->_subject->setError($e->getMessage());
            }
        }
    }

    /**
     * Remove all user profile information for the given user ID
     *
     * Method is called after user data is deleted from the database
     *
     * @param array  $user    Holds the user data
     * @param bool   $success True if user was succesfully stored in the database
     * @param string $msg     Message
     *
     * @return void
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if ($success) {
            $userId = ArrayHelper::getValue($user, 'id', 0, 'int');
            if ($userId) {
                try {
                    $db = JFactory::getDbo();
                    $db->setQuery(
                        $db->getQuery(true)
                            ->delete('#__user_profiles')
                            ->where(
                                array(
                                    'user_id = ' . $userId,
                                    'profile_key LIKE ' . $db->quote('jinbound.%')
                                )
                            )
                    );

                    if (!$db->execute()) {
                        throw new Exception($db->getErrorMsg());
                    }
                } catch (Exception $e) {
                    $this->_subject->setError($e->getMessage());
                }
            }
        }
    }
}
