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

defined('JPATH_PLATFORM') or die;

class JInboundViewForms extends JInboundListView
{
    /**
     * Default sorting column
     *
     * @var string
     */
    protected $_sortColumn = 'Form.title';

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'Form.title'      => JText::_('COM_JINBOUND_TITLE'),
            'Form.type'       => JText::_('COM_JINBOUND_FORM_TYPE_LABEL'),
            'FormFieldCount'  => JText::_('COM_JINBOUND_FIELD_COUNT'),
            'Form.created_by' => JText::_('COM_JINBOUND_CREATED_BY'),
            'Form.published'  => JText::_('COM_JINBOUND_PUBLISHED'),
            'Form.default'    => JText::_('COM_JINBOUND_DEFAULT')
        );
    }
}
