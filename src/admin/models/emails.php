<?php
/**
 * @package             jInbound
 * @subpackage          com_jinbound
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

defined('JPATH_PLATFORM') or die;

/**
 * This models supports retrieving lists of emails.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelEmails extends JInboundListModel
{
    public $context = 'com_jinbound.emails';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Campaign.name',
                'Email.name',
                'Email.type',
                'Email.published',
                'Email.sendafter'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to send all the emails that need to be sent
     *
     * @return void
     * @throws Exception
     */
    public function send()
    {
        $dispatcher = JEventDispatcher::getInstance();
        $this->sendCampaignEmails();
        $dispatcher->trigger('onJInboundSend');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function sendCampaignEmails()
    {
        JPluginHelper::importPlugin('content');

        $db         = $this->getDbo();
        $out        = JInboundHelper::config("debug", 0);
        $interval   = $out ? 'MINUTE' : 'DAY';
        $now        = JFactory::getDate();
        $params     = new JRegistry;
        $dispatcher = JEventDispatcher::getInstance();
        $limit      = (int)JInboundHelper::config("cron_max_campaign_mails", 0);

        // @TODO: add date column to contacts_campaigns to prevent contacts from slipping their email dates
        $query = $db->getQuery(true)
            ->select(
                array(
                    'Contact.first_name AS first_name',
                    'Contact.last_name AS last_name',
                    'Contact.created AS created',
                    'Conversion.formdata AS form',
                    'Contact.email AS email',
                    'Contact.id AS contact_id',
                    'Conversion.id AS conversion_id',
                    'Page.id AS page_id',
                    'Page.formname AS form_name',
                    'Campaign.id AS campaign_id',
                    'Campaign.name AS campaign_name',
                    'Email.id AS email_id',
                    'Email.sendafter AS sendafter',
                    'Email.fromname AS fromname',
                    'Email.fromemail AS fromemail',
                    'Email.subject AS subject',
                    'Email.htmlbody AS htmlbody',
                    'Email.plainbody AS plainbody',
                    'Record.id AS record_id',
                    'MAX(Version.id) AS version_id'
                )
            )
            ->from('#__jinbound_contacts AS Contact')
            ->leftJoin('#__jinbound_conversions AS Conversion ON Conversion.contact_id = Contact.id')
            ->leftJoin('#__jinbound_pages AS Page ON Conversion.page_id = Page.id')
            ->leftJoin('#__jinbound_campaigns AS Campaign ON Page.campaign = Campaign.id')
            ->leftJoin('#__jinbound_emails AS Email ON Email.campaign_id = Campaign.id')
            ->leftJoin('#__jinbound_emails_records AS Record ON Record.lead_id = Contact.id AND Record.email_id = Email.id')
            ->leftJoin('#__jinbound_subscriptions AS Sub ON Contact.id = Sub.contact_id')
            ->leftJoin('#__jinbound_emails_versions AS Version ON Version.email_id = Email.id')
            ->where(
                array(
                    'Record.id IS NULL',
                    'DATE_ADD(Conversion.created, INTERVAL Email.sendafter ' . $interval . ') < UTC_TIMESTAMP()',
                    'Email.type = ' . $db->quote('campaign'),
                    'Email.published = 1',
                    'Page.published = 1',
                    'Campaign.published = 1',
                    'Sub.enabled <> 0'
                )
            )
            ->group(
                array(
                    'Email.id',
                    'Contact.id'
                )
            );

        if ($out) {
            echo '<h3>Query</h3><pre>' . print_r((string)$query, 1) . '</pre>';
        }

        try {
            $results = $db->setQuery($query, 0, $limit)->loadObjectList();
            if (empty($results)) {
                throw new Exception('No records found');
            }

        } catch (Exception $e) {
            if ($out) {
                echo $e->getMessage() . "\n<pre>" . $e->getTraceAsString() . "</pre>";
            }
            return;
        }

        foreach ($results as $result) {
            $reg  = new JRegistry($result->form);
            $arr  = $reg->toArray();
            $tags = array();
            foreach (array_keys($arr['lead']) as $tag) {
                $tags[] = 'email.lead.' . $tag;
            }
            array_unique($tags);
            $reg = $reg->toObject();

            $dispatcher->trigger('onContentBeforeDisplay', array('com_jinbound.email', &$result, &$params, 0));

            $result->htmlbody  = $this->replaceTags($result->htmlbody, $reg, $tags);
            $result->plainbody = $this->replaceTags($result->plainbody, $reg, $tags);

            $tags              = array('email.campaign_name', 'email.form_name');
            $result->htmlbody  = $this->replaceTags($result->htmlbody, $result, $tags);
            $result->plainbody = $this->replaceTags($result->plainbody, $result, $tags);

            if (JInboundHelper::config('unsubscribe', 1)) {
                $unsubscribe       = JInboundHelperUrl::toFull(
                    JInboundHelperUrl::task('unsubscribe', false, array('email' => $result->email))
                );
                $result->htmlbody  = $result->htmlbody . JText::sprintf('COM_JINBOUND_UNSUBSCRIBE_HTML', $unsubscribe);
                $result->plainbody = $result->plainbody
                    . JText::sprintf('COM_JINBOUND_UNSUBSCRIBE_PLAIN', $unsubscribe);
            }

            $dispatcher->trigger('onContentAfterDisplay', array('com_jinbound.email', &$result, &$params, 0));

            if ($out) {
                echo '<h3>Result</h3><pre>' . htmlspecialchars(print_r($result, 1)) . '</pre>';
            }

            $mailer = JFactory::getMailer();
            $mailer->ClearAllRecipients();
            $mailer->SetFrom($result->fromemail, $result->fromname);
            $mailer->addRecipient($result->email, $result->first_name . ' ' . $result->last_name);
            $mailer->setSubject($result->subject);
            $mailer->setBody($result->htmlbody);
            $mailer->IsHTML(true);
            $mailer->AltBody = $result->plainbody;

            if ($out) {
                echo('<h3>Mailer</h3><pre>' . print_r($mailer, 1) . '</pre>');
            }

            $sent = $mailer->Send();

            if (!$sent) {
                if ($out) {
                    echo('<h3>COULD NOT SEND MAIL!!!!</h3>');
                }
                continue;
            }

            $object = (object)array(
                'email_id'   => $result->email_id,
                'lead_id'    => $result->contact_id,
                'sent'       => $now->toSql(),
                'version_id' => $result->version_id
            );

            try {
                $db->insertObject('#__jinbound_emails_records', $object);
            } catch (Exception $e) {
                if ($out) {
                    echo $e->getMessage() . "\n" . $e->getTraceAsString();
                }
                continue;
            }
        }

        echo "\n";
    }

    public function replaceTags($string, $object, $extra = false)
    {
        $out = false;
        if ($out) {
            echo('<h3>Email Tags</h3>');
        }
        $tags = array(
            'email.lead.first_name',
            'email.lead.last_name',
            'email.lead.email'
        );

        if ($extra && is_array($extra)) {
            $tags = array_merge($tags, $extra);
        }
        array_unique($tags);

        if ($out) {
            echo('<h4>Tags</h4><pre>' . print_r($tags, 1) . '</pre>');
            echo('<h4>Object</h4><pre>' . print_r($object, 1) . '</pre>');
        }

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (false === stripos($string, $tag)) {
                    continue;
                }
                $parts   = explode('.', $tag);
                $context = array_shift($parts);
                $params  = false;
                $value   = false;
                if ($out) {
                    echo('<h4>Context</h4><pre>' . print_r($context, 1) . '</pre>');
                    echo('<h4>Parts</h4><pre>' . print_r($parts, 1) . '</pre>');
                }
                while (!empty($parts)) {
                    $part = array_shift($parts);
                    if ($out) {
                        echo('<h4>Part</h4><pre>' . print_r($part, 1) . '</pre>');
                    }
                    // handle the value differently based on it's type
                    if ($value) {
                        // arrays should have the key available
                        if (is_array($value) && array_key_exists($part, $value)) {
                            $value = $value[$part];
                        } // JRegistry uses get() for values
                        else {
                            if (is_object($value) && $value instanceof JRegistry) {
                                $value = $value->get($part);
                            } // normal object
                            else {
                                if (is_object($value) && property_exists($value, $part)) {
                                    $value = $value->{$part};
                                } // object with this method
                                else {
                                    if (is_object($value) && method_exists($value, $part)) {
                                        $value = call_user_func(array($value, $part));
                                    } // don't know what to do here...
                                    else {
                                        $value = '';
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        $value = $object->{$part};
                    }
                    if ($out) {
                        echo('<h4>Value</h4><pre>' . print_r($value, 1) . '</pre>');
                    }
                }
                // last checks on value
                if (is_array($value) || is_object($value)) {
                    $value = print_r($value, 1);
                }
                // replace tag
                $string = str_ireplace("{%$tag%}", $value, $string);
            }
        }
        if ($out) {
            echo('<h4>String</h4><pre>' . htmlspecialchars(print_r($string, 1)) . '</pre>');
        }
        return $string;
    }

    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();

        // main query
        $query = $db->getQuery(true)
            ->select(
                array(
                    'Email.*',
                    'Campaign.name AS campaign_name'
                )
            )
            ->from('#__jinbound_emails AS Email')
            ->leftJoin('#__jinbound_campaigns AS Campaign ON Email.campaign_id = Campaign.id')
            ->group('Email.id')
            ->order('Campaign.name ASC');

        $this->appendAuthorToQuery($query, 'Email');
        $this->filterSearchQuery(
            $query,
            $this->state->get('filter.search'),
            'Email',
            'id',
            array('name', 'subject', 'Campaign.name')
        );
        $this->filterPublished($query, $this->getState('filter.published'), 'Email');

        // Add the list ordering clause.
        $listOrdering = $this->getState('list.ordering', 'Email.sendafter');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }
}
