<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->post) {
	// save the payment to zone mappings
	if (is_array($this->post['payment_zone']) && count($this->post['payment_zone'])) {
		$payment_methods=mslib_fe::loadPaymentMethods();
		$zones=mslib_fe::loadAllCountriesZones();
		foreach ($zones['zone_id'] as $zone_id) {
			foreach ($payment_methods as $payment_method) {
				if ($this->post['payment_zone'][$zone_id][$payment_method['id']]) {
					// add mapping
					$insertArray=array();
					$insertArray['zone_id']=$zone_id;
					$insertArray['payment_method_id']=$payment_method['id'];
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_to_zones', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				} else {
					// delete mapping
					$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_payment_methods_to_zones', 'zone_id=\''.$zone_id.'\' and payment_method_id=\''.$payment_method['id'].'\'');
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'#payment_to_zone_mapping');
	}
	if (is_array($this->post['checkbox']) && count($this->post['checkbox'])) {
		$shipping_methods=mslib_fe::loadShippingMethods();
		$payment_methods=mslib_fe::loadPaymentMethods();
		foreach ($shipping_methods as $shipping_method) {
			foreach ($payment_methods as $payment_method) {
				if ($this->post['checkbox'][$shipping_method['id']][$payment_method['id']]) {
					// add mapping
					$insertArray=array();
					$insertArray['shipping_method']=$shipping_method['id'];
					$insertArray['payment_method']=$payment_method['id'];
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_shipping_mappings', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				} else {
					// delete mapping
					$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_payment_shipping_mappings', 'shipping_method=\''.$shipping_method['id'].'\' and payment_method=\''.$payment_method['id'].'\'');
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'#admin_shipping_payment_mappings');
	}
	if ($this->post['sub']=='add_payment_method' && $this->post['payment_method_code']) {
		$erno=array();
		$check=mslib_fe::getPaymentMethod($this->post['custom_code'], 'p.code');
		if ($check['id']) {
			$erno[]='<li>Code already in use</li>';
		}
		if (!count($erno)) {
			$this->post['custom_code']=trim($this->post['custom_code']);
			$this->post['handling_costs']=trim($this->post['handling_costs']);
			$this->post['payment_method_code']=trim($this->post['payment_method_code']);
			// save payment method
			$insertArray=array();
			$insertArray['code']=$this->post['custom_code'];
			if ($this->post['handling_costs_type']=='percentage') {
				$insertArray['handling_costs']=str_replace('%', '', $this->post['handling_costs']).'%';
			} else {
				$insertArray['handling_costs']=$this->post['handling_costs'];
			}
			$insertArray['tax_id']=$this->post['tax_id'];
			$insertArray['sort_order']=$this->post['sort_order'];
			$insertArray['date']=time();
			$insertArray['status']=1;
			$insertArray['page_uid']=$this->post['related_shop_pid'];
			$insertArray['provider']=$this->post['payment_method_code'];
			$insertArray['vars']=serialize($this->post);
			$updateArray['enable_on_default']=$this->post['enable_on_default'];
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($res) {
				$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
				foreach ($this->post['name'] as $key=>$value) {
					$updateArray=array();
					$updateArray['name']=$this->post['name'][$key];
					$updateArray['description']=$this->post['description'][$key];
					$str="select 1 from tx_multishop_payment_methods_description where id='".$id."' and language_id='".$key."'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_payment_methods_description', 'id=\''.$id.'\' and language_id=\''.$key.'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$updateArray['id']=$id;
						$updateArray['language_id']=$key;
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_description', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				$this->ms['show_main']=1;
				header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']));
			}
		}
	} else if ($this->post['sub']=='update_payment_method' && $this->post['payment_method_id']) {
		// update payment method
		$row=mslib_fe::getPaymentMethod($this->post['payment_method_id'], 'p.id');
		if ($row['id']) {
			// now update the baby
			$updateArray=array();
			$updateArray['page_uid']=$this->post['related_shop_pid'];
			if ($this->post['handling_costs_type']=='percentage') {
				$updateArray['handling_costs']=str_replace('%', '', $this->post['handling_costs']).'%';
			} else {
				$updateArray['handling_costs']=$this->post['handling_costs'];
			}
			$updateArray['tax_id']=$this->post['tax_id'];
			$updateArray['vars']=serialize($this->post);
			$updateArray['enable_on_default']=$this->post['enable_on_default'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_payment_methods', 'id=\''.$row['id'].'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			foreach ($this->post['name'] as $key=>$value) {
				$updateArray=array();
				$updateArray['name']=$this->post['name'][$key];
				$updateArray['description']=$this->post['description'][$key];
				$str="select 1 from tx_multishop_payment_methods_description where id='".$row['id']."' and language_id='".$key."'";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_payment_methods_description', 'id=\''.$row['id'].'\' and language_id=\''.$key.'\'', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				} else {
					$updateArray['id']=$row['id'];
					$updateArray['language_id']=$key;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_description', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
			$this->ms['show_main']=1;
			header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']));
		}
	}
}
$active_shop=mslib_fe::getActiveShop();
$GLOBALS['TSFE']->additionalHeaderData['admin_payment_methods_edit']='
<link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/css/style.css">
<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.css" />
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.js"></script>
<script type="text/javascript">
function productPrice(to_include_vat, o, type) {
	var original_val = o.val();
	var current_value = parseFloat(o.val());
	var tax_id = jQuery("#tax_id").val();
	if (current_value > 0) {
		if (to_include_vat) {
			jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: true, tax_group_id: jQuery("#tax_id").val() }, function(json) {
				if (json && json.price_including_tax) {
					var incl_tax_crop = decimalCrop(json.price_including_tax);

					o.parent().next().first().children().val(incl_tax_crop);
				} else {
					o.parent().next().first().children().val(current_value);
				}
			});

			// update the hidden excl vat
			o.parent().next().next().first().children().val(original_val);
		} else {
			jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: false, tax_group_id: jQuery("#tax_id").val() }, function(json) {
				if (json && json.price_excluding_tax) {
					var excl_tax_crop = decimalCrop(json.price_excluding_tax);

					// update the excl. vat
					o.parent().prev().first().children().val(excl_tax_crop);

					// update the hidden excl vat
					o.parent().next().first().children().val(json.price_excluding_tax);

				} else {
					// update the excl. vat
					o.parent().prev().first().children().val(original_val);

					// update the hidden excl vat
					o.next().parent().first().next().first().children().val(original_val);
				}
			});
		}
	} else {
		if (to_include_vat) {
			// update the incl. vat
			o.parent().next().first().children().val(0);

			// update the hidden excl vat
			o.parent().next().next().first().children().val(0);

		} else {
			// update the excl. vat
			o.parent().prev().first().children().next().val(0);

			// update the hidden excl vat
			o.next().parent().first().next().first().children().val(0);
		}
	}
}
function decimalCrop(float) {
	var numbers = float.toString().split(".");
	var prime 	= numbers[0];
	if (numbers[1] > 0 && numbers[1] != "undefined") {
		var decimal = new String(numbers[1]);
	} else {
		var decimal = "00";
	}
	var number = prime + "." + decimal.substr(0, 2);
	return number;
}
function mathRound(float) {
	//return float;
	return Math.round(float*100)/100;
}
jQuery(document).ready(function($) {
	$(\'.mceEditor\').redactor({
		focus: false,
		clipboardUploadUrl: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=clipboardUploadUrl').'\',
		imageUpload: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=imageUpload').'\',
		fileUpload: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=fileUpload').'\',
		imageGetJson: \''.$this->FULL_HTTP_URL.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_redactor&tx_multishop_pi1[redactorType]=imageGetJson').'\',
		minHeight:\'400\'
	});
	$("#add_payment_method").click(function(e){
		e.preventDefault();
		$(\'#admin_payment_methods_list\').slideToggle(\'slow\', function(){});
	});
	$(document).on("keyup", ".msHandlingCostExcludingVat", function() {
		productPrice(true, jQuery(this));
	});
	$(document).on("change", "#tax_id", function() {
		$(".msHandlingCostExcludingVat").each(function(i) {
			productPrice(true, jQuery(this));
		});
	});
	$(document).on("keyup", ".msHandlingCostIncludingVat", function() {
		productPrice(false, jQuery(this));
	});
	$(document).on("change", "#handling_cost_type", function(){
		if ($(this).val()=="amount") {
			$("#handling_cost_amount_div").show();
			$("#handling_cost_amount_input").removeAttr("disabled");
			$("#handling_cost_percentage_div").hide();
			$("#handling_cost_percentage_input").attr("disabled", "disabled");
		} else if ($(this).val()=="percentage") {
			$("#handling_cost_amount_div").hide();
			$("#handling_cost_amount_input").attr("disabled", "disabled");
			$("#handling_cost_percentage_div").show();
			$("#handling_cost_percentage_input").removeAttr("disabled");
		}
	});
	$(".pspSelect2").select2();
});
</script>
';
$default_payment_methods=mslib_fe::loadAllPaymentMethods();
$mslib_payment=t3lib_div::makeInstance('mslib_payment');
$mslib_payment->init($this);
$payment_methods=array();
$payment_methods=$mslib_payment->getInstalledPaymentMethods($this);
if (count($payment_methods)>0) {
	// merge default and installed payment
	$payment_methods=array_merge($default_payment_methods, $payment_methods);
} else {
	$payment_methods=$default_payment_methods;
}
//$content.=mslib_befe::print_r($payment_methods);
if ($this->get['edit']) {
	$row=mslib_fe::getPaymentMethod($this->get['payment_method_id'], 'p.id');
	$str="SELECT * from tx_multishop_payment_methods_description where id='".$row['id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$lngproduct=array();
	while (($tmprow=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$lngproduct[$tmprow['language_id']]=$tmprow;
	}
	$psp=$payment_methods[$row['provider']];
	// psp mail templates
	if (!isset($psp['vars']['order_confirmation'])) {
		$psp['vars']['order_confirmation']['type']='psp_mail_template_email_order_confirmation';
	}
	if (!isset($psp['vars']['order_paid'])) {
		$psp['vars']['order_paid']['type']='psp_mail_template_email_order_paid_letter';
	}
	if (!isset($psp['vars']['order_thank_you_page'])) {
		$psp['vars']['order_thank_you_page']['type']='psp_mail_template_order_received_thank_you_page';
	}
	if (!isset($psp['vars']['order_payment_reminder'])) {
		$psp['vars']['order_payment_reminder']['type']='psp_mail_template_payment_reminder_email_templates';
	}
	$inner_content=mslib_fe::parsePaymentMethodEditForm($psp, unserialize($row['vars']), 1);
	$tmpcontent.='<form id="add_payment_form" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
		<input name="sub" type="hidden" value="update_payment_method" />
		<input name="payment_method_id" type="hidden" value="'.$row['id'].'" />';
	foreach ($this->languages as $key=>$language) {
		$tmpcontent.='<div class="account-field">
		<label>'.mslib_befe::strtoupper($this->pi_getLL('language')).'</label>';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
			$tmpcontent.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		}
		$tmpcontent.=$language['title'].'
		</div>
		<div class="account-field">
			<label for="name">'.$this->pi_getLL('admin_name').'</label>
			<input type="text" class="text" name="name['.$language['uid'].']" id="name_'.$language['uid'].'" value="'.htmlspecialchars($lngproduct[$language['uid']]['name']).'" required="required">
		</div>
		<div class="account-field">
			<label for="description">'.mslib_befe::strtoupper($this->pi_getLL('admin_short_description')).'</label>
			<textarea name="description['.$language['uid'].']" id="description['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngproduct[$language['uid']]['description']).'</textarea>
		</div>';
	}
	$cost_tax_rate=0;
	$percentage_handling_cost=$row['handling_costs'];
	if (strpos($percentage_handling_cost, '%')===FALSE) {
		$tmp_phc=explode('.', $percentage_handling_cost);
		if (isset($tmp_phc[1])>0) {
			$percentage_handling_cost=mslib_fe::taxDecimalCrop($percentage_handling_cost, 2, false).'%';
		} else {
			$percentage_handling_cost=$percentage_handling_cost.'%';
		}
	}
	$amount_handling_cost=str_replace('%', '', $row['handling_costs']);
	$data=mslib_fe::getTaxRuleSet($row['tax_id'], $amount_handling_cost);
	$cost_tax_rate=$data['total_tax_rate'];
	$cost_tax=mslib_fe::taxDecimalCrop(($amount_handling_cost*$cost_tax_rate)/100);
	$cost_excl_vat_display=mslib_fe::taxDecimalCrop($amount_handling_cost, 2, false);
	$cost_incl_vat_display=mslib_fe::taxDecimalCrop($amount_handling_cost+$cost_tax, 2, false);
	$tmpcontent.='<div class="account-field">
		<label>'.$this->pi_getLL('code').'</label>
		<input name="custom_code" id="custom_code" type="hidden" value="'.htmlspecialchars($row['code']).'" />
		'.$row['code'].'
	</div>';
	if (count($active_shop)>1) {
		$tmpcontent.='<div class="account-field">
			<label for="related_shop_pid">'.$this->pi_getLL('relate_shipping_to_shop', 'Relate this method to').'</label>
			<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0"'.(($row['page_uid']==0) ? ' checked="checked"' : '').' />&nbsp'.$this->pi_getLL('relate_payment_to_all_shop', 'All shop').'</span>';

		foreach ($active_shop as $pageinfo) {
			$tmpcontent.='<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="'.$pageinfo['uid'].'"'.(($row['page_uid']==$pageinfo['uid']) ? ' checked="checked"' : '').' />'.$pageinfo['title'].'</span>';
		}
		$tmpcontent.='</div>';
	} else {
		$tmpcontent.='<input type="hidden" name="related_shop_pid" value="'.$row['page_uid'].'">';
	}
	$percentage_cost=false;
	if (strpos($row['handling_costs'], '%')!==false) {
		$percentage_cost=true;
	}
	$tmpcontent.='
		<div class="account-field">
			<label>'.$this->pi_getLL('handling_costs_type').'</label>
			<div class="msAttribute">
				<select name="handling_costs_type" id="handling_cost_type">
					<option value="amount"'.(!$percentage_cost ? ' selected="selected"' : '').'>amount</option>
					<option value="percentage"'.($percentage_cost ? ' selected="selected"' : '').'>percentage</option>
				</select>
			</div>
		</div>
		<div class="account-field" id="handling_cost_percentage_div"'.(!$percentage_cost ? ' style="display:none"' : '').'>
			<label>'.$this->pi_getLL('handling_costs').'</label>
			<div class="msAttribute">
				<input name="handling_costs" id="handling_cost_percentage_input" type="text" value="'.$percentage_handling_cost.'"'.(!$percentage_cost ? ' disabled="disabled"' : '').' />
			</div>
		</div>
		<div class="account-field" id="handling_cost_amount_div"'.($percentage_cost ? ' style="display:none"' : '').'>
			<label>'.$this->pi_getLL('handling_costs').'</label>
			<div class="msAttribute">
				<div class="msAttributesField"><input type="text" id="display_name" name="display_name" class="msHandlingCostExcludingVat" value="'.$cost_excl_vat_display.'"><label for="display_name">'.$this->pi_getLL('excluding_vat').'</label></div>
				<div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msHandlingCostIncludingVat" value="'.$cost_incl_vat_display.'"><label for="display_name">'.$this->pi_getLL('including_vat').'</label></div>
				<div class="msAttributesField hidden"><input name="handling_costs" type="hidden" value="'.$amount_handling_cost.'" id="handling_cost_amount_input"'.($percentage_cost ? ' disabled="disabled"' : '').' /></div>
			</div>
		</div>
		<div class="account-field">
		<label for="tax_id">'.$this->pi_getLL('admin_vat_rate').'</label>
		<select name="tax_id" id="tax_id"><option value="0">'.$this->pi_getLL('admin_label_no_tax').'</option>';
	$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($tax_group=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		if (!$this->get['payment_method_id']) {
			$tmpcontent.='<option value="'.$tax_group['rules_group_id'].'" '.(($tax_group['default_status']) ? 'selected' : '').'>'.htmlspecialchars($tax_group['name']).'</option>';
		} else {
			$tmpcontent.='<option value="'.$tax_group['rules_group_id'].'" '.(($tax_group['rules_group_id']==$row['tax_id']) ? 'selected' : '').'>'.htmlspecialchars($tax_group['name']).'</option>';
		}
	}
	$tmpcontent.='
		</select>
	</div>
			'.$inner_content.'
		<div class="account-field">
			<label>'.$this->pi_getLL('admin_label_method_is_enabled_on_default').'</label>
			<div class="input_label_wrapper"><input type="radio" name="enable_on_default" value="1" id="enable_on_default_yes"'.($row['enable_on_default']>0 ? ' checked="checked"' : '').' /><label for="enable_on_default_yes">'.$this->pi_getLL('yes').'</label></div>
			<div class="input_label_wrapper"><input type="radio" name="enable_on_default" value="0" id="enable_on_default_no"'.(!$row['enable_on_default'] ? ' checked="checked"' : '').' /><label for="enable_on_default_no">'.$this->pi_getLL('no').'</label></div>
		</div>
		<div class="account-field">
			<label for="">&nbsp;</label>
			<input name="Submit" type="submit" class="msadmin_button" value="'.$this->pi_getLL('save').'" />
		</div>
	</form>';
	$content.=$tmpcontent;
} else if ($this->get['sub']=='add_payment_method' && $this->get['payment_method_code']) {
	if ($erno or !$this->post) {
		if (count($erno)) {
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		}
		$psp=$payment_methods[$this->get['payment_method_code']];
		// psp mail templates
		if (!isset($psp['vars']['order_confirmation'])) {
			$psp['vars']['order_confirmation']['type']='psp_mail_template_email_order_confirmation';
		}
		if (!isset($psp['vars']['order_paid'])) {
			$psp['vars']['order_paid']['type']='psp_mail_template_email_order_paid_letter';
		}
		if (!isset($psp['vars']['order_thank_you_page'])) {
			$psp['vars']['order_thank_you_page']['type']='psp_mail_template_order_received_thank_you_page';
		}
		if (!isset($psp['vars']['order_payment_reminder'])) {
			$psp['vars']['order_payment_reminder']['type']='psp_mail_template_payment_reminder_email_templates';
		}
		$tmpcontent.='<form class="edit_form" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" id="add_payment_form" method="post">';
		foreach ($this->languages as $key=>$language) {
			$tmpcontent.='<div class="account-field">
				<label>'.mslib_befe::strtoupper($this->pi_getLL('language')).'</label>';
			if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
				$tmpcontent.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
			}
			$tmpcontent.=''.$language['title'].'
				</div>
				<div class="account-field">
					<label for="name">'.$this->pi_getLL('admin_name').'</label>
					<input type="text" class="text" name="name['.$language['uid'].']" id="name_'.$language['uid'].'" value="'.htmlspecialchars($lngproduct[$language['uid']]['name']).'" required="required">
				</div>
				<div class="account-field">
					<label for="description">'.mslib_befe::strtoupper($this->pi_getLL('admin_short_description')).'</label>
					<textarea name="description['.$language['uid'].']" id="description['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngproduct[$language['uid']]['description']).'</textarea>
				</div>';
		}
		$tmpcontent.='
		<div class="account-field">
			<label for="custom_code">'.$this->pi_getLL('code').'</label>
			<input name="custom_code" id="custom_code" type="text" value="'.htmlspecialchars($this->post['custom_code']).'" required="required" />
		</div>';
		if (count($active_shop)>1) {
			$tmpcontent.='
					<div class="account-field">
						<label for="related_shop_pid">'.$this->pi_getLL('relate_shipping_to_shop', 'Relate this method to').'</label>
						<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0" checked="checked"/>&nbsp;'.$this->pi_getLL('relate_payment_to_all_shop', 'All shop').'</span>';
			foreach ($active_shop as $pageinfo) {
				$tmpcontent.='<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="'.$pageinfo['uid'].'"'.(($this->shop_pid==$pageinfo['uid']) ? ' checked="checked"' : '').' />&nbsp;'.$pageinfo['title'].'</span>';
			}
			$tmpcontent.='
					</div>';
		} else {
			$tmpcontent.='<input type="hidden" name="related_shop_pid" value="'.$row['page_uid'].'">';
		}
		$tmpcontent.='
		<div class="account-field">
			<label>'.$this->pi_getLL('handling_costs_type').'</label>
			<div class="msAttribute">
				<select name="handling_costs_type" id="handling_cost_type">
					<option value="amount" selected="selected">amount</option>
					<option value="percentage">percentage</option>
				</select>
			</div>
		</div>
		<div class="account-field" id="handling_cost_percentage_div" style="display:none">
			<label>'.$this->pi_getLL('handling_costs').'</label>
			<div class="msAttribute">
				<input name="handling_costs" id="handling_cost_percentage_input" type="text" value="0%" disabled="disabled" />
			</div>
		</div>
		<div class="account-field" id="handling_cost_amount_div">
			<label>'.$this->pi_getLL('handling_costs').'</label>
			<div class="msAttribute">
				<div class="msAttributesField"><input type="text" id="display_name" name="display_name" class="msHandlingCostExcludingVat" value="0.00"><label for="display_name">'.$this->pi_getLL('excluding_vat').'</label></div>
				<div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msHandlingCostIncludingVat" value="0.00"><label for="display_name">'.$this->pi_getLL('including_vat').'</label></div>
				<div class="msAttributesField hidden"><input name="handling_costs" id="handling_cost_amount_input" type="hidden" value="0" /></div>
			</div>
		</div>
		<div class="account-field">
		<label for="tax_id">'.$this->pi_getLL('admin_vat_rate').'</label>
		<select name="tax_id" id="tax_id"><option value="0">'.$this->pi_getLL('admin_label_no_tax').'</option>';
		$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($tax_group=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if (!$this->get['payment_method_id']) {
				$tmpcontent.='<option value="'.$tax_group['rules_group_id'].'" '.(($tax_group['default_status']) ? 'selected' : '').'>'.htmlspecialchars($tax_group['name']).'</option>';
			} else {
				$tmpcontent.='<option value="'.$tax_group['rules_group_id'].'" '.(($tax_group['rules_group_id']==$row['tax_id']) ? 'selected' : '').'>'.htmlspecialchars($tax_group['name']).'</option>';
			}
		}
		$tmpcontent.='
		</select>
	</div>
		';
		$tmpcontent.=mslib_fe::parsePaymentMethodEditForm($psp, $this->post);
		$tmpcontent.='
		<div class="account-field">
			<label>'.$this->pi_getLL('admin_label_method_is_enabled_on_default').'</label>
			<div class="input_label_wrapper"><input type="radio" name="enable_on_default" value="1" id="enable_on_default_yes" checked="checked" /><label for="enable_on_default_yes">'.$this->pi_getLL('yes').'</label></div>
			<div class="input_label_wrapper"><input type="radio" name="enable_on_default" value="0" id="enable_on_default_no" /><label for="enable_on_default_no">'.$this->pi_getLL('no').'</label></div>
		</div>
		<div class="account-field">
			<label>&nbsp;</label>
			<input name="payment_method_code" type="hidden" value="'.htmlspecialchars($this->get['payment_method_code']).'" />
			<input name="sub" type="hidden" value="add_payment_method" />
			<input name="Submit" class="msadmin_button" type="submit" value="'.$this->pi_getLL('save').'" />
		</div>
		</form>';
		$content.=mslib_fe::returnBoxedHTML($psp['name'], $tmpcontent);
		$tmpcontent='';
	}
} else {
	$this->ms['show_main']=1;
}
if ($this->ms['show_main']) {
	if (is_numeric($this->get['status']) and is_numeric($this->get['payment_method_id'])) {
		$updateArray=array();
		$updateArray['status']=$this->get['status'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_payment_methods', 'id=\''.$this->get['payment_method_id'].'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	} elseif (is_numeric($this->get['status']) and is_numeric($this->get['shipping_method_id'])) {
		$updateArray=array();
		$updateArray['status']=$this->get['status'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods', 'id=\''.$this->get['shipping_method_id'].'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if (is_numeric($this->get['delete']) and is_numeric($this->get['payment_method_id'])) {
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_payment_methods', 'id=\''.$this->get['payment_method_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_payment_shipping_mappings', 'payment_method=\''.$this->get['payment_method_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_method_mappings', 'type=\'payment\' and method_id=\''.$this->get['payment_method_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if (isset($this->get['download']) && $this->get['download']=='payment' && is_numeric($this->get['payment_method_id'])) {
		$rowsData=array();
		$sql=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_payment_methods ', // FROM ...
			'id= \''.$this->get['payment_method_id'].'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$serial_value=array();
			foreach ($data as $key_idx=>$key_val) {
				$rowsData[$this->get['payment_method_id']]['general'][$key_idx]=$key_val;
			}
			$query_desc=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_payment_methods_description', // FROM ...
				'id=\''.$this->get['payment_method_id'].'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res_desc=$GLOBALS['TYPO3_DB']->sql_query($query_desc);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_desc)>0) {
				while ($row_desc=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_desc)) {
					foreach ($row_desc as $col_desc_name=>$col_desc_val) {
						$rowsData[$this->get['payment_method_id']]['description'][$row_desc['language_id']][$col_desc_name]=$col_desc_val;
					}
				}
			}
			$serial_data='';
			if (count($rowsData)>0) {
				$serial_data=serialize($rowsData);
			}
			$filename='multishop_payment_method_'.date('YmdHis').'_'.$this->get['payment_method_id'].'.txt';
			$filepath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/'.$filename;
			file_put_contents($filepath, $serial_data);
			header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
			header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
			readfile($filepath); //Read and stream the file
			@unlink($filepath);
			exit();
		}
	}
	if (isset($this->get['upload']) && $this->get['upload']=='payment' && $_FILES) {
		if (!$_FILES['payment_file']['error']) {
			$filename=$_FILES['payment_file']['name'];
			$target=$this->DOCUMENT_ROOT.'/uploads/tx_multishop'.$filename;
			if (move_uploaded_file($_FILES['payment_file']['tmp_name'], $target)) {
				$payment_content=file_get_contents($target);
				$unserial_payment_data=unserialize($payment_content);
				if (is_array($unserial_payment_data) && count($unserial_payment_data)) {
					foreach ($unserial_payment_data as $payment_data) {
						$insertArray=array();
						if (is_array($payment_data['general']) && count($payment_data['general'])) {
							foreach ($payment_data['general'] as $payment_col=>$payment_val) {
								if ($payment_col!='id') {
									switch ($payment_col) {
										case 'code':
											if (isset($this->post['new_code']) && !empty($this->post['new_code'])) {
												$insertArray['code']=$this->post['new_code'];
											} else {
												$insertArray['code']=$payment_val;
											}
											break;
										case 'page_uid':
											$insertArray['page_uid']=$this->shop_pid;
											break;
										default:
											$insertArray[$payment_col]=$payment_val;
											break;
									}
								}
							}
						}
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods', $insertArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						$payment_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
						if (is_array($payment_data['description']) && count($payment_data['description'])) {
							foreach ($payment_data['description'] as $language_id=>$payment_desc_data) {
								if (is_array($payment_desc_data) && count($payment_desc_data)) {
									$insertArrayDesc=array();
									foreach ($payment_desc_data as $payment_desc_col_name=>$payment_desc_val) {
										switch ($payment_desc_col_name) {
											case 'id':
												$insertArrayDesc['id']=$payment_id;
												break;
											default:
												$insertArrayDesc[$payment_desc_col_name]=$payment_desc_val;
												break;
										}
									}
									$query_desc=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_description', $insertArrayDesc);
									$GLOBALS['TYPO3_DB']->sql_query($query_desc);
								}
							}
						}
					}
				}
				@unlink($target);
			}
		}
		header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']));
	}
	// payment method admin system
	$colspan=4;
	$str="SELECT *,d.name from tx_multishop_payment_methods p, tx_multishop_payment_methods_description d where d.language_id='".$this->sys_language_uid."' and (p.page_uid = '".$this->shop_pid."' or p.page_uid = '0') and p.id=d.id order by p.sort_order";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$tr_type='even';
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
		$tmpcontent.='<table class="msZebraTable msadmin_border" id="admin_modules_listing">';
		$tmpcontent.='<tr>';
		if (count($active_shop)>1) {
			$tmpcontent.='<th>'.$this->pi_getLL('shop', 'Shop').'</th>';
		}
		$tmpcontent.='<th>'.$this->pi_getLL('payment_method').'</th><th width="60">'.$this->pi_getLL('template').'</th><th width="120">'.$this->pi_getLL('date_added').'</th><th width="60">'.$this->pi_getLL('status').'</th><th width="30">'.$this->pi_getLL('action').'</th><th width="30">'.ucfirst($this->pi_getLL('download')).'</th></tr>
		<tbody class="sortable_content">
		';
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			//		$tmpcontent.='<h3>'.$cat['name'].'</h3>';
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$tmpcontent.='<tr class="'.$tr_type.'" id="multishop_payment_method_'.$row['id'].'">';
			if (count($active_shop)>1) {
				if ($row['page_uid']>0) {
					$tmpcontent.='<td><strong>'.mslib_fe::getShopNameByPageUid($row['page_uid']).'</strong></td>';
				} else {
					$tmpcontent.='<td><strong>All</strong></td>';
				}
			}
			$tmpcontent.='<td><strong><a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&payment_method_id='.$row['id'].'&edit=1').'">'.$row['name'].'</a>
			</strong></td>
			<td>'.$row['provider'].'</td>
			<td>'.date("Y-m-d", $row['date']).'</td>
			<td align="center">';
			if (!$row['status']) {
				$tmpcontent.='<span class="admin_status_red" alt="'.$this->pi_getLL('disable').'"></span>';
				$tmpcontent.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&payment_method_id='.$row['id'].'&status=1').'"><span class="admin_status_green_disable" alt="'.$this->pi_getLL('enabled').'"></span></a>';
			} else {
				$tmpcontent.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&payment_method_id='.$row['id'].'&status=0').'"><span class="admin_status_red_disable" alt="'.$this->pi_getLL('disabled').'"></span></a>';
				$tmpcontent.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
			}
			$tmpcontent.='
			</td>
			<td align="center">
			<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&payment_method_id='.$row['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="'.$this->pi_getLL('admin_label_alt_remove').'"></a>
			</td>
			<td align="center">
				<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&download=payment&payment_method_id='.$row['id']).'" class="msadmin_button"><i>'.ucfirst($this->pi_getLL('download_record')).'</i></a>
			</td>
			</tr>';
		}
		$tmpcontent.='</tbody></table>';
	} else {
		$tmpcontent.=$this->pi_getLL('currently_there_are_no_payment_methods_defined').'.';
	}
	$tmpcontent.='<fieldset id="scheduled_import_jobs_form"><legend>'.$this->pi_getLL('upload_record').'</legend>
			<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&upload=payment').'" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="blockSubmitForm">
				<div class="account-field">
					<label for="new_code">'.$this->pi_getLL('code').'</label>
					<input name="new_code" type="text" value="" />
				</div>
				<div class="account-field">
					<label for="upload_payment_file">'.$this->pi_getLL('file').'</label>
					<input type="file" name="payment_file">&nbsp;<input type="submit" name="upload_payment_file" class="submit msadmin_button" id="upload_payment_file" value="upload">
				</div>
			</form>
		</fieldset>';
	$tmpcontent.='<p class="float_right"><a href="#" id="add_payment_method" class="admin_menu_add label">'.$this->pi_getLL('add_payment_method').'</a></p>';
	$tmpcontent.='<div id="flexible_container"><ul id="admin_payment_methods_list" class="hide">';
	$innercount=0;
	$count=0;
	foreach ($payment_methods as $code=>$item) {
		$innercount++;
		$count++;
		$tmpcontent.='<li class="item'.$count.'"><div class="flexible_li"><a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&sub=add_payment_method&payment_method_code='.$code).'" alt="Add '.htmlspecialchars($item['name']).'" title="Add '.htmlspecialchars($item['name']).'">';
		if ($item['image']) {
			$tmpcontent.='<span class="multishop_psp_image_wrapper"><span class="multishop_psp_image"><img src="'.t3lib_extMgm::siteRelPath($this->extKey).'templates/images/psp/'.$item['image'].'" alt="Add '.htmlspecialchars($item['name']).'" title="Add '.htmlspecialchars($item['name']).'"></span></span>';
		} else {
			$tmpcontent.='<span class="multishop_psp_name">'.$item['name'].'</span>';
		}
		$tmpcontent.='<span class="multishop_psp_add">add '.$item['name'].'</span></a>';
		if ($item['more_info_link']) {
			$tmpcontent.='<span class="multishop_psp_register"><a href="'.$item['more_info_link'].'" target="_blank">register account</a></span>';
		}
		$tmpcontent.='</div></li>';
		if ($innercount==3) {
			$innercount=0;
		}
	}
	if ($innercount>0) {
		for ($i=3; $i>$innercount; $i--) {
			$count++;
			$tmpcontent.='<li class="item'.$count.'"><div class="flexible_li">';
			$tmpcontent.='<a href="'.$this->conf['admin_development_company_url'].'" title="'.htmlspecialchars($this->conf['admin_development_company_name']).'" target="_blank"><span class="multishop_psp_image_wrapper"><span class="multishop_psp_image"><img src="'.$this->conf['admin_development_company_logo_gray_path'].'" border="0"></span></span><span class="multishop_psp_add"></span></a>';
			$tmpcontent.='';
			$tmpcontent.='</div></li>';
		}
	}
	$tmpcontent.='</ul></div>';
	//tabs array
	$tabs=array();
	// shipping methods tab
	$tabs[]=array('label'=>ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_payment_methods'))), 'id'=>'admin_payment_methods', 'content'=>mslib_fe::returnBoxedHTML(ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_payment_methods'))), $tmpcontent));
	// payment methods to zone mappings
	$tmpcontent='';
	$shipping_methods=mslib_fe::loadShippingMethods();
	$payment_methods=mslib_fe::loadPaymentMethods();
	$zones=mslib_fe::loadAllCountriesZones();
	if (count($zones['zone_id'])) {
		$colspan=4;
		$tr_type='even';
		if (count($payment_methods)) {
			$tmpcontent.='<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'">';
			$tmpcontent.='<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">';
			$tmpcontent.='<tr>';
			// zone header
			$zone_cols=array();
			foreach ($zones['zone_id'] as $zone_idx=>$zone_id) {
				$tmpcontent.='<th>'.$zones['zone_name'][$zone_idx].' ('.implode('<br/> ', $zones['countries'][$zone_id]).')</th>';
				$zone_cols[]=$zone_id;
			}
			$tmpcontent.='</tr>';
			$tmpcontent.='<tr>';
			// shipping method rows
			foreach ($zone_cols as $zone_id) {
				$tmpcontent.='<td>';
				$tmpcontent.='<table class="sortable_column">';
				$payment_methods_sorted=array();
				$unsorted_number=99;
				$sort_number=50;
				foreach ($payment_methods as $payment_method) {
					$sql_check="select id, sort_order from tx_multishop_payment_methods_to_zones where zone_id = ".$zone_id." and payment_method_id = ".$payment_method['id'];
					$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)) {
						$rs_check=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_check);
						$payment_method['checked']=true;
						if ($rs_check['sort_order']>0) {
							$payment_methods_sorted[$rs_check['sort_order']]=$payment_method;
						} else {
							$payment_methods_sorted[$sort_number]=$payment_method;
						}
						$sort_number++;
					} else {
						$payment_method['checked']=false;
						$payment_methods_sorted[$unsorted_number]=$payment_method;
						$unsorted_number++;
					}
				}
				ksort($payment_methods_sorted);
				foreach ($payment_methods_sorted as $payment_method) {
					$vars=unserialize($payment_method['vars']);
					if ($payment_method['checked']) {
						$tmpcontent.='<tr id="payment_zone_['.$zone_id.']_'.$payment_method['id'].'" class="row_sortable">';
						$tmpcontent.='<td><input type="checkbox" name="payment_zone['.$zone_id.']['.$payment_method['id'].']" id="payment_zone_'.$zone_id.'_'.$payment_method['id'].'" checked="checked" onclick="this.form.submit()"><label for="payment_zone_'.$zone_id.'_'.$payment_method['id'].'">'.$vars['name'][0].'</label></td>';
					} else {
						$tmpcontent.='<tr class="row_unsortable">';
						$tmpcontent.='<td><input type="checkbox" name="payment_zone['.$zone_id.']['.$payment_method['id'].']" id="payment_zone_'.$zone_id.'_'.$payment_method['id'].'" onclick="this.form.submit()"><label for="payment_zone_'.$zone_id.'_'.$payment_method['id'].'">'.$vars['name'][0].'</label></td>';
					}
					$tmpcontent.='</tr>';
				}

				$tmpcontent.='</table>';
				$tmpcontent.='</td>';
			}
			$tmpcontent.='</tr>';
			$tmpcontent.='</table>';
			$tmpcontent.='<input name="param" type="hidden" value="update_mapping" />';
			$tmpcontent.='</form>';
		} else {
			$tmpcontent.=$this->pi_getLL('admin_label_currently_no_payment_method_defined');
		}
	} else {
		$tmpcontent.=$this->pi_getLL('admin_label_currently_no_payment_method_defined');
	}
	$tabs[]=array('label'=>ucfirst(mslib_befe::strtolower($this->pi_getLL('payment_to_zone_mapping'))), 'id'=>'payment_to_zone_mapping', 'content'=>mslib_fe::returnBoxedHTML(ucfirst(mslib_befe::strtolower($this->pi_getLL('payment_to_zone_mapping'))), $tmpcontent));
	// shipping to payment mappings
	$tmpcontent='';
	$payment_methods=mslib_fe::loadPaymentMethods();
	if (count($payment_methods)) {
		//$tmpcontent.='<div class="main-heading"><h2>'.$this->pi_getLL('shipping_to_payment_mapping').'</h2></div>';
		$colspan=4;
		$tr_type='even';
		if (count($shipping_methods)) {
			$tmpcontent.='<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'">';
			$tmpcontent.='<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">';
			$tmpcontent.='<tr><th>&nbsp;</th>';
			foreach ($shipping_methods as $shipping_method) {
				$tmpcontent.='<th>'.$shipping_method['name'].'</th>';
			}
			$tmpcontent.='</tr>';
			foreach ($payment_methods as $row) {
				//		$content.='<h3>'.$cat['name'].'</h3>';
				if (!$tr_type or $tr_type=='even') {
					$tr_type='odd';
				} else {
					$tr_type='even';
				}
				$tmpcontent.='<tr class="'.$tr_type.'">
				<td><strong>'.$row['name'].'</strong></td>';
				foreach ($shipping_methods as $shipping_method) {
					$tmpcontent.='<td>';
					$tmpcontent.='<input name="checkbox['.$shipping_method['id'].']['.$row['id'].']" type="checkbox" value="1" onclick="this.form.submit();" ';
					$str2="SELECT * from tx_multishop_payment_shipping_mappings where payment_method='".$row['id']."' and shipping_method='".$shipping_method['id']."'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)>0) {
						$tmpcontent.='checked';
					}
					$tmpcontent.=' /></td>';
				}
				$tmpcontent.='</tr>';
			}
			$tmpcontent.='</table>';
			$tmpcontent.='<input name="param" type="hidden" value="update_mapping" /></form>';
		} else {
			$tmpcontent.=$this->pi_getLL('admin_label_currently_no_payment_method_defined');
		}
	} else {
		$tmpcontent.=$this->pi_getLL('admin_label_currently_no_payment_method_defined');
	}
	$tabs[]=array('label'=>ucfirst(mslib_befe::strtolower($this->pi_getLL('payment_to_shipping_mapping'))), 'id'=>'admin_shipping_payment_mappings', 'content'=>mslib_fe::returnBoxedHTML(ucfirst(mslib_befe::strtolower($this->pi_getLL('payment_to_shipping_mapping'))), $tmpcontent));
	// render the tabs
	$tab_button='';
	$tab_content='';
	foreach ($tabs as $tab) {
		$tab_button.='<li><a href="#'.$tab['id'].'">'.$tab['label'].'</a></li>';
		$tab_content.='<div style="display: block;" id="'.$tab['id'].'" class="tab_content">';
		$tab_content.=$tab['content'];
		$tab_content.='</div>';
	}
	$tabs_element='<div id="tab-container">';
	$tabs_element.='<ul class="tabs" id="admin_orders">';
	$tabs_element.=$tab_button;
	$tabs_element.='</ul>';
	$tabs_element.='<div class="tab_container">';
	$tabs_element.=$tab_content;
	$tabs_element.='</div>';
	$tabs_element.='</div>'; // parent #tab_container
	// flush to render variable
	$content=$tabs_element;
	// payment method admin system eof
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
$GLOBALS['TSFE']->additionalHeaderData['admin_payment_methods']='
<script type="text/javascript">
jQuery(document).ready(function($) {
	// sortables
	var result2	= jQuery("#admin_modules_listing tbody.sortable_content").sortable({
		cursor: "move",
		//axis: "y",
		update: function(e, ui) {
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=method_sortables').'";
			jQuery(this).sortable("refresh");
			sorted = jQuery(this).sortable("serialize", "id");
			jQuery.ajax({
				type:   "POST",
				url:    href,
				data:   sorted,
				success: function(msg) {
						//do something with the sorted data
				}
			});
		}
	});
	jQuery(".sortable_column").sortable({
		cursor: "move",
		//axis: "y",
		items: "tr.row_sortable",
		update: function(e, ui) {
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=zone_method_sortables').'";
			jQuery(this).sortable("refresh");
			sorted = jQuery(this).sortable("serialize", "id");
			jQuery.ajax({
				type:   "POST",
				url:    href,
				data:   sorted,
				success: function(msg) {
					//do something with the sorted data
				}
			});
		}
	});
	// sortables eof
	// tabs js
	$(".tab_content").hide();
    $("ul.tabs li:first").addClass("active").show();
    $(".tab_content:first").show();
    $("ul.tabs li").click(function () {
        $("ul.tabs li").removeClass("active");
        $(this).addClass("active");
        $(".tab_content").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn(0);
        return false;
    });
    // auto activate the tabs based on hash
    var lochash=window.location.hash;
	if (lochash!=\'\') {
		var li_this = $("ul > li").find("a[href=\'" + lochash + "\']").parent();
		if (li_this.length > 0) {
			$("ul.tabs li").removeClass("active");
			$(li_this).addClass("active");
			$(".tab_content").hide();
			$(lochash).fadeIn(0);
		}
	}
});
</script>
';
?>