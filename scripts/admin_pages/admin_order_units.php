<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->get['tx_multishop_pi1']['action']) {
	switch ($this->get['tx_multishop_pi1']['action']) {
		case 'delete':
			if (intval($this->get['tx_multishop_pi1']['order_unit_id'])) {
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_order_units', 'id=\''.$this->get['tx_multishop_pi1']['order_unit_id'].'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_order_units_description', 'id=\''.$this->get['tx_multishop_pi1']['order_unit_id'].'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_order_units'));
				exit();
			}
			break;
	}
}
if ($this->post) {
	if (isset($this->post['tx_multishop_pi1']['order_unit_id'])) {
		$order_unit_id=(int)$this->post['tx_multishop_pi1']['order_unit_id'];
		if ($order_unit_id) {
			$updateArray=array();
			$updateArray['code']=$this->post['tx_multishop_pi1']['order_unit_code'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_order_units', 'id=\''.$order_unit_id.'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// order unit name
			foreach ($this->post['tx_multishop_pi1']['order_unit_name'] as $key=>$value) {
				$updateArray=array();
				$updateArray['name']=$value;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_order_units_description', 'order_unit_id=\''.$order_unit_id.'\' and language_id = '.$key, $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_order_units'));
			exit();
		}
	} else {
		// add new order status eof
		if (count($this->post['tx_multishop_pi1']['order_unit_name'])) {
			if ($this->post['tx_multishop_pi1']['order_unit_name'][0]) {
				$insertArray=array();
				$insertArray['code']=$this->post['tx_multishop_pi1']['order_unit_code'];
				$insertArray['page_uid']=$this->shop_pid;
				$insertArray['crdate']=time();
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
				if ($id) {
					foreach ($this->post['tx_multishop_pi1']['order_unit_name'] as $key=>$value) {
						$insertArray=array();
						$insertArray['name']=$value;
						$insertArray['language_id']=$key;
						$insertArray['order_unit_id']=$id;
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units_description', $insertArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
			header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_order_units'));
			exit();
		}
		// add new order status eof
	}
}
if ($this->get['tx_multishop_pi1']['action']=='edit') {
	$str="SELECT o.id, o.code, od.name, od.language_id from tx_multishop_order_units o, tx_multishop_order_units_description od where (o.page_uid='0' or o.page_uid='".$this->shop_pid."') and o.id=od.order_unit_id and od.order_unit_id = ".$this->get['tx_multishop_pi1']['order_unit_id']." order by o.id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$lngstatus=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$lngstatus[$row['language_id']]=$row;
	}
}
$content.='<div class="panel-heading"><h3>'.$this->pi_getLL('admin_order_unit').'</h3></div>';
$content.='<div class="panel-body">
<form class="form-horizontal" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
<div class="panel panel-default"><div class="panel-heading"><h3>'.$this->pi_getLL('add').'</h3></div>
<div class="panel-body">
';
foreach ($this->languages as $key=>$language) {
	$flag_path='';
	if ($language['flag']) {
		$flag_path='sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif';
	}
	$language_lable='';
	if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.$flag_path)) {
		$language_lable.='<img src="'.$this->FULL_HTTP_URL_TYPO3.$flag_path.'"> ';
	}
	$language_lable.=''.$language['title'];
	$tmpcontent.='
			<div class="form-group toggle_advanced_option msEditProductLanguageDivider">
				<label class="control-label col-md-2">'.$this->pi_getLL('language').'</label>
				<div class="col-md-10">
				<p class="form-control-static"><strong>'.$language_lable.'</strong></p>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-2" for="products_name">'.$this->pi_getLL('admin_name').'</label>
				<div class="col-md-10">
				<input type="text" class="form-control text" name="tx_multishop_pi1[order_unit_name]['.$language['uid'].']" id="order_unit_name_'.$language['uid'].'" value="'.htmlspecialchars($lngstatus[$language['uid']]['name']).'">
				</div>
			</div>
		';
	$tmpcontent.='
	<div class="form-group">
		<label class="control-label col-md-2" for="order_unit_code">'.$this->pi_getLL('code').'</label>
		<div class="col-md-10">
		<input type="text" class="form-control text" name="tx_multishop_pi1[order_unit_code]" value="'.htmlspecialchars($lngstatus[$language['uid']]['code']).'">
		</div>
	</div>
	';
}
if ($this->get['tx_multishop_pi1']['action']=='edit') {
	$tmpcontent.='<input type="hidden" class="text" name="tx_multishop_pi1[order_unit_id]" value="'.$this->get['tx_multishop_pi1']['order_unit_id'].'">';
}
$content.=$tmpcontent.'
<div class="form-group">
	<div class="col-md-10 col-md-offset-2">
		<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> '.$this->pi_getLL('save').'</button>
	</div>
</div>
</div>
</div>
</form>
';
$str="SELECT o.id, o.code, od.name from tx_multishop_order_units o, tx_multishop_order_units_description od where o.page_uid='".$this->shop_pid."' and o.id=od.order_unit_id and od.language_id='0' order by o.id desc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$zones=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$order_units[]=$row;
}
if (count($order_units)) {
	$content.='<table class="table table-striped table-bordered msadmin_border">
		<thead><tr><th class="cellID">'.$this->pi_getLL('id').'</th>
		<th class="cellCode">'.$this->pi_getLL('code').'</th>
		<th class="cellName">'.$this->pi_getLL('name').'</th>
		<th class="cellAction">'.$this->pi_getLL('action').'</th></tr></thead>';
	foreach ($order_units as $status) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$content.='<tr class="'.$tr_type.'">
		<td class="cellID">
			'.$status['id'].'
		</td>
		';
		$content.='
		<td class="cellCode">'.$status['code'].'</td>
		<td class="cellName">'.$status['name'].'</td>

		<td  class="cellAction msAdminProductsSearchCellActionIcons">
			<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[order_unit_id]='.$status['id'].'&tx_multishop_pi1[action]=edit').'" class="btn btn-primary btn-sm admin_menu_edit" alt="'.$this->pi_getLL('edit').'"><i class="fa fa-pencil"></i></a>
			<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[order_unit_id]='.$status['id'].'&tx_multishop_pi1[action]=delete').'" onclick="return confirm(\''.$this->pi_getLL('are_you_sure').'?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="'.$this->pi_getLL('delete').'"><i class="fa fa-trash-o"></i></a>
		</td>';
		$content.='</tr>';
	}
	$content.='</table>';
}
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';

?>