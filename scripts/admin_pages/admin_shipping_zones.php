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
	$content.='<fieldset class="multishop_fieldset">';
	$content.='<legend>Zone: '.$zone['name'].'</legend>';
	$str="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
	if (is_numeric($this->get['zone_id']) and $this->get['edit'] and $this->get['zone_id']==$zone['id']) {
		$str="SELECT sc.*, c.id as cid from static_countries sc, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr order by sc.cn_short_en";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$countries=array();
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			if ($row['cid']) {
				$str2="select zone_id from tx_multishop_countries_to_zones where cn_iso_nr='".$row['cn_iso_nr']."'";
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
			<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post" id="zone_edit_form">
			<input name="zone_id" type="hidden" value="'.$this->get['zone_id'].'" />
			<ul id="tx_multishop_countries_checkboxes">';
			$counter=0;
			foreach ($countries as $country) {
				$content.='<li><input name="countries['.$country['cn_iso_nr'].']" type="checkbox" value="1" '.(($country['current']) ? 'checked' : '').' id="zone_country_'.$counter.'" /><label for="zone_country_'.$counter.'">'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']).'</label></li>';
				$counter++;
			}
			$content.='</ul>
			<input name="Submit" type="submit" value="'.$this->pi_getLL('cancel').'" onclick="history.back();return false;" class="msadmin_button" /><input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="msadmin_button" />
			</form>
			';
		} else {
			$content.='Currently all active countries are in use. <input name="Submit" type="submit" value="'.$this->pi_getLL('cancel').'" onclick="history.back();return false;" class="msadmin_button" />';
		}
	} else {
		if ($rows>0) {
			$content.='<ul class="zone_item">';
			while (($country=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$content.='<li>'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']).'</li>';
			}
			$content.='</ul>';
		} else {
			$content.=$this->pi_getLL('admin_label_current_no_countries_mapped_to_this_zone');
		}
	}
	if ($this->get['zone_id']!=$zone['id']) {
		$content.='<br />
		<ul class="zone_item_buttons">
			<li><a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&edit=1&zone_id='.$zone['id']).'">'.$this->pi_getLL('add_countries').'</a></li>
			<li><a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&delete=1&zone_id='.$zone['id']).'">'.$this->pi_getLL('delete_zone').'</a></li>
		</ul>';
	}
	$content.='</fieldset>';
}
$content.='
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
<fieldset><legend>'.$this->pi_getLL('add_new_zone').'</legend>
<div class="account-field">
		<label for="">'.$this->pi_getLL('name').'</label>
		<input type="text" name="zone_name" id="zone_name" value=""> <input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="msadmin_button" />
</div>
</fieldset>
</form>
';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>