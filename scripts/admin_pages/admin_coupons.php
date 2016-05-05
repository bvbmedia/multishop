<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery().ready(function($){
	$("#s_date_visitor").datepicker({
		dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'm/d/Y').'",
		altField: "#s_date",
		altFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
		showOtherMonths: true,
		showAnim: \'slideDown\',
		yearRange: "'.date("Y").':'.(date("Y")+10).'"
	});
	$("#e_date_visitor").datepicker({
		dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'm/d/Y').'",
		altField: "#e_date",
		altFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
		showOtherMonths: true,
		showAnim: \'slideDown\',
		yearRange: "'.date("Y").':'.(date("Y")+10).'"
	});
});
</script>
';
if ($this->post) {
	if (!$this->post['s_date']) {
		$this->post['s_date']=date("Y-m-d");
	}
	if (!$this->post['e_date']) {
		$this->post['e_date']=(date('Y')+10).date("-m-d");
	}
	if (is_numeric($this->post['s_hours']) and is_numeric($this->post['s_minutes'])) {
		$this->post['s_date'].=' '.$this->post['s_hours'].':'.$this->post['s_minutes'].':00';
	}
	if (is_numeric($this->post['e_hours']) and is_numeric($this->post['e_minutes'])) {
		$this->post['e_date'].=' '.$this->post['e_hours'].':'.$this->post['e_minutes'].':00';
	}
	$s_time=strtotime($this->post['s_date']);
	$e_time=strtotime($this->post['e_date']);
	if ($this->post['discount_type']=='price' and strstr($this->post['discount'], ',')) {
		$this->post['discount']=str_replace(",", ".", $this->post['discount']);
	}
	$updateArray=array(
		'code'=>$this->post['code'],
		'status'=>1,
		'discount'=>$this->post['discount'],
		'discount_type'=>$this->post['discount_type'],
		'max_usage'=>$this->post['max_usage'],
		'startdate'=>$s_time,
		'enddate'=>$e_time,
		'page_uid'=>$this->post['related_shop_pid']
	);
	if ($this->post['coupons_id']) {
		// edit
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_coupons', 'id='.$this->post['coupons_id'], $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	} else {
		$updateArray['times_used']=0;
		// insert
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_coupons', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
}
if (is_numeric($this->get['status'])) {
	$updateArray=array(
		'status'=>$this->get['status']
	);
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_coupons', 'id='.$this->get['coupons_id'], $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
if ($this->get['delete']) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_coupons', 'id='.$this->get['coupons_id']);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$this->get['coupons_id']='';
} elseif (is_numeric($this->get['coupons_id'])) {
	$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
		'tx_multishop_coupons', // FROM ...
		'id='.$this->get['coupons_id'], // WHERE.
		'', // GROUP BY...
		'id desc', // ORDER BY...
		'' // LIMIT ...
	);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$edit_row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	$s_date=date("Y-m-d", $edit_row['startdate']);
	$s_hours=date("H", $edit_row['startdate']);
	$s_minutes=date("i", $edit_row['startdate']);
	$e_date=date("Y-m-d", $edit_row['enddate']);
	$e_hours=date("H", $edit_row['enddate']);
	$e_minutes=date("i", $edit_row['enddate']);
}
if ($this->get['coupons_id']) {
	$title=$this->pi_getLL('edit');
} else {
	$title=$this->pi_getLL('add');
}
$title.=" ".$this->pi_getLL('coupon_code');
$title=ucfirst($title);
// create / edit form
$content.='
<div class="panel-heading"><h3>'.$title.'</h3></div>
<div class="panel-body">
<form method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" class="form-horizontal ms_admin_form">
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('coupon_code').'</label>
	<div class="col-md-10">
		<input type="text" class="form-control" name="code" value="'.$edit_row['code'].'" />
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('discount').'</label>
	<div class="col-md-10 form-inline">
	<input type="text" class="form-control" name="discount" value="'.$edit_row['discount'].'" />
	<select name="discount_type" class="form-control">
		<option value="percentage"'.($edit_row['discount_type']=='percentage' ? ' selected' : '').'>'.$this->pi_getLL('percentage').'</option>
		<option value="price"'.($edit_row['discount_type']=='price' ? ' selected' : '').'>'.$this->pi_getLL('price').'</option>
	</select>
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('start_time').'</label>
	<div class="col-md-10 form-inline">
	<input type="text" name="s_date" id="s_date_visitor" class="form-control dateok" value="'.$s_date.'" />
	<input type="hidden" name="s_date" id="s_date" class="dateok" value="'.$s_date.'" />
';
$content.='<select name="s_hours" class="form-control">';
for ($i=0; $i<24; $i++) {
	$hour=str_pad($i, 2, "0", STR_PAD_LEFT);
	$content.='<option value="'.$hour.'"'.($s_hours==$hour ? ' selected' : '').'>'.$hour.'</option>';
}
$content.='</select> : ';
$content.='<select name="s_minutes" class="form-control">';
for ($i=0; $i<61; $i++) {
	$minute=str_pad($i, 2, "0", STR_PAD_LEFT);
	$content.='<option value="'.$minute.'"'.($s_minutes==$minute ? ' selected' : '').'>'.$minute.'</option>';
}
$content.='</select>
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('end_time').'</label>
	<div class="col-md-10 form-inline">
	<input type="text" name="e_date_visitor" id="e_date_visitor" class="form-control dateok" value="'.$e_date.'" />
	<input type="hidden" name="e_date" id="e_date" value="'.$e_date.'" />
';
$content.='<select name="e_hours" class="form-control">';
for ($i=0; $i<24; $i++) {
	$hour=str_pad($i, 2, "0", STR_PAD_LEFT);
	$content.='<option value="'.$hour.'"'.($e_hours==$hour ? ' selected' : '').'>'.$hour.'</option>';
}
$content.='</select> : ';
$content.='<select name="e_minutes" class="form-control">';
for ($i=0; $i<61; $i++) {
	$minute=str_pad($i, 2, "0", STR_PAD_LEFT);
	$content.='<option value="'.$minute.'"'.($e_minutes==$minute ? ' selected' : '').'>'.$minute.'</option>';
}
$content.='</select>
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('max_usage').'</label>
	<div class="col-md-10">
		<input type="text" class="form-control" name="max_usage" value="'.$edit_row['max_usage'].'" />
	</div>
</div>';
$active_shop=mslib_fe::getActiveShop();
if (count($active_shop)>1) {
	$content.='<div class="form-group">
			<label for="related_shop_pid" class="control-label col-md-2">'.$this->pi_getLL('relate_shipping_to_shop', 'Relate this method to').'</label>
			<div class="col-md-10">
			<div class="radio radio-success radio-inline"><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0"'.(($edit_row['page_uid']==0) ? ' checked="checked"' : '').' /><label>'.$this->pi_getLL('relate_payment_to_all_shop', 'All shop').'</label></div>';
	foreach ($active_shop as $pageinfo) {
		$content.='<div class="radio radio-success radio-inline"><input name="related_shop_pid" id="related_shop_pid" type="radio" value="'.$pageinfo['uid'].'"'.(($edit_row['page_uid']==$pageinfo['uid']) ? ' checked="checked"' : '').' /><label>'.$pageinfo['title'].'</label></div>';
	}
	$content.='</div></div>';
} else {
	$content.='<input type="hidden" name="related_shop_pid" value="'.$row['page_uid'].'">';
}
$content.='<div class="form-group">
	<div class="col-md-10 col-md-offset-2">
		<input type="hidden" name="coupons_id" value="'.$edit_row['id'].'" />
		<button type="submit" class="btn btn-success" name="editpost" value=""><i class="fa fa-save"></i> '.$this->pi_getLL('save').'</button>
	</div>
</div>
</form>
';
// list the existing coupon codes
$str="SELECT * from tx_multishop_coupons where (page_uid=0 or page_uid='".$this->showCatalogFromPage."') order by discount";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$coupons_options=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$coupons_options[]=$row;
}
if (count($coupons_options)>0) {
	$content.='<div class="panel panel-default multishop_fieldset">';
	$content.='<div class="panel-heading"><h3>'.$this->pi_getLL('coupon_codes').'</h3></div>';
	$content.='<div class="panel-body">';
	$content.='<table width="100%" border="0" align="center" class="table table-striped table-bordered msadmin_orders_listing" id="product_import_table">';
	$content.='<thead><tr>
				<th class="cellName">'.$this->pi_getLL('coupon_code').'</th>
				<th width="60" nowrap>'.$this->pi_getLL('discount').'</th>
				<th class="cellDate">'.$this->pi_getLL('start_time').'</th>
				<th class="cellDate">'.$this->pi_getLL('end_time').'</th>
				<th width="150" nowrap>'.$this->pi_getLL('max_usage').'</th>
				<th width="120" nowrap>'.$this->pi_getLL('times_used').'</th>
				<th class="cellStatus">'.$this->pi_getLL('status').'</th>
				<th class="cellAction">'.$this->pi_getLL('action').'</th>
			</tr></thead><tbody>';
	foreach ($coupons_options as $option) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		if (!$option['max_usage']) {
			$option['max_usage']=$this->pi_getLL('unlimited');
		}
		$content.='<tr class="'.$tr_type.'">
		<td class="cellName">
		<strong><a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&coupons_id='.$option['id'].'&edit=1').'">'.$option['code'].'</a></strong>
		</td>
		<td align="right">';
		switch ($option['discount_type']) {
			case 'percentage':
				$content.=number_format($option['discount']).'%';
				break;
			case 'price':
				$content.=mslib_fe::amount2Cents($option['discount'], 0);
				break;
		}
		$content.='
		</td>
		<td class="cellDate">'.strftime("%x %X", $option['startdate']).'</td>
		<td class="cellDate">'.strftime("%x %X", $option['enddate']).'</td>
		<td align="center">'.$option['max_usage'].'</td>
		<td align="center">'.$option['times_used'].'</td>
		<td class="cellStatus">';
		if (!$option['status']) {
			$content.='<span class="admin_status_red" alt="'.$this->pi_getLL('disable').'"></span>';
			$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&coupons_id='.$option['id'].'&status=1').'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('enabled').'"></span></a>';
		} else {
			$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&coupons_id='.$option['id'].'&status=0').'"><span class="admin_status_red disabled" alt="'.$this->pi_getLL('disabled').'"></span></a>';
			$content.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
		}
		$content.='
		</td>
		<td class="cellAction">';
		$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&coupons_id='.$option['id'].'&edit=1').'" " class="btn btn-primary btn-sm admin_menu_edit" alt="'.$this->pi_getLL('edit').'"><i class="fa fa-pencil"></i></a> ';
		$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&coupons_id='.$option['id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="'.$this->pi_getLL('admin_label_alt_remove').'"><i class="fa fa-trash-o"></i></a>';
		$content.='</td>
		</tr>
		';
	}
	$content.='</tbody></table>';
	$content.='</div></div>';
}
$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';

?>