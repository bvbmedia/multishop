<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('delete_category').'</h1></div>';
if (is_numeric($_REQUEST['cid'])) {
	if ($_REQUEST['confirm']) {
		mslib_befe::deleteCategory($_REQUEST['cid']);
		$content.=$this->pi_getLL('category_has_been_removed').'.';
		$content.='
		<script type="text/javascript">
		parent.window.location.reload();
		</script>
		';
	} else {
		$str="SELECT * from tx_multishop_categories_description where categories_id='".$_REQUEST['cid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if (is_numeric($row['categories_id'])) {
			$content.='<form class="admin_categories_edit" name="admin_categories_edit_'.$_REQUEST['cid'].'" id="admin_categories_edit_'.$_REQUEST['cid'].'" method="post" action="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&categories_id='.$_REQUEST['cid']).'">';
			$content.='
	<div class="save_block">
		<input name="cid" type="hidden" value="'.$_REQUEST['cid'].'" />
		<input name="confirm" type="hidden" value="1" />
		<input name="action" type="hidden" value="delete_category" />
		<input name="cancel" type="button" value="'.$this->pi_getLL('cancel').'" onClick="parent.window.hs.close();" class="submit" />
		<input name="Submit" type="submit" value="'.$this->pi_getLL('delete').': '.htmlspecialchars($row['categories_name']).'" class="submit" />
	</div>	
';
			$content.='</form>';
		}
	}
}
?>