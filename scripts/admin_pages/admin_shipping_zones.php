<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->get['delete'] and is_numeric($this->get['zone_id'])) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_zones', 'id=\''.$this->get['zone_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_countries_to_zones', 'zone_id=\''.$this->get['zone_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
if ($this->post) {
	// add countries to a specific zone
	if (is_numeric($this->post['zone_id'])) {
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_countries_to_zones', 'zone_id=\''.$this->post['zone_id'].'\'');
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		foreach ($this->post['countries'] as $country=>$value) {
			if (is_numeric($country)) {
				$insertArray=array();
				$insertArray['zone_id']=$this->post['zone_id'];
				$insertArray['cn_iso_nr']=$country;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_countries_to_zones', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
	// add countries to a specific zone eof
	// add new zone name
	if ($this->post['zone_name']) {
		$insertArray=array();
		$insertArray['name']=$this->post['zone_name'];
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_zones', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	// add new zone name eof
}
$str="SELECT * from tx_multishop_zones order by name";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$zones=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$zones[]=$row;
}
foreach ($zones as $zone) {
	$content.='<div class="panel panel-default multishop_fieldset">';
	$content.='<div class="panel-heading"><h3>Zone: '.$zone['name'].'</h3></div><div class="panel-body">';
	$str="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	if (is_numeric($this->get['zone_id']) and $this->get['edit'] and $this->get['zone_id']==$zone['id']) {
		$str="SELECT sc.*, c.id as cid from static_countries sc, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr order by sc.cn_short_en";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$countries=array();
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['cid']) {
				$str2="select ctz.zone_id from tx_multishop_countries_to_zones ctz, tx_multishop_zones z where ctz.cn_iso_nr='".$row['cn_iso_nr']."' and ctz.zone_id=z.id";
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
					$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
					if ($row2['zone_id']==$this->get['zone_id']) {
						$row['current']=1;
						$countries[]=$row;
					}
				} else {
					$countries[]=$row;
				}
			}
		}
		if (count($countries)>0) {
			$content.='
			<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post" id="zone_edit_form">
			<input name="zone_id" type="hidden" value="'.$this->get['zone_id'].'" />
			<ul id="tx_multishop_countries_checkboxes" class="zone_items">';
			$counter=0;
			foreach ($countries as $country) {
				$content.='<li class="zone_item_country"><div class="checkbox checkbox-success"><input name="countries['.$country['cn_iso_nr'].']" type="checkbox" value="1" '.(($country['current']) ? 'checked' : '').' id="zone_country_'.$counter.'" /><label for="zone_country_'.$counter.'">'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']).'</label></div></li>';
				$counter++;
			}
			$content.='</ul>
			<hr>
			<div class="clearfix">
			<div class="pull-right">
			<button name="Submit" type="submit" value="" onclick="history.back();return false;" class="btn btn-danger btn-sm"><i class="fa fa-remove"></i> '.$this->pi_getLL('cancel').'</button>
			<button name="Submit" type="submit" value="" class="btn btn-success btn-sm"><i class="fa fa-save"></i> '.$this->pi_getLL('save').'</button>
			</div>
			</div>
			</form>
			';
		} else {
			$content.='Currently all active countries are in use. <input name="Submit" type="submit" value="'.$this->pi_getLL('cancel').'" onclick="history.back();return false;" class="btn btn-success" />';
		}
	} else {
		if ($rows>0) {
			$content.='<ul class="zone_items fa-ul">';
			while (($country=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$content.='<li class="zone_item_country"><i class=" fa-li fa fa-square"></i>'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']).'</li>';
			}
			$content.='</ul>';
		} else {
			$content.=$this->pi_getLL('admin_label_current_no_countries_mapped_to_this_zone');
		}
	}
	if ($this->get['zone_id']!=$zone['id']) {
		$content.='<hr>
		<div class="clearfix">
		<div class="pull-right">
			<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&edit=1&zone_id='.$zone['id']).'" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> '.$this->pi_getLL('add_countries').'</a>
			<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&delete=1&zone_id='.$zone['id']).'" class="btn btn-danger btn-sm"><i class="fa fa-save"></i> '.$this->pi_getLL('delete_zone').'</a>
			</div>
		</div>';
	}
	$content.='</div></div>';
}
$content.='
<div class="panel panel-default">
<div class="panel-heading"><h3>'.$this->pi_getLL('add_new_zone').'</h3></div>
<div class="panel-body">
<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post" class="form-horizontal">
<div class="form-group">
		<label for="" class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
		<div class="col-md-10">
			<div class="input-group">
			<input class="form-control" type="text" name="zone_name" id="zone_name" value="">
			<span class="input-group-btn">
			<input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="btn btn-success" />
			</span>
			</div>
		</div>
</div>
</form>
</div>
</div>
';
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div></div>';
$content='<div class="panel panel-default"><div class="panel-body">'.mslib_fe::shadowBox($content).'';
?>