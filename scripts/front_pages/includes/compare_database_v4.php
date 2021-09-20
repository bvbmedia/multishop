<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$str = "select DISTINCT products_id from tx_multishop_orders_products where categories_id=0";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $filter = array();
    $filter[] = 'is_deepest=1';
    $record = mslib_befe::getRecord($row['products_id'], 'tx_multishop_products_to_categories', 'products_id', $filter);
    if (is_array($record) && $record['crumbar_identifier']) {
        $updateArray = array();
        $counter = 0;
        $catIds = explode(',', $record['crumbar_identifier']);
        foreach ($catIds as $catId) {
            $category = mslib_befe::getRecord($catId, 'tx_multishop_categories_description', 'categories_id');
            if ($category['categories_id']) {
                $updateArray['categories_id_' . $counter] = $category['categories_id'];
                $updateArray['categories_name_' . $counter] = $category['categories_name'];
            }
            $counter++;
        }
        $updateArray['categories_id'] = $updateArray['categories_id_' . ($counter - 1)];
        //$updateArray['categories_name']=$updateArray['categories_name_'.($counter-1)];
        $query2 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'products_id=\'' . $row['products_id'] . '\'', $updateArray);
        $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
        $messages[] = $query2;
    }
}
$str = "select image_filename from tt_address limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tt_address` ADD `image_filename` tinytext";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
/*$key='PRICE_FILTER_WITHOUT_CATEGORY_QUERY_STRING';
$title='Price Filter without category query string';
$description='Optional field.';
$default_value='0';
if (!isset($settings['GLOBAL_MODULES'][$key])) {
	$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 3, NULL, NULL, now(), 'tep_cfg_select_option(array(''0'',''1''),');";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$messages[]=$str;
}*/
$str = "describe `tx_multishop_products_options`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'products_options_name') {
        if ($row['Type'] == 'varchar(64)') {
            $str2 = "ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_name`  `products_options_name` varchar(150)";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "describe `tx_multishop_products_options_values`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'products_options_values_name') {
        if ($row['Type'] == 'varchar(64)') {
            $str2 = "ALTER TABLE  `tx_multishop_products_options_values` CHANGE  `products_options_values_name`  `products_options_values_name` varchar(150)";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "describe `tx_multishop_products_options`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'products_options_descriptions') {
        if ($row['Type'] == 'varchar(255)') {
            $str2 = "ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_descriptions`  `products_options_descriptions` text";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "select `import_notes` from tx_multishop_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_products` ADD `import_notes` varchar(250) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// rename column amount to invoice_grand_total
$str = "select amount from tx_multishop_invoices limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str = "ALTER TABLE `tx_multishop_invoices` CHANGE `amount` `invoice_grand_total` decimal(24,14) default '0.00000000000000'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add invoice_grand_total column incase renamed "amount/invoice_grand_total" column not yet exist
$str = "select invoice_grand_total from tx_multishop_invoices limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_invoices` ADD `invoice_grand_total` decimal(24,14) default '0.00000000000000'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add invoice_grand_total_excluding_vat column
$str = "select invoice_grand_total_excluding_vat from tx_multishop_invoices limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_invoices` ADD `invoice_grand_total_excluding_vat` decimal(24,14) default '0.00000000000000'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$sql_order = "select id, orders_id, invoice_id, reversal_invoice from tx_multishop_invoices";
$qry_order = $GLOBALS['TYPO3_DB']->sql_query($sql_order);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_order)) {
    while ($invoices_row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_order)) {
        $order_record = mslib_befe::getRecord($invoices_row['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'grand_total, grand_total_excluding_vat');
        if ($order_record['grand_total_excluding_vat'] > 0) {
            $updateArray = array();
            if ($invoices_row['reversal_invoice'] > 0) {
                // credit invoices
                if ($order_record['grand_total'] < 0) {
                    // reverse to positive value if the tx_multishop_orders.grand_total already minus
                    $updateArray['invoice_grand_total'] = str_replace('-', '', $order_record['grand_total']);
                    $updateArray['invoice_grand_total_excluding_vat'] = str_replace('-', '', $order_record['grand_total_excluding_vat']);
                } else {
                    $updateArray['invoice_grand_total'] = '-' . $order_record['grand_total'];
                    $updateArray['invoice_grand_total_excluding_vat'] = '-' . $order_record['grand_total_excluding_vat'];
                }
            } else {
                $updateArray['invoice_grand_total'] = $order_record['grand_total'];
                $updateArray['invoice_grand_total_excluding_vat'] = $order_record['grand_total_excluding_vat'];
            }
            $query2 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id=' . $invoices_row['id'] . ' and invoice_id=' . $invoices_row['invoice_id'], $updateArray);
            $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
        }
    }
    //$messages[] = "invoice_grand_total value in tx_multishop_invoices table updated";
    //$messages[] = "invoice_grand_total_excluding_vat value in tx_multishop_invoices table updated";
}
$str = "select id from tx_multishop_feeds_excludelist limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str = "ALTER TABLE `tx_multishop_feeds_excludelist` ADD `negate` tinyint(1) default '0', ADD KEY `negate` (`negate`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $str = "ALTER TABLE `tx_multishop_feeds_excludelist` RENAME `tx_multishop_catalog_to_feeds`";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $str = "UPDATE `tx_multishop_catalog_to_feeds` SET `negate`=1";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
} else {
    $str = "select id from tx_multishop_catalog_to_feeds";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    if (!$qry) {
        $str = "CREATE TABLE IF NOT EXISTS `tx_multishop_catalog_to_feeds` (
            `id` int(11) NULL AUTO_INCREMENT,
            `feed_id` int(11) NULL DEFAULT '0',
            `negate` tinyint(1) NULL DEFAULT '0',
            `exclude_id` int(11) NULL DEFAULT '0',
            `exclude_type` varchar(11) CHARACTER SET utf8 NULL DEFAULT 'categories',
            PRIMARY KEY (`id`),
            KEY `feed_id` (`feed_id`),
            KEY `negate` (`negate`),
            KEY `exclude_id` (`exclude_id`),
            KEY `exclude_type` (`exclude_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$str = "select id from tx_multishop_feeds_stock_excludelist limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str = "ALTER TABLE `tx_multishop_feeds_stock_excludelist` ADD `negate` tinyint(1) default '0', ADD KEY `negate` (`negate`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $str = "ALTER TABLE `tx_multishop_feeds_stock_excludelist` RENAME `tx_multishop_catalog_to_feeds_stocks`";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $str = "UPDATE `tx_multishop_catalog_to_feeds_stocks` SET `negate`=1";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
} else {
    $str = "select id from tx_multishop_catalog_to_feeds_stocks";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    if (!$qry) {
        $str = "CREATE TABLE IF NOT EXISTS `tx_multishop_catalog_to_feeds_stocks` (
            `id` int(11) NULL AUTO_INCREMENT,
            `feed_id` int(11) NULL DEFAULT '0',
            `negate` tinyint(1) NULL DEFAULT '0',
            `exclude_id` int(11) NULL DEFAULT '0',
            `exclude_type` varchar(11) CHARACTER SET utf8 NULL DEFAULT 'categories',
            PRIMARY KEY (`id`),
            KEY `feed_id` (`feed_id`),
            KEY `negate` (`negate`),
            KEY `exclude_id` (`exclude_id`),
            KEY `exclude_type` (`exclude_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
// add requester ip address
$str = "select requester_ip_addr from tx_multishop_orders_status_history limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_status_history` ADD `requester_ip_addr` varchar(127) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add memo_crdate
$str = "select memo_crdate from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD `memo_crdate` int(11) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add discount_percentage
$str = "select discount_percentage from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD `discount_percentage` int(3) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select `is_hidden` from tx_multishop_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_products` ADD `is_hidden` tinyint(1) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select `products_id` from tx_multishop_products_flat limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str = "select `image` from tx_multishop_products_flat limit 1";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    if (!$qry) {
        $str = "ALTER TABLE `tx_multishop_products_flat` ADD `image` varchar(50) default ''";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$str = "select `products_tax_id` from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_products` ADD `products_tax_id` int(11) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    // copy the tx_multishop_products.tax_id to tx_multishop_orders_products.products_tax_id
    $sql_copy = "update tx_multishop_orders_products op set products_tax_id = (select tax_id from tx_multishop_products p where p.products_id=op.products_id)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($sql_copy);
}
$str = "select product_capital_price from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_products` ADD `product_capital_price` decimal(24,14) default '0.00000000000000', ADD KEY `product_capital_price` (`product_capital_price`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $sql_upd = "UPDATE tx_multishop_orders_products op INNER JOIN tx_multishop_products p ON op.products_id=p.products_id SET op.product_capital_price=p.product_capital_price WHERE op.product_capital_price=0";
    $res_upd = $GLOBALS['TYPO3_DB']->sql_query($sql_upd);
} else {
    $sql_upd = "UPDATE tx_multishop_orders_products op INNER JOIN tx_multishop_products p ON op.products_id=p.products_id SET op.product_capital_price=p.product_capital_price WHERE op.product_capital_price=0";
    $res_upd = $GLOBALS['TYPO3_DB']->sql_query($sql_upd);
}
$str = "select foreign_source_name from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `foreign_source_name` varchar(30) default '', ADD KEY `foreign_source_name` (`foreign_source_name`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_orders_id from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `foreign_orders_id` varchar(30) default '', ADD KEY `foreign_orders_id` (`foreign_orders_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$indexesToDrop = array();
$indexesToDrop[] = 'credit_order';
$indexesToDrop[] = 'payed';
$indexesToDrop[] = 'klanten_id';
$indexes = array();
$table_name = 'tx_multishop_orders';
$str = "show indexes from `" . $table_name . "` ";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
foreach ($indexesToDrop as $indexToDrop) {
    if (in_array($indexToDrop, $indexes)) {
        $str = "ALTER TABLE  `" . $table_name . "` DROP INDEX `" . $indexToDrop . "`";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$str = "select categories_name from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `categories_name` varchar(150) not null default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $order_records = mslib_befe::getRecords('', 'tx_multishop_orders_products');
    if (is_array($order_records) && count($order_records)) {
        foreach ($order_records as $order_record) {
            $category_name = mslib_fe::getCategoryName($order_record['categories_id']);
            if ($category_name) {
                $updateArray = array();
                $updateArray['categories_name'] = $category_name;
                $query2 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'orders_products_id=' . $order_record['orders_products_id'], $updateArray);
                $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
            }
        }
    }
}
$str = "select department from fe_users limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `fe_users` ADD `department` varchar(127) not null default '', ADD KEY `department` (`department`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select billing_department from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `billing_department` varchar(127) not null default '', ADD KEY `billing_department` (`billing_department`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select delivery_department from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `delivery_department` varchar(127) not null default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select department from tt_address limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tt_address` ADD `department` varchar(127) not null default '', ADD KEY `department` (`department`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add combined index for maximum speed
$indexes = array();
$table_name = 'tx_multishop_products_attributes';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
if (!in_array('combined1', $indexes)) {
    $str = "ALTER TABLE `" . $table_name . "` ADD KEY `combined1` (`products_id`,`options_id`,`options_values_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
if (!in_array('combined2', $indexes)) {
    $str = "ALTER TABLE `" . $table_name . "` ADD KEY `combined2` (`options_id`,`options_values_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// cms table
$str = "describe `tx_multishop_cart_contents`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'contents') {
        if ($row['Type'] == 'longtext') {
            $str2 = "ALTER TABLE  `tx_multishop_cart_contents` CHANGE  `contents`  `contents` BLOB DEFAULT NULL;";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "select product_link from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `product_link` varchar(255) default '', ADD KEY `product_link` (`product_link`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select page_uid from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `page_uid` int(11) default '0', ADD KEY `page_uid` (`page_uid`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $products = mslib_befe::getRecords('', 'tx_multishop_orders_products', '', array(), '', '', '', array('orders_products_id', 'products_id'));
    if (is_array($products) && count($products)) {
        foreach ($products as $product) {
            $product_info = mslib_befe::getRecord($product['products_id'], 'tx_multishop_products p, tx_multishop_products_to_categories p2c', 'p.products_id', array('p2c.is_deepest=1 and p.products_id=p2c.products_id'), 'p.page_uid, p2c.categories_id');
            if (is_array($product_info) && count($product_info)) {
                if ($product_info['categories_id']) {
                    // get all cats to generate multilevel fake url
                    $level = 0;
                    $cats = mslib_fe::Crumbar($product_info['categories_id']);
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
                $product_detail_link = $this->FULL_HTTP_URL . mslib_fe::typolink($product_info['page_uid'], $where . '&products_id=' . $product['products_id'] . '&tx_multishop_pi1[page_section]=products_detail');
                // update orders_products table
                $updateArray = array();
                $updateArray['page_uid'] = $product_info['page_uid'];
                $updateArray['product_link'] = $product_detail_link;
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'orders_products_id=\'' . $product['orders_products_id'] . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
        }
    }
}
$str = "select hash from tx_multishop_payment_methods limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_payment_methods` ADD `hash` varchar(127) default '', ADD KEY `hash` (`hash`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $payments = mslib_befe::getRecords('', 'tx_multishop_payment_methods', '', array('hash=\'\''), '', '', '', array('id', 'hash'));
    if (is_array($payments) && count($payments)) {
        foreach ($payments as $payment) {
            // update orders_products table
            $updateArray = array();
            $updateArray['hash'] = md5(uniqid('', true));
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_payment_methods', 'id=\'' . $payment['id'] . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        }
    }
}
$str = "select hash from tx_multishop_shipping_methods limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_shipping_methods` ADD `hash` varchar(127) default '', ADD KEY `hash` (`hash`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    $shippings = mslib_befe::getRecords('', 'tx_multishop_shipping_methods', '', array('hash=\'\''), '', '', '', array('id', 'hash'));
    if (is_array($shippings) && count($shippings)) {
        foreach ($shippings as $shipping) {
            // update orders_products table
            $updateArray = array();
            $updateArray['hash'] = md5(uniqid('', true));
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_methods', 'id=\'' . $shipping['id'] . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        }
    }
}
$str = "select is_shipping_costs_manual from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD `is_shipping_costs_manual` tinyint(1) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "describe `fe_users`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'address_ext') {
        if ($row['Type'] == 'varchar(10)') {
            $str2 = "ALTER TABLE  `fe_users` CHANGE  `address_ext`  `address_ext` varchar(35)";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "describe `tt_address`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'address_ext') {
        if ($row['Type'] == 'varchar(10)') {
            $str2 = "ALTER TABLE  `tt_address` CHANGE  `address_ext`  `address_ext` varchar(35)";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "describe `tx_multishop_orders`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    if ($row['Field'] == 'billing_address_ext') {
        if ($row['Type'] == 'varchar(10)') {
            $str2 = "ALTER TABLE  `tx_multishop_orders` CHANGE  `billing_address_ext`  `billing_address_ext` varchar(35)";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    } elseif ($row['Field'] == 'delivery_address_ext') {
        if ($row['Type'] == 'varchar(10)') {
            $str2 = "ALTER TABLE  `tx_multishop_orders` CHANGE  `delivery_address_ext`  `delivery_address_ext` varchar(35)";
            $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
            $messages[] = $str2;
        }
    }
}
$str = "select foreign_source_name from tx_multishop_categories limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_categories` ADD `foreign_source_name` varchar(30) default '', ADD KEY `foreign_source_name` (`foreign_source_name`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_source_name from tx_multishop_manufacturers limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_manufacturers` ADD `foreign_source_name` varchar(30) default '', ADD KEY `foreign_source_name` (`foreign_source_name`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add contact e-mail
$str = "select `contact_email` from fe_users limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `fe_users` ADD `contact_email` varchar(256) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    // copy fe_users.email to fe_users.contact_email
    $str = "UPDATE `fe_users` set contact_email=email";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add contact e-mail
$str = "select `payment_condition` from tx_multishop_payment_methods limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_payment_methods` ADD `payment_condition` varchar(50) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$query = "update tt_address set gender='m' where gender='0'";
$res = $GLOBALS['TYPO3_DB']->sql_query($query);
$query = "update tt_address set gender='f' where gender='1'";
$res = $GLOBALS['TYPO3_DB']->sql_query($query);
// hide zone
$str = "select `hide_in_frontend` from tx_multishop_zones limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_zones` ADD `hide_in_frontend` tinyint(1) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// hide zone
$str = "select `hide_in_frontend` from tx_multishop_countries_to_zones limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_countries_to_zones` ADD `hide_in_frontend` tinyint(1) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
// add extra column for order status change in history table
$str = "select `action_call` from tx_multishop_orders_status_history limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_status_history` ADD `action_call` varchar(256) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_customer_id from fe_users limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `fe_users` ADD `foreign_customer_id` int(11) default '0', ADD KEY `foreign_customer_id` (`foreign_customer_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_source_name from fe_users limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `fe_users` ADD `foreign_source_name` varchar(30) default '0', ADD KEY `foreign_source_name` (`foreign_source_name`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select http_host_referer from tx_multishop_sessions limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_sessions` ADD `http_host_referer` varchar(75) default '', ADD KEY `http_host_referer` (`http_host_referer`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    // Repair data
    $str = "SELECT id,http_referer from tx_multishop_sessions where http_referer != '' and http_referer is not null";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $urlArray = parse_url($rs['http_referer']);
        $updateArray = array();
        $updateArray['http_host_referer'] = $urlArray['host'];
        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_sessions', 'id=' . $rs['id'], $updateArray);
        $GLOBALS['TYPO3_DB']->sql_query($query);
    }
}
// remove the DISABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER
$query2 = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\'DISABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER\'');
$res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
/*
$auto_shipping_costs=mslib_befe::getRecord('DISABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER', 'tx_multishop_configuration', 'configuration_key');
if (is_array($auto_shipping_costs) && isset($auto_shipping_costs['configuration_value'])) {
    $current_value=$auto_shipping_costs['configuration_value'];
    $new_value='0';
    if ($current_value=='0') {
        $new_value='1';
    }
    $updateArray=array();
    $updateArray['configuration_value']=$new_value;
    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_configuration', 'configuration_key=\'ENABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER\'', $updateArray);
    $GLOBALS['TYPO3_DB']->sql_query($query);

    $query2 = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration','configuration_key=\'DISABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER\'');
    $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
    $messages[] = 'DELETE FROM tx_multishop_configuration WHERE configuration_key=\'DISABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER\'';
}
*/
$indexes = array();
$table_name = 'tx_multishop_cart_contents';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
if (!in_array('session_id', $indexes)) {
    $str = "ALTER TABLE `" . $table_name . "` ADD KEY `session_id` (`session_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select orders_products_qty_shipped_id from tx_multishop_orders_products_qty_shipped";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "CREATE TABLE `tx_multishop_orders_products_qty_shipped` (
 `orders_products_qty_shipped_id` int(11) NOT NULL auto_increment,
 `orders_products_id` int(11) default '0',
 `orders_id` int(11) default '0',
 `products_id` int(11) default '0',
 `qty` decimal(8,2),
 `status` int(3) default '0',
 `crdate` int(11) default '0',
 PRIMARY KEY (`orders_products_qty_shipped_id`),
 KEY `orders_products_id` (`orders_products_id`),
 KEY `orders_id` (`orders_id`),
 KEY `products_id` (`products_id`),
 KEY `qty` (`qty`),
 KEY `status` (`status`),
 KEY `crdate` (`crdate`)
);";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
if ($this->conf['enableAttributeOptionValuesGroup'] == '1') {
    $str = "select attributes_options_values_groups_id from tx_multishop_attributes_options_values_groups";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    if (!$qry) {
        $str = "CREATE TABLE `tx_multishop_attributes_options_values_groups` (
 `attributes_options_values_groups_id` int(11) NOT NULL auto_increment,
 `language_id` int(11) default '0',
 `attributes_options_values_groups_name` varchar (127) default '',
 `sort_order` int(11) default '0',
 PRIMARY KEY (`attributes_options_values_groups_id`),
 KEY `language_id` (`language_id`),
 KEY `attributes_options_values_groups_name` (`attributes_options_values_groups_name`),
 KEY `sort_order` (`sort_order`)
);";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
    $str = "select attributes_options_values_groups_to_products_options_values_id from tx_multishop_attributes_options_values_groups_to_options_values";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    if (!$qry) {
        $str = "CREATE TABLE `tx_multishop_attributes_options_values_groups_to_options_values` (
 `attributes_options_values_groups_to_products_options_values_id` int(11) NOT NULL auto_increment,
 `attributes_options_values_groups_id` int(11) default '0',
 `products_options_values_id` int (11) default '0',
 PRIMARY KEY (`attributes_options_values_groups_to_products_options_values_id`)
);";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$str = "select stock_subtracted from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `stock_subtracted` tinyint(1) default '0', ADD KEY `stock_subtracted` (`stock_subtracted`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select crdate from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `crdate` int(11) default '0', ADD KEY `crdate` (`crdate`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select manufacturers_name from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `manufacturers_name` varchar(127) default '', ADD KEY `manufacturers_name` (`manufacturers_name`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select related_to_orders_products_id from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_products` ADD related_to_orders_products_id int(11) default '0', ADD KEY `related_to_orders_products_id` (`related_to_orders_products_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select ignore_stock_level from tx_multishop_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_products` ADD ignore_stock_level tinyint(1) default '0', ADD KEY `ignore_stock_level` (`ignore_stock_level`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select cruser_id from tx_multishop_orders_status_history limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_status_history` ADD cruser_id int(11) default '0', ADD KEY `cruser_id` (`cruser_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select is_default from tx_multishop_order_units limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_order_units` ADD is_default tinyint(1) default '0', ADD KEY `is_default` (`is_default`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_source_name from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `foreign_source_name` varchar(30) default '', ADD KEY `foreign_source_name` (`foreign_source_name`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_orders_products_id from tx_multishop_orders_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders_products` ADD `foreign_orders_products_id` varchar(30) default '', ADD KEY `foreign_orders_products_id` (`foreign_orders_products_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select http_host from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `http_host` varchar(127) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_address_id from tt_address limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tt_address` ADD `foreign_address_id` int(11) default '0', ADD KEY `foreign_address_id` (`foreign_address_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select orders_id_extra from tx_multishop_payment_transactions limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_payment_transactions` ADD `orders_id_extra` varchar(256) default '', ADD KEY `orders_id_extra` (`orders_id_extra`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select http_host from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD `http_host` varchar(127) default '', ADD KEY `http_host` (`http_host`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select foreign_orders_id from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD `foreign_orders_id` varchar(30) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select ignored_locked_fields from tx_multishop_import_jobs limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_import_jobs` ADD `ignored_locked_fields` text";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select parcel_information_json from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD `parcel_information_json` blob";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select group_dropdown_label from tx_multishop_products_options_values limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_products_options_values` ADD `group_dropdown_label` varchar(150) default ''";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select meta_title from tx_multishop_cms_description limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_cms_description` ADD `meta_title` varchar(254) default '', ADD KEY `meta_title` (`meta_title`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select meta_description from tx_multishop_cms_description limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_cms_description` ADD `meta_description` text, ADD KEY `meta_description` (`meta_description`(250))";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$indexes = array();
$table_name = 'tx_multishop_orders_products';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
if (!in_array('products_id', $indexes)) {
    $str = "ALTER TABLE `" . $table_name . "` ADD KEY `products_id` (`products_id`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
if (!in_array('products_model', $indexes)) {
    $str = "ALTER TABLE `" . $table_name . "` ADD KEY `products_model` (`products_model`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$indexToCheck=array();
$indexToCheck[]='orders_id';
$indexToCheck[]='transaction_id';
$indexToCheck[]='psp';
$indexToCheck[]='status';
$indexToCheck[]='code';
$indexes = array();
$table_name = 'tx_multishop_payment_transactions';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
foreach($indexToCheck as $index) {
    if (!in_array($index, $indexes)) {
        $str = "ALTER TABLE `" . $table_name . "` ADD KEY `".$index."` (`".$index."`)";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$indexToCheck=array();
$indexToCheck[]='billing_country';
$indexes = array();
$table_name = 'tx_multishop_orders';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
foreach($indexToCheck as $index) {
    if (!in_array($index, $indexes)) {
        $str = "ALTER TABLE `" . $table_name . "` ADD KEY `".$index."` (`".$index."`)";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$indexToCheck=array();
$indexToCheck[]='products_id';
$indexes = array();
$table_name = 'tx_multishop_orders_products';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
foreach($indexToCheck as $index) {
    if (!in_array($index, $indexes)) {
        $str = "ALTER TABLE `" . $table_name . "` ADD KEY `".$index."` (`".$index."`)";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
// Drop some unneeded indexes on the orders table to prevent reaching the maximum number of indexes
$indexToCheck=array();
$indexToCheck[]='coupon_discount_value';
$indexToCheck[]='coupon_discount_type';
$indexToCheck[]='combined';
$indexes = array();
$table_name = 'tx_multishop_orders';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
foreach($indexToCheck as $index) {
    if (in_array($index, $indexes)) {
        $str = "ALTER TABLE `" . $table_name . "` DROP KEY `".$index."`";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
// Add some indexes
$indexToCheck=array();
$indexToCheck[]='transaction_id';
$indexToCheck[]='psp';
$indexes = array();
$table_name = 'tx_multishop_payment_transactions';
$str = "show indexes from `" . $table_name . "`";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
while (($rs = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $indexes[] = $rs['Key_name'];
}
foreach($indexToCheck as $index) {
    if (!in_array($index, $indexes)) {
        $str = "ALTER TABLE `" . $table_name . "` ADD KEY `".$index."` (`".$index."`)";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[] = $str;
    }
}
$key = 'APPLY_ROUNDING_TAX_AMOUNT_ON_PRODUCT_PIECE_PRICE';
if (!isset($settings['GLOBAL_MODULES'][$key])) {
    $str = "INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Apply rounding tax amount on product piece price', '" . $key . "', '0', 'Only apply to shops that have defined prices excluding VAT with 2 decimals.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
}
$str = "select discount_amount_excl_tax from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders` ADD discount_amount_excl_tax decimal(8,2) DEFAULT '0.00'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}

$key = 'ADMIN_PRODUCTS_SEARCH_AND_EDIT_DEFAULT_ORDER_BY';
if (!isset($settings['GLOBAL_MODULES'][$key])) {
    $str = "INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin products search and edit > Default order by', '" . $key . "', '', 'i.e. products_name or sort_order', 11, NULL, NULL, now(), NULL, '');";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
}
$str = "select deleted_by_uid from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `deleted_by_uid` int(11) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select deleted_tstamp from tx_multishop_orders limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE  `tx_multishop_orders` ADD `deleted_tstamp` int(11) default '0'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
}
$str = "select `tax_rate` from tx_multishop_products limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_products` ADD `tax_rate` decimal(6,4) default '0.0000'";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;

    $select = array();
    $select[] = 'p.products_id, t.rate';
    $filter = array();
    $filter[] = 'p.tax_id = t.tax_id';
    $products = mslib_befe::getRecords('', 'tx_multishop_products p, tx_multishop_taxes t', '', $filter, '', '', '99999999', $select);
    if (is_array($products) && count($products)) {
        foreach ($products as $product) {
            $updateArray = array();
            $updateArray['tax_rate'] = $product['rate'];
            $query2 = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $product['products_id'] . '\'', $updateArray);
            $res2 = $GLOBALS['TYPO3_DB']->sql_query($query2);
        }
    }
}

$str = "select `sort_order` from tx_multishop_orders_status limit 1";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str = "ALTER TABLE `tx_multishop_orders_status` int(11) default '0', ADD KEY `sort_order` (`sort_order`)";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[] = $str;
    // Update the newly created order status sort order using order status id
    $sql_update = 'update tx_multishop_orders_status set sort_order = id where sort_order=0';
    $GLOBALS['TYPO3_DB']->sql_query($sql_update);
}
