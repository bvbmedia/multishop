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
    var $that = array();
    var $interfaceKey = '';
    var $headerButtons = array();
    public function init($ref) {
        mslib_fe::init($ref);
    }
    function initLanguage($ms_locallang) {
        $this->pi_loadLL();
        //array_merge with new array first, so a value in locallang (or typoscript) can overwrite values from ../locallang_db
        $this->LOCAL_LANG = array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
        if ($this->altLLkey) {
            $this->LOCAL_LANG = array_replace_recursive($this->LOCAL_LANG, is_array($ms_locallang) ? $ms_locallang : array());
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
        $this->interfaceKey = $interfaceKey;
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
        $this->headerButtons = $headerButtons;
        //hook to let other plugins further manipulate the method
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceHeaderButtonsPostProc'])) {
            $interfaceKey =& $this->interfaceKey;
            $params = array('interfaceKey' => &$interfaceKey);
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceHeaderButtonsPostProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if (is_array($this->headerButtons) && count($this->headerButtons)) {
            $sortedHeaderButtonsSorted = array();
            $sortedHeaderButtons = array();
            foreach ($this->headerButtons as $item) {
                if (isset($item['sort'])) {
                    $sortedHeaderButtonsSorted[] = $item;
                } else {
                    $sortedHeaderButtons[] = $item;
                }
            }
            $this->headerButtons = array_merge($sortedHeaderButtons, $sortedHeaderButtonsSorted);
        }
    }
    public function renderHeaderButtons() {
        if (is_array($this->headerButtons)) {
            $content = '<div class="form-inline pull-right">';
            foreach ($this->headerButtons as $headingButton) {
                $content .= '<a href="' . $headingButton['href'] . '" class="' . $headingButton['btn_class'] . '"' . ($headingButton['attributes'] ? ' ' . $headingButton['attributes'] : '') . ($headingButton['target'] ? ' target="' . $headingButton['target'] . '"' : '') . '><i class="' . $headingButton['fa_class'] . '"></i> ' . htmlspecialchars($headingButton['title']) . '</a> ';
            }
            $content .= '</div>';
            return $content;
        }
    }
    function renderInterface($params, &$that) {
        mslib_fe::init($that);
        //hook to let other plugins further manipulate the method
        if (!isset($params['interfaceKey']) && isset($this->interfaceKey)) {
            $params['interfaceKey'] = $this->interfaceKey;
        }
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['renderInterfacePreProc'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['renderInterfacePreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }

        $tableId = uniqid();
        // for pagination
        $this->get = $that->get;
        $this->post = $that->post;
        if ($this->post) {
            if ($params['postErno']) {
                if (count($params['postErno'])) {
                    $returnMarkup = '
                    <div style="display:none" id="msAdminPostMessage">
                    <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">Status</th>
                        <th>Message</th>
                    </tr>
                    </thead>
                    <tbody>
                    ';
                    foreach ($params['postErno'] as $item) {
                        switch ($item['status']) {
                            case 'error':
                                $item['status'] = '<span class="fa-stack text-danger"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-thumbs-down fa-stack-1x fa-inverse"></i></span>';
                                break;
                            case 'info':
                                $item['status'] = '<span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-thumbs-up fa-stack-1x fa-inverse"></i></span>';
                                break;
                        }
                        $returnMarkup .= '<tr><td class="text-center">' . $item['status'] . '</td><td>' . $item['message'] . '</td></tr>' . "\n";
                    }
                    $returnMarkup .= '</tbody></table></div>';
                    $tableContent .= $returnMarkup;
                    $GLOBALS['TSFE']->additionalHeaderData[] = '<script type="text/javascript" data-ignore="1">
                    jQuery(document).ready(function ($) {
                        $.confirm({
                            title: \'\',
                            content: $(\'#msAdminPostMessage\').html()
                        });
                    });
                    </script>
                    ';
                }
            }
        }

        $updateCookie = 0;
        $that->cookie = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_cookie');
        if (!isset($params['settings']['limit'])) {
            if ((isset($that->get['Search']) || isset($that->get['submit'])) and (is_numeric($that->get['limit']) && $that->get['limit'] > 0 && $that->get['limit'] != $that->cookie['limit'])) {
                $that->cookie['limit'] = $that->get['limit'];
                $that->get['tx_multishop_pi1']['limit'] = $that->cookie['limit'];
                $params['settings']['limit'] = $that->cookie['limit'];
                $updateCookie = 1;
            }
        }
        if ($that->get['Search'] and ($that->get['display_all_records'] != $that->cookie['display_all_records'])) {
            $that->cookie['display_all_records'] = $that->get['display_all_records'];
            $updateCookie = 1;
        }
        if (!isset($params['settings']['limit']) && !$that->get['limit'] && !$that->ms['MODULES']['PAGESET_LIMIT']) {
            $that->get['limit'] = 25;
            $that->cookie['limit'] = $that->get['limit'];
        }
        if ($updateCookie) {
            $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $that->cookie);
            $GLOBALS['TSFE']->storeSessionData();
        }
        if ($that->cookie['display_all_records']) {
            $that->get['display_all_records'] = $that->cookie['display_all_records'];
        } else {
            $that->get['display_all_records'] = '';
        }
        if (is_numeric($that->get['tx_multishop_pi1']['limit'])) {
            $that->get['limit'] = $that->get['tx_multishop_pi1']['limit'];
            $params['settings']['limit'] = $that->get['limit'];
        }
        if (!isset($params['settings']['limit'])) {
            /*
            if (!isset($that->get['limit']) || $that->get['limit']!=$that->cookie['limit']) {
                if ($params['settings']['limit'] && is_numeric($params['settings']['limit'])) {
                    $that->get['limit'] = $params['settings']['limit'];
                } else {
                    $that->get['limit'] = 15;
                }
            } else {
            */
            if ($that->cookie['limit']) {
                $that->get['limit'] = $that->cookie['limit'];
                $params['settings']['limit'] = $that->cookie['limit'];
            }
            //}
        } else {
            if (!is_numeric($that->get['limit'])) {
                $that->get['limit'] = 50;
            }
            if ($params['settings']['limit'] && is_numeric($params['settings']['limit'])) {
                $that->get['limit'] = $params['settings']['limit'];
            }
        }
        $that->ms['MODULES']['PAGESET_LIMIT'] = $that->get['limit'];
        if ($params['settings']['limit'] && is_numeric($params['settings']['limit'])) {
            $that->ms['MODULES']['PAGESET_LIMIT'] = $params['settings']['limit'];
        }
        if (is_numeric($that->get['p'])) {
            $p = $that->get['p'];
        }
        $that->searchKeywords = array();
        if ($that->get['tx_multishop_pi1']['keyword']) {
            //  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
            $that->get['tx_multishop_pi1']['keyword'] = trim($that->get['tx_multishop_pi1']['keyword']);
            $that->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($that->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
            $that->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($that->get['tx_multishop_pi1']['keyword'], true);
            $that->get['tx_multishop_pi1']['keyword'] = mslib_fe::RemoveXSS($that->get['tx_multishop_pi1']['keyword']);
            $that->searchKeywords[] = $that->get['tx_multishop_pi1']['keyword'];
            $that->searchMode = '%keyword%';
        }
        $limit_search_result_selectbox = '<select name="limit" class="form-control">';
        $limits = array();
        $limits[] = '10';
        $limits[] = '15';
        $limits[] = '20';
        $limits[] = '25';
        $limits[] = '30';
        $limits[] = '40';
        $limits[] = '50';
        $limits[] = '100';
        $limits[] = '150';
        $limits[] = '200';
        $limits[] = '250';
        $limits[] = '300';
        $limits[] = '350';
        $limits[] = '400';
        $limits[] = '450';
        $limits[] = '500';
        $limits[] = '600';
        $limits[] = '700';
        $limits[] = '800';
        $limits[] = '900';
        $limits[] = '1000';
        $limits[] = '1500';
        $limits[] = '2000';
        $limits[] = '2500';
        $limits[] = '3000';
        $limits[] = '3500';
        foreach ($limits as $limit) {
            $limit_search_result_selectbox .= '<option value="' . $limit . '"' . ($limit == $that->get['limit'] ? ' selected="selected"' : '') . '>' . $limit . '</option>';
        }
        $limit_search_result_selectbox .= '</select>';
        $queryData = array();
        $queryData['where'] = array();
        if (count($that->searchKeywords)) {
            $keywordOr = array();
            $that->searchMode = '%keyword%';
            foreach ($that->searchKeywords as $searchKeyword) {
                if ($searchKeyword) {
                    switch ($that->searchMode) {
                        case 'keyword%':
                            $that->sqlKeyword = addslashes($searchKeyword) . '%';
                            break;
                        case '%keyword%':
                        default:
                            $that->sqlKeyword = '%' . addslashes($searchKeyword) . '%';
                            break;
                    }
                    if (is_array($params['query']['keywordSearchByColumns']) && count($params['query']['keywordSearchByColumns'])) {
                        foreach ($params['query']['keywordSearchByColumns'] as $col) {
                            $keywordOr[] = $col . " like '" . $that->sqlKeyword . "'";
                        }
                    }
                }
            }
            if (is_array($keywordOr) && count($keywordOr)) {
                $queryData['keywordSearchWhere'] = "(" . implode(" OR ", $keywordOr) . ")";
            }
        }
        if ($queryData['keywordSearchWhere'] && is_array($params['query']['whereMatch'])) {
            $orFilter = array();
            $orFilter[] = $queryData['keywordSearchWhere'];
            $orFilter[] = '(' . implode(' OR ', $params['query']['whereMatch']) . ')';
            $queryData['where'][] = '(' . implode(' OR ', $orFilter) . ')';
        } elseif ($queryData['keywordSearchWhere']) {
            $queryData['where'][] = $queryData['keywordSearchWhere'];
        } elseif (is_array($params['query']['whereMatch'])) {
            $queryData['where'][] = '(' . implode(' OR ', $params['query']['whereMatch']) . ')';
        }
        if ($params['query']['where']) {
            if (is_array($params['query']['where'])) {
                $queryData['where'] = array_merge(array_values($queryData['where']), array_values($params['query']['where']));
            } else {
                $queryData['where'][] = $params['query']['where'];
            }
        }
        $sortOnColumn = array();
        foreach ($params['tableColumns'] as $col => $valArray) {
            if (isset($valArray['enableSortDbOnColumn']) && !empty($valArray['enableSortDbOnColumn']))
            $sortOnColumn[$col] = $valArray['enableSortDbOnColumn'];
        }
        switch ($that->get['tx_multishop_pi1']['order_by']) {
            default:
                if (isset($that->get['tx_multishop_pi1']['order_by']) && !empty($that->get['tx_multishop_pi1']['order_by'])) {
                    $sortColumn = $that->get['tx_multishop_pi1']['order_by'];
                    if (isset($sortOnColumn[$sortColumn]) && !empty($sortOnColumn[$sortColumn])) {
                        $sortColumn = $sortOnColumn[$sortColumn];
                    }
                    $order_by = addslashes($sortColumn);
                } else {
                    if (is_array($params['query']['defaultOrderByColumns']) && count($params['query']['defaultOrderByColumns'])) {
                        $order_by = implode(',', $params['query']['defaultOrderByColumns']);
                    }
                }
                break;
        }
        switch ($that->get['tx_multishop_pi1']['order']) {
            case 'a':
                $order = 'asc';
                $order_link = 'd';
                break;
            case 'd':
                $order = 'desc';
                $order_link = 'a';
                break;
            default:
                if ($params['query']['defaultOrder'] == 'asc') {
                    $order = 'asc';
                    $order_link = 'd';
                } else {
                    $order = 'desc';
                    $order_link = 'a';
                }
                break;
        }
        $orderby[] = $order_by . ' ' . $order;
        if (is_array($params['query']['select'])) {
            $queryData['select'] = implode(',', $params['query']['select']);
        } else {
            $queryData['select'] = $params['query']['select'];
        }
        if (is_array($params['query']['from'])) {
            $queryData['from'] = implode(',', $params['query']['from']);
        } else {
            $queryData['from'] = $params['query']['from'];
        }
        if (is_array($params['query']['group_by'])) {
            $queryData['group_by'] = implode(',', $params['query']['group_by']);
        } elseif ($params['query']['group_by']) {
            $queryData['group_by'][] = $params['query']['group_by'];
        }
        if (is_array($params['query']['having'])) {
            $queryData['having'] = $params['query']['having'];
        } elseif ($params['query']['having']) {
            $queryData['having'][] = $params['query']['having'];
        }
        $queryData['order_by'] = $orderby;
        $queryData['limit'] = $that->ms['MODULES']['PAGESET_LIMIT'];
        if (is_numeric($that->get['p'])) {
            $p = $that->get['p'];
        }
        if ($p > 0) {
            $queryData['offset'] = (((($p) * $that->ms['MODULES']['PAGESET_LIMIT'])));
        } else {
            $p = 0;
            $queryData['offset'] = 0;
        }
        if ($params['msDebug']) {
            $this->msDebug = 1;
        }
        //$this->msDebug=1;
        //echo print_r($queryData);
        //die();
        $pageset = mslib_fe::getRecordsPageSet($queryData);
        if ($this->msDebug) {
            echo $this->msDebugInfo;
            die();
        }
        if ($params['returnResultsSet']) {
            return $pageset;
        }
        if ($params['settings']['contentAboveTable']) {
            $tableContent .= $params['settings']['contentAboveTable'];
        }
        //echo print_r($queryData);
        //die();
        $columnSorterData = array();
        $columnSorterSettings = array();
        if (count($pageset['dataset'])) {
            $tr_type = 'even';
            if (!$params['settings']['disableForm']) {
                $tableContent .= '<form method="post" action="' . $params['postForm']['actionUrl'] . '" enctype="multipart/form-data">';
            }
            $columnSorterData = array();
            if (isset($params['settings']['colsSortable']) && $params['settings']['colsSortable'] > 0) {
                $colCounter = 0;
            }
            $tableContent .= '<div class="table-responsive">';
            if (isset($params['settings']['colsSortable']) && $params['settings']['colsSortable'] > 0) {
                $tableContent .= '<table class="table table-striped table-bordered table-valign-middle tablesorter" id="msAdminTableInterface' . $tableId . '">';
            } else {
                $tableContent .= '<table class="table table-striped table-bordered table-valign-middle msAdminTableInterface" id="msAdminTableInterface' . $tableId . '">';
            }
            $tableContent .= '<thead><tr>';
            if ($params['settings']['enableRowBasedCheckboxSelection']) {
	            $checkboxFieldKeyName = 'tableOverviewSelection';
	            if (isset($params['settings']['rowBasedCheckboxSelectionFieldKeyName']) && !empty($params['settings']['rowBasedCheckboxSelectionFieldKeyName'])) {
		            $checkboxFieldKeyName = $params['settings']['rowBasedCheckboxSelectionFieldKeyName'];
	            }
                $headerData = '';
                $headerData .= '
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$(\'#check_all_' . $checkboxFieldKeyName . '\').click(function(){
							$(\'td > div.' . $checkboxFieldKeyName . 'CheckboxClass > input:checkbox\').prop(\'checked\', this.checked);
						});
					});
				</script>';
                $GLOBALS['TSFE']->additionalHeaderData[] = $headerData;
                $headerData = '';
                $tableContent .= '
				<th class="cellCheckbox">
					<div class="checkbox checkbox-success checkbox-inline">
					<input type="checkbox" id="check_all_' . $checkboxFieldKeyName . '">
					<label for="check_all_' . $checkboxFieldKeyName . '"></label>
					</div>
				</th>';
                if (isset($params['settings']['colsSortable']) && $params['settings']['colsSortable'] > 0) {
                    $columnSorterData[$colCounter] = false;
                    $colCounter++;
                }
            }
            if (isset($params['settings']['colsSortable']) && $params['settings']['colsSortable'] > 0) {
                foreach ($params['tableColumns'] as $col => $valArray) {
                    if (isset($valArray['enableSorter'])) {
                        if ($valArray['enableSorter']) {
                            $columnSorterData[$colCounter] = true;
                            $columnSorterDataSettings[$colCounter][$valArray['valueType']] = true;
                            if ($valArray['href']) {
                                $columnSorterDataSettings[$colCounter]['href'] = true;
                            }
                        } else {
                            $columnSorterData[$colCounter] = false;
                        }
                    } else {
                        $columnSorterData[$colCounter] = false;
                    }
                    $colCounter++;
                }
            }
            $countColumnSorterData = count($columnSorterData);
            foreach ($params['tableColumns'] as $col => $valArray) {
                $tdClass = array();
                //if (is_array($columnSorterData) && $countColumnSorterData) {
                //$tdClass[] = 'header';
                //}
                if ($valArray['align']) {
                    $tdClass[] = 'text-' . $valArray['align'];
                }
                if ($valArray['nowrap']) {
                    $tdClass[] = 'cellNoWrap';
                }
                if ($valArray['class']) {
                    $tdClass[] = $valArray['class'];
                }
                if (isset($valArray['enableSortDbOnHeader']) && $valArray['enableSortDbOnHeader']) {
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['adminInterfaceTableRowsEnableSortDbOnHeaderPreProc'])) {
                        $conf = array(
                            'col' => $col,
							'interfaceKey' => $params['interfaceKey'],
                            'valArray' => &$valArray,
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['adminInterfaceTableRowsEnableSortDbOnHeaderPreProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $that);
                        }
                    }
                }
                $tableContent .= '<th' . (count($tdClass) ? ' class="' . implode(' ', $tdClass) . '"' : '') . '>' . $valArray['title'] . '</th>';
            }
            if (isset($params['settings']['rowsSortable']) && $params['settings']['rowsSortable']) {
                $tableContent .= '</tr></thead><tbody class="sortable_content">';
            } else {
                $tableContent .= '</tr></thead><tbody>';
            }
            $summarize = array();
            $recordCounter = 0;
            foreach ($pageset['dataset'] as $rowKey => $row) {
                $recordCounter++;
                if (!$tr_type or $tr_type == 'even') {
                    $tr_type = 'odd';
                } else {
                    $tr_type = 'even';
                }
                $row_sortable_id = '';
                if (isset($params['settings']['rowsSortable']) && $params['settings']['rowsSortable'] && isset($params['settings']['rowsSortableKey']) && !empty($params['settings']['rowsSortableKey'])) {
                    $row_sortable_id = ' id="row_sortable_' . $row[$params['settings']['rowsSortableKey']] . '"';
                }
                $tr_tag = '<tr class="' . $tr_type . '"' . $row_sortable_id . '>';
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['adminInterfaceTableRowsPreProc'])) {
                    $conf = array(
                            'row' => &$row,
                            'rowKey' => &$rowKey,
                            'interfaceKey' => $this->interfaceKey,
                            'tr_type' => &$tr_type,
                            'tr_tag' => &$tr_tag,
                            'row_sortable_id' => &$row_sortable_id
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['adminInterfaceTableRowsPreProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $that);
                    }
                }
                $tableContent .= $tr_tag;
                if ($params['settings']['enableRowBasedCheckboxSelection'] && $params['settings']['rowBasedCheckboxSelectionKey']) {
                    $tableContent .= '<td class="cellCheckbox">
						<div class="checkbox ' . $checkboxFieldKeyName . 'CheckboxClass checkbox-success checkbox-inline">
							<input type="checkbox" name="tx_multishop_pi1[' . $checkboxFieldKeyName . '][]" id="' . $checkboxFieldKeyName . 'Checkbox_' . $row[$params['settings']['rowBasedCheckboxSelectionKey']] . '" value="' . htmlspecialchars($row[$params['settings']['rowBasedCheckboxSelectionKey']]) . '">
							<label for="' . $checkboxFieldKeyName . 'Checkbox_' . $row[$params['settings']['rowBasedCheckboxSelectionKey']] . '"></label>
						</div>
					</td>';
                }
                foreach ($params['tableColumns'] as $col => $valArray) {
                    $originalValue = $row[$col];
                    switch ($valArray['valueType']) {
                        case 'number_format_8_decimals':
                            $row[$col] = rtrim(sprintf('%.8F', (float)$row[$col]), '0');
                            if (substr($row[$col], (strlen($row[$col]) - 1), 1) == '.') {
                                $row[$col] = substr($row[$col], 0, -1);
                            }
                            $summarize[$col] += $row[$col];
                            break;
                        case 'number_format_2_decimals':
                            $row[$col] = round(number_format((float)$row[$col], 2, '.', ''), 2);
                            $summarize[$col] += $row[$col];
                            break;
                        case 'number_format_thousands_seperator_without_decimals':
                        case 'number_format_thousands_seperator_without_decimals_no_sum':
                            $value=number_format((float)$row[$col], 0, '.', '');
                            $row[$col] = number_format($value, 0, '', '.');
                            $summarize[$col] += $value;
                            break;
                        case 'recordCounter':
                            $row[$col] = $recordCounter;
                            break;
                        case 'download_invoice':
                            $row[$col] = '<a href="uploads/tx_multishopexactonline/' . $row[$col] . '" target="_blank">' . $row[$col] . '</a>';
                            break;
                        case 'currency':
                            $summarize[$col] += $row[$col];
                            $row[$col] = mslib_fe::amount2Cents($row[$col], 0);
                            break;
                        case 'domain_name':
                            if ($row[$col]) {
                                $row[$col] = '<a href="http://' . $row[$col] . '" target="_blank">' . $row[$col] . '</a>';
                            }
                            break;
                        case 'datetime':
                            if ($row[$col]) {
                                $row[$col] = strftime("%x %X", strtotime($row[$col]));
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'date_datetime_tooltip':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = '<a href="javascript:void(0);" data-toggle="tooltip" class="btn-memo btn btn-default btn" data-title="' . htmlspecialchars(strftime("%a. %x<br/>%X", $row[$col])) . '" data-original-title="" title="">' . strftime("%x", $row[$col]) . '</a>';
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'timestamp':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = strftime("%x %X", $row[$col]);
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'timestamp_no_seconds':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = strftime("%x %H:%M", $row[$col]);
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'timestamp_to_day_date_time':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = strftime("%a. %x<br/>%X", $row[$col]);
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'timestamp_to_day_date_time_no_seconds':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = strftime("%a. %x %H:%M", $row[$col]);
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'timestamp_to_date':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = strftime("%x", $row[$col]);
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'human_friendly_duration':
                            if (is_numeric($row[$col]) && $row[$col] > 0) {
                                $row[$col] = mslib_befe::humanFriendlyDuration($row[$col]);
                            } else {
                                $row[$col] = '';
                            }
                            break;
                        case 'form':
                            $content = '<form method="';
                            switch ($valArray['formAction']) {
                                case 'post':
                                    $content .= 'POST';
                                    break;
                                case 'get':
                                default:
                                    $content .= 'GET';
                                    break;
                            }
                            $content .= '" action="' . $valArray['actionUrl'] . '" enctype="multipart/form-data">';
                            if ($valArray['content']) {
                                $content .= $valArray['content'];
                            }
                            if (is_array($valArray['hiddenFields'])) {
                                foreach ($valArray['hiddenFields'] as $hiddenFieldKey => $hiddenFieldVal) {
                                    foreach ($row as $tmpCol => $tmpVal) {
                                        $hiddenFieldVal = str_replace('###' . $tmpCol . '###', $row[$tmpCol], $hiddenFieldVal);
                                        $hiddenFieldVal = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $row[$tmpCol], $hiddenFieldVal);
                                    }
                                    $content .= '<input name="' . $hiddenFieldKey . '" type="hidden" value="' . $hiddenFieldVal . '" />';
                                }
                            }
                            $content .= '</form>';
                            $row[$col] = $content;
                            break;
                        case 'content':
                            foreach ($row as $tmpCol => $tmpVal) {
                                $value = $row[$tmpCol];
                                switch ($valArray['encode_type']) {
                                    case 'htmlspecialchars':
                                        $value = htmlspecialchars($value);
                                        break;
                                }
                                if (isset($valArray['maxChars']) && $valArray['maxChars'] > 0) {
                                    if (!empty($value) && strlen($value) > $valArray['maxChars']) {
                                        $value = substr($value, 0, $valArray['maxChars']) . '...';
                                    }
                                }
                                $valArray['content'] = str_replace('###shop_pid###', $this->shop_pid, $valArray['content']);
                                $valArray['content'] = str_replace('###' . $tmpCol . '###', $value, $valArray['content']);
                                $valArray['content'] = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $value, $valArray['content']);
                            }
                            $row[$col] = $valArray['content'];
                            break;
                        case 'products_detail_page_link':
                            $where = '';
                            if ($row['categories_id']) {
                                // get all cats to generate multilevel fake url
                                $level = 0;
                                $cats = mslib_fe::Crumbar($row['categories_id']);
                                $cats = array_reverse($cats);
                                $where = '';
                                if (count($cats) > 0) {
                                    foreach ($cats as $cat) {
                                        $where .= "categories_id[" . $level . "]=" . $cat['id'] . "&";
                                        $level++;
                                    }
                                    $where = substr($where, 0, (strlen($where) - 1));
                                    $where .= '&';
                                }
                                // get all cats to generate multilevel fake url eof
                            }
                            $product_detail_link = mslib_fe::typolink($this->conf['products_detail_page_pid'], '&' . $where . '&products_id=' . $row['products_id'] . '&tx_multishop_pi1[page_section]=products_detail', 1);
                            $row[$col] = '<a href="' . $product_detail_link . '" target="_blank">' . htmlspecialchars($row['products_name']) . '</a>';
                            break;
                        case 'boolean':
                            $status_html = '';
                            if (!$row[$col]) {
                                $status_html .= '<span class="admin_status_red" alt="' . $this->pi_getLL('no') . '"></span>';
                            } else {
                                $status_html .= '<span class="admin_status_green" alt="' . $this->pi_getLL('yes') . '"></span>';
                            }
                            $row[$col] = $status_html;
                            break;
                        case 'booleanToggle':
                            $status_html = '<span class="booleanToggle">';
                            if (!$row[$col]) {
                                $status_html .= '<span class="admin_status_red" alt="' . $this->pi_getLL('disable') . '"></span>';
                                if ($valArray['hrefEnable']) {
                                    foreach ($row as $tmpCol => $tmpVal) {
                                        $valArray['hrefEnable'] = str_replace('###' . $tmpCol . '###', $row[$tmpCol], $valArray['hrefEnable']);
                                        $valArray['hrefEnable'] = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $row[$tmpCol], $valArray['hrefEnable']);
                                        // attributes
                                        $valArray['hrefEnableAttributes'] = str_replace('###' . $tmpCol . '###', $row[$tmpCol], $valArray['hrefEnableAttributes']);
                                        $valArray['hrefEnableAttributes'] = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $row[$tmpCol], $valArray['hrefEnableAttributes']);
                                    }
                                    $status_html .= '<a href="' . $valArray['hrefEnable'] . '"' . ($valArray['hrefEnableAttributes'] ? ' ' . $valArray['hrefEnableAttributes'] : '') . '><span class="admin_status_green disabled" alt="' . $this->pi_getLL('enabled') . '"></span></a>';
                                }
                            } else {
                                if ($valArray['hrefDisable']) {
                                    foreach ($row as $tmpCol => $tmpVal) {
                                        $valArray['hrefDisable'] = str_replace('###' . $tmpCol . '###', $row[$tmpCol], $valArray['hrefDisable']);
                                        $valArray['hrefDisable'] = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $row[$tmpCol], $valArray['hrefDisable']);
                                        // attributes
                                        $valArray['hrefDisableAttributes'] = str_replace('###' . $tmpCol . '###', $row[$tmpCol], $valArray['hrefDisableAttributes']);
                                        $valArray['hrefDisableAttributes'] = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $row[$tmpCol], $valArray['hrefDisableAttributes']);
                                    }
                                    $status_html .= '<a href="' . $valArray['hrefDisable'] . '"' . ($valArray['hrefDisableAttributes'] ? ' ' . $valArray['hrefDisableAttributes'] : '') . '><span class="admin_status_red disabled" alt="' . $this->pi_getLL('disabled') . '"></span></a>';
                                }
                                $status_html .= '<span class="admin_status_green" alt="' . $this->pi_getLL('enable') . '"></span>';
                            }
                            $status_html .= '</span>';
                            $row[$col] = $status_html;
                            break;
                        case 'options_selectbox':
                            $options_selectbox_html = '';
                            $options_array = $valArray['optionsValue'];
                            $options_name = (isset($valArray['optionsName']) && !empty($valArray['optionsName']) ? $valArray['optionsName'] : $col);
                            $options_class = (isset($valArray['optionsClass']) && !empty($valArray['optionsClass']) ? ' ' . $valArray['optionsClass'] : 'change_' . $col);
                            if (is_array($options_array) && count($options_array)) {
                                $options_selectbox_html = '<select name="' . $options_name . '" class="form-control' . $options_class . '">
		                        <option value="">' . $this->pi_getLL('choose') . '</option>';
                                if (is_array($options_array)) {
                                    foreach ($options_array as $item) {
                                        $options_selectbox_html .= '<option value="' . $item['id'] . '"' . ($item['id'] == $originalValue ? ' selected' : '') . '>' . $item['name'] . '</option>' . "\n";
                                    }
                                }
                                $options_selectbox_html .= '</select>';
                            }
                            $row[$col] = $options_selectbox_html;
                            break;
                        case 'nl2br':
                            if (!empty($row[$col])) {
                                $row[$col] = nl2br($row[$col]);
                            }
                            break;
                        case 'unserialize':
                            if (!empty($row[$col])) {
                                $row[$col] = mslib_befe::print_r(unserialize($row[$col]));
                            }
                            break;
                        case 'json_decode':
                            if (!empty($row[$col])) {
                                $row[$col] = mslib_befe::print_r(json_decode($row[$col], true));
                            }
                            break;
                        case 'pre':
                            if (!empty($row[$col])) {
                                $row[$col] = '<pre>' . htmlspecialchars($row[$col]) . '</pre>';
                            }
                            break;
                        case 'page_uid':
                            if ($row[$col] > 0) {
                                $row[$col] = mslib_fe::getShopNameByPageUid($row[$col]);
                            } else {
                                $row[$col] = 'All';
                            }
                            break;
                    }
                    $adjustedValue = $row[$col];
                    if ($valArray['prefixValue']) {
                        $adjustedValue = $valArray['prefixValue'] . $adjustedValue;
                    }
                    if ($valArray['suffixValue']) {
                        $adjustedValue .= $valArray['suffixValue'];
                    }
                    if ($valArray['href']) {
                        foreach ($row as $tmpCol => $tmpVal) {
                            $valArray['href'] = str_replace('###' . $tmpCol . '###', $row[$tmpCol], $valArray['href']);
                            $valArray['href'] = str_replace('%23%23%23' . $tmpCol . '%23%23%23', $row[$tmpCol], $valArray['href']);
                        }
                        $adjustedValue = '<a ' . ($valArray['hrefNoFollow'] ? ' rel="nofollow"' : '') . ' href="' . $valArray['href'] . '"' . ($valArray['href_target'] ? ' target="' . $valArray['href_target'] . '""' : '') . '>' . $adjustedValue . '</a>';
                    }
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['tableColumnsPreProc'])) {
                        $conf = array(
                                'col' => &$col,
                                'row' => &$row,
                                'originalValue' => &$originalValue,
                                'adjustedValue' => &$adjustedValue,
                                'params' => &$params,
                                'valArray' => &$valArray,
                                'summarize' => &$summarize,
                                'interfaceKey' => $this->interfaceKey
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_interface.php']['tableColumnsPreProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $that);
                        }
                    }
                    $tdClass = array();
                    if ($valArray['align']) {
                        $tdClass[] = 'text-' . $valArray['align'];
                    }
                    if ($valArray['nowrap']) {
                        $tdClass[] = 'cellNoWrap';
                    }
                    if ($valArray['class']) {
                        $tdClass[] = $valArray['class'];
                    }
                    $tdAttributes = array();
                    if ($valArray['attributes']) {
                        $tdAttributes[] = $valArray['attributes'];
                    }
                    $tableContent .= '<td' . (count($tdClass) ? ' class="' . implode(' ', $tdClass) . '"' : '') . (count($tdAttributes) ? ' ' . implode(' ', $tdAttributes) : '') . '>' . $adjustedValue . '</td>';
                }
                $tableContent .= '</tr>';
                if ($params['settings']['returnResultSetAsArray']) {
                    $pageset['dataset'][$rowKey] = $row;
                }
            }
            $tableContent .= '</tbody>';
            if (!$params['settings']['skipSummarize']) {
                // Summarize footer
                $tableContent .= '<tfoot><tr>';
                if ($params['settings']['enableRowBasedCheckboxSelection']) {
                    $tableContent .= '<th></th>';
                }
                foreach ($params['tableColumns'] as $col => $valArray) {
                    switch ($valArray['valueType']) {
                        case 'currency':
                            $row[$col] = mslib_fe::amount2Cents($summarize[$col], 0);
                            break;
                        case 'number_format_2_decimals':
                        case 'number_format_thousand_seperator':
                            $row[$col] = round(number_format($summarize[$col], 2, '.', ''), 2);
                            break;
                        case 'number_format_thousands_seperator_without_decimals':
                            $row[$col] = number_format($summarize[$col], 0, '', '.');
                            break;
                        case 'number_format_8_decimals':
                            $row[$col] = rtrim(sprintf('%.8F', $summarize[$col]), '0');
                            if (substr($row[$col], (strlen($row[$col]) - 1), 1) == '.') {
                                $row[$col] = substr($row[$col], 0, -1);
                            }
                            break;
                        default:
                            $row[$col] = $valArray['title'];
                            break;
                    }
                    $tdClass = array();
                    if ($valArray['align']) {
                        $tdClass[] = 'text-' . $valArray['align'];
                    }
                    if ($valArray['nowrap']) {
                        $tdClass[] = 'cellNoWrap';
                    }
                    if ($valArray['class']) {
                        $tdClass[] = $valArray['class'];
                    }
                    $tableContent .= '<th' . (count($tdClass) ? ' class="' . implode(' ', $tdClass) . '"' : '') . '>' . $row[$col] . '</th>';
                    // $tableContent.='<th'.($valArray['align'] ? ' class="text-'.$valArray['align'].'"' : '').($valArray['nowrap'] ? ' nowrap' : '').'>'.$row[$col].'</th>';
                }
                $tableContent .= '</tr></tfoot>';
            }
            $tableContent .= '</table>';
            if ($params['settings']['enableActionSelectionForm']) {
                $action_selectbox .= '<div class="input-group">';
            }
            if ($params['settings']['enableActionSelectionForm'] && is_array($params['settings']['tableSelectionActions']) && count($params['settings']['tableSelectionActions'])) {
                $actions = $params['settings']['tableSelectionActions'];
                if (count($actions)) {
                    // custom page hook that can be controlled by third-party plugin eof
                    $classes=[];
                    $classes[]='form-control';
                    if (is_array($params['settings']['tableSelectionActionsClassArray'])) {
                        // Override classes
                        $classes=$params['settings']['tableSelectionActionsClassArray'];
                    }
                    $action_selectbox .= '<select name="tx_multishop_pi1[action]" id="msAdminTableAction' . $tableId . '" class="'.implode(' ',$classes).'"><option value="">' . htmlspecialchars($this->pi_getLL('choose_action')) . '</option>';
                    foreach ($actions as $key => $value) {
                        $action_selectbox .= '<option value="' . htmlspecialchars($key) . '">' . htmlspecialchars($value) . '</option>';
                    }
                    $action_selectbox .= '</select>';
                    $tableContent .= $action_selectbox;
                }
            }
            if ($params['settings']['contentBelowTable']) {
                $tableContent .= $params['settings']['contentBelowTable'];
            }
            if ($params['settings']['enableActionSelectionForm'] && is_array($params['settings']['tableSelectionActions']) && count($params['settings']['tableSelectionActions'])) {
                $tableContent .= '<div class="input-group-btn">
                    <input class="btn btn-success btn-disable-after-click" type="submit" name="submit" value="' . htmlspecialchars($this->pi_getLL('submit_form')) . '" />
                </div>';
            }
            if ($params['settings']['enableActionSelectionForm']) {
                $action_selectbox .= '</div>';
            }
            $tableContent .= '
			</div>
			';
            if ($params['settings']['contentBelowTableDiv']) {
                $tableContent .= $params['settings']['contentBelowTableDiv'];
            }
            if (!$params['settings']['disableForm']) {
                $tableContent .= '</form>';
            }
            // pagination
            $paginationMarkup = '';
            if (!$params['settings']['skipPaginationMarkup'] and $pageset['total_rows'] > $that->ms['MODULES']['PAGESET_LIMIT']) {
                $total_pages = ceil(($pageset['total_rows'] / $that->ms['MODULES']['PAGESET_LIMIT']));
                require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/admin_pages/includes/admin_pagination.php');
                $paginationMarkup = $tmp;
                $tableContent .= $tmp;
                $tmp = '';
            }
            // pagination eof
        }
        $content = '';
        if (!$params['settings']['skipPanelMarkup']) {
            $content .= '<div class="panel panel-default">';
            $content .= '<div class="panel-heading">';
        }
        if ($params['interfaceTitle']) {
            $interfaceTitle = $params['interfaceTitle'];
        } else {
            $interfaceTitle = $params['title'];
        }
        if (!$params['settings']['skipTitle']) {
            $content .= '<h3>' . htmlspecialchars($interfaceTitle) . '</h3>';
        }
        if (is_array($params['settings']['headingButtons'])) {
            $content .= '<div class="form-inline">';
            foreach ($params['settings']['headingButtons'] as $headingButton) {
                $content .= '<a href="' . $headingButton['href'] . '" class="' . $headingButton['btn_class'] . '"' . ($headingButton['attributes'] ? ' ' . $headingButton['attributes'] : '') . '><i class="' . $headingButton['fa_class'] . '"></i> ' . htmlspecialchars($headingButton['title']) . '</a> ';
            }
            $content .= '</div>';
        }
        if (!$params['settings']['skipPanelMarkup']) {
            $content .= '</div>';
        }
        if (!$params['settings']['skipPanelMarkup']) {
            $content .= '<div class="panel-body">';
        }
        if (!$params['settings']['skipTabMarkup']) {
            $GLOBALS['TSFE']->additionalHeaderData['msAdminTabJs'] = '<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(\'.nav-tabs a:first\').tab(\'show\');
			});
			</script>
			';
            $content .= '
				<div id="tab-container">
				<ul class="nav nav-tabs" id="admin_orders" role="tablist">
					<li role="presentation"><a href="#CmsListing" aria-controls="profile" role="tab" data-toggle="tab">' . htmlspecialchars($params['title']) . '</a></li>
				</ul>
				<div class="tab-content">
			';
        }
        $searchForm = '';
        if ($params['settings']['contentAboveForm']) {
            $searchForm .= $params['settings']['contentAboveForm'];
        }
        if ($params['settings']['enableKeywordSearch']) {
            $searchForm .= '
			<form id="form1" name="form1" method="get" action="index.php">
				<div class="well">
					<div class="row formfield-container-wrapper">
						';
            foreach ($params['searchForm']['hiddenFields'] as $key => $val) {
                $searchForm .= '<input name="' . $key . '" type="hidden" value="' . htmlspecialchars($val) . '" />' . "\n";
            }
            $searchForm .= '
						<div class="col-sm-8 formfield-wrapper">
							<div class="form-inline">
								<label class="control-label">' . $that->pi_getLL('keyword') . '</label>
								<input type="text" name="tx_multishop_pi1[keyword]" class="form-control" value="' . htmlspecialchars($that->get['tx_multishop_pi1']['keyword']) . '" />
								<input type="submit" name="Search" class="btn btn-success" value="' . $that->pi_getLL('search') . '" />
							</div>
						</div>
						';
            if (!$params['settings']['hideLimitSelectbox']) {
                $searchForm .= '
                <div class="col-sm-4 formfield-wrapper">
                    <div class="pull-right form-inline">
                        <label class="control-label">' . $that->pi_getLL('limit_number_of_records_to') . '</label>
                        ' . $limit_search_result_selectbox . '
                    </div>
                </div>';
            }
            $searchForm .= '
					</div>
				</div>
			</form>
			';
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceSearchFormPostProc'])) {
                $interfaceKey =& $this->interfaceKey;
                $params_searchform = array(
                        'interfaceKey' => &$interfaceKey,
                        'searchForm' => &$searchForm,
                        'adminInterfaceParams' => &$params,
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/class.tx_mslib_admin_interface.php']['setAdminInterfaceSearchFormPostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_searchform, $this);
                }
            }
        }
        if (!$params['settings']['skipTabMarkup']) {
            $content .= '
				<div role="tabpanel" id="CmsListing" class="tab-pane">
					' . $searchForm . '
					' . $tableContent . '
				</div>
			</div>
			</div>
			';
        } else {
            $content .= $searchForm . $tableContent;
        }
        if ($params['settings']['skipRecordCount'] || ($params['settings']['skipRecordCountWhenZeroResults'] && !$pageset['total_rows'])) {
            $skipRecordCount = 1;
        }
        if ($params['settings']['skipTotalCount'] || ($params['settings']['skipTotalCountWhenZeroResults'] && !$params['summarizeData']['totalRecordsInTable'])) {
            $skipTotalCount = 1;
        }
        $recordCountMarkup = '';
        if (!$skipRecordCount) {
            $recordCountMarkup .= $this->pi_getLL('found_records') . ': <strong>' . number_format($pageset['total_rows'], 0, '', '.') . '</strong><br/>';
        }
        if (!$skipTotalCount && isset($params['summarizeData']['totalRecordsInTable'])) {
            $recordCountMarkup .= $this->pi_getLL('total_records_in_database') . ': <strong>' . number_format($params['summarizeData']['totalRecordsInTable'], 0, '', '.') . '</strong><br/>';
        }
        if ($recordCountMarkup) {
            $content .= '<p class="mt-10 text-center">' . $recordCountMarkup . '</p>';
        }
        if (!$params['settings']['skipFooterMarkup']) {
            $content .= '<div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> ' . $that->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></div>';
        }
        if (!$params['settings']['skipPanelMarkup']) {
            $content .= '</div>';
            $content .= '</div>';
        }
        if (is_array($columnSorterData) && $countColumnSorterData) {
            $sort_js = array();
            // only for non-sortable column
            foreach ($columnSorterData as $col_idx => $col_value) {
                if (!$col_value) {
                    $sort_js[] = $col_idx . ':{ sorter: false}';
                }
            }
            $GLOBALS['TSFE']->additionalHeaderData['tablesorter_css'] = '<link rel="stylesheet" type="text/css" href="typo3conf/ext/multishop/templates/global/css/tablesorter.css" media="all" />';
            $GLOBALS['TSFE']->additionalHeaderData['tablesorter_js' . $tableId] = '<script type="text/javascript" data-ignore="1">
			jQuery(document).ready(function ($) {
				$(\'#msAdminTableInterface' . $tableId . '\').tablesorter({
				    headers: { ' . implode(', ', $sort_js) . ' }
				});
			});
			</script>
			';
        }
        if ($params['settings']['returnOnlyWhenRecordsFound'] && !$pageset['total_rows']) {
            // return nothing
            return;
        } else {
            if ($params['settings']['returnResultSetAsArray']) {
                $array = array();
                $array['searchForm'] = $searchForm;
                $array['paginationMarkup'] = $paginationMarkup;
                $array['dataset'] = $pageset['dataset'];
                $array['total_rows'] = $pageset['total_rows'];
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
