<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$content .= '<div class="main-heading"><h1>' . $this->pi_getLL('admin_delete_product') . '</h1></div>';
if (is_numeric($_REQUEST['pid'])) {
    if ($_REQUEST['confirm']) {
        if (isset($this->post['SubmitDeleteFromSingleCat'])) {
            mslib_befe::deleteProduct($_REQUEST['pid'], $_REQUEST['cid']);
        } else if (isset($this->post['SubmitDeleteFromAllCat'])) {
            mslib_befe::deleteProduct($_REQUEST['pid'], $_REQUEST['cid'], false, true);
        }
        if ($this->post['tx_multishop_pi1']['referrer']) {
            header("Location: " . $this->post['tx_multishop_pi1']['referrer']);
            exit();
        } else {
            header("Location: " . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_cms', 1));
            exit();
        }
    } else {
        $subpartArray['###VALUE_REFERRER###'] = '';
        if ($this->post['tx_multishop_pi1']['referrer']) {
            $subpartArray['###VALUE_REFERRER###'] = $this->post['tx_multishop_pi1']['referrer'];
        } else {
            $subpartArray['###VALUE_REFERRER###'] = $_SERVER['HTTP_REFERER'];
        }
        $categories_id = 0;
        if (isset($this->get['cid']) && is_numeric($this->get['cid']) && $this->get['cid'] > 0) {
            $categories_id = $this->get['cid'];
        } else {
            // use default cat if setting is on and default path is set
            if ($this->ms['MODULES']['ENABLE_DEFAULT_CRUMPATH'] > 0) {
                $product_path = mslib_befe::getRecord($this->get['pid'], 'tx_multishop_products_to_categories', 'products_id', array('is_deepest=1 and default_path=1'));
                if (is_array($product_path) && count($product_path)) {
                    $categories_id = $product_path['node_id'];
                }
            }
        }
        // get all cats relation
        $have_multiple_cats = false;
        $product_paths = mslib_befe::getRecords($this->get['pid'], 'tx_multishop_products_to_categories', 'products_id', array('is_deepest=1 and page_uid=\'' . $this->showCatalogFromPage . '\''), '', 'categories_id asc', '', array('categories_id'));
        if (is_array($product_paths)) {
            if (count($product_paths) > 1) {
                $have_multiple_cats = true;
            }
            if (!$categories_id) {
                $categories_id = $product_paths[0]['categories_id'];
            }
        }
        $categories_name = '';
        if ($categories_id > 0) {
            $categories_name = mslib_fe::getCategoryName($categories_id);
        }
        $str = "SELECT * from tx_multishop_products_description where products_id='" . $_REQUEST['pid'] . "'";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
        if (is_numeric($row['products_id'])) {
            $content .= '<form class="admin_product_edit" name="admin_product_edit_' . $_REQUEST['pid'] . '" id="admin_product_edit_' . $_REQUEST['pid'] . '" method="post" action="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $_REQUEST['action'] . '&pid=' . $_REQUEST['pid'], 1) . '">
			<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="' . $subpartArray['###VALUE_REFERRER###'] . '" >
			';
            $content .= '
            <div class="save_block">
                <input name="cid" type="hidden" value="' . $categories_id . '" />
                <input name="pid" type="hidden" value="' . $_REQUEST['pid'] . '" />
                <input name="confirm" type="hidden" value="1" />
                <input name="action" type="hidden" value="delete_product" />
                <a href="' . $subpartArray['###VALUE_REFERRER###'] . '" class="btn btn-danger">' . $this->pi_getLL('cancel') . '</a> ';
            $content .= '<input name="SubmitDeleteFromSingleCat" type="submit" value="' . sprintf($this->pi_getLL('delete_product_x_from_current_category_x'), htmlspecialchars($row['products_name']), htmlspecialchars($categories_name)) . '" class="btn btn-success" /> ';
            if ($have_multiple_cats) {
                $content .= '<input name="SubmitDeleteFromAllCat" type="submit" value="' . sprintf($this->pi_getLL('delete_product_x_from_all_categories'), htmlspecialchars($row['products_name'])) . '" class="btn btn-warning" />';
            }
            $content .= '</div>';
            $content .= '</form>';
        }
    }
}
?>
