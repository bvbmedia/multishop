<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->get['tx_multishop_pi1']['action']) {
	switch ($this->get['tx_multishop_pi1']['action']) {
		case 'update_default_status':
			if (intval($this->get['tx_multishop_pi1']['orders_status_id'])) {
				$updateArray=array();
				$updateArray['default_status']=1;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id=\''.$this->get['tx_multishop_pi1']['orders_status_id'].'\' and page_uid='.$this->showCatalogFromPage, $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$updateArray=array();
				$updateArray['default_status']=0;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id <> \''.$this->get['tx_multishop_pi1']['orders_status_id'].'\' and page_uid='.$this->showCatalogFromPage, $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			break;
		case 'delete':
			if (intval($this->get['tx_multishop_pi1']['orders_status_id'])) {
				$updateArray=array();
				$updateArray['deleted']=1;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id=\''.$this->get['tx_multishop_pi1']['orders_status_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			break;
	}
}
if ($this->post) {
	switch ($this->post['tx_multishop_pi1']['action']) {
		case 'update_status':
			if (intval($this->post['tx_multishop_pi1']['orders_status_id'])) {
				foreach ($this->post['tx_multishop_pi1']['order_status_name'] as $key=>$value) {
					$updateArray=array();
					$updateArray['name']=$value;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status_description', 'orders_status_id=\''.$this->post['tx_multishop_pi1']['orders_status_id'].'\' and language_id = '.$key, $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
			break;
		default:
			// add new order status eof
			if (count($this->post['tx_multishop_pi1']['order_status_name'])) {
				if ($this->post['tx_multishop_pi1']['order_status_name'][0]) {
					$insertArray=array();
					$insertArray['page_uid']=$this->showCatalogFromPage;
					$insertArray['deleted']=0;
					$insertArray['crdate']=time();
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
					if ($id) {
						foreach ($this->post['tx_multishop_pi1']['order_status_name'] as $key=>$value) {
							$insertArray=array();
							$insertArray['name']=$value;
							$insertArray['language_id']=$key;
							$insertArray['orders_status_id']=$id;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status_description', $insertArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
				}
			}
			// add new order status eof
			break;
	}
}
if ($this->get['tx_multishop_pi1']['action']=='edit') {
	$str="SELECT o.id, o.default_status, od.name, od.language_id from tx_multishop_orders_status o, tx_multishop_orders_status_description od where (o.page_uid='0' or o.page_uid='".$this->shop_pid."') and o.deleted=0 and o.id=od.orders_status_id and od.orders_status_id = ".$this->get['tx_multishop_pi1']['orders_status_id']." order by o.id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$lngstatus=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$lngstatus[$row['language_id']]=$row;
	}
}
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('order_status').'</h1></div>';
$content.='
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
<fieldset><legend>'.$this->pi_getLL('add_order_status').'</legend>
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
			<div class="account-field toggle_advanced_option msEditProductLanguageDivider">
				<label>'.$this->pi_getLL('language').'</label>
				<span><strong>'.$language_lable.'</strong></span>
			</div>			
			<div class="account-field">
				<label for="products_name">'.$this->pi_getLL('admin_name').'</label>
						
				<input type="text" class="text" name="tx_multishop_pi1[order_status_name]['.$language['uid'].']" id="order_status_name_'.$language['uid'].'" value="'.htmlspecialchars($lngstatus[$language['uid']]['name']).'">
			</div>		
		';
}
$content.=$tmpcontent.'
<div class="account-field">
	<label>&nbsp;</label>
	<input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="btn btn-success" />
</div>	
</fieldset>';
if ($this->get['tx_multishop_pi1']['action']=='edit') {
	$content.='<input type="hidden" name="tx_multishop_pi1[orders_status_id]" value="'.$this->get['tx_multishop_pi1']['orders_status_id'].'" />';
	$content.='<input type="hidden" name="tx_multishop_pi1[action]" value="update_status" />';
}
$content.='</form>';
$str="SELECT o.id, o.default_status, od.name from tx_multishop_orders_status o, tx_multishop_orders_status_description od where (o.page_uid='0' or o.page_uid='".$this->showCatalogFromPage."') and o.deleted=0 and o.id=od.orders_status_id and od.language_id='0' order by o.id desc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$zones=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$statusses[]=$row;
}
if (count($statusses)) {
	$content.='<table class="table table-striped table-bordered msadmin_border" width="100%">
		<th>&nbsp;</th>
		<th>'.$this->pi_getLL('name').'</th>
		<th>'.$this->pi_getLL('default', 'Default').'</th>
		<th>'.$this->pi_getLL('action').'</th>';
	foreach ($statusses as $status) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$content.='<tr class="'.$tr_type.'">
		<td width="50" align="right">
			'.$status['id'].'
		</td>				
		';
		$content.='<td>'.$status['name'].'</td>
		<td width="60" align="center">';
		if (!$status['default_status']) {
			$content.='';
			$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_default_status&tx_multishop_pi1[orders_status_id]='.$status['id'].'&tx_multishop_pi1[status]=1').'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('enabled').'"></span></a>';
		} else {
			$content.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
			$content.='';
		}
		$content.='
		</td>
		<td width="50" class="align_center">
			<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[orders_status_id]='.$status['id'].'&tx_multishop_pi1[action]=edit').'" class="admin_menu_edit" alt="'.$this->pi_getLL('edit').'"></a>
			<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[orders_status_id]='.$status['id'].'&tx_multishop_pi1[action]=delete').'" onclick="return confirm(\''.$this->pi_getLL('are_you_sure').'?\')" class="admin_menu_remove" alt="'.$this->pi_getLL('delete').'"></a>
		</td>';
		$content.='</tr>';
	}
	$content.='</table>';
}
$content.='<p class="extra_padding_bottom"><a class="btn btn-success" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';

?>