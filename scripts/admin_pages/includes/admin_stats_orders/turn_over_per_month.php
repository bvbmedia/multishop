<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->post['Search'] and ($this->get['payment_status']!=$this->cookie['payment_status'])) {
	$this->cookie['payment_status']=$this->get['payment_status'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->get['stats_year_sb']>0) {
	if ($this->get['stats_year_sb']!=$this->cookie['stats_year_sb']) {
		$this->cookie['stats_year_sb']=$this->get['stats_year_sb'];
	}
} else {
	$this->cookie['stats_year_sb']=date("Y");
}
if ($this->get['Search']) {
	if ($this->get['paid_orders_only'] and $this->get['paid_orders_only']!=$this->cookie['paid_orders_only']) {
		$this->cookie['paid_orders_only']=$this->get['paid_orders_only'];
	} else {
		$this->cookie['paid_orders_only']='';
	}
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
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
$temp_year='<select name="stats_year_sb" id="stats_year_sb">';
if ($oldest_year) {
	for ($y=$current_year; $y>=$oldest_year; $y--) {
		if ($this->cookie['stats_year_sb']==$y) {
			$temp_year.='<option value="'.$y.'" selected="selected">'.$y.'</option>';
		} else {
			$temp_year.='<option value="'.$y.'">'.$y.'</option>';
		}
	}
} else {
	$temp_year.='<option value="'.$current_year.'" selected="selected">'.$current_year.'</option>';
}
$temp_year.='</select>';
$selected_year='Y-';
if ($this->cookie['stats_year_sb']>0) {
	$selected_year=$this->cookie['stats_year_sb']."-";
}
/*$order_status_sb='<h2>'.$this->pi_getLL('order_status').'</h2>';
$all_orders_status=mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
if (is_array($all_orders_status) and count($all_orders_status)) {
	if (is_array($all_orders_status) and count($all_orders_status)) {
		$order_status_sb.='<ul class="horizontal_list order_status_checkbox" id="admin_sales_stats_order_status">';
		foreach ($all_orders_status as $row) {
			$order_status_sb.='<li><input type="checkbox" name="tx_multishop_pi1[status][]" value="'.$row['id'].'" '.(in_array($row['id'], $this->get['tx_multishop_pi1']['status']) ? 'checked="checked"' : '').' class="admin_sales_stats_order_status" id="sales_stats_status_'.$row['id'].'" /><label for="sales_stats_status_'.$row['id'].'">'.$row['name'].'</label></li>';
		}
		$order_status_sb.='</ul>';
	}
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
*/
// input for search
// usergroup
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
$customer_groups_input.='<select id="groups" class="order_select2" name="usergroup" style="width:200px">'."\n";
$customer_groups_input.='<option value="0">'.$this->pi_getLL('all').' '.$this->pi_getLL('usergroup').'</option>'."\n";
if (is_array($groups) and count($groups)) {
	foreach ($groups as $group) {
		$customer_groups_input.='<option value="'.$group['uid'].'"'.($this->get['usergroup']==$group['uid'] ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
	}
}
$customer_groups_input.='</select>'."\n";
// usergroup eol
// payment status
$payment_status_select='<select name="payment_status" id="payment_status" class="order_select2" style="width:250px">
<option value="">'.$this->pi_getLL('select_orders_payment_status').'</option>';
if ($this->cookie['payment_status']=='paid_only') {
	$payment_status_select.='<option value="paid_only" selected="selected">'.$this->pi_getLL('show_paid_orders_only').'</option>';
} else {
	$payment_status_select.='<option value="paid_only">'.$this->pi_getLL('show_paid_orders_only').'</option>';
}
if ($this->cookie['payment_status']=='unpaid_only') {
	$payment_status_select.='<option value="unpaid_only" selected="selected">'.$this->pi_getLL('show_unpaid_orders_only').'</option>';
} else {
	$payment_status_select.='<option value="unpaid_only">'.$this->pi_getLL('show_unpaid_orders_only').'</option>';
}
$payment_status_select.='</select>';
// payment status eol
// order status
$orders_status_list='<select name="orders_status_search" id="orders_status_search" class="order_select2" style="width:200px"><option value="0" '.((!$order_status_search_selected) ? 'selected' : '').'>'.$this->pi_getLL('all_orders_status', 'All orders status').'</option>';
if (is_array($all_orders_status)) {
	$order_status_search_selected=false;
	foreach ($all_orders_status as $row) {
		$orders_status_list.='<option value="'.$row['id'].'" '.(($this->get['orders_status_search']==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
		if ($this->post['orders_status_search']==$row['id']) {
			$order_status_search_selected=true;
		}
	}
}
$orders_status_list.='</select>';
// order status eol
// payment method
$payment_methods=array();
$sql=$GLOBALS['TYPO3_DB']->SELECTquery('payment_method, payment_method_label', // SELECT ...
	'tx_multishop_orders', // FROM ...
	'', // WHERE...
	'payment_method', // GROUP BY...
	'payment_method_label', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	if (empty($row['payment_method_label'])) {
		$row['payment_method']='nopm';
		$row['payment_method_label']='Empty payment method';
	}
	$payment_methods[$row['payment_method']]=$row['payment_method_label'].($row['payment_method']!='nopm' ? ' (code: '.$row['payment_method'].')' : '');
}
$payment_method_input='';
$payment_method_input.='<select id="payment_method" class="order_select2" name="payment_method" style="width:200px">'."\n";
$payment_method_input.='<option value="all">'.$this->pi_getLL('all_payment_methods').'</option>'."\n";
if (is_array($payment_methods) and count($payment_methods)) {
	foreach ($payment_methods as $payment_method_code=>$payment_method) {
		$payment_method_input.='<option value="'.$payment_method_code.'"'.($this->get['payment_method']==$payment_method_code ? ' selected="selected"' : '').'>'.$payment_method.'</option>'."\n";
	}
}
$payment_method_input.='</select>'."\n";
// payment method eol
// shipping method
$shipping_methods=array();
$sql=$GLOBALS['TYPO3_DB']->SELECTquery('shipping_method, shipping_method_label', // SELECT ...
	'tx_multishop_orders', // FROM ...
	'', // WHERE...
	'shipping_method', // GROUP BY...
	'shipping_method_label', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	if (empty($row['shipping_method_label'])) {
		$row['shipping_method']='nosm';
		$row['shipping_method_label']='Empty shipping method';
	}
	$shipping_methods[$row['shipping_method']]=$row['shipping_method_label'].($row['shipping_method']!='nosm' ? ' (code: '.$row['shipping_method'].')' : '');
}
$shipping_method_input='';
$shipping_method_input.='<select id="shipping_method" class="order_select2" name="shipping_method" style="width:200px">'."\n";
$shipping_method_input.='<option value="all">'.$this->pi_getLL('all_shipping_methods').'</option>'."\n";
if (is_array($shipping_methods) and count($shipping_methods)) {
	foreach ($shipping_methods as $shipping_method_code=>$shipping_method) {
		$shipping_method_input.='<option value="'.$shipping_method_code.'"'.($this->get['shipping_method']==$shipping_method_code ? ' selected="selected"' : '').'>'.$shipping_method.'</option>'."\n";
	}
}
$shipping_method_input.='</select>'."\n";
// shipping method eol
$content.='<div class="order_stats_mode_wrapper">
<ul class="horizontal_list">
	<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_orders&tx_multishop_pi1[stats_section]=turnoverPerYear').'" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('stats_turnover_per_year', 'Turnover per year')).'</a></li>
	<li><strong class="msadmin_button">'.htmlspecialchars($this->pi_getLL('stats_turnover_per_month', 'Turnover per month')).'</strong></li>
</ul>
</div>';
$content.='
<form method="get" id="orders_stats_form" class="float_right">
<!--
<input name="id" type="hidden" value="'.$this->get['id'].'" />
<div class="stat-years float_right">'.$temp_year.'</div>
<input name="type" type="hidden" value="2003" />
<input name="Search" type="hidden" value="1" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_stats_orders" />
<input name="tx_multishop_pi1[stats_section]" type="hidden" value="turnoverPerMonth" />
<div class="paid-orders"><input id="checkbox_paid_orders_only" name="paid_orders_only" type="checkbox" value="1" '.($this->cookie['paid_orders_only'] ? 'checked' : '').' /><label for="checkbox_paid_orders_only">'.$this->pi_getLL('show_paid_orders_only').'</label></div>
-->

<div id="search-orders">
	<input name="id" type="hidden" value="'.$this->get['id'].'" />
	<!-- <div class="stat-years float_right">'.$temp_year.'</div> -->
	<input name="type" type="hidden" value="2003" />
	<input name="Search" type="hidden" value="1" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_stats_orders" />
	<input name="tx_multishop_pi1[stats_section]" type="hidden" value="turnoverPerMonth" />
	<div class="row formfield-container-wrapper">
		<div class="col-sm-4 formfield-wrapper">
			<label for="groups">'.$this->pi_getLL('usergroup').'</label>
			'.$customer_groups_input.'
			<label for="order_date_from">'.$this->pi_getLL('from').':</label>
			<input type="text" name="order_date_from" id="order_date_from" value="'.$this->get['order_date_from'].'">
			<label for="order_date_till" class="labelInbetween">'.$this->pi_getLL('to').':</label>
			<input type="text" name="order_date_till" id="order_date_till" value="'.$this->get['order_date_till'].'">
		</div>
		<div class="col-sm-4 formfield-wrapper">
			<label for="payment_status">'.$this->pi_getLL('order_payment_status').'</label>
			'.$payment_status_select.'
			<label for="orders_status_search" class="labelInbetween">'.$this->pi_getLL('order_status').'</label>
			'.$orders_status_list.'
		</div>
		<div class="col-sm-4 formfield-wrapper">
			<label for="payment_method">'.$this->pi_getLL('payment_method').'</label>
			'.$payment_method_input.'
			<label for="shipping_method" class="labelInbetween">'.$this->pi_getLL('shipping_method').'</label>
			'.$shipping_method_input.'
		</div>
	</div>
	<div class="row formfield-container-wrapper">
		<div class="col-sm-12 formfield-wrapper">
			<input type="submit" name="Search" value="'.htmlspecialchars($this->pi_getLL('search')).'" />
		</div>
	</div>
</div>

</form>
<script type="text/javascript" language="JavaScript">
	jQuery(document).ready(function($) {
		$(document).on("click", ".admin_sales_stats_order_status", function() {
			var serial=$(".admin_sales_stats_order_status").serialize();
			if (serial!="") {
				location.href = "'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_orders&tx_multishop_pi1[stats_section]=turnoverPerMonth').'&" + serial;
			} else {
				location.href = "'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_orders&tx_multishop_pi1[stats_section]=turnoverPerMonth').'";
			}
		});
	});
</script>';
// search processor
$search_start_time='';
$search_end_time='';
$filter=array();
if (!empty($this->get['order_date_from']) && !empty($this->get['order_date_till'])) {
	list($from_date, $from_time)=explode(" ", $this->get['order_date_from']);
	list($fd, $fm, $fy)=explode('/', $from_date);
	list($till_date, $till_time)=explode(" ", $this->get['order_date_till']);
	list($td, $tm, $ty)=explode('/', $till_date);
	$search_start_time=strtotime($fy.'-'.$fm.'-'.$fd.' '.$from_time);
	$search_end_time=strtotime($ty.'-'.$tm.'-'.$td.' '.$till_time);
	$filter[]="o.crdate BETWEEN '".$start_time."' and '".$end_time."'";
}
if ($this->post['orders_status_search']>0) {
	$filter[]="(o.status='".$this->get['orders_status_search']."')";
}
if (isset($this->get['payment_method']) && $this->get['payment_method']!='all') {
	if ($this->get['payment_method']=='nopm') {
		$filter[]="(o.payment_method is null)";
	} else {
		$filter[]="(o.payment_method='".$this->get['payment_method']."')";
	}
}
if (isset($this->get['shipping_method']) && $this->get['shipping_method']!='all') {
	if ($this->get['shipping_method']=='nosm') {
		$filter[]="(o.shipping_method is null)";
	} else {
		$filter[]="(o.shipping_method='".$this->get['shipping_method']."')";
	}
}
if (isset($this->get['usergroup']) && $this->get['usergroup']>0) {
	$filter[]=' o.customer_id IN (SELECT uid from fe_users where '.$GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->get['usergroup'], 'fe_users').')';
}
if ($this->cookie['payment_status']=='paid_only') {
	$filter[]="(o.paid='1')";
} else {
	if ($this->cookie['payment_status']=='unpaid_only') {
		$filter[]="(o.paid='0')";
	}
}
if (!$this->masterShop) {
	$filter[]='o.page_uid='.$this->shop_pid;
}
// search processor eol
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h2>';
for ($i=1; $i<13; $i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date($selected_year."m", $time);
}
$content.='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" id="product_import_table">';
$content.='<tr class="odd">';
foreach ($dates as $key=>$value) {
	$content.='<td align="right">'.ucfirst($key).'</td>';
}
$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('cumulative')).'</td>';
$content.='</tr>';
$content.='<tr class="even">';
$total_amount=0;
foreach ($dates as $key=>$value) {
	$total_price=0;
	if ($search_start_time && $search_end_time) {
		$start_time=$search_start_time;
		$end_time=$search_end_time;
	} else {
		$start_time=strtotime($value."-01 00:00:00");
		$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	}
	$where=array();
	foreach ($filter as $filter_data) {
		$where[]=$filter_data;
	}
	$where[]='(o.deleted=0)';
	if (!empty($status_where)) {
		$where[]=$status_where;
	}
	$str="SELECT o.orders_id, o.grand_total FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$total_price=($total_price+$row['grand_total']);
	}
	$content.='<td align="right">'.mslib_fe::amount2Cents($total_price, 0).'</td>';
	$total_amount=$total_amount+$total_price;
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
$content.='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount, 0).'</td>';
$content.='<td align="right" nowrap>'.mslib_fe::amount2Cents(($total_amount/$dayOfTheYear)*365, 0).'</td>';
$content.='</tr>';
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$content.='
</table>';
// LAST MONTHS EOF
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('average_order_amount_per_month', 'Average order amount per month')).'</h2>';
for ($i=1; $i<13; $i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date($selected_year."m", $time);
}
$content.='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" id="product_import_table">';
$content.='<tr class="odd">';
foreach ($dates as $key=>$value) {
	$content.='<td align="right">'.ucfirst($key).'</td>';
}
$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
$content.='</tr>';
$content.='<tr class="even">';
$total_amount_avg=0;
$total_orders_avg=0;
foreach ($dates as $key=>$value) {
	$total_price_avrg=0;
	$total_orders=0;
	if ($search_start_time && $search_end_time) {
		$start_time=$search_start_time;
		$end_time=$search_end_time;
	} else {
		$start_time=strtotime($value."-01 00:00:00");
		$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	}
	$where=array();
	foreach ($filter as $filter_data) {
		$where[]=$filter_data;
	}
	$where[]='(o.deleted=0)';
	if (!empty($status_where)) {
		$where[]=$status_where;
	}
	$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$total_orders=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	$total_orders_avg+=$total_orders;
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$total_price_avrg=($total_price_avrg+$row['grand_total']);
	}
	if ($total_price_avrg>0 && $total_orders>0) {
		$totalSum=$total_price_avrg/$total_orders;
	} else {
		$totalSum=0;
	}
	$content.='<td align="right">'.mslib_fe::amount2Cents($totalSum, 0).'</td>';
	$total_amount_avg=$total_amount_avg+$total_price_avrg;
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
if ($total_amount_avg>0 && $total_orders_avg>0) {
	$totalSum=$total_amount_avg/$total_orders_avg;
} else {
	$totalSum=0;
}
$content.='<td align="right" nowrap>'.mslib_fe::amount2Cents($totalSum, 0).'</td>';
$content.='</tr>';
if (!$tr_type or $tr_type=='even') {
	$tr_type='odd';
} else {
	$tr_type='even';
}
$content.='
</table>';
// LAST MONTHS EOF
$tr_type='even';
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_day')).'</h2>';
if ($currentMonth) {
	$endDay=date("d");
} else {
	$endDay=31;
}
for ($i=0; $i<$endDay; $i++) {
	$time=strtotime("-".$i." day", strtotime(date($currentDay.'-'.$month.'-'.$this->cookie['stats_year_sb'])));
	$dates[strftime("%x", $time)]=$time;
}
$content.='<table width="100%" class="msZebraTable" cellpadding="0" cellspacing="0" border="0" id="product_import_table">
<tr>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('day')).'</th>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('amount')).'</th>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('average', 'average')).'</th>
	<th>'.htmlspecialchars($this->pi_getLL('orders_id')).'</th>
</tr>';
foreach ($dates as $key=>$value) {
	$total_daily_orders=0;
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$content.='<tr class="'.$tr_type.'">';
	$content.='<td align="right">'.$key.'</td>';
	$total_price=0;
	$system_date=date($selected_year."m-d", $value);
	if ($search_start_time && $search_end_time) {
		$start_time=$search_start_time;
		$end_time=$search_end_time;
	} else {
		$start_time=strtotime($system_date." 00:00:00");
		$end_time=strtotime($system_date." 23:59:59");
	}
	$where=array();
	foreach ($filter as $filter_data) {
		$where[]=$filter_data;
	}
	$where[]='(o.deleted=0)';
	if (!empty($status_where)) {
		$where[]=$status_where;
	}
	$str="SELECT o.customer_id, o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$uids=array();
	$users=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$total_price=($total_price+$row['grand_total']);
		$uids[]='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$row['orders_id'].'&action=edit_order', 1).'">'.$row['orders_id'].'</a>';
		$total_daily_orders++;
	}
	if ($total_price>0 && $total_daily_orders>0) {
		$totalSum=$total_price/$total_daily_orders;
	} else {
		$totalSum=0;
	}
	$content.='<td align="right">'.mslib_fe::amount2Cents($total_price, 0).'</td>';
	$content.='<td align="right">'.mslib_fe::amount2Cents($totalSum, 0).'</td>';
	if (count($uids)) {
		$content.='<td>'.implode(", ", $uids).'</td>';
	} else {
		$content.='<td> </td>';
	}
	$content.='</tr>';
}
$content.='</table>';
// LAST MONTHS EOF
$content.='<div class="msAdminOrdersStatsButtonWrapper">';
$dlink_param['stats_year_sb']=$this->get['stats_year_sb'];
$dlink_param['paid_orders_only']=$this->get['paid_orders_only'];
$param_link='';
$param_val_ctr=0;
foreach ($dlink_param as $key=>$val) {
	$param_link.='&'.$key.'='.$val;
	if (!empty($val)) {
		$param_val_ctr++;
	}
}
if ($param_val_ctr>0) {
	$dlink="location.href = '/".mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_orders_stats_dl_xls'.$param_link)."'";
} else {
	$dlink="downloadOrdersExcelParam();";
}
$content.='</div>';
$content.='<p class="extra_padding_bottom">';
$content.='<a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a>';
$content.='<span id="msAdminOrdersListingDownload">';
$content.='<input type="button" name="download" class="link_block" value="'.mslib_befe::strtoupper($this->pi_getLL('admin_download_as_excel_file')).'" onclick="'.$dlink.'" />';
$content.='</span>';
$content.='
</p>';
$headerData='';
$headerData.='
<script type="text/javascript">
function downloadOrdersExcelParam() {
	var href = "/'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_orders_stats_dl_xls').'";
	var form_ser = jQuery("form").serializeArray();
	var form_param = "";
	jQuery.each(form_ser, function(i, v) {
		if (v.name == "stats_year_sb" ||
			v.name == "paid_orders_only") {
			if (form_param == "") {
				form_param += v.name + "=" + v.value;
			} else {
				form_param += "&" + v.name + "=" + v.value;
			}
		}
	});
	return location.href = href + "?" + form_param;
}
jQuery(document).ready(function ($) {
	$(\'#order_date_from\').datetimepicker({
		dateFormat: \'dd/mm/yy\',
		showSecond: true,
		timeFormat: \'HH:mm:ss\'
	});
	$(\'#order_date_till\').datetimepicker({
		dateFormat: \'dd/mm/yy\',
        showSecond: true,
        timeFormat: \'HH:mm:ss\'
	});
});
</script>';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
$headerData='';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>