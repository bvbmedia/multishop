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
}
echo $content;
exit();
?>
