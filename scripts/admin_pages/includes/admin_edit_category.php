<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
// when editing the current category we must prevent the user to chain the selected category to it's childs.
$skip_ids=array();
if ($_REQUEST['action']=='edit_category') {
	if (is_numeric($this->get['cid']) and $this->get['cid']>0) {
		$str="select categories_id from tx_multishop_categories where parent_id='".$this->get['cid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$skip_ids[]=$row['categories_id'];
		}
	}
	$skip_ids[]=$this->get['cid'];
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
window.onload = function(){
  var text_input = document.getElementById (\'categories_name_0\');
  text_input.focus ();
  text_input.select ();
}
jQuery(document).ready(function($) {
	$(\'.select2BigDropWider\').select2({
		dropdownCssClass: "bigdropWider", // apply css that makes the dropdown taller
		width:\'220px\'
	});
	$(\'#parent_id\').select2({
		dropdownCssClass: "", // apply css that makes the dropdown taller
		width:\'500px\',
		minimumInputLength: 1,
		multiple: false,
		//allowClear: true,
		query: function(query) {
			$.ajax(\''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree').'\', {
				data: {
					q: query.term,
					skip_ids: \''.implode(',', $skip_ids).'\'
				},
				dataType: "json"
			}).done(function(data) {
				//categoriesIdSearchTerm[query.term]=data;
				query.callback({results: data});
			});
		},
		initSelection: function(element, callback) {
			var id=$(element).val();
			if (id!=="") {
				$.ajax(\''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues').'\', {
					data: {
						preselected_id: id,
						skip_ids: \''.implode(',', $skip_ids).'\'
					},
					dataType: "json"
				}).done(function(data) {
					//categoriesIdTerm[data.id]={id: data.id, text: data.text};
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
		escapeMarkup: function (m) { return m; }
	});
});
</script>
';
$update_category_image='';
// hidden filename that is retrieved from the ajax upload
if ($this->post['ajax_categories_image']) {
	$update_category_image=$this->post['ajax_categories_image'];
}
if ($this->post and is_array($_FILES) and count($_FILES)) {
	if ($this->post['categories_name'][0]) {
		$this->post['categories_name'][0]=trim($this->post['categories_name'][0]);
	}
	if (is_array($_FILES) and count($_FILES)) {
		$file=$_FILES['categories_image'];
		if ($file['tmp_name']) {
			$size=getimagesize($file['tmp_name']);
			if ($size[0]>5 and $size[1]>5) {
				$imgtype=mslib_befe::exif_imagetype($file['tmp_name']);
				if ($imgtype) {
					// valid image
					$ext=image_type_to_extension($imgtype, false);
					$i=0;
					$filename=mslib_fe::rewritenamein($this->post['categories_name'][0]).'.'.$ext;
					$folder=mslib_befe::getImagePrefixFolder($filename);
					if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder)) {
						t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder);
					}
					$folder.='/';
					$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
					if (file_exists($target)) {
						do {
							$filename=mslib_fe::rewritenamein($this->post['categories_name'][0]).'-'.$i.'.'.$ext;
							$folder=mslib_befe::getImagePrefixFolder($filename);
							if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder)) {
								t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder);
							}
							$folder.='/';
							$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
							$i++;
						} while (file_exists($target));
					}
					if (move_uploaded_file($file['tmp_name'], $target)) {
						$update_category_image=mslib_befe::resizeCategoryImage($target, $filename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath($this->extKey), 1);
					}
				}
			}
		}
	}
}
if ($this->post) {
	// sometimes the categories startingpoint is not zero. To protect merchants configure a category that is member of itself we reset the parent_id to zero
	if ($this->post['parent_id']==$this->post['cid']) {
		$this->post['parent_id']=0;
	}
	$updateArray=array();
	$updateArray['custom_settings']=$this->post['custom_settings'];
	$updateArray['parent_id']=$this->post['parent_id'];
	if (isset($this->post['hide_in_menu'])) {
		$updateArray['hide_in_menu']=$this->post['hide_in_menu'];
	} else {
		$updateArray['hide_in_menu']=0;
	}
	$updateArray['categories_url']=$this->post['categories_url'];
	$updateArray['status']=$this->post['status'];
	if ($update_category_image) {
		$updateArray['categories_image']=$update_category_image;
	}
	//Options ID
	// disabled for test (19/12/2013)
	/* $option_attributes = "";
	$i_x = 0;
	if (is_array($this->post['products_options']) and count($this->post['products_options'])) {
		foreach ($this->post['products_options'] as $option_id) {
			if ($this->post['html_options'][$i_x] != '0') {
				$option_attributes .= $option_id . ":" . $this->post['html_options'][$i_x] . ";";
			}
			$i_x++;
		}
	}
	$updateArray['option_attributes']=$option_attributes; */
	$updateArray['option_attributes']='';
	if ($_REQUEST['action']=='add_category') {
		$updateArray['page_uid']=$this->showCatalogFromPage;
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$catid=$GLOBALS['TYPO3_DB']->sql_insert_id();
	} else {
		if ($_REQUEST['action']=='edit_category') {
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=\''.$this->post['cid'].'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$catid=$this->post['cid'];
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$products=mslib_fe::getProducts('', $catid);
				if (is_array($products)) {
					foreach ($products as $product) {
						// if the flat database module is enabled we have to sync the changes to the flat table
						mslib_befe::convertProductToFlat($product['products_id']);
					}
				}
			}
		}
	}
	if ($catid) {
		foreach ($this->post['categories_name'] as $key=>$value) {
			$str="select 1 from tx_multishop_categories_description where categories_id='".$catid."' and language_id='".$key."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$updateArray=array();
				$updateArray['categories_name']=$this->post['categories_name'][$key];
				$updateArray['meta_title']=$this->post['meta_title'][$key];
				$updateArray['meta_keywords']=$this->post['meta_keywords'][$key];
				$updateArray['meta_description']=$this->post['meta_description'][$key];
				$updateArray['content']=$this->post['content'][$key];
				$updateArray['content_footer']=$this->post['content_footer'][$key];
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', 'categories_id=\''.$catid.'\' and language_id=\''.$key.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$updateArray=array();
				$updateArray['categories_id']=$catid;
				$updateArray['language_id']=$key;
				$updateArray['categories_name']=$this->post['categories_name'][$key];
				$updateArray['meta_title']=$this->post['meta_title'][$key];
				$updateArray['meta_keywords']=$this->post['meta_keywords'][$key];
				$updateArray['meta_description']=$this->post['meta_description'][$key];
				$updateArray['content']=$this->post['content'][$key];
				$updateArray['content_footer']=$this->post['content_footer'][$key];
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		if (count($this->post['exclude_feed'])) {
			$sql_check="delete from tx_multishop_feeds_excludelist where exclude_id='".addslashes($catid)."' and exclude_type='categories'";
			$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
			foreach ($this->post['exclude_feed'] as $feed_id) {
				$updateArray=array();
				$updateArray['feed_id']=$feed_id;
				$updateArray['exclude_id']=$catid;
				$updateArray['exclude_type']='categories';
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_feeds_excludelist', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		} else {
			$sql_check="delete from tx_multishop_feeds_excludelist where exclude_id='".addslashes($catid)."' and exclude_type='categories'";
			$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
		}
		if (count($this->post['exclude_stock_feed'])) {
			$sql_check="delete from tx_multishop_feeds_stock_excludelist where exclude_id='".addslashes($catid)."' and exclude_type='categories'";
			$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
			foreach ($this->post['exclude_stock_feed'] as $feed_id) {
				$updateArray=array();
				$updateArray['feed_id']=$feed_id;
				$updateArray['exclude_id']=$catid;
				$updateArray['exclude_type']='categories';
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_feeds_stock_excludelist', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		} else {
			$sql_check="delete from tx_multishop_feeds_stock_excludelist where exclude_id='".addslashes($catid)."' and exclude_type='categories'";
			$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
		}
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['saveCategoryPostHook'])) {
			$params=array(
				'catid'=>$catid
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['saveCategoryPostHook'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		if ($this->post['tx_multishop_pi1']['referrer']) {
			header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
			exit();
		} else {
			header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit', 1));
			exit();
		}
	}
} else {
	if ($_REQUEST['action']=='edit_category') {
		$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id='".$_REQUEST['cid']."' and c.categories_id=cd.categories_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$category=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if ($this->get['delete_image'] and is_numeric($this->get['cid'])) {
			if ($category[$this->get['delete_image']]) {
				mslib_befe::deleteCategoryImage($category[$this->get['delete_image']]);
				$updateArray=array();
				$updateArray[$this->get['delete_image']]='';
				$category[$this->get['delete_image']]='';
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=\''.$this->get['cid'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id='".$this->get['cid']."' and c.categories_id=cd.categories_id";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$lngcat[$row['language_id']]=$row;
		}
	}
	if ($category['categories_id'] or $_REQUEST['action']=='add_category') {
		// now parse all the objects in the tmpl file
		if ($this->conf['admin_edit_category_tmpl_path']) {
			$template=$this->cObj->fileResource($this->conf['admin_edit_category_tmpl_path']);
		} else {
			$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_edit_category.tmpl');
		}
		// Extract the subparts from the template
		$subparts=array();
		$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
		//if (!$category['parent_id']) {
		//$category['parent_id']=$this->get['cid'];
		//}
		if ($_REQUEST['action']=='add_category') {
			$heading_page='<div class="main-heading"><h1>'.$this->pi_getLL('add_category').'</h1></div>';
		} else {
			if ($_REQUEST['action']=='edit_category') {
				$level=0;
				$cats=mslib_fe::Crumbar($category['categories_id']);
				$cats=array_reverse($cats);
				$where='';
				if (count($cats)>0) {
					foreach ($cats as $item) {
						$where.="categories_id[".$level."]=".$item['id']."&";
						$level++;
					}
					$where=substr($where, 0, (strlen($where)-1));
				}
				// get all cats to generate multilevel fake url eof
				$details_link=mslib_fe::typolink($this->conf['products_listing_page_pid'], $where.'&tx_multishop_pi1[page_section]=products_listing');
				$heading_page='<div class="main-heading"><h1>'.$this->pi_getLL('edit_category').' (ID: '.$category['categories_id'].')</h1><span class="viewfront"><a href="'.$details_link.'" target="_blank">'.$this->pi_getLL('admin_edit_view_front_category', 'View in front').'</a></span></div>';
			}
		}
		$category_name_block='';
		foreach ($this->languages as $key=>$language) {
			$category_name_block.='
			<div class="account-field" id="msEditCategoryInputName_'.$language['uid'].'">
			<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
			if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
				$category_name_block.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
			}
			$category_name_block.=''.$language['title'].'
			</div>
			<div class="account-field" id="msEditCategoryInputCategoryName_'.$language['uid'].'">
				<label for="categories_name">'.$this->pi_getLL('admin_name').'</label>
				<input spellcheck="true" type="text" class="text" name="categories_name['.$language['uid'].']" id="categories_name_'.$language['uid'].'" value="'.htmlspecialchars($lngcat[$language['uid']]['categories_name']).'">
			</div>
			';
		}
		$category_tree='
		<div class="account-field" id="msEditCategoryInputParent">
			<label for="parent_id">'.$this->pi_getLL('admin_parent').'</label>
			<input type="hidden" name="parent_id" id="parent_id" class="categoriesIdSelect2BigDropWider" value="'.$category['parent_id'].'" />
		</div>';
		//'.mslib_fe::tx_multishop_draw_pull_down_menu('parent_id', mslib_fe::tx_multishop_get_category_tree('', '', $skip_ids), $category['parent_id'],'class="select2BigDropWider"').'
		$categories_image='';
		if ($_REQUEST['action']=='edit_category' and $category['categories_image']) {
			$categories_image.='<img src="'.mslib_befe::getImagePath($category['categories_image'], 'categories', 'normal').'">';
			$categories_image.=' <a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$_REQUEST['cid'].'&action=edit_category&delete_image=categories_image').'" onclick="return confirm(\''.$this->pi_getLL('admin_label_js_are_you_sure').'\')"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/icons/delete2.png" border="0" alt="delete image"></a>';
		}
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['addItemsToTabDetails'])) {
			$params=array(
				'tmpcontent'=>&$tmpcontent,
				'category'=>&$category
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['addItemsToTabDetails'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		$categories_content_block='';
		foreach ($this->languages as $key=>$language) {
			$categories_content_block.='
			<div class="account-field" id="msEditCategoryInputContent_'.$language['uid'].'">
			<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
			if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
				$categories_content_block.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
			}
			$categories_content_block.=''.$language['title'].'
			</div>
			<div class="account-field" id="msEditCategoryInputContentHeader_'.$language['uid'].'">
						<label for="content">'.t3lib_div::strtoupper($this->pi_getLL('content')).' '.t3lib_div::strtoupper($this->pi_getLL('top')).'</label>
						<textarea spellcheck="true" name="content['.$language['uid'].']" id="content['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngcat[$language['uid']]['content']).'</textarea>
					</div>
					<div class="account-field" id="msEditCategoryInputContentFooter_'.$language['uid'].'">
						<label for="content_footer">'.t3lib_div::strtoupper($this->pi_getLL('content')).' '.t3lib_div::strtoupper($this->pi_getLL('bottom')).'</label>
						<textarea spellcheck="true" name="content_footer['.$language['uid'].']" id="content_footer['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngcat[$language['uid']]['content_footer']).'</textarea>
			</div>';
		}
		$categories_meta_block='';
		foreach ($this->languages as $key=>$language) {
			$categories_meta_block.='
			<div class="account-field" id="msEditCategoryInputMeta_'.$language['uid'].'">
			<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
			if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
				$categories_meta_block.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
			}
			$categories_meta_block.=''.$language['title'].'
			</div>
			<div class="account-field" id="msEditCategoryInputMetaTitle_'.$language['uid'].'">
				<label for="meta_title">'.$this->pi_getLL('admin_label_input_meta_title').'</label>
				<input type="text" class="text" name="meta_title['.$language['uid'].']" id="meta_title['.$language['uid'].']" value="'.htmlspecialchars($lngcat[$language['uid']]['meta_title']).'">
			</div>
			<div class="account-field" id="msEditCategoryInputMetaKeywords_'.$language['uid'].'">
				<label for="meta_keywords">'.$this->pi_getLL('admin_label_input_meta_keywords').'</label>
				<input type="text" class="text" name="meta_keywords['.$language['uid'].']" id="meta_keywords['.$language['uid'].']" value="'.htmlspecialchars($lngcat[$language['uid']]['meta_keywords']).'">
			</div>
			<div class="account-field" id="msEditCategoryInputMetaDesc_'.$language['uid'].'">
				<label for="meta_description">'.$this->pi_getLL('admin_label_input_meta_description').'</label>
				<input type="text" class="text" name="meta_description['.$language['uid'].']" id="meta_description['.$language['uid'].']" value="'.htmlspecialchars($lngcat[$language['uid']]['meta_description']).'">
			</div>';
		}
		$subpartArray=array();
		$subpartArray['###VALUE_REFERRER###']='';
		if ($this->post['tx_multishop_pi1']['referrer']) {
			$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
		} else {
			$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
		}
		if ($category['hide_in_menu']==1) {
			$subpartArray['###CATEGORY_HIDE_IN_MENU_CHECKED###']='checked="checked"';
		} else {
			$subpartArray['###CATEGORY_HIDE_IN_MENU_CHECKED###']='';
		}
		$subpartArray['###CATEGORIES_ID0###']=$category['categories_id'];
		$subpartArray['###CATEGORIES_ID1###']=$category['categories_id'];
		$subpartArray['###FORM_POST_URL###']=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$_REQUEST['cid']);
		$subpartArray['###LABEL_BUTTON_CANCEL###']=$this->pi_getLL('cancel');
		$subpartArray['###LINK_BUTTON_CANCEL###']=$subpartArray['###VALUE_REFERRER###'];
		$subpartArray['###LINK_BUTTON_CANCEL_FOOTER###']=$subpartArray['###VALUE_REFERRER###'];
		$subpartArray['###LABEL_BUTTON_SAVE###']=$this->pi_getLL('save');
		$subpartArray['###HEADING_PAGE###']=$heading_page;
		$subpartArray['###INPUT_CATEGORY_NAME_BLOCK###']=$category_name_block;
		$subpartArray['###SELECTBOX_CATEGORY_TREE###']=$category_tree;
		$subpartArray['###LABEL_VISIBILITY###']=$this->pi_getLL('admin_visible');
		$subpartArray['###CATEGORY_STATUS_YES###']=(($category['status'] or $_REQUEST['action']=='add_category') ? 'checked' : '');
		$subpartArray['###LABEL_STATUS_YES###']=$this->pi_getLL('admin_yes');
		$subpartArray['###CATEGORY_STATUS_NO###']=((!$category['status'] and $_REQUEST['action']=='edit_category') ? 'checked' : '');
		$subpartArray['###LABEL_STATUS_NO###']=$this->pi_getLL('admin_no');
		$subpartArray['###LABEL_IMAGE###']=$this->pi_getLL('admin_image');
		$subpartArray['###UPLOAD_IMAGE_URL###']=mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_upload_product_images');
		$subpartArray['###LABEL_CHOOSE_IMAGE###']=addslashes(htmlspecialchars($this->pi_getLL('choose_image')));
		$subpartArray['###CATEGORIES_IMAGE###']=$categories_image;
		$subpartArray['###LABEL_CATEGORIES_EXTERNAL_URL###']=$this->pi_getLL('admin_external_url');
		$subpartArray['###VALUE_CATEGORIES_EXTERNAL_URL###']=htmlspecialchars($category['categories_url']);
		$subpartArray['###EXTRA_DETAILS_FIELDS###']=$extra_fields;
		$subpartArray['###CATEGORIES_CONTENT_BLOCK###']=$categories_content_block;
		$subpartArray['###CATEGORIES_META_BLOCK###']=$categories_meta_block;
		$subpartArray['###LABEL_ADVANCED_SETTINGS###']=$this->pi_getLL('admin_custom_configuration');
		$subpartArray['###VALUE_ADVANCED_SETTINGS###']=htmlspecialchars($category['custom_settings']);
		$subpartArray['###LABEL_BUTTON_CANCEL_FOOTER###']=$this->pi_getLL('cancel');
		$subpartArray['###LABEL_BUTTON_SAVE_FOOTER###']=$this->pi_getLL('save');
		$subpartArray['###CATEGORIES_ID_FOOTER0###']=$category['categories_id'];
		$subpartArray['###PAGE_ACTION###']=$_REQUEST['action'];
		$subpartArray['###CATEGORIES_ID_FOOTER1###']=$category['categories_id'];
		$subpartArray['###LABEL_HIDE_IN_MENU###']=$this->pi_getLL('hide_in_menu', 'Hide in menu');

		$feed_checkbox='';
		$feed_stock_checkbox='';
		$sql_feed='SELECT * from tx_multishop_product_feeds';
		$qry_feed=$GLOBALS['TYPO3_DB']->sql_query($sql_feed);
		while ($rs_feed=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_feed)) {
			if ($_REQUEST['action']=='edit_category') {
				$sql_check="select id from tx_multishop_feeds_excludelist where feed_id='".addslashes($rs_feed['id'])."' and exclude_id='".addslashes($category['categories_id'])."' and exclude_type='categories'";
				$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)) {
					$feed_checkbox.='<input type="checkbox" name="exclude_feed[]" value="'.$rs_feed['id'].'" checked="checked" />&nbsp;'.$rs_feed['name'].'&nbsp;';
				} else {
					$feed_checkbox.='<input type="checkbox" name="exclude_feed[]" value="'.$rs_feed['id'].'" />&nbsp;'.$rs_feed['name'].'&nbsp;';
				}
				$sql_stock_check="select id from tx_multishop_feeds_stock_excludelist where feed_id='".addslashes($rs_feed['id'])."' and exclude_id='".addslashes($category['categories_id'])."' and exclude_type='categories'";
				$qry_stock_check=$GLOBALS['TYPO3_DB']->sql_query($sql_stock_check);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_stock_check)) {
					$feed_stock_checkbox.='<input type="checkbox" name="exclude_stock_feed[]" value="'.$rs_feed['id'].'" checked="checked" />&nbsp;'.$rs_feed['name'].'&nbsp;';
				} else {
					$feed_stock_checkbox.='<input type="checkbox" name="exclude_stock_feed[]" value="'.$rs_feed['id'].'" />&nbsp;'.$rs_feed['name'].'&nbsp;';
				}
			} else {
				$feed_checkbox.='<input type="checkbox" name="exclude_feed[]" value="'.$rs_feed['id'].'" />&nbsp;'.$rs_feed['name'].'&nbsp;';
				$feed_stock_checkbox.='<input type="checkbox" name="exclude_stock_feed[]" value="'.$rs_feed['id'].'" />&nbsp;'.$rs_feed['name'].'&nbsp;';
			}
		}
		$subpartArray['###LABEL_EXCLUDE_FROM_FEED###']=$this->pi_getLL('exclude_from_feeds', 'Exclude from feeds');
		if (empty($feed_checkbox)) {
			$subpartArray['###FEEDS_LIST###']=$this->pi_getLL('admin_label_no_feeds');
		} else {
			$subpartArray['###FEEDS_LIST###']=$feed_checkbox;
		}
		$subpartArray['###LABEL_EXCLUDE_STOCK_FROM_FEED###']=$this->pi_getLL('exclude_stock_from_feeds', 'Exclude stock from feeds');
		if (empty($feed_stock_checkbox)) {
			$subpartArray['###STOCK_FEEDS_LIST###']=$this->pi_getLL('admin_label_no_feeds');
		} else {
			$subpartArray['###STOCK_FEEDS_LIST###']=$feed_stock_checkbox;
		}
		$subpartArray['###ADMIN_LABEL_DROP_FILES_HERE_TO_UPLOAD###']=$this->pi_getLL('admin_label_drop_files_here_to_upload');
		$subpartArray['###ADMIN_LABEL_TABS_DETAILS###']=$this->pi_getLL('admin_label_tabs_details');
		$subpartArray['###ADMIN_LABEL_TABS_CONTENT###']=$this->pi_getLL('admin_label_tabs_content');
		$subpartArray['###ADMIN_LABEL_TABS_META###']=$this->pi_getLL('admin_label_tabs_meta');
		$subpartArray['###ADMIN_LABEL_TABS_ADVANCED###']=$this->pi_getLL('admin_label_tabs_advanced');
		// plugin marker place holder
		$plugins_extra_tab=array();
		$js_extra=array();
		$plugins_extra_tab['tabs_header']=array();
		$plugins_extra_tab['tabs_content']=array();
		// custom page hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['adminEditCategoryPreProc'])) {
			$params=array(
				'subpartArray'=>&$subpartArray,
				'category'=>&$category,
				'plugins_extra_tab'=>&$plugins_extra_tab,
				'js_extra'=>&$js_extra
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['adminEditCategoryPreProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom page hook that can be controlled by third-party plugin eof
		if (!count($plugins_extra_tab['tabs_header']) && !count($plugins_extra_tab['tabs_content'])) {
			$subpartArray['###LABEL_EXTRA_PLUGIN_TABS###']='';
			$subpartArray['###CONTENT_EXTRA_PLUGIN_TABS###']='';
		} else {
			$subpartArray['###LABEL_EXTRA_PLUGIN_TABS###']=implode("\n", $plugins_extra_tab['tabs_header']);
			$subpartArray['###CONTENT_EXTRA_PLUGIN_TABS###']=implode("\n", $plugins_extra_tab['tabs_content']);
		}
		if (!count($js_extra['functions'])) {
			$subpartArray['###JS_FUNCTIONS_EXTRA###']='';
		} else {
			$subpartArray['###JS_FUNCTIONS_EXTRA###']=implode("\n", $js_extra['functions']);
		}
		if (!count($js_extra['triggers'])) {
			$subpartArray['###JS_TRIGGERS_EXTRA###']='';
		} else {
			$subpartArray['###JS_TRIGGERS_EXTRA###']=implode("\n", $js_extra['triggers']);
		}
		$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
	}
}
?>