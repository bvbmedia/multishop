<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
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
/*$content.='
<form action="index.php" method="get" id="orders_stats_form" class="float_right">
<div class="stat-years">'.$year_select.'</div>
<input name="type" type="hidden" value="2003" />
<input name="Search" type="hidden" value="1" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_stats_products" />
<input name="tx_multishop_pi1[stats_section]" type="hidden" value="stats_per_months" />

<div class="checkbox checkbox-success checkbox-inline"><input id="checkbox_paid_orders_only" name="paid_orders_only" type="checkbox" value="1" '.($this->cookie['paid_orders_only'] ? 'checked' : '').' /><label for="checkbox_paid_orders_only">'.htmlspecialchars($this->pi_getLL('show_paid_orders_only')).'</label></div>
</form>
<script type="text/javascript" language="JavaScript">
	jQuery(document).ready(function($) {
		$("#checkbox_paid_orders_only").click(function(e) {
			$("#orders_stats_form").submit();
		});

		$("#stats_year_sb").change(function(e) {
			$("#orders_stats_form").submit();
		});
	});
</script>
';*/
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h2>';
for ($i=1; $i<13; $i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date($selected_year."m", $time);
}
$content.='<table width="100%" class="table table-striped table-bordered" cellspacing="0" cellpadding="0" border="0" id="product_import_table">';
$content.='<tr class="odd">';
foreach ($dates as $key=>$value) {
	$content.='<td align="right">'.ucfirst($key).'</td>';
}
//$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
//$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('cumulative')).'</td>';
$content.='</tr>';
$content.='<tr class="even">';
$search_start_time='';
$search_end_time='';
$filter=array();
$data_query=array();
if (!empty($this->get['order_date_from']) && !empty($this->get['order_date_till'])) {
	list($from_date, $from_time)=explode(" ", $this->get['order_date_from']);
	list($fd, $fm, $fy)=explode('/', $from_date);
	list($till_date, $till_time)=explode(" ", $this->get['order_date_till']);
	list($td, $tm, $ty)=explode('/', $till_date);
	$search_start_time=strtotime($fy.'-'.$fm.'-'.$fd.' '.$from_time);
	$search_end_time=strtotime($ty.'-'.$tm.'-'.$td.' '.$till_time);
	$data_query['where'][]="o.crdate BETWEEN '".$search_start_time."' and '".$search_end_time."'";
}
if ($this->get['orders_status_search']>0) {
	$data_query['where'][]="(o.status='".$this->get['orders_status_search']."')";
}
if (isset($this->get['payment_method']) && $this->get['payment_method']!='all') {
	if ($this->get['payment_method']=='nopm') {
		$data_query['where'][]="(o.payment_method is null)";
	} else {
		$data_query['where'][]="(o.payment_method='".addslashes($this->get['payment_method'])."')";
	}
}
if (isset($this->get['shipping_method']) && $this->get['shipping_method']!='all') {
	if ($this->get['shipping_method']=='nosm') {
		$data_query['where'][]="(o.shipping_method is null)";
	} else {
		$data_query['where'][]="(o.shipping_method='".addslashes($this->get['shipping_method'])."')";
	}
}
if (isset($this->get['usergroup']) && $this->get['usergroup']>0) {
	$data_query['where'][]=' o.customer_id IN (SELECT uid from fe_users where '.$GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->get['usergroup'], 'fe_users').')';
}
if (isset($this->get['country']) && !empty($this->get['country'])) {
	$data_query['where'][]="o.billing_country='".addslashes($this->get['country'])."'";
}
if ($this->get['payment_status']=='paid_only') {
	$data_query['where'][]="(o.paid='1')";
} else {
	if ($this->get['payment_status']=='unpaid_only') {
		$data_query['where'][]="(o.paid='0')";
	}
}
if (!$this->masterShop) {
	$data_query['where'][]='o.page_uid='.$this->shop_pid;
}
$grandTotalColumnName='grand_total';
if (isset($this->get['tx_multishop_pi1']['excluding_vat'])) {
	$grandTotalColumnName='grand_total_excluding_vat';
}
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_stats_orders/turn_over_per_month.php']['monthlyStatsOrdersQueryHookPreProc'])) {
	$params=array(
		'data_query'=>&$data_query
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_stats_orders/turn_over_per_month.php']['monthlyStatsOrdersQueryHookPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// search processor eol
$dates=array();
//$content.='<h3>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h3>';
if (!empty($this->get['order_date_from']) && !empty($this->get['order_date_till'])) {
	$globalStartTime=$search_start_time;
	$globalEndTime=$search_end_time;
} else {
	$globalStartTime=strtotime(date($selected_year.'1'."-01")." 00:00:00");
	$globalEndTime=strtotime(date($selected_year.'12'."-01")." 00:00:00");
}
for ($i=0; $i<12; $i++) {
	$time=strtotime('+'.$i.' month',$globalStartTime);
	if ($time <= $globalEndTime) {
		//$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
		$dates[strftime("%B %Y", $time)]=date($selected_year."m", $time);
	}
}
if ($this->cookie['paid_orders_only']) {
	$data_query['where'][]='(o.paid=1)';
} else {
	$data_query['where'][]='(o.paid=1 or o.paid=0)';
}
$data_query['where'][]='(o.deleted=0)';
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_stats_products/stats_per_month.php']['monthlyStatsProductsQueryHookPreProc'])) {
	$params=array(
		'data_query'=>&$data_query
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_stats_products/stats_per_month.php']['monthlyStatsProductsQueryHookPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
foreach ($dates as $key=>$value) {
	$total_price=0;
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime('+1 month -1 second',$start_time);

	$filter=array();
	$filter[]='('.implode(' AND ', $data_query['where']).')';
	$filter[]='o.crdate BETWEEN '.$start_time.' and '.$end_time;
	$filter[]='o.orders_id=op.orders_id';
	$str=$GLOBALS['TYPO3_DB']->SELECTquery('sum(op.qty) as total, op.products_name, op.products_id, op.categories_id', // SELECT ...
		'tx_multishop_orders o, tx_multishop_orders_products op', // FROM ...
		implode(' AND ',$filter), // WHERE...
		'op.products_name having total > 0', // GROUP BY...
		'total desc', // ORDER BY...
		'10000' // LIMIT ...
	);
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$content.='<td valign="top">';
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$content.='
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="table table-striped table-bordered">
			<tr class="'.$tr_type.'">
				<th valign="top">Qty</td>
				<th valign="top">Product</td>
			</tr>
		';
		$total_amount=0;
		while (($product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$where='';
			if (!$product['categories_id']) {
				// fix fold old orders that did not have categories id in orders_products table
				$tmpProduct=mslib_fe::getProduct($product['products_id']);
				$product['categories_id']=$tmpProduct;
			}
			if ($product['categories_id']) {
				// get all cats to generate multilevel fake url
				$level=0;
				$cats=mslib_fe::Crumbar($product['categories_id']);
				$cats=array_reverse($cats);
				$where='';
				if (count($cats)>0) {
					foreach ($cats as $cat) {
						$where.="categories_id[".$level."]=".$cat['id']."&";
						$level++;
					}
					$where=substr($where, 0, (strlen($where)-1));
				}
				// get all cats to generate multilevel fake url eof
				$productLink=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
			} else {
				$productLink='';
			}
			$total_amount+=round($product['total'], 2);
			$content.='
			<tr class="'.$tr_type.'">
				<td valign="top" class="text-right"><strong>'.round($product['total'], 2).'</strong></td>
				<td valign="top"><a href="'.$productLink.'" target="_blank">'.htmlspecialchars($product['products_name']).'</a></td>
			</tr>
			';
		}
		$content.='
			<tr class="'.$tr_type.'">
				<th valign="top" class="text-right">'.round($total_amount, 2).'</td>
				<th valign="top">Product</td>
			</tr>
		';
		$content.='</table>';
	}
	$content.='</td>';
}
$content.='</tr>';
$content.='
</table>';
$content.='<p class="extra_padding_bottom">';
$content.='<a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a>';
$content.='
</p>';
$content='<div class="panel panel-default"><div class="panel-body">'.mslib_fe::shadowBox($content).'</div></div>';
?>