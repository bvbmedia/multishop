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
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid = '.$GLOBALS["TSFE"]->fe_user->user['uid'], $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
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
			}); //end of first load
		</script>';
	$content.='<div class="error_msg" style="display:none">';
	$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
	$content.='<li class="item-error" style="display:none"></li>';
	$content.='</ul></div>';
	$content.='
	<div id="live-validation" class="editAccount">
	<form action="" method="post" name="checkout" class="AdvancedForm" id="checkout">
	<div class="row">
	<div id="live-validation-l" class="col-md-6">
	<div class="msFrontBillingDetails">
	<h2 class="msFrontEditAccountHeading">'.$this->pi_getLL('billing_address').'</h2>
	<div class="row">
	<div id="input-gender" class="account-field col-sm-12">
		<span id="ValidRadio" class="InputGroup">
			<label for="radio" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
			<input type="radio" class="InputGroup" name="gender" value="m" class="account-gender-radio" id="radio" '.(($user['gender']=='m') ? 'checked' : '').' required="required" data-h5-errorid="invalid-gender" title="'.$this->pi_getLL('gender_is_required', 'Title is required').'">
			<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
			<input type="radio" name="gender" value="f" class="InputGroup" id="radio2" '.(($user['gender']=='f') ? 'checked' : '').'>
			<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
			<div id="invalid-gender" class="error-space" style="display:none"></div>
		</span>';
	if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
		$content.='<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'*</label>
			<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.htmlspecialchars($user['date_of_birth']).'" >
			<input type="hidden" name="birthday" class="birthday" id="birthday" value="'.htmlspecialchars($user['date_of_birth']).'" >';
	}
	$content.='</div>
	<div id="input-company" class="account-field col-sm-12">
		<label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
		<input type="text" name="company" class="company" id="company" value="'.htmlspecialchars($user['company']).'"/>	
	</div>
	<div id="input-firstname" class="account-field col-sm-4">
		<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
		<input type="text" name="first_name" class="first-name" id="first_name" value="'.htmlspecialchars($user['first_name']).'" required="required" data-h5-errorid="invalid-first_name" title="'.$this->pi_getLL('first_name_required').'"><div id="invalid-first_name" class="error-space" style="display:none"></div>
	</div>
	<div id="input-middlename" class="account-field col-sm-4">
		<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
		<input type="text" name="middle_name" id="middle_name" class="middle_name" value="'.htmlspecialchars($user['middle_name']).'">
	</div>
	<div id="input-lastname" class="account-field col-sm-4">
		<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
		<input type="text" name="last_name" id="last_name" class="last-name" value="'.htmlspecialchars($user['last_name']).'" required="required" data-h5-errorid="invalid-last_name" title="'.$this->pi_getLL('surname_is_required').'"><div id="invalid-last_name" class="error-space" style="display:none"></div>
	</div>
	';
	// load countries
	if (count($enabled_countries)==1) {
		$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
		$content.='<input name="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		$content.='<input name="delivery_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
	} else {
		foreach ($enabled_countries as $country) {
			$tmpcontent_con.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($user['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
			$tmpcontent_con_delivery.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($user['delivery_country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
		}
		if ($tmpcontent_con) {
			$content.='
			<div id="input-country" class="account-field col-sm-8">
			<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
			<select name="country" id="country" class="country" required="required" data-h5-errorid="invalid-country" title="'.$this->pi_getLL('country_is_required').'">
			<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
			'.$tmpcontent_con.'
			</select>
			<div id="invalid-country" class="error-space" style="display:none"></div>
			</div>';
		}
	}
	// country eof
	$content.='
	<div id="input-zip" class="account-field col-sm-4">
		<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
		<input type="text" name="zip" id="zip" class="zip" value="'.htmlspecialchars($user['zip']).'" required="required" data-h5-errorid="invalid-zip" title="'.$this->pi_getLL('zip_is_required').'"><div id="invalid-zip" class="error-space" style="display:none"></div>
	</div>
	<div id="input-address" class="account-field col-sm-3">
		<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
		<input type="text" name="street_name" id="address" class="address" value="'.htmlspecialchars($user['street_name']).'" required="required" data-h5-errorid="invalid-address" title="'.$this->pi_getLL('street_address_is_required').'"><div id="invalid-address" class="error-space" style="display:none"></div>
	</div>
	<div id="input-housenumber" class="account-field col-sm-3">
		<label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
		<input type="text" name="address_number" id="address_number" class="address-number" value="'.htmlspecialchars($user['address_number']).'" required="required" data-h5-errorid="invalid-address_number" title="'.$this->pi_getLL('street_number_is_required').'"><div id="invalid-address_number" class="error-space" style="display:none"></div>
	</div>
	<div id="input-extension" class="account-field col-sm-3">
		<label for="address-ext" id="account-ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
		<input type="text" name="address_ext" class="address-ext" id="address-ext" value="'.htmlspecialchars($user['address_ext']).'"/>	
	</div>
	<div id="input-city" class="account-field col-sm-3">
		<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
		<input type="text" name="city" id="city" class="city" value="'.htmlspecialchars($user['city']).'" required="required" data-h5-errorid="invalid-city" title="'.$this->pi_getLL('city_is_required').'"><div id="invalid-city" class="error-space" style="display:none"></div>
	</div>';

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
	$content.='
	<div id="input-phone" class="account-field col-sm-6">
		<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
		<input type="text" name="telephone" id="telephone" class="telephone" value="'.htmlspecialchars($user['telephone']).'"'.$telephone_validation.'><div id="invalid-telephone" class="error-space" style="display:none"></div>
	</div>
	<div id="input-mobile" class="account-field col-sm-6">
		<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
		<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($user['mobile']).'">
	</div>
	<div id="input-email" class="account-field col-sm-12">
		<label for="email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'*</label>
		<input type="text" name="email" id="email" class="email" value="'.htmlspecialchars($user['email']).'" required="required" data-h5-errorid="invalid-email" title="'.$this->pi_getLL('email_is_required').'"><div id="invalid-email" class="error-space" style="display:none"></div>
	</div>
	</div>
	</div>
	</div>';
	$tmpcontent.='
	<div class="row">
	<div id="input-dgender" class="account-field col-sm-12">
		<span id="delivery_ValidRadio" class="InputGroup">
			<label for="delivery_gender" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
			<input type="radio" class="delivery_InputGroup" name="delivery_gender" value="m" class="account-gender-radio" id="delivery_radio" '.(($user['delivery_gender']=='m') ? 'checked' : '').'>
			<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
			<input type="radio" name="delivery_gender" value="f" class="delivery_InputGroup" id="radio2" '.(($user['delivery_gender']=='f') ? 'checked' : '').'>
			<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
		</span>
		<div id="invalid-delivery_gender" class="error-space" style="display:none"></div>
	</div>
	<div id="input-dcompany" class="account-field col-sm-12">
		<label for="delivery_company">'.ucfirst($this->pi_getLL('company')).'</label>
		<input type="text" name="delivery_company" id="delivery_company" class="delivery_company" value="'.htmlspecialchars($user['delivery_company']).'">
	</div>
	<div id="input-dfirstname" class="account-field col-sm-4">
		<label class="account-firstname" for="delivery_first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
		<input type="text" name="delivery_first_name" class="delivery_first-name left-this" id="delivery_first_name" value="'.htmlspecialchars($user['delivery_first_name']).'" ><div id="invalid-delivery_first_name" class="error-space" style="display:none"></div>
	</div>
	<div id="input-dmiddlename" class="account-field col-sm-4">
		<label class="account-middlename" for="delivery_middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
		<input type="text" name="delivery_middle_name" id="delivery_middle_name" class="delivery_middle_name left-this" value="'.htmlspecialchars($user['delivery_middle_name']).'">
	</div>
	<div id="input-dlastname" class="account-field col-sm-4">
		<label class="account-lastname" for="delivery_last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
		<input type="text" name="delivery_last_name" id="delivery_last_name" class="delivery_last-name left-this" value="'.htmlspecialchars($user['delivery_last_name']).'" ><div id="invalid-delivery_last_name" class="error-space" style="display:none"></div>
	</div>
	';
	if ($tmpcontent_con) {
		$tmpcontent.='
		<div id="input-dcountry" class="account-field col-sm-8">
		<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
		<select name="delivery_country" id="delivery_country" class="delivery_country">
		<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
		'.$tmpcontent_con_delivery.'
		</select>
		<div id="invalid-delivery_country" class="error-space" style="display:none"></div>
		</div>';
	}
	$tmpcontent.='
	<div id="input-dzip" class="account-field col-sm-4">
		<label for="delivery_zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
		<input type="text" name="delivery_zip" id="delivery_zip" class="delivery_zip left-this" value="'.htmlspecialchars($user['delivery_zip']).'"><div id="invalid-delivery_zip" class="error-space" style="display:none"></div>
	</div>
	<div id="input-daddress" class="account-field col-sm-3">
		<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
		<input  type="text" name="delivery_street_name" id="delivery_address" class="delivery_address left-this" value="'.htmlspecialchars($user['delivery_street_name']).'"><div id="invalid-delivery_address" class="error-space" style="display:none"></div>
	</div>
	<div id="input-dhousenumber" class="account-field col-sm-3">
		<label class="delivery_account-addressnumber" for="delivery_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
		<input type="text" name="delivery_address_number" id="delivery_address_number" class="delivery_address-number" value="'.htmlspecialchars($user['delivery_address_number']).'" ><div id="invalid-delivery_address_number" class="error-space" style="display:none"></div>
	</div>
	<div id="input-dextension" class="account-field col-sm-3">
		<label for="delivery_address-ext" id="delivery_account-ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
		<input type="text" name="delivery_address_ext" class="delivery_address-ext" id="delivery_address-ext" value="'.htmlspecialchars($user['delivery_address_ext']).'"/>
	</div>		

	<div id="input-dcity" class="account-field col-sm-3">
		<label class="account-city" for="delivery_city">'.ucfirst($this->pi_getLL('city')).'*</label>
		<input type="text" name="delivery_city" id="delivery_city" class="delivery_city" value="'.htmlspecialchars($user['delivery_city']).'" ><div id="invalid-delivery_city" class="error-space" style="display:none"></div>
	</div>
	<div id="input-dphone" class="account-field col-sm-6">
		<label for="delivery_telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
		<input type="text" name="delivery_telephone" id="delivery_telephone" class="delivery_telephone" value="'.htmlspecialchars($user['delivery_telephone']).'"><div id="invalid-delivery_telephone" class="error-space" style="display:none"></div>
	</div>
	<div id="input-dmobile" class="account-field col-sm-6">
		<label for="delivery_mobile" class="account_mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
		<input type="text" name="delivery_mobile" id="delivery_mobile" class="delivery_mobile" value="'.htmlspecialchars($user['delivery_mobile']).'">
	</div>
	<div id="input-demail" class="account-field col-sm-12">
		<label for="delivery_email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'*</label>
		<input type="text" name="delivery_email" id="delivery_email" class="delivery_email" value="'.htmlspecialchars($user['delivery_email']).'"/>
	</div>
	</div>
	</div>
	';
	$content.='<div id="live-validation-r" class="col-md-6"><div id="delivery_address_category"><h2 class="msFrontEditAccountHeading">'.$this->pi_getLL('delivery_address').'</h2>'.$tmpcontent.'
	<script type="text/javascript">
		jQuery(document).ready(function($) {
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
		});
	</script>
	</div>
	</div>
	<div class="msFrontUserNameDetails">
		<h2 class="msFrontEditAccountHeading">'.$this->pi_getLL('login_details').'</h2>
		<div class="row">
			<div id="input-username" class="account-field col-sm-12">
				<label for="username" id="account-username">'.ucfirst($this->pi_getLL('username')).'</label>
				<input type="text" name="username" class="username" id="username" value="'.htmlspecialchars($user['username']).'" readonly />
			</div>
			<div id="input-password" class="account-field col-sm-12">
				<label for="password" id="account-password">'.ucfirst($this->pi_getLL('password')).'</label>
				<input type="password" name="password" class="password" id="password" value="" />
			</div>
			<div id="input-password" class="account-field col-sm-12">
				<label for="repassword" id="account-password">'.ucfirst($this->pi_getLL('repassword')).'</label>
				<input type="password" name="repassword" class="repassword" id="repassword" value="" />
			</div>
		</div>
	</div>
	<div id="bottom-navigation">
		<input type="hidden" id="user_id" value="'.$user['ses_userid'].'" name="user_id"/>
		<span class="msFrontButton continueState arrowRight arrowPosLeft" id="submit"><input type="submit" value="'.($this->contentMisc=='edit_account' ? ucfirst($this->pi_getLL('update_account')) : ucfirst($this->pi_getLL('register'))).'"/></span>
	</div>
	</form>
	</div>';
	// end form
}
$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
?>