<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($this->post) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_shipping_countries', 'page_uid=\''.$this->showCatalogFromPage.'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	foreach($this->post['countries'] as $iso=>$activate) {
		if($activate) {
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
while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
	$str2="select * from tx_multishop_shipping_countries where cn_iso_nr='".$row['cn_iso_nr']."' and page_uid='".$this->showCatalogFromPage."' ";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if($GLOBALS['TYPO3_DB']->sql_num_rows($qry2) > 0) {
		$row['enabled']=1;
	}
	$countries[]=$row;
}
$content.='
<div class="main-heading"><h2>Enabled Countries</h2></div>
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
<ul id="tx_multishop_countries_checkboxes">';
foreach($countries as $country) {
	$content.='<li><input name="countries['.$country['cn_iso_nr'].']" type="checkbox" value="1" '.(($country['enabled']) ? 'checked' : '').' /> '.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']).'</li>';
}
$content.='</ul>
<input name="Submit" type="submit" value="Save" class="msadmin_button" />
</form>
';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>