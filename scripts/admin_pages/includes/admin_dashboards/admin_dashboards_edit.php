<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$referrer='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$referrer=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$referrer=$_SERVER['HTTP_REFERER'];
}
if ($this->post) {
	$erno=array();
	if (is_numeric($this->post['tx_multishop_pi1']['dashboard_id'])) {
		$this->editMode=1;
	}
	if (!$this->editMode) {
		// Insert DB validation
	} else {
		// Edit DB validation
	}
	// Both validation
	if (!$this->post['tx_multishop_pi1']['header_title']) {
		$erno[]='country cannot be empty';
	}
	if (!count($erno)) {
		// Create/Update
		$updateArray=array();
		$cols=array();
		$cols[]='header_title';
        $cols[]='dashboard_layout';
        $cols[]='status';
		if (!$this->editMode) {
            // Insert mode
			$updateArray['crdate']=time();
            $updateArray['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
		} else {
            $updateArray['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
		}
		// Quick way to map other required cols
		if (!is_numeric($this->post['tx_multishop_pi1']['dashboard_id'])) {
			$this->post['tx_multishop_pi1']['dashboard_id']=0;
		}
		foreach ($cols as $col) {
			if (isset($this->post['tx_multishop_pi1'][$col])) {
				$updateArray[$col]=$this->post['tx_multishop_pi1'][$col];
			}
		}
		if ($this->editMode) {
			// Update DB
			// Hook to let other plugins further manipulate the option values display
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['updateDashboardsPreProc'])) {
				$params=array(
					'updateArray'=>&$updateArray,
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['updateDashboardsPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_dashboard', 'id='.addslashes($this->post['tx_multishop_pi1']['dashboard_id']), $updateArray);
			if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
				$erno[]='Failed to update.<br/><br/>SQL error:<br/>'.$GLOBALS['TYPO3_DB']->sql_error().'<br/><br/>Query:<br/>'.$query.'.<br/>';
			}
			// Hook to let other plugins further manipulate the option values display
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['updateDashboardsPostProc'])) {
				$params=array(
					'updateArray'=>&$updateArray,
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['updateDashboardsPostProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if (!count($erno)) {
				if ($this->post['tx_multishop_pi1']['referrer']) {
					header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
					exit();
				} else {
					header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_overview', 1));
					exit();
				}
			}
		} else {
			// Insert DB
			$updateArray['crdate']=time();
            $updateArray['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
			// hook to let other plugins further manipulate the option values display
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['insertDashboardsPreProc'])) {
				$params=array(
					'updateArray'=>&$updateArray,
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['insertDashboardsPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_dashboard', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			$redirectUrl=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_overview');
			//$redirectUrl='';
			// hook to let other plugins further manipulate the option values display
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['insertDashboardsPostProc'])) {
				$params=array(
					'id'=>&$id,
					'redirectUrl'=>&$redirectUrl
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_dashboards/admin_dashboards_edit.php']['insertDashboardsPostProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if (count($erno)) {
				$erno[]='Failed to create record.<br/><br/>SQL error:<br/>'.$GLOBALS['TYPO3_DB']->sql_error().'<br/><br/>Query:<br/>'.$query.'.<br/>';
			} else {
				if ($redirectUrl) {
					header("Location: ".$redirectUrl);
					exit();
				} else {
					unset($this->post['tx_multishop_pi1']);
				}
			}
		}
	}
}
// Create / edit form
if ($this->get['tx_multishop_pi1']['dashboard_id']) {
	$this->editMode=1;
	$filter=array();
	$record=mslib_befe::getRecord($this->get['tx_multishop_pi1']['dashboard_id'], 'tx_multishop_dashboard', 'id', $filter);
	$this->post['tx_multishop_pi1']=$record;
}
if ($this->get['tx_multishop_pi1']['dashboard_id']) {
	$title='Edit dashboard record';
} else {
	$title='Insert dashboard record';
}
$content.='<div class="panel panel-default"><div class="panel-heading"><h3>'.$title.'</h3></div><div class="panel-body">';
if (is_array($erno) && count($erno)) {
	$content.='<div class="alert alert-danger"><h3>The following errors occurred</h3><ul>';
	foreach ($erno as $item) {
		$content.='<li>';
		$content.=$item;
		$content.='</li>';
	}
	$content.='</ul></div>';
}

$content.='<form method="post" class="form-horizontal ms_admin_form" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_overview&tx_multishop_pi1[action]=edit').'" enctype="multipart/form-data">';
//
$cols=array();
$cols[]='header_title';
$cols[]='dashboard_layout';
$cols[]='status';
//
foreach ($cols as $col) {
	$label=str_replace('_id', '', $col);
	$label=str_replace('_', ' ', $label);
	$label=ucfirst($label);
	//
    if ($col=='status') {
        $content .= '
        <div class="form-group">
            <label class="control-label col-md-2">' . $label . '</label>
            <div class="col-md-10">
        ';
        $content .= '<div class="radio radio-inline radio-success"><input type="radio" name="tx_multishop_pi1[' . $col . ']" id="dashboard_status_y" value="1"'.($this->post['tx_multishop_pi1'][$col] ? ' checked="checked"' : '').'><label for="dashboard_status_y">'.$this->pi_getLL("yes").'</label></div>';
        $content .= '<div class="radio radio-inline radio-success"><input type="radio" name="tx_multishop_pi1[' . $col . ']" id="dashboard_status_n" value="0"'.(!$this->post['tx_multishop_pi1'][$col] ? ' checked="checked"' : '').'><label for="dashboard_status_y">'.$this->pi_getLL("no").'</label></div>';
        $content .= '</div>
		</div>
        ';
    } else if ($col=='dashboard_layout') {
        $dashboard_records=mslib_befe::getRecords('', 'tx_multishop_dashboard', '');
        $dashboard_option='<option value="">'.$this->pi_getLL('choose').'</option>';
        $dashboard_option .= '<option value="layout1big1small"'.($this->post['tx_multishop_pi1'][$col]=='layout1big1small' ? ' selected="selected"' : '').'>1 big 1 small (2 cols)</option>';
        $dashboard_option .= '<option value="layout1small1big"'.($this->post['tx_multishop_pi1'][$col]=='layout1small1big' ? ' selected="selected"' : '').'>1 small 1 big (2 cols)</option>';
        $dashboard_option .= '<option value="layout1col"'.($this->post['tx_multishop_pi1'][$col]=='layout1col' ? ' selected="selected"' : '').'>1 col</option>';
        $dashboard_option .= '<option value="layout2cols"'.($this->post['tx_multishop_pi1'][$col]=='layout2cols' ? ' selected="selected"' : '').'>2 cols</option>';
        $dashboard_option .= '<option value="layout3cols"'.($this->post['tx_multishop_pi1'][$col]=='layout3cols' ? ' selected="selected"' : '').'>3 cols</option>';
        $dashboard_option .= '<option value="layout4cols"'.($this->post['tx_multishop_pi1'][$col]=='layout4cols' ? ' selected="selected"' : '').'>4 cols</option>';

        $content .= '
        <div class="form-group">
            <label class="control-label col-md-2">' . $label . '</label>
            <div class="col-md-10">';
        $content .= '<select name="tx_multishop_pi1[' . $col . ']" class="form-control">'.$dashboard_option.'</select>';
        $content .= '
            </div>
		</div>';
    } else {
        $content .= '
        <div class="form-group">
            <label class="control-label col-md-2">' . $label . '</label>
            <div class="col-md-10">
        ';
        $content .= '<input type="text" name="tx_multishop_pi1[' . $col . ']" class="form-control" value="' . htmlspecialchars($this->post['tx_multishop_pi1'][$col]) . '" />';
        $content .= '</div>
		</div>
        ';
    }

}
if (!$this->editMode) {
	// Create only shows URL so we can rip the video first and then redirect to the edit form
} else {
	// Update form
	$content.='<input type="hidden" name="tx_multishop_pi1[dashboard_id]" value="'.htmlspecialchars($this->post['tx_multishop_pi1']['id']).'" />';
}
$content.='
	<div class="clearfix">
		<div class="pull-right">
			<button type="submit" name="editpost" class="btn btn-success msadmin_button" value=""><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-save fa-stack-1x"></i></span> '.($this->get['tx_multishop_pi1']['dashboard_id'] ? $this->pi_getLL('save') : $this->pi_getLL('add')).'</button>
			<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$referrer.'" >
		</div>
	</div>
</form>
</div>
';
?>