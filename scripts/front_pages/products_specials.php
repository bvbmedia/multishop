<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (!is_numeric($this->limit)) {
	$this->limit=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
} else {
	$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->limit;
}
$filter=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$select=array();
$extrajoin=array();
if ($contentType=='specials_listing_page') {
	if (is_numeric($this->get['p'])) {
		$p=$this->get['p'];
	}
	if ($p>0) {
		$offset=(((($p)*$this->limit)));
	} else {
		$p=0;
		$offset=0;
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']) {
		$this->ms['MODULES']['CACHE_FRONT_END']=0;
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$this->cacheLifeTime=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
		if (!$this->cacheLifeTime) {
			$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES'];
		}
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$this->cacheLifeTime
		);
		$Cache_Lite=new Cache_Lite($options);
		$string=$this->cObj->data['uid'].'_'.$this->HTTP_HOST.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
	}
	if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string)) {
		// $content.='<div class="main-heading"><h2>'.$this->pi_getLL('specials').'</h2></div>';
		// product search
		if ($this->get['price_filter']) {
			if (strstr($this->get['price_filter'], ">") or strstr($this->get['price_filter'], "<")) {
				$price_filter=$this->get['price_filter'];
			} else {
				if (strstr($this->get['price_filter'], "-")) {
					$array=explode("-", $this->get['price_filter']);
					if (count($array)==2) {
						$price_filter=$array;
					}
				}
			}
		}
		$having=array();
		if (is_array($price_filter)) {
			if (!$this->ms['MODULES']['FLAT_DATABASE'] and (isset($price_filter[0]) and $price_filter[1])) {
				$having[]="(final_price >='".$price_filter[0]."' and final_price <='".$price_filter[1]."')";
			} else {
				if (isset($price_filter[0])) {
					$filter[]="price_filter=".$price_filter[0];
				}
			}
		} elseif ($price_filter) {
			$chars=array();
			$chars[]='>';
			$chars[]='<';
			foreach ($chars as $char) {
				if (strstr($price_filter, $char)) {
					$price_filter=str_replace($char, "", $price_filter);
					if ($char=='<') {
						$having[]="final_price <='".$price_filter."'";
					} else {
						if ($char=='>') {
							$having[]="final_price >='".$price_filter."'";
						}
					}
				}
			}
		}
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$filter[]='sstatus=1';
		} else {
			$filter[]='s.status=1';
		}
		if ($this->section_code) {
			// load specials on custom section of the website
			$str="SELECT specials_id FROM tx_multishop_specials_sections where name ='".addslashes($this->section_code)."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$specials=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$specials[]=$row['specials_id'];
			}
			if (count($specials)) {
				$filter[]='s.specials_id IN ('.implode(",", $specials).')';
			} else {
				$this->no_database_results=1;
			}
		}
		$categoriesStartingPoint=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoriesStartingPoint', 's_advanced');
		if (!$categoriesStartingPoint and is_numeric($this->conf['categoriesStartingPoint'])) {
			$categoriesStartingPoint=$this->conf['categoriesStartingPoint'];
		}
		if ($categoriesStartingPoint>0) {
			$cats=mslib_fe::get_subcategory_ids($categoriesStartingPoint);
			$cats[]=$categoriesStartingPoint;
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p2c.';
			}
			$filter[]='('.$tbl.'categories_id IN ('.implode(",", $cats).'))';
		}
		$pageset=mslib_fe::getProductsPageSet($filter, $offset, $this->limit, $orderby, $having, $select, $where, 0, array(), array(), 'products_specials');
		$products=$pageset['products'];
		if (!$products) {
			// return nothing
			$this->no_database_results=1;
		}
		if ($pageset['total_rows']>0) {
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
			if (!$this->hidePagination and $pageset['total_rows']>$this->limit) {
				if (!isset($this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']) || $this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']=='default') {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination_with_number.php');
				}
			}
			// pagination eof
		} else {
			$content.='<p>'.$this->pi_getLL('no_specials_available_description').'</p>'."\n";
		}
		if ($this->ms['MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($content);
		}
	}
} elseif ($contentType=='specials_box' or $contentType=='single_special' or $contentType=='specials_section') {
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']) {
		$this->ms['MODULES']['CACHE_FRONT_END']=0;
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$this->cacheLifeTime=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
		if (!$this->cacheLifeTime) {
			$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES'];
		}
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$this->cacheLifeTime
		);
		$Cache_Lite=new Cache_Lite($options);
		$string=$this->cObj->data['uid'].'_'.$contentType.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
	}
	if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string)) {
		if ($this->section_code) {
			if ($contentType=='specials_section') {
				$str="SELECT p.products_id FROM tx_multishop_products p, tx_multishop_specials_sections ss, tx_multishop_specials s where p.products_status=1 and ss.name ='".addslashes($this->section_code)."' and ss.specials_id=s.specials_id and p.products_id=s.products_id order by ss.sort_order limit ".$limit;
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
					$this->no_database_results=1;
				} else {
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_ids[]=$row['products_id'];
					}
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$tbl='pf.';
					} else {
						$tbl='p.';
					}
					$filter[]="(".$tbl."products_id IN (".implode(",", $product_ids)."))";
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$extrajoin[]='left join tx_multishop_specials s on s.products_id='.$tbl.'products_id left join tx_multishop_specials_sections ss on s.specials_id=ss.specials_id';
						$orderby[]='ss.sort_order';
					} else {
						$extrajoin[]='left join tx_multishop_specials_sections ss on s.specials_id=ss.specials_id';
						$orderby[]='ss.sort_order';
					}
				}
			} else {
				$str="SELECT p.products_id FROM tx_multishop_products p, tx_multishop_specials_sections ss, tx_multishop_specials s where p.products_status=1 and ss.name ='".addslashes($this->section_code)."' and ss.specials_id=s.specials_id and p.products_id=s.products_id order by rand() limit ".$limit;
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$product_ids=array();
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					$product_ids[]=$row['products_id'];
				}
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$tbl='';
				} else {
					$tbl='p.';
				}
				if (count($product_ids)) {
					$filter[]="(".$tbl."products_id IN (".implode(",", $product_ids)."))";
				} else {
					$this->no_database_results=1;
				}
			}
		} else {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$filter[]='sstatus=1';
			} else {
				$filter[]='s.status=1';
			}
			//$orderby='rand()';
			// the mslib_fe::Crumbar cannot be used to determine the categories status since that method doesnt return any category status
			/* $str="SELECT p2c.categories_id, p.products_id FROM tx_multishop_products p, tx_multishop_specials s, tx_multishop_products_to_categories p2c where p.products_status=1 and p.products_id=s.products_id and p.products_id=p2c.products_id order by rand() limit ".$limit;
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$product_ids=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {

				$all_cat_enabled = true;
				$cats=mslib_fe::Crumbar($row['categories_id']);
				$cats=array_reverse($cats);
				foreach ($cats as $catdata) {
					if (!$catdata['status']) {
						$all_cat_enabled = false;
					}
				}
				if ($all_cat_enabled) {
					$product_ids[]=$row['products_id'];
				}
			} */
			//$str="SELECT p2c.categories_id, p.products_id, c.status as cat_status FROM tx_multishop_products p, tx_multishop_specials s, tx_multishop_products_to_categories p2c, tx_multishop_categories c where p.products_status=1 and p.products_id=s.products_id and p.products_id=p2c.products_id and p2c.categories_id = c.categories_id and c.status = 1 order by rand() limit ".$this->limit;
			$str="SELECT p2c.categories_id, p.products_id, c.status as cat_status FROM tx_multishop_products p, tx_multishop_specials s, tx_multishop_products_to_categories p2c, tx_multishop_categories c where p.products_status=1 and p.products_id=s.products_id and p.products_id=p2c.products_id and p2c.categories_id = c.categories_id and c.status = 1 order by rand()";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$product_ids=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				if ($row['cat_status']) {
					$product_ids[]=$row['products_id'];
				}
			}
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='';
			} else {
				$tbl='p.';
			}
			if (count($product_ids)) {
				$filter[]="(".$tbl."products_id IN (".implode(",", $product_ids)."))";
			} else {
				$this->no_database_results=1;
			}
		}
		if ($this->no_database_results) {
			return '';
		}
		$pageset=mslib_fe::getProductsPageSet($filter, $offset, $this->limit, $orderby, $having, $select, $where, 0, array(), array(), 'products_specials', '', 0, 1, $extrajoin);
		$products=$pageset['products'];
		if (!count($products)) {
			// return nothing
			$this->no_database_results=1;
		} else {
			if (!$this->ms['MODULES']['SPECIALS_SECTION_LISTING_TYPE']) {
				$this->ms['MODULES']['SPECIALS_SECTION_LISTING_TYPE']='default';
			}
			if (strstr($this->ms['MODULES']['SPECIALS_SECTION_LISTING_TYPE'], "..")) {
				die('error in SPECIALS_SECTION_LISTING_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['SPECIALS_SECTION_LISTING_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['SPECIALS_SECTION_LISTING_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/specials_section_listing/'.$this->ms['MODULES']['SPECIALS_SECTION_LISTING_TYPE'].'.php');
				}
			}
		}
		if ($this->ms['MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($content);
		}
	}
} else {
	if (is_numeric($this->get['categories_id'])) {
		$parent_id=$this->get['categories_id'];
	} else {
		$parent_id=0;
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']) {
		$this->ms['MODULES']['CACHE_FRONT_END']=0;
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$this->cacheLifeTime=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
		if (!$this->cacheLifeTime) {
			$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES'];
		}
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$this->cacheLifeTime
		);
		$Cache_Lite=new Cache_Lite($options);
		$string=$this->cObj->data['uid'].'_'.$parent_id.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
	}
	if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string)) {
		$str='';
		$filter=array();
		if ($parent_id) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$filter[]="(categories_id_0='".$parent_id."' or categories_id_1='".$parent_id."' or categories_id_2='".$parent_id."' or categories_id_3='".$parent_id."' or categories_id_4='".$parent_id."' or categories_id_5='".$parent_id."')";
			} else {
				$subcategories_array=array();
				mslib_fe::getSubcats($subcategories_array, $parent_id);
				if (count($subcategories_array)>0) {
					$where='';
					$filter[]="p2c.categories_id IN (".implode(",", $subcategories_array).")";
				} else {
					$filter[]="p2c.categories_id IN (".$parent_id.")";
				}
			}
		} elseif ($this->get['skeyword']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='';
			} else {
				$tbl='pd.';
			}
			$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."%' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%')";
		}
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$tbl='s';
		} else {
			$tbl='s.';
		}
		$filter[]=$tbl."status=1";
		if ($this->get['price_filter']) {
			if (strstr($this->get['price_filter'], ">") or strstr($this->get['price_filter'], "<")) {
				$price_filter=$this->get['price_filter'];
			} else {
				if (strstr($this->get['price_filter'], "-")) {
					$array=explode("-", $this->get['price_filter']);
					if (count($array)==2) {
						$price_filter=$array;
					}
				}
			}
		}
		$having=array();
		if (is_array($price_filter)) {
			if (!$this->ms['MODULES']['FLAT_DATABASE'] and (isset($price_filter[0]) and $price_filter[1])) {
				$having[]="(final_price >='".$price_filter[0]."' and final_price <='".$price_filter[1]."')";
			} else {
				if (isset($price_filter[0])) {
					$filter[]="price_filter=".$price_filter[0];
				}
			}
		} else {
			if ($price_filter) {
				$chars=array();
				$chars[]='>';
				$chars[]='<';
				foreach ($chars as $char) {
					if (strstr($price_filter, $char)) {
						$price_filter=str_replace($char, "", $price_filter);
						if ($char=='<') {
							$having[]="final_price <='".$price_filter."'";
						} else {
							if ($char=='>') {
								$having[]="final_price >='".$price_filter."'";
							}
						}
					}
				}
			}
		}
		// filter by products name
		if (strlen($this->get['skeyword'])>2) {
			$array=explode(" ", $this->get['skeyword']);
			$total=count($array);
			$oldsearch=0;
			foreach ($array as $item) {
				if (strlen($item)<$this->ms['MODULES']['FULLTEXT_SEARCH_MIN_CHARS']) {
					$oldsearch=1;
					break;
				}
			}
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='';
			} else {
				$tbl='pd.';
			}
			if ($oldsearch) {
				if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='%keyword') {
					// do normal indexed search
					$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."')";
				} else {
					if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='keyword%') {
						// do normal indexed search
						$filter[]="(".$tbl."products_name like '".addslashes($this->get['skeyword'])."%')";
					} else {
						// do normal indexed search
						$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."%')";
					}
				}
			} else {
				// do fulltext search
				$tmpstr=addslashes(mslib_befe::ms_implode(', ', $array, '"', '+', true));
				$select[]="MATCH (".$tbl."products_name) AGAINST ('".$tmpstr."' in boolean mode) AS score";
				$where[]="MATCH (".$tbl."products_name) AGAINST ('".$tmpstr."' in boolean mode)";
				$orderby[]='score desc';
			}
		} else {
			$orderby[]='rand()';
		}
		// filter by products name eof
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$orderby[]='final_price_difference desc';
		} else {
			$orderby[]='p.products_date_available desc';
		}
		if ($this->ajax_content) {
			$limit=4;
		} else {
			$limit=25;
		}
		$pageset=mslib_fe::getProductsPageSet($filter, '', $this->limit, $orderby, $having, $select, $where, 0, array(), array(), 'products_specials');
		$products=$pageset['products'];
		$total=count($products);
		if (!$pageset['total_rows']) {
			$this->no_database_results=1;
		} else {
			if (!$this->ms['MODULES']['SPECIALS_LISTING_TYPE']) {
				$this->ms['MODULES']['SPECIALS_LISTING_TYPE']='default';
			}
			if (strstr($this->ms['MODULES']['SPECIALS_LISTING_TYPE'], "..")) {
				die('error in SPECIALS_LISTING_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['SPECIALS_LISTING_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['SPECIALS_LISTING_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/specials_listing/'.$this->ms['MODULES']['SPECIALS_LISTING_TYPE'].'.php');
				}
			}
		}
		if ($this->ms['MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($content);
		}
	}
}
?>