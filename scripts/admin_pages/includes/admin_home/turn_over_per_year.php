<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$sql_year="select crdate from tx_multishop_orders where deleted=0 order by orders_id asc limit 1";
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if (!$row_year['crdate']) {
	$libaryWidgets['turnoverPerYear']['content']='<p>Nog geen data beschikbaar.</p>';
} else {
	$oldest_year=date("Y", $row_year['crdate']);
	$current_year=date("Y");
	$dates=array();
	for ($i=2; $i>=0; $i--) {
		if ($oldest_year<=(date("Y")-$i)) {
			$time=strtotime("-".$i." year");
			$dates[strftime("%Y", $time)]=date("Y-01-01 00:00:00", $time);
		}
	}
	$total_amount=0;
	$tmpContent='';
	foreach ($dates as $key=>$value) {
		$total_price=0;
		$start_time=strtotime($value);
		//$end_time=strtotime(date("Y-12-31 23:59:59", strtotime($value)));
		$end_time=strtotime("Y-01-01 00:00:00 +1 YEAR",$start_time);
		$where=array();
		$where[]='(o.paid=1 or o.paid=0)';
		$where[]='(o.deleted=0)';
		$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$steps++;
				$total_price=($total_price+$row['grand_total']);
			}
			$tmpContent.='<td align="right">'.mslib_fe::amount2Cents($total_price, 0).'</td>';
			$total_amount=$total_amount+$total_price;
		}
	}
	if ($tmpContent!='') {
		$libaryWidgets['turnoverPerYear']['content'].='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" >';
		$libaryWidgets['turnoverPerYear']['content'].='<tr class="odd">';
		foreach ($dates as $key=>$value) {
			$libaryWidgets['turnoverPerYear']['content'].='<td align="right">'.ucfirst($key).'</td>';
		}
		$libaryWidgets['turnoverPerYear']['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
		$libaryWidgets['turnoverPerYear']['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('average', 'Average')).'</td>';
		$libaryWidgets['turnoverPerYear']['content'].='</tr>';
		$libaryWidgets['turnoverPerYear']['content'].='<tr class="even">';
		$libaryWidgets['turnoverPerYear']['content'].=$tmpContent;
		$tmpContent='';
		$libaryWidgets['turnoverPerYear']['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount, 0).'</td>';
		$libaryWidgets['turnoverPerYear']['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents(($total_amount/count($dates)), 0).'</td>';
		$libaryWidgets['turnoverPerYear']['content'].='</tr>';
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$libaryWidgets['turnoverPerYear']['content'].='
		</table>';
	} else {
		$libaryWidgets['turnoverPerYear']['content']='<p>Nog geen data beschikbaar.</p>';
	}
}
?>