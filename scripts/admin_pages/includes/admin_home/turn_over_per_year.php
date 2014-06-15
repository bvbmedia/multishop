<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$compiledWidget['key']='turnoverPerYear';
$compiledWidget['defaultCol']=1;
$compiledWidget['title']=$this->pi_getLL('sales_volume_by_year', 'Jaaromzet');
$sql_year="select crdate from tx_multishop_orders where deleted=0 order by orders_id asc limit 1";
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if (!$row_year['crdate']) {
	$compiledWidget['content']='<p>'.$this->pi_getLL('admin_label_data_not_available').'</p>';
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
		$end_time=strtotime("Y-01-01 00:00:00 +1 YEAR", $start_time);
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
		$compiledWidget['content'].='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" >';
		$compiledWidget['content'].='<tr class="odd">';
		foreach ($dates as $key=>$value) {
			$compiledWidget['content'].='<td align="right">'.ucfirst($key).'</td>';
		}
		$compiledWidget['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
		$compiledWidget['content'].='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('average', 'Average')).'</td>';
		$compiledWidget['content'].='</tr>';
		$compiledWidget['content'].='<tr class="even">';
		$compiledWidget['content'].=$tmpContent;
		$tmpContent='';
		$compiledWidget['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount, 0).'</td>';
		$compiledWidget['content'].='<td align="right" nowrap>'.mslib_fe::amount2Cents(($total_amount/count($dates)), 0).'</td>';
		$compiledWidget['content'].='</tr>';
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$compiledWidget['content'].='
		</table>';
	} else {
		$compiledWidget['content']='<p>'.$this->pi_getLL('admin_label_data_not_available').'</p>';
	}
}
?>