<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($this->productsLimit) {
	$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->productsLimit;
}
$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
if(isset($this->get['clear_list'])) {
	$cart['last_visited']=array();
	$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
	$GLOBALS['TSFE']->fe_user->storeSessionData();
}
if(count($cart['last_visited'])) {
	if(is_numeric($this->get['p'])) {
		$p=$this->get['p'];
	}
	if(count($cart['last_visited'])) {
		$do_search=1;
	}
	if($p > 0) {
		$extrameta=' (page '.$p.')';
	} else {
		$extrameta='';
	}
	if($p > 0) {
		$offset=(((($p)*$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])));
	} else {
		$p=0;
		$offset=0;
	}
	if($do_search) {
		if(count($cart['last_visited']) > $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']) {
			$rand_keys=array_rand($cart['last_visited'], $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']);
		} else {
			$rand_keys=$cart['last_visited'];
		}
		// product search
		$filter=array();
		$having=array();
		$match=array();
		$orderby=array();
		$where=array();
		$orderby=array();
		$select=array();
		if($this->ms['MODULES']['FLAT_DATABASE']) {
			$tbl='pf.';
		} else {
			$tbl='p.';
		}
		if(is_array($rand_keys) and count($rand_keys)) {
			$filter[]=$tbl.'products_id IN ('.implode(",", $rand_keys).')';
			if(is_numeric($this->get['products_id'])) {
				$filter[]=$tbl.'products_id NOT IN ('.$this->get['products_id'].')';
			}
		}
		$limit_per_page=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
		$pageset=mslib_fe::getProductsPageSet($filter, $offset, $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'], $orderby, $having, $select, $where);
		$products=$pageset['products'];
		$no_content=false;
		if($pageset['total_rows'] > 0) {
			if(strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "..")) {
				die('error in PRODUCTS_LISTING_TYPE value');
			} else {
				if(strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing/'.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');
				}
			}
			// pagination
			if(!$this->hidePagination and $pageset['total_rows'] > $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']) {
				if(!isset($this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']) || $this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE'] == 'default') {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination_with_number.php');
				}
			}
			// pagination eof
		} else {
			$this->no_database_results=1;
			if(!$this->hideIfNoResults) {
				//$content.='<div class="main-heading"><h2>'.$this->pi_getLL('no_new_products_found_heading').'</h2></div>'."\n";			
				//$content.='<p>'.$this->pi_getLL('no_new_products_found_description').'</p>'."\n";
			}
		}
	}
	if($pageset['total_rows'] > 0) {
		$link=mslib_fe::typolink('', 'clear_list=1&'.mslib_fe::tep_get_all_get_params(array('clear_list')));
		$content.='<a href="'.$link.'" class="btnClearList">Clear list</a>';
	}
}
?>