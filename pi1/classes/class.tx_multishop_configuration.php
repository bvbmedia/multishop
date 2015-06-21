<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// not yet being used
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
class tx_multishop_configuration {
	var $shopPid='';
	var $configurationArray=array();
	var $staticConfiguration=true;
	/**
	 * @param string $shopPid
	 */
	public function setShopPid($shopPid) {
		$this->shopPid=$shopPid;
	}
	/**
	 * @return the $configurationArray
	 */
	public function generateConfigurationArray() {
		if ($this->staticConfiguration==true) {
			static $settings;
			if (is_array($settings)) {
				// the settings are already loaded before so lets return them.
				$this->configurationArray=$settings;
				return '';
			}
			// first check if we already loaded the configuration before
		}
		$settings=array();
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_configuration_values', // FROM ...
			'page_uid="'.$this->shopPid.'"', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if (isset($row['configuration_value']) and $row['configuration_value']!='') {
					$settings['LOCAL_MODULES'][$row['configuration_key']]=$row['configuration_value'];
				}
			}
		}
		// load local front-end module config eof
		// load global front-end module config
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_configuration', // FROM ...
			'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if (isset($row['configuration_value'])) {
					$settings['GLOBAL_MODULES'][$row['configuration_key']]=$row['configuration_value'];
				}
			}
		}
		// load global front-end module config eof
		// merge global with local front-end module config
		foreach ($settings['GLOBAL_MODULES'] as $key=>$value) {
			if (isset($settings['LOCAL_MODULES'][$key])) {
				$settings[$key]=$settings['LOCAL_MODULES'][$key];
			} else {
				$settings[$key]=$value;
			}
		}
		// merge global with local front-end module config eof
		if ($settings['COUNTRY_ISO_NR']) {
			$country=mslib_fe::getCountryByIso($settings['COUNTRY_ISO_NR']);
			$settings['CURRENCY_ARRAY']=mslib_befe::loadCurrency($country['cn_currency_iso_nr']);
			// if default currency is not set then define it to the store country currency
			if (!$settings['DEFAULT_CURRENCY']) {
				$settings['DEFAULT_CURRENCY']=$settings['CURRENCY_ARRAY']['cu_iso_3'];
			}
			switch ($settings['COUNTRY_ISO_NR']) {
				case '528':
				case '276':
					$settings['CURRENCY']='&#8364;';
					break;
				default:
					$settings['CURRENCY']=$settings['CURRENCY_ARRAY']['cu_symbol_left'];
					break;
			}
		}
		if (!$this->cookie['selected_currency']) {
			$this->cookie['selected_currency']=$settings['DEFAULT_CURRENCY'];
		}
		if ($this->cookie['selected_currency']) {
			// load customer selected currency
			$settings['CUSTOMER_CURRENCY_ARRAY']=mslib_befe::loadCurrency($this->cookie['selected_currency'], 'cu_iso_3');
			$settings['CUSTOMER_CURRENCY']=$settings['CUSTOMER_CURRENCY_ARRAY']['cu_symbol_left'];
		}
		//hook to let other plugins further manipulate the settings
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loadConfiguration'])) {
			$params=array(
				'settings'=>&$settings,
				'this'=>&$this
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loadConfiguration'] as $funcRef) {
				 \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		$this->configurationArray=$settings;
	}
	/**
	 * @return the $configurationArray
	 */
	public function getConfigurationArray() {
		return $this->configurationArray;
	}
}
?>