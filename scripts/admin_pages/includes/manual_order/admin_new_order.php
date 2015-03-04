<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
$products=$cart['products'];
if (count($products)<0) {
	$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
} else {
	if ($this->get['tx_multishop_pi1']['is_proposal']) {
		$content.='<div class="account-field">
			<h1>'.$this->pi_getLL('admin_label_create_quotation').'</h1>
		</div>';
	} else {
		$content.='<div class="account-field">
			<h1>'.$this->pi_getLL('admin_label_create_order').'</h1>
		</div>';
	}
	$customers=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'company, name, email');
	if (is_array($customers) and count($customers)) {
		$content.='<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[page_section]=admin_processed_manual_order').'" method="post" name="checkout" class="AdvancedForm" id="ms_checkout_direct">';
		if ($this->get['tx_multishop_pi1']['is_proposal']) {
			$content.='<input name="tx_multishop_pi1[is_proposal]" type="hidden" value="'.$this->get['tx_multishop_pi1']['is_proposal'].'" />';
		}
		$content.='<div class="account-field">
			<label>'.$this->pi_getLL('admin_customer').'</label>
			<select id="manual_order_customer_id" name="customer_id" width="300px"><option value="">'.htmlspecialchars($this->pi_getLL('existing_customer', 'Existing customers')).'</option>';
		foreach ($customers as $customer) {
			if ($customer['email']) {
				$itemTitle='';
				if ($customer['company']) {
					$itemTitle=$customer['company'];
				}
				if (!$itemTitle && ($customer['name'] && $customer['name'] !=$customer['company'])) {
					$itemTitle=$customer['name'];
				}
				$itemArray=array();
				if ($customer['name']) {
					$itemArray[]=array('label'=>$this->pi_getLL('name'),'value'=>$customer['name']);
				}
				if ($customer['email']) {
					$itemArray[]=array('label'=>$this->pi_getLL('email'),'value'=>$customer['email']);
				}
				if ($customer['username']) {
					$itemArray[]=array('label'=>$this->pi_getLL('username'),'value'=>$customer['username']);
				}
				if ($customer['address']) {
					$itemArray[]=array('label'=>$this->pi_getLL('address'),'value'=>$customer['address']);
				}
				if ($customer['telephone']) {
					$itemArray[]=array('label'=>$this->pi_getLL('telephone'),'value'=>$customer['telephone']);
				}
				// CUSTOM HTML MARKUP FOR SELECT2
				$htmlTitle='<h3>'.$itemTitle.'</h3>';
				foreach ($itemArray as $rowItem) {
					$htmlTitle.=$rowItem['label'].': <strong>'.$rowItem['value'].'</strong><br/>';
				}
				$content.='<option value="'.$customer['uid'].'">'.htmlspecialchars($htmlTitle).'</option>';
			}
		}
		$content.='</select>';
		$content.='<input type="hidden" id="proceed_order" value="proceed_order" name="proceed_order"/></div>';
		$content.='</form>';
	}
	if ($this->post) {
	} else {
		$show_checkout_address=1;
	}
	if ($show_checkout_address) {
		// load enabled countries to array
		$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		$enabled_countries=array();
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$enabled_countries[]=$row2;
		}
		// load enabled countries to array eof
		$regex="/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
		$regex_for_character="/[^0-9]$/";
		$birthday_validation='';
		//if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
			$birthday_validation='
			$("#birthday_visitor").datepicker({
				dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
				altField: "#birthday",
				altFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				yearRange: "-100:+0"
			});
			$("#delivery_birthday_visitor").datepicker({
				dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
				altField: "#delivery_birthday",
				altFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				yearRange: "-100:+0"
			});';
		//}
		$GLOBALS['TSFE']->additionalHeaderData[]='<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(\'#ms_checkout\').h5Validate();
				'.$birthday_validation.'
			}); //end of first load
		</script>';
		if (is_array($erno) and count($erno)>0) {
			$content_.='<div class="error_msg">';
			$content_.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content_.='<li>'.$item.'</li>';
			}
			$content_.='</ul>';
			$content_.='</div>';
		}
		$content.='<div class="error_msg" style="display:none">';
		$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
		$content.='<li class="item-error" style="display:none"></li>';
		$content.='</ul></div>';
		$content.='<div id="live-validation">
		<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[page_section]=admin_processed_manual_order').'" method="post" name="checkout" class="AdvancedForm" id="ms_checkout">';
		$content.='<div id="customer_details_form">
		<div class="step">
			<div class="account-field">
				<span id="ValidRadio" class="InputGroup">
					<label for="radio" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
					<input type="radio" class="InputGroup" name="gender" value="m" class="account-gender-radio" id="radio" '.(($user['gender']=='m') ? 'checked' : '').' required="required" data-h5-errorid="invalid-gender" title="'.$this->pi_getLL('gender_is_required', 'Title is required').'">
					<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
					<input type="radio" name="gender" value="f" class="InputGroup" id="radio2" '.(($user['gender']=='f') ? 'checked' : '').'>
					<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
					<div id="invalid-gender" class="error-space" style="display:none"></div>
				</span>
				<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'</label>
				<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.htmlspecialchars($user['birthday']).'" >
				<input type="hidden" name="birthday" class="birthday" id="birthday" value="'.htmlspecialchars($user['birthday']).'" >
			</div>
		</div>
		<div class="step">
		<div class="account-field">
			<label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
			<input type="text" name="company" class="company" id="company" value="'.htmlspecialchars($user['company']).'"/>
			<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.ucfirst($this->pi_getLL('vat_id', 'VAT ID')).'</label>
			<input type="text" name="tx_multishop_vat_id" class="tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.htmlspecialchars($user['tx_multishop_vat_id']).'" />
		</div>
		<div class="account-field">
			<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
			<input type="text" name="first_name" class="first-name" id="first_name" value="'.htmlspecialchars($user['first_name']).'" required="required" data-h5-errorid="invalid-first_name" title="'.$this->pi_getLL('first_name_required').'"><div id="invalid-first_name" class="error-space" style="display:none"></div>
			<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
			<input type="text" name="middle_name" id="middle_name" class="middle_name" value="'.htmlspecialchars($user['middle_name']).'">
		</div>
		<div class="account-field">
			<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
			<input type="text" name="last_name" id="last_name" class="last-name" value="'.htmlspecialchars($user['last_name']).'" required="required" data-h5-errorid="invalid-last_name" title="'.$this->pi_getLL('surname_is_required').'"><div id="invalid-last_name" class="error-space" style="display:none"></div>
		</div>
		<div class="account-field">
			<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
			<input type="text" name="street_name" id="address" class="address" value="'.htmlspecialchars($user['street_name']).'" required="required" data-h5-errorid="invalid-address" title="'.$this->pi_getLL('street_address_is_required').'"><div id="invalid-address" class="error-space" style="display:none"></div>
			<label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
			<input type="text" name="address_number" id="address_number" class="address-number" value="'.htmlspecialchars($user['address_number']).'" required="required" data-h5-errorid="invalid-address_number" title="'.$this->pi_getLL('street_number_is_required').'"><div id="invalid-address_number" class="error-space" style="display:none"></div>
        </div>
		<div class="account-field">
			<label class="account-address_address_ext" for="address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
			<input type="text" name="address_ext" id="address_ext" class="address_ext" value="'.htmlspecialchars($user['address_ext']).'" >
        </div>
        </div>
		<div class="account-field">
			<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
			<input type="text" name="zip" id="zip" class="zip" value="'.htmlspecialchars($user['zip']).'" required="required" data-h5-errorid="invalid-zip" title="'.$this->pi_getLL('zip_is_required').'"><div id="invalid-zip" class="error-space" style="display:none"></div>
			<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
			<input type="text" name="city" id="city" class="city" value="'.htmlspecialchars($user['city']).'" required="required" data-h5-errorid="invalid-city" title="'.$this->pi_getLL('city_is_required').'"><div id="invalid-city" class="error-space" style="display:none"></div>
		</div>
		<div class="account-field">';
		// load countries
		if (count($enabled_countries)==1) {
			$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
			$content.='<input name="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
			$content.='<input name="delivery_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		} else {
			$billing_countries_option=array();
			$delivery_countries_option=array();
			foreach ($enabled_countries as $country) {
				$cn_localized_name=htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']));
				$billing_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.(($user['country']==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
				$delivery_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.(($user['delivery_country']==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
			}
			ksort($billing_countries_option);
			ksort($delivery_countries_option);
			$tmpcontent_con=implode("\n", $billing_countries_option);
			$tmpcontent_con_delivery=implode("\n", $delivery_countries_option);
			if ($tmpcontent_con) {
				$content.='<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
				<select name="country" id="country" class="country" required="required" data-h5-errorid="invalid-country" title="'.$this->pi_getLL('country_is_required').'">
				<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
				'.$tmpcontent_con.'
				</select>
				<div id="invalid-country" class="error-space" style="display:none"></div>';
			}
		}
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
		// country eof
		$content.='</div>
		<div class="account-field">
			<label for="email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'</label>
			<input type="text" name="email" id="email" class="email" value="'.htmlspecialchars($user['email']).'" required="required" data-h5-errorid="invalid-email" title="'.$this->pi_getLL('email_is_required').'"><div id="invalid-email" class="error-space" style="display:none"></div>
		</div>
		<div class="account-field">
			<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'</label>
			<input type="text" name="tx_multishop_pi1[telephone]" id="telephone" class="telephone" value="'.htmlspecialchars($user['telephone']).'"'.$telephone_validation.'><div id="invalid-telephone" class="error-space" style="display:none"></div>
			<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
			<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($user['mobile']).'"><div id="invalid-mobile" class="error-space" style="display:none"></div>
		</div>
		<div class="account-field">
		<label>
		<input type="checkbox" name="different_delivery_address" id="checkboxdifferent_delivery_address" '.(($this->post['different_delivery_address']) ? 'checked' : '').' /></label>
		'.$this->pi_getLL('click_here_if_your_delivery_address_is_different_from_your_billing_address').'.
		</div>
		<div class="mb10" style="clear:both"></div>';
		$tmpcontent='';
		if ($user['delivery_zip']) {
			$tmpcontent.='<script type="text/javascript">
				jQuery(\'#delivery_address_category\').show(\'slow\', function(){});
			</script>';
		}
		$tmpcontent.='<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(\'#manual_order_customer_id\').select2({
					width:\'350px\',
					formatSelection: function(item) {
						return item.text;
					},
					escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
				});
				$(\'#manual_order_customer_id\').change(function() {
					if ($(this).val() == \'\') {
						$("#customer_details_form").show();
					} else {
						$("#customer_details_form").hide();
						$("#ms_checkout_direct").submit();
					}
				});
				if ($("#checkboxdifferent_delivery_address").is(\':checked\')) {
					// set the h5validate attributes for required delivery data
					$(\'#delivery_radio\').attr(\'required\', \'required\');
					$(\'#delivery_radio\').attr(\'data-h5-errorid\', \'invalid-delivery_gender\');
					$(\'#delivery_radio\').attr(\'title\', \''.addslashes($this->pi_getLL('gender_is_required', 'Title is required')).' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

					$(\'#delivery_first_name\').attr(\'required\', \'required\');
					$(\'#delivery_first_name\').attr(\'data-h5-errorid\', \'invalid-delivery_first_name\');
					$(\'#delivery_first_name\').attr(\'title\', \''.addslashes($this->pi_getLL('first_name_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_last_name\').attr(\'required\', \'required\');
					$(\'#delivery_last_name\').attr(\'data-h5-errorid\', \'invalid-delivery_last_name\');
					$(\'#delivery_last_name\').attr(\'title\', \''.addslashes($this->pi_getLL('surname_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_address\').attr(\'required\', \'required\');
					$(\'#delivery_address\').attr(\'data-h5-errorid\', \'invalid-delivery_address\');
					$(\'#delivery_address\').attr(\'title\', \''.addslashes($this->pi_getLL('street_address_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_address_number\').attr(\'required\', \'required\');
					$(\'#delivery_address_number\').attr(\'data-h5-errorid\', \'invalid-delivery_address_number\');
					$(\'#delivery_address_number\').attr(\'title\', \''.addslashes($this->pi_getLL('street_number_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_zip\').attr(\'required\', \'required\');
					$(\'#delivery_zip\').attr(\'data-h5-errorid\', \'invalid-delivery_zip\');
					$(\'#delivery_zip\').attr(\'title\', \''.addslashes($this->pi_getLL('zip_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_city\').attr(\'required\', \'required\');
					$(\'#delivery_city\').attr(\'data-h5-errorid\', \'invalid-delivery_city\');
					$(\'#delivery_city\').attr(\'title\', \''.addslashes($this->pi_getLL('city_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_country\').attr(\'required\', \'required\');
					$(\'#delivery_country\').attr(\'data-h5-errorid\', \'invalid-delivery_country\');
					$(\'#delivery_country\').attr(\'title\', \''.addslashes($this->pi_getLL('country_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_telephone\').attr(\'required\', \'required\');
					$(\'#delivery_telephone\').attr(\'data-h5-errorid\', \'invalid-delivery_telephone\');
					$(\'#delivery_telephone\').attr(\'title\', \''.addslashes($this->pi_getLL('telephone_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_address_category\').show();
				} else {
					// remove the h5validate attributes
					$(\'#delivery_radio\').removeAttr(\'required\');
					$(\'#delivery_radio\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_radio\').removeAttr(\'title\');

					$(\'#delivery_first_name\').removeAttr(\'required\');
					$(\'#delivery_first_name\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_first_name\').removeAttr(\'title\');

					$(\'#delivery_last_name\').removeAttr(\'required\');
					$(\'#delivery_last_name\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_last_name\').removeAttr(\'title\');

					$(\'#delivery_address\').removeAttr(\'required\');
					$(\'#delivery_address\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_address\').removeAttr(\'title\');

					$(\'#delivery_address_number\').removeAttr(\'required\');
					$(\'#delivery_address_number\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_address_number\').removeAttr(\'title\');

					$(\'#delivery_zip\').removeAttr(\'required\');
					$(\'#delivery_zip\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_zip\').removeAttr(\'title\');

					$(\'#delivery_city\').removeAttr(\'required\');
					$(\'#delivery_city\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_city\').removeAttr(\'title\');

					$(\'#delivery_country\').removeAttr(\'required\');
					$(\'#delivery_country\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_country\').removeAttr(\'title\');

					$(\'#delivery_telephone\').removeAttr(\'required\');
					$(\'#delivery_telephone\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_telephone\').removeAttr(\'title\');

					$(\'#delivery_address_category\').hide();
				}

				jQuery("#checkboxdifferent_delivery_address").click(function(event) {
					jQuery(\'#delivery_address_category\').slideToggle(\'slow\', function(){});

					if ($("#checkboxdifferent_delivery_address").is(\':checked\')) {
						// set the h5validate attributes for required delivery data
						$(\'#delivery_radio\').attr(\'required\', \'required\');
						$(\'#delivery_radio\').attr(\'data-h5-errorid\', \'invalid-delivery_gender\');
						$(\'#delivery_radio\').attr(\'title\', \''.addslashes($this->pi_getLL('gender_is_required', 'Title is required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_first_name\').attr(\'required\', \'required\');
						$(\'#delivery_first_name\').attr(\'data-h5-errorid\', \'invalid-delivery_first_name\');
						$(\'#delivery_first_name\').attr(\'title\', \''.addslashes($this->pi_getLL('first_name_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_last_name\').attr(\'required\', \'required\');
						$(\'#delivery_last_name\').attr(\'data-h5-errorid\', \'invalid-delivery_last_name\');
						$(\'#delivery_last_name\').attr(\'title\', \''.addslashes($this->pi_getLL('surname_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_address\').attr(\'required\', \'required\');
						$(\'#delivery_address\').attr(\'data-h5-errorid\', \'invalid-delivery_address\');
						$(\'#delivery_address\').attr(\'title\', \''.addslashes($this->pi_getLL('street_address_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_address_number\').attr(\'required\', \'required\');
						$(\'#delivery_address_number\').attr(\'data-h5-errorid\', \'invalid-delivery_address_number\');
						$(\'#delivery_address_number\').attr(\'title\', \''.addslashes($this->pi_getLL('street_number_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_zip\').attr(\'required\', \'required\');
						$(\'#delivery_zip\').attr(\'data-h5-errorid\', \'invalid-delivery_zip\');
						$(\'#delivery_zip\').attr(\'title\', \''.addslashes($this->pi_getLL('zip_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_city\').attr(\'required\', \'required\');
						$(\'#delivery_city\').attr(\'data-h5-errorid\', \'invalid-delivery_city\');
						$(\'#delivery_city\').attr(\'title\', \''.addslashes($this->pi_getLL('city_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_country\').attr(\'required\', \'required\');
						$(\'#delivery_country\').attr(\'data-h5-errorid\', \'invalid-delivery_country\');
						$(\'#delivery_country\').attr(\'title\', \''.addslashes($this->pi_getLL('country_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_telephone\').attr(\'required\', \'required\');
						$(\'#delivery_telephone\').attr(\'data-h5-errorid\', \'invalid-delivery_telephone\');
						$(\'#delivery_telephone\').attr(\'title\', \''.addslashes($this->pi_getLL('telephone_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_address_category\').show();
					} else {
						// remove the h5validate attributes
						$(\'#delivery_radio\').removeAttr(\'required\');
						$(\'#delivery_radio\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_radio\').removeAttr(\'title\');

						$(\'#delivery_first_name\').removeAttr(\'required\');
						$(\'#delivery_first_name\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_first_name\').removeAttr(\'title\');

						$(\'#delivery_last_name\').removeAttr(\'required\');
						$(\'#delivery_last_name\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_last_name\').removeAttr(\'title\');

						$(\'#delivery_address\').removeAttr(\'required\');
						$(\'#delivery_address\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_address\').removeAttr(\'title\');

						$(\'#delivery_address_number\').removeAttr(\'required\');
						$(\'#delivery_address_number\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_address_number\').removeAttr(\'title\');

						$(\'#delivery_zip\').removeAttr(\'required\');
						$(\'#delivery_zip\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_zip\').removeAttr(\'title\');

						$(\'#delivery_city\').removeAttr(\'required\');
						$(\'#delivery_city\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_city\').removeAttr(\'title\');

						$(\'#delivery_country\').removeAttr(\'required\');
						$(\'#delivery_country\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_country\').removeAttr(\'title\');

						$(\'#delivery_telephone\').removeAttr(\'required\');
						$(\'#delivery_telephone\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_telephone\').removeAttr(\'title\');

						$(\'#delivery_address_category\').hide();
					}
				});
			});
			</script>
			<div class="step">
			<div class="account-field">
				<span id="delivery_ValidRadio" class="delivery_InputGroup">
					<label for="delivery_gender" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
					<input type="radio" class="delivery_InputGroup" name="delivery_gender" value="m" class="account-gender-radio" id="delivery_radio" '.(($user['delivery_gender']=='m') ? 'checked' : '').'>
					<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
					<input type="radio" name="delivery_gender" value="f" class="delivery_InputGroup" id="radio2" '.(($user['delivery_gender']=='f') ? 'checked' : '').'>
					<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
					<div id="invalid-delivery_gender" class="error-space" style="display:none"></div>
				</span>
			</div>
		 </div>
		 <div class="step">
			<div class="account-field">
				<label for="delivery_company">'.ucfirst($this->pi_getLL('company')).':</label>
				<input type="text" name="delivery_company" id="delivery_company" class="delivery_company" value="'.htmlspecialchars($user['delivery_company']).'">
			</div>
			<div class="account-field">
				<label class="account-firstname" for="delivery_first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
				<input type="text" name="delivery_first_name" class="delivery_first-name left-this" id="delivery_first_name" value="'.htmlspecialchars($user['delivery_first_name']).'" ><div id="invalid-delivery_first_name" class="error-space" style="display:none"></div>
				<label class="account-middlename" for="delivery_middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
				<input type="text" name="delivery_middle_name" id="delivery_middle_name" class="delivery_middle_name left-this" value="'.htmlspecialchars($user['delivery_middle_name']).'">
				<label class="account-lastname" for="delivery_last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
				<input type="text" name="delivery_last_name" id="delivery_last_name" class="delivery_last-name left-this" value="'.htmlspecialchars($user['delivery_last_name']).'" ><div id="invalid-delivery_last_name" class="error-space" style="display:none"></div>
		    </div>
		 </div>
			<div class="account-field">
				<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).'*:</label>
				<input  type="text" name="delivery_street_name" id="delivery_address" class="delivery_address left-this" value="'.htmlspecialchars($user['delivery_street_name']).'"><div id="invalid-delivery_address" class="error-space" style="display:none"></div>
				<label class="delivery_account-addressnumber" for="delivery_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
				<input type="text" name="delivery_address_number" id="delivery_address_number" class="delivery_address-number" value="'.htmlspecialchars($user['delivery_address_number']).'" ><div id="invalid-delivery_address_number" class="error-space" style="display:none"></div>
			</div>
			<div class="account-field">
				<label class="account-address_delivery_address_ext" for="delivery_address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
				<input type="text" name="delivery_address_ext" id="delivery_address_ext" class="delivery_address_ext" value="'.htmlspecialchars($user['delivery_address_ext']).'" >
			</div>
			<div class="account-field">
				<label for="delivery_zip">'.ucfirst($this->pi_getLL('zip')).'*:</label>
				<input type="text" name="delivery_zip" id="delivery_zip" class="delivery_zip left-this" value="'.htmlspecialchars($user['delivery_zip']).'"><div id="invalid-delivery_zip" class="error-space" style="display:none"></div>
				<label class="account-city" for="delivery_city">'.ucfirst($this->pi_getLL('city')).'*</label>
				<input type="text" name="delivery_city" id="delivery_city" class="delivery_city" value="'.htmlspecialchars($user['delivery_city']).'" ><div id="invalid-delivery_city" class="error-space" style="display:none"></div>';
		if ($tmpcontent_con) {
			$tmpcontent.='<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
			<select name="delivery_country" id="delivery_country" class="delivery_country">
			<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
			'.$tmpcontent_con_delivery.'
			</select>
			<div id="invalid-delivery_country" class="error-space" style="display:none"></div>';
		}
		$tmpcontent.='</div>
			<div class="account-field">
				<label for="delivery_email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'</label>
				<input type="text" name="delivery_email" id="delivery_email" class="delivery_email" value="'.htmlspecialchars($user['delivery_email']).'"/>
			</div>
			<div class="account-field">
				<label for="delivery_telephone">'.ucfirst($this->pi_getLL('telephone')).'*:</label>
				<input type="text" name="delivery_telephone" id="delivery_telephone" class="delivery_telephone" value="'.htmlspecialchars($user['delivery_telephone']).'"><div id="invalid-delivery_telephone" class="error-space" style="display:none"></div>
				<label for="delivery_mobile" class="account_mobile">'.ucfirst($this->pi_getLL('mobile')).':</label>
				<input type="text" name="delivery_mobile" id="delivery_mobile" class="delivery_mobile" value="'.htmlspecialchars($user['delivery_mobile']).'">
			</div>';
		$content.='<div id="delivery_address_category" class="hide"><div class="main-heading"><h2>'.$this->pi_getLL('delivery_address').'</h2></div>'.$tmpcontent.'</div></div>';
		$content.='<div id="bottom-navigation">
						<div id="navigation">
	 						<input type="hidden" id="proceed_order" value="proceed_order" name="proceed_order"/>
	 						<input type="submit" id="submit" value="'.htmlspecialchars($this->pi_getLL('next')).'"/>
							<input name="tx_multishop_pi1[is_proposal]" type="hidden" value="'.$this->get['tx_multishop_pi1']['is_proposal'].'" />
	 					</div>
				</div>
				</form>
				</div>';
	}
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>