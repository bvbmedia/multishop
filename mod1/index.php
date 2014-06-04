<?php
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

$LANG->includeLLFile('EXT:multishop/mod1/locallang.xml');
//require_once(PATH_t3lib . 'class.t3lib_scbase.php');
include_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.mslib_fe.php');
include_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.mslib_befe.php');
//require_once(PATH_t3lib . 'class.t3lib_iconworks.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]


/**
 * Module 'Multishop' for the 'multishop' extension.
 * @author    BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
 * @package    TYPO3
 * @subpackage    tx_multishop
 */
class  tx_multishop_module1 extends t3lib_SCbase {
	var $pageinfo;
	/**
	 * Initializes the Module
	 * @return    void
	 */
	function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		parent::init();
		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}
	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 * @return    void
	 */
	function menuConfig() {
		global $LANG;
		$this->MOD_MENU=Array(
			'function'=>Array(
				'1'=>$LANG->getLL('function1'),
				'2'=>$LANG->getLL('function2'),
				'3'=>$LANG->getLL('function3'),
			)
		);
		parent::menuConfig();
	}
	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 * @return    [type]        ...
	 */
	function main() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo=t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access=is_array($this->pageinfo) ? 1 : 0;
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id)) {
			$full_script_path=t3lib_extMgm::extPath('multishop').'scripts/';
			$this->relative_site_path=t3lib_extMgm::siteRelPath('multishop');
			// first get the http-path to typo3:
			$this->httpTypo3Path=substr(substr(t3lib_div::getIndpEnv('TYPO3_SITE_URL'), strlen(t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST'))), 0, -1);
			if (strlen($this->httpTypo3Path)==1) {
				$this->httpTypo3Path='/';
			} else {
				$this->httpTypo3Path.='/';
			}
			// Get the vhost full path (example: /var/www/html/domain.com/public_html/my_cms/)
			$this->DOCUMENT_ROOT=PATH_site;
			// Get the vhost full path to multishop (example: /var/www/html/domain.com/public_html/my_cms/typo3conf/ext/multishop/)
			$this->DOCUMENT_ROOT_MS=PATH_site.t3lib_extMgm::siteRelPath('multishop');
			// Get the site full URL (example: http://domain.com/my_cms/)
			$this->FULL_HTTP_URL=t3lib_div::getIndpEnv('TYPO3_SITE_URL');
			// Get the multishop full URL (example: http://domain.com/my_cms/typo3/ext/multishop or http://domain.com/my_cms/typo3conf/ext/multishop)
			$this->FULL_HTTP_URL_MS=t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelPath('multishop');
			// Get the host URL (example: http://domain.com)
			// dont use hostURL cause its not supporting subdirectory hosting
			$this->hostURL=t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST');
			// Draw the header.
			$this->doc=t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath=$BACK_PATH;
			// JavaScript
			$this->doc->JScode='
							<style type="text/css"> 
							/*<![CDATA[*/
							<!-- 
							/*inDocStyles*/
							 
							 
							/*###POSTCSSMARKER###*/
							-->
							/*]]>*/
							.shadow_bottom {
							overflow:hidden;
							width:100%;
							background:url('.$this->FULL_HTTP_URL_MS.'mod1/images/shadow_bottom.png) left bottom no-repeat;
							padding:0 0 35px;
							}
							fieldset {
							display:block;
							border:1px solid #999;
							background:#fff;
							margin:10px 0 0;
							padding:10px 10px 10px;
							}
							fieldset legend {
							background:#585858;
							color:#fff;
							font-family:Verdana,Arial,Helvetica,sans-serif;
							font-size:11px;
							padding:2px 4px;
							}
							fieldset legend a
							{
								color:#fff;
							}
							fieldset legend a:hover
							{
								color:#fff;
								text-decoration:underline;
							}							 
							ul {
							list-style:none;
							margin:0;
							padding:0;
							}
							a.buttons, a.buttons_db {
							-moz-border-radius:1px 1px 1px 1px;
							BACKGROUND-IMAGE: url('.$this->FULL_HTTP_URL.'typo3/sysext/t3skin/images/backgrounds/button.png); 
							background-color:#F6F6F6;
							background-image:-moz-linear-gradient(center top , #F6F6F6 10%, #D5D5D5 90%);
							background-position:center bottom;
							background-repeat:repeat-x;
							border:1px solid #7C7C7C;
							color:#434343;
							display:block;
							padding:2px 4px;
							text-align:center;
							font-family:Verdana,Arial,Helvetica,sans-serif;
							font-size:11px;
							line-height:16px;
							}
							a.buttons:hover, a.buttons_db:hover {
							BACKGROUND-IMAGE: url('.$this->FULL_HTTP_URL.'typo3/sysext/t3skin/images/backgrounds/button-hover.png); 
							background-color:#C8C8C8;
							background-image:-moz-linear-gradient(center top , #F6F6F6 10%, #BDBCBC 90%);
							background-position:center bottom;
							background-repeat:repeat-x;
							border:1px solid #737F91;
							color:#1E1E1E;
							}
							a.buttons {
							width:142px;
							}
							a.buttons_db {
							width:126px;
							}
							fieldset.mod1MultishopFieldset ul { overflow:hidden; width:100%; }
							fieldset.mod1MultishopFieldset ul li { float:left; margin:0 10px 0 0; }							
							</style> 						
							<!--[if IE]>
							<style>
							fieldset {
							position: relative;
							padding-top:15px;
							margin:20px 0 0;
							}
							legend {
							position: absolute;
							top: -10px;
							left: 10px;
							}
							</style>
							<![endif]-->
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
								function CONFIRM(label)
								{
												if (confirm(label))
												{
													return true;
												}
												else
												{
													return false;
												}
								}												
							</script>
						';
			$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';
			$headerSection=$this->doc->getHeader('pages', $this->pageinfo, $this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'], 50);
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('', $this->doc->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);
			// Render content:
			$this->moduleContent();
			// ShortCut
			if ($BE_USER->mayMakeShortcut()) {
				$this->content.=$this->doc->spacer(20).$this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}
			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero
			$this->doc=t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath=$BACK_PATH;
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}
	/**
	 * Prints out the module HTML
	 * @return    void
	 */
	function printContent() {
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	/**
	 * Generates the module content
	 * @return    void
	 */
	function moduleContent() {
		if (t3lib_extMgm::isLoaded('t3jquery')) {
			require_once(t3lib_extMgm::extPath('t3jquery').'class.tx_t3jquery.php');
			tx_t3jquery::addJqJS();
			$this->content.=tx_t3jquery::getJqJSBE();
		}
		$typo3Version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : t3lib_div::int_from_ver(TYPO3_version);
		if ($typo3Version>=6000000) {
			$t3lib_BEfuncAlias = '\TYPO3\CMS\Backend\Utility\BackendUtility';
		} else {
			$t3lib_BEfuncAlias = 't3lib_BEfunc';
		}

		$this->iconWorks=method_exists('t3lib_iconWorks', 'getSpriteIcon');
		$this->mod_info=$this->getExtensionInfo('multishop');
		$this->get=$_GET;
		$this->post=$_POST;
		switch ((string)$this->MOD_SETTINGS['function']) {
			case 1:
				require_once(t3lib_extMgm::extPath('multishop').'mod1/pages/welcome.php');
				break;
			case 2:
				require_once(t3lib_extMgm::extPath('multishop').'mod1/pages/administration.php');
				break;
			case 3:
				require_once(t3lib_extMgm::extPath('multishop').'mod1/pages/help.php');
				break;
		}
		$year=date("Y");
		if ($year==2010) {
			$year_string.=$year;
		} else {
			$year_string.='2010-'.$year;
		}
		$this->content.='
					<center>
	';
		$this->content.='

<a title="Click for more information about TYPO3 Multishop" href="http://www.typo3multishop.com/?utm_source=Typo3Multishop_Backend&utm_medium=cpc&utm_term=Typo3Multishop&utm_content=Listing&utm_campaign=Typo3Multishop" target="_blank"><img src="'.$this->FULL_HTTP_URL_MS.'mod1/images/typo3multishop.png"></a><br><strong>version: '.$this->mod_info['version'].'</strong><BR><BR>
<a title="Follow TYPO3 Multishop on Twitter" href="http://twitter.com/typo3multishop" target="_blank"><img src="'.$this->FULL_HTTP_URL_MS.'mod1/images/twitter.png"></a> 
<a title="Follow TYPO3 Multishop on LinkedIn" href="http://www.linkedin.com/groups?gid=3117344" target="_blank"><img src="'.$this->FULL_HTTP_URL_MS.'mod1/images/linkedin.png"></a> 
<a title="Follow TYPO3 Multishop on FaceBook" href="http://www.facebook.com/typo3multishop" target="_blank"><img src="'.$this->FULL_HTTP_URL_MS.'mod1/images/facebook.png"></a>
<br>skype: typo3multishop<BR><BR>
					<a title="copyright '.$year_string.' by BVB Media BV" href="http://www.bvbmedia.com/?utm_source=Typo3Multishop_Backend&utm_medium=cpc&utm_term=Typo3Multishop&utm_content=Listing&utm_campaign=Typo3Multishop" target="_blank">copyright '.$year_string.' to BVB Media BV</a><br>
					webdevelopment by <a href="http://www.basvanbeek.nl/?utm_source=Typo3Multishop_Backend&utm_medium=cpc&utm_term=Typo3Multishop&utm_content=Listing&utm_campaign=Typo3Multishop" target="_blank">Bas van Beek</a> - <a href="mailto:bvbmedia@gmail.com">bvbmedia@gmail.com</a><br>
					</center>
					';
	}
	// get tree
	function getPageTree($pid='0', $cates=array(), $times=0, $include_itself=0) {
		if ($include_itself) {
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,keywords,description', 'pages', 'hidden = 0 and deleted = 0 and (uid='.$pid.')', '');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$cates[$row['uid']]=array(
					'|'.str_repeat("--", $times-1)."-[ ".$row['title'],
					$row
				);
				$cates=$this->getPageTree($row['uid'], $cates, $times);
			}
			$times++;
		}
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,keywords,description', 'pages', 'hidden = 0 and deleted = 0 and pid='.$pid.'', '');
		$times++;
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$cates[$row['uid']]=array(
				'|'.str_repeat("--", $times-1)."-[ ".$row['title'],
				$row
			);
			$cates=$this->getPageTree($row['uid'], $cates, $times);
		}
		$times--;
		return $cates;
	}
	/**
	 * This function creates a zip file
	 * Credits goes to Kraft Bernhard (kraftb@think-open.at)
	 * @param    string        File/Directory to pack
	 * @param    string        Zip-file target directory
	 * @param    string        Zip-file target name
	 * @return    array        Files packed
	 */
	function zipPack($file, $targetFile) {
		if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
			return array();
		}
		$zip=$GLOBALS['TYPO3_CONF_VARS']['BE']['zip_path'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['zip_path'] : 'zip';
		$path=dirname($file);
		$file=basename($file);
		chdir($path);
		$cmd=$zip.' -r -9 '.escapeshellarg($targetFile).' '.escapeshellarg($file);
		exec($cmd, $list, $ret);
		if ($ret) {
			return array();
		}
		$result=$this->getFileResult($list, 'zip');
		return $result;
	}
	/**
	 * This function unpacks a zip file
	 * Credits goes to Kraft Bernhard (kraftb@think-open.at)
	 * @param    string        File to unpack
	 * @return    array        Files unpacked
	 */
	function zipUnpack($file) {
		if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
			return array();
		}
		$path=dirname($file);
		chdir($path);
		// Unzip without overwriting existing files
		$unzip=$GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path'] : 'unzip';
		if ($this->overwrite) {
			$cmd=$unzip.' -o '.escapeshellarg($file);
		} else {
			$cmd=$unzip.' -n '.escapeshellarg($file);
		}
		exec($cmd, $list, $ret);
		if ($ret) {
//						return array();
		}
		$result=$this->getFileResult($list, 'unzip');
		return $result;
	}
	/**
	 * This method helps filtering the output of the various archive binaries to get a clean php array
	 * Credits goes to Kraft Bernhard (kraftb@think-open.at)
	 * @param    array        The output of the executed archive binary
	 * @param    string        The type/configuration for which to parse the output
	 * @return    array        A clean list of the filenames returned by the binary
	 */
	function getFileResult($list, $type='zip') {
		$sc=$GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['split_char'];
		$pre=intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['pre_lines']);
		$post=intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['post_lines']);
		$pos=intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['file_pos']);
		// Removing trailing lines
		while ($post--) {
			array_pop($list);
		}
		// Only last lines
		if ($pre===-1) {
			$fl=array();
			while ($line=trim(array_pop($list))) {
				array_unshift($fl, $line);
			}
			$list=$fl;
		}
		// Remove preceeding lines
		if ($pre>0) {
			while ($pre--) {
				array_shift($list);
			}
		}
		$fl=array();
		foreach ($list as $file) {
			$parts=preg_split('/'.preg_quote($sc).'+/', $file);
			$fl[]=trim($parts[$pos]);
		}
		return $fl;
	}
	/**
	 * This method recursively deletes folders
	 * @param    string        The path of the folder to delete
	 * @return    boolean        True or False
	 */
	function deltree($path) {
		if (is_dir($path)) {
			if (version_compare(PHP_VERSION, '5.0.0')<0) {
				$entries=array();
				if ($handle=opendir($path)) {
					while (false!==($file=readdir($handle))) {
						$entries[]=$file;
					}
					closedir($handle);
				}
			} else {
				$entries=scandir($path);
				if ($entries===false) {
					$entries=array();
				} // just in case scandir fail...
			}
			foreach ($entries as $entry) {
				if ($entry!='.' && $entry!='..') {
					$this->deltree($path.'/'.$entry);
				}
			}
			return rmdir($path);
		} else {
			return unlink($path);
		}
	}
	/**
	 * Gets information for an extension, eg. version and most-recently-edited-script
	 * @param    string        Extension key
	 * @return    array        Information array (unless an error occured)
	 */
	function getExtensionInfo($extKey) {
		$rc='';
		if (t3lib_extMgm::isLoaded($extKey)) {
			$path=t3lib_extMgm::extPath($extKey);
			$file=$path.'/ext_emconf.php';
			if (@is_file($file)) {
				$_EXTKEY=$extKey;
				$EM_CONF=array();
				include($file);
				$eInfo=array();
				// Info from emconf:
				$eInfo['title']=$EM_CONF[$extKey]['title'];
				$eInfo['author']=$EM_CONF[$extKey]['author'];
				$eInfo['author_email']=$EM_CONF[$extKey]['author_email'];
				$eInfo['author_company']=$EM_CONF[$extKey]['author_company'];
				$eInfo['version']=$EM_CONF[$extKey]['version'];
				$eInfo['description']=$EM_CONF[$extKey]['description'];
				$eInfo['CGLcompliance']=$EM_CONF[$extKey]['CGLcompliance'];
				$eInfo['CGLcompliance_note']=$EM_CONF[$extKey]['CGLcompliance_note'];
				if (is_array($EM_CONF[$extKey]['constraints']) && is_array($EM_CONF[$extKey]['constraints']['depends'])) {
					$eInfo['TYPO3_version']=$EM_CONF[$extKey]['constraints']['depends']['typo3'];
				} else {
					$eInfo['TYPO3_version']=$EM_CONF[$extKey]['TYPO3_version'];
				}
				$filesHash=unserialize($EM_CONF[$extKey]['_md5_values_when_last_written']);
				$eInfo['manual']=@is_file($path.'/doc/manual.sxw');
				$rc=$eInfo;
			} else {
				$rc='ERROR: No emconf.php file: '.$file;
			}
		} else {
			$rc='Error: Extension '.$extKey.' has not been installed. (tx_fhlibrary_system::getExtensionInfo)';
		}
		return $rc;
	}
	/*
		check if the method is existing, cause in older TYPO3 version it's not yet there
	*/
	function Typo3Icon($class, $label='') {
		if ($this->iconWorks) {
			return t3lib_iconWorks::getSpriteIcon($class);
		} else {
			return (($label) ? '['.$label.']' : '');
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multishop/mod1/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multishop/mod1/index.php']);
}
// Make instance:
$SOBE=t3lib_div::makeInstance('tx_multishop_module1');
$SOBE->init();
// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}
$SOBE->main();
$SOBE->printContent();
?>
