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
	//$string=$this->cObj->data['uid'].'_'.$this->HTTP_HOST.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
	$string=md5(serialize($this->conf)).$this->cObj->data['uid'].'_'.$this->HTTP_HOST.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string)) {
	if ($p>0) {
		$extrameta=' (page '.$p.')';
	} else {
		$extrameta='';
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
	$do_search=1;
	if ($do_search) {
		// product search
		$filter=array();
		$having=array();
		$match=array();
		$orderby=array();
		$where=array();
		$orderby=array();
		$select=array();
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
		if ($this->ms['MODULES']['NUMBER_OF_DAYS']) {
			$days=$this->ms['MODULES']['NUMBER_OF_DAYS'];
		} elseif ($this->ms['MODULES']['PRODUCTS_NEW_NUMBER_OF_DAYS']) {
			$days=$this->ms['MODULES']['PRODUCTS_NEW_NUMBER_OF_DAYS'];
		} else {
			$days=30;
		}
//		$filter[]	=$tbl."products_date_available > '".(time()-(3600*24*$days))."' and ".$tbl."products_date_available <= '".time()."'";
//		$orderby[]	=$tbl."products_date_available desc";
		$filter[]=$tbl."products_date_added BETWEEN ".(time()-(3600*24*$days))." and ".time();
		$orderby[]=$tbl."products_date_added desc";
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
		//$limit_per_page=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
		$pageset=mslib_fe::getProductsPageSet($filter, $offset, $limit_per_page, $orderby, $having, $select, $where, 0, array(), array(), 'new_products');
		$products=$pageset['products'];
		$products_compare=false;
		if ($pageset['total_rows']>0) {
			if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "..")) {
				die('error in PRODUCTS_LISTING_TYPE value');
			} else {
				if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');
				} else {
					if (!$this->ms['MODULES']['PRODUCTS_LISTING_TYPE']) {
						$this->ms['MODULES']['PRODUCTS_LISTING_TYPE']='default';
					}
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/products_listing/'.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');
				}
			}
			// pagination
			if (!$this->hidePagination and $pageset['total_rows']>$limit_per_page) {
				if (!isset($this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']) || $this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']=='default') {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');
				} else {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination_with_number.php');
				}
			}
			// pagination eof
		} else {
			$this->no_database_results=1;
			if (!$this->hideIfNoResults) {
				$content.='<div class="main-heading"><h2>'.$this->pi_getLL('no_new_products_found_heading').'</h2></div>'."\n";
				$content.='<p>'.$this->pi_getLL('no_new_products_found_description').'</p>'."\n";
			}
		}
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
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