<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
header("Content-Type:application/json; charset=UTF-8");
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
    $this->ms['MODULES']['CACHE_FRONT_END'] = 0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
    $options = array(
            'caching' => true,
            'cacheDir' => $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/cache/',
            'lifeTime' => $this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
    );
    $Cache_Lite = new Cache_Lite($options);
    $string = md5('ajax_products_staffelprice_search_' . $this->shop_pid . '_' . $_REQUEST['pid']);
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content = $Cache_Lite->get($string))) {
    if ($_REQUEST['pid']) {
        $this->get['pid'] = (int)$_REQUEST['pid'];
        $this->get['qty'] = (int)$_REQUEST['qty'];
    }
    if ($_REQUEST['pid'] and strlen($_REQUEST['pid']) < 1) {
        exit();
    }
    $product = mslib_fe::getProduct($this->get['pid'], '', '', 1, 1);
    $quantity = $this->get['qty'];
    if ($product['staffel_price']) {
        if ($this->ms['MODULES']['MAKE_FIRST_LEVEL_OF_STEPPING_PRICE_EDITABLE'] == '1') {
            $product['staffel_price'] = mslib_fe::rebuildStaffelPrice($product['staffel_price'], $product['final_price']);
        }
        $staffel_price['price'] = (mslib_fe::calculateStaffelPrice($product['staffel_price'], $quantity) / $quantity);
    } else {
        $staffel_price['price'] = ($product['final_price']);
    }
    //if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
    $staffel_price['price_include_vat'] = $staffel_price['price'] + ($staffel_price['price'] * $product['tax_rate']);
    //}
    $staffel_price['display_price'] = number_format($staffel_price['price'], 2, '.', '');
    $staffel_price['display_price_include_vat'] = number_format($staffel_price['price_include_vat'], 2, '.', '');
    //
    $staffel_price['use_tax_id'] = true;
    if (isset($this->get['oid']) && is_numeric($this->get['oid']) && $this->get['oid'] > 0) {
        $orders = mslib_fe::getOrder($this->get['oid']);
        if (!$customer_address = mslib_fe::getAddressInfo('customer', $orders['customer_id'])) {
            $customer_address['country'] = $order['billing_country'];
            $customer_address['region'] = $order['billing_region'];
        }
        if (!isset($customer_address['country']) && isset($customer_address['default']['country'])) {
            $customer_address = $customer_address['default'];
        }
        $iso_customer = mslib_fe::getCountryByName($orders['billing_country']);
        $iso_customer['country'] = $iso_customer['cn_short_en'];
        if (!empty($customer_address['region'])) {
            $zone = mslib_fe::getRegionByName($customer_address['region']);
        } else {
            $zone['zn_country_iso_nr'] = 0;
        }
        $vat_id = $orders['billing_vat_id'];
        // hook for adding new fieldsets into edit_order
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/ajax_products_staffelprice_search.php']['ajaxProductsStaffelPriceSearchExistingOrder'])) {
            $params = array(
                    'vat_id' => &$vat_id,
                    'orders' => &$orders
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/ajax_products_staffelprice_search.php']['ajaxProductsStaffelPriceSearchExistingOrder'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
            // hook oef
        }
        $this->ms['MODULES']['DISABLE_VAT_RATE'] = 0;
        if (strtolower($iso_customer['country']) != strtolower($this->tta_shop_info['country'])) {
            if ($this->ms['MODULES']['DISABLE_VAT_FOR_FOREIGN_CUSTOMERS_WITH_COMPANY_VAT_ID'] and $vat_id) {
                $this->ms['MODULES']['DISABLE_VAT_RATE'] = 1;
            }
            $sql_tax_sb = $GLOBALS['TYPO3_DB']->SELECTquery('t.tax_id, t.rate, t.name, trg.default_status', // SELECT ...
                    'tx_multishop_taxes t, tx_multishop_tax_rules tr, tx_multishop_tax_rule_groups trg', // FROM ...
                    't.tax_id=tr.tax_id and tr.rules_group_id=trg.rules_group_id and trg.status=1 and tr.cn_iso_nr=\'' . $iso_customer['cn_iso_nr'] . '\' and tr.zn_country_iso_nr = \'' . addslashes($zone['zn_country_iso_nr']) . '\' and tr.rules_group_id = \'' . addslashes($product['tax_id']) . '\'', // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry_tax_sb = $GLOBALS['TYPO3_DB']->sql_query($sql_tax_sb);
            $tax_id_data = array();
            $default_tax_id = 0;
            while ($rs_tx_sb = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tax_sb)) {
                $tax_id_data[$rs_tx_sb['tax_id']] = $rs_tx_sb;
                if ($rs_tx_sb['default_status'] > 0) {
                    $default_tax_id = $rs_tx_sb['tax_id'];
                }
            }
            // if at this point $default_tax_id still 0 it mean there are mis-settings in tax interface (multishop backend)
            if ($tax_id_data[$default_tax_id]['tax_id'] > 0) {
                if ($tax_id_data[$default_tax_id]['rate'] < 0.1) {
                    //$staffel_price['use_tax_id']=false;
                    $this->ms['MODULES']['DISABLE_VAT_RATE'] = 1;
                }
                $product['tax_id'] = $tax_id_data[$default_tax_id]['tax_id'];
            }
            //$this->ms['MODULES']['DISABLE_VAT_RATE']=1;
        } else {
            $sql_tax_sb = $GLOBALS['TYPO3_DB']->SELECTquery('t.tax_id, t.rate, t.name, trg.default_status', // SELECT ...
                    'tx_multishop_taxes t, tx_multishop_tax_rules tr, tx_multishop_tax_rule_groups trg', // FROM ...
                    't.tax_id=tr.tax_id and tr.rules_group_id=trg.rules_group_id and trg.status=1 and tr.cn_iso_nr=\'' . $iso_customer['cn_iso_nr'] . '\' and tr.zn_country_iso_nr = \'' . addslashes($zone['zn_country_iso_nr']) . '\' and tr.rules_group_id = \'' . addslashes($product['tax_id']) . '\'', // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry_tax_sb = $GLOBALS['TYPO3_DB']->sql_query($sql_tax_sb);
            $rs_tx_sb = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tax_sb);
            $product['tax_id'] = $rs_tx_sb['tax_id'];
        }
    }
    //
    $staffel_price['tax_id'] = $product['tax_id'];
    if ($this->ms['MODULES']['DISABLE_VAT_RATE']) {
        //$staffel_price['use_tax_id']=false;
        //$staffel_price['tax_id'] = '';
        $staffel_price['price_include_vat'] = $staffel_price['price'];
        $staffel_price['display_price'] = number_format($staffel_price['price'], 2, '.', '');
        $staffel_price['display_price_include_vat'] = number_format($staffel_price['price'], 2, '.', '');
    }
    $content = $staffel_price;
    $content = json_encode($content, ENT_NOQUOTES);
    if ($this->ms['MODULES']['CACHE_FRONT_END']) {
        $Cache_Lite->save($content);
    }
}
echo $content;
exit;
?>
