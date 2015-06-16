<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->post) {
	$cat_target_id=$this->post['mergecats_target'];
	foreach ($this->post['mergecats_source'] as $cat_source_id) {
		if ($cat_source_id!=$cat_target_id) {
			$cat_source=mslib_fe::getCategory($cat_source_id, 1);
			//
			/*$updateArray=array();
			$where="categories_id = ".$cat_target_id;
			$updateArray['parent_id']=$cat_source['parent_id'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', $where, $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);*/
			//
			$updateArray=array();
			$where="parent_id = ".$cat_source_id;
			$updateArray['parent_id']=$cat_target_id;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', $where, $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// check the p2c that have categories_id=$cat_source_id
			$qry=$GLOBALS['TYPO3_DB']->SELECTquery('p2c.*', // SELECT ...
				'tx_multishop_products_to_categories p2c', // FROM ...
				'FIND_IN_SET(\''.$cat_source_id.'\', p2c.crumbar_identifier)', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$categories_query=$GLOBALS['TYPO3_DB']->sql_query($qry);
			$product_ids=array();
			while ($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($categories_query)) {
				$crumbar_identifier=explode(',', $rs['crumbar_identifier']);
				foreach ($crumbar_identifier as $idx=>$ident) {
					if ($ident==$cat_source_id) {
						$crumbar_identifier[$idx]=$cat_target_id;
					}
				}
				$rs['crumbar_identifier']=implode(',', $crumbar_identifier);
				//
				$updateArray=array();
				if ($rs['categories_id']==$cat_source_id) {
					$updateArray['categories_id']=$cat_target_id;
				}
				if ($rs['node_id']==$cat_source_id) {
					$updateArray['node_id']=$cat_target_id;
				}
				$updateArray['crumbar_identifier']=$rs['crumbar_identifier'];
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', "products_to_categories_id = ".$rs['products_to_categories_id'], $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if (!in_array($rs['products_id'], $product_ids)) {
					$product_ids[]=$rs['products_id'];
				}
			}
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				foreach ($product_ids as $product_id) {
					// if the flat database module is enabled we have to sync the changes to the flat table
					mslib_befe::convertProductToFlat($product_id);
				}
			}
			//
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_categories', 'categories_id=\''.$cat_source_id.'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_categories_description', 'categories_id=\''.$cat_source_id.'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	header('Location: ' . $this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_categories'));
}
//
$categories=mslib_fe::getSubcatsOnly($this->categoriesStartingPoint, 1);
//
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('merge_categories').'</h1></div>
<form action="'.mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=merge_categories').'" method="post" class="merge_attribute_options_form">
	<div class="account-field">
			<ul>
			';
$cat_selectbox='';
foreach ($categories as $category) {
	//
	$tmp_return_data=array();
	$tmp_return_data[$category['categories_id']]=$category['categories_name'];
	//
	$categories_tree=array();
	$categories_tree[0][]=array(
		'id'=>$category['categories_id'],
		'name'=>$category['categories_name']
	);
	mslib_fe::getSubcatsArray($categories_tree,'',$category['categories_id']);
	//level 0
	foreach ($categories_tree[$category['categories_id']] as $category_tree_0) {
		$tmp_return_data[$category_tree_0['id']]=$category_tree_0['name'];
		if (is_array($categories_tree[$category['categories_id']])) {
			mslib_fe::build_categories_path($tmp_return_data, $category['categories_id'], $tmp_return_data[$category['categories_id']], $categories_tree, true);
		}
	}
	// build the path
	/*$content.='<li>';
	$content.='<input type="checkbox" class="movecats" name="mergecats_source[]" value="'.$category['categories_id'].'">&nbsp;';
	$content.='<strong>'.$category['categories_name'].' '.(!$category['status'] ? '(disabled)' : '').'</strong>';
	$content.='</li>'."\n";*/
	//
	foreach ($tmp_return_data as $tree_id=>$tree_path) {
		$tree_path=str_replace('\\', '>', $tree_path);
		$content.='<li>';
		$content.='<input type="checkbox" class="movecats" name="mergecats_source[]" value="'.$tree_id.'" id="tree_cats_'.$tree_id.'">&nbsp;';
		$content.='<label for="tree_cats_'.$tree_id.'">'.$tree_path. ' (ID: '.$tree_id.')' .'</label>';
		$content.='</li>'."\n";
		//
		$cat_selectbox.='<option value="'.$tree_id.'" id="sl-cat_'.$tree_id.'">'.$tree_path. ' (ID: '.$tree_id.')' .'</option>';
	}
}
$cat_selectbox='<select name="mergecats_target" id="mergecats_target" style="width:400px">
<option value="0">'.$this->pi_getLL('admin_label_option_main_category').'</option>
'.$cat_selectbox.'
</select>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery("#mergecats_target").select2();
});
</script>
';

$content.='
			</ul>
	</div>
	<div class="account-field">
			<label>Merge selected categories to: </label>
			'.$cat_selectbox.'<input type="submit" id="submit" class="msadmin_button" value="'.$this->pi_getLL('merge_selected').'" />
	</div>
</form>
';
?>