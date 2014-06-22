<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$active_shop=mslib_fe::getActiveShop();
$GLOBALS['TSFE']->additionalHeaderData[]='
<link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/css/style.css">
<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.css" />
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.js"></script>
<script type="text/javascript">
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
if ($_REQUEST['sub']=='update_payment_method' and $_REQUEST['payment_method_id']) {
	// update payment method
	$row=mslib_fe::getPaymentMethod($_REQUEST['payment_method_id'], 'p.id');
	if ($row['id']) {
		/* $data=unserialize($row['vars']);
		foreach ($this->post as $key => $value)
		{
			$data[$key]=trim($this->post[$key]);
		} */
		// now update the baby
		$updateArray=array();
		$updateArray['page_uid']=$this->post['related_shop_pid'];
		$updateArray['handling_costs']=$this->post['handling_costs'];
		$updateArray['tax_id']=$this->post['tax_id'];
		$updateArray['vars']=serialize($this->post);
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
	}
} elseif ($this->get['edit']) {
	$row=mslib_fe::getPaymentMethod($_REQUEST['payment_method_id'], 'p.id');
	$str="SELECT * from tx_multishop_payment_methods_description where id='".$row['id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$lngproduct=array();
	while (($tmprow=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$lngproduct[$tmprow['language_id']]=$tmprow;
	}
	$psp=$payment_methods[$row['provider']];
	$inner_content=mslib_fe::parsePaymentMethodEditForm($psp, unserialize($row['vars']), 1);
	$tmpcontent.='
		<form id="add_payment_form" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
		<input name="sub" type="hidden" value="update_payment_method" />
		<input name="payment_method_id" type="hidden" value="'.$row['id'].'" />
';
	foreach ($this->languages as $key=>$language) {
		$tmpcontent.='
		<div class="account-field">
		<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
			$tmpcontent.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		}
		$tmpcontent.=''.$language['title'].'
		</div>	
		<div class="account-field">
			<label for="name">'.$this->pi_getLL('admin_name').'</label>
			<input type="text" class="text" name="name['.$language['uid'].']" id="name_'.$language['uid'].'" value="'.htmlspecialchars($lngproduct[$language['uid']]['name']).'">
		</div>		
		<div class="account-field">
			<label for="description">'.t3lib_div::strtoupper($this->pi_getLL('admin_short_description')).'</label>
			<textarea name="description['.$language['uid'].']" id="description['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngproduct[$language['uid']]['description']).'</textarea>			
		</div>		
		';
	}
	$cost_tax_rate=0;
	$data=mslib_fe::getTaxRuleSet($row['tax_id'], $row['handling_costs']);
	$cost_tax_rate=$data['total_tax_rate'];
	$cost_tax=mslib_fe::taxDecimalCrop(($row['handling_costs']*$cost_tax_rate)/100);
	$cost_excl_vat_display=mslib_fe::taxDecimalCrop($row['handling_costs'], 2, false);
	$cost_incl_vat_display=mslib_fe::taxDecimalCrop($row['handling_costs']+$cost_tax, 2, false);
	$tmpcontent.='

		<div class="account-field">
			<label>'.$this->pi_getLL('code').'</label>
			'.$row['code'].'
		</div>';
	if (count($active_shop)>1) {
		$tmpcontent.='
						<div class="account-field">
							<label for="related_shop_pid">'.$this->pi_getLL('relate_shipping_to_shop', 'Relate this method to').'</label>
							<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0"'.(($row['page_uid']==0) ? ' checked="checked"' : '').' />&nbsp'.$this->pi_getLL('relate_payment_to_all_shop', 'All shop').'</span>';
		foreach ($active_shop as $pageinfo) {
			$tmpcontent.='<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="'.$pageinfo['puid'].'"'.(($row['page_uid']==$pageinfo['puid']) ? ' checked="checked"' : '').' />'.$pageinfo['title'].'</span>';
		}
		$tmpcontent.='
						</div>';
	} else {
		$tmpcontent.='<input type="hidden" name="related_shop_pid" value="'.$row['page_uid'].'">';
	}
	$tmpcontent.='
		<div class="account-field">
			<label>'.$this->pi_getLL('handling_costs').'</label>
			<div class="msAttribute">
				<div class="msAttributesField"><input type="text" id="display_name" name="display_name" class="msHandlingCostExcludingVat" value="'.$cost_excl_vat_display.'"><label for="display_name">'.$this->pi_getLL('excluding_vat').'</label></div>
				<div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msHandlingCostIncludingVat" value="'.$cost_incl_vat_display.'"><label for="display_name">'.$this->pi_getLL('including_vat').'</label></div>
				<div class="msAttributesField hidden"><input name="handling_costs" type="hidden" value="'.$row['handling_costs'].'" /></div>
			</div>
		</div>
		<div class="account-field">
		<label for="tax_id">'.$this->pi_getLL('admin_vat_rate').'</label>	
		<select name="tax_id" id="tax_id"><option value="0"'.$this->pi_getLL('admin_label_no_tax').'</option>';
	$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($tax_group=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$tmpcontent.='<option value="'.$tax_group['rules_group_id'].'" '.(($tax_group['rules_group_id']==$row['tax_id']) ? 'selected' : '').'>'.htmlspecialchars($tax_group['name']).'</option>';
	}
	$tmpcontent.='
		</select>
	</div>				
			'.$inner_content.'
		<div class="account-field">
			<label for="">&nbsp;</label>
			<input name="Submit" type="submit" class="msadmin_button" value="'.$this->pi_getLL('save').'" />
		</div>				
		</form>		
	';
	$tmpcontent.='
		<script type="text/javascript" language="JavaScript">
			function productPrice(to_include_vat, o, type) {
				var original_val	= o.val();
				var current_value 	= parseFloat(o.val());
				var tax_id 			= jQuery("#tax_id").val();
		
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
				jQuery(".msHandlingCostExcludingVat").keyup(function() {
					productPrice(true, jQuery(this));
				});
			
				jQuery("#tax_id").change(function() {
					jQuery(".msHandlingCostExcludingVat").each(function(i) {
						productPrice(true, jQuery(this));
					});
				});
			
				jQuery(".msHandlingCostIncludingVat").keyup(function() {
					productPrice(false, jQuery(this));
				});
			});
		</script>
		';
	$content.=$tmpcontent;
} elseif ($_REQUEST['sub']=='add_payment_method' and $_REQUEST['payment_method_code']) {
	if ($this->post) {
		$erno=array();
		$check=mslib_fe::getPaymentMethod($this->post['custom_code'], 'p.code');
		if ($check['id']) {
			$erno[]='<li>Code already in use</li>';
		}
		if (is_array($erno) and count($erno)>0) {
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item) {
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		} else {
			$this->post['custom_code']=trim($this->post['custom_code']);
			$this->post['handling_costs']=trim($this->post['handling_costs']);
			$_REQUEST['payment_method_code']=trim($_REQUEST['payment_method_code']);
			// save payment method
			$insertArray=array();
			$insertArray['code']=$this->post['custom_code'];
			$insertArray['handling_costs']=$this->post['handling_costs'];
			$insertArray['tax_id']=$this->post['tax_id'];
			$insertArray['sort_order']=$this->post['sort_order'];
			$insertArray['date']=time();
			$insertArray['status']=1;
			$insertArray['page_uid']=$this->post['related_shop_pid'];
			$insertArray['provider']=$_REQUEST['payment_method_code'];
			$insertArray['vars']=serialize($this->post);
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
			}
		}
	}
	if ($erno or !$this->post) {
		$psp=$payment_methods[$_REQUEST['payment_method_code']];
		$tmpcontent.='<form class="edit_form" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" id="add_payment_form" method="post">';
		foreach ($this->languages as $key=>$language) {
			$tmpcontent.='
		<div class="account-field">
		<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
			if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
				$tmpcontent.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
			}
			$tmpcontent.=''.$language['title'].'
		</div>	
		<div class="account-field">
			<label for="name">'.$this->pi_getLL('admin_name').'</label>
			<input type="text" class="text" name="name['.$language['uid'].']" id="name_'.$language['uid'].'" value="'.htmlspecialchars($lngproduct[$language['uid']]['name']).'">
		</div>		
		<div class="account-field">
			<label for="description">'.t3lib_div::strtoupper($this->pi_getLL('admin_short_description')).'</label>
			<textarea name="description['.$language['uid'].']" id="description['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngproduct[$language['uid']]['description']).'</textarea>			
		</div>		
		';
		}
		$tmpcontent.='
		<div class="account-field">
			<label for="custom_code">'.$this->pi_getLL('code').'</label>
			<input name="custom_code" id="custom_code" type="text" value="'.htmlspecialchars($_REQUEST['custom_code']).'" />
		</div>';
		if (count($active_shop)>1) {
			$tmpcontent.='
					<div class="account-field">
						<label for="related_shop_pid">'.$this->pi_getLL('relate_shipping_to_shop', 'Relate this method to').'</label>
						<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0" checked="checked"/>&nbsp;'.$this->pi_getLL('relate_payment_to_all_shop', 'All shop').'</span>';
			foreach ($active_shop as $pageinfo) {
				$tmpcontent.='<span><input name="related_shop_pid" id="related_shop_pid" type="radio" value="'.$pageinfo['puid'].'"'.(($this->shop_pid==$pageinfo['puid']) ? ' checked="checked"' : '').' />&nbsp;'.$pageinfo['title'].'</span>';
			}
			$tmpcontent.='
					</div>';
		} else {
			$tmpcontent.='<input type="hidden" name="related_shop_pid" value="'.$row['page_uid'].'">';
		}
		$tmpcontent.='
		<div class="account-field">
			<label>'.$this->pi_getLL('handling_costs').'</label>
			<div class="msAttribute">
				<div class="msAttributesField"><input type="text" id="display_name" name="display_name" class="msHandlingCostExcludingVat" value="0.00"><label for="display_name">'.$this->pi_getLL('excluding_vat').'</label></div>
				<div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msHandlingCostIncludingVat" value="0.00"><label for="display_name">'.$this->pi_getLL('including_vat').'</label></div>
				<div class="msAttributesField hidden"><input name="handling_costs" type="hidden" value="0" /></div>
			</div>
		</div>
		<div class="account-field">
		<label for="tax_id">'.$this->pi_getLL('admin_vat_rate').'</label>	
		<select name="tax_id" id="tax_id"><option value="0">'.$this->pi_getLL('admin_label_no_tax').'</option>';
		$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($tax_group=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$tmpcontent.='<option value="'.$tax_group['rules_group_id'].'" '.(($tax_group['rules_group_id']==$row['tax_id']) ? 'selected' : '').'>'.htmlspecialchars($tax_group['name']).'</option>';
		}
		$tmpcontent.='
		</select>
	</div>
		';
		$tmpcontent.=mslib_fe::parsePaymentMethodEditForm($psp, $this->post);
		$tmpcontent.='
		<div class="account-field">
			<label>&nbsp;</label>
			<input name="payment_method_code" type="hidden" value="'.htmlspecialchars($_REQUEST['payment_method_code']).'" />
			<input name="sub" type="hidden" value="add_payment_method" />
			<input name="Submit" class="msadmin_button" type="submit" value="'.$this->pi_getLL('save').'" />
		</div>
		</form>
		<script type="text/javascript" language="JavaScript">
			function productPrice(to_include_vat, o, type) {
				var original_val	= o.val();
				var current_value 	= parseFloat(o.val());
				var tax_id 			= jQuery("#tax_id").val();
				
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
				jQuery(".msHandlingCostExcludingVat").keyup(function() {
					productPrice(true, jQuery(this));
				});
					
				jQuery("#tax_id").change(function() {
					jQuery(".msHandlingCostExcludingVat").each(function(i) {
						productPrice(true, jQuery(this));
					});
				});
					
				jQuery(".msHandlingCostIncludingVat").keyup(function() {
					productPrice(false, jQuery(this));
				});	

				$("#add_payment_form").submit(function(e) {
					if (!$("#name_0").val()) {
						e.preventDefault();
						$("#name_0").focus();
						alert("'.$this->pi_getLL('payment_name_is_required').'!");
					} else if (!$("#custom_code").val()) {
						e.preventDefault();
						$("#custom_code").focus();
						alert("'.$this->pi_getLL('code_is_required').'!");
					} else {
						return true;
					}
				 });							
			});
		</script>	
		';
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
		$tmpcontent.='<th>'.$this->pi_getLL('payment_method').'</th><th width="60">'.$this->pi_getLL('template').'</th><th width="120">'.$this->pi_getLL('date_added').'</th><th width="60">'.$this->pi_getLL('status').'</th><th width="30">'.$this->pi_getLL('action').'</th></tr>
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
			</tr>';
		}
		$tmpcontent.='</tbody></table>';
	} else {
		$tmpcontent.=$this->pi_getLL('currently_there_are_no_payment_methods_defined').'.';
	}
	$tmpcontent.='<p class="float_right"><a href="#" id="add_payment_method" class="admin_menu_add label">'.$this->pi_getLL('add_payment_method').'</a></p>
	';
	$tmpcontent.='
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		// sortables
		var result2	= jQuery("#admin_modules_listing tbody.sortable_content").sortable({
				cursor:     "move", 
			//axis:       "y", 
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
		// sortables eof
	});
	</script>
	';
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
	$content.=mslib_fe::returnBoxedHTML(ucfirst(t3lib_div::strtolower($this->pi_getLL('admin_payment_methods'))), $tmpcontent);
	$tmpcontent='';
	// payment method admin system eof
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>