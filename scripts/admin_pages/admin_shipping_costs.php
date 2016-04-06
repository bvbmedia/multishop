<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<script type="text/javascript" src="'.$this->FULL_HTTP_URL_MS.'scripts/front_pages/includes/js/shipping/weight_based_shipping_costs.js"></script>';
$content.='<div class="panel panel-default">';
$content.='<div class="panel-heading"><h3>'.$this->pi_getLL('shipping_costs').'</h3></div>';
if ($this->post) {
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['postedShippingCostData'])) {
		$params=array(
			'post'=>&$this->post
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['postedShippingCostData'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	//print_r($this->post);
	//die();
	foreach ($this->post['costs'] as $shipping_id=>$shipping_type) {
		if ($shipping_type=='weight') {
			$i=0;
			foreach ($this->post as $key=>$weight) {
				$shipping_zones=explode(":", $key);
				$key_price=$shipping_zones[0].$shipping_zones[1]."_Price";
				if (count($shipping_zones)==2 && $shipping_zones[0]==$shipping_id) {
					//delete DB  for clean old data
					$where_del='shipping_method_id = '.$shipping_zones[0].' AND zone_id = '.$shipping_zones[1];
					//select row for checking if any
					$checking_query=$GLOBALS['TYPO3_DB']->SELECTquery('count(*) as countrow', // SELECT ...
						'tx_multishop_shipping_methods_costs', // FROM ...
						$where_del, // WHERE.
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$res_checking=$GLOBALS['TYPO3_DB']->sql_query($checking_query);
					$row_checking=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
					$i=0;
					$weight_and_price="";
					foreach ($weight as $value) {
						// empty value will always read as 0
						if (isset($this->post[$key_price][$i]) && $this->post[$key_price][$i]!=='') {
							if (strstr($this->post[$key_price][$i], ",")) {
								$this->post[$key_price][$i]=str_replace(",", ".", $this->post[$key_price][$i]);
							}
							$weight_and_price[]=$this->post[$key][$i].":".$this->post[$key_price][$i];
						}
						$i++;
					}
					$free_shippingcosts_notation='';
					if (isset($this->post['freeshippingcostsabove'][$shipping_zones[0].$shipping_zones[1]]) && $this->post['freeshippingcostsabove'][$shipping_zones[0].$shipping_zones[1]]>0) {
						$free_shippingcosts_notation=$this->post['freeshippingcostsabove_value'][$shipping_zones[0].$shipping_zones[1]].':0';
					}
					//checking row
					if ($row_checking['countrow']==0) {
						$insertArray=array();
						$insertArray['shipping_method_id']=$shipping_zones[0];
						$insertArray['zone_id']=$shipping_zones[1];
						$insertArray['price']=implode(',', $weight_and_price);
						$insertArray['override_shippingcosts']=$free_shippingcosts_notation;
						if (!empty($insertArray['price'])) {
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_costs', $insertArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					} else {
						$insertArray=array();
						$insertArray['price']=implode(',', $weight_and_price);
						$insertArray['override_shippingcosts']=$free_shippingcosts_notation;
						if (!empty($insertArray['price'])) {
							$query_update=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods_costs', $where_del, $insertArray);
							$res_update=$GLOBALS['TYPO3_DB']->sql_query($query_update);
						}
					}
				}
			}
		} else {
			if ($shipping_type=='flat') {
				foreach ($this->post as $key=>$weight) {
					$shipping_zones=explode(":", $key);
					$shipping_zones_id=$shipping_zones[0];
					if (count($shipping_zones)==2 && $shipping_zones[0]==$shipping_id) {
						$where_del='shipping_method_id = '.$shipping_zones[0].' AND zone_id = '.$shipping_zones[1];
						$checking_query=$GLOBALS['TYPO3_DB']->SELECTquery('count(*) as countrow', // SELECT ...
							'tx_multishop_shipping_methods_costs', // FROM ...
							$where_del, // WHERE.
							'', // GROUP BY...
							'', // ORDER BY...
							'' // LIMIT ...
						);
						$res_checking=$GLOBALS['TYPO3_DB']->sql_query($checking_query);
						$row_checking=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
						$weight_and_price=$this->post[$key];
						if (strstr($weight_and_price, ",") && !strstr($weight_and_price, ":")) {
							$weight_and_price=str_replace(",", ".", $weight_and_price);
						}
						$free_shippingcosts_notation='';
						if (isset($this->post['freeshippingcostsabove'][$key]) && $this->post['freeshippingcostsabove'][$key]>0) {
							$free_shippingcosts_notation=$this->post['freeshippingcostsabove_value'][$key].':0';
						}
						//checking row
						if ($row_checking['countrow']==0) {
							$insertArray=array();
							$insertArray['shipping_method_id']=$shipping_zones[0];
							$insertArray['zone_id']=$shipping_zones[1];
							$insertArray['price']=$weight_and_price;
							$insertArray['override_shippingcosts']=$free_shippingcosts_notation;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_costs', $insertArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						} else {
							$insertArray=array();
							$insertArray['price']=$weight_and_price;
							$insertArray['override_shippingcosts']=$free_shippingcosts_notation;
							$query_update=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods_costs', $where_del, $insertArray);
							$res_update=$GLOBALS['TYPO3_DB']->sql_query($query_update);
						}
					} // end of check for flat value
				}
			}
		}
		//if cost type is empty / no shipping
		if ($shipping_type=='') {
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_shipping_methods_costs', 'shipping_method_id = '.$shipping_id);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
		//update for tx_multishop_shipping_methods
		$update_shipping=array();
		$update_shipping['shipping_costs_type']=$shipping_type;
		$query_update_shipping=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods', "id = ".$shipping_id, $update_shipping);
		$res_update_ship=$GLOBALS['TYPO3_DB']->sql_query($query_update_shipping);
	} // end for POST
} //end if post
$str="SELECT * from tx_multishop_zones order by name";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$zones=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$zones[]=$row;
}
$shipping_methods=mslib_fe::loadShippingMethods();
$shipping_cost_types=array();
$shipping_cost_types[]=array(
	'value'=>'flat',
	'title'=>$this->pi_getLL('flat_based')
);
$shipping_cost_types[]=array(
	'value'=>'weight',
	'title'=>$this->pi_getLL('weight_based')
);
// hook to add shipping cost type
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['addShippingCostType'])) {
	$params=array(
		'shipping_cost_types'=>&$shipping_cost_types,
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['addShippingCostType'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// hook to add shipping cost type eof
$tr_type='even';
if (count($shipping_methods)>0) {
	$content.='<div class="panel-body">';
	$content.='<form class="form-horizontal edit_form" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'], 1).'" method="post" enctype="multipart/form-data">';
	$content.='<div class="shipping_cost_input_field_wrapper">';
	$count_shipping_methods=array();
	$row_index=0;
	$counter_shipping_methods=count($shipping_methods);
	foreach ($shipping_methods as $row) {
		$content.='<div class="panel panel-default">';
		$content.='<div class="panel-heading panel-heading-toggle'.($row_index>0 ? ' collapsed' : '').'" data-toggle="collapse" data-target="#msAdminShippingCost'.$row['id'].'">';
		$content.='<h3 class="panel-title">';
		$content.='<a role="button" data-toggle="collapse" href="#msAdminShippingCost'.$row['id'].'">'.$this->pi_getLL('shipping_method').': '.$row['name'].'</a>';
		$content.='</h3>';
		$content.='</div>';
		$content.='<div id="msAdminShippingCost'.$row['id'].'" class="panel-collapse collapse'.($row_index===0 ? ' in' : '').'">';
		$content.='<div class="panel-body">';
		$content.='<div class="form-group">';
		$content.='<div class="col-md-12">';
		$content.='<select name="costs['.$row['id'].']" id="flat_weight'.$row['id'].'" class="form-control">';
		$content.='<option value="">'.$this->pi_getLL('no_shipping_costs').'</option>';
		foreach ($shipping_cost_types as $shipping_cost_type) {
			$content.='<option value="'.$shipping_cost_type['value'].'" '.(($row['shipping_costs_type']==$shipping_cost_type['value']) ? 'selected' : '').'>'.$shipping_cost_type['title'].'</option>';
		}
		$content.='</select>';
		$content.='<input type="hidden" id="based_old'.$row['id'].'" value="'.$row['shipping_costs_type'].'" />';
		$content.='</div>'; // .col-md-12
		$content.='</div>'; // .form-group
		$content.='<div id="has'.$row['id'].'">';
		//if empty
		if (empty($row['shipping_costs_type'])) {
			$count_shipping_methods[]=$row['id'];
		}
		if ($row['shipping_costs_type']=='flat') {
			// start for flat based
			$zone_index=0;
			$counter_zones=count($zones);
			foreach ($zones as $zone) {
				$content.='<div class="panel panel-default'.($zone_index==($counter_zones-1) ? ' no-mb' : '').'">';
				$content.='<div class="panel-heading"><h3>Zone: '.$zone['name'];
				$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$content.=' (';
				$tmpcontent='';
				while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
					$tmpcontent.=$row2['cn_iso_2'].',';
				}
				$tmpcontent=substr($tmpcontent, 0, strlen($tmpcontent)-1);
				$content.=$tmpcontent.')';
				$content.='</h3></div>';
				$str3="SELECT * from tx_multishop_shipping_methods_costs where shipping_method_id='".$row['id']."' and zone_id='".$zone['id']."'";
				$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
				$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
				$sc_tax_rate=0;
				$data=mslib_fe::getTaxRuleSet($row['tax_id'], 0);
				$sc_tax_rate=$data['total_tax_rate'];
				if (strpos($row3['price'], ':')!==false || strpos($row3['price'], ',')!==false) {
					$sc_tax=0;
					$sc_price_display=$row3['price'];
					$price_list_format=explode(',', $row3['price']);
					$price_list_incl_tax=array();
					foreach ($price_list_format as $price_format) {
						$price_excl=explode(':', $price_format);
						$pf_tax=mslib_fe::taxDecimalCrop(($price_excl[0]*$sc_tax_rate)/100);
						$price_incl=mslib_fe::taxDecimalCrop($price_excl[0]+$pf_tax, 2, false);
						$price_excl[0]=$price_incl;
						$price_list_incl_tax[]=implode(':', $price_excl);
					}
					$sc_price_display_incl=implode(',', $price_list_incl_tax);
				} else {
					$sc_tax=mslib_fe::taxDecimalCrop(($row3['price']*$sc_tax_rate)/100);
					$sc_price_display=mslib_fe::taxDecimalCrop($row3['price'], 2, false);
					$sc_price_display_incl=mslib_fe::taxDecimalCrop($row3['price']+$sc_tax, 2, false);
				}
				$freeshippingcosts_above=false;
				$free_shippingcosts=0;
				$fsc_price_display=0;
				$fsc_price_display_incl=0;
				if (!empty($row3['override_shippingcosts'])) {
					$freeshippingcosts_above=true;
					list($free_shippingcosts,)=explode(':', $row3['override_shippingcosts']);
					$fsc_tax=mslib_fe::taxDecimalCrop(($free_shippingcosts*$sc_tax_rate)/100);
					$fsc_price_display=mslib_fe::taxDecimalCrop($free_shippingcosts, 2, false);
					$fsc_price_display_incl=mslib_fe::taxDecimalCrop($free_shippingcosts+$fsc_tax, 2, false);
				}
				$zone_pid=$row['id'].":".$zone['id'];
				$content.='
					<div class="panel-body">
					<div class="form-group">
						<label id="'.$zone_pid.'_NivLevel'.$i.'" class="control-label col-md-4">Level 1 :</label>
						<div class="col-md-8">
							<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" id="display_name" name="display_name" class="form-control msProductsPriceExcludingVat" value="'.htmlspecialchars($sc_price_display).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('excluding_vat').'</span></div></div>
							<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" name="display_name" id="display_name" class="form-control msProductsPriceIncludingVat" value="'.htmlspecialchars($sc_price_display_incl).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('including_vat').'</span></div></div>
							<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'"  value="'.$row3['price'].'"></div>
						</div>
					</div>
					<hr>
					<div class="form-group">
						<div id="'.$zone_pid.'_NivLevel'.$i.'" class="control-label col-md-4"><div class="checkbox checkbox-success"><input type="checkbox" id="freeshippingcostsabove['.$zone_pid.']" name="freeshippingcostsabove['.$zone_pid.']" value="1"'.($freeshippingcosts_above ? ' checked="checked"' : '').' /><label for="freeshippingcostsabove['.$zone_pid.']">'.$this->pi_getLL('free_shippingcosts_for_order_amount_above').'</label></div></div>
						<div class="col-md-8">
							<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" id="display_name" name="display_name" class="form-control msProductsPriceExcludingVat" value="'.htmlspecialchars($fsc_price_display).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('excluding_vat').'</span></div></div>
							<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" name="display_name" id="display_name" class="form-control msProductsPriceIncludingVat" value="'.htmlspecialchars($fsc_price_display_incl).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('including_vat').'</span></div></div>
							<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="freeshippingcostsabove_value['.$zone_pid.']"  value="'.$free_shippingcosts.'"></div>
						</div>
					</div>';
				$content.='</div></div>';
				//
				$zone_index++;
			}
		} else {
			if ($row['shipping_costs_type']=='weight') {
				// start for weight based
				$zone_index=0;
				$counter_zones=count($zones);
				foreach ($zones as $zone) {
					$content.='<div class="panel panel-default'.($zone_index==($counter_zones-1) ? ' no-mb' : '').'">';
					$content.='<div class="panel-heading"><h3>Zone: '.$zone['name'];
					$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$content.=' (';
					$tmpcontent='';
					while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
						$tmpcontent.=$row2['cn_iso_2'].',';
					}
					$tmpcontent=substr($tmpcontent, 0, strlen($tmpcontent)-1);
					$content.=$tmpcontent.')';
					$content.='</h3></div>';
					$str3="SELECT * from tx_multishop_shipping_methods_costs where shipping_method_id='".$row['id']."' and zone_id='".$zone['id']."'";
					$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
					$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
					$content.='<div class="panel-body">';
					$zone_pid=$row['id'].$zone['id'];
					$shipping_cost=array();
					if (isset($row3['price']) && !empty($row3['price'])) {
						$row3['price']=trim($row3['price'], ',');
						$shipping_cost=explode(",", $row3['price']);
					}
					$count_sc=count($shipping_cost);
					if ($count_sc>0) {
						for ($i=1; $i<=$count_sc; $i++) {
							$nextVal=$i+1;
							$numKey=$i;
							$end_weight=101;
							$zone_price=explode(":", $shipping_cost[$numKey-1]);
							$sc_tax_rate=0;
							$data=mslib_fe::getTaxRuleSet($row['tax_id'], 0);
							$sc_tax_rate=$data['total_tax_rate'];
							$sc_tax=mslib_fe::taxDecimalCrop(($zone_price[1]*$sc_tax_rate)/100);
							$sc_price_display=mslib_fe::taxDecimalCrop($zone_price[1], 2, false);
							$sc_price_display_incl=mslib_fe::taxDecimalCrop($zone_price[1]+$sc_tax, 2, false);
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['weightConversion'])) {
								$params=array(
										'zone_price'=>&$zone_price,
										'end_weight'=>&$end_weight
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['weightConversion'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							$weight_next=$i>1 ? $weight_old : '0';
							$zone_price_display=$zone_price[0]==$end_weight ? 'End' : $zone_price[0];
							$content.='
						<div id="'.$zone_pid.'_Row_'.$i.'" class="form-group">
							<label id="'.$zone_pid.'_Label_'.$i.'" class="form-inline control-label col-md-4 firstLabel">Level '.$i.':
							<span id="'.$zone_pid.'_BeginWeightLevel'.$i.'">'.$weight_next.' '.$this->pi_getLL('admin_shipping_kg').'</span>
							<span id="'.$zone_pid.'_TotLevel'.$i.'"> '.$this->pi_getLL('up_to_and_including').' </span>
							';
							if ($i>1) {
								$content.='<select class="form-control" name="'.$row['id'].":".$zone['id'].'[]" id="'.$zone_pid.'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('.$nextVal.', '.$zone_pid.', this.value); ">
										'.mslib_befe::createSelectboxWeightsList($zone_price[0], $zone_price[0]).'
									</select>';
							} else {
								$content.='<select class="form-control" name="'.$row['id'].":".$zone['id'].'[]" id="'.$zone_pid.'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('.$nextVal.', '.$zone_pid.', this.value); ">
										'.mslib_befe::createSelectboxWeightsList($zone_price[0]).'
									</select>';
							}
							$content.='</label>
							<div class="col-md-8">
								<div id="'.$zone_pid.'_PriceLevel'.$i.'">
									<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" id="display_name" name="display_name" class="form-control msProductsPriceExcludingVat '.$zone_pid.'_priceInput'.$i.'" value="'.htmlspecialchars($sc_price_display).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('excluding_vat').'</span></div></div>
									<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" name="display_name" id="display_name" class="form-control msProductsPriceIncludingVat '.$zone_pid.'_priceInput'.$i.'" value="'.htmlspecialchars($sc_price_display_incl).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('including_vat').'</span></div></div>
									<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="'.$zone_pid.'_Price[]" id="'.$zone_pid.'_Price'.$i.'" value="'.$zone_price[1].'" class="'.$zone_pid.'_priceInput'.$i.'"></div>
								</div>
							</div>
						</div>';
							$weight_old=$zone_price[0];
						}
					}
					if ($count_sc<10) {
						if ($count_sc>0) {
							$sc_row=$count_sc+1;
						} else {
							$sc_row=1;
						}
						$row_counter=$sc_row;
						for ($i=$sc_row; $i<=10; $i++) {
							$nextVal=$i+1;
							if ($row_counter==1) {
								$content.='<div id="'.$zone_pid.'_Row_'.$i.'" class="form-group">';
							} else {
								$content.='<div id="'.$zone_pid.'_Row_'.$i.'" class="form-group" style="display:none">';
							}
							$content.='
								<label id="'.$zone_pid.'_Label_'.$i.'" class="form-inline control-label col-md-4 firstLabel">Level '.$i.':
								<span id="'.$zone_pid.'_BeginWeightLevel'.$i.'" >0 '.$this->pi_getLL('admin_shipping_kg').'</span>
								<span id="'.$zone_pid.'_TotLevel'.$i.'" > '.$this->pi_getLL('up_to_and_including').' </span>
								';
							$disabled='';
							if ($row_counter==1) {
								$content.='<select class="form-control" name="'.$row['id'].":".$zone['id'].'[]" id="'.$zone_pid.'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('.$nextVal.', '.$zone_pid.', this.value); ">
										'.mslib_befe::createSelectboxWeightsList().'
										</select>';
							} else {
								$disabled=' disabled="disabled"';
								$content.='<select class="form-control" name="'.$row['id'].":".$zone['id'].'[]" id="'.$zone_pid.'_EndWeightLevel'.$i.'" onchange="UpdateWeightPrice('.$nextVal.', '.$zone_pid.', this.value); "></select>';
							}
							$content.='</label>
								<div class="col-md-8">
									<div id="'.$zone_pid.'_PriceLevel'.$i.'">
										<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" id="display_name" name="display_name" class="form-control msProductsPriceExcludingVat '.$zone_pid.'_priceInput'.$i.'" value="" rel="'.$row['tax_id'].'"'.$disabled.'><span class="input-group-addon">'.$this->pi_getLL('excluding_vat').'</span></div></div>
										<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" name="display_name" id="display_name" class="form-control msProductsPriceIncludingVat '.$zone_pid.'_priceInput'.$i.'" value="" rel="'.$row['tax_id'].'"'.$disabled.'><span class="input-group-addon">'.$this->pi_getLL('including_vat').'</span></div></div>
										<div class="msAttributesField hidden"><input type="hidden" style="text-align:right;display:none;" size="3" name="'.$zone_pid.'_Price[]" id="'.$zone_pid.'_Price'.$i.'" value="" class="'.$zone_pid.'_priceInput'.$i.'"'.$disabled.'/></div>
									</div>
								</div>
							</div>';
							$row_counter++;
						}
					}
					$freeshippingcosts_above=false;
					$free_shippingcosts=0;
					$fsc_price_display=0;
					$fsc_price_display_incl=0;
					if (!empty($row3['override_shippingcosts'])) {
						$freeshippingcosts_above=true;
						list($free_shippingcosts,)=explode(':', $row3['override_shippingcosts']);
						$fsc_tax=mslib_fe::taxDecimalCrop(($free_shippingcosts*$sc_tax_rate)/100);
						$fsc_price_display=mslib_fe::taxDecimalCrop($free_shippingcosts, 2, false);
						$fsc_price_display_incl=mslib_fe::taxDecimalCrop($free_shippingcosts+$fsc_tax, 2, false);
					}
					$content.='<hr>
						<div class="form-group">
							<label id="'.$zone_pid.'_NivLevel'.$i.'" class="control-label col-md-4 secondLabel"><div class="checkbox checkbox-success"><input type="checkbox" name="freeshippingcostsabove['.$zone_pid.']" id="freeshippingcostsabove['.$zone_pid.']" value="1"'.($freeshippingcosts_above ? ' checked="checked"' : '').' /><label for="freeshippingcostsabove['.$zone_pid.']">'.$this->pi_getLL('free_shippingcosts_for_order_amount_above').'</label></div></label>
							<div class="col-md-8">
									<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" id="display_name" name="display_name" class="form-control msProductsPriceExcludingVat" value="'.htmlspecialchars($fsc_price_display).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('excluding_vat').'</span></div></div>
									<div class="msAttributesField"><div class="input-group"><span class="input-group-addon">'.mslib_fe::currency().'</span><input type="text" name="display_name" id="display_name" class="form-control msProductsPriceIncludingVat" value="'.htmlspecialchars($fsc_price_display_incl).'" rel="'.$row['tax_id'].'"><span class="input-group-addon">'.$this->pi_getLL('including_vat').'</span></div></div>
									<div class="msAttributesField hidden"><input type="hidden" style="text-align:right" size="3" name="freeshippingcostsabove_value['.$zone_pid.']"  value="'.$free_shippingcosts.'"></div>
							</div>
						</div>
					';
					$content.='</div>';
					$content.='<script type="text/javascript">';
					$content.="</script>";
					$content.='</div>';
					//break;
					$zone_index++;
				} //end for weight based
			} else {
				// hook to process custom visual shipping cost type
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['processZoneShippingCostType'])) {
					$params=array(
							'row'=>&$row,
							'zones'=>&$zones,
							'content'=>&$content
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_shipping_cost.php']['processZoneShippingCostType'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				// hook to process custom visual shipping cost type eof
			}
		}
		$content.='</div>'; // #has
		$content.='</div>'; // .panel-body
		$content.='</div>'; // .panel-collapse .collapse .in
		$content.='</div>'; // .panel .panel-default
		$count_shipping_methods[]=$row['id'];
		//break;
		$row_index++;
	}
	$content.='
	</div>
	<div class="clearfix">
		<div class="pull-right">
		<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
		</div>
	</div>
	</form>';
	if (isset($this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_AJAX']) && !empty($this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_AJAX'])) {
		$url_relative=$this->ms['MODULES']['SHIPPING_COST_WEIGHT_JS_AJAX'];
	} else {
		$url_relative='&tx_multishop_pi1[page_section]=admin_shipping_costs_ajax';
	}
	$content.='
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var url_relatives = "'.mslib_fe::typolink($this->shop_pid.',2002', $url_relative).'";
			jQuery("#load").hide();
			jQuery().ajaxStart(function() {
				jQuery("#load").show();
				jQuery("#has").hide();
			}).ajaxStop(function() {
				jQuery("#load").hide();
				jQuery("#has").show();

			});';
	foreach ($count_shipping_methods as $valId) {
		$content.='
				jQuery("#flat_weight'.$valId.'").change(function(){
				jQuery.ajax({
					type: "POST",
					url: url_relatives,
					data: {zone:"'.count($zones).'",based:jQuery(this).val(),shippingid:"'.$valId.'",basedold:jQuery("#based_old'.$valId.'").val()},
					success: function(data) {
						jQuery("#has'.$valId.'").html(data);
					}
				});
			});
		';
	}
	$content.='
	function productPrice(to_include_vat, o, type) {
		var original_val = jQuery(o).val();
		var current_value = parseFloat(jQuery(o).val());
		//
		if (original_val.indexOf(",")!=-1 && original_val.indexOf(".")!=-1) {
			var thousand=original_val.split(".");
			if (thousand[1].indexOf(",")!=-1) {
				var hundreds = thousand[1].split(",");
				original_val = thousand[0] + hundreds[0] + "." + hundreds[1];
				current_value = parseFloat(original_val);
				//
				$(o).val(original_val);
			} else {
				thousand=original_val.split(",");
				if (thousand[1].indexOf(".")!=-1) {
					var hundreds = thousand[1].split(".");
					original_val = thousand[0] + hundreds[0] + "." + hundreds[1];
					current_value = parseFloat(original_val);
					//
					$(o).val(original_val);
				}
			}
		}
		//
		var tax_id 			= jQuery(o).attr("rel");

		var have_comma = original_val.indexOf(",");
		var have_colon = original_val.indexOf(":");

		if (current_value > 0) {
			if (to_include_vat) {
				jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: true, tax_group_id: tax_id }, function(json) {
    				if (json && json.price_including_tax) {
						if ((have_comma > 0 || have_colon > 0) || (have_comma > 0 && have_colon > 0)) {
							//o.parent().next().first().children().val(json.price_including_tax);
							jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().children().find(\'input\').val(json.price_including_tax);
						} else {
							var incl_tax_crop = decimalCrop(json.price_including_tax);
							//o.parent().next().first().children().val(incl_tax_crop);
							jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().children().find(\'input\').val(incl_tax_crop);
						}

					} else {
						//o.parent().next().first().children().val(original_val);
						jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().children().find(\'input\').val(original_val);
					}
    			});

				// update the hidden excl vat
				jQuery(o).parentsUntil(\'msAttributesField\').next().next().first().children().val(original_val);

			} else {
				jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: false, tax_group_id: tax_id }, function(json) {
    				if (json && json.price_excluding_tax) {
						if ((have_comma > 0 || have_colon > 0) || (have_comma > 0 && have_colon > 0)) {
							//o.parent().prev().first().children().val(json.price_excluding_tax);
							jQuery(o).parentsUntil(\'.msAttributesField\').parent().prev().children().find(\'input\').val(json.price_excluding_tax);
						} else {
							var excl_tax_crop = decimalCrop(json.price_excluding_tax);
							// update the excl. vat
							//o.parent().prev().first().children().val(excl_tax_crop);
							jQuery(o).parentsUntil(\'.msAttributesField\').parent().prev().children().find(\'input\').val(excl_tax_crop);
						}

						// update the hidden excl vat
						//o.parent().next().first().children().val(json.price_excluding_tax);
						jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().first().children().val(json.price_excluding_tax);

					} else {
						// update the excl. vat
						jQuery(o).parentsUntil(\'.msAttributesField\').parent().prev().children().find(\'input\').val(original_val);
						// update the hidden excl vat
						jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().first().children().val(original_val);
					}
    			});
			}

		} else {
			if (to_include_vat) {
				// update the incl. vat
				jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().children().find(\'input\').val(0);
				// update the hidden excl vat
				jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().next().first().children().val(0);
			} else {
				// update the excl. vat
				jQuery(o).parentsUntil(\'.msAttributesField\').parent().prev().children().find(\'input\').val(0);
				// update the hidden excl vat
				jQuery(o).parentsUntil(\'.msAttributesField\').parent().next().next().first().children().val(0);
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
	$(document).on("keyup", ".msProductsPriceExcludingVat", function(e) {
		if (e.keyCode!=9) {
			productPrice(true, this);
		}
	});
	$(document).on("keyup", ".msProductsPriceIncludingVat", function(e) {
		if (e.keyCode!=9) {
			productPrice(false, this);
		}
	});
});
</script>';
} else {
	$content.=$this->pi_getLL('admin_label_currently_no_shipping_method_defined');
}
$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div></div>';
$content=''.mslib_fe::shadowBox($content).'';
?>