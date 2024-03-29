<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$response = array();
$response['status'] = 'NOTOK';
$erno = array();
if (is_numeric($this->post['tx_multishop_pi1']['pid'])) {
    switch ($this->get['tx_multishop_pi1']['update_product_info']) {
        case 'products_price':
            if ((float)$this->post['tx_multishop_pi1']['value'] == '') {
                // Error!
                $erno[] = 'Input is not valid';
            } else {
                $this->post['tx_multishop_pi1']['value'] = (float)$this->post['tx_multishop_pi1']['value'];
            }
            if (!count($erno)) {
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'] == '1') {
                    $product = mslib_befe::getRecord($this->post['tx_multishop_pi1']['pid'], 'tx_multishop_products', 'products_id', array(), 'tax_id');
                    if ($product['tax_id'] > 0) {
                        $data = mslib_fe::getTaxRuleSet($product['tax_id'], 0);
                        $product_tax_rate = $data['total_tax_rate'];
                        //$product_price_incl_vat = number_format( $this->post['tx_multishop_pi1']['value'] * (1 + ($product_tax_rate / 100)), '14', '.', '');
                        $product_price_incl_vat = round($this->post['tx_multishop_pi1']['value'], 2);
                        $product_price_excl_vat = number_format(($product_price_incl_vat / (100 + $product_tax_rate)) * 100, 14, '.', '');
                        $this->post['tx_multishop_pi1']['value'] = $product_price_excl_vat;
                    }
                }
                $updateArray = array();
                $updateArray['products_price'] = $this->post['tx_multishop_pi1']['value'];
	            $updateArray['products_last_modified'] = time();
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $this->post['tx_multishop_pi1']['pid'] . '\'', $updateArray);
                if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                    $response['status'] = 'OK';
                }
            }
            break;
        case 'products_quantity':
            if ((float)$this->post['tx_multishop_pi1']['value'] == '') {
                // Error!
                $erno[] = 'Input is not valid';
            } else {
                $this->post['tx_multishop_pi1']['value'] = (float)$this->post['tx_multishop_pi1']['value'];
            }
            if (!count($erno)) {
                $updateArray = array();
                $updateArray['products_quantity'] = $this->post['tx_multishop_pi1']['value'];
	            $updateArray['products_last_modified'] = time();
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $this->post['tx_multishop_pi1']['pid'] . '\'', $updateArray);
                if ($GLOBALS['TYPO3_DB']->sql_query($query)) {
                    $response['status'] = 'OK';
                }
            }
            break;
        case 'products_status':
            if ($this->post['tx_multishop_pi1']['value'] === '') {
                // Error!
                $erno[] = 'Input is not valid';
            } else {
                $this->post['tx_multishop_pi1']['value'] = (int) $this->post['tx_multishop_pi1']['value'];
            }
            if (!count($erno)) {
                $updateArray = array();
                $updateArray['products_status'] = $this->post['tx_multishop_pi1']['value'];
	            $updateArray['products_last_modified'] = time();
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
            'response' => &$response,
            'erno' => &$erno
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/update_product_info.php']['ajaxUpdateProductInfoPostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
if (count($erno)) {
    $response['errors'] = $erno;
}
echo json_encode($response);
exit();
