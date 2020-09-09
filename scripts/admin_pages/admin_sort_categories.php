<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$content .= '<div class="panel-heading"><h3>' . strtoupper($this->pi_getLL('admin_sort_categories')) . '</h3></div>';
//
$parent_id = 0;
if (isset($this->get['tx_multishop_pi1']['categories_id']) && $this->get['tx_multishop_pi1']['categories_id'] > 0) {
    $parent_id = $this->get['tx_multishop_pi1']['categories_id'];
}
$where_status = '';
$query_c = $GLOBALS['TYPO3_DB']->SELECTquery('c.categories_id, cd.categories_name', // SELECT ...
        'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
        'c.page_uid=' . $this->showCatalogFromPage . ' and cd.language_id=' . $this->sys_language_uid . $where_status . ' and c.categories_id=cd.categories_id', // WHERE...
        'c.categories_id', // GROUP BY...
        'cd.categories_name asc', // ORDER BY...
        '' // LIMIT ...
);
$res_c = $GLOBALS['TYPO3_DB']->sql_query($query_c);
$categories_list = array();
while ($row_c = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_c)) {
    $catname = array();
    if ($row_c['categories_id']) {
        $cats = mslib_fe::Crumbar($row_c['categories_id']);
        $cats = array_reverse($cats);
        $where = '';
        if (count($cats) > 0) {
            foreach ($cats as $cat) {
                $catname[] = $cat['name'];
            }
        }
    }
    //
    $categories_list[implode(' > ', $catname)] = $row_c['categories_id'];
}
ksort($categories_list);
$count_categories = count($categories_list);
if ($count_categories) {
    $categories_option = array();
    foreach ($categories_list as $catpath => $catid) {
        if (isset($this->get['tx_multishop_pi1']['categories_id']) && $this->get['tx_multishop_pi1']['categories_id'] == $catid) {
            $categories_option[] = '<option value="' . $catid . '" selected="selected">' . $catpath . ' (ID: ' . $catid . ')</option>';
        } else {
            $categories_option[] = '<option value="' . $catid . '">' . $catpath . ' (ID: ' . $catid . ')</option>';
        }
    }
}
//
$content .= '<div class="panel-body"><form name="sort_categories" id="sort_categories" method="get" action="">';
$content .= '<input type="hidden" name="id" value="' . $this->shop_pid . '">';
$content .= '<input type="hidden" name="type" value="2003">';
$content .= '<input type="hidden" name="tx_multishop_pi1[page_section]" value="admin_sort_categories">';
$content .= '<select name="tx_multishop_pi1[categories_id]" id="sort_categories_id" style="width:100%"><option value="">' . $this->pi_getLL('choose') . '</option>' . implode("\n", $categories_option) . '</select>';
$content .= '<div class="show_disabled_status_wrapper"><div class="checkbox checkbox-success"><input type="checkbox" name="tx_multishop_pi1[show_disabled_category]" id="show_disabled_category" value="1"' . (isset($this->get['tx_multishop_pi1']['show_disabled_category']) ? ' checked="checked"' : '') . '><label for="show_disabled_category">' . $this->pi_getLL('show_disabled_category') . '</label></div></div>';
$content .= '</form><hr>';
//
$filter = array();
$filter[] = 'c.page_uid=' . $this->showCatalogFromPage;
$filter[] = 'cd.language_id=' . $this->sys_language_uid;
$filter[] = 'c.categories_id=cd.categories_id';
//
$categories_id = 0;
if (isset($this->get['tx_multishop_pi1']['categories_id']) && is_numeric($this->get['tx_multishop_pi1']['categories_id']) && $this->get['tx_multishop_pi1']['categories_id'] > 0) {
    $categories_id = (int)$this->get['tx_multishop_pi1']['categories_id'];
    $filter[] = 'c.parent_id=' . $categories_id;
} else {
    $filter[] = 'c.parent_id=0';
}
if (!isset($this->get['tx_multishop_pi1']['show_disabled_category'])) {
    $filter[] = 'c.status=1';
}
//if ($categories_id > 0) {
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sort_categories.php']['adminSortCategoriesQuesryFilter'])) {
    $params = array(
            'filter' => &$filter,
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sort_categories.php']['adminSortCategoriesQuesryFilter'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
$query_c = $GLOBALS['TYPO3_DB']->SELECTquery('c.categories_id, c.categories_image, cd.categories_name', // SELECT ...
        'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
        implode(' and ', $filter), // WHERE...
        'c.categories_id', // GROUP BY...
        'c.sort_order asc', // ORDER BY...
        '' // LIMIT ...
);
//
$res_c = $GLOBALS['TYPO3_DB']->sql_query($query_c);
if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_c)) {
    $categories_list = array();
    while ($row_c = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_c)) {
        $tmp_category = '';
        //
        if ($categories_id) {
            // get all cats to generate multilevel fake url
            $level = 0;
            $cats = mslib_fe::Crumbar($categories_id);
            $cats = array_reverse($cats);
            $where = '';
            if (count($cats) > 0) {
                foreach ($cats as $cat) {
                    $where .= "categories_id[" . $level . "]=" . $cat['id'] . "&";
                    $level++;
                }
                $where = substr($where, 0, (strlen($where) - 1));
                $where .= '&';
            }
            // get all cats to generate multilevel fake url eof
        }
        $link = mslib_fe::typolink($this->conf['products_listing_page_pid'], $where . '&tx_multishop_pi1[page_section]=products_listing');
        //
        $imagePath = '<div class="no_image"></div>';
        if ($row_c['categories_image']) {
            $imagePath = '<a href="' . $link . '" target="_blank"><img src="' . mslib_befe::getImagePath($row_c['categories_image'], 'categories', 'normal') . '" alt="' . htmlspecialchars($row_c['categories_name']) . '" /></a>';
        }
        $tmp_category .= '<div class="image">
       ' . $imagePath . '
    </div>';
        //
        $tmp_category .= '<strong><a href="' . $link . '" target="_blank">' . htmlspecialchars($row_c['categories_name']) . '</a> (ID: ' . $row_c['categories_id'] . ')</strong>';
        //
        if ($this->ROOTADMIN_USER || ($this->ADMIN_USER && $this->CATALOGADMIN_USER)) {
            $tmp_category .= '<div class="admin_menu"><a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=edit_category&cid=' . $row_c['categories_id'] . '&action=edit_category', 1) . '" class="admin_menu_edit btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a> </div>';
        }
        $tmp_category .= '<div class="button_wrapper">
       <button type="button" class="btnTop btn btn-default btn-sm" rel="#categorylisting_' . $row_c['categories_id'] . '"><i class="fa fa-arrow-up"></i> Top</button>
       <button type="button" class="btnOneUp btn btn-default btn-sm" rel="#categorylisting_' . $row_c['categories_id'] . '"><i class="fa fa-arrow-circle-up"></i> Up</button>
       <button type="button" class="btnOneDown btn btn-default btn-sm" rel="#categorylisting_' . $row_c['categories_id'] . '"><i class="fa fa-arrow-circle-down"></i> Down</button>
       <button type="button" class="btnBottom btn btn-default btn-sm" rel="#categorylisting_' . $row_c['categories_id'] . '"><i class="fa fa-arrow-down"></i> Bottom</button>
    </div>';
        $categories_list[] = '<li id="categorylisting_' . $row_c['categories_id'] . '">' . $tmp_category . '</li>';
    }
    if (count($categories_list)) {
        $content .= '<ul class="admin_sort_category_listing">';
        $content .= implode("\n", $categories_list);
        $content .= '</ul>';
        $content .= '
    <script type="text/javascript">
    function AJAXSortCategories() {
		jQuery(".admin_sort_category_listing").sortable("refresh");
		sorted = jQuery(".admin_sort_category_listing").sortable("serialize", "id");
		href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=categories') . '";
		jQuery.ajax({
			type:   "POST",
			url:    href,
			data:   sorted,
			success: function(msg) {
				//do something with the sorted data
			}
		});
		// enable all
		$(".admin_sort_category_listing").children().children().find("button").prop("disabled", false);
		// disable Top and oneTop button for first entry
		$(".admin_sort_category_listing").children(":first").children().find("button.btnTop").prop("disabled", true);
		$(".admin_sort_category_listing").children(":first").children().find("button.btnOneUp").prop("disabled", true);
		// disable Down and oneDown button for last entry
		$(".admin_sort_category_listing").children(":last").children().find("button.btnOneDown").prop("disabled", true);
		$(".admin_sort_category_listing").children(":last").children().find("button.btnBottom").prop("disabled", true);
    }
    jQuery(document).ready(function($) {
		// disable Top and oneTop button for first entry
		$(".admin_sort_category_listing").children(":first").children().find("button.btnTop").prop("disabled", true);
		$(".admin_sort_category_listing").children(":first").children().find("button.btnOneUp").prop("disabled", true);
		// disable Down and oneDown button for last entry
		$(".admin_sort_category_listing").children(":last").children().find("button.btnOneDown").prop("disabled", true);
		$(".admin_sort_category_listing").children(":last").children().find("button.btnBottom").prop("disabled", true);
		//
		jQuery(".admin_sort_category_listing").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				AJAXSortCategories();
			}
		});
		$(document).on("click", ".btnOneUp", function() {
			var current = $($(this).attr("rel"));
			current.prev().before(current);
			AJAXSortCategories();
		});
		$(document).on("click", ".btnOneDown", function() {
			var current = $($(this).attr("rel"));
			current.next().after(current);
			AJAXSortCategories();
		});
		$(document).on("click", ".btnTop", function() {
			var current = $($(this).attr("rel"));
			current.parent().prepend(current);
			AJAXSortCategories();
		});
		$(document).on("click", ".btnBottom", function() {
			var current = $($(this).attr("rel"));
			current.parent().append(current);
			AJAXSortCategories();
		});
    });
    </script>';
    }
}
//} else {
//    $content .= '<p>' . $this->pi_getLL('admin_label_please_select_categories_to_display_products') . '</p>';
//}
$content .= '<script type="text/javascript">
jQuery(document).ready(function($) {
    $("#sort_categories_id").select2();
    $(document).on("change", "#sort_categories_id", function() {
        $("#sort_categories").submit();
    });
    $(document).on("change", "#show_disabled_category", function() {
        $("#sort_categories").submit();
    });
});
</script>';
$content .= '<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></div></div>';
$content = '<div class="panel panel-default">' . mslib_fe::shadowBox($content) . '</div>';
?>
