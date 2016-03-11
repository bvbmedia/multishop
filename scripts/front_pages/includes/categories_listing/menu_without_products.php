<?php
$cols=array();
if ($current['content']) {
	$content.=mslib_fe::htmlBox($current['categories_name'], $current['content'], 1);
} else {
	$content.='<div class="main-heading"><h1>'.$current['categories_name'].'</h1></div>';
}
$counter=0;
foreach ($categories as $category) {
	$html='';
	if ($category['categories_name']) {
		$counter++;
		if (mslib_fe::hasProducts($category['categories_id'])) {
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
				$where.='&';
			}
			$where.='categories_id['.$level.']='.$category['categories_id'];
			// get all cats to generate multilevel fake url eof
			if ($category['categories_external_url']) {
				$link=$category['categories_external_url'];
			} else {
				$link=mslib_fe::typolink($this->conf['products_listing_page_pid'], '&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
			}
			$name='<a href="'.$link.'" class="ajax_link">'.$category['categories_name'].'</a>';
			$html='<div class="main_category_box">'."\n".'
			<h2>'.$name.'</h2>'."\n".'</div>';
		} else {
			$name='<span>'.$category['categories_name'].'</span>';
			$subcats_html=mslib_fe::get_subcategories_as_ul($category['categories_id']);
			if ($subcats_html) {
				$html='<div class="main_category_box">'."\n".'
				<h2>'.$name.'</h2>'."\n";
				$html.=$subcats_html."\n".'
				</div>';
			}
		}
		if ($html) {
			$cols[]=$html;
		}
	}
}
$content.='<div id="menu_category_listing">';
$delimited=ceil(count($cols)/3);
if ($delimited<1) {
	$delimited=1;
}
$counter=0;
for ($col=0; $col<3; $col++) {
	$content.='<div class="three_cols_wrapper">';
	for ($i=0; $i<$delimited; $i++) {
		if ($cols[$counter]) {
			$content.=$cols[$counter];
		}
		$counter++;
	}
	$content.='</div>';
}
$content.='</div>';
if ($current['content_footer']) {
	$content.='<div class="msCategoriesFooterDescription">'.mslib_fe::htmlBox('', $current['content_footer'], 2).'</div>';
}

?>