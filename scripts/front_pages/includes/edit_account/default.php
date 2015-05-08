<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (!mslib_fe::loggedin()) {
	exit();
}
// load enabled countries to array
$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
$enabled_countries=array();
while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
	$enabled_countries[]=$row2;
}
// load enabled countries to array eof
if ($this->post) {
	// billing details
	$user['email']=$this->post['email'];
	$user['company']=$this->post['company'];
	$user['first_name']=$this->post['first_name'];
	$user['middle_name']=$this->post['middle_name'];
	$user['last_name']=$this->post['last_name'];
	$user['name']=preg_replace('/\s+/', ' ', $user['first_name'].' '.$user['middle_name'].' '.$user['last_name']);
	$user['birthday']=$this->post['birthday'];
	$user['phone']=$this->post['telephone'];
	$user['mobile']=$this->post['mobile'];
	// fe user table holds integer as value: 0 is male, 1 is female
	// but in tt_address its varchar: m is male, f is female
	switch ($this->post['gender']) {
		case '0':
		case 'm':
			$user['gender']='0';
			break;
		case '1':
		case 'f':
			$user['gender']='1';
			break;
		case '2':
		case 'c':
			$user['gender']='2';
			break;
	}
	$user['street_name']=$this->post['street_name'];
	$user['address_number']=$this->post['address_number'];
	$user['address_ext']=$this->post['address_ext'];
	$user['address']=$user['street_name'].' '.$user['address_number'];
	if ($user['address_ext']) {
		$user['address'].='-'.$user['address_ext'];
	}
	$user['zip']=$this->post['zip'];
	$user['city']=$this->post['city'];
	$user['country']=$this->post['country'];
	$user['email']=$this->post['email'];
	$user['telephone']=$this->post['telephone'];
	$date_of_birth=explode("-", $user['birthday']);
	// billing details eof
	// delivery details
	if ($this->post['delivery_first_name']) {
		$this->post['different_delivery_address']=1;
	}
	if (isset($this->post['tx_multishop_newsletter'])) {
		$user['tx_multishop_newsletter']=$this->post['tx_multishop_newsletter'];
	} else {
		$user['tx_multishop_newsletter']=0;
	}
	if (!$this->post['different_delivery_address']) {
		$user['delivery_email']=$this->post['email'];
		$user['delivery_company']=$this->post['company'];
		$user['delivery_first_name']=$this->post['first_name'];
		$user['delivery_middle_name']=$this->post['middle_name'];
		$user['delivery_last_name']=$this->post['last_name'];
		$user['delivery_name']=$user['delivery_first_name'].' '.$user['delivery_middle_name'].' '.$user['delivery_last_name'];
		$user['delivery_name']=preg_replace('/\s+/', ' ', $user['delivery_name']);
		$user['delivery_telephone']=$this->post['telephone'];
		$user['delivery_mobile']=$this->post['mobile'];
		$user['delivery_gender']=$this->post['gender'];
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($user['delivery_gender']) {
			case '0':
			case 'm':
				$user['delivery_gender']='m';
				break;
			case '1':
			case 'f':
				$user['delivery_gender']='f';
				break;
			case '2':
			case 'c':
				$user['delivery_gender']='c';
				break;
		}
		$user['delivery_street_name']=$this->post['street_name'];
		$user['delivery_address_number']=$this->post['address_number'];
		$user['delivery_address_ext']=$this->post['address_ext'];
		$user['delivery_address']=$user['delivery_street_name'].' '.$user['delivery_address_number'];
		if ($user['delivery_address_ext']) {
			$user['delivery_address'].='-'.$user['address_ext'];
		}
		$user['delivery_zip']=$this->post['zip'];
		$user['delivery_city']=$this->post['city'];
		$user['delivery_country']=$this->post['country'];
	} else {
		$user['delivery_email']=$this->post['delivery_email'];
		$user['delivery_company']=$this->post['delivery_company'];
		$user['delivery_first_name']=$this->post['delivery_first_name'];
		$user['delivery_middle_name']=$this->post['delivery_middle_name'];
		$user['delivery_last_name']=$this->post['delivery_last_name'];
		$user['delivery_name']=$user['delivery_first_name'].' '.$user['delivery_middle_name'].' '.$user['delivery_last_name'];
		$user['delivery_name']=preg_replace('/\s+/', ' ', $user['delivery_name']);
		$user['delivery_telephone']=$this->post['delivery_telephone'];
		$user['delivery_mobile']=$this->post['delivery_mobile'];
		$user['delivery_gender']=$this->post['delivery_gender'];
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($user['delivery_gender']) {
			case '0':
			case 'm':
				$user['delivery_gender']='m';
				break;
			case '1':
			case 'f':
				$user['delivery_gender']='f';
				break;
			case '2':
			case 'c':
				$user['delivery_gender']='c';
				break;
		}
		$user['delivery_street_name']=$this->post['delivery_street_name'];
		$user['delivery_address_number']=$this->post['delivery_address_number'];
		$user['delivery_address_ext']=$this->post['delivery_address_ext'];
		$user['delivery_address']=$user['delivery_street_name'].' '.$user['delivery_address_number'];
		if ($user['delivery_address_ext']) {
			$user['delivery_address'].='-'.$user['address_ext'];
		}
		$user['delivery_zip']=$this->post['delivery_zip'];
		$user['delivery_city']=$this->post['delivery_city'];
		$user['delivery_country']=$this->post['delivery_country'];
		if (isset($this->post['tx_multishop_vat_id'])) {
			$user['tx_multishop_vat_id']=$this->post['tx_multishop_vat_id'];
		}
		if (isset($this->post['tx_multishop_coc_id'])) {
			$user['tx_multishop_coc_id']=$this->post['tx_multishop_coc_id'];
		}
	}
	if ($this->post) {
//		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
		$address=$user;
		$insertArray=array();
		$insertArray['company']=$address['company'];
		$insertArray['name']=$address['name'];
		$insertArray['first_name']=$address['first_name'];
		$insertArray['middle_name']=$address['middle_name'];
		$insertArray['last_name']=$address['last_name'];
		$insertArray['email']=$address['email'];
		$insertArray['street_name']=$address['street_name'];
		$insertArray['address_number']=$address['address_number'];
		$insertArray['address_ext']=$address['address_ext'];
		$insertArray['address']=preg_replace('/\s+/', ' ', $insertArray['street_name'].' '.$insertArray['address_number'].' '.$insertArray['address_ext']);
		$insertArray['mobile']=$address['mobile'];
		$insertArray['zip']=$address['zip'];
		$insertArray['telephone']=$address['telephone'];
		$insertArray['city']=$address['city'];
		$insertArray['country']=$address['country'];
		if ($this->post['password'] and ($this->post['repassword']==$this->post['repassword'])) {
			$insertArray['password']=mslib_befe::getHashedPassword($this->post['password']);
		}
		$insertArray['gender']=$address['gender'];
		$insertArray['date_of_birth']=$timestamp=strtotime($date_of_birth[2].'-'.$date_of_birth[1].'-'.$date_of_birth[0]);
		$insertArray['tx_multishop_newsletter']=$address['tx_multishop_newsletter'];
		$insertArray['tx_multishop_vat_id']=$address['tx_multishop_vat_id'];
		$insertArray['tx_multishop_coc_id']=$address['tx_multishop_coc_id'];
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/edit_account/default.php']['updateAccountDetailsPreProc'])) {
			$params=array(
				'insertArray'=>&$insertArray,
				'uid'=>&$GLOBALS["TSFE"]->fe_user->user['uid']
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/edit_account/default.php']['updateAccountDetailsPreProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid = '.$GLOBALS["TSFE"]->fe_user->user['uid'], $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/edit_account/default.php']['updateAccountDetailsPostProc'])) {
			$params=array(
				'insertArray'=>&$insertArray,
				'uid'=>&$GLOBALS["TSFE"]->fe_user->user['uid']
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/edit_account/default.php']['updateAccountDetailsPostProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		// add / update billing tt_address
		$insertTTArray=array();
		$insertTTArray['company']=$address['company'];
		$insertTTArray['name']=$address['name'];
		$insertTTArray['gender']=$address['gender'];
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($insertTTArray['gender']) {
			case '0':
			case 'm':
				$insertTTArray['gender']='m';
				break;
			case '1':
			case 'f':
				$insertTTArray['gender']='f';
				break;
			case '2':
			case 'c':
				$insertTTArray['gender']='c';
				break;
		}
		$insertTTArray['first_name']=$address['first_name'];
		$insertTTArray['middle_name']=$address['middle_name'];
		$insertTTArray['last_name']=$address['last_name'];
		$insertTTArray['birthday']=$address['birthday'];
		$insertTTArray['email']=$address['email'];
		$insertTTArray['phone']=$address['telephone'];
		$insertTTArray['mobile']=$address['mobile'];
		$insertTTArray['zip']=$address['zip'];
		$insertTTArray['city']=$address['city'];
		$insertTTArray['country']=$address['country'];
		$insertTTArray['street_name']=$address['street_name'];
		$insertTTArray['address']=$address['address'];
		$insertTTArray['address_number']=$address['address_number'];
		$insertTTArray['address_ext']=$address['address_ext'];
		$sql_tt_address="select uid from tt_address where tx_multishop_customer_id='".$GLOBALS["TSFE"]->fe_user->user['uid']."' and tx_multishop_address_type='billing' and deleted=0";
		$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
		$rows_tt_address=$GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address);
		if ($rows_tt_address) {
			$row_tt_address=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
			$tt_address_id=$row_tt_address['uid'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'uid = '.$tt_address_id, $insertTTArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		} else {
			$insertTTArray['tstamp']=time();
			$insertTTArray['tx_multishop_customer_id']=$GLOBALS["TSFE"]->fe_user->user['uid'];
			$insertTTArray['pid']=$this->conf['fe_customer_pid'];
			$insertTTArray['page_uid']=$this->shop_pid;
			$insertTTArray['tx_multishop_default']='1';
			$insertTTArray['tx_multishop_address_type']='billing';
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertTTArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
		// add / update billing tt_address eof
		// add / update delivery tt_address
		$insertTTArray=array();
		$insertTTArray['company']=$address['delivery_company'];
		$insertTTArray['name']=$address['delivery_name'];
		$insertTTArray['gender']=$address['delivery_gender'];
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($insertTTArray['gender']) {
			case '0':
			case 'm':
				$insertTTArray['gender']='m';
				break;
			case '1':
			case 'f':
				$insertTTArray['gender']='f';
				break;
			case '2':
			case 'c':
				$insertTTArray['gender']='c';
				break;
		}
		$insertTTArray['first_name']=$address['delivery_first_name'];
		$insertTTArray['middle_name']=$address['delivery_middle_name'];
		$insertTTArray['last_name']=$address['delivery_last_name'];
		$insertTTArray['birthday']=$address['delivery_birthday'];
		$insertTTArray['email']=$address['delivery_email'];
		$insertTTArray['phone']=$address['delivery_telephone'];
		$insertTTArray['mobile']=$address['delivery_mobile'];
		$insertTTArray['zip']=$address['delivery_zip'];
		$insertTTArray['city']=$address['delivery_city'];
		$insertTTArray['country']=$address['delivery_country'];
		$insertTTArray['street_name']=$address['delivery_street_name'];
		$insertTTArray['address']=$address['delivery_address'];
		$insertTTArray['address_number']=$address['delivery_address_number'];
		$insertTTArray['address_ext']=$address['delivery_address_ext'];
		$sql_tt_address="select uid from tt_address where tx_multishop_customer_id='".$GLOBALS["TSFE"]->fe_user->user['uid']."' and tx_multishop_address_type='delivery' and deleted=0";
		$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
		$rows_tt_address=$GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address);
		if ($rows_tt_address) {
			$row_tt_address=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
			$tt_address_id=$row_tt_address['uid'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'uid = '.$tt_address_id, $insertTTArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		} else {
			$insertTTArray['tstamp']=time();
			$insertTTArray['tx_multishop_customer_id']=$GLOBALS["TSFE"]->fe_user->user['uid'];
			$insertTTArray['pid']=$this->conf['fe_customer_pid'];
			$insertTTArray['page_uid']=$this->shop_pid;
			$insertTTArray['tx_multishop_address_type']='delivery';
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertTTArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
		// add / update delivery tt_address eof
		$content.=$this->pi_getLL('your_details_has_been_saved').'.';
		//echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
	}
} else {
	// begin form
	// load enabled countries to array
	if (mslib_fe::loggedin()) {
		$user=array();
		foreach ($GLOBALS["TSFE"]->fe_user->user as $key=>$val) {
			$user[$key]=$val;
		}
		$user['date_of_birth']=strftime("%x %X", $user['date_of_birth']);
		$user['gender']=$user['gender']==0 ? 'm' : 'f';
		// load delivery details
		$sql_tt_address="select * from tt_address where tx_multishop_customer_id='".$GLOBALS["TSFE"]->fe_user->user['uid']."' and tx_multishop_address_type='delivery' and deleted=0 order by uid desc limit 1";
		$qry_tt_address=$GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
		$rows_tt_address=$GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address);
		if ($rows_tt_address) {
			$row_tt_address=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);
			$user['delivery_email']=$row_tt_address['email'];
			$user['delivery_company']=$row_tt_address['company'];
			$user['delivery_first_name']=$row_tt_address['first_name'];
			$user['delivery_middle_name']=$row_tt_address['middle_name'];
			$user['delivery_last_name']=$row_tt_address['last_name'];
			$user['delivery_name']=$row_tt_address['name'];
			$user['delivery_telephone']=$row_tt_address['phone'];
			$user['delivery_mobile']=$row_tt_address['mobile'];
			$user['delivery_gender']=$row_tt_address['gender'];
			$user['delivery_street_name']=$row_tt_address['street_name'];
			$user['delivery_address']=$row_tt_address['address'];
			$user['delivery_address_number']=$row_tt_address['address_number'];
			$user['delivery_address_ext']=$row_tt_address['address_ext'];
			$user['delivery_zip']=$row_tt_address['zip'];
			$user['delivery_city']=$row_tt_address['city'];
			$user['delivery_country']=$row_tt_address['country'];
		} else {
			// add default
			$user['delivery_email']=$user['email'];
			$user['delivery_company']=$user['company'];
			$user['delivery_first_name']=$user['first_name'];
			$user['delivery_middle_name']=$user['middle_name'];
			$user['delivery_last_name']=$user['last_name'];
			$user['delivery_name']=$user['name'];
			$user['delivery_telephone']=$user['telephone'];
			$user['delivery_mobile']=$user['mobile'];
			$user['delivery_gender']=$user['gender'];
			$user['delivery_street_name']=$user['street_name'];
			$user['delivery_address']=$user['address'];
			$user['delivery_address_number']=$user['address_number'];
			$user['delivery_address_ext']=$user['address_ext'];
			$user['delivery_zip']=$user['zip'];
			$user['delivery_city']=$user['city'];
			$user['delivery_country']=$user['country'];
		}
	}
	//print_r($user);
	// load enabled countries to array eof
	$regex="/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
	$regex_for_character="/[^0-9]$/";
	$validate_password='';
	$GLOBALS['TSFE']->additionalHeaderData[]='
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery(\'#checkout\').h5Validate();
				'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY'] ? '
				jQuery("#birthday_visitor").datepicker({
					dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
					altField: "#birthday",
					altFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					yearRange: "'.(date("Y")-100).':'.date("Y").'"
				});
				jQuery("#delivery_birthday_visitor").datepicker({
					dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
					altField: "#delivery_birthday",
					altFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					yearRange: "'.(date("Y")-100).':'.date("Y").'"
				});' : '').'
				// set the h5validate attributes for required delivery data
				$(\'#delivery_radio\').attr(\'required\', \'required\');
				$(\'#delivery_radio\').attr(\'data-h5-errorid\', \'invalid-delivery_gender\');
				$(\'#delivery_radio\').attr(\'title\', \''.$this->pi_getLL('gender_is_required', 'Title is required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_first_name\').attr(\'required\', \'required\');
				$(\'#delivery_first_name\').attr(\'data-h5-errorid\', \'invalid-delivery_first_name\');
				$(\'#delivery_first_name\').attr(\'title\', \''.$this->pi_getLL('first_name_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_last_name\').attr(\'required\', \'required\');
				$(\'#delivery_last_name\').attr(\'data-h5-errorid\', \'invalid-delivery_last_name\');
				$(\'#delivery_last_name\').attr(\'title\', \''.$this->pi_getLL('surname_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_address\').attr(\'required\', \'required\');
				$(\'#delivery_address\').attr(\'data-h5-errorid\', \'invalid-delivery_address\');
				$(\'#delivery_address\').attr(\'title\', \''.$this->pi_getLL('street_address_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_address_number\').attr(\'required\', \'required\');
				$(\'#delivery_address_number\').attr(\'data-h5-errorid\', \'invalid-delivery_address_number\');
				$(\'#delivery_address_number\').attr(\'title\', \''.$this->pi_getLL('street_number_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_zip\').attr(\'required\', \'required\');
				$(\'#delivery_zip\').attr(\'data-h5-errorid\', \'invalid-delivery_zip\');
				$(\'#delivery_zip\').attr(\'title\', \''.$this->pi_getLL('zip_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_city\').attr(\'required\', \'required\');
				$(\'#delivery_city\').attr(\'data-h5-errorid\', \'invalid-delivery_city\');
				$(\'#delivery_city\').attr(\'title\', \''.$this->pi_getLL('city_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_country\').attr(\'required\', \'required\');
				$(\'#delivery_country\').attr(\'data-h5-errorid\', \'invalid-delivery_country\');
				$(\'#delivery_country\').attr(\'title\', \''.$this->pi_getLL('country_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

				$(\'#delivery_telephone\').attr(\'required\', \'required\');
				$(\'#delivery_telephone\').attr(\'data-h5-errorid\', \'invalid-delivery_telephone\');
				$(\'#delivery_telephone\').attr(\'title\', \''.$this->pi_getLL('telephone_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');
			}); //end of first load
		</script>';
	$content.='<div class="error_msg" style="display:none">';
	$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
	$content.='<li class="item-error" style="display:none"></li>';
	$content.='</ul></div>';
	$vat_input_block='';
	if ($this->ms['MODULES']['CHECKOUT_DISPLAY_VAT_ID_INPUT']) {
		$vat_input_block=' <div class="account-field col-sm-6" id="input-tx_multishop_vat_id">
			<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.ucfirst($this->pi_getLL('vat_id', 'VAT ID')).'</label>
			<input type="text" name="tx_multishop_vat_id" class="tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.htmlspecialchars($user['tx_multishop_vat_id']).'"/>
		</div>';
	}
	$coc_input_block='';
	if ($this->ms['MODULES']['CHECKOUT_DISPLAY_COC_ID_INPUT']) {
		$coc_input_block=' <div class="account-field col-sm-6" id="input-tx_multishop_coc_id">
			<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">'.ucfirst($this->pi_getLL('coc_id', 'KvK ID')).'</label>
			<input type="text" name="tx_multishop_coc_id" class="tx_multishop_coc_id" id="tx_multishop_coc_id" value="'.htmlspecialchars($user['tx_multishop_coc_id']).'"/>
		</div>';
	}
	//
	if ($this->conf['edit_account_tmpl_path']) {
		$template=$this->cObj->fileResource($this->conf['edit_account_tmpl_path']);
	} else {
		$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/edit_account.tmpl');
	}
	//
	$markerArray=array();
	$markerArray['###LABEL_BILLING_ADDRESS###']=$this->pi_getLL('billing_address');
	$markerArray['###LABEL_TITLE###']=ucfirst($this->pi_getLL('title'));
	$markerArray['###GENDER_MR_CHECKED###']=(($user['gender']=='m') ? 'checked' : '');
	$markerArray['###LABEL_ERROR_GENDER_IS_REQUIRED###']=$this->pi_getLL('gender_is_required', 'Title is required');
	$markerArray['###LABEL_GENDER_MR###']=ucfirst($this->pi_getLL('mr'));
	$markerArray['###GENDER_MRS_CHECKED###']=(($user['gender']=='f') ? 'checked' : '');
	$markerArray['###LABEL_GENDER_MRS###']=ucfirst($this->pi_getLL('mrs'));
	//
	$birthday_block='';
	if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
		$birthday_block='<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'*</label>
		<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.htmlspecialchars($user['date_of_birth']).'" >
		<input type="hidden" name="birthday" class="birthday" id="birthday" value="'.htmlspecialchars($user['date_of_birth']).'" >';
	}
	//
	$markerArray['###BIRTHDAY_BLOCK###']=$birthday_block;
	//
	$markerArray['###LABEL_COMPANY###']=ucfirst($this->pi_getLL('company')).($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? '*' : '');
	$markerArray['###VALUE_COMPANY###']=htmlspecialchars($user['company']);
	$markerArray['###COMPANY_VALIDATION###']=($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? ' required="required" data-h5-errorid="invalid-company" title="'.$this->pi_getLL('company_is_required').'"' : '');
	$markerArray['###INPUT_VAT_BLOCK###']=$vat_input_block;
	$markerArray['###INPUT_COC_BLOCK###']=$coc_input_block;
	$markerArray['###LABEL_FIRST_NAME###']=ucfirst($this->pi_getLL('first_name'));
	$markerArray['###VALUE_FIRST_NAME###']=htmlspecialchars($user['first_name']);
	$markerArray['###LABEL_ERROR_FIRST_NAME_IS_REQUIRED###']=$this->pi_getLL('first_name_required');
	$markerArray['###LABEL_MIDDLE_NAME###']=ucfirst($this->pi_getLL('middle_name'));
	$markerArray['###VALUE_MIDDLE_NAME###']=htmlspecialchars($user['middle_name']);
	$markerArray['###LABEL_LAST_NAME###']=ucfirst($this->pi_getLL('last_name'));
	$markerArray['###VALUE_LAST_NAME###']=htmlspecialchars($user['last_name']);
	$markerArray['###LABEL_ERROR_LAST_NAME_IS_REQUIRED###']=$this->pi_getLL('surname_is_required');
	//
	// load countries
	$country_block='';
	$delivery_country_block='';
	if (count($enabled_countries)==1) {
		$country_block='<input name="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		$delivery_country_block='<input name="delivery_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
	} else {
		foreach ($enabled_countries as $country) {
			$tmpcontent_con.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($user['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
			$tmpcontent_con_delivery.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($user['delivery_country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
		}
		if ($tmpcontent_con) {
			$country_block='<div id="input-country" class="account-field col-sm-'.($this->conf['edit_account_tmpl_path'] ? '12' : '8').'">
				<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
				<select name="country" id="country" class="country" required="required" data-h5-errorid="invalid-country" title="'.$this->pi_getLL('country_is_required').'">
				<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
				'.$tmpcontent_con.'
				</select>
				<div id="invalid-country" class="error-space" style="display:none"></div>
			</div>';

			$delivery_country_block='<div id="input-dcountry" class="account-field col-sm-'.($this->conf['edit_account_tmpl_path'] ? '12' : '8').'">
				<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
				<select name="delivery_country" id="delivery_country" class="delivery_country">
				<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
				'.$tmpcontent_con_delivery.'
				</select>
				<div id="invalid-delivery_country" class="error-space" style="display:none"></div>
			</div>';
		}
	}
	// country eof
	$markerArray['###INPUT_COUNTRY_BLOCK###']=$country_block;
	//
	$markerArray['###LABEL_ZIP###']=ucfirst($this->pi_getLL('zip'));
	$markerArray['###VALUE_ZIP###']=htmlspecialchars($user['zip']);
	$markerArray['###LABEL_ERROR_ZIP_IS_REQUIRED###']=$this->pi_getLL('zip_is_required');
	$markerArray['###LABEL_ADDRESS###']=ucfirst($this->pi_getLL('street_address'));
	$markerArray['###VALUE_ADDRESS###']=htmlspecialchars($user['street_name']);
	$markerArray['###LABEL_ERROR_ADDRESS_IS_REQUIRED###']=$this->pi_getLL('street_address_is_required');
	$markerArray['###LABEL_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number'));
	$markerArray['###VALUE_ADDRESS_NUMBER###']=htmlspecialchars($user['address_number']);
	$markerArray['###LABEL_ERROR_ADDRESS_NUMBER_IS_REQUIRED###']=$this->pi_getLL('street_number_is_required');
	$markerArray['###LABEL_ADDRESS_EXT###']=ucfirst($this->pi_getLL('address_extension'));
	$markerArray['###VALUE_ADDRESS_EXT###']=htmlspecialchars($user['address_ext']);
	$markerArray['###LABEL_CITY###']=ucfirst($this->pi_getLL('city'));
	$markerArray['###VALUE_CITY###']=htmlspecialchars($user['city']);
	$markerArray['###LABEL_ERROR_CITY_IS_REQUIRED###']=$this->pi_getLL('city_is_required');
	$markerArray['###LABEL_TELEPHONE###']=ucfirst($this->pi_getLL('telephone'));
	$markerArray['###VALUE_TELEPHONE###']=htmlspecialchars($user['telephone']);
	//
	$telephone_validation='';
	$mobile_validation='';
	if ($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE']) {
		if (!$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER']) {
			$telephone_validation=' required="required" data-h5-errorid="invalid-telephone" title="'.$this->pi_getLL('telephone_is_required').'"';
			$mobile_validation=' required="required" data-h5-errorid="invalid-mobile" title="'.$this->pi_getLL('mobile_must_be_x_digits_long').'"';
		} else {
			$telephone_validation=' required="required" data-h5-errorid="invalid-telephone" title="'.$this->pi_getLL('telephone_is_required').'" pattern=".{'.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].'}"';
			$mobile_validation=' required="required" data-h5-errorid="invalid-mobile" title="'.$this->pi_getLL('mobile_must_be_x_digits_long').'"';
		}
	}
	//
	$markerArray['###TELEPHONE_VALIDATION###']=$telephone_validation;
	$markerArray['###LABEL_MOBILE###']=ucfirst($this->pi_getLL('mobile'));
	$markerArray['###VALUE_MOBILE###']=htmlspecialchars($user['mobile']);
	$markerArray['###LABEL_EMAIL###']=ucfirst($this->pi_getLL('e-mail_address'));
	$markerArray['###VALUE_EMAIL###']=htmlspecialchars($user['email']);
	$markerArray['###LABEL_ERROR_EMAIL_IS_REQUIRED###']=$this->pi_getLL('email_is_required');
	$markerArray['###LABEL_DELIVERY_ADDRESS_TITLE###']=ucfirst($this->pi_getLL('delivery_address'));
	$markerArray['###LABEL_DELIVERY_TITLE###']=ucfirst($this->pi_getLL('title'));
	$markerArray['###DELIVERY_GENDER_MR_CHECKED###']=(($user['delivery_gender']=='m') ? 'checked' : '');
	$markerArray['###LABEL_DELIVERY_GENDER_MR###']=ucfirst($this->pi_getLL('mr'));
	$markerArray['###DELIVERY_GENDER_MRS_CHECKED###']=(($user['delivery_gender']=='f') ? 'checked' : '');
	$markerArray['###LABEL_DELIVERY_GENDER_MRS###']=ucfirst($this->pi_getLL('mrs'));


	$markerArray['###LABEL_DELIVERY_COMPANY###']=ucfirst($this->pi_getLL('company')).($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? '*' : '');
	$markerArray['###VALUE_DELIVERY_COMPANY###']=htmlspecialchars($user['delivery_company']);
	$markerArray['###DELIVERY_COMPANY_VALIDATION###']=($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? ' required="required" data-h5-errorid="invalid-delivery_company" title="'.$this->pi_getLL('company_is_required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')"' : '');
	$markerArray['###LABEL_DELIVERY_FIRST_NAME###']=ucfirst($this->pi_getLL('first_name'));
	$markerArray['###VALUE_DELIVERY_FIRST_NAME###']=htmlspecialchars($user['delivery_first_name']);
	$markerArray['###LABEL_DELIVERY_MIDDLE_NAME###']=ucfirst($this->pi_getLL('middle_name'));
	$markerArray['###VALUE_DELIVERY_MIDDLE_NAME###']=htmlspecialchars($user['delivery_middle_name']);
	$markerArray['###LABEL_DELIVERY_LAST_NAME###']=ucfirst($this->pi_getLL('last_name'));
	$markerArray['###VALUE_DELIVERY_LAST_NAME###']=htmlspecialchars($user['delivery_last_name']);
	$markerArray['###INPUT_DELIVERY_COUNTRY_BLOCK###']=$delivery_country_block;
	$markerArray['###LABEL_DELIVERY_ZIP###']=ucfirst($this->pi_getLL('zip'));
	$markerArray['###VALUE_DELIVERY_ZIP###']=htmlspecialchars($user['delivery_zip']);
	$markerArray['###LABEL_DELIVERY_ADDRESS###']=ucfirst($this->pi_getLL('street_address'));
	$markerArray['###VALUE_DELIVERY_ADDRESS###']=htmlspecialchars($user['delivery_street_name']);
	$markerArray['###LABEL_DELIVERY_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number'));
	$markerArray['###VALUE_DELIVERY_ADDRESS_NUMBER###']=htmlspecialchars($user['delivery_address_number']);
	$markerArray['###LABEL_DELIVERY_ADDRESS_EXT###']=ucfirst($this->pi_getLL('address_extension'));
	$markerArray['###VALUE_DELIVERY_ADDRESS_EXT###']=htmlspecialchars($user['delivery_address_ext']);
	$markerArray['###LABEL_DELIVERY_CITY###']=ucfirst($this->pi_getLL('city'));
	$markerArray['###VALUE_DELIVERY_CITY###']=htmlspecialchars($user['delivery_city']);
	$markerArray['###LABEL_DELIVERY_TELEPHONE###']=ucfirst($this->pi_getLL('telephone'));
	$markerArray['###VALUE_DELIVERY_TELEPHONE###']=htmlspecialchars($user['delivery_telephone']);
	$markerArray['###LABEL_DELIVERY_MOBILE###']=ucfirst($this->pi_getLL('mobile'));
	$markerArray['###VALUE_DELIVERY_MOBILE###']=htmlspecialchars($user['delivery_mobile']);
	$markerArray['###LABEL_DELIVERY_EMAIL###']=ucfirst($this->pi_getLL('e-mail_address'));
	$markerArray['###VALUE_DELIVERY_EMAIL###']=htmlspecialchars($user['delivery_email']);
	//
	$newsletter_subscribe='';
	if ($this->ms['MODULES']['DISPLAY_SUBSCRIBE_TO_NEWSLETTER_IN_CREATE_ACCOUNT']) {
		$newsletter_subscribe='
		<div class="msFrontNewsletterDetails">
			<h2 class="msFrontEditAccountHeading">'.$this->pi_getLL('newsletter').'</h2>
			<div class="row">
				<div class="account-field newsletter_checkbox col-sm-12">
					<div class="account-boxes">'.$this->pi_getLL('subscribe_to_our_newsletter_description').'.</div>
				</div>
				<div class="checkboxAgreement newsletter_checkbox_message col-sm-12">
					<input type="checkbox" name="tx_multishop_newsletter" id="tx_multishop_newsletter" value="1"'.($user['tx_multishop_newsletter']>0 ? ' checked="checked"' : '').' />
					<label class="account-value" for="tx_multishop_newsletter">'.$this->pi_getLL('subscribe_to_our_newsletter').'</label>
				</div>
			</div>
		</div>';
	}
	//
	$markerArray['###EDIT_ACCOUNT_NEWSLETTER_SUBSCRIBE###']=$newsletter_subscribe;
	$markerArray['###LABEL_LOGIN_DETAILS###']=$this->pi_getLL('login_details');
	$markerArray['###LABEL_USERNAME###']=ucfirst($this->pi_getLL('username'));
	$markerArray['###VALUE_USERNAME###']=htmlspecialchars($user['username']);
	$markerArray['###LABEL_PASSWORD###']=ucfirst($this->pi_getLL('password'));
	$markerArray['###LABEL_PASSWORD_CONFIRM###']=ucfirst($this->pi_getLL('repassword'));
	$markerArray['###LABEL_UPDATE_OR_REGISTER###']=($this->contentMisc=='edit_account' ? ucfirst($this->pi_getLL('update_account')) : ucfirst($this->pi_getLL('register')));
	//
	$content.=$this->cObj->substituteMarkerArray($template, $markerArray);
}
$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
?>