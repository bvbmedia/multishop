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
if (!t3lib_extMgm::isLoaded('static_info_tables', 0) or !t3lib_extMgm::isLoaded('static_info_tables_taxes', 0)) {
	echo '<div class="main-heading"><h2>Please install the following extension:</h2></div>';
	if (!t3lib_extMgm::isLoaded('static_info_tables', 0)) {
		echo 'static_info_tables<BR />';
	}
	if (!t3lib_extMgm::isLoaded('static_info_tables_taxes', 0)) {
		echo 'static_info_tables_taxes<BR />';
	}
	exit();
}
// explanation: end loading globals
include_once($this->DOCUMENT_ROOT_MS.'res/PHPMailer/class.phpmailer.php');
$paths=array();
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/log';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/micro_downloads';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/cmt_images';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/cmsimages';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/cmsfiles';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products/50';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products/100';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products/200';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products/300';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products/original';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/products/normal';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/categories';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/categories/normal';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/categories/original';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/manufacturers';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/manufacturers/normal';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/manufacturers/original';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/attribute_values';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/images/attribute_values/original';
$paths[]=$this->DOCUMENT_ROOT.'uploads/tx_multishop/order_resources';
foreach ($paths as $path) {
	if (!is_dir($path)) {
		t3lib_div::mkdir($path);
	}
}
?>