<?php 
// deprecated, missing too much functionality
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_eidtools.php'); 
tslib_eidtools::connectDB();
$GLOBALS['TSFE']->fe_user = tslib_eidtools::initFeUser();
$this->ms['eID']=1;
include_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.mslib_fe.php');
include_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.mslib_befe.php');
//require_once(t3lib_extMgm::extPath('pagepath').'class.tx_pagepath_api.php');
$typeGet = mslib_fe::RemoveXSS( \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('type'));
// pagepath plugin must be added soon to support cooluri urls when working in eID
/*
function typolink ($page_id='', $vars='')
{
	if (!$page_id) $page_id=$GLOBALS["TSFE"]->id;
	$conf=array();
	$conf['parameter']=$page_id;
	if ($vars) $conf['additionalParams']='&'.$vars;
	$conf['returnLast'] = 'url'; // get it as URL
//	$url = htmlspecialchars($GLOBALS["TSFE"]->cObj->typolink(NULL, $conf));	
	$url = tx_pagepath_api::getPagePath($page_id, $conf);
//		$url = $GLOBALS["TSFE"]->cObj->typolink(NULL, $conf);	
	return $url;
}
*/
?>