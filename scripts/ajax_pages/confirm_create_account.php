<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$user=mslib_fe::getUser($this->get['tx_multishop_pi1']['hash'],'code');
if ($user['uid'] and !$user['tx_multishop_optin_crdate']) {
	$updateArray=array();
	$updateArray['disable']						=0;			
	$updateArray['tx_multishop_optin_crdate']	=time();
	$updateArray['tx_multishop_optin_ip']		=t3lib_div::getIndpEnv('REMOTE_ADDR');
	$query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$user['uid'],$updateArray);
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);
	// auto login the user
	$loginData=array(
			'uname' => $user['username'], //usernmae
			'uident' => $user['password'], //password
			'status' => 'login'
	);
	$GLOBALS['TSFE']->fe_user->checkPid = 0; //do not use a particular pid
	$info= $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
	$user=$GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'],$loginData['uname']);
	$GLOBALS['TSFE']->fe_user->createUserSession($user);				
	// auto login the user
	
	// redirect to specific page
	$redirect_url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid);
	//hook to let other plugins further manipulate the redirect link
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationSuccesfulRedirectLinkPreProc']))
	{
		$params = array (
			'updateArray' => $updateArray,
			'user' => $user,
			'redirect_url' => &$redirect_url			
		); 
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationSuccesfulRedirectLinkPreProc'] as $funcRef)
		{
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	if ($redirect_url) {
		header("Location: ".$redirect_url);						
	}
	exit();				
} elseif ($user['uid'] and $user['tx_multishop_optin_crdate']) {
	// user is already confirmed	
	// redirect to specific page
	$redirect_url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid);	
	//hook to let other plugins further manipulate the redirect link
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationRepeatedRedirectLinkPreProc']))
	{
		$params = array (
			'updateArray' => $updateArray,
			'user' => $user,
			'redirect_url' => &$redirect_url			
		); 
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/confirm_create_account']['confirmationRepeatedRedirectLinkPreProc'] as $funcRef)
		{
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	if ($redirect_url) {
		header("Location: ".$redirect_url);						
	}
	exit();
}
exit();
?>