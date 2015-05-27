<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/valums-file-uploader/client/fileuploader.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/css/style.css">
<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.css" />
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/redactor/redactor.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/table.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/fontcolor.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/fontsize.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/filemanager.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/imagemanager.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/video.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/textexpander.js"></script>
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/redactor/plugins/fullscreen.js"></script>
';
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_attributes').'</h1></div>';
$selects=array();
$selects['select']=$this->pi_getLL('admin_label_option_type_selectbox');
$selects['select_multiple']=$this->pi_getLL('admin_label_option_type_selectbox_multiple');
$selects['radio']=$this->pi_getLL('admin_label_option_type_radio');
$selects['checkbox']=$this->pi_getLL('admin_label_option_type_checkbox');
$selects['input']=$this->pi_getLL('admin_label_option_type_text_input');
$selects['textarea']=$this->pi_getLL('admin_label_option_type_textarea');
$selects['hidden_field']=$this->pi_getLL('admin_label_option_type_hidden_field');
$selects['file']=$this->pi_getLL('admin_label_option_type_file_input');
$selects['divider']=$this->pi_getLL('admin_label_option_type_divider');

// new options
$content.='<form method="post" class="msadminFromFancybox" name="admin_product_new_attribute_options">';
$content.='<ul>';
$content.='<li>';
$content.='<h2>';
$content.='<span class="option_id">'.$this->pi_getLL('admin_label_add_new_attribute_options').'</span>';
$content.='</h2>';
$options_group='';
if ($this->ms['MODULES']['ENABLE_ATTRIBUTES_OPTIONS_GROUP']) {
	$options_group=mslib_fe::buildAttributesOptionsGroupSelectBox($row['products_options_id'], 'id="new_options_groups" class="add_new_attributes_options"');
	if (!empty($options_group)) {
		$options_group='<span class="options_groups">'.$this->pi_getLL('admin_label_options_group').': '.$options_group.'</span>';
	} else {
		$options_group='<span class="options_groups">'.$this->pi_getLL('admin_label_options_group').': '.$this->pi_getLL('admin_label_no_groups_defined').'</span>';
	}
}
// settings related to options
$content.='<div class="option_settings">';
$content.='<span class="options_name"><label for="new_option_name">'.$this->pi_getLL('admin_label_new_option_name').':</label> <input type="text" id="new_option_name" name="new_option_name" class="add_new_attributes_options"> </span>';
$content.=$options_group;
$content.='<span class="listing_type">';
$content.=$this->pi_getLL('admin_label_listing_type').': <select name="listtype" id="new_listtype" class="add_new_attributes_options">';
foreach ($selects as $key=>$value) {
	$content.='<option value="'.$key.'"'.($key==$row['listtype'] ? ' selected' : '').'>'.htmlspecialchars($value).'</option>';
}
$content.='</select>';
$content.='</span>';
$content.='<span class="required">
		<input name="required" type="checkbox" value="1" class="add_new_attributes_options"/> '.$this->pi_getLL('required').'
	</span>';
$content.='<span class="hide_in_cart">
		<input name="hide_in_cart" type="checkbox" value="1" class="add_new_attributes_options"/> '.$this->pi_getLL('admin_label_dont_include_attribute_values_in_cart').'
	</span>';
$content.='</div>';
$content.='<div class="option_values">';
$content.='<a href="#" class="msBackendButton continueState arrowRight arrowPosLeft" id="save_new_attribute_options"><span>'.$this->pi_getLL('admin_label_add_new_attribute_options').'</span></a>';
$content.='</div>';
$content.='</li>';
$content.='</ul>';
$content.='</form>';

if ($this->post) {
	if (is_array($this->post['listtype']) and count($this->post['listtype'])) {
		foreach ($this->post['listtype'] as $products_options_id=>$settings_value) {
			$updateArray=array();
			$updateArray['language_id']=$language_id;
			$updateArray['products_options_id']=$products_options_id;
			$updateArray['listtype']=$settings_value;
			$updateArray['required']=$this->post['required'][$products_options_id];
			$updateArray['hide_in_cart']=$this->post['hide_in_cart'][$products_options_id];
			$str=$GLOBALS['TYPO3_DB']->SELECTquery('1', // SELECT ...
				'tx_multishop_products_options', // FROM ...
				'products_options_id=\''.$products_options_id.'\' and language_id=\''.$language_id.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$products_options_id.'\' and language_id=\''.$language_id.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$updateArray['products_options_name']='';
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			if ($this->ms['MODULES']['ENABLE_ATTRIBUTES_OPTIONS_GROUP']) {
				if (isset($this->post['options_groups'][$products_options_id]) && !empty($this->post['options_groups'][$products_options_id])) {
					$updateArray=array();
					$updateArray['attributes_options_groups_id']=$this->post['options_groups'][$products_options_id];
					$updateArray['products_options_id']=$products_options_id;
					$str=$GLOBALS['TYPO3_DB']->SELECTquery('1', // SELECT ...
						'tx_multishop_attributes_options_groups_to_products_options', // FROM ...
						'products_options_id=\''.$products_options_id.'\' and attributes_options_groups_id=\''.$this->post['options_groups'][$products_options_id].'\'', // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_attributes_options_groups_to_products_options', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_attributes_options_groups_to_products_options', 'products_options_id=\''.$products_options_id.'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
		// redirect to current page after done saving an option settings
		header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_product_attributes'));
	}
}
// select2 cache
$js_select2_cache='';
$js_select2_cache_values=array();
$js_select2_cache='<script type="text/javascript">
	var attributesSearchValues=[];
	var attributesValues=[];'."\n";
// load the interface
mslib_befe::loadLanguages();

// load options
$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
	'tx_multishop_products_options', // FROM ...
	'language_id=\'0\'', // WHERE...
	'', // GROUP BY...
	'sort_order', // ORDER BY...
	'' // LIMIT ...
);
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
if ($rows) {
	$content.='<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_product_attributes').'" method="post" class="msadminFromFancybox" name="admin_product_attributes">';
//	$content.='<span class="msBackendButton float_right continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" /></span>';
	//$content.='<form role="form" class="msadminFromFancybox" name="admin_product_attributes">';
	$content.='<ul class="attribute_options_sortable" id="attribute_listings">';
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$content.='<li id="options_'.$row['products_options_id'].'">';
		$content.='<h2>';
		$content.='<span class="option_id">'.$this->pi_getLL('admin_label_option_name').': '.$row['products_options_name'].' (ID: '.$row['products_options_id'].')</span>';
		$content.='<span class="option_edit">';
		$content.='<a href="#" class="edit_options msadmin_button" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('edit').'</a>&nbsp;';
		$content.='<a href="#" class="delete_options msadmin_button" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('delete').'</a>&nbsp;';
		$content.='</span>';
		$content.='</h2>';
		$options_group='';
		if ($this->ms['MODULES']['ENABLE_ATTRIBUTES_OPTIONS_GROUP']) {
			$options_group=mslib_fe::buildAttributesOptionsGroupSelectBox($row['products_options_id']);
			if (!empty($options_group)) {
				$options_group='<span class="options_groups">'.$this->pi_getLL('admin_label_options_group').': '.$options_group.'</span>';
			} else {
				$options_group='<span class="options_groups">'.$this->pi_getLL('admin_label_options_group').': '.$this->pi_getLL('admin_label_no_groups_defined').'</span>';
			}
		}
		// settings related to options
		$content.='<div class="option_settings">';
		$content.=$options_group;
		$content.='<span class="listing_type">';
		$content.=$this->pi_getLL('admin_label_listing_type').': <select name="listtype['.$row['products_options_id'].']">';
		foreach ($selects as $key=>$value) {
			$content.='<option value="'.$key.'"'.($key==$row['listtype'] ? ' selected' : '').'>'.htmlspecialchars($value).'</option>';
		}
		$content.='</select>';
		$content.='</span>';
		$content.='<span class="required">
			<input name="required['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['required'] ? ' checked' : '').'/> '.$this->pi_getLL('required').'
		</span>';
		$content.='<span class="hide_in_cart">
			<input name="hide_in_cart['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['hide_in_cart'] ? ' checked' : '').'/> '.$this->pi_getLL('admin_label_dont_include_attribute_values_in_cart').'
		</span>';
		$content.='</div>';
		$content.='<div class="option_values">';
		$content.='<a href="#" class="msadmin_button add_attributes_values" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('admin_add_new_value').'</a>&nbsp;<a href="#" class="msadmin_button fetch_attributes_values" id="button_label_'.$row['products_options_id'].'" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('show_attributes_values', 'SHOW VALUES').'</a>&nbsp;';
		//$content.='<a href="#" class="msadmin_button fetch_options_description" id="button_label_desc_'.$row['products_options_id'].'" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('show_options_description', 'EDIT DESCRIPTION').'</a>';
		$content.='<ul class="attribute_option_values_sortable" rel="'.$row['products_options_id'].'" id="vc_'.$row['products_options_id'].'" style="display:none">';
		$content.='<li id="last_line_'.$row['products_options_id'].'"><a href="#" class="msadmin_button add_attributes_values" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('admin_add_new_value').'</a>&nbsp;<a href="#" class="msadmin_button hide_attributes_values" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('admin_label_hide_values').'</a></li>';
		$content.='</ul>';
		$content.='<input type="hidden" name="values_fetched_'.$row['products_options_id'].'" id="values_fetched_'.$row['products_options_id'].'" value="0" />';
		$content.='</div>';
		$content.='</li>';
	}
	$content.='</ul>';
	$content.='<span class="float_right msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" /></span>';
	$content.='</form>';




	$count_js_cache_values=count($js_select2_cache_values);
	if ($count_js_cache_values) {
		$js_select2_cache.=implode(";\n", $js_select2_cache_values).";\n";
	}
	$js_select2_cache.='</script>';
	if (!empty($js_select2_cache)) {
		$GLOBALS['TSFE']->additionalHeaderData['js_select2_cache']=$js_select2_cache;
	}
	$content.='<div id="dialog-confirm" title="'.$this->pi_getLL('admin_label_warning_this_action_is_not_reversible').'">
	  		<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_are_you_sure_want_to_delete_x_attributes'), '<span id="attributes-name0"></span>').'</p>
		</div>

		<div id="dialog-confirm-force" title="'.$this->pi_getLL('admin_label_warning_this_action_is_not_reversible').'">
	  		<p>
				<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_there_are_x_products_using_x_attributes_are_you_sure_want_to_delete_it'), '<span id="used-product-number"></span>', '<span id="attributes-name1"></span>').'
			</p>
			<br/><br/>
			<p style="text-align:left">
				'.$this->pi_getLL('admin_label_the_products_using_this_attributes_are').':
				<br/>
				('.$this->pi_getLL('admin_label_link_will_open_in_new_tab_window').')
			</p>
			<br/>
			<span id="products-used-attributes-list" style="text-align:left"></span>
		</div>';
	// now load the sortables jQuery code
	$content.='<script type="text/javascript">
	function attributesEditDialog (textTitle, textBody, mode) {
		var dialog = $(\'<div/>\', {
			id: \'attributesEditDialog\',
			title: textTitle
		});
		dialog.append(textBody);
		dialog.dialog({
			'.($this->ms['MODULES']['USE_RTE_IN_ADMIN_ATTRIBUTE_DESCRIPTION_EDITOR'] ? '
			width: 800,
			height:400,
			' : '
			width: 450,
			height:500,
			').'
			modal: true,
			body: "",
			resizable: true,
			open: function () {
				'.(!$this->ms['MODULES']['USE_RTE_IN_ADMIN_ATTRIBUTE_DESCRIPTION_EDITOR'] ? '
				$("#attributesEditDialog").keypress(function (e) {
					//console.log(e);
					if (e.keyCode == 13) {
						$(this).siblings(\'.ui-dialog-buttonpane\').find(\'.continueState\').click();
					}
				});
				' : '').'
				// right button (OK button) must be the default button when user presses enter key
				$(this).siblings(\'.ui-dialog-buttonpane\').find(\'.continueState\').focus();
			},
			buttons: {
				"cancel": {
					text: "'.$this->pi_getLL('cancel').'",
					class: \'msCancelButton msBackendButton prevState arrowLeft arrowPosLeft\',
					click: function () {
						$(this).dialog("close");
						$(this).hide();
					}
				},
				"save": {
					text: "'.$this->pi_getLL('save').'",
					class: \'msOkButton msBackendButton continueState arrowRight arrowPosLeft\',
					click: function () {
						if (mode=="edit_options") {
							var options_value=$(".edit_option_inputs").serialize();
							saveOptionsData(options_value, "options");
						} else if (mode=="edit_options_values") {
							var options_values=$(".edit_option_values_inputs").serialize();
							saveOptionsData(options_values, "options_values");
						}
						$(this).dialog("close");
						$(this).hide();
						$(this).remove();
					}
				}
			}
		});
	}
	function getOptionData(optid) {
		href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=get_option_data').'";
		$.ajax({
			type:"POST",
			url:href,
			data:"option_id=" + optid,
			dataType:"json",
			success: function(s) {
				var dialog_title="'.addslashes($this->pi_getLL('admin_label_edit_option')).': " + s.options_title;
				var dialog_body=\'<div class="edit_dialog_input_wrapper">\';
				dialog_body+=\'<input type="hidden" value="\' + optid + \'" name="option_id">\';
				$.each(s.options, function(i, v){
					dialog_body+=\'<div class="edit_dialog_input_options">\';
					dialog_body+=\'<label>\' + v.lang_title + \' : </label>\';
					dialog_body+=\'<div class="edit_dialog_input">\';
					dialog_body+=\'<input type="text" class="edit_option_inputs" name="option_names[\' + optid + \'][\' + i + \']" value="\' + v.options_name + \'"/>\';
					dialog_body+=\'<span class="option_description_label">'.addslashes($this->pi_getLL('description')).'</span>\';
					dialog_body+=\'<textarea class="redactor_options edit_option_inputs" id="option_desc_\' + optid + \'_\' + i + \'" name="option_desc[\' + optid + \'][\' + i + \']">\' + v.options_desc + \'</textarea>\';
					dialog_body+=\'</div>\';
					dialog_body+=\'</div>\';
				});
				dialog_body+=\'</div>\';
				attributesEditDialog(dialog_title, dialog_body, "edit_options");
				'.($this->ms['MODULES']['USE_RTE_IN_ADMIN_ATTRIBUTE_DESCRIPTION_EDITOR'] ? '
				jQuery(\'.redactor_options\').redactor({
					focus: false,
					minHeight:\'100\',
					plugins: [\'table\',\'fontcolor\',\'fontsize\',\'filemanager\',\'imagemanager\',\'video\',\'textexpander\',\'fullscreen\']
				});
				' : '').'
			}
		});
	}
	function getOptionValuesData(relation_id) {
		href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=get_option_values_data').'";
		$.ajax({
			type:"POST",
			url:href,
			data:"relation_id=" + relation_id,
			dataType:"json",
			success: function(s) {
				var dialog_title="'.addslashes($this->pi_getLL('admin_label_edit_option')).': " + s.options_name + " - '.addslashes($this->pi_getLL('admin_value')).': " + s.options_values_name;
				var dialog_body=\'<div class="edit_dialog_input_wrapper">\';
				dialog_body+=\'<input type="hidden" class="edit_option_values_inputs" value="\' + relation_id + \'" name="data_id">\';
				$.each(s.results, function(i, v) {
					dialog_body+=\'<div class="edit_dialog_input_options">\';
					dialog_body+=\'<label>\' + v.lang_title + \' : </label>\';
					dialog_body+=\'<div class="edit_dialog_input">\';
					dialog_body+=\'<input type="text" class="edit_option_values_inputs" name="option_values[\' + s.options_values_id + \'][\' + i + \']" value="\' + v.lang_values + \'"/>\';
					dialog_body+=\'<span class="option_description_label">'.addslashes($this->pi_getLL('description')).'</span>\';
					dialog_body+=\'<textarea class="redactor_values edit_option_values_inputs" name="ov_desc[\' + v.lang_description_pov2po_id + \'][\' + i + \']">\' + v.lang_description + \'</textarea>\';
					dialog_body+=\'</div>\';
					dialog_body+=\'</div>\';
				});
				dialog_body+=\'</div>\';
				attributesEditDialog(dialog_title, dialog_body, "edit_options_values");
				'.($this->ms['MODULES']['USE_RTE_IN_ADMIN_ATTRIBUTE_DESCRIPTION_EDITOR'] ? '
				jQuery(\'.redactor_values\').redactor({
					focus: false,
					minHeight:\'100\',
					plugins: [\'table\',\'fontcolor\',\'fontsize\',\'filemanager\',\'imagemanager\',\'video\',\'textexpander\',\'fullscreen\']
				});
				' : '').'
			}
		});
	}
	function saveOptionsData(serial_value, mode) {
		if (mode=="options") {
			href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_options_data').'";
		} else if (mode=="options_values") {
			href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_options_values_data').'";
		}
		$.ajax({
			type:"POST",
			url:href,
			data:serial_value,
			dataType:"json",
			success: function(s) {}
		});
	}
	var select2_options_value = function(selector_str, placeholder, dropdowncss, ajax_url) {
		$(selector_str).select2({
			placeholder: placeholder,
			createSearchChoice:function(term, data) {
				if (attributesValues[term] === undefined) {
					attributesValues[term]={id: term, text: term};
				}
				return {id:term, text:term};
			},
			minimumInputLength: 0,
			query: function(query) {
				if (attributesSearchValues[query.term] !== undefined) {
					query.callback({results: attributesSearchValues[query.term]});
				} else {
					$.ajax(ajax_url, {
						data: {
							q: query.term
						},
						dataType: "json"
					}).done(function(data) {
						attributesSearchValues[query.term]=data;
						query.callback({results: data});
					});
				}
			},
			initSelection: function(element, callback) {
				var id=$(element).val();
				if (id!=="") {
					if (attributesValues[id] !== undefined) {
						callback(attributesValues[id]);
					} else {
						$.ajax(ajax_url, {
							data: {
								preselected_id: id
							},
							dataType: "json"
						}).done(function(data) {
							attributesValues[data.id]={id: data.id, text: data.text};
							callback(data);
						});
					}
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
			dropdownCssClass: dropdowncss,
			escapeMarkup: function (m) { return m; }
		}).on("select2-selecting", function(e) {
			if (e.object.id == e.object.text) {
				$(this).next().val("1");
			} else {
				$(this).next().val("0");
			}
		});
	}
	jQuery(document).ready(function($) {
		$("#dialog-confirm").hide();
		$("#dialog-confirm-force").hide();
		$(document).on("click", "a", function(e) {
			if ($(this).attr("href")=="#") {
				e.preventDefault();
			}
		});
		// save new option
		$(document).on("click", "#save_new_attribute_options", function() {
			if ($("#new_option_name").val()!="") {
				var serial_value=$(".add_new_attributes_options").serialize();

				// save new option
				href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=add_new_options').'";
				$.ajax({
					type:"POST",
					url:href,
					data:serial_value,
					dataType:"json",
					success: function(s) {
						var ul_option_listings=$("#attribute_listings");
						var new_option_html=\'\';
						console.log(s);
						if (s.status=="OK") {
							new_option_html+=\'<li id="options_\' + s.option_id + \'">\';
							new_option_html+=\'<h2>\';
							new_option_html+=\'<span class="option_id">'.addslashes($this->pi_getLL('admin_label_option_name')).': \' + s.option_name + \' (ID: \' + s.option_id + \')</span>\';
							new_option_html+=\'<span class="option_edit">\';
							new_option_html+=\'<a href="#" class="edit_options msadmin_button" rel="\' + s.option_id + \'">'.$this->pi_getLL('edit').'</a>&nbsp;\';
							new_option_html+=\'<a href="#" class="delete_options msadmin_button" rel="\' + s.option_id + \'">'.$this->pi_getLL('delete').'</a>&nbsp;\';
							new_option_html+=\'</span>\';
							new_option_html+=\'</h2>\';
							// settings related to options
							new_option_html+=\'<div class="option_settings">\';
							new_option_html+=s.options_groups
							new_option_html+=\'<span class="listing_type">\';
							new_option_html+=\''.addslashes($this->pi_getLL('admin_label_listing_type')).': \';
							new_option_html+=s.listtype;
							new_option_html+=\'</span>\';
							new_option_html+=\'<span class="required">\';
							if (s.required=="1") {
								new_option_html+=\'<input name="required[\' + s.option_id + \']" type="checkbox" value="1" checked /> '.$this->pi_getLL('required').'\';
							} else {
								new_option_html+=\'<input name="required[\' + s.option_id + \']" type="checkbox" value="1" /> '.$this->pi_getLL('required').'\';
							}
							new_option_html+=\'</span>\';
							new_option_html+=\'<span class="hide_in_cart">\';
							if (s.hide_in_cart=="1") {
								new_option_html+=\'<input name="hide_in_cart[\' + s.option_id + \']" type="checkbox" value="1" checked /> '.addslashes($this->pi_getLL('admin_label_dont_include_attribute_values_in_cart')).'\';
							} else {
								new_option_html+=\'<input name="hide_in_cart[\' + s.option_id + \']" type="checkbox" value="1" /> '.addslashes($this->pi_getLL('admin_label_dont_include_attribute_values_in_cart')).'\';
							}
							new_option_html+=\'</span>\';
							new_option_html+=\'</div>\';
							new_option_html+=\'<div class="option_values">\';
							new_option_html+=\'<a href="#" class="msadmin_button fetch_attributes_values" id="button_label_\' + s.option_id + \'" rel="\' + s.option_id + \'">'.addslashes($this->pi_getLL('show_attributes_values', 'SHOW VALUES')).'</a>&nbsp;\';
							new_option_html+=\'<ul class="attribute_option_values_sortable" rel="\' + s.option_id + \'" id="vc_\' + s.option_id + \'" style="display:none">\';
							new_option_html+=\'<li id="last_line_\' + s.option_id + \'"><a href="#" class="msadmin_button add_attributes_values" rel="\' + s.option_id + \'">'.addslashes($this->pi_getLL('admin_add_new_value')).'</a>&nbsp;<a href="#" class="msadmin_button hide_attributes_values" rel="\' + s.option_id + \'">'.$this->pi_getLL('admin_label_hide_values').'</a></li>\';
							new_option_html+=\'</ul>\';
							new_option_html+=\'<input type="hidden" name="values_fetched_\' + s.option_id + \'" id="values_fetched_\' + s.option_id + \'" value="0" />\';
							new_option_html+=\'</div>\';
							new_option_html+=\'</li>\';
							$(ul_option_listings).append(new_option_html);
						} else {
							msDialog("ERROR", s.reason);
						}
					}
				});
			} else {
				msDialog("ERROR", "'.addslashes($this->pi_getLL('admin_label_error_option_name_empty')).'");
			}
		});
	  	$(document).on("click", ".edit_options", function() {
	  		var optid=$(this).attr("rel");
	  		getOptionData(optid);
	  	});
	  	$(document).on("click", ".edit_options_values", function() {
	  		var tmp_relation_id=$(this).attr("rel");
	  		getOptionValuesData(tmp_relation_id);
	  	});
	  	$(document).on("click", ".add_attributes_values", function(){
	  		var d = new Date();
			var n = d.getTime();
			var new_values_input=\'new_options_values\' + n;
			var optid=$(this).attr("rel");
			var ul_parent_id="#vc_" + optid;
			var last_line_id="#last_line_" + optid;
			var ul_parent=$(this).parent().parent();
			var last_content_li=$(ul_parent).children().last().prev();
			var li_class="odd";
			if ($(last_content_li).hasClass("odd")) {
				li_class="even";
			}
			var new_li=\'<li class="\' + li_class + \' new_options_values" id="\' + new_values_input + \'">\';
			new_li+=\'<span class="values_id">'.$this->pi_getLL('admin_label_option_value').': <input type="hidden" style="width:200px" name="new_values" class="new_input_values_hidden \' + new_values_input + \'" /><input type="hidden" name="is_manual" class="new_input_values_hidden" value="0" /></span>\';
			new_li+=\'<span class="values_edit">\';
			new_li+=\'<a href="#" class="cancel_new_options_values msadmin_button">'.$this->pi_getLL('cancel').'</a>&nbsp;\';
			new_li+=\'<a href="#" class="save_new_options_values msadmin_button" rel="\' + optid + \'">'.$this->pi_getLL('save').'</a>&nbsp;\';
			new_li+=\'</span>\';
			new_li+="</li>";
			$(new_li).insertBefore(last_line_id);
			if ($(ul_parent_id).is(":hidden")) {
				$(ul_parent_id).show();
			}
			select2_options_value("." + new_values_input, "new options values", "new_values_input_drop", "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=get_attributes_values').'");
	  	});
	  	$(document).on("click", ".cancel_new_options_values", function() {
	  		$(this).parent().parent().remove();
	  	});
	  	$(document).on("click", ".save_new_options_values", function () {
	  		var optid=$(this).attr("rel");
	  		var parent_li=$(this).parent().parent();
	  		var select2_class="." + $(parent_li).attr("id");
	  		$(select2_class).select2("destroy");
	  		// gather value
	  		var hidden_input=$(this).parent().parent().children("span.values_id").children("input.new_input_values_hidden");
	  		var serial_value="optid=" + optid + "&" + $(hidden_input).serialize();
			// save new values
			href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=save_options_values_data').'";
			$.ajax({
				type:"POST",
				url:href,
				data:serial_value,
				dataType:"json",
				success: function(s) {
					var attributeImageUploader=[];
					var li_class=\'even\';
					if ($(parent_li).hasClass("odd")) {
						li_class=\'odd\';
					}
					var values_data=\'\';
					values_data+=\'<li id="option_values_\' + s.values_id + \'" class="option_values_\' + optid + \'_\' + s.values_id + \' \'+li_class+\'">\';
					values_data+=\'<span class="values_id">\';
					values_data+=\''.addslashes($this->pi_getLL('admin_label_option_value')).': \';
					values_data+=s.values_name;
					values_data+=\'</span>\';
					values_data+=\'<span class="values_image">\';
					values_data+=\'<label for="attribute_values_image\' + s.pov2po_id + \'">'.addslashes($this->pi_getLL('admin_image')).'</label>\';
					values_data+=\'<div id="attribute_values_image\' + s.pov2po_id + \'">\';
					values_data+=\'<noscript>\';
					values_data+=\'<input name="attribute_values_image\' + s.pov2po_id + \'" type="file" />\';
					values_data+=\'</noscript>\';
					values_data+=\'</div>\';
					values_data+=\'<input name="ajax_attribute_values_image\' + s.pov2po_id + \'" id="ajax_attribute_values_image\' + s.pov2po_id + \'" type="hidden" value="" />\';
					//values_data+=s.values_image_display;
					values_data+=\'</span>\';
					values_data+=\'<span class="values_edit">\';
					values_data += \'<a href="#" class="edit_options_values msadmin_button" rel="\' + s.pov2po_id + \'">'.$this->pi_getLL('edit').'</a>&nbsp;\';
					values_data += \'<a href="#" class="delete_options_values msadmin_button" rel="\' + optid + \':\' + s.values_id + \'">'.$this->pi_getLL('delete').'</a>&nbsp;\';
					values_data+=\'</span>\';
					values_data += \'</li>\';
					$("#" + $(parent_li).attr("id")).replaceWith(values_data);

					var attribute_values_name=\'attribute_values_image_\' + s.pov2po_id;
					attributeImageUploader[s.pov2po_id] = new qq.FileUploader({
						element: document.getElementById(\'attribute_values_image\' + s.pov2po_id),
						action: \''.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=upload_attribute_values_image').'\',
						params: {
							attribute_values_name: attribute_values_name,
							pov2po_id: s.pov2po_id,
							file_type: \'attribute_values_image\' + s.pov2po_id
						},
						template: \'<div class="qq-uploader">\' +
								  \'<div class="qq-upload-drop-area"><span>'.addslashes($this->pi_getLL('admin_label_drop_files_here_to_upload')).'</span></div>\' +
								  \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
								  \'<ul class="qq-upload-list"></ul>\' +
								  \'</div>\',
						onComplete: function(id, fileName, responseJSON){
							console.log(responseJSON);
							var filenameServer = responseJSON[\'filename\'];
							var image_display_val=responseJSON[\'image_display\'];
							var target_after=responseJSON[\'target_after\'];
							var target_delete=responseJSON[\'target_delete\'];
							$(\'#ajax_attribute_values_image\' + s.pov2po_id).val(filenameServer);
							$(target_delete).remove();
							$(image_display_val).insertAfter(target_after);

						},
						debug: false
					});
				}
			});
	  	});
		$(document).on("click", ".fetch_attributes_values", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			var container_id = "#vc_" + opt_id;
			var fetched_id = "#values_fetched_" + opt_id;
			var button_label_id = "#button_label_" + opt_id;
			if ($(fetched_id).val() == "0") {
				href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=fetch_attributes').'";
				$.ajax({
					type:   "POST",
					url:    href,
					data:   \'data_id=\' + opt_id,
					dataType: "json",
					success: function(r) {
						if (r.results) {
							var attributeImageUploader=[];
							var classItem=\'even\';
							$(container_id).empty();
							$.each(r.results, function(i, v) {
								var values_data = "";
								if (classItem==\'even\') {
									classItem=\'odd\';
								} else {
									classItem=\'even\';
								}
								attributesValues[v.values_id]={id: v.values_id, text: v.values_name}
								values_data+=\'<li id="option_values_\' + v.values_id + \'" class="option_values_\' + opt_id + \'_\' + v.values_id + \' \'+classItem+\'">\';
								values_data+=\'<span class="values_id">\';
								values_data+=\''.addslashes($this->pi_getLL('admin_label_option_value')).': \';
								values_data+=v.values_name;
								values_data+=\'</span>\';
								if (v.values_image!=\'disabled\') {
									values_data+=\'<span class="values_image">\';
									values_data+=\'<label for="attribute_values_image\' + v.pov2po_id + \'">'.addslashes($this->pi_getLL('admin_image')).'</label>\';
									values_data+=\'<div id="attribute_values_image\' + v.pov2po_id + \'">\';
									values_data+=\'<noscript>\';
									values_data+=\'<input name="attribute_values_image\' + v.pov2po_id + \'" type="file" />\';
									values_data+=\'</noscript>\';
									values_data+=\'</div>\';
									values_data+=\'<input name="ajax_attribute_values_image\' + v.pov2po_id + \'" id="ajax_attribute_values_image\' + v.pov2po_id + \'" type="hidden" value="" />\';
									values_data+=v.values_image_display;
									values_data+=\'</span>\';
								}
								values_data+=\'<span class="values_edit">\';
								values_data += \'<a href="#" class="edit_options_values msadmin_button" rel="\' + v.pov2po_id + \'">'.$this->pi_getLL('edit').'</a>&nbsp;\';
								values_data += \'<a href="#" class="delete_options_values msadmin_button" rel="\' + opt_id + \':\' + v.values_id + \'">'.$this->pi_getLL('delete').'</a>&nbsp;\';
								values_data+=\'</span>\';
								values_data += \'</li>\';
								$(container_id).append(values_data);
								if (v.values_image!=\'disabled\') {
									var attribute_values_name=\'attribute_values_image_\' + v.pov2po_id;
									attributeImageUploader[v.pov2po_id] = new qq.FileUploader({
										element: document.getElementById(\'attribute_values_image\' + v.pov2po_id),
										action: \''.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=upload_attribute_values_image').'\',
										params: {
											attribute_values_name: attribute_values_name,
											pov2po_id: v.pov2po_id,
											file_type: \'attribute_values_image\' + v.pov2po_id
										},
										template: \'<div class="qq-uploader">\' +
												  \'<div class="qq-upload-drop-area"><span>'.$this->pi_getLL('admin_label_drop_files_here_to_upload').'</span></div>\' +
												  \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
												  \'<ul class="qq-upload-list"></ul>\' +
												  \'</div>\',
										onComplete: function(id, fileName, responseJSON){
											console.log(responseJSON);
											var filenameServer = responseJSON[\'filename\'];
											var image_display_val=responseJSON[\'image_display\'];
											var target_after=responseJSON[\'target_after\'];
											var target_delete=responseJSON[\'target_delete\'];
											$(\'#ajax_attribute_values_image\' + v.pov2po_id).val(filenameServer);
											$(target_delete).remove();
											$(image_display_val).insertAfter(target_after);

										},
										debug: false
									});
								}

							});
							var values_data= \'<li id="last_line_\' + opt_id + \'"><a href="#" class="msadmin_button add_attributes_values" rel="\' + opt_id + \'">'.addslashes($this->pi_getLL('admin_add_new_value')).'</a>&nbsp;<a href="#" class="msadmin_button hide_attributes_values" rel="\' + opt_id + \'">'.$this->pi_getLL('admin_label_hide_values').'</a></li>\';
							$(container_id).append(values_data);

							$(fetched_id).val("1");
							$(container_id).show();
							$(button_label_id).html("'.addslashes($this->pi_getLL('admin_label_hide_values')).'");
						} else {
							$(container_id).show();
							$(button_label_id).html("'.addslashes($this->pi_getLL('admin_label_hide_values')).'");
						}
					}
				});
			} else if ($(fetched_id).val() == "1") {
				if ($(container_id).is(":hidden")) {
					$(container_id).show();
					$(button_label_id).html("'.addslashes($this->pi_getLL('admin_label_hide_values')).'");
				} else {
					$(container_id).hide();
					$(button_label_id).html("'.addslashes($this->pi_getLL('show_attributes_values')).'");
				}
			}
		});
		$(document).on("click", "#delete_attribute_values_image", function(e) {
			e.preventDefault();
			href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_values_image').'";
			var pov2po_id=$(this).attr("rel");
			if (confirm(\'Are you sure?\')) {
				$.ajax({
					type:   "POST",
					url:    href,
					data:   \'pov2po=\' + pov2po_id,
					dataType: "json",
					success: function(r) {
						if (r.target_delete!=\'\') {
							$(r.target_delete).remove();
						}
					}
				});
			}
		});
		$(document).on("click", ".hide_attributes_values", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			var container_id = "#vc_" + opt_id;
			var button_label_id = "#button_label_" + opt_id;
			if ($(container_id).is(":hidden")) {
				$(container_id).show();
				$(button_label_id).html("'.addslashes($this->pi_getLL('admin_label_hide_values')).'");
			} else {
				$(container_id).hide();
				$(button_label_id).html("'.addslashes($this->pi_getLL('show_attributes_values')).'");
			}
		});
		$(document).on("click", ".delete_options", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_attributes').'";
			$.ajax({
				type:   "POST",
				url:    href,
				data:   \'data_id=\' + opt_id,
				dataType: "json",
				success: function(r) {
					if (r.delete_status == "notok") {
						//var products_used = parseInt(r.products_used);
						var dialog_box_id = "#dialog-confirm";

						if (parseInt(r.products_used) > 0) {
							dialog_box_id = "#dialog-confirm-force";

							// add product list that mapped to attributes
							$("#used-product-number").html("<strong>" + r.products_used + "</strong>");

							var product_list = "<ul>";
							$.each(r.products, function(i, v){
								product_list += "<li>"+ parseInt(i+1) +". <a href=\""+v.link+"\" target=\"_blank\" alt=\"Edit\">"+ v.name +"</a></li>";
							});
							product_list += "<ul>";
							$("#products-used-attributes-list").html(product_list);
						}

						if (r.option_value_id != null) {
							$("#attributes-name0").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
							$("#attributes-name1").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
						} else {
							$("#attributes-name0").html("<strong>Option: " + r.option_name + "</strong>");
							$("#attributes-name1").html("<strong>Option: " + r.option_name + "</strong>");
						}
						$(dialog_box_id).show();
						$(dialog_box_id).dialog({
							resizable: false,
							height:400,
							width:500,
							modal: true,
							buttons: {
								"Cancel":{
									text: "'.$this->pi_getLL('cancel').'",
									class: \'msCancelButton msBackendButton prevState arrowLeft arrowPosLeft\',
									click: function() {
										$(this).dialog("close");
										$(this).hide();
									}
								},
								"delete":{
									text: "'.$this->pi_getLL('delete').'",
									class: \'msOkButton msBackendButton continueState arrowRight arrowPosLeft\',
									click: function() {
										href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_attributes&force_delete=1').'";
										$.ajax({
												type:   "POST",
												url:    href,
												data:   \'data_id=\' + r.data_id,
												dataType: "json",
												success: function(s) {
													if (s.delete_status == "ok"){
														$(s.delete_id).remove();
													}
												}
										});
										$(this).dialog("close");
										$(this).hide();
									}
								}
							}
						});
					}
				}
			});
		});
		$(document).on("click", ".delete_options_values", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_options_values').'";
			$.ajax({
				type:   "POST",
				url:    href,
				data:   \'data_id=\' + opt_id,
				dataType: "json",
				success: function(r) {
					if (r.delete_status == "notok") {
						//var products_used = parseInt(r.products_used);
						var dialog_box_id = "#dialog-confirm";

						if (parseInt(r.products_used) > 0) {
							dialog_box_id = "#dialog-confirm-force";

							// add product list that mapped to attributes
							$("#used-product-number").html("<strong>" + r.products_used + "</strong>");

							var product_list = "<ul>";
							$.each(r.products, function(i, v){
								product_list += "<li>"+ parseInt(i+1) +". <a href=\""+v.link+"\" target=\"_blank\" alt=\"Edit\">"+ v.name +"</a></li>";
							});
							product_list += "<ul>";
							$("#products-used-attributes-list").html(product_list);
						}

						if (r.option_value_id != null) {
							$("#attributes-name0").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
							$("#attributes-name1").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
						} else {
							$("#attributes-name0").html("<strong>Option: " + r.option_name + "</strong>");
							$("#attributes-name1").html("<strong>Option: " + r.option_name + "</strong>");
						}
						$(dialog_box_id).show();
						$(dialog_box_id).dialog({
							resizable: false,
							height:400,
							width:500,
							modal: true,
							buttons: {
								"Cancel":{
									text: "'.$this->pi_getLL('cancel').'",
									class: \'msCancelButton msBackendButton prevState arrowLeft arrowPosLeft\',
									click: function() {
										$(this).dialog("close");
										$(this).hide();
									}
								},
								"delete":{
									text: "'.$this->pi_getLL('delete').'",
									class: \'msOkButton msBackendButton continueState arrowRight arrowPosLeft\',
									click: function() {
										href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_options_values&force_delete=1').'";
										$.ajax({
												type:   "POST",
												url:    href,
												data:   \'data_id=\' + r.data_id,
												dataType: "json",
												success: function(s) {
													if (s.delete_status == "ok"){
														$(s.delete_id).remove();
													}
												}
										});
										$(this).dialog("close");
										$(this).hide();
									}
								}
							}
						});
					}
				}
			});
		});
		var result=$(".attribute_options_sortable").sortable({
			cursor:"move",
			//axis:"y",
			update:function(e, ui) {
				href="'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_attributes_sortable&tx_multishop_pi1[type]=options').'";
				$(this).sortable("refresh");
				sorted=$(this).sortable("serialize","id");
				$.ajax({
					type:"POST",
					url:href,
					data:sorted,
					success:function(msg) {}
				});
			}
		});
		var result2=$(".attribute_option_values_sortable").sortable({
			cursor:"move",
			//axis:"y",
			update:function(e, ui) {
				href = "'.mslib_fe::typolink(',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_attributes_sortable&tx_multishop_pi1[type]=option_values').'";
				$(this).sortable("refresh");
				sorted = $(this).sortable("serialize","id");
				var products_options_id=$(this).attr("rel");
				$.ajax({
					type:"POST",
					url:href,
					data:sorted+"&products_options_id="+products_options_id,
					success:function(msg) {}
				});
			}
		});
	});
</script>';
} else {
	$content.='<h1>'.$this->pi_getLL('admin_label_no_product_attributes_defined_yet').'</h1>';
	$content.=$this->pi_getLL('admin_label_you_can_add_product_attributes_while_creating_and_or_editing_a_product');
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>