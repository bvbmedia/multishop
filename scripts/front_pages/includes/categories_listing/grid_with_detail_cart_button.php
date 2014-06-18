<?php
// load optional cms content and show the current category name
if ($current['content']) {
	$content.=mslib_fe::htmlBox($current['categories_name'], $current['content'], 1);
} elseif ($current['categories_name']) {
	$content.='<div class="main-heading"><h1>'.$current['categories_name'].'</h1></div>';
}
// load optional cms content and show the current category name eof
$content.='<ul id="category_listing">';
$counter=0;
foreach ($categories as $category) {
	$counter++;
	if ($category['categories_image']) {
		$image='<img src="'.mslib_befe::getImagePath($category['categories_image'], 'categories', 'normal').'" alt="'.htmlspecialchars($category['categories_name']).'">';
	} else {
		$image='<div class="no_image"></div>';
	}
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($category['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats)>0) {
		foreach ($cats as $item) {
			$where.="categories_id[".$level."]=".$item['id']."&";
			$level++;
		}
		$where=substr($where, 0, (strlen($where)-1));
//			$where.='&';
	}
//		$where.='categories_id['.$level.']='.$category['categories_id'];
	// get all cats to generate multilevel fake url eof
	if ($category['categories_url']) {
		$link=$category['categories_url'];
	} else {
		$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
	}
	$content.='<li class="item_'.$counter.'"';
	if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
		$content.=' id="sortable_subcat_'.$category['categories_id'].'" ';
	}
	$content.='>
		<div class="image"><a href="'.$link.'" title="'.htmlspecialchars($category['categories_name']).'" class="ajax_link">'.$image.'</a></div>
		<h2><a href="'.$link.'" class="ajax_link">'.$category['categories_name'].'</a></h2>
		<div class="description">'.$category['categories_name'].'</div>
		<div class="link_detail"><a href="'.$link.'" class="ajax_link">'.$this->pi_getLL('details').'</a></div>
		';
	if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
		$content.='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id'].'&action=edit_category',1).'" class="admin_menu_edit">Edit</a><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id'].'&action=delete_category',1).'" class="admin_menu_remove" title="Remove"></a></div>';
	}
	$content.='
		</li>';
}
$content.='</ul>';
if ($current['content_footer']) {
	$content.='<div class="msCategoriesFooterDescription">'.mslib_fe::htmlBox('', $current['content_footer'], 2).'</div>';
}

?>