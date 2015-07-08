<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (isset($this->get['download']) && $this->get['download']=='feed' && is_numeric($this->get['feed_id'])) {
	$sql=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
		'tx_multishop_product_feeds ', // FROM ...
		'id= \''.$this->get['feed_id'].'\'', // WHERE...
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$serial_value=array();
		foreach ($data as $key_idx=>$key_val) {
			if ($key_idx!='id' && $key_idx!='page_uid') {
				$serial_value[$key_idx]=$key_val;
			}
		}
		$serial_data='';
		if (count($serial_value)>0) {
			$serial_data=serialize($serial_value);
		}
		$filename='multishop_product_feed_record_'.date('YmdHis').'_'.$this->get['feed_id'].'.txt';
		$filepath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/'.$filename;
		file_put_contents($filepath, $serial_data);
		header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
		header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
		readfile($filepath); //Read and stream the file
		@unlink($filepath);
		exit();
	}
}
if (isset($this->get['upload']) && $this->get['upload']=='feed' && $_FILES) {
	if (!$_FILES['feed_record_file']['error']) {
		$filename=$_FILES['feed_record_file']['name'];
		$target=$this->DOCUMENT_ROOT.'/uploads/tx_multishop'.$filename;
		if (move_uploaded_file($_FILES['feed_record_file']['tmp_name'], $target)) {
			$task_content=file_get_contents($target);
			$unserial_task_data=unserialize($task_content);
			$insertArray=array();
			$insertArray['page_uid']=$this->showCatalogFromPage;
			foreach ($unserial_task_data as $col_name=>$col_val) {
				if ($col_name=='code') {
					$insertArray[$col_name]=md5(uniqid());
				} else if ($col_name=='name' && isset($this->post['new_name']) && !empty($this->post['new_name'])) {
					$insertArray[$col_name]=$this->post['new_name'];
				} else if ($col_name=='crdate') {
					$insertArray[$col_name]=time();
				} else {
					$insertArray[$col_name]=$col_val;
				}
			}
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_product_feeds', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			@unlink($target);
		}
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_product_feeds'));
}
// defining the types
$array=array();
$array['categories_name']=$this->pi_getLL('feed_exporter_fields_label_categories_name_product_category_level');
$array['categories_content_top']=$this->pi_getLL('feed_exporter_fields_label_categories_content_top_product_category_level');
$array['categories_content_bottom']=$this->pi_getLL('feed_exporter_fields_label_categories_content_bottom_product_category_level');
$array['categories_id']=$this->pi_getLL('feed_exporter_fields_label_categories_id');
$array['categories_meta_title']=$this->pi_getLL('feed_exporter_fields_label_categories_meta_title_product_category_level');
$array['categories_meta_keywords']=$this->pi_getLL('feed_exporter_fields_label_categories_meta_keywords_product_category_level');
$array['categories_meta_description']=$this->pi_getLL('feed_exporter_fields_label_categories_meta_description_product_category_level');
for ($i=1; $i<6; $i++) {
	$array['categories_meta_title_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_meta_title_level_x'), $i);
	$array['categories_meta_keywords_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_meta_keywords_level_x'), $i);
	$array['categories_meta_description_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_meta_description_level_x'), $i);
	$array['categories_image_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_image_level_x'), $i);
	$array['categories_content_top_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_content_top_level_x'), $i);
	$array['categories_content_bottom_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_content_bottom_level_x'), $i);
	$array['categories_name_'.$i]=sprintf($this->pi_getLL('feed_exporter_fields_label_categories_name_level_x'), $i);
}
$array['products_id']=$this->pi_getLL('feed_exporter_fields_label_products_id');
$array['products_url']=$this->pi_getLL('feed_exporter_fields_label_products_link');
$array['products_external_url']=$this->pi_getLL('feed_exporter_fields_label_products_external_url');
$array['products_name']=$this->pi_getLL('feed_exporter_fields_label_products_name');
$array['products_model']=$this->pi_getLL('feed_exporter_fields_label_products_model');
$array['products_shortdescription']=$this->pi_getLL('feed_exporter_fields_label_products_shortdescription');
$array['products_description']=$this->pi_getLL('feed_exporter_fields_label_products_description');
$array['products_description_encoded']=$this->pi_getLL('feed_exporter_fields_label_products_description_html_encoded');
$array['products_description_strip_tags']=$this->pi_getLL('feed_exporter_fields_label_products_description_plain_stripped_tags');
for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
	if (!$x) {
		$s='';
	} else {
		$s='_'.($x+1);
	}
	$suffix=' ('.($x+1).')';
	$array['products_image_50'.$s]=$this->pi_getLL('feed_exporter_fields_label_products_image_thumbnail_50').$suffix;
	$array['products_image_100'.$s]=$this->pi_getLL('feed_exporter_fields_label_products_image_thumbnail_100').$suffix;
	$array['products_image_200'.$s]=$this->pi_getLL('feed_exporter_fields_label_products_image_thumbnail_200').$suffix;
	$array['products_image_normal'.$s]=$this->pi_getLL('feed_exporter_fields_label_products_image_enlarged').$suffix;
	$array['products_image_original'.$s]=$this->pi_getLL('feed_exporter_fields_label_products_image_original').$suffix;
}
$array['products_ean']=$this->pi_getLL('feed_exporter_fields_label_products_ean_code');
$array['products_sku']=$this->pi_getLL('feed_exporter_fields_label_products_sku_code');
$array['foreign_products_id']=$this->pi_getLL('feed_exporter_fields_label_foreign_products_id');
$array['products_quantity']=$this->pi_getLL('feed_exporter_fields_label_products_quantity');
$array['products_old_price']=$this->pi_getLL('feed_exporter_fields_label_products_old_price_incl_vat');
$array['products_old_price_excluding_vat']=$this->pi_getLL('feed_exporter_fields_label_products_old_price_excl_vat');
$array['products_price']=$this->pi_getLL('feed_exporter_fields_label_products_price_incl_vat');
$array['products_price_excluding_vat']=$this->pi_getLL('feed_exporter_fields_label_products_price_excl_vat');
$array['product_capital_price']=$this->pi_getLL('feed_exporter_fields_label_products_capital_price');
$array['products_weight']=$this->pi_getLL('feed_exporter_fields_label_products_weight');
$array['products_status']=$this->pi_getLL('feed_exporter_fields_label_products_status');
$array['minimum_quantity']=$this->pi_getLL('feed_exporter_fields_label_products_minimum_quantity');
$array['maximum_quantity']=$this->pi_getLL('feed_exporter_fields_label_products_maximum_quantity');
$array['products_multiplication']=$this->pi_getLL('feed_exporter_fields_label_products_multiplication', 'Multiplication');
$array['order_unit_name']=$this->pi_getLL('feed_exporter_fields_label_products_order_unit_name');
$array['products_vat_rate']=$this->pi_getLL('feed_exporter_fields_label_products_vat_rate');
$array['category_link']=$this->pi_getLL('feed_exporter_fields_label_category_link');
$array['manufacturers_name']=$this->pi_getLL('feed_exporter_fields_label_manufacturers_name');
$array['manufacturers_id']=$this->pi_getLL('feed_exporter_fields_label_manufacturers_id');
$array['manufacturers_products_id']=$this->pi_getLL('feed_exporter_fields_label_manufacturers_products_id');
$array['delivery_time']=$this->pi_getLL('feed_exporter_fields_label_delivery_time');
$array['products_condition']=$this->pi_getLL('feed_exporter_fields_label_products_condition');
$array['category_crum_path']=$this->pi_getLL('feed_exporter_fields_label_category_crum_path');
$array['products_meta_title']=$this->pi_getLL('feed_exporter_fields_label_products_meta_title');
$array['products_meta_keywords']=$this->pi_getLL('feed_exporter_fields_label_products_meta_keywords');
$array['products_meta_description']=$this->pi_getLL('feed_exporter_fields_label_products_meta_description');
if ($this->ms['MODULES']['DISPLAY_MANUFACTURERS_ADVICE_PRICE_INPUT']) {
	$array['manufacturers_advice_price']=$this->pi_getLL('feed_exporter_fields_label_manufacturers_advice_price');
}
$array['custom_field']=$this->pi_getLL('feed_exporter_fields_label_custom_field_with_values');
// attributes
$str="SELECT * FROM `tx_multishop_products_options` where language_id='".$GLOBALS['TSFE']->sys_language_uid."' order by products_options_id asc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$array['attribute_option_name_'.$row['products_options_id']]=sprintf($this->pi_getLL('feed_exporter_fields_label_attribute_option_name_x_values_without_price'), $row['products_options_name']);
	$array['attribute_option_name_'.$row['products_options_id'].'_including_prices']=sprintf($this->pi_getLL('feed_exporter_fields_label_attribute_option_name_x_values_with_price'), $row['products_options_name']);
	$array['attribute_option_name_'.$row['products_options_id'].'_including_prices_including_vat']=sprintf($this->pi_getLL('feed_exporter_fields_label_attribute_option_name_x_values_with_price_incl_vat'), $row['products_options_name']);
}
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['adminProductFeedsColtypesHook'])) {
	$params=array(
		'array'=>&$array
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['adminProductFeedsColtypesHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
uksort($array, "strnatcmp");
//asort($array);
if ($_REQUEST['section']=='edit' or $_REQUEST['section']=='add') {
	if ($this->post) {
		$erno=array();
		if (!$this->post['name']) {
			$erno[]=$this->pi_getLL('feed_exporter_label_error_name_is_required');
		} else {
			if (!$this->post['feed_type'] and (!is_array($this->post['fields']) || !count($this->post['fields']))) {
				$erno[]=$this->pi_getLL('feed_exporter_label_error_no_fields_defined');
			}
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
			// lets save it
			$updateArray=array();
			$updateArray['name']=$this->post['name'];
			$updateArray['utm_source']=$this->post['utm_source'];
			$updateArray['utm_medium']=$this->post['utm_medium'];
			$updateArray['utm_term']=$this->post['utm_term'];
			$updateArray['utm_content']=$this->post['utm_content'];
			$updateArray['utm_campaign']=$this->post['utm_campaign'];
			$updateArray['status']=$this->post['status'];
			$updateArray['include_header']=$this->post['include_header'];
			$updateArray['include_disabled']=$this->post['include_disabled'];
			$updateArray['plain_text']=$this->post['plain_text'];
			$updateArray['delimiter']=$this->post['delimiter'];
			if (isset($this->post['feed_type']) && !empty($this->post['feed_type'])) {
				$updateArray['feed_type']=$this->post['feed_type'];
			} else {
				$updateArray['feed_type']='';
			}
			$updateArray['fields']=serialize($this->post['fields']);
			$updateArray['post_data']=serialize($this->post);
			if (is_numeric($this->post['feed_id'])) {
				// edit
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_product_feeds', 'id=\''.$this->post['feed_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				// insert
				$updateArray['page_uid']=$this->showCatalogFromPage;
				$updateArray['crdate']=time();
				$updateArray['code']=md5(uniqid());
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_product_feeds', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$this->ms['show_main']=1;
		}
	} else {
		if ($_REQUEST['section']=='edit' and is_numeric($this->get['feed_id'])) {
			$str="SELECT * from tx_multishop_product_feeds where id='".$this->get['feed_id']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$feeds=array();
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$this->post=$row;
				$this->post['fields']=unserialize($row['fields']);
				// now also unserialize for the custom field
				$post_data=unserialize($row['post_data']);
				$this->post['fields_headers']=$post_data['fields_headers'];
				$this->post['fields_values']=$post_data['fields_values'];
			}
		}
	}
	if (!$this->ms['show_main']) {
		$content.='
		<div class="bvbBox-heading"><h3>'.$this->pi_getLL('feed_exporter_label_product_feed_generator').'</h3></div>
		<form method="post" action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" id="products_feed_form">
			<div class="account-field">
					<label>'.htmlspecialchars($this->pi_getLL('name')).'</label><input type="text" name="name" value="'.htmlspecialchars($this->post['name']).'" />
			</div>
			<div class="account-field">
					<label>Google utm_source</label><input type="text" name="utm_source" value="'.htmlspecialchars($this->post['utm_source']).'" />
			</div>
			<div class="account-field">
					<label>Google utm_medium</label><input type="text" name="utm_medium" value="'.htmlspecialchars($this->post['utm_medium']).'" />
			</div>
			<div class="account-field">
					<label>Google utm_term</label><input type="text" name="utm_term" value="'.htmlspecialchars($this->post['utm_term']).'" />
			</div>
			<div class="account-field">
					<label>Google utm_content</label><input type="text" name="utm_content" value="'.htmlspecialchars($this->post['utm_content']).'" />
			</div>
			<div class="account-field">
					<label>Google utm_campaign</label><input type="text" name="utm_campaign" value="'.htmlspecialchars($this->post['utm_campaign']).'" />
			</div>';
		$feed_types=array();
		// custom page hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['feedTypesPreHook'])) {
			$params=array(
				'feed_types'=>&$feed_types
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['feedTypesPreHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom page hook that can be controlled by third-party plugin eof
		if (count($feed_types)) {
			$content.='
				<div class="account-field">
						<label>Feed Type</label>
						<select name="feed_type" id="feed_type">
						<option value="">'.htmlspecialchars('Custom').'</option>
				';
			natsort($feed_types);
			foreach ($feed_types as $key=>$label) {
				$content.='<option value="'.$key.'"'.(($this->post['feed_type']==$key) ? ' selected' : '').'>'.htmlspecialchars($label).'</option>'."\n";
			}
			$content.='
				</select>
				</div>
				';
		}
		$content.='
		<div class="account-field hide_pf">
				<label>'.htmlspecialchars($this->pi_getLL('delimiter')).'</label>
				<select name="delimiter">
					<option value="">'.htmlspecialchars($this->pi_getLL('choose')).'</option>
					<option value="dash"'.(($this->post['delimiter']=='dash') ? ' selected' : '').'>dash</option>
					<option value="dotcomma"'.(($this->post['delimiter']=='dotcomma') ? ' selected' : '').'>dotcomma</option>
					<option value="tab"'.(($this->post['delimiter']=='tab') ? ' selected' : '').'>tab</option>
				</select>
		</div>
		<div class="account-field hide_pf">
				<label>'.htmlspecialchars($this->pi_getLL('include_header')).'</label>
				<select name="include_header">
					<option value="">'.htmlspecialchars($this->pi_getLL('no')).'</option>
					<option value="1"'.(($this->post['include_header']=='1') ? ' selected' : '').'>'.htmlspecialchars($this->pi_getLL('yes')).'</option>
				</select>
		</div>
		<div class="account-field hide_pf">
				<label>'.htmlspecialchars($this->pi_getLL('include_disabled_products', 'Include disabled products')).'</label>
				<select name="include_disabled">
					<option value="">'.htmlspecialchars($this->pi_getLL('no')).'</option>
					<option value="1"'.(($this->post['include_disabled']=='1') ? ' selected' : '').'>'.htmlspecialchars($this->pi_getLL('yes')).'</option>
				</select>
		</div>
		<div class="account-field">
				<label>'.htmlspecialchars($this->pi_getLL('status')).'</label>
				<input name="status" type="radio" value="0"'.((isset($this->post['status']) and !$this->post['status']) ? ' checked' : '').' /> '.htmlspecialchars($this->pi_getLL('disabled')).'
				<input name="status" type="radio" value="1"'.((!isset($this->post['status']) or $this->post['status']) ? ' checked' : '').' /> '.htmlspecialchars($this->pi_getLL('enabled')).'
		</div>
		<div class="account-field hide_pf">
			<label>'.htmlspecialchars($this->pi_getLL('plain_text', 'Plain text')).'</label>
			<select name="plain_text">
				<option value="">'.htmlspecialchars($this->pi_getLL('no')).'</option>
				<option value="1"'.(($this->post['plain_text']=='1') ? ' selected' : '').'>'.htmlspecialchars($this->pi_getLL('yes')).'</option>
			</select>
		</div>
		<div class="account-field hide_pf">
			<div class="hr"></div>
		</div>
		<div class="account-field hide_pf">
				<label>'.htmlspecialchars($this->pi_getLL('fields')).'</label>
				<input id="add_field" name="add_field" type="button" value="'.htmlspecialchars($this->pi_getLL('add_field')).'" class="btn btn-success" />
		</div>
		<div id="product_feed_fields">';
		$counter=0;
		if (is_array($this->post['fields']) and count($this->post['fields'])) {
			foreach ($this->post['fields'] as $field) {
				$counter++;
				$content.='<div><div class="account-field"><label>'.htmlspecialchars($this->pi_getLL('type')).'</label><select name="fields['.$counter.']" rel="'.$counter.'" class="msAdminProductsFeedSelectField">';
				foreach ($array as $key=>$option) {
					$content.='<option value="'.$key.'"'.($field==$key ? ' selected' : '').'>'.htmlspecialchars($option).'</option>';
				}
				$content.='</select><input class="delete_field btn btn-success" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div>';
				// custom field
				if ($field=='custom_field') {
					$content.='<div class="account-field"><label></label><span class="key">Key</span><input name="fields_headers['.$counter.']" type="text" value="'.$this->post['fields_headers'][$counter].'" /><span class="value">Value</span><input name="fields_values['.$counter.']" type="text" value="'.$this->post['fields_values'][$counter].'" /></div>';
				}
				$content.='
				</div>';
			}
		}
		$content.='
		</div>
		<div class="account-field">
			<div class="hr"></div>
		</div>
		<div class="account-field">
				<label>&nbsp;</label>
				<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('save')).'" class="btn btn-success" /></span>
		</div>
		<input name="feed_id" type="hidden" value="'.$this->get['feed_id'].'" />
		<input name="section" type="hidden" value="'.$_REQUEST['section'].'" />
		</form>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery("#product_feed_fields").sortable({
				cursor:     "move",
				//axis:       "y",
				update: function(e, ui) {
					jQuery(this).sortable("refresh");
				}
			});
			var counter=\''.$counter.'\';
			$("#feed_type").change(function(){
				var selected=$("#feed_type option:selected").val();
				if (selected) {
					// hide
					$(".hide_pf").hide();
				} else {
					$(".hide_pf").show();
				}
			});
			$(document).on("click", "#add_field", function(event) {
				counter++;
				var item=\'<div><div class="account-field"><label>Type</label><select name="fields[\'+counter+\']" rel="\'+counter+\'" class="msAdminProductsFeedSelectField">';
		foreach ($array as $key=>$option) {
			$content.='<option value="'.$key.'">'.htmlspecialchars($option).'</option>';
		}
		$content.='</select><input class="delete_field btn btn-success" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div></div>\';
				$(\'#product_feed_fields\').append(item);
				$(\'select.msAdminProductsFeedSelectField\').select2({
					width:\'650px\'
				});
			});
			$(document).on("click", ".delete_field", function() {
				jQuery(this).parent().parent().remove();
			});
			$(\'.msAdminProductsFeedSelectField\').select2({
					width:\'650px\'
			});
			$(document).on("change", ".msAdminProductsFeedSelectField", function() {
				var selected=$(this).val();
				var counter=$(this).attr("rel");
				if(selected==\'custom_field\') {
					$(this).next().remove();
					$(this).parent().append(\'<div class="account-field"><label></label><span class="key">Key</span><input name="fields_headers[\'+counter+\']" type="text" /><span class="value">Value</span><input name="fields_values[\'+counter+\']" type="text" /></div>\');
				}
			});
		});
		</script>';
	}
} else {
	$this->ms['show_main']=1;
}
if ($this->ms['show_main']) {
	if (is_numeric($this->get['status']) and is_numeric($this->get['feed_id'])) {
		$updateArray=array();
		$updateArray['status']=$this->get['status'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_product_feeds', 'id=\''.$this->get['feed_id'].'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if (is_numeric($this->get['delete']) and is_numeric($this->get['feed_id'])) {
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_product_feeds', 'id=\''.$this->get['feed_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	// show listing
	$str="SELECT * from tx_multishop_product_feeds order by id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$feeds=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$feeds[]=$row;
	}
	if (is_array($feeds) and count($feeds)) {
		$content.='<div class="panel panel-default"><div class="panel-heading"><h3>'.htmlspecialchars($this->pi_getLL('product_feeds')).'</h3></div>
		<div class="panel-body">
		<table width="100%" border="0" align="center" class="table table-striped table-bordered" id="admin_modules_listing">
		<thead>
		<tr>
			<th class="cellID">'.htmlspecialchars($this->pi_getLL('id')).'</th>
			<th class="cellName">'.htmlspecialchars($this->pi_getLL('name')).'</th>
			<th class="cellDate">'.htmlspecialchars($this->pi_getLL('created')).'</th>
			<th class="cellStatus">'.htmlspecialchars($this->pi_getLL('status')).'</th>
			<th class="cellDownload">'.htmlspecialchars($this->pi_getLL('download')).'</th>
			<th class="cellAction">'.htmlspecialchars($this->pi_getLL('action')).'</th>
			<th class="cellBackup">'.htmlspecialchars($this->pi_getLL('download_feed_record')).'</th>
		</tr>
		</thead>';
		foreach ($feeds as $feed) {
			$feed['feed_link']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_product_feed&feed_hash='.$feed['code'];
			$feed['feed_link_excel']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_product_feed&feed_hash='.$feed['code'].'&format=excel';
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['feedsIterationItem'])) {
				$params=array(
					'feed'=>&$feed
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['feedsIterationItem'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eof
			$content.='
			<tr>
				<td class="cellID"><a href="'.$feed['feed_link'].'" target="_blank">'.htmlspecialchars($feed['id']).'</a></td>
				<td class="cellName"><a href="'.$feed['feed_link'].'" target="_blank">'.htmlspecialchars($feed['name']).'</a></td>
				<td class="cellDate">'.date("Y-m-d", $feed['crdate']).'</td>
				<td class="cellStatus">
				';
			if (!$feed['status']) {
				$content.='<span class="admin_status_red" alt="Disable"></span>';
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
			} else {
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
				$content.='<span class="admin_status_green" alt="Enable"></span>';
			}
			$content.='</td>
			<td class="cellDownload">
				<a href="'.$feed['feed_link'].'" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Download feed</a>
				<a href="'.$feed['feed_link_excel'].'" class="btn btn-success btn-sm"><i class="fa fa-download"></i>Download Excel feed</a>
			</td>
			<td class="cellAction">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&section=edit').'" class="btn btn-primary btn-sm admin_menu_edit"><i class="fa fa-pencil fa-fw"></i></a>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="Remove"><i class="fa fa-trash-o fa-fw"></i></a>';
			$content.='
			</td>
			<td class="cellBackup">
				<a href="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_product_feeds&download=feed&feed_id='.$feed['id']).'" class="btn btn-success btn-sm">'.$this->pi_getLL('download_feed_record').'</a>
			</td>
			</tr>';
		}
		$content.='</table></div></div>';
	} else {
		$content.='<h3>'.htmlspecialchars($this->pi_getLL('currently_there_are_no_product_feeds_created')).'</h3></div></div>';
	}
	$content.='<div class="panel panel-default"><div class="panel-heading"><h3>'.$this->pi_getLL('import_feed_record').'</h3></div>
	<div class="panel-body">
	<fieldset id="scheduled_import_jobs_form">
		<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_product_feeds&upload=feed').'" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="form-horizontal blockSubmitForm">
			<div class="form-group">
				<label for="new_name" class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
				<div class="col-md-10">
				    <input name="new_name" type="text" value="" class="form-control" />
				</div>
			</div>
			<div class="form-group">
				<label for="upload_feed_file" class="control-label col-md-2">'.$this->pi_getLL('file').'</label>
				<div class="col-md-10">
				    <input type="file" name="feed_record_file" class="form-control">
				</div>
			</div>
			<div class="form-group">
			    <div class="col-md-offset-2 col-md-10">
			        <input type="submit" name="upload_feed_file" class="submit btn btn-success" id="upload_feed_file" value="upload">
			    </div>
            </div>
		</form>
	</fieldset>';
	$content.='<hr><a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&section=add').'" class="msBackendButton continueState arrowRight arrowPosLeft float_right"><span>'.htmlspecialchars($this->pi_getLL('add')).'</span></a></div></div>';
}
?>