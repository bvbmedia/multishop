<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->post) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_shipping_countries', 'page_uid=\''.$this->showCatalogFromPage.'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	foreach ($this->post['countries'] as $iso=>$activate) {
		if ($activate) {
			$replaceArray=array();
			$replaceArray['cn_iso_nr']=$iso;
			$replaceArray['page_uid']=$this->showCatalogFromPage;
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_countries', $replaceArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
}
$str="SELECT * from static_countries sc order by cn_short_en";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$countries=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select * from tx_multishop_shipping_countries where cn_iso_nr='".$row['cn_iso_nr']."' and page_uid='".$this->showCatalogFromPage."' ";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)>0) {
		$row['enabled']=1;
	}
	$countries[]=$row;
}
$content.='
<div class="panel panel-default">
<div class="panel-heading"><h3>'.$this->pi_getLL('admin_label_enabled_countries').'</h3></div>
<div class="panel-body">
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
<ul id="tx_multishop_countries_checkboxes" class="list-inline">';
foreach ($countries as $country) {
	$content.='<li><div class="checkbox"><label><input name="countries['.$country['cn_iso_nr'].']" type="checkbox" value="1" '.(($country['enabled']) ? 'checked' : '').' />'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']).'</label></div></li>';
}
$content.='</ul>
<div class="clearfix">
<div class="pull-right">
<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
</div>
</div>
</form>
';
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></div></div></div>';
$content=''.mslib_fe::shadowBox($content).'';
?>