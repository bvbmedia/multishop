<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
mslib_befe::loadLanguages();
if (isset($this->post['new_options_groups_name']) && !empty($this->post['new_options_groups_name'])) {
    $sql_chk = "select attributes_options_groups_id from tx_multishop_attributes_options_groups where attributes_options_groups_name = '" . addslashes($this->post['new_options_groups_name']) . "' and language_id = '" . $this->sys_language_uid . "' order by sort_order";
    $qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
    if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)) {
        $sql_chk = "select attributes_options_groups_id from tx_multishop_attributes_options_groups order by attributes_options_groups_id desc limit 1";
        $qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
        $rs_chk = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
        $max_optid = $rs_chk['attributes_options_groups_id'] + 1;
        $sql_ins = "insert into tx_multishop_attributes_options_groups (attributes_options_groups_id, language_id, attributes_options_groups_name, sort_order) values ('" . $max_optid . "', '0', '" . addslashes($this->post['new_options_groups_name']) . "', '" . $max_optid . "')";
        $GLOBALS['TYPO3_DB']->sql_query($sql_ins);
    }
    // hook for adding new items to details fieldset
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_attributes_options_groups.php']['adminAttributeGroupsSaveHook'])) {
        // hook
        $params = array();
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_attributes_options_groups.php']['adminAttributeGroupsSaveHook'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
        // hook oef
    }
    header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups'));
    exit();
}
if (is_array($this->post['option_groups_names']) and count($this->post['option_groups_names'])) {
    foreach ($this->post['option_groups_names'] as $attributes_options_groups_id => $array) {
        foreach ($array as $language_id => $value) {
            if (is_numeric($attributes_options_groups_id)) {
                $updateArray = array();
                $updateArray['language_id'] = $language_id;
                $updateArray['attributes_options_groups_id'] = $attributes_options_groups_id;
                $updateArray['attributes_options_groups_name'] = $value;
                $str = "select 1 from tx_multishop_attributes_options_groups where attributes_options_groups_id='" . $attributes_options_groups_id . "' and language_id='" . $language_id . "'";
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_attributes_options_groups', 'attributes_options_groups_id=\'' . $attributes_options_groups_id . '\' and language_id=\'' . $language_id . '\'', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                } else {
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_attributes_options_groups', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
            }
        }
    }
    // hook for adding new items to details fieldset
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_attributes_options_groups.php']['adminAttributeGroupsSaveHook'])) {
        // hook
        $params = array();
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_attributes_options_groups.php']['adminAttributeGroupsSaveHook'] as $funcRef) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
        }
        // hook oef
    }
    header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups'));
    exit();
}
$content .= '
<div class="panel-heading"><h3>' . $this->pi_getLL('admin_attributes_options_groups') . '</h3></div>
<div class="panel-body">
<form action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups') . '" method="post" name="new_attributes_options_groups" id="add_new_options_groups" class="form-horizontal">
	<div class="form-group new_options_groups_name_input">
		<label for="new_options_groups_name" class="control-label col-md-2">' . $this->pi_getLL('name') . ':</label>
		<div class="col-md-10">
			<div class="input-group">
				<input type="text" name="new_options_groups_name" id="new_options_groups_name" class="form-control" />
				<span class="input-group-btn">
				<input class="btn btn-success" type="submit" name="add_new_options_groups" value="' . $this->pi_getLL('add') . '">
				</span>
			</div>
		</div>
	</div>
</form>
<hr>';
$str = "select * from tx_multishop_attributes_options_groups where language_id='0' order by sort_order";
$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
$rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
if ($rows) {
    $content .= '
	<form action="' . mslib_fe::typolink($this->shop_pid . ',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups') . '" method="post" name="admin_attributes_options_groups" class="form-horizontal">
	<div class="attribute_options_groups_sortable">';
    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
        $content .= '<div class="form-group" id="options_groups_' . $row['attributes_options_groups_id'] . '">
		<label class="option_group_id control-label col-md-2">' . $this->pi_getLL('admin_label_option_group_name') . ':</label>
		<div class="col-md-10 form-inline">
		<input name="option_groups_names[' . $row['attributes_options_groups_id'] . '][0]" type="text" class="form-control" value="' . htmlspecialchars($row['attributes_options_groups_name']) . '" />';
        foreach ($this->languages as $key => $language) {
            if ($key > 0) {
                $str3 = "select attributes_options_groups_name from tx_multishop_attributes_options_groups where attributes_options_groups_id='" . $row['attributes_options_groups_id'] . "' and language_id='" . $key . "'";
                $qry3 = $GLOBALS['TYPO3_DB']->sql_query($str3);
                while (($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3)) != false) {
                    if ($row3['attributes_options_groups_name']) {
                        $value = htmlspecialchars($row3['attributes_options_groups_name']);
                    }
                }
                $content .= ' <p class="form-control-static">' . $this->languages[$key]['title'] . '</p> <input name="option_groups_names[' . $row['attributes_options_groups_id'] . '][' . $key . ']" type="text" class="form-control" value="' . $value . '" /> ';
            }
        }
        $content .= '<a href="#" class="btn btn-danger delete_options admin_menu_remove" rel="' . $row['attributes_options_groups_id'] . '"><i class="fa fa-trash-o"></i> ' . $this->pi_getLL('delete') . '</a>';
        $content .= '</div></div>';
    }
    $content .= '</div>
	<div class="form-group">
		<div class="col-md-10 col-md-offset-2">
			<input name="Submit" type="submit" value="' . $this->pi_getLL('save') . '" class="btn btn-success" />
		</div>
	</div>
	</form>';
    // now load the sortables jQuery code
    $content .= '<script type="text/javascript">
		var result 	= jQuery(".attribute_options_groups_sortable").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=update_attributes_options_groups_sortable&tx_multishop_pi1[type]=options_groups') . '";
				jQuery(this).sortable("refresh");
				sorted = jQuery(this).sortable("serialize","id");
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
		jQuery(document).ready(function($) {
			$(document).on("click", ".delete_options", function(e) {
				if (confirm(\'' . htmlspecialchars($this->pi_getLL('are_you_sure')) . '?\')) {
					e.preventDefault();
					var li_obj=$(this).parent().parent();
					var group_id=$(this).attr("rel");
					href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=delete_options_group') . '";
					jQuery.ajax({
						type: "POST",
						url: href,
						dataType: "json",
						data: "tx_multishop_pi1[group_id]=" + group_id,
						success: function(e) {
							if (e.result=="OK") {
								$(li_obj).remove();
							}
						}
					});
				}
			});
		});
	  </script>';
} else {
    $content .= '<h1>' . $this->pi_getLL('admin_label_no_attributes_options_groups_defined') . '</h1>';
    $content .= $this->pi_getLL('admin_label_you_can_add_attributes_options_groups_below');
}
$content .= '<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> ' . $this->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></div></div>';
$content = '<div class="panel panel-default">' . mslib_fe::shadowBox($content) . '</div>';
