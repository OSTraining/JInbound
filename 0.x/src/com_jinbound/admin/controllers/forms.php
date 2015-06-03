<?php
/**
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JInbound', JPATH_ADMINISTRATOR.'/components/com_jinbound/helpers/jinbound.php');
JInbound::registerHelper('path');
JInbound::registerHelper('access');
JInbound::registerHelper('url');
JInbound::registerLibrary('JInboundBaseModel', 'models/basemodel');

jimport('joomla.application.component.controlleradmin');

class JInboundControllerForms extends JControllerAdmin
{
	public function getModel($name='Form', $prefix = 'JInboundModel') {
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
	
	public function permissions() {
		JInbound::registerHelper('access');
		JInboundHelperAccess::saveRulesWithRedirect('form');
	}
	
	public function migrate()
	{
		if (!JInboundHelperForm::needsMigration())
		{
			throw new Exception(JText::_('COM_JINBOUND_NO_FORM_MIGRATION_NEEDED'));
		}
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$forms = $db->setQuery($db->getQuery(true)
			->select('id, formid, formname, formbuilder')
			->from('#__jinbound_pages')
		)->loadObjectList();
		if (empty($forms))
		{
			throw new Exception(JText::_('COM_JINBOUND_NO_FORMS_FOUND'));
		}
		
		// migrate the forms
		// TODO break this up?
		foreach ($forms as $oldform)
		{
			$fieldids = array();
			// decode the form
			$structure = json_decode($oldform->formbuilder);
			// we can't always bank on the __ordering being there (for older installs)
			// so only use if available
			if (property_exists($structure, '__ordering'))
			{
				// determine the correct field ordering
				$ordering = explode('|', $structure->__ordering);
			}
			// no __ordering? just use the keys in the order they appear
			else
			{
				$ordering = array_keys((array) $structure);
			}
			// create the fields
			foreach ($ordering as $order => $oldfield)
			{
				// skip empties
				if (!(property_exists($structure, $oldfield)
					&& is_object($structure->$oldfield)
					&& property_exists($structure->$oldfield, 'title')))
				{
					continue;
				}
				if (empty($structure->$oldfield->title))
				{
					continue;
				}
				// build the field data to be saved
				$data = array(
					'title'        => $structure->$oldfield->title
				,	'name'         => property_exists($structure->$oldfield, 'name') ? $structure->$oldfield->name : $oldfield
				,	'type'         => $structure->$oldfield->type
				,	'description'  => ''
				,	'published'    => $structure->$oldfield->enabled
				,	'params'       => array()
				);
				// check old
				$oldfieldid = $db->setQuery($db->getQuery(true)
					->select('id')
					->from('#__jinbound_fields')
					->where('name = ' . $db->quote($data['name']))
					->where('title = ' . $db->quote($data['title']))
				)->loadResult();
				// this field has already been created
				if ($oldfieldid)
				{
					// if the field is enabled, go ahead and add to the list
					if ($structure->$oldfield->enabled)
					{
						// ensure the field is published at all costs
						$db->setQuery($db->getQuery(true)
							->update('#__jinbound_fields')
							->set('published = 1')
							->where('id = ' . $oldfieldid)
						)->query();
						$fieldids[] = $oldfieldid;
					}
					// regardless, we don't need to create this one
					continue;
				}
				// set attributes
				$attr = property_exists($structure->$oldfield, 'attributes') ? $structure->$oldfield->attributes : new stdClass;
				$opts = property_exists($structure->$oldfield, 'options') ? $structure->$oldfield->options : new stdClass;					
				// fix options
				if (!property_exists($opts, 'key'))
				{
					$opts->key = array();
				}
				if (property_exists($opts, 'name'))
				{
					$opts->key = $opts->name;
				}
				if (!property_exists($opts, 'value'))
				{
					$opts->value = array();
				}
				// fix attributes
				if (!property_exists($attr, 'key'))
				{
					$attr->key = array();
				}
				if (property_exists($attr, 'name'))
				{
					$attr->key = $attr->name;
				}
				if (!property_exists($attr, 'value'))
				{
					$attr->value = array();
				}
				$data['params']['required'] = property_exists($structure->$oldfield, 'required') && $structure->$oldfield->required;
				$data['params']['attrs'] = (array) $attr;
				$data['params']['opts']  = (array) $opts;
				// save the field
				$field_model = JInboundBaseModel::getInstance('Field', 'JInboundModel');
				if (!$field_model->save($data))
				{
					continue;
				}
				$newfieldid = (int) $field_model->getState($field_model->getName() . '.id');
				// only push existing, enabled fields
				if ($newfieldid && $structure->$oldfield->enabled)
				{
					$fieldids[] = $newfieldid;
				}
			}
			// new form data
			$newform = array(
				'title'      => $oldform->formname
			,	'published'  => 1
			,	'formfields' => implode('|', $fieldids)
			);
			// save the form
			$form_model = JInboundBaseModel::getInstance('Form', 'JInboundModel');
			if (!$form_model->save($newform))
			{
				continue;
			}
			$newformid = (int) $form_model->getState($form_model->getName() . '.id');
			// update the page
			$db->setQuery($db->getQuery(true)
				->update('#__jinbound_pages')
				->set('formid = ' . (int) $newformid)
				->set('formname = ' . $db->quote(''))
				->set('formbuilder = ' . $db->quote(''))
				->where('id = ' . $oldform->id)
			)->query();
		}
		// all done
		$app->enqueueMessage(JText::_('COM_JINBOUND_FORM_MIGRATION_COMPLETE'));
		$app->redirect(JInboundHelperUrl::_(array(), false));
	}
}
