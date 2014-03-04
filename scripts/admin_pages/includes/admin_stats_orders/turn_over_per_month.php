<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->get['stats_year_sb'] > 0) {
	if ($this->get['stats_year_sb'] != $this->cookie['stats_year_sb']) {
		$this->cookie['stats_year_sb'] = $this->get['stats_year_sb'];
	}
} else {
	$this->cookie['stats_year_sb']=date("Y");
}
if ($this->get['Search']) {
	if ($this->get['paid_orders_only'] and $this->get['paid_orders_only'] != $this->cookie['paid_orders_only']) {
		$this->cookie['paid_orders_only'] = $this->get['paid_orders_only'];
	} else {
		$this->cookie['paid_orders_only'] = '';
	}
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
$sql_year 		= "select crdate from tx_multishop_orders where deleted=0 order by orders_id asc limit 1";
$qry_year 		= $GLOBALS['TYPO3_DB']->sql_query($sql_year);
$row_year 		= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_year);
if ($row_year['crdate'] > 0) {
	$oldest_year 	= date("Y", $row_year['crdate']);
} else {
	$oldest_year 	= date("Y");
}
$current_year 	= date("Y");
$temp_year = '<select name="stats_year_sb" id="stats_year_sb">';
if ($oldest_year) {
	for ($y = $current_year; $y >= $oldest_year; $y--) {
		if ($this->cookie['stats_year_sb'] == $y) {
			$temp_year .= '<option value="'.$y.'" selected="selected">'.$y.'</option>';
		} else {
			$temp_year .= '<option value="'.$y.'">'.$y.'</option>';
		}
	}
	
} else {
	$temp_year .= '<option value="'.$current_year.'" selected="selected">'.$current_year.'</option>';
}
$temp_year .= '</select>';
$selected_year = 'Y-';
if ($this->cookie['stats_year_sb'] > 0) {
	$selected_year = $this->cookie['stats_year_sb'] . "-";
}
$content.='<div class="order_stats_mode_wrapper" style="width:250px">';
$content.='<span class="float_right">[<a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_stats_orders&tx_multishop_pi1[stats_section]=turnoverPerYear').'">'.htmlspecialchars($this->pi_getLL('stats_turnover_per_year', 'Turnover per year')).'</a>]</span>';
$content.='<span>[<span><strong>'.htmlspecialchars($this->pi_getLL('stats_turnover_per_month', 'Turnover per month')).'</strong></span>]</span>';
$content.='</div>';
$content.='
<form method="get" id="orders_stats_form" class="float_right">
<div class="stat-years float_right">'.$temp_year.'</div>
<input name="type" type="hidden" value="2003" />
<input name="Search" type="hidden" value="1" />
<input name="tx_multishop_pi1[stats_section]" type="hidden" value="turnoverPerMonth" />
<div class="paid-orders"><input id="checkbox_paid_orders_only" name="paid_orders_only" type="checkbox" value="1" '.($this->cookie['paid_orders_only']?'checked':'').' /> '.$this->pi_getLL('show_paid_orders_only').'</div>
</form>
<script type="text/javascript" language="JavaScript">
	jQuery(document).ready(function($){
		$("#checkbox_paid_orders_only").click(function(e) {
			$("#orders_stats_form").submit();
		});

		$("#stats_year_sb").change(function(e) {
			$("#orders_stats_form").submit();
		});
	});
</script>';
$dates=array();
$content.='<h2>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h2>';
for ($i=1;$i<13;$i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y",$time)]=date($selected_year . "m", $time);
}
$content.='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" id="product_import_table">';
$content.='<tr class="odd">';
foreach ($dates as $key => $value) {
	$content.='<td align="right">'.ucfirst($key).'</td>';
}
$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('cumulative')).'</td>';
$content.='</tr>';
$content.='<tr class="even">';
$total_amount=0;
foreach ($dates as $key => $value) {
	$total_price=0;
	$start_time	= strtotime($value."-01 00:00:00");
	$end_time	= strtotime($value."-31 23:59:59");
	$where=array();	
	if ($this->cookie['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ",$where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$total_price=($total_price+$row['grand_total']);
	}
	$content.='<td align="right">'.mslib_fe::amount2Cents($total_price,0).'</td>';
	$total_amount=$total_amount+$total_price;
}
if ($this->cookie['stats_year_sb'] == date("Y") || !$this->cookie['stats_year_sb']) {
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
$content.='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount,0).'</td>';
$content.='<td align="right" nowrap>'.mslib_fe::amount2Cents(($total_amount/$dayOfTheYear)*365,0).'</td>';
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
$content.='<h2>'.htmlspecialchars($this->pi_getLL('sales_average_by_month', 'Monthly sales average')).'</h2>';
for ($i=1;$i<13;$i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y",$time)]=date($selected_year . "m", $time);
}
$content.='<table width="100%" class="msZebraTable" cellspacing="0" cellpadding="0" border="0" id="product_import_table">';
$content.='<tr class="odd">';
foreach ($dates as $key => $value) {
	$content.='<td align="right">'.ucfirst($key).'</td>';
}
$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
$content.='</tr>';
$content.='<tr class="even">';
$total_amount_avg = 0;
$total_orders_avg = 0;
foreach ($dates as $key => $value) {
	$total_price_avrg=0;
	$total_orders = 0;
	$start_time	= strtotime($value."-01 00:00:00");
	$end_time	= strtotime($value."-31 23:59:59");
	$where=array();
	if ($this->cookie['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	$str="SELECT o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ",$where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$total_orders = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);	
	$total_orders_avg += $total_orders;
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$total_price_avrg = ($total_price_avrg+$row['grand_total']);
	}
	$content.='<td align="right">'.mslib_fe::amount2Cents($total_price_avrg/$total_orders,0).'</td>';
	$total_amount_avg = $total_amount_avg + $total_price_avrg;
}
if ($this->cookie['stats_year_sb'] == date("Y") || !$this->cookie['stats_year_sb']) {
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
$content.='<td align="right" nowrap>'.mslib_fe::amount2Cents($total_amount_avg/$total_orders_avg,0).'</td>';
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
for ($i=0;$i<$endDay;$i++) {
	$time=strtotime("-".$i." day",strtotime(date($currentDay.'-'.$month.'-'.$this->cookie['stats_year_sb'])));
	$dates[strftime("%x",  $time)]=$time;
}
$content.='<table width="100%" class="msZebraTable" cellpadding="0" cellspacing="0" border="0" id="product_import_table">
<tr>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('day')).'</th>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('amount')).'</th>
	<th width="100" align="right">'.htmlspecialchars($this->pi_getLL('average', 'average')).'</th>
	<th>'.htmlspecialchars($this->pi_getLL('orders_id')).'</th>	
</tr>';
foreach ($dates as $key => $value) {
	$total_daily_orders = 0;
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';	
	}
	$content.='<tr class="'.$tr_type.'">';
	$content.='<td align="right">'.$key.'</td>';

	$total_price=0;
	$system_date=date($selected_year."m-d",$value);
	$start_time	= strtotime($system_date." 00:00:00");
	$end_time	= strtotime($system_date." 23:59:59");
	$where=array();
	if ($this->cookie['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	
	$str="SELECT o.customer_id, o.orders_id, o.grand_total  FROM tx_multishop_orders o WHERE (".implode(" AND ",$where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$uids=array();
	$users=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$total_price=($total_price+$row['grand_total']);
		$uids[]='<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$row['orders_id'].'&action=edit_order').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 980, height: browser_height} )">'.$row['orders_id'].'</a>';
		$total_daily_orders++;
	}
	$content.='<td align="right">'.mslib_fe::amount2Cents($total_price,0).'</td>';
	$content.='<td align="right">'.mslib_fe::amount2Cents($total_price/$total_daily_orders,0).'</td>';
	if (count($uids)) {
		$content.='<td>'.implode(", ",$uids).'</td>';
	} else {
		$content.='<td> </td>';
	}
	$content.='</tr>';
}
$content.='</table>';
// LAST MONTHS EOF
$content.= '<div class="msAdminOrdersStatsButtonWrapper">';
$dlink_param['stats_year_sb'] = $this->get['stats_year_sb'];
$dlink_param['paid_orders_only'] = $this->get['paid_orders_only'];
$param_link = '';
$param_val_ctr = 0;
foreach ($dlink_param as $key => $val) {
	$param_link .= '&'.$key.'='.$val;
	if (!empty($val)) {
		$param_val_ctr++;
	}
}
if ($param_val_ctr > 0) {
	$dlink = "location.href = '/".mslib_fe::typolink('','tx_multishop_pi1[page_section]=admin_orders_stats_dl_xls' . $param_link)."'";
} else {
	$dlink = "downloadOrdersExcelParam();";
}
$content .= '</div>';
$content.='<p class="extra_padding_bottom">';
$content.='<a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a>';
$content.='<span id="msAdminOrdersListingDownload">';
$content .= '<input type="button" name="download" class="link_block" value="'.t3lib_div::strtoupper($this->pi_getLL('admin_download_as_excel_file')).'" onclick="'.$dlink.'" />';
$content.='</span>';
$content.='
</p>';
$headerData='';
$headerData .= '
<script type="text/javascript">
	function downloadOrdersExcelParam() {
		var href = "/'.mslib_fe::typolink('','tx_multishop_pi1[page_section]=admin_orders_stats_dl_xls').'";
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
</script>
';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
$headerData='';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>