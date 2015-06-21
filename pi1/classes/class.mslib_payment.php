<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
/***************************************************************
 *  Copyright notice
 *  (c) 2010 BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 * Hint: use extdeveval to insert/update function index above.
 */
class mslib_payment extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $name='';
	var $variables='';
	static $installedPaymentMethods=array();
	static $enabledPaymentMethods=array();
	public $ref='';
	function initLanguage($ms_locallang) {
		$this->pi_loadLL();
		//array_merge with new array first, so a value in locallang (or typoscript) can overwrite values from ../locallang_db
		$this->LOCAL_LANG=array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
		if ($this->altLLkey) {
			$this->LOCAL_LANG=array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
		}
	}
	function init($ref) {
		mslib_fe::init($ref);
		$this->initLanguage($ref->LOCAL_LANG);
		static $installedPaymentMethods;
		// custom hook for loading the installed payment methods
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_payment.php']['mslib_payment'])) {
			$params=array('installedPaymentMethods'=>&$installedPaymentMethods);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_payment.php']['mslib_payment'] as $funcRef) {
				 \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $ref);
			}
		}
		$this->installedPaymentMethods=$params['installedPaymentMethods'];
		// custom hook for loading the installed payment methods eof
		// load enabled payment methods
		$str="SELECT * from tx_multishop_payment_methods s, tx_multishop_payment_methods_description d where ";
		if (!$include_hidden_items) {
			$str.="s.status=1 and ";
		}
		$str.="d.language_id='".$this->sys_language_uid."' and s.id=d.id order by s.sort_order";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
			$array=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
				$array[$row['code']]=$row;
			}
			$this->enabledPaymentMethods=$array;
		}
		// load enabled payment methods eof	
	}
	function getInstalledPaymentMethods() {
		return $this->installedPaymentMethods;
	}
	function getEnabledPaymentMethods() {
		return $this->enabledPaymentMethods;
	}
	function setPaymentMethod($name) {
		if (is_array($this->installedPaymentMethods) and $this->installedPaymentMethods[$name]) {
			$this->name=$name;
			return true;
		}
	}
	function ajaxNotificationServer($ref) {
		return $content;
	}
	function setVariables($vars) {
		$this->variables=$vars;
	}
	function getVariables($vars) {
		return $this->variables;
	}
	function displayPaymentButton($orders_id, $ref) {
		return $content;
	}
	function paymentNotificationHandler() {
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_payment.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_payment.php"]);
}
?>