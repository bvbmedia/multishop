<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$compiledWidget['key'] = 'profitThisMonthLastMonth';
$compiledWidget['defaultCol'] = 1;
$compiledWidget['title'] = $this->pi_getLL('profitThisMonthLastMonth', 'Bi-Monthly profit');
$current_datetime=date('Y-m-d 00:00:00');
$current_month_turnover=array();
$current_month_date_tag=array();
// current month date range definition
$current_month_number=date('m');
$current_month_year=date('Y');
$current_month_date=array();
$current_month_date['start'] = date('Y-m-d', strtotime($current_datetime . ' -1 month'));
$current_month_date['end'] = date('Y-m-d', strtotime($current_datetime));
// current month turnover
$start_time = $current_month_date['start'];
$end_time = $current_month_date['end'];
$data_query['where'] = array();
$data_query['where'][] = '(o.deleted=0)';
$data_query['where'][] = '(o.crdate BETWEEN ' . strtotime($start_time) . ' and ' . strtotime($end_time) . ')';
//$data_query['where'][] = '(o.paid=1 or o.paid=0)';
$str = $GLOBALS['TYPO3_DB']->SELECTquery('o.crdate, o.orders_id, op.products_price, op.final_price, op.product_capital_price', // SELECT ...
    'tx_multishop_orders o, tx_multishop_orders_products op', // FROM ...
    '(' . implode(" AND ", $data_query['where']) . ') and o.orders_id=op.orders_id', // WHERE...
    '', // GROUP BY...
    'o.crdate asc', // ORDER BY...
    '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $current_month_date_tag[date('j', $row['crdate'])]=$row['crdate'];
        $profit=($row['final_price']-$row['product_capital_price']);
        if ($profit>=0) {
            $current_month_turnover[date('Ymd', $row['crdate'])] += $profit;
        }
    }
}
// current month dates range list to fill up blank date
$current = strtotime($current_month_date['start']);
$last = strtotime($current_month_date['end']);
$current_month_period=array();
while( $current <= $last ) {
    $current_month_period[] = date('Ymd', $current);
    $current = strtotime('+1 day', $current);
}
foreach ($current_month_period as $current_month_date_range) {
    if (!isset($current_month_turnover[$current_month_date_range])) {
        $strtotime=strtotime($current_month_date_range);
        $current_month_date_tag[date('j', $strtotime)]=$strtotime;
        $current_month_turnover[date('Ymd', $strtotime)] =0;
    }
}
ksort($current_month_turnover);
// last month date range definition
$last_month_turnover=array();
$last_month_number=date('m')-1;
$last_month_year=date('Y');
if (date('n')=='1') {
    $last_month_number=12;
    $last_month_year=date('Y')-1;
}
$last_month_date=array();
$last_month_date['start'] = date('Y-m-d', strtotime($current_month_date['start'] . ' -1 month'));
$last_month_date['end'] = date('Y-m-d', strtotime($last_month_date['start'] . ' +30 days '));

//$last_month_date['start'] = date($last_month_year . '-'.$last_month_number.'-01');
//$last_month_date['end'] = date($last_month_year . '-'.$last_month_number.'-' . date('d', strtotime($last_month_date['start'] . ' +' . count($current_month_period) . ' days')));
// last month turnover
$start_time = $last_month_date['start'];
$end_time = $last_month_date['end'];
$data_query['where'] = array();
$data_query['where'][] = '(o.deleted=0)';
$data_query['where'][] = '(o.crdate BETWEEN ' . strtotime($start_time) . ' and ' . strtotime($end_time) . ')';
$data_query['where'][] = '(o.paid=1 or o.paid=0)';
$str = $GLOBALS['TYPO3_DB']->SELECTquery('o.crdate, o.orders_id, op.products_price, op.final_price, op.product_capital_price', // SELECT ...
    'tx_multishop_orders o, tx_multishop_orders_products op', // FROM ...
    '(' . implode(" AND ", $data_query['where']) . ') and o.orders_id=op.orders_id', // WHERE...
    '', // GROUP BY...
    'o.crdate asc', // ORDER BY...
    '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        if ($row['crdate']>0) {
            $profit=($row['final_price']-$row['product_capital_price']);
            if ($profit>=0) {
                $last_month_turnover[date('Ymd', $row['crdate'])] += $profit;
            }
        }
    }
}
// current month dates range list to fill up blank date
$current = strtotime($last_month_date['start']);
$last = strtotime($last_month_date['end']);
$last_month_period=array();
while( $current <= $last ) {
    $last_month_period[] = date('Ymd', $current);
    $current = strtotime('+1 day', $current);
}
foreach ($last_month_period as $last_month_date_range) {
    $strtotime=strtotime($last_month_date_range);
    //if ($strtotime>0) {
        $day_nr=date('j', $strtotime);
        if (!isset($last_month_turnover[$last_month_date_range])) {
            $strtotime=$current_month_date_tag[$day_nr];
            if ($strtotime>0) {
                $last_month_turnover[date('Ymd', $strtotime)] = 0;
            }
        } else {
            $strtotime=$current_month_date_tag[$day_nr];
            if ($strtotime>0) {
                $last_month_turnover[date('Ymd', $strtotime)] = $last_month_turnover[$last_month_date_range];
                unset($last_month_turnover[$last_month_date_range]);
            }
        }
    //}
}
ksort($last_month_turnover);
// jqplot definition
$jqplot_array=array();
if (is_array($current_month_turnover) && count($current_month_turnover)) {
    foreach ($current_month_turnover as $current_turnover_date => $current_turnover_value) {
        $jqplot_array['current_month_turnover'][] = '[\''.date('Y-m-d', strtotime($current_turnover_date)).'\', '.round($current_turnover_value, 2).']';
    }
}
if (is_array($last_month_turnover) && count($last_month_turnover)) {
    foreach ($last_month_turnover as $last_turnover_date => $last_turnover_value) {
        $jqplot_array['last_month_turnover'][] = '[\''.date('Y-m-d', strtotime($last_turnover_date)).'\', '.round($last_turnover_value, 2).']';
    }
}
$compiledWidget['content']= '
<script type="text/javascript">
$(document).ready(function () {
    $.jqplot._noToImageButton = true;
    var prevMonth = ['.implode(', ', $jqplot_array['last_month_turnover']).'];
    var currMonth = ['.implode(', ', $jqplot_array['current_month_turnover']).'];
    var plot1 = $.jqplot("chartProfitThisMonthLastMonth", [prevMonth, currMonth], {
        seriesColors: ["rgba(78, 135, 194, 0.7)", "rgb(211, 235, 59)"],
        title: \''.$compiledWidget['title'].'\',
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
                label: \''.$this->pi_getLL('last_month', 'Last month').'\'
            },
            {
                label: \''.$this->pi_getLL('current_month', 'Current month').'\'
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
                min: "'.$current_month_date['start'].'",
                max: "'.$current_month_date['end'].'",
                tickInterval: "2 days",
                drawMajorGridlines: false
            },
            yaxis: {
                renderer: $.jqplot.LogAxisRenderer,
                pad: 0,
                rendererOptions: {
                    minorTicks: 1
                },
                tickOptions: {
                    formatString: "&euro;%\'d",
                    showMark: false
                }
            }
        }
    });
    $(\'.jqplot-highlighter-tooltip\').addClass(\'ui-corner-all\')
});
</script>
<div id="chartProfitThisMonthLastMonth"></div>';
?>