<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

?>
<h2><?php echo JText::_('COM_JINBOUND_CREATE_A_NEW_LANDING_PAGE'); ?></h2>
<div class="btn-toolbar">
	<div class="btn-group row-fluid">
<?php foreach (array('A', 'B', 'C', 'D') as $template) : ?>
		<div class="btn span2<?php echo ('A' == $template ? ' offset1' : ''); ?>">
			<a href="<?php echo JInboundHelperUrl::edit('page', 0, false, array('set' => $template)); ?>">
				<span class="row-fluid">
					<img class="img-polaroid" src="<?php echo $this->escape(JInboundHelperUrl::media() . '/images/layout-' . strtolower($template) . '.png'); ?>" alt="<?php echo $this->escape(JText::_('COM_JINBOUND_TEMPLATE_' . $template)); ?>" />
				</span>
				<span class="row-fluid"><?php echo $this->escape(JText::_('COM_JINBOUND_TEMPLATE_' . $template)); ?></span>
			</a>
		</div>
<?php endforeach; ?>
		<div class="btn span2">
			<a href="<?php echo JInboundHelperUrl::edit('page', 0, false, array('set' => 'custom')); ?>">
				<span class="row-fluid">
					<img class="img-polaroid" src="<?php echo $this->escape(JInboundHelperUrl::media() . '/images/layout-custom.png'); ?>" alt="<?php echo $this->escape(JText::_('COM_JINBOUND_TEMPLATE_CUSTOM')); ?>" />
				</span>
				<span class="row-fluid"><?php echo $this->escape(JText::_('COM_JINBOUND_TEMPLATE_CUSTOM')); ?></span>
			</a>
		</div>
	</div>
</div>