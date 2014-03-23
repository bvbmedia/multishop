<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
// now parse all the objects in the tmpl file
if($this->conf['admin_edit_customer_group_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_edit_customer_group_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_edit_customer_group.tmpl');
}
// Extract the subparts from the template
if($this->post) {
	$insertArray=array();
	$insertArray['title']=$this->post['group_name'];
	$insertArray['pid']=$this->conf['fe_customer_pid'];
	$insertArray['tx_multishop_discount']=$this->post['discount'];
	// custom page hook that can be controlled by third-party plugin
	if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminUpdateCustomerGroupPreProc'])) {
		$params=array(
			'insertArray'=>&$insertArray,
			'customer_group_id'=>&$this->post['customer_group_id']);
		foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminUpdateCustomerGroupPreProc'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom page hook that can be controlled by third-party plugin eof	
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_groups', 'uid='.$this->post['customer_group_id'], $insertArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$users=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'name');
	foreach($users as $user) {
		// check if the user should be member or not
		if(in_array($user['uid'], $this->post['tx_multishop_pi1']['users'])) {
			$add_array=array();
			$remove_array=array();
			$add_array[]=$this->post['customer_group_id'];
			$group_string=mslib_fe::updateFeUserGroup($user['uid'], $add_array, $remove_array);
		} else {
			$add_array=array();
			$remove_array=array();
			$remove_array[]=$this->post['customer_group_id'];
			$group_string=mslib_fe::updateFeUserGroup($user['uid'], $add_array, $remove_array);
		}
	}
	echo '
	<script>
		parent.window.location.reload();
	</script>
	';
}
$group=mslib_fe::getGroup($this->get['customer_group_id'], 'uid');
$group['tx_multishop_remaining_budget']=round($group['tx_multishop_remaining_budget'], 13);
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['members_option']=$this->cObj->getSubpart($subparts['template'], '###MEMBERS_OPTION###');
// now lets load the users 
$users=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'name');
$contentItem='';
foreach($users as $user) {
	if(!$user['name']) {
		$user['name']=$user['username'];
	}
	$markerArray=array();
	$markerArray['LABEL_MEMBERS_USERID']=$user['uid'];
	$markerArray['LABEL_MEMBERS_NAME']=$user['name'];
	$markerArray['LABEL_MEMBERS_USERNAME']=$user['username'];
	if(mslib_fe::inUserGroup($this->get['customer_group_id'], $user['usergroup'])) {
		$markerArray['MEMBERS_SELECTED']=' selected="selected"';
	} else {
		$markerArray['MEMBERS_SELECTED']='';
	}
	$contentItem.=$this->cObj->substituteMarkerArray($subparts['members_option'], $markerArray, '###|###');
}
$subpartArray=array();
$subpartArray['###LABEL_HEADING###']=$this->pi_getLL('edit_group');
$subpartArray['###FORM_ACTION###']=mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&customer_group_id='.$_REQUEST['customer_group_id']);
$subpartArray['###CUSTOMER_GROUP_ID###']=$_REQUEST['customer_group_id'];
$subpartArray['###FORM_INPUT_ACTION###']=$_REQUEST['action'];
$subpartArray['###LABEL_NAME###']=$this->pi_getLL('name');
$subpartArray['###VALUE_GROUP_NAME###']=htmlspecialchars($group['title']);
$subpartArray['###LABEL_ADMIN_NO###']=$this->pi_getLL('admin_no');
$subpartArray['###LABEL_DISCOUN###']=$this->pi_getLL('discount');
$subpartArray['###VALUE_DISCOUNT###']=htmlspecialchars($group['tx_multishop_discount']);
$subpartArray['###LABEL_MEMBERS###']='MEMBERS';
$subpartArray['###MEMBERS_OPTION###']=$contentItem;
$subpartArray['###LABEL_BUTTON_SAVE###']=$this->pi_getLL('save');
// custom page hook that can be controlled by third-party plugin
if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminEditCustomerGroupTmplPreProc'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'group'=>&$group);
	foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminEditCustomerGroupTmplPreProc'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom page hook that can be controlled by third-party plugin eof	
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
?>