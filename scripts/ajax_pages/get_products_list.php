<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
    $return_data=array();
    if (!empty($this->get['q'])) {
        $filter=array();
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
        $filter[]='p.products_id=pd.products_id';
        $filter[]='pd.language_id=\''.$this->sys_language_uid.'\'';
        $filter[]='(pd.products_name like \'%'.$this->get['q'].'%\')';
        $query=$GLOBALS['TYPO3_DB']->SELECTquery('pd.products_name, pd.products_id', // SELECT ...
                'tx_multishop_products p, tx_multishop_products_description pd', // FROM ...
                implode(' and ', $filter), // WHERE...
                'pd.products_id', // GROUP BY...
                'pd.products_name asc', // ORDER BY...
                '' // LIMIT ...
        );
        $res=$GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
            $counter=0;
            while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $return_data[$counter]['text']=$row['products_name'];
                $return_data[$counter]['id']=$row['products_id'];
                $counter++;
            }
        }
    } else {
        $filter=array();
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
        $products=mslib_fe::getProductsPageSet($filter, 0, 100, array($prefix.'products_name asc'));
        $counter=0;
        foreach ($products['products'] as $product) {
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
            $return_data[$counter]['text']=htmlentities(implode(" > ", $catsname).'>'.$product['products_name']);
            $return_data[$counter]['id']=$product['products_id'];
            $counter++;
        }
    }
    echo json_encode($return_data);
    exit;
}
exit();
?>