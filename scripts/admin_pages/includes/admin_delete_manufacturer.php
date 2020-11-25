<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$content .= '<div class="main-heading"><h1>' . $this->pi_getLL('delete_manufacturer') . '</h1></div>';
if (is_numeric($_REQUEST['manufacturers_id'])) {
    if ($_REQUEST['confirm']) {
        mslib_befe::deleteManufacturer($_REQUEST['manufacturers_id']);
        if ($this->post['tx_multishop_pi1']['referrer']) {
            header("Location: " . $this->post['tx_multishop_pi1']['referrer']);
            exit();
        } else {
            header("Location: " . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=admin_categories', 1));
            exit();
        }
    } else {
        $subpartArray['###VALUE_REFERRER###'] = '';
        if ($this->post['tx_multishop_pi1']['referrer']) {
            $subpartArray['###VALUE_REFERRER###'] = $this->post['tx_multishop_pi1']['referrer'];
        } else {
            $subpartArray['###VALUE_REFERRER###'] = $_SERVER['HTTP_REFERER'];
        }
        $str = "SELECT * from tx_multishop_manufacturers where manufacturers_id='" . $_REQUEST['manufacturers_id'] . "'";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
        if (is_numeric($row['manufacturers_id'])) {
            $content .= '<form class="admin_categories_edit" name="admin_categories_edit_' . $_REQUEST['manufacturers_id'] . '" id="admin_categories_edit_' . $_REQUEST['manufacturers_id'] . '" method="post" action="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=' . $_REQUEST['action'] . '&manufacturers_id=' . $_REQUEST['manufacturers_id'], 1) . '">
			<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="' . $subpartArray['###VALUE_REFERRER###'] . '" >
			';
            $content .= '
	<div class="save_block">
		<input name="manufacturers_id" type="hidden" value="' . $_REQUEST['manufacturers_id'] . '" />
		<input name="confirm" type="hidden" value="1" />
		<input name="action" type="hidden" value="delete_manufacturer" />
		<a href="' . $subpartArray['###VALUE_REFERRER###'] . '" class="btn btn-danger">' . $this->pi_getLL('cancel') . '</a>
		<input name="Submit" type="submit" value="' . $this->pi_getLL('delete') . ': ' . htmlspecialchars($row['manufacturers_name']) . '" class="btn btn-success" />
	</div>
';
            $content .= '</form>';
        }
    }
}
