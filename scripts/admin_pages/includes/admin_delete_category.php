<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('delete_category').'</h1></div>';
if (is_numeric($_REQUEST['cid'])) {
	if ($_REQUEST['confirm']) {
		mslib_befe::deleteCategory($_REQUEST['cid']);
		if ($this->post['tx_multishop_pi1']['referrer']) {
			header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
			exit();
		} else {
			header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_categories', 1));
			exit();
		}
	} else {
		$subpartArray['###VALUE_REFERRER###']='';
		if ($this->post['tx_multishop_pi1']['referrer']) {
			$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
		} else {
			$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
		}
		$str="SELECT * from tx_multishop_categories_description where categories_id='".$_REQUEST['cid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if (is_numeric($row['categories_id'])) {
			$content.='<form class="admin_categories_edit" name="admin_categories_edit_'.$_REQUEST['cid'].'" id="admin_categories_edit_'.$_REQUEST['cid'].'" method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$_REQUEST['action'].'&categories_id='.$_REQUEST['cid'], 1).'">
			<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
			';
			$content.='
	<div class="save_block">
		<input name="cid" type="hidden" value="'.$_REQUEST['cid'].'" />
		<input name="confirm" type="hidden" value="1" />
		<input name="action" type="hidden" value="delete_category" />
		<a href="'.$subpartArray['###VALUE_REFERRER###'].'" class="btn btn-danger">'.$this->pi_getLL('cancel').'</a>
		<input name="Submit" type="submit" value="'.$this->pi_getLL('delete').': '.htmlspecialchars($row['categories_name']).'" class="btn btn-success" />
	</div>
';
			$content.='</form>';
		}
	}
}
?>