<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p>0) {
	$offset=(((($p)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])));
} else {
	$p=0;
	$offset=0;
}
if ($this->get['Search'] and ($this->get['no_checkout_cart_entries_only']!=$this->cookie['no_checkout_cart_entries_only'])) {
	$this->cookie['no_checkout_cart_entries_only']=$this->get['no_checkout_cart_entries_only'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
$content.='<div class="panel-body">
<form method="get" action="index.php" id="orders_stats_form" class="float_right">
<input name="id" type="hidden" value="'.$this->shop_pid.'" />
<input name="Search" type="hidden" value="1" />
<input name="type" type="hidden" value="2003" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="'.$this->ms['page'].'" />
<div class="well">
	<div class="checkbox checkbox-success checkbox-inline">
		<input id="checkbox_no_checkout_cart_entries_only" name="no_checkout_cart_entries_only" id="no_checkout_cart_entries_only" type="checkbox" value="1" '.($this->cookie['no_checkout_cart_entries_only'] ? 'checked' : '').' /><label for="no_checkout_cart_entries_only">'.$this->pi_getLL('display_unfinished_checkout_cart_entries_only').'</label>
	</div>
</div>
</form>
';
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript" language="JavaScript">
	jQuery(document).ready(function($) {
		$("#checkbox_no_checkout_cart_entries_only").click(function(e) {
			$("#orders_stats_form").submit();
		 });
		$(".is_not_checkout").css("opacity", "0.5");
	});
</script>
';
$dates=array();
$content.='<h3>'.htmlspecialchars($this->pi_getLL('month')).'</h3>';
for ($i=1; $i<13; $i++) {
	$time=strtotime(date("Y-".$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
$content.='<table class="table table-striped table-bordered" id="product_import_table">';
$content.='<thead><tr>';
foreach ($dates as $key=>$value) {
	$content.='<th class="cellDate">'.ucfirst($key).'</th>';
}
$content.='<th class="cellDate">'.htmlspecialchars($this->pi_getLL('cumulative')).'</th></tr></thead>';
$content.='<tbody><tr>';
$total=0;
$data_query=array();
if ($this->cookie['no_checkout_cart_entries_only']) {
	$data_query['where'][]='(c.is_checkout=0)';
} else {
	$data_query['where'][]='(c.is_checkout=0 or c.is_checkout=1)';
}
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shopping_cart_stats.php']['shoppingCartStatsQueryHookPreProc'])) {
	$params=array(
			'data_query'=>&$data_query
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shopping_cart_stats.php']['shoppingCartStatsQueryHookPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
foreach ($dates as $key=>$value) {
	$total_price=0;
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$str="SELECT c.session_id FROM tx_multishop_cart_contents c WHERE (".implode(" AND ", $data_query['where']).") and (c.crdate BETWEEN ".$start_time." and ".$end_time.") and page_uid='".$this->shop_pid."' group by c.session_id ";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	$content.='<td class="cellDate">'.$rows.'</td>';
	$total_carts=$total_carts+$rows;
}
$content.='<td class="cellDate">'.number_format(($total_carts/date("m"))*12).'</td></tr></tbody>';
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
$content.='<h2>'.htmlspecialchars($this->pi_getLL('day')).'</h2>';
for ($i=0; $i<31; $i++) {
	$time=strtotime("-".$i." day");
	$dates[strftime("%a. %x", $time)]=$time;
}
$content.='<table class="table table-striped table-bordered" id="product_import_table">
<thead>
<tr>
	<th class="cellDate">'.htmlspecialchars($this->pi_getLL('day')).'</th>
	<th class="text-right">'.htmlspecialchars($this->pi_getLL('number_of_shopping_carts')).'</th>
	<th>'.htmlspecialchars($this->pi_getLL('content')).'</th>
</tr>
</thead>
<tbody>';
foreach ($dates as $key=>$value) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$content.='<tr class="'.$tr_type.'">';
	$content.='<td class="cellDate">'.$key.'</td>';
	$total_price=0;
	$system_date=date("Y-m-d", $value);
	$start_time=strtotime($system_date." 00:00:00");
	$end_time=strtotime($system_date." 23:59:59");
	$str="SELECT c.ip_address,c.session_id FROM tx_multishop_cart_contents c WHERE (".implode(" AND ", $data_query['where']).") and (c.crdate BETWEEN ".$start_time." and ".$end_time.") and page_uid='".$this->shop_pid."' group by c.session_id ";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	$content.='<td class="text-right">'.number_format($rows).'</td>';
	$content.='<td>';
	$pageset['total_rows']=$rows;
	// GET THE PRODUCTS THAT ARE INSIDE THE CART
	$str="SELECT * FROM tx_multishop_cart_contents c WHERE (".implode(" AND ", $data_query['where']).") and (c.crdate BETWEEN ".$start_time." and ".$end_time.") and page_uid='".$this->shop_pid."' order by c.id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$session_ids=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))) {
		if (!in_array($row['session_id'], $session_ids)) {
			$cart=unserialize($row['contents']);
			if (count($cart['products'])>0) {
				$products=array();
				foreach ($cart['products'] as $product) {
					$products[]=$product;
				}
				if (count($products)>0) {
					// print customer settings
					$content.='<table id="product_import_table" class="table table-striped table-bordered '.(!$row['is_checkout'] ? 'is_not_checkout' : '').'">';
					$tr_rows=array();
					$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('date').'</th><td>'.strftime("%a. %x %X", $row['crdate']).'</td>';
					if ($row['ip_address']) {
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('ip_address').'</th><td>'.$row['ip_address'].'</td>';
					}
					if ($row['is_checkout']) {
						// lets find out how long the user did to finish the checkout
						$str2="SELECT crdate FROM tx_multishop_cart_contents c where c.session_id='".$row['session_id']."' and c.id < '".$row['id']."'  and page_uid='".$this->shop_pid."' order by c.id asc limit 1";
						$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
						$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
						$time=($row['crdate']-$row2['crdate']);
						if ($time>=60) {
							$time_label=round(($time/60)).' minutes';
						} else {
							$time_label=$time.' seconds';
						}
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('time_needed_to_finish_checkout').'</th><td>'.$time_label.'</td>';
					} else {
						if ($row['customer_id']) {
							$user=mslib_fe::getUser($row['customer_id']);
							$cart['user']=$user;
						}
					}
					if ($cart['user']['username']) {
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('username').'</th><td>'.$cart['user']['username'].'</td>';
					}
					if ($cart['user']['first_name']) {
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('name').'</th><td>'.$cart['user']['first_name'].' '.$cart['user']['middle_name'].' '.$cart['user']['last_name'].'</td>';
					}
					if ($cart['user']['company']) {
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('company').'</th><td>'.$cart['user']['company'].'</td>';
					}
					if ($cart['user']['telephone']) {
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('telephone').'</th><td>'.$cart['user']['telephone'].'</td>';
					}
					if ($cart['user']['email']) {
						$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('email').'</th><td>'.$cart['user']['email'].'</td>';
					}
					$tmp_content='<table class="table table-striped table-bordered table-condensed no-mb">';
					$tmp_content.='<thead><tr>
					<th class="cellQty">'.$this->pi_getLL('qty').'</th>
					<th class="cellName">'.$this->pi_getLL('products_name').'</th>
					<th class="cellPrice">'.$this->pi_getLL('price').'</th>
					</tr></thead><tbody>';
					$sub_sub_tr_type='odd';
					foreach ($products as $product) {
						if (!$sub_sub_tr_type or $sub_sub_tr_type=='even') {
							$sub_sub_tr_type='odd';
						} else {
							$sub_sub_tr_type='even';
						}
						$tmp_content.='<tr class="'.$sub_sub_tr_type.'">';
						$tmp_content.='<td class="cellQty">'.$product['qty'].'</td>';
						$tmp_content.='<td class="cellName">'.$product['products_name'].'</td>';
						$tmp_content.='<td class="cellPrice">'.mslib_fe::amount2Cents($product['final_price'], 0).'</td>';
						$tmp_content.='</tr>';
					}
					$tmp_content.='</tbody></table>';
//					$tmp_content.='</table>';
					$tr_rows[]='<th class="text-right" width="100">'.$this->pi_getLL('content').'</th><td>'.$tmp_content.'</td>';
					$sub_tr_type='odd';
					foreach ($tr_rows as $tr_row) {
						if (!$sub_tr_type or $sub_tr_type=='even') {
							$sub_tr_type='odd';
						} else {
							$sub_tr_type='even';
						}
						$content.='<tr class="'.$sub_tr_type.'">'.$tr_row.'</tr>';
					}
					$content.='</table>';
				}
			}
		}
		$session_ids[]=$row['session_id'];
	}
	$content.='
	</td>';
	$content.='</tr>';
}
$content.='</tbody></table>';
$this->ms['MODULES']['PAGESET_LIMIT']=$this->ms['MODULES']['ORDERS_LISTING_LIMIT'];
if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
	$tmp='';
	//require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/orders/pagination.php');
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
	$pagination_listing=$tmp;
}
$content.=$pagination_listing;
// LAST MONTHS EOF
$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>