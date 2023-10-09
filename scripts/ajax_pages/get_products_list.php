<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->ADMIN_USER) {
    if ($this->get['type'] == '2002' && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('multishop_flat_catalog')) {
        $this->ms['MODULES']['FLAT_DATABASE'] = 1;
    }
    $return_data = array();
    $catid = 0;
    if (strpos($this->get['q'], '||catid') !== false) {
        list($search_term, $tmp_catid) = explode('||', $this->get['q']);
        $search_term = trim($search_term);
        list(, $catid) = explode('=', $tmp_catid);
        if (!empty($search_term)) {
            $this->get['q'] = $search_term;
        } else {
            $this->get['q'] = '';
        }
    }
    $filter = array();
    if (!empty($this->get['q'])) {
        if (isset($this->get['exclude_pids']) && !empty($this->get['exclude_pids'])) {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'p.';
            } else {
                $prefix = 'pf.';
            }
            $filter[] = $prefix . 'products_id NOT IN (' . addslashes($this->get['exclude_pids']) . ')';
        }
        if (!$this->masterShop) {
            $filter[] = 'p.page_uid=\'' . $this->shop_pid . '\'';
        }
        if ($catid > 0 && is_numeric($catid)) {
            $filter[] = 'p2c.categories_id=\'' . $catid . '\'';
        } else {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $filter[] = 'p2c.is_deepest=\'1\'';
            }
        }
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix_p = 'p.';
            $prefix_pd = 'pd.';
        } else {
            $prefix_p = 'pf.';
            $prefix_pd = 'pf.';
        }
        if (!empty($this->get['q'])) {
            $filter[] = '('.$prefix_p.'products_model like \'%' . addslashes($this->get['q']) . '%\' OR '.$prefix_pd.'products_name like \'%' . addslashes($this->get['q']) . '%\')';
        }
    } else {
        if (isset($this->get['exclude_pids']) && !empty($this->get['exclude_pids'])) {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'p.';
            } else {
                $prefix = 'pf.';
            }
            $filter[] = $prefix . 'products_id NOT IN (' . addslashes($this->get['exclude_pids']) . ')';
        }
        if (isset($this->get['preselected_id']) && $this->get['preselected_id'] > 0) {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'p.';
            } else {
                $prefix = 'pf.';
            }
            $filter[] = $prefix . 'products_id IN (' . addslashes($this->get['preselected_id']) . ')';
        }
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pd.';
        } else {
            $prefix = 'pf.';
        }
        if ($catid > 0 && is_numeric($catid)) {
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $filter[] = 'pf.categories_id=\'' . $catid . '\'';
            } else {
                $filter[] = 'p2c.categories_id=\'' . $catid . '\'';
            }
        } else {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $filter[] = 'p2c.is_deepest=\'1\'';
            }
        }
    }
    if (!$this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix_p = 'p.';
        $prefix_pd = 'pd.';
    } else {
        $prefix_p = 'pf.';
        $prefix_pd = 'pf.';
    }
    if (!$this->ms['MODULES']['FLAT_DATABASE']) {
        $filter[] = 'p.products_id=pd.products_id';
        $filter[] = 'p.products_id=p2c.products_id';
    }
    $filter[] = $prefix_pd.'language_id=\'' . $this->sys_language_uid . '\'';
    // hook
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_products_list.php']['getProductListFilterPreProc'])) {
        $params = array(
                'filter' => &$filter,
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_products_list.php']['getProductListFilterPreProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    $records = array();
    if (!empty($this->get['q'])) {
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix_p = 'p.';
            $prefix_pd = 'pd.';
        } else {
            $prefix_p = 'pf.';
            $prefix_pd = 'pf.';
        }
        $fromTables = array();
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $fromTables[] = 'tx_multishop_products p';
            $fromTables[] = 'tx_multishop_products_description pd';
            $fromTables[] = 'tx_multishop_products_to_categories p2c';
        } else {
            $fromTables[] = 'tx_multishop_products_flat pf';
        }
        $selectFields = array();
        $selectFields[] = $prefix_pd . 'products_name';
        $selectFields[] = $prefix_p . 'products_id';
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $selectFields[] = 'p.products_status';
            $selectFields[] = 'p2c.categories_id';
        }
        $groupByFields = array();
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $groupByFields[] = 'p.products_status desc';
        }
        $groupByFields[] = $prefix_p . 'products_id';
        $query = $GLOBALS['TYPO3_DB']->SELECTquery(implode(', ', $selectFields), // SELECT ...
                implode(', ', $fromTables), // FROM ...
                implode(' and ', $filter), // WHERE...
                implode(', ', $groupByFields), // GROUP BY...
                $prefix_pd . 'products_name asc', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $records[] = $row;
            }
        }
    } else {
        $limit = 500;
        if ($this->ms['MODULES']['LIMIT_CATALOG_SELECT2_INIT_RESULTS'] == '1') {
            $limit = 15;
        }
        //$this->msDebug=1;
        $orderby = '';
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $orderby = array('products_status desc, ' . $prefix . 'products_name asc');
        }
        $having = array();
        $select = array();
        $where = array();
        $redirect_if_one_product = 0;
        $extra_from = array();
        $groupby = array();
        $select_total_count = '';
        $returnTotalCountOnly = 0;
        $enableFetchTaxRate = 1;
        $extra_join = array();
        $includeDisabled = 1;
        $skipIsDeepest = 0;
        //$this->msDebug=1;
        $products = mslib_fe::getProductsPageSet($filter, 0, $limit, $orderby, $having, $select, $where, $redirect_if_one_product, $extra_from, $groupby, 'products_search', $select_total_count, $returnTotalCountOnly, $enableFetchTaxRate, $extra_join, $includeDisabled, $skipIsDeepest);
        //echo $this->msDebugInfo;
        //die('sss');
        $counter = 0;
        foreach ($products['products'] as $row) {
            $records[] = $row;
        }
    }
    if (is_array($records)) {
        $counter = 0;
        if (!$this->get['preselected_id']) {
            $return_data[0]['text'] = htmlentities($this->pi_getLL('choose'));
            $return_data[0]['id'] = '';
            $counter = 1;
        }
        foreach ($records as $row) {
            $labelArray = array();
            if (isset($row['products_status']) && !$row['products_status']) {
                $labelArray[] = '[' . htmlspecialchars($this->pi_getLL('disabled_product')) . ']';
            }
            if ((is_numeric($catid) && $catid > 0) || (isset($this->get['preselected_id']) && is_numeric($this->get['preselected_id']) && $this->get['preselected_id'] > 0)) {
                $labelArray[] = $row['products_name'];
            } else {
                $catsname = array();
                if ($row['categories_id'] > 0) {
                    // get all cats to generate multilevel fake url
                    $level = 0;
                    $cats = mslib_fe::Crumbar($row['categories_id']);
                    $cats = array_reverse($cats);
                    $where = '';
                    if (count($cats) > 0) {
                        foreach ($cats as $cat) {
                            $catsname[] = $cat['name'];
                        }
                    }
                    // get all cats to generate multilevel fake url eof
                }
                if (!empty($row['products_model'])) {
                    $labelArray[] = '(' . addslashes($row['products_model']) . ')';
                } else {
                    $return_data[$counter]['text'] = htmlentities(implode(" > ", $catsname) . ' > ' . $row['products_name']) . ' (PID: ' . $row['products_id'] . ')';
                }
                $labelArray[] = implode(" > ", $catsname) . ' > ' . $row['products_name'];
            }
            $labelArray[] = '(PID: ' . $row['products_id'] . ')';
            $return_data[$counter]['text'] = htmlentities(implode(' ', $labelArray));
            $return_data[$counter]['id'] = $row['products_id'];
            $counter++;
        }
    }
    // hook eof
    /*
    if (!empty($this->get['q'])) {
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('pd.products_name, pd.products_id, p2c.categories_id', // SELECT ...
                'tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c', // FROM ...
                implode(' and ', $filter), // WHERE...
                'p.products_status desc, p.products_id', // GROUP BY...
                'pd.products_name asc', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
            $counter = 0;
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $labelArray=array();
                if ((is_numeric($catid) && $catid > 0) || (isset($this->get['preselected_id']) && is_numeric($this->get['preselected_id']) && $this->get['preselected_id'] > 0)) {
                    $return_data[$counter]['text'] = htmlentities($row['products_name']) .' (PID: '.$row['products_id'].')';
                } else {
                    $catsname = array();
                    if ($row['categories_id'] > 0) {
                        // get all cats to generate multilevel fake url
                        $level = 0;
                        $cats = mslib_fe::Crumbar($row['categories_id']);
                        $cats = array_reverse($cats);
                        $where = '';
                        if (count($cats) > 0) {
                            foreach ($cats as $cat) {
                                $catsname[] = $cat['name'];
                            }
                        }
                        // get all cats to generate multilevel fake url eof
                    }
                    if (!empty($row['products_model'])) {
                        $return_data[$counter]['text'] =  '('.addslashes($row['products_model']).') ' . htmlentities(implode(" > ", $catsname) . ' > ' . $row['products_name']) . ' (PID: ' . $row['products_id'] . ')';
                    } else {
                        $return_data[$counter]['text'] = htmlentities(implode(" > ", $catsname) . ' > ' . $row['products_name']) . ' (PID: ' . $row['products_id'] . ')';
                    }
                }
                $return_data[$counter]['id'] = $row['products_id'];
                $counter++;
            }
        }
    } else {
        $limit=500;
        if ($this->ms['MODULES']['LIMIT_CATALOG_SELECT2_INIT_RESULTS']=='1') {
            $limit=15;
        }
        $products = mslib_fe::getProductsPageSet($filter, 0, $limit, array('products_status desc, '.$prefix . 'products_name asc'));
        $counter = 0;
        foreach ($products['products'] as $product) {
            if ($product['products_name'] && !empty($product['products_name'])) {
                if ((is_numeric($catid) && $catid > 0) || (isset($this->get['preselected_id']) && is_numeric($this->get['preselected_id']) && $this->get['preselected_id'] > 0)) {
                    $return_data[$counter]['text'] = htmlspecialchars($product['products_name']) . ' (PID: '.$product['products_id'].')';
                } else {
                    $catsname = array();
                    if ($product['categories_id']) {
                        // get all cats to generate multilevel fake url
                        $level = 0;
                        $cats = mslib_fe::Crumbar($product['categories_id']);
                        $cats = array_reverse($cats);
                        $where = '';
                        if (count($cats) > 0) {
                            foreach ($cats as $cat) {
                                $catsname[] = $cat['name'];
                            }
                        }
                        // get all cats to generate multilevel fake url eof
                    }
                    if (!empty($product['products_model'])) {
                        $return_data[$counter]['text'] =  '('.addslashes($product['products_model']).') ' . htmlentities(implode(" > ", $catsname) . ' > ' . $product['products_name']) . ' (PID: ' . $product['products_id'] . ')';
                    } else {
                        $return_data[$counter]['text'] = htmlspecialchars(implode(" > ", $catsname) . ' > ' . $product['products_name']) . ' (PID: ' . $product['products_id'] . ')';
                    }
                }
                $return_data[$counter]['id'] = $product['products_id'];
                $counter++;
            }
        }
    }
    */
    echo json_encode($return_data);
    exit;
}
exit();
?>
