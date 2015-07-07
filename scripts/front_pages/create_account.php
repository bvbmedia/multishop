<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_user.php');
if (strstr($this->ms['MODULES']['CREATE_ACCOUNT_TYPE'], "/")) {
	require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CREATE_ACCOUNT_TYPE'].'.php');
} else {
	if ($this->ms['MODULES']['CREATE_ACCOUNT_TYPE']) {
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/create_account/'.$this->ms['MODULES']['CREATE_ACCOUNT_TYPE'].'.php');
	} else {
		require_once('includes/create_account/default.php');
	}
}
?>