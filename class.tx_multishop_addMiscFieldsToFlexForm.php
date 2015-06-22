<?php

class tx_multishop_addMiscFieldsToFlexForm {
	function addMiscFields($config) {
		$optionList=array();
		$optionList[]=array(
			0=>'Create account',
			1=>'create_account'
		);
		$optionList[]=array(
			0=>'Edit account',
			1=>'edit_account'
		);
		$optionList[]=array(
			0=>'Custom script',
			1=>'custom_script'
		);
		$optionList[]=array(
			0=>'Store locator',
			1=>'store_locator'
		);
		$optionList[]=array(
			0=>'Shopping cart',
			1=>'shopping_cart'
		);
		$optionList[]=array(
			0=>'Checkout',
			1=>'checkout'
		);
		$optionList[]=array(
			0=>'Order history',
			1=>'order_history'
		);
		$optionList[]=array(
			0=>'Currency selector',
			1=>'currency_selector'
		);
		//hook to let other plugins further manipulate the flexform
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/class.tx_multishop_addMiscFieldsToFlexForm.php']['addMiscFields'])) {
			$params=array(
				'optionList'=>&$optionList
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/class.tx_multishop_addMiscFieldsToFlexForm.php']['addMiscFields'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		if (!count($optionList)) {
			$optionList[0]=array(
				0=>'none',
				1=>'none'
			);
		}
		$config['items']=array_merge($config['items'], $optionList);
		return $config;
	}
}

?>
