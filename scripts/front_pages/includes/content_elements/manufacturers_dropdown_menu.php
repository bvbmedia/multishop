<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$content.='
<form action="index.php" method="get" name="products_searchform" id="multishop_products_searchform">
<input name="id" type="hidden" value="'.$this->conf['search_page_pid'].'" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="products_search" />

<div class="form-fieldset">
<select name="manufacturers_id" id="manufacturers_dropdown_menu"><option value="">'.htmlspecialchars($this->pi_getLL('manufacturers')).'</option>						
';
$str="SELECT * from tx_multishop_manufacturers m, tx_multishop_manufacturers_info mi where m.manufacturers_id=mi.manufacturers_id and m.status=1 order by m.sort_order,m.manufacturers_name";

$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$manufacturers=array();	
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
	$manufacturers[]=$row;
}
if (count($manufacturers) > 0) {
	foreach ($manufacturers as $manufacturer) {
		$link=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid,'tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id='.$manufacturer['manufacturers_id']);
		$content.='<option value="'.$link.'"'.($this->get['manufacturers_id']==$manufacturer['manufacturers_id']?' selected':'').'>'.htmlspecialchars($manufacturer['manufacturers_name']).'</option>'."\n";
	}
}
$content.='</select>						
</div>
</form>
<script>
	jQuery("#manufacturers_dropdown_menu").change(function() {
		window.location = jQuery(this).val();
	});						
</script>';	
?>