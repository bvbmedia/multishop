<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if ($this->ADMIN_USER) {
	$shippingid 	= $_REQUEST['shippingid'];
	$zones 			= $_REQUEST['zone'];
	$based 			= $_REQUEST['based'];
	$basedold 		= $_REQUEST['basedold'];
	$content 		= "";
	
	$str_tid="SELECT tax_id from tx_multishop_shipping_methods where id='". $shippingid ."'";
	$qry_tid=$GLOBALS['TYPO3_DB']->sql_query($str_tid);
	$row_tid=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tid);
	
	$str="SELECT * from tx_multishop_zones order by name";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$zones=array();	
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$zones[]=$row;
	}	
	
	if ($based == 'flat' && $basedold == 'flat') {
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
			
			$str3="SELECT * from tx_multishop_shipping_methods_costs where shipping_method_id='". $shippingid ."' and zone_id='".$zone['id']."'";
			$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
			$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
			
			$zone_pid =  $shippingid .":". $zone['id'];
			
			$data 			= mslib_fe::getTaxRuleSet($row_tid['tax_id'], 0);
			$sc_tax_rate 	= $data['total_tax_rate'];
			
			$sc_tax 			= mslib_fe::taxDecimalCrop(($row3['price']*$sc_tax_rate)/100);
			$sc_price_display = mslib_fe::taxDecimalCrop($row3['price'], 2, false);
			$sc_price_display_incl = mslib_fe::taxDecimalCrop($row3['price'] + $sc_tax, 2, false);
			
			$content .='
				<table>
				<tr>
						<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'"><b> Level 1 :</b></div></td>
						<td width="100" align="right">
							<div>
								<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="'.htmlspecialchars($sc_price_display).'" rel="'.$row_tid['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
								<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="'.htmlspecialchars($sc_price_display_incl).'" rel="'.$row_tid['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
								<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'"  value="'. $row3['price'] .'"></div>
							</div>
						</td>
					</tr></table>
			';
			$content.='</fieldset>';
		}
		
	} else if ($based == "weight" &&  $basedold == "weight") {
		foreach ($zones as $zone) { // start for weight based
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
			
			$str3="SELECT * from tx_multishop_shipping_methods_costs where shipping_method_id='".$shippingid."' and zone_id='".$zone['id']."'";
			$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
			$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
			//while ($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3))
			//{
				//$shipping_cost[] =$row3;
			//}
			
			$content .= '<table border="0" cellpadding="0" cellspacing="0" height="100%">';
			$zone_pid =  $row['id'] . $zone['id'];
			
			$shipping_cost = explode(",",$row3['price']);
			if (count($shipping_cost) > 1){
				for ($i=1; $i<count($shipping_cost); $i++){
					$nextVal = $i + 1;
					$numKey = $i - 1;
					$zhone_price = explode(":",$shipping_cost[$numKey]);
					
					$sc_tax_rate 	= 0;
					$data 			= mslib_fe::getTaxRuleSet($row_tid['tax_id'], 0);
					$sc_tax_rate 	= $data['total_tax_rate'];
					
					$sc_tax 			= mslib_fe::taxDecimalCrop(($zhone_price[1]*$sc_tax_rate)/100);
					$sc_price_display = mslib_fe::taxDecimalCrop($zhone_price[1], 2, false);
					$sc_price_display_incl = mslib_fe::taxDecimalCrop($zhone_price[1] + $sc_tax, 2, false);
					
					$weight_next = $i > 1 ? "$weight_old" : '0';
					$zone_price_display = $zhone_price[0] == 101 ? $this->pi_getLL('end') : $zhone_price[0];
					$content .= '
					
					<tr>
						<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'"><b> Level '.$i.':</b></div></td>
						<td width="70" align="right"><div id="22631_'.$zone_pid .'_BeginWeightLevel'.$i.'">'. $weight_next  .'  KG</div></td>
						<td width="70" align="center"><div id="22631_'. $zone_pid .'_TotLevel'.$i.'"> to </div></td>
						<td>
						
						<select name="'. $row['id'] .":". $zone['id'] .'[]" id="22631_'. $zone_pid .'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('. $nextVal .', 22631, '. $zone_pid .'); ">
						<option  value= "'.$zhone_price[0] .'">'. $zone_price_display .'</selected>
						</select>
						
						</td>
						<td width="100" align="right">
							<div id="22631_'. $zone_pid .'_PriceLevel'.$i.'">
								<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="'.htmlspecialchars($sc_price_display).'" rel="'.$row_tid['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
								<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="'.htmlspecialchars($sc_price_display_incl).'" rel="'.$row_tid['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
								<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'_Price[]" id="22631_'. $zone_pid.'_Price'.$i.'" value="'.$zhone_price[1].'"></div>
							</div>
						</td>
					</tr>
					';
					$weight_old = $zhone_price[0];
				}
			} 
			
			if (count($shipping_cost) <13 ){
				for ($i= count($shipping_cost) ; $i<=10; $i++) {
					$nextVal = $i + 1;
					$content .= '
						<tr>
						<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'" style="display:none;"><b> Level '.$i.':</b></div></td>
						<td width="70" align="right"><div style="display:none;" id="22631_'.$zone_pid .'_BeginWeightLevel'.$i.'" >0  KG</div></td>
						<td width="70" align="center"><div style="display:none;" id="22631_'. $zone_pid .'_TotLevel'.$i.'" > to </div></td>
						<td><select name="'. $row['id'] .":". $zone['id'] .'[]" id="22631_'. $zone_pid .'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('. $nextVal .', 22631, '. $zone_pid .'); " style="display:none;"></select></td>
						<td width="100" align="right">
							<div id="22631_'. $zone_pid .'_PriceLevel'.$i.'" style="display:none;" >
								<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="" rel="'.$row_tid['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
								<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="" rel="'.$row_tid['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
								<div class="msAttributesField hidden"><input type="hidden" style="text-align:right; display=none;" size="3" name="'.$zone_pid.'_Price[]" id="22631_'. $zone_pid.'_Price'.$i.'" value="" /></div>
							</div>
						</td>
						</tr>';
				}
			}
			
			$content .= '</table>';
			
			$content .= "
			<script>
				var Zone = ".$zone_pid.";
			";
			for ($i=1; $i<count($shipping_cost); $i++){
				$content .= "CreateWeightList(PackageId+'_'+Zone+'_EndWeightLevel".$i."', 0, 1, PackageId, Zone);";
			}
			//echo ();
			//die();
			if (empty($row3['price'])){
				$content .= "CreateWeightList(PackageId+'_'+Zone+'_EndWeightLevel1', 0, 1, PackageId, Zone);";
			}
			$content .= "</script>";
			$content.='</fieldset>';
			//break;
			
		} //end for weight base
	} //end else 
	
	
	// new for weight
	if ($based == "weight" && ($basedold == "flat" || $basedold == "" || $basedold != "weight")) {
		$content = '<script type="text/javascript" src="'.$this->FULL_HTTP_URL_MS.'scripts/front_pages/includes/js/shipping/xajax.js"></script>';
		$content .= '<script type="text/javascript" src="'.$this->FULL_HTTP_URL_MS.'scripts/front_pages/includes/js/shipping/zone.js"></script>';
		foreach ($zones as $zone) { // start for weight based
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
			$content .= '<table border="0" cellpadding="0" cellspacing="0" height="100%">';
			$zone_pid =  $shippingid . $zone['id'];
			$PackageId = "22631";
			for ($i=1 ; $i<=10; $i++){
				$nextVal = $i + 1;
				$content .= '
					<tr id="row_'.$PackageId.'_'.$zone_pid .'_NivLevel'.$i.'">
					<td><div id="'.$PackageId.'_'.$zone_pid .'_NivLevel'.$i.'" ><b> Level '.$i.':</b></div></td>
					<td width="70" align="right"><div  id="'.$PackageId.'_'.$zone_pid .'_BeginWeightLevel'.$i.'" >0  KG</div></td>
					<td width="70" align="center"><div  id="'.$PackageId.'_'. $zone_pid .'_TotLevel'.$i.'" > to </div></td>
					<td><select name="'. $shippingid.":". $zone['id'] .'[]" id="'.$PackageId.'_'. $zone_pid .'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('. $nextVal .', '.$PackageId.', '. $zone_pid .'); " ></select></td>
					<td width="100" align="right">
						<div id="'.$PackageId.'_'. $zone_pid .'_PriceLevel'.$i.'">
							<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="" rel="'.$row_tid['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
							<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="" rel="'.$row_tid['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
							<div class="msAttributesField hidden"><input type="hidden" style="text-align:right;" size="3" name="'.$zone_pid.'_Price[]" id="'.$PackageId.'_'. $zone_pid.'_Price'.$i.'" value="" /></div>
						</div>
					</td>
					</tr>';
			}
			$content .= '</table>';
			$content .= "
			<script>
				//var Zone = ".$zone_pid.";
				//alert(Zone);
				//var PackageId =  ".$PackageId.";
				CreateWeightList('".$PackageId."'+'_'+'".$zone_pid."'+'_EndWeightLevel1', 0, 1, '".$PackageId."', '".$zone_pid."');
			</script>	
			";
			$content .='</fieldset>';
			//break;
		} //end for weight base
		
	} else if ($based == 'flat' && ($basedold == 'weight' || $basedold == '' || $basedold != "flat")) { // new for flat
		foreach ($zones as $zone) { // start for weight based
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
			
			$zone_pid =  $shippingid .":". $zone['id'];
			$content .='
				<table>
				<tr>
					<td><div id="22631_'.$zone_pid .'_NivLevel'.$i.'"><b> Level 1 :</b></div></td>
					<td width="100" align="right">
						<div>
							<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="" rel="'.$row_tid['tax_id'].'"><label for="display_name">Excl. VAT</label></div>
							<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="" rel="'.$row_tid['tax_id'].'"><label for="display_name">Incl. VAT</label></div>
							<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'"  value=""></div>
						</div>
					</td>
				</tr></table>';
			$content.='</fieldset>';
		}	
	}
	
	// hook to process custom shipping type ajax request
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_shipping_costs_ajax.php']['processCustomShippingTypeAjaxRequest'])) {
		$params = array (
			'zones' => &$zones,
			'content' => &$content
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/admin_shipping_costs_ajax.php']['processCustomShippingTypeAjaxRequest'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	// hook to process custom shipping type ajax request eof
	
	$content .='
		<script type="text/javascript">
			jQuery(document).ready(function($) {
	
				function productPrice(to_include_vat, o, type) {
			var current_value 	= parseFloat(o.val());
			var tax_id 			= o.attr("rel");
			
			if (current_value > 0) {
				if (to_include_vat) {
					jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: current_value, to_tax_include: true, tax_group_id: tax_id }, function(json) {
						if (json && json.price_including_tax) {
							var incl_tax_crop = decimalCrop(json.price_including_tax);
				
							o.parent().next().first().children().val(incl_tax_crop);
						} else {
							o.parent().next().first().children().val(current_value);
						}
					});
	
					// update the hidden excl vat
					o.parent().next().next().first().children().val(current_value);
	
				} else {
					jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: current_value, to_tax_include: false, tax_group_id: tax_id }, function(json) {
						if (json && json.price_excluding_tax) {
							var excl_tax_crop = decimalCrop(json.price_excluding_tax);
				
							// update the excl. vat
							o.parent().prev().first().children().val(excl_tax_crop);
				
							// update the hidden excl vat
							o.parent().next().first().children().val(json.price_excluding_tax);
				
						} else {
							// update the excl. vat
							o.parent().prev().first().children().val(current_value);
				
							// update the hidden excl vat
							o.parent().next().first().children().val(current_value);
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
				
		jQuery(".msProductsPriceExcludingVat").keyup(function() {
			productPrice(true, jQuery(this));
		});
	
		jQuery(".msProductsPriceIncludingVat").keyup(function() {
			productPrice(false, jQuery(this));
		});
			});
			</script>';
	echo $content;
	exit();
}
?>