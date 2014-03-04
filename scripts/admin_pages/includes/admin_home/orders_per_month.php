<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');


$sql_year 		= "select crdate from tx_multishop_orders where deleted=0 order by uid asc limit 1";
$qry_year 		= $GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year 		= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);

if ($row_year['crdate'] > 0) {
	$oldest_year 	= date("Y", $row_year['crdate']);
} else {
	$oldest_year 	= date("Y");
}
$current_year 	= date("Y");


$dates=array();
//$libaryWidgets['ordersPerMonth']['content'].='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h2>';
for ($i=3;$i>=0;$i--) {
	//$time=strtotime("-".$i." month");
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
	
//	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y",$time)]=date("Y-m", $time);
}
$libaryWidgets['ordersPerMonth']['content'].='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" >';
$libaryWidgets['ordersPerMonth']['content'].='<tr class="odd">';
foreach ($dates as $key => $value) {
	$libaryWidgets['ordersPerMonth']['content'].='<td align="right">'.ucfirst($key).'</td>';
}
$libaryWidgets['ordersPerMonth']['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
$libaryWidgets['ordersPerMonth']['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('cumulative')).'</td>';
$libaryWidgets['ordersPerMonth']['content'].='</tr>';
$libaryWidgets['ordersPerMonth']['content'].='<tr class="even">';
$total_amount=0;
foreach ($dates as $key => $value) {
	$total_orders=0;
	$start_time	= strtotime($value."-01 00:00:00");
	$end_time	= strtotime($value."-31 23:59:59");
	$where=array();	
	$where[]='(f.deleted=0)';
	
	$str="SELECT count(1) as total from tx_multishop_orders f WHERE (".implode(" AND ",$where).") and (f.crdate BETWEEN ".$start_time." and ".$end_time.")";

	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$total_orders=($total_orders+$row['total']);
	}
	$libaryWidgets['ordersPerMonth']['content'].='<td align="right">'.number_format($total_orders,0,3,'.').'</td>';
	$total_amount=$total_amount+$total_orders;
}
if ($this->cookie['stats_year_sb'] == date("Y") || !$this->cookie['stats_year_sb']) {
	$month=date("m");
	$currentDay=date("d");
	$dayOfTheYear=date("z");
	$currentYear=1;
	if ($month==1) {
		$currentMonth=1;
	}
} else {
	$month=12;
	$dayOfTheYear=365;
	$currentDay=31;
	$currentYear=0;
	$currentMonth=0;
}
$libaryWidgets['ordersPerMonth']['content'].='<td align="right" nowrap>'.number_format($total_amount,0,3,'.').'</td>';
$libaryWidgets['ordersPerMonth']['content'].='<td align="right" nowrap>'.number_format(($total_amount/$dayOfTheYear)*365,0,3,'.').'</td>';
$libaryWidgets['ordersPerMonth']['content'].='</tr>';
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$libaryWidgets['ordersPerMonth']['content'].='
</table>';
?>