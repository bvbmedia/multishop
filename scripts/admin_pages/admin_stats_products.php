<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// billing countries
$additional_where=array();
if (!$this->masterShop) {
	$additional_where[]='page_uid=\''.$this->shop_pid.'\'';
}
$order_countries=mslib_befe::getRecords('', 'tx_multishop_orders', '', $additional_where, 'billing_country', 'billing_country asc');
$order_billing_country=array();
foreach ($order_countries as $order_country) {
	$cn_localized_name=htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order_country['billing_country']));
	if (!empty($cn_localized_name)) {
		$order_billing_country[] = '<option value="' . mslib_befe::strtolower($order_country['billing_country']) . '" ' . ((mslib_befe::strtolower($this->get['country']) == strtolower($order_country['billing_country'])) ? 'selected' : '') . '>' . $cn_localized_name . '</option>';
	}
}
ksort($order_billing_country);
$billing_countries_sb='<select class="invoice_select2" name="country" id="country""><option value="">'.$this->pi_getLL('all_countries').'</option>'.implode("\n", $order_billing_country).'</select>';

$all_orders_status=mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
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
$year_select='<select name="stats_year_sb" class="form-control" id="stats_year_sb"><option value="">'.$this->pi_getLL('choose').'</option>';
if ($oldest_year) {
	for ($y=$current_year; $y>=$oldest_year; $y--) {
		if ($this->cookie['stats_year_sb']==$y) {
			$year_select.='<option value="'.$y.'" selected="selected">'.$y.'</option>';
		} else {
			$year_select.='<option value="'.$y.'">'.$y.'</option>';
		}
	}
} else {
	$year_select.='<option value="'.$current_year.'" selected="selected">'.$current_year.'</option>';
}
$year_select.='</select>';
$selected_year='Y-';
if ($this->cookie['stats_year_sb']>0) {
	$selected_year=$this->cookie['stats_year_sb']."-";
}
// input for search
// usergroup
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
$customer_groups_input.='<select id="groups" class="order_select2" name="usergroup">'."\n";
$customer_groups_input.='<option value="0">'.$this->pi_getLL('all').' '.$this->pi_getLL('usergroup').'</option>'."\n";
if (is_array($groups) and count($groups)) {
	foreach ($groups as $group) {
		$customer_groups_input.='<option value="'.$group['uid'].'"'.($this->get['usergroup']==$group['uid'] ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
	}
}
$customer_groups_input.='</select>'."\n";
// usergroup eol
// payment status
$payment_status_select='<select name="payment_status" id="payment_status" class="order_select2">
<option value="">'.$this->pi_getLL('select_orders_payment_status').'</option>';
if ($this->get['payment_status']=='paid_only') {
	$payment_status_select.='<option value="paid_only" selected="selected">'.$this->pi_getLL('show_paid_orders_only').'</option>';
} else {
	$payment_status_select.='<option value="paid_only">'.$this->pi_getLL('show_paid_orders_only').'</option>';
}
if ($this->get['payment_status']=='unpaid_only') {
	$payment_status_select.='<option value="unpaid_only" selected="selected">'.$this->pi_getLL('show_unpaid_orders_only').'</option>';
} else {
	$payment_status_select.='<option value="unpaid_only">'.$this->pi_getLL('show_unpaid_orders_only').'</option>';
}
$payment_status_select.='</select>';
// payment status eol
// order status
$orders_status_list='<select name="orders_status_search" id="orders_status_search" class="order_select2"><option value="0" '.((!$order_status_search_selected) ? 'selected' : '').'>'.$this->pi_getLL('all_orders_status', 'All orders status').'</option>';
if (is_array($all_orders_status)) {
	$order_status_search_selected=false;
	if (is_array($all_orders_status) && count($all_orders_status)) {
		foreach ($all_orders_status as $row) {
			$orders_status_list.='<option value="'.$row['id'].'" '.(($this->get['orders_status_search']==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
			if ($this->get['orders_status_search']==$row['id']) {
				$order_status_search_selected=true;
			}
		}
	}
}
$orders_status_list.='</select>';
// order status eol
// payment method
$payment_methods=array();
$sql=$GLOBALS['TYPO3_DB']->SELECTquery('payment_method, payment_method_label', // SELECT ...
	'tx_multishop_orders', // FROM ...
	((!$this->masterShop) ? 'page_uid=\''.$this->shop_pid.'\'' : ''), // WHERE...
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
$payment_method_input.='<select id="payment_method" class="order_select2" name="payment_method">'."\n";
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
	((!$this->masterShop) ? 'page_uid=\''.$this->shop_pid.'\'' : ''), // WHERE...
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
$shipping_method_input.='<select id="shipping_method" class="order_select2" name="shipping_method">'."\n";
$shipping_method_input.='<option value="all">'.$this->pi_getLL('all_shipping_methods').'</option>'."\n";
if (is_array($shipping_methods) and count($shipping_methods)) {
	foreach ($shipping_methods as $shipping_method_code=>$shipping_method) {
		$shipping_method_input.='<option value="'.$shipping_method_code.'"'.($this->get['shipping_method']==$shipping_method_code ? ' selected="selected"' : '').'>'.$shipping_method.'</option>'."\n";
	}
}
$shipping_method_input.='</select>'."\n";
// shipping method eol
$header_form_content='
<form method="get" id="products_stats_form">
<div id="search-orders" class="well">
	<input name="id" type="hidden" value="'.$this->get['id'].'" />
	<!-- <div class="stat-years float_right">'.$year_select.'</div> -->
	<input name="type" type="hidden" value="2003" />
	<input name="Search" type="hidden" value="1" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_stats_products" />
	<input name="tx_multishop_pi1[stats_section]" type="hidden" value="'.(isset($this->get['tx_multishop_pi1']['stats_section']) ? $this->get['tx_multishop_pi1']['stats_section'] : 'perMonth').'" />
	<div class="row formfield-container-wrapper">
		<div class="col-md-4 formfield-wrapper">
			<div class="form-group">
			<label for="groups">'.$this->pi_getLL('usergroup').'</label>
			'.$customer_groups_input.'
			</div>
			<div class="form-group">
				<label for="country">'.$this->pi_getLL('countries').'</label>
				'.$billing_countries_sb.'
			</div>
			<label>Date</label>
			<div class="form-group form-inline">
			<label for="order_date_from">'.$this->pi_getLL('from').':</label>
			<input type="text" class="form-control" name="order_date_from" id="order_date_from" value="'.$this->get['order_date_from'].'">
			<label for="order_date_till" class="labelInbetween">'.$this->pi_getLL('to').':</label>
			<input type="text" class="form-control" name="order_date_till" id="order_date_till" value="'.$this->get['order_date_till'].'">
			</div>
		</div>
		<div class="col-md-4 formfield-wrapper">
			<div class="form-group">
			<label for="payment_status">'.$this->pi_getLL('order_payment_status').'</label>
			'.$payment_status_select.'
			</div>
			<div class="form-group">
			<label for="orders_status_search" class="labelInbetween">'.$this->pi_getLL('order_status').'</label>
			'.$orders_status_list.'
			</div>
			<label>'.$this->pi_getLL('year').'</label>
			<div class="form-group form-inline">
				'.$year_select.'
			</div>
		</div>
		<div class="col-md-4 formfield-wrapper">
			<div class="form-group">
			<label for="payment_method">'.$this->pi_getLL('payment_method').'</label>
			'.$payment_method_input.'
			</div>
			<div class="form-group">
			<label for="shipping_method" class="labelInbetween">'.$this->pi_getLL('shipping_method').'</label>
			'.$shipping_method_input.'
			</div>
			<div class="form-group">
				<div class="col-md-6">
					<div class="checkbox checkbox-success checkbox-inline">
						<input type="checkbox" id="filter_by_excluding_vat" name="tx_multishop_pi1[excluding_vat]" value="1"'.($this->get['tx_multishop_pi1']['excluding_vat']?' checked':'').'>
						<label for="filter_by_excluding_vat">'.htmlspecialchars($this->pi_getLL('excluding_vat')).'</label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row formfield-container-wrapper">
		<div class="col-sm-12 formfield-wrapper">
			<input type="submit" name="Search" class="btn btn-success pull-right" value="'.htmlspecialchars($this->pi_getLL('search')).'" />
		</div>
	</div>
</div>

</form>';
$headerData='';
$headerData.='
<script type="text/javascript">
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
	$(".order_select2").select2();
	$(".invoice_select2").select2();
});
</script>';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
switch ($this->get['tx_multishop_pi1']['stats_section']) {
	case 'perYear':
		$content.='<div class="order_stats_mode_wrapper">
		<ul class="pagination horizontal_list">
			<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_products&tx_multishop_pi1[stats_section]=perMonth').'">'.htmlspecialchars($this->pi_getLL('per_month', 'Per month')).'</a></li>
			<li class="active"><span>'.htmlspecialchars($this->pi_getLL('per_year', 'Per year')).'</span></li>
		</ul>
		</div>';
		$content.=$header_form_content;
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_stats_products/stats_per_year.php');
		break;
	case 'perMonth':
	default:
		$content.='<div class="order_stats_mode_wrapper">
		<ul class="pagination horizontal_list">
			<li class="active"><span>'.htmlspecialchars($this->pi_getLL('per_month', 'Per month')).'</span></li>
			<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_products&tx_multishop_pi1[stats_section]=perYear').'">'.htmlspecialchars($this->pi_getLL('per_year', 'Per year')).'</a></li>
		</ul>
		</div>';
		$content.=$header_form_content;
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_stats_products/stats_per_months.php');
		break;
}
?>