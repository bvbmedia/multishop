<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$compiledWidget['key'] = 'turnoverMainCategoryThisMonthLastMonth';
$compiledWidget['defaultCol'] = 1;
$compiledWidget['title'] = $this->pi_getLL('turnoverMainCategoryThisMonthLastMonth', 'Monthly turnover per-main category');
$current_datetime=date('Y-m-d 00:00:00');
$current_month_turnover=array();
$current_month_date_tag=array();
// current month date range definition
$current_month_number=date('m');
$current_month_year=date('Y');
$current_month_date=array();
$current_month_date['start'] = date('Y-m-d 00:00:00', strtotime($current_datetime . ' -1 month'));
$current_month_date['end'] = date('Y-m-d 23:59:59', strtotime($current_datetime));
// current month turnover
$start_time = $current_month_date['start'];
$end_time = $current_month_date['end'];
$data_query['where'] = array();
$data_query['where'][] = '(o.deleted=0)';
$data_query['where'][] = '(o.crdate BETWEEN ' . strtotime($start_time) . ' and ' . strtotime($end_time) . ')';
//$data_query['where'][] = '(o.paid=1 or o.paid=0)';
$str = $GLOBALS['TYPO3_DB']->SELECTquery('o.crdate, o.orders_id, op.categories_name_0, op.final_price', // SELECT ...
        'tx_multishop_orders o, tx_multishop_orders_products op', // FROM ...
        '(' . implode(" AND ", $data_query['where']) . ') and o.orders_id=op.orders_id', // WHERE...
        '', // GROUP BY...
        'op.categories_name_0 asc', // ORDER BY...
        '' // LIMIT ...
);
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$grand_total=0;
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        //$current_month_date_tag[date('j', $row['crdate'])]=$row['crdate'];
        if ($row['final_price']>=0) {
            $grand_total += $row['final_price'];
            $current_month_turnover[$row['categories_name_0']] += $row['final_price'];
        }
    }
}
foreach ($current_month_turnover as $maincat => $maincat_total) {
    $maincat_percentage=($maincat_total/$grand_total) * 100;
    $current_month_turnover[$maincat] = $maincat_percentage;
}
// current month dates range list to fill up blank date
/*
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
*/
// last month date range definition
/*
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
// last month turnover
$start_time = $last_month_date['start'];
$end_time = $last_month_date['end'];
$data_query['where'] = array();
$data_query['where'][] = '(o.deleted=0)';
$data_query['where'][] = '(o.crdate BETWEEN ' . strtotime($start_time) . ' and ' . strtotime($end_time) . ')';
$data_query['where'][] = '(o.paid=1 or o.paid=0)';
$str = $GLOBALS['TYPO3_DB']->SELECTquery('o.crdate, o.orders_id, op.categories_name_0, sum(op.final_price) as maincat_total', // SELECT ...
        'tx_multishop_orders o, tx_multishop_orders_products op', // FROM ...
        '(' . implode(" AND ", $data_query['where']) . ') and o.orders_id=op.orders_id', // WHERE...
        'op.categories_id_0', // GROUP BY...
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
*/
// jqplot definition
$jqplot_array=array();
if (is_array($current_month_turnover) && count($current_month_turnover)) {
    foreach ($current_month_turnover as $current_maincat => $current_turnover_value) {
        $jqplot_array['current_month_turnover'][] = '[\''.addslashes(htmlspecialchars($current_maincat)).'\', '.round($current_turnover_value, 2).']';
    }
}
/*
if (is_array($last_month_turnover) && count($last_month_turnover)) {
    foreach ($last_month_turnover as $last_turnover_date => $last_turnover_value) {
        $jqplot_array['last_month_turnover'][] = '[\''.date('Y-m-d', strtotime($last_turnover_date)).'\', '.round($last_turnover_value, 2).']';
    }
}
*/
$compiledWidget['content']= '
<script type="text/javascript">
$(document).ready(function(){
  var data = [
   '.implode(',', $jqplot_array['current_month_turnover']).'
  ];
  var plot1 = jQuery.jqplot (\'chartturnoverMainCategoryThisMonthLastMonth\', [data], 
    { 
      grid: {
            drawBorder: false, 
            drawGridlines: false,
            background: \'#ffffff\',
            shadow:false
      },
      gridPadding: {top:0, bottom:0, left:0, right:0},
      height: 225,
      width: 225,
      seriesDefaults: {
        // Make this a pie chart.
        renderer: jQuery.jqplot.PieRenderer, 
        rendererOptions: {
          // Put data labels on the pie slices.
          // By default, labels show the percentage of the slice.
          showDataLabels: true
        },
      }, 
      legend: { show:false, location: \'e\' }
    }
  );
  $(\'#chartturnoverMainCategoryThisMonthLastMonth\').bind(\'jqplotDataHighlight\', function (ev, seriesIndex, pointIndex, data) { 
        document.getElementById(\'chartturnoverMainCategoryThisMonthLastMonth\').title = data;
        //alert(\'series: \'+seriesIndex+\', point: \'+pointIndex+\', data: \'+data);
  });
});
</script>
<div id="chartturnoverMainCategoryThisMonthLastMonth"></div>';
?>