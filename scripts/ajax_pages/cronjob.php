<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_time_limit(86400);
ignore_user_abort(true);
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/cronjob.php']['cronjobProc'])) {
	$params=array('action'=>$this->get['tx_multishop_pi1']['action']);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/cronjob.php']['cronjobProc'] as $funcRef) {
		 \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// hook oef	
?>