<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
if (count($cart['products'])<1) {
	$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
} else {
	if (mslib_fe::loggedin()) {
		if (isset($cart['user']['first_name']) && isset($cart['user']['street_name'])) {
			$user=$cart['user'];
		} else {
			$billing_address=mslib_fe::getFeUserTTaddressDetails($GLOBALS['TSFE']->fe_user->user['uid']);
			if (is_array($billing_address)) {
				$user=array();
				$user['first_name']=$billing_address['first_name'];
				$user['middle_name']=$billing_address['middle_name'];
				$user['last_name']=$billing_address['last_name'];
				$user['gender']=($billing_address['gender']==0 ? "m" : "f");
				$user['company']=$billing_address['company'];
				$user['tx_multishop_newsletter']=$billing_address['tx_multishop_newsletter'];
				$user['address_ext']=$billing_address['address_ext'];
				$user['street_name']=$billing_address['street_name'];
				$user['address_number']=$billing_address['address_number'];
				$user['address']=$billing_address['street_name'].' '.$billing_address['address_number'].($billing_address['address_ext'] ? '-'.$billing_address['address_ext'] : '');
				$user['address']=preg_replace('/\s+/', ' ', $user['address']);
				$user['zip']=$billing_address['zip'];
				$user['city']=$billing_address['city'];
				if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
					$user['state']=$billing_address['state'];
				}
				$user['email']=$billing_address['email'];
				$user['telephone']=$billing_address['phone'];
				$user['mobile']=$billing_address['mobile'];
				$user['country']=$billing_address['country'];
			} else {
				$user=array();
				$user['first_name']=$GLOBALS['TSFE']->fe_user->user['first_name'];
				$user['middle_name']=$GLOBALS['TSFE']->fe_user->user['middle_name'];
				$user['last_name']=$GLOBALS['TSFE']->fe_user->user['last_name'];
				$user['gender']=($GLOBALS['TSFE']->fe_user->user['gender']==0 ? "m" : "f");
				$user['company']=$GLOBALS['TSFE']->fe_user->user['company'];
				$user['tx_multishop_newsletter']=$GLOBALS['TSFE']->fe_user->user['tx_multishop_newsletter'];
				$user['address_ext']=$GLOBALS['TSFE']->fe_user->user['address_ext'];
				$user['street_name']=$GLOBALS['TSFE']->fe_user->user['street_name'];
				$user['address_number']=$GLOBALS['TSFE']->fe_user->user['address_number'];
				$user['address']=$GLOBALS['TSFE']->fe_user->user['street_name'].' '.$GLOBALS['TSFE']->fe_user->user['address_number'].($GLOBALS['TSFE']->fe_user->user['address_ext'] ? '-'.$GLOBALS['TSFE']->fe_user->user['address_ext'] : '');
				$user['address']=preg_replace('/\s+/', ' ', $user['address']);
				$user['zip']=$GLOBALS['TSFE']->fe_user->user['zip'];
				$user['city']=$GLOBALS['TSFE']->fe_user->user['city'];
				if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
					$user['state']=$GLOBALS['TSFE']->fe_user->user['state'];
					$user['delivery_state']=$GLOBALS['TSFE']->fe_user->user['delivery_state'];
				}
				$user['email']=$GLOBALS['TSFE']->fe_user->user['email'];
				$user['email_confirm']=$GLOBALS['TSFE']->fe_user->user['email_confirm'];
				$user['telephone']=$GLOBALS['TSFE']->fe_user->user['telephone'];
				$user['mobile']=$GLOBALS['TSFE']->fe_user->user['mobile'];
				$user['country']=$GLOBALS['TSFE']->fe_user->user['country'];
			}
			if ($this->ms['MODULES']['CHECKOUT_DISPLAY_VAT_ID_INPUT'] && !empty($GLOBALS['TSFE']->fe_user->user['tx_multishop_vat_id'])) {
				$user['tx_multishop_vat_id']=$GLOBALS['TSFE']->fe_user->user['tx_multishop_vat_id'];
			}
			if ($this->ms['MODULES']['CHECKOUT_DISPLAY_COC_ID_INPUT'] && !empty($GLOBALS['TSFE']->fe_user->user['tx_multishop_coc_id'])) {
				$user['tx_multishop_coc_id']=$GLOBALS['TSFE']->fe_user->user['tx_multishop_coc_id'];
			}
		}
	} else {
		$user=$cart['user'];
	}
	if ($posted_page==current($stepCodes)) {
		// now verify the posted values
		if (!$this->post['tx_multishop_pi1']['email']) {
			$erno[]=$this->pi_getLL('no_email_address_has_been_specified');
		}
		if (!$this->post['street_name']) {
			$erno[]='No street name has been specified';
		}
		if (!$this->post['address_number']) {
			$erno[]=$this->pi_getLL('no_address_number_has_been_specified');
		}
		if (!$this->post['first_name']) {
			$erno[]=$this->pi_getLL('no_first_name_has_been_specified');
		}
		if (!$this->post['last_name']) {
			$erno[]=$this->pi_getLL('no_last_name_has_been_specified');
		}
		if (!$this->post['zip']) {
			$erno[]=$this->pi_getLL('no_zip_has_been_specified');
		}
		if (!$this->post['city']) {
			$erno[]=$this->pi_getLL('no_city_has_been_specified');
		}
		if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] && !$this->post['company']) {
			$erno[]=$this->pi_getLL('company_is_required');
		}
		if (!$this->post['tx_multishop_pi1']['email_confirm'] || $this->post['tx_multishop_pi1']['email']!=$this->post['tx_multishop_pi1']['email_confirm']) {
			$erno[]=$this->pi_getLL('verification_email_not_match');
		}
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_address.php']['checkoutAddressValidationPreHook'])) {
			$params=array(
				'user'=>$user,
				'erno'=>&$erno
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_address.php']['checkoutAddressValidationPreHook'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		if (!$erno) {
			// billing details
			$user['email']=$this->post['tx_multishop_pi1']['email'];
			$user['company']=$this->post['company'];
			$user['first_name']=$this->post['first_name'];
			$user['middle_name']=$this->post['middle_name'];
			$user['last_name']=$this->post['last_name'];
			if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
				$user['birthday']=$this->post['birthday'];
			}
			$user['telephone']=$this->post['telephone'];
			$user['mobile']=$this->post['mobile'];
			$user['gender']=$this->post['gender'];
			$user['street_name']=$this->post['street_name'];
			$user['address_number']=$this->post['address_number'];
			$user['address_ext']=$this->post['address_ext'];
			$user['address']=$user['street_name'].' '.$user['address_number'].($user['address_ext'] ? '-'.$user['address_ext'] : '');
			$user['address']=preg_replace('/\s+/', ' ', $user['address']);
			$user['zip']=$this->post['zip'];
			$user['city']=$this->post['city'];
			$user['country']=$this->post['country'];
			$user['email']=$this->post['tx_multishop_pi1']['email'];
			$user['telephone']=$this->post['telephone'];
			$user['tx_multishop_newsletter']=$this->post['tx_multishop_newsletter'];
			if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
				$user['state']=$this->post['state'];
			}
			if ($this->ms['MODULES']['CHECKOUT_DISPLAY_VAT_ID_INPUT'] && !empty($this->post['tx_multishop_vat_id'])) {
				$user['tx_multishop_vat_id']=$this->post['tx_multishop_vat_id'];
			}
			if ($this->ms['MODULES']['CHECKOUT_DISPLAY_COC_ID_INPUT'] && !empty($this->post['tx_multishop_coc_id'])) {
				$user['tx_multishop_coc_id']=$this->post['tx_multishop_coc_id'];
			}
			// billing details eof
			// delivery details
			if (!$this->post['different_delivery_address']) {
				$user['different_delivery_address']=0;
			} else {
				$user['different_delivery_address']=1;
				$user['delivery_email']=$this->post['delivery_email'];
				$user['delivery_company']=$this->post['delivery_company'];
				$user['delivery_first_name']=$this->post['delivery_first_name'];
				$user['delivery_middle_name']=$this->post['delivery_middle_name'];
				$user['delivery_last_name']=$this->post['delivery_last_name'];
				$user['delivery_telephone']=$this->post['delivery_telephone'];
				$user['delivery_mobile']=$this->post['delivery_mobile'];
				$user['delivery_gender']=$this->post['delivery_gender'];
				$user['delivery_street_name']=$this->post['delivery_street_name'];
				$user['delivery_address_number']=$this->post['delivery_address_number'];
				$user['delivery_address_ext']=$this->post['delivery_address_ext'];
				$user['delivery_address']=$user['delivery_street_name'].' '.$user['delivery_address_number'].($user['delivery_address_ext'] ? '-'.$user['delivery_address_ext'] : '');
				$user['delivery_address']=preg_replace('/\s+/', ' ', $user['delivery_address']);
				$user['delivery_zip']=$this->post['delivery_zip'];
				$user['delivery_city']=$this->post['delivery_city'];
				$user['delivery_country']=$this->post['delivery_country'];
				$user['delivery_email']=$this->post['delivery_email'];
				$user['delivery_telephone']=$this->post['delivery_telephone'];
				$user['delivery_state']=$this->post['delivery_state'];
			}
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_address.php']['checkoutAddressUserSessionPreHook'])) {
				$params=array(
					'user'=>&$user
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_address.php']['checkoutAddressUserSessionPreHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
			// delivery details eof
			$cart['user']=$user;
			$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
			$GLOBALS['TSFE']->storeSessionData();
			// good, proceed with the next step
			next($stepCodes);
			require(current($stepCodes).'.php');
		} else {
			$user=array_merge($user, $this->post);
		}
	} else {
		$show_checkout_address=1;
	}
	if ($erno or $show_checkout_address) {
		if($this->post['tx_multishop_pi1']['email_confirm']) {
			$user['email_confirm']=$this->post['tx_multishop_pi1']['email_confirm'];
		} else {
			$user['email_confirm']=$user['email'];
		}
		// load enabled countries to array
		$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		$enabled_countries=array();
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$enabled_countries[]=$row2;
		}
		// load enabled countries to array eof
		//$regex = "/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
		$regex='/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i';
		$regex_for_character="/[^0-9]$/";
		if ($this->ms['MODULES']['CHECKOUT_VALIDATE_FORM']) {
			$validation.='
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery(\'#checkout\').h5Validate();
				});
			</script>';
			$GLOBALS['TSFE']->additionalHeaderData[]=$validation;
		}
		// birthday validation
		$GLOBALS['TSFE']->additionalHeaderData[]='
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY'] ? '
				$("#birthday_visitor").datepicker({
					dateFormat: "'.$this->pi_getLL('locale_date_format', 'mm-d-yy').'",
					altField: "#birthday",
					altFormat: "yy-mm-dd",
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					yearRange: "-100:+0"
				});
				' : '').'
				if (jQuery("#checkboxdifferent_delivery_address").is(\':checked\')) {
					// set the h5validate attributes for required delivery data
					$(\'#radio_delivery_gender_mr\').attr(\'required\', \'required\');
					$(\'#radio_delivery_gender_mr\').attr(\'data-h5-errorid\', \'invalid-delivery_gender\');
					$(\'#radio_delivery_gender_mr\').attr(\'title\', \''.$this->pi_getLL('gender_is_required', 'Title is required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

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

					jQuery(\'#delivery_address_category\').show();
				} else {
					// remove the h5validate attributes
					$(\'#radio_delivery_gender_mr\').removeAttr(\'required\');
					$(\'#radio_delivery_gender_mr\').removeAttr(\'data-h5-errorid\');
					$(\'#radio_delivery_gender_mr\').removeAttr(\'title\');

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

					jQuery(\'#delivery_address_category\').hide();
				}

				jQuery("#checkboxdifferent_delivery_address").click(function(event) {
					jQuery(\'#delivery_address_category\').slideToggle(\'slow\', function(){});

					if ($("#checkboxdifferent_delivery_address").is(\':checked\')) {
						// set the h5validate attributes for required delivery data
						$(\'#radio_delivery_gender_mr\').attr(\'required\', \'required\');
						$(\'#radio_delivery_gender_mr\').attr(\'data-h5-errorid\', \'invalid-delivery_gender\');
						$(\'#radio_delivery_gender_mr\').attr(\'title\', \''.$this->pi_getLL('gender_is_required', 'Title is required').' ('.mslib_befe::strtolower($this->pi_getLL('delivery_address')).')\');

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

						$(\'#delivery_address_category\').show();
					} else {
						// remove the h5validate attributes
						$(\'#radio_delivery_gender_mr\').removeAttr(\'required\');
						$(\'#radio_delivery_gender_mr\').removeAttr(\'data-h5-errorid\');
						$(\'#radio_delivery_gender_mr\').removeAttr(\'title\');

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
		 </script>';
		// birthday validation eof
		$content.=CheckoutStepping($stepCodes, current($stepCodes), $this);
		//
		if ($this->conf['multistep_checkout_address_tmpl_path']) {
			$template=$this->cObj->fileResource($this->conf['multistep_checkout_address_tmpl_path']);
		} else {
			$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/multistep_checkout_address.tmpl');
		}
		//
		if (is_array($erno) and count($erno)>0) {
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		}
		$content.='<div class="error_msg" style="display:none">';
		$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
		$content.='<li class="item-error" style="display:none"></li>';
		$content.='</ul></div>';
		//
		$markerArray=array();
		$markerArray['###CHECKOUT_MULTISTEP_FORM_URL###']=mslib_fe::typolink($this->conf['checkout_page_pid'], 'tx_multishop_pi1[page_section]=checkout&tx_multishop_pi1[previous_checkout_section]='.current($stepCodes));
		$markerArray['###LABEL_BILLING_ADDRESS###']=$this->pi_getLL('billing_address');
		$markerArray['###LABEL_GENDER_TITLE###']=ucfirst($this->pi_getLL('title'));
		$markerArray['###VALUE_GENDER_MALE_CHECKED###']=(($user['gender']=='m') ? ' checked' : '');
		$markerArray['###GENDER_INPUT_REQUIRED###']=(($this->ms['MODULES']['GENDER_INPUT_REQUIRED']) ? 'required="required" data-h5-errorid="invalid-gender" title="'.$this->pi_getLL('gender_is_required', 'Title is required').'"' : '');
		$markerArray['###LABEL_GENDER_MALE###']=ucfirst($this->pi_getLL('mr'));
		$markerArray['###VALUE_GENDER_FEMALE_CHECKED###']=(($user['gender']=='f') ? ' checked' : '');
		$markerArray['###LABEL_GENDER_FEMALE###']=ucfirst($this->pi_getLL('mrs'));
		//
		$birthdate_block='';
		if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
			$birthdate_block.='<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'</label>
					<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.htmlspecialchars($user['birthday']).'" >
					<input type="hidden" name="birthday" id="birthday" value="'.htmlspecialchars($user['birthday']).'" >';
		}
		$markerArray['###BIRTHDATE_BLOCK###']=$birthdate_block;
		$markerArray['###LABEL_FIRST_NAME###']=ucfirst($this->pi_getLL('first_name'));
		$markerArray['###VALUE_FIRST_NAME###']=htmlspecialchars($user['first_name']);
		$markerArray['###LABEL_ERROR_FIRSTNAME_MESSAGE###']=$this->pi_getLL('first_name_required');
		$markerArray['###LABEL_MIDDLE_NAME###']=ucfirst($this->pi_getLL('middle_name'));
		$markerArray['###VALUE_MIDDLE_NAME###']=htmlspecialchars($user['middle_name']);
		$markerArray['###LABEL_LAST_NAME###']=ucfirst($this->pi_getLL('last_name'));
		$markerArray['###VALUE_LAST_NAME###']=htmlspecialchars($user['last_name']);
		$markerArray['###LABEL_ERROR_LASTNAME_MESSAGE###']=$this->pi_getLL('surname_is_required');

		$markerArray['###LABEL_EMAIL###']=ucfirst($this->pi_getLL('e-mail_address'));
		$markerArray['###VALUE_EMAIL###']=htmlspecialchars($user['email']);
		$markerArray['###LABEL_ERROR_EMAIL_IS_REQUIRED###']=$this->pi_getLL('email_is_required');
		$markerArray['###LABEL_CONFIRMATION_EMAIL###']=ucfirst($this->pi_getLL('confirm_email_address'));
		$markerArray['###VALUE_CONFIRMATION_EMAIL###']=htmlspecialchars($user['email_confirm']);
		$markerArray['###LABEL_ERROR_CONFIRMATION_EMAIL_IS_REQUIRED###']=$this->pi_getLL('email_is_required');
		$markerArray['###LABEL_COMPANY###']=ucfirst($this->pi_getLL('company')).($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? '*' : '');
		$markerArray['###VALUE_COMPANY###']=htmlspecialchars($user['company']);
		$markerArray['###COMPANY_VALIDATION###']=($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? ' required="required" data-h5-errorid="invalid-company" title="'.$this->pi_getLL('company_is_required').'"' : '');
		//
		$vat_id_block='';
		if ($this->ms['MODULES']['CHECKOUT_DISPLAY_VAT_ID_INPUT']) {
			$vat_id_block.='<div class="account-field col-sm-6" id="input-tx_multishop_vat_id">
			<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.ucfirst($this->pi_getLL('vat_id')).'</label>
			<input type="text" name="tx_multishop_vat_id" class="tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.htmlspecialchars($user['tx_multishop_vat_id']).'"/>
			</div>';
		}
		$coc_id_block='';
		if ($this->ms['MODULES']['CHECKOUT_DISPLAY_COC_ID_INPUT']) {
			$coc_id_block.='<div class="account-field col-sm-6" id="input-tx_multishop_coc_id">
			<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">'.ucfirst($this->pi_getLL('coc_id')).'</label>
			<input type="text" name="tx_multishop_coc_id" class="tx_multishop_coc_id" id="tx_multishop_coc_id" value="'.htmlspecialchars($user['tx_multishop_coc_id']).'"/>
			</div>
			';
		}
		$markerArray['###INPUT_VAT_ID###']=$vat_id_block;
		$markerArray['###INPUT_COC_ID###']=$coc_id_block;
		//
		$state_block='';
		if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
			$state_block.='<div class="account-field col-sm-12" id="input-state">
				<label class="account-state" for="state">'.ucfirst($this->pi_getLL('state')).'*</label>
				<input type="text" name="state" id="state" class="state" value="'.htmlspecialchars($user['state']).'" >
			</div>';
		}
		$markerArray['###STATE_BLOCK###']=$state_block;
		//
		// load countries
		$country_block='';
		$delivery_country_block='';
		if (count($enabled_countries)==1) {
			$country_block.='<input name="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
			$delivery_country_block.='<input name="delivery_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		} else {
			$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
			if (!$user['country']) {
				$user['country']=$default_country['cn_short_en'];
			}
			if (!$user['delivery_country']) {
				$user['delivery_country']=$default_country['cn_short_en'];
			}
			foreach ($enabled_countries as $country) {
				$tmpcontent_con.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($user['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
				$tmpcontent_con_delivery.='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($user['delivery_country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en'])).'</option>';
			}
			if ($tmpcontent_con) {
				$country_block.='
				<div class="account-field col-sm-8" id="input-country">
					<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
					<select name="country" id="country" class="country" required="required" data-h5-errorid="invalid-country" title="'.$this->pi_getLL('country_is_required').'">
						<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
						'.$tmpcontent_con.'
					</select>
					<div id="invalid-country" class="error-space" style="display:none"></div>
		        </div>
				';
			}
		}
		// country eof
		$markerArray['###COUNTRY_BLOCK###']=$country_block;
		$markerArray['###LABEL_ZIP###']=ucfirst($this->pi_getLL('zip'));
		$markerArray['###VALUE_ZIP###']=htmlspecialchars($user['zip']);
		$markerArray['###LABEL_ERROR_ZIP_IS_REQUIRED###']=$this->pi_getLL('zip_is_required');
		$markerArray['###LABEL_STREET_NAME###']=ucfirst($this->pi_getLL('street_address'));
		$markerArray['###VALUE_STREET_NAME###']=htmlspecialchars($user['street_name']);
		$markerArray['###LABEL_ERROR_STREET_NAME_IS_REQUIRED###']=$this->pi_getLL('street_address_is_required');
		$markerArray['###LABEL_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number'));
		$markerArray['###VALUE_ADDRESS_NUMBER###']=htmlspecialchars($user['address_number']);
		$markerArray['###LABEL_ERROR_ADDRESS_NUMBER_IS_REQUIRED###']=$this->pi_getLL('street_number_is_required');
		$markerArray['###LABEL_ADDRESS_EXT###']=ucfirst($this->pi_getLL('address_extension'));
		$markerArray['###VALUE_ADDRESS_EXT###']=htmlspecialchars($user['address_ext']);
		$markerArray['###LABEL_CITY###']=ucfirst($this->pi_getLL('city'));
		$markerArray['###VALUE_CITY###']=htmlspecialchars($user['city']);
		$markerArray['###LABEL_ERROR_CITY_IS_REQUIRED###']=$this->pi_getLL('city_is_required');
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
		$markerArray['###LABEL_TELEPHONE###']=ucfirst($this->pi_getLL('telephone'));
		$markerArray['###VALUE_TELEPHONE###']=htmlspecialchars($user['telephone']);
		$markerArray['###TELEPHONE_VALIDATION###']=$telephone_validation;
		$mobile_input='';
		if ($this->ms['MODULES']['SHOW_MOBILE_NUMBER_INPUT_IN_CHECKOUT']) {
			$mobile_input='
			<div class="account-field col-sm-6" id="input-mobile">
				<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
				<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($user['mobile']).'">
			</div>
			';
		}
		$markerArray['###MOBILE_NUMBER_INPUT###']=$mobile_input;
		//
		$newsletter_subscribe='';
		if ($this->ms['MODULES']['DISPLAY_SUBSCRIBE_TO_NEWSLETTER_IN_CHECKOUT']) {
			$newsletter_subscribe.='
			<div class="checkboxAgreement accept_newsletter">
				<input type="checkbox" name="tx_multishop_newsletter" id="tx_multishop_newsletter" '.(($user['tx_multishop_newsletter']) ? 'checked' : '').' value="1" />
				<label class="checkbox_label_two" for="tx_multishop_newsletter">'.ucfirst($this->pi_getLL('subscribe_to_our_newsletter')).'</label>
			</div>';
		}
		$markerArray['###NEWSLETTER_SUBSCRIBE###']=$newsletter_subscribe;
		$markerArray['###DIFFERENT_DELIVERY_ADDRESS_CHECKED###']=(($user['different_delivery_address']) ? ' checked' : '');
		$markerArray['###LABEL_USE_DIFFERENT_DELIVERY_ADDRESS###']=$this->pi_getLL('click_here_if_your_delivery_address_is_different_from_your_billing_address');

		$markerArray['###LABEL_DELIVERY_ADDRESS###']=$this->pi_getLL('delivery_address');
		$markerArray['###LABEL_GENDER###']=ucfirst($this->pi_getLL('title'));
		$markerArray['###GENDER_MR_CHECKED###']=(($user['delivery_gender']=='m') ? ' checked' : '');
		$markerArray['###LABEL_GENDER_MR###']=ucfirst($this->pi_getLL('mr'));
		$markerArray['###GENDER_MRS_CHECKED###']=(($user['delivery_gender']=='f') ? ' checked' : '');
		$markerArray['###LABEL_GENDER_MRS###']=ucfirst($this->pi_getLL('mrs'));
		$markerArray['###LABEL_DELIVERY_FIRST_NAME###']=ucfirst($this->pi_getLL('first_name'));
		$markerArray['###VALUE_DELIVERY_FIRST_NAME###']=htmlspecialchars($user['delivery_first_name']);
		$markerArray['###LABEL_DELIVERY_MIDDLE_NAME###']=ucfirst($this->pi_getLL('middle_name'));
		$markerArray['###VALUE_DELIVERY_MIDDLE_NAME###']=htmlspecialchars($user['delivery_middle_name']);
		$markerArray['###LABEL_DELIVERY_LAST_NAME###']=ucfirst($this->pi_getLL('last_name'));
		$markerArray['###VALUE_DELIVERY_LAST_NAME###']=htmlspecialchars($user['delivery_last_name']);
		$markerArray['###LABEL_DELIVERY_COMPANY###']=ucfirst($this->pi_getLL('company')).($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? '*' : '');
		$markerArray['###VALUE_DELIVERY_COMPANY###']=htmlspecialchars($user['delivery_company']);
		$markerArray['###COMPANY_VALIDATION###']=($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY'] ? ' required="required" data-h5-errorid="invalid-delivery_company" title="'.$this->pi_getLL('company_is_required').'"' : '');
		$markerArray['###LABEL_DELIVERY_STREET_NAME###']=ucfirst($this->pi_getLL('street_address'));
		$markerArray['###VALUE_DELIVERY_STREET_NAME###']=htmlspecialchars($user['delivery_street_name']);
		$markerArray['###LABEL_DELIVERY_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number'));
		$markerArray['###VALUE_DELIVERY_ADDRESS_NUMBER###']=htmlspecialchars($user['delivery_address_number']);
		$markerArray['###LABEL_DELIVERY_ADDRESS_EXT###']=ucfirst($this->pi_getLL('address_extension'));
		$markerArray['###VALUE_DELIVERY_ADDRESS_EXT###']=htmlspecialchars($user['delivery_address_ext']);
		$markerArray['###LABEL_DELIVERY_ZIP###']=ucfirst($this->pi_getLL('zip'));
		$markerArray['###VALUE_DELIVERY_ZIP###']=htmlspecialchars($user['delivery_zip']);
		$markerArray['###LABEL_DELIVERY_CITY###']=ucfirst($this->pi_getLL('city'));
		$markerArray['###VALUE_DELIVERY_CITY###']=htmlspecialchars($user['delivery_city']);
		//
		$delivery_state_block='';
		if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
			$delivery_state_block='
			<div class="account-field col-sm-7" id="input-dfstate">
				<label class="account-state" for="delivery_state">'.ucfirst($this->pi_getLL('state')).'*</label>
				<input type="text" name="delivery_state" id="delivery_state" class="delivery_state" value="'.htmlspecialchars($user['delivery_state']).'" >
			</div>';
		}
		$markerArray['###DELIVERY_STATE_BLOCK###']=$delivery_state_block;
		//
		if ($tmpcontent_con) {
			$delivery_country_block='
			<div class="account-field col-sm-7" id="input-dfcountry">
				<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
				<select name="delivery_country" id="delivery_country" class="delivery_country">
					<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
					'.$tmpcontent_con_delivery.'
				</select>
				<div id="invalid-delivery_country" class="error-space" style="display:none"></div>
			</div>';
		}
		$markerArray['###DELIVERY_COUNTRY_BLOCK###']=$delivery_country_block;
		$markerArray['###LABEL_DELIVERY_EMAIL###']=ucfirst($this->pi_getLL('e-mail_address'));
		$markerArray['###VALUE_DELIVERY_EMAIL###']=htmlspecialchars($user['delivery_email']);
		$markerArray['###LABEL_DELIVERY_TELEPHONE###']=ucfirst($this->pi_getLL('telephone'));
		$markerArray['###VALUE_DELIVERY_TELEPHONE###']=htmlspecialchars($user['delivery_telephone']);
		//
		$mobile_input=='';
		if ($this->ms['MODULES']['SHOW_MOBILE_NUMBER_INPUT_IN_CHECKOUT']) {
			$mobile_input='
			<div class="account-field col-sm-6" id="input-dfmobile">
				<label for="delivery_mobile" class="account_mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
				<input type="text" name="delivery_mobile" id="delivery_mobile" class="delivery_mobile" value="'.htmlspecialchars($user['delivery_mobile']).'">
			</div>
			';
		}
		$markerArray['###DELIVERY_MOBILE_NUMBER_INPUT###']=$mobile_input;
		$markerArray['###SHOPPING_CART_URL###']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=shopping_cart');
		$markerArray['###LABEL_BACK###']=$this->pi_getLL('back');
		$markerArray['###LABEL_NEXT###']=$this->pi_getLL('next');
		//
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_address.php']['checkoutAddressPostHook'])) {
			$params=array(
				'content'=>&$content,
				'markerArray'=>&$markerArray
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/checkout/multistep/checkout_address.php']['checkoutAddressPostHook'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		//
		$content.=$this->cObj->substituteMarkerArray($template, $markerArray);
	}
}

?>