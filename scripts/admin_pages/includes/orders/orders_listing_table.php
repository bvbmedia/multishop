<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->get['tx_multishop_pi1']['is_proposal']) {
	$page_type='proposals';
} else {
	$page_type='orders';
}
$counter=0;
$tr_type='even';
$cb_ctr=0;
$orderItem='';
foreach ($tmporders as $order) {
	$grandTotalColumnName='grand_total';
	if (isset($this->get['tx_multishop_pi1']['excluding_vat'])) {
		$grandTotalColumnName='grand_total_excluding_vat';
	}
	$edit_order_popup_width=980;
	if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS']>0) {
		$edit_order_popup_width+=70;
	}
	if ($this->ms['MODULES']['ORDER_EDIT'] && !$order['is_locked']) {
		if ($edit_order_popup_width>980) {
			$edit_order_popup_width+=155;
		} else {
			$edit_order_popup_width+=70;
		}
	}
	//	$order=mslib_fe::getOrder($order_row['orders_id']);
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$master_shop_col='';
	if ($this->masterShop) {
		$master_shop_col='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($order['page_uid']).'</td>';
	}
	$customerNameArray=array();
	if ($order['billing_company']) {
		$customerNameArray[]=$order['billing_company'];
	}
	$customerNameArray[]=$order['billing_name'];

	$order_status_selectbox='<select name="orders_status" class="form-control change_orders_status" rel="'.$order['orders_id'].'" id="orders_'.$order['orders_id'].'">
		<option value="">'.$this->pi_getLL('choose').'</option>';
	if (is_array($all_orders_status)) {
		foreach ($all_orders_status as $item) {
			$order_status_selectbox.='<option value="'.$item['id'].'"'.($item['id']==$order['status'] ? ' selected' : '').'>'.$item['name'].'</option>'."\n";
		}
	}
	$order_status_selectbox.='</select>';
	$paid_status='';
	if (!$order['paid']) {
		$paid_status.='<span class="admin_status_red" alt="'.$this->pi_getLL('has_not_been_paid').'" title="'.$this->pi_getLL('has_not_been_paid').'"></span> ';
		$paid_status.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_orders_to_paid&selected_orders[]='.$order['orders_id']).'" class="update_to_paid" data-order-id="'.$order['orders_id'].'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('change_to_paid').'" title="'.$this->pi_getLL('change_to_paid').'"></span></a>';
	} else {
		$paid_status.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_orders_to_not_paid&selected_orders[]='.$order['orders_id']).'" class="update_to_unpaid" data-order-id="'.$order['orders_id'].'"><span class="admin_status_red disabled" alt="'.$this->pi_getLL('change_to_not_paid').'" title="'.$this->pi_getLL('change_to_not_paid').'"></span></a> ';
		$paid_status.='<span class="admin_status_green" alt="'.$this->pi_getLL('has_been_paid').'" title="'.$this->pi_getLL('has_been_paid').'"></span>';
	}
	$orderlist_buttons=array();
	switch ($page_type) {
		case 'proposals':
			$orderlist_buttons['mail_order']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=mail_order&orders_id='.$order['orders_id'].'&action=mail_order', 1).'" rel="email" class="btn btn-primary btn-sm">'.htmlspecialchars($this->pi_getLL('email')).'</a>';
			$orderlist_buttons['convert_to_order']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_id='.$order['orders_id'].'&tx_multishop_pi1[action]=convert_to_order').'" class="btn btn-primary btn-sm">'.htmlspecialchars($this->pi_getLL('convert_to_order')).'</a>';
			break;
		case 'orders':
			if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {
				if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
					$orderlist_buttons['invoice']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order&print=invoice', 1).'" class="btn btn-primary btn-sm"'.($this->ms['MODULES']['INVOICE_PDF_DIRECT_LINK_FROM_ORDERS_LISTING'] ? ' target="_blank"' : '').'>'.htmlspecialchars($this->pi_getLL('invoice')).'</a>';
				}
				if ($this->ms['MODULES']['PACKING_LIST_PRINT']) {
					if ($this->ms['MODULES']['PACKINGSLIP_PDF_DIRECT_LINK_FROM_ORDERS_LISTING']) {
						$orderlist_buttons['pakbon']='<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_packingslip&tx_multishop_pi1[order_id]='.$order['orders_id']).'" class="btn btn-primary btn-sm" target="_blank">'.htmlspecialchars($this->pi_getLL('packing_list')).'</a>';
					} else {
						$orderlist_buttons['pakbon']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order&print=packing', 1).'" class="btn btn-primary btn-sm">'.htmlspecialchars($this->pi_getLL('packing_list')).'</a>';
					}
				}
			}
			break;
	}
	// extra input jquery
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingButton'])) {
		$params=array(
			'orderlist_buttons'=>&$orderlist_buttons,
			'order'=>&$order
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingButton'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	$order_list_button_extra='';
	if (count($orderlist_buttons)) {
		//button area
		$order_list_button_extra.='<td class="cellActionFluid">';
		$order_list_button_extra.=implode(' ', $orderlist_buttons);
		$order_list_button_extra.='</td>';
	}
	$markerArray=array();
	$markerArray['ROW_TYPE']=$tr_type;
	$markerArray['ORDER_ID']=$order['orders_id'];

	$parentClasses=array();
	$parentClasses[]='fa-stack';

	$classes=array();
	if ($order['by_phone']) {
		$parentClasses[]='text-orange';
	} else {
		if ($order['customer_comments']) {
			// Show red indicator icon to make merchant aware that order contains: customer comments
			$parentClasses[]='text-danger';
		} else {
			$parentClasses[]='text-info';
		}
	}
	if ($order['by_phone']) {
		$classes[]='fa fa-phone fa-stack-1x fa-inverse';
		$markerArray['MANUAL_ORDER_ICON']='<span class="'.implode(' ',$parentClasses).'"><i class="fa fa-circle fa-stack-2x fa-circle-phone"></i><i class="'.implode(' ',$classes).'"></i></span>';
	} else {
		$classes[]='fa fa-cart-plus fa-stack-1x fa-inverse';
		$markerArray['MANUAL_ORDER_ICON']='<span class="'.implode(' ',$parentClasses).'"><i class="fa fa-circle fa-stack-2x fa-circle-cart-plus"></i><i class="'.implode(' ',$classes).'"></i></span>';
	}
	$markerArray['INVOICE_NUMBER']='';
	if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
		$markerArray['INVOICE_NUMBER']='<td align="right">';
		$filter=array();
		$invoices=mslib_befe::getRecords($order['orders_id'], 'tx_multishop_invoices i', 'i.orders_id', $filter);
		$links=array();
		if (is_array($invoices) && count($invoices)) {
			foreach ($invoices as $invoice) {
				$invoice=mslib_fe::getInvoice($invoice['id'], 'id');
				$links[]='<a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'" target="_blank">'.$invoice['invoice_id'].'</a>';
			}
		}
		if (count($links)) {
			$markerArray['INVOICE_NUMBER'].=implode('<br/>', $links);
		}
		$markerArray['INVOICE_NUMBER'].='</td>';
	}
	if (isset($this->get['tx_multishop_pi1']['is_proposal'])) {
		$markerArray['ORDER_EDIT_URL']=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order&tx_multishop_pi1[is_proposal]=1');
	} else {
		$markerArray['ORDER_EDIT_URL']=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order');
	}
	$markerArray['EDIT_ORDER_POPUP_WIDTH']=$edit_order_popup_width;
	$markerArray['LABEL_LOADING']=htmlspecialchars($this->pi_getLL('loading'));
	//$markerArray['ORDER_TOOLTIP_DATA_URL']=mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=getAdminOrdersListingDetails&tx_multishop_pi1[orders_id]='.$order['orders_id']);
	$markerArray['ORDER_CUSTOMER_NAME']=implode('<br/>',$customerNameArray);
	$markerArray['ORDER_CREATE_DATE']='<a href="#" data-toggle="tooltip" class="btn-memo btn btn-default btn" data-title="'.htmlspecialchars(strftime("%a. %x<br/>%X", $order['crdate'])).'" data-original-title="" title="">'.strftime("%x", $order['crdate']).'</a>';


	$markerArray['ORDER_GRAND_TOTAL']=mslib_fe::amount2Cents($order[$grandTotalColumnName], 0);
	$markerArray['ORDER_SHIPPING_METHOD']=$order['shipping_method_label'];
	$markerArray['ORDER_PAYMENT_METHOD']=$order['payment_method_label'];
	$markerArray['ORDER_STATUS']=$order_status_selectbox;

	$markerArray['ORDER_LAST_MODIFIED']='';
	if ($order['status_last_modified']) {
		$markerArray['ORDER_LAST_MODIFIED']='<a href="#" data-toggle="tooltip" class="btn-memo btn btn-default btn" data-title="'.htmlspecialchars(strftime("%a. %x<br/>%X", $order['status_last_modified'])).'" data-original-title="" title="">'.strftime("%x", $order['status_last_modified']).'</a>';
	}

	$markerArray['ORDER_PAID_STATUS']=$paid_status;
	$markerArray['PRINT_ORDER_LIST_BUTTON']=$order_list_button_extra;
    $markerArray['PLUGIN_EXTRA_CONTENT']='';
	$markerArray['MASTER_SHOP']=$master_shop_col;
	// custom page hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/orders/orders_listing_table.php']['adminOrdersListingTmplIteratorPreProc'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'order'=>&$order
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/orders/orders_listing_table.php']['adminOrdersListingTmplIteratorPreProc'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom page hook that can be controlled by third-party plugin eof
	$orderItem.=$this->cObj->substituteMarkerArray($subparts['orders_listing'], $markerArray, '###|###');
}
$actions=array();
$actions['delete_selected_orders']=$this->pi_getLL('delete_selected_orders');
$actions['change_order_status_for_selected_orders']=$this->pi_getLL('change_order_status_for_selected_orders');
$actions['update_selected_orders_to_paid']=$this->pi_getLL('update_selected_orders_to_paid');
$actions['update_selected_orders_to_not_paid']=$this->pi_getLL('update_selected_orders_to_not_paid');
$actions['mail_selected_orders_to_customer']=$this->pi_getLL('mail_selected_orders_to_customer', 'Mail selected orders to customer');
$actions['mail_selected_orders_to_merchant']=$this->pi_getLL('mail_selected_orders_to_merchant', 'Mail selected orders to merchant');
$actions['export_selected_order_to_xls']=$this->pi_getLL('export_selected_order_to_xls', 'Export selected orders to Excel');
$actions['mail_selected_orders_for_payment_reminder']=$this->pi_getLL('mail_selected_orders_for_payment_reminder', 'Mail selected orders for payment reminder');
$actions['create_invoice_for_selected_orders']=$this->pi_getLL('create_invoice_for_selected_orders', 'Create invoice for selected orders');
$actions['download_selected_orders_packingslips_in_one_pdf']=$this->pi_getLL('download_selected_orders_packingslips_in_one_pdf', 'Download selected orders packingslips in one PDF file');
// extra action
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionSelectboxProc'])) {
	$params=array('actions'=>&$actions);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionSelectboxProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$formFields=array();
$formFields['orders_list_action']='
<select name="tx_multishop_pi1[action]" id="selected_orders_action" class="form-control">
<option value="">'.$this->pi_getLL('choose_action').'</option>';
foreach ($actions as $key=>$value) {
	//$tmp.='<option value="'.$key.'"'. ($this->get['tx_multishop_pi1']['action']==$key?' selected':'').'>'.$value.'</option>';
	$formFields['orders_list_action'].='<option value="'.$key.'">'.$value.'</option>';
}
$formFields['orders_list_action'].='</select>';
$formFields['update_to_order_status']='<select name="tx_multishop_pi1[update_to_order_status]" id="msadmin_order_status_select" class="form-control"><option value="">'.$this->pi_getLL('choose').'</option>';
if (is_array($all_orders_status)) {
	foreach ($all_orders_status as $row) {
		$formFields['update_to_order_status'].='<option value="'.$row['id'].'" '.(($order['status']==$row['id']) ? 'selected' : '').'>'.$row['name'].'</option>'."\n";
	}
}
$formFields['update_to_order_status'].='</select>';
// extra input
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputProc'])) {
	$params=array('formFields'=>&$formFields);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$formFields['submit_button']='<input class="btn btn-success" type="submit" name="submit" value="'.$this->pi_getLL('submit_form').'" />';
$form_fields_block='';
foreach ($formFields as $key=>$formField) {
	$form_fields_block.='<div class="form-group msAdminOrdersFormField" id="msAdminOrdersFormField_'.$key.'">'.$formField.'</div>';
}
$query_string=mslib_fe::tep_get_all_get_params(array(
	'tx_multishop_pi1[action]',
	'tx_multishop_pi1[order_by]',
	'tx_multishop_pi1[order]',
	'p',
	'Submit',
	'weergave',
	'clearcache'
));
$subpartArray=array();
$subpartArray['###FORM_POST_ACTION_URL###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders');
$subpartArray['###SHOP_PID2###']=$this->shop_pid;

$key='orders_id';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###HEADER_SORTBY_LINK_ORDER_ID###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
//$subpartArray['###LABEL_HEADER_ORDER_ID###']=$this->pi_getLL('orders_id');
$subpartArray['###LABEL_HEADER_ORDER_ID###']=$this->pi_getLL('id');
$subpartArray['###FOOTER_SORTBY_LINK_ORDER_ID###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
//$subpartArray['###LABEL_FOOTER_ORDER_ID###']=$this->pi_getLL('orders_id');
$subpartArray['###LABEL_FOOTER_ORDER_ID###']=$this->pi_getLL('id');
$master_shop_header='';
if ($this->masterShop) {
	$master_shop_header='<th width="75" class="cell_store">'.$this->pi_getLL('store').'</th>';
}
$subpartArray['###HEADER_INVOICE_NUMBER###']='';
$subpartArray['###FOOTER_INVOICE_NUMBER###']='';
if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
	$subpartArray['###HEADER_INVOICE_NUMBER###']='<th width="50" class="cell_invoice_id">'.$this->pi_getLL('invoice_number').'</th>';
	$subpartArray['###FOOTER_INVOICE_NUMBER###']='<th width="50" class="cell_invoice_id">'.$this->pi_getLL('invoice_number').'</th>';
}
$subpartArray['###HEADER_MASTER_SHOP###']=$master_shop_header;
$subpartArray['###FOOTER_MASTER_SHOP###']=$master_shop_header;
$subpartArray['###LABEL_HEADER_CUSTOMER###']=$this->pi_getLL('customer');
$subpartArray['###LABEL_FOOTER_CUSTOMER###']=$this->pi_getLL('customer');
$key='crdate';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###HEADER_SORTBY_LINK_ORDER_DATE###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_HEADER_ORDER_DATE###']=$this->pi_getLL('order_date');
$subpartArray['###FOOTER_SORTBY_LINK_ORDER_DATE###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_FOOTER_ORDER_DATE###']=$this->pi_getLL('order_date');
$key='grand_total';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###HEADER_SORTBY_LINK_AMOUNT###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_HEADER_AMOUNT###']=$this->pi_getLL('amount');
$subpartArray['###FOOTER_SORTBY_LINK_AMOUNT###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_FOOTER_AMOUNT###']=$this->pi_getLL('amount');
$key='shipping_method_label';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###HEADER_SORTBY_LINK_SHIPPING_METHOD###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_HEADER_SHIPPING_METHOD###']=$this->pi_getLL('shipping_method');
$subpartArray['###FOOTER_SORTBY_LINK_SHIPPING_METHOD###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_FOOTER_SHIPPING_METHOD###']=$this->pi_getLL('shipping_method');
$key='payment_method_label';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###HEADER_SORTBY_LINK_PAYMENT_METHOD###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_HEADER_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
$subpartArray['###FOOTER_SORTBY_LINK_PAYMENT_METHOD###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_FOOTER_PAYMENT_METHOD###']=$this->pi_getLL('payment_method');
$subpartArray['###LABEL_HEADER_STATUS###']=$this->pi_getLL('order_status');
$subpartArray['###LABEL_FOOTER_STATUS###']=$this->pi_getLL('order_status');
$key='status_last_modified';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###HEADER_SORTBY_LINK_MODIFIED###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_HEADER_MODIFIED###']=$this->pi_getLL('modified_on', 'Modified on');
$subpartArray['###FOOTER_SORTBY_LINK_MODIFIED###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
$subpartArray['###LABEL_FOOTER_MODIFIED###']=$this->pi_getLL('modified_on', 'Modified on');
$subpartArray['###LABEL_HEADER_PAID###']=$this->pi_getLL('admin_paid');
$subpartArray['###LABEL_FOOTER_PAID###']=$this->pi_getLL('admin_paid');
$extra_header='';
if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
	$extra_header='<th class="cellActionFluid">&nbsp;</th>';
}
if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] || $this->ms['MODULES']['PACKING_LIST_PRINT'] || $page_type=='proposals') {
	$extra_header='<th class="cellActionFluid">&nbsp;</th>';
}
$subpartArray['###EXTRA_RIGHT_HEADER###']=$extra_header;
$subpartArray['###EXTRA_RIGHT_FOOTER###']=$extra_header;
$subpartArray['###FORM_FIELDS_LISTING_ACTION_BLOCK###']=$form_fields_block;
//
$subpartArray['###PLUGIN_EXTRA_HEADER###']='';
$subpartArray['###PLUGIN_EXTRA_FOOTER###']='';
//
$pagination_listing='';
// pagination
$this->ms['MODULES']['PAGESET_LIMIT']=$this->ms['MODULES']['ORDERS_LISTING_LIMIT'];
if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
	$tmp='';
	//require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/orders/pagination.php');
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
	$pagination_listing=$tmp;
}
// pagination eof
$subpartArray['###PAGINATION###']=$pagination_listing;
$subpartArray['###ORDERS_LISTING###']=$orderItem;
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/orders/orders_listing_table.php']['adminOrdersListingTmplPreProc'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'order'=>&$order
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/orders/orders_listing_table.php']['adminOrdersListingTmplPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom page hook that can be controlled by third-party plugin eof
$order_results=$this->cObj->substituteMarkerArrayCached($subparts['orders_results'], array(), $subpartArray);
$headerData='';
$headerData.='
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(document).on("click", ".update_to_paid", function(e){
			e.preventDefault();
			var link=$(this).attr("href");
			var order_id=$(this).attr("data-order-id");
			var tthis=$(this).parent();
			jQuery.ajax({
				type: "POST",
				url: "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=get_order_payment_methods').'",
				dataType: \'json\',
				data: "tx_multishop_pi1[order_id]=" + order_id,
				success: function(d) {
					var tmp_confirm_content =\''.addslashes(sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_been_paid'), '%order_id%')).'\';
					var confirm_content = \'<div><h3 class="panel-title">\' + tmp_confirm_content . replace(\'%order_id%\', order_id) + \'</h3></div><div class="form-group" id="popup_order_wrapper_listing">\' + d.payment_method_date_purchased + \'</div>\';
					var confirm_box=jQuery.confirm({
						title: \'\',
						content: confirm_content,
						confirm: function(){
							var payment_id=this.$b.find("#payment_method_sb_listing").val();
							var date_paid=this.$b.find("#orders_paid_timestamp").val();
							//
							jQuery.ajax({
								type: "POST",
								url: "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=update_paid_status_save_popup_value').'",
								dataType: \'json\',
								data: "tx_multishop_pi1[payment_id]=" + payment_id + "&tx_multishop_pi1[date_paid]=" + date_paid + "&tx_multishop_pi1[order_id]=" + order_id + "&tx_multishop_pi1[action]=update_selected_orders_to_paid",
								success: function(d) {
									if (d.status=="OK") {
										var return_string = \'<a href="#" class="update_to_unpaid" data-order-id="\' + order_id + \'"><span class="admin_status_red disabled" alt="'.$this->pi_getLL('admin_label_disable').'"></span></a><span class="admin_status_green" alt="'.$this->pi_getLL('admin_label_enable').'"></span>\';
									    tthis.html(return_string);
									}
								}
							});
							//window.location =link;
						},
						cancel: function(){},
						confirmButton: \''.$this->pi_getLL('yes').'\',
						cancelButton: \''.$this->pi_getLL('no').'\',
						backgroundDismiss: false
					});
					confirm_box.$b.find("#orders_paid_timestamp_visual").datepicker({
						dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'yy/mm/dd').'",
						altField: "#orders_paid_timestamp",
						altFormat: "yy-mm-dd",
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						yearRange: "'.(date("Y")-15).':'.(date("Y")+2).'"
					});
				}
			});
		});
		$(document).on("click", ".update_to_unpaid", function(e){
			e.preventDefault();
			var link=$(this).attr("href");
			var order_id=$(this).attr("data-order-id");
			var tthis=$(this).parent();
			var tmp_confirm_content =\''.addslashes(sprintf($this->pi_getLL('admin_label_are_you_sure_that_invoice_x_has_not_been_paid'), '%order_id%')).'\';
			var confirm_content=\'<div class="confirm_to_unpaid_status">\' + tmp_confirm_content.replace(\'%order_id%\', order_id) + \'</div>\';
			//
			$.confirm({
				title: \'\',
				content: confirm_content,
				confirm: function(){
					jQuery.ajax({
						type: "POST",
						url: "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_edit_order&tx_multishop_pi1[admin_ajax_edit_order]=update_paid_status_save_popup_value').'",
						dataType: \'json\',
						data: "tx_multishop_pi1[order_id]=" + order_id + "&tx_multishop_pi1[action]=update_selected_orders_to_not_paid",
						success: function(d) {
							if (d.status=="OK") {
								var return_string = \'<span class="admin_status_red" alt="'.$this->pi_getLL('admin_label_disable').'"></span><a href="#" class="update_to_paid" data-order-id="\' + order_id + \'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('admin_label_enable').'"></span></a>\';
								tthis.html(return_string);
							}
						}
					});
				},
				cancel: function(){},
				confirmButton: \'Yes\',
    			cancelButton: \'NO\'
			});
		});
		$(\'.change_orders_status\').change(function(){
			var orders_id=$(this).attr("rel");
			var orders_status_id=$("option:selected", this).val();
			var orders_status_label=$("option:selected", this).text();
			var confirm_label="'.$this->pi_getLL('admin_label_js_do_you_want_to_change_orders_id_x_to_status_x').'";
			confirm_label=confirm_label.replace(\'OrdersId\', orders_id).replace(\'OrdersStatusLabel\', orders_status_label);
			if (confirm(confirm_label)) {
				var request = $.ajax({
						type:   "POST",
						url:    "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_update_orders_status') . '",
						dataType: \'json\',
						data:   "tx_multishop_pi1[orders_id]="+orders_id+"&tx_multishop_pi1[orders_status_id]="+orders_status_id,
						dataType: "html"
				});
				request.done(function(msg) {
				    if (msg) {
				        $("body").append(msg);
				    }
				});
			}
		});
		$(\'#selected_orders_action\').change(function(){
			if ($(this).val()==\'change_order_status_for_selected_orders\') {
				$("#msadmin_order_status_select").show();
			} else {
				$("#msadmin_order_status_select").hide();
			}';
// extra input jquery
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputJQueryProc'])) {
	$params=array('tmp'=>&$headerData);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputJQueryProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$headerData.='});
		'.($this->get['tx_multishop_pi1']['action']!='change_order_status_for_selected_orders' ? '$("#msadmin_order_status_select").hide();' : '').'
		var originalLeave = $.fn.popover.Constructor.prototype.leave;
		$.fn.popover.Constructor.prototype.leave = function(obj){
		  var self = obj instanceof this.constructor ? obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data(\'bs.\' + this.type)
		  var container, timeout;
		  originalLeave.call(this, obj);
		  if(obj.currentTarget) {
			container = $(obj.currentTarget).siblings(\'.popover\')
			timeout = self.timeout;
			container.one(\'mouseenter\', function(){
			  //We entered the actual popover – call off the dogs
			  clearTimeout(timeout);
			  //Let\'s monitor popover content instead
			  container.one(\'mouseleave\', function(){
				  $.fn.popover.Constructor.prototype.leave.call(self, self);
				  $(".popover-link").popover("hide");
			  });
			})
		  }
		};
		$(".popover-link").popover({
			position: "down",
			placement: \'bottom\',
			html: true,
			trigger:"hover",
			delay: {show: 20, hide: 200}
		});
		var tooltip_is_shown=\'\';
		$(\'.popover-link\').on(\'show.bs.popover, mouseover\', function () {
			var that=$(this);
			//$(".popover").remove();
			//$(".popover-link").popover(\'hide\');
			var orders_id=$(this).attr(\'rel\');
			//if (tooltip_is_shown != orders_id) {
				tooltip_is_shown=orders_id;
				$.ajax({
					type:   "POST",
					url:    \''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=getAdminOrdersListingDetails&').'\',
					data:   \'tx_multishop_pi1[orders_id]=\'+orders_id,
					dataType: "json",
					success: function(data) {
            			if (data.content!="") {
            				that.next().html(\'<div class="arrow"></div>\' + data.title + data.content);
            				//that.next().popover("show");
            				//$(that).popover(\'show\');
            			} else {
            				$(".popover").remove();
            			}
					}
				});
			//}
		});
		$(\'#check_all_1\').click(function(){
			//checkAllPrettyCheckboxes(this,$(\'.msadmin_orders_listing\'));
			$(\'td > div.checkbox > input:checkbox\').prop(\'checked\', this.checked);
		});
	});
</script>';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
$headerData='';
?>