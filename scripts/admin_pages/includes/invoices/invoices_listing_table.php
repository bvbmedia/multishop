<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$counter=0;
$tr_type='even';
$tmp='
<table width="100%" cellpadding="0" cellspacing="0" border="0" id="product_import_table" class="msZebraTable msadmin_invoices_listing">
<tr>
	<td align="center" width="17">
		<label for="check_all_1"></label>
		<input type="checkbox" class="PrettyInput" id="check_all_1">
	</td>
';
$headercol.='
<th width="50">'.$this->pi_getLL('invoice_number').'</th>
<th width="50" class="cell_orders_id">'.$this->pi_getLL('orders_id').'</th>
<th width="75">'.$this->pi_getLL('store').'</th>
<th>'.$this->pi_getLL('customers').'</th>
<th width="50">'.$this->pi_getLL('order_date').'</th>
<th width="50">'.$this->pi_getLL('amount').'</th>
<th width="50">'.$this->pi_getLL('admin_paid').'</th>
';
$headercol.='
</tr>';
$cb_ctr=0;
$tmp.=$headercol;

foreach ($invoices as $invoice) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$tmp.='<tr class="'.$tr_type.'">';
	$tmp.='<td nowrap>
	<label for="checkbox_'.$invoice['invoice_id'].'"></label>
	<input type="checkbox" name="selected_invoices[]" class="PrettyInput" id="checkbox_'.$invoice['invoice_id'].'" value="'.$invoice['invoice_id'].'">
	</td>
	';
	$user=mslib_fe::getUser($invoice['customer_id']);
	$link_name=$invoice['ordered_by'];
	if ($user['username']) {
		$link_name.=" (".$user['username'].")";
	}
	$tmp.='<th align="right" nowrap><a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" target="_blank">'.$invoice['invoice_id'].'</a></th>';
	$tmp.='<td align="right" nowrap>'.$invoice['orders_id'].'</td>';
	$tmp.='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($invoice['page_uid']).'</td>';
	$tmp.='<td align="left" nowrap><a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" target="_blank">'.$link_name.'</a></td>';
	$tmp.='<td align="right" nowrap>'.strftime("%x", $invoice['crdate']).'</td>';
	$tmp.='<td align="right" nowrap>'.mslib_fe::amount2Cents(($invoice['reversal_invoice'] ? '-' : '').$invoice['amount'], 0).'</td>';
	$tmp.='<td align="center" nowrap>';
	if (!$invoice['paid']) {
		$tmp.='<span class="admin_status_red" alt="'.$this->pi_getLL('has_not_been_paid').'" title="'.$this->pi_getLL('has_not_been_paid').'"></span>&nbsp;';
		$tmp.='<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_invoices_to_paid&selected_invoices[]='.$invoice['invoice_id']).'" onclick="return confirm(\''.sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_been_paid'), $invoice['invoice_id']).'\')"><span class="admin_status_green_disable" alt="'.$this->pi_getLL('change_to_paid').'" title="'.$this->pi_getLL('change_to_paid').'"></span></a>';
	} else {
		$tmp.='<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_invoices_to_not_paid&selected_invoices[]='.$invoice['invoice_id']).'" onclick="return confirm(\''.sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_not_been_paid'), $invoice['invoice_id']).'\')"><span class="admin_status_red_disable" alt="'.$this->pi_getLL('change_to_not_paid').'" title="'.$this->pi_getLL('change_to_not_paid').'"></span></a>&nbsp;';
		$tmp.='<span class="admin_status_green" alt="'.$this->pi_getLL('has_been_paid').'" title="'.$this->pi_getLL('has_been_paid').'"></span>';
	}
	$tmp.='</td>';
	$tmp.='</tr>';
}
$tmp.='
<tr>
	<th>
		&nbsp;
	</th>
'.$headercol;
$tmp.='
</table>
';
$actions=array();
$actions['create_reversal_invoice']=$this->pi_getLL('create_reversal_invoice_for_selected_invoices');
$actions['mail_invoices']=$this->pi_getLL('mail_selected_invoices');
$actions['update_selected_invoices_to_paid']=$this->pi_getLL('update_selected_invoices_to_paid');
$actions['update_selected_invoices_to_not_paid']=$this->pi_getLL('update_selected_invoices_to_not_paid');
$tmp.='
<select name="tx_multishop_pi1[action]" id="selected_invoices_action">
<option value="">'.$this->pi_getLL('choose_action').'</option>
';
foreach ($actions as $key=>$value) {
	$tmp.='<option value="'.$key.'">'.$value.'</option>';
}
$tmp.='
	</select>
	<input name="tx_multishop_pi1[mailto]" type="text" value="'.$this->ms['MODULES']['STORE_EMAIL'].'" id="msadmin_invoices_mailto" />
	<input class="msadmin_button" type="submit" name="submit" value="'.$this->pi_getLL('submit').'" ></input>
	<script>
		jQuery(document).ready(function($) {
			$(\'#selected_invoices_action\').change(function() {
				if ($(this).val()==\'mail_invoices\') {
					$("#msadmin_invoices_mailto").show();
				} else {
					$("#msadmin_invoices_mailto").hide();
				}
			});
			'.($this->get['tx_multishop_pi1']['action']!='mail_invoices' ? '$("#msadmin_invoices_mailto").hide();' : '').'
		});
	</script>
	';
$tmp.='
<script>
	jQuery(".tooltip").tooltip({position: "bottom"});
	jQuery(function($){
		$(\'#check_all_1\').click(function(){
			checkAllPrettyCheckboxes(this,$(\'.msadmin_invoices_listing\'));
		});
	});
</script>
';

?>