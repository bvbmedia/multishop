<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tmp='';
$str="SELECT *, os.name as orders_status from tx_multishop_orders o left join tx_multishop_orders_status os on o.status=os.id where o.by_phone = 1 order by orders_id desc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$orders=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$orders[$row['orders_id']]=$row;
	// count total
	$total_amount=0;
	$str2="SELECT * from tx_multishop_orders_products where orders_id='".$row['orders_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	while (($orders_products=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
		$item_price=($orders_products['qty']*$orders_products['final_price']);
		// now count the attributes
		$str3="SELECT * from tx_multishop_orders_products_attributes where orders_products_id='".$orders_products['orders_products_id']."'";
		$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
		while (($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3))!=false) {
			if ($row3['price_prefix']=='+') {
				$item_price=$item_price+($orders_products['qty']*$row3['options_values_price']);
			} else {
				$item_price=$item_price-($orders_products['qty']*$row3['options_values_price']);
			}
		}
		// now count the attributes eof				
		// vat
		if ($item_price and $orders_products['products_tax']) {
			$item_price=$item_price+($item_price*$orders_products['products_tax']/100);
		}
		// vat eof
		// now adding the item price to the order total price 
		$total_amount=$total_amount+$item_price;
	}
	$total_amount=$total_amount+$row['shipping_method_costs']+$row['payment_method_costs'];
	$orders[$row['orders_id']]['total_amount']=$total_amount;
	// count eof
}
$tmp.='
<script>
var checkAll = function() {
	for (var x = 0; x < 100; x++) {
		if (document.getElementById(\'ordid_\' + x) != null) {
			document.getElementById(\'ordid_\' + x).checked = true;
		}
	}
}


var uncheckAll = function() {
	for (var x = 0; x < 100; x++) {
		if (document.getElementById(\'ordid_\' + x) != null) {
			document.getElementById(\'ordid_\' + x).checked = false;
		}
	}
}
</script>
';
$tr_type='even';
$tmp.='<table class="table table-striped table-bordered msadmin_border" width="100%">';
if ($this->ms['MODULES']['INVOICE_PRINT'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {
	$tmp.='<th align="center"><input type="checkbox" onclick="if (this.checked == true) { checkAll(); } else { uncheckAll(); }"></th>';
}
$tmp.='<th>'.$this->pi_getLL('admin_order_id').'</th><th>'.$this->pi_getLL('admin_customer_name').'</th><th>'.$this->pi_getLL('total_price').'</th><th>'.$this->pi_getLL('paid').'</th><th>'.$this->pi_getLL('status').'</th>';
if ($this->ms['MODULES']['INVOICE_PRINT'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {
	$tmp.='<th>&nbsp;</th>';
	$tmp.='<form action="'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=admin_ajax').'?action=edit_order&print=invoice&all=1" method="post" target="_blank">';
}
$cb_ctr=0;
foreach ($orders as $order) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$tmp.='<tr class="'.$tr_type.'">';
	if ($this->ms['MODULES']['INVOICE_PRINT']) {
		$tmp.='<td align="center"><input type="checkbox" name="ordid[]" id="ordid_'.$cb_ctr++.'" value="'.$order['orders_id'].'"></td>';
	}
	$tmp.='<td align="left">'.$order['orders_id'].'</td>';
	$tmp.='<td align="left"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order', 1).'">'.$order['billing_name'].'</a></td>';
	$tmp.='<td align="right">'.mslib_fe::amount2Cents($order['total_amount'], 0).'</td>';
	$tmp.='<td align="center">'.(($order['paid']) ? $this->pi_getLL('yes') : $this->pi_getLL('no')).'</td>';
	// green,red,yellow,grey,orange
	$tmp.='<td align="center" width="100"><div class="orders_status_button_gray">'.$order['orders_status'].'</div></td>';
	if ($this->ms['MODULES']['INVOICE_PRINT'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {
		$tmp.='<td align="center">';
		if ($this->ms['MODULES']['INVOICE_PRINT']) {
			$tmp.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order&print=invoice', 1).'"><input type="button" class="btn btn-success" value="'.htmlspecialchars($this->pi_getLL('invoice')).'" /></a>';
		}
		if ($this->ms['MODULES']['INVOICE_PRINT'] && $this->ms['MODULES']['INVOICE_PRINT']) {
			$tmp.='&nbsp;';
		}
		if ($this->ms['MODULES']['PACKING_LIST_PRINT']) {
			$tmp.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order&print=packing', 1).'"><input type="button" class="btn btn-success" value="'.htmlspecialchars($this->pi_getLL('packing_list')).'" /></a>';
		}
		$tmp.='</td>';
	}
	$tmp.='</tr>';
}
$tmp.='<tr><td colspan="7"><input type="submit" class="btn btn-success" value="'.htmlspecialchars($this->pi_getLL('print_selected_orders')).'"></td></tr>';
$tmp.='</form>';
$tmp.='</table>';
$tabs=array();
$tabs['manual_orders']=array(
	htmlspecialchars($this->pi_getLL('admin_manual_order')),
	$tmp
);
require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop')."scripts/admin_pages/includes/manual_order/admin_add_order.php");
$content.='
<div class="main-heading"><h2>Orders</h2></div>

<script type="text/javascript"> 
jQuery(document).ready(function($) {
 
	jQuery(".tab_content").hide(); 
	jQuery("ul.tabs li:first").addClass("active").show();
	jQuery(".tab_content:first").show();
	jQuery("ul.tabs li").click(function() {
		jQuery("ul.tabs li").removeClass("active");
		jQuery(this).addClass("active"); 
		jQuery(".tab_content").hide();
		var activeTab = jQuery(this).find("a").attr("href");
		jQuery(activeTab).fadeIn(0);
		return false;
	});
 
});
</script>
<div id="tab-container">
    <ul class="tabs" id="admin_orders">	
';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='<li'.(($count==1) ? ' class="active"' : '').'><a href="#'.$key.'">'.$value[0].'</a></li>';
}
$content.='        
    </ul>
    <div class="tab_container">

	';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
			'.$value[1].'
        </div>
	';
}
$content.=$save_block.'
    </div>
</div>
';
$content.='<p class="extra_padding_bottom"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>