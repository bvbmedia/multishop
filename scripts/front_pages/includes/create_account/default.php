<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');
if (mslib_fe::loggedin()) {
	// user is already signed in
	$content.=$this->pi_getLL('you_are_already_signed_in');
} else {
	$erno=array();
	if ($this->post) {
		$mslib_user = t3lib_div::makeInstance('tx_mslib_user');
		$mslib_user->init($this);		
		$mslib_user->setUsername($this->post['email']);
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
		$erno = $mslib_user->checkUserData();
		if (!count($erno)) {
			$customer_id = $mslib_user->saveUserData();
			if ($customer_id) {
				$newCustomer = mslib_fe::getUser($customer_id);			
				// save as billing address and default address, later on
				// customer can edit the profile
				$res = $mslib_user->saveUserBillingAddress($customer_id);
				if ($res) {
					$page=mslib_fe::getCMScontent('email_create_account_confirmation',$GLOBALS['TSFE']->sys_language_uid);
					if ($page[0]['content']) {
						// loading the email confirmation letter eof
						// replacing the variables with dynamic values
						$array1=array();
						$array2=array();

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
						$link = $this->FULL_HTTP_URL.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=confirm_create_account&tx_multishop_pi1[hash]='.$newCustomer['tx_multishop_code']);
						$array1[] = '###LINK###';
						$array2[] = '<a href="'.$link.'">'.htmlspecialchars($this->pi_getLL('click_here_to_confirm_registration')).'</a>';
						$array1[] = '###CONFIRMATION_LINK###';
						$array2[] = '<a href="'.$link.'">'.htmlspecialchars($this->pi_getLL('click_here_to_confirm_registration')).'</a>';
						if($page[0]['content']) {
							$page[0]['content'] = str_replace($array1,$array2,$page[0]['content']);
						}
						if($page[0]['name']) {
							$page[0]['name'] = str_replace($array1,$array2,$page[0]['name']);
						}
						$user=array();
						$user['name']	= $newCustomer['first_name'];
						$user['email']	= $newCustomer['email'];
						mslib_fe::mailUser($user,$page[0]['name'],$page[0]['content'],$this->ms['MODULES']['STORE_EMAIL'],$this->ms['MODULES']['STORE_NAME']);
						// mail a copy to the merchant
						$merchant=array();
						$merchant['name']	=$this->ms['MODULES']['STORE_NAME'];
						$merchant['email']	=$this->ms['MODULES']['STORE_EMAIL'];
						mslib_fe::mailUser($merchant,$page[0]['name'],$page[0]['content'],$this->ms['MODULES']['STORE_EMAIL'],$this->ms['MODULES']['STORE_NAME']);
						// display the thank you page
						$page=mslib_fe::getCMScontent('create_account_thank_you_page',$GLOBALS['TSFE']->sys_language_uid);
						if ($page[0]['content']) {
							// loading the email confirmation letter eof
							// replacing the variables with dynamic values
							$array1=array();
							$array2=array();
								
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
								$page[0]['name']=str_replace($array1,$array2,$page[0]['name']);
								$content.='<div class="main-heading"><h3>'.$page[0]['name'].'</h3></div>';
							}
							if ($page[0]['content']) {
								$page[0]['content']=str_replace($array1,$array2,$page[0]['content']);
								$content.=$page[0]['content'];
							}
						}
					}
				}	
			}
		}
	}
	if (!$this->post or count($erno)) {
		if (count($erno) > 0) {
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		}
		$content.='
		<div id="live-validation-create-account">
		  <form action="'.mslib_fe::typolink().'" method="post" name="create_account" class="AdvancedForm" id="create-account">
			<div id="live-validation_l">
			  <div class="account-boxes">
				<div class="account-heading">
				  <h2>'.$this->pi_getLL('personal_details').'</h2>
				</div>
				<div class="account-boxes">'.$this->pi_getLL('personal_details_description').'.</div>
			  </div>
			  <div class="account-field" id="input-gender"> <span id="ValidRadio" class="InputGroup">
				<label for="gender_mr" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
				<input type="radio" class="InputGroup" name="gender" value="m" id="gender_mr"'.($this->post['gender']=='m'?' checked="checked"':'').' />
				<label class="account-male" for="gender_mr">'.$this->pi_getLL('mr').'</label>
				<input type="radio" name="gender" value="f" class="InputGroup" id="gender_mrs"'.($this->post['gender']=='f'?' checked="checked"':'').' />
				<label class="account-female" for="gender_mrs">'.$this->pi_getLL('mrs').'</label>
				</span> <span class="error-space"></span></div>
			  <div class="account-field" id="input-firstname">
				<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
				<input type="text" name="first_name" class="first-name" id="first_name" value="'.htmlspecialchars($this->post['first_name']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-middlename">
				<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
				<input type="text" name="middle_name" id="middle_name" class="middle_name" value="'.htmlspecialchars($this->post['middle_name']).'">
				<span class="account-desc"></span> <span class="error-space"></span></div>
			  <div class="account-field" id="input-lastname">
				<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
				<input type="text" name="last_name" id="last_name" class="last-name" value="'.htmlspecialchars($this->post['last_name']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-company">
				<label for="company" id="account-company">'.$this->pi_getLL('company').'</label>
				<input type="text" name="company" class="company" id="company" value="'.htmlspecialchars($this->post['company']).'" />
				<span class="error-space"></span></div>							
		';
				// load enabled countries to array
				$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$enabled_countries=array();
				while ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) {
					$enabled_countries[]=$row2;
				}
				// load enabled countries to array eof
				if (count($enabled_countries) ==1) {
					$content.='<input name="country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
				} else {
					$content.='
					  <div class="account-field" id="input-country">
						<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label> 						
					';
					$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
					if (!$this->post) {
						$this->post['country']=$default_country['cn_short_en'];
					}
					foreach ($enabled_countries as $country) {
						$tmpcontent.='<option value="'.t3lib_div::strtoupper($country['cn_short_en']).'" '.((t3lib_div::strtolower($this->post['country'])==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$country['cn_short_en'])).'</option>';
					}
					if ($tmpcontent) {
						$content.='
						<select name="country" class="country">
							<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
							'.$tmpcontent.'
						</select>
						';
					}			
					$content.='	
					  </div>				
					  ';
				}
			$content.='	
			  <div class="account-field" id="input-address">
				<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
				<input type="text" name="address" id="address" class="address" value="'.htmlspecialchars($this->post['address']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-housenumber">
				<label class="account-address-number" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
				<input type="text" name="address_number" id="address_number" class="address-number" value="'.htmlspecialchars($this->post['address_number']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-extension">
				<label class="account-address-ext" for="address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
				<input type="text" name="address_ext" id="address_ext" class="address-ext" value="'.htmlspecialchars($this->post['address_ext']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-zip">
				<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
				<input type="text" name="zip" class="zip" id="zip" value="'.htmlspecialchars($this->post['zip']).'">
				<span class="error-space"></span>
			  </div>		
			  <div class="account-field" id="input-city">
				<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
				<input id="city" name="city" type="text" value="'.htmlspecialchars($this->post['city']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-phone">
				<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
				<input type="text" name="telephone" id="telephone" class="telephone" value="'.htmlspecialchars($this->post['telephone']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="input-mobile">
				<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
				<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($this->post['mobile']).'" />
				<span class="error-space"></span></div>  
			</div>
			<div id="live-validation_r">
			  <div class="account-boxes">
				<div class="account-heading">
				  <h2>'.$this->pi_getLL('login_details').'</h2>
				</div>
				<div class="account-boxes">'.$this->pi_getLL('login_details_description').'.</div>
			  </div>
			  <div class="account-field" id="user-email">
				<label class="account-email" for="email">'.$this->pi_getLL('e-mail_address').'</label>
				<input type="text" name="email" class="email" id="email" value="'.htmlspecialchars($this->post['email']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="user-confirmemail">
				<label class="account-email-confirm" for="email_confirm">'.$this->pi_getLL('confirm_email_address').'</label>
				<input type="text" name="email_confirm" class="email-confirm" id="email_confirm" value="'.htmlspecialchars($this->post['email_confirm']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="user-password">
				<label class="account-password" for="password">'.$this->pi_getLL('password').'</label>
				<input type="password" name="password" class="password" id="password" value="'.htmlspecialchars($this->post['password']).'" />
				<span class="error-space"></span></div>
			  <div class="account-field" id="user-confirmpassword">
				<label class="account-password-confirm" for="password_confirm">'.$this->pi_getLL('confirm_password').'</label>
				<input type="password" name="password_confirm" class="password-confirm" id="password_confirm" value="'.htmlspecialchars($this->post['password_confirm']).'" />
			  <span class="error-space"></span></div>
			  <div class="account-field newsletter_checkbox">
				<div class="account-heading">
				  <h2>'.$this->pi_getLL('newsletter').'</h2>
				</div>
				<div class="account-boxes">'.$this->pi_getLL('subscribe_to_our_newsletter_description').'.</div>
			  </div>
			  <div class="account-field newsletter_checkbox_message">
				<label class="account-label">
				  <input type="checkbox" name="tx_multishop_newsletter" id="tx_multishop_newsletter" value="1"'.($this->post['tx_multishop_newsletter']?' checked="checked"':'').' />
				</label>
				<label class="account-value" for="tx_multishop_newsletter">'.$this->pi_getLL('subscribe_to_our_newsletter').'</label>
				</div>
			  	<div class="account-field security">
					<label>
					  <img src="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=captcha').'">
					</label>				
					  <input type="text" name="tx_multishop_pi1[captcha_code]" id="tx_multishop_captcha_code" value="" />
				</div>			
			</div>
			<div id="bottom-navigation">
				<a href="" onClick="history.back(); return false;" class="msFrontButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('back').'</span></a>
			  <div id="navigation">
				<span class="msFrontButton continueState arrowRight arrowPosLeft"><input type="submit" id="submit" value="'.$this->pi_getLL('register').'" /></span>
			  </div>
			</div>
		  </form>
		</div>
		';
	}
}
$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
?>