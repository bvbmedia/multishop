<?php
if ($current['content']) {
	$content.=mslib_fe::htmlBox($current['categories_name'], $current['content'], 1);
} else {
	$content.='<div class="main-heading"><h1>'.$current['categories_name'].'</h1></div>';
}
$content.='<ul id="menu_category_listing">';
$counter=0;
foreach ($categories as $category) {
	$counter++;
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($this->get['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats)>0) {
		foreach ($cats as $item) {
			$where.="categories_id[".$level."]=".$item['id']."&";
			$level++;
		}
		$where=substr($where, 0, (strlen($where)-1));
		$where.='&';
	}
	$where.='categories_id['.$level.']='.$category['categories_id'];
	// get all cats to generate multilevel fake url eof
	$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
	$content.='<li class="item_'.$counter.'"';
	if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
		$content.=' id="sortable_subcat_'.$category['categories_id'].'" ';
	}
	$content.='>
		<h2>'.$category['categories_name'].'</h2>
		';
	// now try to grab the products
	// product listing
	$str="SELECT *, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_id=pd.products_id and p.products_status=1 and p.products_id=p2c.products_id and p2c.categories_id='".$category['categories_id']."' and p.page_uid='".$this->showCatalogFromPage."' and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id order by p2c.sort_order";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$qry2=$GLOBALS['TYPO3_DB']->sql_query("SELECT FOUND_ROWS();");
	$row2=$GLOBALS['TYPO3_DB']->sql_fetch_row($qry2);
	$totalrows=$row2[0];
	if ($this->ADMIN_USER and $this->get['sort_by']) {
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
			switch ($this->get['sort_by']) {
				case 'alphabet':
					$str="SELECT *, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_id=pd.products_id and p.products_status=1 and p.products_id=p2c.products_id and p2c.categories_id='".$category['categories_id']."' and   p.page_uid='".$this->showCatalogFromPage."' and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id order by pd.products_name";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$counter=0;
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$updateArray=array();
						$updateArray['sort_order']=$counter;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'categories_id='.$row['categories_id'].' and products_id='.$row['products_id'], $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						$counter++;
					}
					$str="SELECT *, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id = m.manufacturers_id, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p.products_id=pd.products_id and p.products_status=1 and p.products_id=p2c.products_id and p2c.categories_id='".$category['categories_id']."' and   p.page_uid='".$this->showCatalogFromPage."' and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id order by p2c.sort_order";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					break;
			}
		}
	}
	$products=array();
	while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
		$products[]=$row;
	}
	$content.='<ul id="menu_product_listing_'.$category['categories_id'].'">';
	$counter=0;
	foreach ($products as $item) {
		if ($item['categories_id']) {
			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($item['categories_id']);
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
		$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$item['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
		$catlink=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
		$counter++;
		if ($item['products_image']) {
			$image='<img src="'.$this->ms['image_paths']['products']['100'].'/'.$item['products_image'].'" alt="'.htmlspecialchars($item['products_name']).'">';
		} else {
			$image='<div class="no_image"></div>';
		}
		$content.='<li id="productlisting_'.$item['products_id'].'">';
		if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
			$content.='<div class="admin_menu"><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$item['products_id'].'&action=edit_product', 1).'" class="admin_menu_edit"></a> <a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$item['products_id'].'&action=delete_product', 1).'" class="admin_menu_remove" title="Remove"></a></div>';
		}
//			if ($item['products_quantity'] > 0)	$content.='<div style="float:right;color:green">'.$item['products_quantity'].'</div>';			
//			else											$content.='<div style="float:right;color:red">not on stock</div>';					
		$content.='<a href="'.$link.'" class="ajax_link">'.$item['products_name'].'</a>';
		if ($item['products_price']>0) {
			if ($item['products_price']<>$item['final_price']) {
				$content.='<div class="old_price">'.mslib_fe::amount2Cents($item['products_price']).'</div><div class="specials_price">'.mslib_fe::amount2Cents($item['final_price']).'</div>';
			} else {
				$content.='<div class="price">'.mslib_fe::amount2Cents($item['products_price']).'</div>';
			}
		}
		$content.='</li>';
	}
	$content.='</ul>';
	if ($this->ADMIN_USER and $this->ms['page']<>'products_search') {
		$content.='
			<script>
			  jQuery(document).ready(function($) {
				var result = jQuery("#menu_product_listing_'.$category['categories_id'].'").sortable({
					cursor:     "move", 
					//axis:       "y", 
					update: function(e, ui) { 
						href = "'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=product&catid='.$item['categories_id']).'";
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
	// product listing eof
	// now try to grab the products eof
	$content.='
		</li>';
}
$content.='</ul>';
if ($current['content_footer']) {
	$content.='<div class="msCategoriesFooterDescription">'.mslib_fe::htmlBox('', $current['content_footer'], 2).'</div>';
}
?>