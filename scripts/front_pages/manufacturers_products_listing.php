<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (is_numeric($this->get['manufacturers_id'])) {
	if ($this->productsLimit) {
		$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->productsLimit;
	}
	if (is_numeric($this->get['p'])) {
		$p=$this->get['p'];
	}
	if (($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) or $this->ROOTADMIN_USER) {
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
	if ((!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string)) && is_numeric($this->get['manufacturers_id'])) {
		// current manufacturer
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('mc.content,mc.content_footer,m.manufacturers_id, m.manufacturers_name, m.manufacturers_image', // SELECT ...
			'tx_multishop_manufacturers m, tx_multishop_manufacturers_cms mc', // FROM ...
			'm.status=1 and m.manufacturers_id=\''.addslashes($this->get['manufacturers_id']).'\' and mc.language_id=\''.$this->sys_language_uid.'\' and m.manufacturers_id=mc.manufacturers_id', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$current=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$content.='<div class="main-heading"><h1>'.$current['manufacturers_name'].'</h1></div>';

		// now the listing
		if ($p>0) {
			$extrameta=' (page '.$p.')';
		} else {
			$extrameta='';
		}
		if (!$this->conf['disableMetatags']) {
			$GLOBALS['TSFE']->additionalHeaderData['title']='<title>'.htmlspecialchars($current['manufacturers_name']).$this->ms['MODULES']['PAGE_TITLE_DELIMETER'].$this->ms['MODULES']['STORE_NAME'].'</title>';
		}
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
		$do_search=1;
		if ($do_search) {
			if ($current['content'] and !$p) {
				$content.=mslib_fe::htmlBox('', $current['content'], '', 'msFrontManufacturersProductsListingCmsTop');
				//$content.=$current['content'];
			}
			if ($this->get['skeyword']) {
				$content.='<div class="main-heading"><h2></h2></div>';
			}
			// product search
			$filter=array();
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$orderby=array();
			$select=array();
			$extra_join=array();
			if (isset($this->cookie['sortbysb']) && !empty($this->cookie['sortbysb']) && isset($this->get['tx_multishop_pi1']['sortbysb']) && !empty($this->get['tx_multishop_pi1']['sortbysb'])) {
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$tbl='pf.';
					$tbl_m='pf.';
				} else {
					$tbl='p.';
					$tbl_m='m.';
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
					case 'manufacturers_asc':
						$orderby[]=$tbl_m."manufacturers_name asc";
						break;
					case 'manufacturers_desc':
						$orderby[]=$tbl_m."manufacturers_name desc";
						break;
				}
			}
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$tbl='pf.';
			} else {
				$tbl='p.';
			}
			$filter[]=$tbl."manufacturers_id='".$this->get['manufacturers_id']."'";
			//$this->msDebug=1;
			$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit_per_page, $orderby, $having, $select, $where, 0, array(), array(), 'manufacturers_products', '', 0, 1, $extra_join);
			//echo $this->msDebugInfo;
			$products=$pageset['products'];
			if ($pageset['total_rows']>0) {
				if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "..")) {
					die('error in PRODUCTS_LISTING_TYPE value');
				} else {
					if (!$this->ms['MODULES']['PRODUCTS_LISTING_TYPE']) {
						$this->ms['MODULES']['PRODUCTS_LISTING_TYPE']='default';
					}
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
				$content.='<div class="main-heading"><h2>'.$this->pi_getLL('no_products_found_heading').'</h2></div>'."\n";
				$content.='<p>'.$this->pi_getLL('no_new_products_found_description').'</p>'."\n";
			}
			if ($current['content_footer'] and !$p) {
				$content.=mslib_fe::htmlBox('', $current['content_footer'], '', 'msFrontManufacturersProductsListingCmsBottom');
				//$content.=$current['content_footer'];
			}
		}
		if ($this->ms['MODULES']['CACHE_FRONT_END']) {
			$Cache_Lite->save($content);
		}
	}
}
?>