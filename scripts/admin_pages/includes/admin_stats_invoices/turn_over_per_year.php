<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->get['Search']) {
	if ($this->get['paid_orders_only_py'] and $this->get['paid_orders_only_py']!=$this->cookie['paid_orders_only']) {
		$this->cookie['paid_orders_only_py']=$this->get['paid_orders_only_py'];
	} else {
		$this->cookie['paid_orders_only_py']='';
	}
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
$sql_year="select crdate from tx_multishop_orders order by orders_id asc limit 1";
$qry_year=$GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if ($row_year['crdate']>0) {
	$oldest_year=date("Y", $row_year['crdate']);
} else {
	$oldest_year=date("Y");
}
$current_year=date("Y");
$order_status_sb='<h3>'.$this->pi_getLL('order_status').'</h3>';
$all_orders_status=mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
if (is_array($all_orders_status) and count($all_orders_status)) {
	$order_status_sb.='<div class="order_status_checkbox" id="admin_sales_stats_order_status">';
	foreach ($all_orders_status as $row) {
		$order_status_sb.='<div class="checkbox checkbox-success"><input type="checkbox" name="tx_multishop_pi1[status][]" value="'.$row['id'].'" '.(in_array($row['id'], $this->get['tx_multishop_pi1']['status']) ? 'checked="checked"' : '').' class="admin_sales_stats_order_status" id="sales_stats_status_'.$row['id'].'" /><label for="sales_stats_status_'.$row['id'].'">'.$row['name'].'</label></div>';
	}
	$order_status_sb.='</div>';
}
if (isset($this->get['tx_multishop_pi1']['status']) && count($this->get['tx_multishop_pi1']['status'])>0) {
	$status_where='';
	$tmp=array();
	foreach ($this->get['tx_multishop_pi1']['status'] as $order_status) {
		$tmp[]='o.status='.$order_status;
	}
	if (count($tmp)) {
		$status_where='('.implode(' or ', $tmp).')';
	}
}
$content.='<div class="order_stats_mode_wrapper">
<ul class="pagination horizontal_list">
	<li class="active"><span>'.htmlspecialchars($this->pi_getLL('stats_turnover_per_year', 'Turnover per year')).'</span></li>
	<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_invoices&tx_multishop_pi1[stats_section]=turnoverPerMonth').'">'.htmlspecialchars($this->pi_getLL('stats_turnover_per_month', 'Turnover per month')).'</a>
</ul>
'.$order_status_sb.'
</div>';
$content.='
<hr>
<form method="get" id="orders_stats_form" class="float_right">
<input name="id" type="hidden" value="'.$this->get['id'].'" />
<input name="type" type="hidden" value="2003" />
<input name="Search" type="hidden" value="1" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_stats_invoices" />
<input name="tx_multishop_pi1[stats_section]" type="hidden" value="turnoverPerYear" />
<div class="paid-orders">
<div class="checkbox checkbox-success">
<input id="checkbox_paid_orders_only" name="paid_orders_only_py" type="checkbox" value="1" '.($this->cookie['paid_orders_only_py'] ? 'checked' : '').' /><label for="checkbox_paid_orders_only">'.$this->pi_getLL('show_paid_orders_only').'</label>
</div>
</div>
</form>
<hr>
<script type="text/javascript" language="JavaScript">
jQuery(document).ready(function($) {
	$(document).on("click", "#checkbox_paid_orders_only", function(e) {
		$("#orders_stats_form").submit();
	});
	$(document).on("click", ".admin_sales_stats_order_status", function() {
		var serial=$(".admin_sales_stats_order_status").serialize();
		if (serial!="") {
			location.href = "'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_invoices&tx_multishop_pi1[stats_section]=turnoverPerYear').'&" + serial;
		} else {
			location.href = "'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_invoices&tx_multishop_pi1[stats_section]=turnoverPerYear').'";
		}
	});
});
</script>
';
$year_total_amount=array();
$year_total_order=array();
for ($yr=$current_year; $yr>=$oldest_year; $yr--) {
	$dates=array();
	for ($i=1; $i<13; $i++) {
		$time=strtotime(date($yr."-".$i."-01")." 00:00:00");
		$dates[strftime("%B %Y", $time)]=date($yr."-"."m", $time);
	}
	$total_amount=0;
	$total_orders_per_year=0;
	foreach ($dates as $key=>$value) {
		$total_price=0;
		$total_orders=0;
		$start_time=strtotime($value."-01 00:00:00");
		//$end_time=strtotime($value."-31 23:59:59");
		$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
		$data_query=array();
		if ($this->cookie['paid_orders_only_py']) {
			$data_query['where'][]='(o.paid=1)';
		} else {
			$data_query['where'][]='(o.paid=1 or o.paid=0)';
		}
		$data_query['where'][]='(o.deleted=0)';
		if (!empty($status_where)) {
			$data_query['where'][]=$status_where;
		}
		// hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_stats_invoices/turn_over_per_year.php']['annuallyStatsInvoicesQueryHookPreProc'])) {
			$params=array(
				'data_query'=>&$data_query
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_stats_invoices/turn_over_per_year.php']['annuallyStatsInvoicesQueryHookPreProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$str="SELECT i.reversal_invoice,i.invoice_id, i.invoice_id, o.orders_id, o.grand_total  FROM tx_multishop_orders o, tx_multishop_invoices i  WHERE (".implode(" AND ", $data_query['where']).") and (i.crdate BETWEEN ".$start_time." and ".$end_time.") AND o.orders_id=i.orders_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if (!$row['reversal_invoice']) {
				$total_price=($total_price+$row['grand_total']);
			} else {
				$total_price=($total_price-$row['grand_total']);
			}
			$total_orders++;
		}
		$total_amount=$total_amount+$total_price;
		$total_orders_per_year=$total_orders_per_year+$total_orders;
	}
	$year_total_amount[$yr]=mslib_fe::amount2Cents($total_amount, 0);
	$year_total_order[$yr]=mslib_fe::amount2Cents($total_amount/$total_orders_per_year, 0);
}
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$tr_type='even';
$content.='<h3>'.htmlspecialchars($this->pi_getLL('sales_volume_by_year', 'Sales volume by year')).'</h3>';
$content.='<table class="table table-striped table-bordered" id="product_import_table">
<thead><tr>
	<th width="200">'.htmlspecialchars($this->pi_getLL('year', 'Year')).'</th>
	<th align="right">'.htmlspecialchars($this->pi_getLL('amount', 'Amount')).'</th>
	<th align="right">'.htmlspecialchars($this->pi_getLL('average', 'Average')).'</th>
</tr></thead><tbody>';
foreach ($year_total_amount as $years=>$year_total) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$content.='<tr class="'.$tr_type.'">';
	$content.='<td align="right">'.$years.'</td>';
	$content.='<td align="right">'.$year_total.'</td>';
	$content.='<td align="right">'.($year_total_order[$years]).'</td>';
	$content.='</tr>';
}
$content.='</tbody></table>';
// LAST MONTHS EOF
$content='<div class="panel panel-default"><div class="panel-body">'.mslib_fe::shadowBox($content).'</div></div>';
?>