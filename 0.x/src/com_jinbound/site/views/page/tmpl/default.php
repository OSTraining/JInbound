<?php
/**
 * @version		$Id$
 * @package		JInbound
 * @subpackage	com_jinbound
@ant_copyright_header@
 */

defined('JPATH_PLATFORM') or die;

echo $this->loadTemplate('layout');
//echo $this->loadTemplate($this->item->template);

?>
<pre><?php echo htmlspecialchars(print_r($this->item, 1)); ?></pre>
<pre><?php echo htmlspecialchars(print_r($this->form, 1)); ?></pre>