<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if ($this->get['tx_multishop_pi1']['is_proposal']) {
	$page_type='proposals';
} else {
	$page_type='orders';
}
$counter 	= 0;
$tr_type 	= 'even';

$cb_ctr = 0;
$orderItem = '';
foreach ($tmporders as $order) {
	$edit_order_popup_width = 980;
	if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
		$edit_order_popup_width += 70;
	}
	if ($this->ms['MODULES']['ORDER_EDIT'] && !$order['is_locked']) {
		if ($edit_order_popup_width > 980) {
			$edit_order_popup_width += 155;
		} else {
			$edit_order_popup_width += 70;
		}
	}
	
	//	$order=mslib_fe::getOrder($order_row['orders_id']);
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';			
	}
	if ($this->masterShop) {
		$master_shop_col ='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($order['page_uid']).'</td>';
	}
	if ($order['billing_company']) {
		$customer_name=$order['billing_company'];
	} else {
		$customer_name=$order['billing_name'];
	}
	
	$markerArray=array();
	$markerArray['ROW_TYPE'] 				= $tr_type;
	$markerArray['CUSTOMER_NAME'] 	= $customer_name;
	$markerArray['IPADDRESS'] 		= $order['ip_address'];
	$markerArray['USERAGENTS'] 		= $order['user_agent'];
	// custom page hook that can be controlled by third-party plugin eof	
	$orderItem .= $this->cObj->substituteMarkerArray($subparts['useragents_listing'], $markerArray,'###|###');
}

$actions=array();
$formFields=array();

$query_string=mslib_fe::tep_get_all_get_params(array('tx_multishop_pi1[action]','tx_multishop_pi1[order_by]','tx_multishop_pi1[order]','p','Submit','weergave','clearcache'));

$subpartArray = array();
$subpartArray['###LABEL_HEADER_CUSTOMER###'] 	= $this->pi_getLL('customer');
$subpartArray['###LABEL_HEADER_IPADDRESS###'] 	= $this->pi_getLL('ip_address');
$subpartArray['###LABEL_HEADER_USERAGENTS###'] 	= $this->pi_getLL('user_agent', 'user agents');
$subpartArray['###LABEL_FOOTER_CUSTOMER###'] 	= $this->pi_getLL('customer');
$subpartArray['###LABEL_FOOTER_IPADDRESS###'] 	= $this->pi_getLL('ip_address');
$subpartArray['###LABEL_FOOTER_USERAGENTS###'] 	= $this->pi_getLL('user_agent', 'user agents');


$pagination_listing = '';
// pagination
if (!$this->ms['nopagenav'] and $pageset['total_rows'] > $this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
	$tmp = '';
	
	$total_pages = ceil(($pageset['total_rows']/$this->ms['MODULES']['ORDERS_LISTING_LIMIT']));
	
	$tmp.='<div id="pagenav_container_list_wrapper">
<ul id="pagenav_container_list">
<li class="pagenav_first">';
	if($p > 0) {
		$tmp .= '<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_stats_user_agent&'.mslib_fe::tep_get_all_get_params(array('p','Submit','tx_multishop_pi1[action]','clearcache'))).'">'.$this->pi_getLL('first').'</a></div>';
	} else {
		$tmp.='<span>&nbsp;</span>';
	}
	$tmp.='</li>';
	
	$tmp .= '<li class="pagenav_previous">';
	if($p > 0) {
		if (($p-1) > 0) {
			$tmp .= '<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_stats_user_agent&p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array('p','Submit','tx_multishop_pi1[action]','clearcache'))).'">'.$this->pi_getLL('previous').'</a></div>';
		} else {
			$tmp .= '<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_stats_user_agent&p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array('p','Submit','tx_multishop_pi1[action]','clearcache'))).'">'.$this->pi_getLL('previous').'</a></div>';
		}
	
	} else {
		$tmp.='<span>&nbsp;</span>';
	}
	$tmp.='</li>';
	
	if ($p == 0 || $p < 9) {
		$start_page_number 		= 1;
	
		if ($total_pages <= 10) {
			$end_page_number 	= $total_pages;
		} else {
			$end_page_number 	= 10;
		}
	
	} else if ($p >= 9) {
		$start_page_number 	= ($p - 5) + 1;
		$end_page_number 	= ($p + 4) + 1;
	
		if ($end_page_number > $total_pages) {
			$end_page_number = $total_pages;
		}
	}
	
	$tmp .= '<li class="pagenav_number">
<ul id="pagenav_number_wrapper">';
	for ($x = $start_page_number; $x <= $end_page_number; $x++) {
		if (($p+1) == $x) {
			$tmp.= '<li><div class="dyna_button"><span>'.$x.'</span></a></li>';
		} else {
			$tmp.= '<li><div class="dyna_button"><a class="ajax_link pagination_button" href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_stats_user_agent&p='.($x - 1).'&'.mslib_fe::tep_get_all_get_params(array('p','Submit','page','mini_foto','clearcache'))).'">'.$x.'</a></div></li>';
		}
	}
	$tmp.='</ul>
</li>';
	
	$tmp .= '<li class="pagenav_next">';
	if((($p+1)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) < $pageset['total_rows']) {
		$tmp .= '<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_stats_user_agent&p='.($p+1).'&'.mslib_fe::tep_get_all_get_params(array('p','Submit','tx_multishop_pi1[action]','clearcache'))).'">'.$this->pi_getLL('next').'</a></div>';
	
	} else {
		$tmp.='<span>&nbsp;</span>';
	}
	$tmp.='</li>';
	
	$tmp .= '<li class="pagenav_last">';
	if((($p+1)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT']) < $pageset['total_rows']) {
		$lastpage = floor(($pageset['total_rows']/$this->ms['MODULES']['ORDERS_LISTING_LIMIT']));
	
		if ($lastpage*$this->ms['MODULES']['ORDERS_LISTING_LIMIT']==$pageset['total_rows']) {
			$lastpage--;
		}
	
		$tmp.= '<div class="dyna_button"><a class="pagination_button" href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_stats_user_agent&p='.$lastpage.'&'.mslib_fe::tep_get_all_get_params(array('p','Submit','tx_multishop_pi1[action]','clearcache'))).'">'.$this->pi_getLL('last').'</a></div>';
	
	} else {
		$tmp.='<span>&nbsp;</span>';
	}
	$tmp .= '</li>';
	$tmp .= '</ul></div>';
	
	$pagination_listing = $tmp;
}
// pagination eof
$subpartArray['###DOWNLOAD_EXCEL###'] = mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_useragent_export');
$subpartArray['###PAGINATION###'] 							= $pagination_listing;
$subpartArray['###ORDERS_LISTING###'] 	= $orderItem;	
// custom page hook that can be controlled by third-party plugin eof
$order_results = $this->cObj->substituteMarkerArrayCached($subparts['useragents_results'], array(), $subpartArray);
?>