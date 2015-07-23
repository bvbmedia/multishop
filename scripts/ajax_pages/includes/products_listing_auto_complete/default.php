<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$prod=array();
foreach ($products as $product) {
	if (!$tr_type or $tr_type=='even') {
		$tr_type='odd';
	} else {
		$tr_type='even';
	}
	if ($product['categories_id']) {
		// get all cats to generate multilevel fake url
		$level=0;
		$cats=mslib_fe::Crumbar($product['categories_id']);
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
	$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail&skeywords='.urlencode($this->get['q']));
	$prod['Link']=$link;
	if ($product['products_image']) {
		$prod['Image']='<div class="ajax_products_image">'.'<img src="'.mslib_befe::getImagePath($product['products_image'], 'products', '50').'">'.'</div>';
	} else {
		$prod['Image']='<div class="ajax_products_image"><div class="no_image_50"></div></div>';
	}
	$prod['Title']='<div class="ajax_products_name">'.substr($product['products_name'], 0, 50).'</div>';
	$prod['Title']=$prod['Title'];
	$prod['Desc']='<div class="ajax_products_shortdescription">'.addslashes(mslib_befe::str_highlight(substr($product['products_shortdescription'], 0, 75), $this->get['q'])).'</div>';
	$final_price=mslib_fe::final_products_price($product);
	if ($product['products_price']<>$product['final_price']) {
		$old_price=$product['products_price']*(1+$product['tax_rate']);
		$prod['Price']='<div class="ajax_products_price"><div class="ajax_old_price">'.mslib_fe::amount2Cents($old_price).'</div><div class="ajax_specials_price">'.mslib_fe::amount2Cents($final_price).'</div></div>';
		$prod['PriceNum']=$final_price;
	} else {
		$prod['Price']='<div class="ajax_products_price"><div class="ajax_normal_price">'.mslib_fe::amount2Cents($final_price).'</div></div>';
		$prod['PriceNum']=$final_price;
	}
	$prod['Name']=substr($product['products_name'], 0, 50);
	if ($this->get['tx_multishop_pi1']['type']=='edit_order') {
		// admin edit order needs the price without vat as well
		$prod['final_price']=round($product['final_price'], 4);
		//$prod['tax_id']=$product['tax_id'];
		$prod['tax_rate']=($product['tax_rate']*100);
	}
	$prod['skeyword']=$this->get['q'];
	$prod['Page']=$pages;
	$prod['Product']=true;
	$prod['products_id']=$product['products_id'];
	$data[]=$prod;
}
$totpage=ceil($pageset['total_rows']/$limit);
//echo $totpage;
$pages=!$this->get['page'] ? 0 : $this->get['page'];
if ($pages>$totpage) {
	$this->get['page']=$totpage;
}
if ($pages<$totpage) {
	$pages=$pages+1;
} else {
	$pages=0;
}
if (isset($p)) {
	if ($totpage>1) {
		//echo $totpage;
		if ($pages!=$totpage) {
			$prod=array();
			$prod['Name']=$this->pi_getLL('more_results');
			$prod['Title']='<span id="more-results">'.htmlspecialchars($this->pi_getLL('more_results')).' >></span>';
			$prod['Link']=mslib_fe::typolink($this->shop_pid, 'tx_multishop_pi1[page_section]=products_search&skeyword='.urlencode($this->get['q']));
			$prod['skeyword']=$this->get['q'];
			$prod['Page']=$pages;
			$prod['Product']=false;
			$data[]=$prod;
		}
	}
}
$content=array("products"=>$data);
//print_r($alldata);
?>