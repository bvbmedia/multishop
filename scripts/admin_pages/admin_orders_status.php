<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if ($this->get['tx_multishop_pi1']['action']) {
    switch ($this->get['tx_multishop_pi1']['action']) {
        case 'update_default_status':
            if (intval($this->get['tx_multishop_pi1']['orders_status_id'])) {
                $updateArray = array();
                $updateArray['default_status'] = 1;
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id=\'' . $this->get['tx_multishop_pi1']['orders_status_id'] . '\' and page_uid=' . $this->showCatalogFromPage, $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                $updateArray = array();
                $updateArray['default_status'] = 0;
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id <> \'' . $this->get['tx_multishop_pi1']['orders_status_id'] . '\' and page_uid=' . $this->showCatalogFromPage, $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            break;
        case 'delete':
            if (intval($this->get['tx_multishop_pi1']['orders_status_id'])) {
                $updateArray = array();
                $updateArray['deleted'] = 1;
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id=\'' . $this->get['tx_multishop_pi1']['orders_status_id'] . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
            break;
    }
}
if ($this->post) {
    switch ($this->post['tx_multishop_pi1']['action']) {
        case 'update_status':
            if (intval($this->post['tx_multishop_pi1']['orders_status_id'])) {
                $updateArray = array();
                $updateArray['page_uid'] = $this->post['related_shop_pid'];
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status', 'id=\'' . $this->post['tx_multishop_pi1']['orders_status_id'] . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                foreach ($this->post['tx_multishop_pi1']['order_status_name'] as $key => $value) {
                    $updateArray = array();
                    $updateArray['name'] = $value;
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_status_description', 'orders_status_id=\'' . $this->post['tx_multishop_pi1']['orders_status_id'] . '\' and language_id = ' . $key, $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
            }
            break;
        default:
            // add new order status eof
            if (count($this->post['tx_multishop_pi1']['order_status_name'])) {
                if ($this->post['tx_multishop_pi1']['order_status_name'][0]) {
                    $insertArray = array();
                    $insertArray['page_uid'] = $this->post['related_shop_pid'];
                    $insertArray['deleted'] = 0;
                    $insertArray['crdate'] = time();
                    $insertArray['sort_order'] = time();
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status', $insertArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    $id = $GLOBALS['TYPO3_DB']->sql_insert_id();
                    $this->post['tx_multishop_pi1']['orders_status_id'] = $id;
                    if ($id) {
                        foreach ($this->post['tx_multishop_pi1']['order_status_name'] as $key => $value) {
                            $insertArray = array();
                            $insertArray['name'] = $value;
                            $insertArray['language_id'] = $key;
                            $insertArray['orders_status_id'] = $id;
                            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status_description', $insertArray);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                    }
                }
            }
            // add new order status eof
            break;
    }
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusUpdatePostProc'])) {
        $params = array();
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusUpdatePostProc'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
}
$active_shop = mslib_fe::getActiveShop();
if ($this->get['tx_multishop_pi1']['action'] == 'edit') {
    $str = "SELECT o.id, o.default_status, od.name, od.language_id from tx_multishop_orders_status o, tx_multishop_orders_status_description od where (o.page_uid='0' or o.page_uid='" . $this->shop_pid . "') and o.deleted=0 and o.id=od.orders_status_id and od.orders_status_id = " . $this->get['tx_multishop_pi1']['orders_status_id'] . " order by o.id desc";
    $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
    $lngstatus = array();
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $lngstatus[$row['language_id']] = $row;
    }
}
$content .= '<div class="panel-heading"><h3>' . $this->pi_getLL('order_status') . '</h3></div>';
$content .= '
<div class="panel-body"><form class="form-horizontal" action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page']) . '" method="post">
<div class="panel panel-default"><div class="panel-heading"><h3>' . $this->pi_getLL('add_order_status') . '</h3></div><div class="panel-body">
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
		<div class="panel panel-default order_status_input">
			<div class="panel-heading panel-heading-toggle' . (($language['uid'] === 0 || !empty($lngstatus[$language['uid']]['name'])) ? '' : ' collapsed') . '" data-toggle="collapse" data-target="#msEditOrderStatusInputName_' . $language['uid'] . '">
				<h3 class="panel-title">
					<a role="button" data-toggle="collapse" href="#msEditOrderStatusInputName_' . $language['uid'] . '"><i class="fa fa-file-text-o"></i> ' . $language['title'] . '</a>
				</h3>
			</div>
			<div id="msEditOrderStatusInputName_' . $language['uid'] . '" class="panel-collapse collapse' . ((($language['uid'] === 0 || !$this->ms['MODULES']['FOLD_FOREIGN_LANGUAGE_INPUT_FIELDS']) || !empty($lngstatus[$language['uid']]['name'])) ? ' in' : '') . '">
				<div class="panel-body">
					<div class="form-group">
						<label for="products_name" class="control-label col-md-2">' . $this->pi_getLL('admin_name') . '</label>
						<div class="col-md-10">
						<input type="text" class="text form-control" name="tx_multishop_pi1[order_status_name][' . $language['uid'] . ']" id="order_status_name_' . $language['uid'] . '" value="' . htmlspecialchars($lngstatus[$language['uid']]['name']) . '">
						</div>
					</div>
				</div>
			</div>
		</div>
		';
}
$content .= $tmpcontent;
// hook
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusInputField'])) {
    $params = array(
            'content' => &$content
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusInputField'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// hook oef
if (count($active_shop) > 1) {
    if ($this->get['tx_multishop_pi1']['action'] == 'edit') {
        $str_status = "SELECT o.page_uid from tx_multishop_orders_status o where o.id = " . $this->get['tx_multishop_pi1']['orders_status_id'];
        $qry_status = $GLOBALS['TYPO3_DB']->sql_query($str_status);
        $row_status = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_status);
        $content .= '<div class="form-group relate_to_shop_pid_input">
			<label for="related_shop_pid" class="control-label col-md-2">' . $this->pi_getLL('relate_order_status_to_shop', 'Relate this order status to') . '</label>
			<div class="col-md-10">
			<div class="radio radio-success radio-inline"><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0"' . ($row_status['page_uid'] == '0' ? ' checked="checked"' : '') . ' /><label>' . $this->pi_getLL('relate_order_status_to_all_shop', 'All shop') . '</label></div>';
        foreach ($active_shop as $pageinfo) {
            $pageTitle = $pageinfo['title'];
            if ($pageinfo['nav_title']) {
                $pageTitle = $pageinfo['nav_title'];
            }
            $content .= '<div class="radio radio-success radio-inline"><input name="related_shop_pid" id="related_shop_pid" type="radio" value="' . $pageinfo['uid'] . '"' . ($row_status['page_uid'] == $pageinfo['uid'] ? ' checked="checked"' : '') . ' /><label>' . $pageTitle . '</label></div>';
        }
        $content .= '</div></div>';
    } else {
        $content .= '<div class="form-group relate_to_shop_pid_input">
			<label for="related_shop_pid" class="control-label col-md-2">' . $this->pi_getLL('relate_order_status_to_shop', 'Relate this order status to') . '</label>
			<div class="col-md-10">
			<div class="radio radio-success radio-inline"><input name="related_shop_pid" id="related_shop_pid" type="radio" value="0" checked="checked" /><label>' . $this->pi_getLL('relate_order_status_to_all_shop', 'All shop') . '</label></div>';
        foreach ($active_shop as $pageinfo) {
            $pageTitle = $pageinfo['title'];
            if ($pageinfo['nav_title']) {
                $pageTitle = $pageinfo['nav_title'];
            }
            $content .= '<div class="radio radio-success radio-inline"><input name="related_shop_pid" id="related_shop_pid" type="radio" value="' . $pageinfo['uid'] . '" /><label>' . $pageTitle . '</label></div>';
        }
        $content .= '</div></div>';
    }
} else {
    $content .= '<input type="hidden" name="related_shop_pid" value="' . $this->showCatalogFromPage . '">';
}
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusInputFieldPostProc'])) {
    $params = array(
            'content' => &$content
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusInputFieldPostProc'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
$content .= '
	<div class="form-group">
		<div class="col-md-10 col-md-offset-2">
		<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> ' . $this->pi_getLL('save') . '</button>
		</div>
	</div>
</div>
</div>';
if ($this->get['tx_multishop_pi1']['action'] == 'edit') {
    $content .= '<input type="hidden" name="tx_multishop_pi1[orders_status_id]" value="' . $this->get['tx_multishop_pi1']['orders_status_id'] . '" />';
    $content .= '<input type="hidden" name="tx_multishop_pi1[action]" value="update_status" />';
}
$content .= '</form>';
$str = "SELECT o.*, od.name from tx_multishop_orders_status o, tx_multishop_orders_status_description od where (o.page_uid='0' or o.page_uid='" . $this->showCatalogFromPage . "') and o.deleted=0 and o.id=od.orders_status_id and od.language_id='0' order by o.sort_order asc";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$zones = array();
while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
    $statusses[] = $row;
}
if (count($statusses)) {
    $content .= '<table class="table table-striped table-bordered msadmin_border">';
    // table headers
    $table_headers = array();
    $table_headers['status_id'] = '<th class="cellID">' . $this->pi_getLL('id') . '</th>';
    $table_headers['status_name'] = '<th class="cellName">' . $this->pi_getLL('name') . '</th>';
    if (count($active_shop) > 1) {
        $table_headers['shop'] = '<th class="cellStatus">' . $this->pi_getLL('shop', 'Shop') . '</th>';
    }
    $table_headers['is_default'] = '<th class="cellStatus">' . $this->pi_getLL('default', 'Default') . '</th>';
    $table_headers['action'] = '<th class="cellAction">' . $this->pi_getLL('action') . '</th>';
    $table_headers['sort_order'] = '<th class="cellAction">&nbsp;</th>';
    // hook
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusListingTableHeaders'])) {
        $params = array(
                'table_headers' => &$table_headers
        );
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusListingTableHeaders'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
    }
    $content .= '<thead>' . implode("\n", $table_headers) . '</thead>
    <tbody class="sortable_content">';
    // hook oef
    foreach ($statusses as $status) {
        if (!$tr_type or $tr_type == 'even') {
            $tr_type = 'odd';
        } else {
            $tr_type = 'even';
        }
        $status_id = '<td class="cellID">
			' . $status['id'] . '
		</td>
		';
        $status_name = '<td class="cellName">' . $status['name'] . '</td>';
        if (count($active_shop) > 1) {
            $shop_title = '<td class="cellStatus">';
            if ($status['page_uid'] > 0) {
                $shop_title .= '<strong>' . mslib_fe::getShopNameByPageUid($status['page_uid']) . '</strong>';
            } else {
                $shop_title .= '<strong>All</strong>';
            }
            $shop_title .= '</td>';
        }
        $status_html = '
		<td class="cellStatus">';
        if (!$status['default_status']) {
            $status_html .= '';
            $status_html .= '<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[action]=update_default_status&tx_multishop_pi1[orders_status_id]=' . $status['id'] . '&tx_multishop_pi1[status]=1') . '"><span class="admin_status_green disabled" alt="' . $this->pi_getLL('enabled') . '"></span></a>';
        } else {
            $status_html .= '<span class="admin_status_green" alt="' . $this->pi_getLL('enable') . '"></span>';
            $status_html .= '';
        }
        $status_html .= '
		</td>';
        $rows_content = array();
        $rows_content['status_id'] = $status_id;
        $rows_content['status_name'] = $status_name;
        if (count($active_shop) > 1) {
            $rows_content['shop'] = $shop_title;
        }
        $rows_content['is_default'] = $status_html;
        $rows_content['action'] = '<td class="cellAction">
			<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[orders_status_id]=' . $status['id'] . '&tx_multishop_pi1[action]=edit') . '" class="btn btn-primary btn-sm admin_menu_edit" alt="' . $this->pi_getLL('edit') . '"><i class="fa fa-pencil"></i></a>
			<a href="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=' . $this->ms['page'] . '&tx_multishop_pi1[orders_status_id]=' . $status['id'] . '&tx_multishop_pi1[action]=delete') . '" onclick="return confirm(\'' . $this->pi_getLL('are_you_sure') . '?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="' . $this->pi_getLL('delete') . '"><i class="fa fa-trash-o"></i></a>
		</td>';
        $rows_content['sort_order'] = '<td class="cellAction sortOrder" style="padding:20px; cursor: move">
			<i class="fa fa-bars"></i>
		</td>';
        // hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusListingTableData'])) {
            $params = array(
                    'rows_content' => &$rows_content,
                    'status' => $status
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders_status.php']['adminOrdersStatusListingTableData'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        $content .= '<tr class="' . $tr_type . '" id="row_sortable_'.$status['id'] .'">' . implode("\n", $rows_content) . '</tr>';
    }
    $content .= '</tbody></table>';
}
$content .= '<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></div></div>';
$content = '<div class="panel panel-default">' . mslib_fe::shadowBox($content) . '</div>';
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("tbody.sortable_content").sortable({
		cursor:"move",
		handle:".sortOrder",
		update: function(e, ui) {
			href = "' . mslib_fe::typolink($ref->shop_pid.',2002', '&tx_multishop_pi1[page_section]=sort_orders_status') . '";
			jQuery(this).sortable("refresh");
			sorted = jQuery(this).sortable("serialize", "id");
			jQuery.ajax({
				type:"POST",
				url:href,
				data:sorted,
				success: function(msg) {
					//do something with the sorted data
				}
			});
		}
	});
});
</script>';
?>
