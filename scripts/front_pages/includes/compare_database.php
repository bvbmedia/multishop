<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$messages=array();
$skipMultishopUpdates=0;
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/compare_database.php']['compareDatabasePreHook'])) {
	$params=array(
		'messages'=>&$messages,
		'skipMultishopUpdates'=>&$skipMultishopUpdates
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/compare_database.php']['compareDatabasePreHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
if (!$skipMultishopUpdates) {
	// V1/V2 COMPARE DATABASE FIRST
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/compare_database_v2.php');
	// V3 COMPARE DATABASE
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/compare_database_v3.php');
	// V4 COMPARE DATABASE
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/compare_database_v4.php');

	/*
	// V4 BETA COMPARE DATABASE (MULTIPLE SHOPS DATABASE DESIGN) EOL
	$str="select tx_multishop_customer_id from fe_users limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `fe_users` ADD `tx_multishop_customer_id` int(11) UNSIGNED default '0', ADD KEY `tx_multishop_customer_id` (`tx_multishop_customer_id`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="UPDATE `fe_users` set tx_multishop_customer_id=uid where tx_multishop_customer_id='0'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	*/
	// V4 BETA COMPARE DATABASE (MULTIPLE SHOPS DATABASE DESIGN) EOL
	// CREATE / UPDATE MULTISHOP SETTINGS. CAN BE FURTHER CONTROLLED BY THIRD PARTY PLUGINS.
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/configuration/tx_multishop_configuration_group.php');
	foreach ($records as $record) {
		if (!mslib_befe::ifExists($record['id'], 'tx_multishop_configuration_group', 'id')) {
			$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration_group', $record);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
	}
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/configuration/tx_multishop_configuration.php');
	foreach ($records as $record) {
		if (!mslib_befe::ifExists($record['configuration_key'], 'tx_multishop_configuration', 'configuration_key')) {
			$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration', $record);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
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
	// CREATE / UPDATE MULTISHOP SETTINGS. CAN BE FURTHER CONTROLLED BY THIRD PARTY PLUGINS. EOL
	// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/compare_database.php']['compareDatabasePostHook'])) {
		$params=array(
			'messages'=>&$messages
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/includes/compare_database.php']['compareDatabasePostHook'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	// custom hook that can be controlled by third-party plugin eof
}
?>