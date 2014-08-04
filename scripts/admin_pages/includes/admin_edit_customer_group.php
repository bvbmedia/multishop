<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
// now parse all the objects in the tmpl file
if ($this->conf['admin_edit_customer_group_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_edit_customer_group_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_edit_customer_group.tmpl');
}
// Extract the subparts from the template
if ($this->post) {
	$insertArray=array();
	$insertArray['title']=$this->post['group_name'];
	$insertArray['pid']=$this->conf['fe_customer_pid'];
	$insertArray['tx_multishop_discount']=$this->post['discount'];
	// custom page hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminUpdateCustomerGroupPreProc'])) {
		$params=array(
			'insertArray'=>&$insertArray,
			'customer_group_id'=>&$this->post['customer_group_id']
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminUpdateCustomerGroupPreProc'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom page hook that can be controlled by third-party plugin eof
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_groups', 'uid='.$this->post['customer_group_id'], $insertArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$users=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'name');
	foreach ($users as $user) {
		// check if the user should be member or not
		if (in_array($user['uid'], $this->post['tx_multishop_pi1']['users'])) {
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
	// customer shipping/payment method mapping
	if ($this->post['customer_group_id'] && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
		// shipping/payment methods
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_customers_groups_method_mappings', 'customers_groups_id=\''.$customer_id.'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if (is_array($this->post['payment_method']) and count($this->post['payment_method'])) {
			foreach ($this->post['payment_method'] as $payment_method_id => $value) {
				$updateArray=array();
				$updateArray['customers_groups_id']=$this->post['customer_group_id'];
				$updateArray['method_id']=$payment_method_id;
				$updateArray['type']='payment';
				$updateArray['negate']=$value;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_groups_method_mappings', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		if (is_array($this->post['shipping_method']) and count($this->post['shipping_method'])) {
			foreach ($this->post['shipping_method'] as $shipping_method_id => $value) {
				$updateArray=array();
				$updateArray['customers_groups_id']=$this->post['customer_group_id'];
				$updateArray['method_id']=$shipping_method_id;
				$updateArray['type']='shipping';
				$updateArray['negate']=$value;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_groups_method_mappings', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		// shipping/payment methods eof
	}
	if ($this->post['tx_multishop_pi1']['referrer']) {
		header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
		exit();
	} else {
		header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_groups',1));
		exit();
	}
}
// customer to shipping/payment method mapping
$shipping_payment_method='';
if ($this->ms['MODULES']['GROUP_EDIT_METHOD_FILTER']) {
	$payment_methods=mslib_fe::loadPaymentMethods();
	// loading shipping methods eof
	$shipping_methods=mslib_fe::loadShippingMethods();
	if (count($payment_methods) or count($shipping_methods)) {
		// the value is are the negate value
		// negate 1 mean the shipping/payment are excluded
		$shipping_payment_method.='
						<div class="account-field div_products_mappings toggle_advanced_option" id="msEditProductInputPaymentMethod">
							<label>'.$this->pi_getLL('admin_mapped_methods').'</label>
							<div class="innerbox_methods">
								<div class="innerbox_payment_methods">
									<h4>'.$this->pi_getLL('admin_payment_methods').'</h4>
									<ul>';
		// load mapped ids
		$method_mappings=array();
		if ($this->get['customer_group_id']) {
			$method_mappings=mslib_befe::getMethodsByGroup($this->get['customer_group_id']);
		}
		$tr_type='';
		if (count($payment_methods)) {
			foreach ($payment_methods as $code=>$item) {
				if (!$tr_type or $tr_type=='even') {
					$tr_type='odd';
				} else {
					$tr_type='even';
				}
				$count++;
				$shipping_payment_method.='<li class="'.$tr_type.'"  id="multishop_payment_method_'.$item['id'].'"><span>'.$item['name'].'</span>';
				if ($price_wrap) {
					$tmpcontent.=$price_wrap;
				}
				$shipping_payment_method.='<input name="payment_method['.htmlspecialchars($item['id']).']" class="payment_method_cb" id="enable_payment_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="0"'.((is_array($method_mappings['payment']) && in_array($item['id'], $method_mappings['payment']) && !$method_mappings['payment']['method_data'][$item['id']]['negate']) ? ' checked' : '').' /><label for="enable_payment_method_'.$item['id'].'">'.$this->pi_getLL('enable').'</label>';
				$shipping_payment_method.='<input name="payment_method['.htmlspecialchars($item['id']).']" class="payment_method_cb" id="disable_payment_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="1"'.((is_array($method_mappings['payment']) && in_array($item['id'], $method_mappings['payment']) && $method_mappings['payment']['method_data'][$item['id']]['negate']>0) ? ' checked' : '').' /><label for="disable_payment_method_'.$item['id'].'">'.$this->pi_getLL('disable').'</label>';
				$shipping_payment_method.='</li>';
			}
		}
		$shipping_payment_method.='</ul>
								</div>
								<div class="innerbox_shipping_methods" id="msEditProductInputShippingMethod">
									<h4>'.$this->pi_getLL('admin_shipping_methods').'</h4>
							 		<ul id="multishop_shipping_method">';
		$count=0;
		$tr_type='';
		if (count($shipping_methods)) {
			foreach ($shipping_methods as $code=>$item) {
				$count++;
				$shipping_payment_method.='<li><span>'.$item['name'].'</span>';
				if ($price_wrap) {
					$shipping_payment_method.=$price_wrap;
				}
				$shipping_payment_method.='<input name="shipping_method['.htmlspecialchars($item['id']).']" class="shipping_method_cb" id="enable_shipping_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="0"'.((is_array($method_mappings['shipping']) && in_array($item['id'], $method_mappings['shipping']) && !$method_mappings['shipping']['method_data'][$item['id']]['negate']) ? ' checked' : '').'  /><label for="enable_shipping_method_'.$item['id'].'">'.$this->pi_getLL('enable').'</label>';
				$shipping_payment_method.='<input name="shipping_method['.htmlspecialchars($item['id']).']" class="shipping_method_cb" id="disable_shipping_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="1"'.((is_array($method_mappings['shipping']) && in_array($item['id'], $method_mappings['shipping']) && $method_mappings['shipping']['method_data'][$item['id']]['negate']>0) ? ' checked' : '').'  /><label for="enable_shipping_method_'.$item['id'].'">'.$this->pi_getLL('disable').'</label>';
				$shipping_payment_method.='</li>';
			}
		}
		$shipping_payment_method.='
					 				</ul>
								</div>
							</div>
						</div>';
	}
}
$group=mslib_fe::getGroup($this->get['customer_group_id'], 'uid');
$group['tx_multishop_remaining_budget']=round($group['tx_multishop_remaining_budget'], 13);
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['members_option']=$this->cObj->getSubpart($subparts['template'], '###MEMBERS_OPTION###');
// now lets load the users
$users=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'name');
$contentItem='';
foreach ($users as $user) {
	if (!$user['name']) {
		$user['name']=$user['username'];
	}
	$markerArray=array();
	$markerArray['LABEL_MEMBERS_USERID']=$user['uid'];
	$markerArray['LABEL_MEMBERS_NAME']=$user['name'];
	$markerArray['LABEL_MEMBERS_USERNAME']=$user['username'];
	if (mslib_fe::inUserGroup($this->get['customer_group_id'], $user['usergroup'])) {
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
$subpartArray['###VALUE_REFERRER###']='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
}
$subpartArray['###INPUT_EDIT_SHIPPING_AND_PAYMENT_METHOD###']=$shipping_payment_method;
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminEditCustomerGroupTmplPreProc'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'group'=>&$group
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer_groups.php']['adminEditCustomerGroupTmplPreProc'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom page hook that can be controlled by third-party plugin eof
$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
?>