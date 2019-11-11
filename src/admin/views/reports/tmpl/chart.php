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


JText::script('COM_JINBOUND_NAME');
JText::script('COM_JINBOUND_DATE');
JText::script('COM_JINBOUND_FORM_CONVERTED_ON');
JText::script('COM_JINBOUND_LANDING_PAGE_NAME');
JText::script('COM_JINBOUND_LEADS');
JText::script('COM_JINBOUND_SUBMISSIONS');
JText::script('COM_JINBOUND_VIEWS');
JText::script('COM_JINBOUND_CONVERSIONS');
JText::script('COM_JINBOUND_CONVERSION_RATE');
JText::script('COM_JINBOUND_ERROR_LOADING_PLOT_DATA');
JText::script('COM_JINBOUND_NOT_FOUND');
JText::script('COM_JINBOUND_GOAL_COMPLETIONS');
JText::script('COM_JINBOUND_GOAL_COMPLETION_RATE');

JText::script('COM_JINBOUND_REPORTS');
JText::script('COM_JINBOUND_REPORTS_TITLE_CONVERSIONCOUNT');
JText::script('COM_JINBOUND_REPORTS_TITLE_CONVERSIONRATE');
JText::script('COM_JINBOUND_REPORTS_TITLE_LEADS');
JText::script('COM_JINBOUND_REPORTS_TITLE_VIEWS');
JText::script('COM_JINBOUND_REPORTS_TITLE_VIEWSTOLEADS');

$chart_options = array(
    (object)array('text' => JText::_('COM_JINBOUND_VIEWS'), 'value' => 'views')
,
    (object)array('text' => JText::_('COM_JINBOUND_LEADS'), 'value' => 'leads')
,
    (object)array('text' => JText::_('COM_JINBOUND_VIEWS_TO_LEADS'), 'value' => 'viewstoleads')
,
    (object)array('text' => JText::_('COM_JINBOUND_CONVERSIONS'), 'value' => 'conversioncount')
,
    (object)array('text' => JText::_('COM_JINBOUND_CONVERSION_RATE'), 'value' => 'conversionrate')
);

?>
<div class="container-fluid <?php echo $this->viewClass; ?>" id="jinbound_component">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
            <?php endif; ?>
            <form action="<?php echo JInboundHelperUrl::view('reports', false, array('layout' => 'chart')); ?>"
                  method="post" id="adminForm" name="adminForm" class="form-validate" enctype="multipart/form-data">
                <div class="row-fluid">
                    <div class="span12 text-center">
                        <div class="reports_search">
                            <?php echo JHtml::_('select.genericlist', $chart_options, 'filter_chart', array(
                                'list.attr'   => array(
                                    'onchange' => $this->filter_change_code
                                ),
                                'list.select' => $this->state->get('filter.chart')
                            )); ?>
                            <?php echo $this->campaign_filter; ?>
                            <?php echo $this->page_filter; ?>
                            <?php echo JHtml::_('calendar', $this->state->get('filter.start'), 'filter_start',
                                'filter_start', '%Y-%m-%d', array(
                                    'size'        => 10
                                ,
                                    'placeholder' => JText::_('COM_JINBOUND_FROM')
                                ,
                                    'onchange'    => $this->filter_change_code
                                )); ?>
                            <?php echo JHtml::_('calendar', $this->state->get('filter.end'), 'filter_end', 'filter_end',
                                '%Y-%m-%d', array(
                                    'size'        => 10
                                ,
                                    'placeholder' => JText::_('COM_JINBOUND_TO')
                                ,
                                    'onchange'    => $this->filter_change_code
                                )); ?>
                        </div>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span12">
                        <div class="well">
                            <div id="jinbound-reports-graph" style="width:100%;height:300px"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        (function($) {
            window.jinbound_chart_type = '<?php echo $this->escape($this->state->get('filter_chart', 'views')); ?>';
            window.jinbound_chart_filters = ['start', 'end', 'campaign', 'page', 'priority', 'status'];
            window.jinbound_chart_baseurl = '<?php
                echo JRoute::_('index.php?option=com_jinbound&task=reports.plot&format=json', false);
                ?>';
            window.jinbound_history_baseurl = '<?php
                echo JRoute::_('index.php?option=com_jinbound&view=reports&layout=chart', false);
                ?>';
            window.jinbound_last_filter = false;
            window.changeTitle = function(title) {
                $('h1.page-title, .pagetitle h2').each(function(idx, el) {
                    $(el).text(title);
                });
            };
            window.fetchReports = function() {
                var filter = '', args = arguments;
                $.each(window.jinbound_chart_filters, function(idx, el) {
                    var key = idx + 2;
                    if (args.length > key) {
                        filter += '&filter_' + el + '=' + ('undefined' === typeof args[key] ? '' : args[key]);
                    }
                });
                $.ajax(window.jinbound_chart_baseurl + filter, {
                    dataType : 'json'
                    , success: function(data, textStatus, jqXHR) {
                        var chart, charts, c, g, b, i, n, max, v, x, y, d, s, opts;
                        if (!(data && data.hits)) {
                            alert(Joomla.JText._('COM_JINBOUND_ERROR_LOADING_PLOT_DATA'));
                            return;
                        }
                        chart = $('#filter_chart').find(':selected').val();
                        charts = {
                            views            : {
                                name : 'hits', pct: false, label: 'COM_JINBOUND_VIEWS',
                                title: 'COM_JINBOUND_REPORTS_TITLE_VIEWS'
                            }
                            , leads          : {
                                name : 'leads', pct: false, label: 'COM_JINBOUND_LEADS',
                                title: 'COM_JINBOUND_REPORTS_TITLE_LEADS'
                            }
                            , viewstoleads   : {
                                dem     : 'hits', num: 'leads', pct: true,
                                demlabel: 'COM_JINBOUND_VIEWS', numlabel: 'COM_JINBOUND_LEADS',
                                title   : 'COM_JINBOUND_REPORTS_TITLE_VIEWSTOLEADS'
                            }
                            , conversionrate : {
                                dem     : 'leads', num: 'conversions', pct: true,
                                demlabel: 'COM_JINBOUND_LEADS', numlabel: 'COM_JINBOUND_CONVERSIONS',
                                title   : 'COM_JINBOUND_REPORTS_TITLE_CONVERSIONRATE'
                            }
                            , conversioncount: {
                                name : 'conversions', pct: false, label: 'COM_JINBOUND_CONVERSIONS',
                                title: 'COM_JINBOUND_REPORTS_TITLE_CONVERSIONCOUNT'
                            }
                        };
                        if ('undefined' == typeof charts[chart]) {
                            alert(Joomla.JText._('COM_JINBOUND_ERROR_LOADING_PLOT_DATA'));
                            return;
                        }
                        c = charts[chart];
                        if (window.jinbound_chart_type != chart || filter != window.jinbound_last_filter) {
                            window.jinbound_chart_type = chart;
                            window.jinbound_last_filter = filter;
                            window.history.pushState({}, '', window.jinbound_history_baseurl + filter + '&filter_chart=' + chart);
                        }
                        window.changeTitle(Joomla.JText._(c.title));
                        if (c.pct) {
                            d = [data[c.dem], data[c.num]];
                            s = [
                                {label: Joomla.JText._(c.demlabel)}
                                , {label: Joomla.JText._(c.numlabel)}
                            ]
                        }
                        else {
                            d = [data[c.name]];
                            s = [
                                {label: Joomla.JText._(c.label)}
                            ]
                        }
                        for (i = 0, n = d[0].length, max = 0; n > i; i++) {
                            v = parseInt(d[0][i][1], 10);
                            max = max > v ? max : v;
                        }
                        max = max + (0 < max % 5 ? (5 - (max % 5)) : 5);
                        g = $('#jinbound-reports-graph');
                        b = (g.width() - 100) / n;
                        b = b > 5 ? b : 5;
                        y = {
                            min          : 0
                            , max        : max
                            , tickOptions: {
                                formatString: '%d'
                            }
                        };
                        x = {
                            renderer      : $.jqplot.DateAxisRenderer
                            , tickInterval: data.tick ? data.tick : '1 day'
                            , tickOptions : {
                                angle: -30
                            }
                        };
                        opts = {
                            animate         : true
                            , animateReplot : true
                            , series        : s
                            , seriesDefaults: {
                                renderer       : $.jqplot.BarRenderer,
                                rendererOptions: {barWidth: b}
                            }
                            , legend        : {
                                show: true
                            }
                            , axesDefaults  : {
                                tickRenderer: $.jqplot.CanvasAxisTickRenderer
                            }
                            , axes          : {
                                xaxis  : x
                                , yaxis: y
                            }
                            , highlighter   : {
                                show      : true,
                                sizeAdjust: 7.5
                            },
                            cursor          : {
                                show: false
                            }
                        };
                        try {
                            window.reportsChart.destroy();
                        }
                        catch (e) {
                        }
                        g.empty();
                        window.reportsChart = $.jqplot('jinbound-reports-graph', d, opts);
                    }
                });
            };

            var args = [];
            $.each(window.jinbound_chart_filters, function(idx, filter) {
                var el = $('#filter_' + filter), val = '';
                if (el.length) {
                    if ('start' == filter || 'end' == filter) {
                        val = el.val();
                    }
                    else {
                        val = el.find(':selected').val();
                    }
                }
                args[idx] = val;
            });
            window.fetchReports(0, 10, args[0], args[1], args[2], args[3], args[4], args[5]);
        })(jQuery);
    </script>
