<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if (!$this->imageWidth) {
	$this->imageWidth='100';
}
if ($show_default_header) {
	$content.='<div class="main-heading"><h2>'.trim($current['categories_name']).'</h2></div>';
}
$content.='<ul id="product_listing">';
$counter=0;
foreach ($products as $current_product) {
	$where='';
	if ($current_product['categories_id']) {
		// get all cats to generate multilevel fake url
		$level=0;
		$cats=mslib_fe::Crumbar($current_product['categories_id']);
		$cats=array_reverse($cats);
		$where='';
		if (count($cats)>0) {
			foreach ($cats as $cat) {
				$where.="categories_id[".$level."]=".$cat['id']."&";
				$level++;
			}
			$where=substr($where, 0, (strlen($where)-1));
			$where.='&';
		}
		// get all cats to generate multilevel fake url eof
	}
	$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$current_product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
	$catlink=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
	$counter++;
	if ($current_product['products_image']) {
		$image='<img src="'.mslib_befe::getImagePath($current_product['products_image'], 'products', $this->imageWidth).'" alt="'.htmlspecialchars($current_product['products_name']).'" />';
	} else {
		$image='<div class="no_image"></div>';
	}
	$ITEM_CLASS='';
	if (($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) and !$current_product['products_status'] and !$this->ms['MODULES']['FLAT_DATABASE']) {
		$ITEM_CLASS='disabled_product';
	}
	$content.='<li id="productlisting_'.$current_product['products_id'].'" class="'.$ITEM_CLASS.'">
	<h2><a href="'.$link.'" class="ajax_link">'.$current_product['products_name'].'</a></h2>		
	<div class="image"><a href="'.$link.'" title="'.htmlspecialchars($current_product['products_name']).'" class="ajax_link">'.$image.'</a></div>
	<div class="description">'.$current_product['products_shortdescription'].'</div>
	<div class="category"><a href="'.$catlink.'" class="ajax_link">'.$current_product['categories_name'].'</a></div>		
	<div class="link_detail"><a href="'.$link.'" class="ajax_link">'.$this->pi_getLL('details').'</a></div>
	';
	$final_price=mslib_fe::final_products_price($current_product);
	if ($current_product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
		$content.='<div class="price_excluding_vat">'.$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($current_product['final_price']).'</div>';
	}
	if ($current_product['products_price']<>$current_product['final_price']) {
		if (!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($current_product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
			$old_price=$current_product['products_price']*(1+$current_product['tax_rate']);
		} else {
			$old_price=$current_product['products_price'];
		}
		$content.='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="specials_price">'.mslib_fe::amount2Cents($final_price).'</div>';
	} else {
		$content.='<div class="price">'.mslib_fe::amount2Cents($final_price).'</div>';
	}
	if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
		$content.='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$current_product['categories_id'].'&pid='.$current_product['products_id'].'&action=edit_product',1).'" class="admin_menu_edit"></a> <a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$current_product['categories_id'].'&pid='.$current_product['products_id'].'&action=delete_product',1).'" class="admin_menu_remove" title="Remove"></a></div>';
	}
	$content.='</li>';
}
$content.='</ul>';
$skippedTypes=array();
$skippedTypes[]='products_modified';
$skippedTypes[]='products_search';
$skippedTypes[]='products_specials';
$skippedTypes[]='products_news';
if (!in_array($this->ms['page'], $skippedTypes) and ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER))) {
	$content.='					
	<script>
	  jQuery(document).ready(function($) {
		$(".disabled_product").css({ opacity: 0.6 });
		$(".disabled_product").hover(
		  function () {
			$(".disabled_product").css({ opacity: 1 });
		  },
		  function () {
			$(".disabled_product").css({ opacity: 0.6 });
		  }
		);			  
		var result = jQuery("#product_listing").sortable({
			cursor:     "move", 
			//axis:       "y", 
			update: function(e, ui) { 
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=product&catid='.$current_product['categories_id']).'";
				jQuery(this).sortable("refresh"); 
				sorted = jQuery(this).sortable("serialize", "id"); 
				jQuery.ajax({ 
						type:   "POST", 
						url:    href, 
						data:   sorted, 
						success: function(msg) { 
								//do something with the sorted data 
						} 
				}); 
			} 

		});
	  });
	  </script>					
	';
}
?>