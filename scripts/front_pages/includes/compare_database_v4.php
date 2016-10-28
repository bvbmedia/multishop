<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$str="select products_id from tx_multishop_orders_products where categories_id=0 group by products_id";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$filter=array();
	$filter[]='is_deepest=1';
	$record=mslib_befe::getRecord($row['products_id'],'tx_multishop_products_to_categories','products_id',$filter);
	if (is_array($record) && $record['crumbar_identifier']) {
		$updateArray=array();

        $counter=0;
		$catIds=explode(',',$record['crumbar_identifier']);
		foreach ($catIds as $catId) {
			$category=mslib_befe::getRecord($catId,'tx_multishop_categories_description','categories_id');
			if ($category['categories_id']) {
				$updateArray['categories_id_'.$counter]=$category['categories_id'];
				$updateArray['categories_name_'.$counter]=$category['categories_name'];
			}
			$counter++;
		}
		$updateArray['categories_id']=$updateArray['categories_id_'.($counter-1)];
		//$updateArray['categories_name']=$updateArray['categories_name_'.($counter-1)];
		$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'products_id=\''.$row['products_id'].'\'', $updateArray);
		$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
		$messages[]=$query2;
	}
}
$str="select image_filename from tt_address limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tt_address` ADD `image_filename` tinytext";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
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

$str="describe `tx_multishop_products_options`";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if ($row['Field']=='products_options_name') {
		if ($row['Type']=='varchar(64)') {
			$str2="ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_name`  `products_options_name` varchar(150)";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$messages[]=$str2;
		}
	}
}
$str="describe `tx_multishop_products_options_values`";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if ($row['Field']=='products_options_values_name') {
		if ($row['Type']=='varchar(64)') {
			$str2="ALTER TABLE  `tx_multishop_products_options_values` CHANGE  `products_options_values_name`  `products_options_values_name` varchar(150)";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$messages[]=$str2;
		}
	}
}
$str="describe `tx_multishop_products_options`";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if ($row['Field']=='products_options_descriptions') {
		if ($row['Type']=='varchar(255)') {
			$str2="ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_descriptions`  `products_options_descriptions` text";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			$messages[]=$str2;
		}
	}
}
$str="select `import_notes` from tx_multishop_products limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
	$str="ALTER TABLE `tx_multishop_products` ADD `import_notes` varchar(250) default ''";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$messages[]=$str;
}
// rename column amount to invoice_grand_total
$str="select amount from tx_multishop_invoices limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str="ALTER TABLE `tx_multishop_invoices` CHANGE `amount` `invoice_grand_total` decimal(24,14) default '0.00000000000000'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
// add invoice_grand_total column incase renamed "amount/invoice_grand_total" column not yet exist
$str="select invoice_grand_total from tx_multishop_invoices limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_invoices` ADD `invoice_grand_total` decimal(24,14) default '0.00000000000000'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
// add invoice_grand_total_excluding_vat column
$str="select invoice_grand_total_excluding_vat from tx_multishop_invoices limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_invoices` ADD `invoice_grand_total_excluding_vat` decimal(24,14) default '0.00000000000000'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
$sql_order="select id, orders_id, invoice_id, reversal_invoice from tx_multishop_invoices";
$qry_order=$GLOBALS['TYPO3_DB']->sql_query($sql_order);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_order)) {
    while ($invoices_row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_order)) {
        $order_record=mslib_befe::getRecord($invoices_row['orders_id'], 'tx_multishop_orders', 'orders_id', array(), 'grand_total, grand_total_excluding_vat');
        if ($order_record['grand_total_excluding_vat']>0) {
            $updateArray=array();
            if ($invoices_row['reversal_invoice']>0) {
                // credit invoices
                if ($order_record['grand_total']<0) {
                    // reverse to positive value if the tx_multishop_orders.grand_total already minus
                    $updateArray['invoice_grand_total'] = str_replace('-', '', $order_record['grand_total']);
                    $updateArray['invoice_grand_total_excluding_vat'] = str_replace('-', '', $order_record['grand_total_excluding_vat']);
                } else {
                    $updateArray['invoice_grand_total'] = '-'.$order_record['grand_total'];
                    $updateArray['invoice_grand_total_excluding_vat'] = '-'.$order_record['grand_total_excluding_vat'];
                }
            } else {
                $updateArray['invoice_grand_total'] = $order_record['grand_total'];
                $updateArray['invoice_grand_total_excluding_vat'] = $order_record['grand_total_excluding_vat'];
            }
            $query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id='.$invoices_row['id'].' and invoice_id='. $invoices_row['invoice_id'], $updateArray);
            $res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
        }
    }

    $messages[]="invoice_grand_total value in tx_multishop_invoices table updated";
    $messages[]="invoice_grand_total_excluding_vat value in tx_multishop_invoices table updated";
}
$str="select id from tx_multishop_feeds_excludelist limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str="ALTER TABLE `tx_multishop_feeds_excludelist` ADD `negate` tinyint(1) default '0', ADD KEY `negate` (`negate`)";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
    $str="ALTER TABLE `tx_multishop_feeds_excludelist` RENAME `tx_multishop_catalog_to_feeds`";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
    $str="UPDATE `tx_multishop_catalog_to_feeds` SET `negate`=1";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
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
$str="select id from tx_multishop_feeds_stock_excludelist limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str="ALTER TABLE `tx_multishop_feeds_stock_excludelist` ADD `negate` tinyint(1) default '0', ADD KEY `negate` (`negate`)";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
    $str="ALTER TABLE `tx_multishop_feeds_stock_excludelist` RENAME `tx_multishop_catalog_to_feeds_stocks`";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
    $str="UPDATE `tx_multishop_catalog_to_feeds_stocks` SET `negate`=1";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
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
$str="select requester_ip_addr from tx_multishop_orders_status_history limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_orders_status_history` ADD `requester_ip_addr` varchar(127) default ''";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
// add memo_crdate
$str="select memo_crdate from tx_multishop_orders limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_orders` ADD `memo_crdate` int(11) default '0'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
// add discount_percentage
$str="select discount_percentage from tx_multishop_orders limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_orders` ADD `discount_percentage` int(3) default '0'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}
$str="select `is_hidden` from tx_multishop_products limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_products` ADD `is_hidden` tinyint(1) default '0'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
}

$str="select `products_id` from tx_multishop_products_flat limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if ($qry) {
    $str="select `image` from tx_multishop_products_flat limit 1";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    if (!$qry) {
        $str="ALTER TABLE `tx_multishop_products_flat` ADD `image` varchar(50) default ''";
        $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
        $messages[]=$str;
    }
}
$str="select `products_tax_id` from tx_multishop_orders_products limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
    $str="ALTER TABLE `tx_multishop_orders_products` ADD `products_tax_id` int(11) default '0'";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($str);
    $messages[]=$str;
    // copy the tx_multishop_products.tax_id to tx_multishop_orders_products.products_tax_id
    $sql_copy="update tx_multishop_orders_products op set products_tax_id = (select tax_id from tx_multishop_products p where p.products_id=op.products_id)";
    $qry=$GLOBALS['TYPO3_DB']->sql_query($sql_copy);
}

?>