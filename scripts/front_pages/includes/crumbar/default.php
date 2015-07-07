<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
// now parse all the objects in the tmpl file
if ($this->conf['crumbar_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['crumbar_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/crumbar.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
$lifetime=3600;
$string='crumbar_'.$this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$tmp=mslib_befe::cacheLite('get', $string, $lifetime, 1))) {
	// code here
	if ($this->get['products_id']) {
		$product=mslib_fe::getProduct($this->get['products_id'], $this->get['categories_id']);
	}
	if ($this->get['categories_id']) {
		$categories_id=$this->get['categories_id'];
	} else {
		if ($product['categories_id']) {
			$categories_id=$product['categories_id'];
		}
	}
	if (!$categories_id && $this->get['manufacturers_id'] && is_numeric($this->get['manufacturers_id'])) {
		$manufacturers_id=$this->get['manufacturers_id'];
	}
	if ($categories_id || $manufacturers_id) {
		$output['label_you_are_currently_here']=$this->pi_getLL('you_are_currently_here');
		if ($this->conf['crumbar_rootline_pid'] && is_numeric($this->conf['crumbar_rootline_pid'])) {
			$output['homepage_link']=mslib_fe::typolink($this->conf['crumbar_rootline_pid']);
		} else {
			$output['homepage_link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=home');
		}
		if ($this->conf['crumbar_rootline_title']) {
			$output['homepage_title']=$this->conf['crumbar_rootline_title'];
		} else {
			$record=mslib_befe::getRecord($this->shop_pid, 'pages', 'uid');
			$GLOBALS['TSFE']->sys_page->getRecordOverlay('pages', $record, $this->lang);
			$output['homepage_title']=$record['title'];
		}
		if (is_numeric($manufacturers_id)) {
			$strCms=$GLOBALS ['TYPO3_DB']->SELECTquery('m.manufacturers_name', // SELECT ...
				'tx_multishop_manufacturers m', // FROM ...
				"m.manufacturers_id='".$manufacturers_id."' AND m.status=1", // WHERE.
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qryCms=$GLOBALS['TYPO3_DB']->sql_query($strCms);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qryCms)) {
				$rowCms=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qryCms);
				$contentItem='';
				$output['product_name']='<li class="crumbar_product_level"><strong>'.$rowCms['manufacturers_name'].'</strong></li>';
			}
		} elseif ($categories_id) {
			if ($GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar']) {
				$cats=$GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar'];
			} else {
				$cats=mslib_fe::Crumbar($categories_id);
			}
			$contentItem='';
			$teller=0;
			$tree_idx_start=count($cats)-1;
			for ($i=$tree_idx_start; $i>=0; $i--) {
				if ($cats[$i]['id']!=$this->categoriesStartingPoint) {
					$teller++;
					$link='';
					if (($cats[$i]['id']!=$categories_id) or $this->get['products_id']) {
						$cats2=array();
						$cats2=mslib_fe::Crumbar($cats[$i]['id']);
						$cats2=array_reverse($cats2);
						$where='';
						$level=0;
						if (count($cats2)>0) {
							foreach ($cats2 as $item) {
								$where.="categories_id[".$level."]=".$item['id']."&";
								$level++;
							}
							$where=substr($where, 0, (strlen($where)-1));
							$where.='&';
						} else {
							$where.='categories_id['.$level.']='.$cats[$i]['id'];
						}
						// get all cats to generate multilevel fake url eof
						$output['link']=mslib_fe::typolink($this->conf['products_listing_page_pid'], $where.'&tx_multishop_pi1[page_section]=products_listing');
					}
					if (!$cats[$i]['meta_description']) {
						$cats[$i]['meta_description']=$cats[$i]['name'];
					}
					$output['level_counter']=$teller;
					if ($i<1 && empty($product['products_name'])) {
						$output['link']='';
					}
					if ($output['link']) {
						$output['crumbar_value']='<a href="'.$output['link'].'" class="ajax_link" title="'.htmlspecialchars($cats[$i]['meta_description']).'">'.$cats[$i]['name'].'</a>';
					} else {
						$output['crumbar_value']='<strong>'.$cats[$i]['name'].'</strong>';
					}
					$markerArray=array();
					$markerArray['LEVEL_COUNTER']=$output['level_counter'];
					$markerArray['CRUMBAR_VALUE']=$output['crumbar_value'];
					$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
				}
			}
			if (!empty($product['products_name'])) {
				$output['product_name']='<li class="crumbar_product_level"><strong>'.$product['products_name'].'</strong></li>';
			}
		}
		// fill the row marker with the expanded rows
		$subpartArray['###LABEL_YOU_ARE_CURRENTLY_HERE###']=$output['label_you_are_currently_here'];
		$subpartArray['###HOMEPAGE_LINK###']=$output['homepage_link'];
		$subpartArray['###HOMEPAGE_TITLE###']=$output['homepage_title'];
		$subpartArray['###PRODUCT_NAME###']=$output['product_name'];
		$subpartArray['###ITEM###']=$contentItem;
		$crum=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
		// completed the template expansion by replacing the "item" marker in the template
	}
	// code eof	
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$tmp=array();
		$tmp['crum']=$crum;
		$tmp['product']=$product;
		$tmp['cats']=$cats;
		mslib_befe::cacheLite('save', $string, $lifetime, 1, $tmp);
	}
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$crum=$tmp['crum'];
	$product=$tmp['product'];
	$cats=$tmp['cats'];
}
?>