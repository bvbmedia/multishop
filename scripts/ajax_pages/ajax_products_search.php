<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
header("Content-Type:application/json; charset=UTF-8");
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
	);
	$Cache_Lite=new Cache_Lite($options);
	$string=md5('ajax_products_search_'.$this->shop_pid.'_'.$_REQUEST['q'].'_'.$this->get['page']);
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
	if ($_REQUEST['q']) {
		$this->get['q']=$_REQUEST['q'];
		$this->get['q']=trim($this->get['q']);
		$this->get['q']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['q'], $GLOBALS['TSFE']->metaCharset);
		$this->get['q']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['q'], true);
		$this->get['q']=mslib_fe::RemoveXSS($this->get['q']);
	}
	if ($_REQUEST['q'] and strlen($_REQUEST['q'])<1) {
		exit();
	}
	$p=!$this->get['page'] ? 0 : $this->get['page'];
	if (!is_numeric($p)) {
		$p=0;
	}
	$limit=4;
	$offset=$p*$limit;
	// product search
	$filter=array();
	$having=array();
	$match=array();
	$orderby=array();
	$where=array();
	$orderby=array();
	$select=array();
	if (strlen($this->get['q'])>0) {
		$extra_columns='';
		if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_ID']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$extra_columns.=" or ".$tbl."products_id ='".addslashes($this->get['q'])."'";
		}
		if ($this->ms['MODULES']['SEARCH_ALSO_IN_VENDOR_CODE']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$extra_columns.=" or ".$tbl."vendor_code like '%".addslashes($this->get['q'])."%'";
		}
		if ($this->ms['MODULES']['SEARCH_ALSO_IN_EAN_CODE']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$extra_columns.=" or ".$tbl."ean_code like '%".addslashes($this->get['q'])."%'";
		}
		if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_MODEL']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$extra_columns.=" or ".$tbl."products_model like '%".addslashes($this->get['q'])."%'";
		}
		if ($this->ms['MODULES']['SEARCH_ALSO_IN_SKU_CODE']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$extra_columns.=" or ".$tbl."sku_code like '%".addslashes($this->get['q'])."%'";
		}
		if ($this->ms['MODULES']['SEARCH_ALSO_IN_CATEGORIES_NAME']) {
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='cd.';
			}
			$extra_columns.=" or ".$tbl."categories_name like '%".addslashes($this->get['q'])."%'";
		}
		$array=explode(" ", $this->get['q']);
		$total=count($array);
		$oldsearch=0;
		if (!$this->ms['MODULES']['ENABLE_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH']) {
			$oldsearch=1;
		} else {
			foreach ($array as $item) {
//				if (strlen($item) < 4)
				if (strlen($item)<$this->ms['MODULES']['FULLTEXT_SEARCH_MIN_CHARS']) {
					$oldsearch=1;
					break;
				}
			}
		}
		if ($this->get['tx_multishop_pi1']['type']=='edit_order') {
			// admin edit order must not use match
			$oldsearch=1;
		}
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$tbl='pf.';
		} else {
			$tbl='pd.';
		}
		if ($oldsearch) {
			if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='%keyword') {
				// do normal indexed search
				if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_TITLE']) {
					$extra_columns.=" or ".$tbl."products_meta_title like '%".addslashes($this->get['q'])."'";
				}
				if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_KEYWORDS']) {
					$extra_columns.=" or ".$tbl."products_meta_keywords like '%".addslashes($this->get['q'])."'";
				}
				if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_DESCRIPTION']) {
					$extra_columns.=" or ".$tbl."products_meta_description like '%".addslashes($this->get['q'])."'";
				}
				if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
					$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."' or ".$tbl."products_description like '%".addslashes($this->get['q'])."%' ".$extra_columns.")";
				} else {
					$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."' ".$extra_columns.")";
				}
			} else {
				if ($this->ms['MODULES']['REGULAR_SEARCH_MODE']=='keyword%') {
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_TITLE']) {
						$extra_columns.=" or ".$tbl."products_meta_title like '".addslashes($this->get['q'])."%'";
					}
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_KEYWORDS']) {
						$extra_columns.=" or ".$tbl."products_meta_keywords like '".addslashes($this->get['q'])."%'";
					}
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_DESCRIPTION']) {
						$extra_columns.=" or ".$tbl."products_meta_description like '".addslashes($this->get['q'])."%'";
					}
					// do normal indexed search
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
						$filter[]="(".$tbl."products_name like '".addslashes($this->get['q'])."%' or ".$tbl."products_description like '%".addslashes($this->get['q'])."%' ".$extra_columns.")";
					} else {
						$filter[]="(".$tbl."products_name like '".addslashes($this->get['q'])."%' ".$extra_columns.")";
					}
				} else {
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_TITLE']) {
						$extra_columns.=" or ".$tbl."products_meta_title like '%".addslashes($this->get['q'])."%'";
					}
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_KEYWORDS']) {
						$extra_columns.=" or ".$tbl."products_meta_keywords like '%".addslashes($this->get['q'])."%'";
					}
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_META_DESCRIPTION']) {
						$extra_columns.=" or ".$tbl."products_meta_description like '%".addslashes($this->get['q'])."%'";
					}
					// do normal indexed search
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
						$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."%' or ".$tbl."products_description like '%".addslashes($this->get['q'])."%' ".$extra_columns.")";
					} else {
						$filter[]="(".$tbl."products_name like '%".addslashes($this->get['q'])."%' ".$extra_columns.")";
					}
				}
			}
		} else {
			// do fulltext search
			$tmpstr=addslashes(mslib_befe::ms_implode(', ', $array, '"', '+', true));
			$select[]="MATCH (".$tbl."products_name) AGAINST ('".$tmpstr."' in boolean mode) AS score";
			$where[]="MATCH (".$tbl."products_name) AGAINST ('".$tmpstr."' in boolean mode)";
			$key=$this->get['q'];
			$orderby[]='score desc';
		}
	}
	if (is_numeric($parent_id) and $parent_id>0) {
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$string='(';
			for ($i=0; $i<4; $i++) {
				if ($i>0) {
					$string.=" or ";
				}
				$string.="categories_id_".$i." = '".$parent_id."'";
			}
			$string.=')';
			if ($string) {
				$filter[]=$string;
			}
			//
		} else {
			$cats=mslib_fe::get_subcategory_ids($parent_id);
			$cats[]=$parent_id;
			$filter[]="p2c.categories_id IN (".implode(",", $cats).")";
		}
	}
	$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit, $orderby, $having, $select, $where, 0, array(), array(), 'ultra_products_search');
	$products=$pageset['products'];
	if ($pageset['total_rows']>0) {
		if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_AUTO_COMPLETE_TYPE'], "..")) {
			die('error in PRODUCTS_LISTING_AUTO_COMPLETE_TYPE value');
		} else {
			if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_AUTO_COMPLETE_TYPE'], "/")) {
				require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_LISTING_AUTO_COMPLETE_TYPE'].'.php');
			} else {
				require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/ajax_pages/includes/products_listing_auto_complete/'.$this->ms['MODULES']['PRODUCTS_LISTING_AUTO_COMPLETE_TYPE'].'.php');
			}
		}
	} else {
		$content=array("products"=>array());
	}
	$content=json_encode($content, ENT_NOQUOTES);
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
	}
}
echo $content;
exit;
?>