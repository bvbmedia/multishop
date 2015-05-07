<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
	'tx_multishop_products_options', // FROM ...
	'language_id=\'0\'', // WHERE...
	'', // GROUP BY...
	'sort_order', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
$options_data=array();
$js_select2_cache_options=array();
if ($rows) {
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$options_data[]=$row;
		$js_select2_cache_options[$row['products_options_id']]='attributesOptions['.$row['products_options_id'].']={id:"'.$row['products_options_id'].'", text:"'.htmlentities($row['products_options_name'], ENT_QUOTES).'"}';
	}
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('merge_attribute_values').'</h1></div>
<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_attribute_options_values').'" method="post" class="merge_attribute_values_form">
	<div class="account-field">
			<ul>
			';
foreach ($options_data as $option_val) {
	$content.='<li>';
	$content.='<span class="option_name">'.$option_val['products_options_name'].'</span>';
	//
	$src_values_id=mslib_fe::getAttributeValuesByOptionId($option_val['products_options_id']);
	if (count($src_values_id)>0) {
		$content.='<ul class="attribute_values">';
		foreach ($src_values_id as $src_value_id=>$src_value_name) {
			$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('options_values_id', // SELECT ...
				'tx_multishop_products_attributes', // FROM ...
				'options_id=\''.$option_val['products_options_id'].'\' and options_values_id=\''.$src_value_id.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
			$item_count=$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk);
			if ($item_count<=1) {
				$item_count=' ('.$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk).' '.$this->pi_getLL('product').')';
			} else {
				$item_count=' ('.$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk).' '.$this->pi_getLL('products').')';
			}
			$content.='<li>';
			$content.='<div class="merge_attribute_values_wrapper">';
			$content.='<div class="merge_attribute_values_source">';
			$content.='<input name="tx_multishop_pi1[merge_attribute_values_src]['.$option_val['products_options_id'].'][]" id="merge_src_'.$option_val['products_options_id'].'_'.$src_value_id.'" type="checkbox" value="'.$src_value_id.'" class="merge_source" rel="'.$option_val['products_options_id'].'" />';
			$content.='<label for="merge_src_'.$option_val['products_options_id'].'_'.$src_value_id.'" class="merge_source_label"><span>'.$src_value_name.$item_count.'</span></label>';
			$content.='</div>';
			$content.='</div>';
			$content.='</li>';
		}
		$content.='</ul>';
		$content.='<div class="merge_attribute_values_target" id="merge_target_'.$option_val['products_options_id'].'_wrapper" style="display:none">';
		$content.='<label for="merge_target_'.$option_val['products_options_id'].'"><span>'.$this->pi_getLL('merge_to').'</span></label>';
		$content.='<input type="hidden" id="merge_option_id_'.$option_val['products_options_id'].'" value="'.$option_val['products_options_id'].'">';
		$content.='<input type="hidden" id="merge_target_'.$option_val['products_options_id'].'" name="tx_multishop_pi1[merge_attribute_values_target]['.$option_val['products_options_id'].']" class="merge_attribute_target_selectbox" style="width:400px">';
		$content.='</div>';
	}
	$content.='</li>'."\n";
}
$content.='
			</ul>
	</div>
	<div class="account-field">
			<label></label>
			<input type="submit" id="submit" class="msadmin_button" value="'.$this->pi_getLL('merge_selected').'" />
	</div>
</form>
<script type="text/javascript">
var select2_sb = function(selector_str, option_id, exclude_id) {
	jQuery(selector_str).select2({
		placeholder: \''.$this->pi_getLL('choose').'...\',
		createSearchChoice:function(term, data) {
			return {id:term, text:term};
		},
		minimumInputLength: 0,
		query: function(query) {
			$.ajax(\''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&tx_multishop_pi1[admin_ajax_product_attributes]=get_attributes_values').'\' + \'&option_id=\' + option_id + \'&exclude_id=\' + exclude_id, {
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
				$.ajax(\''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&tx_multishop_pi1[admin_ajax_product_attributes]=get_attributes_values').'\' + \'&option_id=\' + option_id + \'&exclude_id=\' + exclude_id, {
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
		dropdownCssClass: "new_product_attribute_options_dropdown",
		escapeMarkup: function (m) { return m; }
	})
}
jQuery(document).ready(function(){
	jQuery(document).on("click", ".merge_source, .merge_source_label", function(){
		if (jQuery(this).hasClass("merge_source")) {
			var target_element_cue=jQuery(this).attr("rel");
			var target_element="merge_target_" + target_element_cue;
			var cb_element_id="#" + jQuery(this).attr("id");
			var cb_value=jQuery(this).val();
			var option_element_id="#merge_option_id_" + target_element_cue;
		} else if (jQuery(this).attr("class")=="merge_source_label") {
			var target_element_cue=jQuery(this).parent().children("input").attr("rel");
			var target_element="merge_target_" + target_element_cue;
			var cb_element_id="#" + jQuery(this).parent().children("input").attr("id");
			var cb_value=jQuery(this).parent().children("input").val();
			var option_element_id="#merge_option_id_" + target_element_cue;
		}
		var option_id=jQuery(option_element_id).val();
		var target_input_element_id="#" + target_element;
		var target_div_element_id="#" + target_element + "_wrapper";
		if (jQuery(cb_element_id).prop("checked")) {
			jQuery(target_div_element_id).show();
			select2_sb(target_input_element_id, option_id, cb_value);
		} else {
			var parent_ul=jQuery(this).parent().parent().parent().parent();
			if (jQuery(parent_ul).children().find(\'input:checkbox:checked\').length==0) {
				jQuery(target_input_element_id).select2("destroy");
				jQuery(target_div_element_id).hide();
			}
		}
	});
});
</script>
';
if ($this->post) {
	$new_attribute_value_id=array();
	$clean_up_db=false;
	$clean_up_unused_value=array();
	foreach ($this->post['tx_multishop_pi1']['merge_attribute_values_src'] as $src_option_id => $src_values) {
		if ((is_array($src_values) && count($src_values)) && (is_array($this->post['tx_multishop_pi1']['merge_attribute_values_target']) && $this->post['tx_multishop_pi1']['merge_attribute_values_target'][$src_option_id])) {
			foreach ($src_values as $src_value_id) {
				$new_value=false;
				$target_value_id=$this->post['tx_multishop_pi1']['merge_attribute_values_target'][$src_option_id];
				// make sure the manual input is not option id
				$is_target_value_id_exist=mslib_befe::getRecord($target_value_id, 'tx_multishop_products_options_values', 'products_options_values_name', array('language_id=\'0\''));
				if (!is_array($is_target_value_id_exist) || (isset($new_attribute_value_id[$target_value_id_name]) && $new_attribute_value_id[$target_value_id_name]>0)) {
					$new_value=true;
				}
				if ($new_value) {
					$target_value_id_name=$target_value_id;
					if (isset($new_attribute_value_id[$target_value_id_name]) && $new_attribute_value_id[$target_value_id_name]>0) {
						$target_value_id=$new_attribute_value_id[$target_value_id_name];
					} else {
						$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_id', // SELECT ...
							'tx_multishop_products_options_values', // FROM ...
							"products_options_values_name = '".addslashes($target_value_id_name)."' and language_id = '0'", // WHERE...
							'', // GROUP BY...
							'', // ORDER BY...
							'' // LIMIT ...
						);
						$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
							$rs_chk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
							$target_value_id=$rs_chk['products_options_values_id'];
							$new_attribute_value_id[$target_value_id_name]=$target_value_id;
						} else {
							$insertArray=array();
							$insertArray['products_options_values_id']='';
							$insertArray['language_id']=0;
							$insertArray['products_options_values_name']=$target_value_id_name;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
							$GLOBALS['TYPO3_DB']->sql_query($query);
							$target_value_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
							$new_attribute_value_id[$target_value_id_name]=$target_value_id;
							// add for multilang slug
							foreach ($this->languages as $key=>$language) {
								if ($language['uid']>0) {
									$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_name', // SELECT ...
										'tx_multishop_products_options_values', // FROM ...
										"products_options_values_id = '".$target_value_id."' and language_id = '".$language['uid']."'", // WHERE...
										'', // GROUP BY...
										'', // ORDER BY...
										'' // LIMIT ...
									);
									$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
									if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
										// prep for update
										$option_value_name=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
										if (empty($option_value_name['products_options_values_name']) || !$option_value_name['products_options_values_name']) {
											$updateArray=array();
											$updateArray['products_options_values_name']=$target_value_id_name;
											$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', 'products_options_values_id=\''.$target_value_id.'\' and language_id=\''.$language['uid'].'\'', $updateArray);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										}
									} else {
										$insertArray=array();
										$insertArray['products_options_values_id']=$target_value_id;
										$insertArray['language_id']=$language['uid'];
										$insertArray['products_options_values_name']=$target_value_id_name;
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
										$GLOBALS['TYPO3_DB']->sql_query($query);
									}
								}
							}
						}
					}
				}
				if ($src_value_id>0 && $target_value_id>0 && $src_value_id!=$target_value_id) {
					//
					$clean_up_db=true;
					$clean_up_unused_value[]=$src_value_id;
					//
					$updateArray=array();
					$updateArray['products_options_values_id']=$target_value_id;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id=\''.$src_option_id.'\' and products_options_values_id=\''.$src_value_id.'\'', $updateArray);
					$GLOBALS['TYPO3_DB']->sql_query($query);
					//
					$updateArray=array();
					$updateArray['options_values_id']=$target_value_id;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', 'options_id=\''.$src_option_id.'\' and options_values_id=\''.$src_value_id.'\'', $updateArray);
					$GLOBALS['TYPO3_DB']->sql_query($query);
					/*if ($new_value) {
						// delete the source option
						//$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values', 'products_options_values_id='.$src_value_id);
						//$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						// update the deleted src option_id to target option id
						$updateArray=array();
						$updateArray['products_options_values_id']=$target_value_id;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id=\''.$src_option_id.'\' and products_options_values_id=\''.$src_value_id.'\'', $updateArray);
						$GLOBALS['TYPO3_DB']->sql_query($query);
						//
						$updateArray=array();
						$updateArray['options_values_id']=$target_value_id;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', 'options_id=\''.$src_option_id.'\' and options_values_id=\''.$src_value_id.'\'', $updateArray);
						$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id=\''.$src_option_id.'\' and products_options_values_id=\''.$src_value_id.'\'');
						$GLOBALS['TYPO3_DB']->sql_query($query);
						//
						$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_attributes', 'options_id=\''.$src_option_id.'\' and options_values_id=\''.$src_value_id.'\'');
						$GLOBALS['TYPO3_DB']->sql_query($query);
					}*/
				}
			}
		}
	}
	// cleaning up
	if ($clean_up_db) {
		// clean up unused values
		if (count($clean_up_unused_value)) {
			$unused_values=array_unique($clean_up_unused_value);
		}
		foreach ($unused_values as $unused_value_id) {
			$have_povp_record=false;
			$have_pa_record=false;
			//
			$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_id', // SELECT ...
				'tx_multishop_products_options_values_to_products_options', // FROM ...
				"products_options_values_id = '".$unused_value_id."'", // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
				$have_povp_record=true;
			}
			//
			$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('options_values_id', // SELECT ...
				'tx_multishop_products_attributes', // FROM ...
				"options_values_id = '".$unused_value_id."'", // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
				$have_pa_record=true;
			}
			if (!$have_povp_record && !$have_pa_record) {
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values', 'products_options_values_id='.$unused_value_id);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		// delete double record
		$delete_qry="DELETE povp1 FROM tx_multishop_products_options_values_to_products_options povp1, tx_multishop_products_options_values_to_products_options povp2 WHERE povp1.products_options_values_to_products_options_id > povp2.products_options_values_to_products_options_id AND povp1.products_options_id = povp2.products_options_id AND povp1.products_options_values_id = povp2.products_options_values_id";
		$GLOBALS['TYPO3_DB']->sql_query($delete_qry);
		//
		$delete_qry="DELETE pa1 FROM tx_multishop_products_attributes pa1, tx_multishop_products_attributes pa2 WHERE pa1.products_attributes_id > pa2.products_attributes_id AND pa1.products_id = pa2.products_id AND pa1.options_id = pa2.options_id AND pa1.options_values_id = pa2.options_values_id";
		$GLOBALS['TYPO3_DB']->sql_query($delete_qry);
	}
	//
	header('Location: ' . $this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_attribute_options_values'));
}
?>