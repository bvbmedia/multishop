<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$content = '';
$product_id = (int) $this->post['tx_multishop_pi1']['pid'];
$new_value = $this->post['tx_multishop_pi1']['value'];
switch ($this->get['tx_multishop_pi1']['update_product_info']) {
    case 'products_price':
        $return_data = array();
        $return_data['status'] = 'NOTOK';
        if ($product_id > 0 && $new_value) {
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] == '1') {
                $product = mslib_befe::getRecord($product_id, 'tx_multishop_products', 'products_id', array(), 'tax_id');
                if ($product['tax_id'] > 0) {
                    $data = mslib_fe::getTaxRuleSet($product['tax_id'], 0);
                    $product_tax_rate = $data['total_tax_rate'];
                    $new_value = ($new_value / (100 + $product_tax_rate)) * 100;
                }
            }
            $updateArray = array();
            $updateArray['products_price'] = addslashes($new_value);
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $product_id . '\'', $updateArray);
            if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                $return_data['status'] = 'OK';
            }
        }
        echo json_encode($return_data);
        exit();
        breaks;
    case 'products_quantity':
        $return_data = array();
        $return_data['status'] = 'NOTOK';
        if ($product_id > 0 && $new_value) {
            $updateArray = array();
            $updateArray['products_quantity'] = addslashes($new_value);
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $product_id . '\'', $updateArray);
            if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                $return_data['status'] = 'OK';
            }
        }
        echo json_encode($return_data);
        exit();
        breaks;
    case 'products_status':
        $return_data = array();
        $return_data['status'] = 'NOTOK';
        $new_value = (string) $new_value;
        if ($product_id > 0 && $new_value != '') {
            $updateArray = array();
            $updateArray['products_status'] = addslashes($new_value);
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $product_id . '\'', $updateArray);
            if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                $return_data['status'] = 'OK';
            }
        }
        echo json_encode($return_data);
        exit();
        breaks;
    default:
        $return_data = array();
        $return_data['status'] = 'NOTOK';
        // custom page hook that can be controlled by third-party plugin
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfo'])) {
            $params = array(
                'return_data' => &$return_data
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfo'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        // custom page hook that can be controlled by third-party plugin eof
        echo json_encode($return_data);
        exit();
        breaks;
}
echo $content;
exit();
?>
