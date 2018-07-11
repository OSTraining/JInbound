<?php
/**
 * @package             jInbound
 * @subpackage          plg_system_jinbound
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

class plgSystemJInbound extends JPlugin
{
    /**
     * @var JApplicationCms
     */
    private static $app = null;

    /**
     * @var bool
     */
    protected static $enabled = null;

    /**
     * @var bool
     */
    protected static $setCookie = null;

    /**
     * @var string
     */
    protected static $cookie = null;

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
        $this->loadLanguage();

        if (static::$app === null) {
            static::$app = JFactory::getApplication();
        }
        if ($this->isEnabled()) {
            JFactory::getLanguage()->load('com_jinbound', JPATH_ADMINISTRATOR . '/components/com_jinbound');
        }
    }

    /**
     * @param bool $warn
     *
     * @return bool
     * @throws Exception
     */
    protected function isEnabled($warn = false)
    {
        if (static::$app === null) {
            static::$app = JFactory::getApplication();
        }

        if (!defined('JINB_LOADED') && static::$app->input->getCmd('option') != 'com_install') {
            $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
            if (is_file($path)) {
                require_once $path;
            }
        }

        static::$enabled = defined('JINB_LOADED');

        if ($warn && !static::$enabled) {
            static::$app->enqueueMessage(JText::_('PLG_SYSTEM_JINBOUND_COMPONENT_NOT_INSTALLED'));
        }

        return static::$enabled;
    }

    /**
     * @param string $extension
     * @param string $basePath
     *
     * @return bool
     */
    public function loadLanguage($extension = 'plg_system_jinbound.sys', $basePath = JPATH_ADMINISTRATOR)
    {
        return parent::loadLanguage($extension, $basePath);
    }

    public function onAfterInitialise()
    {
        $this->profile('BeforeInitialise');
        if (!static::$app->isClient('administrator')) {
            $this->setUserCookie();
        }

        $this->profile('AfterInitialise');
    }

    /**
     * @param string $action
     *
     * @return void
     */
    protected function profile($action)
    {
        $profiler = JProfiler::getInstance('Application');
        $profiler->mark("onPlgSysJinbound{$action}");
    }

    /**
     * Sets the jInbound user cookie
     *
     * @TODO stupid EU cookie law crap
     *
     * @return void
     */
    public static function setUserCookie()
    {
        if (headers_sent()) {
            static::$setCookie = true;

        } else {
            static::$setCookie = !setcookie('__jib__', static::getCookieValue());
        }
    }

    /**
     * Derives a unique cookie name for this user
     *
     * @return string
     */
    public static function getCookieValue()
    {
        $c = filter_input(INPUT_COOKIE, '__jib__');
        if (!empty($c)) {
            return $c;
        }

        if (static::$cookie === null) {
            $ua             = static::getUserAgent();
            $ip             = static::getIp();
            $salt           = strrev(md5(static::$app->get('secret')));
            static::$cookie = sha1("$ua.$salt.$ip", false);
        }

        return static::$cookie;
    }

    public static function getUserAgent()
    {
        return static::getServerVar('HTTP_USER_AGENT');
    }

    public static function getServerVar($variable, $default = null, $type = 'string')
    {
        return static::$app->input->server->get($variable, $default, $type);
    }

    public static function getIp()
    {
        return static::getServerVar('REMOTE_ADDR');
    }

    /**
     * onAfterDispatch
     *
     * handles flair after dispatch
     *
     * @return void
     * @throws Exception
     */
    public function onAfterDispatch()
    {
        static::profile('BeforeDispatch');
        if (!$this->isEnabled()) {
            return;
        }

        $opt = static::$app->input->get('option', '', 'cmd');
        if (static::$app->isClient('administrator')) {
            $this->onAfterDispatchAdmin($opt);
        }

        static::profile('AfterDispatch');
    }

    /**
     * @param string $option
     *
     * @return void
     * @throws Exception
     */
    public function onAfterDispatchAdmin($option)
    {
        switch ($option) {
            case 'com_categories':
                $this->onAfterDispatchAdminCategories();
                break;

            case 'com_menus':
                if (static::$app->input->get('layout') == 'edit' && 'item' == static::$app->input->get('view')) {
                    JText::script('COM_JINBOUND_MENU_NOT_SET_TO_USE_JINBOUND_TEMPLATE');
                    JFactory::getDocument()->addScript(JInboundHelperUrl::media() . '/js/admin.menu.js');
                }
                break;
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function onAfterDispatchAdminCategories()
    {
        // Extras for com_categories
        if ($this->isEnabled(true) && static::$app->input->getCmd('extension') == 'com_jinbound') {
            JHtml::_('stylesheet', 'com_jinbound/admin.categories.css', array('relative' => true));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function onAfterRoute()
    {
        if (!$this->isEnabled() || static::$app->isClient('administrator')) {
            return;
        }
        static::profile('BeforeRoute');

        $db     = JFactory::getDbo();
        $cookie = static::getCookieValue();

        try {
            $campaign_data = $db->setQuery(
                $db->getQuery(true)
                    ->select(
                        array(
                            'ContactCampaign.contact_id AS contact_id',
                            'ContactCampaign.campaign_id AS campaign_id',
                            'Campaign.conversion_url'
                        )
                    )
                    ->from('#__jinbound_contacts_campaigns AS ContactCampaign')
                    ->leftJoin('#__jinbound_contacts AS Contact ON Contact.id = ContactCampaign.contact_id')
                    ->leftJoin('#__jinbound_campaigns AS Campaign ON Campaign.id = ContactCampaign.campaign_id')
                    ->where(
                        array(
                            'Contact.cookie = ' . $db->quote($cookie),
                            'Campaign.conversion_url <> ' . $db->quote('')
                        )
                    )
            )
                ->loadObjectList();

        } catch (Exception $e) {
            if (defined('JDEBUG') && JDEBUG) {
                static::$app->enqueueMessage($e->getMessage(), 'error');
            }
            static::profile('AfterRoute');

            return;
        }

        if (empty($campaign_data)) {
            static::profile('AfterRoute');

            return;
        }

        $status_id = JInboundHelperStatus::getFinalStatus();

        $processed = array();
        // this visitor is detected as being in one or more campaigns
        // check if this request is for one of the conversion urls
        foreach ($campaign_data as $data) {
            // only process once
            // TODO just fix query instead
            $processed_key = $data->contact_id . ':' . $data->campaign_id;
            if (in_array($processed_key, $processed)) {
                continue;
            }
            $processed[] = $processed_key;

            $params       = array();
            $param_string = trim($data->conversion_url);

            if (substr($param_string, 0, 10) === 'index.php?') {
                $param_string = substr($param_string, 10);
            }

            $param_string = ltrim($param_string, '?');

            // this url *might* be in sef :(
            $matches = false;
            // if so, just compare to query string
            $trimmed   = trim($param_string, '/');
            $trim_root = trim(JUri::root(true), '/');
            $uri       = static::getURI(false);
            if (!empty($trimmed)
                && (trim($uri, '/') === $trimmed
                    || trim($uri, '/') === trim($trim_root . '/' . $trimmed, '/')
                )) {
                $matches = true;

            } else {
                if (false !== strpos($param_string, '=')) {
                    // handle non-sef
                    parse_str($param_string, $params);
                    // get just the given params from request and compare the arrays
                    $request = array();
                    foreach (array_keys($params) as $param) {
                        $request[$param] = static::$app->input->get($param);
                    }
                    // fix ids, catids, etc, for slugs
                    foreach (array('id', 'a_id', 'cat', 'catid') as $fix) {
                        if (array_key_exists($fix, $request)) {
                            $request[$fix] = preg_replace('/^([1-9][0-9]*?).*$/', '$1', $request[$fix]);
                        }
                    }

                    if (!empty($request)) {
                        $diff    = array_diff_assoc($params, $request);
                        $matches = empty($diff);
                    }
                }
            }

            // if the arrays are the same, there's a match - assign if there's a final status
            if ($matches && $status_id) {
                JInboundHelperStatus::setContactStatusForCampaign($status_id, $data->contact_id, $data->campaign_id);
                continue;
            }
        }

        static::profile('AfterRoute');
    }

    /**
     * Copied from sh404sef (which copied from Joomla)
     * Modified a wee bit
     *
     * @return string
     */
    public static function getURI($full = true)
    {
        $theURI = JUri::getInstance();

        return $full
            ? $theURI->toString()
            : $theURI->toString(array('path', 'query', 'fragment'));
    }

    /**
     * Alter the response body before sending to the client
     *
     * @return void
     * @throws Exception
     */
    public function onAfterRender()
    {
        if (!$this->isEnabled() || static::$app->isClient('administrator')) {
            return;
        }

        static::profile('BeforeRender');

        $body = static::$app->getBody();

        $add = '';
        if (intval(JInboundHelper::config()->def('cron_type', '')) == 0) {
            $url = JInboundHelperFilter::escape(JInboundHelperUrl::task('cron', false));
            $add .= sprintf(
                '<iframe src="%s" style="width:1px;height:1px;position:absolute;left:-999px;border:0px"></iframe>',
                $url
            );
        }

        // add cookie script
        if (static::$setCookie) {
            $cookie = JInboundHelperFilter::escape(static::getCookieValue());
            $add    .= sprintf(
                '<script data-jib="%s" id="jinbound_tracks" type="text/javascript" src="%s"></script>',
                $cookie,
                JInboundHelperUrl::media() . '/js/track.js'
            );
        }

        if (!empty($add)) {
            $body = str_replace('</body>', $add . '</body>', $body);
            static::$app->setBody($body);
        }

        $this->recordUserTrack();

        static::profile('AfterRender');
    }

    /**
     * records the user's request
     */
    protected function recordUserTrack()
    {
        static::profile('BeforeTrack');

        $db           = JFactory::getDbo();
        $ip           = static::getIp();
        $session      = session_id();
        $id           = microtime() . $ip . md5($session);
        $detecteduser = static::getCookieUser();

        if (is_array($detecteduser)) {
            $detecteduser = $detecteduser[0];
        }

        $track = (object)array(
            'id'               => $id,
            'cookie'           => static::getCookieValue(),
            'detected_user_id' => $detecteduser,
            'current_user_id'  => JFactory::getUser()->id,
            'user_agent'       => static::getUserAgent(),
            'created'          => JFactory::getDate()->toSql(),
            'ip'               => $ip,
            'session_id'       => $session,
            'type'             => static::getServerVar('REQUEST_METHOD'),
            'url'              => static::getURI()
        );

        static::profile('BeforeInsertTrack');
        try {
            $db->insertObject('#__jinbound_tracks', $track);

        } catch (Exception $e) {
        }

        static::profile('BeforeInsertUserTrack');
        if ($detecteduser === 0 && (int)$track->current_user_id) {
            $userTrack = (object)array(
                'user_id' => $track->current_user_id,
                'cookie'  => $track->cookie
            );
            try {
                $db->insertObject('#__jinbound_users_tracks', $userTrack);

            } catch (Exception $e) {
            }
        }
    }

    /**
     * Checks the database for a user previously associated with this cookie
     *
     * @return int|int[]
     */
    public static function getCookieUser()
    {
        $db = JFactory::getDbo();
        $db->setQuery(
            $db->getQuery(true)
                ->select('user_id')
                ->from('#__jinbound_users_tracks')
                ->where('cookie = ' . $db->quote(static::getCookieValue()))
        );

        try {
            $ids = $db->loadColumn();

        } catch (Exception $e) {
            return 0;
        }

        if (empty($ids)) {
            return 0;
        }

        if (count($ids) < 1) {
            ArrayHelper::toInteger($ids);
            return $ids;
        }

        return (int)$ids[0];
    }
}
