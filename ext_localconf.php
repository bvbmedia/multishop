<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_multishop_pi1.php', '_pi1', 'list_type', 0);
if (TYPO3_MODE == 'BE') {
	// Page module hook
	$TYPO3_CONF_VARS['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['multishop_pi1'][] = 'EXT:multishop/pi1/classes/class.tx_multishop_cms_layout.php:tx_multishop->getExtensionSummary';
}
// RealURL autoconfiguration
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['multishop'] = 'EXT:multishop/class.tx_multishop_realurl.php:tx_multishop_realurl->addConfig';
// Multishop cli
$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['multishop'] = array('EXT:multishop/cli/multishop.php','_CLI_multishop');
?>