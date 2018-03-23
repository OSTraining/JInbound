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

use Alledia\Installer\AbstractScript;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

// Adapt for install and uninstall environments
if (file_exists(__DIR__ . '/admin/library/Installer/AbstractScript.php')) {
    require_once __DIR__ . '/admin/library/Installer/AbstractScript.php';
} else {
    require_once __DIR__ . '/library/Installer/AbstractScript.php';
}

jimport('joomla.form.form');

class com_JInboundInstallerScript extends AbstractScript
{
    /**
     * @param JInstallerAdapter $parent
     *
     * @return bool
     * @throws Exception
     */
    public function install($parent)
    {
        try {
            return parent::install($parent);

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage(
                $e->getFile() . ':' . $e->getLine() . '<br/>' . $e->getMessage(),
                'error'
            );

        } catch (Throwable $e) {
            JFactory::getApplication()->enqueueMessage(
                $e->getFile() . ':' . $e->getLine() . '<br/>' . $e->getMessage(),
                'error'
            );
        }

        return false;
    }

    /**
     * @TODO: remove contacts added with jinbound, including category (must remove contacts first)
     *
     * @param JInstallerAdapter $parent
     *
     * @return void
     * @throws Exception
     */
    public function uninstall($parent)
    {
        parent::uninstall($parent);

        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where($db->quoteName('extension') . ' = ' . $db->quote('com_contact'))
                ->where($db->quoteName('note') . ' = ' . $db->quote('com_jinbound'))
        );

        try {
            $catids = $db->loadColumn();

        } catch (Exception $e) {
            $catids = array();
        }

        if (is_array($catids) && !empty($catids)) {
            $deletedCats  = array();
            $deletedLeads = array();

            ArrayHelper::toInteger($catids);

            $db->setQuery(
                $db->getQuery(true)
                    ->select('id')
                    ->from('#__contact_details')
                    ->where($db->quoteName('catid') . ' IN (' . implode(',', $catids) . ')')
            );

            try {
                $ids = $db->loadColumn();

            } catch (Exception $e) {
                $ids = array();
            }

            if ($ids) {
                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_contact/tables');

                foreach ($ids as $id) {
                    $table = JTable::getInstance('Contact', 'ContactTable');
                    $table->load($id);
                    if ($table->id == $id) {
                        if ($table->delete($id)) {
                            $deletedLeads[] = $id;
                        }
                    }
                }
            }

            // now delete the category
            foreach ($catids as $catid) {
                $table = JTable::getInstance('Category');
                $table->load($catid);
                if ($table->id == $catid) {
                    if ($table->delete($catid)) {
                        $deletedCats[] = $catid;
                    }
                }
            }

            if (!empty($deletedCats)) {
                $app->enqueueMessage(
                    JText::sprintf('COM_JINBOUND_UNINSTALL_DELETED_N_CATEGORIES', count($deletedCats))
                );
            }

            if (!empty($deletedLeads)) {
                $app->enqueueMessage(JText::sprintf('COM_JINBOUND_UNINSTALL_DELETED_N_LEADS', count($deletedLeads)));
            }
        }
    }

    /**
     * @param string            $type
     * @param JInstallerAdapter $parent
     *
     * @return void
     * @throws Exception
     */
    public function postflight($type, $parent)
    {
        try {
            parent::postFlight($type, $parent);

            require_once JPATH_ADMINISTRATOR . '/components/com_jinbound/include.php';

            $lang = JFactory::getLanguage();
            $root = $parent->getParent()->getPath('source');

            $lang->load('com_jinbound', $root);
            $lang->load('com_jinbound.sys', $root);

            switch ($type) {
                case 'install':
                case 'discover_install':
                    $this->saveDefaults($parent);
                    // Fall through

                case 'update':
                    $this->removePackage();
                    $this->checkAssets();
                    $this->checkDefaultReportEmails();
                    $this->forceReportEmailOption($parent);
                    $this->checkContactCategory();
                    $this->checkInboundCategory();
                    $this->checkCampaigns();
                    $this->checkContactSubscriptions();
                    $this->checkDefaultPriorities();
                    $this->checkDefaultStatuses();
                    $this->checkEmailVersions();
                    $this->fixMissingLanguageDefaults();
                    $this->cleanupMissingRecords();
                    $this->checkCorePlugins();
                    break;
            }

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage(
                'postflight():' . __LINE__ . '<br/>'
                . $e->getFile() . ':' . $e->getLine() . '<br/>' . $e->getMessage(),
                'error'
            );
        } catch (Throwable $e) {
            JFactory::getApplication()->enqueueMessage(
                'postflight:' . __LINE__ . '<br/>'
                . $e->getFile() . ':' . $e->getLine() . '<br/>' . $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * @param JInstallerAdapter $parent
     *
     * @return void
     * @throws Exception
     */
    protected function saveDefaults($parent)
    {
        $app        = JFactory::getApplication();
        $configFile = $parent->getParent()->getPath('extension_root') . '/config.xml';

        if (!is_file($configFile)) {
            return;
        }
        $form = JForm::getInstance('installer', $configFile, array(), false, '/config');

        $params = array();
        if ($fieldsets = $form->getFieldsets()) {
            foreach ($fieldsets as $fieldset) {
                $fields = $form->getFieldset($fieldset->name);
                if (!empty($fields)) {
                    /** @var JFormField $field */
                    foreach ($fields as $name => $field) {
                        $fieldName  = $field->name;
                        $fieldValue = $field->value;

                        switch ($fieldName) {
                            // force report email value
                            case 'report_recipients':
                                $fieldValue = $app->get('mailfrom');
                                break;

                            case 'load_jquery_back':
                            case 'load_jquery_ui_back':
                            case 'load_bootstrap_back':
                                $fieldValue = '0';
                                break;

                            default:
                                break;
                        }
                        $params[$fieldName] = $fieldValue;
                    }
                }
            }
        }

        $db = JFactory::getDbo();
        $db->setQuery(
            $db->getQuery(true)
                ->update('#__extensions')
                ->set('params = ' . $db->quote(json_encode($params)))
                ->where('element = ' . $db->quote($parent->get('element')))
        );
        try {
            $db->execute();

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkDefaultReportEmails()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();
        $id  = 0;

        $emails = $db->setQuery(
            $db->getQuery(true)
                ->select('id, subject, htmlbody, plainbody')
                ->from('#__jinbound_emails')
                ->where($db->quoteName('type') . ' = ' . $db->quote('report'))
        )->loadObjectList();

        if ($emails) {
            $app->enqueueMessage('Checking existing report emails');

            foreach ($emails as $email) {
                $found = false;
                foreach (array('', '_2') as $sfx) {
                    $html = preg_replace(
                        '/\s/',
                        '',
                        JText::_("COM_JINBOUND_DEFAULT_REPORT_EMAIL_HTMLBODY_LEGACY{$sfx}")
                    );

                    $plain = preg_replace(
                        '/\s/',
                        '',
                        implode(
                            "\n",
                            explode('<br>', JText::_("COM_JINBOUND_DEFAULT_REPORT_EMAIL_PLAINBODY_LEGACY{$sfx}"))
                        )
                    );

                    if (preg_replace('/\s/', '', $email->htmlbody) == $html
                        && preg_replace('/\s/', '', $email->plainbody) == $plain
                    ) {
                        $app->enqueueMessage('Found older report email - updating');
                        $id    = $email->id;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    break;
                }
            }

            if (empty($id)) {
                $app->enqueueMessage('Non-default report emails found - not updating');
                return;
            }
        }

        $data = array(
            'name'        => JText::_('COM_JINBOUND_DEFAULT_REPORT_EMAIL_NAME'),
            'published'   => '1',
            'type'        => 'report',
            'campaign_id' => '',
            'fromname'    => $app->get('fromname'),
            'fromemail'   => $app->get('mailfrom'),
            'sendafter'   => '',
            'subject'     => JText::_('COM_JINBOUND_DEFAULT_REPORT_EMAIL_SUBJECT'),
            'htmlbody'    => JText::_('COM_JINBOUND_DEFAULT_REPORT_EMAIL_HTMLBODY'),
            'plainbody'   => implode("\n", explode('<br>', JText::_('COM_JINBOUND_DEFAULT_REPORT_EMAIL_PLAINBODY'))),
            'params'      => array(
                'reports_frequency' => '1 WEEK',
                'recipients'        => $app->get('mailfrom'),
                'campaigns'         => array()
            )
        );

        if ($id) {
            $data['id'] = $id;
        }

        try {
            $save = JInboundBaseModel::getInstance('Email', 'JInboundModel')->save($data);
            $app->enqueueMessage('Save ' . ($save ? '' : 'not ') . 'successful', $save ? 'message' : 'error');

            return;

        } catch (Exception $e) {
            // ignore
        } catch (Throwable $e) {
            // ignore
        }

        $app->enqueueMessage('Could not save default emails', 'error');
    }

    /**
     * @param JInstallerAdapter $parent
     *
     * @return void
     * @throws Exception
     */
    protected function forceReportEmailOption($parent)
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select('params')
                ->from('#__extensions')
                ->where('element = ' . $db->quote('com_jinbound'))
        );

        try {
            $json   = $db->loadResult();
            $params = json_decode($json);
            if (!is_object($params)) {
                $app->enqueueMessage('Data is not an object', 'error');
            }

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            return;
        }

        // if this variable is set, don't update the value
        // defaults method will handle new installs
        if (property_exists($params, 'report_force_admin')) {
            return;
        }

        // check that there's a value - don't mess with non-empty values
        if (property_exists($params, 'report_recipients') && !empty($params->report_recipients)) {
            return;
        }

        // set from config
        $params->report_recipients = $app->get('mailfrom');

        $db->setQuery(
            $db->getQuery(true)
                ->update('#__extensions')
                ->set('params = ' . $db->quote(json_encode($params)))
                ->where('element = ' . $db->quote('com_jinbound'))
        );

        try {
            $db->execute();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkContactCategory()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();
        $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where(
                    array(
                        $db->quoteName('extension') . ' = ' . $db->quote('com_contact'),
                        $db->quoteName('published') . ' = 1',
                        $db->quoteName('note') . ' = ' . $db->quote('com_jinbound')
                    )
                )
        );
        try {
            $categories = $db->loadColumn();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage());
            return;
        }

        if ($categories) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_CONTACT_CATEGORIES_FOUND'));
            return;
        }

        /** @var JTableCategory $category */
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_contact/tables');
        $category = JTable::getInstance('Category');

        $categoryData = array(
            'parent_id'   => $category->getRootId(),
            'extension'   => 'com_contact',
            'title'       => JText::_('COM_JINBOUND_DEFAULT_CONTACT_CATEGORY_TITLE'),
            'note'        => 'com_jinbound',
            'description' => JText::_('COM_JINBOUND_DEFAULT_CONTACT_CATEGORY_DESCRIPTION'),
            'published'   => 1,
            'language'    => '*'
        );
        if (!$category->bind($categoryData)) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_CONTACT_CATEGORIES_BIND_ERROR'));
            return;
        }
        if (!$category->check()) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_CONTACT_CATEGORIES_CHECK_ERROR'));
            return;
        }
        if (!$category->store()) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_CONTACT_CATEGORIES_STORE_ERROR'));
            return;
        }
        $category->moveByReference(0, 'last-child', $category->id);
        $app->enqueueMessage(JText::_('COM_JINBOUND_CONTACT_CATEGORIES_INSTALLED'));
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkInboundCategory()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();
        $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where(
                    array(
                        $db->quoteName('extension') . ' = ' . $db->quote('com_jinbound'),
                        $db->quoteName('published') . ' = 1'
                    )

                )
        );
        try {
            $categories = $db->loadColumn();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage());
            return;
        }

        if ($categories) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_JINBOUND_CATEGORIES_FOUND'));
            return;
        }

        /** @var JTableCategory $category */
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_contact/tables');
        $category     = JTable::getInstance('Category');
        $categoryData = array(
            'parent_id'   => $category->getRootId(),
            'extension'   => 'com_jinbound',
            'title'       => JText::_('COM_JINBOUND_DEFAULT_JINBOUND_CATEGORY_TITLE'),
            'description' => JText::_('COM_JINBOUND_DEFAULT_JINBOUND_CATEGORY_DESCRIPTION'),
            'published'   => 1,
            'language'    => '*'
        );
        if (!$category->bind($categoryData)) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_JINBOUND_CATEGORIES_BIND_ERROR'));
            return;
        }
        if (!$category->check()) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_JINBOUND_CATEGORIES_CHECK_ERROR'));
            return;
        }
        if (!$category->store()) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_JINBOUND_CATEGORIES_STORE_ERROR'));
            return;
        }
        $category->moveByReference(0, 'last-child', $category->id);
        $app->enqueueMessage(JText::_('COM_JINBOUND_JINBOUND_CATEGORIES_INSTALLED'));
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkCampaigns()
    {
        $db = JFactory::getDbo();

        try {
            $campaigns = $db->setQuery(
                $db->getQuery(true)
                    ->select('*')
                    ->from('#__jinbound_campaigns')
            )
                ->loadObjectList();

            if (!empty($campaigns)) {
                return;
            }

            $data = array(
                'name'       => JText::_('COM_JINBOUND_DEFAULT_CAMPAIGN_NAME'),
                'published'  => 1,
                'created'    => JFactory::getDate()->toSql(),
                'created_by' => JFactory::getUser()->get('id')
            );

            /** @var JInboundTableCampaign $campaign */
            $campaign = JTable::getInstance('Campaign', 'JInboundTable');
            if (!($campaign->bind($data) && $campaign->check() && $campaign->store())) {
                throw new RuntimeException($campaign->getError());
            }

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkContactSubscriptions()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select('Contact.id')
                ->from('#__contact_details AS Contact')
                ->leftJoin('#__jinbound_subscriptions AS Subs ON Subs.contact_id = Contact.id')
                ->where('Subs.enabled IS NULL')
                ->group('Contact.id')
        );


        try {
            $contacts = $db->loadColumn();
            if (!$contacts) {
                return;
            }

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage());
            return;
        }

        ArrayHelper::toInteger($contacts);
        $query = $db->getQuery(true)
            ->insert('#__jinbound_subscriptions')
            ->columns(array('contact_id', 'enabled'));
        foreach ($contacts as $contact) {
            $query->values("$contact, 1");
        }
        $db->setQuery($query);
        try {
            $db->execute();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage());
            return;
        }
    }

    /**
     * checks for the presence of priorities and if none are found creates them
     *
     * @return void
     * @throws Exception
     */
    protected function checkDefaultPriorities()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();
        $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_priorities')
                ->order('ordering ASC')
        );
        try {
            $priorities = $db->loadColumn();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage());
            return;
        }

        if ($priorities) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_PRIORITIES_FOUND'));
            return;
        }

        /** @var JInboundTablePriority $priority */
        foreach (array('COLD', 'WARM', 'HOT', 'ON_FIRE') as $i => $p) {
            $priority     = JTable::getInstance('Priority', 'JInboundTable');
            $priorityData = array(
                'name'        => JText::_('COM_JINBOUND_PRIORITY_' . $p),
                'description' => JText::_('COM_JINBOUND_PRIORITY_' . $p . '_DESC'),
                'published'   => 1,
                'ordering'    => $i + 1
            );
            if (!$priority->bind($priorityData)) {
                continue;
            }
            if (!$priority->check()) {
                continue;
            }
            if (!$priority->store()) {
                continue;
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function checkDefaultStatuses()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();
        $db->setQuery(
            $db->getQuery(true)
                ->select('id')
                ->from('#__jinbound_lead_statuses')
        );
        try {
            $statuses = $db->loadColumn();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage());
            return;
        }

        if ($statuses) {
            $app->enqueueMessage(JText::_('COM_JINBOUND_STATUSES_FOUND'));
            return;
        }

        $leads   = array('NEW_LEAD', 'NOT_INTERESTED', 'EMAIL', 'VOICEMAIL', 'GOAL_COMPLETED');
        $default = 0;
        $final   = count($leads) - 1;

        /** @var JInboundTableStatus $status */
        foreach ($leads as $i => $p) {
            $status     = JTable::getInstance('Status', 'JInboundTable');
            $statusData = array(
                'name'        => JText::_('COM_JINBOUND_STATUS_' . $p),
                'description' => JText::_('COM_JINBOUND_STATUS_' . $p . '_DESC'),
                'published'   => 1,
                'ordering'    => $i + 1,
                'default'     => (int)($i == $default),
                'active'      => (int)!('NOT_INTERESTED' == $p),
                'final'       => (int)($i == $final)
            );
            if (!$status->bind($statusData)) {
                continue;
            }
            if (!$status->check()) {
                continue;
            }
            if (!$status->store()) {
                continue;
            }
        }
    }

    /**
     * adds initial versions to all emails, updates records to reflect
     *
     * NOTE: can't do anything about data we didn't track before, sorry folks
     *
     * @return void
     * @throws Exception
     */
    protected function checkEmailVersions()
    {
        $app = JFactory::getApplication();
        $db  = JFactory::getDbo();

        // get all the emails that don't appear in the email versions table
        $db->setQuery(
            $db->getQuery(true)
                ->select('Email.id')
                ->from('#__jinbound_emails AS Email')
                ->leftJoin('#__jinbound_emails_versions AS Version ON Email.id = Version.email_id')
                ->where('Version.id IS NULL')
                ->group('Email.id')
        );
        try {
            $emails = $db->loadObjectList();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            return;
        }

        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            $db->setQuery(
                'INSERT INTO #__jinbound_emails_versions'
                . ' (email_id, subject, htmlbody, plainbody)'
                . ' SELECT id, subject, htmlbody, plainbody FROM #__jinbound_emails'
                . ' WHERE id = ' . $email->id
            );
            try {
                $db->execute();
            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
                continue;
            }

            // update version_id in records table to match the newly created version
            $db->setQuery(
                'UPDATE #__jinbound_emails_records'
                . ' SET version_id = ((SELECT MAX(id) FROM #__jinbound_emails_versions WHERE email_id = ' . $email->id . '))'
                . ' WHERE email_id = ' . $email->id
            );
            try {
                $db->execute();

            } catch (Exception $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
                continue;
            }
        }
    }

    /**
     * some language strings were not present and saved to the database
     */
    protected function fixMissingLanguageDefaults()
    {
        $tags = array(
            'lead_statuses' => array(
                'COM_JINBOUND_STATUS_CONVERTED_DESC',
                'COM_JINBOUND_STATUS_EMAIL_DESC',
                'COM_JINBOUND_STATUS_NEW_LEAD_DESC',
                'COM_JINBOUND_STATUS_NOT_INTERESTED_DESC',
                'COM_JINBOUND_STATUS_VOICEMAIL_DESC'
            ),
            'priorities'    => array(
                'COM_JINBOUND_PRIORITY_COLD_DESC',
                'COM_JINBOUND_PRIORITY_WARM_DESC',
                'COM_JINBOUND_PRIORITY_HOT_DESC',
                'COM_JINBOUND_PRIORITY_ON_FIRE_DESC'
            )
        );

        // connect to the database and fix each one
        $db = JFactory::getDbo();
        foreach ($tags as $table => $labels) {
            foreach ($labels as $label) {
                $db->setQuery(
                    $db->getQuery(true)
                        ->update('#__jinbound_' . $table)
                        ->set($db->quoteName('description') . ' = ' . $db->quote(JText::_($label)))
                        ->where($db->quoteName('description') . ' = ' . $db->quote($label))
                );

                try {
                    $db->execute();

                } catch (Exception $e) {
                    // ignore
                }
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function cleanupMissingRecords()
    {
        $db  = JFactory::getDbo();

        try {
            $ids = $db->setQuery(
                $db->getQuery(true)
                    ->select('id')->from('#__jinbound_contacts')
            )
                ->loadColumn();

        } catch (Exception $e) {
            return;
        }
        if (empty($ids)) {
            return;
        }

        $tables = array(
            '#__jinbound_contacts_campaigns'  => 'contact_id',
            '#__jinbound_conversions'         => 'contact_id',
            '#__jinbound_contacts_statuses'   => 'contact_id',
            '#__jinbound_contacts_priorities' => 'contact_id',
            '#__jinbound_emails_records'      => 'lead_id',
            '#__jinbound_notes'               => 'lead_id',
            '#__jinbound_subscriptions'       => 'contact_id'
        );

        foreach ($tables as $table => $key) {
            $query = $db->getQuery(true)->delete($table);
            foreach ($ids as $id) {
                $query->clear('where')->where($db->quoteName($key) . ' <> ' . (int)$id);
            }
            try {
                $db->setQuery($query)->execute();

            } catch (Exception $e) {
                continue;
            }
        }
    }

    /**
     * Check and fix any legacy issues with assets
     * NOTE! This CANNOT be called before parent post flight completes!
     */
    public function checkAssets()
    {
        // Let's first get rid of crappy old broken assets
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__assets')
            ->where('name LIKE ' . $db->quote('#__jinbound%'));
        $db->setQuery($query)->execute();

        /** @var JTableAsset $root */
        $root = JTable::getInstance('Asset');
        $root->loadByName('com_jinbound');

        $sectionNames = array(
            'campaign'   => 'campaigns',
            'contact'    => 'contacts',
            'conversion' => 'conversions',
            'email'      => 'emails',
            'field'      => 'fields',
            'form'       => 'forms',
            'page'       => 'pages',
            'priority'   => 'priorities',
            'report'     => 'reports',
            'status'     => 'statuses'
        );

        foreach ($sectionNames as $itemName => $sectionName) {
            /** @var JTableAsset $sectionRoot */
            $name        = 'com_jinbound.' . $sectionName;
            $sectionRoot = JTable::getInstance('Asset');

            if (!$sectionRoot->loadByName('com_jinbound.' . $itemName)) {
                $sectionRoot->loadByName($name);
            }

            $rules = $sectionRoot->rules ? json_decode($sectionRoot->rules) : new stdClass();
            $dummy = 'core.dummy';
            if (isset($rules->$dummy)) {
                unset($rules->$dummy);
            }

            $rootTitle = JText::_(sprintf('COM_JINBOUND_%s_PERMISSIONS', strtoupper($sectionName)));
            $sectionRoot->setProperties(
                array(
                    'name'      => $name,
                    'title'     => $rootTitle,
                    'parent_id' => $root->id,
                    'rules'     => json_encode((object)$rules)
                )
            );

            if ($success = $sectionRoot->store()) {
                if ($sectionRoot->parent_id != $root->id) {
                    $sectionRoot->moveByReference($root->id, 'last-child');
                }
                if (($sectionRoot->rgt - $sectionRoot->lft) < 2) {
                    if ($success = $sectionRoot->rebuild()) {
                        $this->checkAssetLeaves($sectionName, $itemName);
                    }
                }

                if ($success = $sectionRoot->moveByReference($root->id, 'last-child')) {
                    $this->checkAssetLeaves($sectionName, $itemName);
                }
            }
        }

        if (!$success) {
            $this->setMessage($sectionRoot->getError(), 'error');
        }
    }

    /**
     * Checks assets at level 3 from the custom asset areas
     *
     * @param string $sectionName
     * @param string $itemName
     *
     * @return void
     */
    protected function checkAssetLeaves($sectionName, $itemName)
    {
        $db = JFactory::getDbo();

        /**
         * Check for any incorrectly nested registration assets
         */
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__assets')
            ->where(
                array(
                    'name LIKE ' . $db->quote("com_jinbound.{$itemName}.%"),
                    'level != 3'
                )
            );

        if ($assetIds = $db->setQuery($query)->loadColumn()) {
            /** @var JTableAsset $rootAsset */
            $rootAsset = JTable::getInstance('Asset');
            if ($rootAsset->loadByName("com_jinbound.{$sectionName}")) {
                foreach ($assetIds as $assetId) {
                    /** @var JTableAsset $asset */
                    $asset = JTable::getInstance('Asset');
                    $asset->load($assetId);
                    $asset->moveByReference($rootAsset->id, 'last-child');
                }
            }
        }
    }

    /**
     * Remove all references to the old package installer
     */
    protected function removePackage()
    {
        $db = JFactory::getDbo();

        $query       = $db->getQuery(true)
            ->select('extension_id')
            ->from('#__extensions')
            ->where('element = ' . $db->quote('pkg_jinbound'));
        $extensionId = $db->setQuery($query)->loadResult();

        if (!empty($extensionId)) {
            // Extension
            $query = $db->getQuery(true)
                ->delete('#__extensions')
                ->where('element = ' . $db->quote('pkg_jinbound'));
            $db->setQuery($query)->execute();

            // Sub extensions to now removed package
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set('package_id = 0')
                ->where('package_id = ' . $extensionId);
            $db->setQuery($query)->execute();

            // Update site
            $query = $db->getQuery(true)
                ->delete('#__update_sites')
                ->where('name = ' . $db->quote('jinbound'))
                ->where('type = ' . $db->quote('collection'));
            $db->setQuery($query)->execute();

            // Update sites extension
            $query = $db->getQuery(true)
                ->delete('#__update_sites_extensions')
                ->where('extension_id = ' . $db->quote($extensionId));
            $db->setQuery($query)->execute();

            // Updates
            $query = $db->getQuery(true)
                ->delete('#__updates')
                ->where('extension_id = ' . $db->quote($extensionId));
            $db->setQuery($query)->execute();

            // Schemas
            $query = $db->getQuery(true)
                ->delete('#__schemas')
                ->where('extension_id = ' . $db->quote($extensionId));
            $db->setQuery($query)->execute();
        }
    }

    /**
     * Make sure all the core plugins are enabled
     *
     * @return void
     */
    protected function checkCorePlugins()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('extension_id, enabled')
            ->from('#__extensions')
            ->where(
                array(
                    'enabled <> 1',
                    'type = '  . $db->quote('plugin'),
                    'element = ' . $db->quote('jinbound'),
                    sprintf(
                        'folder IN (%s)',
                        join(',', array_map(array($db, 'quote'), array('system', 'user', 'content')))
                    )
                )
            );

        $disabledCore = $db->setQuery($query)->loadObjectList();
        foreach ($disabledCore as $plugin) {
            $plugin->enabled = 1;
            $db->updateObject('#__extensions', $plugin, array('extension_id'));
        }
    }

}
