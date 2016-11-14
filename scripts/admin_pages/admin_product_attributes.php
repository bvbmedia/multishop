<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$content.='<div class="panel-heading"><h3>'.$this->pi_getLL('admin_attributes').'</h3></div><div class="panel-body form-horizontal">';
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
$selects['date']=$this->pi_getLL('admin_label_option_type_date');
$selects['datetime']=$this->pi_getLL('admin_label_option_type_datetime');
$selects['dateofbirth']=$this->pi_getLL('admin_label_option_type_dateofbirth');
$selects['datecustom']=$this->pi_getLL('admin_label_option_type_datecustom');
// new options
$options_group='';
if ($this->ms['MODULES']['ENABLE_ATTRIBUTES_OPTIONS_GROUP']) {
    $options_group=mslib_fe::buildAttributesOptionsGroupSelectBox($row['products_options_id'], 'id="new_options_groups" class="form-control add_new_attributes_options"');
    if (!empty($options_group)) {
        $options_group='<div class="form-group"><label class="col-md-2 control-label">'.$this->pi_getLL('admin_label_options_group').': </label><div class="col-md-4">'.$options_group.'</div></div>';
    } else {
        $options_group='<div class="form-group"><label class="col-md-2 control-label">'.$this->pi_getLL('admin_label_options_group').': </label><div class="col-md-4">'.$this->pi_getLL('admin_label_no_groups_defined').'</div></div>';
    }
}
$content.='<div class="panel panel-default">';
$content.='<div class="panel-heading">';
$content.='<h3>'.$this->pi_getLL('admin_label_add_new_attribute_options').'</h3>';
$content.='</div>';
$content.='<div class="panel-body">';
$content.='<form method="post" class="msadminFromFancybox  form-horizontal" name="admin_product_new_attribute_options">';
// settings related to options
$content.='<div class="option_settings">';
$content.='<div class="form-group">';
$content.='<label for="new_option_name" class="col-md-2 control-label">'.$this->pi_getLL('admin_label_new_option_name').':</label>';
$content.='<div class="col-md-4">';
$content.='<input type="text" id="new_option_name" name="new_option_name" class="form-control text add_new_attributes_options">';
$content.='</div>';
$content.='</div>';
$content.=$options_group;
$content.='<div class="form-group">';
$content.='<label class="col-md-2 control-label">'.$this->pi_getLL('admin_label_listing_type').': </label><div class="col-md-4"><select name="listtype" id="new_listtype" class="form-control add_new_attributes_options">';
foreach ($selects as $key=>$value) {
    $content.='<option value="'.$key.'"'.($key==$row['listtype'] ? ' selected' : '').'>'.htmlspecialchars($value).'</option>';
}
$content.='</select></div>';
$content.='</div>';
$content.='
<div class="form-group">
    <div class="col-md-10 col-md-offset-2">
        <div class="checkbox checkbox-success checkbox-inline">
            <input name="required" id="required_checkbox" type="checkbox" value="1" class="add_new_attributes_options"><label for="required_checkbox">'.$this->pi_getLL('required').'</label>
        </div>
        <div class="checkbox checkbox-success checkbox-inline">
            <input name="hide_in_details_page" id="hide_in_details_page_checkbox" type="checkbox" value="1" class="add_new_attributes_options"><label for="hide_in_details_page_checkbox">'.$this->pi_getLL('admin_label_hide_in_details_page').'</label>
        </div>
        <div class="checkbox checkbox-success checkbox-inline">
            <input name="hide_in_cart" id="hide_in_cart_checkbox" type="checkbox" value="1" class="add_new_attributes_options"><label for="hide_in_cart_checkbox">'.$this->pi_getLL('admin_label_dont_include_attribute_values_in_cart').'</label>
        </div>
    </div>
</div>';
$content.='</div>';
$content.='</form>';
$content.='<div class="form-group no-mb">';
$content.='<div class="col-md-10 col-md-offset-2">';
$content.='<a href="#" class="btn btn-success" id="save_new_attribute_options"><i class="fa fa-check fa-plus"></i> '.$this->pi_getLL('admin_label_add_new_attribute_options').'</a>';
$content.='</div>';
$content.='</div>';
$content.='</div>';
$content.='</div>';
//
if ($this->post) {
    if (is_array($this->post['listtype']) and count($this->post['listtype'])) {
        foreach ($this->post['listtype'] as $products_options_id=>$settings_value) {
            foreach ($this->languages as $key=>$language) {
                if (!$key) {
                    $default_settings_value =$settings_value;
                 }
                $updateArray = array();
                $updateArray['language_id'] = $key;
                $updateArray['products_options_id'] = $products_options_id;
                $updateArray['listtype'] = $default_settings_value;
                $updateArray['required'] = $this->post['required'][$products_options_id];
                $updateArray['hide'] = $this->post['hide_in_details_page'][$products_options_id];
                $updateArray['hide_in_cart'] = $this->post['hide_in_cart'][$products_options_id];
                $str = $GLOBALS['TYPO3_DB']->SELECTquery('1', // SELECT ...
                        'tx_multishop_products_options', // FROM ...
                        'products_options_id=\'' . $products_options_id . '\' and language_id=\'' . $key . '\'', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\'' . $products_options_id . '\' and language_id=\'' . $key . '\'', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                } else {
                    $updateArray['products_options_name'] = '';
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
            }
            if ($this->ms['MODULES']['ENABLE_ATTRIBUTES_OPTIONS_GROUP']) {
                if (isset($this->post['options_groups'][$products_options_id])) {
                    if (!empty($this->post['options_groups'][$products_options_id])) {
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
                    } else {
                        $query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_attributes_options_groups_to_products_options', 'products_options_id=\''.$products_options_id.'\'');
                        $res=$GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
            }
        }
        // redirect to current page after done saving an option settings
        header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_product_attributes'));
		exit();
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
$content.='<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_product_attributes').'" method="post" class="msadminFromFancybox" name="admin_product_attributes">';
$content.='<div class="attribute_options_sortable" id="attribute_listings">';
if ($rows) {
    //$content.='<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_product_attributes').'" method="post" class="msadminFromFancybox" name="admin_product_attributes">';
//	$content.='<span class="msBackendButton float_right continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" /></span>';
    //$content.='<form role="form" class="msadminFromFancybox" name="admin_product_attributes">';
    //$content.='<div class="attribute_options_sortable" id="attribute_listings">';
    while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
        $content.='<div class="panel panel-default" id="options_'.$row['products_options_id'].'">';
        $content.='<div class="panel-heading">';
        $content.='<h3>'.$this->pi_getLL('admin_label_option_name').': '.$row['products_options_name'].' (ID: '.$row['products_options_id'].')';
        $content.='<span class="option_edit">';
        $content.='&nbsp;<a href="#" class="edit_options btn btn-primary btn-xs" rel="'.$row['products_options_id'].'"><i class="fa fa-pencil"></i></a>';
        $content.='&nbsp;<a href="#" class="delete_options btn btn-danger btn-xs" rel="'.$row['products_options_id'].'"><i class="fa fa-remove"></i></a>&nbsp;';
        $content.='</span>';
        $content.='</h3>';
        $content.='</div>';
        $content.='<div class="panel-body">';
        $options_group='';
        if ($this->ms['MODULES']['ENABLE_ATTRIBUTES_OPTIONS_GROUP']) {
            $options_group=mslib_fe::buildAttributesOptionsGroupSelectBox($row['products_options_id'], 'class="form-control"');
            if (!empty($options_group)) {
                $options_group='<div class="form-group"><label class="col-md-2 control-label">'.$this->pi_getLL('admin_label_options_group').': </label><div class="col-md-4">'.$options_group.'</div></div>';
            } else {
                $options_group='<div class="form-group"><label class="col-md-2 control-label">'.$this->pi_getLL('admin_label_options_group').': </label><div class="col-md-4">'.$this->pi_getLL('admin_label_no_groups_defined').'</div></div>';
            }
        }
        // settings related to options
        //$content.='<div class="option_settings">';
        $content.=$options_group;
        $content.='<div class="form-group">';
        $content.='<label class="col-md-2 control-label">'.$this->pi_getLL('admin_label_listing_type').': </label><div class="col-md-4"><select name="listtype['.$row['products_options_id'].']" class="form-control">';
        foreach ($selects as $key=>$value) {
            $content.='<option value="'.$key.'"'.($key==$row['listtype'] ? ' selected' : '').'>'.htmlspecialchars($value).'</option>';
        }
        $content.='</select></div>';
        $content.='</div>';


        $content.='
<div class="form-group">
    <div class="col-md-8 col-md-offset-2">
        <div class="checkbox checkbox-success checkbox-inline">
            <input name="required['.$row['products_options_id'].']" id="required['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['required'] ? ' checked' : '').'/><label for="required['.$row['products_options_id'].']">'.$this->pi_getLL('required').'</label>
        </div>
        <div class="checkbox checkbox-success checkbox-inline">
            <input name="hide_in_details_page['.$row['products_options_id'].']" id="hide_in_details_page['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['hide'] ? ' checked' : '').'/><label for="hide_in_details_page['.$row['products_options_id'].']">'.$this->pi_getLL('admin_label_hide_in_details_page').'</label>
        </div>
        <div class="checkbox checkbox-success checkbox-inline">
            <input name="hide_in_cart['.$row['products_options_id'].']" id="hide_in_cart['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['hide_in_cart'] ? ' checked' : '').'/><label for="hide_in_cart['.$row['products_options_id'].']">'.$this->pi_getLL('admin_label_dont_include_attribute_values_in_cart').'</label>
        </div>
    </div>
</div>';
        //$content.='</div>';
        $content.='<div class="form-group">';
		$content.='<div class="col-md-10 col-md-offset-2">';
        $content.='<a href="#" class="btn btn-success add_attributes_values" rel="'.$row['products_options_id'].'"><i class="fa fa-edit"></i> '.$this->pi_getLL('admin_add_new_value').'</a> ';
        $content.='<a href="#" class="btn btn-success fetch_attributes_values" id="button_label_'.$row['products_options_id'].'" rel="'.$row['products_options_id'].'"><i class="fa fa-eye"></i> '.$this->pi_getLL('show_attributes_values', 'SHOW VALUES').'</a>';
        $content.='</div>';
		$content.='</div>';
        //$content.='<a href="#" class="btn btn-success fetch_options_description" id="button_label_desc_'.$row['products_options_id'].'" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('show_options_description', 'EDIT DESCRIPTION').'</a>';

        $content.='<div class="panel panel-default" style="display: none">';
        $content.='<div class="panel-body">';
        $content.='<div class="form-group">';
        $content.='<label for="sort_order_attributes_option_values" class="col-md-4">Sort by</label>';
        $content.='<div class="col-md-8">';
        $content.='<select id="sort_order_attributes_option_values" class="form-control sort_order_attributes_option_values" rel="' . $row['products_options_id'] . '">';
        $content.='<option value="id_asc">Product option values id ('.$this->pi_getLL('ascending').')</option>';
        $content.='<option value="id_desc">Product option values id ('.$this->pi_getLL('descending').')</option>';
        $content.='<option value="alpha_asc">'.$this->pi_getLL('admin_sort_alphabet_asc').'</option>';
        $content.='<option value="alpha_desc">'.$this->pi_getLL('admin_sort_alphabet_desc').'</option>';
        $content.='<option value="alpha_nat_asc">'.$this->pi_getLL('admin_sort_alphabet_natural_asc').'</option>';
        $content.='<option value="alpha_nat_desc">'.$this->pi_getLL('admin_sort_alphabet_natural_desc').'</option>';
        $content.='</select>';
        $content.='</div>';
        $content.='</div>';

        $content.='<div class="attribute_option_values_sortable" rel="'.$row['products_options_id'].'" id="vc_'.$row['products_options_id'].'" style="display:none">';
        $content.='<div id="last_line_'.$row['products_options_id'].'">';
        $content.='<a href="#" class="btn btn-success add_attributes_values" rel="'.$row['products_options_id'].'"><i class="fa fa-edit"></i> '.$this->pi_getLL('admin_add_new_value').'</a> ';
        $content.='<a href="#" class="btn btn-success hide_attributes_values" rel="'.$row['products_options_id'].'"><i class="fa fa-eye"></i> '.$this->pi_getLL('admin_label_hide_values').'</a>';
        $content.='</div>';
        $content.='</div>';
        $content.='</div>';
        $content.='</div>';
        $content.='<input type="hidden" name="values_fetched_'.$row['products_options_id'].'" id="values_fetched_'.$row['products_options_id'].'" value="0" />';
        $content.='</div>';
        $content.='</div>';
    }
    //$content.='</div>';
    //$content.='<button class="btn btn-success" type="submit"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> '.$this->pi_getLL('save').'</button>';
    //$content.='</form>';
    $hide_form_save_btn='';
} else {
    $content.='<div id="no_attributes_box">';
    $content.='<h1>'.$this->pi_getLL('admin_label_no_product_attributes_defined_yet').'</h1>';
    $content.=$this->pi_getLL('admin_label_you_can_add_product_attributes_while_creating_and_or_editing_a_product');
    $content.='</div>';
    $hide_form_save_btn=' style="display:none"';
}
$content.='</div>';
$content .= '<button id="save_attributes_options_form" class="btn btn-success" type="submit"'.$hide_form_save_btn.'><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> ' . $this->pi_getLL('save') . '</button>';
$content.='</form>';
// now load the sortables jQuery code
$count_js_cache_values=count($js_select2_cache_values);
if ($count_js_cache_values) {
    $js_select2_cache.=implode(";\n", $js_select2_cache_values).";\n";
}
$js_select2_cache.='</script>';
if (!empty($js_select2_cache)) {
    $GLOBALS['TSFE']->additionalHeaderData['js_select2_cache']=$js_select2_cache;
}
$GLOBALS['TSFE']->additionalHeaderData['js_admin_product_attributes']='<script type="text/javascript" data-ignore="1">
	function attributesEditDialog (textTitle, textBody, mode) {
        $.confirm({
            title: textTitle,
            content: textBody,
            closeIcon: true,
            columnClass: \'col-md-8\',
            cancelButton: "'.$this->pi_getLL('cancel').'",
            cancel: function() {
            },
            confirmButton:"'.$this->pi_getLL('save').'",
            confirm: function() {
                if (mode=="edit_options") {
                    var options_value=$(".edit_option_inputs").serialize();
                    saveOptionsData(options_value, "options");
                } else if (mode=="edit_options_values") {
                    var options_values=$(".edit_option_values_inputs").serialize();
                    saveOptionsData(options_values, "options_values");
                }
            }
        });
	}
	function getOptionData(optid) {
		href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=get_option_data').'";
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
				    dialog_body+=\'<div class="form-group">\';
				    dialog_body+=\'<label for="option_values_\' + s.options_values_id + \'_\' + i + \'">\' + v.lang_title + \' : </label>\';
				    dialog_body+=\'<input type="text" class="form-control text edit_option_inputs" name="option_names[\' + optid + \'][\' + i + \']" id="option_names_\' + optid + \'_\' + i + \'" value="\' + v.options_name + \'"/>\';
				    dialog_body+=\'</div>\';
                    dialog_body+=\'<div class="form-group">\';
                    dialog_body+=\'<label for="option_desc_\' + optid + \'_\' + i + \'" class="option_description_label">'.addslashes($this->pi_getLL('description')).'</label>\';
					dialog_body+=\'<textarea class="form-control redactor_options edit_option_inputs" id="option_desc_\' + optid + \'_\' + i + \'" name="option_desc[\' + optid + \'][\' + i + \']">\' + v.options_desc + \'</textarea>\';
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
		href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=get_option_values_data').'";
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
				    dialog_body+=\'<div class="form-group">\';
				    dialog_body+=\'<label for="option_values_\' + s.options_values_id + \'_\' + i + \'">\' + v.lang_title + \' : </label>\';
				    dialog_body+=\'<input type="text" class="form-control text edit_option_values_inputs" id="option_values_\' + s.options_values_id + \'_\' + i + \'" name="option_values[\' + s.options_values_id + \'][\' + i + \']" value="\' + v.lang_values + \'"/>\';
				    dialog_body+=\'</div>\';
                    dialog_body+=\'<div class="form-group">\';
					dialog_body+=\'<label for="ov_desc_\' + v.lang_description_pov2po_id + \'_\' + i + \'" class="option_description_label">'.addslashes($this->pi_getLL('description')).'</label>\';
					dialog_body+=\'<textarea class="redactor_values edit_option_values_inputs form-control" rows="5" name="ov_desc[\' + v.lang_description_pov2po_id + \'][\' + i + \']" id="ov_desc_\' + v.lang_description_pov2po_id + \'_\' + i + \'">\' + v.lang_description + \'</textarea>\';
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
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_options_data').'";
		} else if (mode=="options_values") {
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_options_values_data').'";
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
				href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=add_new_options').'";
				$.ajax({
					type:"POST",
					url:href,
					data:serial_value,
					dataType:"json",
					success: function(s) {
						var ul_option_listings=$("#attribute_listings");
						var new_option_html=\'\';
						if (s.status=="OK") {
							new_option_html+=\'<div class="panel panel-default" id="options_\' + s.option_id + \'">\';
							new_option_html+=\'<div class="panel-heading">\';
							new_option_html+=\'<h3>'.addslashes($this->pi_getLL('admin_label_option_name')).': \' + s.option_name + \' (ID: \' + s.option_id + \') \';
							new_option_html+=\'<span class="option_edit">\';
							new_option_html+=\'&nbsp;<a href="#" class="edit_options btn btn-primary btn-xs" rel="\' + s.option_id + \'"><i class="fa fa-pencil"></i></a>\';
							new_option_html+=\'&nbsp;<a href="#" class="delete_options btn btn-danger btn-xs" rel="\' + s.option_id + \'"><i class="fa fa-remove"></i></a>&nbsp;\';
							new_option_html+=\'</span>\';
							new_option_html+=\'</h3>\';
							new_option_html+=\'</div>\';

                            new_option_html+=\'<div class="panel-body">\';

							new_option_html+=\'<div class="form-group">\';
							new_option_html+=\'<label class="control-label col-md-2">'.addslashes($this->pi_getLL('admin_label_listing_type')).': </label>\';
							new_option_html+=\'<div class="col-md-4">\' + s.listtype + \'</div>\';
							new_option_html+=\'</div>\';

                            new_option_html+=s.options_groups

                            new_option_html+=\'<div class="form-group">\';
                            new_option_html+=\'<div class="col-md-2">&nbsp;</div>\';
							new_option_html+=\'<div class="col-md-8">\';
							new_option_html+=\'<div class="checkbox checkbox-success checkbox-inline">\';
							if (s.required=="1") {
								new_option_html+=\'<input name="required[\' + s.option_id + \']" id="required[\' + s.option_id + \']" type="checkbox" value="1" checked /><label for="required[\' + s.option_id + \']">'.$this->pi_getLL('required').'</label>\';
							} else {
								new_option_html+=\'<input name="required[\' + s.option_id + \']" id="required[\' + s.option_id + \']" type="checkbox" value="1" /><label for="required[\' + s.option_id + \']">'.$this->pi_getLL('required').'</label>\';
							}
							new_option_html+=\'</div>\';
							new_option_html+=\'<div class="checkbox checkbox-success checkbox-inline">\';
							if (s.hide_in_details_page=="1") {
								new_option_html+=\'<input name="hide_in_details_page[\' + s.option_id + \']" id="hide_in_details_page[\' + s.option_id + \']" type="checkbox" value="1" checked /><label for="hide_in_details_page[\' + s.option_id + \']">'.addslashes($this->pi_getLL('admin_label_hide_in_details_page')).'</label>\';
							} else {
								new_option_html+=\'<input name="hide_in_details_page[\' + s.option_id + \']" id="hide_in_details_page[\' + s.option_id + \']" type="checkbox" value="1" /><label for="hide_in_details_page[\' + s.option_id + \']">'.addslashes($this->pi_getLL('admin_label_hide_in_details_page')).'</label>\';
							}
							new_option_html+=\'</div>\';
							new_option_html+=\'<div class="checkbox checkbox-success checkbox-inline">\';
							if (s.hide_in_cart=="1") {
								new_option_html+=\'<input name="hide_in_cart[\' + s.option_id + \']" id="hide_in_cart[\' + s.option_id + \']" type="checkbox" value="1" checked /><label for="hide_in_cart[\' + s.option_id + \']">'.addslashes($this->pi_getLL('admin_label_dont_include_attribute_values_in_cart')).'</label>\';
							} else {
								new_option_html+=\'<input name="hide_in_cart[\' + s.option_id + \']" id="hide_in_cart[\' + s.option_id + \']" type="checkbox" value="1" /><label for="hide_in_cart[\' + s.option_id + \']">'.addslashes($this->pi_getLL('admin_label_dont_include_attribute_values_in_cart')).'</label>\';
							}
							new_option_html+=\'</div>\';
							new_option_html+=\'</div>\';
							new_option_html+=\'</div>\';

							new_option_html+=\'<div class="form-group">\';
							new_option_html+=\'<div class="col-md-10 col-md-offset-2">\';
							new_option_html+=\'<a href="#" class="btn btn-success add_attributes_values" rel="\' + s.option_id + \'"><i class="fa fa-edit"></i> '.addslashes($this->pi_getLL('admin_add_new_value')).'</a>&nbsp;\';
							new_option_html+=\'<a href="#" class="btn btn-success fetch_attributes_values" id="button_label_\' + s.option_id + \'" rel="\' + s.option_id + \'"><i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('show_attributes_values', 'SHOW VALUES')).'</a>&nbsp;\';
							new_option_html+=\'</div>\';
							new_option_html+=\'</div>\';

                            new_option_html+=\'<div class="panel panel-default" style="display:none">\';
                            new_option_html+=\'<div class="panel-body">\';
                            new_option_html+=\'<div class="form-group">\';
                            new_option_html+=\'<label for="sort_order_attributes_option_values" class="col-md-4">Sort by</label>\';
                            new_option_html+=\'<div class="col-md-8">\';
                            new_option_html+=\'<select id="sort_order_attributes_option_values" class="form-control sort_order_attributes_option_values" rel="\' + s.option_id + \'">\';
                            new_option_html+=\'<option value="id_asc">Product option values id ('.$this->pi_getLL('ascending').')</option>\';
                            new_option_html+=\'<option value="id_desc">Product option values id ('.$this->pi_getLL('descending').')</option>\';
                            new_option_html+=\'<option value="alpha_asc">'.$this->pi_getLL('admin_sort_alphabet_asc').'</option>\';
                            new_option_html+=\'<option value="alpha_desc">'.$this->pi_getLL('admin_sort_alphabet_desc').'</option>\';
                            new_option_html+=\'<option value="alpha_nat_asc">'.$this->pi_getLL('admin_sort_alphabet_natural_asc').'</option>\';
                            new_option_html+=\'<option value="alpha_nat_desc">'.$this->pi_getLL('admin_sort_alphabet_natural_desc').'</option>\';
                            new_option_html+=\'</select>\';
                            new_option_html+=\'</div>\';
                            new_option_html+=\'</div>\';
                            
                            new_option_html+=\'<div class="attribute_option_values_sortable" rel="\' + s.option_id + \'" id="vc_\' + s.option_id + \'" style="display:none">\';
							new_option_html+=\'<div id="last_line_\' + s.option_id + \'">\';
							new_option_html+=\'<a href="#" class="btn btn-success add_attributes_values" rel="\' + s.option_id + \'"><i class="fa fa-edit"></i> '.addslashes($this->pi_getLL('admin_add_new_value')).'</a>&nbsp;\';
							new_option_html+=\'<a href="#" class="btn btn-success hide_attributes_values" rel="\' + s.option_id + \'"><i class="fa fa-eye"></i> '.$this->pi_getLL('admin_label_hide_values').'</a>\';
							new_option_html+=\'</div>\';
							new_option_html+=\'<input type="hidden" name="values_fetched_\' + s.option_id + \'" id="values_fetched_\' + s.option_id + \'" value="0" />\';
							new_option_html+=\'</div>\';
                            new_option_html+=\'</div>\'; // panel-body
                            new_option_html+=\'</div>\'; // panel-default
                            

							new_option_html+=\'</div>\'; // .panel-body
							new_option_html+=\'</div>\'; // .panel .panel-default
							$(ul_option_listings).append(new_option_html);
							//
							if ($(\'#save_attributes_options_form\').is(\':hidden\')) {
							    $(\'#no_attributes_box\').hide();
							    $(\'#save_attributes_options_form\').show();
							}
							//
							$("#new_option_name").val("");
							$("#new_listtype").val("select");
							$(".add_new_attributes_options").prop("checked", false);
							//
							$(\'html, body\').animate({
                                scrollTop: $(\'#options_\' + s.option_id).offset().top
                            }, 2000);
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
			var new_li=\'<div class="panel panel-default mb-10 \' + li_class + \' new_options_values" id="\' + new_values_input + \'">\';
			new_li+=\'<div class="panel-body">\';
			new_li+=\'<span class="values_id">'.htmlentities($this->pi_getLL('admin_label_option_value')).': <input type="hidden" name="new_values" class="new_input_values_hidden \' + new_values_input + \'" style="width:300px" /><input type="hidden" name="is_manual" class="new_input_values_hidden" value="0" /></span>&nbsp;\';
			new_li+=\'<span class="values_edit">\';
			new_li+=\'<a href="#" class="cancel_new_options_values btn btn-danger"><i class="fa fa-remove"></i> '.$this->pi_getLL('cancel').'</a>&nbsp;\';
			new_li+=\'<a href="#" class="save_new_options_values btn btn-success" rel="\' + optid + \'"><i class="fa fa-check"></i> '.$this->pi_getLL('save').'</a>&nbsp;\';
			new_li+=\'</span>\';
			new_li+="</div>";
			new_li+="</div>";

			$(new_li).insertBefore(last_line_id);
			if ($(ul_parent_id).is(":hidden")) {
				$(ul_parent_id).parent().parent().show();
				$(ul_parent_id).show();
			}
			select2_options_value("." + new_values_input, "new options values", "new_values_input_drop", "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=get_attributes_values').'");
	  	});
	  	$(document).on("click", ".cancel_new_options_values", function() {
	  		$(this).parent().parent().parent().remove();
	  	});
	  	$(document).on("click", ".save_new_options_values", function () {
	  		var optid=$(this).attr("rel");
	  		var parent_li=$(this).parent().parent().parent();
	  		var select2_class="." + $(parent_li).attr("id");
	  		$(select2_class).select2("destroy");
	  		// gather value
	  		var hidden_input=$(this).parent().parent().children("span.values_id").children("input.new_input_values_hidden");
	  		var serial_value="optid=" + optid + "&" + $(hidden_input).serialize();
			// save new values
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=save_options_values_data').'";
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
					values_data+=\'<div id="option_values_\' + s.values_id + \'" class="panel panel-default option_values_\' + optid + \'_\' + s.values_id + \' \'+li_class+\'">\';
					values_data+=\'<div class="panel-heading">\';
					values_data+=\'<h3>\';
					values_data+=\''.addslashes($this->pi_getLL('admin_label_option_value')).': \';
					values_data+=s.values_name;
                    values_data+=\'<span class="values_edit">\';
					values_data += \'&nbsp;<a href="#" class="edit_options_values btn btn-primary btn-xs" rel="\' + s.pov2po_id + \'"><i class="fa fa-pencil"></i></a>\';
					values_data += \'&nbsp;<a href="#" class="delete_options_values btn btn-danger btn-xs" rel="\' + optid + \':\' + s.values_id + \'"><i class="fa fa-remove"></i></a>&nbsp;\';
					values_data+=\'</span>\';
					values_data+=\'</h3>\';
					values_data+=\'</div>\';
					values_data+=\'<div class="panel-body">\';
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
					values_data+=\'</div>\';
					values_data += \'</div>\';

					$("#" + $(parent_li).attr("id")).replaceWith(values_data);

					var attribute_values_name=\'attribute_values_image_\' + s.pov2po_id;
					attributeImageUploader[s.pov2po_id] = new qq.FileUploader({
						element: document.getElementById(\'attribute_values_image\' + s.pov2po_id),
						action: \''.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=upload_attribute_values_image').'\',
						params: {
							attribute_values_name: attribute_values_name,
							pov2po_id: s.pov2po_id,
							file_type: \'attribute_values_image\' + s.pov2po_id
						},
						template: \'<div class="qq-uploader">\' +
								  \'<div class="qq-upload-drop-area"><span>'.addslashes($this->pi_getLL('admin_label_drop_files_here_to_upload')).'</span></div>\' +
								  \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
								  \'<ul class="qq-upload-list" style="display:none"></ul>\' +
								  \'</div>\',
						onComplete: function(id, fileName, responseJSON){
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
				href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=fetch_attributes').'";
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
								values_data+=\'<div id="option_values_\' + v.values_id + \'" class="panel panel-default option_values_\' + opt_id + \'_\' + v.values_id + \' \'+classItem+\'">\';
								values_data+=\'<div class="panel-heading">\';
								values_data+=\'<h3>'.addslashes($this->pi_getLL('admin_label_option_value')).': \';
								values_data+=v.values_name;
								values_data+=\'<span class="values_edit">\';
								values_data += \'&nbsp;<a href="#" class="edit_options_values btn btn-primary btn-xs" rel="\' + v.pov2po_id + \'"><i class="fa fa-pencil"></i></a>\';
								values_data += \'&nbsp;<a href="#" class="delete_options_values btn btn-danger btn-xs" rel="\' + opt_id + \':\' + v.values_id + \'"><i class="fa fa-remove"></i></a>&nbsp;\';
								values_data+=\'</span>\';
								values_data+=\'</h3>\';
								values_data+=\'</div>\';
								values_data+=\'<div class="panel-body">\';
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
								values_data+=\'</div>\';
								values_data += \'</div>\';

								$(container_id).append(values_data);
								if (v.values_image!=\'disabled\') {
									var attribute_values_name=\'attribute_values_image_\' + v.pov2po_id;
									attributeImageUploader[v.pov2po_id] = new qq.FileUploader({
										element: document.getElementById(\'attribute_values_image\' + v.pov2po_id),
										action: \''.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=upload_attribute_values_image').'\',
										params: {
											attribute_values_name: attribute_values_name,
											pov2po_id: v.pov2po_id,
											file_type: \'attribute_values_image\' + v.pov2po_id
										},
										template: \'<div class="qq-uploader">\' +
												  \'<div class="qq-upload-drop-area"><span>'.$this->pi_getLL('admin_label_drop_files_here_to_upload').'</span></div>\' +
												  \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
												  \'<ul class="qq-upload-list" style="display:none"></ul>\' +
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
							var values_data= \'<div id="last_line_\' + opt_id + \'">\';
							values_data+= \'<a href="#" class="btn btn-success add_attributes_values" rel="\' + opt_id + \'"><i class="fa fa-edit"></i> '.addslashes($this->pi_getLL('admin_add_new_value')).'</a>&nbsp;\';
							values_data+= \'<a href="#" class="btn btn-success hide_attributes_values" rel="\' + opt_id + \'"><i class="fa fa-eye"></i> '.$this->pi_getLL('admin_label_hide_values').'</a>\';
							values_data+= \'</div>\';
							$(container_id).append(values_data);

							$(fetched_id).val("1");
							$(container_id).parent().parent().show();
							$(container_id).show();
							$(button_label_id).html(\'<i class="fa fa-eye"></i>  '.addslashes($this->pi_getLL('admin_label_hide_values')).'\');
						} else {
							$(container_id).parent().parent().show();
							$(container_id).show();
							
							$(button_label_id).html(\'<i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('admin_label_hide_values')).'\');
						}
					}
				});
			} else if ($(fetched_id).val() == "1") {
				if ($(container_id).is(":hidden")) {
					$(container_id).parent().parent().show();
					$(container_id).show();
					$(button_label_id).html(\'<i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('admin_label_hide_values')).'\');
				} else {
				    $(container_id).parent().parent().hide();
					$(container_id).hide();
					$(button_label_id).html(\'<i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('show_attributes_values')).'\');
				}
			}
		});
		$(document).on("change", ".sort_order_attributes_option_values", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			var container_id = "#vc_" + opt_id;
			var fetched_id = "#values_fetched_" + opt_id;
			var button_label_id = "#button_label_" + opt_id;
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=fetch_attributes').'&tx_multishop_pi1[sort_by]=" + $(this).val();
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
                            values_data+=\'<div id="option_values_\' + v.values_id + \'" class="panel panel-default option_values_\' + opt_id + \'_\' + v.values_id + \' \'+classItem+\'">\';
                            values_data+=\'<div class="panel-heading">\';
                            values_data+=\'<h3>'.addslashes($this->pi_getLL('admin_label_option_value')).': \';
                            values_data+=v.values_name;
                            values_data+=\'<span class="values_edit">\';
                            values_data += \'&nbsp;<a href="#" class="edit_options_values btn btn-primary btn-xs" rel="\' + v.pov2po_id + \'"><i class="fa fa-pencil"></i></a>\';
                            values_data += \'&nbsp;<a href="#" class="delete_options_values btn btn-danger btn-xs" rel="\' + opt_id + \':\' + v.values_id + \'"><i class="fa fa-remove"></i></a>&nbsp;\';
                            values_data+=\'</span>\';
                            values_data+=\'</h3>\';
                            values_data+=\'</div>\';
                            values_data+=\'<div class="panel-body">\';
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
                            values_data+=\'</div>\';
                            values_data += \'</div>\';

                            $(container_id).append(values_data);
                            if (v.values_image!=\'disabled\') {
                                var attribute_values_name=\'attribute_values_image_\' + v.pov2po_id;
                                attributeImageUploader[v.pov2po_id] = new qq.FileUploader({
                                    element: document.getElementById(\'attribute_values_image\' + v.pov2po_id),
                                    action: \''.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=upload_attribute_values_image').'\',
                                    params: {
                                        attribute_values_name: attribute_values_name,
                                        pov2po_id: v.pov2po_id,
                                        file_type: \'attribute_values_image\' + v.pov2po_id
                                    },
                                    template: \'<div class="qq-uploader">\' +
                                              \'<div class="qq-upload-drop-area"><span>'.$this->pi_getLL('admin_label_drop_files_here_to_upload').'</span></div>\' +
                                              \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
                                              \'<ul class="qq-upload-list" style="display:none"></ul>\' +
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
                        var values_data= \'<div id="last_line_\' + opt_id + \'">\';
                        values_data+= \'<a href="#" class="btn btn-success add_attributes_values" rel="\' + opt_id + \'"><i class="fa fa-edit"></i> '.addslashes($this->pi_getLL('admin_add_new_value')).'</a>&nbsp;\';
                        values_data+= \'<a href="#" class="btn btn-success hide_attributes_values" rel="\' + opt_id + \'"><i class="fa fa-eye"></i> '.$this->pi_getLL('admin_label_hide_values').'</a>\';
                        values_data+= \'</div>\';
                        $(container_id).append(values_data);

                        $(fetched_id).val("1");
                        $(container_id).parent().parent().show();
                        $(container_id).show();
                        $(button_label_id).html(\'<i class="fa fa-eye"></i>  '.addslashes($this->pi_getLL('admin_label_hide_values')).'\');
                    } else {
                        $(container_id).parent().parent().show();
                        $(container_id).show();
                        
                        $(button_label_id).html(\'<i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('admin_label_hide_values')).'\');
                    }
                }
            });
		});
		$(document).on("click", "#delete_attribute_values_image", function(e) {
			e.preventDefault();
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_values_image').'";
			var pov2po_id=$(this).attr("rel");
			ifConfirm("'.$this->pi_getLL('delete').'","'.$this->pi_getLL('are_you_sure').'",function() {
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
			});
		});
		$(document).on("click", ".hide_attributes_values", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			var container_id = "#vc_" + opt_id;
			var button_label_id = "#button_label_" + opt_id;
			if ($(container_id).is(":hidden")) {
			    $(container_id).parent().parent().show();
				$(container_id).show();
				$(button_label_id).html(\'<i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('admin_label_hide_values')).'\');
			} else {
			    $(container_id).parent().parent().hide();
				$(container_id).hide();
				$(button_label_id).html(\'<i class="fa fa-eye"></i> '.addslashes($this->pi_getLL('show_attributes_values')).'\');
			}
		});
		$(document).on("click", ".delete_options", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_attributes').'";
			$.ajax({
				type:   "POST",
				url:    href,
				data:   \'data_id=\' + opt_id,
				dataType: "json",
				success: function(r) {
					if (r.delete_status == "notok") {
					    var dialog_title="'.$this->pi_getLL('admin_label_warning_this_action_is_not_reversible').'";
                        var dialog_body=\'<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_are_you_sure_want_to_delete_x_attributes'), '<span id="attributes-name0"></span>').'</p>\';
						if (parseInt(r.products_used) > 0) {
							dialog_body=\'<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_there_are_x_products_using_x_attributes_are_you_sure_want_to_delete_it'), '<span id="used-product-number"></span>', '<span id="attributes-name1"></span>').'</p><br/><br/><p style="text-align:left">'.$this->pi_getLL('admin_label_the_products_using_this_attributes_are').':<br/>('.$this->pi_getLL('admin_label_link_will_open_in_new_tab_window').')</p><br/><span id="products-used-attributes-list" style="text-align:left"></span>\';
						}
						var confirm=$.confirm({
                            title: dialog_title,
                            content: dialog_body,
                            closeIcon: true,
                            columnClass: \'col-md-8\',
                            confirmButton: "'.$this->pi_getLL('delete').'",
                            confirm: function(){
                                href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_attributes&force_delete=1').'";
                                    $.ajax({
                                        type:   "POST",
                                        url:    href,
                                        data:   \'data_id=\' + r.data_id,
                                        dataType: "json",
                                        success: function(s) {
                                            if (s.delete_status == "ok"){
                                                $(s.delete_id).remove();
                                                if ($(\'#attribute_listings > div.panel\').length==0) {
                                                    $(\'#no_attributes_box\').show();
							                        $(\'#save_attributes_options_form\').hide();
                                                }
                                            }
                                        }
                                    });
                            },
                            cancelButton: "'.$this->pi_getLL('cancel').'",
                            cancel: function() {

                            }
                        });
                        if (parseInt(r.products_used) > 0) {
							// add product list that mapped to attributes
							confirm.contentDiv.find("span#used-product-number").html("<strong>" + r.products_used + "</strong>");

							var product_list = "<ul>";
							$.each(r.products, function(i, v){
								product_list += "<li>"+ parseInt(i+1) +". <a href=\""+v.link+"\" target=\"_blank\" alt=\"Edit\">"+ v.name +"</a></li>";
							});
							product_list += "<ul>";
							confirm.contentDiv.find("span#products-used-attributes-list").html(product_list);
						}
						if (r.option_value_id != null) {
							confirm.contentDiv.find("span#attributes-name0").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
							confirm.contentDiv.find("span#attributes-name1").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
						} else {
							confirm.contentDiv.find("span#attributes-name0").html("<strong>Option: " + r.option_name + "</strong>");
							confirm.contentDiv.find("span#attributes-name1").html("<strong>Option: " + r.option_name + "</strong>");
						}
					}
				}
			});
		});
		$(document).on("click", ".delete_options_values", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");
			href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_options_values').'";
			$.ajax({
				type:   "POST",
				url:    href,
				data:   \'data_id=\' + opt_id,
				dataType: "json",
				success: function(r) {
					if (r.delete_status == "notok") {
						//var products_used = parseInt(r.products_used);
                        var dialog_title="'.$this->pi_getLL('admin_label_warning_this_action_is_not_reversible').'";
                        var dialog_body=\'<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_are_you_sure_want_to_delete_x_attributes'), '<span id="attributes-name0"></span>').'</p>\';
						if (parseInt(r.products_used) > 0) {
							dialog_body=\'<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_there_are_x_products_using_x_attributes_are_you_sure_want_to_delete_it'), '<span id="used-product-number"></span>', '<span id="attributes-name1"></span>').'</p><br/><br/><p style="text-align:left">'.$this->pi_getLL('admin_label_the_products_using_this_attributes_are').':<br/>('.$this->pi_getLL('admin_label_link_will_open_in_new_tab_window').')</p><br/><span id="products-used-attributes-list" style="text-align:left"></span>\';
						}
						var confirm=$.confirm({
                            title: dialog_title,
                            content: dialog_body,
                            closeIcon: true,
                            columnClass: \'col-md-8\',
                            confirmButton: "'.$this->pi_getLL('delete').'",
                            confirm: function(){
                                href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=delete_options_values&force_delete=1').'";
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
                            },
                            cancelButton: "'.$this->pi_getLL('cancel').'",
                            cancel: function() {

                            }
                        });
                        if (parseInt(r.products_used) > 0) {
							// add product list that mapped to attributes
							confirm.contentDiv.find("span#used-product-number").html("<strong>" + r.products_used + "</strong>");

							var product_list = "<ul>";
							$.each(r.products, function(i, v){
								product_list += "<li>"+ parseInt(i+1) +". <a href=\""+v.link+"\" target=\"_blank\" alt=\"Edit\">"+ v.name +"</a></li>";
							});
							product_list += "<ul>";
							confirm.contentDiv.find("span#products-used-attributes-list").html(product_list);
						}
						if (r.option_value_id != null) {
							confirm.contentDiv.find("span#attributes-name0").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
							confirm.contentDiv.find("span#attributes-name1").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
						} else {
							confirm.contentDiv.find("span#attributes-name0").html("<strong>Option: " + r.option_name + "</strong>");
							confirm.contentDiv.find("span#attributes-name1").html("<strong>Option: " + r.option_name + "</strong>");
						}
					}
				}
			});
		});
		var result=$(".attribute_options_sortable").sortable({
			cursor:"move",
			//axis:"y",
			update:function(e, ui) {
				href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_attributes_sortable&tx_multishop_pi1[type]=options').'";
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
				href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax_attributes_options_values&tx_multishop_pi1[admin_ajax_attributes_options_values]=update_attributes_sortable&tx_multishop_pi1[type]=option_values').'";
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
$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>