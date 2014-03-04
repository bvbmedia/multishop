<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// Configuration group ids
// 1 Homepage
// 2 Image Settings
// 3 Webshop Settings
// 4 Webshop Plugins
// 5 Categories Listing
// 6 Products Listing
// 7 Products Detail Page
// 8 Checkout Settings
// 9 Orders Settings
// 10 Products Stock Settings
// 11 Admin Settings
// 12 Invoice Settings
$records=array();
$records[]=array(
		'id'=>'1',
		'configuration_title'=>'Homepage',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'2',
		'configuration_title'=>'Image Settings',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'3',
		'configuration_title'=>'Webshop Settings',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'4',
		'configuration_title'=>'Webshop Plugins',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'5',
		'configuration_title'=>'Categories Listing',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'6',
		'configuration_title'=>'Products Listing',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'7',
		'configuration_title'=>'Products Detail Page',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'8',
		'configuration_title'=>'Checkout Settings',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'9',
		'configuration_title'=>'Orders Settings',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'10',
		'configuration_title'=>'Products Stock Settings',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'11',
		'configuration_title'=>'Admin Settings',
		'description'=>'',
		'visible'=>'1'
		);
$records[]=array(
		'id'=>'12',
		'configuration_title'=>'Invoice Settings',
		'description'=>'',
		'visible'=>'1'
		);
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/configuration/tx_multishop_configuration_group.php']['addConfigurationGroupRecordsPreHook'])) {
	$params = array (
		'records' => &$records
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/configuration/tx_multishop_configuration_group.php']['addConfigurationGroupRecordsPreHook'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof	
?>