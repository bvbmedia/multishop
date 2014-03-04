<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// now parse all the objects in the tmpl file
if ($this->conf['admin_manufacturers_tmpl_path']) {
	$template = $this->cObj->fileResource($this->conf['admin_manufacturers_tmpl_path']);
} else {
	$template = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_manufacturers.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template'] 	= $this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['results'] 	= $this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['noresults'] 	= $this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
$subparts['manufacturers'] 	= $this->cObj->getSubpart($subparts['results'], '###MANUFACTURERS###');

if (is_numeric($this->get['status']) and is_numeric($this->get['manufacturers_id'])) {
	$updateArray=array();
	$updateArray['status']	 =	$this->get['status'];
	$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$this->get['manufacturers_id'].'\'',$updateArray);
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
} elseif (is_numeric($this->get['delete']) and is_numeric($this->get['manufacturers_id'])) {
	mslib_befe::deleteManufacturer($this->get['manufacturers_id']);		
}
if ($this->get['Search'] and ($this->get['limit'] != $this->cookie['limit'])) {	
	$this->cookie['limit'] = $this->get['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
	$this->get['limit']=$this->cookie['limit'];
} else {
	$this->get['limit']=10;
}
$this->ms['MODULES']['PAGESET_LIMIT']=$this->get['limit'];	
if (is_numeric($this->get['p'])) 	$p=$this->get['p'];
if ($p >0) {
	$queryData['offset']=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$queryData['offset']=0;
}
$this->searchKeywords=array();
if ($this->get['tx_multishop_pi1']['searchByChar']) {
	switch ($this->get['tx_multishop_pi1']['searchByChar']) {
		case '0-9':
			for ($i=0;$i<10;$i++) {
				$this->searchKeywords[]=$i;
			}
		break;
		case '#':
			$this->searchKeywords[]='#';
		break;
		case 'all':
		break;
		default:
			$this->searchKeywords[]=$this->get['tx_multishop_pi1']['searchByChar'];
		break;
	}
	$this->searchMode='keyword%';
} elseif ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword'] = trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'],TRUE);
	$this->get['tx_multishop_pi1']['keyword'] = mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
		
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}
$searchCharNav='<div id="msAdminSearchByCharNav"><ul>';
$chars=array();
$chars=array('0-9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','#','all');
foreach ($chars as $char) {
	$searchCharNav.='<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[searchByChar]='.$char.'&tx_multishop_pi1[page_section]=admin_manufacturers').'">'.strtoupper($char).'</a></li>';	
}
$searchCharNav.='</ul></div>';
		
$limit_search_result_selectbox = '<label>'.$this->pi_getLL('limit_number_of_records_to').':</label>';
$limit_search_result_selectbox .= '<select name="limit">';
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
	$limit_search_result_selectbox .='<option value="'.$limit.'"'.($limit==$this->get['limit']?' selected="selected"':'').'>'.$limit.'</option>';
}
$limit_search_result_selectbox .='</select>';
					
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
			$keywordOr[]="manufacturers_name like '".$this->sqlKeyword."'";
		}
	}	
	$queryData['where'][]="(".implode(" OR ",$keywordOr).")";
}
$queryData['select'][]='*';
$queryData['from'][]='tx_multishop_manufacturers m';

switch ($this->get['tx_multishop_pi1']['order_by']) {
	case 'name':
		$order_by='manufacturers_name';
		break;
	case 'date_added':
		$order_by='date_added';
		break;
	case 'id':
		$order_by='manufacturers_id';
		break;
	default:
		$order_by='sort_order,manufacturers_name';
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
$queryData['order_by']=$orderby;

$queryData['limit']=$this->ms['MODULES']['PAGESET_LIMIT'];
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p >0)  {
	$queryData['offset']=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$queryData['offset']=0;
}
$pageset=mslib_fe::getRecordsPageSet($queryData);
if (!count($pageset['dataset'])) {
	$subpartArray = array();
	$subpartArray['###LABEL_NO_RESULT###'] = $this->pi_getLL('no_records_found','No records found.').'.<br />';
	$noresults = $this->cObj->substituteMarkerArrayCached($subparts['noresults'], array(), $subpartArray);
} else {
	$manufacturers=array();	
	foreach ($pageset['dataset'] as $row) {
		$manufacturers[]=$row;				
	}
	if (count($manufacturers) > 0) {
		$tr_type='even';
		$contentItem = '';
		foreach ($manufacturers as $row) {
			$status_html = '';
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';	
			}
			if (strlen($row['date_added'])==4) {
				$row['date_added']='';
			}
			if ($row['date_added']) {
				$row['date_added']=date("Y-m-d G:i:s",$row['date_added']);
			}
			
			if (!$row['status']) {
				$status_html.='<span class="admin_status_red" alt="Disable"></span>';
				$status_html.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&manufacturers_id='.$row['manufacturers_id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
			} else {
				$status_html.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&manufacturers_id='.$row['manufacturers_id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
				$status_html.='<span class="admin_status_green" alt="Enable"></span>';
			}
			
			$markerArray=array();
			$markerArray['ROW_TYPE'] 					= $tr_type;
			$markerArray['MANUFACTURER_ID'] 			= $row['manufacturers_id'];
			$markerArray['MANUFACTURER_EDIT_LINK'] 		= mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$row['manufacturers_id']).'&action=edit_manufacturer';
			$markerArray['MANUFACTURER_NAME']			= $row['manufacturers_name'];
			$markerArray['MANUFACTURER_DATE_ADDED'] 	= strftime("%x %X", strtotime($row['date_added']));
			$markerArray['MANUFACTURER_STATUS'] 		= $status_html;
			$markerArray['MANUFACTURER_DELETE_LINK'] 	= mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&manufacturers_id='.$row['manufacturers_id'].'&delete=1');
			$contentItem .= $this->cObj->substituteMarkerArray($subparts['manufacturers'], $markerArray,'###|###');
		}

		$subpartArray = array();
		$query_string=mslib_fe::tep_get_all_get_params(array('tx_multishop_pi1[action]','tx_multishop_pi1[order_by]','tx_multishop_pi1[order]','p','Submit','weergave','clearcache'));
		$key='id';
		if ($this->get['tx_multishop_pi1']['order_by']==$key) {
			$final_order_link=$order_link;
		} else {
			$final_order_link='a';
		}
		$subpartArray['###LABEL_MANUFACTURER_ID###'] 					= '<a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_manufacturers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('id').'</a>';
		$subpartArray['###LABEL_FOOTER_MANUFACTURER_ID###'] 			= '<a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_manufacturers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('id').'</a>';
		
		$key='name';
		if ($this->get['tx_multishop_pi1']['order_by']==$key) {
			$final_order_link=$order_link;
		} else {
			$final_order_link='a';
		}
		$subpartArray['###LABEL_MANUFACTURER_NAME###'] 					= '<a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_manufacturers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('manufacturer').'</a>';
		$subpartArray['###LABEL_FOOTER_MANUFACTURER_NAME###'] 			= '<a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_manufacturers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('manufacturer').'</a>';
		
		$key='date_added';
		if ($this->get['tx_multishop_pi1']['order_by']==$key) {
			$final_order_link=$order_link;
		} else {
			$final_order_link='a';
		}
		$subpartArray['###LABEL_MANUFACTURER_DATE_ADDED###'] 			= '<a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_manufacturers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('date_added').'</a>';
		$subpartArray['###LABEL_FOOTER_MANUFACTURER_DATE_ADDED###'] 	= '<a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_manufacturers&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.$this->pi_getLL('date_added').'</a>';
		$subpartArray['###LABEL_MANUFACTURER_STATUS###'] 				= $this->pi_getLL('status');
		$subpartArray['###LABEL_MANUFACTURER_ACTION###'] 				= $this->pi_getLL('action');
		$subpartArray['###LABEL_FOOTER_MANUFACTURER_STATUS###'] 		= $this->pi_getLL('status');
		$subpartArray['###LABEL_FOOTER_MANUFACTURER_ACTION###'] 		= $this->pi_getLL('action');
		$subpartArray['###MANUFACTURERS###'] 							= $contentItem;
		$results = $this->cObj->substituteMarkerArrayCached($subparts['results'], array(), $subpartArray);
	}
}

$subpartArray = array();
$subpartArray['###SHOP_PID###'] 					= $this->shop_pid;
$subpartArray['###LABEL_KEYWORD###'] 				= ucfirst($this->pi_getLL('keyword'));
$subpartArray['###VALUE_KEYWORD###'] 				= htmlspecialchars($this->get['tx_multishop_pi1']['keyword']);
$subpartArray['###LABEL_SEARCH###'] 				= $this->pi_getLL('search');
$subpartArray['###INPUT_LIMIT_RESULT_SELECTBOX###'] = $limit_search_result_selectbox;
$subpartArray['###SEARCH_NAV###'] 					= $searchCharNav;
$subpartArray['###RESULTS###'] 						= $results;
$subpartArray['###NORESULTS###'] 					= $noresults;
$content .= $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
// pagination
if (!$this->ms['nopagenav'] and $pageset['total_rows'] > $this->ms['MODULES']['PAGESET_LIMIT']) {
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
	$content.=$tmp;
}
// pagination eof

$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
$content.='<div class="float_right"><div class="add_manufacturer"><a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$row['manufacturers_id']).'&action=add_manufacturer" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="float_right msadmin_button">'.t3lib_div::strtoupper($this->pi_getLL('add_manufacturer')).'</a></div></div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
?>