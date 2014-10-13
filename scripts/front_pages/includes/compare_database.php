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
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
if (!$skipMultishopUpdates) {
	// V1/V2 COMPARE DATABASE FIRST
	require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/compare_database_old.php');
	// V3 COMPARE DATABASE
	$str="select id from tx_multishop_sessions limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_sessions` (
		  `id` int(11) auto_increment,
		  `customer_id` int(11) default '0',
		  `crdate` int(11) default '0',
		  `session_id` varchar(150) default '',
		  `page_uid` int(11) default '0',
		  `ip_address` varchar(150) default '',
		  `http_host` varchar(150) default '',
		  `query_string` text,
		  `http_user_agent` text,
		  `http_referer` text,
		  `url` text,
		  `segment_type` varchar(50) default '',
		  `segment_id` varchar(50) default '',
		  PRIMARY KEY (`id`),
		  KEY `customer_id` (`customer_id`),
		  KEY `crdate` (`crdate`),
		  KEY `page_uid` (`page_uid`),
		  KEY `session_id` (`session_id`),
		  KEY `ip_address` (`ip_address`),
		  KEY `http_host` (`http_host`),
		  KEY `segment_type` (`segment_type`),
		  KEY `segment_id` (`segment_id`)
		);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select coupon_code from tx_multishop_orders limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_orders` ADD `coupon_code` varchar(255) default '', ADD KEY `coupon_code` (`coupon_code`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="ALTER TABLE `tx_multishop_orders` ADD `coupon_discount_type` varchar(25) default 'percentage', ADD KEY `coupon_discount_type` (`coupon_discount_type`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
		$str="ALTER TABLE `tx_multishop_orders` ADD `coupon_discount_value` decimal(24,14) default '0.00000000000000', ADD KEY `coupon_discount_value` (`coupon_discount_value`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="DESCRIBE `tx_multishop_customers_groups_method_mappings`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if ($qry) {
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
			if ($row['Field']=='id') {
				if (empty($row['Extra'])) {
					$str="ALTER TABLE  `tx_multishop_customers_groups_method_mappings` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					break;
				}
			}
		}
	}
	// attributes values image
	$str="select products_options_values_image from tx_multishop_products_options_values_to_products_options limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_options_values_to_products_options` ADD `products_options_values_image` varchar(255) default '', ADD KEY `products_options_values_image` (`products_options_values_image`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	// V3 COMPARE DATABASE EOL

	// CREATE / UPDATE MULTISHOP SETTINGS. CAN BE FURTHER CONTROLLED BY THIRD PARTY PLUGINS.
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/configuration/tx_multishop_configuration_group.php');
	foreach ($records as $record) {
		if (!mslib_befe::ifExists($record['id'], 'tx_multishop_configuration_group', 'id')) {
			$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration_group', $record);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
	}
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/configuration/tx_multishop_configuration.php');
	foreach ($records as $record) {
		if (!mslib_befe::ifExists($record['configuration_key'], 'tx_multishop_configuration', 'configuration_key')) {
			$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration', $record);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
	}
	// CREATE / UPDATE MULTISHOP SETTINGS. CAN BE FURTHER CONTROLLED BY THIRD PARTY PLUGINS. EOL
}
?>