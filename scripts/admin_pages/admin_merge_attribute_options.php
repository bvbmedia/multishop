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
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('merge_attribute_options').'</h1></div>
<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_attribute_options').'" method="post" class="merge_attribute_options_form">
	<div class="account-field">
			<ul>
			';
foreach ($options_data as $option_val) {
	$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_id', // SELECT ...
		'tx_multishop_products_options_values_to_products_options', // FROM ...
		'products_options_id=\''.$option_val['products_options_id'].'\'', // WHERE...
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
	$item_count=$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk);
	if ($item_count<=1) {
		$item_count=' ('.$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk).' '.$this->pi_getLL('item').')';
	} else {
		$item_count=' ('.$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk).' '.$this->pi_getLL('items').')';
	}
	$content.='<li>';
	$content.='<div class="merge_attribute_options_wrapper">';
	$content.='<div class="merge_attribute_options_source">';
	$content.='<input name="tx_multishop_pi1[merge_attribute_options_src]['.$option_val['products_options_id'].']" id="merge_src_'.$option_val['products_options_id'].'" type="checkbox" value="'.$option_val['products_options_id'].'" class="merge_source" rel="merge_target_'.$option_val['products_options_id'].'" />';
	$content.='<label for="merge_src_'.$option_val['products_options_id'].'" class="merge_source_label"><span>'.$option_val['products_options_name'].$item_count.'</span></label>';
	$content.='</div>';
	$content.='<div class="merge_attribute_options_target" id="merge_target_'.$option_val['products_options_id'].'_wrapper" style="display:none">';
	$content.='<label for="merge_target_'.$option_val['products_options_id'].'"><span>'.$this->pi_getLL('merge_to').'</span></label>';
	$content.='<input type="hidden" id="merge_target_'.$option_val['products_options_id'].'" name="tx_multishop_pi1[merge_attribute_options_target]['.$option_val['products_options_id'].']" class="merge_attribute_target_selectbox" style="width:400px">';
	/*$content.='<select id="merge_target_'.$target_option_val['products_options_id'].'" name="tx_multishop_pi1[merge_attribute_options_target]['.$option_val['products_options_id'].']" class="merge_attribute_target_selectbox" style="width:400px">';
	$content.='<option value="0">'.$this->pi_getLL('choose').'...</option>';
	foreach ($target_merge_sb as $target_option_val) {
		if ($target_option_val['products_options_id']!=$option_val['products_options_id']) {
			$content.='<option value="'.$target_option_val['products_options_id'].'">'.$target_option_val['products_options_name'].'</option>';
		}
	}
	$content.='</select>';*/
	$content.='</div>';
	$content.='</div>';
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
var select2_sb = function(selector_str, exclude_id) {
	jQuery(selector_str).select2({
		placeholder: \''.$this->pi_getLL('choose').'...\',
		createSearchChoice:function(term, data) {
			return {id:term, text:term};
		},
		minimumInputLength: 0,
		query: function(query) {
			$.ajax(\''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&tx_multishop_pi1[admin_ajax_product_attributes]=get_attributes_options').'\' + \'&exclude_id=\' + exclude_id, {
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
				$.ajax(\''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&tx_multishop_pi1[admin_ajax_product_attributes]=get_attributes_options').'\' + \'&exclude_id=\' + exclude_id, {
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
			var target_element=jQuery(this).attr("rel");
			var cb_element_id="#" + jQuery(this).attr("id");
			var cb_value=jQuery(this).val();
		} else if (jQuery(this).attr("class")=="merge_source_label") {
			var target_element=jQuery(this).parent().children("input").attr("rel");
			var cb_element_id="#" + jQuery(this).parent().children("input").attr("id");
			var cb_value=jQuery(this).parent().children("input").val();
		}
		var target_input_element_id="#" + target_element;
		var target_div_element_id="#" + target_element + "_wrapper";
		if (jQuery(cb_element_id).prop("checked")) {
			jQuery(target_div_element_id).show();
			select2_sb(target_input_element_id, cb_value);
		} else {
			jQuery(target_input_element_id).select2("destroy");
			jQuery(target_div_element_id).hide();
		};
	});
});
</script>
';
if ($this->post && (is_array($this->post['tx_multishop_pi1']['merge_attribute_options_src']) and count($this->post['tx_multishop_pi1']['merge_attribute_options_src'])) &&
   (is_array($this->post['tx_multishop_pi1']['merge_attribute_options_target']) and count($this->post['tx_multishop_pi1']['merge_attribute_options_target']))) {
	$new_attribute_option_id=array();
	foreach ($this->post['tx_multishop_pi1']['merge_attribute_options_src'] as $src_option_id => $item) {
		$new_option=false;
		$target_option_id=$this->post['tx_multishop_pi1']['merge_attribute_options_target'][$src_option_id];
		// make sure the manual input is not option id
		$is_target_option_id_exist=mslib_befe::getRecord($target_option_id, 'tx_multishop_products_options', 'products_options_id', array('language_id=\'0\''));
		if (!is_array($is_target_option_id_exist) || (isset($new_attribute_option_id[$target_option_id]) && $new_attribute_option_id[$pa_option_name]>0)) {
			$new_option=true;
		}
		if ($new_option) {
			$pa_option_name=$target_option_id;
			if (isset($new_attribute_option_id[$pa_option_name]) && $new_attribute_option_id[$pa_option_name]>0) {
				$target_option_id=$new_attribute_option_id[$pa_option_name];
			} else {
				$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_id', // SELECT ...
					'tx_multishop_products_options', // FROM ...
					'', // WHERE...
					'', // GROUP BY...
					'products_options_id desc', // ORDER BY...
					'1' // LIMIT ...
				);
				$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
				$rs_chk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
				$max_optid=$rs_chk['products_options_id']+1;
				// use microtime as the default sorting
				$tmp_mtime=explode(" ", microtime());
				$mtime=array_sum($tmp_mtime);
				// prep for insertion
				$insertArray=array();
				$insertArray['products_options_id']=$max_optid;
				$insertArray['language_id']='0';
				$insertArray['products_options_name']=$target_option_id;
				$insertArray['listtype']='pulldownmenu';
				$insertArray['attributes_values']='0';
				$insertArray['sort_order']=$mtime;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$target_option_id=$max_optid;
				$new_attribute_option_id[$pa_option_name]=$target_option_id;
				// check for multilanguages
				foreach ($this->languages as $key=>$language) {
					if ($language['uid']>0) {
						$sql_chk=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_name', // SELECT ...
							'tx_multishop_products_options', // FROM ...
							"products_options_id = '".$target_option_id."' and language_id = '".$language['uid']."'", // WHERE...
							'', // GROUP BY...
							'', // ORDER BY...
							'' // LIMIT ...
						);
						$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)>0) {
							// prep for update
							$option_name=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
							if (empty($option_name['products_options_name']) || !$option_name['products_options_name']) {
								$updateArray=array();
								$updateArray['products_options_name']=$pa_option_name;
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$target_option_id.'\' and language_id=\''.$language['uid'].'\'', $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
						} else {
							$tmp_mtime=explode(" ", microtime());
							$mtime=array_sum($tmp_mtime);
							// prep for insertion
							$insertArray=array();
							$insertArray['products_options_id']=$target_option_id;
							$insertArray['language_id']=$language['uid'];
							$insertArray['products_options_name']=$pa_option_name;
							$insertArray['listtype']='pulldownmenu';
							$insertArray['attributes_values']='0';
							$insertArray['sort_order']=$mtime;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
				}
			}
		}
		if ($src_option_id>0 && $target_option_id>0) {
			// check if the values of source have counterpart or not in target (for existing target)
			if (!$new_option) {
				$src_values_id=mslib_fe::getAttributeValuesByOptionId($src_option_id);
				$target_values_id=array();
				foreach ($src_values_id as $src_value_id=>$src_value_name) {
					$target_value_id=mslib_fe::getAttributeValueIdByValueName($src_value_name, $target_option_id);
					if ($target_value_id) {
						// delete the source value
						if ($src_value_id!=$target_value_id) {
 							/*$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values', 'products_options_values_id='.$src_value_id);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);*/
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
						}
					}
				}
			}
			// delete the source option
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options', 'products_options_id='.$src_option_id);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// update the deleted src option_id to target option id
			$updateArray=array();
			$updateArray['products_options_id']=$target_option_id;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id=\''.$src_option_id.'\'', $updateArray);
			$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			$updateArray=array();
			$updateArray['options_id']=$target_option_id;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_attributes', 'options_id=\''.$src_option_id.'\'', $updateArray);
			$GLOBALS['TYPO3_DB']->sql_query($query);
			// cleanup double record in tx_multishop_products_options_values_to_products_options
			$query='DELETE pov2po1 FROM tx_multishop_products_options_values_to_products_options pov2po1, tx_multishop_products_options_values_to_products_options pov2po2 WHERE pov2po1.products_options_values_to_products_options_id > pov2po2.products_options_values_to_products_options_id AND pov2po1.products_options_id = pov2po2.products_options_id AND pov2po1.products_options_values_id = pov2po2.products_options_values_id';
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// cleanup double record in tx_multishop_products_attributes
			$query='DELETE pa1 FROM tx_multishop_products_attributes pa1, tx_multishop_products_attributes pa2 WHERE pa1.products_attributes_id > pa2.products_attributes_id AND pa1.products_id=pa2.products_id AND pa1.options_id = pa2.options_id AND pa1.options_values_id = pa2.options_values_id';
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	//
	header('Location: ' . $this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_attribute_options'));
}
?>