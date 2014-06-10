<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$sql_year="select crdate from tx_multishop_orders where deleted=0 order by orders_id asc limit 1";
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if ($row_year['crdate']>0) {
	$oldest_year=date("Y", $row_year['crdate']);
} else {
	$oldest_year=date("Y");
}
$current_year=date("Y");
$dates=array();
//$libaryWidgets['turnoverPerMonth']['content'].='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h2>';
for ($i=3; $i>=0; $i--) {
	//$time=strtotime("-".$i." month");
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
//	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
$libaryWidgets['turnoverPerMonth']['content'].='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" >';
$libaryWidgets['turnoverPerMonth']['content'].='<tr class="odd">';
foreach ($dates as $key=>$value) {
	$libaryWidgets['turnoverPerMonth']['content'].='<td align="right">'.ucfirst($key).'</td>';
}
$libaryWidgets['turnoverPerMonth']['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
$libaryWidgets['turnoverPerMonth']['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('cumulative')).' '.date("Y").'</td>';
$libaryWidgets['turnoverPerMonth']['content'].='</tr>';
$libaryWidgets['turnoverPerMonth']['content'].='<tr class="even">';
$total_amount=0;
foreach ($dates as $key=>$value) {
	$total_price=0;
	$start_time=strtotime($value."-01 00:00:00");
	//$end_time=strtotime($value."-31 23:59:59");
	$end_time=strtotime($value."-01 00:00:00 +1 MONTH -1 DAY");
	$where=array();
	if ($this->cookie['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$total_price=($total_price+$row['grand_total']);
	}
	$libaryWidgets['turnoverPerMonth']['content'].='<td align="right">'.mslib_fe::amount2Cents($total_price, 0).'</td>';
	$total_amount=$total_amount+$total_price;
	if (date("Y", $start_time)==date("Y")) {
		$total_amount_cumulative=$total_amount_cumulative+$total_price;
	}
}
if ($this->cookie['stats_year_sb']==date("Y") || !$this->cookie['stats_year_sb']) {
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
$libaryWidgets['turnoverPerMonth']['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount, 0).'</td>';
$libaryWidgets['turnoverPerMonth']['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents(($total_amount_cumulative/$dayOfTheYear)*365, 0).'</td>';
$libaryWidgets['turnoverPerMonth']['content'].='</tr>';
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$libaryWidgets['turnoverPerMonth']['content'].='
</table>';
?>