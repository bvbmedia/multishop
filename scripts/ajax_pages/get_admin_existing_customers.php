<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	$return_data=array();
	$customers=mslib_fe::getUsers($this->conf['fe_customer_usergroup'], 'company, name, email');
	foreach ($customers as $customer) {
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
			if (isset($this->get['q']) &&!empty($this->get['q'])) {
				if (strpos($htmlTitle, $this->get['q'])!==false) {
					$return_data[]=array(
						'id'=>$customer['uid'],
						'text'=>$htmlTitle
					);
				}
			} else {
				$return_data[]=array(
					'id'=>$customer['uid'],
					'text'=>$htmlTitle
				);
			}
		}
	}
	echo json_encode($return_data, ENT_NOQUOTES);
}
exit();
?>