<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data=array();
    if (strpos($this->get['q'], '||catid')!==false) {
        list($search_term, $tmp_catid)=explode('||', $this->get['q']);
        $search_term=trim($search_term);
        list(, $catid)=explode('=', $tmp_catid);
        if (!empty($search_term)) {
            $this->get['q']=$search_term;
        } else {
            $this->get['q']='';
        }
    }
    $filter=array();
    if (!empty($this->get['q'])) {
        if (isset($this->get['exclude_pids']) && !empty($this->get['exclude_pids'])) {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix='p.';
            } else {
                $prefix='pf.';
            }
            $filter[]=$prefix.'products_id NOT IN ('.$this->get['exclude_pids'].')';
        }
        if (!$this->masterShop) {
            $filter[]='p.page_uid=\''.$this->shop_pid.'\'';
        }
        if ($catid>0 && is_numeric($catid)) {
            $filter[] = 'p2c.categories_id=\'' . $catid . '\'';
        } else {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $filter[] = 'p2c.is_deepest=\'1\'';
            }
        }
        if (!empty($this->get['q'])) {
            $filter[] = '(pd.products_name like \'%' . $this->get['q'] . '%\')';
        }
    } else {
        if (isset($this->get['exclude_pids']) && !empty($this->get['exclude_pids'])) {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix='p.';
            } else {
                $prefix='pf.';
            }
            $filter[]=$prefix.'products_id NOT IN ('.$this->get['exclude_pids'].')';
        }
        if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
            if (!$this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix='p.';
            } else {
                $prefix='pf.';
            }
            $filter[]=$prefix.'products_id IN ('.$this->get['preselected_id'].')';
        }
        if (!$this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix='pd.';
        } else {
            $prefix='pf.';
        }
        if ($catid>0 && is_numeric($catid)) {
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
    $filter[] = 'p.products_id=pd.products_id';
    $filter[] = 'p.products_id=p2c.products_id';
    $filter[] = 'pd.language_id=\'' . $this->sys_language_uid . '\'';
    // hook
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_products_list.php']['getProductListFilterPreProc'])) {
        $params=array(
            'filter'=>&$filter,
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/get_products_list.php']['getProductListFilterPreProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    // hook eof
    if (!empty($this->get['q'])) {
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('pd.products_name, pd.products_id, p2c.categories_id', // SELECT ...
            'tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c', // FROM ...
            implode(' and ', $filter), // WHERE...
            'p.products_id', // GROUP BY...
            'pd.products_name asc', // ORDER BY...
            '' // LIMIT ...
        );
        $res=$GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
            $counter=0;
            while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $catsname=array();
                if ($row['categories_id']>0) {
                    // get all cats to generate multilevel fake url
                    $level=0;
                    $cats=mslib_fe::Crumbar($row['categories_id']);
                    $cats=array_reverse($cats);
                    $where='';
                    if (count($cats)>0) {
                        foreach ($cats as $cat) {
                            $catsname[]=$cat['name'];
                        }
                    }
                    // get all cats to generate multilevel fake url eof
                }
                $return_data[$counter]['text']=htmlentities(implode(" > ", $catsname).' > '.$row['products_name']);
                $return_data[$counter]['id']=$row['products_id'];
                $counter++;
            }
        }
    } else {
        $products=mslib_fe::getProductsPageSet($filter, 0, 100, array($prefix.'products_name asc'));
        $counter=0;
        foreach ($products['products'] as $product) {
            if ($product['products_name'] && !empty($product['products_name'])) {
                $catsname=array();
                if ($product['categories_id']) {
                    // get all cats to generate multilevel fake url
                    $level=0;
                    $cats=mslib_fe::Crumbar($product['categories_id']);
                    $cats=array_reverse($cats);
                    $where='';
                    if (count($cats)>0) {
                        foreach ($cats as $cat) {
                            $catsname[]=$cat['name'];
                        }
                    }
                    // get all cats to generate multilevel fake url eof
                }
                $return_data[$counter]['text'] = htmlentities(implode(" > ", $catsname) . ' > ' . $product['products_name']);
                $return_data[$counter]['id'] = $product['products_id'];
                $counter++;
            }
        }
    }
    echo json_encode($return_data);
    exit;
}
exit();
?>