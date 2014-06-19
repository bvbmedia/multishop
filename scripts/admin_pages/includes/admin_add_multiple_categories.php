<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(\'.select2BigDropWider\').select2({
		dropdownCssClass: "bigdropWider", // apply css that makes the dropdown taller
		width:\'220px\'
	});
});
</script>
';
// hidden filename that is retrieved from the ajax upload
$subpartArray=array();
$subpartArray['###VALUE_REFERRER###']='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
}
if ($this->post) {
	$categories_name=explode("\n", $this->post['categories_name']);
	foreach ($categories_name as $category_name) {
		$category_name=str_replace("\r", "", $category_name);
		if ($category_name) {
			$category_name=trim($category_name);
		}
		if (!empty($category_name)) {
			$query3=$GLOBALS['TYPO3_DB']->SELECTquery('cd.categories_name, c.categories_id, c.parent_id', // SELECT ...
				'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
				'c.parent_id ='.$this->post['parent_id'].' and cd.categories_name=\''.addslashes($category_name).'\' and c.categories_id=cd.categories_id', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res3=$GLOBALS['TYPO3_DB']->sql_query($query3);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res3)>0) {
				// sometimes the categories startingpoint is not zero. To protect merchants configure a category that is member of itself we reset the parent_id to zero
				$updateArray=array();
				$updateArray['custom_settings']='';
				$updateArray['parent_id']=$this->post['parent_id'];
				$updateArray['categories_url']='';
				$updateArray['status']=$this->post['status'];
				$updateArray['option_attributes']='';
				$updateArray['page_uid']=$this->showCatalogFromPage;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$catid=$GLOBALS['TYPO3_DB']->sql_insert_id();
				if ($catid) {
					$str="select 1 from tx_multishop_categories_description where categories_id='".$catid."' and language_id='0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
						$updateArray=array();
						$updateArray['categories_name']=$category_name;
						$updateArray['meta_title']='';
						$updateArray['meta_keywords']='';
						$updateArray['meta_description']='';
						$updateArray['content']='';
						$updateArray['content_footer']='';
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', 'categories_id=\''.$catid.'\' and language_id=\'0\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$updateArray=array();
						$updateArray['categories_id']=$catid;
						$updateArray['language_id']='0';
						$updateArray['categories_name']=$category_name;
						$updateArray['meta_title']='';
						$updateArray['meta_keywords']='';
						$updateArray['meta_description']='';
						$updateArray['content']='';
						$updateArray['content_footer']='';
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
	}
	if ($this->post['tx_multishop_pi1']['referrer']) {
		header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
		exit();
	} else {
		header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_categories',1));
		exit();
	}
} else {
	if (!$category['parent_id']) {
		$category['parent_id']=$this->get['cid'];
	}
	$save_block='
		<div class="save_block">
			<a href="'.$subpartArray['###VALUE_REFERRER###'].'" class="msBackendButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('cancel').'</span></a>
			<span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" /></span>
		</div>
	';

	$content.='
	<form class="admin_add_multiple_categories blockSubmitForm" name="admin_add_multiple_categories" id="admin_add_multiple_categories" method="post" action="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax').'" enctype="multipart/form-data">
	<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
	';
	$tmpcontent.='<div style="float:right;">'.$save_block.'</div>';
	//$tmpcontent.='<div class="main-heading"><h1>'.$this->pi_getLL('add_category').'</h1></div>';
	$tmpcontent.='
		<div class="account-field" id="msEditCategoriesInputVisibility">
			<label for="status">'.$this->pi_getLL('admin_visible').'</label>
			<input name="status" type="radio" value="1" checked="checked" /> '.$this->pi_getLL('admin_yes').' <input name="status" type="radio" value="0" /> '.$this->pi_getLL('admin_no').'
		</div>
		<div class="account-field" id="msEditCategoriesInputParent">
			<label for="parent_id">'.$this->pi_getLL('admin_parent').'</label>
			'.mslib_fe::tx_multishop_draw_pull_down_menu('parent_id', mslib_fe::tx_multishop_get_category_tree('', '', $skip_ids), $category['parent_id'],'class="select2BigDropWider"').'
		</div>';
	$tmpcontent.='
	<div class="account-field" id="msEditCategoriesInputName">
		<label for="categories_name">'.$this->pi_getLL('admin_multiple_categories', 'CATEGORIES NAME').'</label>
		<textarea name="categories_name" id="categories_name" style="width:250px;height:500px;" class="expand100-200"></textarea>
	</div>';
	$tabs['category_main']=array(
		'DETAILS',
		$tmpcontent
	);
	// tabber
	$content.='<h1>'.$this->pi_getLL('admin_new_multiple_category').'</h1>
';
	$count=0;
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.='

			'.$value[1].'

	';
	}

	// tabber eof
	$content.='<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
	</form>';
	$content.='';
}
?>