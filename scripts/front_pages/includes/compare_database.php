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
	$str="select products_id from tx_multishop_products where extid='0' or extid='' or extid is null";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$array=array();
		$array['extid']=md5(uniqid());
		$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$row['products_id'].'\'', $array);
		$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
	}

	$str="select id from tx_multishop_products_options_values_to_products_options_desc limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_products_options_values_to_products_options_desc` (
						`id` int(11) NULL AUTO_INCREMENT,
						`products_options_values_to_products_options_id` int(11) DEFAULT '0',
						`language_id` int(11) DEFAULT '0',
						`description` varchar(255) DEFAULT '',
						PRIMARY KEY (`id`),
						KEY `products_options_values_to_products_options_id` (`products_options_values_to_products_options_id`),
						KEY `language_id` (`language_id`)
					) ENGINE=MyISAM";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select foreign_source_name from tx_multishop_products limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products` ADD `foreign_source_name` varchar(30) default '', ADD KEY `foreign_source_name` (`foreign_source_name`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}	
	$str="select id from tx_multishop_products_locked_fields limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="CREATE TABLE `tx_multishop_products_locked_fields` (`id` int(11) NULL auto_increment,`products_id` int(11) DEFAULT '0',`field_key` varchar(50) DEFAULT '',
  `crdate` int(11) NULL default '0',
  `cruser_id` int(11) NULL default '0',
  `original_value` text,
  PRIMARY KEY (`id`),
  KEY cruser_id (cruser_id),  
  KEY crdate (crdate),  
  KEY field_key (field_key),  
  KEY products_id (products_id)
) ENGINE=InnoDB;";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select sort_order_option_name from tx_multishop_products_attributes limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_attributes` ADD `sort_order_option_name` int(11) DEFAULT '0'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select sort_order_option_value from tx_multishop_products_attributes limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE `tx_multishop_products_attributes` ADD `sort_order_option_value` int(11) DEFAULT '0'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select relation_types from tx_multishop_products_to_relative_products limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products_to_relative_products` ADD `relation_types` varchar(127) DEFAULT 'cross-sell'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select foreign_products_id from tx_multishop_products limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products` ADD `foreign_products_id` varchar(30) default '', ADD KEY `foreign_products_id` (`foreign_products_id`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select http_host from tx_multishop_products_search_log limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products_search_log` ADD `http_host` varchar(60) default '', ADD KEY `http_host` (`http_host`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select page_uid from tx_multishop_products_search_log limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products_search_log` ADD `page_uid` int(11) default '0', ADD KEY `page_uid` (`page_uid`)";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$required_indexes=array();
	$required_indexes[]='crdate';
	$indexes=array();
	$table_name='tx_multishop_products_search_log';
	$str="show indexes from `".$table_name."` ";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$indexes[]=$rs['Key_name'];
	}
	foreach ($required_indexes as $required_index) {
		if (!in_array($required_index, $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
	}
	$str="select products_options_descriptions from tx_multishop_products_options limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if (!$qry) {
		$str="ALTER TABLE  `tx_multishop_products_options` ADD `products_options_descriptions` varchar(255) default ''";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$messages[]=$str;
	}
	$str="select id from tx_multishop_configuration_group limit 1";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	if ($rows) {
		// OLD V1/V2 FIXES
		if (t3lib_extMgm::isLoaded('static_info_tables')) {
			$required_indexes=array();
			$required_indexes[]='cn_short_en';
			$required_indexes[]='cn_iso_nr';
			$indexes=array();
			$table_name='static_countries';
			$str="show indexes from `".$table_name."` ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$indexes[]=$rs['Key_name'];
			}
			foreach ($required_indexes as $required_index) {
				if (!in_array($required_index, $indexes)) {
					$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		if (t3lib_extMgm::isLoaded('cooluri')) {
			$table_name='link_cache';
			$str="show indexes from `".$table_name."` ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				if ($rs['Key_name']=='params') {
					if ($rs['Sub_part']=='64') {
						// cooluri default key length on parameters field is way too short. lets optimize it.
						$str2="ALTER TABLE link_cache DROP INDEX `params`, ADD INDEX  `params` (`params` (250))";
						$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
						$messages[]=$str2;
					}
				}
				if ($rs['Key_name']=='url') {
					if ($rs['Sub_part']=='64') {
						// cooluri default key length on parameters field is way too short. lets optimize it.
						$str2="ALTER TABLE link_cache DROP INDEX `url`, ADD INDEX  `url` (`url` (128))";
						$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
						$messages[]=$str2;
					}
				}
			}
		}
		$required_indexes=array();
		$required_indexes[]='crdate';
		$required_indexes[]='ip_address';
		$required_indexes[]='is_checkout';
		$indexes=array();
		$table_name='tx_multishop_cart_contents';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		foreach ($required_indexes as $required_index) {
			if (!in_array($required_index, $indexes)) {
				$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$str="select plain_text from tx_multishop_product_feeds limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_product_feeds` ADD `plain_text` tinyint(1) default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select starttime from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `starttime` int(11) default '0', ADD KEY `starttime` (starttime)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select endtime from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `endtime` int(11) default '0', ADD KEY `endtime` (endtime)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select hide_in_menu from tx_multishop_categories limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_categories` ADD `hide_in_menu` tinyint(1) default '0', ADD KEY `hide_in_menu` (hide_in_menu)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select user_agent from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `user_agent` varchar(255) default '', ADD KEY `user_agent` (user_agent)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_vat_id from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `tx_multishop_vat_id` varchar(127) default '', ADD KEY `vat_id` (tx_multishop_vat_id)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_coc_id from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `tx_multishop_coc_id` varchar(127) default '', ADD KEY `coc_id` (tx_multishop_coc_id)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select type from tx_multishop_import_jobs limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_import_jobs` ADD `type` varchar(35) NULL default '', ADD KEY `type` (type)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select cruser_id from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `cruser_id` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select content_footer from tx_multishop_manufacturers_cms limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_manufacturers_cms` ADD `content_footer` text NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select meta_title from tx_multishop_manufacturers_cms limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_manufacturers_cms` ADD `meta_title` varchar(254) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select meta_description from tx_multishop_manufacturers_cms limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_manufacturers_cms` ADD `meta_description` text NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select meta_keywords from tx_multishop_manufacturers_cms limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_manufacturers_cms` ADD `meta_keywords` text NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select alert_quantity_threshold from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `alert_quantity_threshold` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select ean_code from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `ean_code` varchar(50) DEFAULT  ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select sku_code from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `sku_code` varchar(50) DEFAULT  ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select vendor_code from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `vendor_code` varchar(50) DEFAULT  ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe `tx_multishop_products`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='ean_code') {
				if ($row['Type']=='varchar(13)') {
					$str2="ALTER TABLE  `tx_multishop_products` CHANGE  `ean_code`  `ean_code` varchar(50) DEFAULT  ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
					$str2="ALTER TABLE  `tx_multishop_products_flat` CHANGE  `ean_code`  `ean_code` varchar(50) DEFAULT  ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="select categories_id_0 from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			for ($i=0; $i<6; $i++) {
				$str="ALTER TABLE `tx_multishop_orders_products` ADD `categories_id_".$i."` int(5) NULL DEFAULT '0'";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
				$str="ALTER TABLE `tx_multishop_orders_products` ADD `categories_name_".$i."` varchar(150) NULL";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$str="select id from tx_multishop_payment_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_payment_log` (
  `id` int(11) NULL auto_increment,
  `orders_id` int(11) NULL DEFAULT '0',
  `multishop_transaction_id` varchar(127) DEFAULT '',
  `provider_transaction_id` varchar(127) DEFAULT '',
  `provider` varchar(127) DEFAULT '',
  `ip_address` varchar(127) DEFAULT '',
  `crdate` int(11) DEFAULT '0',
  `title` varchar(127) DEFAULT '',
  `description` text,  
  `is_error` tinyint(1) DEFAULT '0',
  `status_type` varchar(127) DEFAULT '',
  `raw_data` mediumtext NULL,
  PRIMARY KEY (`id`),
  KEY `orders_id` (`orders_id`),
  KEY `multishop_transaction_id` (`multishop_transaction_id`)
) ENGINE=InnoDB;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select title from tx_multishop_payment_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_payment_log` ADD `title` varchar(127) default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select description from tx_multishop_payment_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_payment_log` ADD `description` text";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select is_error from tx_multishop_payment_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_payment_log` ADD `is_error` tinyint(1) DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select status_type from tx_multishop_payment_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_payment_log` ADD `status_type` varchar(127) default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select attributes_options_groups_id from tx_multishop_attributes_options_groups limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE IF NOT EXISTS `tx_multishop_attributes_options_groups` (
				`attributes_options_groups_id` int(11) NULL AUTO_INCREMENT,
				`language_id` int(5) NULL DEFAULT '0',
				`attributes_options_groups_name` varchar(64) DEFAULT '',
				`sort_order` int(11) DEFAULT '0',
				PRIMARY KEY (`attributes_options_groups_id`,`language_id`),
				KEY `attributes_options_groups_name` (`attributes_options_groups_name`),
				KEY `attributes_options_groups_id` (`attributes_options_groups_id`),
				KEY `sort_order` (`sort_order`)
			) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="select attributes_options_groups_to_products_options_id from tx_multishop_attributes_options_groups_to_products_options limit 1";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if (!$qry) {
				$str="CREATE TABLE IF NOT EXISTS `tx_multishop_attributes_options_groups_to_products_options` (
					`attributes_options_groups_to_products_options_id` int(11) NULL AUTO_INCREMENT,
					`attributes_options_groups_id` int(11) NULL DEFAULT '0',
					`products_options_id` int(11) NULL DEFAULT '0',
					PRIMARY KEY (`attributes_options_groups_to_products_options_id`)
				) ENGINE=MyISAM;";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$str="select street_name from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `street_name` varchar(75) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$query="select * from fe_users";
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array=array();
					$array['street_name']=$row['address'];
					$array['address']=$row['address'].' '.$row['address_number'].$row['address_ext'];
					$array['address']=preg_replace('/\s+/', ' ', $array['address']);
					$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\''.$row['uid'].'\'', $array);
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				}
			}
		}
		$str="select order_unit_id from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `order_unit_id` int(11) NULL DEFAULT '0',ADD INDEX (  `order_unit_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select order_unit_label from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` CHANGE  order_unit_label `order_unit_name` varchar(100) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		} else {
			$str="select order_unit_name from tx_multishop_orders_products limit 1";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if (!$qry) {
				$str="ALTER TABLE  `tx_multishop_orders_products` ADD `order_unit_name` varchar(100) NULL DEFAULT ''";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$str="select order_unit_code from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `order_unit_code` varchar(15) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select categories_id from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `categories_id` int(11) NULL DEFAULT '0',ADD INDEX (  `categories_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select manufacturers_id from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `manufacturers_id` int(11) NULL DEFAULT '0',ADD INDEX (  `manufacturers_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select ip_address from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `ip_address` varchar(100) NULL DEFAULT '',ADD INDEX (`ip_address`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select http_referer from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `http_referer` text NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select http_referer from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `http_referer` text NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select ip_address from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `ip_address` text NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select import_job_id from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `import_job_id` int(11) NULL DEFAULT '0',ADD INDEX (  `import_job_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select street_name from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tt_address` ADD `street_name` varchar(75) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$query="select * from tt_address";
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array=array();
					$array['street_name']=$row['address'];
					$array['address']=$row['address'].' '.$row['address_number'].$row['address_ext'];
					$array['address']=preg_replace('/\s+/', ' ', $array['address']);
					$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'uid=\''.$row['uid'].'\'', $array);
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				}
			}
		}
		$query="update fe_users set gender=0 where gender='m'";
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$query="update fe_users set gender=1 where gender='f'";
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$str="select billing_street_name from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `billing_street_name` varchar(75) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_orders` ADD `delivery_street_name` varchar(75) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$query="select * from tx_multishop_orders";
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array=array();
					$array['billing_street_name']=$row['billing_address'];
					$array['billing_address']=$row['billing_address'].' '.$row['billing_address_number'].$row['billing_address_ext'];
					$array['billing_address']=preg_replace('/\s+/', ' ', $array['billing_address']);
					$array['delivery_street_name']=$row['delivery_address'];
					$array['delivery_address']=$row['delivery_address'].' '.$row['delivery_address_number'].$row['delivery_address_ext'];
					$array['delivery_address']=preg_replace('/\s+/', ' ', $array['delivery_address']);
					$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$row['orders_id'].'\'', $array);
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				}
			}
		}
		$key='DISABLE_CHECKOUT_FOR_GUESTS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Disable checkout for guests', '".$key."', '0', 'Allow or disallow checkout for guests.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select hashed_id from tx_multishop_categories limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_categories` ADD `hashed_id` varchar(32) NULL default '', ADD INDEX `hashed_id` (`hashed_id`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// field will be one if the product is imported by the products importer
		$str="select imported_product from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `imported_product` tinyint(1) NULL default '0', ADD INDEX `imported_product` (`imported_product`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// field will be one if the product is imported by the products importer and it has been changed by the merchant in edit product
		$str="select lock_imported_product from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD `lock_imported_product` tinyint(1) NULL default '0', ADD INDEX `lock_imported_product` (`lock_imported_product`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_ATTRIBUTE_OPTION_IDS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in products attributes values', '".$key."', '', 'This enables the search-engine to also search in the product attribute option values table. Provide the option id(s) here. Example value: 1,2,3.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_MANUFACTURERS_NAME';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in manufacturers name', '".$key."', '0', 'This enables the search-engine to also search in the column manufacturers name.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_VENDOR_CODE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in vendor code', '".$key."', '0', 'This enables the search-engine to also search in the column vendor code.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_CATEGORIES_NAME';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in categories name', '".$key."', '0', 'This enables the search-engine to also search in the column categories name.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_SKU_CODE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in SKU code', '".$key."', '0', 'This enables the search-engine to also search in the column SKU code.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_EAN_CODE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in EAN code', '".$key."', '0', 'This enables the search-engine to also search in the column EAN code.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_PRODUCTS_MODEL';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in products model', '".$key."', '0', 'This enables the search-engine to also search in the column products model.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$required_indexes=array();
		$required_indexes[]='vendor_code';
		$indexes=array();
		$table_name='tx_multishop_products';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		foreach ($required_indexes as $required_index) {
			if (!in_array($required_index, $indexes)) {
				$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$str="select grand_total from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `grand_total` decimal(24,14) DEFAULT  '0.00000000000000'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe `tx_multishop_products`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_price') {
				if ($row['Type']=='decimal(10,4)' or $row['Type']=='decimal(10,2)') {
					$str2="ALTER TABLE  `tx_multishop_products` CHANGE  `products_price`  `products_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='product_capital_price') {
				if ($row['Type']=='decimal(8,2)') {
					$str2="ALTER TABLE  `tx_multishop_products` CHANGE  `product_capital_price`  `product_capital_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="describe `tx_multishop_products_undo`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_price') {
				if ($row['Type']=='decimal(10,4)' or $row['Type']=='decimal(10,2)') {
					$str2="ALTER TABLE  `tx_multishop_products` CHANGE  `products_price`  `products_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="describe `tx_multishop_shipping_methods`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='handling_costs') {
				if ($row['Type']=='decimal(10,4)') {
					$str2="ALTER TABLE  `tx_multishop_shipping_methods` CHANGE  `handling_costs`  `handling_costs` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="describe `tx_multishop_payment_methods`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='handling_costs') {
				if ($row['Type']=='varchar(10)') {
					$str2="ALTER TABLE  `tx_multishop_payment_methods` CHANGE  `handling_costs`  `handling_costs` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$key='ULTRASEARCH_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Ultrasearch Content Element', '".$key."', 'ultrasearch', 'The script name of the Ultrasearch content element. Default value: ultrasearch.', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='PRODUCTS_SEARCH_FALLBACK_SEARCH';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products Search Fallback Search', '".$key."', '0', 'Enables fall back search. This means the search engine tries a different query when the normal query returns zero results.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'', ''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select post_data from tx_multishop_product_feeds limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_product_feeds` ADD  `post_data` text";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select status_last_modified from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `status_last_modified` int(11) NULL DEFAULT '0',ADD INDEX (  `status_last_modified` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$query="select orders_id from tx_multishop_orders where status_last_modified=0 order by orders_id asc";
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$query2="select crdate from tx_multishop_orders_status_history where orders_id='".$row['orders_id']."' order by orders_status_history_id desc limit 1";
				$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res2)>0) {
					$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2);
					$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$row['orders_id'].'\'', array('status_last_modified'=>$row2['crdate']));
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
					$messages[]=$query2;
				}
			}
		}
		$str="describe `tx_multishop_orders_status_history`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='orders_status_history_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders_status_history` CHANGE  `orders_status_history_id`  `orders_status_history_id` INT( 11 ) NULL AUTO_INCREMENT";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='orders_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders_status_history` CHANGE  `orders_id`  `orders_id` INT( 11 ) NULL";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="describe `tx_multishop_orders`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='orders_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders` CHANGE  `orders_id`  `orders_id` INT( 11 ) NULL AUTO_INCREMENT";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='customer_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders` CHANGE  `customer_id`  `customer_id` int(11) NULL";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='shipping_method_costs') {
				if ($row['Type']=='decimal(10,4)') {
					$str2="ALTER TABLE  `tx_multishop_orders` CHANGE  `shipping_method_costs`  `shipping_method_costs` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='payment_method_costs') {
				if ($row['Type']=='decimal(10,4)') {
					$str2="ALTER TABLE  `tx_multishop_orders` CHANGE  `payment_method_costs`  `payment_method_costs` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='discount') {
				if ($row['Type']=='decimal(10,4)') {
					$str2="ALTER TABLE  `tx_multishop_orders` CHANGE  `discount`  `discount` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="describe `tx_multishop_orders_products`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_price') {
				if ($row['Type']=='varchar(15)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products` CHANGE  `products_price`  `products_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='final_price') {
				if ($row['Type']=='varchar(15)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products` CHANGE  `final_price`  `final_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='products_tax_data') {
				if ($row['Type']=='varchar(255)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products` CHANGE  `products_tax_data`  `products_tax_data` text default ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='products_model') {
				if ($row['Type']=='varchar(128)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products` CHANGE  `products_model`  `products_model` varchar(250) default null";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$str="describe `tx_multishop_orders_products_attributes`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='orders_products_attributes_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `orders_products_attributes_id`  `orders_products_attributes_id` int(11) NULL AUTO_INCREMENT";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='orders_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `orders_id`  `orders_id` int(11) DEFAULT '0'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='orders_products_id') {
				if ($row['Type']=='int(5)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `orders_products_id`  `orders_products_id` int(11) DEFAULT '0'";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='products_options') {
				if ($row['Type']=='varchar(64)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `products_options`  `products_options` varchar(250) DEFAULT ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='products_options_values') {
				if ($row['Type']=='varchar(64)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `products_options_values`  `products_options_values` varchar(250) DEFAULT null";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
			if ($row['Field']=='attributes_tax_data') {
				if ($row['Type']=='varchar(255)') {
					$str2="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `attributes_tax_data`  `attributes_tax_data` text DEFAULT ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str2;
				}
			}
		}
		$required_indexes=array();
		$required_indexes[]='email';
		$required_indexes[]='name';
		$required_indexes[]='company';
		$required_indexes[]='starttime';
		$required_indexes[]='endtime';
		$required_indexes[]='country';
		$required_indexes[]='gender';
		$required_indexes[]='telephone';
		$required_indexes[]='mobile';
		$required_indexes[]='first_name';
		$required_indexes[]='middle_name';
		$required_indexes[]='last_name';
		$required_indexes[]='tx_multishop_code';
		$indexes=array();
		$table_name='fe_users';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		foreach ($required_indexes as $required_index) {
			if (!in_array($required_index, $indexes)) {
				$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		// orders table
		$required_indexes=array();
		$required_indexes[]='billing_company';
		$required_indexes[]='billing_name';
		$required_indexes[]='billing_email';
		$required_indexes[]='billing_address';
		$required_indexes[]='billing_zip';
		$required_indexes[]='billing_city';
		$required_indexes[]='billing_telephone';
		$required_indexes[]='billing_mobile';
		$required_indexes[]='delivery_company';
		$required_indexes[]='delivery_name';
		$required_indexes[]='delivery_email';
		$required_indexes[]='delivery_address';
		$required_indexes[]='delivery_zip';
		$required_indexes[]='delivery_city';
		$required_indexes[]='delivery_telephone';
		$required_indexes[]='delivery_mobile';
		$required_indexes[]='shipping_method';
		$required_indexes[]='payment_method';
		$indexes=array();
		$table_name='tx_multishop_orders';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		foreach ($required_indexes as $required_index) {
			if (!in_array($required_index, $indexes)) {
				$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$key='FULLTEXT_SEARCH_MIN_CHARS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Minimum number of chars to use Full-Text Search (MySQL MATCH).', '".$key."', '3', 'Minimum number of chars to use Full-Text Search (MySQL MATCH).', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$tables=array();
		$tables[]='tx_multishop_categories_description';
		$tables[]='tx_multishop_products_description';
		$tables[]='tx_multishop_products_options';
		$tables[]='tx_multishop_products_options_values';
		$tables[]='tx_multishop_products_options_values_extra';
		foreach ($tables as $table) {
			$str="describe ".$table;
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				if ($row['Field']=='language_id') {
					if ($row['Default']=='1') {
						$str2="ALTER TABLE  `".$table."` CHANGE  `language_id`  `language_id` INT( 5 ) NULL DEFAULT  '0'";
						$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
						$messages[]=$str2;
					}
				}
			}
		}
		$str="describe `tx_multishop_products_description`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_shortdescription') {
				if ($row['Type']=='varchar(500)' or $row['Type']=='varchar(255)') {
					$str2="ALTER TABLE  `tx_multishop_products_description` CHANGE  `products_shortdescription`  `products_shortdescription` text default ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str;
					$str2="ALTER TABLE  `tx_multishop_products_description_flat` CHANGE  `products_shortdescription`  `products_shortdescription` text default ''";
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$messages[]=$str;
				}
			}
		}
		$str="select feed_type from tx_multishop_product_feeds limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_product_feeds` ADD  `feed_type` varchar(50) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select deleted from tx_multishop_orders_status limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_status` ADD  `deleted` tinyint(1) NULL default '0', ADD  `crdate` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe `tx_multishop_products`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_price') {
				if ($row['Type']=='decimal(10,4)' || $row['Type']=='decimal(8,2)') {
					$str="ALTER TABLE  `tx_multishop_products` CHANGE  `products_price`  `products_price` decimal(24,14) DEFAULT  '0.00000000000000', CHANGE  `product_capital_price`  `product_capital_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_products_flat` CHANGE  `products_price`  `products_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_products_attributes` CHANGE  `options_values_price`  `options_values_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_specials` CHANGE  `specials_new_products_price`  `specials_new_products_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_specials` CHANGE  `specials_vanaf_price`  `specials_vanaf_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_orders_products_attributes` CHANGE  `options_values_price`  `options_values_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_products_flat` CHANGE  `final_price`  `final_price` decimal(24,14) DEFAULT  '0.00000000000000', CHANGE  `specials_new_products_price`  `specials_new_products_price` decimal(24,14) DEFAULT  '0.00000000000000', CHANGE  `product_capital_price`  `product_capital_price` decimal(24,14) DEFAULT  '0.00000000000000'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select language_id from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `language_id` int(5) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select order_unit_id from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_products` ADD `order_unit_id` int(11) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE `tx_multishop_products_flat` ADD `order_unit_id` int(11) NULL DEFAULT '0', ADD  `order_unit_code` varchar(15) default '',ADD `order_unit_name` varchar(25) default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_order_units limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE tx_multishop_order_units (
		  id int(11) NULL AUTO_INCREMENT,
		  `code` varchar(15) NULL DEFAULT '',
		  crdate int(11) NULL default '0',
		  `page_uid` INT( 11 ) NULL DEFAULT  '0',  
		  PRIMARY KEY (id),
		  KEY `code` (`code`),
		  KEY `page_uid` (`page_uid`)  
		) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_order_units_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE tx_multishop_order_units_description (
		  id int(11) NULL AUTO_INCREMENT,
		  order_unit_id int(11) NULL DEFAULT '0',
		  language_id int(5) NULL DEFAULT '1',
		  `name` varchar(50) DEFAULT NULL,
		  PRIMARY KEY (id),
		  KEY order_unit_id (order_unit_id),
		  KEY `name` (`name`),
		  KEY language_id (language_id)
		) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='REGULAR_SEARCH_MODE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search wildcard operator (%) mode', '".$key."', '%keyword%', 'Search wildcard operator (%) mode', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''%keyword%'',''%keyword'', ''keyword%''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		//OLD_PRODUCTS_SEARCH_MODE
		$key='STORES_MODULE';
		if (isset($settings['GLOBAL_MODULES'][$key])) {
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\''.$key.'\'');
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'configuration_key=\''.$key.'\'');
		}
		$key='OLD_PRODUCTS_SEARCH_MODE';
		if (isset($settings['GLOBAL_MODULES'][$key])) {
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\''.$key.'\'');
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'configuration_key=\''.$key.'\'');
		}
		$key='ENABLE_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Enable Full-Text Search (MySQL MATCH) in products search', '".$key."', '0', 'Enable Full-Text Search (MySQL MATCH) in products search.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select id from tx_multishop_configuration where configuration_key='PRODUCTS_LISTING_AUTOCORRECTION_TYPE' limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="update `tx_multishop_configuration` set configuration_key='PRODUCTS_LISTING_AUTO_COMPLETE_TYPE' where configuration_key='PRODUCTS_LISTING_AUTOCORRECTION_TYPE'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_configuration_values where configuration_key='PRODUCTS_LISTING_AUTO_COMPLETE_TYPE' limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="update `tx_multishop_configuration_values` set configuration_key='PRODUCTS_LISTING_AUTO_COMPLETE_TYPE' where configuration_key='PRODUCTS_LISTING_AUTOCORRECTION_TYPE'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select name from tx_multishop_orders_status_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_orders_status_description` (
			id int(11) NULL AUTO_INCREMENT,
			orders_status_id int(11) NULL DEFAULT '0',
			language_id int(5) NULL DEFAULT '1',
			name varchar(50) DEFAULT NULL,
			PRIMARY KEY (id),
			KEY orders_status_id (orders_status_id),
			KEY name (name),
			KEY language_id (language_id)
			) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$query="select id,name from tx_multishop_orders_status";
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array=array();
					$array['language_id']=0;
					$array['orders_status_id']=$row['id'];
					$array['name']=$row['name'];
					$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status_description', $array);
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				}
			}
		}
		$str="select default_status from tx_multishop_orders_status limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_status` ADD  `default_status` TINYINT( 1 ) NULL DEFAULT  '0',ADD INDEX (  `default_status` ), ADD `page_uid` int(11) NULL default '0', ADD KEY page_uid (page_uid) ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="UPDATE `tx_multishop_orders_status` set `default_status`=1 where id=1";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="alter table tx_multishop_orders_status drop `name`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="update tx_multishop_orders_status set page_uid='".$this->showCatalogFromPage."' where page_uid=0";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// extra check
		$str="select name from tx_multishop_orders_status limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="alter table tx_multishop_orders_status drop `name`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DISPLAY_REALTIME_NOTIFICATION_MESSAGES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin notification', '".$key."', '1', 'Get notified when visitor order/search on your shop.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DISPLAY_SPECIALS_ABOVE_PRODUCTS_LISTING';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Display specials above products listing', '".$key."', '1', 'Enable to display product specials above the products listing page.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'', ''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe `tx_multishop_import_jobs`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='code') {
				if ($row['Type']=='varchar(16)') {
					$str="ALTER TABLE  `tx_multishop_import_jobs` CHANGE  `code`  `code` varchar( 32 ) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select paid from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` CHANGE  `payed`  `paid` TINYINT( 1 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select buildyear from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$columns='
			buildyear
			kilometers
			dealer_price
			delivery_week
			products_legend
			products_article_number
			price_group_id
			wrapper_type
			source_id
			tip
			productfeed
			gordijn_stof
			gordijn_type
			price_perset
			gordijn_fabric
			gordijn_fabric_size
			knipstal
			products_shippingcost
			packages
			is_package
			root_oid
			attributes_values
			is_topsell
			m2_per_box
			qty_per_box
			max_box_per_pallet
			price_type
			dealer_staffel_price
			free_shippingcost
			vanaf_price
			fake_url
			oid
			minimum_order
			did
			pid
			products_pdf1
			products_pdf2
			';
			$array=explode("\n", $columns);
			$drop=array();
			foreach ($array as $column) {
				$column=trim($column);
				if ($column) {
					$drop[]='DROP `'.$column.'`';
				}
			}
			$str="ALTER TABLE `tx_multishop_products`
			".implode(",\n", $drop).";";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select paid from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_invoices` CHANGE  `payed`  `paid` TINYINT( 1 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_configuration where configuration_key='ORDERS_PAYED_CUSTOM_SCRIPT' limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="update `tx_multishop_configuration` set configuration_title='Orders Paid Custom Script', configuration_key='ORDERS_PAID_CUSTOM_SCRIPT',description='Optionally you can process the paid order by your custom script.' where configuration_key='ORDERS_PAYED_CUSTOM_SCRIPT'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_configuration_values where configuration_key='ORDERS_PAYED_CUSTOM_SCRIPT' limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="update `tx_multishop_configuration_values` set configuration_key='ORDERS_PAID_CUSTOM_SCRIPT' where configuration_key='ORDERS_PAYED_CUSTOM_SCRIPT'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select country_tax_id from tx_multishop_tax_rules limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_tax_rules` ADD  `country_tax_id` int(11) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select orders_tax_data from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `orders_tax_data` TEXT";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select products_tax_data from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD  `products_tax_data` TEXT NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select attributes_tax_data from tx_multishop_orders_products_attributes limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products_attributes` ADD  `attributes_tax_data` varchar(255) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tax_id from tx_multishop_payment_methods limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_payment_methods` ADD `tax_id` int(11) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tax_id from tx_multishop_shipping_methods limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_shipping_methods` ADD `tax_id` int(11) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		/*
		 * type of address information
		 * possible value are:
		 * - billing
		 * - delivery
		 */
		$str="select tx_multishop_address_type from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tt_address` ADD `tx_multishop_address_type` varchar(9) NULL DEFAULT 'billing'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// CANADA TAX SUPPORT
		/*
		CREATE TABLE `tx_multishop_tax_rule_groups` (
		  `rules_group_id` int(11) NULL AUTO_INCREMENT,
		  `name` varchar(50) DEFAULT NULL,
		  `status` tinyint(1) NULL DEFAULT '1',
		  PRIMARY KEY (`rules_group_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		
		CREATE TABLE `tx_multishop_tax_rules` (
		  `rule_id` int(11) NULL AUTO_INCREMENT,
		  `name` varchar(50) DEFAULT NULL,
		  `status` tinyint(1) NULL DEFAULT '1',
			`rules_group_id` INT( 11 ) NULL ,
			`country_id` INT( 11 ) NULL ,
			`state_id` INT( 11 ) NULL ,
			`county_id` INT( 11 ) NULL ,
			`tax_id` INT( 11 ) NULL ,
			`state_modus` TINYINT( 1 ) NULL ,
			`county_modus` TINYINT( 1 ) NULL,
		  PRIMARY KEY (`rule_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		*/
		$str="select name from tx_multishop_taxes limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_taxes` (
		  `tax_id` int(4) NULL AUTO_INCREMENT,
		  `name` varchar(50) DEFAULT NULL,
		  `rate` DECIMAL( 6, 4 ) NULL,
		  `status` TINYINT( 1 ) NULL DEFAULT  '1',
		  PRIMARY KEY (`tax_id`)
		) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select name from tx_multishop_tax_rule_groups limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE IF NOT EXISTS `tx_multishop_tax_rule_groups` (
		`rules_group_id` int(11) NULL AUTO_INCREMENT,
		`name` varchar(50) DEFAULT NULL,
		`status` tinyint(1) NULL DEFAULT '1',
		PRIMARY KEY (`rules_group_id`)
		) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select rule_id from tx_multishop_tax_rules limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE IF NOT EXISTS `tx_multishop_tax_rules` (
		`rule_id` int(11) NULL AUTO_INCREMENT,
		`status` tinyint(1) NULL DEFAULT '1',
		`rules_group_id` int(11) NULL,
		`cn_iso_nr` int(11) NULL,
		`zn_country_iso_nr` int(11) NULL,
		`tax_id` int(11) NULL,
		`state_modus` tinyint(1) NULL,
		`county_modus` tinyint(1) NULL,
		`country_tax_id` int(11) NULL,
		PRIMARY KEY (`rule_id`)
		) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// CANADA TAX SUPPORT
		$key='MANUFACTURER_IMAGE_SIZE_NORMAL';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Manufacturer image size', '".$key."', '176x140', 'The maximum image size in pixels for the manufacturer image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_shipping_countries_to_zones limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str='RENAME TABLE `tx_multishop_shipping_countries_to_zones` TO `tx_multishop_countries_to_zones`';
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str='DROP TABLE `tx_multishop_shipping_countries_to_zones`';
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_shipping_zones limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str='RENAME TABLE `tx_multishop_shipping_zones` TO `tx_multishop_zones`';
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str='DROP TABLE `tx_multishop_shipping_zones`';
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_notification limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_notification` (
					  `id` int(11) NULL AUTO_INCREMENT,
					  `title` varchar(150),
					  `message` text,
					  `customer_id` int(11) NULL default '0',
					  `unread` tinyint(1) NULL default '0',
					  `message_type` varchar(35),
					  `crdate` int(11) NULL default '0',
					  PRIMARY KEY (`id`),
					  INDEX ( `unread` ),
					  INDEX ( `customer_id` ),			  
					  INDEX ( `message_type` )			  
				   ) ENGINE=MyISAM";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		/*
		 * Jira ref: MULTISHOP-256
		 */
		$str="select id from tx_multishop_cart_contents limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_cart_contents` (
					  `id` int(11) NULL AUTO_INCREMENT,
					  `contents` text,
					  `customer_id` int(11) NULL default '0',
					  `is_checkout` tinyint(1) NULL default '0',
					  `crdate` int(11) NULL default '0',
					  `session_id` varchar(150) NULL DEFAULT '',
					  `ip_address` varchar(150) NULL DEFAULT '',
					  PRIMARY KEY (`id`),
					  INDEX ( `customer_id` )
				   ) ENGINE=MyISAM";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		/*
		 * Jira ref: MULTISHOP-256 EOL
		 */
		$str="select id from tx_multishop_products_search_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_products_search_log` (
					  `id` int(11) NULL AUTO_INCREMENT,
					  `keyword` varchar(150) NULL,
					  `ip_address` varchar(150) NULL,
					  `crdate` int(11) NULL default '0',
					  `customer_id` int(11) NULL default '0',
					  `categories_id` int(11) NULL default '0',
					  `negative_results` tinyint(1) NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  INDEX keyword ( `keyword` ),
					  INDEX categories_id ( `categories_id` ),
					  INDEX customer_id ( `customer_id` ),
					  INDEX negative_results ( `negative_results` )
				   ) ENGINE=MyISAM";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select categories_id from tx_multishop_products_search_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_search_log` ADD  `categories_id` int(11) NULL default '0', ADD  INDEX categories_id ( `categories_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select customer_id from tx_multishop_products_search_log limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_search_log` ADD  `customer_id` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_search_log` ADD  INDEX ( `customer_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select orders_products_id from tx_multishop_orders_products_downloads limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE  `tx_multishop_orders_products_downloads` (
		`orders_products_id` INT( 11 ) NULL ,
		`orders_id`	 INT( 11 ) NULL ,
		`ip_address` VARCHAR( 255 ) NULL ,
		`date_of_download` INT( 11 ) NULL ,
		PRIMARY KEY (  `orders_products_id` ) ,
		INDEX (  `date_of_download` ),
		INDEX (  `orders_id` )
		) ENGINE=MyISAM;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select file_remote_location from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD  `file_remote_location` TEXT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select file_number_of_downloads from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD  `file_number_of_downloads` INT( 11 ) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select file_remote_location from tx_multishop_products_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD  `file_remote_location` TEXT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select file_number_of_downloads from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD  `file_number_of_downloads` INT( 11 ) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_PRODUCTS_SEARCH_AND_EDIT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Products Search and Edit', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the admin products search and edit page. Example value: fileadmin/scripts/admin_products_search_and_edit', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ULTRASEARCH_SERVER_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Ultrasearch Server Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the ultrasearch server (that generates the ajax products listing). Example value: fileadmin/scripts/ultrasearch_server', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select configuration_key from tx_multishop_configuration where configuration_key='DISABLE_WARNING_SYSTEM'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$str="UPDATE `tx_multishop_configuration` SET `configuration_key` ='DISABLE_MULTISHOP_CONFIGURATION_VALIDATION' WHERE configuration_key='DISABLE_WARNING_SYSTEM';";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		} else {
			$key='DISABLE_MULTISHOP_CONFIGURATION_VALIDATION';
			if (!isset($settings['GLOBAL_MODULES'][$key])) {
				$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Disable validation of Multishop configuration', '".$key."', '0', 'This setting will disable the validation of the Multishop configuration.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		$key='REDIRECT_BACK_TO_PRODUCTS_DETAIL_PAGE_AFTER_ADD_TO_CART';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Add to Cart - Redirect Back to Products Detail Page', '".$key."', '0', 'When this module is enabled the user will be redirected back to the products detail page, after adding the product to the cart.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ENABLED_LANGUAGES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Enabled languages', '".$key."', '', 'Optional field (leave empty to enable all TYPO3 enabled languages). Enable only specific languages. Example value: nl,de,es', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='NUMBER_OF_PRODUCT_IMAGES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Number of Product Images', '".$key."', '5', 'Define how many product images you want to use. Default value: 5', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ENABLED_CURRENCIES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Enabled Currencies', '".$key."', '', 'Optional field (leave empty to use the default). Use this to display the currencies in the multi currency selector dropdownmenu. Example value: USD,EUR,GBP', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DEFAULT_CURRENCY';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Default Currency', '".$key."', '', 'Optional field (leave empty to use the default). Use this to select the default currency in the multi currency selector. Example value: USD', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select store_currency from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_invoices` ADD `store_currency` char(3) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select customer_currency from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_invoices` ADD `customer_currency` char(3) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select currency_rate from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_invoices` ADD `currency_rate` varchar(15) NULL default '1'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select store_currency from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `store_currency` char(3) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select customer_currency from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `customer_currency` char(3) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select currency_rate from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `currency_rate` varchar(15) NULL default '1'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_MANUFACTURERS_EDIT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Manufacturers Edit Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the manufacturers products listing page. Example value: fileadmin/scripts/admin_manufacturers_edit', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='MANUFACTURERS_PRODUCTS_LISTING_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Manufacturers Products Listing Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the manufacturers products listing page. Example value: fileadmin/scripts/manufacturers_products_listing', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ORDER_HISTORY_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Account Order History', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the accounts order history page. Example value: fileadmin/scripts/order_history', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_CUSTOMERS_IMPORT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Customers Import Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the customers import module. Example value: fileadmin/scripts/admin_customers_import', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SPECIALS_SECTION_LISTING_TYPE';
		$title='Specials Section Listing Type';
		$description='The lay-out type for displaying the listing of the specials (based on section)';
		$default_value='default';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 6, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ALLOW_ORDER_OUT_OF_STOCK_PRODUCT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Allowing customers to make reservation order for out of stock product(s)', '".$key."', '1', 'Allowing customers to make reservation order for out-of-stock product(s), by enabling this setting the \'Disable Product When Negative Stock\' module will not functioning properly even when the module turned on.', 10, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'', ''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DISABLE_OUT_OF_STOCK_PRODUCT_WARNING_MESSAGE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Allowing webshop owner to disable out of stock warning message', '".$key."', '1', 'Disable the out of stock warning message on shopping cart page', 10, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'', ''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DISABLE_PRODUCT_WHEN_NEGATIVE_STOCK';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Disable Product When Negative Stock', '".$key."', '0', 'Automatically turn off the product when the stock level gets negatave.', 10, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'', ''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_ORDERS_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Orders Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the admin orders listing page. Example value: fileadmin/scripts/admin_orders)', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='LOCK_ORDER_AFTER_CREATING_INVOICE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Lock Order After Creating Invoice', '".$key."', '1', 'When this setting is enabled the order will be locked after creating the invoice.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'', ''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select track_and_trace_code from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `track_and_trace_code` varchar(50) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select expected_delivery_date from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `expected_delivery_date` int(11) NULL default '0', ADD KEY expected_delivery_date (expected_delivery_date)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select address_ext from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `address_ext` varchar(10) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select address_ext from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tt_address` ADD `address_ext` varchar(10) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select billing_address_ext from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `billing_address_ext` varchar(10) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select delivery_address_ext from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `delivery_address_ext` varchar(10) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_source_id from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `tx_multishop_source_id` varchar(50) NULL default '', ADD KEY tx_multishop_source_id (tx_multishop_source_id)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select hash from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `hash` varchar(50) NULL default '0', ADD KEY hash (hash)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select hash from tx_multishop_cms limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_cms` ADD `hash` varchar(50) NULL default '', ADD KEY hash (hash)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$query="select id from tx_multishop_cms where hash=''";
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_cms', 'id=\''.$row['id'].'\'', array('hash'=>md5(uniqid('', TRUE))));
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				}
			}
		}
		$str="select products_options_id from tx_multishop_orders_products_attributes limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products_attributes` ADD `products_options_id` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select products_options_values_id from tx_multishop_orders_products_attributes limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products_attributes` ADD `products_options_values_id` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select sort_orders from tx_multishop_products_options_values";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_products_options_values` DROP  `sort_orders`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select qty_delivered from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `qty_delivered` int(4) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select qty_not_deliverable from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders_products` ADD `qty_not_deliverable` int(4) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select cruser_id from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `cruser_id` int(11) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select products_extra_description from tx_multishop_products_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_products_description` DROP  `products_extra_description`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_description` DROP  `products_extra_tab_description1`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_description` DROP  `products_extra_tab_description2`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe `tx_multishop_products`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='staffel_price') {
				if ($row['Type']=='varchar(127)') {
					$str="ALTER TABLE  `tx_multishop_products` CHANGE  `staffel_price`  `staffel_price` varchar( 250 ) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_products_flat` CHANGE  `staffel_price`  `staffel_price` varchar( 250 ) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select required from tx_multishop_products_options limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_options` ADD `required` tinyint(1) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select page_uid from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD `page_uid` int(11) NULL default '0', ADD KEY page_uid (page_uid)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select is_proposal from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD `is_proposal` tinyint(1) NULL default '0', ADD KEY is_proposal (is_proposal)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_ORDER_PROPOSAL_MODULE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Order Proposal System', '".$key."', '0', 'Enable the order proposal system.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CUSTOMERS_DATA_EXPORT_IMPORT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin customers export/import data', '".$key."', '0', 'Enable export/import data of the customers.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_INVOICE_MODULE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Invoice System', '".$key."', '0', 'Enable the invoice system.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CART_PAGE_UID';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Cart session page uid', '".$key."', '', 'Normally the cart of your customer is shared with all shop in shops you create. By giving this setting a custom number the cart is only used on the current shop.', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='PRODUCTS_LISTING_SORT_ORDER_OPTION';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products Listing Sort-Order Option', '".$key."', 'desc', 'Normally the products in the products listing page gets sorted ascending. By this setting you can switch it to descending.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''asc'',''desc''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='PRODUCTS_NEW_NUMBER_OF_DAYS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products New: number of days', '".$key."', '30', 'The number of days that a product should be listed on the latest products page.', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='MANUFACTURERS_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Manufacturers Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the manufacturers listing page. Example value: fileadmin/scripts/manufacturers_listing', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$default_value=0;
		$key='INCLUDE_VAT_OVER_METHOD_COSTS';
		if (isset($settings['GLOBAL_MODULES'][$key])) {
			$str="select * from tx_multishop_configuration where configuration_key='".$key."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			if ($row['set_function']=='tep_cfg_select_option(array(\'1\',\'0\'),') {
				// if its boolean delete it and recreate it
				if ($row['configuration_value']) {
					$default_value='19';
				} else {
					$default_value=0;
				}
				$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\''.$key.'\'');
				$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'configuration_key=\''.$key.'\'');
				unset($settings['GLOBAL_MODULES'][$key]);
			}
		}
		$key='GEONAMES_USERNAME';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Geonames username', '".$key."', '', 'Geonames username. You can register your username here: http://www.geonames.org/.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEARCH_ALSO_IN_PRODUCTS_ID';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in products id', '".$key."', '0', 'This enables the search-engine to also search for the products id.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		if (isset($settings['GLOBAL_MODULES']['INVOICE_PREFIX']) and $settings['GLOBAL_MODULES']['INVOICE_PREFIX']=='0') {
			// zero is an invalid prefix. lets remove it
			$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_configuration', 'configuration_key=\'INVOICE_PREFIX\'', array('configuration_value'=>''));
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
		}
		$str="select reversal_invoice from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_invoices` ADD  `reversal_invoice` tinyint(1) NULL default '0', ADD KEY reversal_invoice (reversal_invoice), ADD   reversal_related_id int(11) NULL default '0', ADD KEY reversal_related_id (reversal_related_id)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select is_locked from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `is_locked` tinyint(1) NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// rebuild all invoices
		/*			
		$query="select orders_id from tx_multishop_orders where paid=1 order by orders_id asc";
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
		{
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
			{
				$tmp=mslib_fe::createOrderInvoice($row['orders_id']);					
			}	
		}			
		*/
		// rebuild all invoices eof		
		$str="describe `tx_multishop_products_description`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_url') {
				if ($row['Type']=='varchar(255)') {
					$str="ALTER TABLE  `tx_multishop_products_description` CHANGE  `products_url`  `products_url` TEXT";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe `tx_multishop_categories`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='categories_url') {
				if ($row['Type']=='varchar(254)') {
					$str="ALTER TABLE  `tx_multishop_categories` CHANGE  `categories_url`  `categories_url` TEXT";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe `tx_multishop_shipping_methods`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='provider') {
				if ($row['Type']=='varchar(25)') {
					$str="ALTER TABLE  `tx_multishop_shipping_methods` CHANGE  `provider`  `provider` varchar( 50 ) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe `tx_multishop_payment_methods`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='provider') {
				if ($row['Type']=='varchar(25)') {
					$str="ALTER TABLE  `tx_multishop_payment_methods` CHANGE  `provider`  `provider` varchar( 50 ) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select page_uid, zone_id from tx_multishop_shipping_methods limit 1";
		if (!$qry=$GLOBALS['TYPO3_DB']->sql_query($str)) {
			$str="ALTER TABLE `tx_multishop_shipping_methods`
			ADD  `page_uid` INT( 11 ) NULL DEFAULT  '0',
			ADD  `zone_id` INT( 11 ) NULL DEFAULT  '0',
			ADD INDEX (  `page_uid` ,  `zone_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select page_uid, zone_id from tx_multishop_payment_methods limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry=$GLOBALS['TYPO3_DB']->sql_query($str)) {
			$str="ALTER TABLE `tx_multishop_payment_methods`
				ADD  `page_uid` INT( 11 ) NULL DEFAULT  '0',
				ADD  `zone_id` INT( 11 ) NULL DEFAULT  '0',
				ADD INDEX (  `page_uid` ,  `zone_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_payment_methods_to_zones limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE  `tx_multishop_payment_methods_to_zones` (
						`id` int(11) NULL AUTO_INCREMENT,
						`zone_id` int(4) DEFAULT '0',
						`payment_method_id` int(11) DEFAULT '0',
						PRIMARY KEY (`id`),
						UNIQUE KEY `zone_id` (`zone_id`,`payment_method_id`)
					) ENGINE=MyISAM";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_shipping_methods_to_zones limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE  `tx_multishop_shipping_methods_to_zones` (
						`id` int(11) NULL AUTO_INCREMENT,
						`zone_id` int(4) DEFAULT '0',
						`shipping_method_id` int(11) DEFAULT '0',
						PRIMARY KEY (`id`),
						UNIQUE KEY `zone_id` (`zone_id`,`shipping_method_id`)
					) ENGINE=MyISAM";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DISABLE_CRUMBAR';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Disable crumbar navigation menu', '".$key."', '0', 'Disable internal crumbar navigation menu.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='INCLUDE_PRODUCTS_DESCRIPTION_DB_FIELD_IN_PRODUCTS_LISTING';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Include products description database field', '".$key."', '0', 'Include products description database field in the products listing page.', 6, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DISABLE_VAT_RATE_WHEN_CROSS_BORDERS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Disable VAT rate when cross borders', '".$key."', '1', 'When a customer is from a different country than the store owner calculate zero tax.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''0'',''1''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='PRODUCTS_DETAIL_NUMBER_OF_TABS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products Detail Tabs', '".$key."', '0', 'Optional field. Number of tabs used on the products detail page.', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='PRODUCTS_DETAIL_NUMBER_OF_TABS';
		if (isset($settings['GLOBAL_MODULES'][$key])) {
			// check if the description table has enough tabs fields
			$total=$settings['GLOBAL_MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS'];
			if ($total) {
				for ($i=1; $i<=$total; $i++) {
					$name='products_description_tab_content_'.$i;
					$str="select ".$name." from tx_multishop_products_description limit 1";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if (!$qry) {
						$str="ALTER TABLE  `tx_multishop_products_description` ADD  `products_description_tab_content_".$i."` TEXT NULL";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$messages[]=$str;
						$str="ALTER TABLE  `tx_multishop_products_description` ADD  `products_description_tab_title_".$i."` varchar(50) NULL";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$messages[]=$str;
					}
				}
			}
		}
		$key='NUMBER_OF_PRODUCT_IMAGES';
		if (isset($settings['GLOBAL_MODULES'][$key])) {
			// check if the products table has enough product image fields
			for ($x=0; $x<=($settings['GLOBAL_MODULES']['NUMBER_OF_PRODUCT_IMAGES']-1); $x++) {
				$i=$x;
				if ($i==0) {
					$i='';
				}
				$field='products_image'.$i;
				$str="select ".$field." from tx_multishop_products limit 1";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if (!$qry) {
					$str="ALTER TABLE  `tx_multishop_products` ADD `products_image".$i."` varchar(50) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('id', // SELECT ...
			'tx_multishop_invoices', // FROM ...
			'hash=\'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_invoices', 'id=\''.$row['id'].'\'', array('hash'=>md5(uniqid('', TRUE))));
				$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			}
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('uid', // SELECT ...
			'fe_users', // FROM ...
			'tx_multishop_code=\'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\''.$row['uid'].'\'', array('tx_multishop_code'=>md5(uniqid('', TRUE))));
				$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			}
		}
		$key='ADMIN_CUSTOMERS_EDIT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Customers Edit Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the edit customer form. Example value: fileadmin/scripts/admin_edit_customer', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_PRODUCTS_IMPORT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Products Import Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the products import module. Example value: fileadmin/scripts/admin_import', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DOWNLOAD_INVOICE_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Download Invoice Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the PDF invoice. Example value: fileadmin/scripts/download_invoice', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CATEGORIES_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Categories Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the categories navigation box content element. Example value: fileadmin/scripts/categories_nav', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CRUMBAR_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Crumbar Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the crumbar. Example value: fileadmin/scripts/crumbar', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='BASKET_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Basket Content Element Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the cart contents element. Example value: fileadmin/scripts/basket_content_element', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CREATE_ACCOUNT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Create Account Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the create account page. Example value: fileadmin/scripts/create_account', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='EDIT_ACCOUNT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Edit Account Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the edit account page. Example value: fileadmin/scripts/edit_account', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='INVOICE_PDF_HEADER_IMAGE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(12, 'Invoice Settings', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Invoice PDF header image', '".$key."', '', 'Full URL to header image of the PDF invoice.', 12, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$key='INVOICE_PDF_FOOTER_IMAGE';
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Invoice PDF footer image', '".$key."', '', 'Full URL to footer image of the PDF invoice.', 12, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='MULTISHOP_VERSION';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Multishop version', '".$key."', '0', 'Database version.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SUBTRACT_STOCK';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Subtract Stock', '".$key."', '0', 'Enable the subtraction of the products stock level.', 10, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_code from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD  `tx_multishop_code` varchar( 50 ) NULL ,ADD INDEX (  `tx_multishop_code` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_optin_ip from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD  `tx_multishop_optin_ip` varchar( 50 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_optin_crdate from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD  `tx_multishop_optin_crdate` int(10) unsigned NULL default '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_newsletter from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD  `tx_multishop_newsletter` tinyint( 1 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select address_number from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tt_address` ADD  `address_number` varchar( 10 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select customer_id from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tt_address` DROP  `customer_id`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_customer_id from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tt_address` ADD  `tx_multishop_customer_id` INT( 11 ) NULL, ADD INDEX (`tx_multishop_customer_id`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_default from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tt_address` ADD  `tx_multishop_default` tinyint( 1 ) NULL, ADD INDEX (`tx_multishop_default`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select products_condition from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD products_condition varchar(20) NULL DEFAULT 'new'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select include_header from tx_multishop_product_feeds limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_product_feeds` ADD include_header tinyint(1) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select customer_id from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_invoices` ADD customer_id int(11) NULL,ADD crdate int(11) DEFAULT '0',
			ADD due_date int(11) NULL,
			ADD reference varchar(150) NULL,
			ADD ordered_by varchar(50) NULL,
			ADD invoice_to varchar(50) NULL,
			ADD payment_condition varchar(50) NULL,
			ADD currency varchar(5) NULL,
			ADD discount double(10,4) NULL,
			ADD amount double(10,4) NULL,
			ADD page_uid int(11) NULL,
			ADD paid tinyint(1) NULL,
			ADD `hash` varchar(50) NULL,
			ADD KEY paid (paid),
			ADD KEY `hash` (`hash`),
			ADD KEY customer_id (customer_id)
			";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select count(1) as total from tx_multishop_products_description where language_id=0";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if (!$row['total']) {
			$str="select count(1) as total from tx_multishop_products_description";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			if ($row['total']) {
				// there are products in the database, but they are not saved as default language id 0, so lets correct this
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_description', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers_cms', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers_info', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_extra', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_reviews_description', '', array('language_id'=>0));
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$messages[]='Whole catalog is updated to the default language ID.';
			}
		}
		$str="select name from tx_multishop_payment_methods limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="CREATE TABLE tx_multishop_payment_methods_description (
		id int(4) NULL DEFAULT '0',
		language_id int(5) NULL DEFAULT '1',
		name varchar(255) DEFAULT NULL,
		description text,
		PRIMARY KEY (id,language_id),
		KEY name (name),
		KEY id (id),
		KEY language_id (language_id),
		KEY combined_two (language_id,id)
		) ENGINE=MyISAM;
		";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="select id, name from tx_multishop_payment_methods";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$array=array();
				$array['id']=$row['id'];
				$array['name']=$row['name'];
				$array['language_id']=$this->sys_language_uid;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_description', $array);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$str="ALTER TABLE  `tx_multishop_payment_methods` DROP  `name`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select name from tx_multishop_shipping_methods limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="CREATE TABLE tx_multishop_shipping_methods_description (
		id int(4) NULL DEFAULT '0',
		language_id int(5) NULL DEFAULT '1',
		name varchar(255) DEFAULT NULL,
		description text,
		PRIMARY KEY (id,language_id),
		KEY name (name),
		KEY id (id),
		KEY language_id (language_id),
		KEY combined_two (language_id,id)
		) ENGINE=MyISAM;
		";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="select id, name from tx_multishop_shipping_methods";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$array=array();
				$array['id']=$row['id'];
				$array['name']=$row['name'];
				$array['language_id']=$this->sys_language_uid;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_description', $array);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$str="ALTER TABLE  `tx_multishop_shipping_methods` DROP  `name`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select customer_comments from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `customer_comments` TEXT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select delivery_time from tx_multishop_products_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD  `delivery_time` varchar( 150 ) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select sku_code from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD  `sku_code` varchar( 25 ) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_flat` ADD  `sku_code` varchar( 25 ) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_undo_products` ADD  `sku_code` varchar( 25 ) NULL default ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX `sku_code` (  `sku_code` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select shipping_method_label from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `shipping_method_label` varchar( 150 ) NULL ,
		ADD  `payment_method_label` varchar( 150 ) NULL ,
		ADD  `discount` decimal( 10, 4 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select max_usage from tx_multishop_coupons limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_coupons` ADD  `crdate` INT( 11 ) NULL ,
			ADD  `max_usage` INT( 11 ) NULL DEFAULT '0',
			ADD  `discount_type` varchar( 25 ) NULL DEFAULT  'percentage'
			";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_product_feeds limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_product_feeds` (
			  `id` int(11) NULL AUTO_INCREMENT,
			  `name` varchar(75) NULL,
			  `page_uid` int(11) NULL DEFAULT '0',
			  `crdate` int(11) NULL DEFAULT '0',
			  `utm_source` varchar(75) NULL,
			  `utm_medium` varchar(75) NULL,
			  `utm_term` varchar(75) NULL,
			  `utm_content` varchar(75) NULL,
			  `utm_campaign` varchar(75) NULL,
			  `fields` text NULL,
			  `format` varchar(25) NULL,
			  `delimiter` varchar(10) NULL,
			  `code` varchar(150) NULL,
			  `status` tinyint(1) NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `code` (`code`)
			) ENGINE=MyISAM;
			";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe `tx_multishop_payment_transactions`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='transaction_id') {
				if ($row['Type']=='varchar(65)') {
					$str="ALTER TABLE  `tx_multishop_payment_transactions` CHANGE  `transaction_id`  `transaction_id` varchar( 150 ) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$key='ADMIN_EDIT_ORDER_PRINT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Edit Order Print Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the admin edit order print page. Example value: fileadmin/scripts/admin_edit_order_print)', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_EDIT_ORDER_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Edit Order Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the admin edit order page. Example value: fileadmin/scripts/admin_edit_order)', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_CATEGORIES_EDIT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Categories Edit Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the admin categories edit page. Example value: fileadmin/scripts/admin_categories_edit)', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ADMIN_PRODUCTS_EDIT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Products Edit Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the admin products edit page. Example value: fileadmin/scripts/admin_products_edit)', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SHOPPING_CART_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Shopping cart Type', '".$key."', '', 'Optional field (leave empty to use the default). Use this for customizing the shopping cart page. Example value: fileadmin/scripts/shopping_cart)', 11, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$indexes=array();
		$table_name='tx_multishop_configuration_values';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('configuration_key', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_configuration_values` ADD INDEX `configuration_key` (  `configuration_key` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_configuration_values` ADD INDEX `page_uid` (  `page_uid` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_configuration_values` ADD FULLTEXT `configuration_value` (`configuration_value`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_configuration_values` ADD INDEX  `admin_search` (  `configuration_key` ,  `page_uid` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_configuration` ADD INDEX `configuration_title` (  `configuration_title` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_configuration` ADD FULLTEXT `configuration_value` (`configuration_value`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_configuration` ADD INDEX  `admin_search` (  `configuration_title` ,  `configuration_key` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `fe_users` ADD INDEX  `disable` (`disable`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `fe_users` ADD INDEX  `deleted` (`deleted`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `fe_users` ADD INDEX  `admin_search` (`company`,`name`,`email`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_orders` ADD INDEX `deleted` (  `deleted` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_orders` ADD INDEX `crdate` (  `crdate` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select * from tx_multishop_configuration_group where id='11'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(11, 'Admin Settings', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_EDIT_METHOD_FILTER';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product edit method filter', '".$key."', '0', 'Enables the shipping / payment methods filter on the product edit page.', 11, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select hide_in_cart from tx_multishop_products_options";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_products_options` ADD  `hide_in_cart` tinyint( 1 ) NULL DEFAULT '0', ADD INDEX (`hide_in_cart`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='ULTRASEARCH';
		if (isset($settings['GLOBAL_MODULES'][$key])) {
			$str="delete from `tx_multishop_configuration` where configuration_key='".$key."';";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		/*
		$key='ULTRASEARCH_TYPE';
		if (isset($settings['GLOBAL_MODULES'][$key]))
		{
			$str="delete from `tx_multishop_configuration` where configuration_key='".$key."';";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);					
			$messages[]=$str;
		}
		*/
		$key='PRODUCTS_RELATIVES_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products Relatives Type', '".$key."', 'default', 'The lay-out type for displaying the products relatives on the products detail page.', 7, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select billing_address_number from tx_multishop_orders";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `billing_address_number` varchar( 10 ) DEFAULT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select delivery_address_number from tx_multishop_orders";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `delivery_address_number` varchar( 10 ) DEFAULT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select products_id from tx_multishop_products_method_mappings";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE  `tx_multishop_products_method_mappings` (
		`id` INT( 11 ) NULL AUTO_INCREMENT PRIMARY KEY ,
		`products_id` INT( 11 ) NULL DEFAULT '0',
		`method_id` INT( 11 ) NULL DEFAULT '0' ,
		`type` varchar( 25 ) NULL ,
		`negate` tinyint( 1 ) NULL DEFAULT '0' ,
		INDEX (  `products_id` ,  `method_id` ,  `type` , `negate` )
		) ENGINE = MYISAM ;
		";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select sort_order from tx_multishop_products_options_values_to_products_options";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_options_values_to_products_options` ADD  `sort_order` INT( 11 ) NULL DEFAULT '0', ADD INDEX (  `sort_order` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select minimum_quantity from tx_multishop_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD  `minimum_quantity` INT( 11 ) NULL DEFAULT '1'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_flat` ADD  `minimum_quantity` INT( 11 ) NULL DEFAULT '1'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select maximum_quantity from tx_multishop_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD  `maximum_quantity` INT( 11 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_flat` ADD  `maximum_quantity` INT( 11 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='DB_PRICES_INCLUDE_VAT';
		// not yet got the time to finish this implementation so temporary will be removed
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			/*
				$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Database prices are stored including VAT', '".$key."', '0', 'Enable this when you want to store the prices in the database including VAT.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			*/
		} else {
			// remove it, will be added later
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\''.$key.'\'');
		}
		$key='SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Search also in products description', '".$key."', '0', 'This enables the search-engine to also search in the products description.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$indexes=array();
		$table_name='tx_multishop_products_to_relative_products';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('pid_to_relative_id', $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `pid_to_relative_id` (  `products_id`,`relative_product_id` )	";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		if (!in_array('relative_to_pid_id', $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `relative_to_pid_id` (  `relative_product_id`,`products_id` )	";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe tx_multishop_products_flat";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='language_id') {
				if ($row['Type']=='int(5)') {
					$str="ALTER TABLE  `tx_multishop_products_flat` CHANGE  `language_id`  `language_id` tinyint( 2 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe tx_multishop_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_model') {
				if ($row['Type']!='varchar(128)') {
					$str="ALTER TABLE  `tx_multishop_products` CHANGE  `products_model`  `products_model` varchar(128) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_products_flat` CHANGE  `products_model`  `products_model` varchar(128) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
					$str="ALTER TABLE  `tx_multishop_orders_products` CHANGE  `products_model`  `products_model` varchar(128) NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe tx_multishop_specials_sections";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='name') {
				if ($row['Type']=='varchar(15)') {
					$str="ALTER TABLE  `tx_multishop_specials_sections` CHANGE  `name`  `name` varchar( 30 ) NULL DEFAULT NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select file_label from tx_multishop_orders_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD `file_label` varchar( 250 ) NULL DEFAULT '', ADD `file_location` varchar( 250 ) NULL DEFAULT '', ADD `file_downloaded` INT(11) NULL DEFAULT '0', ADD `file_download_code` varchar( 32 ) NULL DEFAULT '0', ADD `file_locked` tinyint(1) NULL DEFAULT '0', ADD INDEX (`file_download_code`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select file_label from tx_multishop_products_description";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD `file_label` varchar( 250 ) NULL DEFAULT '', ADD `file_location` varchar( 250 ) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select products_multiplication from tx_multishop_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD  `products_multiplication` INT( 11 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_flat` ADD  `products_multiplication` INT( 11 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_discount from fe_groups";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_groups` ADD  `tx_multishop_discount` INT( 2 ) NULL DEFAULT '0',ADD INDEX (  `tx_multishop_discount` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select code from tx_multishop_payment_transactions";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_payment_transactions` ADD `code` varchar( 35 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select tx_multishop_discount from fe_users";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `fe_users` ADD  `tx_multishop_discount` INT( 2 ) NULL DEFAULT '0',ADD INDEX (  `tx_multishop_discount` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select languages_id from tx_multishop_reviews_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_reviews_description` CHANGE  `languages_id`  `language_id` tinyint( 2 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_reviews_description` DROP  `languages_id`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select languages_id from tx_multishop_cms_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_cms_description` CHANGE  `languages_id`  `language_id` tinyint( 2 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_cms_description` DROP  `languages_id` ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select languages_id from tx_multishop_manufacturers_cms limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_manufacturers_cms` CHANGE  `languages_id`  `language_id` tinyint( 2 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_manufacturers_cms` DROP  `languages_id` ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select languages_id from tx_multishop_manufacturers_info limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE  `tx_multishop_manufacturers_info` CHANGE  `languages_id`  `language_id` tinyint( 2 ) NULL DEFAULT  '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_manufacturers_info` DROP  `languages_id` ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe tx_multishop_configuration_values";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='configuration_value') {
				if ($row['Type']=='varchar(254)') {
					$str="ALTER TABLE `tx_multishop_configuration_values` CHANGE  `configuration_value`  `configuration_value` TEXT DEFAULT NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				}
			}
		}
		$key='DISABLE_PRODUCT_ATTRIBUTES_TAB_IN_EDITOR';
		$title='Disable product attributes tab in products editor';
		$description='Disables the product attributes tab in the products editor';
		$default_value='0';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'',''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SPECIALS_LISTING_TYPE';
		$title='Specials Listing Type';
		$description='The lay-out type for displaying the listing of the specials';
		$default_value='default';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 6, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_payment_transactions";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE  `tx_multishop_payment_transactions` (
		`id` INT( 11 ) NULL AUTO_INCREMENT PRIMARY KEY ,
		`orders_id` INT( 11 ) NULL DEFAULT '0' ,
		`transaction_id` varchar( 65 ) NULL ,
		`psp` varchar( 25 ) NULL ,
		`crdate` INT( 11 ) NULL DEFAULT '0' ,
		`status` tinyint( 1 ) NULL DEFAULT '0' ,
		INDEX (  `orders_id` ,  `transaction_id` ,  `crdate` ,  `status` )
		) ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_feeds_excludelist";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE IF NOT EXISTS `tx_multishop_feeds_excludelist` (
			  	`id` int(11) NULL AUTO_INCREMENT,
			  	`feed_id` int(11) NULL DEFAULT '0',
			  	`exclude_id` int(11) NULL DEFAULT '0',
			  	`exclude_type` varchar(11) CHARACTER SET utf8 NULL DEFAULT 'categories',
			  	PRIMARY KEY (`id`),
			  	KEY `feed_id` (`feed_id`),
			  	KEY `exclude_id` (`exclude_id`),
			  	KEY `exclude_type` (`exclude_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_feeds_stock_excludelist";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE IF NOT EXISTS `tx_multishop_feeds_stock_excludelist` (
			  	`id` int(11) NULL AUTO_INCREMENT,
			  	`feed_id` int(11) NULL DEFAULT '0',
			  	`exclude_id` int(11) NULL DEFAULT '0',
			  	`exclude_type` varchar(11) CHARACTER SET utf8 NULL DEFAULT 'categories',
			  	PRIMARY KEY (`id`),
			  	KEY `feed_id` (`feed_id`),
			  	KEY `exclude_id` (`exclude_id`),
			  	KEY `exclude_type` (`exclude_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='PRICE_FILTER_BOX_STEPPINGS';
		$title='Price Filter Box Steppings';
		$description='Optional field. Defines the steppings of the price filter box.';
		$default_value='0-10;10-25;25-50;50-100;100-250;250-500;500-1000;1000-2000;2000-3000';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO';
		$title='Send Order Confirmation Letters also to';
		$description='Send the order confirmation letter also to the following e-mail addresses.';
		$default_value='';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="describe tx_multishop_orders";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='paid') {
				if (!isset($row['Default'])) {
					$str="ALTER TABLE  `tx_multishop_orders` CHANGE  `paid`  `paid` tinyint( 1 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select custom_settings from tx_multishop_categories limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_categories` ADD  `custom_settings` TEXT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select custom_settings from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_products` ADD  `custom_settings` TEXT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select start_date from tx_multishop_specials limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_specials` ADD  `start_date` INT( 11 ) NULL DEFAULT '0' AFTER  `specials_last_modified`,ADD INDEX (  `start_date` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select contains_image from tx_multishop_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_products` ADD  `contains_image` tinyint( 1 ) NULL DEFAULT '0',ADD INDEX (  `contains_image` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_products_flat` ADD  `contains_image` tinyint( 1 ) NULL DEFAULT '0',ADD INDEX (  `contains_image` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='SHOW_PRODUCTS_WITH_IMAGE_FIRST';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Show products with image first', '".$key."', '0', 'If this setting is enabled the products that contains an image will be shown first.', 7, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CHECKOUT_ENABLE_STATE';
		$title='Enable state';
		$description='Show or hide the state/region inputfield.';
		$default_value=0;
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 8, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='CHECKOUT_ENABLE_BIRTHDAY';
		$title='Enable birthday';
		$description='Show or hide the birthday inputfield.';
		$default_value=0;
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 8, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// old updates			
		$str="describe tx_multishop_products_options";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_options_id') {
				if ($row['Extra']!='auto_increment') {
					$str="ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_id`  `products_options_id` INT( 11 ) NULL AUTO_INCREMENT;";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe tx_multishop_categories";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='date_added') {
				if ($row['Type']=='datetime') {
					// date fixer
					$fields='tx_multishop_categories.date_added
					tx_multishop_categories.last_modified
					tx_multishop_configuration.last_modified
					tx_multishop_configuration.date_added
					tx_multishop_manufacturers.date_added
					tx_multishop_manufacturers.last_modified
					tx_multishop_manufacturers_info.date_last_click
					tx_multishop_modules.date
					tx_multishop_payment_methods.date
					tx_multishop_products.products_date_added
					tx_multishop_products.products_last_modified
					tx_multishop_products.products_date_available
					tx_multishop_products_flat.products_date_added
					tx_multishop_products_flat.products_date_available
					tx_multishop_reviews.date_added
					tx_multishop_reviews.last_modified
					tx_multishop_specials.specials_last_modified
					tx_multishop_specials.specials_date_added
					tx_multishop_specials.expires_date
					tx_multishop_specials.date_status_change
					tx_multishop_undo_products.products_last_modified
					tx_multishop_undo_products.products_date_added
					tx_multishop_undo_products.products_date_available
					tx_multishop_coupons.startdate
					tx_multishop_coupons.enddate			
					tx_multishop_shipping_methods.date
					tx_multishop_shipping_options.date
					tx_multishop_specials_sections.date
					';
					$array=explode("\n", $fields);
					$output=array();
					foreach ($array as $item) {
						$item=trim($item);
						$var=explode(".", $item);
						if ($var[0] and $var[1]) {
							$str="ALTER TABLE  `".$var[0]."` CHANGE  `".$var[1]."`  `".$var[1]."` INT( 11 ) DEFAULT 0;";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							$messages[]=$str;
						}
					}
					break;
					// date fixer eof
				}
			}
		}
		$str="describe tx_multishop_products_options_values";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_options_values_id') {
				if ($row['Extra']!='auto_increment') {
					$str="ALTER TABLE  `tx_multishop_products_options_values` CHANGE  `products_options_values_id`  `products_options_values_id` INT( 11 ) NULL AUTO_INCREMENT;";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="describe tx_multishop_products_options_values_to_products_options";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['Field']=='products_options_values_to_products_options_id') {
				if ($row['Extra']!='auto_increment') {
					$str="ALTER TABLE `tx_multishop_products_options_values_to_products_options` CHANGE `products_options_values_to_products_options_id` `products_options_values_to_products_options_id` INT( 11 ) NULL AUTO_INCREMENT";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$messages[]=$str;
				}
			}
		}
		$str="select id from tx_multishop_orphan_files limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_orphan_files` (
			`id` INT( 11 ) NULL AUTO_INCREMENT PRIMARY KEY ,
			`type` varchar( 50 ) NULL ,
			`path` TEXT NULL ,
			`file` varchar(255) NULL ,
			`orphan` tinyint(1) NULL DEFAULT '0',
			`checked` tinyint(1) NULL DEFAULT '0',
			`crdate` INT( 11 ) NULL  DEFAULT '0',
			INDEX ( `type` ),
			INDEX ( `crdate`),
			INDEX ( `file`),
			INDEX ( `orphan`),
			INDEX ( `checked`),
			FULLTEXT (`path`)
			) ENGINE = MYISAM ;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select categories_url from tx_multishop_categories limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_categories` ADD  `categories_url` varchar( 254 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select content_footer from tx_multishop_categories_description limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_categories_description` ADD  `content_footer` TEXT NULL AFTER  `content`";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select invoice_id from tx_multishop_invoices limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_invoices` (
			`id` INT( 11 ) NULL AUTO_INCREMENT PRIMARY KEY ,
			`invoice_id` varchar(255) NULL ,
			`invoice_inc` varchar(11) NULL ,
			`orders_id` INT( 11 ) NULL DEFAULT '0' ,
			`date` INT( 11 ) NULL DEFAULT '0' ,
			`status` INT( 11 ) NULL  DEFAULT '0'
			) ENGINE = MYISAM ;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select id from tx_multishop_specials_sections limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE  `tx_multishop_specials_sections` (
		`id` INT( 11 ) NULL AUTO_INCREMENT PRIMARY KEY ,
		`specials_id` INT( 11 ) NULL DEFAULT '0' ,
		`date` int(11) NULL DEFAULT '0',
		`name` varchar( 30 ) NULL
		)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_specials_sections` ADD INDEX (  `date` ), ADD INDEX (  `specials_id` ), ADD INDEX (  `name` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$str="select status from tx_multishop_specials_sections limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_specials_sections` ADD  `status` tinyint( 1 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_specials_sections` ADD INDEX (  `status` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
			$str="ALTER TABLE  `tx_multishop_specials` CHANGE  `specials_id`  `specials_id` INT( 11 ) NULL AUTO_INCREMENT";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='FLAT_DATABASE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Flat Database', '".$key."', '0', 'This module creates flat products and categories database tables for maximum speed. An essential module for webshops that contain more than 250.000 products.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Flat Database extra attribute option columns', '".$key."', '', 'If you use the flat database module and you need the (single) attribute values inside a seperate table field in the flat table then define the attribute option id\'s here (example: 1:varchar(10);4:int(10);5:varchar(10);10:varchar(10)).', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='AFFILIATE_SHOP';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="delete from `tx_multishop_configuration` where configuration_key='".$key."';";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="delete from `tx_multishop_configuration` where configuration_title='iDEAL Test Stage';";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="replace INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(3, 'Misc', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Affiliate Shop', '".$key."', '0', 'This module enables the webshop as affiliate shop (hiding the order/basket system).', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CACHE_FRONT_END';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Cache front-end', '".$key."', '0', 'This module enables optimal caching features to the front-end. It will boost performance to big stores.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCTS_SHORT_DESCRIPTION_CONTAINS_HTML_MARKUP';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products short description contains HTML markup', '".$key."', '0', 'Defines whether we should save the content as plain text or by HTML markup.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CACHE_TIME_OUT_LISTING_PAGES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Cache time out for categories/products listing page', '".$key."', '3600', 'Specify the expiry time of the categories and products listing page cache files. Default: 3600. To turn this caching feature off specify: 0.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CACHE_TIME_OUT_SEARCH_PAGES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Cache time out for search page', '".$key."', '3600', 'Specify the expiry time of the search page cache files. Default: 3600. To turn this caching feature off specify: 0.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CACHE_TIME_OUT_PRODUCTS_DETAIL_PAGES';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Cache time out for products detail page', '".$key."', '3600', 'Specify the expiry time of the products detail page cache files. Default: 3600. To turn this caching feature off specify: 0.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Cache time out for the categories navigation menu', '".$key."', '3600', 'Specify the expiry time of the categories navigation menu cache files. Default: 3600. To turn this caching feature off specify: 0.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='COUNTRY_ISO_NR';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Webshop Country ISO Number', '".$key."', '0', 'The country where the webstore is located. Used to determine the local VAT classes.', 3, NULL, NULL, now(), NULL, 'tep_country_select_option(');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='SHOW_INNER_FOOTER_NAV';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Show the footer navigation in the middle content', '".$key."', '0', 'This module enables the inner footer navigation.', 1, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='STORE_NAME';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Store Name', '".$key."', '', 'The name of the store.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='STORE_EMAIL';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Store E-mail', '".$key."', '', 'The e-mail address of the store owner.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// update fixed
		$str="select `prefix_source_name` from tx_multishop_import_jobs limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_import_jobs` ADD `prefix_source_name` varchar( 50 ) NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_import_jobs` ADD INDEX ( `prefix_source_name` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `products_model` from tx_multishop_orders_products limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD  `products_model` varchar( 64 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `deleted` from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_orders` ADD  `deleted` tinyint( 1 ) DEFAULT 0";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `key` from tx_multishop_configuration ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_configuration` CHANGE `key` `configuration_key` varchar( 64 ) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `depend_on_configuration_key` from tx_multishop_configuration limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tx_multishop_configuration` ADD  `depend_on_configuration_key` varchar( 64 ) NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `code` from tx_multishop_import_jobs limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_import_jobs` ADD `code` varchar( 16 ) NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_import_jobs` ADD INDEX ( `code` ) ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="SELECT * from tx_multishop_import_jobs order by id desc";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				// fix the registered jobs that hasnt got any md5 code
				$updateArray=array();
				$updateArray['code']=md5(uniqid());
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=\''.$row['id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
		$str="select `value` from tx_multishop_configuration ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_configuration` CHANGE `value` `configuration_value` varchar( 64 ) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `title` from tx_multishop_configuration ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_configuration` CHANGE `title` `configuration_title` varchar( 64 ) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `title` from tx_multishop_configuration_group ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_configuration_group` CHANGE `title` `configuration_title` varchar( 64 ) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select products_tax_class_id from tx_multishop_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_products` CHANGE `products_tax_class_id` `tax_id` INT( 5 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select page_id from tx_multishop_cms";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_cms` CHANGE `page_id` `page_uid` INT(11) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select date_added from tx_multishop_cms";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_cms` CHANGE `date_added` `crdate` INT(11) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select date_added from tx_multishop_orders_status_history";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_orders_status_history` CHANGE `date_added` `crdate` INT(11) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select paid from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `paid` tinyint( 1 ) NULL DEFAULT '0' ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_orders` ADD INDEX ( `paid` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select by_phone from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `by_phone` tinyint( 1 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_orders` ADD INDEX ( `by_phone` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select option_attributes from tx_multishop_categories limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_categories` ADD `option_attributes` varchar( 254 ) NULL  DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_shipping_countries limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE `tx_multishop_shipping_countries` (
		  `id` int(11) NULL auto_increment,
		  `page_uid` int(11) NULL DEFAULT '0',
		  `cn_iso_nr` int(11) NULL DEFAULT '0',
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `cn_iso_nr` (`cn_iso_nr`,`page_uid`)
		) ENGINE=MyISAM  ;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select id from tx_multishop_coupons limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="CREATE TABLE IF NOT EXISTS `tx_multishop_coupons` (
		`id` int(11) NULL AUTO_INCREMENT,
		`code` varchar(250) DEFAULT '',
		`discount` decimal(5,2) NULL DEFAULT '0.00',
		`status` tinyint(1) NULL DEFAULT '0',
		`startdate` date NULL,
		`enddate` date NULL,
		`times_used` int(5) NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		UNIQUE KEY `code` (`code`)
		) ENGINE=MyISAM ;
		";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select endate from tx_multishop_coupons";
		if (($qry=$GLOBALS['TYPO3_DB']->sql_query($str))!=false) {
			$str="ALTER TABLE `tx_multishop_coupons` CHANGE `endate` `enddate` DATE NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_coupons` CHANGE `status` `status` tinyint( 1 ) NULL DEFAULT '0'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_coupons` ADD `times_used` INT( 5 ) NULL DEFAULT '0' ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE `tx_multishop_coupons` ADD UNIQUE (`code` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='2'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			if ($row['configuration_title']=='Product Listing') {
				$str="UPDATE `tx_multishop_configuration_group` SET `configuration_title` = 'Image Settings' WHERE `id` =2;";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			} elseif ($row['configuration_title']=='Image Size Settings') {
				$str="UPDATE `tx_multishop_configuration_group` SET `configuration_title` = 'Image Settings' WHERE `id` =2;";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
		}
		$str="select * from tx_multishop_configuration_group where id='3'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			if ($row['configuration_title']=='Misc') {
				$str="UPDATE `tx_multishop_configuration_group` SET `configuration_title` = 'Webshop Settings' WHERE `id` =3;";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
		}
		$str="select * from tx_multishop_configuration where configuration_title='Store Name' and group_id='1'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			$str="UPDATE `tx_multishop_configuration` SET `group_id` =3 WHERE configuration_title='Store Name';";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// 12 july 2010
		$str="select `billing_phone` from tx_multishop_orders ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_orders` CHANGE `billing_phone` `billing_telephone` varchar( 150 ) NULL DEFAULT NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `delivery_phone` from tx_multishop_orders ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_orders` CHANGE `delivery_phone` `delivery_telephone` varchar( 150 ) NULL DEFAULT NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `billing_gender` from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `billing_gender` CHAR( 1 ) NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `billing_birthday` from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `billing_birthday` int( 11 ) NULL DEFAULT '0' ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `billing_email` from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `billing_email` varchar( 254 ) NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `delivery_gender` from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `delivery_gender` CHAR( 1 ) NULL ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select `delivery_email` from tx_multishop_orders limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_orders` ADD `delivery_email` varchar( 254 ) NULL 	";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Show specials box till categories sublevel', '".$key."', '2', 'This value defines till which categories sublevel the specials scroller should be shown. Choose 0 to show the specials on each categories level.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='SHOW_PRICES_INCLUDING_VAT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Show prices including VAT', '".$key."', '1', 'This setting defines whether we should show product prices including VAT or without.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='MANUAL_ORDER';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Enable the manual order', '".$key."', '0', 'This module enables the manual order.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='INVOICE_PRINT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'This module enable to print invoice order', '".$key."', '0', 'This module enable to print invoice order.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PACKING_LIST_PRINT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'This module enable to print order packing list', '".$key."', '0', 'This module enable to print order packing list.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='INVOICE_PREFIX';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Invoice prefix', '".$key."', '0', 'Invoice prefix.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// 12 july eof
		//13 oct 2010 for fe_user
		$str="select address_number from fe_users limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `fe_users` ADD `address_number` varchar( 150 ) NULL DEFAULT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select mobile from fe_user limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `fe_users` ADD `mobile` varchar( 150 ) NULL DEFAULT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select attribute_image from tx_multishop_products_attributes limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE `tx_multishop_products_attributes` ADD `attribute_image` varchar( 150 ) NULL DEFAULT NULL";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$width='normal';
		$value="176x140";
		$key='CATEGORY_IMAGE_SIZE_'.t3lib_div::strtoupper($width);
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Category image size', '".$key."', '".$value."', 'The maximum image size in pixels for the category image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$width=50;
		$value="50x50";
		$key='PRODUCT_IMAGE_SIZE_'.t3lib_div::strtoupper($width);
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product image size (".$width.")', '".$key."', '".$value."', 'The maximum ".$type." in pixels for the ".$width." image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$width=100;
		$value="175x123";
		$key='PRODUCT_IMAGE_SIZE_'.t3lib_div::strtoupper($width);
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product image size (".$width.")', '".$key."', '".$value."', 'The maximum ".$type." in pixels for the ".$width." image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$width=200;
		$value="140x150";
		$key='PRODUCT_IMAGE_SIZE_'.t3lib_div::strtoupper($width);
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product image size (".$width.")', '".$key."', '".$value."', 'The maximum ".$type." in pixels for the ".$width." image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$width=300;
		$value="350x300";
		$key='PRODUCT_IMAGE_SIZE_'.t3lib_div::strtoupper($width);
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product image size (".$width.")', '".$key."', '".$value."', 'The maximum ".$type." in pixels for the ".$width." image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$width='enlarged';
		$value="600x400";
		$key='PRODUCT_IMAGE_SIZE_'.t3lib_div::strtoupper($width);
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product image size (".$width.")', '".$key."', '".$value."', 'The maximum ".$type." in pixels for the ".$width." image folder.', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// meta
		$key='META_TITLE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Meta Title', '".$key."', 'Webshop Title', 'The title of the webshop, used for the Meta Tags.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='META_DESCRIPTION';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Meta Description', '".$key."', 'Description of the webshop.', 'The description of the webshop, used for the Meta Tags.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='META_KEYWORDS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Meta Keywords', '".$key."', 'keyword1, keyword2', 'The search words that are related to the webshop, used for the Meta Tags.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='4'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(4, 'Webshop Plugins', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='8'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(8, 'Checkout Settings', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$key='CHECKOUT_LENGTH_TELEPHONE_NUMBER';
			if (!isset($settings['GLOBAL_MODULES'][$key])) {
				$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Length telephone number', '".$key."', '10', 'The total chars of a valid telephone number.', 8, NULL, NULL, now(), NULL, '');";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
			$key='CHECKOUT_VALIDATE_FORM';
			if (!isset($settings['GLOBAL_MODULES'][$key])) {
				$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Validate Checkout Form', '".$key."', '1', 'Validate Checkout Form by JavaScript.', 8, NULL, NULL, now(), NULL, '');";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
			$key='CHECKOUT_REQUIRED_TELEPHONE';
			$title='Telephone Required';
			$description='Validate Checkout Form by JavaScript.';
			$default_value=1;
			if (!isset($settings['GLOBAL_MODULES'][$key])) {
				$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', '".$title."', '".$key."', '".$default_value."', '".$description."', 8, NULL, NULL, now(), NULL, '');";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
		}
		$str="select * from tx_multishop_configuration_group where id='9'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(9, 'Orders Settings', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='ORDERS_CUSTOM_EXPORT_SCRIPT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Orders Custom Export Script', '".$key."', '', 'Optionally you can process the newly created orders by your custom script.', 9, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='ORDERS_PAID_CUSTOM_SCRIPT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Orders Paid Custom Script', '".$key."', '', 'Optionally you can process the paid order by your custom script.', 9, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='10'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(10, 'Products Stock Settings', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='SHOW_STOCK_LEVEL_AS_BOOLEAN';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Show stock level as boolean (yes/no)', '".$key."', '0', 'Instead of showing the total count of the products stock level, show stock as: yes or no.', 10, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''yes_without_image'',''yes_with_image'', ''no''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		$key='GOOGLE_ANALYTICS_ACCOUNT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Google Analytics Account', '".$key."', 'UA-16775241-1', 'Google Analytics is the enterprise-class web analytics solution that gives you rich insights into your website traffic and marketing effectiveness.', 4, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='ADDTHIS_ACCOUNT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Addthis Account', '".$key."', 'typo3multishop', 'Add a Addthis button to your webshop. Addthis.com makes sharing your website urls easy.', 4, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='GOOGLE_ADWORDS_CONVERSION_CODE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Google Adwords Conversion Code', '".$key."', '', 'Add a Google Adwords Conversion Code to the thank you page of the webshop. This makes it possible to calculate the success-ratio of your Google Adwords campaign.', 4, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_WATERMARK_TEXT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Watermark Text', '".$key."', '', 'Add a watermark to enlarged product images. Example value: typo3multishop.com', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_WATERMARK_WIDTH';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Watermark Width', '".$key."', '100', 'The width of the text. Example value: 100', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_WATERMARK_HEIGHT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Watermark Height', '".$key."', '60', 'The height of the text. Example value: 60', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_WATERMARK_FONT_SIZE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Watermark Font-size', '".$key."', '16', 'Specify the font-size (in pt) of the watermark. Example value: 16', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_WATERMARK_FONT_FILE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Watermark Font File', '".$key."', 'nasaliza.ttf', 'Specify the font file for the watermarking. Example value: nasaliza.ttf', 2, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_WATERMARK_POSITION';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Watermark Position', '".$key."', 'south-east', 'Choose the position of the watermark.', 2, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''north-east'', ''south-east'',''south-west'',''north-west''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCT_IMAGE_SHAPED_CORNERS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Image Shaped Corners', '".$key."', '0', 'Adds shaped corners to every thumbnailed product image.', 2, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CATEGORY_IMAGE_SHAPED_CORNERS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Category Image Shaped Corners', '".$key."', '0', 'Adds shaped corners to every category image.', 2, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// ok now we going to convert the table schemes
		// products description
		$from='products_title_seo';
		$to='products_meta_title';
		$str="select ".$from." from tx_multishop_products_description";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_products_description` CHANGE `".$from."` `".$to."` varchar(254) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$from='meta_description';
		$to='products_meta_description';
		$str="select ".$from." from tx_multishop_products_description";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_products_description` CHANGE `".$from."` `".$to."` varchar(254) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$from='products_keywords';
		$to='products_meta_keywords';
		$str="select ".$from." from tx_multishop_products_description";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($qry) {
			$str="ALTER TABLE `tx_multishop_products_description` CHANGE `".$from."` `".$to."` varchar(254) NULL DEFAULT ''";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCTS_LISTING_LIMIT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products Listing Limiting', '".$key."', '18', 'The total number of displayed products per page.', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CHECKOUT_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Checkout Type', '".$key."', 'multistep', 'Choose which checkout process should be used (ie: multistep).', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='SHOW_PRICES_WITH_AND_WITHOUT_VAT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Show product prices with and without VAT', '".$key."', '0', 'If products contain VAT the price including and excluding VAT will be shown.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='5'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(5, 'Categories Listing', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='6'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(6, 'Products Listing', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='CATEGORIES_LISTING_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Categories Listing Type', '".$key."', 'grid', 'The lay-out type for displaying the categories listing.', 5, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCTS_LISTING_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Products Listing Type', '".$key."', 'grid', 'The lay-out type for displaying the products listing.', 6, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="select * from tx_multishop_configuration_group where id='7'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="INSERT INTO `tx_multishop_configuration_group` (`id`, `configuration_title`, `description`, `sort_order`, `visible`) VALUES(7, 'Products Detail Page', '', NULL, 1);";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCTS_DETAIL_PAGE_PAGINATION';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Product Pagination', '".$key."', '0', 'Show\'s a button Previous and Next on the products detail page to go to the next or previous product within the same category.', 7, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='PRODUCTS_DETAIL_TYPE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('Products Detail Type', 'PRODUCTS_DETAIL_TYPE', 'default', 'The lay-out type for displaying the products detail page.', 7, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='STAFFEL_PRICE_MODULE';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Staffel Prices', '".$key."', '0', 'Enable staffel prices to your webshop.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='ULTRASEARCH_FIELDS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Ultrasearch Fields', '".$key."', '', 'Define which fields you\'d like to show in the ultrasearch form. (example: 1:list;2:radio;3:checkbox;price_filter:0-1000)', 3, NULL, NULL, now(), NULL, '');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='COUPONS';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Coupon Code', '".$key."', '0', 'Activate coupon code.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$key='ORDER_EDIT';
		if (!isset($settings['GLOBAL_MODULES'][$key])) {
			$str="INSERT INTO `tx_multishop_configuration` (`id`, `configuration_title`, `configuration_key`, `configuration_value`, `description`, `group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`) VALUES('', 'Admin Order Edit', '".$key."', '0', 'Enables you to edit orders.', 3, NULL, NULL, now(), NULL, 'tep_cfg_select_option(array(''1'', ''0''),');";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$sql="describe tx_multishop_countries_to_zones";
		$qry=$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($rs['Field']=='cn_iso_nr') {
				if ($rs['Type']=='int(4)') {
					$str="ALTER TABLE `tx_multishop_countries_to_zones` CHANGE `cn_iso_nr` `cn_iso_nr` INT( 11 ) NULL DEFAULT NULL";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
				}
			}
		}
		$sql="describe tx_multishop_products_description";
		$qry=$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$colname='products_shortdescription';
			if ($rs['Field']==$colname) {
				if ($rs['Type']=='text') {
					$str="ALTER TABLE `tx_multishop_products_description` CHANGE `".$colname."` `".$colname."` text NULL DEFAULT ''";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
				}
			}
			$colname='products_negative_keywords';
			if ($rs['Field']==$colname) {
				if ($rs['Type']=='text') {
					$str="ALTER TABLE `tx_multishop_products_description` CHANGE `".$colname."` `".$colname."` varchar(255) NULL DEFAULT ''";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
				}
			}
			$colname='promotext';
			if ($rs['Field']==$colname) {
				if ($rs['Type']=='text') {
					$str="ALTER TABLE `tx_multishop_products_description` CHANGE `".$colname."` `".$colname."` varchar(255) NULL DEFAULT ''";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
				}
			}
		}
		$sql="describe tx_multishop_orders";
		$qry=$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($rs['Field']=='crdate') {
				if ($rs['Type']=='date') {
					$str="ALTER TABLE `tx_multishop_orders` CHANGE `crdate` `crdate` INT( 11 ) NULL DEFAULT '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
				}
			}
		}
		$str="SELECT * from tx_multishop_import_jobs limit 1";
		if (!$qry=$GLOBALS['TYPO3_DB']->sql_query($str)) {
			$str="CREATE TABLE `tx_multishop_import_jobs` (
			`id` INT( 11 ) NULL AUTO_INCREMENT PRIMARY KEY ,
			`name` varchar( 254 ) NULL ,
			`period` INT( 11 ) NULL DEFAULT '0' ,
			`last_run` INT( 11 ) NULL DEFAULT '0' ,
			`data` TEXT NULL ,
			`status` tinyint( 1 ) NULL DEFAULT '0' ,
			`page_uid` INT( 11 ) NULL DEFAULT '0',
			`categories_id` INT( 5 ) NULL DEFAULT '0',
			INDEX ( `last_run` , `status` , `page_uid`,`categories_id` ) 
			) ;";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$str="describe tx_multishop_products";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($rs['Field']=='products_id') {
				if ($rs['Type']=='int(5)') {
					$str="ALTER TABLE  `tx_multishop_products` CHANGE  `products_id`  `products_id` INT( 11 ) NULL AUTO_INCREMENT";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_description` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_attributes` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `products_attributes_id` CHANGE  `products_attributes_id`  `products_attributes_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_attributes_extra` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_options` CHANGE  `products_options_id`  `products_options_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_options_values` CHANGE  `products_options_values_id`  `products_options_values_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_options_values_extra` CHANGE  `products_options_values_extra_id`  `products_options_values_extra_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_options_values_to_products_options` CHANGE  `products_options_values_to_products_options_id`  `products_options_values_to_products_options_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_to_categories` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_to_relative_products` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_products_to_relative_products` CHANGE  `relative_product_id`  `relative_product_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_reviews` CHANGE  `reviews_id`  `reviews_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_reviews` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_reviews_description` CHANGE  `reviews_id`  `reviews_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_specials` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_specials` CHANGE  `specials_id`  `specials_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$str="ALTER TABLE  `tx_multishop_undo_products` CHANGE  `products_id`  `products_id` INT( 11 ) NULL DEFAULT  '0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
				}
			}
		}
		$indexes=array();
		$table_name='tx_multishop_products';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		for ($i=0; $i<5; $i++) {
			$index=$i;
			if ($index==0) {
				$index='';
			}
			if (!in_array('products_image'.$index, $indexes)) {
				$str="ALTER TABLE `".$table_name."` ADD INDEX `products_image".$index."` (`products_image".$index."`)";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
		}
		// add combined indexes for maxium speed
		$indexes=array();
		$table_name='tx_multishop_products_options';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('products_options_name', $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `products_options_name` (  `language_id`,`products_options_name` )	";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// add full text index to products_model for maxium speed
		$indexes=array();
		$table_name='tx_multishop_products_description';
		$str="show indexes from `".$table_name."` where Index_type='FULLTEXT' and Key_name ='products_model_2'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$str="ALTER TABLE  `".$table_name."` ADD FULLTEXT `products_model_2` (`products_model` )	";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_products_options_values';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('products_options_values_name', $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `products_options_values_name` (  `products_options_values_name` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// delete double keys
		$indexes=array();
		$table_name='tx_multishop_coupons';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (in_array('code_10', $indexes)) {
			for ($i=2; $i<65; $i++) {
				$str="ALTER TABLE  `tx_multishop_coupons` DROP INDEX  `code_".$i."`";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			}
			$str="ALTER TABLE  `tx_multishop_coupons` ADD INDEX (  `status` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_coupons` ADD INDEX (  `startdate` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_coupons` ADD INDEX (  `enddate` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_coupons` ADD INDEX (  `times_used` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_invoices` ADD INDEX (  `orders_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_invoices` ADD INDEX (  `status` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_invoices` ADD INDEX (  `invoice_id` ) ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_invoices` ADD INDEX (  `date` ) ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_orders_products` ADD INDEX (  `products_name` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_orders_products_attributes` ADD INDEX (  `orders_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_orders_products_attributes` ADD INDEX (  `orders_products_id` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$str="ALTER TABLE  `tx_multishop_orders_status_history` ADD INDEX (  `crdate` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// delete double keys eof
		if (!in_array('combined_one', $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `combined_one` (`language_id`,`products_options_values_name` )";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_categories';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('combined_one', $indexes)) {
			$str="ALTER TABLE  `".$table_name."` ADD INDEX `combined_one` (`page_uid`,`status`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_two', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_categories` ADD INDEX  `combined_two` (`page_uid`,`status`,`categories_id`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_three', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_categories` ADD INDEX  `combined_three` (`page_uid`,`status`,`parent_id`)	";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_categories_description';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('combined_one', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_categories_description` ADD INDEX  `combined_one` (  `language_id`,`categories_name`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_two', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_categories_description` ADD INDEX  `combined_two` (  `language_id`,`categories_id`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_three', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_categories_description` ADD INDEX  `combined_three` (  `language_id`,`categories_id`,`categories_name`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_products';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('combined_one', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_one` (`page_uid`,`products_status`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_two', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_two` (`page_uid`,`products_status`,`products_id`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_three', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_three` (`page_uid`,`products_status`,`sort_order`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_four', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_four` (`page_uid`,`products_status`,`products_model`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_five', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_five` (`page_uid`,`products_status`,`products_date_added`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_six', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_six` (`page_uid`,`products_status`,`products_last_modified`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_seven', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_seven` (`page_uid`,`products_status`,`products_date_available`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_eight', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_eight` (`page_uid`,`products_status`,`extid`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_nine', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products` ADD INDEX  `combined_nine` (`page_uid`,`products_status`,`products_price`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_products_description';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('combined_one', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD INDEX  `combined_one` (  `language_id`,`products_name`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_two', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD INDEX  `combined_two` (  `language_id`,`products_id`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_three', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD INDEX  `combined_three` (  `language_id`,`products_id`,`products_name`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_seven', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD INDEX  `combined_seven` (  `language_id`,`products_meta_keywords`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_eight', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_products_description` ADD INDEX  `combined_eight` (  `language_id`,`products_name`,`products_description`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_specials';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('combined_one', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_specials` ADD INDEX  `combined_one` (`page_uid`,`status`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_two', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_specials` ADD INDEX  `combined_two` (`page_uid`,`status`,`specials_new_products_price`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_three', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_specials` ADD INDEX  `combined_three` (`page_uid`,`status`,`expires_date`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		if (!in_array('combined_four', $indexes)) {
			$str="ALTER TABLE  `tx_multishop_specials` ADD INDEX  `combined_four` (`page_uid`,`status`,`expires_date`,`specials_new_products_price`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$indexes=array();
		$table_name='tx_multishop_products_description';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		if (!in_array('combined_four', $indexes)) {
			$str="ALTER TABLE `tx_multishop_products_description` ADD FULLTEXT  `combined_four` (`products_name` ,`products_description`)";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		// end combined indexes
		// delete duplicates
		$str="ALTER IGNORE TABLE tx_multishop_configuration ADD UNIQUE INDEX dupidx (configuration_key);";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$str="ALTER TABLE tx_multishop_configuration DROP INDEX dupidx;";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		// fixes for v3
		$keys=array();
		$keys[]='CATEGORIES_LISTING_TYPE';
		$keys[]='PRODUCTS_LISTING_TYPE';
		foreach ($keys as $key) {
			$str="UPDATE `tx_multishop_configuration` SET configuration_value='default' where configuration_key='".$key."' and configuration_value='grid'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			//	$messages[]=$str;
			$str="UPDATE `tx_multishop_configuration_values` SET configuration_value='default' where configuration_key='".$key."' and configuration_value='grid'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			//	$messages[]=$str;
		}
		$str="select page_uid from tt_address limit 1";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry) {
			$str="ALTER TABLE  `tt_address` ADD `page_uid` int(11) NULL default '0', ADD KEY page_uid (page_uid) ";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		// first fix the already working v3 shops
		$str="select uid from tt_address where tx_multishop_address_type='billing' and tx_multishop_customer_id=0 and page_uid='".$this->showCatalogFromPage."' and pid='".$this->conf['fe_customer_pid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)==1) {
			$str="UPDATE `tt_address` SET tx_multishop_address_type='store' where tx_multishop_address_type='billing' and tx_multishop_customer_id=0";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$messages[]=$str;
		}
		if (!$this->conf['tt_address_record_id_store']) {
			$str="select uid from tt_address where tx_multishop_address_type='store' and tx_multishop_customer_id=0 and page_uid='".$this->showCatalogFromPage."' and pid='".$this->conf['fe_customer_pid']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
				$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
				$array=array();
				$array['pid']=$this->conf['fe_customer_pid'];
				$array['name']='Store';
				$array['country']=$default_country['cn_short_en'];
				$array['tx_multishop_customer_id']=0;
				$array['tx_multishop_default']=0;
				$array['tx_multishop_address_type']='store';
				$array['page_uid']=$this->showCatalogFromPage;
				$array['tstamp']=time();
				$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $array);
				$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
				$messages[]=$str;
			}
		}
		// now fix the vat
		$str="select tax_id from tx_multishop_taxes";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
			$str="select tax_id from tx_multishop_products group by tax_id";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					if ($row['tax_id']) {
						$tax=mslib_fe::getTaxById($row['tax_id']);
						if ($tax>0) {
							$array=array();
							$array['name']=$tax.'%';
							$array['rate']=$tax;
							$array['status']=1;
							$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_taxes', $array);
							$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
							$tax_rule_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
							if ($tax_rule_id) {
								$array=array();
								$array['name']='Default '.($tax).'%';
								$array['status']=1;
								$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_tax_rule_groups ', $array);
								$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
								$tax_rules_group_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
								if ($tax_rules_group_id) {
									$array=array();
									$array['status']=1;
									$array['rules_group_id']=$tax_rules_group_id;
									$array['cn_iso_nr']=$default_country['cn_iso_nr'];
									$array['zn_country_iso_nr']=0;
									$array['tax_id']=$tax_rule_id;
									$array['state_modus']=0;
									$array['county_modus']=0;
									$array['country_tax_id']=0;
									$query2=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_tax_rules ', $array);
									$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
									$tax_rules_group_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									$str="UPDATE tx_multishop_products set tax_id='".$tax_rules_group_id."' where tax_id='".$row['tax_id']."'";
									$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								}
							}
						}
					}
				}
			}
		}
		// now fix the V2 orders to V3
		if ($this->get['force_rebuild_orders']) {
			$sql="select orders_id from tx_multishop_orders order by orders_id asc";
		} else {
			$sql="select orders_id from tx_multishop_orders where orders_tax_data is null or orders_tax_data ='' or grand_total=0 order by orders_id asc";
		}
		//$sql = "select orders_id from tx_multishop_orders where orders_id=754 order by orders_id asc";
		//$sql = "select orders_id from tx_multishop_orders order by orders_id asc";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_order.php');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$mslib_order=t3lib_div::makeInstance('tx_mslib_order');
				$mslib_order->init($this);
				$mslib_order->repairOrder($row['orders_id']);
			}
		}
		$key='INCLUDE_VAT_OVER_METHOD_COSTS';
		$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\''.$key.'\'');
		$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'configuration_key=\''.$key.'\'');
		$required_indexes=array();
		$required_indexes[]='products_date_added';
		$required_indexes[]='products_last_modified';
		$required_indexes[]='products_date_available';
		$indexes=array();
		$table_name='tx_multishop_products';
		$str="show indexes from `".$table_name."` ";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$indexes[]=$rs['Key_name'];
		}
		foreach ($required_indexes as $required_index) {
			if (!in_array($required_index, $indexes)) {
				$str="ALTER TABLE  `".$table_name."` ADD INDEX `".$required_index."` (`".$required_index."`)";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$messages[]=$str;
			}
		}
		// now fix the V2 orders to V3 eof
		// FIXING V1 IMAGES TO V3
		// V1 had images without subdirectory. lets verify if this site has this file structure, so we can fix it dynamically			
		$imageTypes=array();
		$imageTypes[]='categories';
		$imageTypes[]='products';
		$imageTypes[]='manufacturers';
		foreach ($imageTypes as $imageType) {
			switch ($imageType) {
				case 'products':
					$tableName='tx_multishop_products';
					$numberOfImages=5;
					break;
				case 'categories':
					$tableName='tx_multishop_categories';
					$numberOfImages=1;
					break;
				case 'manufacturers':
					$tableName='tx_multishop_manufacturers';
					$numberOfImages=1;
					break;
			}
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				$tableName, // FROM ...
				'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					for ($i=0; $i<$numberOfImages; $i++) {
						if ($i==0) {
							$s='';
						} else {
							$s=$i;
						}
						switch ($imageType) {
							case 'products':
								$colName='products_image'.$s;
								break;
							case 'categories':
								$colName='categories_image'.$s;
								break;
							case 'manufacturers':
								$colName='manufacturers_image'.$s;
								break;
						}
						if ($row[$colName]) {
							$folder=mslib_befe::getImagePrefixFolder($row[$colName]);
							$v1_folder=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/'.$imageType.'/original/'.$row[$colName];
							$v3_folder=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/'.$imageType.'/original/'.$folder.'/'.$row[$colName];
							if (!file_exists($v3_folder) and file_exists($v1_folder)) {
								// V1 setup
								$dirs=array();
								$dirs[]=$this->DOCUMENT_ROOT.$this->ms['image_paths'][$imageType]['100'].'/'.$folder;
								$dirs[]=$this->DOCUMENT_ROOT.$this->ms['image_paths'][$imageType]['200'].'/'.$folder;
								$dirs[]=$this->DOCUMENT_ROOT.$this->ms['image_paths'][$imageType]['300'].'/'.$folder;
								$dirs[]=$this->DOCUMENT_ROOT.$this->ms['image_paths'][$imageType]['50'].'/'.$folder;
								$dirs[]=$this->DOCUMENT_ROOT.$this->ms['image_paths'][$imageType]['normal'].'/'.$folder;
								$dirs[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/'.$imageType.'/original/'.$folder;
								foreach ($dirs as $dir) {
									if (!is_dir($dir)) {
										t3lib_div::mkdir($dir);
									}
								}
								if (copy($v1_folder, $v3_folder)) {
									$deleteOriginal=0;
									foreach ($this->ms['image_paths'][$imageType] as $thumbFolderKey=>$thumbFolder) {
										$v1_thumbFolder=$this->DOCUMENT_ROOT.$thumbFolder.'/'.$row[$colName];
										$v3_thumbFolder=$this->DOCUMENT_ROOT.$thumbFolder.'/'.$folder.'/'.$row[$colName];
										if (copy($v1_thumbFolder, $v3_thumbFolder)) {
											@unlink($v1_thumbFolder);
											$deleteOriginal=1;
										}
									}
									if ($deleteOriginal) {
										@unlink($this->DOCUMENT_ROOT.'uploads/tx_multishop/images/'.$imageType.'/original/'.$row[$colName]);
									}
								}
							}
						}
					}
				}
			}
		}
	}
	// MOVE SEVERAL SETTINGS TO DIFFERENT GROUPS
	$keys=array();
	$keys[]=array('key' => 'SEARCH_ALSO_IN_ATTRIBUTE_OPTION_IDS', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_CATEGORIES_NAME', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_EAN_CODE', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_MANUFACTURERS_NAME', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_PRODUCTS_ID', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_PRODUCTS_MODEL', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_PRODUCTS_NEGATIVE_KEYWORDS', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_SKU_CODE', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'SEARCH_ALSO_IN_VENDOR_CODE', 'oldGroupId'=>'3', 'newGroupId'=>'13');
	$keys[]=array('key' => 'ENABLE_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH', 'oldGroupId'=>'11', 'newGroupId'=>'13');
	$keys[]=array('key' => 'FULLTEXT_SEARCH_MIN_CHARS', 'oldGroupId'=>'11', 'newGroupId'=>'13');
	$keys[]=array('key' => 'PRODUCTS_SEARCH_FALLBACK_SEARCH', 'oldGroupId'=>'11', 'newGroupId'=>'13');
	$keys[]=array('key' => 'REGULAR_SEARCH_MODE', 'oldGroupId'=>'11', 'newGroupId'=>'11');
	$keys[]=array('key' => 'CATEGORIES_TYPE', 'oldGroupId'=>'11', 'newGroupId'=>'5');
	$keys[]=array('key' => 'DEFAULT_CURRENCY', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'DISABLE_CHECKOUT_FOR_GUESTS', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'DISABLE_CRUMBAR', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'DISPLAY_SPECIALS_ABOVE_PRODUCTS_LISTING', 'oldGroupId'=>'11', 'newGroupId'=>'6');
	$keys[]=array('key' => 'DOWNLOAD_INVOICE_TYPE', 'oldGroupId'=>'11', 'newGroupId'=>'12');
	$keys[]=array('key' => 'ENABLED_CURRENCIES', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'ENABLED_LANGUAGES', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'REDIRECT_BACK_TO_PRODUCTS_DETAIL_PAGE_AFTER_ADD_TO_CART', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'CART_PAGE_UID', 'oldGroupId'=>'11', 'newGroupId'=>'3');
	$keys[]=array('key' => 'LOCK_ORDER_AFTER_CREATING_INVOICE', 'oldGroupId'=>'11', 'newGroupId'=>'12');
	$keys[]=array('key' => 'PRODUCTS_DETAIL_NUMBER_OF_TABS', 'oldGroupId'=>'11', 'newGroupId'=>'7');
	$keys[]=array('key' => 'PRODUCTS_LISTING_SORT_ORDER_OPTION', 'oldGroupId'=>'11', 'newGroupId'=>'6');
	$keys[]=array('key' => 'PRODUCTS_LISTING_SPECIALS', 'oldGroupId'=>'1', 'newGroupId'=>'6');
	$keys[]=array('key' => 'SHOW_INNER_FOOTER_NAV', 'oldGroupId'=>'1', 'newGroupId'=>'3');
	$keys[]=array('key' => 'USE_FLAT_DATABASE_ALSO_IN_ADMIN_PRODUCTS_SEARCH_AND_EDIT', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'PRODUCTS_NEW_NUMBER_OF_DAYS', 'oldGroupId'=>'11', 'newGroupId'=>'13');

	$keys[]=array('key' => 'MANUAL_ORDER', 'oldGroupId'=>'3', 'newGroupId'=>'9');
	$keys[]=array('key' => 'INVOICE_PRINT', 'oldGroupId'=>'3', 'newGroupId'=>'12');
	$keys[]=array('key' => 'INVOICE_PREFIX', 'oldGroupId'=>'3', 'newGroupId'=>'12');
	$keys[]=array('key' => 'ORDER_EDIT', 'oldGroupId'=>'3', 'newGroupId'=>'12');
	$keys[]=array('key' => 'PACKING_LIST_PRINT', 'oldGroupId'=>'3', 'newGroupId'=>'12');
	$keys[]=array('key' => 'PRODUCTS_LISTING_LIMIT', 'oldGroupId'=>'3', 'newGroupId'=>'6');
	$keys[]=array('key' => 'CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL', 'oldGroupId'=>'3', 'newGroupId'=>'5');



	$keys[]=array('key' => 'CACHE_FRONT_END', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'CACHE_TIME_OUT_LISTING_PAGES', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'CACHE_TIME_OUT_PRODUCTS_DETAIL_PAGES', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'CACHE_TIME_OUT_SEARCH_PAGES', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'FLAT_DATABASE', 'oldGroupId'=>'3', 'newGroupId'=>'14');
	$keys[]=array('key' => 'FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS', 'oldGroupId'=>'3', 'newGroupId'=>'14');

	$keys[]=array('key' => 'DISPLAY_REALTIME_NOTIFICATION_MESSAGES', 'oldGroupId'=>'3', 'newGroupId'=>'11');


	foreach ($keys as $row) {
		$filter=array();
		$filter[]='group_id='.$row['oldGroupId'];
		if (mslib_befe::ifExists($row['key'],'tx_multishop_configuration','configuration_key',$filter)) {
			$updateArray=array();
			$updateArray['group_id']=$row['newGroupId'];
			$query2=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_configuration', 'configuration_key=\''.addslashes($row['key']).'\'', $updateArray);
			$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
			$messages[]=$query2;
		}
	}
	// DELETE OLD SETTINGS
	$keys=array();
	$keys[]='ACCORDION_MENU';
	$keys[]='ACCORDION_SETUP_MODULES';
	$keys[]='AFFILIATE_SHOP';
	foreach ($keys as $key) {
		$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration', 'configuration_key=\''.addslashes($key).'\'');
	}
	// CREATE / UPDATE MULTISHOP SETTINGS
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
	// CREATE / UPDATE MULTISHOP SETTINGS EOF	
}
?>