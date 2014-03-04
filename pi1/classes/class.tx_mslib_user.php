<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

/***************************************************************
*  Copyright notice
*
*  (c) 2010 BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */
class tx_mslib_user {
	var $get 			= '';
	var $post 			= '';
	var $ms 			= '';
	var $ref			= '';
	
	/*
	 * properties of user
	*/
	var $username 				= '';
	var $email 					= '';
	var $confirmation_email 	= '';
	var $gender 				= '';
	var $password 				= '';
	var $confirmation_password 	= '';
	var $first_name 			= '';
	var $middle_name 			= '';
	var $last_name 				= '';
	var $name 					= '';
	var $company 				= '';
	var $country 				= '';
	var $address 				= '';
	var $address_number 		= '';
	var $address_ext 			= '';
	var $zip 					= '';
	var $city 					= '';
	var $telephone 				= '';
	var $mobile 				= '';
	var $newsletter 			= '';
	var $captcha_code 			= '';
	var $birthday 				= '';
	var $region 				= '';
	var $customFields			= array();
	
	function init(&$ref) {		
		$this->get 		= &$ref->get;
		$this->post 	= &$ref->post;
		$this->ms 		= &$ref->ms;
		$this->ref		= &$ref;
		return true;
	}

	/**
	 * @return the $username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * @return the $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}
	
	/**
	 * @return the $confirmation_email
	 */
	public function getConfirmation_email() {
		return $this->confirmation_email;
	}
	
	/**
	 * @param string $confirmation_email
	 */
	public function setConfirmation_email($confirmation_email) {
		$this->confirmation_email = $confirmation_email;
	}

	/**
	 * @return the $gender
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * @param string $gender
	 */
	public function setGender($gender) {
		$this->gender = $gender;
	}

	/**
	 * @return the $password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}
	
	/**
	 * @return the $confirmation_password
	 */
	public function getConfirmation_password() {
		return $this->confirmation_password;
	}
	
	/**
	 * @param string $confirmation_password
	 */
	public function setConfirmation_password($confirmation_password) {
		$this->confirmation_password = $confirmation_password;
	}

	/**
	 * @return the $first_name
	 */
	public function getFirst_name() {
		return $this->first_name;
	}

	/**
	 * @param string $first_name
	 */
	public function setFirst_name($first_name) {
		$this->first_name = $first_name;
	}

	/**
	 * @return the $middle_name
	 */
	public function getMiddle_name() {
		return $this->middle_name;
	}

	/**
	 * @param string $middle_name
	 */
	public function setMiddle_name($middle_name) {
		$this->middle_name = $middle_name;
	}

	/**
	 * @return the $last_name
	 */
	public function getLast_name() {
		return $this->last_name;
	}

	/**
	 * @param string $last_name
	 */
	public function setLast_name($last_name) {
		$this->last_name = $last_name;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$name = preg_replace('/\s+/', ' ', $name);
		$this->name = $name;
	}

	/**
	 * @return the $company
	 */
	public function getCompany() {
		return $this->company;
	}

	/**
	 * @param string $company
	 */
	public function setCompany($company) {
		$this->company = $company;
	}

	/**
	 * @return the $country
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * @param string $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * @return the $address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @param string $address
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * @return the $address_number
	 */
	public function getAddress_number() {
		return $this->address_number;
	}

	/**
	 * @param string $address_number
	 */
	public function setAddress_number($address_number) {
		$this->address_number = $address_number;
	}

	/**
	 * @return the $address_ext
	 */
	public function getAddress_ext() {
		return $this->address_ext;
	}

	/**
	 * @param string $address_ext
	 */
	public function setAddress_ext($address_ext) {
		$this->address_ext = $address_ext;
	}

	/**
	 * @return the $zip
	 */
	public function getZip() {
		return $this->zip;
	}

	/**
	 * @param string $zip
	 */
	public function setZip($zip) {
		$this->zip = $zip;
	}

	/**
	 * @return the $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @param string $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * @return the $telephone
	 */
	public function getTelephone() {
		return $this->telephone;
	}

	/**
	 * @param string $telephone
	 */
	public function setTelephone($telephone) {
		$this->telephone = $telephone;
	}

	/**
	 * @return the $mobile
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @param string $mobile
	 */
	public function setMobile($mobile) {
		$this->mobile = $mobile;
	}

	/**
	 * @return the $newsletter
	 */
	public function getNewsletter() {
		return $this->newsletter;
	}

	/**
	 * @param string $newsletter
	 */
	public function setNewsletter($newsletter) {
		$this->newsletter = $newsletter;
	}
	
	/**
	 * @return the $captcha_code
	 */
	public function getCaptcha_code() {
		return $this->captcha_code;
	}
	
	/**
	 * @param string $captcha_code
	 */
	public function setCaptcha_code($captcha_code) {
		$this->captcha_code = $captcha_code;
	}

	/**
	 * @return the $birthday
	 */
	public function getBirthday() {
		return $this->birthday;
	}

	/**
	 * @param string $birthday
	 */
	public function setBirthday($birthday) {
		$this->birthday = $birthday;
	}

	/**
	 * @return the $region
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @param string $region
	 */
	public function setRegion($region) {
		$this->region = $region;
	}

	public function checkUserData() {
		$captcha_code = $this->getCaptcha_code();	
		$session = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_multishop_session');
		if (!$captcha_code or $session['captcha_code'] != md5($captcha_code)) {
			$erno[]=$this->ref->pi_getLL('captcha_code_is_invalid');
		}
		if (!$this->getEmail()) {
			$erno[]=$this->ref->pi_getLL('no_email_address_has_been_specified');
		}
		
		if (!$this->getAddress()) {
			$erno[]=$this->ref->pi_getLL('no_address_has_been_specified');
		}
		
		if (!$this->getAddress_number()) {
			$erno[]=$this->ref->pi_getLL('no_address_number_has_been_specified');
		}
		
		if (!$this->getFirst_name()) {
			$erno[]=$this->ref->pi_getLL('no_first_name_has_been_specified');
		}
		
		if (!$this->getLast_name()) {
			$erno[]=$this->ref->pi_getLL('no_last_name_has_been_specified');
		}
		
		if (!$this->getZip()) {
			$erno[]=$this->ref->pi_getLL('no_zip_has_been_specified');
		}
		
		if (!$this->getCity()) {
			$erno[]=$this->ref->pi_getLL('no_city_has_been_specified');
		}
		if (!$this->getCountry()) {
			$erno[]=$this->ref->pi_getLL('no_country_has_been_specified');
		}
		if (!$this->getPassword()) {
			$erno[]=$this->ref->pi_getLL('password_is_required');
		}		
		if ($this->getEmail() != $this->getConfirmation_email()) {
			$erno[]=$this->ref->pi_getLL('email_is_not_the_same_as_repeated_email');
		}
		if ($this->getPassword() != $this->getConfirmation_password()) {
			$erno[]=$this->ref->pi_getLL('password_is_not_the_same_as_repeated_password');
		}
/*		
		$count = count($erno);		
		if ($count == 1) {
			if (empty($erno[0])) {
				unset($erno[0]);
			}
		}
*/
		if ($this->getEmail()) {
			// check if username is not in use
			$checkUsername=mslib_fe::getUser($this->getEmail(),'username');
			if ($checkUsername['uid']) {
				if ($this->getEmail() == $this->getUsername()) {
					$erno[]=$this->ref->pi_getLL('specified_email_address_already_in_use');
				} else {
					$erno[]=$this->ref->pi_getLL('specified_username_already_in_use');
				}
			}
			$checkEmail=mslib_fe::getUser($this->getEmail(),'email');
			if (!$checkUsername['uid'] and $checkEmail['uid']) {
				$erno[]=$this->ref->pi_getLL('specified_email_address_already_in_use');
			}			
		}		
		return $erno;
	}
	
	function saveUserData() {
		// add the user
		$insertArray=array();
		if ($this->username) {
			$insertArray['username']					= $this->username;
		} else {
			$insertArray['username']					= $this->email;
		}
		$insertArray['email']						= $this->email;
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($this->gender) {
			case '0':
			case 'm':
				// male
				$insertArray['gender']='0';
			break;
			case '1':
			case 'f':
				// female
				$insertArray['gender']='1';
			break;
			case '2':
			case 'c':
				// couple
				$insertArray['gender']='2';
			break;
		}
		$insertArray['password'] 					= mslib_befe::getHashedPassword($this->password);
		$insertArray['first_name']					= $this->first_name;
		$insertArray['middle_name']					= $this->middle_name;
		$insertArray['last_name']					= $this->last_name;
		$insertArray['name']						= $this->name;
		$insertArray['company']						= $this->company;
		$insertArray['country']						= $this->country;
		$insertArray['street_name']					= $this->address;
		$insertArray['address_number']				= $this->address_number;
		$insertArray['address_ext']					= $this->address_ext;
		$insertArray['address']						= $insertArray['street_name'].' '.$insertArray['address_number'];
		if ($insertArray['address_ext']) {
			$insertArray['address'].='-'.$insertArray['address_ext'];
		}
		$insertArray['address']=preg_replace('/\s+/', ' ',$insertArray['address']);
		$insertArray['zip']							= $this->zip;
		$insertArray['city']						= $this->city;
		$insertArray['telephone']					= $this->telephone;
		$insertArray['mobile']						= $this->mobile;
		$insertArray['tx_multishop_newsletter']		= $this->newsletter;
		$insertArray['disable']						= 1;
		$insertArray['tstamp']						= time();
		$insertArray['usergroup']					= $this->ref->conf['fe_customer_usergroup'];
		$insertArray['pid']							= $this->ref->conf['fe_customer_pid'];
		$insertArray['tx_multishop_code']			= md5(uniqid('',TRUE));
		$insertArray['crdate']						= time();
		$insertArray['page_uid']					= $this->ref->shop_pid;
		$insertArray['http_referer']				= $this->ref->cookie['HTTP_REFERER'];	
		$insertArray['ip_address']					= $this->ref->REMOTE_ADDR;	
		if (is_array($this->customFields) and count($this->customFields)) {
			foreach ($this->customFields as $key => $val) {
				$insertArray[$key] = $val;
			}
		}
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $insertArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if ($res) {
			$customer_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
			//hook to let other plugins further manipulate the create table query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_user.php']['createUserPostProc'])) {
				$params = array (
					'customer_id' => &$customer_id
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_user.php']['createUserPostProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			return $customer_id;
		}
		return false;	
	}
	
	function saveUserBillingAddress($customer_id, $is_default = true) {
		// insert billing into tt_address
		$insertArray=array();
		$insertArray['tstamp']						= time();
		$insertArray['company']						= $this->company;
		$insertArray['name']						= $this->name;
		$insertArray['first_name']					= $this->first_name;
		$insertArray['middle_name']					= $this->middle_name;
		$insertArray['last_name']					= $this->last_name;
		$insertArray['email']						= $this->email;
		$insertArray['street_name']					= $this->address;
		$insertArray['address_number']				= $this->address_number;
		$insertArray['address_ext']					= $this->address_ext;
		$insertArray['address']						= $insertArray['street_name'].' '.$insertArray['address_number'];
		if ($insertArray['address_ext']) {
			$insertArray['address'].='-'.$insertArray['address_ext'];
		}
		$insertArray['zip']							= $this->zip;
		$insertArray['phone']						= $this->telephone;
		$insertArray['mobile']						= $this->mobile;
		$insertArray['city']						= $this->city;
		$insertArray['country']						= $this->country;
		
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($this->gender) {
			case '0':
			case 'm':
				$insertArray['gender']='m';
			break;
			case '1':
			case 'f':
				$insertArray['gender']='f';
			break;
			case '2':
			case 'c':
				$insertArray['gender']='c';
			break;
		}
		$insertArray['birthday'] 					= strtotime($this->birthday);
		$insertArray['title'] 						= (($this->gender == 'm') ? 'Mr.' : 'Mrs.');
		$insertArray['region']						= $this->region;
		
		$insertArray['pid']							= $this->ref->conf['fe_customer_pid'];
		$insertArray['page_uid']					= $this->ref->shop_pid;
		$insertArray['tstamp']						= time();
		$insertArray['tx_multishop_address_type'] 	= 'billing';
		
		$insertArray['tx_multishop_default'] 		= ($is_default) ? 1 : 0;
		$insertArray['tx_multishop_customer_id'] 	= $customer_id;
		
		$query 										= $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertArray);
		$res 										= $GLOBALS['TYPO3_DB']->sql_query($query);
		
		if ($res) {
			return true;
		}
		
		return false;
	}
	
	function saveUserDeliveryAddress($customer_id, $is_default = false) {
		// insert billing into tt_address
		$insertArray=array();
		$insertArray['tstamp']						= time();
		$insertArray['company']						= $this->company;
		$insertArray['name']						= $this->name;
		$insertArray['first_name']					= $this->first_name;
		$insertArray['middle_name']					= $this->middle_name;
		$insertArray['last_name']					= $this->last_name;
		$insertArray['email']						= $this->email;
		$insertArray['address']						= $this->address;
		$insertArray['address_number']				= $this->address_number;
		$insertArray['zip']							= $this->zip;
		$insertArray['phone']						= $this->telephone;
		$insertArray['mobile']						= $this->mobile;
		$insertArray['city']						= $this->city;
		$insertArray['country']						= $this->country;
		// fe user table holds integer as value: 0 is male, 1 is female
		// but in tt_address its varchar: m is male, f is female
		switch ($this->gender) {
			case '0':
			case 'm':
				$insertArray['gender']='m';
			break;
			case '1':
			case 'f':
				$insertArray['gender']='f';
			break;
			case '2':
			case 'c':
				$insertArray['gender']='c';
			break;
		}
		$insertArray['birthday'] 					= strtotime($this->birthday);
		$insertArray['title'] 						= (($this->gender == 'm') ? 'Mr.' : 'Mrs.');
		$insertArray['region']						= $this->region;
		
		$insertArray['pid']							= $this->ref->conf['fe_customer_pid'];
		$insertArray['tstamp']						= time();
		$insertArray['tx_multishop_address_type'] 	= 'delivery';
		
		$insertArray['tx_multishop_default'] 		= ($is_default) ? 1 : 0;
		$insertArray['tx_multishop_customer_id'] 	= $customer_id;
		
		$query 										= $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertArray);
		$res 										= $GLOBALS['TYPO3_DB']->sql_query($query);
		
		if ($res) {
			return true;
		}
		
		return false;
	}
	function setCustomField($name,$val) {
		$this->customFields[$name]=$val;
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_user.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_user.php"]);
}
?>