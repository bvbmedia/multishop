<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (!is_numeric($this->get['cid'])) {
    $this->get['cid'] = $this->categoriesStartingPoint;
}
$postMessageArray = array();
// now parse all the objects in the tmpl file
if ($this->conf['admin_products_search_and_edit_tmpl_path']) {
    $template = $this->cObj->fileResource($this->conf['admin_products_search_and_edit_tmpl_path']);
} else {
    $template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey) . 'templates/admin_products_search_and_edit.tmpl');
}
// Extract the subparts from the template
$subparts = array();
$subparts['template'] = $this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['results'] = $this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['products_item'] = $this->cObj->getSubpart($subparts['results'], '###PRODUCTS_ITEM###');
$subparts['noresults'] = $this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
// temporary disable the flat mode if its enabled
if ($this->get['search'] and ($this->get['tx_multishop_pi1']['limit'] != $this->cookie['limit'])) {
    $this->cookie['limit'] = $this->get['tx_multishop_pi1']['limit'];
    $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
    $GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
    $this->get['tx_multishop_pi1']['limit'] = $this->cookie['limit'];
} else {
    $this->get['tx_multishop_pi1']['limit'] = 10;
}
$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'] = $this->get['tx_multishop_pi1']['limit'];
$prepending_content = $content;
$content = '';
if ($this->get['keyword']) {
    $this->get['keyword'] = trim($this->get['keyword']);
}
if (is_numeric($this->get['p'])) {
    $p = $this->get['p'];
}
if ($p > 0) {
    $offset = (((($p) * $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])));
} else {
    $p = 0;
    $offset = 0;
}
if ($this->post['submit']) {
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $updateFlatProductIds = array();
    }
    $data_update = array();
    foreach ($this->post['up']['regular_price'] as $pid => $price) {
        if (is_numeric($pid)) {
            if (strstr($price, ",")) {
                $price = str_replace(",", ".", $price);
            }
            $data_update[$pid]['price'] = $price;
            $updateArray = array();
            $updateArray['products_price'] = $price;
            // if product is originally coming from products importer we have to define that the merchant changed it
            $filter = array();
            $filter[] = 'products_id=' . $pid;
            if (mslib_befe::ifExists('1', 'tx_multishop_products', 'imported_product', $filter)) {
                // lock changed columns
                mslib_befe::updateImportedProductsLockedFields($pid, 'tx_multishop_products', $updateArray);
            }
            /*
            // if product is originally coming from products importer we have to define that the merchant changed it
            $str="select products_id from tx_multishop_products where imported_product=1 and lock_imported_product=0 and products_id='".$pid."'";
            $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                $updateArray['lock_imported_product']=1;
            }
            */
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $pid . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $updateFlatProductIds[] = $pid;
            }
        }
    }
    foreach ($this->post['up']['capital_price'] as $pid => $price) {
        if (is_numeric($pid)) {
            if (strstr($price, ",")) {
                $price = str_replace(",", ".", $price);
            }
            $updateArray = array();
            $updateArray['product_capital_price'] = $price;
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $pid . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        }
    }
    foreach ($this->post['up']['weight'] as $pid => $weight) {
        $data_update[$pid]['weight'] = $weight;
        $sql_upd = "update tx_multishop_products set products_weight = '" . $weight . "' where products_id = " . $pid;
        $GLOBALS['TYPO3_DB']->sql_query($sql_upd);
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $updateFlatProductIds[] = $pid;
        }
    }
    foreach ($this->post['up']['stock'] as $pid => $qty) {
        $data_update[$pid]['qty'] = $qty;
        $sql_upd = "update tx_multishop_products set products_quantity = '" . $qty . "' where products_id = " . $pid;
        $GLOBALS['TYPO3_DB']->sql_query($sql_upd);
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $updateFlatProductIds[] = $pid;
        }
    }
    foreach ($this->post['up']['special_price'] as $pid => $price) {
        $data_update[$pid]['special_price'] = $price;
        if (strstr($price, ",")) {
            $price = str_replace(",", ".", $price);
        }
        if ($price > 0) {
            $sql_check = "select products_id from tx_multishop_specials where products_id = " . $pid;
            $qry_check = $GLOBALS['TYPO3_DB']->sql_query($sql_check);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check) > 0 && $price > 0) {
                $sql_upd = "update tx_multishop_specials set specials_new_products_price = '" . $price . "', status = 1 where products_id = " . $pid;
                $GLOBALS['TYPO3_DB']->sql_query($sql_upd);
                if ($this->ms['MODULES']['FLAT_DATABASE']) {
                    $updateFlatProductIds[] = $pid;
                }
            } else {
                if ($price > 0) {
                    $sql_ins = "insert into tx_multishop_specials (products_id, status, specials_new_products_price, specials_date_added, news_item, home_item, scroll_item) values (" . $pid . ", 1, '" . $price . "', NOW(), 1, 1, 1)";
                    $GLOBALS['TYPO3_DB']->sql_query($sql_ins);
                    if ($this->ms['MODULES']['FLAT_DATABASE']) {
                        $updateFlatProductIds[] = $pid;
                    }
                }
            }
        } else {
            $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_specials', 'products_id=\'' . addslashes($pid) . '\'');
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $updateFlatProductIds[] = $pid;
            }
        }
    }
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        if (count($updateFlatProductIds)) {
            $ids = array_unique($updateFlatProductIds);
            foreach ($ids as $prodid) {
                // if the flat database module is enabled we have to sync the changes to the flat table
                mslib_befe::convertProductToFlat($prodid);
            }
        }
    }
    // clear the multishop cache
    if ($this->ms['MODULES']['AUTOMATICALLY_CLEAR_MULTISHOP_CACHE_ON_CATALOG_CHANGES']) {
        if (count($data_update)) {
            mslib_befe::cacheLite('delete_all');
        }
    }
    // custom page hook that can be controlled by third-party plugin
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionProductPostProc'])) {
        $params = array();
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionProductPostProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    // custom page hook that can be controlled by third-party plugin eof
    if (count($this->post['selectedProducts'])) {
        switch ($this->post['tx_multishop_pi1']['action']) {
            case 'delete':
                foreach ($this->post['selectedProducts'] as $old_categories_id => $array) {
                    foreach ($array as $pid) {
                        mslib_befe::deleteProduct($pid, $old_categories_id);
                    }
                }
                break;
            case 'move':
                if (is_numeric($this->post['tx_multishop_pi1']['target_categories_id']) and mslib_befe::canContainProducts($this->post['tx_multishop_pi1']['target_categories_id'])) {
                    foreach ($this->post['selectedProducts'] as $old_categories_id => $array) {
                        foreach ($array as $pid) {
                            $filter = array();
                            $filter[] = 'products_id=' . $pid;
                            if (mslib_befe::ifExists('1', 'tx_multishop_products', 'imported_product', $filter)) {
                                // lock changed columns
                                mslib_befe::updateImportedProductsLockedFields($pid, 'tx_multishop_products_to_categories', array('categories_id' => $this->post['tx_multishop_pi1']['target_categories_id']));
                            }
                            mslib_befe::moveProduct($pid, $this->post['tx_multishop_pi1']['target_categories_id'], $old_categories_id);
                        }
                    }
                }
                break;
            case 'duplicate':
                foreach ($this->post['selectedProducts'] as $old_categories_id => $array) {
                    if ($this->post['tx_multishop_pi1']['target_categories_id'] > 0) {
                        $target_cat_id = $this->post['tx_multishop_pi1']['target_categories_id'];
                    } else {
                        $target_cat_id = $old_categories_id;
                    }
                    foreach ($array as $pid) {
                        mslib_befe::duplicateProduct($pid, $target_cat_id);
                    }
                }
                break;
            default:
                // custom page hook that can be controlled by third-party plugin
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionProductIteratorProc'])) {
                    $params = array(
                            'action' => &$this->post['tx_multishop_pi1']['action'],
                            'postMessageArray' => &$postMessageArray
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionProductIteratorProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                // custom page hook that can be controlled by third-party plugin eof
                break;
        }
    }
    // lets notify plugin that we have update action in products
    tx_mslib_catalog::productsUpdateNotifierForPlugin($this->post);
}
$fields = array();
$fields['products_name'] = $this->pi_getLL('products_name');
$fields['products_model'] = $this->pi_getLL('products_model');
$fields['products_description'] = $this->pi_getLL('products_description');
//$fields['products_price'] = $this->pi_getLL('admin_price');
//$fields['specials_price'] = ucfirst($this->pi_getLL('admin_specials_price'));
//$fields['capital_price'] = $this->pi_getLL('capital_price');
$fields['products_id'] = $this->pi_getLL('products_id');
$fields['categories_name'] = $this->pi_getLL('admin_category');
//$fields['products_quantity'] = $this->pi_getLL('admin_stock');
$fields['products_weight'] = $this->pi_getLL('admin_weight');
$fields['manufacturers_name'] = $this->pi_getLL('manufacturer');
$fields['ean_code'] = $this->pi_getLL('admin_ean_code');
$fields['sku_code'] = $this->pi_getLL('admin_sku_code');
$fields['foreign_products_id'] = $this->pi_getLL('admin_foreign_products_id');
$fields['vendor_code'] = $this->pi_getLL('admin_manufacturers_products_id');
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionSearchByFilter'])) {
    $params = array(
            'fields' => &$fields
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionSearchByFilter'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
asort($fields);
$searchby_selectbox = '<select name="tx_multishop_pi1[search_by]" class="form-control">';
if (isset($this->conf['adminProductsSearchAndEditStandardCustomSearchOn']) && $this->conf['adminProductsSearchAndEditStandardCustomSearchOn'] != '') {
    $new_fields = array();
    $new_fields['default'] = $this->pi_getLL('default');
    $fields = array_merge($new_fields, $fields);
}
foreach ($fields as $key => $label) {
    $option_selected = '';
    if (isset($this->get['tx_multishop_pi1']['search_by'])) {
        if ($this->get['tx_multishop_pi1']['search_by'] == $key) {
            $option_selected = ' selected="selected"';
        }
    } else {
        if (isset($this->conf['adminProductsSearchAndEditStandardCustomSearchOn']) && $this->conf['adminProductsSearchAndEditStandardCustomSearchOn'] != '') {
            if ($key == 'default') {
                $option_selected = ' selected="selected"';
            }
        } else {
            if ($key == 'products_name') {
                $option_selected = ' selected="selected"';
            }
        }
    }
    $searchby_selectbox .= '<option value="' . $key . '"' . $option_selected . '>' . $label . '</option>' . "\n";
}
$searchby_selectbox .= '</select>';
//$search_category_selectbox=mslib_fe::tx_multishop_draw_pull_down_menu('cid', mslib_fe::tx_multishop_get_category_tree('', '', '', '', false, false, 'Root'), $this->get['cid'],'class="form-control"');
$search_category_selectbox = '<input type="hidden" name="cid" class="categories_select2_top" id="msAdminSelect2Top" value="' . $this->get['cid'] . '">';
$search_limit = '<select name="tx_multishop_pi1[limit]" class="form-control">';
$limits = array();
$limits[] = '10';
$limits[] = '15';
$limits[] = '20';
$limits[] = '25';
$limits[] = '30';
$limits[] = '40';
$limits[] = '50';
$limits[] = '100';
$limits[] = '150';
$limits[] = '300';
$limits[] = '500';
$limits[] = '750';
if (!in_array($this->get['tx_multishop_pi1']['limit'], $limits)) {
    $limits[] = $this->get['tx_multishop_pi1']['limit'];
}
foreach ($limits as $limit) {
    $search_limit .= '<option value="' . $limit . '"' . ($limit == $this->get['tx_multishop_pi1']['limit'] ? ' selected' : '') . '>' . $limit . '</option>';
}
$search_limit .= '</select>';
// product search
if ($this->ms['MODULES']['FLAT_DATABASE'] and !$this->ms['MODULES']['USE_FLAT_DATABASE_ALSO_IN_ADMIN_PRODUCTS_SEARCH_AND_EDIT']) {
    $this->ms['MODULES']['FLAT_DATABASE'] = 0;
}
$filter = array();
$having = array();
$match = array();
$orderby = array();
$where = array();
$select = array();
if (!$this->ms['MODULES']['FLAT_DATABASE']) {
    $select[] = 'p.products_status';
    $select[] = 'p.product_capital_price';
    $select[] = 'p.products_weight';
    $select[] = 'p.products_quantity';
    $select[] = 's.specials_new_products_price';
}
//$filter[]='p.page_uid='.$this->shop_pid; is already inside the getProductsPageSet
if (isset($this->get['keyword']) and strlen($this->get['keyword']) > 0) {
    switch ($this->get['tx_multishop_pi1']['search_by']) {
        case 'default':
            $search_on_fields = explode(',', $this->conf['adminProductsSearchAndEditStandardCustomSearchOn']);
            $subfilter = array();
            foreach ($search_on_fields as $search_on_field) {
                $search_on_field = trim($search_on_field);
                $subfilter[] = "(" . $search_on_field . " like '%" . addslashes($this->get['keyword']) . "%')";
            }
            if (count($subfilter)) {
                $filter[] = '(' . implode(' OR ', $subfilter) . ')';
            }
            break;
        case 'products_description':
            $prefix = 'pd.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_description like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'products_model':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_model like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'products_weight':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_weight like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'products_quantity':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_quantity like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'products_price':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_price like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'capital_price':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "product_capital_price like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'categories_name':
            $prefix = 'cd.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "categories_name like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'specials_price':
            $prefix = 's.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "specials_new_products_price like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'products_id':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_id like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'manufacturers_name':
            $prefix = 'm.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "manufacturers_name like '" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'products_name':
            $prefix = 'pd.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "products_name like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'ean_code':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "ean_code like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'sku_code':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "sku_code like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'foreign_products_id':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "foreign_products_id like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        case 'vendor_code':
            $prefix = 'p.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = "(" . $prefix . "vendor_code like '%" . addslashes($this->get['keyword']) . "%')";
            break;
        default:
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionSearchByFilterQuery'])) {
                $params = array(
                        'filter' => &$filter,
                        'select' => &$select
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionSearchByFilterQuery'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            break;
    }
}
if (isset($this->get['manufacturers_id']) && !empty($this->get['manufacturers_id']) && $this->get['manufacturers_id'] != 'all') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = $prefix . 'manufacturers_id=' . (int)$this->get['manufacturers_id'];
}
if (isset($this->get['product_condition']) && !empty($this->get['product_condition']) && $this->get['product_condition'] != 'all') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = $prefix . 'products_condition=' . addslashes($this->get['product_condition']);
}
if (isset($this->get['order_unit_id']) && $this->get['order_unit_id'] != '' && $this->get['order_unit_id'] != 'all') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = $prefix . 'order_unit_id=' . (int)$this->get['order_unit_id'];
}
if (isset($this->get['tax_id']) && $this->get['tax_id'] != '' && $this->get['tax_id'] != 'all') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = $prefix . 'tax_id=' . (int)$this->get['tax_id'];
}
if (isset($this->get['product_price_from']) && $this->get['product_price_from'] != '' && isset($this->get['product_price_till']) && $this->get['product_price_till'] != '') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    switch ($this->get['search_by_product_price']) {
        case 'products_price':
            $filter[] = $prefix . 'products_price BETWEEN ' . $this->get['product_price_from'] . ' AND ' . $this->get['product_price_till'];
            break;
        case 'product_capital_price':
            $filter[] = $prefix . 'product_capital_price BETWEEN ' . $this->get['product_price_from'] . ' AND ' . $this->get['product_price_till'];
            break;
        case 'specials_new_products_price':
            $prefix = 's.';
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $prefix = 'pf.';
            }
            $filter[] = $prefix . 'specials_new_products_price BETWEEN ' . $this->get['product_price_from'] . ' AND ' . $this->get['product_price_till'];
            break;
    }
}
if (isset($this->get['product_status']) && $this->get['product_status'] != 'all') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = $prefix . 'products_status=' . addslashes($this->get['product_status']);
}
if (isset($this->get['product_date_from']) && !empty($this->get['product_date_from']) && isset($this->get['product_date_till']) && !empty($this->get['product_date_till'])) {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $date_from = strtotime($this->get['product_date_from']);
    $date_till = strtotime($this->get['product_date_till']);
} else {
    if (isset($this->get['product_date_from']) && !empty($this->get['product_date_from'])) {
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $date_from = strtotime($this->get['product_date_from']);
        $dates_till = '';
    }
    if (isset($this->get['product_date_till']) && !empty($this->get['product_date_till'])) {
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $dates_from = '';
        $date_till = strtotime($this->get['product_date_till']);
    }
}
if ($date_from && $date_till) {
    switch ($this->get['search_by_product_date']) {
        case 'products_date_added':
            if ($date_from && $date_till) {
                $filter[] = $prefix . 'products_date_added BETWEEN ' . $date_from . ' AND ' . $date_till;
            } else {
                if ($date_from) {
                    $filter[] = $prefix . 'products_date_added >= ' . $date_from;
                }
                if ($date_till) {
                    $filter[] = $prefix . 'products_date_added <= ' . $date_till;
                }
            }
            break;
        case 'products_last_modified':
            if ($date_from && $date_till) {
                $filter[] = $prefix . 'products_last_modified BETWEEN ' . $date_from . ' AND ' . $date_till;
            } else {
                if ($date_from) {
                    $filter[] = $prefix . 'products_last_modified >= ' . $date_from;
                }
                if ($date_till) {
                    $filter[] = $prefix . 'products_last_modified <= ' . $date_till;
                }
            }
            break;
        case 'products_date_available':
            if ($date_from && $date_till) {
                $filter[] = $prefix . 'products_date_available BETWEEN ' . $date_from . ' AND ' . $date_till;
            } else {
                if ($date_from) {
                    $filter[] = $prefix . 'products_date_available >= ' . $date_from;
                }
                if ($date_till) {
                    $filter[] = $prefix . 'products_date_available <= ' . $date_till;
                }
            }
            break;
        case 'products_date_visible':
            if ($date_from && $date_till) {
                $filter[] = $prefix . 'products_date_visible BETWEEN ' . $date_from . ' AND ' . $date_till;
            } else {
                if ($date_from) {
                    $filter[] = $prefix . 'products_date_visible >= ' . $date_from;
                }
                if ($date_till) {
                    $filter[] = $prefix . 'products_date_visible <= ' . $date_till;
                }
            }
            break;
    }
}
if (isset($this->get['search_engine']) && $this->get['search_engine'] != 'all') {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = $prefix . 'search_engines_allow_indexing=' . addslashes($this->get['search_engine']);
}
switch ($this->get['tx_multishop_pi1']['order_by']) {
    case 'products_status':
        $order_by = 'p.products_status';
        break;
    case 'products_model':
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'products_model';
        break;
    case 'products_price':
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'products_price';
        break;
    case 'products_weight':
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'products_weight';
        break;
    case 'products_quantity':
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'products_quantity';
        break;
    case 'categories_name':
        $prefix = 'cd.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'categories_name';
        break;
    case 'specials_price':
        $prefix = 's.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'specials_new_products_price';
        break;
    case 'capital_price':
        $prefix = 'p.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'product_capital_price';
        break;
    case 'products_name':
    default:
        $prefix = 'pd.';
        if ($this->ms['MODULES']['FLAT_DATABASE']) {
            $prefix = 'pf.';
        }
        $order_by = $prefix . 'products_name';
        break;
}
switch ($this->get['tx_multishop_pi1']['order']) {
    case 'a':
        $order = 'asc';
        $order_link = 'd';
        break;
    case 'd':
    default:
        $order = 'desc';
        $order_link = 'a';
        break;
}
$orderby[] = $order_by . ' ' . $order;
if (is_numeric($this->get['manufacturers_id'])) {
    $prefix = 'p.';
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $prefix = 'pf.';
    }
    $filter[] = "(" . $prefix . "manufacturers_id='" . addslashes($this->get['manufacturers_id']) . "')";
}
if (is_numeric($this->get['cid']) and $this->get['cid'] > 0) {
    if ($this->ms['MODULES']['FLAT_DATABASE']) {
        $string = '(';
        for ($i = 0; $i < 4; $i++) {
            if ($i > 0) {
                $string .= " or ";
            }
            $string .= "categories_id_" . $i . " = '" . $this->get['cid'] . "'";
        }
        $string .= ')';
        if ($string) {
            $filter[] = $string;
        }
    } else {
        $cats = mslib_fe::get_subcategory_ids($this->get['cid']);
        $cats[] = $this->get['cid'];
        $filter[] = "p2c.categories_id IN (" . implode(",", $cats) . ")";
    }
}
if (is_array($price_filter)) {
    if (!$this->ms['MODULES']['FLAT_DATABASE'] and (isset($price_filter[0]) and $price_filter[1])) {
        $having[] = "(final_price >='" . $price_filter[0] . "' and final_price <='" . $price_filter[1] . "')";
    } elseif (isset($price_filter[0])) {
        $filter[] = "price_filter=" . $price_filter[0];
    }
} elseif ($price_filter) {
    $chars = array();
    $chars[] = '>';
    $chars[] = '<';
    foreach ($chars as $char) {
        if (strstr($price_filter, $char)) {
            $price_filter = str_replace($char, "", $price_filter);
            if ($char == '<') {
                $having[] = "final_price <='" . $price_filter . "'";
            } elseif ($char == '>') {
                $having[] = "final_price >='" . $price_filter . "'";
            }
        }
    }
}
if ($this->ms['MODULES']['FLAT_DATABASE'] and count($having)) {
    $filter[] = $having[0];
    unset($having);
}
if (isset($this->get['stock_from']) && $this->get['stock_from'] != '' && isset($this->get['stock_till']) && $this->get['stock_till'] != '') {
    $prefix = 'p.';
    $filter[] = "(" . $prefix . "products_quantity between " . $this->get['stock_from'] . " and " . $this->get['stock_till'] . ")";
}
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditGetProductsPagesetFilterPreProc'])) {
    $params = array(
            'filter' => &$filter,
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditGetProductsPagesetFilterPreProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// custom page hook that can be controlled by third-party plugin eof
$pageset = mslib_fe::getProductsPageSet($filter, $offset, $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'], $orderby, $having, $select, $where, 0, array(), array(), 'admin_products_search');
$products = $pageset['products'];
$product_tax_rate_js = array();
if ($pageset['total_rows'] > 0) {
    $subpartArray = array();
    $subpartArray['###FORM_ACTION_PRICE_UPDATE_URL###'] = mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_products_search_and_edit&' . mslib_fe::tep_get_all_get_params(array(
                    'tx_multishop_pi1[action]',
                    'p',
                    'Submit',
                    'weergave',
                    'clearcache'
            )));
    $query_string = mslib_fe::tep_get_all_get_params(array(
            'tx_multishop_pi1[action]',
            'tx_multishop_pi1[order_by]',
            'tx_multishop_pi1[order]',
            'p',
            'Submit',
            'weergave',
            'clearcache'
    ));
    $key = 'products_name';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_PRODUCT_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_PRODUCT_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    //
    $key = 'products_model';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_MODEL_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_MODEL_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'products_status';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_VISIBLE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_VISIBLE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'categories_name';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_CATEGORY_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_CATEGORY_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'products_price';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_PRICE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_PRICE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'specials_price';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_SPECIAL_PRICE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_SPECIAL_PRICE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'capital_price';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_CAPITAL_PRICE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_CAPITAL_PRICE_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'products_quantity';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_STOCK_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_STOCK_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $key = 'products_weight';
    if ($this->get['tx_multishop_pi1']['order_by'] == $key) {
        $final_order_link = $order_link;
    } else {
        $final_order_link = 'a';
    }
    $subpartArray['###FOOTER_SORTBY_WEIGHT_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###HEADER_SORTBY_WEIGHT_LINK###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]=' . $key . '&tx_multishop_pi1[order]=' . $final_order_link . '&' . $query_string);
    $subpartArray['###LABEL_HEADER_CELL_NUMBER###'] = $this->pi_getLL('admin_nr');
    $subpartArray['###LABEL_HEADER_PRODUCT###'] = $this->pi_getLL('admin_product');
    $subpartArray['###LABEL_HEADER_MODEL###'] = $this->pi_getLL('admin_model');
    $subpartArray['###LABEL_HEADER_VISIBLE###'] = $this->pi_getLL('admin_visible');
    $subpartArray['###LABEL_HEADER_CATEGORY###'] = $this->pi_getLL('admin_category');
    $subpartArray['###LABEL_HEADER_PRICE###'] = $this->pi_getLL('admin_price');
    $subpartArray['###LABEL_HEADER_SPECIAL_PRICE###'] = $this->pi_getLL('admin_specials_price');
    $subpartArray['###LABEL_HEADER_CAPITAL_PRICE###'] = $this->pi_getLL('capital_price');
    $subpartArray['###LABEL_HEADER_STOCK###'] = $this->pi_getLL('admin_stock');
    $subpartArray['###LABEL_HEADER_WEIGHT###'] = $this->pi_getLL('admin_weight');
    $subpartArray['###LABEL_HEADER_ACTION###'] = $this->pi_getLL('admin_action');
    $subpartArray['###LABEL_FOOTER_CELL_NUMBER###'] = $this->pi_getLL('admin_nr');
    $subpartArray['###LABEL_FOOTER_PRODUCT###'] = $this->pi_getLL('admin_product');
    $subpartArray['###LABEL_FOOTER_MODEL###'] = $this->pi_getLL('admin_model');
    $subpartArray['###LABEL_FOOTER_VISIBLE###'] = $this->pi_getLL('admin_visible');
    $subpartArray['###LABEL_FOOTER_CATEGORY###'] = $this->pi_getLL('admin_category');
    $subpartArray['###LABEL_FOOTER_PRICE###'] = $this->pi_getLL('admin_price');
    $subpartArray['###LABEL_FOOTER_SPECIAL_PRICE###'] = $this->pi_getLL('admin_specials_price');
    $subpartArray['###LABEL_FOOTER_CAPITAL_PRICE###'] = $this->pi_getLL('capital_price');
    $subpartArray['###LABEL_FOOTER_STOCK###'] = $this->pi_getLL('admin_stock');
    $subpartArray['###LABEL_FOOTER_WEIGHT###'] = $this->pi_getLL('admin_weight');
    $subpartArray['###LABEL_FOOTER_ACTION###'] = $this->pi_getLL('admin_action');
    // custom page hook that can be controlled by third-party plugin
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditTmplPreProc'])) {
        $params = array(
                'subpartArray' => &$subpartArray
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditTmplPreProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    // custom page hook that can be controlled by third-party plugin eof
    $s = 0;
    $productsItem = '';
    foreach ($products as $rs) {
        if ($switch == 'odd') {
            $switch = 'even';
        } else {
            $switch = 'odd';
        }
        if ($rs['specials_new_products_price'] == 0 || empty($rs['specials_new_products_price'])) {
            $rs['specials_new_products_price'] = '';
        }
        $link_edit_cat = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=edit_category&cid=' . $rs['categories_id'] . '&action=edit_category');
        $link_edit_prod = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=edit_product&pid=' . $rs['products_id'] . '&cid=' . $rs['categories_id'] . '&action=edit_product');
        $link_delete_prod = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=delete_product&pid=' . $rs['products_id'] . '&action=delete_product&cid=' . $rs['categories_id']);
        // view product link
        $where = '';
        if ($rs['categories_id']) {
            // get all cats to generate multilevel fake url
            $level = 0;
            $cats = mslib_fe::Crumbar($rs['categories_id']);
            $cats = array_reverse($cats);
            $where = '';
            if (count($cats) > 0) {
                foreach ($cats as $cat) {
                    $where .= "categories_id[" . $level . "]=" . $cat['id'] . "&";
                    $level++;
                }
                $where = substr($where, 0, (strlen($where) - 1));
                $where .= '&';
            }
            // get all cats to generate multilevel fake url eof
        }
        $product_detail_link = mslib_fe::typolink($this->conf['products_detail_page_pid'], '&' . $where . '&products_id=' . $rs['products_id'] . '&tx_multishop_pi1[page_section]=products_detail');
        // view product link eof
        $tmp_product_categories = mslib_fe::getProductToCategories($rs['products_id'], $rs['categories_id']);
        $product_categories = explode(',', $tmp_product_categories);
        $cat_crumbar = '';
        foreach ($product_categories as $product_category) {
            $cat_crumbar .= '<ul class="msAdminCategoriesCrum list-inline">';
            $cats = mslib_fe::Crumbar($product_category);
            $teller = 0;
            $total = count($cats);
            for ($i = ($total - 1); $i >= 0; $i--) {
                $teller++;
                // get all cats to generate multilevel fake url eof
                if ($total == $teller) {
                    $class = 'lastItem';
                } else {
                    $class = '';
                }
                $cat_crumbar .= '<li class="' . $class . '"><a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=edit_category&cid=' . $cats[$i]['id'] . '&action=edit_category') . '">' . $cats[$i]['name'] . '</a></li>';
            }
            $cat_crumbar .= '</ul>';
        }
        $status = '';
        // fix for the flat table
        if (isset($rs['products_status'])) {
            if (!$rs['products_status']) {
                $status .= '<span class="admin_status_red" alt="Disable"></span>';
                $status .= '<a href="#" class="update_product_status" rel="' . $rs['products_id'] . '"><span class="admin_status_green disabled" alt="Enabled"></span></a>';
            } else {
                $status .= '<a href="#" class="update_product_status" rel="' . $rs['products_id'] . '"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
                $status .= '<span class="admin_status_green" alt="Enable"></span>';
            }
        } else {
            $status .= '<a href="#" class="update_product_status" rel="' . $rs['products_id'] . '"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
            $status .= '<span class="admin_status_green" alt="Enable"></span>';
        }
        $product_tax_rate = 0;
        $data = mslib_fe::getTaxRuleSet($rs['tax_id'], 0);
        $product_tax_rate = $data['total_tax_rate'];
        $product_tax_rate_js[] = 'product_tax_rate_js["' . $rs['products_id'] . '"]="' . $data['total_tax_rate'] . '";';
        $product_tax = mslib_fe::taxDecimalCrop(($rs['products_price'] * $product_tax_rate) / 100);
        $product_price_display = mslib_fe::taxDecimalCrop($rs['products_price'], 2, false);
        $product_price_display_incl = mslib_fe::taxDecimalCrop($rs['products_price'] + $product_tax, 2, false);
        $special_tax = mslib_fe::taxDecimalCrop(($rs['specials_new_products_price'] * $product_tax_rate) / 100);
        $special_price_display = mslib_fe::taxDecimalCrop($rs['specials_new_products_price'], 2, false);
        $special_price_display_incl = mslib_fe::taxDecimalCrop($rs['specials_new_products_price'] + $special_tax, 2, false);
        $capital_tax = mslib_fe::taxDecimalCrop(($rs['product_capital_price'] * $product_tax_rate) / 100);
        $capital_price_display = mslib_fe::taxDecimalCrop($rs['product_capital_price'], 2, false);
        $capital_price_display_incl = mslib_fe::taxDecimalCrop($rs['product_capital_price'] + $capital_tax, 2, false);
        $markerArray = array();
        $markerArray['ROW_TYPE'] = $switch;
        $markerArray['CATEGORY_ID0'] = $rs['categories_id'];
        $markerArray['CHECKBOX_COUNTER0'] = $s;
        $markerArray['CHECKBOX_COUNTER1'] = $s;
        $markerArray['CELL_NUMBER'] = (($p * $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']) + $s + 1);
        $markerArray['PRODUCT_NAME'] = ($rs['products_name'] ? $rs['products_name'] : $this->pi_getLL('no_name'));
        $markerArray['PRODUCT_CATEGORIES_CRUMBAR'] = $cat_crumbar;
        $markerArray['PRODUCT_MODEL'] = $rs['products_model'];
        $markerArray['PRODUCT_STATUS'] = $status;
        $markerArray['LINK_EDIT_CAT'] = $link_edit_cat;
        $markerArray['CATEGORY_NAME'] = $rs['categories_name'];
        $markerArray['VALUE_TAX_ID'] = $rs['tax_id'];
        $markerArray['CURRENCY0'] = mslib_fe::currency();
        $markerArray['CURRENCY1'] = mslib_fe::currency();
        $markerArray['CURRENCY2'] = mslib_fe::currency();
        $markerArray['CURRENCY3'] = mslib_fe::currency();
        $markerArray['CURRENCY4'] = mslib_fe::currency();
        $markerArray['CURRENCY5'] = mslib_fe::currency();
        $markerArray['SUFFIX_PRICE_EXCL_VAT'] = $this->pi_getLL('excluding_vat');
        $markerArray['SUFFIX_PRICE_INCL_VAT'] = $this->pi_getLL('including_vat');
        $markerArray['SUFFIX_SPECIAL_PRICE_EXCL_VAT'] = $this->pi_getLL('excluding_vat');
        $markerArray['SUFFIX_SPECIAL_PRICE_INCL_VAT'] = $this->pi_getLL('including_vat');
        $markerArray['SUFFIX_CAPITAL_PRICE_EXCL_VAT'] = $this->pi_getLL('excluding_vat');
        $markerArray['SUFFIX_CAPITAL_PRICE_INCL_VAT'] = $this->pi_getLL('including_vat');
        $markerArray['VALUE_PRICE_EXCL_VAT'] = htmlspecialchars($product_price_display);
        $markerArray['VALUE_PRICE_INCL_VAT'] = htmlspecialchars($product_price_display_incl);
        $markerArray['VALUE_ORIGINAL_PRICE'] = $rs['products_price'];
        $markerArray['VALUE_SPECIAL_PRICE_EXCL_VAT'] = htmlspecialchars($special_price_display);
        $markerArray['VALUE_SPECIAL_PRICE_INCL_VAT'] = htmlspecialchars($special_price_display_incl);
        $markerArray['VALUE_ORIGINAL_SPECIAL_PRICE'] = $rs['specials_new_products_price'];
        $markerArray['VALUE_CAPITAL_PRICE_EXCL_VAT'] = htmlspecialchars($capital_price_display);
        $markerArray['VALUE_CAPITAL_PRICE_INCL_VAT'] = htmlspecialchars($capital_price_display_incl);
        $markerArray['VALUE_ORIGINAL_CAPITAL_PRICE'] = $rs['product_capital_price'];
        $markerArray['VALUE_PRODUCT_QUANTITY'] = $rs['products_quantity'];
        $markerArray['VALUE_PRODUCT_WEIGHT'] = $rs['products_weight'];
        $markerArray['PID0'] = $rs['products_id'];
        $markerArray['PID1'] = $rs['products_id'];
        $markerArray['PID2'] = $rs['products_id'];
        $markerArray['PID3'] = $rs['products_id'];
        $markerArray['PID4'] = $rs['products_id'];
        $markerArray['PID5'] = $rs['products_id'];
        $markerArray['PID6'] = $rs['products_id'];
        $markerArray['PID7'] = $rs['products_id'];
        $markerArray['PID8'] = $rs['products_id'];
        $markerArray['PID9'] = $rs['products_id'];
        $markerArray['PID10'] = $rs['products_id'];
        $markerArray['PID11'] = $rs['products_id'];
        $markerArray['EDIT_PRODUCT_LINK0'] = $link_edit_prod;
        $markerArray['EDIT_PRODUCT_LINK1'] = $link_edit_prod;
        $markerArray['PRODUCT_DETAIL_LINK'] = $product_detail_link;
        $markerArray['DELETE_PRODUCT_LINK'] = $link_delete_prod;
        // custom page hook that can be controlled by third-party plugin
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditTmplIteratorPreProc'])) {
            $params = array(
                    'markerArray' => &$markerArray,
                    'rs' => &$rs
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditTmplIteratorPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        // custom page hook that can be controlled by third-party plugin eof
        $productsItem .= $this->cObj->substituteMarkerArray($subparts['products_item'], $markerArray, '###|###');
        $s++;
    }
    $actions = array();
    $actions['move'] = $this->pi_getLL('move_selected_products_to') . ':';
    $actions['duplicate'] = $this->pi_getLL('duplicate_selected_products_to') . ':';
    $actions['delete'] = $this->pi_getLL('delete_selected_products');
    // custom page hook that can be controlled by third-party plugin
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionItemsPreProc'])) {
        $params = array(
                'actions' => &$actions
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionItemsPreProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    // custom page hook that can be controlled by third-party plugin eof
    $action_selectbox .= '<select name="tx_multishop_pi1[action]" id="products_search_action" class="form-control"><option value="">' . $this->pi_getLL('choose_action') . '</option>';
    foreach ($actions as $key => $value) {
        $action_selectbox .= '<option value="' . $key . '">' . $value . '</option>';
    }
    $action_selectbox .= '</select>';
    //$input_categories_selectbox=mslib_fe::tx_multishop_draw_pull_down_menu('tx_multishop_pi1[target_categories_id]', mslib_fe::tx_multishop_get_category_tree('', '', ''), '', 'class="form-control" id="target_categories_id"');
    $input_categories_selectbox = '<div id="target_categories_id"><input type="hidden" name="tx_multishop_pi1[target_categories_id]" class="categories_select2" id="msAdminSelect2Bottom"></div>';
    $dlink = "location.href = '/" . mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_price_update_dl_xls') . "'";
    if (isset($this->get['cid']) && $this->get['cid'] > 0) {
        $dlink = "location.href = '/" . mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=admin_price_update_dl_xls&cid=' . $this->get['cid']) . "'";
    }
    $pagination = '';
    $content = '';
    $this->ms['MODULES']['PAGESET_LIMIT'] = $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
    // pagination
    if (!$this->ms['nopagenav'] and $pageset['total_rows'] > $this->ms['MODULES']['PAGESET_LIMIT']) {
        require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/admin_pages/includes/admin_pagination.php');
        $pagination = $tmp;
    }
    // pagination eof
    $content = '';
    $subpartArray['###PAGE_NUMBER###'] = $this->get['p'];
    $subpartArray['###CATEGORY_ID1###'] = $this->get['cid'];
    $subpartArray['###INPUT_ACTION_SELECTBOX###'] = $action_selectbox;
    $subpartArray['###INPUT_CATEGORIES_SELECTBOX###'] = $input_categories_selectbox;
    $subpartArray['###LABEL_ADMIN_SUBMIT###'] = $this->pi_getLL('submit_form');
    $subpartArray['###LABEL_DOWNLOAD_AS_EXCEL_FILE###'] = $this->pi_getLL('admin_download_as_excel_file');
    $subpartArray['###DOWNLOAD_AS_EXCEL_URL###'] = $dlink;
    $subpartArray['###LABEL_UPDATE_MODIFIED_PRODUCTS###'] = $this->pi_getLL('update_modified_products');
    $subpartArray['###FORM_UPLOAD_ACTION_URL###'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_price_update_up_xls');
    $subpartArray['###CATEGORY_ID2###'] = $this->get['cid'];
    $subpartArray['###PRODUCTS_PAGINATION###'] = $pagination;
    $subpartArray['###LABEL_UPLOAD_EXCEL_FILE###'] = $this->pi_getLL('admin_upload_excel_file');
    $subpartArray['###LABEL_ADMIN_UPLOAD###'] = $this->pi_getLL('admin_upload');
    $subpartArray['###LABEL_BACK_TO_CATALOG###'] = $this->pi_getLL('admin_close_and_go_back_to_catalog');
    $subpartArray['###BACK_TO_CATALOG_LINK###'] = mslib_fe::typolink();
    $subpartArray['###LABEL_ADMIN_YES###'] = $this->pi_getLL('admin_yes');
    $subpartArray['###LABEL_ADMIN_NO###'] = $this->pi_getLL('admin_no');
    $subpartArray['###ADMIN_LABEL_ENABLE0###'] = $this->pi_getLL('admin_label_enable');
    $subpartArray['###ADMIN_LABEL_DISABLE0###'] = $this->pi_getLL('admin_label_disable');
    $subpartArray['###ADMIN_LABEL_ENABLE1###'] = $this->pi_getLL('admin_label_enable');
    $subpartArray['###ADMIN_LABEL_DISABLE1###'] = $this->pi_getLL('admin_label_disable');
    $subpartArray['###PRODUCTS_ITEM###'] = $productsItem;
    $tmp_content_results = $this->cObj->substituteMarkerArrayCached($subparts['results'], array(), $subpartArray);
} else {
    $subpartArray = array();
    $subpartArray['###LABEL_BACK_TO_CATALOG###'] = $this->pi_getLL('admin_close_and_go_back_to_catalog');
    $subpartArray['###BACK_TO_CATALOG_LINK###'] = mslib_fe::typolink();
    $subpartArray['###LABEL_NO_RESULT###'] = $this->pi_getLL('no_products_available');
    $tmp_content_noresults = $this->cObj->substituteMarkerArrayCached($subparts['noresults'], array(), $subpartArray);
}
$subpartArray = array();
$subpartArray['###POST_MESSAGE###'] = '';
if ($postMessageArray) {
    $postmessage = '<div id="postMessage"><h3>System message</h3><ul>';
    foreach ($postMessageArray as $item) {
        $postmessage .= '<li>' . $item . '</li>';
    }
    $postmessage .= '</ul></div>';
    $subpartArray['###POST_MESSAGE###'] = $postmessage;
}
$subpartArray['###SHOP_PID###'] = $this->shop_pid;
$subpartArray['###UNFOLD_SEARCH_BOX###'] = '';
if ((isset($this->get['stock_from']) && !empty($this->get['stock_from'])) ||
        (isset($this->get['stock_till']) && !empty($this->get['stock_till'])) ||
        (isset($this->get['manufacturers_id']) && !empty($this->get['manufacturers_id']) && $this->get['manufacturers_id'] != '0') ||
        (isset($this->get['product_condition']) && !empty($this->get['product_condition']) && $this->get['product_condition'] != 'all') ||
        (isset($this->get['order_unit_id']) && !empty($this->get['order_unit_id']) && $this->get['order_unit_id'] != 'all') ||
        (isset($this->get['tax_id']) && $this->get['tax_id'] != '' && $this->get['tax_id'] != 'all') ||
        (isset($this->get['product_price_from']) && !empty($this->get['product_price_from'])) ||
        (isset($this->get['product_price_till']) && !empty($this->get['product_price_till'])) ||
        (isset($this->get['product_status']) && $this->get['product_status'] != 'all') ||
        (isset($this->get['product_date_from']) && !empty($this->get['product_date_from'])) ||
        (isset($this->get['product_date_till']) && !empty($this->get['product_date_till'])) ||
        (isset($this->get['search_engine']) && $this->get['search_engine'] != 'all')
) {
    $subpartArray['###UNFOLD_SEARCH_BOX###'] = ' in';
}
$subpartArray['###LABEL_STOCK_FROM###'] = $this->pi_getLL('from');
$subpartArray['###LABEL_STOCK###'] = $this->pi_getLL('stock');
$subpartArray['###VALUE_STOCK_FROM###'] = $this->get['stock_from'];
$subpartArray['###LABEL_STOCK_TO###'] = $this->pi_getLL('to');
$subpartArray['###VALUE_STOCK_TO###'] = $this->get['stock_till'];
$subpartArray['###PAGE_HEADER###'] = $this->pi_getLL('products');
$subpartArray['###LABEL_SEARCH_KEYWORD###'] = $this->pi_getLL('admin_search_for');
$subpartArray['###VALUE_SEARCH_KEYWORD###'] = ((isset($this->get['keyword'])) ? htmlspecialchars($this->get['keyword']) : '');
$subpartArray['###LABEL_SEARCH_BY###'] = $this->pi_getLL('search_by');
$subpartArray['###SEARCH_BY_SELECTBOX###'] = $searchby_selectbox;
$subpartArray['###LABEL_SEARCH_IN###'] = ucfirst($this->pi_getLL('in'));
$subpartArray['###SEACRH_IN_CATEGORY_TREE_SELECTBOX###'] = $search_category_selectbox;
$subpartArray['###LABEL_SEARCH_LIMIT###'] = $this->pi_getLL('limit_number_of_records_to');
$subpartArray['###SEARCH_LIMIT###'] = $search_limit;
$subpartArray['###LABEL_ADVANCED_SEARCH###'] = $this->pi_getLL('advanced_search');
$subpartArray['###LABEL_SEARCH###'] = $this->pi_getLL('search');
$subpartArray['###LABEL_RESET_ADVANCED_SEARCH_FILTER###'] = $this->pi_getLL('reset_advanced_search_filter');
// advanced search label
$subpartArray['###LABEL_MANUFACTURERS###'] = $this->pi_getLL('manufacturers');
$subpartArray['###VALUE_MANUFACTURERS###'] = $this->get['manufacturers_id'];
$subpartArray['###LABEL_PRODUCT_CONDITION###'] = $this->pi_getLL('feed_exporter_fields_label_products_condition');
$subpartArray['###PRODUCT_CONDITION_SELECTBOX###'] = $product_condition_selectbox;
$subpartArray['###CONDITION_NEW_SELECTED###'] = ($this->get['product_condition'] == 'new' ? ' selected' : '');
$subpartArray['###CONDITION_USED_SELECTED###'] = ($this->get['product_condition'] == 'used' ? ' selected' : '');
$subpartArray['###CONDITION_REFURBISHED_SELECTED###'] = ($this->get['product_condition'] == 'refurbished' ? ' selected' : '');
$subpartArray['###LABEL_CONDITION_NEW###'] = $this->pi_getLL('new');
$subpartArray['###LABEL_CONDITION_USED###'] = $this->pi_getLL('used');
$subpartArray['###LABEL_CONDITION_REFURBISHED###'] = $this->pi_getLL('refurbished');
$subpartArray['###LABEL_CONDITION_ALL###'] = $this->pi_getLL('all');
// order unit
$order_unit_selectbox = '<select name="order_unit_id" class="form-control">';
$str = "SELECT o.id, o.code, od.name from tx_multishop_order_units o, tx_multishop_order_units_description od where (o.page_uid='" . $this->shop_pid . "' or o.page_uid=0) and o.id=od.order_unit_id and od.language_id='0' order by od.name asc";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$order_unit_selectbox .= '<option value="all">' . $this->pi_getLL('all') . '</option>';
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $order_unit_selectbox .= '<option value="' . $row['id'] . '" ' . (($row['id'] == $this->get['order_unit_id']) ? 'selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
}
$order_unit_selectbox .= '</select>';
$subpartArray['###LABEL_ORDER_UNIT###'] = $this->pi_getLL('admin_order_units');
$subpartArray['###ORDER_UNIT_SELECTBOX###'] = $order_unit_selectbox;
// tax rate
$tax_rate_selectbox = '<select name="tax_id" id="tax_id" class="form-control">';
$str = "SELECT trg.*, t.rate FROM `tx_multishop_tax_rule_groups` trg, `tx_multishop_tax_rules` tr, `tx_multishop_taxes` t where trg.rules_group_id=tr.rules_group_id and tr.tax_id=t.tax_id group by trg.rules_group_id order by trg.rules_group_id asc";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$product_tax_rate = 0;
$data = mslib_fe::getTaxRuleSet($product['tax_id'], $product['products_price']);
$product_tax_rate = $data['total_tax_rate'];
$tax_list_data = array();
$tax_rate_selectbox .= '<option value="all">' . $this->pi_getLL('all') . '</option>
<option value="0"' . ($this->get['tax_id'] == '0' ? ' selected' : '') . '>' . $this->pi_getLL('admin_no_tax') . '</option>
';
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $tax_rate_selectbox .= '<option value="' . $row['rules_group_id'] . '" ' . (($row['rules_group_id'] == $this->get['tax_id']) ? 'selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
}
$tax_rate_selectbox .= '</select>';
$subpartArray['###LABEL_TAX_RATE###'] = $this->pi_getLL('admin_taxes');
$subpartArray['###TAX_RATE_SELECTBOX###'] = $tax_rate_selectbox;
// product date filter
$subpartArray['###LABEL_DATE###'] = $this->pi_getLL('date');
$subpartArray['###LABEL_DATE_FROM###'] = $this->pi_getLL('from');
$subpartArray['###LABEL_DATE_TO###'] = $this->pi_getLL('to');
$subpartArray['###VALUE_DATE_FROM###'] = '';
$subpartArray['###VALUE_DATE_FROM_VISUAL###'] = '';
if ($this->get['product_date_from']) {
    $subpartArray['###VALUE_DATE_FROM###'] = date('Y-m-d H:i:s', strtotime($this->get['product_date_from']));
    $subpartArray['###VALUE_DATE_FROM_VISUAL###'] = date($this->pi_getLL('locale_datetime_format'), strtotime($this->get['product_date_from']));
}
$subpartArray['###VALUE_DATE_TO###'] = '';
$subpartArray['###VALUE_DATE_TO_VISUAL###'] = '';
if ($this->get['product_date_till']) {
    $subpartArray['###VALUE_DATE_TO###'] = date('Y-m-d H:i:s', strtotime($this->get['product_date_till']));
    $subpartArray['###VALUE_DATE_TO_VISUAL###'] = date($this->pi_getLL('locale_datetime_format'), strtotime($this->get['product_date_till']));
}
$subpartArray['###DATE_TIME_JS_FORMAT0###'] = $this->pi_getLL('locale_date_format_js');
$subpartArray['###DATE_TIME_JS_FORMAT1###'] = $this->pi_getLL('locale_date_format_js');
$subpartArray['###LABEL_FILTER_DATE_ADDED###'] = $this->pi_getLL('date_added');
$subpartArray['###LABEL_FILTER_LAST_MODIFIED###'] = $this->pi_getLL('modified');
$subpartArray['###LABEL_FILTER_DATE_AVAILABLE###'] = $this->pi_getLL('products_date_available');
$subpartArray['###LABEL_FILTER_DATE_VISIBLE###'] = $this->pi_getLL('products_visible');
$subpartArray['###FILTER_BY_DATE_ADDED_CHECKED###'] = (!isset($this->get['search_by_product_date']) || $this->get['search_by_product_date'] == 'products_date_added' ? ' checked="checked"' : '');
$subpartArray['###FILTER_BY_LAST_MODIFIED_CHECKED###'] = ($this->get['search_by_product_date'] == 'products_last_modified' ? ' checked="checked"' : '');
$subpartArray['###FILTER_BY_DATE_AVAILABLE_CHECKED###'] = ($this->get['search_by_product_date'] == 'products_date_available' ? ' checked="checked"' : '');
$subpartArray['###FILTER_BY_DATE_VISIBLE_CHECKED###'] = ($this->get['search_by_product_date'] == 'products_date_visible' ? ' checked="checked"' : '');
// product price filter
$subpartArray['###LABEL_PRICE###'] = $this->pi_getLL('price');
$subpartArray['###LABEL_PRICE_FROM###'] = $this->pi_getLL('from');
$subpartArray['###LABEL_PRICE_TO###'] = $this->pi_getLL('to');
$subpartArray['###VALUE_PRICE_FROM###'] = $this->get['product_price_from'];
$subpartArray['###VALUE_PRICE_TO###'] = $this->get['product_price_till'];
$subpartArray['###LABEL_FILTER_PRODUCTS_PRICE###'] = $this->pi_getLL('admin_price');
$subpartArray['###LABEL_FILTER_SPECIALS_PRICE###'] = $this->pi_getLL('admin_specials_price');
$subpartArray['###LABEL_FILTER_PRODUCTS_CAPITAL_PRICE###'] = $this->pi_getLL('capital_price');
$subpartArray['###FILTER_BY_PRODUCTS_PRICE_CHECKED###'] = (!isset($this->get['search_by_product_price']) || $this->get['search_by_product_price'] == 'products_price' ? ' checked="checked"' : '');
$subpartArray['###FILTER_BY_PRODUCTS_SPECIALS_PRICE_CHECKED###'] = ($this->get['search_by_product_price'] == 'specials_new_products_price' ? ' checked="checked"' : '');
$subpartArray['###FILTER_BY_PRODUCTS_CAPITAL_PRICE_CHECKED###'] = ($this->get['search_by_product_price'] == 'product_capital_price' ? ' checked="checked"' : '');
// product_status
$product_status_selectbox = '<select name="product_status" id="product_status" class="form-control">';
$product_status_selectbox .= '<option value="all">' . $this->pi_getLL('all') . '</option>
<option value="1"' . ($this->get['product_status'] == '1' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('enabled') . '</option>
<option value="0"' . ($this->get['product_status'] == '0' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('disabled') . '</option>
';
$product_status_selectbox .= '</select>';
$subpartArray['###LABEL_PRODUCT_STATUS###'] = $this->pi_getLL('admin_visible');
$subpartArray['###PRODUCT_STATUS_SELECTBOX###'] = $product_status_selectbox;
// search_engine indexing
$search_engine_selectbox = '<select name="search_engine" id="search_engine" class="form-control">';
$search_engine_selectbox .= '<option value="all">' . $this->pi_getLL('all') . '</option>
<option value="1"' . ($this->get['search_engine'] == '1' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('admin_yes') . '</option>
<option value="0"' . ($this->get['search_engine'] == '0' ? ' selected="selected"' : '') . '>' . $this->pi_getLL('admin_no') . '</option>
';
$search_engine_selectbox .= '</select>';
$subpartArray['###LABEL_SEARCH_ENGINE_INDEXING###'] = $this->pi_getLL('search_engine_indexing');
$subpartArray['###SEARCH_ENGINE_INDEXING_SELECTBOX###'] = $search_engine_selectbox;
//
$subpartArray['###AJAX_UPDATE_PRODUCT_STATUS_URL###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=update_products_status');
$subpartArray['###AJAX_PRODUCT_CATEGORIES_FULL0###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree&tx_multishop_pi1[includeDisabledCats]=1');
$subpartArray['###AJAX_PRODUCT_CATEGORIES_GET_VALUE0###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues');
$subpartArray['###AJAX_PRODUCT_CATEGORIES_FULL1###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree');
$subpartArray['###AJAX_PRODUCT_CATEGORIES_GET_VALUE1###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues');
$subpartArray['###AJAX_GET_TAX_RULESET_URL0###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset');
$subpartArray['###AJAX_GET_TAX_RULESET_URL1###'] = mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=get_tax_ruleset');
//
$subpartArray['###RESULTS###'] = $tmp_content_results;
$subpartArray['###NORESULTS###'] = $tmp_content_noresults;
// Instantiate admin interface object
$objRef = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface');
$objRef->init($this);
$objRef->setInterfaceKey('admin_products');
// Header buttons
$headerButtons = array();
$headingButton = array();
$headingButton['btn_class'] = 'btn btn-primary';
$headingButton['fa_class'] = 'fa fa-plus-circle';
$headingButton['title'] = $this->pi_getLL('admin_create_new_products_here');
$headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=add_product&action=add_product');
$headerButtons[] = $headingButton;
// Create category button
$headingButton['btn_class'] = 'btn btn-primary';
$headingButton['fa_class'] = 'fa fa-plus-circle';
$headingButton['title'] = $this->pi_getLL('admin_add_new_category_to_the_catalog');
$headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=add_category&action=add_category');
$headerButtons[] = $headingButton;
// Create multiple categories button
$headingButton = array();
$headingButton['btn_class'] = 'btn btn-primary';
$headingButton['fa_class'] = 'fa fa-plus-circle';
$headingButton['title'] = $this->pi_getLL('admin_add_new_multiple_category_to_the_catalog', 'Add new categories simultaneous');
$headingButton['href'] = mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=add_multiple_category&action=add_multiple_category');
$headerButtons[] = $headingButton;
// Set header buttons through interface class so other plugins can adjust it
$objRef->setHeaderButtons($headerButtons);
// Get header buttons through interface class so we can render them
$interfaceHeaderButtons = $objRef->renderHeaderButtons();
// Get header buttons through interface class so we can render them
$subpartArray['###INTERFACE_HEADER_BUTTONS###'] = $objRef->renderHeaderButtons();
// extra input
$extra_advanced_search_input = array();
$extra_advanced_search_input_new_row = array();
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionMarkerPostProc'])) {
    $params = array(
            'subpartArray' => &$subpartArray,
            'extra_advanced_search_input' => &$extra_advanced_search_input,
            'extra_advanced_search_input_new_row' => &$extra_advanced_search_input_new_row
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_products_search_and_edit.php']['adminProductsSearchAndEditActionMarkerPostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// custom page hook that can be controlled by third-party plugin eof
$subpartArray['###EXTRA_ADVANCED_SEARCH_INPUT###'] = implode('', $extra_advanced_search_input);
$subpartArray['###EXTRA_ADVANCED_SEARCH_INPUT_NEW_ROW###'] = implode('', $extra_advanced_search_input_new_row);
$content .= $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$content = $prepending_content . '<div class="fullwidth_div">' . mslib_fe::shadowBox($content) . '</div>';
$GLOBALS['TSFE']->additionalHeaderData[] = '<script type="text/javascript" data-ignore="1">
jQuery(document).ready(function(){
    $(\'#manufacturers_id_s2\').select2({
		placeholder: \'' . $this->pi_getLL('admin_choose_manufacturer') . '\',
		dropdownCssClass: "", // apply css that makes the dropdown taller
		width:\'100%\',
		minimumInputLength: 0,
		multiple: false,
		//allowClear: true,
		query: function(query) {
			$.ajax(\'' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=getManufacturersList') . '\', {
				data: {
					q: query.term
				},
				dataType: "json"
			}).done(function(data) {
				query.callback({results: data});
			});
		},
		initSelection: function(element, callback) {
			var id=$(element).val();
			if (id!=="") {
				var split_id=id.split(",");
				var callback_data=[];
				$.ajax(\'' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=getManufacturersList') . '\', {
					data: {
						preselected_id: id
					},
					dataType: "json"
				}).done(function(data) {
					$.each(data, function(i,val){
						callback(val);
					});

				});
			}
		},
		formatResult: function(data){
			if (data.text === undefined) {
				$.each(data, function(i,val){
					return val.text;
				});
			} else {
				return data.text;
			}
		},
		formatSelection: function(data){
			if (data.text === undefined) {
				$.each(data, function(i,val){
					return val.text;
				});
			} else {
				return data.text;
			}
		},
		escapeMarkup: function (m) { return m; }
	});
    $(document).on("click", "#reset-advanced-search", function(e){
        location.href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&cid=') . '";
    });    
});
var product_tax_rate_js=[];
' . implode("\n", $product_tax_rate_js) . '
</script>
';
?>
