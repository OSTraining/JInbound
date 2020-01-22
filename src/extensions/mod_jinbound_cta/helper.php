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

defined('_JEXEC') or die;

if (!defined('JINB_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

// load required classes
JLoader::register('JInbound', "$jinbound_base/libraries/jinbound.php");

abstract class ModJInboundCTAHelper
{
    const CONDITION_ANY  = -1;
    const CONDITION_ALL  = 1;
    const CONDITION_NONE = 0;

    const USER_ANY      = -1;
    const USER_NEW      = 1;
    const USER_EXISTING = 0;

    /**
     * Flag so module cannot be placed into an infinite loop
     *
     * @var bool
     */
    public static $running = false;

    /**
     * Array of adapter instances
     *
     * @var array
     */
    static private $adapters = array();

    /**
     * Generates the "default" urls used by the module config with data
     * so the field doesn't HAVE to be loaded via ajax, because com_ajax
     * refuses to load the helper if the module isn't published at least once.
     *
     * Lame.
     *
     * TODO possibly remove ajax loading all together. Might want to add plugin support
     * later, so it can stay for now...
     *
     * And in case someone else stumbles into this code and wonders what the $#@!
     * happened, it all started innocently enough and worked, but apparently needed
     * to be overly complicated for some reason unbeknownst to me.
     *
     */
    public static function getDefaultEmptyFields($input_value = null)
    {
        $buttons = static::getButtonData();
        if (is_object($input_value)) {
            foreach (array(
                         'isnew',
                         'campaign',
                         'priority',
                         'status',
                         'priority_campaign',
                         'priority_status'
                     ) as $what) {
                if (!is_array($input_value->$what)) {
                    continue;
                }
                foreach ($input_value->$what as $i) {
                    $buttons["default_{$what}_{$i}"] = array_merge($buttons[$what], array('default' => $i));
                }
            }
        }

        $url = JURI::root(false) . 'index.php?option=com_ajax&module=jinbound_cta&method=getField&format=json';

        $data = array();
        // TODO read from jform or something, this is gross
        foreach (array('c1_', 'c2_', 'c3_') as $i) {
            $group = $i . 'conditions';
            foreach ($buttons as $button) {
                if ('isnew' === $button['name']) {
                    $args    = '&type=' . $button['field'] . '&group=' . $group . '&label='
                        . $button['label'] . '&desc=' . $button['desc'] . '&name=' . $button['name']
                        . (array_key_exists('default', $button) ? '&default=' . $button['default'] : '')
                        . '&options[0][text]=JYES&options[0][value]=1'
                        . '&options[1][text]=JNO&options[1][value]=0';
                    $options = array(
                        0 => array('text' => JText::_('JYES'), 'value' => '1')
                    ,
                        1 => array('text' => JText::_('JNO'), 'value' => '0')
                    );
                } else {
                    $args    = '&type=' . $button['field'] . '&group=' . $group . '&label='
                        . $button['label'] . '&desc=' . $button['desc'] . '&name=' . $button['name']
                        . (array_key_exists('default', $button) ? '&default=' . $button['default'] : '')
                        . '&options[0][text]=' . $button['empty'] . '&options[0][value]=';
                    $options = array(
                        0 => array('text' => $button['empty'], 'value' => '')
                    );
                }
                $request = array_merge($button, array(
                    'group'   => $group,
                    'format'  => 'json',
                    'type'    => $button['field'],
                    'options' => $options
                ));
                $data[]  = array(
                    'url'  => $url . $args
                ,
                    'data' => array(
                        'data'    => static::getFieldAjax($request)
                    ,
                        'success' => true
                    )
                );
            }
            // add in yes/no for campaigns
            // http://local.jeff/j34/index.php?option=com_ajax&module=jinbound_cta&method=getField&format=json&type=radio&label=x&desc=x&name=campaign_yesno&options[0][text]=JYES&options[0][value]=1&options[1][text]=JNO&options[1][value]=0&class=radio%20btn-group%20btn-group-yesno%20mod_jinbound_cta_campaign_toggle&default=1&group=c1_conditions
            $data[] = array(
                'url'  => $url . '&type=radio&label=x&desc=x&name=campaign_yesno&options[0][text]=JYES&options[0][value]=1&options[1][text]=JNO&options[1][value]=0&class=radio%20btn-group%20btn-group-yesno%20mod_jinbound_cta_campaign_toggle&default=1&group=' . $group
            ,
                'data' => array(
                    'data'    => static::getFieldAjax(array(
                        'type'    => 'radio',
                        'label'   => 'x',
                        'desc'    => 'x',
                        'name'    => 'campaign_yesno',
                        'class'   => 'radio btn-group btn-group-yesno mod_jinbound_cta_campaign_toggle',
                        'default' => '1',
                        'group'   => $group,
                        'options' => array(
                            0 => array('text' => 'JYES', 'value' => '1')
                        ,
                            1 => array('text' => 'JNO', 'value' => '0')
                        )
                    ))
                ,
                    'success' => true
                )
            );
            // add in campaign select for priority/status
            foreach (array('priority', 'status') as $what) {
                $data[] = array(
                    'url'  => $url . '&type=jinboundcampaignlist&label=x&desc=x&name=' . $what . '_campaign&options[0][text]=MOD_JINBOUND_CTA_ANY_CAMPAIGN&options[0][value]=&group=' . $group
                ,
                    'data' => array(
                        'data'    => static::getFieldAjax(array(
                            'type'    => 'jinboundcampaignlist',
                            'label'   => 'x',
                            'desc'    => 'x',
                            'name'    => $what . '_campaign',
                            'default' => '',
                            'group'   => $group,
                            'options' => array(
                                0 => array('text' => 'MOD_JINBOUND_CTA_ANY_CAMPAIGN', 'value' => '')
                            )
                        ))
                    ,
                        'success' => true
                    )
                );
            }
        }
        return $data;
    }

    public static function getButtonData()
    {
        $data = array(
            'isnew'    => array(
                'name'  => 'isnew'
            ,
                'field' => 'radio'
            ,
                'label' => 'MOD_JINBOUND_CTA_ISNEW_LABEL'
            ,
                'desc'  => 'MOD_JINBOUND_CTA_ISNEW_DESC'
            ,
                'class' => 'btn-group btn-group-yesno'
            )
        ,
            'status'   => array(
                'name'  => 'status'
            ,
                'field' => 'jinboundstatuses'
            ,
                'label' => 'MOD_JINBOUND_CTA_STATUS_LABEL'
            ,
                'desc'  => 'MOD_JINBOUND_CTA_STATUS_DESC'
            ,
                'empty' => 'MOD_JINBOUND_CTA_STATUS_SELECT'
            )
        ,
            'priority' => array(
                'name'  => 'priority'
            ,
                'field' => 'jinboundpriorities'
            ,
                'label' => 'MOD_JINBOUND_CTA_PRIORITY_LABEL'
            ,
                'desc'  => 'MOD_JINBOUND_CTA_PRIORITY_DESC'
            ,
                'empty' => 'MOD_JINBOUND_CTA_PRIORITY_SELECT'
            )
        ,
            'campaign' => array(
                'name'  => 'campaign'
            ,
                'field' => 'jinboundcampaignlist'
            ,
                'label' => 'MOD_JINBOUND_CTA_CAMPAIGN_LABEL'
            ,
                'desc'  => 'MOD_JINBOUND_CTA_CAMPAIGN_DESC'
            ,
                'empty' => 'MOD_JINBOUND_CTA_CAMPAIGN_SELECT'
            )
        );
        // TODO plugin
        return $data;
    }

    /**
     * option=com_ajax&module=jinbound_cta&method=getField&type={field type}&format={format}
     *
     * @param string[] $variables
     *
     * @return object
     * @throws Exception
     */
    public static function getFieldAjax($variables = array())
    {
        JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_jinbound/models/fields');
        JFactory::getLanguage()->load('mod_jinbound_cta.sys', JPATH_SITE . '/modules/mod_jinbound_cta');

        $app = JFactory::getApplication();

        $variables = array_merge(
            array(
                'format'  => $app->input->getWord('format'),
                'type'    => $app->input->getWord('type'),
                'group'   => $app->input->getCmd('group'),
                'control' => $app->input->getWord('control', 'jform'),
                'name'    => $app->input->getCmd('name'),
                'default' => $app->input->getString('default'),
                'label'   => $app->input->getString('label'),
                'desc'    => $app->input->getString('desc'),
                'class'   => $app->input->getString('class'),
                'options' => $app->input->get('options', array(), 'array')
            ),
            $variables
        );

        // validate
        if (!$variables['type']) {
            throw new LogicException('Field type required', 500);
        }
        if (!$variables['name']) {
            throw new LogicException('Field name required', 500);
        }

        $form = JForm::getInstance(
            'jinbound_form_module',
            '<form><!-- --></form>',
            array('control' => $variables['control'])
        );
        $xml  = new SimpleXMLElement('<form/>');

        $params = $xml->addChild('fields');
        $params->addAttribute('name', 'params');
        if (empty($variables['group'])) {
            $fieldset = $params->addChild('fieldset');

        } else {
            $fields = $params->addChild('fields');
            $fields->addAttribute('name', $variables['group']);
            $fieldset = $fields->addChild('fieldset');
        }

        $field = $fieldset->addChild('field');
        // configure field
        $field->addAttribute('name', $variables['name']);
        $field->addAttribute('type', $variables['type']);
        $field->addAttribute('default', $variables['default']);
        $field->addAttribute('class', $variables['class']);
        $field->addAttribute('label', JText::_($variables['label']));
        $field->addAttribute('description', JText::_($variables['desc']));

        foreach ($variables['options'] as $option) {
            if (is_array($option)
                && !empty($option['value'])
                && !empty($option['text'])
            ) {
                $opt = $field->addChild('option', JText::_($option['text']));
                $opt->addAttribute('value', $option['value']);
            }
        }

        $form->load($xml, false);
        $obj    = $form->getField($variables['name'], "params.{$variables['group']}");
        $result = (object)array(
            'field' => (object)array(
                'label' => $obj->label,
                'field' => $obj->input
            )
        );
        if (in_array($variables['format'], array('json', 'debug'))) {
            return $result;
        }

        return $result->field;
    }

    /**
     * Gets the adapter instance
     *
     * @param Registry $params
     * @param bool      $cached
     *
     * @return ModJInboundCTAAdapter
     * @throws Exception
     */
    public static function getAdapter(Registry $params, $cached = true)
    {
        $app    = JFactory::getApplication();
        $filter = JFilterInput::getInstance();

        /*
         * in order to load the correct adapter, conditions need to be checked
         * get the correct adapter parameter name
         * should return one of: '', 'c1_', 'c2_', 'c3_'
         */
        $pfx  = $filter->clean(static::findAdapterType($params), 'cmd');
        $name = $filter->clean($params->get($pfx . 'mode', 'module'), 'cmd');

        if (!empty(static::$adapters[$name]) && $cached) {
            if (JDEBUG) {
                $app->enqueueMessage("Found pre-existing adapter '$name' ...");
            }

        } else {
            if (!class_exists($class = 'ModJInboundCTA' . ucfirst($name) . 'Adapter')) {
                if (!file_exists($require = dirname(__FILE__) . '/adapters/' . $name . '.php')) {
                    throw new RuntimeException('Adapter not found', 404);
                }

                require_once $require;
                if (!class_exists($class)) {
                    throw new RuntimeException('Adapter file not found', 404);
                }
            }

            if (JDEBUG) {
                $app->enqueueMessage("Creating new adapter instance '{$class}' with pfx '{$pfx}' ...");
            }

            $adapter = new $class($params);

            $adapter->pfx = $pfx;

            static::$adapters[$name] = $adapter;
        }

        return static::$adapters[$name];
    }

    /**
     * @param Registry $params
     *
     * @return string
     * @throws Exception
     */
    protected static function findAdapterType(Registry $params)
    {
        $app  = JFactory::getApplication();
        $pfxs = array('c1_', 'c2_', 'c3_');
        $data = static::loadContactData();
        $type = '';
        if (JDEBUG) {
            $app->enqueueMessage(
                sprintf('Checking against this user data:<pre>%s</pre>', htmlspecialchars(print_r($data, 1)))
            );
        }

        foreach ($pfxs as $pfx) {
            if (static::checkData($data, $params, $pfx)) {
                $type = $pfx;
                break;
            }
        }

        if (JDEBUG) {
            $app->enqueueMessage("Found adapter type '{$type}' ...");
        }

        return $type;
    }

    protected static function loadContactData()
    {
        $cookie            = filter_input(INPUT_COOKIE, '__jib__');
        $contact_id        = static::getContactId();
        $contact           = new stdClass();
        $contact->id       = $contact_id;
        $contact->isnew    = empty($cookie);
        $contact->campaign = array();
        $contact->priority = array();
        $contact->status   = array();

        if (!empty($contact_id)) {
            $contact->campaign = JInboundHelperContact::getContactCampaigns($contact_id);
            $contact->priority = JInboundHelperContact::getContactPriorities($contact_id);
            $contact->status   = JInboundHelperContact::getContactStatuses($contact_id);
        }

        return $contact;
    }

    protected static function getContactId()
    {
        $cookie = plgSystemJInbound::getCookieValue();
        $db     = JFactory::getDbo();
        return (int)$db->setQuery($db->getQuery(true)
            ->select($db->quoteName('id'))->from('#__jinbound_contacts')
            ->where($db->quoteName('cookie') . ' = ' . $db->quote($cookie))
        )->loadResult();
    }

    protected static function checkData($data, Registry $params, $pfx)
    {
        // init
        $app     = JFactory::getApplication();
        $enabled = (int)$params->get($pfx . 'enabled', ModJInboundCTAHelper::CONDITION_ANY);
        if (!$enabled) {
            if (JDEBUG) {
                $app->enqueueMessage("Conditions for prefix '$pfx' not enabled...");
            }
            return false;
        } else {
            if (JDEBUG) {
                $app->enqueueMessage("Conditions for prefix '$pfx' enabled...");
            }
        }
        $match      = (int)$params->get($pfx . 'match', ModJInboundCTAHelper::CONDITION_ANY);
        $conditions = $params->get($pfx . 'conditions');
        $matches    = array();
        // conditions MUST be an object to continue
        if (is_object($conditions)) {
            // fix conditions
            foreach (array(
                         'isnew',
                         'campaign',
                         'campaign_yesno',
                         'priority',
                         'priority_campaign',
                         'status',
                         'status_campaign'
                     ) as $property) {
                if (!property_exists($conditions, $property)) {
                    continue;
                }
                if (!is_array($conditions->$property)) {
                    $conditions->$property = array_values((array)$conditions->$property);
                }
            }
            if (JDEBUG) {
                $app->enqueueMessage(serialize($conditions));
            }
            // isnew
            if (property_exists($conditions, 'isnew')) {
                $isnew = (array)$conditions->isnew;
                foreach ($isnew as $key => $value) {
                    foreach ((array)$value as $v) {
                        if (JDEBUG) {
                            $app->enqueueMessage("User should " . ((int)$v ? '' : 'NOT ') . "be new ...");
                        }
                        $matches[] = ((int)$v) && $data->isnew;
                    }
                }
            }
            // there should be 3*2 variables:
            // campaign && campaign_yesno
            if (property_exists($conditions, 'campaign') && property_exists($conditions, 'campaign_yesno')) {
                // check if the user is in one of the campaigns
                foreach ($conditions->campaign as $key => $value) {
                    // check if the user is in this campaign
                    $in = false;
                    foreach ($data->campaign as $c) {
                        if ($c->id == $value) {
                            $in = true;
                            break;
                        }
                    }
                    if (JDEBUG) {
                        $app->enqueueMessage("User is " . ($in ? '' : 'NOT ') . "in campaign '$value' ($key) ...");
                    }
                    // check yes/no
                    $yn = $conditions->campaign_yesno[$key];
                    if (JDEBUG) {
                        $app->enqueueMessage("User should " . ($yn ? '' : 'NOT ') . "be in campaign '$value' ($key, $yn) ...");
                    }
                    $matches[] = (bool)($in ? $yn : !$yn);
                }
            }
            // status && status_campaign
            if (property_exists($conditions, 'status') && property_exists($conditions, 'status_campaign')) {
                // check each status
                foreach ($conditions->status as $key => $value) {
                    // get the campaign to be checked
                    $status_campaign = $conditions->status_campaign[$key];
                    // if there is a specific campaign, check only that
                    if (!empty($status_campaign)) {
                        // if the key does not exist at all, then the user has no status for this campaign
                        if (!array_key_exists($status_campaign, $data->status)) {
                            // cannot have this status in this campaign
                            $matches[] = false;
                            if (JDEBUG) {
                                $app->enqueueMessage("User has no status for campaign '$status_campaign' ($key) ...");
                            }
                        } // key must exist, so check the first entry (shouldn't be empty?)
                        else {
                            $in        = ($value == $data->status[$status_campaign][0]->status_id);
                            $matches[] = (bool)$in;
                            if (JDEBUG) {
                                $app->enqueueMessage("User has " . ($in ? '' : 'in') . "correct status for campaign '$status_campaign' ($key) ...");
                            }
                        }
                    } // otherwise loop through all campaigns
                    else {
                        $set = false;
                        foreach ($data->status as $statuses) {
                            // if one matches, we're satisfied
                            if ($statuses[0]->status_id == $value) {
                                $set = true;
                                break;
                            }
                        }
                        if (JDEBUG) {
                            $app->enqueueMessage("User " . ($set ? 'has' : 'does not have') . " status '$value' ($key) ...");
                        }
                        $matches[] = (bool)$set;
                    }
                }
            }
            // priority && priority_campaign
            if (property_exists($conditions, 'priority') && property_exists($conditions, 'priority_campaign')) {
                // check each priority
                foreach ($conditions->priority as $key => $value) {
                    // get the campaign to be checked
                    $priority_campaign = $conditions->priority_campaign[$key];
                    // if there is a specific campaign, check only that
                    if (!empty($priority_campaign)) {
                        // if the key does not exist at all, then the user has no priority for this campaign
                        if (!array_key_exists($priority_campaign, $data->priority)) {
                            // cannot have this priority in this campaign
                            $matches[] = false;
                            if (JDEBUG) {
                                $app->enqueueMessage("User has no priority for campaign '$priority_campaign' ($key) ...");
                            }
                        } // key must exist, so check the first entry (shouldn't be empty?)
                        else {
                            $in        = ($value == $data->priority[$priority_campaign][0]->priority_id);
                            $matches[] = (bool)$in;
                            if (JDEBUG) {
                                $app->enqueueMessage("User has " . ($in ? '' : 'in') . "correct priority for campaign '$priority_campaign' ($key) ...");
                            }
                        }
                    } // otherwise loop through all campaigns
                    else {
                        $set = false;
                        foreach ($data->priority as $priorities) {
                            // if one matches, we're satisfied
                            if ($priorities[0]->priority_id == $value) {
                                $set = true;
                                break;
                            }
                        }
                        if (JDEBUG) {
                            $app->enqueueMessage("User " . ($set ? 'has' : 'does not have') . " priority '$value' ($key) ...");
                        }
                        $matches[] = (bool)$set;
                    }
                }
            }
        }
        // no matches at all? bail
        if (empty($matches)) {
            if (JDEBUG) {
                $app->enqueueMessage("Found no matches for this user...");
            }
            return false;
        }

        $result = false;
        switch ($match) {
            case ModJInboundCTAHelper::CONDITION_ANY:
                if (JDEBUG) {
                    $app->enqueueMessage('User must match ANY ...');
                }
                $place  = array_search(true, $matches, true);
                $result = (false !== $place);
                break;
            case ModJInboundCTAHelper::CONDITION_NONE:
                if (JDEBUG) {
                    $app->enqueueMessage('User must match NONE ...');
                }
                $place  = array_search(true, $matches, true);
                $result = (false === $place);
                break;
            case ModJInboundCTAHelper::CONDITION_ALL:
                if (JDEBUG) {
                    $app->enqueueMessage('User must match ALL ...');
                }
                $place  = array_search(false, $matches, true);
                $result = (false === $place);
                break;
        }
        if (JDEBUG) {
            $app->enqueueMessage("User is " . ($result ? '' : 'NOT ') . "a match!");
        }
        return $result;
    }
}
