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