<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$response = array();
$response['status'] = 'NOTOK';
if (is_numeric($this->post['tx_multishop_pi1']['pid'])) {
    switch ($this->get['tx_multishop_pi1']['update_product_info']) {
        case 'products_price':
            if (is_float($this->post['tx_multishop_pi1']['value'])) {
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] == '1') {
                    $product = mslib_befe::getRecord($this->post['tx_multishop_pi1']['pid'], 'tx_multishop_products', 'products_id', array(), 'tax_id');
                    if ($product['tax_id'] > 0) {
                        $data = mslib_fe::getTaxRuleSet($product['tax_id'], 0);
                        $product_tax_rate = $data['total_tax_rate'];
                        $this->post['tx_multishop_pi1']['value'] = ($this->post['tx_multishop_pi1']['value'] / (100 + $product_tax_rate)) * 100;
                    }
                }
                $updateArray = array();
                $updateArray['products_price'] = $this->post['tx_multishop_pi1']['value'];
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $this->post['tx_multishop_pi1']['pid'] . '\'', $updateArray);
                if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                    $response['status'] = 'OK';
                }
            }
            break;
        case 'products_quantity':
            if (is_float($this->post['tx_multishop_pi1']['value'])) {
                $updateArray = array();
                $updateArray['products_quantity'] = $this->post['tx_multishop_pi1']['value'];
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $this->post['tx_multishop_pi1']['pid'] . '\'', $updateArray);
                if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                    $response['status'] = 'OK';
                }
            }
            break;
        case 'products_status':
            if (is_numeric($this->post['tx_multishop_pi1']['value'])) {
                $updateArray = array();
                $updateArray['products_status'] = $this->post['tx_multishop_pi1']['value'];
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $this->post['tx_multishop_pi1']['pid'] . '\'', $updateArray);
                if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                    $response['status'] = 'OK';
                }
            }
            break;
        default:
            // custom page hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfo'])) {
                $params = array(
                        'return_data' => &$response
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfo'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom page hook that can be controlled by third-party plugin eof
            break;
    }
}
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfoPostProc'])) {
    $params = array(
            'response' => &$response
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfoPostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
echo json_encode($response);
exit();
