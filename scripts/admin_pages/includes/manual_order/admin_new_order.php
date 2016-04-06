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
		$content.='<div class="panel-heading">
			<h3>'.$this->pi_getLL('admin_label_create_quotation').'</h3>
		</div><div class="panel-body">';
	} else {
		$content.='<div class="panel-heading">
			<h3>'.$this->pi_getLL('admin_label_create_order').'</h3>
		</div><div class="panel-body">';
	}
	$customers=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'company, name, email');
	if (is_array($customers) and count($customers)) {
		$content.='<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[page_section]=admin_processed_manual_order').'" method="post" name="checkout" class="AdvancedForm" id="ms_checkout_direct">';
		if ($this->get['tx_multishop_pi1']['is_proposal']) {
			$content.='<input name="tx_multishop_pi1[is_proposal]" type="hidden" value="'.$this->get['tx_multishop_pi1']['is_proposal'].'" />';
		}
		$content.='<div class="form-group">
<div class="row">
<div class="col-md-8">
			<label>'.$this->pi_getLL('admin_customer').'</label>
			<input type="hidden" id="manual_order_customer_id" name="customer_id" value="" />';
		$content.='<input type="hidden" id="proceed_order" value="proceed_order" name="proceed_order"/><hr></div><div class="col-md-4"></div></div></div>';
		$content.='</form>';
	}
	if ($this->post) {
	} else {
		$show_checkout_address=1;
	}
	if ($show_checkout_address) {
		// load enabled countries to array
		$str2="SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
		//$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
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
			$content_.='<div class="alert alert-danger">';
			$content_.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content_.='<li>'.$item.'</li>';
			}
			$content_.='</ul>';
			$content_.='</div>';
		}
		$content.='<div class="alert alert-danger" style="display:none">';
		$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
		$content.='<li class="item-error" style="display:none"></li>';
		$content.='</ul></div>';
		$content.='<div id="live-validation">
		<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[page_section]=admin_processed_manual_order').'" method="post" name="checkout" class="AdvancedForm" id="ms_checkout">';
		$content.='<div id="customer_details_form">
		<div class="row">
		<div class="col-md-8">
		<div class="form-group">
			<span id="ValidRadio" class="InputGroup">
				<label for="radio" id="account-gender">'.ucfirst($this->pi_getLL('title')).'</label>
				<div class="radio radio-success radio-inline">
				<input type="radio" class="InputGroup" name="gender" value="m" class="account-gender-radio" id="radio" '.(($user['gender']=='m') ? 'checked' : '').'>
				<label class="account-male" for="radio">'.ucfirst($this->pi_getLL('mr')).'</label>
				</div>
				<div class="radio radio-success radio-inline">
				<input type="radio" name="gender" value="f" class="InputGroup" id="radio2" '.(($user['gender']=='f') ? 'checked' : '').'>
				<label class="account-female" for="radio2">'.ucfirst($this->pi_getLL('mrs')).'</label>
				</div>
				<div id="invalid-gender" class="error-space" style="display:none"></div>
			</span>
		</div>

		<div class="form-group">
			<div class="row">
			<div class="col-md-6">
				<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'</label>
				<input type="text" name="birthday_visitor" class="form-control birthday" id="birthday_visitor" value="'.htmlspecialchars($user['birthday']).'" >
				<input type="hidden" name="birthday" class="birthday" id="birthday" value="'.htmlspecialchars($user['birthday']).'" >
			</div>
			<div class="col-md-6">
				<label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
				<input type="text" name="company" class="form-control company" id="company" value="'.htmlspecialchars($user['company']).'"/>
				'.($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? '<div id="invalid-company" class="error-space" style="display:none"></div>' : '').'
			</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-md-6">
					<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.ucfirst($this->pi_getLL('vat_id', 'VAT ID')).'</label>
					<input type="text" name="tx_multishop_vat_id" class="form-control tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.htmlspecialchars($user['tx_multishop_vat_id']).'" />
				</div>
				<div class="col-md-6">
					<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">'.ucfirst($this->pi_getLL('coc_id', 'KvK ID')).'</label>
					<input type="text" name="tx_multishop_coc_id" class="form-control tx_multishop_coc_id" id="tx_multishop_coc_id" value="'.htmlspecialchars($user['tx_multishop_coc_id']).'" />
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
			<div class="col-md-4">
				<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'</label>
				<input type="text" name="first_name" class="form-control first-name" id="first_name" value="'.htmlspecialchars($user['first_name']).'">
			</div>
			<div class="col-md-4">
				<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
				<input type="text" name="middle_name" id="middle_name" class="form-control middle_name" value="'.htmlspecialchars($user['middle_name']).'">
			</div>
			<div class="col-md-4">
				<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'</label>
				<input type="text" name="last_name" id="last_name" class="form-control last-name" value="'.htmlspecialchars($user['last_name']).'">
			</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-md-4">
					<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'</label>
					<input type="text" name="street_name" id="address" class="form-control address" value="'.htmlspecialchars($user['street_name']).'">
				</div>
				<div class="col-md-4">
					<label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'</label>
					<input type="text" name="address_number" id="address_number" class="form-control address-number" value="'.htmlspecialchars($user['address_number']).'">
				</div>
				<div class="col-md-4">
					<label class="account-address_address_ext" for="address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
					<input type="text" name="address_ext" id="address_ext" class="form-control address_ext" value="'.htmlspecialchars($user['address_ext']).'" >
        		</div>
			</div>
        </div>
		<div class="form-group">
			<div class="row">
			<div class="col-md-6">
				<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'</label>
				<input type="text" name="zip" id="zip" class="form-control zip" value="'.htmlspecialchars($user['zip']).'">
			</div>
			<div class="col-md-6">
				<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'</label>
				<input type="text" name="city" id="city" class="form-control city" value="'.htmlspecialchars($user['city']).'">
			</div>
			</div>
		</div>
		<div class="form-group">';
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
				$billing_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.(($user['country']==mslib_befe::strtolower($country['cn_short_en']) || mslib_befe::strtolower($country['cn_short_en'])=='netherlands') ? 'selected' : '').'>'.$cn_localized_name.'</option>';
				$delivery_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.(($user['delivery_country']==mslib_befe::strtolower($country['cn_short_en']) || mslib_befe::strtolower($country['cn_short_en'])=='netherlands') ? 'selected' : '').'>'.$cn_localized_name.'</option>';
			}
			ksort($billing_countries_option);
			ksort($delivery_countries_option);
			$tmpcontent_con=implode("\n", $billing_countries_option);
			$tmpcontent_con_delivery=implode("\n", $delivery_countries_option);
			if ($tmpcontent_con) {
				$content.='<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'<span class="text-danger">*</span></label>
				<select name="country" id="country" class="form-control country" required="required" data-h5-errorid="invalid-country" title="'.$this->pi_getLL('country_is_required').'">
				<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
				'.$tmpcontent_con.'
				</select>
				<div id="invalid-country" class="error-space" style="display:none"></div>';
			}
		}
		$telephone_validation='';
		$mobile_validation='';
		$this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE']=1;
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
		<div class="form-group">
			<label for="email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'</label>
			<input type="text" name="email" id="email" class="form-control email" value="'.htmlspecialchars($user['email']).'" required="required" data-h5-errorid="invalid-email" title="'.$this->pi_getLL('email_is_required').'">
			<div id="invalid-email" class="error-space" style="display:none"></div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-md-6">
					<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'</label>
					<input type="text" name="tx_multishop_pi1[telephone]" id="telephone" class="form-control telephone" value="'.htmlspecialchars($user['telephone']).'">
				</div>
				<div class="col-md-6">
					<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
					<input type="text" name="mobile" id="mobile" class="form-control mobile" value="'.htmlspecialchars($user['mobile']).'">
					<div id="invalid-mobile" class="error-space" style="display:none"></div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="checkbox checkbox-success checkbox">
				<input type="checkbox" name="different_delivery_address" id="checkboxdifferent_delivery_address" '.(($this->post['different_delivery_address']) ? 'checked' : '').' />
				<label for="checkboxdifferent_delivery_address">'.$this->pi_getLL('click_here_if_your_delivery_address_is_different_from_your_billing_address').'.</label>
			</div>
		</div>
		<hr>';
		$tmpcontent='';
		if ($user['delivery_zip']) {
			$tmpcontent.='<script type="text/javascript">
				jQuery(\'#delivery_address_category\').show(\'slow\', function(){});
			</script>';
		}
		$tmpcontent.='<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(\'#manual_order_customer_id\').select2({
					placeholder:\''.htmlspecialchars($this->pi_getLL('existing_customer', 'Existing customers')).'\',
					width:\'100%\',
					minimumInputLength: 0,
					query: function(query) {
						$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=getExistingCustomers&').'\', {
							data: {
								q: query.term
							},
							dataType: "json"
						}).done(function(data) {
							query.callback({results: data});
						});
					},
					initSelection: function(element, callback) {
						var id=$(element).val();
						if (id!=="") {
							$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=getExistingCustomers&').'\', {
								data: {
									preselected_id: id
								},
								dataType: "json"
							}).done(function(data) {
								callback(data);
							});
						}
					},
					formatResult: function(data){
						if (data.text === undefined) {
							$.each(data, function(i,val){
								return val.text;
							});
						} else {
							return data.text;
						}
					},
					formatSelection: function(data){
						if (data.text === undefined) {
							return data[0].text;
						} else {
							return data.text;
						}
					},
					dropdownCssClass: \'existing_customer_dropdown\',
					escapeMarkup: function (m) { return m; }
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
					$(\'#delivery_country\').attr(\'required\', \'required\');
					$(\'#delivery_country\').attr(\'data-h5-errorid\', \'invalid-delivery_country\');
					$(\'#delivery_country\').attr(\'title\', \''.addslashes($this->pi_getLL('country_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

					$(\'#delivery_address_category\').show();
				} else {
					$(\'#delivery_country\').removeAttr(\'required\');
					$(\'#delivery_country\').removeAttr(\'data-h5-errorid\');
					$(\'#delivery_country\').removeAttr(\'title\');

					$(\'#delivery_address_category\').hide();
				}

				jQuery("#checkboxdifferent_delivery_address").click(function(event) {
					jQuery(\'#delivery_address_category\').slideToggle(\'slow\', function(){});

					if ($("#checkboxdifferent_delivery_address").is(\':checked\')) {
						$(\'#delivery_country\').attr(\'required\', \'required\');
						$(\'#delivery_country\').attr(\'data-h5-errorid\', \'invalid-delivery_country\');
						$(\'#delivery_country\').attr(\'title\', \''.addslashes($this->pi_getLL('country_is_required')).' ('.mslib_befe::strtolower(addslashes($this->pi_getLL('delivery_address'))).')\');

						$(\'#delivery_address_category\').show();
					} else {
						$(\'#delivery_country\').removeAttr(\'required\');
						$(\'#delivery_country\').removeAttr(\'data-h5-errorid\');
						$(\'#delivery_country\').removeAttr(\'title\');

						$(\'#delivery_address_category\').hide();
					}
				});
			});
			</script>
			<div class="form-group">
				<span id="delivery_ValidRadio" class="delivery_InputGroup">
					<label for="delivery_gender" id="account-gender">'.ucfirst($this->pi_getLL('title')).'</label>
					<div class="radio radio-success radio-inline">
					<input type="radio" class="delivery_InputGroup" name="delivery_gender" value="m" class="account-gender-radio" id="delivery_radio" '.(($user['delivery_gender']=='m') ? 'checked' : '').'>
					<label class="account-male" for="delivery_radio">'.ucfirst($this->pi_getLL('mr')).'</label>
					</div>
					<div class="radio radio-success radio-inline">
					<input type="radio" name="delivery_gender" value="f" class="delivery_InputGroup" id="delivery_radio2" '.(($user['delivery_gender']=='f') ? 'checked' : '').'>
					<label class="account-female" for="delivery_radio2">'.ucfirst($this->pi_getLL('mrs')).'</label>
					</div>
				</span>
		 </div>
			<div class="form-group">
				<label for="delivery_company">'.ucfirst($this->pi_getLL('company')).':</label>
				<input type="text" name="delivery_company" id="delivery_company" class="form-control delivery_company" value="'.htmlspecialchars($user['delivery_company']).'">
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-md-4">
						<label class="account-firstname" for="delivery_first_name">'.ucfirst($this->pi_getLL('first_name')).'</label>
						<input type="text" name="delivery_first_name" class="form-control delivery_first-name left-this" id="delivery_first_name" value="'.htmlspecialchars($user['delivery_first_name']).'" >
					</div>
					<div class="col-md-4">
						<label class="account-middlename" for="delivery_middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
						<input type="text" name="delivery_middle_name" id="delivery_middle_name" class="form-control delivery_middle_name left-this" value="'.htmlspecialchars($user['delivery_middle_name']).'">
					</div>
					<div class="col-md-4">
						<label class="account-lastname" for="delivery_last_name">'.ucfirst($this->pi_getLL('last_name')).'</label>
						<input type="text" name="delivery_last_name" id="delivery_last_name" class="form-control delivery_last-name left-this" value="'.htmlspecialchars($user['delivery_last_name']).'" >
					</div>
				</div>
		    </div>
			<div class="form-group">
				<div class="row">
					<div class="col-md-4">
						<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).':</label>
						<input type="text" name="delivery_street_name" id="delivery_address" class="form-control delivery_address left-this" value="'.htmlspecialchars($user['delivery_street_name']).'">
					</div>
					<div class="col-md-4">
						<label class="delivery_account-addressnumber" for="delivery_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'</label>
						<input type="text" name="delivery_address_number" id="delivery_address_number" class="form-control delivery_address-number" value="'.htmlspecialchars($user['delivery_address_number']).'" >
					</div>
					<div class="col-md-4">
						<label class="account-address_delivery_address_ext" for="delivery_address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
						<input type="text" name="delivery_address_ext" id="delivery_address_ext" class="form-control delivery_address_ext" value="'.htmlspecialchars($user['delivery_address_ext']).'" >
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="row">
					<div class="col-md-6">
						<label for="delivery_zip">'.ucfirst($this->pi_getLL('zip')).':</label>
						<input type="text" name="delivery_zip" id="delivery_zip" class="form-control delivery_zip left-this" value="'.htmlspecialchars($user['delivery_zip']).'">
					</div>
					<div class="col-md-6">
						<label class="account-city" for="delivery_city">'.ucfirst($this->pi_getLL('city')).'</label>
						<input type="text" name="delivery_city" id="delivery_city" class="form-control delivery_city" value="'.htmlspecialchars($user['delivery_city']).'" >
					</div>
				</div>
			</div>
				';
		if ($tmpcontent_con) {
			$tmpcontent.='<div class="form-group">
				<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'<span class="text-danger">*</span></label>
				<select name="delivery_country" id="delivery_country" class="form-control delivery_country">
					<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
					'.$tmpcontent_con_delivery.'
				</select>
				<div id="invalid-delivery_country" class="error-space" style="display:none"></div>
			</div>';
		}
		$tmpcontent.='
			<div class="form-group">
				<label for="delivery_email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'</label>
				<input type="text" name="delivery_email" id="delivery_email" class="form-control delivery_email" value="'.htmlspecialchars($user['delivery_email']).'"/>
			</div>
			<div class="form-group">
				<div class="row">
				<div class="col-md-6">
				<label for="delivery_telephone">'.ucfirst($this->pi_getLL('telephone')).'<span class="text-danger">*</span>:</label>
				<input type="text" name="delivery_telephone" id="delivery_telephone" class="form-control delivery_telephone" value="'.htmlspecialchars($user['delivery_telephone']).'"><div id="invalid-delivery_telephone" class="error-space" style="display:none"></div>
				</div>
				<div class="col-md-6">
				<label for="delivery_mobile" class="account_mobile">'.ucfirst($this->pi_getLL('mobile')).':</label>
				<input type="text" name="delivery_mobile" id="delivery_mobile" class="form-control delivery_mobile" value="'.htmlspecialchars($user['delivery_mobile']).'">
				</div>
				</div>
			</div><hr>';
		$content.='<div id="delivery_address_category"><h3 class="page-header">'.$this->pi_getLL('delivery_address').'</h3>'.$tmpcontent.'</div>';
		$content.='<div class="clearfix">
<div class="pull-right">
	 						<input type="hidden" id="proceed_order" value="proceed_order" name="proceed_order"/>
	 						<input type="submit" id="submit" class="btn btn-success" value="'.htmlspecialchars($this->pi_getLL('next')).'"/>
							<input name="tx_multishop_pi1[is_proposal]" type="hidden" value="'.$this->get['tx_multishop_pi1']['is_proposal'].'" />
							</div>
				</div>
				</form>
				</div>
				<div class="col-md-4"></div></div>';
	}
}
$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>