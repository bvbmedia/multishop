<?php
if(!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if(!$this->imageWidth) {
	$this->imageWidth='100';
}
$teller=0;
$specials_items='';
if(!$this->hideHeader) {
	$content.='<div class="main-heading"><h2>'.$this->pi_getLL('specials').'</h2></div>';
}
foreach($products as $product) {
	if($product['products_image']) {
		$image=mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth);
	} else {
		$image='';
	}
	$teller++;
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
	if($this->conf['disableFeFromCalculatingVatPrices'] == '1') {
		$final_price=$product['final_price'];
		$old_price=$product['products_price'];
	} else {
		$final_price=mslib_fe::final_products_price($product);
		if(!$this->ms['MODULES']['DB_PRICES_INCLUDE_VAT'] and ($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT'])) {
			$old_price=$product['products_price']*(1+$product['tax_rate']);
		} else {
			$old_price=$product['products_price'];
		}
	}
	if(!$this->ajax_content) {
		if($old_price) {
			$old_price_label=mslib_fe::amount2Cents($old_price);
		} else {
			$old_price_label='';
		}
		// jcarousel
		$specials_items.='{url: \''.$link.'\', image: \''.$image.'\', title: \''.htmlspecialchars(addslashes($product['products_name'])).'\', price: \''.$old_price_label.'\', specials_price: \''.mslib_fe::amount2Cents($final_price).'\'}';
		if($teller < $total) {
			$specials_items.=',';
		}
		$specials_items.="\n";
	} else {
		if($product['products_image']) {
			$image='<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', $this->imageWidth).'" alt="'.htmlspecialchars($product['products_name']).'" />';
		} else {
			$image='<div class="no_image"></div>';
		}
		// normal 
		$specials_items.='<li id="productlisting_'.$product['products_id'].'">
		<strong class="products_name"><a href="'.$link.'" class="ajax_link">'.$product['products_name'].'</a></strong>
		<div class="image"><a href="'.$link.'" title="'.htmlspecialchars($product['products_name']).'" class="ajax_link">'.$image.'</a></div>	
		';
		$final_price=mslib_fe::final_products_price($product);
		if($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_WITH_AND_WITHOUT_VAT']) {
			$specials_items.='<div class="price_excluding_vat">'.$this->pi_getLL('excluding_vat').' '.mslib_fe::amount2Cents($product['final_price']).'</div>';
		}
		if($product['products_price'] <> $product['final_price']) {
			if($product['tax_rate'] and $this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$old_price=$product['products_price']*(1+$product['tax_rate']);
			} else {
				$old_price=$product['products_price'];
			}
			$specials_items.='<div class="old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="final_price">'.mslib_fe::amount2Cents($final_price).'</div>';
		} else {
			$specials_items.='<div class="final_price">'.mslib_fe::amount2Cents($final_price).'</div>';
		}
		if($this->ADMIN_USER) {
			$specials_items.='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$product['products_id']).'&action=edit_product" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="admin_menu_edit"></a> <a href="'.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$product['products_id']).'&action=delete_product" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="admin_menu_remove" title="Remove"></a></div>';
		}
		$specials_items.='
		<div class="buttons">
			<div class="button_bekijk">
				<a href="'.$link.'" title="Bekijk '.htmlspecialchars($product['products_name']).'" class="ajax_link">Bekijk</a>
			</div>
			<div class="button_bestel">
				';
		if(!strstr($product['products_url'], 'http://') and !strstr($product['products_url'], 'http://')) {
			$product['products_url']='http://'.$product['products_url'];
		}
		$specials_items.='
				<a href="'.$product['products_url'].'" title="Bestel '.htmlspecialchars($product['products_name']).'" class="ajax_link">Bestel</a>
			</div>
		</div>		
		';
		$specials_items.='</li>';
	}
}
if($specials_items and $this->ajax_content) {
	// ajax so lets show the normal grid layout
	$content.='<ul id="product_listing">'.$specials_items.'</ul>';
} elseif($specials_items and !$this->ajax_content) {
	$content.='
	<script type="text/javascript"> 
	var mycarousel_itemList_'.$content_uid.' = [
		'.$specials_items.'
	];
	function mycarousel_itemVisibleInCallback(carousel, item, i, state, evt)
	{
		// The index() method calculates the index from a
		// given index who is out of the actual item range.
		var idx = carousel.index(i, mycarousel_itemList_'.$content_uid.'.length);
		carousel.add(i, mycarousel_getItemHTML(mycarousel_itemList_'.$content_uid.'[idx - 1]));
	};
	function mycarousel_itemVisibleOutCallback(carousel, item, i, state, evt)
	{
		carousel.remove(i);
	};
	/**
	 * Item html creation helper.
	 */
	function mycarousel_getItemHTML(item)
	{
		var image_tag=\'\';
		if (item.image) image_tag=\'<img src="\' + item.image + \'" alt="\' + item.title + \'" />\';
		else			image_tag=\'<div class="no_image"></div>\'
		return \'<div class="carousel_image"><a href="\' + item.url + \'" class="ajax_link">\'+image_tag+\'</a></div><div class="carousel_title"><a href="\' + item.url + \'" class="ajax_link">\' + item.title + \'</a></div><div class="carousel_price">\' + item.price + \'</div><div class="carousel_specials_price">\' + item.specials_price + \'</div>\';
	};
	 
	jQuery(document).ready(function($) {
		jQuery(\'#mycarousel_'.$content_uid.'\').jcarousel({
			wrap: \'circular\',
			auto:3,
			animation:1000,
			scroll:1,
			itemVisibleInCallback: {onBeforeAnimation: mycarousel_itemVisibleInCallback},
			itemVisibleOutCallback: {onAfterAnimation: mycarousel_itemVisibleOutCallback}
		});
	});
	</script>
	';
	$content.='
	 <ul id="mycarousel_'.$content_uid.'" class="jcarousel-skin-ie7">
		<!-- The content will be dynamically loaded in here -->
	  </ul>
	';
}
?>