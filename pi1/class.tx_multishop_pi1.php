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
$version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
if ($version<6000000) {
	require_once(PATH_tslib.'class.tslib_pibase.php');
}
/**
 * Plugin 'multishop' for the 'multishop' extension.
 * @author    BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
 * @package    TYPO3
 * @subpackage    tx_multishop
 */
class tx_multishop_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $cObj; // reference to the calling object.
	var $prefixId='tx_multishop_pi1';        // Same as class name
	var $scriptRelPath='pi1/class.tx_multishop_pi1.php';    // Path to this script relative to the extension dir.
	var $extKey='multishop';    // The extension key.
	var $pi_checkCHash=false;
	// var to hold user/shop info loaded from tt_address table
	var $tta_user_info=array();
	var $tta_shop_info=array();
	var $ms=array();
	var $server=array();
	var $cookie=array();
	/**
	 * The main method of the PlugIn
	 * @param    string $content : The PlugIn content
	 * @param    array $conf : The PlugIn configuration
	 * @return    The content that is displayed on the website
	 */
	function admin_main($content, $conf) {
		self::construct($conf);
		if (!defined('MsApplicationTopOnceIsLoaded')) {
			define('MsApplicationTopOnceIsLoaded', 1);
			require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/application_top_once.php');
		}
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/application_top_always.php');
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/core.php');
		return '<div class="csc-plugin csc-plugin-multishop_pi1"><div id="tx_multishop_pi1_core">'.$this->pi_wrapInBaseClass($content).'</div></div>';
	}
	/**
	 * Init Function: here all the needed configuration values are stored in class variables
	 * @param    array $conf : configuration array from TS
	 * @return   void
	 */
	function construct($conf) {
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_USER_INT_obj=1;
		$this->pi_initPIflexForm();
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.mslib_fe.php');
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.mslib_befe.php');
		$this->HTTP_HOST=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
		// Get the vhost full path (example: /var/www/html/domain.com/public_html/my_cms/)
		$this->DOCUMENT_ROOT=PATH_site;
		// Get the vhost full path to multishop (example: /var/www/html/domain.com/public_html/my_cms/typo3conf/ext/multishop/)
		$this->DOCUMENT_ROOT_MS=PATH_site.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey);
		// Get the vhost full path to TYPO3 (example: /var/www/html/domain.com/public_html/my_cms/typo3/)
		$this->DOCUMENT_ROOT_TYPO3=PATH_site.TYPO3_mainDir;
		// Get the site full URL (example: http://domain.com/my_cms/)
		$this->FULL_HTTP_URL=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
		// Get the multishop full URL (example: http://domain.com/my_cms/typo3/ext/multishop/ or http://domain.com/my_cms/typo3conf/ext/multishop/)
		$this->FULL_HTTP_URL_MS=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL').\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey);
		// Get the full URL (example: http://domain.com/my_cms/typo3/)
		$this->FULL_HTTP_URL_TYPO3=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL').TYPO3_mainDir;
		$this->get=\TYPO3\CMS\Core\Utility\GeneralUtility::_GET();
		$this->post=\TYPO3\CMS\Core\Utility\GeneralUtility::_POST();
		$this->server=array();
		$this->server['HTTP_ACCEPT_LANGUAGE']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_ACCEPT_LANGUAGE');
		$this->server['HTTP_USER_AGENT']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_USER_AGENT');
		$this->server['HTTP_REFERER']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_REFERER');
		if (!$this->server['HTTP_ACCEPT_LANGUAGE']) {
			$this->server['HTTP_ACCEPT_LANGUAGE']='en';
		}
		$this->server['DOCUMENT_ROOT']=$this->DOCUMENT_ROOT;
		$this->server['REQUEST_URI']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI');
		$this->server['REDIRECT_URL']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REDIRECT_URL');
		$this->server['QUERY_STRING']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('QUERY_STRING');
		$this->server['REMOTE_ADDR']=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR');
		$this->REMOTE_ADDR=\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR');
		$this->server['HTTP_HOST']=mslib_befe::strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'));
		$tmp=explode("?", $this->server['REQUEST_URI']);
		$this->server['REQUEST_URI']=$tmp[0];
		$this->server['REQUEST_URI']=preg_replace("/^\//is", '', $this->server['REQUEST_URI']);
		// load language cookie for the backend
		$this->cookie=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_cookie');
//		if (!isset($this->cookie['multishop_admin_language']) and !isset($this->post['multishop_admin_language'])) $this->post['multishop_admin_language']='';
		if (isset($this->post['multishop_admin_language']) and ($this->post['multishop_admin_language']!=$this->cookie['multishop_admin_language'])) {
			if ($this->post['multishop_admin_language']=='default') {
				$this->post['multishop_admin_language']='';
			}
			$this->cookie['multishop_admin_language']=$this->post['multishop_admin_language'];
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
			$GLOBALS['TSFE']->storeSessionData();
		}
		if ($this->server['HTTP_REFERER'] and !$this->cookie['HTTP_REFERER']) {
			$host=@parse_url($this->server['HTTP_REFERER']);
			if (is_array($host) and mslib_befe::strtolower($host['host'])!=$this->server['HTTP_HOST']) {
				$this->cookie['HTTP_REFERER']=$this->server['HTTP_REFERER'];
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
				$GLOBALS['TSFE']->storeSessionData();
			}
		}
		$this->cookie=$GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_cookie');
		if (strlen($this->cookie['multishop_admin_language'])==2) {
			$this->customLang=$this->cookie['multishop_admin_language'];
		}
		// able to change language by get parameters
		if (strlen($this->get['language'])==2) {
			$this->customLang=$this->get['language'];
		}
		if ($this->customLang) {
			$this->LLkey=$this->customLang;
			$this->config['config']['language']=$this->customLang;
			$GLOBALS['TSFE']->config['config']['language']=$this->customLang;
			$sys_language_uid=mslib_befe::getSysLanguageUidByIsoString($this->customLang);
			if (!$sys_language_uid) {
				// try by flag
				$sys_language_uid=mslib_befe::getSysLanguageUidByFlagString($this->customLang);
				//echo $sys_language_uid;
				//die();
			}
			if ($sys_language_uid) {
				$GLOBALS['TSFE']->config['config']['sys_language_uid']=$sys_language_uid;
				$GLOBALS['TSFE']->sys_language_uid=$sys_language_uid;
			}
		}
		if (!$this->LLkey) {
			$this->LLkey='default';
		}
		if (!$GLOBALS['TSFE']->config['config']['locale_all']) {
			$GLOBALS['TSFE']->config['config']['locale_all']=$this->pi_getLL('locale_all');
		}
		$this->lang=$GLOBALS['TSFE']->config['config']['language'];
		setlocale(LC_TIME, $GLOBALS['TSFE']->config['config']['locale_all']);
		$this->sys_language_uid=$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		if (!isset($this->sys_language_uid)) {
			$this->sys_language_uid=0;
		}
		$this->LOCAL_LANG_loaded=0;
		$this->pi_loadLL();
		// load language cookie for the backend eof
		// disabled the code so developer can work with config.absRefPrefix too
		/*
		if (!$GLOBALS['TSFE']->config['config']['baseURL']) {
			echo 'config.baseURL='.$this->FULL_HTTP_URL.' is not set yet. Please go to the TYPO3 template setup field editor and add it.';
			die();
		}
		*/
		// setting coming from typoscript or from flexform
		if ($this->conf['method']) {
			$this->method=$this->conf['method'];
		} else {
			$this->method=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'method', 'sDEFAULT');
		}
		// shop pid
		$this->shop_pid=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'page_uid', 'sDEFAULT');
		if (!$this->shop_pid and $this->conf['shop_pid']>0) {
			$this->shop_pid=$this->conf['shop_pid'];
		}
		if (!$this->shop_pid) {
			$this->shop_pid=$GLOBALS["TSFE"]->id;
		}
		// shop pid eof
		$this->admin_group=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'admin_group', 'sDEFAULT');
		// var for the tabbed navigation menu
		$this->maxDELIMITED=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxDELIMITED', 's_listing');
		// Autoloader works great in TYPO3 4.7.7. But in TYPO3 4.5.X the invalid namespace classes are not autoloaded so lets load it manually then too
		// PHP Fatal error:  Access to undeclared static property: t3lib_autoloader::$classNameToFileMapping in /shopcvs/skeleton/typo3_src-4.7.5/t3lib/class.t3lib_autoloader.php on line 151
		if (!class_exists('Cache_Lite')) {
			require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'res/Cache_Lite-1.7.16/Cache/Lite.php');
		}
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.mslib_payment.php');
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_catalog.php');
	}
	/*
		this method is created for returning ajax content.
	*/
	function ajax_main($content, $conf) {
		$this->AJAX_MODE=1;
		self::construct($conf);
		if (!defined('MsApplicationTopOnceIsLoaded')) {
			define('MsApplicationTopOnceIsLoaded', 1);
			require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/application_top_once.php');
		}
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/application_top_always.php');
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/ajax_pages/core.php');
		return $this->pi_wrapInBaseClass($content);
	}
	function main($content, $conf) {
		self::construct($conf);
		if (!defined('MsApplicationTopOnceIsLoaded')) {
			define('MsApplicationTopOnceIsLoaded', 1);
			require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/application_top_once.php');
		}
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/application_top_always.php');
		switch ($this->method) {
			case 'custom_script':
				if ($this->custom_script_location) {
					require($this->custom_script_location);
				}
				break;
			case 'meta_tags':
				require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/meta_tags.php');
				if (!$this->ajax_content) {
					ksort($meta_tags);
					$meta_tags_html='';
					foreach ($meta_tags as $item) {
						$meta_tags_html.=$item;
					}
					// the reason why we have to return the header tags as content is because this section is already mapped to the head tag
					// if we put it in additionalHeaderData the page.headerData order is ignored and it can give annoying conflicts
					//$GLOBALS['TSFE']->additionalHeaderData[]= mslib_fe::processmeta($meta_tags_html);
					//return mslib_fe::processmeta($meta_tags_html);
					$GLOBALS['TSFE']->additionalHeaderData[]=mslib_fe::processmeta($meta_tags_html);
					mslib_fe::logPageView();
				}
				return $content;
				break;
			case 'basket':
				if (strstr($this->ms['MODULES']['BASKET_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['BASKET_TYPE'].'.php');
				} elseif ($this->ms['MODULES']['BASKET_TYPE']) {
					if ($this->ms['MODULES']['BASKET_TYPE']=='default') {
						$this->ms['MODULES']['BASKET_TYPE']='basket_default';
					}
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/'.$this->ms['MODULES']['BASKET_TYPE'].'.php');
				} else {
					require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/basket_default.php');
				}
				break;
			case 'manufacturers':
				if (!isset($this->ms['MODULES']['MANUFACTURERS_TYPE']) and isset($this->ms['MODULES']['MANUFACTURERS_LISTING_TYPE'])) {
					$this->ms['MODULES']['MANUFACTURERS_TYPE']=$this->ms['MODULES']['MANUFACTURERS_LISTING_TYPE'];
				}
				if (strstr($this->ms['MODULES']['MANUFACTURERS_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['MANUFACTURERS_TYPE'].'.php');
				} elseif ($this->ms['MODULES']['MANUFACTURERS_TYPE']) {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/manufacturers_listing/'.$this->ms['MODULES']['MANUFACTURERS_TYPE'].'.php');
				} else {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/manufacturers_listing/default.php');
				}
				break;
			case 'categories':
				if (strstr($this->ms['MODULES']['CATEGORIES_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CATEGORIES_TYPE'].'.php');
				} elseif ($this->ms['MODULES']['CATEGORIES_TYPE']) {
					if ($this->ms['MODULES']['CATEGORIES_TYPE']=='default') {
						$this->ms['MODULES']['CATEGORIES_TYPE']='categories_default';
					}
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/'.$this->ms['MODULES']['CATEGORIES_TYPE'].'.php');
				} else {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/categories_default.php');
				}
				break;
			case 'crumbar':
				if (strstr($this->ms['MODULES']['CRUMBAR_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CRUMBAR_TYPE'].'.php');
				} elseif ($this->ms['MODULES']['CRUMBAR_TYPE']) {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/crumbar/'.$this->ms['MODULES']['CRUMBAR_TYPE'].'.php');
				} else {
					require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/crumbar/default.php');
				}
				$content=$crum;
				break;
			case 'search':
				// setting coming from typoscript or from flexform
				if ($this->conf['contentType']) {
					$this->contentType=$this->conf['contentType'];
				} else {
					$this->contentType=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contentType', 's_search');
				}
				switch ($this->contentType) {
					case 'searchform_with_keyword_and_category_dropdown_menu':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/searchform_with_keyword_and_category_dropdown_menu.php');
						break;
					case 'ultrasearch':
						if (strstr($this->ms['MODULES']['ULTRASEARCH_TYPE'], "/")) {
							require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ULTRASEARCH_TYPE'].'.php');
						} else {
							require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/ultrasearch.php');
						}
						break;
					case 'price_filter_navigation_box':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/price_filter_navigation_box.php');
						break;
					case 'manufacturers_dropdown_menu':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/manufacturers_dropdown_menu.php');
						break;
					case 'searchform_with_manufacturers_dropdown_menu':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/searchform_with_manufacturers_dropdown_menu.php');
						break;
					case 'default':
					default:
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/searchform.php');
						break;
				}
				break;
			case 'specials':
				if ($this->conf['section_code']) {
					$this->section_code=$this->conf['section_code'];
				} else {
					$this->section_code=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'section_code', 's_specials');
				}
				if ($this->conf['box_class']) {
					$this->box_class=$this->conf['box_class'];
				} else {
					$this->box_class='multishop_specials';
				}
				if ($this->conf['contentType']) {
					$this->contentType=$this->conf['contentType'];
				} else {
					$this->contentType=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contentType', 's_specials');
				}
				if ($this->conf['limit']) {
					$this->limit=$this->conf['limit'];
				} else {
					$this->limit=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'speciallimit', 's_specials');
				}
				if (!$this->limit) {
					$this->limit=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
				}
				$content.=mslib_fe::SpecialsBox($this->contentType, $this->limit, $this->showCatalogFromPage, $this->cObj->data['uid']);
				break;
			case 'products':
				if ($this->conf['contentType']) {
					$this->contentType=$this->conf['contentType'];
				} else {
					$this->contentType=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contentType', 's_products_listing');
				}
				switch ($this->contentType) {
					case 'products_new':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_new.php');
						break;
					case 'products_modified':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_modified.php');
						break;
					case 'products_upcoming':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_upcoming.php');
						break;
					case 'products_hot':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_hot.php');
						break;
					case 'products_last_visited':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_last_visited.php');
						break;
					case 'products_detail':
						if ($this->productsID and !$this->get['products_id']) {
							$this->get['products_id']=$this->productsID;
						}
						if (!$this->ms['MODULES']['DISABLE_CRUMBAR'] and $GLOBALS['TYPO3_CONF_VARS']["tx_multishop"]['crumbar_html']) {
							$content.=$GLOBALS['TYPO3_CONF_VARS']["tx_multishop"]['crumbar_html'];
						}
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_detail.php');
						$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
						break;
					case 'products_listing':
						if ($this->categoriesID and !$this->get['categories_id']) {
							$this->get['categories_id']=$this->categoriesID;
						} elseif ($this->categoriesStartingPoint and !$this->get['categories_id']) {
							$this->get['categories_id']=$this->categoriesStartingPoint;
						}
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/products_listing.php');
						break;
				}
				break;
			case 'misc':
				if ($this->conf['contentMisc']) {
					$this->contentMisc=$this->conf['contentMisc'];
				} else {
					$this->contentMisc=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contentType', 's_misc');
				}
				switch ($this->contentMisc) {
					case 'shopping_cart':
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['shopping_cartPreProc'])) {
							$params=array();
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['shopping_cartPreProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						if (strstr($this->ms['MODULES']['SHOPPING_CART_TYPE'], "..")) {
							die('error in SHOPPING_CART_TYPE value');
						} else {
							if (strstr($this->ms['MODULES']['SHOPPING_CART_TYPE'], "/")) {
								// relative mode
								require($this->DOCUMENT_ROOT.$this->ms['MODULES']['SHOPPING_CART_TYPE'].'.php');
							} else {
								require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/shopping_cart/default.php');
							}
						}
						$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
						break;
					case 'checkout':
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['checkoutPreProc'])) {
							$params=array();
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['checkoutPreProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						if ($this->ms['MODULES']['FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT']) {
							$this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']=1;
						}
						$this->ms['page']='checkout';
						if (strstr($this->ms['MODULES']['CHECKOUT_TYPE'], "..")) {
							die('error in CHECKOUT_TYPE value');
						} else {
							if (strstr($this->ms['MODULES']['CHECKOUT_TYPE'], "/")) {
								// relative mode
								require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CHECKOUT_TYPE'].'/checkout.php');
							} else {
								require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/checkout/'.$this->ms['MODULES']['CHECKOUT_TYPE'].'/checkout.php');
							}
						}
						$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
						break;
					case 'create_account':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/create_account.php');
						break;
					case 'edit_account':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/edit_account.php');
						break;
					case 'store_locator':
						require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/store_locator.php');
						break;
					case 'order_history':
						if (mslib_fe::loggedin()) {
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['order_historyPreProc'])) {
								$params=array();
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['order_historyPreProc'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							if (strstr($this->ms['MODULES']['ORDER_HISTORY_TYPE'], "..")) {
								die('error in ORDER_HISTORY_TYPE value');
							} else {
								if (strstr($this->ms['MODULES']['ORDER_HISTORY_TYPE'], "/")) {
									// relative mode
									require($this->DOCUMENT_ROOT.$this->ms['MODULES']['ORDER_HISTORY_TYPE'].'.php');
								} else {
									require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/order_history/default.php');
								}
							}
						}
						$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
						break;
					case 'currency_selector':
						require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/front_pages/includes/content_elements/currency_selector.php');
						break;
					default:
						// more items could be added through hook
						$filePath='';
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['contentMisc'])) {
							$params=array(
								'contentMisc'=>&$this->contentMisc,
								'filePath'=>&$filePath,
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_multishop_pi1.php']['contentMisc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						if ($filePath) {
							require($filePath);
						}
						break;
				}
				break;
			case 'coreshop':
				require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/core.php');
				if ($this->conf['show_powered_by_multishop']) {
					$content.='
				<div class="align_center" id="typo3multishop_logo">
					<a href="https://www.typo3multishop.com/?utm_source=Typo3Website&utm_medium=cpc&utm_term=Typo3Multishop&utm_content=Listing&utm_campaign=Typo3Multishop" title="Powered by TYPO3 Multishop" target="_blank"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/powered_by_typo3multishop.png" border="0" alt="Powered by TYPO3 Multishop" title="Powered by TYPO3 Multishop"></a>
				</div>
				';
				}
				break;
		}
		if ($this->skipWrapInBase) {
			return $content;
		}
		if ($this->hideIfNoResults and $this->no_database_results) {
			// when the content element in TYPO3 has been configured with hideIfNoResults = true then hide the content if there is no data fetched from the database
			$this->cObj->data['header']='';
			$this->hideHeader=1;
			return '';
		}
		if ($this->showBoxless) {
			$this->hideHeader=1;
			return $this->pi_wrapInBaseClass($content);
		} elseif ($this->addBox or $this->box_class) {
			if ($this->hideHeader) {
				$this->cObj->data['header']='';
			}
			$content=mslib_fe::typobox($this->cObj->data['header'], $content, $this->box_class);
		} elseif (!$this->hideHeader) {
			if (!$this->hideIfNoResults or ($this->hideIfNoResults and !$this->no_database_results)) {
				$content=$this->cObj->cObjGetSingle($GLOBALS['TSFE']->tmpl->setup['lib.']['stdheader'], $GLOBALS['TSFE']->tmpl->setup['lib.']['stdheader.']).$content;
//				$content = $this->cObj->cObjGetSingle('<tt_content','') . $content;
			}
		}
		return $this->pi_wrapInBaseClass($content);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multishop/pi1/class.tx_multishop_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multishop/pi1/class.tx_multishop_pi1.php']);
}
?>