<?php
/**
 * @package             jInbound
 * @subpackage          plg_content_jinbound
 **********************************************
 * jInbound
 * Copyright (c) 2013 Anything-Digital.com
 * Copyright (c) 2018 Open Source Training, LLC
 **********************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.n *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 */

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

class plgContentJInbound extends JPlugin
{
    /**
     * @var bool
     */
    protected static $enabled = false;

    /**
     * Constructor
     *
     * @param JEventDispatcher $subject
     * @param array            $config
     *
     * @retrn void
     * @throws Exception
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        static::$enabled = is_dir(JPATH_ADMINISTRATOR . '/components/com_jinbound');

        if (static::$enabled) {
            JLoader::register('JInbound', JPATH_ADMINISTRATOR . '/components/com_jinbound/helpers/jinbound.php');

        } else {
            $this->loadLanguage('plg_content_jinbound.sys');
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_CONTENT_JINBOUND_COMPONENT_NOT_INSTALLED'));
        }
    }

    /**
     * onContentBeforeSave event - dummy for now
     *
     * @param string $context
     * @param JTable $table
     * @param bool   $isNew
     *
     * @return bool
     * @throws Exception
     */
    public function onContentBeforeSave($context, $table, $isNew)
    {
        if (static::$enabled
            && strpos($context, 'com_jinbound') === 0
            && JInbound::config("debug", 0)
        ) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }

        return true;
    }

    /**
     * onContentAfterSave event - dummy for now
     *
     * @param string $context
     * @param JTable $table
     * @param bool   $isNew
     *
     * @return void
     * @throws Exception
     */
    public function onContentAfterSave($context, $table, $isNew)
    {
        if (static::$enabled
            && strpos($context, 'com_jinbound') === 0
            && JInbound::config("debug", 0)
        ) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }
    }

    /**
     * onContentBeforeDelete event - dummy for now
     *
     * @param string         $context
     * @param JObject|JTable $item
     *
     * @return bool
     * @throws Exception
     */
    public function onContentBeforeDelete($context, $item)
    {
        if (static::$enabled
            && strpos($context, 'com_jinbound') === 0
            && JInbound::config("debug", 0)
        ) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }

        return true;
    }

    /**
     * onContentAfterDelete event - dummy for now
     *
     * @param string         $context
     * @param JObject|JTable $item
     *
     * @return void
     * @throws Exception
     */
    public function onContentAfterDelete($context, $item)
    {
        if (static::$enabled
            && strpos($context, 'com_jinbound') === 0
            && JInbound::config("debug", 0)
        ) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }
    }

    /**
     * onContentChangeState event - dummy for now
     *
     * @param string $context
     * @param int[]  $pks
     * @param int    $value
     *
     * @return bool
     * @throws Exception
     */
    public function onContentChangeState($context, $pks, $value)
    {
        if (static::$enabled
            && strpos($context, 'com_jinbound') === 0
            && JInbound::config("debug", 0)
        ) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }

        return true;
    }

    /**
     * onContentBeforeDisplay event
     *
     * forces URLs in emails to be absolute no matter what
     *
     * @param string $context
     * @param object $table
     * @param object $params
     * @param int    $offset
     *
     * @return string
     * @throws Exception
     */
    public function onContentBeforeDisplay($context, $table, $params, $offset = 0)
    {
        if (!static::$enabled || strpos($context, 'com_jinbound') !== 0) {
            return '';
        }

        if (JInbound::config("debug", 0)) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }

        // handle known contexts
        if ($context == 'com_jinbound.email') {
            $regex = '#(?P<attr>src|href)\=(?P<qte>\"|\\\')(?P<url>.*?)(?P=qte)#Di';
            if (preg_match_all($regex, $table->htmlbody, $matches, PREG_SET_ORDER)) {
                JInbound::registerHelper('url');
                foreach ($matches as $match) {
                    $table->htmlbody = str_replace(
                        $match[0],
                        $match['attr'] . '=' . $match['qte'] . JInboundHelperUrl::toFull($match['url']) . $match['qte'],
                        $table->htmlbody
                    );
                }
            }
        }

        return '';
    }

    /**
     * onContentAfterDisplay event - dummy for now
     *
     * @param string $context
     * @param object $table
     * @param object $params
     * @param int    $offset
     *
     * @return string
     * @throws Exception
     */
    public function onContentAfterDisplay($context, $table, $params, $offset = 0)
    {
        if (static::$enabled
            && strpos($context, 'com_jinbound') === 0
            && JInbound::config("debug", 0)
        ) {
            JFactory::getApplication()->enqueueMessage(__METHOD__ . ' ' . $context);
        }

        return '';
    }

    /**
     * onJInboundChangeState event
     *
     * @param string $context
     * @param int    $campaign
     * @param int[]  $pks
     * @param int    $value
     *
     * @return bool
     * @throws Exception
     */
    public function onJInboundChangeState($context, $campaign, $pks, $value)
    {
        if (!static::$enabled || strpos($context, 'com_jinbound') !== 0) {
            return true;
        }

        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();

        if (JInbound::config("debug", 0)) {
            $app->enqueueMessage(__METHOD__ . ' ' . $context);
        }

        if ($context == 'com_jinbound.contact.status') {
            if (!empty($pks)) {
                ArrayHelper::toInteger($pks);

                try {
                    $final = (int)$db->setQuery(
                        $db->getQuery(true)
                            ->select('final')
                            ->from('#__jinbound_lead_statuses')
                            ->where('id = ' . $db->quote($value))
                    )
                        ->loadResult();

                    $greedy = (int)$db->setQuery(
                        $db->getQuery(true)
                            ->select('greedy')
                            ->from('#__jinbound_campaigns')
                            ->where('id = ' . $db->quote($campaign))
                    )
                        ->loadResult();

                    if ($greedy || $final) {
                        $db->setQuery(
                            $db->getQuery(true)
                                ->delete('#__jinbound_contacts_campaigns')
                                ->where($db->quoteName('contact_id') . ' IN(' . implode('', $pks) . ')')
                                ->where($db->quoteName('campaign_id') . ' <> ' . $db->quote($campaign))
                        )
                            ->execute();
                    }

                } catch (Exception $e) {
                    $app->enqueueMessage($e->getMessage(), 'error');
                }
            }
        }

        return true;
    }
}
