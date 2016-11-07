<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	$return_data=array();
	//$customers=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'company, name, email');
	$customers=array();
	$groupid=$this->conf['fe_customer_usergroup'];
	if (is_numeric($this->get['tx_multishop_pi1']['usergroup'])) {
		$groupid=$this->get['tx_multishop_pi1']['usergroup'];
	}
	$orderby='company, name, email';
	$limit=50;
	if (is_numeric($groupid) and $groupid>0) {
		$filter=array();
		if (is_numeric($this->get['preselected_id'])) {
			$filter[]='uid='.$this->get['preselected_id'];
		}
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			$limit='';
			$this->get['q']=addslashes($this->get['q']);
			$orFilter=array();
			$orFilter[]='company like \'%'.$this->get['q'].'%\'';
			$orFilter[]='name like \'%'.$this->get['q'].'%\'';
			$orFilter[]='email like \'%'.$this->get['q'].'%\'';
			$orFilter[]='username like \'%'.$this->get['q'].'%\'';
			$orFilter[]='address like \'%'.$this->get['q'].'%\'';
			$orFilter[]='telephone like \'%'.$this->get['q'].'%\'';
			$filter[]='('.implode(' OR ',$orFilter).')';
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
			if (!$itemTitle && ($customer['name'] && $customer['name']!=$customer['company'])) {
				$itemTitle=$customer['name'];
			}
			$itemArray=array();
			if ($customer['company']) {
				$itemArray['company']=array(
					'label'=>$this->pi_getLL('company'),
					'value'=>$customer['company']
				);
			}
			if ($customer['name']) {
				$itemArray['name']=array(
					'label'=>$this->pi_getLL('name'),
					'value'=>$customer['name']
				);
			}
			if ($customer['email']) {
				$itemArray['email']=array(
					'label'=>$this->pi_getLL('email'),
					'value'=>$customer['email']
				);
			}
			if ($customer['username']) {
				$itemArray['username']=array(
					'label'=>$this->pi_getLL('username'),
					'value'=>$customer['username']
				);
			}
			if ($customer['address']) {
				$itemArray['address']=array(
					'label'=>$this->pi_getLL('address'),
					'value'=>$customer['address']
				);
			}
			if ($customer['city']) {
				$itemArray['city']=array(
					'label'=>$this->pi_getLL('city'),
					'value'=>$customer['city']
				);
			}
			if ($customer['telephone']) {
				$itemArray['telephone']=array(
					'label'=>$this->pi_getLL('telephone'),
					'value'=>$customer['telephone']
				);
			}
			// CUSTOM HTML MARKUP FOR SELECT2
			$htmlTitle='<h3>'.$itemTitle.'</h3>';
			$htmlTitle_array=array();
			foreach ($itemArray as $item_label => $rowItem) {
				//$htmlTitle.=$rowItem['label'].': <strong>'.$rowItem['value'].'</strong><br/>';
				switch($item_label) {
					case 'company':
						$htmlTitle_array[0]=$rowItem['value'];
						break;
					case 'name':
						$htmlTitle_array[1]=$rowItem['value'];
						break;
					case 'address':
						$htmlTitle_array[2]=$rowItem['value'];
						break;
					case 'city':
						$htmlTitle_array[3]=$rowItem['value'];
						break;
				}
			}
			ksort($htmlTitle_array);
			$htmlTitle='<strong>'.implode(' | ', $htmlTitle_array).'</strong>';
			$return_data[]=array(
				'id'=>$customer['uid'],
				'text'=>$htmlTitle
			);
		}
	}
	if (is_numeric($this->get['preselected_id']) && $this->get['preselected_id']>0 && count($customers)===1) {
	    $tmp_return_data=$return_data[0];
        $return_data=$tmp_return_data;
    }
	echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
?>