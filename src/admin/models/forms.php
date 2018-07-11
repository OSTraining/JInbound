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
 * This model supports retrieving lists of forms.
 *
 * @package        jInbound
 * @subpackage     com_jinbound
 */
class JInboundModelForms extends JInboundListModel
{
    /**
     * @var string
     */
    protected $context = 'com_jinbound.forms';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'Form.id',
                'Form.title',
                'Form.type',
                'FormFieldCount',
                'Form.created_by',
                'Form.published',
                'Form.default'
            );
        }

        parent::__construct($config);
    }

    protected function getStoreId($id = '')
    {
        $id = join(
            ':',
            array(
                $id,
                'com_jinbound',
                $this->getState('filter.published'),
                $this->getState('filter.access'),
                $this->getState('filter.parentId'),
                $this->getState('filter.formtype'),
                $this->getState('filter.default'),
                $this->getState('filter.search')
            )
        );

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select($this->getState('list.select', 'Form.*'))
            ->from('#__jinbound_forms AS Form')
            ->select('COUNT(Xref.field_id) AS FormFieldCount')
            ->leftJoin('#__jinbound_form_fields AS Xref ON Form.id=Xref.form_id');

        $this->appendAuthorToQuery($query, 'Form');

        $published = $this->getState('filter.published');
        if ($published === '') {
            $query->where('(Form.published = 0 OR Form.published = 1)');
        } elseif (is_numeric($published)) {
            $query->where('Form.published = ' . (int)$published);
        }

        $formtype = $this->getState('filter.formtype');
        if ($formtype === '') {
            $query->where('(Form.type = 0 OR Form.type = 1)');
        } elseif (is_numeric($formtype)) {
            $query->where('Form.type = ' . (int)$formtype);
        }

        $default = $this->getState('filter.default');
        if ($default === '') {
            $query->where('(Form.default = 0 OR Form.default = 1)');
        } elseif (is_numeric($default)) {
            $query->where('Form.default = ' . (int)$default);
        }

        $this->filterSearchQuery($query, $this->getState('filter.search'), 'Form', 'id', array('title'));

        $listOrdering = $this->getState('list.ordering', 'Form.title');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));
        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        $query->group('Form.id');

        return $query;
    }
}
