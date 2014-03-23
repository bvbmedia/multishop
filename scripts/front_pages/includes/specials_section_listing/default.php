<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$teller=0;
$specials_items='';
$content.='<ul class="msFrontSpecialsListingSection">';
if(!$this->imageWidth) {
	$this->imageWidth=200;
}
foreach($products as $product) {
	if($product['products_image']) {
		$image='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'">';
	} else {
		$image='<div class="no_image"></div>';
	}
	if($product['categories_id']) {
		// get all cats to generate multilevel fake url
		$level=0;
		$cats=mslib_fe::Crumbar($product['categories_id']);
		$cats=array_reverse($cats);
		$where='';
		if(count($cats) > 0) {
			foreach($cats as $cat) {
				$where.="categories_id[".$level."]=".$cat['id']."&";
				$level++;
			}
			$where=substr($where, 0, (strlen($where)-1));
			$where.='&';
		}
		// get all cats to generate multilevel fake url eof
	}
	$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
	$tel++;
	$content.='<li class="multishop_specialsbox_item"><h3><a href="'.$link.'" class="ajax_link">'.$product['products_name'].'</a></h3><div class="multishop_specialsbox_item_image"><a href="'.$link.'" title="'.htmlspecialchars($product['products_name']).'" class="ajax_link">'.$image.'</a></div>';
	$final_price=mslib_fe::final_products_price($product);
	if(!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
		$old_price=$product['products_price']*(1+$product['tax_rate']);
	} else {
		$old_price=$product['products_price'];
	}
	if($old_price and $final_price) {
		$content.='<div class="section_products_old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="section_products_specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	} else {
		$content.='<div class="section_products_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	}
	$content.='</li>';
}
$content.='</ul>';
//			$content.='</div></div>';		
?>