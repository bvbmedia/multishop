<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_delete_product').'</h1></div>';
if (is_numeric($_REQUEST['pid'])) {
	if ($_REQUEST['confirm']) {
		mslib_befe::deleteProduct($_REQUEST['pid'], $_REQUEST['cid']);
		if ($this->post['tx_multishop_pi1']['referrer']) {
			header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
			exit();
		} else {
			header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_cms',1));
			exit();
		}
	} else {
		$subpartArray['###VALUE_REFERRER###']='';
		if ($this->post['tx_multishop_pi1']['referrer']) {
			$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
		} else {
			$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
		}
		$str="SELECT * from tx_multishop_products_description where products_id='".$_REQUEST['pid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if (is_numeric($row['products_id'])) {
			$content.='<form class="admin_product_edit" name="admin_product_edit_'.$_REQUEST['pid'].'" id="admin_product_edit_'.$_REQUEST['pid'].'" method="post" action="'.mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$_REQUEST['pid'],1).'">
			<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
			';
			$content.='
	<div class="save_block">
		<input name="cid" type="hidden" value="'.$_REQUEST['cid'].'" />
		<input name="pid" type="hidden" value="'.$_REQUEST['pid'].'" />
		<input name="confirm" type="hidden" value="1" />
		<input name="action" type="hidden" value="delete_product" />
		<a href="'.$subpartArray['###VALUE_REFERRER###'].'" class="msBackendButton backState arrowLeft arrowPosLeft"><span>'.$this->pi_getLL('cancel').'</span></a>
		 <span class="msBackendButton continueState arrowRight arrowPosLeft"><input name="Submit" type="submit" value="'.$this->pi_getLL('delete').': '.htmlspecialchars($row['products_name']).'" /></span>
	</div>	
';
			$content.='</form>';
		}
	}
}
?>