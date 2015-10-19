<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$compiledWidget['key']='turnoverPerYear';
$compiledWidget['defaultCol']=1;
$compiledWidget['title']=$this->pi_getLL('sales_volume_by_year', 'Jaaromzet');
$where=array();
$where[]='(o.deleted=0)';
switch ($this->dashboardArray['section']) {
	case 'admin_home':
		break;
	case 'admin_edit_customer':
		if ($this->get['tx_multishop_pi1']['cid'] && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
			$where[]='(o.customer_id='.$this->get['tx_multishop_pi1']['cid'].')';
		}
		break;
}
$str=$GLOBALS['TYPO3_DB']->SELECTquery('o.crdate', // SELECT ...
	'tx_multishop_orders o', // FROM ...
	'('.implode(" AND ", $where).')', // WHERE...
	'', // GROUP BY...
	'orders_id asc', // ORDER BY...
	'1' // LIMIT ...
);
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($str);
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
		$data_query['where']=array();
		$data_query['where'][]='(o.deleted=0)';
		$data_query['where'][]='(o.crdate BETWEEN '.$start_time.' and '.$end_time.')';
		$data_query['where'][]='(o.paid=1 or o.paid=0)';
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
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_home/turn_over_per_year.php']['annuallyHomeStatsQueryHookPreProc'])) {
			$params=array(
				'data_query'=>&$data_query
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_home/turn_over_per_year.php']['annuallyHomeStatsQueryHookPreProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('o.orders_id, o.grand_total', // SELECT ...
			'tx_multishop_orders o', // FROM ...
			'('.implode(" AND ", $data_query['where']).')', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
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
		$compiledWidget['content'].='<table width="100%" class="table table-striped table-bordered" cellspacing="0" cellpadding="0" border="0" >';
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