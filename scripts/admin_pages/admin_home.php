<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_dashboard.php');
$mslib_dashboard=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_dashboard');
$mslib_dashboard->init($this);
$mslib_dashboard->setSection('admin_home');
$mslib_dashboard->renderWidgets();
$content.=$mslib_dashboard->displayDashboard();
$content.='<p class="extra_padding_bottom"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></p>';
?>