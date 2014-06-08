<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_cms_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_cms_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_cms.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['results']=$this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['cms_list']=$this->cObj->getSubpart($subparts['results'], '###CMS_LIST###');
$subparts['noresults']=$this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
//$tmpcontent.='<div class="main-heading"><h2>'.htmlspecialchars(ucfirst(t3lib_div::strtolower($this->pi_getLL('admin_cms')))).'</h2></div>';
if (is_numeric($this->get['status']) and is_numeric($this->get['cms_id'])) {
	$updateArray=array();
	$updateArray['status']=$this->get['status'];
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_cms', 'id=\''.$this->get['cms_id'].'\'', $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
} elseif (is_numeric($this->get['delete']) and is_numeric($this->get['cms_id'])) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_cms', 'id=\''.$this->get['cms_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_cms_description', 'id=\''.$this->get['cms_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
if ($this->get['Search'] and ($this->get['limit']!=$this->cookie['limit'])) {
	$this->cookie['limit']=$this->get['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
	$this->get['limit']=$this->cookie['limit'];
} else {
	$this->get['limit']=10;
}
$this->ms['MODULES']['PAGESET_LIMIT']=$this->get['limit'];
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
$this->searchKeywords=array();
if ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword']=trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'], TRUE);
	$this->get['tx_multishop_pi1']['keyword']=mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}
$limit_search_result_selectbox='<label>'.$this->pi_getLL('limit_number_of_records_to').':</label><select name="limit">';
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
	$limit_search_result_selectbox.='<option value="'.$limit.'"'.($limit==$this->get['limit'] ? ' selected="selected"' : '').'>'.$limit.'</option>';
}
$limit_search_result_selectbox.='</select>';
$queryData=array();
$queryData['where']=array();
if (count($this->searchKeywords)) {
	$keywordOr=array();
	foreach ($this->searchKeywords as $searchKeyword) {
		if ($searchKeyword) {
			switch ($this->searchMode) {
				case 'keyword%':
					$this->sqlKeyword=addslashes($searchKeyword).'%';
					break;
				case '%keyword%':
				default:
					$this->sqlKeyword='%'.addslashes($searchKeyword).'%';
					break;
			}
			$keywordOr[]="c.type like '".$this->sqlKeyword."'";
			$keywordOr[]="cd.name like '".$this->sqlKeyword."'";
			$keywordOr[]="cd.content like '".$this->sqlKeyword."'";
		}
	}
	$queryData['where'][]="(".implode(" OR ", $keywordOr).")";
}
switch ($this->get['tx_multishop_pi1']['order_by']) {
	case 'name':
		$order_by='cd.name';
		break;
	case 'type':
		$order_by='c.type';
		break;
	case 'crdate':
		$order_by='c.crdate';
		break;
	case 'id':
	default:
		$order_by='c.id';
		break;
}
switch ($this->get['tx_multishop_pi1']['order']) {
	case 'a':
		$order='asc';
		$order_link='d';
		break;
	case 'd':
	default:
		$order='desc';
		$order_link='a';
		break;
}
$orderby[]=$order_by.' '.$order;
$queryData['where'][]='c.page_uid=\''.$this->shop_pid.'\' and cd.language_id='.$GLOBALS['TSFE']->sys_language_uid.' and c.id=cd.id';
$queryData['select'][]='*';
$queryData['from'][]='tx_multishop_cms c, tx_multishop_cms_description cd';
$queryData['order_by']=$orderby;
$queryData['limit']=$this->ms['MODULES']['PAGESET_LIMIT'];
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p>0) {
	$queryData['offset']=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$queryData['offset']=0;
}
$pageset=mslib_fe::getRecordsPageSet($queryData);
if (!count($pageset['dataset'])) {
	$subpartArray=array();
	$subpartArray['###LABEL_NO_RESULTS###']=$this->pi_getLL('no_records_found', 'No records found.');
	$no_results=$this->cObj->substituteMarkerArrayCached($subparts['noresults'], array(), $subpartArray);
} else {
	$tr_type='even';
	$contentItem='';
	foreach ($pageset['dataset'] as $row) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		if (!$row['name']) {
			$row['name']='No title';
		}
		$status_html='';
		if (!$row['status']) {
			$status_html.='<span class="admin_status_red" alt="Disable"></span>';
			$status_html.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
		} else {
			$status_html.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
			$status_html.='<span class="admin_status_green" alt="Enable"></span>';
		}
		$markerArray=array();
		$markerArray['ROW_TYPE']=$tr_type;
		$markerArray['CMS_ID']='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cms_id='.$row['id'].'&action=edit_cms',1).'">'.$row['id'].'</a>';
		$markerArray['CMS_TITLE']='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cms_id='.$row['id'].'&action=edit_cms',1).'">'.htmlspecialchars($row['name']).'</a>';
		$markerArray['CMS_TYPE']='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&cms_id='.$row['id'].'&action=edit_cms',1).'">'.htmlspecialchars($row['type']).'</a>';
		$markerArray['CMS_DATE_CREATED']=strftime("%x %X", $row['crdate']);
		$markerArray['CMS_STATUS']=$status_html;
		$markerArray['CMS_REMOVE_BUTTON']='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&delete=1').'" onclick="return confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')" class="admin_menu_remove" alt="Remove"></a>';
		$contentItem.=$this->cObj->substituteMarkerArray($subparts['cms_list'], $markerArray, '###|###');
	}
	$subpartArray=array();
	$query_string=mslib_fe::tep_get_all_get_params(array(
		'tx_multishop_pi1[action]',
		'tx_multishop_pi1[order_by]',
		'tx_multishop_pi1[order]',
		'p',
		'Submit',
		'weergave',
		'clearcache'
	));
	$key='id';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_ID###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_HEADER_CMS_ID###']=htmlspecialchars($this->pi_getLL('id'));
	$subpartArray['###FOOTER_SORTBY_LINK_ID###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_FOOTER_CMS_ID###']=htmlspecialchars($this->pi_getLL('id'));
	$key='name';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_TITLE###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_HEADER_CMS_TITLE###']=htmlspecialchars($this->pi_getLL('name'));
	$subpartArray['###FOOTER_SORTBY_LINK_TITLE###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_FOOTER_CMS_TITLE###']=htmlspecialchars($this->pi_getLL('name'));
	$key='type';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_TYPE###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###FOOTER_SORTBY_LINK_TYPE###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$key='crdate';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_DATE_ADDED###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_HEADER_CMS_DATE_ADDED###']=htmlspecialchars($this->pi_getLL('date_added'));
	$subpartArray['###FOOTER_SORTBY_LINK_DATE_ADDED###']=mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_FOOTER_CMS_DATE_ADDED###']=htmlspecialchars($this->pi_getLL('date_added'));
	$subpartArray['###LABEL_HEADER_STATUS###']=$this->pi_getLL('status');
	$subpartArray['###LABEL_HEADER_CMS_ACTION###']=$this->pi_getLL('action');
	$subpartArray['###LABEL_FOOTER_STATUS###']=$this->pi_getLL('status');
	$subpartArray['###LABEL_FOOTER_CMS_ACTION###']=$this->pi_getLL('action');
	$subpartArray['###CMS_LIST###']=$contentItem;
	$results=$this->cObj->substituteMarkerArrayCached($subparts['results'], array(), $subpartArray);
	// pagination
	if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['PAGESET_LIMIT']) {
		$content='';
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
		$results.=$tmp;
	}
	// pagination eof
}
$subpartArray=array();
$subpartArray['###CMS_GROUP_ID###']=htmlspecialchars($group);
$subpartArray['###SHOP_PID###']=$this->shop_pid;
$subpartArray['###LABEL_KEYWORD###']=ucfirst($this->pi_getLL('keyword'));
$subpartArray['###VALUE_KEYWORD###']=htmlspecialchars($this->get['tx_multishop_pi1']['keyword']);
$subpartArray['###LABEL_SEARCH###']=$this->pi_getLL('search');
$subpartArray['###INPUT_LIMIT_RESULT_SELECTBOX###']=$limit_search_result_selectbox;
$subpartArray['###RESULTS###']=$results;
$subpartArray['###NORESULTS###']=$no_results;
$content=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
$content.='<div class="float_right"><a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&action=edit_cms').'" class="admin_menu_add label">'.htmlspecialchars($this->pi_getLL('add_new_page')).'</a></div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';

?>