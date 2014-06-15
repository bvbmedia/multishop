<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_dashboard.php');
$mslib_dashboard=t3lib_div::makeInstance('tx_mslib_dashboard');
$mslib_dashboard->init($this);
$mslib_dashboard->setSection('admin_home');
$mslib_dashboard->renderWidgets();
$content.=$mslib_dashboard->displayDashboard();
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
?>