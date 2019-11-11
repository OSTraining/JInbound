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

?>
<script type="text/javascript">
    (function($) {
        window.jinbound_leads_baseurl = '<?php
            echo JRoute::_(
                'index.php?option=com_jinbound&view=contacts&format=json&filter_order=latest&filter_order_Dir=desc',
                false
            );
            ?>';
        window.jinbound_leads_limit = 10;
        window.jinbound_leads_start = 0;
        window.jinbound_pages_baseurl = '<?php
            echo JRoute::_(
                'index.php?option=com_jinbound&view=pages&format=json&filter_order=Page.hits&filter_order_Dir=desc',
                false
            );
            ?>';
        window.jinbound_pages_limit = 10;
        window.jinbound_pages_start = 0;
        window.jinbound_plot_baseurl = '<?php
            echo JRoute::_(
                'index.php?option=com_jinbound&task=reports.plot&format=json',
                false
            );
            ?>';
        window.jinbound_glance_baseurl = '<?php
            echo JRoute::_(
                'index.php?option=com_jinbound&task=reports.glance&format=json',
                false
            );
            ?>';
        var makeButtons = function(callback, pagination, cols) {
            var foot    = $('<tfoot></tfoot>'),
                frow    = $('<tr></tr>'),
                fcol    = $('<td class="btn-group" colspan="' + cols + '"></td>'),
                current = 1,
                range   = 1,
                step    = 5,
                i,
                n,
                p;

            if (pagination.pagesTotal > 1) {
                for (i = 0, n = pagination.pagesTotal; n > i; i++) {
                    p = i + 1;
                    if (p === pagination.pagesCurrent) {
                        current = p;
                    }
                }
            }
            if (current >= step) {
                range = Math.ceil(current / step) + (0 === current % step ? 1 : 0);
            }
            if (pagination.pagesTotal > 1) {
                var prev = $('<button class="btn"><i class="icon-arrow-left"> </i></button>'),
                    next = $('<button class="btn"><i class="icon-arrow-right"> </i></button>'),
                    page = null;
                if (0 !== pagination.limitstart) {
                    prev.click(function(e) {
                        callback(Math.max(0, pagination.limitstart - pagination.limit), pagination.limit);
                        e.preventDefault();

                        return false;
                    });

                } else {
                    prev.attr('disabled', 'disabled');
                }

                if (pagination.total > pagination.limit + pagination.limitstart) {
                    next.click(function(e) {
                        callback(
                            Math.min(pagination.total, pagination.limitstart + pagination.limit), pagination.limit
                        );
                        e.preventDefault();

                        return false;
                    });

                } else {
                    next.attr('disabled', 'disabled');
                }

                fcol.append(prev);
                range = [];
                var hi = range * step,
                    lo = range * step - (step + 1),
                    c  = hi - lo + 1;

                while (c--) range[c] = hi--;
                for (i = 0, n = pagination.pagesTotal; n > i; i++) {
                    var num = 1 + i, txt = num;
                    if (num < pagination.pagesStart || num > pagination.pagesStop) {
                        continue;
                    }
                    if ($.inArray(num, range)
                        && (num % step === 0 || num === range * step - (step + 1))
                        && num !== current
                        && num !== range * step - step
                    ) {
                        txt = '...';
                    }

                    page = $('<button class="btn" data-jinbound-page="' + num + '"></button>').text(txt);
                    if (num === pagination.pagesCurrent) {
                        page.addClass('btn-primary');
                    }

                    page.click(function(e) {
                        var num = parseInt($(this).attr('data-jinbound-page'), 10);
                        callback(Math.max(0, (num * pagination.limit) - pagination.limit), pagination.limit);
                        e.preventDefault();
                        return false;
                    });
                    fcol.append(page);
                }

                fcol.append(next);
            }

            frow.append(fcol);
            foot.append(frow);

            return foot;
        };

        window.fetchLeads = function(start, limit) {
            window.jinbound_leads_limit = limit;
            window.jinbound_leads_start = start;
            var filter = '';
            if (arguments.length > 2) {
                filter += '&filter_start=' + arguments[2];
            }

            if (arguments.length > 3) {
                filter += '&filter_end=' + arguments[3];
            }

            if (arguments.length > 4) {
                filter += '&filter_campaign=' + arguments[4];
            }

            if (arguments.length > 5) {
                filter += '&filter_page=' + arguments[5];
            }

            if (arguments.length > 6) {
                filter += '&filter_priority=' + arguments[6];
            }

            if (arguments.length > 7) {
                filter += '&filter_status=' + arguments[7];
            }

            $.ajax(window.jinbound_leads_baseurl + '&limit=' + limit + '&limitstart=' + start + filter, {
                dataType: 'json',
                success : function(data, textStatus, jqXHR) {
                    var $recentLeads = $('#reports_recent_leads');
                    $recentLeads.empty();

                    var i  = 0,
                        n  = data.items.length,
                        t  = $('<table class="table table-striped"></table>'),
                        h  = $('<thead></thead>'),
                        hr = $('<tr></tr>'),
                        b  = $('<tbody></tbody>');

                    h.append(hr);
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_NAME')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_DATE')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_FORM_CONVERTED_ON')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_LANDING_PAGE_NAME')));
                    t.append(h);

                    if (!n) {
                        b.append($('<tr><td colspan="4">' + Joomla.JText._('COM_JINBOUND_NOT_FOUND') + '</td></tr>'));

                        return;
                    }

                    for (; n > i; i++) {
                        var tr = $('<tr></tr>');
                        if (null === data.items[i].name) {
                            tr.append($('<td></td>').text(' '));

                        } else {
                            tr.append($('<td></td>')
                                .append($('<a href="' + data.items[i].url + '"></a>')
                                    .text(data.items[i].full_name)
                                )
                            );
                        }

                        tr.append($('<td></td>').text(data.items[i].latest || data.items[i].created));
                        if (null === data.items[i].latest_conversion_page_formname) {
                            tr.append($('<td></td>').text(' '));

                        } else {
                            tr.append($('<td></td>')
                                .append($('<a href="' + data.items[i].page_url + '"></a>')
                                    .text(data.items[i].latest_conversion_page_formname)
                                )
                            );
                        }

                        if (null === data.items[i].latest_conversion_page_name) {
                            tr.append($('<td></td>').text(' '));

                        } else {
                            tr.append($('<td></td>')
                                .append($('<a href="' + data.items[i].page_url + '"></a>')
                                    .text(data.items[i].latest_conversion_page_name)
                                )
                            );
                        }

                        b.append(tr);
                    }

                    t.append(b);
                    t.append(makeButtons(window.fetchLeads, data.pagination, 4));
                    $recentLeads.append(t);
                }
            });
        };

        window.fetchPages = function(start, limit) {
            window.jinbound_pages_limit = limit;
            window.jinbound_pages_start = start;

            var filter = '';
            if (arguments.length > 2) {
                filter += '&filter_start=' + arguments[2];
            }

            if (arguments.length > 3) {
                filter += '&filter_end=' + arguments[3];
            }

            if (arguments.length > 4) {
                filter += '&filter_campaign=' + arguments[4];
            }

            if (arguments.length > 5) {
                filter += '&filter_page=' + arguments[5];
            }

            if (arguments.length > 6) {
                filter += '&filter_priority=' + arguments[6];
            }

            if (arguments.length > 7) {
                filter += '&filter_status=' + arguments[7];
            }

            $.ajax(window.jinbound_pages_baseurl + '&limit=' + limit + '&limitstart=' + start + filter, {
                dataType: 'json', success: function(data, textStatus, jqXHR) {
                    var $topPages = $('#reports_top_pages');
                    $topPages.empty();

                    var i  = 0,
                        n  = data.items.length,
                        t  = $('<table class="table table-striped"></table>'),
                        h  = $('<thead></thead>'),
                        hr = $('<tr></tr>'),
                        b  = $('<tbody></tbody>');

                    h.append(hr);
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_LANDING_PAGE_NAME')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_VIEWS')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_SUBMISSIONS')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_LEADS')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_GOAL_COMPLETIONS')));
                    hr.append($('<td></td>').text(Joomla.JText._('COM_JINBOUND_GOAL_COMPLETION_RATE')));
                    t.append(h);

                    if (!n) {
                        b.append($('<tr><td colspan="6">' + Joomla.JText._('COM_JINBOUND_NOT_FOUND') + '</td></tr>'));

                        return;
                    }

                    for (; n > i; i++) {
                        var tr = $('<tr></tr>');
                        if (null === data.items[i].name) {
                            tr.append($('<td></td>').text(' '));

                        } else {
                            tr.append($('<td></td>')
                                .append($('<a href="' + data.items[i].url + '"></a>')
                                    .text(data.items[i].name)
                                )
                            );
                        }

                        tr.append($('<td></td>').text(parseInt(data.items[i].hits, 10)));
                        tr.append($('<td></td>').text(parseInt(data.items[i].submissions, 10)));
                        tr.append($('<td></td>').text(parseInt(data.items[i].contact_submissions, 10)));
                        tr.append($('<td></td>').text(parseInt(data.items[i].conversions, 10)));
                        tr.append($('<td></td>').text(data.items[i].conversion_rate + ' %'));
                        b.append(tr);
                    }

                    t.append(b);
                    t.append(makeButtons(window.fetchPages, data.pagination, 6));
                    $topPages.append(t);
                }
            });
        };

        window.fetchPlots = function() {
            var filter = '';
            if (arguments.length > 0) {
                filter += '&filter_start=' + arguments[0];
            }

            if (arguments.length > 1) {
                filter += '&filter_end=' + arguments[1];
            }

            if (arguments.length > 2) {
                filter += '&filter_campaign=' + arguments[2];
            }

            if (arguments.length > 3) {
                filter += '&filter_page=' + arguments[3];
            }

            if (arguments.length > 4) {
                filter += '&filter_priority=' + arguments[4];
            }

            if (arguments.length > 5) {
                filter += '&filter_status=' + arguments[5];
            }

            $.ajax(window.jinbound_plot_baseurl + filter, {
                dataType: 'json',
                success : function(data, textStatus, jqXHR) {
                    if (!(data && data.hits)) {
                        alert(Joomla.JText._('COM_JINBOUND_ERROR_LOADING_PLOT_DATA'));
                        return;
                    }

                    var i = 0, n = data.hits.length, max = 0, v, x, y, opts;
                    for (; n > i; i++) {
                        v = parseInt(data.hits[i][1], 10);
                        max = max > v ? max : v;
                    }
                    for (i = 0, n = data.leads.length; n > i; i++) {
                        v = parseInt(data.leads[i][1], 10);
                        max = max > v ? max : v;
                    }
                    for (i = 0, n = data.conversions.length; n > i; i++) {
                        v = parseInt(data.conversions[i][1], 10);
                        max = max > v ? max : v;
                    }

                    max = max + (0 < max % 5 ? (5 - (max % 5)) : 5);
                    y = {
                        min: 0,
                        max: max
                    };
                    x = {
                        renderer    : $.jqplot.DateAxisRenderer,
                        tickInterval: data.tick ? data.tick : '1 day',
                        tickOptions : {
                            angle: -30
                        }
                    };

                    opts = {
                        animate      : true,
                        animateReplot: true,
                        series       : [
                            {
                                label: Joomla.JText._('COM_JINBOUND_VIEWS')
                            },
                            {
                                label: Joomla.JText._('COM_JINBOUND_LEADS')
                            },
                            {
                                label: Joomla.JText._('COM_JINBOUND_CONVERSIONS')
                            }
                        ],
                        legend       : {
                            show: true
                        },
                        axesDefaults : {
                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                        },
                        axes         : {
                            xaxis: x,
                            yaxis: y
                        }
                    };

                    try {
                        window.reportsPlot.destroy();

                    } catch (e) {
                    }

                    window.reportsPlot = $.jqplot(
                        'jinbound-reports-graph',
                        [data.hits, data.leads,
                            data.conversions],
                        opts
                    );
                }
            });
        };

        window.fetchGlance = function() {
            var filter = '';
            if (arguments.length > 0) {
                filter += '&filter_start=' + arguments[0];
            }

            if (arguments.length > 1) {
                filter += '&filter_end=' + arguments[1];
            }

            if (arguments.length > 2) {
                filter += '&filter_campaign=' + arguments[2];
            }

            if (arguments.length > 3) {
                filter += '&filter_page=' + arguments[3];
            }

            if (arguments.length > 4) {
                filter += '&filter_priority=' + arguments[4];
            }

            if (arguments.length > 5) {
                filter += '&filter_status=' + arguments[5];
            }

            $.ajax(window.jinbound_glance_baseurl + filter, {
                dataType: 'json',
                success : function(data, textStatus, jqXHR) {
                    if (!data) {
                        alert(Joomla.JText._('COM_JINBOUND_ERROR_LOADING_PLOT_DATA'));
                        return;
                    }

                    var a = ['views', 'leads', 'views-to-leads', 'conversion-count', 'conversion-rate'];

                    for (var i = 0; a.length > i; i++) {
                        $('#jinbound-reports-glance-' + a[i]).text(data[a[i]]);
                    }
                }
            });
        };

        window.fetchReports = function(a, b, c, d, e, f, g, h) {
            window.fetchLeads(a, b, c, d, e, f, g, h);
            window.fetchPages(a, b, c, d, e, f, g, h);

            if (document.getElementById('jinbound-reports-glance')) {
                window.fetchGlance(c, d, e, f, g, h);
            }

            if (document.getElementById('jinbound-reports-graph')) {
                window.fetchPlots(c, d, e, f, g, h);
            }
        };

        var start          = $('#filter_start'),
            end            = $('#filter_end'),
            campaign       = $('#filter_campaign'),
            page           = $('#filter_page'),
            priority       = $('#filter_priority'),
            status         = $('#filter_status'),
            start_date     = '',
            end_date       = '',
            campaign_value = '',
            page_value     = '',
            priority_value = '',
            status_value   = '';

        if (start.length && end.length) {
            start_date = start.val();
            end_date = end.val();
        }
        if (campaign.length) {
            campaign_value = campaign.find(':selected').val();
        }
        if (page.length) {
            page_value = page.find(':selected').val();
        }
        if (priority.length) {
            priority_value = priority.find(':selected').val();
        }
        if (status.length) {
            status_value = status.find(':selected').val();
        }

        window.fetchReports(0, 10, start_date, end_date, campaign_value, page_value, priority_value, status_value);
    })(jQuery);
</script>
