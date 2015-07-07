<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (strstr($this->ms['MODULES']['EDIT_ACCOUNT_TYPE'], "/")) {
	require($this->DOCUMENT_ROOT.$this->ms['MODULES']['EDIT_ACCOUNT_TYPE'].'.php');
} else {
	if ($this->ms['MODULES']['EDIT_ACCOUNT_TYPE']) {
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/edit_account/'.$this->ms['MODULES']['EDIT_ACCOUNT_TYPE'].'.php');
	} else {
		require_once('includes/edit_account/default.php');
	}
}
?>