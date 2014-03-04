<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

switch ($this->get['tx_multishop_pi1']['stats_section']) {
	default:
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_stats_products/stats_per_months.php');
	break;
}
?>