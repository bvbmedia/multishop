<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->get['tx_multishop_pi1']['action']) {
    switch ($this->get['tx_multishop_pi1']['action']) {
        case 'update_default_unit':
            if (intval($this->get['tx_multishop_pi1']['orders_unit_id'])) {
                $updateArray = array();
                $updateArray['is_default'] = 0;
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_order_units', 'page_uid=' . $this->showCatalogFromPage, $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);

                $updateArray = array();
                $updateArray['is_default'] = 1;
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_order_units', 'id=\'' . $this->get['tx_multishop_pi1']['orders_unit_id'] . '\' and page_uid=' . $this->showCatalogFromPage, $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            break;
        case 'delete':
            if (intval($this->get['tx_multishop_pi1']['order_unit_id'])) {
                $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_order_units', 'id=\'' . $this->get['tx_multishop_pi1']['order_unit_id'] . '\'');
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                $query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_order_units_description', 'id=\'' . $this->get['tx_multishop_pi1']['order_unit_id'] . '\'');
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_order_units'));
                exit();
            }
            break;
    }
}
if ($this->post) {
    if (isset($this->post['tx_multishop_pi1']['order_unit_id'])) {
        $order_unit_id = (int)$this->post['tx_multishop_pi1']['order_unit_id'];
        if ($order_unit_id) {
            $updateArray = array();
            $updateArray['code'] = $this->post['tx_multishop_pi1']['order_unit_code'];
            $updateArray['page_uid'] = $this->post['tx_multishop_pi1']['related_shop_pid'];
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_order_units', 'id=\'' . $order_unit_id . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            // order unit name
            foreach ($this->post['tx_multishop_pi1']['order_unit_name'] as $key => $value) {
                $check_record = mslib_befe::getRecord($order_unit_id, 'tx_multishop_order_units_description', 'order_unit_id', array('language_id=' . $key));
                if (is_array($check_record)) {
                    $updateArray = array();
                    $updateArray['name'] = $value;
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_order_units_description', 'order_unit_id=\'' . $order_unit_id . '\' and language_id = ' . $key, $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                } else {
                    $insertArray = array();
                    $insertArray['name'] = $value;
                    $insertArray['language_id'] = $key;
                    $insertArray['order_unit_id'] = $order_unit_id;
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units_description', $insertArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
            }
            header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_order_units'));
            exit();
        }
    } else {
        // add new order status eof
        if (count($this->post['tx_multishop_pi1']['order_unit_name'])) {
            if ($this->post['tx_multishop_pi1']['order_unit_name'][0]) {
                $insertArray = array();
                $insertArray['code'] = $this->post['tx_multishop_pi1']['order_unit_code'];
                $insertArray['page_uid'] = $this->post['tx_multishop_pi1']['related_shop_pid'];
                $insertArray['crdate'] = time();
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units', $insertArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                $id = $GLOBALS['TYPO3_DB']->sql_insert_id();
                if ($id) {
                    foreach ($this->post['tx_multishop_pi1']['order_unit_name'] as $key => $value) {
                        $insertArray = array();
                        $insertArray['name'] = $value;
                        $insertArray['language_id'] = $key;
                        $insertArray['order_unit_id'] = $id;
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units_description', $insertArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
            }
            header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_order_units'));
            exit();
        }
        // add new order status eof
    }
}
$active_shop = mslib_fe::getActiveShop();
if ($this->get['tx_multishop_pi1']['action'] == 'edit') {
    $str = "SELECT o.id, o.page_uid, o.code, od.name, od.language_id from tx_multishop_order_units o, tx_multishop_order_units_description od where o.id=od.order_unit_id and od.order_unit_id = " . $this->get['tx_multishop_pi1']['order_unit_id'] . " order by o.id desc";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $lngstatus = array();
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $edit_page_uid = $row['page_uid'];
        $lngstatus[$row['language_id']] = $row;
    }
}
$content .= '<div class="panel-heading"><h3>' . $this->pi_getLL('admin_order_units') . '</h3></div>';
$content .= '<div class="panel-body">
<form class="form-horizontal" action="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $this->ms['page']) . '" method="post">
<div class="panel panel-default"><div class="panel-heading"><h3>' . $this->pi_getLL('add') . '</h3></div>
<div class="panel-body">
<div class="form-group">
    <label class="control-label col-md-2" for="order_unit_code">' . $this->pi_getLL('code') . '</label>
    <div class="col-md-10">
    <input type="text" class="form-control text" name="tx_multishop_pi1[order_unit_code]" id="order_unit_code" value="' . htmlspecialchars($lngstatus[0]['code']) . '">
    </div>
</div>
';
foreach ($this->languages as $key => $language) {
    $flag_path = '';
    if ($language['flag']) {
        $flag_path = 'sysext/cms/tslib/media/flags/flag_' . $language['flag'] . '.gif';
    }
    $language_lable = '';
    if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3 . $flag_path)) {
        $language_lable .= '<img src="' . $this->FULL_HTTP_URL_TYPO3 . $flag_path . '"> ';
    }
    $language_lable .= '' . $language['title'];
    $tmpcontent .= '
		<div class="panel panel-default">
			<div class="panel-heading panel-heading-toggle' . (($language['uid'] === 0 || !empty($lngstatus[$language['uid']]['name'])) ? '' : ' collapsed') . '" data-toggle="collapse" data-target="#msEditOrderUnitInputName_' . $language['uid'] . '">
				<h3 class="panel-title">
					<a role="button" data-toggle="collapse" href="#msEditOrderUnitInputName_' . $language['uid'] . '"><i class="fa fa-file-text-o"></i> ' . $language['title'] . '</a>
				</h3>
			</div>
			<div id="msEditOrderUnitInputName_' . $language['uid'] . '" class="panel-collapse collapse' . ((($language['uid'] === 0 || !$this->ms['MODULES']['FOLD_FOREIGN_LANGUAGE_INPUT_FIELDS']) || !empty($lngstatus[$language['uid']]['name'])) ? ' in' : '') . '">
				<div class="panel-body">
					<div class="form-group">
						<label class="control-label col-md-2" for="order_unit_name_' . $language['uid'] . '">' . $this->pi_getLL('admin_name') . '</label>
						<div class="col-md-10">
						<input type="text" class="form-control text" name="tx_multishop_pi1[order_unit_name][' . $language['uid'] . ']" id="order_unit_name_' . $language['uid'] . '" value="' . htmlspecialchars($lngstatus[$language['uid']]['name']) . '">
						</div>
					</div>
				</div>
			</div>
		</div>
	';
}
if (count($active_shop) > 1) {
    $tmpcontent .= '<div class="form-group">
			<label for="related_shop_pid" class="control-label col-md-2">' . $this->pi_getLL('relate_shipping_to_shop', 'Relate this method to') . '</label>
			<div class="col-md-10">
			<div class="radio radio-success radio-inline"><input name="tx_multishop_pi1[related_shop_pid]" id="related_shop_pid" type="radio" value="0"' . (($edit_page_uid == 0) ? ' checked="checked"' : '') . ' /><label for="related_shop_pid">' . $this->pi_getLL('relate_payment_to_all_shop', 'All shop') . '</label></div>';
    foreach ($active_shop as $pageinfo) {
        $pageTitle = $pageinfo['title'];
        if ($pageinfo['nav_title']) {
            $pageTitle = $pageinfo['nav_title'];
        }
        $tmpcontent .= '<div class="radio radio-success radio-inline"><input name="tx_multishop_pi1[related_shop_pid]" id="related_shop_pid' . $pageinfo['uid'] . '" type="radio" value="' . $pageinfo['uid'] . '"' . (($edit_page_uid == $pageinfo['uid']) ? ' checked="checked"' : '') . ' /><label for="related_shop_pid' . $pageinfo['uid'] . '">' . $pageTitle . '</label></div>';
    }
    $tmpcontent .= '</div></div>';
} else {
    $shop_pid = $row['page_uid'];
    if (!$shop_pid) {
        $shop_pid = $this->showCatalogFromPage;
    }
    $tmpcontent .= '<input type="hidden" name="tx_multishop_pi1[related_shop_pid]" value="' . $shop_pid . '">';
}
if ($this->get['tx_multishop_pi1']['action'] == 'edit') {
    $tmpcontent .= '<input type="hidden" class="text" name="tx_multishop_pi1[order_unit_id]" value="' . $this->get['tx_multishop_pi1']['order_unit_id'] . '">';
}
$content .= $tmpcontent . '
<div class="form-group">
	<div class="col-md-10 col-md-offset-2">
		<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> ' . $this->pi_getLL('save') . '</button>
	</div>
</div>
</div>
</div>
</form>
';
$str = "SELECT o.id, o.is_default, o.page_uid, o.code, od.name from tx_multishop_order_units o, tx_multishop_order_units_description od where o.id=od.order_unit_id and od.language_id='0' order by o.id desc";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$zones = array();
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $order_units[] = $row;
}
if (count($order_units)) {
    $content .= '<table class="table table-striped table-bordered msadmin_border">
		<thead><tr><th class="cellID">' . $this->pi_getLL('id') . '</th>
		';
    if (count($active_shop) > 1) {
        $content .= '
		<th class="cellCode">' . $this->pi_getLL('shop', 'Shop') . '</th>
		';
    }
    $content .= '
		<th class="cellCode">' . $this->pi_getLL('code') . '</th>
		<th class="cellName">' . $this->pi_getLL('name') . '</th>
		<th class="cellStatus">' . $this->pi_getLL('default', 'Default') . '</th>
		<th class="cellAction">' . $this->pi_getLL('action') . '</th></tr></thead>';
    foreach ($order_units as $status) {
        if (!$tr_type or $tr_type == 'even') {
            $tr_type = 'odd';
        } else {
            $tr_type = 'even';
        }
        $content .= '<tr class="' . $tr_type . '">
		<td class="cellID">
			' . $status['id'] . '
		</td>
		';
        if (count($active_shop) > 1) {
            if ($row['page_uid'] > 0) {
                $content .= '
                <td class="cellCode">' . mslib_fe::getShopNameByPageUid($status['page_uid']) . '</td>
                ';
            } else {
                $content .= '
                <td class="cellCode">All</td>
                ';
            }
        }
        $content .= '
		<td class="cellCode">' . $status['code'] . '</td>
		<td class="cellName">' . $status['name'] . '</td>';
        $content .= '<td class="cellStatus">';
        $status_html = '';
        if (!$status['is_default']) {
            $status_html .= '';
            $status_html .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[action]=update_default_unit&tx_multishop_pi1[orders_unit_id]=' . $status['id'] . '&tx_multishop_pi1[status]=1') . '"><span class="admin_status_green disabled" alt="' . $this->pi_getLL('enabled') . '"></span></a>';
        } else {
            $status_html .= '<span class="admin_status_green" alt="' . $this->pi_getLL('enable') . '"></span>';
            $status_html .= '';
        }
        $content .= $status_html . '</td>';
        $content .= '<td  class="cellAction msAdminProductsSearchCellActionIcons">
			<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[order_unit_id]=' . $status['id'] . '&tx_multishop_pi1[action]=edit') . '" class="btn btn-primary btn-sm admin_menu_edit" alt="' . $this->pi_getLL('edit') . '"><i class="fa fa-pencil"></i></a>
			<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[order_unit_id]=' . $status['id'] . '&tx_multishop_pi1[action]=delete') . '" onclick="return confirm(\'' . $this->pi_getLL('are_you_sure') . '?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="' . $this->pi_getLL('delete') . '"><i class="fa fa-trash-o"></i></a>
		</td>';
        $content .= '</tr>';
    }
    $content .= '</table>';
}
$content .= '<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></div></div>';
$content = '<div class="panel panel-default">' . mslib_fe::shadowBox($content) . '</div>';
?>
