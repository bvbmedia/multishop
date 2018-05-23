<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$users = mslib_fe::getUsersByGroup($this->conf['fe_admin_usergroup']);
if (is_array($users)) {
    // grab all usergroups
    $usergroupUids = array();
    foreach ($users as $user) {
        $array = explode(',', $user['usergroup']);
        foreach ($array as $item) {
            if (!in_array($item, $usergroupUids)) {
                $usergroupUids[] = $item;
            }
        }
    }
    $tblRows = array();
    $tblRow[] = 'User';
    $sortedUsergroups = array();
    if (is_array($usergroupUids)) {
        foreach ($usergroupUids as $usergroupUid) {
            if ($usergroupUid != $this->conf['fe_customer_usergroup']) {
                $usergroup = mslib_befe::getRecord($usergroupUid, 'fe_groups', 'uid');
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