<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_customers_listing_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_customers_listing_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_customers_listing.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['customers']=$this->cObj->getSubpart($subparts['template'], '###CUSTOMERS###');
$contentItem='';
foreach ($customers as $customer) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	if ($this->masterShop) {
		$master_shop_col='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($customer['page_uid']).'</td>';
	}
	if (!$customer['name']) {
		$customer['name']=$customer['last_name'];
	}
	if (!$customer['name']) {
		$customer['name']=$customer['username'];
	}
	if ($customer['company']>0) {
		$name=$customer['company'];
	} else {
		$name=$customer['name'];
	}
	if ($customer['lastlogin']) {
		$customer['lastlogin']=strftime("%x %X", $customer['lastlogin']);
	} else {
		$customer['lastlogin']='';
	}
	if ($customer['crdate']>0) {
		$customer['crdate']=strftime("%x %X", $customer['crdate']);
	} else {
		$customer['crdate']='';
	}
	$customer_edit_link=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&tx_multishop_pi1[cid]='.$customer['uid'].'&action=edit_customer',1);
	$latest_order='';
	$str="select orders_id from tx_multishop_orders where customer_id='".$customer['uid']."' and deleted=0 order by orders_id desc limit 2";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	if ($rows>0) {
		$order=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$latest_order.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order', 1).'">'.$order['orders_id'].'</a>'."\n";
		if ($rows>1) {
			$latest_order.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_orders&type_search=customer_id&skeyword='.$customer['uid']).'">('.htmlspecialchars($this->pi_getLL('show_all')).')</a>';
		}
	} else {
		$latest_order.='&nbsp;';
	}
	$status_html='';
	if (!$customer['disable']) {
		$link=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_id='.$customer['uid'].'&disable=1&'.mslib_fe::tep_get_all_get_params(array(
				'customer_id',
				'disable',
				'clearcache'
			)));
		$status_html.='<a href="'.$link.'"><span class="admin_status_red_disable" alt="'.htmlspecialchars($this->pi_getLL('disabled')).'"></span></a>';
		$status_html.='<span class="admin_status_green" alt="'.htmlspecialchars($this->pi_getLL('enable')).'"></span>';
	} else {
		$link=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_id='.$customer['uid'].'&disable=0&'.mslib_fe::tep_get_all_get_params(array(
				'customer_id',
				'disable',
				'clearcache'
			)));
		$status_html.='<span class="admin_status_red" alt="'.htmlspecialchars($this->pi_getLL('disable')).'"></span>';
		$status_html.='<a href="'.$link.'"><span class="admin_status_green_disable" alt="'.htmlspecialchars($this->pi_getLL('enabled')).'"></span></a>';
	}
	$markerArray=array();
	$markerArray['ROW_TYPE']=$tr_type;
	$markerArray['CUSTOMERS_UID']=$customer['uid'];
	$markerArray['CUSTOMERS_EDIT_LINK']=$customer_edit_link;
	$markerArray['CUSTOMERS_USERNAME']=$customer['username'];
	$markerArray['CUSTOMERS_COMPANY']=$customer['company'];
	$markerArray['CUSTOMERS_NAME']=$name;
	$markerArray['CUSTOMERS_CREATED']=$customer['crdate'];
	$markerArray['CUSTOMERS_LATEST_LOGIN']=$customer['lastlogin'];
	$markerArray['CUSTOMERS_LATEST_ORDER']=$latest_order;
	$markerArray['CUSTOMERS_TURN_OVER']=mslib_fe::amount2Cents($customer['grand_total'], 0);
	$markerArray['CUSTOMERS_TURN_OVER_THIS_YEAR']=mslib_fe::amount2Cents($customer['grand_total_this_year'], 0);
	$markerArray['CUSTOMERS_LOGINAS_LINK']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&login_as_customer=1&customer_id='.$customer['uid']);
	$markerArray['CUSTOMERS_LOGINAS']=htmlspecialchars($this->pi_getLL('login'));
	$markerArray['CUSTOMERS_STATUS']=$status_html;
	$markerArray['CUSTOMERS_DELETE_LINK']=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_id='.$customer['uid'].'&delete=1&'.mslib_fe::tep_get_all_get_params(array(
			'customer_id',
			'delete',
			'disable',
			'clearcache'
		)));
	$markerArray['CUSTOMERS_ONCLICK_DELETE_CONFIRM_JS']='return confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')';
	$markerArray['ADMIN_LABEL_ALT_REMOVE']=ucfirst($this->pi_getLL('admin_label_alt_remove'));
	$markerArray['MASTER_SHOP']=$master_shop_col;
	// custom page hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customers_listing.php']['adminCustomersListingTmplIteratorPreProc'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'customer'=>&$customer
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customers_listing.php']['adminCustomersListingTmplIteratorPreProc'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['customers'], $markerArray, '###|###');
}
$subpartArray=array();
$query_string=mslib_fe::tep_get_all_get_params(array(
	'tx_multishop_pi1[action]',
	'tx_multishop_pi1[order_by]',
	'tx_multishop_pi1[order]',
	'p',
	'Submit',
	'weergave',
	'clearcache'
));
$key='uid';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_ID###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('admin_customer_id')).'</a>';
$key='username';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_USERNAME###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('username')).'</a>';
$key='company';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_COMPANY###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('company')).'</a>';
$key='crdate';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_CREATED###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('created')).'</a>';
$key='lastlogin';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_LATEST_LOGIN###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('latest_login')).'</a>';
$key='disable';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_STATUS###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('status')).'</a>';
$key='grand_total';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_TURN_OVER###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('turn_over', 'Turn over')).'</a>';
$key='grand_total_this_year';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_CUSTOMER_TURN_OVER_THIS_YEAR###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.ucfirst($this->pi_getLL('turn_over_this_year', 'Turn over (this year)')).'</a>';
$subpartArray['###LABEL_CUSTOMER_NAME###']=ucfirst($this->pi_getLL('name'));
$subpartArray['###LABEL_CUSTOMER_LATEST_ORDER###']=ucfirst($this->pi_getLL('latest_order'));
$subpartArray['###LABEL_CUSTOMER_LOGIN_AS_USER###']=ucfirst($this->pi_getLL('login_as_user'));
$subpartArray['###LABEL_CUSTOMER_DELETE###']=ucfirst($this->pi_getLL('delete'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_ID###']=ucfirst($this->pi_getLL('admin_customer_id'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_USERNAME###']=ucfirst($this->pi_getLL('username'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_COMPANY###']=ucfirst($this->pi_getLL('company'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_NAME###']=ucfirst($this->pi_getLL('name'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_CREATED###']=ucfirst($this->pi_getLL('created'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_LATEST_LOGIN###']=ucfirst($this->pi_getLL('latest_login'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_LATEST_ORDER###']=ucfirst($this->pi_getLL('latest_order'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_TURN_OVER###']=ucfirst($this->pi_getLL('turn_over', 'Turn over'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_TURN_OVER_THIS_YEAR###']=ucfirst($this->pi_getLL('turn_over_this_year', 'Turn over (this year)'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_LOGIN_AS_USER###']=ucfirst($this->pi_getLL('login_as_user'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_STATUS###']=ucfirst($this->pi_getLL('status'));
$subpartArray['###LABEL_FOOTER_CUSTOMER_DELETE###']=ucfirst($this->pi_getLL('delete'));
$subpartArray['###CUSTOMERS###']=$contentItem;
$master_shop_header='';
if ($this->masterShop) {
	$master_shop_header='<th width="75" class="cell_store">'.$this->pi_getLL('store').'</th>';
}
$subpartArray['###HEADER_MASTER_SHOP###']=$master_shop_header;
$subpartArray['###FOOTER_MASTER_SHOP###']=$master_shop_header;
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/customers/customers_listing.php']['adminCustomersListingTmplPreProc'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'customer'=>&$customer
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/customers/customers_listing.php']['adminCustomersListingTmplPreProc'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom page hook that can be controlled by third-party plugin eof
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
?>