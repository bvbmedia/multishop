<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$key = 'NUMBER_OF_PRODUCT_IMAGES';
if (isset($settings['GLOBAL_MODULES'][$key])) {
    // check if the products table has enough product image fields
    for ($x = 0; $x <= ($settings['GLOBAL_MODULES']['NUMBER_OF_PRODUCT_IMAGES'] - 1); $x++) {
        $i = $x;
        if ($i == 0) {
            $i = '';
        }
        $field = 'products_image' . $i;
        $str = "select " . $field . " from tx_multishop_products limit 1";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        if (!$qry) {
            $str = "ALTER TABLE  `tx_multishop_products` ADD `products_image" . $i . "` varchar(250) NULL";
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            $messages[] = $str;
        }
    }
}
$key = 'PRODUCTS_DETAIL_NUMBER_OF_TABS';
if (isset($settings['GLOBAL_MODULES'][$key])) {
    // check if the description table has enough tabs fields
    $total = $settings['GLOBAL_MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS'];
    if ($total) {
        for ($i = 1; $i <= $total; $i++) {
            $name = 'products_description_tab_content_' . $i;
            $str = "select " . $name . " from tx_multishop_products_description limit 1";
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            if (!$qry) {
                $str = "ALTER TABLE  `tx_multishop_products_description` ADD  `products_description_tab_content_" . $i . "` TEXT NULL";
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                $messages[] = $str;
                $str = "ALTER TABLE  `tx_multishop_products_description` ADD  `products_description_tab_title_" . $i . "` varchar(50) NULL";
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                $messages[] = $str;
            }
        }
    }
}
// Check if there are no products with non-unique hash
$str = 'SELECT products_id,extid FROM tx_multishop_products GROUP BY extid HAVING COUNT(extid) > 1';
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $records = mslib_befe::getRecords($row['extid'], 'tx_multishop_products', 'extid');
        if (is_array($records)) {
            foreach ($records as $record) {
                $updateArray = array();
                $updateArray['extid']=md5(uniqid());
                $query2 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $record['products_id'] . '\'', $updateArray);
                $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
                $messages[] = $query2;
            }
        }
    }
}
?>
