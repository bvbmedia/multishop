<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
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
class tx_mslib_admin_interface extends tslib_pibase {
	function renderInterface($params, &$that) {
		// for pagination
		$this->get=$that->get;
		$updateCookie=0;
		if ($that->get['Search'] and ($that->get['limit']!=$that->cookie['limit'])) {
			$that->cookie['limit']=$that->get['limit'];
			$updateCookie=1;
		}
		if ($that->get['Search'] and ($that->get['display_all_records']!=$that->cookie['display_all_records'])) {
			$that->cookie['display_all_records']=$that->get['display_all_records'];
			$updateCookie=1;
		}
		if ($updateCookie) {
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $that->cookie);
			$GLOBALS['TSFE']->storeSessionData();
		}
		if ($that->cookie['display_all_records']) {
			$that->get['display_all_records']=$that->cookie['display_all_records'];
		} else {
			$that->get['display_all_records']='';
		}
		if ($that->cookie['limit']) {
			$that->get['limit']=$that->cookie['limit'];
		} else {
			$that->get['limit']=50;
		}
		$that->ms['MODULES']['PAGESET_LIMIT']=$that->get['limit'];
		if (is_numeric($that->get['p'])) {
			$p=$that->get['p'];
		}
		$that->searchKeywords=array();
		if ($that->get['tx_multishop_pi1']['keyword']) {
			//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
			$that->get['tx_multishop_pi1']['keyword']=trim($that->get['tx_multishop_pi1']['keyword']);
			$that->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($that->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
			$that->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($that->get['tx_multishop_pi1']['keyword'], true);
			$that->get['tx_multishop_pi1']['keyword']=mslib_fe::RemoveXSS($that->get['tx_multishop_pi1']['keyword']);
			$that->searchKeywords[]=$that->get['tx_multishop_pi1']['keyword'];
			$that->searchMode='%keyword%';
		}
		$limit_search_result_selectbox='<label>'.$that->pi_getLL('limit_number_of_records_to').':</label><select name="limit">';
		$limits=array();
		$limits[]='10';
		$limits[]='15';
		$limits[]='20';
		$limits[]='25';
		$limits[]='30';
		$limits[]='40';
		$limits[]='50';
		$limits[]='100';
		$limits[]='150';
		$limits[]='200';
		$limits[]='250';
		$limits[]='300';
		$limits[]='350';
		$limits[]='400';
		$limits[]='450';
		$limits[]='500';
		foreach ($limits as $limit) {
			$limit_search_result_selectbox.='<option value="'.$limit.'"'.($limit==$that->get['limit'] ? ' selected="selected"' : '').'>'.$limit.'</option>';
		}
		$limit_search_result_selectbox.='</select>';
		$queryData=array();
		$queryData['where']=array();
		if (count($that->searchKeywords)) {
			$keywordOr=array();
			$that->searchMode='%keyword%';
			foreach ($that->searchKeywords as $searchKeyword) {
				if ($searchKeyword) {
					switch ($that->searchMode) {
						case 'keyword%':
							$that->sqlKeyword=addslashes($searchKeyword).'%';
							break;
						case '%keyword%':
						default:
							$that->sqlKeyword='%'.addslashes($searchKeyword).'%';
							break;
					}
					if (is_array($params['query']['keywordSearchByColumns']) && count($params['query']['keywordSearchByColumns'])) {
						foreach ($params['query']['keywordSearchByColumns'] as $col) {
							$keywordOr[]=$col." like '".$that->sqlKeyword."'";
						}
					}
				}
			}
			$queryData['where'][]="(".implode(" OR ", $keywordOr).")";
		}
		switch ($that->get['tx_multishop_pi1']['order_by']) {
			default:
				if (is_array($params['query']['defaultOrderByColumns']) && count($params['query']['defaultOrderByColumns'])) {
					$order_by=implode(',', $params['query']['defaultOrderByColumns']);
				}
				break;
		}
		switch ($that->get['tx_multishop_pi1']['order']) {
			case 'a':
				$order='asc';
				$order_link='d';
				break;
			case 'd':
				$order='desc';
				$order_link='a';
				break;
			default:
				if ($params['query']['defaultOrder']=='asc') {
					$order='asc';
					$order_link='d';
				} else {
					$order='desc';
					$order_link='a';
				}
				break;
		}
		$orderby[]=$order_by.' '.$order;
		if (is_array($params['query']['select'])) {
			$queryData['select']=implode(',', $params['query']['select']);
		} else {
			$queryData['select']=$params['query']['select'];
		}
		if (is_array($params['query']['from'])) {
			$queryData['from']=implode(',', $params['query']['from']);
		} else {
			$queryData['from']=$params['query']['from'];
		}
		$queryData['order_by']=$orderby;
		$queryData['limit']=$that->ms['MODULES']['PAGESET_LIMIT'];
		if (is_numeric($that->get['p'])) {
			$p=$that->get['p'];
		}
		if ($p>0) {
			$queryData['offset']=(((($p)*$that->ms['MODULES']['PAGESET_LIMIT'])));
		} else {
			$p=0;
			$queryData['offset']=0;
		}
		$pageset=mslib_fe::getRecordsPageSet($queryData);
		if (count($pageset['dataset'])) {
			$tr_type='even';
			$tableContent.='
			<div class="msHorizontalOverflowWrapper">
			<form method="post" action="'.$params['postForm']['actionUrl'].'" enctype="multipart/form-data">
			<table class="msZebraTable msadmin_orders_listing" id="product_import_table">';
			$tableContent.='<tr>';
			foreach ($params['tableColumns'] as $col=>$valArray) {
				$tableContent.='<th'.($valArray['align'] ? ' align="'.$valArray['align'].'"' : '').'>'.$valArray['title'].'</th>';
			}
			$tableContent.='
			<th>'.$that->pi_getLL('admin_action').'</th>
			</tr>';
			$summarize=array();
			foreach ($pageset['dataset'] as $row) {
				if (!$tr_type or $tr_type=='even') {
					$tr_type='odd';
				} else {
					$tr_type='even';
				}
				$tableContent.='<tr class="'.$tr_type.'">';
				foreach ($params['tableColumns'] as $col=>$valArray) {
					switch ($valArray['valueType']) {
						case 'download_invoice':
							$row[$col]='<a href="uploads/tx_multishopexactonline/'.$row[$col].'" target="_blank">'.$row[$col].'</a>';
							break;
						case 'currency':
							$summarize[$col]+=$row[$col];
							$row[$col]=mslib_fe::amount2Cents($row[$col], 0);
							break;
						case 'timestamp':
							if ($row[$col]) {
								$row[$col]=strftime("%x %X", $row[$col]);
							}
							break;
					}
					$tableContent.='<td'.($valArray['align'] ? ' align="'.$valArray['align'].'"' : '').($valArray['nowrap'] ? ' nowrap' : '').'>'.$row[$col].'</td>';
				}
				$tableContent.='
				<td>
				</td>';
				$tableContent.='</tr>';
			}
			// SUMMARIZE
			$tableContent.='<tr>';
			foreach ($params['tableColumns'] as $col=>$valArray) {
				switch ($valArray['valueType']) {
					case 'currency':
						$row[$col]=mslib_fe::amount2Cents($summarize[$col], 0);
						break;
					default:
						$row[$col]=$valArray['title'];
						break;
				}
				$tableContent.='<th'.($valArray['align'] ? ' align="'.$valArray['align'].'"' : '').($valArray['nowrap'] ? ' nowrap' : '').'>'.$row[$col].'</th>';
			}
			$tableContent.='<th></th></tr>';
			// SUMMARIZE EOF
			$tableContent.='</table>
			</form>
			</div>
			';
			// pagination
			if (!$that->ms['nopagenav'] and $pageset['total_rows']>$that->ms['MODULES']['PAGESET_LIMIT']) {
				$total_pages=ceil(($pageset['total_rows']/$that->ms['MODULES']['PAGESET_LIMIT']));
				$tmp='';
				$tmp.='<div id="pagenav_container_list_wrapper">
				<ul id="pagenav_container_list">
				<li class="pagenav_first">';
				if ($p>0) {
					$tmp.='<a class="pagination_button msBackendButton backState arrowLeft arrowPosLeft" href="'.mslib_fe::typolink(',2003', ''.mslib_fe::tep_get_all_get_params(array(
								'p',
								'Submit',
								'tx_multishop_pi1[action]',
								'clearcache'
							))).'"><span>'.$that->pi_getLL('first').'</span></a>';
				} else {
					$tmp.='<span class="pagination_button msBackendButton backState arrowLeft arrowPosLeft disabled"><span>'.$that->pi_getLL('first').'</span></span>';
				}
				$tmp.='</li>';
				$tmp.='<li class="pagenav_previous">';
				if ($p>0) {
					if (($p-1)>0) {
						$tmp.='<a class="pagination_button msBackendButton backState arrowLeft arrowPosLeft" href="'.mslib_fe::typolink(',2003', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
									'p',
									'Submit',
									'tx_multishop_pi1[action]',
									'clearcache'
								))).'"><span>'.$that->pi_getLL('previous').'</span></a>';
					} else {
						$tmp.='<a class="pagination_button msBackendButton backState arrowLeft arrowPosLeft" href="'.mslib_fe::typolink(',2003', 'p='.($p-1).'&'.mslib_fe::tep_get_all_get_params(array(
									'p',
									'Submit',
									'tx_multishop_pi1[action]',
									'clearcache'
								))).'"><span>'.$that->pi_getLL('previous').'</span></a>';
					}
				} else {
					$tmp.='<span class="pagination_button msBackendButton backState arrowLeft arrowPosLeft disabled"><span>'.$that->pi_getLL('previous').'</span></span>';
				}
				$tmp.='</li>';
				if ($p==0 || $p<9) {
					$start_page_number=1;
					if ($total_pages<=10) {
						$end_page_number=$total_pages;
					} else {
						$end_page_number=10;
					}
				} else {
					if ($p>=9) {
						$start_page_number=($p-5)+1;
						$end_page_number=($p+4)+1;
						if ($end_page_number>$total_pages) {
							$end_page_number=$total_pages;
						}
					}
				}
				$tmp.='<li class="pagenav_number">
				<ul id="pagenav_number_wrapper">';
				for ($x=$start_page_number; $x<=$end_page_number; $x++) {
					if (($p+1)==$x) {
						$tmp.='<li><span>'.$x.'</span></a></li>';
					} else {
						$tmp.='<li><a class="ajax_link pagination_button" href="'.mslib_fe::typolink(',2003', 'p='.($x-1).'&'.mslib_fe::tep_get_all_get_params(array(
									'p',
									'Submit',
									'page',
									'tx_multishop_pi1[action]',
									'clearcache'
								))).'">'.$x.'</a></li>';
					}
				}
				$tmp.='</ul>
				</li>';
				$tmp.='<li class="pagenav_next">';
				if ((($p+1)*$that->ms['MODULES']['PAGESET_LIMIT'])<$pageset['total_rows']) {
					$tmp.='<a class="pagination_button msBackendButton continueState arrowRight arrowPosLeft" href="'.mslib_fe::typolink(',2003', 'p='.($p+1).'&'.mslib_fe::tep_get_all_get_params(array(
								'p',
								'Submit',
								'tx_multishop_pi1[action]',
								'clearcache'
							))).'"><span>'.$that->pi_getLL('next').'</span></a>';
				} else {
					$tmp.='<span class="pagination_button msBackendButton continueState arrowRight arrowPosLeft disabled"><span>'.$that->pi_getLL('next').'</span></span>';
				}
				$tmp.='</li>';
				$tmp.='<li class="pagenav_last">';
				if ((($p+1)*$that->ms['MODULES']['PAGESET_LIMIT'])<$pageset['total_rows']) {
					$lastpage=floor(($pageset['total_rows']/$that->ms['MODULES']['PAGESET_LIMIT']));
					$tmp.='<a class="pagination_button msBackendButton continueState arrowRight arrowPosLeft" href="'.mslib_fe::typolink(',2003', 'p='.$lastpage.'&'.mslib_fe::tep_get_all_get_params(array(
								'p',
								'Submit',
								'tx_multishop_pi1[action]',
								'clearcache'
							))).'"><span>'.$that->pi_getLL('last').'</span></a>';
				} else {
					$tmp.='<span class="pagination_button msBackendButton continueState arrowRight arrowPosLeft disabled"><span>'.$that->pi_getLL('last').'</span></span>';
				}
				$tmp.='</li>';
				$tmp.='</ul>
				</div>
				';
				$tableContent.=$tmp;
			}
			// pagination eof
		}
		$content='
		<div id="tab-container">
		<ul class="tabs" id="admin_orders">
			<li class="active"><a href="#CmsListing">'.$params['title'].'</a></li>
		</ul>
		<div class="tab_container">
		';
		$searchForm='';
		if ($params['settings']['enableKeywordSearch']) {
			$searchForm='
			<form id="form1" name="form1" method="get" action="index.php">
				<div id="search-orders">
					<table width="100%">
						<tr>
							<td nowrap valign="top">';
			foreach ($params['searchForm']['hiddenFields'] as $key=>$val) {
				$searchForm.='<input name="'.$key.'" type="hidden" value="'.htmlspecialchars($val).'" />'."\n";
			}
			$searchForm.='
								<div class="formfield-container-wrapper">
									<div class="formfield-wrapper">
										<label>'.$that->pi_getLL('keyword').'</label><input type="text" name="tx_multishop_pi1[keyword]" id="skeyword" value="'.htmlspecialchars($that->get['tx_multishop_pi1']['keyword']).'" />
										<input type="submit" name="Search" class="msadmin_button" value="'.$that->pi_getLL('search').'" />
									</div>
								</div>
							</td>
							<td nowrap valign="top" align="right" class="searchLimit">
								<div style="float:right;">
									'.$limit_search_result_selectbox.'
								</div>
							</td>
						</tr>
					</table>
				</div>
			</form>
			';
		}
		$content.='
				<div style="display: block;" id="CmsListing" class="tab_content">
					'.$searchForm.'
					'.$tableContent.'
				</div>
			</div>
		</div>
		';
		$content.='<p><center>Found records: <strong>'.number_format($pageset['total_rows'], 0, '', '.').'</strong></center></p>';
		$content.='<p><center>Total records in database: <strong>'.$params['summarizeData']['totalRecordsInTable'].'</strong></center></p>';
		$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
		$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($that->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
		return $content;
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php"]);
}
?>