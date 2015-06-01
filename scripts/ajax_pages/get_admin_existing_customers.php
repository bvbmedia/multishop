<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	$return_data=array();
	//$customers=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'company, name, email');
	$customers=array();
	$groupid=$this->conf['fe_customer_usergroup'];
	$orderby='company, name, email';
	$limit=50;
	if (is_numeric($groupid) and $groupid>0) {
		$filter=array();
		if (isset($this->get['q']) &&!empty($this->get['q'])) {
			$limit='';
			$this->get['q']=addslashes($this->get['q']);
			$filter[]='(company like \'%'.$this->get['q'].'%\' or name like \'%'.$this->get['q'].'%\' or email like \'%'.$this->get['q'].'%\' or username like \'%'.$this->get['q'].'%\' or address like \'%'.$this->get['q'].'%\' or telephone like \'%'.$this->get['q'].'%\')';
		}
		if (!$this->masterShop) {
			$filter[]='page_uid=\''.$this->shop_pid.'\'';
		}
		$filter[]=$GLOBALS['TYPO3_DB']->listQuery('usergroup', $groupid, 'fe_users');
		if (!$include_disabled) {
			$filter[]='disable=0';
		}
		$filter[]='deleted=0';
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'fe_users', // FROM ...
			implode(' and ', $filter), // WHERE...
			'', // GROUP BY...
			$orderby, // ORDER BY...
			$limit // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$tel=0;
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$customers[]=$row;
			}
		}
	}


	foreach ($customers as $customer_idx=>$customer) {
		if ($customer['email']) {
			$itemTitle='';
			if ($customer['company']) {
				$itemTitle=$customer['company'];
			}
			if (!$itemTitle && ($customer['name'] && $customer['name'] !=$customer['company'])) {
				$itemTitle=$customer['name'];
			}
			$itemArray=array();
			if ($customer['name']) {
				$itemArray[]=array('label'=>$this->pi_getLL('name'),'value'=>$customer['name']);
			}
			if ($customer['email']) {
				$itemArray[]=array('label'=>$this->pi_getLL('email'),'value'=>$customer['email']);
			}
			if ($customer['username']) {
				$itemArray[]=array('label'=>$this->pi_getLL('username'),'value'=>$customer['username']);
			}
			if ($customer['address']) {
				$itemArray[]=array('label'=>$this->pi_getLL('address'),'value'=>$customer['address']);
			}
			if ($customer['telephone']) {
				$itemArray[]=array('label'=>$this->pi_getLL('telephone'),'value'=>$customer['telephone']);
			}
			// CUSTOM HTML MARKUP FOR SELECT2
			$htmlTitle='<h3>'.$itemTitle.'</h3>';
			foreach ($itemArray as $rowItem) {
				$htmlTitle.=$rowItem['label'].': <strong>'.$rowItem['value'].'</strong><br/>';
			}
			$return_data[]=array(
				'id'=>$customer['uid'],
				'text'=>$htmlTitle
			);
		}
	}
	echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
?>