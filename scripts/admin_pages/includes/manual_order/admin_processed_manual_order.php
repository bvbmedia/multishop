<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
//add order
if ($this->post['proceed_order']) {
	$unique_id=md5($this->post['first_name'].$this->post['last_name'].$this->post['company'].$this->post['tx_multishop_pi1']['telephone']);
	if ($this->post['customer_id']) {
		$user=mslib_fe::getUser($this->post['customer_id']);
		if ($user['uid']) {
			$customer_id=$user['uid'];
			$this->post=array_merge($this->post, $user);
			$this->post['tx_multishop_pi1']['telephone']=$this->post['telephone'];
		}
	} else {
		$str="SELECT uid from fe_users where (username='".addslashes($unique_id)."')";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			// use current account
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$customer_id=$row['uid'];
		}
	}
	if (!$customer_id) {
		$insertArray=array();
		$insertArray['page_uid']=$this->shop_pid;
		$insertArray['company']=$this->post['company'];
		$insertArray['name']=$this->post['first_name'].' '.$this->post['middle_name'].' '.$this->post['last_name'];
		$insertArray['name']=preg_replace('/\s+/', ' ', $insertArray['name']);
		$insertArray['first_name']=$this->post['first_name'];
		$insertArray['middle_name']=$this->post['middle_name'];
		$insertArray['last_name']=$this->post['last_name'];
		$insertArray['username']=$unique_id;
		$insertArray['email']=$this->post['email'];
		$insertArray['username']=$this->post['tx_multishop_pi1']['telephone'];
		$insertArray['street_name']=$this->post['street_name'];
		$insertArray['address_number']=$this->post['address_number'];
		$insertArray['address_ext']=$this->post['address_ext'];
		$insertArray['address']=$insertArray['street_name'].' '.$insertArray['address_number'].$insertArray['address_ext'];
		$insertArray['address']=preg_replace('/\s+/', ' ', $insertArray['address']);
		$insertArray['zip']=$this->post['zip'];
		$insertArray['telephone']=$this->post['tx_multishop_pi1']['telephone'];
		$insertArray['city']=$this->post['city'];
		$insertArray['country']=$this->post['country'];
		$insertArray['password']=$insertArray['crdate']=time();
		$insertArray['usergroup']=$this->conf['fe_customer_usergroup'];
		$insertArray['pid']=$this->conf['fe_customer_pid'];
		$insertArray['password']=mslib_befe::getHashedPassword(rand(1000000, 9000000));
		$insertArray['tx_multishop_vat_id']=$this->post['tx_multishop_vat_id'];
		$insertArray['tx_multishop_coc_id']=$this->post['tx_multishop_coc_id'];
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($res) {
			$customer_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
		}
	}
	//add to orders
	if ($customer_id) {
		// now add the order
		$insertArray=array();
		$insertArray['customer_id']=$customer_id;
		$insertArray['page_uid']=$this->shop_pid;
		$insertArray['status']=1;
		$insertArray['billing_company']=$this->post['company'];
		$insertArray['billing_first_name']=$this->post['first_name'];
		$insertArray['billing_middle_name']=$this->post['middle_name'];
		$insertArray['billing_last_name']=$this->post['last_name'];
		$insertArray['billing_name']=preg_replace('/ +/', ' ', $this->post['first_name'].' '.$this->post['middle_name'].' '.$this->post['last_name']);
		$insertArray['billing_email']=$this->post['email'];
		$insertArray['billing_gender']=$this->post['gender'];
		$insertArray['billing_birthday']=$this->post['birthday'];
		$insertArray['billing_street_name']=$this->post['street_name'];
		$insertArray['billing_address_number']=$this->post['address_number'];
		$insertArray['billing_address_ext']=$this->post['address_ext'];
		$insertArray['billing_address']=preg_replace('/ +/', ' ', $insertArray['billing_street_name'].' '.$insertArray['billing_address_number'].' '.$insertArray['billing_address_ext']);
		$insertArray['billing_room']='';
		$insertArray['billing_city']=$this->post['city'];
		$insertArray['billing_zip']=$this->post['zip'];
		$insertArray['billing_region']='';
		$insertArray['billing_country']=$this->post['country'];
		$insertArray['billing_telephone']=$this->post['tx_multishop_pi1']['telephone'];
		$insertArray['billing_mobile']=$this->post['mobile'];
		$insertArray['billing_fax']='';
		$insertArray['billing_vat_id']=$this->post['tx_multishop_vat_id'];
		$insertArray['billing_coc_id']=$this->post['tx_multishop_coc_id'];
		$insertArray['delivery_company']=$this->post['delivery_company'];
		$insertArray['delivery_first_name']=$this->post['delivery_first_name'];
		$insertArray['delivery_middle_name']=$this->post['delivery_middle_name'];
		$insertArray['delivery_last_name']=$this->post['delivery_last_name'];
		$insertArray['delivery_name']=preg_replace('/ +/', ' ', $this->post['delivery_first_name'].' '.$this->post['delivery_middle_name'].' '.$this->post['delivery_last_name']);
		$insertArray['delivery_email']=$this->post['delivery_email'];
		$insertArray['delivery_gender']=$this->post['delivery_gender'];
		$insertArray['delivery_street_name']=$this->post['delivery_street_name'];
		$insertArray['delivery_address_number']=$this->post['delivery_address_number'];
		$insertArray['delivery_address']=preg_replace('/ +/', ' ', $insertArray['delivery_street_name'].' '.$insertArray['delivery_address_number'].' '.$insertArray['delivery_address_ext']);
		$insertArray['delivery_city']=$this->post['delivery_city'];
		$insertArray['delivery_zip']=$this->post['delivery_zip'];
		$insertArray['delivery_address_ext']=$this->post['delivery_address_ext'];
		$insertArray['delivery_room']='';
		$insertArray['delivery_region']='';
		$insertArray['delivery_country']=$this->post['delivery_country'];
		$insertArray['delivery_telephone']=$this->post['delivery_telephone'];
		$insertArray['delivery_mobile']=$this->post['delivery_mobile'];
		$insertArray['delivery_fax']='';
		$insertArray['delivery_vat_id']='';
		$insertArray['bill']=1;
		$insertArray['crdate']=time();
		$insertArray['shipping_method']=$this->post['shipping_method'];
		$insertArray['payment_method']=$this->post['payment_method'];
		$insertArray['shipping_method_costs']=$this->post['shipping_method_costs'];
		$insertArray['payment_method_costs']=$this->post['payment_method_costs'];
		$insertArray['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
		if (!$this->post['different_delivery_address']) {
			$insertArray['delivery_email']=$insertArray['billing_email'];
			$insertArray['delivery_company']=$insertArray['billing_company'];
			$insertArray['delivery_first_name']=$insertArray['billing_first_name'];
			$insertArray['delivery_middle_name']=$insertArray['billing_middle_name'];
			$insertArray['delivery_last_name']=$insertArray['billing_last_name'];
			$insertArray['delivery_telephone']=$insertArray['billing_telephone'];
			$insertArray['delivery_mobile']=$insertArray['billing_mobile'];
			$insertArray['delivery_gender']=$insertArray['billing_gender'];
			$insertArray['delivery_street_name']=$insertArray['billing_street_name'];
			$insertArray['delivery_address']=$insertArray['billing_address'];
			$insertArray['delivery_address_number']=$insertArray['billing_address_number'];
			$insertArray['delivery_address_ext']=$insertArray['billing_address_ext'];
			$insertArray['delivery_zip']=$insertArray['billing_zip'];
			$insertArray['delivery_city']=$insertArray['billing_city'];
			$insertArray['delivery_country']=$insertArray['billing_country'];
			$insertArray['delivery_telephone']=$insertArray['billing_telephone'];
			$insertArray['delivery_region']=$insertArray['billing_region'];
			$insertArray['delivery_name']=$insertArray['billing_name'];
		} else {
			$insertArray['delivery_email']=$this->post['delivery_email'];
			$insertArray['delivery_company']=$this->post['delivery_company'];
			$insertArray['delivery_first_name']=$this->post['delivery_first_name'];
			$insertArray['delivery_middle_name']=$this->post['delivery_middle_name'];
			$insertArray['delivery_last_name']=$this->post['delivery_last_name'];
			$insertArray['delivery_telephone']=$this->post['delivery_telephone'];
			$insertArray['delivery_mobile']=$this->post['delivery_mobile'];
			$insertArray['delivery_gender']=$this->post['delivery_gender'];
			$insertArray['delivery_street_name']=$this->post['delivery_street_name'];
			$insertArray['delivery_address_number']=$this->post['delivery_address_number'];
			$insertArray['delivery_address_ext']=$this->post['delivery_address_ext'];
			$insertArray['delivery_address']=preg_replace('/ +/', ' ', $this->post['delivery_street_name'].' '.$this->post['delivery_address_number'].' '.$this->post['delivery_address_ext']);
			$insertArray['delivery_zip']=$this->post['delivery_zip'];
			$insertArray['delivery_city']=$this->post['delivery_city'];
			$insertArray['delivery_country']=$this->post['delivery_country'];
			$insertArray['delivery_email']=$this->post['delivery_email'];
			$insertArray['delivery_telephone']=$this->post['delivery_telephone'];
			$insertArray['delivery_state']=$this->post['delivery_state'];
			$insertArray['delivery_name']=preg_replace('/ +/', ' ', $this->post['delivery_first_name'].' '.$this->post['delivery_middle_name'].' '.$this->post['delivery_last_name']);
		}
		if ($this->post['tx_multishop_pi1']['is_proposal']) {
			$insertArray['is_proposal']=1;
		} else {
			$insertArray['by_phone']=1;
		}
		$insertArray['hash']=md5(uniqid('', true));
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderPreHook'])) {
			// hook
			$params=array(
				'ms'=>$ms,
				'insertArray'=>&$insertArray
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1']['insertOrderPreHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
			// hook eof
		}
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// now add the order eof
		$orders_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
		// redirect back to orders and let highslide open it
		$url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$orders_id.'&tx_multishop_pi1[is_manual]=1&action=edit_order&tx_multishop_pi1[is_proposal]='.$this->post['tx_multishop_pi1']['is_proposal'], 1);
		header('Location: '.$url);
		exit();
	} //add to orders eof
}
?>