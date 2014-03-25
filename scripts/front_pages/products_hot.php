<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->productsLimit) {
	$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->productsLimit;
}
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$this->cacheLifeTime=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
	if (!$this->cacheLifeTime) {
		$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES'];
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
	if ($p>0) {
		$extrameta=' (page '.$p.')';
	} else {
		$extrameta='';
	}
	if ($p>0) {
		$offset=(((($p)*$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])));
	} else {
		$p=0;
		$offset=0;
	}
	$do_search=1;
	if ($do_search) {
		$filter=array();
		$having=array();
		$match=array();
		$where=array();
		$orderby=array();
		$select=array();
		$from=array();
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$tbl='pf.';
		} else {
			$tbl='p2c.';
		}
		$from[]='tx_multishop_orders_products mop';
		$filter[]=$tbl."products_id=mop.products_id";
		$having[]="total_quantity > 1";
		$select[]='COUNT('.$tbl.'products_id) as total_quantity';
		$orderby[]='total_quantity desc';
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
		$limit_per_page=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
		$pageset=mslib_fe::getProductsPageSet($filter, $offset, $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'], $orderby, $having, $select, $where, 0, $from, array(), 'hot_products');
		$products=$pageset['products'];
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
			if (!$this->hidePagination and $pageset['total_rows']>$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']) {
				if (!isset($this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']) || $this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']=='default') {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination_with_number.php');
				}
			}
			// pagination eof
		} else {
			$this->no_database_results=1;
			$content.='<div class="main-heading"><h2>'.$this->pi_getLL('no_new_products_found_heading').'</h2></div>'."\n";
			$content.='<p>'.$this->pi_getLL('no_new_products_found_description').'</p>'."\n";
		}
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
	}
}
?>