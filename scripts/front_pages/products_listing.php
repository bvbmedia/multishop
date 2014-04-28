<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']
	);
	$Cache_Lite=new Cache_Lite($options);
	$string=$this->cObj->data['uid'].'_'.$this->HTTP_HOST.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$output_array=$Cache_Lite->get($string)) {
	if ($this->get['p']) {
		$p=$this->get['p'];
	}
	if (is_numeric($this->get['categories_id'])) {
		$parent_id=$this->get['categories_id'];
	} else {
		$parent_id=$this->categoriesStartingPoint;
		$this->get['categories_id']=$this->categoriesStartingPoint;
	}
	$subcats=array();
	// current cat
	$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.categories_id='".$parent_id."' and cd.language_id='".$this->sys_language_uid."' and c.page_uid='".$this->showCatalogFromPage."' and c.categories_id=cd.categories_id";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$current=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	// first check if the meta_title exists
	$output_array=array();
	if ($current['categories_id']) {
		if ($current['custom_settings']) {
			mslib_fe::updateCustomSettings($current['custom_settings']);
		}
		if ($current['meta_title']) {
			$meta_title=$current['meta_title'];
		} else {
			$meta_title=$current['categories_name'];
		}
		if ($current['meta_description']) {
			$meta_description=$current['meta_description'];
		} else {
			$meta_description='';
		}
		if ($current['meta_keywords']) {
			$meta_keywords=$current['meta_keywords'];
		} else {
			$meta_keywords='';
		}
		if (!$this->conf['disableMetatags']) {
			$output_array['meta']['title']='<title>'.htmlspecialchars($meta_title).$this->ms['MODULES']['PAGE_TITLE_DELIMETER'].$this->ms['MODULES']['STORE_NAME'].'</title>';
			if ($meta_description) {
				$output_array['meta']['description']='<meta name="description" content="'.$meta_description.'" />';
			}
			if ($meta_keywords) {
				$output_array['meta']['keywords']='<meta name="keywords" content="'.htmlspecialchars($meta_keywords).'" />';
			}
		}
		// create the meta tags eof
		$subCats=mslib_fe::getSubcatsOnly($parent_id);
		if ($this->ADMIN_USER and $this->get['sort_by']) {
			if (is_array($subCats) and count($subCats)>0) {
				switch ($this->get['sort_by']) {
					case 'alphabet':
						if (is_numeric($parent_id)) {
							$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.parent_id='".$parent_id."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' and c.categories_id=cd.categories_id order by cd.categories_name";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							$counter=0;
							while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
								$updateArray=array();
								$updateArray['sort_order']=$counter;
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id='.$row['categories_id'], $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$counter++;
							}
							$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1 and c.parent_id='".$parent_id."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' order by c.sort_order";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						}
						break;
				}
			}
		}
		foreach ($subCats as $subCat) {
			if (!$subCat['hide_in_menu']) {
				$categories[]=$subCat;
			}
		}
		if (!$p) {
			if ($this->ms['MODULES']['PRODUCTS_LISTING_SPECIALS']) {
				if ($GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar']) {
					$cats=$GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar'];
					if ($this->ms['MODULES']['CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL'] and (count($cats)>$this->ms['MODULES']['CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL'])) {
						$hide_specials_box=1;
					}
				}
				if (!$hide_specials_box) {
					$content.=mslib_fe::SpecialsBox($this->ms['page']); // specials module
				}
			}
		}
		if (is_array($categories) and count($categories)>0) {
			// create the meta tags
			// category listing
			if (strstr($this->ms['MODULES']['CATEGORIES_LISTING_TYPE'], "..")) {
				die('error in categories_listing_type value');
			} else {
				if (strstr($this->ms['MODULES']['CATEGORIES_LISTING_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CATEGORIES_LISTING_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/categories_listing/'.$this->ms['MODULES']['CATEGORIES_LISTING_TYPE'].'.php');
				}
			}
			// category listing eof
			if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
				$content.='
				<script>
				jQuery(document).ready(function($) {
				var result = jQuery("#category_listing").sortable({
				 cursor:     "move",
					//axis:       "y",
					update: function(e, ui) {
						href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=subcatlisting').'";
						jQuery(this).sortable("refresh");
						sorted = jQuery(this).sortable("serialize", "id");
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
				});
				</script>';
			}
		} else {
			if ($this->productsLimit) {
				$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->productsLimit;
			}
			$default_limit_page=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
			if ($this->get['tx_multishop_pi1']['limitsb']) {
				if ($this->get['tx_multishop_pi1']['limitsb'] and $this->get['tx_multishop_pi1']['limitsb']!=$this->cookie['limitsb']) {
					$this->cookie['limitsb']=$this->get['tx_multishop_pi1']['limitsb'];
					$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->cookie['limitsb'];
					$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
					$GLOBALS['TSFE']->storeSessionData();
				}
			}
			if ($this->get['tx_multishop_pi1']['sortbysb']) {
				if ($this->get['tx_multishop_pi1']['sortbysb'] and $this->get['tx_multishop_pi1']['sortbysb']!=$this->cookie['sortbysb']) {
					$this->cookie['sortbysb']=$this->get['tx_multishop_pi1']['sortbysb'];
					$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
					$GLOBALS['TSFE']->storeSessionData();
				}
			} else {
				$this->cookie['sortbysb']='';
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
				$GLOBALS['TSFE']->storeSessionData();
			}
			if ($this->ADMIN_USER) {
				$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=150;
			}
			// product listing
			if (isset($this->cookie['limitsb']) && $this->cookie['limitsb']>0) {
				$limit_per_page=$this->cookie['limitsb'];
				if ($this->ADMIN_USER) {
					$limit_per_page=150;
				}
			} else {
				$limit_per_page=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
			}
			if ($p>0) {
				$offset=(((($p)*$limit_per_page)));
			} else {
				$p=0;
				$offset=0;
			}
			if ($this->ADMIN_USER and $this->get['sort_by']) {
				switch ($this->get['sort_by']) {
					case 'alphabet':
						$str="SELECT c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_status=1 and p.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' and cd.language_id=pd.language_id and p2c.categories_id='".$this->get['categories_id']."' and p.products_id=pd.products_id and p.products_id=p2c.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id order by pd.products_name";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$counter=0;
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
							$updateArray=array();
							$updateArray['sort_order']=$counter;
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'categories_id='.$row['categories_id'].' and products_id='.$row['products_id'], $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							$counter++;
						}
						break;
				}
			}
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p2c.';
			}
			$filter=array();
			$filter[]=$tbl.'categories_id='.$this->get['categories_id'];
			$orderby=array();
			$select=array();
			$where=array();
			$extra_from=array();
			$extra_join=array();
			if (isset($this->cookie['sortbysb']) && !empty($this->cookie['sortbysb']) && isset($this->get['tx_multishop_pi1']['sortbysb']) && !empty($this->get['tx_multishop_pi1']['sortbysb'])) {
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$tbl='pf.';
				} else {
					$tbl='p.';
				}
				switch ($this->cookie['sortbysb']) {
					case 'best_selling_asc':
						$select[]='SUM(op.qty) as order_total_qty';
						$extra_join[]='LEFT JOIN tx_multishop_orders_products op ON '.$tbl.'products_id=op.products_id';
						$orderby[]="order_total_qty asc";
						break;
					case 'best_selling_desc':
						$select[]='SUM(op.qty) as order_total_qty';
						$extra_join[]='LEFT JOIN tx_multishop_orders_products op ON '.$tbl.'products_id=op.products_id';
						$orderby[]="order_total_qty desc";
						break;
					case 'price_asc':
						$orderby[]="final_price asc";
						break;
					case 'price_desc':
						$orderby[]="final_price desc";
						break;
					case 'new_asc':
						$orderby[]=$tbl."products_date_added desc";
						break;
					case 'new_desc':
						$orderby[]=$tbl."products_date_added asc";
						break;
				}
			}
			$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit_per_page, $orderby, array(), $select, $where, 0, $extra_from, array(), 'products_listing', '', 0, 1, $extra_join);
			$products=$pageset['products'];
			// load products listing
			$products_compare=true;
			if (!count($products)) {
				if ($current['content'] and !$p) {
					$hide_no_products_message=1;
					if ($current['content']) {
						$content.=mslib_fe::htmlBox($current['categories_name'], $current['content'], 1);
					} else {
						$show_default_header=1;
					}
				}
				if (!$hide_no_products_message) {
					$content.=$this->pi_getLL('no_products_available');
				}
			} else {
				if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "..")) {
					die('error in PRODUCTS_LISTING_TYPE value');
				} else {
					if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "/")) {
						require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');
					} else {
						require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing/'.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');
					}
				}
				// pagination
				if (!$this->hidePagination and ($pageset['total_rows']>$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])) {
					if (!isset($this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']) || $this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']=='default') {
						require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');
					} else {
						require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination_with_number.php');
					}
				}
				// pagination eof
			}
			// load products listing eof
			if ($current['content_footer']) {
				$content.=mslib_fe::htmlBox('', $current['content_footer'], 2);
			}
		}
	} else {
		$content.=$this->pi_getLL('no_products_available');
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$output_array['content']=$content;
		$Cache_Lite->save(serialize($output_array));
	}
} elseif ($output_array) {
	$output_array=unserialize($output_array);
	$content=$output_array['content'];
}
if (is_array($output_array['meta'])) {
	$GLOBALS['TSFE']->additionalHeaderData=array_merge($GLOBALS['TSFE']->additionalHeaderData, $output_array['meta']);
	unset($output_array);
}
?>