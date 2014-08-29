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
$order_status_sb='<label for="order_status">'.$this->pi_getLL('order_status').': </label>';
$all_orders_status=mslib_fe::getAllOrderStatus();
if (is_array($all_orders_status) and count($all_orders_status)) {
	$order_status_sb.='<select name="order_status" id="admin_sales_stats_order_status">';
	$order_status_sb.='<option value="">'.$this->pi_getLL('choose').'</option>';
	foreach ($all_orders_status as $row) {
		if ($this->get['tx_multishop_pi1']['status']==$row['id']) {
			$order_status_sb.='<option value="'.$row['id'].'" selected>'.$row['name'].'</option>'."\n";
		} else {
			$order_status_sb.='<option value="'.$row['id'].'">'.$row['name'].'</option>'."\n";
		}
	}
	$order_status_sb.='</select>';
}
$content.='<div class="order_stats_mode_wrapper">
<ul class="horizontal_list">
	<li><strong class="msadmin_button">'.htmlspecialchars($this->pi_getLL('stats_turnover_per_year', 'Turnover per year')).'</strong></li>
	<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_orders&tx_multishop_pi1[stats_section]=turnoverPerMonth').'" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('stats_turnover_per_month', 'Turnover per month')).'</a>
	<li>'.$order_status_sb.'</li>
</ul>
</div>';
$content.='
<form method="get" id="orders_stats_form" class="float_right">
<input name="type" type="hidden" value="2003" />
<input name="Search" type="hidden" value="1" />
<input name="tx_multishop_pi1[stats_section]" type="hidden" value="turnoverPerYear" />
<div class="paid-orders"><input id="checkbox_paid_orders_only" name="paid_orders_only_py" type="checkbox" value="1" '.($this->cookie['paid_orders_only_py'] ? 'checked' : '').' /> '.$this->pi_getLL('show_paid_orders_only').'</div>
</form>
<script type="text/javascript" language="JavaScript">
jQuery(document).ready(function($) {
	$(document).on("click", "#checkbox_paid_orders_only", function(e) {
		$("#orders_stats_form").submit();
	});
	$(document).on("change", "#admin_sales_stats_order_status", function() {
		location.href = "'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_orders&tx_multishop_pi1[stats_section]=turnoverPerYear').'&tx_multishop_pi1[status]=" + jQuery(this).val();
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

		$where=array();
		if ($this->cookie['paid_orders_only_py']) {
			$where[]='(o.paid=1)';
		} else {
			$where[]='(o.paid=1 or o.paid=0)';
		}
		$where[]='(o.deleted=0)';
		if (isset($this->get['tx_multishop_pi1']['status']) && $this->get['tx_multishop_pi1']['status']>0) {
			$where[]='(o.status='.$this->get['tx_multishop_pi1']['status'].')';
		}
		$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$total_price=($total_price+$row['grand_total']);
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
$content.='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_year', 'Sales volume by year')).'</h2>';
$content.='<table width="100%" class="msZebraTable" cellpadding="0" cellspacing="0" border="0" id="product_import_table">
<tr>
	<th width="50" align="right">'.htmlspecialchars($this->pi_getLL('year', 'Year')).'</th>
	<th align="right">'.htmlspecialchars($this->pi_getLL('amount', 'Amount')).'</th>
	<th align="right">'.htmlspecialchars($this->pi_getLL('average', 'Average')).'</th>
</tr>';
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
$content.='</table>';
// LAST MONTHS EOF
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>