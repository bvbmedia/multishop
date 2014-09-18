<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// defining the types
$array=array();
$array['categories_name']='Categories name (product category level)';
$array['categories_content_top']='Categories content top (product category level)';
$array['categories_content_bottom']='Categories content bottom (product category level)';
$array['categories_id']='Categories id';
$array['categories_meta_title']='Categories META title (product category level)';
$array['categories_meta_keywords']='Categories META keywords (product category level)';
$array['categories_meta_description']='Categories META description (product category level)';
for ($i=1; $i<6; $i++) {
	$array['categories_meta_title_'.$i]='Categories META title (level: '.$i.')';
	$array['categories_meta_keywords_'.$i]='Categories META keywords (level: '.$i.')';
	$array['categories_meta_description_'.$i]='Categories META description (level: '.$i.')';
	$array['categories_image_'.$i]='Categories image (level: '.$i.')';
	$array['categories_content_top_'.$i]='Categories content top (level: '.$i.')';
	$array['categories_content_bottom_'.$i]='Categories content bottom (level: '.$i.')';
	$array['categories_name_'.$i]='Categories name (level: '.$i.')';
}
$array['products_id']='Products id';
$array['products_url']='Products link';
$array['products_external_url']='Products external URL';
$array['products_name']='Products name';
$array['products_model']='Products model';
$array['products_shortdescription']='Products shortdescription';
$array['products_description']='Products description';
$array['products_description_encoded']='Products description (HTML encoded)';
$array['products_description_strip_tags']='Products description (plain / stripped tags)';
$array['products_image_50']='Products image (thumbnail 50)';
$array['products_image_100']='Products image (thumbnail 100)';
$array['products_image_200']='Products image (thumbnail 200)';
$array['products_image_normal']='Products image (enlarged)';
$array['products_image_original']='Products image (biggest / original)';
$array['products_ean']='Products EAN code';
$array['products_sku']='Products SKU code';
$array['foreign_products_id']='Foreign products id (imported product feeds unique identifier)';
$array['products_quantity']='Products quantity';
$array['products_old_price']='Products old price (incl. VAT)';
$array['products_old_price_excluding_vat']='Products old price (excl. VAT)';
$array['products_price']='Products price (incl. VAT)';
$array['products_price_excluding_vat']='Products price (excl. VAT)';
$array['product_capital_price']='Products capital price';
$array['products_weight']='Products weight';
$array['products_status']='Products status';
$array['minimum_quantity']='Products minimum quantity';
$array['maximum_quantity']='Products maximum quantity';
$array['order_unit_name']='Products order unit name';
$array['products_vat_rate']='Products VAT rate';
$array['category_link']='Category link';
$array['manufacturers_name']='Manufacturers name';
$array['manufacturers_id']='Manufacturers id';
$array['manufacturers_products_id']='Manufacturers products id';
$array['delivery_time']='Delivery Time';
$array['products_condition']='Products condition';
$array['category_crum_path']='Category crum path';
$array['products_meta_title']='Products meta title';
$array['products_meta_keywords']='Products meta keywords';
$array['products_meta_description']='Products meta description';
$array['custom_field']='Custom field with value';
// attributes
$str="SELECT * FROM `tx_multishop_products_options` where language_id='".$GLOBALS['TSFE']->sys_language_uid."' order by products_options_id asc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$array['attribute_option_name_'.$row['products_options_id']]='Attribute option name: '.$row['products_options_name'].' (values without price)';
	$array['attribute_option_name_'.$row['products_options_id'].'_including_prices']='Attribute option name: '.$row['products_options_name'].' (values with price)';
	$array['attribute_option_name_'.$row['products_options_id'].'_including_prices_including_vat']='Attribute option name: '.$row['products_options_name'].' (values with price incl. VAT)';
}
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['adminProductFeedsColtypesHook'])) {
	$params=array(
		'array'=>&$array
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['adminProductFeedsColtypesHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
asort($array);
if ($_REQUEST['section']=='edit' or $_REQUEST['section']=='add') {
	if ($this->post) {
		$erno=array();
		if (!$this->post['name']) {
			$erno[]='Name is required';
		} else {
			if (!$this->post['feed_type'] and (!is_array($this->post['fields']) || !count($this->post['fields']))) {
				$erno[]='No fields defined';
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
		<div class="main-heading"><h2>Product feed Generator</h2></div>
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
				t3lib_div::callUserFunction($funcRef, $params, $this);
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
				<input id="add_field" name="add_field" type="button" value="'.htmlspecialchars($this->pi_getLL('add_field')).'" class="msadmin_button" />
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
				$content.='</select><input class="delete_field msadmin_button" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div>';
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
				<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.htmlspecialchars($this->pi_getLL('save')).'" class="msadmin_button" /></span>
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
		$content.='</select><input class="delete_field msadmin_button" name="delete_field" type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" /></div></div>\';
				$(\'#product_feed_fields\').append(item);
				$(\'select.msAdminProductsFeedSelectField\').select2({
					width:\'650px\'
				});
			});
			$(document).on("click", ".delete_field", function() {
				jQuery(this).parent().remove();
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
		$content.='<div class="main-heading"><h2>'.htmlspecialchars($this->pi_getLL('product_feeds')).'</h2></div>
		<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">
		<tr>
			<th width="25">'.htmlspecialchars($this->pi_getLL('id')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('name')).'</th>
			<th width="100" nowrap>'.htmlspecialchars($this->pi_getLL('created')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('status')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('download')).'</th>
			<th>'.htmlspecialchars($this->pi_getLL('action')).'</th>
		</tr>
		';
		foreach ($feeds as $feed) {
			$feed['feed_link']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_product_feed&feed_hash='.$feed['code'];
			$feed['feed_link_excel']=$this->FULL_HTTP_URL.'index.php?id='.$this->shop_pid.'&type=2002&tx_multishop_pi1[page_section]=download_product_feed&feed_hash='.$feed['code'].'&format=excel';
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['feedsIterationItem'])) {
				$params=array(
					'feed'=>&$feed
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_product_feeds.php']['feedsIterationItem'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eof
			$content.='
			<tr>
				<td align="right" width="25" nowrap><a href="'.$feed['feed_link'].'" target="_blank">'.htmlspecialchars($feed['id']).'</a></td>
				<td><a href="'.$feed['feed_link'].'" target="_blank">'.htmlspecialchars($feed['name']).'</a></td>
				<td width="100" align="center" nowrap>'.date("Y-m-d", $feed['crdate']).'</td>
				<td width="50">
				';
			if (!$feed['status']) {
				$content.='<span class="admin_status_red" alt="Disable"></span>';
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
			} else {
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
				$content.='<span class="admin_status_green" alt="Enable"></span>';
			}
			$content.='</td>
			<td width="150">
				<a href="'.$feed['feed_link'].'" class="admin_menu">Download feed</a><br />
				<a href="'.$feed['feed_link_excel'].'" class="admin_menu">Download Excel feed</a>
			</td>
			<td width="50">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&section=edit').'" class="admin_menu_edit">edit</a>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&feed_id='.$feed['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="Remove"></a>';
			$content.='
			</td>
			</tr>
			';
		}
		$content.='</table>';
	} else {
		$content.='<h3>'.htmlspecialchars($this->pi_getLL('currently_there_are_no_product_feeds_created')).'</h3>';
	}
	$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&section=add').'" class="msBackendButton continueState arrowRight arrowPosLeft float_right"><span>'.htmlspecialchars($this->pi_getLL('add')).'</span></a>';
}
?>