<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (is_numeric($this->get['status']) and is_numeric($this->get['shipping_option_id'])) {
	$updateArray=array();
	$updateArray['status']=$this->get['status'];
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_shipping_options', 'id=\''.$this->get['shipping_option_id'].'\'', $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
if (is_numeric($this->get['delete']) and is_numeric($this->get['shipping_option_id'])) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_shipping_options', 'id=\''.$this->get['shipping_option_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
$str="SELECT * from tx_multishop_shipping_options order by name";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$shipping_options=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$shipping_options[]=$row;
}
$content.='<fieldset class="multishop_fieldset">';
$content.='<legend>Shipping Options</legend>';
$content.='<table width="100%" border="0" align="center" class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">';
$content.='<tr><th>Shipping Option</th><th>Price</th><th>Date Added</th><th>Status</th></tr>';
foreach ($shipping_options as $option) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	$content.='<tr class="'.$tr_type.'">
	<td><strong><a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&shipping_option_id='.$option['id']).'">'.$option['name'].'</a></strong></td>
	<td>'.mslib_fe::amount2Cents($option['price'], 0).'</td>
	<td>'.$option['date'].'</td>
	<td width="60">';
	if (!$option['status']) {
		$content.='<span class="admin_status_red" alt="Disable"></span>';
		$content.='<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&shipping_option_id='.$option['id'].'&status=1').'"><span class="admin_status_green disabled" alt="Enabled"></span></a>';
	} else {
		$content.='<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&shipping_option_id='.$option['id'].'&status=0').'"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
		$content.='<span class="admin_status_green" alt="Enable"></span>';
	}
	$content.='<a href="'.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]='.$this->ms['page'].'&shipping_option_id='.$option['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="Remove"></a>';
	$content.='</td>
	</tr>
	';
}
$content.='</table>';
$content.='</fieldset>';
?>