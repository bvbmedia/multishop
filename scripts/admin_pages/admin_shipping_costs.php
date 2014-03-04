<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$content = '<script type="text/javascript" src="'.$this->FULL_HTTP_URL_MS.'scripts/front_pages/includes/js/shipping/xajax.js"></script>';

// $this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_CLIENT'] only can be set through plugin
if (isset($this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_CLIENT']) && !empty($this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_CLIENT'])) {
	$content .= '<script type="text/javascript" src="'.$this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_CLIENT'].'"></script>';
} else {
	$content .= '<script type="text/javascript" src="'.$this->FULL_HTTP_URL_MS.'scripts/front_pages/includes/js/shipping/zone.js"></script>';
}

$content.='<div class="main-heading"><h1>'.$this->pi_getLL('shipping_costs').'</h1></div>';
if ($this->post) {
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['postedShippingCostData'])) {
		$params = array (
				'post' => &$this->post
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['postedShippingCostData'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	
	foreach ($this->post['costs'] as $shippingid => $shipping_type) {
		if ($shipping_type == 'weight') {
			$i=0;	
			foreach ($this->post as $key => $weight) {
				$ship_zon = explode(":",$key);
				$key_price = $ship_zon[0].$ship_zon[1] . "_Price";
				if (count($ship_zon) == 2 && $ship_zon[0] == $shippingid) {
					//delete DB  for clean old data
					$where_del = 'shipping_method_id = '. $ship_zon[0] .' AND zone_id = '.$ship_zon[1];
					//echo $where_del;
					
					//select row for checking if any 
					$checking_query = $GLOBALS['TYPO3_DB']->SELECTquery(
							'count(*) as jumrow',         // SELECT ...
							'tx_multishop_shipping_methods_costs',     // FROM ...
							$where_del,    // WHERE.
							'',            // GROUP BY...
							'',    // ORDER BY...
							''            // LIMIT ...
						);
					$res_checking = $GLOBALS['TYPO3_DB']->sql_query($checking_query);
					$row_checking = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
					
					$i=0;
					$weight_and_price = "";
					foreach ($weight as $value) {
						if (strstr($this->post[$key_price][$i],",")) {
							$this->post[$key_price][$i] = str_replace(",",".",$this->post[$key_price][$i]);
						}
						if ($this->post[$key_price][$i]) {
							$weight_and_price[]= $this->post[$key][$i].":".$this->post[$key_price][$i];
						} else {
							if (count($weight_and_price) > 0) {
								$weight_and_price[] = '101:';
							}
						}
						$i++;
					}
					if (count($weight_and_price) > 0) {
						$weight_and_price[] = '';
					}
					//checking row
					if ($row_checking['jumrow'] == 0) {
						$insertArray=array();	
						$insertArray['shipping_method_id']	= $ship_zon[0];
						$insertArray['zone_id']				= $ship_zon[1];
						$insertArray['price'] 				= implode(',',$weight_and_price);
						
						$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_costs', $insertArray);
						$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
					} else {
						$insertArray=array();	
						$insertArray['price'] 				= implode(',',$weight_and_price);
						
						$query_update = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods_costs', $where_del,$insertArray);
						$res_update = $GLOBALS['TYPO3_DB']->sql_query($query_update);
					}
				}
				unset($insertArray);
			}
		} else if ($shipping_type == 'flat') {
			foreach ($this->post as $key => $weight) {
				$ship_zon = explode(":",$key);
				$ship_zon_id = 	$ship_zon[0];
				if (count($ship_zon) == 2 && $ship_zon[0] == $shippingid) {
					$where_del = 'shipping_method_id = '. $ship_zon[0] .' AND zone_id = '.$ship_zon[1];
					$checking_query = $GLOBALS['TYPO3_DB']->SELECTquery(
							'count(*) as jumrow',         // SELECT ...
							'tx_multishop_shipping_methods_costs',     // FROM ...
							$where_del,    // WHERE.
							'',            // GROUP BY...
							'',    // ORDER BY...
							''            // LIMIT ...
					);
					$res_checking = $GLOBALS['TYPO3_DB']->sql_query($checking_query);
					$row_checking = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
					$weight_and_price = $this->post[$key];
					
					//5.95,32.23:0
					//echo $weight_and_price;
					//die();
					
					if (strstr($weight_and_price,",") && !strstr($weight_and_price,":")) {
						$weight_and_price = str_replace(",",".",$weight_and_price);
					}
					
					//echo $weight_and_price;
					//die();
					
					//checking row
					if ($row_checking['jumrow'] == 0) {
						$insertArray=array();	
						$insertArray['shipping_method_id']	=	$ship_zon[0];
						$insertArray['zone_id']				=	$ship_zon[1];
						$insertArray['price']			   =	$weight_and_price;
						
						$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_costs', $insertArray);
						$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
					} else {
						$insertArray=array();	
						$insertArray['price']			   = $weight_and_price;
						
						$query_update = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods_costs', $where_del,$insertArray);
						$res_update = $GLOBALS['TYPO3_DB']->sql_query($query_update);
					}
						
				} // end of check for flat value
				unset($insertArray);
			}
		}
		//if cost type is empty / no shipping
		if ($shipping_type=='') {
			$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_shipping_methods_costs', 'shipping_method_id = ' . $shippingid);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		}
		//update for tx_multishop_shipping_methods
		$update_shipping =array();	
		$update_shipping['shipping_costs_type'] = $shipping_type;
		$query_update_shipping = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods', "id = $shippingid",$update_shipping);
		$res_update_ship = $GLOBALS['TYPO3_DB']->sql_query($query_update_shipping);
	}  // end for POST		
} //end if post
$str="SELECT * from tx_multishop_zones order by name";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$zones=array();	
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
	$zones[]=$row;
}	
$shipping_methods=mslib_fe::loadShippingMethods();

$shipping_cost_types = array();
$shipping_cost_types[] = array('value' => 'flat', 'title' => $this->pi_getLL('flat_based'));
$shipping_cost_types[] = array('value' => 'weight', 'title' => $this->pi_getLL('weight_based'));
// hook to add shipping cost type
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['addShippingCostType'])) {
	$params = array (
			'shipping_cost_types' => &$shipping_cost_types,
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['addShippingCostType'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// hook to add shipping cost type eof

$tr_type='even';
if (count($shipping_methods) > 0) {
	$content.='<form class="edit_form" action="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post" enctype="multipart/form-data">';
	$jumShippingMethod = array();
	foreach ($shipping_methods as $row) {

		$content.='<fieldset class="multishop_fieldset">';
		$content.='<legend>'.$this->pi_getLL('shipping_method').': '.$row['name'].'</legend>';
		$content.='<select name="costs['.$row['id'].']" id="flat_weight'.$row['id'].'">
		<option value="">'.$this->pi_getLL('no_shipping_costs').'</option>';
		
		foreach ($shipping_cost_types as $shipping_cost_type) {
			$content.='<option value="'.$shipping_cost_type['value'].'" '.(($row['shipping_costs_type']==$shipping_cost_type['value'])?'selected':'').'>'.$shipping_cost_type['title'].'</option>';
		}
		
		$content.='</select>
		<input type="hidden" id="based_old'.$row['id'].'" value="'. $row['shipping_costs_type'] .'" />';
		
		$content .='<div id="has'.$row['id'].'">';
		//if empty
		
		if (empty($row['shipping_costs_type'])) {
			$jumShippingMethod[] = $row['id'];
			$content.='</div></fieldset>';
			continue;
		}
		if ($row['shipping_costs_type']=='flat') {
			// start for flat based
			foreach ($zones as $zone) {
				$content.='<fieldset>';
				$content.='<legend>Zone: '.$zone['name'];
				$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$content.=' (';
				$tmpcontent='';
				while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
					$tmpcontent.=$row2['cn_iso_2'].',';
				}
				$tmpcontent=substr($tmpcontent,0,strlen($tmpcontent)-1);
				$content.=$tmpcontent.')';
				$content.='</legend>';
				
				$str3="SELECT * from tx_multishop_shipping_methods_costs where shipping_method_id='".$row['id']."' and zone_id='".$zone['id']."'";
				$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
				$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
				
				$sc_tax_rate 	= 0;
				$data 			= mslib_fe::getTaxRuleSet($row['tax_id'], 0);
				$sc_tax_rate 	= $data['total_tax_rate'];

				if (strpos($row3['price'], ':') !== false || strpos($row3['price'], ',') !== false) {
					$sc_tax 				= 0;
					$sc_price_display 		= $row3['price'];
					
					$price_list_format = explode(',', $row3['price']);
					$price_list_incl_tax = array();
					foreach ($price_list_format as $price_format) {
						$price_excl = explode(':', $price_format);
						$pf_tax = mslib_fe::taxDecimalCrop(($price_excl[0]*$sc_tax_rate)/100);
						$price_incl = mslib_fe::taxDecimalCrop($price_excl[0] + $pf_tax, 2, false);
						$price_excl[0] = $price_incl;
						
						$price_list_incl_tax[] = implode(':', $price_excl);
					}
					
					//$sc_price_display_incl 	= $row3['price'];
					$sc_price_display_incl 	= implode(',', $price_list_incl_tax);
					
				} else {
					$sc_tax 			= mslib_fe::taxDecimalCrop(($row3['price']*$sc_tax_rate)/100);
					$sc_price_display = mslib_fe::taxDecimalCrop($row3['price'], 2, false);
					$sc_price_display_incl = mslib_fe::taxDecimalCrop($row3['price'] + $sc_tax, 2, false);
				}
				
				$zone_pid =  $row['id'] .":". $zone['id'];
				$content .='
					<table>
					<tr>
							<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'"><b> Level 1 :</b></div></td>
							<td width="100" align="right">
								<div>
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="'.htmlspecialchars($sc_price_display).'" rel="'.$row['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="'.htmlspecialchars($sc_price_display_incl).'" rel="'.$row['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
									<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'"  value="'.$row3['price'].'"></div>
								</div>
							</td>
						</tr></table>
				';
				$content.='</fieldset>';
			}
		
		} else if ($row['shipping_costs_type']=='weight') {
			// start for weight based
			foreach ($zones as $zone) {
				$content.='<fieldset>';
				$content.='<legend>Zone: '.$zone['name'];
				$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$content.=' (';
				$tmpcontent='';
				while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
					$tmpcontent.=$row2['cn_iso_2'].',';
				}
				$tmpcontent=substr($tmpcontent,0,strlen($tmpcontent)-1);
				$content.=$tmpcontent.')';
				$content.='</legend>';
				$str3="SELECT * from tx_multishop_shipping_methods_costs where shipping_method_id='".$row['id']."' and zone_id='".$zone['id']."'";
				$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
				$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);			
				$content .= '<table border="0" cellpadding="0" cellspacing="0" height="100%">';
				$zone_pid =  $row['id'] . $zone['id'];
				
				if (!empty($row3['price']) && substr($row3['price'], -1) != ',') {
					$row3['price'] .= ',';
				}
				
				$shipping_cost = explode(",",$row3['price']);
				$count_sc = count($shipping_cost);
				
				if (empty($shipping_cost[$count_sc - 1])) {
					unset($shipping_cost[$count_sc - 1]);
				}
				
				if ($count_sc > 0){
					for ($i=1; $i<=$count_sc; $i++){
						$nextVal = $i + 1;
						$numKey = $i;
						$end_weight = 101;
						
						$zhone_price = explode(":",$shipping_cost[$numKey - 1]);
						
						$sc_tax_rate 	= 0;
						$data 			= mslib_fe::getTaxRuleSet($row['tax_id'], 0);
						$sc_tax_rate 	= $data['total_tax_rate'];
						
						$sc_tax 			= mslib_fe::taxDecimalCrop(($zhone_price[1]*$sc_tax_rate)/100);
						$sc_price_display = mslib_fe::taxDecimalCrop($zhone_price[1], 2, false);
						$sc_price_display_incl = mslib_fe::taxDecimalCrop($zhone_price[1] + $sc_tax, 2, false);
						
						// custom hook that can be controlled by third-party plugin
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['weightConversion'])) {
							$params = array (
									'zhone_price' => &$zhone_price,
									'end_weight' => &$end_weight
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['weightConversion'] as $funcRef) {
								t3lib_div::callUserFunction($funcRef, $params, $this);
							}
						}
						
						$weight_next = $i > 1 ? $weight_old : '0';
						$zone_price_display = $zhone_price[0] == $end_weight ? $this->pi_getLL('end') : $zhone_price[0];
						
						$content .= '
						<tr>
							<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'"><b> Level '.$i.':</b></div></td>
							<td width="70" align="right"><div id="22631_'.$zone_pid .'_BeginWeightLevel'.$i.'">'. $weight_next  .' '.$this->pi_getLL('admin_shipping_kg').'</div></td>
							<td width="70" align="center"><div id="22631_'. $zone_pid .'_TotLevel'.$i.'"> to </div></td>
							<td>
							
							<select name="'. $row['id'] .":". $zone['id'] .'[]" id="22631_'. $zone_pid .'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('. $nextVal .', 22631, '. $zone_pid .'); ">
							<option  value= "'.$zhone_price[0] .'" selected="selected">'. $zone_price_display .'</option>
							</select>
							<input type="hidden" id="init_22631_'. $zone_pid .'_EndWeightLevel'.$i.'" value="'.$zhone_price[0] .'" />
							<input type="hidden" id="prev_22631_'. $zone_pid .'_EndWeightLevel'.$i.'" value="'.$weight_next .'" />
							<input type="hidden" id="nextdesc_22631_'. $zone_pid .'_EndWeightLevel'.$i.'" value="#22631_'.$zone_pid .'_BeginWeightLevel'.($nextVal).'" />
							
							</td>
							<td width="100" align="right">
								<div id="22631_'. $zone_pid .'_PriceLevel'.$i.'">
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="'.htmlspecialchars($sc_price_display).'" rel="'.$row['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="'.htmlspecialchars($sc_price_display_incl).'" rel="'.$row['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
									<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'_Price[]" id="22631_'. $zone_pid.'_Price'.$i.'" value="'.$zhone_price[1].'"></div>
								</div>
							</td>
						</tr>
						';
						$weight_old = $zhone_price[0];
					}
				}  
				
				if (count($shipping_cost) < 13) {
					for ($i= count($shipping_cost) ; $i<=10; $i++) {
						$nextVal = $i + 1;
						$content .= '
							<tr id="row_22631_'.$zone_pid .'_NivLevel'.$i.'">
							<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'" style="display:none;"><b> Level '.$i.':</b></div></td>
							<td width="70" align="right"><div style="display:none;" id="22631_'.$zone_pid .'_BeginWeightLevel'.$i.'" >0 '.$this->pi_getLL('admin_shipping_kg').'</div></td>
							<td width="70" align="center"><div style="display:none;" id="22631_'. $zone_pid .'_TotLevel'.$i.'" > to </div></td>
							<td><select name="'. $row['id'] .":". $zone['id'] .'[]" id="22631_'. $zone_pid .'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('. $nextVal .', 22631, '. $zone_pid .'); " style="display:none;"></select>
							<input type="hidden" id="init_22631_'. $zone_pid .'_EndWeightLevel'.$i.'" value="" />
							<input type="hidden" id="prev_22631_'. $zone_pid .'_EndWeightLevel'.$i.'" value="" />
							<input type="hidden" id="next_desc_22631_'. $zone_pid .'_EndWeightLevel'.$i.'" value="#22631_'.$zone_pid .'_BeginWeightLevel'.($nextVal).'" />		
							</td>
							<td width="100" align="right">
								<div id="22631_'. $zone_pid .'_PriceLevel'.$i.'" style="display:none;" >
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="" rel="'.$row['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="" rel="'.$row['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
									<div class="msAttributesField hidden"><input type="hidden" style="text-align:right; display=none;" size="3" name="'.$zone_pid.'_Price[]" id="22631_'. $zone_pid.'_Price'.$i.'" value="" /></di>
								</div>
							</td>
							</tr>';
					}
				}
				$content .= '</table>';
				$content .= "
				<script type=\"text/javascript\">
					var Zone = ".$zone_pid.";";
				for ($i=1; $i<count($shipping_cost); $i++){
					$content .= "CreateWeightList(PackageId + '_' + Zone + '_EndWeightLevel" . $i . "', 0, 1, PackageId, Zone);\n";
				}
				//echo ();
				//die();
				if (empty($row3['price'])){
					$content .= "CreateWeightList(PackageId + '_' + Zone + '_EndWeightLevel1', 0, 1, PackageId, Zone);";
				}
				$content .= "</script>";
				$content.='</fieldset>';
				//break;
			} //end for weight based
			
		} else {
			// hook to process custom visual shipping cost type
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['processZoneShippingCostType'])) {
				$params = array (
					'row' => &$row,
					'zones' => &$zones,
					'content' => &$content
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['processZoneShippingCostType'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// hook to process custom visual shipping cost type eof
		}
		$content.='</div></fieldset>';
		$jumShippingMethod[] = $row['id'];
		//break;
	}
	$content.='
	<div class="account-field">
		<label>&nbsp;</label>
		<input name="Submit" type="submit" value="Save" class="msadmin_button" />
	</div>						
	</form>';
	
	if (isset($this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_AJAX']) && !empty($this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_AJAX'])) {
		$url_relative = $this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_AJAX'];
	} else {
		$url_relative = '&tx_multishop_pi1[page_section]=admin_shipping_costs_ajax';
	}
	
	$content .= '
		<script type="text/javascript">
		jQuery(document).ready(function($) {
		
			var url_relatives = "'. mslib_fe::typolink(',2002',$url_relative) .'";
	
			jQuery("#load").hide();
			jQuery().ajaxStart(function() {
				jQuery("#load").show();
				jQuery("#has").hide();
			}).ajaxStop(function() {
				jQuery("#load").hide();
				jQuery("#has").show();
	
			});';
	
	foreach ($jumShippingMethod as $valId) {
		$content .= '
				jQuery("#flat_weight'.$valId.'").change(function(){
				jQuery.ajax({
					type: "POST",
					url: url_relatives,
					data: {zone:"'. count($zones) .'",based:jQuery(this).val(),shippingid:"'.$valId.'",basedold:jQuery("#based_old'.$valId.'").val()},
					success: function(data) {
						jQuery("#has'.$valId.'").html(data);
					}
				});
			});
		';
	}
	$content .='
	function productPrice(to_include_vat, o, type) {
		var original_val	= o.val();
		var current_value 	= parseFloat(o.val());
		var tax_id 			= o.attr("rel");
		
		var have_comma = original_val.indexOf(",");
		var have_colon = original_val.indexOf(":");
		
		/*
		if ((have_comma > 0 || have_colon > 0) || (have_comma > 0 && have_colon > 0)) {
			if (to_include_vat) {
				// update the incl. vat
				o.parent().next().first().children().val(original_val);
				
				// update the hidden excl vat
				o.parent().next().next().first().children().val(original_val);
			
			} else {
				// update the excl. vat
				o.parent().prev().first().children().val(original_val);
				
				// update the hidden excl vat
				o.parent().next().first().children().val(original_val);
			}
		} else*/
						
		if (current_value > 0) {
			if (to_include_vat) {
				jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: true, tax_group_id: tax_id }, function(json) {
    				if (json && json.price_including_tax) {
						if ((have_comma > 0 || have_colon > 0) || (have_comma > 0 && have_colon > 0)) {
							o.parent().next().first().children().val(json.price_including_tax);
						} else {
							var incl_tax_crop = decimalCrop(json.price_including_tax);
							o.parent().next().first().children().val(incl_tax_crop);
						}
						
					} else {
						o.parent().next().first().children().val(original_val);
					}
    			});
							
				// update the hidden excl vat
				o.parent().next().next().first().children().val(original_val);
						
			} else {
				jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: false, tax_group_id: tax_id }, function(json) {
    				if (json && json.price_excluding_tax) {
						if ((have_comma > 0 || have_colon > 0) || (have_comma > 0 && have_colon > 0)) {
							o.parent().prev().first().children().val(json.price_excluding_tax);
						} else {
							var excl_tax_crop = decimalCrop(json.price_excluding_tax);
									
							// update the excl. vat
							o.parent().prev().first().children().val(excl_tax_crop);
						}
									
						// update the hidden excl vat
						o.parent().next().first().children().val(json.price_excluding_tax);
									
					} else {
						// update the excl. vat
						o.parent().prev().first().children().val(original_val);
									
						// update the hidden excl vat
						o.parent().next().first().children().val(original_val);
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
				o.parent().prev().first().children().val(0);
				
				// update the hidden excl vat
				o.parent().next().first().children().val(0);
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
	$(document).on("change", ".msProductsPriceExcludingVat", function() {
		productPrice(true, jQuery(this));
	});	
	$(document).on("change", ".msProductsPriceIncludingVat", function() {
		productPrice(false, $(this));
	});			
});
</script>';	
} else {
	$content.='Currently there isn\'t any shipping method defined.';
}
$content .= '<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content = '<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>