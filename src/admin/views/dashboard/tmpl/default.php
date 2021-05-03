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

JText::script('COM_JINBOUND_RESET_CONFIRM');

$user = JFactory::getUser();

$jserror = "javascript:alert('" . JText::_('JERROR_ALERTNOAUTHOR') . "');";

?>
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            if ('reset' === task && confirm(Joomla.JText._('COM_JINBOUND_RESET_CONFIRM'))) {
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        };
    </script>

    <form action="index.php?option=com_jinbound"
          method="post"
          id="adminForm"
          name="adminForm"
          class="form-validate"
          enctype="multipart/form-data">
        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </form>

    <div class="container-fluid" id="jinbound_component">
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
            <!-- Main Dashboard columns -->
            <div class="row-fluid">
                <!-- Row 1 - Welcome Message-->
                <div class="row-fluid" id="welcome_message">
                    <div class="span12">
                        <p class="lead"><?php echo JText::_('COM_JINBOUND_WELCOME_TO_JINBOUND'); ?></p>
                    </div>
                </div>

                <!-- Row 2 - Buttons -->
                <div class="row-fluid" id="welcome_buttons">
                    <?php
                    echo $this->renderButton(
                        'campaigns',
                        'lead_manager.png',
                        JText::_('COM_JINBOUND_STEP_1_CREATE_A_CAMPAIGN')
                    );

                    echo $this->renderButton(
                        'emails',
                        'leads_nurturing.png',
                        JText::_('COM_JINBOUND_STEP_2_WRITE_EMAILS_FOR_YOUR_CAMPAIGN')
                    );

                    echo $this->renderButton(
                        'pages',
                        'landing_pages.png',
                        JText::_('COM_JINBOUND_STEP_3_CREATE_LANDING_PAGES_TO_GET_PEOPLE_INTO_YOUR_CAMPAIGN')
                    );

                    echo $this->renderButton(
                        'contacts',
                        'reports.png',
                        JText::_('COM_JINBOUND_STEP_4_GET_REPORTS_ON_PEOPLE_WHO_SIGNED_UP')
                    );
                    ?>
                </div>

                <?php
                if ($user->authorise('core.manage', 'com_jinbound.reports')) :
                    ?>
                    <!-- Row 3 - Monthly Report -->
                    <div class="row-fluid">
                        <!-- start the container -->
                        <div class="well">
                            <!-- Report Heading -->
                            <div class="row-fluid">
                                <div class="span12">
                                    <h3 class="text-center">
                                        <?php echo JText::_('COM_JINBOUND_MONTHLY_REPORTING_SNAPSHOT'); ?>
                                    </h3>
                                </div>
                            </div>
                            <?php
                            echo $this->reports->glance;

                            $filter_start = new DateTime();
                            $filter_end   = clone $filter_start;
                            $filter_start->modify('-1 month');
                            $filter_end->modify('+1 day');

                            ?>
                            <input id="filter_start"
                                   type="hidden"
                                   value="<?php echo $filter_start->format('Y-m-d'); ?>"/>
                            <input id="filter_end"
                                   type="hidden"
                                   value="<?php echo $filter_end->format('Y-m-d'); ?>"/>

                            <select id="filter_campaign" style="display:none">
                                <option value=""></option>
                            </select>
                            <select id="filter_page" style="display:none">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <?php
                    echo $this->reports->top_pages;
                endif;
                if ($user->authorise('core.manage', 'com_jinbound.contacts')) :
                    echo $this->reports->recent_leads;
                endif;
                ?>
            </div>
        </div>
    </div>
<?php
echo $this->loadTemplate('footer');

echo $this->reports->script;

