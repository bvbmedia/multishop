<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

// now parse all the objects in the tmpl file
if ($this->conf['admin_edit_manufacturer_tmpl_path']) {
	$template = $this->cObj->fileResource($this->conf['admin_edit_manufacturer_tmpl_path']);
} else {
	$template = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_edit_manufacturer.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template'] 	= $this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['manufacturers_images'] 	= $this->cObj->getSubpart($subparts['template'], '###MANUFACTURER_IMAGES###');
$subparts['manufacturers_content'] 	= $this->cObj->getSubpart($subparts['template'], '###MANUFACTURERS_CONTENT###');
$subparts['manufacturers_meta'] 	= $this->cObj->getSubpart($subparts['template'], '###MANUFACTURERS_META###');

if ($this->get['manufacturers_id']) {
	$_REQUEST['manufacturers_id']=$this->get['manufacturers_id'];
}
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript">
window.onload = function(){
  var text_input = document.getElementById (\'manufacturers_name\');
  text_input.focus ();
  text_input.select ();
}
</script>';

$update_manufacturers_image='';
// hidden filename that is retrieved from the ajax upload
if ($this->post['ajax_manufacturers_image']) {
	$update_manufacturers_image=$this->post['ajax_manufacturers_image'];
}
if ($this->post and is_array($_FILES) and count($_FILES)) {
	if ($this->post['manufacturers_name']) {
		$this->post['manufacturers_name']=trim($this->post['manufacturers_name']);
	}
	if (is_array($_FILES) and count($_FILES)) {
		$file=$_FILES['manufacturers_image'];
		if ($file['tmp_name']) {
			$size=getimagesize($file['tmp_name']);
			if ($size[0] > 5 and $size[1] > 5) {
				$imgtype = mslib_befe::exif_imagetype($file['tmp_name']);
				if ($imgtype) {
					// valid image
					$ext = image_type_to_extension($imgtype, false);
					$i=0;
					$filename=mslib_fe::rewritenamein($this->post['manufacturers_name'][0]).'.'.$ext;
					$folder=mslib_befe::getImagePrefixFolder($filename);
					if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder)) {
						t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder);
					}
					$folder.='/';
					$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
					if (file_exists($target)) {
						do {
							$filename=mslib_fe::rewritenamein($this->post['manufacturers_name'][0]).'-'.$i.'.'.$ext;
							$folder=mslib_befe::getImagePrefixFolder($filename);
							if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder)) {
								t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder);
							}
							$folder.='/';
							$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
							$i++;
						} while (file_exists($target));
					}
					if (move_uploaded_file($file['tmp_name'],$target)) {
						$update_manufacturers_image=mslib_befe::resizeManufacturerImage($target,$filename,$this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath($this->extKey),1);
					}
				}
			}
		}
	}
}

if ($this->post) {
	if ($this->post['manufacturers_name']) {
		$this->post['manufacturers_name']=trim($this->post['manufacturers_name']);	
	}
	$updateArray=array();
    $updateArray['manufacturers_name'] 	= $this->post['manufacturers_name'];
    $updateArray['status'] 				= $this->post['status'];
    if ($update_manufacturers_image) {
    	$updateArray['manufacturers_image'] =$update_manufacturers_image;
    }
	if ($_REQUEST['action']=='add_manufacturer') {
	    $updateArray['date_added'] = time();
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers', $updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$manufacturers_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
		if ($manufacturers_id) {
			$updateArray2=array();		
			$updateArray2['manufacturers_id']		=$manufacturers_id;
			$updateArray2['language_id']			=$this->sys_language_uid;
			$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers_info',$updateArray2);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
			$updateArray['manufacturers_id']		=$manufacturers_id;			
		}				
	} else if($this->post['manufacturers_id']) {
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$this->post['manufacturers_id'].'\'',$updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);		
		$manufacturers_id=$this->post['manufacturers_id'];
	}
	if ($manufacturers_id) {
		foreach ($this->post['content'] as $key => $value) {
			$str="select 1 from tx_multishop_manufacturers_cms where manufacturers_id='".$manufacturers_id."' and language_id='".$key."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);		
			$updateArray=array();
			$updateArray['content']				= $this->post['content'][$key];	
			$updateArray['content_footer']		= $this->post['content_footer'][$key];	
			$updateArray['shortdescription']	= $this->post['shortdescription'][$key];
			$updateArray['meta_title'] 			= $this->post['meta_title'][$key];
			$updateArray['meta_keywords'] 		= $this->post['meta_keywords'][$key];
			$updateArray['meta_description'] 	= $this->post['meta_description'][$key];
			
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers_cms', 'manufacturers_id=\''.$manufacturers_id.'\' and language_id=\''.$key.'\'', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
			} else {
				$updateArray['manufacturers_id']		=$manufacturers_id;	
				$updateArray['language_id']				=$key;					
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers_cms', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
			}
		}
		echo $this->pi_getLL('manufacturer_saved');	
		echo '
		<script>
		parent.window.location.reload();
		</script>
		';
		exit();
	}
}
if ($_REQUEST['action']=='edit_manufacturer') {
	$str="SELECT * from tx_multishop_manufacturers m where m.manufacturers_id='".$_REQUEST['manufacturers_id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$manufacturer=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	
	if ($this->get['delete_image'] and is_numeric($this->get['manufacturers_id'])) {
		if ($manufacturer[$this->get['delete_image']]) {
			mslib_befe::deleteManufacturerImage($manufacturer[$this->get['delete_image']]);
			$updateArray=array();
			$updateArray[$this->get['delete_image']]='';
			$manufacturer[$this->get['delete_image']]='';
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$_REQUEST['manufacturers_id'].'\'',$updateArray);

			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	
	$str="SELECT * from tx_multishop_manufacturers_cms where manufacturers_id='".$_REQUEST['manufacturers_id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		$lngman[$row['language_id']]=$row;
	}
}
$manufacturersImage = '';
$manufacturersContent = '';
$manufacturersMeta = '';
if ($manufacturer['manufacturers_id'] or $_REQUEST['action']=='add_manufacturer') {
	if ($_REQUEST['action'] =='edit_manufacturer' and $manufacturer['manufacturers_image']) {
		$tmpcontent.='<img src="'.mslib_befe::getImagePath($manufacturer['manufacturers_image'],'manufacturers','normal').'">';
		$tmpcontent.=' <a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$_REQUEST['manufacturers_id'].'&action=edit_manufacturer&delete_image=manufacturers_image').'" onclick="return confirm(\'Are you sure?\')"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/icons/delete2.png" border="0" alt="delete image"></a>';
		
		$markerArray=array();
		$markerArray['MANUFACTURER_IMAGES_SRC'] 			= mslib_befe::getImagePath($manufacturer['manufacturers_image'],'manufacturers','normal');
		$markerArray['MANUFACTURER_IMAGES_DELETE_LINK'] 	= mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$_REQUEST['manufacturers_id'].'&action=edit_manufacturer&delete_image=manufacturers_image');
		$markerArray['FULL_HTTP_URL'] 		= $this->FULL_HTTP_URL_MS;
		$manufacturersImage .= $this->cObj->substituteMarkerArray($subparts['manufacturers_images'], $markerArray,'###|###');
	}
	
	foreach ($this->languages as $key => $language) {
		$markerArray=array();
		$markerArray['LANGUAGE_UID'] 							= $language['uid'];
		$markerArray['LABEL_MANUFACTURER_LANGUAGE'] 			= t3lib_div::strtoupper($this->pi_getLL('language'));
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
			$markerArray['MANUFACTURER_CONTENT_FLAG'] 			= '<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		} else {
			$markerArray['MANUFACTURER_CONTENT_FLAG'] 			= '';
		}
		$markerArray['MANUFACTURER_CONTENT_TITLE']				= $language['title'];
		$markerArray['LABEL_MANUFACTURER_SHORT_DESCRIPTION'] 	= $this->pi_getLL('admin_short_description');
		$markerArray['VALUE_MANUFACTURER_SHORT_DESCRIPTION'] 	= htmlspecialchars($lngman[$language['uid']]['shortdescription']);
		$markerArray['LABEL_MANUFACTURER_CONTENT'] 				= t3lib_div::strtoupper($this->pi_getLL('content'));
		$markerArray['VALUE_MANUFACTURER_CONTENT'] 				= htmlspecialchars($lngman[$language['uid']]['content']);
		$markerArray['LABEL_MANUFACTURER_CONTENT_FOOTER'] 		= t3lib_div::strtoupper($this->pi_getLL('content')).' '.t3lib_div::strtoupper($this->pi_getLL('bottom'));
		$markerArray['VALUE_MANUFACTURER_CONTENT_FOOTER'] 		= htmlspecialchars($lngman[$language['uid']]['content_footer']);
		$manufacturersContent .= $this->cObj->substituteMarkerArray($subparts['manufacturers_content'], $markerArray,'###|###');
		
		// manufacturers meta
		$markerArray=array();
		$markerArray['LANGUAGE_UID'] 							= $language['uid'];
		$markerArray['LABEL_MANUFACTURER_META_LANGUAGE'] 		= t3lib_div::strtoupper($this->pi_getLL('language'));
		$markerArray['MANUFACTURER_META_TITLE']					= $language['title'];
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) {
			$markerArray['MANUFACTURER_META_FLAG'] 				= '<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		} else {
			$markerArray['MANUFACTURER_META_FLAG'] 				= '';
		}
		$markerArray['VALUE_MANUFACTURER_META_TITLE']			= htmlspecialchars($lngman[$language['uid']]['meta_title']);
		$markerArray['VALUE_MANUFACTURER_META_KEYWORDS'] 		= htmlspecialchars($lngman[$language['uid']]['meta_keywords']);
		$markerArray['VALUE_MANUFACTURER_META_DESCRIPTION'] 	= htmlspecialchars($lngman[$language['uid']]['meta_description']);
		$manufacturersMeta .= $this->cObj->substituteMarkerArray($subparts['manufacturers_meta'], $markerArray,'###|###');
	}
	
	$subpartArray = array();
	if ($_REQUEST['action']=='add_manufacturer') {
		$subpartArray['###MANUFACTURER_FORM_HEADING###'] 			= t3lib_div::strtoupper($this->pi_getLL('add_manufacturer'));
	} else if ($_REQUEST['action']=='edit_manufacturer') {
		$subpartArray['###MANUFACTURER_FORM_HEADING###'] 			= t3lib_div::strtoupper($this->pi_getLL('edit_manufacturer'));
	}
	if ($manufacturer['status'] or $_REQUEST['action']=='add_manufacturer') {
		$subpartArray['###MANUFACTURER_VISIBLE_CHECKED###'] 		= 'checked="checked"';
	} else {
		$subpartArray['###MANUFACTURER_VISIBLE_CHECKED###'] 		= '';
	}
	if (!$manufacturer['status'] and $_REQUEST['action'] =='edit_manufacturer') {
		$subpartArray['###MANUFACTURER_NOT_VISIBLE_CHECKED###'] 	= 'checked="checked"';
	} else {
		$subpartArray['###MANUFACTURER_NOT_VISIBLE_CHECKED###'] 	= '';
	}
	$subpartArray['###MANUFACTURER_ID###'] 							= $manufacturer['manufacturers_id'];
	$subpartArray['###MANUFACTURER_EDIT_FORM_URL###'] 				= mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$_REQUEST['manufacturers_id']);
	$subpartArray['###LABEL_MANUFACTURER_NAME###'] 					= $this->pi_getLL('admin_name');
	$subpartArray['###VALUE_MANUFACTURER_NAME###'] 					= htmlspecialchars($manufacturer['manufacturers_name']);
	$subpartArray['###LABEL_MANUFACTURER_IMAGE###'] 				= $this->pi_getLL('admin_image');
	$subpartArray['###MANUFACTURER_IMAGES_UPLOAD_URL###'] 			= mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_upload_product_images');
	$subpartArray['###MANUFACTURER_IMAGES_LABEL_CHOOSE_IMAGE###'] 	= addslashes(htmlspecialchars($this->pi_getLL('choose_image')));
	$subpartArray['###LABEL_MANUFACTURER_VISIBLE###'] 				= $this->pi_getLL('admin_visible');
	$subpartArray['###LABEL_MANUFACTURER_ADMIN_YES###'] 			= $this->pi_getLL('admin_yes');
	$subpartArray['###LABEL_MANUFACTURER_ADMIN_NO###'] 				= $this->pi_getLL('admin_no');
	$subpartArray['###LABEL_BUTTON_ADMIN_CANCEL###'] 				= $this->pi_getLL('admin_cancel');
	$subpartArray['###LABEL_BUTTON_ADMIN_SAVE###'] 					= $this->pi_getLL('admin_save');
	$subpartArray['###VALUE_FORM_MANUFACTURER_ACTION_URL###'] 		= $_REQUEST['action'];
	
	$subpartArray['###MANUFACTURER_IMAGES###'] 						= $manufacturersImage;
	$subpartArray['###MANUFACTURERS_CONTENT###'] 					= $manufacturersContent;
	$subpartArray['###MANUFACTURERS_META###'] 					= $manufacturersMeta;
	$content .= $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
}
?>