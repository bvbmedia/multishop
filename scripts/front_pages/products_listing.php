<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']) {
    $this->ms['MODULES']['CACHE_FRONT_END'] = 0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
    $options = array(
            'caching' => true,
            'cacheDir' => $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/cache/',
            'lifeTime' => $this->ms['MODULES']['CACHE_TIME_OUT_LISTING_PAGES']
    );
    $Cache_Lite = new Cache_Lite($options);
    $string = md5(serialize($this->conf)) . $this->cObj->data['uid'] . '_' . $this->HTTP_HOST . '_' . $this->server['REQUEST_URI'] . $this->server['QUERY_STRING'] . serialize($this->post);
    // custom hook that can be controlled by third-party plugin
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingCacheStringKeyPostProc'])) {
        $params = array('string' => &$string);
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['productsListingCacheStringKeyPostProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
}
$output_array = array();
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$output_array = $Cache_Lite->get($string)) {
    if ($this->get['p']) {
        $p = $this->get['p'];
    }
    if (is_numeric($this->get['categories_id'])) {
        $parent_id = $this->get['categories_id'];
    } else {
        $parent_id = $this->categoriesStartingPoint;
        $this->get['categories_id'] = $this->categoriesStartingPoint;
    }
    $subcats = array();
    // current cat
    if ($parent_id > 0) {
        $str = "SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.categories_id='" . addslashes($parent_id) . "' and cd.language_id='" . $this->sys_language_uid . "' and c.page_uid='" . $this->showCatalogFromPage . "' and c.categories_id=cd.categories_id";
        // todo: for max speed, remove *
        // $str = "SELECT c.categories_id, c.parent_id, cd.categories_name from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.categories_id='" . addslashes($parent_id) . "' and cd.language_id='" . $this->sys_language_uid . "' and c.page_uid='" . $this->showCatalogFromPage . "' and c.categories_id=cd.categories_id";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $current = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
        // custom hook that can be controlled by third-party plugin
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingCurrentCategoryPreProc'])) {
            $params = array();
            $params['current'] =& $current;
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingCurrentCategoryPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
    } else {
        // default root has no current category. this is a bad query (bas)
        //$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.parent_id='".$parent_id."' and cd.language_id='".$this->sys_language_uid."' and c.page_uid='".$this->showCatalogFromPage."' and c.categories_id=cd.categories_id";
    }
    // first check if the meta_title exists
    $display_listing = false;
    if ($current['categories_id']) {
        if ($current['custom_settings']) {
            mslib_fe::updateCustomSettings($current['custom_settings']);
        }
        if ($current['meta_title']) {
            $meta_title = $current['meta_title'];
        } else {
            $meta_title = $current['categories_name'];
            $meta_title = $meta_title . $this->ms['MODULES']['PAGE_TITLE_DELIMETER'] . $this->ms['MODULES']['STORE_NAME'];
        }
        if ($current['meta_description']) {
            $meta_description = htmlspecialchars($current['meta_description']);
        } else {
            $meta_description = '';
        }
        if ($current['meta_keywords']) {
            $meta_keywords = htmlspecialchars($current['meta_keywords']);
        } else {
            $meta_keywords = '';
        }
        if ($this->conf['disableMetatags'] == '0') {
            $output_array['meta']['title'] = '<title>' . htmlspecialchars($meta_title) . '</title>';
            if ($meta_description) {
                $output_array['meta']['description'] = '<meta name="description" content="' . $meta_description . '" />';
            }
            if ($meta_keywords) {
                if (!$this->conf['disableMetatagsKeywords']) {
                    $output_array['meta']['keywords'] = '<meta name="keywords" content="' . htmlspecialchars($meta_keywords) . '" />';
                }
            }
        }
        if (isset($current['search_engines_allow_indexing'])) {
            if (!$current['search_engines_allow_indexing']) {
                $output_array['meta']['noindex'] = '<meta name="robots" content="noindex, follow" />';
            } else {
                $no_index = false;
                $level = 0;
                $cats = array();
                $cats = mslib_fe::Crumbar($current['categories_id']);
                if (count($cats) > 0) {
                    foreach ($cats as $cat) {
                        if ($level > 0) {
                            if (!$cat['search_engines_allow_indexing']) {
                                $no_index = true;
                                break;
                            }
                        }
                        $level++;
                    }
                }
                if ($no_index) {
                    $output_array['meta']['noindex'] = '<meta name="robots" content="noindex, follow" />';
                }
            }
        }
        // create the meta tags eof
        $display_listing = true;
    } else {
        if ($this->get['categories_id']) {
            // set custom 404 message
            header('HTTP/1.0 404 Not Found');
            $output_array['http_header'] = 'HTTP/1.0 404 Not Found';
            $page = mslib_fe::getCMScontent('product_not_found_message', $GLOBALS['TSFE']->sys_language_uid);
            if ($page[0]['name']) {
                $content .= '<div class="main-title"><h1>' . $page[0]['name'] . '</h1></div>';
            } else {
                $content .= $this->pi_getLL('no_products_available');
            }
            if ($page[0]['content']) {
                $content .= $page[0]['content'];
            }
        } else {
            $parent_id = $this->categoriesStartingPoint;
            $this->get['categories_id'] = $this->categoriesStartingPoint;
            $display_listing = true;
        }
    }
    if ($display_listing) {
        $subCats = mslib_fe::getSubcatsOnly($parent_id);
        if ($this->ADMIN_USER and $this->get['sort_by']) {
            if (is_array($subCats) and count($subCats) > 0) {
                switch ($this->get['sort_by']) {
                    case 'alphabet':
                        if (is_numeric($parent_id)) {
                            $str = "SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.parent_id='" . $parent_id . "' and c.page_uid='" . $this->showCatalogFromPage . "' and cd.language_id='" . $this->sys_language_uid . "' and c.categories_id=cd.categories_id order by cd.categories_name";
                            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                            $counter = 0;
                            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                                $updateArray = array();
                                $updateArray['sort_order'] = $counter;
                                $updateArray['last_modified'] = time();
                                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=' . $row['categories_id'], $updateArray);
                                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                $counter++;
                            }
                            $str = "SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1 and c.parent_id='" . $parent_id . "' and c.page_uid='" . $this->showCatalogFromPage . "' and cd.language_id='" . $this->sys_language_uid . "' order by c.sort_order";
                            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        }
                        break;
                }
            }
        }
        $categories = array();
        foreach ($subCats as $subCat) {
            if (!$subCat['hide_in_menu']) {
                $categories[] = $subCat;
            }
        }
        if (!$p) {
            if ($this->ms['MODULES']['PRODUCTS_LISTING_SPECIALS']) {
                if ($GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar']) {
                    $cats = $GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar'];
                    if ($this->ms['MODULES']['CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL'] and (count($cats) > $this->ms['MODULES']['CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL'])) {
                        $hide_specials_box = 1;
                    }
                }
                if (!$hide_specials_box) {
                    $content .= mslib_fe::SpecialsBox($this->ms['page']); // specials module
                }
            }
        }
        if (is_array($categories) and count($categories) > 0) {
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingPreProc'])) {
                $params = array();
                $params['categories'] =& $categories;
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // create the meta tags
            // category listing
            if (strstr($this->ms['MODULES']['CATEGORIES_LISTING_TYPE'], "..")) {
                die('error in categories_listing_type value');
            } else {
                if (strstr($this->ms['MODULES']['CATEGORIES_LISTING_TYPE'], "/")) {
                    require($this->DOCUMENT_ROOT . $this->ms['MODULES']['CATEGORIES_LISTING_TYPE'] . '.php');
                } else {
                    if (!$this->ms['MODULES']['CATEGORIES_LISTING_TYPE']) {
                        $this->ms['MODULES']['CATEGORIES_LISTING_TYPE'] = 'default';
                    }
                    require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/front_pages/includes/categories_listing/' . $this->ms['MODULES']['CATEGORIES_LISTING_TYPE'] . '.php');
                }
            }
            // category listing eof
            if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
                if (!isset($output_array['meta']['sortables'])) {
                    $output_array['meta']['sortables'] = '
					<script>
					jQuery(document).ready(function($) {
					    var result = jQuery("#category_listing").sortable({
                            cursor:     "move",
                            //axis:       "y",
                            update: function(e, ui) {
                                href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=subcatlisting') . '";
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
            }
        } else {
            if ($this->productsLimit) {
                $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'] = $this->productsLimit;
            }
            $default_limit_page = $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
            if ($this->get['tx_multishop_pi1']['limitsb']) {
                if ($this->get['tx_multishop_pi1']['limitsb'] and $this->get['tx_multishop_pi1']['limitsb'] != $this->cookie['limitsb']) {
                    $this->cookie['limitsb'] = $this->get['tx_multishop_pi1']['limitsb'];
                    $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'] = $this->cookie['limitsb'];
                    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
                    $GLOBALS['TSFE']->storeSessionData();
                }
            }
            if ($this->get['tx_multishop_pi1']['sortbysb']) {
                if ($this->get['tx_multishop_pi1']['sortbysb'] and $this->get['tx_multishop_pi1']['sortbysb'] != $this->cookie['sortbysb']) {
                    $this->cookie['sortbysb'] = $this->get['tx_multishop_pi1']['sortbysb'];
                    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
                    $GLOBALS['TSFE']->storeSessionData();
                }
            } else {
                $this->cookie['sortbysb'] = '';
                $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
                $GLOBALS['TSFE']->storeSessionData();
            }
            if ($this->ADMIN_USER) {
                $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'] = 150;
            }
            // product listing
            if (isset($this->cookie['limitsb']) && $this->cookie['limitsb'] > 0) {
                $limit_per_page = $this->cookie['limitsb'];
                if ($this->ADMIN_USER) {
                    $limit_per_page = 150;
                }
            } else {
                $limit_per_page = $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
            }
            if ($p > 0) {
                $offset = (((($p) * $limit_per_page)));
            } else {
                $p = 0;
                $offset = 0;
            }
            if ($this->ADMIN_USER and $this->get['sort_by']) {
                switch ($this->get['sort_by']) {
                    case 'alphabet':
                        $str = "SELECT c.categories_id, p.products_id, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_status=1 and p.page_uid='" . $this->showCatalogFromPage . "' and cd.language_id='" . $this->sys_language_uid . "' and cd.language_id=pd.language_id and p2c.categories_id='" . $this->get['categories_id'] . "' and p2c.is_deepest=1 and p.products_id=pd.products_id and p.products_id=p2c.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id order by pd.products_name";
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $counter = 0;
                        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                            $updateArray = array();
                            $updateArray['sort_order'] = $counter;
                            $updateArray['last_updated_at'] = time();
                            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'categories_id=' . $row['categories_id'] . ' and products_id=' . $row['products_id'], $updateArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            $counter++;
                        }
                        break;
                }
            }
            $doProductQuery = 1;
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingProductQueryPreProc'])) {
                $params = array(
                        'doProductQuery' => &$doProductQuery,
                        'current' => &$current,
                        'content' => &$content,
                        'limit_per_page' => &$limit_per_page
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingProductQueryPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            if ($doProductQuery) {
                if ($this->ms['MODULES']['FLAT_DATABASE']) {
                    $tbl = 'pf.';
                } else {
                    $tbl = 'p2c.';
                }
                $filter = array();
                $filter[] = $tbl . 'categories_id=' . $this->get['categories_id'];
                $orderby = array();
                $select = array();
                $where = array();
                $extra_from = array();
                $extra_join = array();
                if (isset($this->cookie['sortbysb']) && !empty($this->cookie['sortbysb']) && isset($this->get['tx_multishop_pi1']['sortbysb']) && !empty($this->get['tx_multishop_pi1']['sortbysb'])) {
                    if ($this->ms['MODULES']['FLAT_DATABASE']) {
                        $tbl = 'pf.';
                        $tbl_m = 'pf.';
                    } else {
                        $tbl = 'p.';
                        $tbl_m = 'm.';
                    }
                    switch ($this->cookie['sortbysb']) {
                        case 'best_selling_asc':
                            //$select[] = 'SUM(op.qty) as order_total_qty';
                            $select[] = '(select SUM(op.qty) from tx_multishop_orders_products op where ' . $tbl . 'products_id=op.products_id) as order_total_qty';
                            //$extra_join[] = 'LEFT JOIN tx_multishop_orders_products op ON ' . $tbl . 'products_id=op.products_id';
                            $orderby[] = "order_total_qty asc";
                            break;
                        case 'best_selling_desc':
                            $select[] = '(select SUM(op.qty) from tx_multishop_orders_products op where ' . $tbl . 'products_id=op.products_id) as order_total_qty';
                            //$extra_join[] = 'LEFT JOIN tx_multishop_orders_products op ON ' . $tbl . 'products_id=op.products_id';
                            $orderby[] = "order_total_qty desc";
                            break;
                        case 'price_asc':
                            $orderby[] = "final_price asc";
                            break;
                        case 'price_desc':
                            $orderby[] = "final_price desc";
                            break;
                        case 'new_asc':
                            $orderby[] = $tbl . "products_date_added desc";
                            break;
                        case 'new_desc':
                            $orderby[] = $tbl . "products_date_added asc";
                            break;
                        case 'manufacturers_asc':
                            $orderby[] = $tbl_m . "manufacturers_name asc";
                            break;
                        case 'manufacturers_desc':
                            $orderby[] = $tbl_m . "manufacturers_name desc";
                            break;
                    }
                }
                // custom hook that can be controlled by third-party plugin
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingProductQueryPostProc'])) {
                    $params = array(
                            'filter' => &$filter,
                            'orderby' => &$orderby,
                            'select' => &$select,
                            'where' => &$where,
                            'extra_from' => &$extra_from,
                            'extra_join' => &$extra_join
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/products_listing.php']['categoriesListingProductQueryPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                //$this->msDebug=1;
                $pageset = mslib_fe::getProductsPageSet($filter, $offset, $limit_per_page, $orderby, array(), $select, $where, 0, $extra_from, array(), 'products_listing', '', 0, 1, $extra_join);
                //echo $this->msDebugInfo;
                //die();
                $products = $pageset['products'];
                // load products listing
                $products_compare = true;
                if (!count($products)) {
                    if ($current['content'] and !$p) {
                        $hide_no_products_message = 1;
                        if ($current['content']) {
                            $content .= mslib_fe::htmlBox($current['categories_name'], $current['content'], 1);
                        } else {
                            $show_default_header = 1;
                        }
                    }
                    if (!$hide_no_products_message) {
                        $content .= '<div class="emptyContent">' . $this->pi_getLL('no_products_available') . '</div>';
                    }
                    if ($current['content_footer'] and !$p) {
                        $hide_no_products_message = 1;
                        if ($current['content_footer']) {
                            $content .= mslib_fe::htmlBox($current['categories_name'], $current['content_footer'], 1);
                        } else {
                            $show_default_header = 1;
                        }
                    }
                } else {
                    if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "..")) {
                        die('error in PRODUCTS_LISTING_TYPE value');
                    } else {
                        if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'], "/")) {
                            require($this->DOCUMENT_ROOT . $this->ms['MODULES']['PRODUCTS_LISTING_TYPE'] . '.php');
                        } else {
                            if (!$this->ms['MODULES']['PRODUCTS_LISTING_TYPE']) {
                                $this->ms['MODULES']['PRODUCTS_LISTING_TYPE'] = 'default';
                            }
                            require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/front_pages/includes/products_listing/' . $this->ms['MODULES']['PRODUCTS_LISTING_TYPE'] . '.php');
                        }
                    }
                    // pagination
                    if (!$this->hidePagination and ($pageset['total_rows'] > $limit_per_page)) {
                        if (!isset($this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE']) || $this->ms['MODULES']['PRODUCTS_LISTING_PAGINATION_TYPE'] == 'default') {
                            require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/front_pages/includes/products_listing_pagination.php');
                        } else {
                            require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/front_pages/includes/products_listing_pagination_with_number.php');
                        }
                    }
                    // pagination eof
                }
                // load products listing eof
            }
        }
    }
    if ($this->ms['MODULES']['CACHE_FRONT_END']) {
        $output_array['content'] = $content;
        $Cache_Lite->save(serialize($output_array), $string);
    }
} elseif ($output_array) {
    $output_array = unserialize($output_array);
    $content = $output_array['content'];
}
if (is_array($output_array['meta'])) {
    $GLOBALS['TSFE']->additionalHeaderData = array_merge($GLOBALS['TSFE']->additionalHeaderData, $output_array['meta']);
    unset($output_array);
}
?>
