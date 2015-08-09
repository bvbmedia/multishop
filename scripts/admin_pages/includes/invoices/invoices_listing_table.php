<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$counter=0;
$totalAmount=0;
$invoiceItem='';
foreach ($invoices as $invoice) {
	$cb_ctr++;
	$user=mslib_fe::getUser($invoice['customer_id']);
	$link_name=$invoice['ordered_by'];
	if ($user['username']) {
		$link_name.=" (".$user['username'].")";
	}
	$master_shop_col='';
	if ($this->masterShop) {
		$master_shop_col='<td class="cellName">'.mslib_fe::getShopNameByPageUid($invoice['page_uid']).'</td>';
	}
	//
	if ($invoice['reversal_invoice']) {
		$totalAmount-=$invoice['amount'];
	} else {
		$totalAmount+=$invoice['amount'];
	}
	//
	$paid_status='';
	if (!$invoice['paid']) {
		$paid_status.='<span class="admin_status_red" alt="'.$this->pi_getLL('has_not_been_paid').'" title="'.$this->pi_getLL('has_not_been_paid').'"></span>';
		$paid_status.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_invoices_to_paid&selected_invoices[]='.$invoice['id']).'" onclick="return confirm(\''.sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_been_paid'), $invoice['invoice_id']).'\')"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('change_to_paid').'" title="'.$this->pi_getLL('change_to_paid').'"></span></a>';
	} else {
		$paid_status.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_invoices_to_not_paid&selected_invoices[]='.$invoice['id']).'" onclick="return confirm(\''.sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_not_been_paid'), $invoice['invoice_id']).'\')"><span class="admin_status_red disabled" alt="'.$this->pi_getLL('change_to_not_paid').'" title="'.$this->pi_getLL('change_to_not_paid').'"></span></a>';
		$paid_status.='<span class="admin_status_green" alt="'.$this->pi_getLL('has_been_paid').'" title="'.$this->pi_getLL('has_been_paid').'"></span>';
	}
	//
	$actionButtons=array();
	$actionButtons['email']='<a href="#" data-dialog-title="Are you sure?" data-dialog-body="Are you sure?" class="disabled msBtnConfirm btn btn-sm btn-default"><i class="fa fa-phone-square"></i> '.ucfirst($this->pi_getLL('','e-mail')).'</a> ';
	$actionButtons['credit']='<a href="#" data-dialog-title="Are you sure?" data-dialog-body="Are you sure?" class="disabled msBtnConfirm btn btn-sm btn-default'.($invoice['reversal_invoice']?' disabled':'').'"><i class="fa fa fa-refresh"></i> '.$this->pi_getLL('','Credit').'</a>';
	//
	$action_button='';
	if (count($actionButtons)) {
		$action_button.='<div class="btn-group">';
		foreach ($actionButtons as $actionButton) {
			$action_button.=$actionButton;
		}
		$action_button.='</div>';
	}
	//
	$markerArray=array();
	$markerArray['INVOICE_CTR']=$cb_ctr;
	$markerArray['INVOICES_URL']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']);
	$markerArray['INVOICES_ID']=$invoice['invoice_id'];
	$markerArray['INVOICES_ORDER_ID']=$invoice['orders_id'];
	$markerArray['MASTER_SHOP']=$master_shop_col;
	$markerArray['INVOICES_CUSTOMER_NAME']=$link_name;
	$markerArray['INVOICES_ORDER_DATE']=strftime("%x", $invoice['crdate']);
	$markerArray['INVOICES_PAYMENT_METHOD']=$invoice['payment_method_label'];
	$markerArray['INVOICES_PAYMENT_CONDITION']=$invoice['payment_condition'];
	$markerArray['INVOICES_AMOUNT']=mslib_fe::amount2Cents(($invoice['reversal_invoice'] ? '-' : '').$invoice['amount'], 0);
	$markerArray['INVOICES_DATE_LAST_SENT']=($invoice['date_mail_last_sent']>0 ? strftime("%x", $invoice['date_mail_last_sent']) : '');
	$markerArray['INVOICES_PAID_STATUS']=$paid_status;
	$markerArray['INVOICES_ACTION']=$action_button;
	$markerArray['CUSTOM_MARKER_1_BODY']='';
	// custom page hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/invoices/invoices_listing_table.php']['adminInvoicesListingTmplIteratorPreProc'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'invoice'=>&$invoice
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/orders/invoices_listing_table.php']['adminInvoicesListingTmplIteratorPreProc'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom page hook that can be controlled by third-party plugin eof
	$invoiceItem.=$this->cObj->substituteMarkerArray($subparts['invoices_listing'], $markerArray, '###|###');
}
// pagination
if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
	$pagination_listing=$tmp;
}
// pagination eof
$actions=array();
$actions['create_reversal_invoice']=$this->pi_getLL('create_reversal_invoice_for_selected_invoices');
$actions['mail_invoices']=$this->pi_getLL('mail_selected_invoices');
$actions['update_selected_invoices_to_paid']=$this->pi_getLL('update_selected_invoices_to_paid');
$actions['update_selected_invoices_to_not_paid']=$this->pi_getLL('update_selected_invoices_to_not_paid');
// extra action
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_invoices.php']['adminInvoicesActionSelectboxProc'])) {
	$params=array('actions'=>&$actions);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_invoices.php']['adminInvoicesActionSelectboxProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$form_fields_listing_block='
<div class="form-group"><div class="input-group"><select name="tx_multishop_pi1[action]" id="selected_invoices_action" class="form-control">
<option value="">'.$this->pi_getLL('choose_action').'</option>
';
foreach ($actions as $key=>$value) {
	$form_fields_listing_block.='<option value="'.$key.'">'.$value.'</option>';
}
$form_fields_listing_block.='
	</select>
	<span class="input-group-btn">
	<input name="tx_multishop_pi1[mailto]" type="text" value="'.$this->ms['MODULES']['STORE_EMAIL'].'" id="msadmin_invoices_mailto" />
	<input class="btn btn-success" type="submit" name="submit" value="'.$this->pi_getLL('submit').'" ></input>
	</span>
	</div>
	</div>';
$master_shop='';
if ($this->masterShop) {
	$master_shop='<th width="75">'.$this->pi_getLL('store').'</th>';
}
$subpartArray=array();
$subpartArray['###HEADER_INVOICES_NUMBER###']=$this->pi_getLL('invoice_number');
$subpartArray['###HEADER_INVOICES_ORDER_ID###']=$this->pi_getLL('orders_id');
$subpartArray['###HEADER_MASTER_SHOP###']=$master_shop;
$subpartArray['###HEADER_INVOICES_CUSTOMER###']=$this->pi_getLL('customers');
$subpartArray['###HEADER_INVOICES_ORDER_DATE###']=$this->pi_getLL('order_date');
$subpartArray['###HEADER_INVOICES_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
$subpartArray['###HEADER_INVOICES_PAYMENT_CONDITION###']=$this->pi_getLL('payment_condition');
$subpartArray['###HEADER_INVOICES_AMOUNT###']=$this->pi_getLL('amount');
$subpartArray['###HEADER_INVOICES_DATE_LAST_SENT###']=$this->pi_getLL('date_last_sent');
$subpartArray['###HEADER_INVOICES_PAID_STATUS###']=$this->pi_getLL('admin_paid');
$subpartArray['###HEADER_INVOICES_ACTION###']=$this->pi_getLL('action');
//
$subpartArray['###PAGINATION###']=$pagination_listing;
$subpartArray['###INVOICES_LISTING###']=$invoiceItem;
$subpartArray['###FORM_FIELDS_LISTING_ACTION_BLOCK###']=$form_fields_listing_block;
//
$subpartArray['###FOOTER_INVOICES_NUMBER###']=$this->pi_getLL('invoice_number');
$subpartArray['###FOOTER_INVOICES_ORDER_ID###']=$this->pi_getLL('orders_id');
$subpartArray['###FOOTER_MASTER_SHOP###']=$master_shop;
$subpartArray['###FOOTER_INVOICES_CUSTOMER###']=$this->pi_getLL('customers');
$subpartArray['###FOOTER_INVOICES_ORDER_DATE###']=$this->pi_getLL('order_date');
$subpartArray['###FOOTER_INVOICES_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
$subpartArray['###FOOTER_INVOICES_PAYMENT_CONDITION###']=$this->pi_getLL('payment_condition');
$subpartArray['###FOOTER_INVOICES_AMOUNT###']=mslib_fe::amount2Cents($totalAmount, 0);
$subpartArray['###FOOTER_INVOICES_DATE_LAST_SENT###']=$this->pi_getLL('date_last_sent');
$subpartArray['###FOOTER_INVOICES_PAID_STATUS###']=$this->pi_getLL('admin_paid');
$subpartArray['###FOOTER_INVOICES_ACTION###']=$this->pi_getLL('action');
$subpartArray['###CUSTOM_MARKER_1_HEADER###']='';
$subpartArray['###CUSTOM_MARKER_1_FOOTER###']='';
//
$invoices_results=$this->cObj->substituteMarkerArrayCached($subparts['invoices_results'], array(), $subpartArray);
?>