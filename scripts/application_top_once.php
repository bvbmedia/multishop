<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
/*
	Webdevelopment by BVB Media BV. (C)2010
	Developer: Bas van Beek, mail@bvbmedia.com
*/
// explanation: loading globals
// checking if the required extensions are loaded
if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables', 0)) {
    echo '<div class="main-heading"><h2>Please install the following extension:</h2></div>';
    if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables', 0)) {
        echo 'static_info_tables<BR />';
    }
    exit();
}
// explanation: end loading globals
$paths = array();
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/log';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/micro_downloads';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/cache';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/cmt_images';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/cmsimages';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/cmsfiles';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products/50';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products/100';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products/200';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products/300';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products/original';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/products/normal';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/categories';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/categories/normal';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/categories/original';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/manufacturers';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/manufacturers/normal';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/manufacturers/original';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/attribute_values';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/attribute_values/original';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/attribute_values/normal';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/images/attribute_values/small';
$paths[] = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/order_resources';
foreach ($paths as $path) {
    if (!is_dir($path)) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($path);
    }
}
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/application_top_once.php']['applicationTopOncePostProc'])) {
    $params = array();
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/application_top_once.php']['applicationTopOncePostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
