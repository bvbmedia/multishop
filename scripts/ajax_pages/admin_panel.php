<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
header("Content-Type:application/json; charset=UTF-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D,d M YH:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
if($this->ADMIN_USER) {
	$this->get['categories_id']=$this->get['tx_multishop_pi1']['categories_id'];
	$this->get['products_id']=$this->get['tx_multishop_pi1']['products_id'];
	$data=mslib_fe::jQueryAdminMenu();
	echo json_encode($data, ENT_NOQUOTES);
	exit();
}
?>