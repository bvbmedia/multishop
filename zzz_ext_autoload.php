<?php
$extensionPath = t3lib_extMgm::extPath('multishop');
return array(
	'tx_multishop' => $extensionPath . 'pi1/classes/class.tx_multishop_cms_layout.php',
	'tx_multishop_addmiscfieldstoflexform' => $extensionPath . 'class.tx_multishop_addMiscFieldsToFlexForm.php',
	'tx_multishop_configuration' => $extensionPath . 'pi1/classes/class.tx_multishop_configuration.php',
	'tx_multishop_module1' => $extensionPath . 'mod1/index.php',
	'tx_multishop_pi1' => $extensionPath . 'pi1/class.tx_multishop_pi1.php',
	'tx_multishop_pi1_wizicon' => $extensionPath . 'pi1/class.tx_multishop_pi1_wizicon.php',
	'tx_multishop_realurl' => $extensionPath . 'class.tx_multishop_realurl.php',
	'tx_mslib_cart' => $extensionPath . 'pi1/classes/class.tx_mslib_cart.php',
	'tx_mslib_payment' => $extensionPath . 'pi1/classes/class.class.mslib_payment.php',
	'mslib_befe' => $extensionPath . 'pi1/classes/class.mslib_befe.php',	
	'mslib_fe' => $extensionPath . 'pi1/classes/class.mslib_fe.php',
	'cache_lite' => $extensionPath . 'res/Cache_Lite-1.7.15/Cache/class.cache_lite.php',		
);
?>