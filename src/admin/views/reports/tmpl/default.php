<?php
/**
 * @package   jInbound
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2015 Anything-Digital.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

JHtml::_('behavior.calendar');


$permissions = $this->loadTemplate('permissions');
$useTabs     = $permissions && JFactory::getUser()->authorise('core.admin', 'com_jinbound');

?>
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        };
    </script>
    <div class="container-fluid <?php echo $this->viewClass; ?>" id="jinbound_component">
        <?php
        $mainAttributes = array(
            'id' => 'j-main-container'
        );
        if (!empty($this->sidebar)) :
            $mainAttributes['class'] = 'span10';
            ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo $this->sidebar; ?>
            </div>
        <?php
        endif;
        ?>
        <div <?php echo ArrayHelper::toString($mainAttributes); ?>>
            <?php
            if ($useTabs) :
                echo JHtml::_('jinbound.startTabSet', 'jinbound_default_tabs', array('active' => 'content_tab'));

                echo JHtml::_(
                    'jinbound.addTab',
                    'jinbound_default_tabs',
                    'content_tab',
                    JText::_('COM_JINBOUND_REPORTS', true)
                );
            endif;
            ?>
            <form action="<?php echo JInboundHelperUrl::view('reports'); ?>"
                  method="post"
                  id="adminForm"
                  name="adminForm"
                  class="form-validate"
                  enctype="multipart/form-data">
                <div class="row-fluid">
                    <div class="span12 text-center">
                        <div class="reports_search">
                            <?php
                            echo $this->campaign_filter;
                            echo $this->page_filter;
                            echo $this->priority_filter;
                            echo $this->status_filter;

                            echo JHtml::_(
                                'calendar',
                                $this->state->get('filter_start'),
                                'filter_start',
                                'filter_start',
                                '%Y-%m-%d',
                                array(
                                    'size'        => 10,
                                    'placeholder' => JText::_('COM_JINBOUND_FROM'),
                                    'onchange'    => $this->filter_change_code
                                )
                            );

                            echo JHtml::_(
                                'calendar',
                                $this->state->get('filter_end'),
                                'filter_end',
                                'filter_end',
                                '%Y-%m-%d',
                                array(
                                    'size'        => 10,
                                    'placeholder' => JText::_('COM_JINBOUND_TO'),
                                    'onchange'    => $this->filter_change_code
                                )
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <?php echo $this->loadTemplate('dashboard'); ?>
                <div>
                    <input name="task" value="" type="hidden"/>
                </div>
            </form>

            <?php
            if ($useTabs) :
                echo JHtml::_('jinbound.endTab');

                echo JHtml::_(
                    'jinbound.addTab',
                    'jinbound_default_tabs',
                    'permissions_tab',
                    JText::_('JCONFIG_PERMISSIONS_LABEL', true)
                );
                echo $permissions;
                echo JHtml::_('jinbound.endTab');

                echo JHtml::_('jinbound.endTabSet');
            endif;
            ?>
        </div>
    </div>
<?php
echo $this->loadTemplate('footer');

echo $this->loadTemplate('script');

