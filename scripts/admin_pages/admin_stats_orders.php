<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
switch ($this->get['tx_multishop_pi1']['stats_section']) {
	case 'turnoverPerYear':
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_stats_orders/turn_over_per_year.php');
		break;
	case 'turnoverPerMonth':
	default:
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_stats_orders/turn_over_per_month.php');
		break;
}
?>