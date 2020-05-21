<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$users = mslib_fe::getUsersByGroup($this->conf['fe_admin_usergroup']);
if (is_array($users)) {
    // Collect all usergroups
    $usergroupUids = array();
    foreach ($users as $user) {
        $array = explode(',', $user['usergroup']);
        foreach ($array as $item) {
            if (!in_array($item, $usergroupUids)) {
                $usergroupUids[] = $item;
            }
        }
    }
    // Collect table heading row
    $tblRows = array();
    $tblRow[] = 'User';
    $sortedUsergroups = array();
    if (is_array($usergroupUids)) {
        foreach ($usergroupUids as $usergroupUid) {
            if ($usergroupUid != $this->conf['fe_customer_usergroup']) {
                $filter = array();
                $filter[] = 'deleted=0';
                $filter[] = 'hidden=0';
                $usergroup = mslib_befe::getRecord($usergroupUid, 'fe_groups', 'uid', $filter);
                if (is_array($usergroup)) {
                    $sortedUsergroups[$usergroup['uid']] = $usergroup['title'];
                }
            }
        }
        asort($sortedUsergroups, SORT_NATURAL);
        foreach ($sortedUsergroups as $uid => $sortedUsergroup) {
            $tblRow[] = $sortedUsergroup;
        }
    }
    $tblRows[] = $tblRow;
    // Collect table body rows
    foreach ($users as $user) {
        $tblRow = array();
        $tblRow[] = $user['username'];
        $usergroupUids = explode(',', $user['usergroup']);
        foreach ($sortedUsergroups as $uid => $sortedUsergroup) {
            $value = '';
            if (is_array($usergroupUids) && in_array($uid, $usergroupUids)) {
                $value = '<span class="fa-stack"><i class="fa fa-check fa-stack-1x"></i></span>';
            }
            $tblRow[] = $value;
        }
        $tblRows[] = $tblRow;
    }
    // Render array to table
    $idName = 'admin_users_overview';
    $settings = array();
    $settings['keyNameAsHeadingTitle'] = 0;
    $settings['sumTr'] = 0;
    // Pass through class names
    $settings['cellClasses'][] = '';
    foreach ($sortedUsergroups as $uid => $sortedUsergroup) {
        $settings['cellClasses'][] = 'text-center';
    }
    $content .= mslib_befe::bootstrapPanel('Admin users overview', mslib_befe::arrayToTable($tblRows, $idName, $settings), 'success');
}
?>
