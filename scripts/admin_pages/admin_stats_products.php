<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
switch ($this->get['tx_multishop_pi1']['stats_section']) {
	case 'perYear':
		$content.='<div class="order_stats_mode_wrapper">
		<ul class="pagination horizontal_list">
			<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_products&tx_multishop_pi1[stats_section]=perMonth').'">'.htmlspecialchars($this->pi_getLL('per_month', 'Per month')).'</a></li>
			<li class="active"><span>'.htmlspecialchars($this->pi_getLL('per_year', 'Per year')).'</span></li>
		</ul>
		</div>';
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_stats_products/stats_per_year.php');
		break;
	case 'perMonth':
	default:
		$content.='<div class="order_stats_mode_wrapper">
		<ul class="pagination horizontal_list">
			<li class="active"><span>'.htmlspecialchars($this->pi_getLL('per_month', 'Per month')).'</span></li>
			<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_stats_products&tx_multishop_pi1[stats_section]=perYear').'">'.htmlspecialchars($this->pi_getLL('per_year', 'Per year')).'</a></li>
		</ul>
		</div>';
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_stats_products/stats_per_months.php');
		break;
}
?>