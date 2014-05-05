<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
/***************************************************************
 *  Copyright notice
 *  (c) 2011 Bas van Beek <bas@bvbmedia.nl>
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
 *   46: class tx_multishop_cms_layout
 *   54:     function getExtensionSummary($params, &$pObj)
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")

 */
/**
 * Hook to display verbose information about pi1 plugin in Web>Page module
 * @author    Bas van Beek <bas@bvbmedia.nl>
 * @package    TYPO3
 * @subpackage    tx_multishop
 */
class tx_multishop {
	/**
	 * Function called from TV, used to generate preview of this plugin
	 * @param  array $row :        tt_content table row
	 * @param  string $table :      usually tt_content
	 * @param  bool $alreadyRendered :  To let TV know we have successfully rendered a preview
	 * @param object $reference tx_templavoila_module1
	 * @return string  $content
	 */
	public function renderPreviewContent_preProcess($row, $table, &$alreadyRendered, &$reference) {
		if ($row['CType']==='list' && $row['list_type']==='multishop_pi1') {
			$content=$this->preview($row);
			$alreadyRendered=true;
			return $content;
		}
	}
	/**
	 * Function called from page view, used to generate preview of this plugin
	 * @param  array $params :  flexform params
	 * @param  array $pObj :    parent object
	 * @return string  $result:  the hghlighted text
	 */
	public function getExtensionSummary($params, &$pObj) {
		if ($params['row']['CType']==='list' && $params['row']['list_type']==='multishop_pi1') {
			$content=$this->preview($params['row']);
			return $content;
		}
	}
	/**
	 * Flattens an array, or returns FALSE on fail.
	 */
	function array_flatten($array) {
		if (!is_array($array)) {
			return false;
		}
		$result=array();
		foreach ($array as $key=>$value) {
			if (is_array($value)) {
				$result=array_merge($result, $this->array_flatten($value));
			} else {
				$result[$key]=$value;
			}
		}
		return $result;
	}
	/**
	 * Render the preview
	 * @param array $row tt_content row of the plugin
	 * @return string rendered preview html
	 */
	protected function preview($row) {
		$data=t3lib_div::xml2array($row['pi_flexform']);
		$selectedMethod=$data['data']['sDEFAULT']['lDEF']['method']['vDEF'];
		if ($selectedMethod) {
			$content='Multishop: '.$data['data']['sDEFAULT']['lDEF']['method']['vDEF'].'<br />';
			$methodToTabArray=array();
			$methodToTabArray['categories']='s_listing';
			$methodToTabArray['products']='s_products_listing';
			$methodToTabArray['search']='s_search';
			$methodToTabArray['specials']='s_specials';
//manufacturers			
			$methodToTabArray['misc']='s_misc';
//			$methodToTabArray[]='s_advanced';	
			if ($methodToTabArray[$selectedMethod]) {
				foreach ($data['data'][$methodToTabArray[$selectedMethod]]['lDEF'] as $key=>$valArray) {
					if (isset($valArray['vDEF']) and $valArray['vDEF']!='') {
						$subContent.=$key.': '.$valArray['vDEF'].'<br />';
					}
				}
			}
			if ($subContent) {
				$content.='<br />'.$subContent;
			}
		}
		return $content;
	}
}
?>