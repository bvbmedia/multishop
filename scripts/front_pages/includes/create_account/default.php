<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (mslib_fe::loggedin()) {
	// user is already signed in
	$content.=$this->pi_getLL('you_are_already_signed_in');
} else {
	if ($this->get['tx_multishop_pi1']['createAccountNonOptInCompleted']) {
		$customerSession=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_customer');
		$customer_id=$customerSession['customer_id'];
		$newCustomer=mslib_fe::getUser($customer_id);
		$page=mslib_fe::getCMScontent('create_account_thank_you_page', $GLOBALS['TSFE']->sys_language_uid);
		if ($page[0]['content']) {
			// loading the email confirmation letter eof
			// replacing the variables with dynamic values
			$array1=array();
			$array2=array();
			$array1[]='###GENDER_SALUTATION###';
			$array2[]=mslib_fe::genderSalutation($newCustomer['gender']);
			$array1[]='###BILLING_COMPANY###';
			$array2[]=$newCustomer['company'];
			$array1[]='###FULL_NAME###';
			$array2[]=$newCustomer['name'];
			$array1[]='###BILLING_NAME###';
			$array2[]=$newCustomer['name'];
			$array1[]='###BILLING_FIRST_NAME###';
			$array2[]=$newCustomer['first_name'];
			$array1[]='###BILLING_LAST_NAME###';
			$last_name=$newCustomer['last_name'];
			if ($newCustomer['middle_name']) {
				$last_name=$newCustomer['middle_name'].' '.$last_name;
			}
			$array2[]=$last_name;
			$array1[]='###CUSTOMER_EMAIL###';
			$array2[]=$newCustomer['email'];
			$array1[]='###BILLING_EMAIL###';
			$array2[]=$newCustomer['email'];
			$array1[]='###BILLING_ADDRESS###';
			$array2[]=$newCustomer['address'];
			$array1[]='###BILLING_TELEPHONE###';
			$array2[]=$newCustomer['telephone'];
			$array1[]='###BILLING_MOBILE###';
			$array2[]=$newCustomer['mobile'];
			$array1[]='###LONG_DATE###'; // ie woensdag 23 juni, 2010
			$long_date=strftime($this->pi_getLL('full_date_format'));
			$array2[]=$long_date;
			$array1[]='###CURRENT_DATE_LONG###'; // ie woensdag 23 juni, 2010
			$long_date=strftime($this->pi_getLL('full_date_format'));
			$array2[]=$long_date;
			$array1[]='###STORE_NAME###';
			$array2[]=$this->ms['MODULES']['STORE_NAME'];
			$array1[]='###CUSTOMER_ID###';
			$array2[]=$customer_id;
			if ($page[0]['name']) {
				$page[0]['name']=str_replace($array1, $array2, $page[0]['name']);
				$content.='<div class="main-heading"><h3>'.$page[0]['name'].'</h3></div>';
			}
			if ($page[0]['content']) {
				$page[0]['content']=str_replace($array1, $array2, $page[0]['content']);
				$content.=$page[0]['content'];
			}
		}
	} else {
		$erno=array();
		if ($this->post) {
			$this->post['email']=mslib_fe::RemoveXSS($this->post['email']);
			$mslib_user=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_user');
			$mslib_user->init($this);
			if ($this->ms['MODULES']['CREATE_ACCOUNT_REGULAR_USERNAME'] && isset($this->post['username']) && !empty($this->post['username'])) {
				$mslib_user->setUsername($this->post['username']);
			} else {
				$mslib_user->setUsername($this->post['email']);
			}
			$mslib_user->setEmail($this->post['email']);
			$mslib_user->setConfirmation_email($this->post['email_confirm']);
			$mslib_user->setGender($this->post['gender']);
			$mslib_user->setPassword($this->post['password']);
			$mslib_user->setConfirmation_password($this->post['password_confirm']);
			$mslib_user->setFirst_name($this->post['first_name']);
			$mslib_user->setMiddle_name($this->post['middle_name']);
			$mslib_user->setLast_name($this->post['last_name']);
			$mslib_user->setName($this->post['first_name'].' '.$this->post['middle_name'].' '.$this->post['last_name']);
			$mslib_user->setCompany($this->post['company']);
			$mslib_user->setCountry($this->post['country']);
			$mslib_user->setAddress($this->post['address']);
			$mslib_user->setAddress_number($this->post['address_number']);
			$mslib_user->setAddress_ext($this->post['address_ext']);
			$mslib_user->setZip($this->post['zip']);
			$mslib_user->setCity($this->post['city']);
			$mslib_user->setTelephone($this->post['telephone']);
			$mslib_user->setMobile($this->post['mobile']);
			$mslib_user->setNewsletter($this->post['tx_multishop_newsletter']);
			$mslib_user->setCaptcha_code($this->post['tx_multishop_pi1']['captcha_code']);
			$mslib_user->setBirthday($this->post['birthday']);
			$mslib_user->setCustomField('tx_multishop_vat_id', $this->post['tx_multishop_vat_id']);
			$mslib_user->setCustomField('tx_multishop_coc_id', $this->post['tx_multishop_coc_id']);
			$erno=$mslib_user->checkUserData();
			if ($this->ms['MODULES']['CREATE_ACCOUNT_DISCLAIMER'] && !isset($this->post['tx_multishop_pi1']['create_account_disclaimer'])) {
				$erno[]=$this->pi_getLL('you_havent_accepted_the_create_account_disclaimer');
			}
			if ($this->ms['MODULES']['DISPLAY_ACCEPT_GENERAL_CONDITIONS_IN_CREATE_ACCOUNT'] && !isset($this->post['accept_general_conditions'])) {
				$erno[]=$this->pi_getLL('you_havent_accepted_the_general_conditions');
			}
			if ($this->ms['MODULES']['RIGHT_OF_WITHDRAWAL_CHECKBOX_IN_CREATE_ACCOUNT'] && !$this->post['tx_multishop_pi1']['right_of_withdrawal']) {
				$erno[]=$this->pi_getLL('you_havent_accepted_the_right_of_withdrawal').'.';
			}
			if (!count($erno)) {
				$customer_id=$mslib_user->saveUserData();
				if ($customer_id) {
					// save as billing address and default address, later on
					// customer can edit the profile
					$res=$mslib_user->saveUserBillingAddress($customer_id);
					if ($res) {
						$send_email_confirmation=mslib_fe::sendCreateAccountConfirmationLetter($customer_id, $this->post['password']);
						if ($send_email_confirmation) {
							// mail a copy to the merchant
							/*
							$merchant=array();
							$merchant['name']=$this->ms['MODULES']['STORE_NAME'];
							$merchant['email']=$this->ms['MODULES']['STORE_EMAIL'];
							mslib_fe::mailUser($merchant, 'Copy for merchant: '.$page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME']);
							*/
							// save customer id in session and redirect to thank you page
							$customerSession=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_customer');
							$customerSession['customer_id']=$customer_id;
							$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_customer', $customerSession);
							$GLOBALS['TSFE']->storeSessionData();
							// redirect to the thank you page
							$link=mslib_fe::typolink('', '&tx_multishop_pi1[createAccountNonOptInCompleted]=1', 1);
							if ($link) {
								header("Location: ".$this->FULL_HTTP_URL.$link);
							}
							exit();
						}
					}
				}
			}
		}
		if (!$this->post or count($erno)) {
			$GLOBALS['TSFE']->additionalHeaderData[]='
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery(\'#checkout\').h5Validate();
						'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY'] ? '
						jQuery("#date_of_birth_visual").datepicker({
							dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'm/d/Y').'",
							altField: "#date_of_birth",
							altFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							showOtherMonths: true,
							yearRange: "'.(date("Y")-150).':'.date("Y").'"
						});
						' : '').'
					}); //end of first load
				</script>';
			if (count($erno)>0) {
				$content.='<div class="alert alert-danger">';
				$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
				foreach ($erno as $item) {
					$content.='<li>'.$item.'</li>';
				}
				$content.='</ul>';
				$content.='</div>';
			}
			//
			if ($this->conf['create_account_tmpl_path']) {
				$template=$this->cObj->fileResource($this->conf['create_account_tmpl_path']);
			} else {
				$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/create_account.tmpl');
			}
			//
			$vat_input_block='';
			if ($this->ms['MODULES']['CHECKOUT_DISPLAY_VAT_ID_INPUT']) {
				$vat_input_block=' <div class="account-field col-sm-6" id="input-tx_multishop_vat_id">
				<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.ucfirst($this->pi_getLL('vat_id', 'VAT ID')).'</label>
				<input type="text" name="tx_multishop_vat_id" class="tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.htmlspecialchars($this->post['tx_multishop_vat_id']).'"/></div>';
			}
			$coc_input_block='';
			if ($this->ms['MODULES']['CHECKOUT_DISPLAY_COC_ID_INPUT']) {
				$coc_input_block=' <div class="account-field col-sm-6" id="input-tx_multishop_coc_id">
				<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">'.ucfirst($this->pi_getLL('coc_id', 'KvK ID')).'</label>
				<input type="text" name="tx_multishop_coc_id" class="tx_multishop_coc_id" id="tx_multishop_coc_id" value="'.htmlspecialchars($this->post['tx_multishop_coc_id']).'"/></div>';
			}
			//
			$markerArray=array();
			//
			$birthday_block='';
			if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
				$birthdayVisual='';
				$birthday='';
				if ($user['date_of_birth']) {
					$birthdayVisual=strftime('%x', $user['date_of_birth']);
					$birthday=date("Y-m-d", $user['date_of_birth']);
				}
				$birthday_block='<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'*</label>
				<input type="text" name="date_of_birth_visual" class="birthday" id="date_of_birth_visual" value="" >
				<input type="hidden" name="date_of_birth" class="birthday" id="date_of_birth" value="" >';
			}
			//
			$markerArray['###BIRTHDAY_BLOCK###']=$birthday_block;
			$markerArray['###CREATE_ACCOUNT_FORM_URL###']=mslib_fe::typolink();
			$markerArray['###LABEL_PERSONAL_DETAILS###']=$this->pi_getLL('personal_details');
			$markerArray['###LABEL_PERSONAL_DETAILS_DESCRIPTION###']=$this->pi_getLL('personal_details_description');
			$markerArray['###LABEL_TITLE###']=ucfirst($this->pi_getLL('title')).'<span class="text-danger">*</span>';
			$markerArray['###GENDER_MR_CHECKED###']=($this->post['gender']=='m' ? ' checked="checked"' : '');
			$markerArray['###LABEL_GENDER_MR###']=$this->pi_getLL('mr');
			$markerArray['###GENDER_MRS_CHECKED###']=($this->post['gender']=='f' ? ' checked="checked"' : '');
			$markerArray['###LABEL_GENDER_MRS###']=$this->pi_getLL('mrs');
			$markerArray['###LABEL_FIRST_NAME###']=ucfirst($this->pi_getLL('first_name')).'<span class="text-danger">*</span>';
			$markerArray['###VALUE_FIRST_NAME###']=htmlspecialchars($this->post['first_name']);
			$markerArray['###LABEL_MIDDLE_NAME###']=ucfirst($this->pi_getLL('middle_name'));
			$markerArray['###VALUE_MIDDLE_NAME###']=htmlspecialchars($this->post['middle_name']);
			$markerArray['###LABEL_LAST_NAME###']=ucfirst($this->pi_getLL('last_name')).'<span class="text-danger">*</span>';
			$markerArray['###VALUE_LAST_NAME###']=htmlspecialchars($this->post['last_name']);
			$markerArray['###LABEL_COMPANY###']=$this->pi_getLL('company').($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? '<span class="text-danger">*</span>' : '');
			$markerArray['###VALUE_COMPANY###']=htmlspecialchars($this->post['company']);
			$markerArray['###INPUT_VAT_BLOCK###']=$vat_input_block;
			$markerArray['###INPUT_COC_BLOCK###']=$coc_input_block;
			//
			// load enabled countries to array
			$str2="SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
			//$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$enabled_countries=array();
			while ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) {
				$enabled_countries[]=$row2;
			}
			// load enabled countries to array eof
			$country_block='';
			if (count($enabled_countries)==1) {
				$country_block.='<input name="country" id="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
			} else {
				$country_block.='<div class="account-field col-sm-8" id="input-country">
							<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'<span class="text-danger">*</span></label>';
				$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
				if (!$this->post) {
					$this->post['country']=$default_country['cn_short_en'];
				}
				foreach ($enabled_countries as $country) {
					$tmpcontent.='<option value="'.mslib_befe::strtoupper($country['cn_short_en']).'" '.((mslib_befe::strtolower($this->post['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
				}
				if ($tmpcontent) {
					$country_block.='<select name="country" id="country" class="country" required="required">
						<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
						'.$tmpcontent.'
					</select>';
				}
				$country_block.='</div>';
			}
			//
			$markerArray['###INPUT_COUNTRY_BLOCK###']=$country_block;
			//
			$markerArray['###LABEL_ZIP###']=ucfirst($this->pi_getLL('zip')).'<span class="text-danger">*</span>';
			$markerArray['###VALUE_ZIP###']=htmlspecialchars($this->post['zip']);
			$markerArray['###LABEL_ADDRESS###']=ucfirst($this->pi_getLL('street_address')).'<span class="text-danger">*</span>';
			$markerArray['###VALUE_ADDRESS###']=htmlspecialchars($this->post['address']);
			$markerArray['###LABEL_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number')).'<span class="text-danger">*</span>';
			$markerArray['###VALUE_ADDRESS_NUMBER###']=htmlspecialchars($this->post['address_number']);
			$markerArray['###LABEL_ADDRESS_EXT###']=ucfirst($this->pi_getLL('address_extension'));
			$markerArray['###VALUE_ADDRESS_EXT###']=htmlspecialchars($this->post['address_ext']);
			$markerArray['###LABEL_CITY###']=ucfirst($this->pi_getLL('city')).'<span class="text-danger">*</span>';
			$markerArray['###VALUE_CITY###']=htmlspecialchars($this->post['city']);
			$markerArray['###LABEL_TELEPHONE###']=ucfirst($this->pi_getLL('telephone')).($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] == '1' ? '<span class="text-danger">*</span>' : '');
			$markerArray['###VALUE_TELEPHONE###']=htmlspecialchars($this->post['telephone']);
			$telephone_validation='';
			if ($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] == '1') {
				if (!$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER']) {
					$telephone_validation=' required="required"';
				} else {
					$telephone_validation=' required="required"';
				}
			}
			$markerArray['###TELEPHONE_VALIDATION###']=$telephone_validation;
			//
			$markerArray['###LABEL_MOBILE###']=ucfirst($this->pi_getLL('mobile'));
			$markerArray['###VALUE_MOBILE###']=htmlspecialchars($this->post['mobile']);
			// login details
			$markerArray['###LABEL_LOGIN_DETAILS###']=$this->pi_getLL('login_details');
			$markerArray['###LABEL_LOGIN_DETAILS_DESCRIPTION###']=$this->pi_getLL('login_details_description');
			$markerArray['###LABEL_EMAIL###']=$this->pi_getLL('e-mail_address').'<span class="text-danger">*</span>';
			$markerArray['###VALUE_EMAIL###']=htmlspecialchars($this->post['email']);
			$markerArray['###LABEL_EMAIL_CONFIRM###']=$this->pi_getLL('confirm_email_address').'<span class="text-danger">*</span>';
			$markerArray['###VALUE_EMAIL_CONFIRM###']=htmlspecialchars($this->post['email_confirm']);
			$markerArray['###LABEL_PASSWORD###']=$this->pi_getLL('password').'<span class="text-danger">*</span>';
			$markerArray['###VALUE_PASSWORD###']=htmlspecialchars($this->post['password']);
			$markerArray['###LABEL_PASSWORD_CONFIRM###']=$this->pi_getLL('confirm_password').'<span class="text-danger">*</span>';
			$markerArray['###VALUE_PASSWORD_CONFIRM###']=htmlspecialchars($this->post['password_confirm']);
			//
			$newsletter_subscribe='';
			if ($this->ms['MODULES']['DISPLAY_SUBSCRIBE_TO_NEWSLETTER_IN_CREATE_ACCOUNT']) {
				$newsletter_subscribe.='<div class="account-field newsletter_checkbox">
					<div class="account-heading">
						<h2>'.$this->pi_getLL('newsletter').'</h2>
					</div>
					<div class="account-boxes">'.$this->pi_getLL('subscribe_to_our_newsletter_description').'.</div>
				</div>
				<div class="checkboxAgreement newsletter_checkbox_message">
					<div class="checkbox checkbox-success">
						<input type="checkbox" name="tx_multishop_newsletter" id="tx_multishop_newsletter" value="1"'.(!isset($this->post['tx_multishop_newsletter']) || $this->post['tx_multishop_newsletter'] ? ' checked="checked"' : '').' />
						<label class="account-value" for="tx_multishop_newsletter">'.$this->pi_getLL('subscribe_to_our_newsletter').'</label>
					</div>
				</div>';
			}
			//
			$markerArray['###CREATE_ACCOUNT_NEWSLETTER_SUBSCRIBE###']=$newsletter_subscribe;
			//
			$account_disclaimer='';
			if ($this->ms['MODULES']['CREATE_ACCOUNT_DISCLAIMER']) {
				$account_disclaimer.='<hr>
				<div class="checkboxAgreement accept_general_conditions_container">
					<div class="checkbox checkbox-success">
					<input name="tx_multishop_pi1[create_account_disclaimer]" id="create_account_disclaimer" type="checkbox" value="1" required="required" />
					<label for="create_account_disclaimer">'.$this->pi_getLL('click_here_if_you_agree_the_create_account_disclaimer').'<span class="text-danger">*</span>';
				$page=mslib_fe::getCMScontent('create_account_disclaimer', $GLOBALS['TSFE']->sys_language_uid);
				if ($page[0]['content']) {
					$account_disclaimer.=' (<a href="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=info&tx_multishop_pi1[cms_hash]='.$page[0]['hash']).'" target="_blank" class="read_disclaimer">'.$this->pi_getLL('view_create_account_disclaimer').'</a>)';
				}
				$account_disclaimer.='</div></div>';
			}
			//
			$markerArray['###CREATE_ACCOUNT_DISCLAIMER###']=$account_disclaimer;
			//
			$right_of_withdrawal='';
			if ($this->ms['MODULES']['RIGHT_OF_WITHDRAWAL_CHECKBOX_IN_CREATE_ACCOUNT']) {
				$page=mslib_fe::getCMScontent('right_of_withdrawal', $GLOBALS['TSFE']->sys_language_uid);
				if ($page[0]['content']) {
					$right_of_withdrawal.='
						<hr>
						<div class="checkboxAgreement accept_general_conditions_container">
							<div class="checkbox checkbox-success">
								<input name="tx_multishop_pi1[right_of_withdrawal]" id="right_of_withdrawal_checkbox_in_checkout" type="checkbox" value="1" />
								<label for="right_of_withdrawal_checkbox_in_checkout">'.$this->pi_getLL('click_here_if_you_agree_the_right_of_withdrawal');
					$right_of_withdrawal.=' (<a href="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=info&tx_multishop_pi1[cms_hash]='.$page[0]['hash']).'" target="_blank" class="read_general_conditions">'.$this->pi_getLL('view_right_of_withdrawal').'</a>)';
					$right_of_withdrawal.='</label>
							</div>
						</div>';
				}
			}
			//
			$markerArray['###RIGHT_OF_WITHDRAWAL###']=$right_of_withdrawal;
			//
			$accept_general_conditions='';
			if ($this->ms['MODULES']['DISPLAY_ACCEPT_GENERAL_CONDITIONS_IN_CREATE_ACCOUNT']) {
				$accept_general_conditions.='
				<hr>
				<div class="checkboxAgreement accept_general_conditions_container">
					<div class="checkbox checkbox-success">
						<input name="accept_general_conditions" id="accept_general_conditions" type="checkbox" value="1" />
						<label for="accept_general_conditions">'.$this->pi_getLL('click_here_if_you_agree_the_general_conditions');
				$page=mslib_fe::getCMScontent('general_conditions', $GLOBALS['TSFE']->sys_language_uid);
				if ($page[0]['content']) {
					$accept_general_conditions.=' (<a href="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=info&tx_multishop_pi1[cms_hash]='.$page[0]['hash']).'" target="_blank" class="read_general_conditions">'.$this->pi_getLL('view_general_conditions').'</a>)';
				}
				$accept_general_conditions.='</div></div>';
			}
			//
			$markerArray['###ACCEPT_GENERAL_CONDITIONS###']=$accept_general_conditions;
			//
			$privacy_statement_link='';
			if ($this->ms['MODULES']['DISPLAY_PRIVACY_STATEMENT_LINK_ON_CREATE_ACCOUNT_PAGE']) {
				$page=mslib_fe::getCMScontent('privacy_statement', $GLOBALS['TSFE']->sys_language_uid);
				if ($page[0]['content']) {
					$privacy_statement_link.='<div class="privacy_statement_link"><a href="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=info&tx_multishop_pi1[cms_hash]='.$page[0]['hash']).'" target="_blank" class="read_privacy_statement"><span>'.$this->pi_getLL('view_privacy_statement').'</pan></a></div>';
				}
			}
			$markerArray['###PRIVACY_STATEMENT_LINK###']=$privacy_statement_link;
			//
			$create_account_username_block='';
			if ($this->ms['MODULES']['CREATE_ACCOUNT_REGULAR_USERNAME']) {
				$create_account_username_block='<div class="account-field col-sm-12" id="user-username">
					<label class="account-username" for="username">'.ucfirst($this->pi_getLL('username')).'<span class="text-danger">*</span></label>
					<input type="text" name="username" class="username" id="username" value="'.$this->post['username'].'" required="required" />
					<span class="error-space"></span>
				</div>';

			}
			$markerArray['###USERNAME_INPUT###']=$create_account_username_block;
			//
			$markerArray['###CREATE_ACCOUNT_CAPTCHA_URL###']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=captcha');
			$markerArray['###LABEL_CAPTCHA_PLACEHOLDER###']=$this->pi_getLL('captcha_code_placeholder');
			$markerArray['###LABEL_BACK###']=$this->pi_getLL('back');
			$markerArray['###LABEL_REGISTER###']=$this->pi_getLL('register');
			//
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/create_account/default.php']['createAccountPostHook'])) {
				$params=array(
					'markerArray'=>&$markerArray
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/create_account/default.php']['createAccountPostHook'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
			//
			$content.=$this->cObj->substituteMarkerArray($template, $markerArray);
		}
	}
}
$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
?>