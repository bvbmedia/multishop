<?php
// THIS CATEGORIES LISTING TYPE DIRECTLY PRINTS THE PRODUCTS
if (is_array($current) && $current['categories_id']==$this->conf['categoriesStartingPoint']) {
	$categories_id=$current['categories_id'];
	$current['categories_name']='';
} else {
	$categories_id=$this->conf['categoriesStartingPoint'];
}
if (!is_numeric($categories_id)) {
	// FALLBACK TO NORMAL CATEGORIES LISTING
	require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/categories_listing/default.php');
} else {
	// get all subcategories
	$cats=array();
	mslib_fe::getSubcats($cats, $categories_id);
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
				$filter=array();
				$filter[]=$tbl.'categories_id IN ('.implode(',', $cats).')';
				$filter[]='p.products_status=1 and p.page_uid=\''.$this->showCatalogFromPage.'\' and cd.language_id=\''.$this->sys_language_uid.'\' and cd.language_id=pd.language_id and p.products_id=pd.products_id and p.products_id=p2c.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id';
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price', // SELECT ...
					'tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
					'', // WHERE...
					'', // GROUP BY...
					'pd.products_name', // ORDER BY...
					'' // LIMIT ...
				);
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
	$filter[]=$tbl.'categories_id IN ('.implode(',', $cats).')';
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
		if ($current['content_footer'] and !$p) {
			$hide_no_products_message=1;
			if ($current['content_footer']) {
				$content.=mslib_fe::htmlBox($current['categories_name'], $current['content_footer'], 1);
			} else {
				$show_default_header=1;
			}
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
}
?>