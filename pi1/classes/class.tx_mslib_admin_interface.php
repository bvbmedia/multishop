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
class tx_mslib_admin_interface extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $that=array();
	var $interfaceKey='';
	var $headerButtons=array();
	public function init($ref) {
		mslib_fe::init($ref);
	}
	function initLanguage($ms_locallang) {
		$this->pi_loadLL();
		//array_merge with new array first, so a value in locallang (or typoscript) can overwrite values from ../locallang_db
		$this->LOCAL_LANG=array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
		if ($this->altLLkey) {
			$this->LOCAL_LANG=array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
		}
	}
	/**
	 * @return string
	 */
	public function getInterfaceKey() {
		return $this->interfaceKey;
	}
	/**
	 * @param string $interfaceKey
	 */
	public function setInterfaceKey($interfaceKey) {
		$this->interfaceKey=$interfaceKey;
	}
	/**
	 * @return array
	 */
	public function getHeaderButtons() {
		return $this->headerButtons;
	}
	/**
	 * @param array $headerButtons
	 */
	public function setHeaderButtons($headerButtons) {
		$this->headerButtons=$headerButtons;
		//hook to let other plugins further manipulate the method
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceHeaderButtonsPostProc'])) {
			$interfaceKey=&$this->interfaceKey;
			$params=array('interfaceKey'=>&$interfaceKey);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceHeaderButtonsPostProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
	}
	public function renderHeaderButtons() {
		if (is_array($this->headerButtons)) {
			$content='<div class="form-inline">';
			foreach ($this->headerButtons as $headingButton) {
				$content.='<a href="'.$headingButton['href'].'" class="'.$headingButton['btn_class'].'"'.($headingButton['attributes']?' '.$headingButton['attributes']:'').($headingButton['target']?' target="'.$headingButton['target'].'"':'').'><i class="'.$headingButton['fa_class'].'"></i> '.htmlspecialchars($headingButton['title']).'</a> ';
			}
			$content.='</div>';
			return $content;
		}
	}
	function renderInterface($params, &$that) {
		mslib_fe::init($that);
		//hook to let other plugins further manipulate the method
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['renderInterfacePreProc'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['renderInterfacePreProc'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
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
		if ($params['settings']['limit'] && is_numeric($params['settings']['limit'])) {
			$that->ms['MODULES']['PAGESET_LIMIT']=$params['settings']['limit'];
		}
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
		$limit_search_result_selectbox='<select name="limit" class="form-control">';
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
		$limits[]='600';
		$limits[]='700';
		$limits[]='800';
		$limits[]='900';
		$limits[]='1000';
		$limits[]='1500';
		$limits[]='2000';
		$limits[]='2500';
		$limits[]='3000';
		$limits[]='3500';
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
		if ($params['query']['where']) {
			if (is_array($params['query']['where'])) {
				$queryData['where']=array_merge(array_values($queryData['where']), array_values($params['query']['where']));
			} else {
				$queryData['where'][]=$params['query']['where'];
			}
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
		if (is_array($params['query']['group_by'])) {
			$queryData['group_by']=implode(',', $params['query']['group_by']);
		} elseif ($params['query']['group_by']) {
			$queryData['group_by'][]=$params['query']['group_by'];
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
		if ($params['msDebug']) {
			$this->msDebug=1;
		}
		//$this->msDebug=1;
		//echo print_r($queryData);
		//die();
		$pageset=mslib_fe::getRecordsPageSet($queryData);
		if ($this->msDebug) {
			echo $this->msDebugInfo;
			die();
		}
		//echo print_r($queryData);
		//die();
		if (count($pageset['dataset'])) {
			$tr_type='even';
			if (!$params['settings']['disableForm']) {
				$tableContent.='<form method="post" action="'.$params['postForm']['actionUrl'].'" enctype="multipart/form-data">';
			}
			$tableContent.='<div class="table-responsive">';
			$tableContent.='<table class="table table-striped table-bordered" id="msAdminTableInterface">';
			$tableContent.='<tr><thead>';
			foreach ($params['tableColumns'] as $col=>$valArray) {
				$tableContent.='<th'.($valArray['align'] ? ' class="text-'.$valArray['align'].'"' : '').'>'.$valArray['title'].'</th>';
			}
			$tableContent.='</thead></tr><tbody>';
			$summarize=array();
			$recordCounter=0;
			foreach ($pageset['dataset'] as $rowKey => $row) {
				$recordCounter++;
				if (!$tr_type or $tr_type=='even') {
					$tr_type='odd';
				} else {
					$tr_type='even';
				}
				$tableContent.='<tr class="'.$tr_type.'">';
				foreach ($params['tableColumns'] as $col=>$valArray) {
					$originalValue=$row[$col];
					switch ($valArray['valueType']) {
						case 'number_format_2_decimals':
							$row[$col]=round(number_format($row[$col], 2), 2);
							break;
						case 'number_format_thousand_seperator':
							$row[$col]=number_format($row[$col], 0, '', '.');
							break;
						case 'recordCounter':
							$row[$col]=$recordCounter;
							break;
						case 'download_invoice':
							$row[$col]='<a href="uploads/tx_multishopexactonline/'.$row[$col].'" target="_blank">'.$row[$col].'</a>';
							break;
						case 'currency':
							$summarize[$col]+=$row[$col];
							$row[$col]=mslib_fe::amount2Cents($row[$col], 0);
							break;
						case 'domain_name':
							if ($row[$col]) {
								$row[$col]='<a href="http://'.$row[$col].'" target="_blank">'.$row[$col].'</a>';
							}
							break;
						case 'datetime':
							if ($row[$col]) {
								$row[$col]=strftime("%x %X", strtotime($row[$col]));
							} else {
								$row[$col]='';
							}
							break;
						case 'timestamp':
							if (is_numeric($row[$col]) && $row[$col]>0) {
								$row[$col]=strftime("%x %X", $row[$col]);
							} else {
								$row[$col]='';
							}
							break;
						case 'timestamp_to_day_date_time':
							if (is_numeric($row[$col]) && $row[$col]>0) {
								$row[$col]=strftime("%a. %x<br/>%X", $row[$col]);
							} else {
								$row[$col]='';
							}
							break;
						case 'timestamp_to_date':
							if (is_numeric($row[$col]) && $row[$col]>0) {
								$row[$col]=strftime("%x", $row[$col]);
							} else {
								$row[$col]='';
							}
							break;
						case 'form':
							$content='<form method="';
							switch ($valArray['formAction']) {
								case 'post':
									$content.='POST';
									break;
								case 'get':
								default:
									$content.='GET';
									break;
							}
							$content.='" action="'.$valArray['actionUrl'].'" enctype="multipart/form-data">';
							if ($valArray['content']) {
								$content.=$valArray['content'];
							}
							if (is_array($valArray['hiddenFields'])) {
								foreach ($valArray['hiddenFields'] as $hiddenFieldKey=>$hiddenFieldVal) {
									foreach ($row as $tmpCol=>$tmpVal) {
										$hiddenFieldVal=str_replace('###'.$tmpCol.'###', $row[$tmpCol], $hiddenFieldVal);
									}
									$content.='<input name="'.$hiddenFieldKey.'" type="hidden" value="'.$hiddenFieldVal.'" />';
								}
							}
							$content.='</form>';
							$row[$col]=$content;
							break;
						case 'content':
							foreach ($row as $tmpCol=>$tmpVal) {
								$valArray['content']=str_replace('###'.$tmpCol.'###', $row[$tmpCol], $valArray['content']);
							}
							$row[$col]=$valArray['content'];
							break;
						case 'products_detail_page_link':
							$where='';
							if ($row['categories_id']) {
								// get all cats to generate multilevel fake url
								$level=0;
								$cats=mslib_fe::Crumbar($row['categories_id']);
								$cats=array_reverse($cats);
								$where='';
								if (count($cats)>0) {
									foreach ($cats as $cat) {
										$where.="categories_id[".$level."]=".$cat['id']."&";
										$level++;
									}
									$where=substr($where, 0, (strlen($where)-1));
									$where.='&';
								}
								// get all cats to generate multilevel fake url eof
							}
							$product_detail_link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$row['products_id'].'&tx_multishop_pi1[page_section]=products_detail',1);
							$row[$col]='<a href="'.$product_detail_link.'" target="_blank">'.htmlspecialchars($row['products_name']).'</a>';
							break;
						case 'boolean':
							$status_html='';
							if (!$row[$col]) {
								$status_html.='<span class="admin_status_red" alt="'.$this->pi_getLL('no').'"></span>';
							} else {
								$status_html.='<span class="admin_status_green" alt="'.$this->pi_getLL('yes').'"></span>';
							}
							$row[$col]=$status_html;
							break;
						case 'booleanToggle':
							$status_html='';
							if (!$row[$col]) {
								$status_html.='<span class="admin_status_red" alt="'.$this->pi_getLL('disable').'"></span>';
								if ($valArray['hrefEnable']) {
									foreach ($row as $tmpCol=>$tmpVal) {
										$valArray['hrefEnable']=str_replace('###'.$tmpCol.'###', $row[$tmpCol], $valArray['hrefEnable']);
									}
									$status_html.='<a href="'.$valArray['hrefEnable'].'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('enabled').'"></span></a>';
								}
							} else {
								if ($valArray['hrefDisable']) {
									foreach ($row as $tmpCol=>$tmpVal) {
										$valArray['hrefDisable']=str_replace('###'.$tmpCol.'###', $row[$tmpCol], $valArray['hrefDisable']);
									}
									$status_html.='<a href="'.$valArray['hrefDisable'].'"><span class="admin_status_red disabled" alt="'.$this->pi_getLL('disabled').'"></span></a>';
								}
								$status_html.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
							}
							$row[$col]=$status_html;
							break;
					}
					$adjustedValue=$row[$col];
					if ($valArray['href']) {
						foreach ($row as $tmpCol=>$tmpVal) {
							$valArray['href']=str_replace('###'.$tmpCol.'###', $row[$tmpCol], $valArray['href']);
						}
						$adjustedValue='<a href="'.$valArray['href'].'"'.($valArray['href_target']?' target="'.$valArray['href_target'].'""':'').'>'.$adjustedValue.'</a>';
					}
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['tableColumnsPreProc'])) {
						$conf=array(
							'col'=>&$col,
							'row'=>&$row,
							'originalValue'=>&$originalValue,
							'adjustedValue'=>&$adjustedValue,
							'params'=>&$params,
							'valArray'=>&$valArray,
							'summarize'=>&$summarize
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['tableColumnsPreProc'] as $funcRef) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $that);
						}
					}
					$tableContent.='<td'.($valArray['align'] ? ' class="text-'.$valArray['align'].'"' : '').($valArray['nowrap'] ? ' nowrap' : '').'>'.$adjustedValue.'</td>';
				}
				$tableContent.='</tr>';
				if ($params['settings']['returnResultSetAsArray']) {
					$pageset['dataset'][$rowKey]=$row;
				}
			}
			$tableContent.='</tbody>';
			if (!$params['settings']['skipSummarize']) {
				// Summarize footer
				$tableContent.='<tfoot><tr>';
				foreach ($params['tableColumns'] as $col=>$valArray) {
					switch ($valArray['valueType']) {
						case 'currency':
							$row[$col]=mslib_fe::amount2Cents($summarize[$col], 0);
							break;
						default:
							$row[$col]=$valArray['title'];
							break;
					}
					$tableContent.='<th'.($valArray['align'] ? ' class="text-'.$valArray['align'].'"' : '').($valArray['nowrap'] ? ' nowrap' : '').'>'.$row[$col].'</th>';
				}
				$tableContent.='</tr></tfoot>';
			}
			$tableContent.='</table>';
			if ($params['settings']['contentBelowTable']) {
				$tableContent.=$params['settings']['contentBelowTable'];
			}
			$tableContent.='
			</div>
			';
			if (!$params['settings']['disableForm']) {
				$tableContent.='</form>';
			}
			// pagination
			$paginationMarkup='';
			if (!$params['settings']['skipPaginationMarkup'] and $pageset['total_rows']>$that->ms['MODULES']['PAGESET_LIMIT']) {
				$total_pages=ceil(($pageset['total_rows']/$that->ms['MODULES']['PAGESET_LIMIT']));
				require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
				$paginationMarkup=$tmp;
				$tableContent.=$tmp;
				$tmp='';
			}
			// pagination eof
		}
		$content='';
		$content.='<div class="panel panel-default">';
		$content.='<div class="panel-heading">';
		if ($params['interfaceTitle']) {
			$interfaceTitle=$params['interfaceTitle'];
		} else {
			$interfaceTitle=$params['title'];
		}
		$content.='<h3>'.htmlspecialchars($interfaceTitle).'</h3>';
		if (is_array($params['settings']['headingButtons'])) {
			$content.='<div class="form-inline">';
			foreach ($params['settings']['headingButtons'] as $headingButton) {
				$content.='<a href="'.$headingButton['href'].'" class="'.$headingButton['btn_class'].'"'.($headingButton['attributes']?' '.$headingButton['attributes']:'').'><i class="'.$headingButton['fa_class'].'"></i> '.htmlspecialchars($headingButton['title']).'</a> ';
			}
			$content.='</div>';
		}
		$content.='</div>';
		$content.='<div class="panel-body">';
		if (!$params['settings']['skipTabMarkup']) {
			$GLOBALS['TSFE']->additionalHeaderData['msAdminTabJs']='<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(\'.nav-tabs a:first\').tab(\'show\');
			});
			</script>
			';
			$content.='
				<div id="tab-container">
				<ul class="nav nav-tabs" id="admin_orders" role="tablist">
					<li role="presentation"><a href="#CmsListing" aria-controls="profile" role="tab" data-toggle="tab">'.htmlspecialchars($params['title']).'</a></li>
				</ul>
				<div class="tab-content">
			';
		}
		$searchForm='';
		if ($params['settings']['enableKeywordSearch']) {
			$searchForm='
			<form id="form1" name="form1" method="get" action="index.php">
				<div class="well">
					<div class="row formfield-container-wrapper">
						';
			foreach ($params['searchForm']['hiddenFields'] as $key=>$val) {
				$searchForm.='<input name="'.$key.'" type="hidden" value="'.htmlspecialchars($val).'" />'."\n";
			}
			$searchForm.='
						<div class="col-sm-8 formfield-wrapper">
							<div class="form-inline">
								<label class="control-label">'.$that->pi_getLL('keyword').'</label>
								<input type="text" name="tx_multishop_pi1[keyword]" class="form-control" value="'.htmlspecialchars($that->get['tx_multishop_pi1']['keyword']).'" />
								<input type="submit" name="Search" class="btn btn-success" value="'.$that->pi_getLL('search').'" />
							</div>
						</div>
						<div class="col-sm-4 formfield-wrapper">
							<div class="pull-right form-inline">
								<label class="control-label">'.$that->pi_getLL('limit_number_of_records_to').'</label>
								'.$limit_search_result_selectbox.'
							</div>
						</div>
					</div>
				</div>
			</form>
			';
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceSearchFormPostProc'])) {
				$interfaceKey=&$this->interfaceKey;
				$params_searchform=array(
					'interfaceKey'=>&$interfaceKey,
					'searchForm'=>&$searchForm,
					'adminInterfaceParams'=>&$params,
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceSearchFormPostProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_searchform, $this);
				}
			}
		}
		if (!$params['settings']['skipTabMarkup']) {
			$content.='
				<div role="tabpanel" id="CmsListing" class="tab-pane">
					'.$searchForm.'
					'.$tableContent.'
				</div>
			</div>
			</div>
			';
		} else {
			$content.=$searchForm.$tableContent;
		}
		if ($params['settings']['skipRecordCount'] || ($params['settings']['skipRecordCountWhenZeroResults'] && !$pageset['total_rows'])) {
			$skipRecordCount=1;
		}
		if ($params['settings']['skipTotalCount'] || ($params['settings']['skipTotalCountWhenZeroResults'] && !$params['summarizeData']['totalRecordsInTable'])) {
			$skipTotalCount=1;
		}
		if (!$skipRecordCount) {
			$content.='<p class="text-center">'.$this->pi_getLL('found_records').': <strong>'.number_format($pageset['total_rows'], 0, '', '.').'</strong></p>';
		}
		if (!$skipTotalCount) {
			$content.='<p class="text-center">'.$this->pi_getLL('total_records_in_database').': <strong>'.number_format($params['summarizeData']['totalRecordsInTable'], 0, '', '.').'</strong></p>';
		}
		if (!$params['settings']['skipFooterMarkup']) {
			$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$that->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div>';
		}
		$content.='</div>';
		$content.='</div>';
		if ($params['settings']['returnOnlyWhenRecordsFound'] && !$pageset['total_rows']) {
			//return;
		} else {
			if ($params['settings']['returnResultSetAsArray']) {
				$array=array();
				$array['searchForm']=$searchForm;
				$array['paginationMarkup']=$paginationMarkup;
				$array['dataset']=$pageset['dataset'];
				return $array;
			} else {
				return $content;
			}
		}
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php"]);
}
?>