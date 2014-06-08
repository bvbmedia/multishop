<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
// now parse all the objects in the tmpl file
if ($this->conf['admin_customer_groups_listing_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_customer_groups_listing_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_customer_groups_listing.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['groups']=$this->cObj->getSubpart($subparts['template'], '###GROUPS###');
$contentItem='';
foreach ($groups as $group) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	if (!isset($group['tx_multishop_discount'])) {
		$group['tx_multishop_discount']=0;
	}
	$group['tx_multishop_discount'].='%';
	$link='';
	$status_html='';
	if (!$group['hidden']) {
		$link=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_group_id='.$group['uid'].'&disable=1&'.mslib_fe::tep_get_all_get_params(array(
				'customer_group_id',
				'disable',
				'clearcache'
			)));
		$status_html.='<a href="'.$link.'"><span class="admin_status_red_disable"  alt="disable group" title="disable group"></span></a>';
		$status_html.='<span class="admin_status_green" alt="group is enabled" title="group is enabled"></span>';
	} else {
		$link=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_group_id='.$group['uid'].'&disable=0&'.mslib_fe::tep_get_all_get_params(array(
				'customer_group_id',
				'disable',
				'clearcache'
			)));
		$status_html.='<span class="admin_status_red"  alt="group is disabled" title="group is disabled"></span>';
		$status_html.='<a href="'.$link.'"><span class="admin_status_green_disable" alt="enable group" title="enable group"></span></a>';
	}
	$markerArray=array();
	$markerArray['ROW_TYPE']=$tr_type;
	$markerArray['VALUE_GROUP_ID']=$group['uid'];
	$markerArray['VALUE_GROUP_EDIT_LINK']=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&customer_group_id='.$group['uid']).'&action=edit_customer_group';
	$markerArray['VALUE_GROUP_NAME']=$group['title'];
	$markerArray['VALUE_GROUP_DISCOUNT']=$group['tx_multishop_discount'];
	$markerArray['VALUE_GROUP_STATUS']=$status_html;
	$markerArray['VALUE_GROUP_DELETE_LINK']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_group_id='.$group['uid'].'&delete=1&'.mslib_fe::tep_get_all_get_params(array(
			'customer_group_id',
			'delete',
			'disable',
			'clearcache'
		)));
	// custom page hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customer_groups_listing.php']['adminCustomerGroupsListingTmplIteratorPreProc'])) {
		$params=array(
			'markerArray'=>&$markerArray,
			'group'=>&$group
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customer_groups_listing.php']['adminCustomerGroupsListingTmplIteratorPreProc'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom page hook that can be controlled by third-party plugin eof	
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['groups'], $markerArray, '###|###');
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
$subpartArray['###LABEL_HEADER_ID###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customer_groups&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('id').'</a>';
$key='name';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_HEADER_NAME###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customer_groups&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('name').'</a>';
$subpartArray['###LABEL_HEADER_BUDGET_USAGE###']='Budget usage';
$key='discount';
if ($this->get['tx_multishop_pi1']['order_by']==$key) {
	$final_order_link=$order_link;
} else {
	$final_order_link='a';
}
$subpartArray['###LABEL_HEADER_DISCOUNT###']='<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customer_groups&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('discount').'</a>';
$subpartArray['###LABEL_HEADER_STATUS###']=$this->pi_getLL('status');
$subpartArray['###LABEL_HEADER_DELETE###']=ucfirst($this->pi_getLL('delete'));
$subpartArray['###GROUPS###']=$contentItem;
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customer_groups_listing.php']['adminCustomerGroupsListingTmplPreProc'])) {
	$params=array(
		'subpartArray'=>&$subpartArray
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customer_groups_listing.php']['adminCustomerGroupsListingTmplPreProc'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom page hook that can be controlled by third-party plugin eof	
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
?>