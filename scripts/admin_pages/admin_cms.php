<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_cms_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_cms_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/admin_cms.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['results']=$this->cObj->getSubpart($subparts['template'], '###RESULTS###');
$subparts['cms_list']=$this->cObj->getSubpart($subparts['results'], '###CMS_LIST###');
$subparts['noresults']=$this->cObj->getSubpart($subparts['template'], '###NORESULTS###');
//$tmpcontent.='<div class="main-heading"><h2>'.htmlspecialchars(ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_cms')))).'</h2></div>';
if (isset($this->get['download']) && $this->get['download']=='cms' && is_array($this->get['downloadCMS'])) {
	$rowsData=array();
	foreach ($this->get['downloadCMS'] as $cms_id) {
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_cms c', // FROM ...
			'id=\''.$cms_id.'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			foreach ($row as $col_name=>$col_val) {
				$rowsData[$cms_id]['cms_data'][$col_name]=$col_val;
			}
		}
		$query_desc=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_cms_description cd', // FROM ...
			'id=\''.$cms_id.'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res_desc=$GLOBALS['TYPO3_DB']->sql_query($query_desc);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_desc)>0) {
			while ($row_desc=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_desc)) {
				foreach ($row_desc as $col_desc_name=>$col_desc_val) {
					$rowsData[$cms_id]['cms_data']['description'][$row_desc['language_id']][$col_desc_name]=$col_desc_val;
				}
			}
		}
	}
	$serial_data='';
	if (count($rowsData)>0) {
		$serial_data=serialize($rowsData);
	}
	$filename='multishop_cms_'.date('YmdHis').'.txt';
	$filepath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/'.$filename;
	file_put_contents($filepath, $serial_data);
	header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
	header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
	readfile($filepath); //Read and stream the file
	@unlink($filepath);
	exit();
}
if (isset($this->get['upload']) && $this->get['upload']=='cms' && $_FILES) {
	if (!$_FILES['cms_file']['error']) {
		$filename=$_FILES['cms_file']['name'];
		$target=$this->DOCUMENT_ROOT.'/uploads/tx_multishop'.$filename;
		if (move_uploaded_file($_FILES['cms_file']['tmp_name'], $target)) {
			$cms_content=file_get_contents($target);
			$unserial_cms_data=unserialize($cms_content);
			if (is_array($unserial_cms_data) && count($unserial_cms_data)) {
				foreach ($unserial_cms_data as $cms_data) {
					$insertArray=array();
					if (is_array($cms_data['cms_data']) && count($cms_data['cms_data'])) {
						foreach ($cms_data['cms_data'] as $cms_col=>$cms_val) {
							if ($cms_col && $cms_col!='description' && $cms_col!='id') {
								switch ($cms_col) {
									case 'page_uid':
										$insertArray['page_uid']=$this->shop_pid;
										break;
									case 'hash':
										$insertArray['hash']=md5(uniqid('', true));
										break;
									case 'crdate':
										$insertArray['crdate']=time();
										break;
									default:
										$insertArray[$cms_col]=$cms_val;
										break;
								}
							}
						}
					}
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_cms', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$cms_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
					if (is_array($cms_data['cms_data']['description']) && count($cms_data['cms_data']['description'])) {
						foreach ($cms_data['cms_data']['description'] as $language_id=>$cms_desc_data) {
							if (is_array($cms_desc_data) && count($cms_desc_data)) {
								$insertArrayDesc=array();
								foreach ($cms_desc_data as $cms_desc_col_name=>$cms_desc_val) {
									switch ($cms_desc_col_name) {
										case 'id':
											$insertArrayDesc['id']=$cms_id;
											break;
										default:
											$insertArrayDesc[$cms_desc_col_name]=$cms_desc_val;
											break;
									}
								}
								$query_desc=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_cms_description', $insertArrayDesc);
								$GLOBALS['TYPO3_DB']->sql_query($query_desc);
							}
						}
					}
				}
			}
			@unlink($target);
		}
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_cms'));
	exit();
}
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
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_cms'));
	exit();
}
if ($this->get['Search'] and ($this->get['cmsLimit']!=$this->cookie['cmsLimit'])) {
	$this->cookie['cmsLimit']=$this->get['cmsLimit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['cmsLimit']) {
	$this->get['cmsLimit']=$this->cookie['cmsLimit'];
} else {
	$this->get['cmsLimit']=30;
}
$this->ms['MODULES']['PAGESET_LIMIT']=$this->get['cmsLimit'];
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
$this->searchKeywords=array();
if ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword']=trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'], true);
	$this->get['tx_multishop_pi1']['keyword']=mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}
$limit_search_result_selectbox='<div class="form-inline"><div class="form-group"><label>'.$this->pi_getLL('limit_number_of_records_to').':</label><select name="limit" class="form-control">';
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
	$limit_search_result_selectbox.='<option value="'.$limit.'"'.($limit==$this->get['cmsLimit'] ? ' selected="selected"' : '').'>'.$limit.'</option>';
}
$limit_search_result_selectbox.='</select></div></div>';
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
		$order_by='c.type';
		break;
}
switch ($this->get['tx_multishop_pi1']['order']) {
	case 'a':
	default:
		$order='asc';
		$order_link='d';
		break;
	case 'd':
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
			$row['name']=$this->pi_getLL('admin_label_no_title');
		}
		$status_html='';
		if (!$row['status']) {
			$status_html.='<span class="admin_status_red" alt="'.$this->pi_getLL('disable').'"></span>';
			$status_html.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&status=1').'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('enabled').'"></span></a>';
		} else {
			$status_html.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&status=0').'"><span class="admin_status_red disabled" alt="'.$this->pi_getLL('disabled').'"></span></a>';
			$status_html.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
		}
		$markerArray=array();
		$markerArray['ROW_TYPE']=$tr_type;
		$markerArray['DOWNLOAD_CMS_CHECKBOX']='';
		if ($this->ROOTADMIN_USER) {
			$markerArray['DOWNLOAD_CMS_CHECKBOX']='<td class="cellCheckbox">
				<div class="checkbox checkbox-success checkbox-inline">
            	<input type="checkbox" name="downloadCMS[]" class="download_cms_cb" id="value_'.$row['id'].'" value="'.$row['id'].'"/><label for="value_'.$row['id'].'"></label>
            	</div>
            </td>';
		}
		$markerArray['CMS_ID']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_cms&cms_id='.$row['id'].'&action=edit_cms', 1).'">'.$row['id'].'</a>';
		$markerArray['CMS_TITLE']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_cms&cms_id='.$row['id'].'&action=edit_cms', 1).'">'.htmlspecialchars($row['name']).'</a>';
		$markerArray['CMS_TYPE']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_cms&cms_id='.$row['id'].'&action=edit_cms', 1).'">'.htmlspecialchars($row['type']).'</a>';
		$markerArray['CMS_DATE_CREATED']=strftime("%x %X", $row['crdate']);
		$markerArray['CMS_STATUS']=$status_html;
		$markerArray['CMS_REMOVE_BUTTON']='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&delete=1').'" onclick="return confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')" class="text-danger admin_menu_remove" alt="Remove"><i class="fa fa-trash-o fa-lg"></i></a>';
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
	$subpartArray['###HEADER_SORTBY_LINK_ID###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_HEADER_CMS_ID###']=htmlspecialchars($this->pi_getLL('id'));
	$subpartArray['###FOOTER_SORTBY_LINK_ID###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_FOOTER_CMS_ID###']=htmlspecialchars($this->pi_getLL('id'));
	$key='name';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_TITLE###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_HEADER_CMS_TITLE###']=htmlspecialchars($this->pi_getLL('name'));
	$subpartArray['###FOOTER_SORTBY_LINK_TITLE###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_FOOTER_CMS_TITLE###']=htmlspecialchars($this->pi_getLL('name'));
	$key='type';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_TYPE###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###FOOTER_SORTBY_LINK_TYPE###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$key='crdate';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) {
		$final_order_link=$order_link;
	} else {
		$final_order_link='a';
	}
	$subpartArray['###HEADER_SORTBY_LINK_DATE_ADDED###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_HEADER_CMS_DATE_ADDED###']=htmlspecialchars($this->pi_getLL('date_added'));
	$subpartArray['###FOOTER_SORTBY_LINK_DATE_ADDED###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string);
	$subpartArray['###LABEL_FOOTER_CMS_DATE_ADDED###']=htmlspecialchars($this->pi_getLL('date_added'));
	$subpartArray['###LABEL_HEADER_STATUS###']=$this->pi_getLL('status');
	$subpartArray['###LABEL_HEADER_CMS_ACTION###']=$this->pi_getLL('action');
	$subpartArray['###LABEL_FOOTER_STATUS###']=$this->pi_getLL('status');
	$subpartArray['###LABEL_FOOTER_CMS_ACTION###']=$this->pi_getLL('action');
	$subpartArray['###CMS_LIST###']=$contentItem;
	$subpartArray['###HEADER_CHECKALL_COLUMN###']='';
	$subpartArray['###FOOTER_CHECKALL_COLUMN###']='';
	$subpartArray['###DOWNLOAD_CMS_BUTTON###']='';
	if ($this->ROOTADMIN_USER) {
		$subpartArray['###HEADER_CHECKALL_COLUMN###']='<th class="cellCheckbox"><div class="checkbox checkbox-success checkbox-inline"><input type="checkbox" id="checkAllCMS"/><label for="checkAllCMS"></label></th>';
		$subpartArray['###FOOTER_CHECKALL_COLUMN###']='<th class="cellCheckbox">&nbsp;</th>';
		$subpartArray['###DOWNLOAD_CMS_BUTTON###']='<tr>
				<td colspan="7"><input type="button" class="submit btn btn-success" id="dl_submit" value="'.$this->pi_getLL('download_selected_cms').'"/></td>
			</tr>';
	}
	$results=$this->cObj->substituteMarkerArrayCached($subparts['results'], array(), $subpartArray);
	// pagination
	if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['PAGESET_LIMIT']) {
		$content='';
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
		$results.=$tmp;
	}
	// pagination eof
}
$subpartArray=array();
$subpartArray['###CMS_GROUP_ID###']=htmlspecialchars($group);
$subpartArray['###SHOP_PID###']=$this->shop_pid;
$subpartArray['###ADMIN_CMS_LINK###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&download=cms');
//$subpartArray['###LABEL_UPLOAD_CMS###']=$this->pi_getLL('upload_cms');
//$subpartArray['###ADMIN_CMS_UPLOAD_URL###']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&upload=cms');
//$subpartArray['###LABEL_FILE###']=$this->pi_getLL('file');
$subpartArray['###IMPORT_CMS_FILE###']='';
if ($this->ROOTADMIN_USER) {
	$subpartArray['###IMPORT_CMS_FILE###']='
		<fieldset id="scheduled_import_jobs_form">
                <div class="page-header"><h4>'.$this->pi_getLL('upload_cms').'</h4></div>
                <form action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms&upload=cms').'" method="post" enctype="multipart/form-data" name="upload_cms" id="upload_cms" class="form-horizontal blockSubmitForm">
                    <div class="form-group">
                        <label for="upload_cms_file" class="control-label col-md-2">'.$this->pi_getLL('file').'</label>
                        <div class="col-md-10">
                        	<div class="input-group">
	                        	<input type="file" name="cms_file" class="form-control">
	                        	<span class="input-group-btn">
	                        		<input type="submit" name="upload_cms_file" class="submit btn btn-success" id="upload_cms_file" value="upload">
	                        	</span>
	                        </div>
                        </div>
                    </div>
                </form>
            </fieldset>
		';
}
$subpartArray['###LABEL_KEYWORD###']=ucfirst($this->pi_getLL('keyword'));
$subpartArray['###VALUE_KEYWORD###']=htmlspecialchars($this->get['tx_multishop_pi1']['keyword']);
$subpartArray['###LABEL_SEARCH###']=$this->pi_getLL('search');
$subpartArray['###INPUT_LIMIT_RESULT_SELECTBOX###']=$limit_search_result_selectbox;
$subpartArray['###RESULTS###']=$results;
$subpartArray['###NORESULTS###']=$no_results;
$content=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
$content=mslib_fe::shadowBox($content);
$content.='<hr><a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_cms&action=edit_cms').'" class="btn btn-success admin_menu_add">'.htmlspecialchars($this->pi_getLL('add_new_page')).'</a>';
$content.='<hr><div class="clearfix"><div class="pull-right"><a class="btn btn-success" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div></div></div>';

?>