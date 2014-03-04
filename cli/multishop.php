<?php
/*************************************************************** 
*  Copyright notice 
* 
*  (c) 2012 BVB Media BV - Bas van Beek <bvbmedia@gmail.com> 
*  All rights reserved 
* 
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify 
*  it under the terms of the GNU General Public License as published by 
*  the Free Software Foundation; either version 2 of the License, or 
*  (at your option) any later version. 
* 
*  The GNU General Public License can be found at 
*  http://www.gnu.org/copyleft/gpl.html. 
* 
*  This script is distributed in the hope that it will be useful, 
*  but WITHOUT ANY WARRANTY; without even the implied warranty of 
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
*  GNU General Public License for more details. 
* 
*  This copyright notice MUST APPEAR in all copies of the script! 
***************************************************************/
// run from shell ie: /var/www/site.com/web/typo3/cli_dispatch.phpsh multishop --attribute=value
if (!defined('TYPO3_cliMode')) die('You cannot run this script directly!');
require_once(PATH_t3lib.'class.t3lib_cli.php');
include_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.mslib_fe.php');
include_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.mslib_befe.php');
class tx_multishop extends t3lib_cli {
	var $shop_pid='';
	var $ms=array();
	function read ($stopchar) {
		$fp = fopen("php://stdin", "r");
		$in = fread($fp, 1); // Start the loop
		$output = '';
		while ($in != $stopchar)
		{
			$output = $output.$in;
			$in = fread($fp, 1);
		}
		fclose ($fp);
		return $output;
	}
	
	function setShopPid($shop_pid) {
		$this->shop_pid=$shop_pid;
		$ms=array();
		$tmp=mslib_befe::loadConfiguration($this->shop_pid);
		$ms['MODULES']=$tmp;		
		$ms=mslib_befe::convertConfiguration($ms);
		$this->ms=$ms;
	}
	
	function execute($commands_array) {
		// init cObj so that the template parser works inside the cli
		/* @var $cObj tslib_cObj */
		chdir(PATH_site);
		if (!$GLOBALS['TSFE'] instanceof tslib_fe) {
		$GLOBALS['TSFE'] = t3lib_div::makeInstance(
		'tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0
		);
		$GLOBALS['TSFE']->config['config']['language'] = null;
		$GLOBALS['TSFE']->initTemplate();
		}
		if (!isset($GLOBALS['TT'])) {
		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_TimeTrackNull');
		}
		$GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');		
		switch($commands_array['action']) {
			case 'rebuild_flat_database':
				mslib_befe::rebuildFlatDatabase();
			break;
		}
		
		// hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/cli/multishop.php']['cli_cron'])) {
			$params = array('commands_array' => &$commands_array);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/cli/multishop.php']['cli_cron'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
	}
}
$commands_array=array();
if (count($_SERVER['argv']) >1) {
	foreach ( $_SERVER['argv'] as $key => $value ) {
		if ($value=='--help') {
			$show_example=1;
		}
		
		if (preg_match("/^--/",$value)) {
			preg_match("/^--(.*?)\=(.*?)$/",$value,$results);			
			$commands_array[$results[1]]=$results[2]; 
		}
	}
}

if ($show_example) {
	$string="Example:\n";
	$string.="/var/www/site.com/web/typo3/cli_dispatch.phpsh multishop --shop_pid=206 --action=cli_action_to_call\n";			
	fwrite(STDOUT,$string);  
	exit(0);	
}

$needed_vars=array();
$needed_vars[]='action';
$needed_vars[]='shop_pid';
$halt=0;
foreach ($needed_vars as $needed_var) {
	if (!array_key_exists($needed_var,$commands_array)) {
		fwrite(STDOUT,$needed_var." is not specified\n"); 
		$halt=1;
	}
}

if ($halt) {
	fwrite(STDOUT,"Script aborted.\n"); 
	exit(0);
}

fwrite(STDOUT, "TYPO3 Multishop Cli\n\n");
set_time_limit(86400); 
ignore_user_abort(true);
$object=t3lib_div::makeInstance('tx_multishop');
$object->setShopPid($commands_array['shop_pid']);
$object->execute($commands_array);
?>