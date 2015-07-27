<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ADMIN_USER) {
	$jsonData=array();
	if (is_numeric($this->post['tx_multishop_pi1']['customer_id'])) {
		$customer=mslib_fe::getUser($this->post['tx_multishop_pi1']['customer_id']);
		if ($customer['uid']) {
			$actionButtons=array();
			if ($customer['email']) {
				$actionLink='mailto:'.$customer['email'];
				$actionButtons['email']='<a href="'.$actionLink.'" class="btn btn-xs btn-default"><i class="fa fa-envelope-o"></i> '.$this->pi_getLL('email').'</a>';
			}
			if ($customer['telephone']) {
				$actionLink='callto:'.$customer['telephone'];
				$actionButtons['call']='<a href="'.$actionLink.'" class="btn btn-xs btn-default"><i class="fa fa-phone-square"></i> '.$this->pi_getLL('call').'</a>';
			}
			$address=array();
			$address[]=rawurlencode($customer['address']);
			$address[]=rawurlencode($customer['zip']);
			$address[]=rawurlencode($customer['city']);
			$address[]=rawurlencode($customer['country']);
			$actionLink='http://maps.google.com/maps?daddr='.implode('+',$address);
			$actionButtons['route']='<a href="'.$actionLink.'" rel="nofollow" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-map-marker"></i> '.$this->pi_getLL('route').'</a>';

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
				$jsonData['html'].=$this->pi_getLL('email').': '.$customer['email'].'<br />';
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
			if (count($actionButtons)) {
				$jsonData['html'].='<div class="btn-group">';
				foreach ($actionButtons as $actionButton) {
					$jsonData['html'].=$actionButton;
				}
				$jsonData['html'].='</div>';
			}
		} else {
			$jsonData['html']='No data.';
		}
	}
	echo json_encode($jsonData, ENT_NOQUOTES);
}
exit();
?>