<?php
// Example TypoScript (mixed tmenu with Multishop menu on specific menu item):
/*
# Custom tmenu with multishop tmenu on Shop pid
lib.msTopNavBottom.tmenu.1.NO.after.cObject=COA
lib.msTopNavBottom.tmenu.1.NO.after.cObject {
10=COA
10 {
  20 < lib.defaultMobileMenu
  20.special = userfunction
  20.special.userFunc =user_msMenuFunc->makeHmenuArray
  20.special.userFunc.conf < plugin.tx_multishop_pi1
  stdWrap.if.value.field = uid
  stdWrap.if.equals = 10
}
if.value.field = uid
if.equals = 10
}
}
*/
class user_msMenuFunc extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	// Create tmenu object for categories menu
	function makeHmenuArray($content,$conf) {
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'pi1/classes/class.mslib_befe.php');
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'pi1/classes/class.mslib_fe.php');
		$this->conf=$conf['userFunc.']['conf.'];
		if (!is_numeric($this->conf['categoriesStartingPoint'])) {
			$this->conf['categoriesStartingPoint']=0;
		}
		if (!isset($GLOBALS['TSFE']->config['config']['sys_language_uid'])) {
			$GLOBALS['TSFE']->config['config']['sys_language_uid']=0;
		}
		$this->sys_language_uid=$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$this->categoriesStartingPoint=$this->conf['categoriesStartingPoint'];
		if (!is_numeric($this->conf['catalog_shop_pid']) or $this->conf['catalog_shop_pid']==0) {
			$this->conf['catalog_shop_pid']=$this->conf['shop_pid'];
		}
		$this->showCatalogFromPage=$this->conf['catalog_shop_pid'];
		$cats=mslib_fe::getSubcatsOnly($this->categoriesStartingPoint,0,$this->conf['catalog_shop_pid'],0);
		$menuArr=array();
		$tel=0;
		foreach ($cats as $cat) {
			$menuArr[$tel]['title']=$cat['categories_name'];
			$menuArr[$tel]['uid']='9999'.$cat['categories_id'];
			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($cat['categories_id']);
			$cats=array_reverse($cats);
			$where='';
			if (count($cats)>0) {
				foreach ($cats as $tmp) {
					$where.="categories_id[".$level."]=".$tmp['id']."&";
					$level++;
				}
				$where=substr($where, 0, (strlen($where)-1));
			}
			$link=mslib_fe::typolink($this->conf['shop_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
			$menuArr[$tel]['_OVERRIDE_HREF']=$link;
			if ($error=$GLOBALS['TYPO3_DB']->sql_error()) {
				$GLOBALS['TT']->setTSlogMessage($error, 3);
			} else {
				$dataArray=mslib_fe::getSitemap($cat['categories_id'], array(), 0, 0);
				$menuArr[$tel]['_SUB_MENU']=array();
				if (count($dataArray)) {
					$sub_content=self::subMenuArray($dataArray);
					$menuArr[$tel]['_SUB_MENU']=$sub_content;
				}
			}
			$tel++;
		}
		return $menuArr;
	}
	// Create tmenu object for categories menu
	function makeManufacturersHmenuArray($content,$conf) {
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'pi1/classes/class.mslib_befe.php');
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'pi1/classes/class.mslib_fe.php');
		$this->conf=$conf['userFunc.']['conf.'];
		if (!isset($GLOBALS['TSFE']->config['config']['sys_language_uid'])) {
			$GLOBALS['TSFE']->config['config']['sys_language_uid']=0;
		}
		$this->sys_language_uid=$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$this->categoriesStartingPoint=$this->conf['categoriesStartingPoint'];
		if (!is_numeric($this->conf['catalog_shop_pid']) or $this->conf['catalog_shop_pid']==0) {
			$this->conf['catalog_shop_pid']=$this->conf['shop_pid'];
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('m.manufacturers_id, m.manufacturers_name, m.manufacturers_image', // SELECT ...
				'tx_multishop_manufacturers m', // FROM ...
				'm.status=1', // WHERE...
				'', // GROUP BY...
				'm.sort_order', // ORDER BY...
				'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$menuArr=array();
		$tel=0;
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$menuArr[$tel]['title']=$row['manufacturers_name'];
			$menuArr[$tel]['uid']='9999'.$row['manufacturers_id'];
			$link=mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id='.$row['manufacturers_id']);

			$menuArr[$tel]['_OVERRIDE_HREF']=$link;
			if ($error=$GLOBALS['TYPO3_DB']->sql_error()) {
				$GLOBALS['TT']->setTSlogMessage($error, 3);
			} else {
				$dataArray=mslib_fe::getSitemap($row['categories_id'], array(), 0, 0);
				$menuArr[$tel]['_SUB_MENU']=array();
				if (count($dataArray)) {
					$sub_content=self::subMenuArray($dataArray);
					$menuArr[$tel]['_SUB_MENU']=$sub_content;
				}
			}
			$tel++;
		}
		return $menuArr;
	}
	function itemArrayProcFunc($menuArr, $conf) {
		require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop').'pi1/classes/class.mslib_fe.php');
		$this->conf=$conf;
		if (!is_numeric($this->conf['categoriesStartingPoint'])) {
			$this->conf['categoriesStartingPoint']=0;
		}
		if (!isset($GLOBALS['TSFE']->config['config']['sys_language_uid'])) {
			$GLOBALS['TSFE']->config['config']['sys_language_uid']=0;
		}
		$this->sys_language_uid=$GLOBALS['TSFE']->config['config']['sys_language_uid'];
		$this->categoriesStartingPoint=$this->conf['categoriesStartingPoint'];
		if (!is_numeric($this->conf['catalog_shop_pid']) or $this->conf['catalog_shop_pid']==0) {
			$this->conf['catalog_shop_pid']=$this->conf['shop_pid'];
		}
		$this->showCatalogFromPage=$this->conf['catalog_shop_pid'];
		if (is_array($menuArr) and count($menuArr)) {
			foreach ($menuArr as $key=>$item) {
				if ($item['uid']==$this->conf['attachMultishopCategoriesOnPid']) {
					// we are now on the menu item that we have to extend with catalog menu items
					$catalog=user_msMenuFunc::makeMenuArray();
					$menuArr[$key]['_SUB_MENU']=$catalog;
					break;
				}
			}
		}
		/*
				$parentPageId = $conf['parentObj']->id;	// id of the parent page
				if ($conf['demoItemStates'])	{		// Used in the example of item states
					$c=0;
					$teststates=explode(',','NO,ACT,IFSUB,CUR,USR,SPC,USERDEF1,USERDEF2');
					foreach ($menuArr as $k => $v) {
						$menuArr[$k]['ITEM_STATE']=$teststates[$c];
						$menuArr[$k]['title'].= ($teststates[$c] ? ' ['.$teststates[$c].']' : '');
						$c++;
					}
				} else {	// used in the fake menu item example!
					if (!count($menuArr))	{		// There must be no menu items if we add the parent page to the submenu:
						$parentPageId = $conf['parentObj']->id;	// id of the parent page
						$parentPageRow = $GLOBALS['TSFE']->sys_page->getPage($parentPageId);	// ... and get the record...
						if (is_array($parentPageRow))	{	// ... and if that page existed (a row was returned) then add it!
							$menuArr[]=$parentPageRow;
						}
					}
				}
		*/
		return $menuArr;
	}
	function makeMenuArray() {
		$cats=mslib_fe::getSubcatsOnly($this->categoriesStartingPoint,0,$this->conf['catalog_shop_pid'],0);
		$menuArr=array();
		$tel=0;
		foreach ($cats as $cat) {
			$menuArr[$tel]['title']=$cat['categories_name'];
			$menuArr[$tel]['uid']='9999'.$cat['categories_id'];
			$menuArr[$tel]['description']=$cat['meta_description'];
			$menuArr[$tel]['image']='';
			if ($cat['categories_image']) {
				$menuArr[$tel]['image']='<img src="uploads/tx_multishop/images/categories/normal'.mslib_befe::getImagePath($cat['categories_image'], 'categories', 'normal').'" alt="'.htmlspecialchars($cat['categories_name']).'">';
			}

			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($cat['categories_id']);
			$cats=array_reverse($cats);
			$where='';
			if (count($cats)>0) {
				foreach ($cats as $tmp) {
					$where.="categories_id[".$level."]=".$tmp['id']."&";
					$level++;
				}
				$where=substr($where, 0, (strlen($where)-1));
			}
			$link=mslib_fe::typolink($this->conf['shop_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
			$menuArr[$tel]['_OVERRIDE_HREF']=$link;
			if ($error=$GLOBALS['TYPO3_DB']->sql_error()) {
				$GLOBALS['TT']->setTSlogMessage($error, 3);
			} else {
				$dataArray=mslib_fe::getSitemap($cat['categories_id'], array(), 0, 0);
				$menuArr[$tel]['_SUB_MENU']=array();
				if (count($dataArray)) {
					$sub_content=self::subMenuArray($dataArray);
					$menuArr[$tel]['_SUB_MENU']=$sub_content;
				}
			}
			$tel++;
		}
		return $menuArr;
	}
	function subMenuArray($dataArray) {
		if (count($dataArray['subs'])) {
			$tel=0;
			foreach ($dataArray['subs'] as $item) {
				$menuArr[$tel]['title']=$item['categories_name'];
				$menuArr[$tel]['uid']='9999'.$item['categories_id'];
				$menuArr[$tel]['description']=$item['meta_description'];
				$menuArr[$tel]['image']='';
				if ($item['categories_image']) {
					$menuArr[$tel]['image']='<img src="uploads/tx_multishop/images/categories/normal'.mslib_befe::getImagePath($item['categories_image'], 'categories', 'normal').'" alt="'.htmlspecialchars($item['categories_name']).'">';
				}
				// get all cats to generate multilevel fake url
				$level=0;
				$cats=mslib_fe::Crumbar($item['categories_id']);
				$cats=array_reverse($cats);
				$where='';
				if (count($cats)>0) {
					foreach ($cats as $tmp) {
						$where.="categories_id[".$level."]=".$tmp['id']."&";
						$level++;
					}
					$where=substr($where, 0, (strlen($where)-1));
				}
				$link=mslib_fe::typolink($this->conf['shop_pid'], $where.'&tx_multishop_pi1[page_section]=products_listing');
				$menuArr[$tel]['_OVERRIDE_HREF']=$link;
				$sub_content=$this->subMenuArray($item);
				if ($sub_content) {
					$menuArr[$tel]['_SUB_MENU']=$sub_content;
				}
				$tel++;
			}
		}
		return $menuArr;
	}

	/**
	 * Used in the menu item state example of the "testsite" package at page-path "/Intro/TypoScript examples/Menu object examples/Menu state test/"
	 * @param    array        The menu item array, $this->I (in the parent object)
	 * @param    array        TypoScript configuration for the function. Notice that the property "parentObj" is a reference to the parent (calling) object (the tslib_Xmenu class instantiated)
	 * @return    array        The processed $I array returned (and stored in $this->I of the parent object again)
	 * @see tslib_menu::userProcess(), tslib_tmenu::writeMenu(), tslib_gmenu::writeMenu()
	 */
	/*
	function IProcFuncTest($I,$conf)	{
		echo 'test';
		print_r($menuArr);
		die();
		$itemRow = $conf['parentObj']->menuArr[$I['key']];
	
			// Setting the document status content to the value of the page title on mouse over
		$I['linkHREF']['onMouseover'].='extraRollover(\''.rawurlencode($itemRow['title']).'\');';
		$conf['parentObj']->I = $I;
		$conf['parentObj']->setATagParts();
		$I = $conf['parentObj']->I;
		if ($I['parts']['ATag_begin'])	$I['parts']['ATag_begin']=$I['A1'];
	
		if ($conf['debug'])	{
				// Outputting for debug example:
			echo 'ITEM: <h2>'.htmlspecialchars($itemRow['uid'].': '.$itemRow['title']).'</h2>';
			t3lib_utility_Debug::debug($itemRow);
			t3lib_utility_Debug::debug($I);
			echo '<hr />';
		}
			// Returns:
		return $I;
	}
	*/
}
?>