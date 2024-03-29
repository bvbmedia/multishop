<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$compiledWidget['key'] = 'turnoverThisWeekLastWeek';
$compiledWidget['defaultCol'] = 1;
$compiledWidget['title'] = $this->pi_getLL('turnoverThisWeekLastWeek', 'Bi-Weekly turnover');
$current_datetime = date('Y-m-d 00:00:00');
// current week date range definition
$current_week_turnover = array();
$current_week_date_tag = array();
$current_week_date['start'] = date('Y-m-d 00:00:00', strtotime($current_datetime . ' -6 days'));
$current_week_date['end'] = date('Y-m-d 23:59:59', strtotime($current_datetime));
// current week turnover
$start_time = $current_week_date['start'];
$end_time = $current_week_date['end'];
$data_query['where'] = array();
$data_query['where'][] = '(o.deleted=0)';
$data_query['where'][] = '(o.crdate BETWEEN ' . strtotime($start_time) . ' and ' . strtotime($end_time) . ')';
//$data_query['where'][] = '(o.paid=1 or o.paid=0)';
$str = $GLOBALS['TYPO3_DB']->SELECTquery('o.crdate, o.orders_id, o.grand_total', // SELECT ...
        'tx_multishop_orders o', // FROM ...
        '(' . implode(" AND ", $data_query['where']) . ')', // WHERE...
        '', // GROUP BY...
        'o.crdate asc', // ORDER BY...
        '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $day_nr = date('w', $row['crdate']);
        if ($day_nr == '0') {
            $day_nr = 7;
        }
        $current_week_date_tag[$day_nr] = $row['crdate'];
        $current_week_turnover[date('Ymd', $row['crdate'])] += $row['grand_total'];
    }
}
// current week dates range list to fill up blank date
$current = strtotime($current_week_date['start']);
$last = strtotime($current_week_date['end']);
$current_week_period = array();
while ($current <= $last) {
    $current_week_period[] = date('Ymd', $current);
    $current = strtotime('+1 day', $current);
}
foreach ($current_week_period as $current_week_date_range) {
    if (!isset($current_week_turnover[$current_week_date_range])) {
        $strtotime = strtotime($current_week_date_range);
        $day_nr = date('w', $strtotime);
        if ($day_nr == '0') {
            $day_nr = 7;
        }
        $current_week_date_tag[$day_nr] = $strtotime;
        $current_week_turnover[date('Ymd', $strtotime)] = 0;
    }
}
ksort($current_week_turnover);
// last week date range definition
$last_week_date_tag = array();
$last_week_turnover = array();
$last_week_date['start'] = date('Y-m-d 00:00:00', strtotime($current_week_date['start'] . ' -7 days'));
$last_week_date['end'] = date('Y-m-d 23:59:59', strtotime($last_week_date['start'] . ' +6 days '));
// last week turnover
$start_time = $last_week_date['start'];
$end_time = $last_week_date['end'];
$data_query['where'] = array();
$data_query['where'][] = '(o.deleted=0)';
$data_query['where'][] = '(o.crdate BETWEEN ' . strtotime($start_time) . ' and ' . strtotime($end_time) . ')';
$data_query['where'][] = '(o.paid=1 or o.paid=0)';
$str = $GLOBALS['TYPO3_DB']->SELECTquery('o.crdate, o.orders_id, o.grand_total', // SELECT ...
        'tx_multishop_orders o', // FROM ...
        '(' . implode(" AND ", $data_query['where']) . ')', // WHERE...
        '', // GROUP BY...
        'o.crdate asc', // ORDER BY...
        '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $day_nr = date('w', $row['crdate']);
        if ($day_nr == '0') {
            $day_nr = 7;
        }
        //$row['crdate']=$current_week_date_tag[$day_nr];
        $last_week_turnover[date('Ymd', $row['crdate'])] += $row['grand_total'];
    }
}
// last week dates range list to fill up blank date
$current = strtotime($last_week_date['start']);
$last = strtotime($last_week_date['end']);
$last_week_period = array();
while ($current <= $last) {
    $last_week_period[] = date('Ymd', $current);
    $current = strtotime('+1 day', $current);
}
foreach ($last_week_period as $last_week_date_range) {
    $strtotime = strtotime($last_week_date_range);
    $day_nr = date('w', $strtotime);
    if ($day_nr == '0') {
        $day_nr = 7;
    }
    if (!isset($last_week_turnover[$last_week_date_range])) {
        $strtotime = $current_week_date_tag[$day_nr];
        $last_week_turnover[date('Ymd', $strtotime)] = 0;
    } else {
        $strtotime = $current_week_date_tag[$day_nr];
        $last_week_turnover[date('Ymd', $strtotime)] = $last_week_turnover[$last_week_date_range];
        unset($last_week_turnover[$last_week_date_range]);
    }
}
ksort($last_week_turnover);
// jqplot definition
$jqplot_array = array();
if (is_array($last_week_turnover) && count($last_week_turnover)) {
    foreach ($last_week_turnover as $last_turnover_date => $last_turnover_value) {
        $jqplot_array['last_week_turnover'][] = '[\'' . date('Y-m-d', strtotime($last_turnover_date)) . '\', ' . round($last_turnover_value, 2) . ']';
    }
}
if (is_array($current_week_turnover) && count($current_week_turnover)) {
    foreach ($current_week_turnover as $current_turnover_date => $current_turnover_value) {
        $jqplot_array['current_week_turnover'][] = '[\'' . date('Y-m-d', strtotime($current_turnover_date)) . '\', ' . round($current_turnover_value, 2) . ']';
    }
}
$compiledWidget['content'] = '
<script type="text/javascript">
function chartTurnoverThisWeekLastWeekFormatTick(format, value) {
    var value = \'&euro;\' + jQuery.number(value, 2, \'' . $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'] . '\', \'' . $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_thousands_point'] . '\')
    return value;
}
$(document).ready(function () {
    $.jqplot._noToImageButton = true;
    var prevWeek = [' . implode(', ', $jqplot_array['last_week_turnover']) . '];
    var currWeek = [' . implode(', ', $jqplot_array['current_week_turnover']) . '];
    var plot1 = $.jqplot("chartTurnoverThisWeekLastWeek", [prevWeek, currWeek], {
        seriesColors: ["rgba(78, 135, 194, 0.7)", "rgb(211, 235, 59)"],
        title: \'' . $compiledWidget['title'] . '\',
        highlighter: {
            show: true,
            sizeAdjust: 1,
            tooltipOffset: 9
        },
        grid: {
            background: \'rgba(57,57,57,0.0)\',
            drawBorder: false,
            shadow: false,
            gridLineColor: \'#666666\',
            gridLineWidth: 2
        },
        legend: {
            show: true,
            placement: \'inside\'
        },
        seriesDefaults: {
            rendererOptions: {
                smooth: true,
                animation: {
                    show: true
                }
            },
            showMarker: false
        },
        series: [
            {
                fill: true,
                label: \'' . $this->pi_getLL('last_week', 'Last week') . '\'
            },
            {
                label: \'' . $this->pi_getLL('current_week', 'Current week') . '\'
            }
        ],
        axesDefaults: {
            rendererOptions: {
                baselineWidth: 1.5,
                baselineColor: \'#444444\',
                drawBaseline: false
            }
        },
        axes: {
            xaxis: {
                renderer: $.jqplot.DateAxisRenderer,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                    formatString: "%b %e",
                    angle: -30,
                    textColor: \'#dddddd\'
                },
                min: "' . $current_week_date['start'] . '",
                max: "' . $current_week_date['end'] . '",
                tickInterval: "1 day",
                drawMajorGridlines: false
            },
            yaxis: {
                renderer: $.jqplot.LogAxisRenderer,
                pad: 0,
                rendererOptions: {
                    minorTicks: 1
                },
                tickOptions: {
                    tickOptions: { formatString: "&euro;%\'d" },
                    formatter: function(format, value){
                        return chartTurnoverThisWeekLastWeekFormatTick(format, value);
                    },
                    showMark: false
                }
            }
        }
    });
 
    $(\'.jqplot-highlighter-tooltip\').addClass(\'ui-corner-all\')
});
</script>
<div id="chartTurnoverThisWeekLastWeek"></div>';
