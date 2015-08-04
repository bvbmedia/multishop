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
$temp_year='<select name="stats_year_sb" id="stats_year_sb" class="form-control">';
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
$content.='<div class="panel-body">
<form action="index.php" method="get" id="orders_stats_form" class="float_right">
<input name="type" type="hidden" value="2003" />
<input name="Search" type="hidden" value="1" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_stats_customers" />
<input name="tx_multishop_pi1[stats_section]" type="hidden" value="stats_per_months" />
<div class="well">
	<div class="form-inline">
		'.$temp_year.'
		<div class="checkbox checkbox-success checkbox-inline">
			<input id="checkbox_paid_orders_only" name="paid_orders_only" type="checkbox" value="1" '.($this->cookie['paid_orders_only'] ? 'checked' : '').' /><label for="checkbox_paid_orders_only">'.$this->pi_getLL('show_paid_orders_only').'</label>
		</div>
	</div>
</div>
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
';
$dates=array();
$content.='<h3>'.htmlspecialchars($this->pi_getLL('sales_volume_by_month')).'</h3>';
for ($i=1; $i<13; $i++) {
	$time=strtotime(date($selected_year.$i."-01")." 00:00:00");
	$dates[strftime("%B %Y", $time)]=date($selected_year."m", $time);
}
$content.='<div class="table-responsive">';
$content.='<table class="table table-striped table-bordered" id="product_import_table">';
$content.='<thead><tr>';
foreach ($dates as $key=>$value) {
	$content.='<th class="cellDate">'.ucfirst($key).'</th>';
}
//$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('total')).'</td>';
//$content.='<td align="right" nowrap>'.htmlspecialchars($this->pi_getLL('cumulative')).'</td>';
$content.='</tr></thead><tbody>';
$content.='<tr>';
foreach ($dates as $key=>$value) {
	$total_price=0;
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$where=array();
	if ($this->cookie['paid_orders_only']) {
		$where[]='(o.paid=1)';
	} else {
		$where[]='(o.paid=1 or o.paid=0)';
	}
	$where[]='(o.deleted=0)';
	$str="SELECT sum(o.grand_total) as total, o.billing_company, o.billing_name, o.customer_id FROM tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.") group by o.customer_id having total > 0 order by total desc limit 10";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$content.='<td valign="top">
		';
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$content.='
		<table class="table table-striped table-bordered table-condensed no-mb" id="product_import_table">
			<thead>
			<tr class="'.$tr_type.'">
				<th class="cellPrice">'.$this->pi_getLL('amount').'</td>
				<th valign="top">'.$this->pi_getLL('customer').'</td>
			</tr>
			</thead>
		';
		$total_amount=0;
		while (($customer=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			if ($customer['billing_company']) {
				$name=$customer['billing_company'];
			} else {
				$name=$customer['billing_name'];
			}
			$customer_edit_link=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]='.$customer['customer_id'].'&action=edit_customer', 1);
			$total_amount+=$customer['total'];
			$content.='
			<tr class="'.$tr_type.'">
				<td class="cellPrice"><strong>'.mslib_fe::amount2Cents($customer['total'], 0).'</strong></td>
				<td valign="top"><a href="'.$customer_edit_link.'">'.$name.'</a></td>
			</tr>
			';
		}
		$content.='
			<tfoot>
			<tr class="'.$tr_type.'">
				<th class="cellPrice">'.mslib_fe::amount2Cents($total_amount, 0).'</td>
				<th valign="top">'.$this->pi_getLL('customer').'</td>
			</tr>
			</tfoot>
		';
		$content.='</table>';
	}
	$content.='</td>';
}
$content.='</tr>';
$content.='</tbody></table></div>';
$content.='<hr>';
$content.='<div class="clearfix">';
$content.='<a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a>';
$content.='</div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>