<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	$jsonData=array();
	if (is_numeric($this->post['tx_multishop_pi1']['customer_id'])) {
		$customer=mslib_fe::getUser($this->post['tx_multishop_pi1']['customer_id']);
		if ($customer['uid']) {
			$jsonData['html']='';
			if ($customer['company']) {
				$jsonData['html'].='<h1>'.$customer['company'].'</h1>';
			}
			if ($customer['name']) {
				$jsonData['html'].='<h1>'.$customer['name'].'</h1>';
			}
			$jsonData['html'].=$customer['address'].'<br />
			'.$customer['zip'].' '.$customer['city'].' <br />
			'.mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $customer['country']).'<br /><br />
			';
			if ($customer['email']) {
				$jsonData['html'].=$this->pi_getLL('email').': <a href="mailto:'.$customer['email'].'">'.$customer['email'].'</a><br />';
			}
			if ($customer['telephone']) {
				$jsonData['html'].=$this->pi_getLL('telephone').': '.$customer['telephone'].'<br />';
			}
			if ($customer['mobile']) {
				$jsonData['html'].=$this->pi_getLL('mobile').': '.$customer['mobile'].'<br />';
			}
			if ($customer['fax']) {
				$jsonData['html'].=$this->pi_getLL('fax').': '.$customer['fax'].'<br />';
			}
			$jsonData['html'].='</div>';
		} else {
			$jsonData['html']='No data.';
		}
	}
	echo json_encode($jsonData, ENT_NOQUOTES);
}
exit();
?>