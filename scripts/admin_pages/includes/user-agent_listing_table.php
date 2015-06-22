<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
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
	if ($this->masterShop) {
		$master_shop_col='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($order['page_uid']).'</td>';
	}
	if ($order['billing_company']) {
		$customer_name=$order['billing_company'];
	} else {
		$customer_name=$order['billing_name'];
	}
	$markerArray=array();
	$markerArray['ROW_TYPE']=$tr_type;
	$markerArray['CUSTOMER_NAME']=$customer_name;
	$markerArray['CUSTOMER_NAME_LINK']=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&tx_multishop_pi1[cid]='.$order['customer_id'].'&action=edit_customer', 1);
	$markerArray['IP_ADDRESS']=$order['ip_address'];
	$markerArray['ORDERS_ID']=$order['orders_id'];
	$markerArray['USER_AGENT']=$order['user_agent'];
	// custom page hook that can be controlled by third-party plugin eof	
	$orderItem.=$this->cObj->substituteMarkerArray($subparts['useragents_listing'], $markerArray, '###|###');
}
$actions=array();
$formFields=array();
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
$subpartArray['###LABEL_HEADER_ORDERS_ID###']=$this->pi_getLL('orders_id');
$subpartArray['###LABEL_FOOTER_ORDERS_ID###']=$this->pi_getLL('orders_id');
$subpartArray['###LABEL_HEADER_CUSTOMER###']=$this->pi_getLL('customer');
$subpartArray['###LABEL_FOOTER_CUSTOMER###']=$this->pi_getLL('customer');
$subpartArray['###LABEL_HEADER_IP_ADDRESS###']=$this->pi_getLL('ip_address');
$subpartArray['###LABEL_FOOTER_IP_ADDRESS###']=$this->pi_getLL('ip_address');
$subpartArray['###LABEL_HEADER_USER_AGENT###']=$this->pi_getLL('user_agent', 'user agents');
$subpartArray['###LABEL_FOOTER_USER_AGENT###']=$this->pi_getLL('user_agent', 'user agents');
$subpartArray['###ADMIN_LABEL_LINK_DOWNLOAD_AS_EXCEL###']=$this->pi_getLL('admin_label_link_download_as_excel');
// pagination
$this->ms['MODULES']['PAGESET_LIMIT']=$this->ms['MODULES']['ORDERS_LISTING_LIMIT'];
if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['PAGESET_LIMIT']) {
	$tmp='';
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
	$pagination_listing=$tmp;
}
// pagination eof
$subpartArray['###DOWNLOAD_EXCEL###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_useragent_export');
$subpartArray['###PAGINATION###']=$pagination_listing;
$subpartArray['###ORDERS_LISTING###']=$orderItem;
// custom page hook that can be controlled by third-party plugin eof
$order_results=$this->cObj->substituteMarkerArrayCached($subparts['useragents_results'], array(), $subpartArray);
?>