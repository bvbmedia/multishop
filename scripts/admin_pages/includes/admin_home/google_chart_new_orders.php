<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$key='google_chart_orders';
$compiledWidget['key']='google_chart';
$compiledWidget['defaultCol']=2;
$compiledWidget['title']=$this->pi_getLL('admin_label_orders');
$dates=array();
$data=array();
$data[]=array(
	$this->pi_getLL('date'),
	$this->pi_getLL('admin_label_orders')
);
for ($i=12; $i>=0; $i--) {
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
foreach ($dates as $key=>$value) {
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$data_query=array();
	$data_query['where'][]='(o.deleted=0)';
	switch ($this->dashboardArray['section']) {
		case 'admin_home':
			break;
		case 'admin_edit_customer':
			if ($this->get['tx_multishop_pi1']['cid'] && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
				$data_query['where'][]='(o.customer_id='.$this->get['tx_multishop_pi1']['cid'].')';
			}
			break;
	}
	// hook
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_home/google_chart_new_orders.php']['googleChartNewOrdersHomeStatsQueryHookPreProc'])) {
		$params=array(
			'data_query'=>&$data_query
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_home/google_chart_new_orders.php']['googleChartNewOrdersHomeStatsQueryHookPreProc'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	$qry=$GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
		'tx_multishop_orders o', // FROM ...
		'('.implode(" AND ", $data_query['where']).') and (o.crdate BETWEEN '.$start_time.' and '.$end_time.')', // WHERE...
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$res=$GLOBALS['TYPO3_DB']->sql_query($qry);
	$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	$data[]=array(
		date("m-Y", $start_time),
		(int)$row['total']
	);
}
$compiledWidget['class']='googleChart-wrapper';
$compiledWidget['content']='
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart'.$compiledWidget['key'].');
      function drawChart'.$compiledWidget['key'].'() {
        var data = google.visualization.arrayToDataTable(
		'.json_encode($data, ENT_NOQUOTES).'
		);
        var options = {
			title: \'\',
			height:100,
			hAxis: {title: \'\', titleTextStyle: {color: \'red\'}},
			vAxis: {title: \''.$this->pi_getLL('admin_label_orders').'\', titleTextStyle: {color: \'red\'}},
			legend: {position: \'none\'},
			chartArea: {
				left: 60,
				top:20,
				width: "98%",
				height: "50"
			}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById(\'chart_div_orders\'));
        chart.draw(data, options);
      }
    </script>
<div id="chart_div_orders"></div>
';
$compiledWidget['additionalHeaderData']['key']='google_chart';
$compiledWidget['additionalHeaderData']['content']='<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
?>