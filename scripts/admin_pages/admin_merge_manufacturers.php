<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->post) {
	$man_target_id=(int)$this->post['mergemanufacturers_target'];
	foreach ($this->post['mergemanufacturers_source'] as $man_source_id) {
		if ($man_source_id!=$man_target_id && $man_target_id>0) {
			//
			$updateArray=array();
			$where="manufacturers_id = ".$man_source_id;
			$updateArray['manufacturers_id']=$man_target_id;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', $where, $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$man_source_id.'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_manufacturers_cms', 'manufacturers_id=\''.$man_source_id.'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			//
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_manufacturers_info', 'manufacturers_id=\''.$man_source_id.'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=merge_manufacturers'));
	exit();
}
//
$manufacturers=mslib_fe::getManufacturers();
//
$content.='
<div class="panel panel-default">
    <div class="panel-heading">
        <h3>'.htmlspecialchars($this->pi_getLL('merge_manufacturers')).'</h3>
    </div>
<div class="panel-body">
<form action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=merge_manufacturers').'" method="post" id="merge_attribute_options_form" class="merge_attribute_options_form">
	<div class="account-field">
			<ul>
			';
$cat_selectbox='';
foreach ($manufacturers as $manufacturer) {
	$content.='<li>';
	$content.='<input type="checkbox" class="movecats" name="mergemanufacturers_source[]" value="'.$manufacturer['manufacturers_id'].'" id="tree_cats_'.$manufacturer['manufacturers_id'].'">&nbsp;';
	$content.='<label for="tree_cats_'.$manufacturer['manufacturers_id'].'">'.$manufacturer['manufacturers_name'].' (ID: '.$manufacturer['manufacturers_id'].')'.'</label>';
	$content.='</li>'."\n";
	//
	$cat_selectbox.='<option value="'.$manufacturer['manufacturers_id'].'" id="sl-cat_'.$manufacturer['manufacturers_id'].'">'.$manufacturer['manufacturers_name'].' (ID: '.$manufacturer['manufacturers_id'].')'.'</option>';
}
$cat_selectbox='<select name="mergemanufacturers_target" id="mergemanufacturers_target" style="width:400px">
<option value="0">'.$this->pi_getLL('manufacturers').'</option>
'.$cat_selectbox.'
</select>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery("#mergemanufacturers_target").select2();
	jQuery("#merge_attribute_options_form").submit(function(event){
		if (jQuery("#mergemanufacturers_target").val()=="0") {
			alert("please select the manufacturer target");
			jQuery("#mergemanufacturers_target").addClass("danger");
			event.preventDefault();
		}
	});
});
</script>
';
$content.='
			</ul>
	</div>
	<div class="account-field">
			<label>Merge selected manufacturers to: </label>
			'.$cat_selectbox.'<input type="submit" id="submit" class="btn btn-success" value="'.$this->pi_getLL('merge_selected').'" />
	</div>
</form>
</div>
</div>
';
?>