<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$jsSelect2InitialValue=array();
$jsSelect2InitialValue[]='var categoriesIdTerm=[];';
$shopPids=array();
if ($this->conf['enableMultipleShops'] && $this->conf['connectedShopPids'] && $this->ms['MODULES']['ENABLE_CATEGORIES_TO_CATEGORIES']) {
	$shopPids=explode(',', $this->conf['connectedShopPids']);
}
if (count($shopPids) && $this->ms['MODULES']['ENABLE_CATEGORIES_TO_CATEGORIES']) {
	foreach ($shopPids as $shopPid) {
		$jsSelect2InitialValue[]='categoriesIdTerm['.$shopPid.']=[];';
		$jsSelect2InitialValue[]='categoriesIdTerm['.$shopPid.'][0]={id:"0", text:"'.htmlentities($this->pi_getLL('admin_main_category')).'"};';
		if (is_numeric($shopPid)) {
			$pageinfo=mslib_befe::getRecord($shopPid, 'pages', 'uid', array('deleted=0 and hidden=0'));
			if ($pageinfo['uid'] && $this->get['cid']) {
				$category_ep=mslib_fe::getCategoriesToCategories($this->get['cid'], $pageinfo['uid']);
				$categories_ep=explode(',', $category_ep);
				if (is_array($categories_ep) && count($categories_ep)) {
					foreach ($categories_ep as $category_id) {
						$category_id=trim($category_id);
						$cats=mslib_fe::Crumbar($category_id, '', array(), $pageinfo['uid']);
						$cats=array_reverse($cats);
						$catpath=array();
						foreach ($cats as $cat) {
							$catpath[]=$cat['name'];
						}
						if (count($catpath)>0) {
							$jsSelect2InitialValue[]='categoriesIdTerm['.$shopPid.']['.$category_id.']={id:"'.$category_id.'", text:"'.htmlentities(implode(' > ', $catpath), ENT_QUOTES).'"};';
						}
					}
				}
			}
		}
	}
} else {
	$jsSelect2InitialValue[]='categoriesIdTerm['.$this->shop_pid.']=[];';
	$jsSelect2InitialValue[]='categoriesIdTerm['.$this->shop_pid.'][0]={id:"0", text:"'.htmlentities($this->pi_getLL('admin_main_category')).'"};';
	$category_ep=mslib_fe::getCategoriesToCategories($this->get['cid'], $this->shop_pid);
	$categories_ep=explode(',', $category_ep);
	if (is_array($categories_ep) && count($categories_ep)) {
		foreach ($categories_ep as $category_id) {
			$category_id=trim($category_id);
			$cats=mslib_fe::Crumbar($category_id, '', array(), $this->shop_pid);
			$cats=array_reverse($cats);
			$catpath=array();
			foreach ($cats as $cat) {
				$catpath[]=$cat['name'];
			}
			if (count($catpath)>0) {
				$jsSelect2InitialValue[]='categoriesIdTerm['.$this->shop_pid.']['.$category_id.']={id:"'.$category_id.'", text:"'.htmlentities(implode(' > ', $catpath), ENT_QUOTES).'"};';
			}
		}
	}
}
// when editing the current category we must prevent the user to chain the selected category to it's childs.
$skip_ids=array();
if ($this->get['action']=='edit_category') {
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
'.implode("\n", $jsSelect2InitialValue).'
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
		minimumInputLength: 0,
		multiple: false,
		//allowClear: true,
		query: function(query) {
			$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree').'\', {
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
				var split_id=id.split(",");
				var callback_data=[];
				$.each(split_id, function(i, v) {
					if (categoriesIdTerm['.$this->shop_pid.'][v]!==undefined) {
						callback_data[i]=categoriesIdTerm['.$this->shop_pid.'][v];
					}
				});
				if (callback_data.length) {
					callback(callback_data);
				} else {
					$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues').'\', {
						data: {
							preselected_id: id
						},
						dataType: "json"
					}).done(function(data) {
						categoriesIdTerm[data.id]={id: data.id, text: data.text};
						callback(data);
					});
				}
				/*$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues').'\', {
					data: {
						preselected_id: id,
						skip_ids: \''.implode(',', $skip_ids).'\'
					},
					dataType: "json"
				}).done(function(data) {
					//categoriesIdTerm[data.id]={id: data.id, text: data.text};
					callback(data);
				});*/
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
	'.($this->ms['MODULES']['ENABLE_CATEGORIES_TO_CATEGORIES'] ? '
	$(\'#link_categories_id\').select2({
		dropdownCssClass: "", // apply css that makes the dropdown taller
		width:\'500px\',
		minimumInputLength: 0,
		multiple: true,
		//allowClear: true,
		query: function(query) {
			$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree&no_maincat=1').'\', {
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
				var split_id=id.split(",");
				var callback_data=[];
				$.each(split_id, function(i, v) {
					if (categoriesIdTerm['.$this->shop_pid.'][v]!==undefined) {
						callback_data[i]=categoriesIdTerm['.$this->shop_pid.'][v];
					}
				});
				if (callback_data.length) {
					callback(callback_data);
				} else {
					$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues').'\', {
						data: {
							preselected_id: id
						},
						dataType: "json"
					}).done(function(data) {
						categoriesIdTerm[data.id]={id: data.id, text: data.text};
						callback(data);
					});
				}
				/*$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues').'\', {
					data: {
						preselected_id: id,
						skip_ids: \''.implode(',', $skip_ids).'\'
					},
					dataType: "json"
				}).done(function(data) {
					//categoriesIdTerm[data.id]={id: data.id, text: data.text};
					callback(data);
				});*/
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
	' : '').'
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
						\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder);
					}
					$folder.='/';
					$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
					if (file_exists($target)) {
						do {
							$filename=mslib_fe::rewritenamein($this->post['categories_name'][0]).'-'.$i.'.'.$ext;
							$folder=mslib_befe::getImagePrefixFolder($filename);
							if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder)) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder);
							}
							$folder.='/';
							$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
							$i++;
						} while (file_exists($target));
					}
					if (move_uploaded_file($file['tmp_name'], $target)) {
						$update_category_image=mslib_befe::resizeCategoryImage($target, $filename, $this->DOCUMENT_ROOT.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey), 1);
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
	if ($this->ROOTADMIN_USER) {
		$updateArray['custom_settings']=$this->post['custom_settings'];
	}
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
			$updateArray['last_modified']=time();
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
			// clean up the link
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_categories_to_categories', 'categories_id=\''.$catid.'\' and page_uid=\''.$this->showCatalogFromPage.'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// link to other categories
			$catIds=array();
			$reflector_cattree=array();
			if (!empty($this->post['link_categories_id'])) {
				if (strpos($this->post['link_categories_id'], ',')!==false) {
					$tmp_link_categories_id=explode(',', $this->post['link_categories_id']);
					$catIds[$this->showCatalogFromPage]=$tmp_link_categories_id;
					foreach ($tmp_categories_id as $tmp_catid) {
						$reflector_cattree[$tmp_catid]=$catid;
					}
				} else {
					$catIds[$this->showCatalogFromPage][]=$this->post['link_categories_id'];
					$reflector_cattree[$this->post['link_categories_id']]=$catid;
				}
			} else {
				// clean up the link
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_categories', 'related_to=\''.$catid.'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			if ($this->conf['enableMultipleShops'] && is_array($this->post['tx_multishop_pi1']['categories_to_categories']) && count($this->post['tx_multishop_pi1']['categories_to_categories'])) {
				foreach ($this->post['tx_multishop_pi1']['categories_to_categories'] as $page_uid=>$shopRecord) {
					if (in_array($page_uid, $this->post['tx_multishop_pi1']['enableMultipleShops']) && empty($shopRecord)) {
						$tmp_categories_id=array();
						$tmp_categories_id[]=$catid;
						// check if the cat have subcats
						$subcategories_array=array();
						mslib_fe::getSubcatsArray($subcategories_array, '', $catid, $this->showCatalogFromPage);
						if (isset($subcategories_array[$catid])) {
							foreach ($subcategories_array[$catid] as $subcat_data) {
								$subcatid=$subcat_data['id'];
								$tmp_categories_id[]=$subcatid;
								if (isset($subcategories_array[$subcatid])) {
									mslib_fe::extractDeepestCat($tmp_categories_id, $subcategories_array, $subcatid);
								}
							}
						}
						$endpoint_catid=array();
						foreach ($tmp_categories_id as $tmp_category_id) {
							$tmp_catname=mslib_fe::getCategoryName($tmp_category_id);
							if (!empty($tmp_catname)) {
								$foreign_catid=mslib_fe::getCategoryIdByName($tmp_catname, $page_uid);
								if (!$foreign_catid) {
									$foreign_catid=mslib_fe::createExternalShopCategoryTree($tmp_category_id, $page_uid);
								}
								$endpoint_catid[]=$foreign_catid;
								$reflector_cattree[$foreign_catid]=$tmp_category_id;
							}
						}
						$shopRecord=implode(',', $endpoint_catid);
					}
					if (strpos($shopRecord, ',')!==false) {
						$catIds[$page_uid]=explode(',', $shopRecord);
					} else {
						$catIds[$page_uid][]=$shopRecord;
					}
				}
			}
			foreach ($catIds as $page_uid=>$catIdsToAdd) {
				foreach ($catIdsToAdd as $foreign_cat_id) {
					if ($foreign_cat_id>0) {
						if (in_array($page_uid, $this->post['tx_multishop_pi1']['enableMultipleShops']) || $page_uid==$this->showCatalogFromPage) {
							$insertArray=array();
							$insertArray['categories_id']=$reflector_cattree[$foreign_cat_id];
							$insertArray['foreign_categories_id']=$foreign_cat_id;
							$insertArray['page_uid']=$this->showCatalogFromPage;
							$insertArray['foreign_page_uid']=$page_uid;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_to_categories', $insertArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							// link existing product from source to foreign cat id
							$has_products=mslib_fe::getProducts('', $reflector_cattree[$foreign_cat_id]);
							if (count($has_products)) {
								foreach ($has_products as $product_id=>$product_data) {
									$updateArray=array();
									$updateArray['categories_id']=$foreign_cat_id;
									$updateArray['products_id']=$product_id;
									$updateArray['sort_order']=time();
									$updateArray['page_uid']=$page_uid;
									$updateArray['related_to']=$reflector_cattree[$foreign_cat_id];
									// create categories tree linking
									tx_mslib_catalog::linkCategoriesTreeToProduct($product_id, $foreign_cat_id, $updateArray);
								}
							}
						}
					}
				}
			}
		}
	}
	if ($catid) {
		if ($this->ms['MODULES']['ADMIN_CROP_CATEGORIES_IMAGES']) {
			if ($update_category_image) {
				$image_filename=$update_category_image;
				$image_crop_data=mslib_befe::getRecord($image_filename, 'tx_multishop_categories_crop_image_coordinate', 'image_filename', array('categories_id=\'0\''));
				if (is_array($image_crop_data) && $image_crop_data['id']>0) {
					$updateArray=array();
					$updateArray['categories_id']=$catid;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_crop_image_coordinate', 'id=\''.$image_crop_data['id'].'\'', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
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
		// linking for newly created categories
		if ($_REQUEST['action']=='add_category') {
			// link to others
			$foreign_categories=mslib_fe::getForeignCategoriesData($this->post['parent_id'], $this->showCatalogFromPage);
			if (is_array($foreign_categories) && count($foreign_categories)) {
				$this->post['tx_multishop_pi1']['enableMultipleShops'][]=$foreign_categories['page_uid'];
			}
			// link to other categories
			$catIds=array();
			$reflector_cattree=array();
			if (!empty($this->post['link_categories_id'])) {
				if (strpos($this->post['link_categories_id'], ',')!==false) {
					$tmp_link_categories_id=explode(',', $this->post['link_categories_id']);
					$catIds[$this->showCatalogFromPage]=$tmp_link_categories_id;
					foreach ($tmp_categories_id as $tmp_catid) {
						$reflector_cattree[$tmp_catid]=$catid;
					}
				} else {
					$catIds[$this->showCatalogFromPage][]=$this->post['link_categories_id'];
					$reflector_cattree[$this->post['link_categories_id']]=$catid;
				}
			}
			if ($this->conf['enableMultipleShops'] && is_array($this->post['tx_multishop_pi1']['categories_to_categories']) && count($this->post['tx_multishop_pi1']['categories_to_categories'])) {
				foreach ($this->post['tx_multishop_pi1']['categories_to_categories'] as $page_uid=>$shopRecord) {
					if (in_array($page_uid, $this->post['tx_multishop_pi1']['enableMultipleShops']) && empty($shopRecord)) {
						$tmp_categories_id=array();
						$tmp_categories_id[]=$catid;
						// check if the cat have subcats
						$subcategories_array=array();
						mslib_fe::getSubcatsArray($subcategories_array, '', $catid, $this->showCatalogFromPage);
						if (isset($subcategories_array[$catid])) {
							foreach ($subcategories_array[$catid] as $subcat_data) {
								$subcatid=$subcat_data['id'];
								$tmp_categories_id[]=$subcatid;
								if (isset($subcategories_array[$subcatid])) {
									mslib_fe::extractDeepestCat($tmp_categories_id, $subcategories_array, $subcatid);
								}
							}
						}
						print_r($tmp_categories_id);
						$endpoint_catid=array();
						foreach ($tmp_categories_id as $tmp_category_id) {
							$tmp_catname=mslib_fe::getCategoryName($tmp_category_id);
							if (!empty($tmp_catname)) {
								$foreign_catid=mslib_fe::getCategoryIdByName($tmp_catname, $page_uid);
								if (!$foreign_catid) {
									$foreign_catid=mslib_fe::createExternalShopCategoryTree($tmp_category_id, $page_uid);
								}
								$endpoint_catid[]=$foreign_catid;
								$reflector_cattree[$foreign_catid]=$tmp_category_id;
							}
						}
						$shopRecord=implode(',', $endpoint_catid);
					}
					if (strpos($shopRecord, ',')!==false) {
						$catIds[$page_uid]=explode(',', $shopRecord);
					} else {
						$catIds[$page_uid][]=$shopRecord;
					}
				}
			}
			foreach ($catIds as $page_uid=>$catIdsToAdd) {
				foreach ($catIdsToAdd as $foreign_cat_id) {
					if ($foreign_cat_id>0) {
						$insertArray=array();
						$insertArray['categories_id']=$reflector_cattree[$foreign_cat_id];
						$insertArray['foreign_categories_id']=$foreign_cat_id;
						$insertArray['page_uid']=$this->showCatalogFromPage;
						$insertArray['foreign_page_uid']=$page_uid;
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_to_categories', $insertArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						$has_products=mslib_fe::getProducts('', $catid);
						if (count($has_products)) {
							foreach ($has_products as $product_id=>$product_data) {
								$updateArray=array();
								$updateArray['categories_id']=$foreign_cat_id;
								$updateArray['products_id']=$product_id;
								$updateArray['sort_order']=time();
								$updateArray['page_uid']=$page_uid;
								$updateArray['related_to']=$reflector_cattree[$foreign_cat_id];
								// create categories tree linking
								tx_mslib_catalog::linkCategoriesTreeToProduct($product_id, $foreign_cat_id, $updateArray);
							}
						}
					}
				}
			}
		}
		if ($this->ms['MODULES']['DISPLAY_EXCLUDE_FROM_FEED_INPUT']) {
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
		}
		if ($_REQUEST['action']=='edit_category') {
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['updateCategoryPostHook'])) {
				$params=array(
					'categories_id'=>$catid
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['updateCategoryPostHook'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
		} else {
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['insertCategoryPostHook'])) {
				$params=array(
					'categories_id'=>$catid
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['insertCategoryPostHook'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
		}
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['saveCategoryPostHook'])) {
			$params=array(
				'catid'=>$catid
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['saveCategoryPostHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
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
			$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/admin_edit_category.tmpl');
		}
		// Extract the subparts from the template
		$subparts=array();
		$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
		$subparts['advanced_settings_tab_button']=$this->cObj->getSubpart($subparts['template'], '###ADVANCED_SETTINGS_TAB_BUTTON###');
		$subparts['advanced_settings_tab_content']=$this->cObj->getSubpart($subparts['template'], '###ADVANCED_SETTINGS_TAB_CONTENT###');
		//if (!$category['parent_id']) {
		//$category['parent_id']=$this->get['cid'];
		//}
		if ($_REQUEST['action']=='add_category') {
			$heading_page='<div class="panel-heading"><h3>'.$this->pi_getLL('add_category').'</h3></div>';
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
				$heading_page='<div class="panel-heading"><h3>'.$this->pi_getLL('edit_category').' (ID: '.$category['categories_id'].') <span class="viewfront pull-right"><a href="'.$details_link.'" target="_blank">'.$this->pi_getLL('admin_edit_view_front_category', 'View in front').'</a></span></h3></div>';
			}
		}
		$category_name_block='';
		foreach ($this->languages as $key=>$language) {
			$category_name_block.='
			<div class="panel panel-default">
                <div class="panel-heading panel-heading-toggle'.($language['uid']>0 ? ' collapsed' : '').'" data-toggle="collapse" data-target="#msEditCategoryInputName_'.$language['uid'].'">
                    <h3 class="panel-title">
                        <a role="button" data-toggle="collapse" href="#msEditCategoryInputName_'.$language['uid'].'"><i class="fa fa-file-text-o"></i> '.$language['title'].'</a>
                    </h3>
                </div>
                <div id="msEditCategoryInputName_'.$language['uid'].'" class="panel-collapse collapse'.($language['uid']===0 ? ' in' : '').'">
                <div class="panel-body">
			';
			$category_name_block.='
			<div class="form-group" id="msEditCategoryInputCategoryName_'.$language['uid'].'">
				<label class="control-label col-md-2" for="categories_name">'.$this->pi_getLL('admin_name').'</label>
				<div class="col-md-10">
				<input spellcheck="true" type="text" class="form-control text" name="categories_name['.$language['uid'].']" id="categories_name_'.$language['uid'].'" value="'.htmlspecialchars($lngcat[$language['uid']]['categories_name']).'">
				</div>
			</div>
			';
            $category_name_block.='</div></div></div>';
		}
		if ($this->get['action']=='add_category') {
			if (isset($this->get['cid']) && $this->get['cid']>0) {
				$category['parent_id']=$this->get['cid'];
			} else {
				$category['parent_id']=0;
			}
		}
		$category_tree='
		<div class="form-group" id="msEditCategoryInputParent">
			<label for="parent_id" class="control-label col-md-2">'.$this->pi_getLL('admin_parent').'</label>
			<div class="col-md-10">
			<input type="hidden" name="parent_id" id="parent_id" class="categoriesIdSelect2BigDropWider" value="'.$category['parent_id'].'" />
			</div>
		</div>';
		//'.mslib_fe::tx_multishop_draw_pull_down_menu('parent_id', mslib_fe::tx_multishop_get_category_tree('', '', $skip_ids), $category['parent_id'],'class="select2BigDropWider"').'
		$categories_image='<div class="image_action">';
		if ($_REQUEST['action']=='edit_category' and $category['categories_image']) {
			$categories_image.='<img src="'.mslib_befe::getImagePath($category['categories_image'], 'categories', 'normal').'">';
			$categories_image.='<div class="image_tools">';
			if ($this->ms['MODULES']['ADMIN_CROP_CATEGORIES_IMAGES']) {
				$categories_image.=' <a href="#" class="btn btn-primary btn-sm" id="cropEditor" rel="'.$category['categories_image'].'"><i class="fa fa-crop"></i></a>';
			}
			$categories_image.=' <a class="btn btn-danger btn-sm delete_categories_images" rel="\' + filenameServer + \'"><i class="fa fa-trash-o"></i></a>';
			$categories_image.='</div>';
		}
		$categories_image.='</div>';
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['addItemsToTabDetails'])) {
			$params=array(
				'tmpcontent'=>&$tmpcontent,
				'category'=>&$category
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_category.php']['addItemsToTabDetails'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		$categories_content_block='';
		foreach ($this->languages as $key=>$language) {
			$categories_content_block.='
			<div class="panel panel-default toggle_advanced_option">
                <div class="panel-heading panel-heading-toggle'.($language['uid']>0 ? ' collapsed' : '').'" data-toggle="collapse" data-target="#msEditCategoryInputContent_'.$language['uid'].'">
                    <h3 class="panel-title">
                        <a role="button" data-toggle="collapse" href="#msEditCategoryInputContent_'.$language['uid'].'"><i class="fa fa-file-text-o"></i> '.$language['title'].'</a>
                    </h3>
                </div>
                <div id="msEditCategoryInputContent_'.$language['uid'].'" class="panel-collapse collapse'.($language['uid']===0 ? ' in' : '').'">
                <div class="panel-body">
			<div class="form-group" id="msEditCategoryInputContentHeader_'.$language['uid'].'">
						<label class="control-label col-md-2" for="content">'.$this->pi_getLL('content').' '.$this->pi_getLL('top').'</label>
						<div class="col-md-10">
						<textarea spellcheck="true" name="content['.$language['uid'].']" id="content['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngcat[$language['uid']]['content']).'</textarea>
						</div>
					</div>
					<div class="form-group" id="msEditCategoryInputContentFooter_'.$language['uid'].'">
						<label class="control-label col-md-2" for="content_footer">'.$this->pi_getLL('content').' '.$this->pi_getLL('bottom').'</label>
						<div class="col-md-10">
						<textarea spellcheck="true" name="content_footer['.$language['uid'].']" id="content_footer['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngcat[$language['uid']]['content_footer']).'</textarea>
						</div>
			</div>';
            $categories_content_block.='</div></div></div>';
		}
		$categories_meta_block='';
		foreach ($this->languages as $key=>$language) {
			$categories_meta_block.='
			<div class="panel panel-default toggle_advanced_option">
                <div class="panel-heading panel-heading-toggle'.($language['uid']>0 ? ' collapsed' : '').'" data-toggle="collapse" data-target="#msEditCategoryInputMeta_'.$language['uid'].'">
                    <h3 class="panel-title">
                        <a role="button" data-toggle="collapse" href="#msEditCategoryInputMeta_'.$language['uid'].'"><i class="fa fa-file-text-o"></i> '.$language['title'].'</a>
                    </h3>
                </div>
                <div id="msEditCategoryInputMeta_'.$language['uid'].'" class="panel-collapse collapse'.($language['uid']===0 ? ' in' : '').'">
                <div class="panel-body">
			<div class="form-group" id="msEditCategoryInputMetaTitle_'.$language['uid'].'">
				<label class="control-label col-md-2" for="meta_title">'.$this->pi_getLL('admin_label_input_meta_title').'</label>
				<div class="col-md-10">
				<input type="text" class="form-control text" name="meta_title['.$language['uid'].']" id="meta_title['.$language['uid'].']" value="'.htmlspecialchars($lngcat[$language['uid']]['meta_title']).'">
				</div>
			</div>
			<div class="form-group" id="msEditCategoryInputMetaKeywords_'.$language['uid'].'">
				<label class="control-label col-md-2" for="meta_keywords">'.$this->pi_getLL('admin_label_input_meta_keywords').'</label>
				<div class="col-md-10">
				<input type="text" class="form-control text" name="meta_keywords['.$language['uid'].']" id="meta_keywords['.$language['uid'].']" value="'.htmlspecialchars($lngcat[$language['uid']]['meta_keywords']).'">
				</div>
			</div>
			<div class="form-group" id="msEditCategoryInputMetaDesc_'.$language['uid'].'">
				<label class="control-label col-md-2" for="meta_description">'.$this->pi_getLL('admin_label_input_meta_description').'</label>
				<div class="col-md-10">
				<input type="text" class="form-control text" name="meta_description['.$language['uid'].']" id="meta_description['.$language['uid'].']" value="'.htmlspecialchars($lngcat[$language['uid']]['meta_description']).'">
				</div>
			</div>';
            $categories_meta_block.='</div></div></div>';
		}
		// INPUT_CATEGORY_TREE
		$tmpcontent='';
		if ($this->conf['enableMultipleShops'] && $this->ms['MODULES']['ENABLE_CATEGORIES_TO_CATEGORIES']) {
			$shopPids=explode(',', $this->conf['connectedShopPids']);
			$tmpcontent.='<div class="form-group" class="msEditCategoriesInputMultipleShopCategory">
				<label>'.$this->pi_getLL('link_to_categories_in_other_shops').'</label>
				<div class="msAttributesWrapper">';
			foreach ($shopPids as $shopPid) {
				if (is_numeric($shopPid) and $shopPid!=$this->shop_pid) {
					$pageinfo=mslib_befe::getRecord($shopPid, 'pages', 'uid', array('deleted=0 and hidden=0'));
					if ($pageinfo['uid']) {
						$categories_to_categories='';
						$shop_checkbox='';
						$select2_block_visibility=' style="display:none"';
						if ($this->get['action']=='edit_category') {
							$categories_to_categories=mslib_fe::getCategoriesToCategories($this->get['cid'], $pageinfo['uid']);
							if (!empty($categories_to_categories)) {
								$shop_checkbox=' checked="checked"';
								$select2_block_visibility=' style="display:block"';
							}
						}
						$tmpcontent.='<div class="msAttributes">
						<input type="checkbox" class="enableMultipleShopsCheckbox" id="enableMultipleShops_'.$pageinfo['uid'].'" name="tx_multishop_pi1[enableMultipleShops][]" value="'.$pageinfo['uid'].'" rel="'.$pageinfo['uid'].'"'.$shop_checkbox.' />
						<label for="enableMultipleShops_'.$pageinfo['uid'].'">'.$pageinfo['title'].'</label>
						<div class="msEditCategoriesInputMultipleShopCategory" id="msEditCategoriesInputMultipleShopCategory'.$pageinfo['uid'].'"'.$select2_block_visibility.'>
							<input type="hidden" name="tx_multishop_pi1[categories_to_categories]['.$pageinfo['uid'].']" id="enableMultipleShopsTree_'.$pageinfo['uid'].'" class="categoriesIdSelect2BigDropWider" value="'.$categories_to_categories.'" />
						</div>
						</div>';
						$GLOBALS['TSFE']->additionalHeaderData[]='
						<script type="text/javascript">
						jQuery(document).ready(function($) {
							var categoriesIdSearchTerm_'.$pageinfo['uid'].'=[];
							$(\'#enableMultipleShopsTree_'.$pageinfo['uid'].'\').select2({
								dropdownCssClass: "", // apply css that makes the dropdown taller
								width:\'500px\',
								minimumInputLength: 0,
								multiple: true,
								//allowClear: true,
								query: function(query) {
									$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree&tx_multishop_pi1[page_uid]='.$pageinfo['uid']).'&no_maincat=1\', {
										data: {
											q: query.term
										},
										dataType: "json"
									}).done(function(data) {
										categoriesIdSearchTerm_'.$pageinfo['uid'].'[query.term]=data;
										query.callback({results: data});
									});
								},
								initSelection: function(element, callback) {
									var id=$(element).val();
									if (id!=="") {
										var split_id=id.split(",");
										var callback_data=[];
										$.each(split_id, function(i, v) {
											if (categoriesIdTerm['.$pageinfo['uid'].'][v]!==undefined) {
												callback_data[i]=categoriesIdTerm['.$pageinfo['uid'].'][v];
											}
										});
										if (callback_data.length) {
											callback(callback_data);
										} else {
											$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues&tx_multishop_pi1[page_uid]='.$pageinfo['uid']).'\', {
												data: {
													preselected_id: id
												},
												dataType: "json"
											}).done(function(data) {
												categoriesIdTerm['.$pageinfo['uid'].'][data.id]={id: data.id, text: data.text};
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
								escapeMarkup: function (m) { return m; }
							});
						});
						</script>';
					}
				}
			}
			$tmpcontent.='</div></div>';
			$GLOBALS['TSFE']->additionalHeaderData[]='
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(document).on("click", ".enableMultipleShopsCheckbox", function(){
					var page_uid=$(this).attr("rel");
					var block_id="#msEditCategoriesInputMultipleShopCategory" + page_uid;
					if ($(this).prop("checked")) {
						$(block_id).show();
					} else {
						$(block_id).hide();
					}
				});
				/*$(\'.enableMultipleShopsCheckbox:checked\').each(function() {
					$(this).parent().find(\'.msEditProductInputMultipleShopCategory\').css(\'display\',\'block\');
				});*/
			});
			</script>
			';
		}
		$link_to_categories_elem='';
		if ($this->get['action']=='edit_category' && $this->ms['MODULES']['ENABLE_CATEGORIES_TO_CATEGORIES']) {
			$link_categories_id=mslib_fe::getCategoriesToCategories($this->get['cid'], $this->shop_pid);
			$link_to_categories_elem='
			<div class="form-group" id="msEditLinkInputCategory">
            	<label for="link_categories_id" class="control-label col-md-2">'.$this->pi_getLL('admin_link_category').'</label>
            	<div class="col-md-10">
				<input type="hidden" name="link_categories_id" id="link_categories_id" class="categoriesIdSelect2BigDropWider" value="'.$link_categories_id.'" />
				</div>
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

		$subpartArray['###FORM_POST_URL###']=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$_REQUEST['action'].'&cid='.$_REQUEST['cid']);
		$subpartArray['###LABEL_BUTTON_CANCEL###']=$this->pi_getLL('cancel');
		$subpartArray['###LINK_BUTTON_CANCEL###']=$subpartArray['###VALUE_REFERRER###'];
		$subpartArray['###LINK_BUTTON_CANCEL_FOOTER###']=$subpartArray['###VALUE_REFERRER###'];
		$subpartArray['###LABEL_BUTTON_SAVE###']=$this->pi_getLL('save');
		$subpartArray['###HEADING_PAGE###']=$heading_page;
		$subpartArray['###INPUT_CATEGORY_NAME_BLOCK###']=$category_name_block;
		$subpartArray['###SELECTBOX_CATEGORY_TREE###']=$category_tree;
		$subpartArray['###LINK_INPUT_CATEGORY_TREE###']=$link_to_categories_elem;
		$subpartArray['###LINK_TO_CATEGORIES###']=$tmpcontent;
		$subpartArray['###LABEL_VISIBILITY###']=$this->pi_getLL('admin_visible');
		$subpartArray['###CATEGORY_STATUS_YES###']=(($category['status'] or $_REQUEST['action']=='add_category') ? 'checked' : '');
		$subpartArray['###LABEL_STATUS_YES###']=$this->pi_getLL('admin_yes');
		$subpartArray['###CATEGORY_STATUS_NO###']=((!$category['status'] and $_REQUEST['action']=='edit_category') ? 'checked' : '');
		$subpartArray['###LABEL_STATUS_NO###']=$this->pi_getLL('admin_no');
		$subpartArray['###LABEL_IMAGE###']=$this->pi_getLL('admin_image');
		$subpartArray['###UPLOAD_IMAGE_URL###']=mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=admin_upload_product_images');
		$subpartArray['###DELETE_IMAGES_CATEGORIES_ID###']=$category['categories_id'];
		$subpartArray['###LABEL_ARE_YOU_SURE###']=addslashes($this->pi_getLL('admin_label_js_are_you_sure'));
		$subpartArray['###AJAX_URL_DELETE_CATEGORIES_IMAGE###']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=delete_categories_images');
		$subpartArray['###CATEGORIES_IMAGE_CROP_JS###']='';
		if ($this->ms['MODULES']['ADMIN_CROP_CATEGORIES_IMAGES']) {
			$subpartArray['###CATEGORIES_IMAGE_CROP_JS###']='
			var filenameLocationServer = responseJSON[\'fileLocation\'];
			// hide the qq-upload status
			$("#qq-upload-list-ul").hide();
			// display instantly uploaded image
			$(".image_action").empty();
			var new_image=\'<img src="\' + filenameLocationServer + \'" />\';
			new_image+=\'<div class="image_tools">\';
			new_image+=\'<a href="#" class="btn btn-primary btn-sm" id="cropEditor" rel="\' + filenameServer + \'"><i class="fa fa-crop"></i></a> \';
			new_image+=\'<a href="#" class="btn btn-danger btn-sm delete_categories_images" rel="\' + filenameServer + \'"><i class="fa fa-trash-o"></i></a>\';
			new_image+=\'</div>\';
			$(".image_action").html(new_image);';
		}
		$subpartArray['###LABEL_CHOOSE_IMAGE###']=addslashes(htmlspecialchars($this->pi_getLL('choose_image')));
		$subpartArray['###CATEGORIES_IMAGE###']=$categories_image;
		$subpartArray['###LABEL_CATEGORIES_EXTERNAL_URL###']=$this->pi_getLL('admin_external_url');
		$subpartArray['###VALUE_CATEGORIES_EXTERNAL_URL###']=htmlspecialchars($category['categories_url']);
		$subpartArray['###EXTRA_DETAILS_FIELDS###']=$extra_fields;
		$subpartArray['###CATEGORIES_CONTENT_BLOCK###']=$categories_content_block;
		$subpartArray['###CATEGORIES_META_BLOCK###']=$categories_meta_block;
		$subpartArray['###LABEL_BUTTON_CANCEL_FOOTER###']=$this->pi_getLL('cancel');
		$subpartArray['###LABEL_BUTTON_SAVE_FOOTER###']=$this->pi_getLL('save');
		$subpartArray['###CATEGORIES_ID_FOOTER0###']=$category['categories_id'];
		$subpartArray['###PAGE_ACTION###']=$_REQUEST['action'];
		$subpartArray['###CATEGORIES_ID_FOOTER1###']=$category['categories_id'];
		$subpartArray['###LABEL_HIDE_IN_MENU###']=$this->pi_getLL('hide_in_menu', 'Hide in menu');
		if (!$this->ms['MODULES']['DISPLAY_EXCLUDE_FROM_FEED_INPUT']) {
			$subpartArray['###EXCLUDE_FROM_FEED_INPUT###']='';
		} else {
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
		}
		$subpartArray['###ADMIN_LABEL_DROP_FILES_HERE_TO_UPLOAD###']=$this->pi_getLL('admin_label_drop_files_here_to_upload');
		$subpartArray['###ADMIN_LABEL_TABS_DETAILS###']=$this->pi_getLL('admin_label_tabs_details');
		$subpartArray['###ADMIN_LABEL_TABS_CONTENT###']=$this->pi_getLL('admin_label_tabs_content');
		$subpartArray['###ADMIN_LABEL_TABS_META###']=$this->pi_getLL('admin_label_tabs_meta');
		// plugin marker place holder
		$plugins_extra_tab=array();
		$js_extra=array();
		$plugins_extra_tab['tabs_header']=array();
		$plugins_extra_tab['tabs_content']=array();
		// crop images
		if ($this->ms['MODULES']['ADMIN_CROP_CATEGORIES_IMAGES']) {
			$jcrop_js='
<script src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/tapmodo-Jcrop-1902fbc/js/jquery.Jcrop.js"></script>
<script src="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/tapmodo-Jcrop-1902fbc/js/jquery.color.js"></script>
<link rel="stylesheet" href="'.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'js/tapmodo-Jcrop-1902fbc/css/jquery.Jcrop.css" type="text/css" />';
			$GLOBALS['TSFE']->additionalHeaderData[]=$jcrop_js;
			$js_extra['functions'][]='
var jcrop_api;
var bounds, boundx, boundy, scaled;
function activate_jcrop_js(aspecratio, minsize, setselect, truesize) {
	jcrop_api=$(\'#cropbox\').Jcrop({
		onChange: updateCoords,
		onSelect: updateCoords,
		aspectRatio: aspecratio,
		minSize: minsize,
		setSelect: setselect,
		trueSize: truesize,
		boxWidth: 640,
		boxHeight: 480
	},function(){
		jcrop_api = this;
		bounds = jcrop_api.getBounds();
		boundx = bounds[0];
		boundy = bounds[1];
		scaled = jcrop_api.tellScaled();

		var new_scale_x2=minsize[0]==null?50:minsize[0];
		var new_scale_y2=minsize[1]==null?50:minsize[1];
		if (parseInt(minsize[0])>parseInt(scaled.x2)) {
			new_scale_x2=scaled.x2;
		}
		if (parseInt(minsize[1])>parseInt(scaled.y2)) {
			new_scale_y2=scaled.y2;
		}
		$("#default_minsize_settings").val(new_scale_x2 + "," + new_scale_y2);
		jcrop_api.setOptions({
			minSize: [new_scale_x2, new_scale_y2],
			setSelect: [0, 0, new_scale_x2, new_scale_y2],
		});
	});
}
function updateCoords(c) {
	$(\'#jCropX\').val(c.x);
	$(\'#jCropY\').val(c.y);
	$(\'#jCropW\').val(c.w);
	$(\'#jCropH\').val(c.h);
}
function cropEditorDialog(textTitle, textBody) {
    $.confirm({
        title: textTitle,
        content: textBody,
        columnClass: "col-md-12",
        closeIcon: true,
        confirmButton:"'.$this->pi_getLL('close').'",
        confirm: function(){
        },
        cancelButton: "'.$this->pi_getLL('cancel').'",
        cancel: function(){
        }
    });
}';
			$js_extra['triggers'][]='
$(document).on(\'click\', "#cropEditor", function(e) {
	e.preventDefault();
	var image_name=$(this).attr("rel");
	href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=get_images_for_crop&tx_multishop_pi1[crop_section]=categories').'";
	jQuery.ajax({
		type:"POST",
		url:href,
		data: "imagename=" + image_name,
		dataType: "json",
		success: function(r) {
			//do something with the sorted data
			if (r.status=="OK") {
				var image_interface=\'<div id="crop_editor_wrapper">\';
				image_interface+=\'<div id="crop_main_window_editor" align="center"><img src="\' + r.images["enlarged"] + \'" id="cropbox" /></div>\';
				image_interface+=\'<div id="crop_thumb_image_button">\';
				image_interface+=\'<div id="crop_save_btn_wrapper""><span class="msBackendButton continueState"><input type="button" id="crop_save" value="crop & save" /></span></div>\';
				image_interface+=\'<div id="crop_restore_btn_wrapper" style="display:none"><span class="msBackendButton continueState"><input type="button" id="crop_restore" value="restore image" /></span></div>\';
				image_interface+=\'<div id="minsize_settings_btn_wrapper" style="display:none"><label for="remove_minsize"><input type="checkbox" id="remove_minsize" checked="checked" /> Lock minimal size of crop selection</label></div>\';
				image_interface+=\'<div id="aspectratio_settings_btn_wrapper" style="display:none"><label for="remove_aspectratio"><input type="checkbox" id="remove_aspectratio" checked="checked" /> Lock aspect ratio of crop selection</label></div>\';
				image_interface+=\'<input type="hidden" id="jCropImageName" name="tx_multishop_pi1[jCropImageName]" class="jcrop_coords" value="\' + image_name + \'" />\';
				image_interface+=\'<input type="hidden" id="jCropImageSize" name="tx_multishop_pi1[jCropImageSize]" class="jcrop_coords" value="enlarged" />\';
				image_interface+=\'<input type="hidden" id="jCropX" name="tx_multishop_pi1[jCropX]" class="jcrop_coords" value="" />\';
				image_interface+=\'<input type="hidden" id="jCropY" name="tx_multishop_pi1[jCropY]" class="jcrop_coords" value="" />\';
				image_interface+=\'<input type="hidden" id="jCropW" name="tx_multishop_pi1[jCropW]" class="jcrop_coords" value="" />\';
				image_interface+=\'<input type="hidden" id="jCropH" name="tx_multishop_pi1[jCropH]" class="jcrop_coords" value="" />\';
				image_interface+=\'<input type="hidden" id="default_minsize_settings" name="default_minsize_settings" class="jcrop_coords" value="\' + r.minsize["enlarged"] + \'" />\';
				image_interface+=\'<input type="hidden" id="default_aspectratio_settings" name="default_aspectratio_settings" class="jcrop_coords" value="\' + r.aspectratio["enlarged"] + \'" />\';
				image_interface+=\'</div>\';
				image_interface+=\'</div>\';
				cropEditorDialog("Crop image " + image_name + " [enlarged]", image_interface);
				// default for first time loading is enlarged
				if (r.disable_crop_button=="disabled") {
					$("#crop_save_btn_wrapper").hide();
					$("#crop_restore_btn_wrapper").show();
					$("#minsize_settings_btn_wrapper").hide();
					$("#aspectratio_settings_btn_wrapper").hide();
				} else {
					$("#crop_save_btn_wrapper").show();
					$("#crop_restore_btn_wrapper").hide();
					$("#minsize_settings_btn_wrapper").show();
					$("#remove_minsize").prop("checked", true);
					$("#aspectratio_settings_btn_wrapper").show();
					$("#remove_aspectratio").prop("checked", true);
					activate_jcrop_js(r.aspectratio["enlarged"], r.minsize["enlarged"], r.setselect["enlarged"], r.truesize["enlarged"]);
				}
			}
		}
	});
});
$(document).on(\'click\', "#crop_save", function(e) {
	e.preventDefault();
	href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=crop_product_image&tx_multishop_pi1[crop_section]=categories').'";
	var cropall=0;
	if ($("#onecrop_for_all").prop("checked")) {
		cropall=1;
	}
	jQuery.ajax({
		type:"POST",
		url:href,
		data: $(".jcrop_coords").serialize() + "&cid='.(isset($this->get['cid']) && $this->get['cid']>0 ? $this->get['cid'] : '').'",
		dataType: "json",
		success: function(r) {
			//do something with the sorted data
			if (r.status=="OK") {
				var new_image=\'<img src="\' + r.images[$("#jCropImageSize").val()] + \'" id="cropbox"/>\';
				$("#jCropX").val("");
				$("#jCropY").val("");
				$("#jCropW").val("");
				$("#jCropH").val("");
				$("#crop_main_window_editor").empty();
				$("#crop_main_window_editor").html(new_image);
				if (r.disable_crop_button=="disabled") {
					$("#crop_save_btn_wrapper").hide();
					$("#crop_restore_btn_wrapper").show();
					$("#minsize_settings_btn_wrapper").hide();
					$("#aspectratio_settings_btn_wrapper").hide();
				} else {
					$("#crop_save_btn_wrapper").show();
					$("#crop_restore_btn_wrapper").hide();
					$("#minsize_settings_btn_wrapper").show();
					$("#remove_minsize").prop("checked", true);
					$("#aspectratio_settings_btn_wrapper").show();
					$("#remove_aspectratio").prop("checked", true);
					activate_jcrop_js(r.aspectratio[$("#jCropImageSize").val()], r.minsize[$("#jCropImageSize").val()], r.setselect[$("#jCropImageSize").val()], r.truesize[$("#jCropImageSize").val()]);
				}
			}
		}
	});
});
$(document).on(\'click\',"#crop_restore",function(e) {
	e.preventDefault();
	href = "'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=restore_crop_image&tx_multishop_pi1[crop_section]=categories').'";
	var cropall=0;
	if ($("#onecrop_for_all").prop("checked")) {
		cropall=1;
	}
	jQuery.ajax({
		type:"POST",
		url:href,
		data: $(".jcrop_coords").serialize() + "cid='.(isset($this->get['cid']) && $this->get['cid']>0 ? $this->get['cid'] : '').'",
		dataType: "json",
		success: function(r) {
			//do something with the sorted data
			if (r.status=="OK") {
				var new_image=\'<img src="\' + r.images[$("#jCropImageSize").val()] + \'" id="cropbox"/>\';
				$("#jCropX").val("");
				$("#jCropY").val("");
				$("#jCropW").val("");
				$("#jCropH").val("");
				$("#crop_main_window_editor").empty();
				$("#crop_main_window_editor").html(new_image);
				if (r.disable_crop_button=="disabled") {
					$("#crop_save_btn_wrapper").hide();
					$("#crop_restore_btn_wrapper").show();
					$("#minsize_settings_btn_wrapper").hide();
					$("#aspectratio_settings_btn_wrapper").hide();
				} else {
					$("#crop_save_btn_wrapper").show();
					$("#crop_restore_btn_wrapper").hide();
					$("#minsize_settings_btn_wrapper").show();
					$("#remove_minsize").prop("checked", true);
					$("#aspectratio_settings_btn_wrapper").show();
					$("#remove_aspectratio").prop("checked", true);
					activate_jcrop_js(r.aspectratio[$("#jCropImageSize").val()], r.minsize[$("#jCropImageSize").val()], r.setselect[$("#jCropImageSize").val()], r.truesize[$("#jCropImageSize").val()]);
				}
			}
		}
	});
});
$(document).on("change", "#remove_minsize", function(){
	jcrop_api.setOptions(this.checked? {
		minSize: $("#default_minsize_settings").val().split(",")
	}: {
		minSize: [0,0]
	});
	jcrop_api.focus();
});
$(document).on("change", "#remove_aspectratio", function(){
	jcrop_api.setOptions(this.checked? {
		aspectRatio: $("#default_aspectratio_settings").val()
	}: {
		aspectRatio: 0
	});
	jcrop_api.focus();
});
';
		}
		// custom page hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['adminEditCategoryPreProc'])) {
			$params=array(
				'subpartArray'=>&$subpartArray,
				'category'=>&$category,
				'plugins_extra_tab'=>&$plugins_extra_tab,
				'js_extra'=>&$js_extra
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_category.php']['adminEditCategoryPreProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
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
		// advanced settings tab button
		$markerArray=array();
		if (!$this->ROOTADMIN_USER) {
			$markerArray['ADMIN_LABEL_TABS_ADVANCED']='';
			$advanced_settings_tab_button='';
		} else {
			$markerArray['ADMIN_LABEL_TABS_ADVANCED']=$this->pi_getLL('admin_label_tabs_advanced');
			$advanced_settings_tab_button=$this->cObj->substituteMarkerArray($subparts['advanced_settings_tab_button'], $markerArray, '###|###');
		}
		$subpartArray['###ADVANCED_SETTINGS_TAB_BUTTON###']=$advanced_settings_tab_button;
		// advanced settings tab button eol
		// advanced settings tab content
		$markerArray=array();
		if (!$this->ROOTADMIN_USER) {
			$markerArray['LABEL_ADVANCED_SETTINGS']='';
			$markerArray['VALUE_ADVANCED_SETTINGS']='';
			$advanced_settings_tab_content='';
		} else {
			$markerArray['LABEL_ADVANCED_SETTINGS']=$this->pi_getLL('admin_custom_configuration');
			$markerArray['VALUE_ADVANCED_SETTINGS']=htmlspecialchars($category['custom_settings']);
			$advanced_settings_tab_content=$this->cObj->substituteMarkerArray($subparts['advanced_settings_tab_content'], $markerArray, '###|###');
		}
		// advanced settings tab content eol
		$subpartArray['###ADVANCED_SETTINGS_TAB_BUTTON###']=$advanced_settings_tab_button;
		$subpartArray['###ADVANCED_SETTINGS_TAB_CONTENT###']=$advanced_settings_tab_content;
		$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
	}
}
?>