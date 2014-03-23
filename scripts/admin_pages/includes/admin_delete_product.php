<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_delete_product').'</h1></div>';
if(is_numeric($_REQUEST['pid'])) {
	if($_REQUEST['confirm']) {
		mslib_befe::deleteProduct($_REQUEST['pid'], $_REQUEST['cid']);
		$content.=$this->pi_getLL('product_has_been_removed').'.';
		$content.='
		<script type="text/javascript">
		parent.window.location.reload();
		</script>
		';
	} else {
		$str="SELECT * from tx_multishop_products_description where products_id='".$_REQUEST['pid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if(is_numeric($row['products_id'])) {
			$content.='<form class="admin_product_edit" name="admin_product_edit_'.$_REQUEST['pid'].'" id="admin_product_edit_'.$_REQUEST['pid'].'" method="post" action="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&pid='.$_REQUEST['pid']).'">';
			$content.='
	<div class="save_block">
		<input name="cid" type="hidden" value="'.$_REQUEST['cid'].'" />
		<input name="pid" type="hidden" value="'.$_REQUEST['pid'].'" />
		<input name="confirm" type="hidden" value="1" />
		<input name="action" type="hidden" value="delete_product" />
		<input name="cancel" type="button" value="'.$this->pi_getLL('cancel').'" onClick="parent.window.hs.close();" class="submit" />
		<input name="Submit" type="submit" value="'.$this->pi_getLL('delete').': '.htmlspecialchars($row['products_name']).'" class="submit" />
	</div>	
';
			$content.='</form>';
		}
	}
}
?>