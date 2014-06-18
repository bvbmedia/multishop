<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
mslib_befe::loadLanguages();
$selects=array();
$selects['select']='Selectbox';
$selects['select_multiple']='Selectbox multiple';
$selects['radio']='Radio';
$selects['checkbox']='Checkbox';
$selects['input']='Text input';
$selects['textarea']='Textarea';
$selects['hidden_field']='Hidden field';
$selects['file']='File input';
$selects['divider']='Divider';
if (is_array($this->post['option_names']) and count($this->post['option_names'])) {
	foreach ($this->post['option_names'] as $products_options_id=>$array) {
		foreach ($array as $language_id=>$value) {
			$updateArray=array();
			$updateArray['language_id']=$language_id;
			$updateArray['products_options_id']=$products_options_id;
			$updateArray['products_options_name']=$value;
			$updateArray['listtype']=$this->post['listtype'][$products_options_id];
			$updateArray['required']=$this->post['required'][$products_options_id];
			$updateArray['hide_in_cart']=$this->post['hide_in_cart'][$products_options_id];
			$str="select 1 from tx_multishop_products_options where products_options_id='".$products_options_id."' and language_id='".$language_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$products_options_id.'\' and language_id=\''.$language_id.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			if (isset($this->post['options_groups'][$products_options_id]) && !empty($this->post['options_groups'][$products_options_id])) {
				$updateArray=array();
				$updateArray['attributes_options_groups_id']=$this->post['options_groups'][$products_options_id];
				$updateArray['products_options_id']=$products_options_id;
				$str="select 1 from tx_multishop_attributes_options_groups_to_products_options where products_options_id='".$products_options_id."' and attributes_options_groups_id='".$this->post['options_groups'][$products_options_id]."'";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_attributes_options_groups_to_products_options', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
	}
}
if (is_array($this->post['option_values']) and count($this->post['option_values'])) {
	foreach ($this->post['option_values'] as $products_options_values_id=>$array) {
		foreach ($array as $language_id=>$value) {
			$updateArray=array();
			$updateArray['language_id']=$language_id;
			$updateArray['products_options_values_id']=$products_options_values_id;
			$updateArray['products_options_values_name']=$value;
			$str="select 1 from tx_multishop_products_options_values where products_options_values_id='".$products_options_values_id."' and language_id='".$language_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', 'products_options_values_id=\''.$products_options_values_id.'\' and language_id=\''.$language_id.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
}
$str="select * from tx_multishop_products_options where language_id='0' order by sort_order";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
if ($rows) {
	$content.='
	<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_product_attributes').'" method="post" name="admin_product_attributes">
	<ul class="attribute_options_sortable">';
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$options_group=mslib_fe::buildAttributesOptionsGroupSelectBox($row['products_options_id']);
		if (!empty($options_group)) {
			$options_group='<span class="options_groups">options group: '.$options_group.'</span>';
		} else {
			$options_group='<span class="options_groups">options group: -no groups defined-</span>';
		}
		$content.='
		<li id="options_'.$row['products_options_id'].'">
		<h2><span class="option_id">Option ID: '.$row['products_options_id'].'</span>
		'.$options_group.'
		<span class="listing_type">
		listing type: 
		<select name="listtype['.$row['products_options_id'].']">';
		foreach ($selects as $key=>$value) {
			$content.='<option value="'.$key.'"'.($key==$row['listtype'] ? ' selected' : '').'>'.htmlspecialchars($value).'</option>';
		}
		$content.='</select>
		</span>
		<span class="required">
			<input name="required['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['required'] ? ' checked' : '').'/> '.$this->pi_getLL('required').'
		</span>		
		<span class="hide_in_cart">
			<input name="hide_in_cart['.$row['products_options_id'].']" type="checkbox" value="1"'.($row['hide_in_cart'] ? ' checked' : '').'/> '.$this->pi_getLL('admin_label_dont_include_attribute_values_in_cart').'
		</span>		
		</h2>
		<h3>Option name <input name="option_names['.$row['products_options_id'].'][0]" type="text" value="'.htmlspecialchars($row['products_options_name']).'"  />';
		$value=htmlspecialchars($row2['products_options_values_name']);
		foreach ($this->languages as $key=>$language) {
			if ($key>0) {
				$str3="select products_options_name from tx_multishop_products_options where products_options_id='".$row['products_options_id']."' and language_id='".$key."'";
				$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
				while (($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3))!=false) {
					if ($row3['products_options_name']) {
						$value=htmlspecialchars($row3['products_options_name']);
					}
				}
				$content.=$this->languages[$key]['title'].' <input name="option_names['.$row['products_options_id'].']['.$key.']" type="text" value="'.$value.'"  />';
			}
		}
		$content.='<a href="#" class="delete_options admin_menu_remove" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('delete').'</a>&nbsp;';
		$content.='<a href="#" class="msadmin_button fetch_attributes_values" id="button_label_'.$row['products_options_id'].'" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('show_attributes_values', 'SHOW VALUES').'</a>&nbsp;';
		$content.='<a href="#" class="msadmin_button fetch_options_description" id="button_label_desc_'.$row['products_options_id'].'" rel="'.$row['products_options_id'].'">'.$this->pi_getLL('show_options_description', 'EDIT DESCRIPTION').'</a>';
		$content.='</h3>
		<ul class="attribute_option_values_sortable" rel="'.$row['products_options_id'].'" id="vc_'.$row['products_options_id'].'" style="display:none">';
		/* // now load the related values
		$str2="select * from tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov where povp.products_options_id='".$row['products_options_id']."' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id='0' order by povp.sort_order";
	
		$qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
		while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {	
			$content.='<li id="option_values_'.$row2['products_options_values_id'].'" class="option_values_'.$row['products_options_id'].'_'.$row2['products_options_values_id'].'">Option value <input name="option_values['.$row2['products_options_values_id'].'][0]" type="text" value="'.htmlspecialchars($row2['products_options_values_name']).'"   />';
			$value=htmlspecialchars($row2['products_options_values_name']);																																																				
			foreach ($this->languages as $key => $language) {
				$str3="select products_options_values_name from tx_multishop_products_options_values pov where pov.products_options_values_id='".$row2['products_options_values_id']."' and pov.language_id='".$key."'"; 
				$qry3 = $GLOBALS['TYPO3_DB']->sql_query($str3);
				while (($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3)) != false) {
					if ($row3['products_options_values_name']) {
						$value=htmlspecialchars($row3['products_options_values_name']);			
					}
				}
					
				$content.=$this->languages[$key]['title'].' <input name="option_values['.$row2['products_options_values_id'].']['.$key.']" type="text" value="'.$value.'"   />';
			}
				
			$content.='<a href="#" class="delete_options admin_menu_remove" rel="'.$row['products_options_id'].':'.$row2['products_options_values_id'].'">delete</a></li>';
		} */
		$content.='</ul>
		<input type="hidden" name="values_fetched_'.$row['products_options_id'].'" id="values_fetched_'.$row['products_options_id'].'" value="0" />		
		</li>';
	}
	$content.='
		</ul>
		<br /><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="msadmin_button" />
		</form>
		
		<div id="dialog-edit-description" title="'.$this->pi_getLL('admin_label_edit_options_description').'">
	  		<div id="description_editor_header"></div>
			<div id="description_editor"></div>
		</div>
			
		<div id="dialog-edit-options-values-description" title="'.$this->pi_getLL('admin_label_edit_options_values_description').'">
	  		<div id="description_ov_editor_header"></div>
			<div id="description_ov_editor"></div>
		</div>
			
		<div id="dialog-confirm" title="'.$this->pi_getLL('admin_label_warning_this_action_is_not_reversible').'">
	  		<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.sprintf($this->pi_getLL('admin_label_are_you_sure_want_to_delete_x_attributes'), '<span id="attributes-name0"></span>').'</p>
		</div>
			
		<div id="dialog-confirm-force" title="'.$this->pi_getLL('admin_label_warning_this_action_is_not_reversible').'">
	  		<p>
				<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
				'.sprintf($this->pi_getLL('admin_label_there_are_x_products_using_x_attributes_are_you_sure_want_to_delete_it'), '<span id="used-product-number"></span>', '<span id="attributes-name1"></span>').'
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
	  jQuery(document).ready(function($) {
		jQuery("#dialog-edit-description").hide();	
		jQuery("#dialog-edit-options-values-description").hide();	
		jQuery("#dialog-confirm").hide();
		jQuery("#dialog-confirm-force").hide();	
		
		jQuery(".fetch_options_description").click(function(e) {
			e.preventDefault();
			var opt_id = jQuery(this).attr("rel");
			var dialog_box_id = "#dialog-edit-description";
			var dialog_box_content_holder = "#description_editor";
			var dialog_height = "300";
			var dialog_width = "500";
			
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=fetch_options_description').'";
			jQuery.ajax({ 
				type:   "POST", 
				url:    href, 
				data:   \'data_id=\' + opt_id,
				dataType: "json",
				success: function(r) {
					if (r.results) {
						if (r.options_name != "") {
							jQuery("#description_editor_header").html("");
							jQuery("#description_editor_header").html("<strong>Option: " + r.options_name + "</strong>");
						}
						var values_data = "";
					
						if (r.results.length > 1) {
							dialog_height = parseInt(170 * r.results.length);
						}
						
						jQuery.each(r.results, function(i, v){
							values_data += \'<li class="description_content">\';
							values_data += \'<span>\' + v.lang_title + \': </span>\';
							values_data += \'<textarea name="opt_desc[\' + v.option_id + \'][\' + v.lang_id + \']" id="opt_desc_\' + v.option_id + \'_\' + v.lang_id + \'" rows="8" cols="50">\' + v.description + \'</textarea>\';
							values_data += \'</li>\';
						});
									
						if (values_data != "") {
							values_data = "<ul>" + values_data + "</ul>";
							jQuery(dialog_box_content_holder).html(values_data);
							
							jQuery(dialog_box_id).show();	
					
							jQuery(dialog_box_id).dialog({
								resizable: false,
								height: dialog_height,
								width: dialog_width,
								modal: true,
								buttons: {
									"Save":{
										text: "Save",
										class: \'msOkButton msBackendButton continueState arrowRight arrowPosLeft\',
										click: function() {
											href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=save_options_description').'";
											jQuery.ajax({ 
													type:   "POST", 
													url:    href, 
													data:   jQuery("[id^=opt_desc_]").serialize(),
													dataType: "json",
													success: function(s) { 
														
													} 
											});
													
											jQuery("#description_editor_header").html("");
											jQuery( dialog_box_content_holder ).html("");
													
											jQuery( this ).dialog( "close" );
											jQuery( this ).hide();
										}
									},
									"Cancel":{
									text: "Cancel",
									class: \'msCancelButton msBackendButton prevState arrowLeft arrowPosLeft\',
									click: function() {
										jQuery("#description_editor_header").html("");
										jQuery( dialog_box_content_holder ).html("");
												
										jQuery( this ).dialog( "close" );
										jQuery( this ).hide();
									}
									}
								}
							});
						}
					}
				} 
			});
		});												
		$(document).on("click", ".fetch_options_values_description", function(e) {
			e.preventDefault();
			var opt_id = jQuery(this).attr("rel");
			var dialog_box_id = "#dialog-edit-options-values-description";
			var dialog_box_content_holder = "#description_ov_editor";
			var dialog_height = "300";
			var dialog_width = "500";
			
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=fetch_options_values_description').'";
			jQuery.ajax({ 
				type:   "POST", 
				url:    href, 
				data:   \'data_id=\' + opt_id,
				dataType: "json",
				success: function(r) {
					if (r.results) {
						if (r.options_name != "") {
							jQuery("#description_ov_editor_header").html("");
							jQuery("#description_ov_editor_header").html("<strong>" + r.options_name + ": " + r.options_values_name + "</strong>");
						}
						var values_data = "";
					
						if (r.results.length > 1) {
							dialog_height = parseInt(170 * r.results.length);
						}
						
						jQuery.each(r.results, function(i, v){
							values_data += \'<li class="ov_description_content">\';
							values_data += \'<span>\' + v.lang_title + \': </span>\';
							values_data += \'<textarea name="ov_desc[\' + v.pov2po_id + \'][\' + v.lang_id + \']" id="ov_desc_\' + v.pov2po_id + \'_\' + v.lang_id + \'" rows="8" cols="50">\' + v.description + \'</textarea>\';
							values_data += \'</li>\';
						});
									
						if (values_data != "") {
							values_data = "<ul>" + values_data + "</ul>";
							jQuery(dialog_box_content_holder).html(values_data);
							
							jQuery(dialog_box_id).show();	
					
							jQuery(dialog_box_id).dialog({
								resizable: false,
								height: dialog_height,
								width: dialog_width,
								modal: true,
								buttons: {
									"Save": function() {
										href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=save_options_values_description').'";
										jQuery.ajax({ 
												type:   "POST", 
												url:    href, 
												data:   jQuery("[id^=ov_desc_]").serialize(),
												dataType: "json",
												success: function(s) { 
													
												} 
										});
												
										jQuery("#description_ov_editor_header").html("");
										jQuery( dialog_box_content_holder ).html("");
												
										jQuery( this ).dialog( "close" );
										jQuery( this ).hide();
									},
									Cancel: function() {
										jQuery("#description_ov_editor_header").html("");
										jQuery( dialog_box_content_holder ).html("");
												
										jQuery( this ).dialog( "close" );
										jQuery( this ).hide();
									}
								}
							});
						}
					}
				} 
			});
		});
			
		jQuery(".fetch_attributes_values").click(function(e) {
			e.preventDefault();
			var opt_id = jQuery(this).attr("rel");
			var container_id = "#vc_" + opt_id;
			var fetched_id = "#values_fetched_" + opt_id;
			var button_label_id = "#button_label_" + opt_id;
			
			if (jQuery(fetched_id).val() == "0") {
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=fetch_attributes').'";
				jQuery.ajax({ 
					type:   "POST", 
					url:    href, 
					data:   \'data_id=\' + opt_id,
					dataType: "json",
					success: function(r) { 
						if (r.results) {
							var values_data = "";
							
							jQuery.each(r.results, function(i, v){
								values_data += \'<li id="option_values_\' + v.values_id + \'" class="option_values_\' + opt_id + \'_\' + v.values_id + \'">Option value <input name="option_values[\' + v.values_id + \'][0]" type="text" value="\' + v.values_name + \'" />\';
								jQuery.each(v.language, function(x, y){
									values_data += y.lang_title + \' <input name="option_values[\' + v.values_id + \'][\' + y.lang_id + \']" type="text" value="\' + y.lang_values + \'" />\';
								});
							
								values_data += \'<a href="#" class="delete_options admin_menu_remove" rel="\' + opt_id + \':\' + v.values_id + \'">'.$this->pi_getLL('delete').'</a>&nbsp;\';
								values_data += \'<a href="#" class="fetch_options_values_description msadmin_button" rel="\' + v.pov2po_id + \'">'.$this->pi_getLL('show_options_description', 'EDIT VALUES DESCRIPTION').'</a>\';
								values_data += \'</li>\';
							});

							values_data += \'<a href="#" class="msadmin_button hide_attributes_values" rel="\' + opt_id + \'">'.$this->pi_getLL('admin_label_hide_values').'</a>\';
						
							jQuery(container_id).html(values_data);
							jQuery(fetched_id).val("1");
										
							jQuery(container_id).show();
							jQuery(button_label_id).html("'.$this->pi_getLL('admin_label_hide_values').'");
						} else {
							jQuery(button_label_id).html("'.$this->pi_getLL('admin_label_no_values').'");
						}
					} 
				});
				
			} else if ($(fetched_id).val() == "1") {
				if ($(container_id).is(":hidden")) {
					$(container_id).show();
					$(button_label_id).html("'.$this->pi_getLL('admin_label_hide_values').'");
				} else {
					$(container_id).hide();
					$(button_label_id).html("'.$this->pi_getLL('show_attributes_values').'");
				}
			}
		});
		$(document).on("click", ".hide_attributes_values", function(e) {
			e.preventDefault();
			var opt_id = jQuery(this).attr("rel");
			var container_id = "#vc_" + opt_id;
			var button_label_id = "#button_label_" + opt_id;
			if ($(container_id).is(":hidden")) {
				$(container_id).show();
				$(button_label_id).html("'.$this->pi_getLL('admin_label_hide_values').'");
			} else {
				$(container_id).hide();
				$(button_label_id).html("'.$this->pi_getLL('show_attributes_values').'");
			}
		});		
		$(document).on("click", ".delete_options", function(e) {
			e.preventDefault();
			var opt_id = $(this).attr("rel");			
			href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=delete_attributes').'";
			jQuery.ajax({ 
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
								jQuery("#used-product-number").html("<strong>" + r.products_used + "</strong>");
								
								var product_list = "<ul>";
								jQuery.each(r.products, function(i, v){
									product_list += "<li>"+ parseInt(i+1) +". <a href=\""+v.link+"\" target=\"_blank\" alt=\"Edit\">"+ v.name +"</a></li>";
								});
								product_list += "<ul>";
								jQuery("#products-used-attributes-list").html(product_list);
							}
					
							if (r.option_value_id != null) {
								jQuery("#attributes-name0").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
								jQuery("#attributes-name1").html("<strong>" + r.option_name + ": " + r.option_value_name + "</strong>");
							} else {
								jQuery("#attributes-name0").html("<strong>Option: " + r.option_name + "</strong>");
								jQuery("#attributes-name1").html("<strong>Option: " + r.option_name + "</strong>");
							}
					
							jQuery(dialog_box_id).show();	
					
							jQuery(dialog_box_id).dialog({
								resizable: false,
								height:300,
								width:500,
								modal: true,
								buttons: {
									"CONFIRM DELETE": function() {
										href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=delete_attributes&force_delete=1').'";
											jQuery.ajax({ 
													type:   "POST", 
													url:    href, 
													data:   \'data_id=\' + r.data_id,
													dataType: "json",
													success: function(s) { 
														if (s.delete_status == "ok"){
															jQuery(s.delete_id).remove();
														}
													} 
											});
							
							
										jQuery( this ).dialog( "close" );
										jQuery( this ).hide();
									},
									Cancel: function() {
										jQuery( this ).dialog( "close" );
										jQuery( this ).hide();
									}
								}
							});
						}
					} 
			});
		});
		
		var result 	= jQuery(".attribute_options_sortable").sortable({
			cursor:     "move", 
			//axis:       "y", 
			update: function(e, ui) { 
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=update_attributes_sortable&tx_multishop_pi1[type]=options').'";
				jQuery(this).sortable("refresh"); 
				sorted = jQuery(this).sortable("serialize","id"); 
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
		var result2 	= jQuery(".attribute_option_values_sortable").sortable({
			cursor:     "move", 
			//axis:       "y", 
			update: function(e, ui) { 
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=update_attributes_sortable&tx_multishop_pi1[type]=option_values').'";
				jQuery(this).sortable("refresh"); 
				sorted = jQuery(this).sortable("serialize", "id"); 
				var products_options_id=jQuery(this).attr("rel");
				jQuery.ajax({ 
						type:   "POST", 
						url:    href, 
						data:   sorted+"&products_options_id="+products_options_id, 
						success: function(msg) { 
								//do something with the sorted data 
						} 
				}); 
			} 
		});		
	  });
	  </script>';
} else {
	$content.='<h1>'.$this->pi_getLL('admin_label_no_product_attributes_defined_yet').'</h1>';
	$content.=$this->pi_getLL('admin_label_you_can_add_product_attributes_while_creating_and_or_editing_a_product');
}
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>