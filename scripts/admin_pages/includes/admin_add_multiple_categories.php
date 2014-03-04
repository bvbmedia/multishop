<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript">
window.onload = function(){
  var text_input = document.getElementById (\'categories_name_0\');
  text_input.focus ();
  text_input.select ();
}
</script>
';
// hidden filename that is retrieved from the ajax upload
if ($this->post) {
	$categories_name = explode("\n", $this->post['categories_name']);
	foreach ($categories_name as $category_name) {
		$category_name = str_replace("\r", "", $category_name);
		if ($category_name) {
			$category_name=trim($category_name);
		}
		if (!empty($category_name)) {
			$query3 = $GLOBALS['TYPO3_DB']->SELECTquery(
					'cd.categories_name, c.categories_id, c.parent_id',         // SELECT ...
					'tx_multishop_categories c, tx_multishop_categories_description cd',     // FROM ...
					'c.parent_id ='.$this->post['parent_id'].' and cd.categories_name=\''.addslashes($category_name).'\' and c.categories_id=cd.categories_id',    // WHERE...
					'',            // GROUP BY...
					'',    // ORDER BY...
					''            // LIMIT ...
			);
			$res3 = $GLOBALS['TYPO3_DB']->sql_query($query3);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res3) > 0) {
				// sometimes the categories startingpoint is not zero. To protect merchants configure a category that is member of itself we reset the parent_id to zero
				$updateArray=array();
				$updateArray['custom_settings']				= '';
				$updateArray['parent_id']					= $this->post['parent_id'];
				$updateArray['categories_url']				= '';	
				$updateArray['status']						= $this->post['status'];
			
				
				$updateArray['option_attributes']			= '';
				$updateArray['page_uid'] = $this->showCatalogFromPage;		
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);
				$catid=$GLOBALS['TYPO3_DB']->sql_insert_id();
				
				if ($catid)	{
					$str="select 1 from tx_multishop_categories_description where categories_id='".$catid."' and language_id='0'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);		
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {	
						$updateArray=array();
						$updateArray['categories_name']				= $category_name;
						$updateArray['meta_title']					= '';
						$updateArray['meta_keywords']				= '';
						$updateArray['meta_description']			= '';
						$updateArray['content']						= '';
						$updateArray['content_footer']				= '';
						
						$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', 'categories_id=\''.$catid.'\' and language_id=\'0\'',$updateArray);
						$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
					} else {
						$updateArray=array();
						$updateArray['categories_id']				= $catid;
						$updateArray['language_id']					= '0';
						$updateArray['categories_name']				= $category_name;
						$updateArray['meta_title']					= '';
						$updateArray['meta_keywords']				= '';
						$updateArray['meta_description']			= '';
						$updateArray['content']						= '';
						$updateArray['content_footer']				= '';
						
						$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray);
						$res = $GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
	}
	$content.= $this->pi_getLL('category_saved').'.';
	$content.= '
		<script type="text/javascript">
		parent.window.location.reload();
		</script>
	';
} else {
	if (!$category['parent_id']) $category['parent_id']=$this->get['cid'];
	$save_block='
		<div class="save_block">
			<input name="cancel" type="button" value="'.$this->pi_getLL('cancel').'" onClick="parent.window.hs.close();" class="submit" />
			<input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="submit" />
		</div>
	';
	$content 	.= '
	<form class="admin_add_multiple_categories" name="admin_add_multiple_categories" id="admin_add_multiple_categories" method="post" action="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax').'" enctype="multipart/form-data">';
	$tmpcontent .= '<div style="float:right;">'.$save_block.'</div>';
	$tmpcontent .= '<div class="main-heading"><h1>'.$this->pi_getLL('add_category').'</h1></div>';
	
	$tmpcontent	.='
		<div class="account-field" id="msEditCategoriesInputVisibility">
			<label for="status">'.$this->pi_getLL('admin_visible').'</label>
			<input name="status" type="radio" value="1" checked="checked" /> '.$this->pi_getLL('admin_yes').' <input name="status" type="radio" value="0" /> '.$this->pi_getLL('admin_no').'
		</div>
		<div class="account-field" id="msEditCategoriesInputParent">
			<label for="parent_id">'.$this->pi_getLL('admin_parent').'</label>
			'. mslib_fe::tx_multishop_draw_pull_down_menu('parent_id', mslib_fe::tx_multishop_get_category_tree('','',$skip_ids), $category['parent_id']).'
		</div>';
	
	$tmpcontent	.='
	<div class="account-field" id="msEditCategoriesInputName">
		<label for="categories_name">'.$this->pi_getLL('admin_multiple_categories', 'CATEGORIES NAME').'</label>
		<textarea name="categories_name" id="categories_name" class="expand100-200"></textarea>
	</div>';	
	$tabs['category_main']=array('DETAILS',$tmpcontent);	
	// tabber
	$content.='
<script type="text/javascript">
jQuery(document).ready(function($) {
	
	jQuery(".tab_content").hide();
	jQuery("ul.tabs li:first").addClass("active").show();
	jQuery(".tab_content:first").show();
	jQuery("ul.tabs li").click(function() {
		jQuery("ul.tabs li").removeClass("active");
		jQuery(this).addClass("active");
		jQuery(".tab_content").hide();
		var activeTab = jQuery(this).find("a").attr("href");
		jQuery(activeTab).fadeIn(0);
		return false;
	});
	
});
</script>
<div id="tab-container">
    <ul class="tabs" id="admin_orders">
';
	$count=0;
	foreach ($tabs as $key => $value) {
		$count++;
		$content.='<li'.(($count==1)?' class="active"':'').'><a href="#'.$key.'">'.$value[0].'</a></li>';
	}
	$content.='
    </ul>
    <div class="tab_container">
	
	';
	$count=0;
	foreach ($tabs as $key => $value) {
		$count++;
		$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
			'.$value[1].'
        </div>
	';
	}
	$content.=
	$save_block.
	'
    </div>
</div>
';
	
	// tabber eof
	$content.='<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
	</form>';
	$content.='';
	
	
}
?>