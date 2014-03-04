<?php
if (!defined('TYPO3_MODE')) die('Access denied.');
// when having a multi category based url get the deepest categories_id and save it as $this->get['categories_id']		
/*
if (is_array($this->get['categories_id'])) {
	$GLOBALS['categories_id_array']=$this->get['categories_id'];
	$this->get['categories_id']=max($this->get['categories_id']);	
}
*/
if (is_array($this->get['categories_id'])) {
	$GLOBALS['categories_id_array']=$this->get['categories_id'];
	for ($x=5;$x>=0;$x--) {
		if ($this->get['categories_id'][$x]) {
			$this->get['categories_id']=$this->get['categories_id'][$x];
			break;
		}
	}
}
$this->productsID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'productsID', 's_advanced');
$this->categoriesID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoriesID', 's_advanced');
$this->searchKeywordListing = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'searchKeywordListing', 's_advanced');
// if categoriesStartingPoint is defined through the template
if (is_numeric($this->conf['categoriesStartingPoint'])) {
	$this->categoriesStartingPoint=$this->conf['categoriesStartingPoint'];
}
// if categoriesStartingPoint is defined through flexform
$this->categoriesStartingPointFlexForm = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoriesStartingPoint', 's_advanced');
if ($this->categoriesStartingPointFlexForm) {
	$this->categoriesStartingPoint=$this->categoriesStartingPointFlexForm;
}
if (!is_numeric($this->categoriesStartingPoint)) {
	$this->categoriesStartingPoint=0;
}
if ($this->categoriesID and !$this->get['categories_id']) {
	$this->get['categories_id']=$this->categoriesID;
} elseif ($this->categoriesStartingPoint and !$this->get['categories_id']) {
	$this->get['categories_id']=$this->categoriesStartingPoint;
}
$this->showCatalogFromPage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showCatalogFromPage', 's_advanced');
if (!$this->showCatalogFromPage and $this->conf['catalog_shop_pid']) {
	$this->showCatalogFromPage=$this->conf['catalog_shop_pid'];
} elseif (!$this->showCatalogFromPage) {
	$this->showCatalogFromPage=$this->shop_pid;
}
$this->masterShop = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'masterShop', 's_advanced');
if (!$this->masterShop and $this->conf['masterShop']) {
	$this->masterShop=$this->conf['masterShop'];
}
// load shop info from tt_address
$this->tta_shop_info = mslib_fe::getAddressInfo();
$this->tta_user_info = false;
if (mslib_fe::loggedin()) {
	if (!is_numeric($this->conf['fe_admin_usergroup'])) {
		die("no admin group defined yet. Please add the admin usergroup_id to the constants field in the TYPO3 template.");
	}
	// load customer info from tt_address
	$this->tta_user_info = mslib_fe::getAddressInfo('customer', $GLOBALS['TSFE']->fe_user->user['uid']);
	$this->ADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_admin_usergroup']);
	if (!$this->conf['fe_rootadmin_usergroup']) {
		$this->ROOTADMIN_USER=$this->ADMIN_USER;
	} else {
		$this->ROOTADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_rootadmin_usergroup']);
	}	
	if ($this->ROOTADMIN_USER) {
		$this->ADMIN_USER=1;
		$this->CMSADMIN_USER=1;
		$this->CUSTOMERSADMIN_USER=1;
		$this->CMSADMIN_USER=1;
		$this->CATALOGADMIN_USER=1;
		$this->ORDERSADMIN_USER=1;
		$this->STORESADMIN_USER=1;
		$this->SEARCHADMIN_USER=1;
		$this->SYSTEMADMIN_USER=1;
		$this->STATISTICSADMIN_USER=1;
	} elseif($this->ADMIN_USER) {
		if (!$this->conf['fe_cmsadmin_usergroup']) {
			$this->conf['fe_cmsadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_customersadmin_usergroup']) {
			$this->conf['fe_customersadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_catalogadmin_usergroup']) {
			$this->conf['fe_catalogadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_ordersadmin_usergroup']) {
			$this->conf['fe_ordersadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_storesadmin_usergroup']) {
			$this->conf['fe_storesadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_cmsadmin_usergroup']) {
			$this->conf['fe_cmsadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_searchadmin_usergroup']) {
			$this->conf['fe_searchadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_systemadmin_usergroup']) {
			$this->conf['fe_systemadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		if (!$this->conf['fe_statisticsadmin_usergroup']) {
			$this->conf['fe_statisticsadmin_usergroup']=$this->conf['fe_admin_usergroup'];
		}
		$this->CMSADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_cmsadmin_usergroup']);		
		$this->CUSTOMERSADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_customersadmin_usergroup']);		
		$this->CATALOGADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_catalogadmin_usergroup']);		
		$this->ORDERSADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_ordersadmin_usergroup']);		
		$this->STORESADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_storesadmin_usergroup']);		
		$this->SEARCHADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_searchadmin_usergroup']);		
		$this->SYSTEMADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_systemadmin_usergroup']);		
		$this->STATISTICSADMIN_USER=mslib_fe::ifPermissioned($GLOBALS['TSFE']->fe_user->user['uid'],$this->conf['fe_statisticsadmin_usergroup']);		
	}
}
// define usergroups that should not be shown on the edit customer usergroups selectbox
$this->excluded_userGroups=array();
if ($this->conf['fe_customer_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_customer_usergroup'];
}
if ($this->conf['fe_admin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_admin_usergroup'];
}
if ($this->conf['fe_rootadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_rootadmin_usergroup'];
}
if ($this->conf['fe_cmsadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_cmsadmin_usergroup'];
}
if ($this->conf['fe_customersadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_customersadmin_usergroup'];
}
if ($this->conf['fe_catalogadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_catalogadmin_usergroup'];
}
if ($this->conf['fe_ordersadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_ordersadmin_usergroup'];
}
if ($this->conf['fe_storesadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_storesadmin_usergroup'];
}
if ($this->conf['fe_searchadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_searchadmin_usergroup'];
}
if ($this->conf['fe_systemadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_systemadmin_usergroup'];
}
if ($this->conf['fe_statisticsadmin_usergroup']) {
	$this->excluded_userGroups[]=$this->conf['fe_statisticsadmin_usergroup'];
}
if ($this->conf['cacheConfiguration'] == '1') {
	$this->ms['MODULES']['CACHE_FRONT_END']=1;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$string='loadConfiguration_'.$this->HTTP_HOST.'_'.$this->shop_pid.'_'.$this->cObj->data['uid'].'_'.md5(serialize($this->conf));
}
$lifetime=36000;
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$tmp=mslib_befe::cacheLite('get',$string,$lifetime,1))) {
	$tmp=mslib_befe::loadConfiguration($this->shop_pid);
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		mslib_befe::cacheLite('save',$string,$lifetime,1,$tmp);
	}
}
$this->ms['MODULES']=$tmp;
// make sure the page title always have default delimeter value, when config not yet updated
if (!isset($this->ms['MODULES']['PAGE_TITLE_DELIMETER'])) {
	$this->ms['MODULES']['PAGE_TITLE_DELIMETER'] = ' :: ';
}
$this->ms=mslib_befe::convertConfiguration($this->ms);
//if ($this->ms['MODULES']['CACHE_FRONT_END'] or $this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'])	require_once(t3lib_extMgm::extPath('multishop').'res/Cache_Lite-1.7.8/Lite.php');
if ($this->ADMIN_USER) {
	// load enabled languages
	$enabled_countries=array();
	if ($this->ms['MODULES']['ENABLED_LANGUAGES']) {
		// if this setting is defined we must not enable all TYPO3 languages, but only the ones that are defined in this variable
		$this->ms['MODULES']['ENABLED_LANGUAGES']=t3lib_div::strtoupper($this->ms['MODULES']['ENABLED_LANGUAGES']);
		if (strstr($this->ms['MODULES']['ENABLED_LANGUAGES']," ")) $this->ms['MODULES']['ENABLED_LANGUAGES']=str_replace(" ","",$this->ms['MODULES']['ENABLED_LANGUAGES']);
		$enabled_countries=explode(",",$this->ms['MODULES']['ENABLED_LANGUAGES']);
	}
	$this->languages=array();
	$str="select sl.flag, sl.uid, sl.title, sli.lg_iso_2 from sys_language sl, static_languages sli where sl.hidden=0 and sl.static_lang_isocode=sli.uid";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($language=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		if ((count($enabled_countries) and (in_array($language['lg_iso_2'],$enabled_countries))) or (!count($enabled_countries))) {
			$this->languages[$language['uid']]=$language;
		}
	}
	// add default language id 0
	$this->languages[0]['uid']	=0;
	$this->languages[0]['title']	=htmlspecialchars($this->pi_getLL('default_language'));
	ksort($this->languages);	
	// load enabled languages eof
}
$this->addBox 					= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'addBox', 'sDEFAULT');
$this->hideHeader 				= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'hideHeader', 'sDEFAULT');
$this->hidePagination 			= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'hidePagination', 'sDEFAULT');
$this->productsLimit 			= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'productsLimit', 'sDEFAULT');
$this->hideIfNoResults 			= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'hideIfNoResults', 'sDEFAULT');
// FlexForm custom settings
$this->customSettings 			= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'customSettings', 's_advanced');
$this->disableMetatags			= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disableMetatags', 's_advanced');
if ($this->disableMetatags) {
	$this->conf['disableMetatags']=1;
}
// TypoScript custom settings
if ($this->conf['customSettings']=='{$plugin.multishop.customSettings}') {
	$this->conf['customSettings']='';
}
if ($this->conf['customSettings']) {
	if ($this->customSettings) {
		$this->customSettings.="\n";
	}
	$this->customSettings.=$this->conf['customSettings'];
}
$this->imageWidth = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageWidth', 's_specials');
if (!$this->custom_script) {
	$this->custom_script = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'custom_script', 's_misc');
}
if ($this->conf['custom_script']) $this->custom_script=$this->conf['custom_script'];
if ($this->custom_script) {
	if (preg_match("/\.php$/",$this->custom_script)) {
		$this->custom_script=preg_replace("/\.php$/","",$this->custom_script);
	}
	if ($this->custom_script) {
		if (strstr($this->custom_script,"..")) {
			die('error in custom script path: ('.htmlspecialchars($this->custom_script).')');
		} else {
			if (strstr($this->custom_script,"/")) {
				$this->custom_script_location=$this->DOCUMENT_ROOT.$this->custom_script.'.php';
				$this->method='custom_script';
			}
		}		
	}	
}
if ($GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar']) {
	// overwrite multishop settings loaded from the categories
	$tmpcats=array_reverse($GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar']);
	foreach ($tmpcats as $tmpitem) {
		if ($tmpitem['custom_settings']) {
			mslib_fe::updateCustomSettings($tmpitem['custom_settings']);
		}
	}
}
if (is_numeric($this->get['products_id'])) {
	// overwrite multishop settings loaded from the product
	$product=mslib_fe::getProduct($this->get['products_id'],$this->get['categories_id'],'p.custom_settings',1);
	if ($product['custom_settings']) mslib_fe::updateCustomSettings($product['custom_settings']);
}
// overwrite multishop settings loaded from the content element
if ($this->customSettings) {
	mslib_fe::updateCustomSettings($this->customSettings);
}
// overwrite multishop settings loaded from the content element eof
if (!$this->conf['admin_template_folder']) {
	$this->conf['admin_template_folder'] = 'admin_multishop';
}
// reset the fileadmin admin folder to local plugin location
if (!$this->conf['search_page_pid']) {
	$this->conf['search_page_pid'] = $this->shop_pid;
}
if (!$this->conf['shoppingcart_page_pid']) {
	$this->conf['shoppingcart_page_pid'] = $this->shop_pid;
}
if (!$this->conf['products_detail_page_pid']) {
	$this->conf['products_detail_page_pid'] = $this->shop_pid;
}
if (!$this->conf['products_listing_page_pid']) {
	$this->conf['products_listing_page_pid'] = $this->shop_pid;
}
if (!$this->conf['checkout_page_pid']) {
	$this->conf['checkout_page_pid'] = $this->shop_pid;
}
if (!isset($this->conf['includejCarousel'])) {
	$this->conf['includejCarousel'] = '1';
}
if (!isset($this->conf['includejAutocomplete'])) {
	$this->conf['includejAutocomplete'] = '1';
}
if (!$this->conf['highslide_folder']) {
	$this->conf['highslide_folder'] = 'highslide';
}
if (!$this->conf['admin_help_url']) {
	$this->conf['admin_help_url'] = 'http://www.typo3multishop.com';
}
if (!$this->conf['admin_development_company_url']) {
	$this->conf['admin_development_company_url'] = 'http://www.typo3multishop.com';
}
if (!$this->conf['admin_development_company_name']) {
	$this->conf['admin_development_company_name'] = 'TYPO3 Multishop';
}
if (!$this->conf['admin_development_company_logo']) {
	$this->conf['admin_development_company_logo'] = t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_multishop/images/admin_logo.gif';
}
if (!$this->conf['admin_development_company_logo_gray_path']) {
	$this->conf['admin_development_company_logo_gray_path']	= t3lib_extMgm::siteRelPath($this->extKey).'templates/images/powered_by_typo3multishop_gray.png';
}
if ($this->conf['cart_uid']) {
	$this->ms['MODULES']['CART_PAGE_UID'] = $this->conf['cart_uid'];
}
$key='';
if ($this->ms['MODULES']['CART_PAGE_UID']) {
	$key='_'.$this->ms['MODULES']['CART_PAGE_UID'];		
}
$this->cart_page_uid='tx_multishop_cart'.$key;
if ($this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS'] and !$this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS']) {
	// one time load for the attribute option names. When we have to add or update products to the flat table we already know the attribute option column names, so this way it requires less running queries
	$lifetime=36000;
	$string='flat_database_extra_attribute_options_'.$this->shop_pid.'_'.$this->cObj->data['uid'];
	if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$flat_database_extra_attribute_options=mslib_befe::cacheLite('get',$string,$lifetime,1))) {
		$flat_database_extra_attribute_options=array();	
		$array=explode(";",$this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS']);
		foreach ($array as $row) {
			$item=explode(":",$row);
			if (is_numeric($item[0])) {
				$field_name="a_".str_replace("-","_",mslib_fe::rewritenamein(mslib_fe::getProductsOptionName($item[0])));			
				if ($field_name) {
					$flat_database_extra_attribute_options[$item[0]]=array(0=>$field_name,1=>$item[1]);
				}
			}
		}		
		if ($this->ms['MODULES']['CACHE_FRONT_END']) {
			mslib_befe::cacheLite('save',$string,$lifetime,1,$flat_database_extra_attribute_options);
		}
	}
	$this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS']=$flat_database_extra_attribute_options;
}
if(!$this->conf['disableMetatags']) {		
	if ($this->ms['MODULES']['META_TITLE'] and !$GLOBALS['TSFE']->additionalHeaderData['title']) $GLOBALS['TSFE']->additionalHeaderData['title'] = '<title>'.htmlspecialchars($this->ms['MODULES']['META_TITLE']).'</title>';	
	if ($this->ms['MODULES']['META_DESCRIPTION'] and !$GLOBALS['TSFE']->additionalHeaderData['description']) $GLOBALS['TSFE']->additionalHeaderData['description'] 		= '<meta name="description" content="'.htmlspecialchars($this->ms['MODULES']['META_DESCRIPTION']).'" />';		
}
// if cache module is enabled and a admin is logged in temporary disable the caching module
if ($this->ms['MODULES']['CACHE_FRONT_END'] and $this->ADMIN_USER) $this->ms['MODULES']['CACHE_FRONT_END']=0;		
$format=explode("x",$this->ms['MODULES']['CATEGORY_IMAGE_SIZE_NORMAL']);
$this->ms['category_image_formats']['normal']['width'] = $format[0];
$this->ms['category_image_formats']['normal']['height']	= $format[1];
$format=explode("x",$this->ms['MODULES']['PRODUCT_IMAGE_SIZE_50']);
$this->ms['product_image_formats'][50]['width'] = $format[0];
$this->ms['product_image_formats'][50]['height'] = $format[1];
$format=explode("x",$this->ms['MODULES']['PRODUCT_IMAGE_SIZE_100']);
$this->ms['product_image_formats'][100]['width'] = $format[0];
$this->ms['product_image_formats'][100]['height'] = $format[1];
$format=explode("x",$this->ms['MODULES']['PRODUCT_IMAGE_SIZE_200']);
$this->ms['product_image_formats'][200]['width'] = $format[0];
$this->ms['product_image_formats'][200]['height'] = $format[1];
$format=explode("x",$this->ms['MODULES']['PRODUCT_IMAGE_SIZE_300']);
$this->ms['product_image_formats'][300]['width'] = $format[0];
$this->ms['product_image_formats'][300]['height'] = $format[1];
$format=explode("x",$this->ms['MODULES']['PRODUCT_IMAGE_SIZE_ENLARGED']);
$this->ms['product_image_formats']['enlarged']['width']	= $format[0];
$this->ms['product_image_formats']['enlarged']['height'] = $format[1];
$format=explode("x",$this->ms['MODULES']['MANUFACTURER_IMAGE_SIZE_NORMAL']);
$this->ms['manufacturer_image_formats']['enlarged']['width'] = $format[0];
$this->ms['manufacturer_image_formats']['enlarged']['height'] = $format[1];
?>