<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='<script type="text/javascript">
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
</script>';
if (is_numeric($this->get['disable']) and is_numeric($this->get['customer_group_id'])) {
	$updateArray=array();
	$updateArray['hidden']=$this->get['disable'];
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_groups', 'uid=\''.$this->get['customer_group_id'].'\'', $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
} elseif (is_numeric($this->get['delete']) and is_numeric($this->get['customer_group_id'])) {
	$updateArray['deleted']=1;
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_groups', 'uid=\''.$this->get['customer_group_id'].'\'', $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
if ($this->post) {
	$erno=array();
	if (!$this->post['group_name']) {
		$erno[]=$this->pi_getLL('admin_label_group_name_is_not_defined');
	}
	if (!count($erno)) {
		$insertArray=array();
		$insertArray['title']=$this->post['group_name'];
		$insertArray['pid']=$this->conf['fe_customer_pid'];
		$insertArray['tstamp']=time();
		$insertArray['crdate']=time();
		// custom page hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customer_groups.php']['adminInsertCustomerGroupPreProc'])) {
			$params=array(
				'insertArray'=>&$insertArray
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_customer_groups.php']['adminInsertCustomerGroupPreProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom page hook that can be controlled by third-party plugin eof
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('fe_groups', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
}
$this->cObj->data['header']=$this->pi_getLL('groups');
$this->hideHeader=1;
$this->ms['MODULES']['ADMIN_CUSTOMERS_LISTING_LIMIT']=25;
if ($_REQUEST['skeyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['skeyword']=$_REQUEST['skeyword'];
	$this->get['skeyword']=trim($this->get['skeyword']);
	$this->get['skeyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['skeyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['skeyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['skeyword'], TRUE);
	$this->get['skeyword']=mslib_fe::RemoveXSS($this->get['skeyword']);
}
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p>0) {
	$offset=(((($p)*$this->ms['MODULES']['ADMIN_CUSTOMERS_LISTING_LIMIT'])));
} else {
	$p=0;
	$offset=0;
}
$user=$GLOBALS['TSFE']->fe_user->user;
$content='
<div class="main-heading"><h2>'.$this->pi_getLL('add_new_group').'</h2></div>
<form id="form1" name="form1" method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_groups').'">
	<div class="account-field">
		<label>'.$this->pi_getLL('name').'</label><input type="text" name="group_name" id="group_name" value="'.htmlspecialchars($this->post['group_name']).'" />
	</div>
	<div class="account-field">
		<label>Enable usage of budget</label>
		<input name="tx_multishop_pi1[budget_enabled]" type="radio" value="1" '.(($this->post['tx_multishop_pi1']['budget_enabled']) ? 'checked' : '').' /> '.$this->pi_getLL('admin_yes').' <input name="tx_multishop_pi1[budget_enabled]" type="radio" value="0" '.((!$this->post['tx_multishop_pi1']['budget_enabled']) ? 'checked' : '').' /> '.$this->pi_getLL('admin_no').'
	</div>
	<div class="account-field">
		<label>Budget usage</label>
		<input type="text" name="tx_multishop_pi1[remaining_budget]" size="8" id="remaining_budget" value="'.htmlspecialchars($this->post['tx_multishop_pi1']['remaining_budget']).'" />
	</div>
	<div class="account-field">
		<label>'.$this->pi_getLL('discount').'</label>
		<input type="text" name="discount" size="2" maxlength="2" id="discount" value="'.htmlspecialchars($this->post['discount']).'" />%
	</div>
	<div class="account-field">
		<label>&nbsp;</label>
		<span class="msBackendButton continueState arrowRight arrowPosLeft"><input type="submit" name="Submit" value="'.$this->pi_getLL('add_new_group').'" /></span>
	</div>
</form>
<div class="main-heading"><h2>'.$this->pi_getLL('groups').'</h2></div>
<form id="form1" name="form1" method="get" action="index.php">
	<input name="tx_multishop_pi1[do_search]" type="hidden" value="1" />
	<input name="type" type="hidden" value="2003" />
	<input name="id" type="hidden" value="'.$this->shop_pid.'" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_customer_groups" />
	<div class="account-field">
		<label>'.$this->pi_getLL('search_by').'</label>
		<select name="tx_multishop_pi1[search_by]">
			<option value="group_name">'.$this->pi_getLL('name').'</option>
		</select>
	</div>
	<div class="account-field">
		<label>'.ucfirst($this->pi_getLL('keyword')).'</label><input type="text" name="tx_multishop_pi1[keyword]" id="skeyword" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['keyword']).'" />
		<input type="submit" name="Submit" class="msadmin_button" value="'.$this->pi_getLL('search').'" />
	</div>
</form>';
// product search
$filter=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$select=array();
if (strlen($this->get['tx_multishop_pi1']['keyword'])>0) {
	if ($this->get['tx_multishop_pi1']['search_by']=='group_name') {
		$filter[]="f.title like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
	}
}
if (!$this->get['tx_multishop_pi1']['show_deleted_accounts']) {
	$filter[]='(f.deleted=0)';
}
$filter[]='f.pid='.$this->conf['fe_customer_pid'];
$filter[]="uid NOT IN (".implode(",", $this->excluded_userGroups).")";
switch ($this->get['tx_multishop_pi1']['order_by']) {
	case 'name':
		$order_by='f.title';
		break;
	case 'discount':
		$order_by='f.tx_multishop_discount';
		break;
	case 'uid':
	default:
		$order_by='f.uid';
		break;
}
switch ($this->get['tx_multishop_pi1']['order']) {
	case 'a':
		$order='asc';
		$order_link='d';
		break;
	case 'd':
	default:
		$order='desc';
		$order_link='a';
		break;
}
$orderby[]=$order_by.' '.$order;
$pageset=mslib_fe::getCustomerGroupsPageSet($filter, $offset, 0, $orderby, $having, $select, $where);
$groups=$pageset['groups'];
if ($pageset['total_rows']>0) {
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_customer_groups_listing.php');
	// pagination
	if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['ADMIN_CUSTOMERS_LISTING_LIMIT']) {
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
		$content.=$tmp;
	}
	// pagination eof
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>