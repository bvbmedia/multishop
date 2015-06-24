<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content.='<h1>'.strtoupper($this->pi_getLL('admin_sort_products')).'</h1>';
//
$query_c=$GLOBALS['TYPO3_DB']->SELECTquery('p2c.categories_id, cd.categories_name', // SELECT ...
    'tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
    'c.page_uid='.$this->showCatalogFromPage.' and cd.language_id='.$this->sys_language_uid.' and p2c.categories_id=c.categories_id and p2c.is_deepest=1 and c.categories_id=cd.categories_id', // WHERE...
    'p2c.categories_id', // GROUP BY...
    'cd.categories_name asc', // ORDER BY...
    '' // LIMIT ...
);
$res_c=$GLOBALS['TYPO3_DB']->sql_query($query_c);
$categories_list=array();
while ($row_c=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_c)) {
    $catname=array();
    if ($row_c['categories_id']) {
        $cats=mslib_fe::Crumbar($row_c['categories_id']);
        $cats=array_reverse($cats);
        $where='';
        if (count($cats)>0) {
            foreach ($cats as $cat) {
                $catname[]=$cat['name'];
            }
        }
    }
    //
    $categories_list[implode(' > ', $catname)]=$row_c['categories_id'];
}
ksort($categories_list);
if (count($categories_list)) {
    $categories_option=array();
    foreach ($categories_list as $catpath => $catid) {
        if (isset($this->get['tx_multishop_pi1']['categories_id']) && $this->get['tx_multishop_pi1']['categories_id']==$catid) {
            $categories_option[]='<option value="'.$catid.'" selected="selected">'.$catpath.' (ID: '.$catid.')</option>';
        } else {
            $categories_option[]='<option value="'.$catid.'">'.$catpath.' (ID: '.$catid.')</option>';
        }
    }
}
//
$content.='<form name="sort_products_categories" id="sort_products_categories" method="get" action="">';
$content.='<input type="hidden" name="id" value="'.$this->shop_pid.'">';
$content.='<input type="hidden" name="type" value="2003">';
$content.='<input type="hidden" name="tx_multishop_pi1[page_section]" value="admin_sort_products">';
$content.='<select name="tx_multishop_pi1[categories_id]" id="sort_categories_id" style="width:400px"><option value="">'.$this->pi_getLL('choose').'</option>'.implode("\n", $categories_option).'</select>';
$content.='</form>';
if (isset($this->get['tx_multishop_pi1']['categories_id']) && is_numeric($this->get['tx_multishop_pi1']['categories_id']) && $this->get['tx_multishop_pi1']['categories_id']>0) {
    $query_p=$GLOBALS['TYPO3_DB']->SELECTquery('p.products_id, p.products_image, pd.products_name', // SELECT ...
            'tx_multishop_products_to_categories p2c, tx_multishop_products p, tx_multishop_products_description pd', // FROM ...
            'p.page_uid=' . $this->showCatalogFromPage . ' and pd.language_id=' . $this->sys_language_uid . ' and p2c.categories_id=' . (int)$this->get['tx_multishop_pi1']['categories_id'] . ' and p.products_id=pd.products_id and p2c.products_id=p.products_id and p2c.is_deepest=1', // WHERE...
            'p.products_id', // GROUP BY...
            'p2c.sort_order asc', // ORDER BY...
            '' // LIMIT ...
    );
    //
    $res_p=$GLOBALS['TYPO3_DB']->sql_query($query_p);
    $products_list=array();
    while ($row_p=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_p)) {
        $tmp_product='';
        //
        $tmp_product.='<strong>'.htmlspecialchars($row_p['products_name']).' (ID: '.$row_p['products_id'].')</strong>';
        //
        $imagePath='<div class="no_image"></div>';
        if ($row_p['products_image']) {
            $imagePath='<img src="'.mslib_befe::getImagePath($row_p['products_image'], 'products', '50').'" alt="'.htmlspecialchars($row_p['products_name']).'" />';
        }
        /*$tmp_product.='<div class="image">
           '.$imagePath.'
        </div>';*/
        $tmp_product.='<div class="button_wrapper">
           <button type="button" class="btnOneUp btn btn-default btn-sm" rel="#productlisting_'.$row_p['products_id'].'"><span class="glyphicon glyphicon-arrow-up"></span>Up</button>
           <button type="button" class="btnOneDown btn btn-default btn-sm" rel="#productlisting_'.$row_p['products_id'].'"><span class="glyphicon glyphicon-arrow-down"></span>Down</button>
           <button type="button" class="btnTop btn btn-default btn-sm" rel="#productlisting_'.$row_p['products_id'].'"><span class="glyphicon glyphicon-arrow-up"></span>Top</button>
           <button type="button" class="btnBottom btn btn-default btn-sm" rel="#productlisting_'.$row_p['products_id'].'"><span class="glyphicon glyphicon-arrow-down"></span>Bottom</button>
        </div>';
        $products_list[]='<li id="productlisting_'.$row_p['products_id'].'">'.$tmp_product.'</li>';
    }
    if (count($products_list)) {
        $content.='<ul class="admin_sort_product_listing">';
        $content.=implode("\n", $products_list);
        $content.='</ul>';
        $content.='
<script type="text/javascript">
function AJAXSortProducts() {
    jQuery(".admin_sort_product_listing").sortable("refresh");
    sorted = jQuery(".admin_sort_product_listing").sortable("serialize", "id");
    href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=product&catid='.$this->get['tx_multishop_pi1']['categories_id']).'";
    jQuery.ajax({
        type:   "POST",
        url:    href,
        data:   sorted,
        success: function(msg) {
            //do something with the sorted data
        }
    });
}
jQuery(document).ready(function($) {
    jQuery(".admin_sort_product_listing").sortable({
        cursor:     "move",
        //axis:       "y",
        update: function(e, ui) {
            AJAXSortProducts();
        }
    });
    $(document).on("click", ".btnOneUp", function() {
        var current = $($(this).attr("rel"));
        current.prev().before(current);
        AJAXSortProducts();
    });
    $(document).on("click", ".btnOneDown", function() {
        var current = $($(this).attr("rel"));
        current.next().after(current);
        AJAXSortProducts();
    });
    $(document).on("click", ".btnTop", function() {
        var current = $($(this).attr("rel"));
        current.parent().prepend(current);
        AJAXSortProducts();
    });
    $(document).on("click", ".btnBottom", function() {
        var current = $($(this).attr("rel"));
        current.parent().append(current);
        AJAXSortProducts();
    });
});
</script>';
    }
} else {
	$content.='<p>'.$this->pi_getLL('admin_label_please_select_categories_to_display_products').'</p>';
}
$content.='<script type="text/javascript">
jQuery(document).ready(function($) {
    $("#sort_categories_id").select2();
    $(document).on("change", "#sort_categories_id", function() {
        $("#sort_products_categories").submit();
    });
});
</script>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>