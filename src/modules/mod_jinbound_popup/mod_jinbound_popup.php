<?php
/**
 * @package		JInbound
 * @subpackage	mod_jinbound_popup
@ant_copyright_header@
 */

defined('_JEXEC') or die;

// lighten the load by returning an empty module if this user has already seen it
if (filter_input(INPUT_COOKIE, 'mod_' . $module->id))
{
	return;
}

// get helper class
require_once dirname(__FILE__) . '/helper.php';

if (version_compare(JInbound::VERSION, '2.1.6', '<='))
{
	echo JText::_('MOD_JINBOUND_POPUP_REQUIRES_JINBOUND_2_1_6');
	return;
}

modJinboundPopupHelper::addHtmlAssets();

// initialise
$form = modJinboundPopupHelper::getForm($module, $params);
$data = modJinboundPopupHelper::getFormData($module, $params);
$sfx  = $params->get('moduleclass_sfx', '');
$btn  = $params->get('submit_text', 'JSUBMIT');
$introtext = $params->get('introtext', '');
$stripped  = strip_tags($introtext);
$showintro = !empty($stripped);

if (false === $form || false === $data)
{
	return false;
}

// coerce empty button text
if (empty($btn))
{
	$btn = 'JSUBMIT';
}

// create data to store in the session in order to save form
$session_name = 'mod_jinbound_popup.form.' . $module->id;
JFactory::getSession()->set($session_name, $data);
$form_url = JInboundHelperUrl::toFull(JInboundHelperUrl::task('lead.save', true, array(
	'token' => $session_name
)));

// render module
require JModuleHelper::getLayoutPath('mod_jinbound_popup', $params->get('layout', 'default'));