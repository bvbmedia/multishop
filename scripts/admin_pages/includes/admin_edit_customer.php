<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if ($this->post && $this->post['email']) {
	$this->post['email']=mslib_fe::RemoveXSS($this->post['email']);
	$erno=array();
	if (is_numeric($this->post['tx_multishop_pi1']['cid'])) {
		$edit_mode=1;
		$user=mslib_fe::getUser($this->post['tx_multishop_pi1']['cid']);
		if ($user['email']<>$this->post['email']) {
			if (!$this->ms['MODULES']['ADMIN_ALLOW_DUPLICATE_CUSTOMERS_EMAIL_ADDRESS']) {
				// check if the emailaddress is not already in use
				$usercheck=mslib_fe::getUser($this->post['email'], 'email');
				if ($usercheck['uid']) {
					$erno[]='Email address is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
				}
			}
		}
		if ($user['username']<>$this->post['username']) {
			// check if the emailaddress is not already in use
			$usercheck=mslib_fe::getUser($this->post['username'], 'username');
			if ($usercheck['uid']) {
				$erno[]='Username is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
			}
		}
	} else {
		if (!$this->ms['MODULES']['ADMIN_ALLOW_DUPLICATE_CUSTOMERS_EMAIL_ADDRESS']) {
			// check if the emailaddress is not already in use
			$usercheck=mslib_fe::getUser($this->post['email'], 'email');
			if ($usercheck['uid']) {
				$erno[]='Email address is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
			}
		}
		// check if the emailaddress is not already in use
		$usercheck=mslib_fe::getUser($this->post['username'], 'username');
		if ($usercheck['uid']) {
			$erno[]='Username is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
		}
	}
	if (count($erno)) {
		$this->get['tx_multishop_pi1']['cid']=$this->post['tx_multishop_pi1']['cid'];
		$continue=0;
	} else {
		$continue=1;
	}
	if ($continue) {
		$updateArray=array();
        if (isset($this->post['tx_multishop_language'])) {
            $updateArray['tx_multishop_language']=$this->post['tx_multishop_language'];
        }
		$updateArray['username']=$this->post['username'];
		if ($this->post['birthday']) {
			$updateArray['date_of_birth']=strtotime($this->post['birthday']);
		}
		$updateArray['first_name']=$this->post['first_name'];
		$updateArray['middle_name']=$this->post['middle_name'];
		$updateArray['last_name']=$this->post['last_name'];
		$updateArray['name']=$updateArray['first_name'].' '.$updateArray['middle_name'].' '.$updateArray['last_name'];
		$updateArray['name']=preg_replace('/\s+/', ' ', $updateArray['name']);
		$updateArray['gender']=$this->post['gender'];
		$updateArray['company']=$this->post['company'];
		$updateArray['street_name']=$this->post['street_name'];
		$updateArray['address_number']=$this->post['address_number'];
		$updateArray['address_ext']=$this->post['address_ext'];
		$updateArray['address']=$updateArray['street_name'].' '.$updateArray['address_number'].$updateArray['address_ext'];
		$updateArray['address']=preg_replace('/\s+/', ' ', $updateArray['address']);
		$updateArray['zip']=$this->post['zip'];
		$updateArray['city']=$this->post['city'];
		$updateArray['country']=$this->post['country'];
		$updateArray['email']=$this->post['email'];
		$updateArray['www']=$this->post['www'];
		$updateArray['telephone']=$this->post['telephone'];
		$updateArray['mobile']=$this->post['mobile'];
		$updateArray['tx_multishop_discount']=$this->post['tx_multishop_discount'];
		$updateArray['tx_multishop_payment_condition']=$this->post['tx_multishop_payment_condition'];
		if ($this->post['password']) {
			$updateArray['password']=mslib_befe::getHashedPassword($this->post['password']);
		}
		if ($this->post['tx_multishop_pi1']['image']) {
			$updateArray['image']=$this->post['tx_multishop_pi1']['image'];
		}
		if (isset($this->post['tx_multishop_vat_id'])) {
			if (!empty($this->post['tx_multishop_vat_id'])) {
				$updateArray['tx_multishop_vat_id']=$this->post['tx_multishop_vat_id'];
			} else {
				$updateArray['tx_multishop_vat_id']='';
			}
		}
		if (isset($this->post['tx_multishop_coc_id'])) {
			if (!empty($this->post['tx_multishop_coc_id'])) {
				$updateArray['tx_multishop_coc_id']=$this->post['tx_multishop_coc_id'];
			} else {
				$updateArray['tx_multishop_coc_id']='';
			}
		}
		if ($this->post['page_uid'] and $this->masterShop) {
			$updateArray['page_uid']=$this->post['page_uid'];
		}
		if (is_numeric($this->post['tx_multishop_pi1']['cid'])) {
			$customer_id=$this->post['tx_multishop_pi1']['cid'];
			// update mode
			if (count($this->post['tx_multishop_pi1']['groups'])) {
				$updateArray['usergroup']=implode(",", $this->post['tx_multishop_pi1']['groups']);
				if (isset($user['usergroup'])) {
					// first get old usergroup data, cause maybe the user is also member of excluded usergroups that we should remain
					$old_usergroups=explode(",", $user['usergroup']);
					foreach ($this->excluded_userGroups as $usergroup) {
						if (in_array($usergroup, $old_usergroups)) {
							if (!empty($updateArray['usergroup'])) {
								$updateArray['usergroup'].=','.$usergroup;
							} else {
								$updateArray['usergroup'].=$usergroup;
							}
						}
					}
				}
			} else {
				if (isset($user['usergroup'])) {
					// first get old usergroup data, cause maybe the user is also member of excluded usergroups that we should remain
					$old_usergroups=explode(",", $user['usergroup']);
					foreach ($this->excluded_userGroups as $usergroup) {
						if (in_array($usergroup, $old_usergroups)) {
							if (!empty($updateArray['usergroup'])) {
								$updateArray['usergroup'].=','.$usergroup;
							} else {
								$updateArray['usergroup'].=$usergroup;
							}
						}
					}
				}
			}
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPreProc'])) {
				$params=array(
					'uid'=>$this->post['tx_multishop_pi1']['cid'],
					'updateArray'=>&$updateArray,
					'user'=>$user,
					'erno'=>$erno
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			if (count($erno)) {
				$this->get['tx_multishop_pi1']['cid']=$this->post['tx_multishop_pi1']['cid'];
				$continue=0;
			} else {
				$continue=1;
			}
			if ($continue) {
				// custom hook that can be controlled by third-party plugin eof
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$this->post['tx_multishop_pi1']['cid'], $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				//update the tt_address billing
				$updateTTAddressArray=array();
				$updateTTAddressArray['tstamp']=time();
				$updateTTAddressArray['company']=$updateArray['company'];
				$updateTTAddressArray['name']=$updateArray['first_name'].' '.$updateArray['middle_name'].' '.$updateArray['last_name'];
				$updateTTAddressArray['name']=preg_replace('/\s+/', ' ', $insertArray['name']);
				$updateTTAddressArray['first_name']=$updateArray['first_name'];
				$updateTTAddressArray['middle_name']=$updateArray['middle_name'];
				$updateTTAddressArray['last_name']=$updateArray['last_name'];
				$updateTTAddressArray['email']=$updateArray['email'];
				if (!$updateArray['street_name']) {
					// fallback for old custom checkouts
					$updateTTAddressArray['street_name']=$updateArray['address'];
					$updateTTAddressArray['address_number']=$updateArray['address_number'];
					$updateTTAddressArray['address_ext']=$updateArray['address_ext'];
					$updateTTAddressArray['address']=$updateTTAddressArray['street_name'].' '.$updateTTAddressArray['address_number'].($insertArray['address_ext'] ? '-'.$updateTTAddressArray['address_ext'] : '');
					$updateTTAddressArray['address']=preg_replace('/\s+/', ' ', $updateTTAddressArray['address']);
				} else {
					$updateTTAddressArray['street_name']=$updateArray['street_name'];
					$updateTTAddressArray['address_number']=$updateArray['address_number'];
					$updateTTAddressArray['address_ext']=$updateArray['address_ext'];
					$updateTTAddressArray['address']=$updateArray['address'];
				}
				$updateTTAddressArray['zip']=$updateArray['zip'];
				$updateTTAddressArray['phone']=$updateArray['telephone'];
				$updateTTAddressArray['mobile']=$updateArray['mobile'];
				$updateTTAddressArray['city']=$updateArray['city'];
				$updateTTAddressArray['country']=$updateArray['country'];
				$updateTTAddressArray['gender']=$updateArray['gender'];
				$updateTTAddressArray['birthday']=strtotime($updateArray['birthday']);
				if ($updateArray['gender']=='m') {
					$updateTTAddressArray['title']='Mr.';
				} else {
					if ($updateArray['gender']=='f') {
						$updateTTAddressArray['title']='Mrs.';
					}
				}
				$updateTTAddressArray['region']=$updateArray['state'];
				$updateTTAddressArray['pid']=$this->conf['fe_customer_pid'];
				$updateTTAddressArray['page_uid']=$this->shop_pid;
				$updateTTAddressArray['tstamp']=time();
				$updateTTAddressArray['tx_multishop_address_type']='billing';
				$updateTTAddressArray['tx_multishop_default']=1;
				$updateTTAddressArray['tx_multishop_customer_id']=$customer_id;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'tx_multishop_customer_id='.$customer_id.' and tx_multishop_address_type=\'billing\'', $updateTTAddressArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				// custom hook that can be controlled by third-party plugin
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPostProc'])) {
					$params=array(
						'uid'=>$this->post['tx_multishop_pi1']['cid']
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPostProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
			}
			// custom hook that can be controlled by third-party plugin eof
			// customer shipping/payment method mapping
			if ($customer_id && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
				// shipping/payment methods
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_customers_method_mappings', 'customers_id=\''.$customer_id.'\'');
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if (is_array($this->post['payment_method']) and count($this->post['payment_method'])) {
					foreach ($this->post['payment_method'] as $payment_method_id=>$value) {
						$updateArray=array();
						$updateArray['customers_id']=$customer_id;
						$updateArray['method_id']=$payment_method_id;
						$updateArray['type']='payment';
						$updateArray['negate']=$value;
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				if (is_array($this->post['shipping_method']) and count($this->post['shipping_method'])) {
					foreach ($this->post['shipping_method'] as $shipping_method_id=>$value) {
						$updateArray=array();
						$updateArray['customers_id']=$customer_id;
						$updateArray['method_id']=$shipping_method_id;
						$updateArray['type']='shipping';
						$updateArray['negate']=$value;
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				// shipping/payment methods eof
			}
		} else {
			// insert mode
            if (isset($this->post['tx_multishop_language'])) {
                $updateArray['tx_multishop_language']=$this->post['tx_multishop_language'];
            }
			if (count($this->post['tx_multishop_pi1']['groups'])) {
				$this->post['tx_multishop_pi1']['groups'][]=$this->conf['fe_customer_usergroup'];
				$updateArray['usergroup']=implode(",", $this->post['tx_multishop_pi1']['groups']);
			} else {
				$updateArray['usergroup']=$this->conf['fe_customer_usergroup'];
			}
			$updateArray['pid']=$this->conf['fe_customer_pid'];
			$updateArray['tx_multishop_code']=md5(uniqid('', true));
			$updateArray['tstamp']=time();
			$updateArray['crdate']=time();
			if ($this->post['password']) {
				$updateArray['password']=mslib_befe::getHashedPassword($this->post['password']);
			} else {
				$updateArray['password']=mslib_befe::getHashedPassword(rand(1000000, 9000000));
			}
			if ($this->post['page_uid'] and $this->masterShop) {
				$updateArray['page_uid']=$this->post['page_uid'];
			} else {
				$updateArray['page_uid']=$this->shop_pid;
			}
			if (isset($this->post['tx_multishop_vat_id'])) {
				if (!empty($this->post['tx_multishop_vat_id'])) {
					$updateArray['tx_multishop_vat_id']=$this->post['tx_multishop_vat_id'];
				} else {
					$updateArray['tx_multishop_vat_id']='';
				}
			}
			if (isset($this->post['tx_multishop_coc_id'])) {
				if (!empty($this->post['tx_multishop_coc_id'])) {
					$updateArray['tx_multishop_coc_id']=$this->post['tx_multishop_coc_id'];
				} else {
					$updateArray['tx_multishop_coc_id']='';
				}
			}
//			$updateArray['tx_multishop_newsletter']			=	$address['tx_multishop_newsletter'];
			$updateArray['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPreProc'])) {
				$params=array(
					'uid'=>$this->post['tx_multishop_pi1']['cid'],
					'updateArray'=>&$updateArray
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) {
				$erno[]=$GLOBALS['TYPO3_DB']->sql_error();
			} else {
				$customer_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
				// ADD TT_ADDRESS RECORD
				$insertArray=array();
				$insertArray['tstamp']=time();
				$insertArray['company']=$updateArray['company'];
				$insertArray['name']=$updateArray['first_name'].' '.$updateArray['middle_name'].' '.$updateArray['last_name'];
				$insertArray['name']=preg_replace('/\s+/', ' ', $insertArray['name']);
				$insertArray['first_name']=$updateArray['first_name'];
				$insertArray['middle_name']=$updateArray['middle_name'];
				$insertArray['last_name']=$updateArray['last_name'];
				$insertArray['email']=$updateArray['email'];
				if (!$updateArray['street_name']) {
					// fallback for old custom checkouts
					$insertArray['street_name']=$updateArray['address'];
					$insertArray['address_number']=$updateArray['address_number'];
					$insertArray['address_ext']=$updateArray['address_ext'];
					$insertArray['address']=$insertArray['street_name'].' '.$insertArray['address_number'].($insertArray['address_ext'] ? '-'.$insertArray['address_ext'] : '');
					$insertArray['address']=preg_replace('/\s+/', ' ', $insertArray['address']);
				} else {
					$insertArray['street_name']=$updateArray['street_name'];
					$insertArray['address_number']=$updateArray['address_number'];
					$insertArray['address_ext']=$updateArray['address_ext'];
					$insertArray['address']=$updateArray['address'];
				}
				$insertArray['zip']=$updateArray['zip'];
				$insertArray['phone']=$updateArray['telephone'];
				$insertArray['mobile']=$updateArray['mobile'];
				$insertArray['city']=$updateArray['city'];
				$insertArray['country']=$updateArray['country'];
				$insertArray['gender']=$updateArray['gender'];
				$insertArray['birthday']=strtotime($updateArray['birthday']);
				if ($updateArray['gender']=='m') {
					$insertArray['title']='Mr.';
				} else {
					if ($updateArray['gender']=='f') {
						$insertArray['title']='Mrs.';
					}
				}
				$insertArray['region']=$updateArray['state'];
				$insertArray['pid']=$this->conf['fe_customer_pid'];
				$insertArray['page_uid']=$this->shop_pid;
				$insertArray['tstamp']=time();
				$insertArray['tx_multishop_address_type']='billing';
				$insertArray['tx_multishop_default']=1;
				$insertArray['tx_multishop_customer_id']=$customer_id;
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPostProc'])) {
					$params=array(
						'uid'=>$customer_id
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPostProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				// customer shipping/payment method mapping
				if ($customer_id && $this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
					// shipping/payment methods
					$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_customers_method_mappings', 'customers_id=\''.$customer_id.'\'');
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if (is_array($this->post['payment_method']) and count($this->post['payment_method'])) {
						foreach ($this->post['payment_method'] as $payment_method_id=>$value) {
							$updateArray=array();
							$updateArray['customers_id']=$customer_id;
							$updateArray['method_id']=$payment_method_id;
							$updateArray['type']='payment';
							$updateArray['negate']=$value;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
					if (is_array($this->post['shipping_method']) and count($this->post['shipping_method'])) {
						foreach ($this->post['shipping_method'] as $shipping_method_id=>$value) {
							$updateArray=array();
							$updateArray['customers_id']=$customer_id;
							$updateArray['method_id']=$shipping_method_id;
							$updateArray['type']='shipping';
							$updateArray['negate']=$value;
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_customers_method_mappings', $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
					// shipping/payment methods eof
				}
			}
		}
		if (!count($erno)) {
			if ($this->post['tx_multishop_pi1']['referrer']) {
				header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
				exit();
			} else {
				header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers', 1));
				exit();
			}
		}
	}
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_edit_customer_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_edit_customer_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/admin_edit_customer.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['details']=$this->cObj->getSubpart($subparts['template'], '###DETAILS###');

// load enabled countries to array
$str2="SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
//$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
$enabled_countries=array();
while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
	$enabled_countries[]=$row2;
}
$regex="/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
$regex_for_character="/[^0-9]$/";
if (!$this->post && is_numeric($this->get['tx_multishop_pi1']['cid'])) {
	$user=mslib_fe::getUser($this->get['tx_multishop_pi1']['cid']);
	$this->post=$user;
}
$head='';
$head.='
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var validate=jQuery(\'#edit_customer\').h5Validate();
		$("#birthday_visitor").datepicker({
			dateFormat: "'.$this->pi_getLL('locale_date_format_js', 'm/d/Y').'",
			altField: "#birthday",
			altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "'.(date("Y")-100).':'.date("Y").'"
			});
		$("#delivery_birthday_visitor").datepicker({
			dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
			altField: "#delivery_birthday",
			altFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			yearRange: "'.(date("Y")-100).':'.date("Y").'"
		});
		var originalLeave = $.fn.popover.Constructor.prototype.leave;
		$.fn.popover.Constructor.prototype.leave = function(obj){
		  var self = obj instanceof this.constructor ? obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data(\'bs.\' + this.type)
		  var container, timeout;
		  originalLeave.call(this, obj);
		  if(obj.currentTarget) {
			container = $(obj.currentTarget).siblings(\'.popover\')
			timeout = self.timeout;
			container.one(\'mouseenter\', function(){
			  //We entered the actual popover – call off the dogs
			  clearTimeout(timeout);
			  //Let\'s monitor popover content instead
			  container.one(\'mouseleave\', function(){
				  $.fn.popover.Constructor.prototype.leave.call(self, self);
				  $(".popover-link").popover("hide");
			  });
			})
		  }
		};
		$(".popover-link").popover({
			position: "down",
			placement: \'bottom\',
			html: true,
			trigger:"hover",
			delay: {show: 20, hide: 200}
		});
		var tooltip_is_shown=\'\';
		$(\'.popover-link\').on(\'show.bs.popover, mouseover\', function () {
			var that=$(this);
			//$(".popover").remove();
			//$(".popover-link").popover(\'hide\');
			var orders_id=$(this).attr(\'rel\');
			//if (tooltip_is_shown != orders_id) {
				tooltip_is_shown=orders_id;
				$.ajax({
					type:   "POST",
					url:    \''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=getAdminOrdersListingDetails&').'\',
					data:   \'tx_multishop_pi1[orders_id]=\'+orders_id,
					dataType: "json",
					success: function(data) {
						if (data.content!="") {
							that.next().html(\'<div class="arrow"></div>\' + data.title + data.content);
							//that.next().popover("show");
							//$(that).popover(\'show\');
						} else {
							$(".popover").remove();
						}
					}
				});
			//}
		});
	}); //end of first load
</script>';
$GLOBALS['TSFE']->additionalHeaderData[]=$head;
$head='';
if (is_array($erno) and count($erno)>0) {
	$content.='<div class="alert alert-danger">';
	$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
	$content.='<li class="item-error" style="display:none"></li>';
	foreach ($erno as $item) {
		$content.='<li class="item-error">'.$item.'</li>';
	}
	$content.='</ul>';
	$content.='</div>';
} else {
	$content.='<div class="alert alert-danger" style="display:none">';
	$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
	//$content.='<li class="item-error" style="display:none"></li>';
	$content.='</ul></div>';
}
// load countries
$countries_input='';
if (count($enabled_countries)==1) {
	$countries_input='<input name="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
	$countries_input.='<input name="delivery_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
} else {
	$billing_countries_option=array();
	$delivery_countries_option=array();
	foreach ($enabled_countries as $country) {
		$cn_localized_name=htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']));
		if ($this->get['action']=='add_customer') {
			$billing_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($this->tta_shop_info['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
		} else {
			$billing_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($this->post['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
		}
		$delivery_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.(($this->post['delivery_country']==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
	}
	ksort($billing_countries_option);
	ksort($delivery_countries_option);
	$tmpcontent_con=implode("\n", $billing_countries_option);
	$tmpcontent_con_delivery=implode("\n", $delivery_countries_option);
	if ($tmpcontent_con) {
		$countries_input='
		<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'<span class="text-danger">*</span></label>
		<select name="country" id="country" class="country" required="required" data-h5-errorid="invalid-country" title="'.$this->pi_getLL('country_is_required').'">
		<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
		'.$tmpcontent_con.'
		</select>
		<div id="invalid-country" class="error-space" style="display:none"></div>';
	}
}
// country eof
// fe_user image
$images_tab_block='';
$images_tab_block.='
<div class="account-field" id="msEditProductInputImage">
	<label for="products_image" class="width-fw">'.$this->pi_getLL('admin_image').'</label>
	<div id="fe_user_image">
		<noscript>
			<input name="fe_user_image" type="file" />
		</noscript>
	</div>
';
if ($this->post['image']) {
	$temp_file=$this->DOCUMENT_ROOT.'uploads/pics/'.$this->post['image'];
	$size=getimagesize($temp_file);
	if ($size[0]>150) {
		$size[0]=150;
	}
	$images_tab_block.='
	<div class="fe_user_image">
		<img src="uploads/pics/'.$this->post['image'].'" width="'.$size[0].'" />
	</div>
	';
}
$images_tab_block.='

	<input name="tx_multishop_pi1[image]" id="ajax_fe_user_image" type="hidden" value="" />';
// todo: question from Bas: what is edit_product code doing in edit_customer
if ($_REQUEST['action']=='edit_product' and $this->post['image']) {
	$images_tab_block.='<img src="'.mslib_befe::getImagePath($this->post['image'], 'products', '50').'">';
	$images_tab_block.=' <a href="'.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=admin_ajax&cid='.$_REQUEST['cid'].'&pid='.$_REQUEST['pid'].'&action=edit_product&delete_image=products_image').'" onclick="return confirm(\''.addslashes($this->pi_getLL('admin_label_js_are_you_sure')).'\')"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/icons/delete2.png" border="0" alt="'.$this->pi_getLL('admin_delete_image').'"></a>';
}
$images_tab_block.='</div>';
$images_tab_block.='
<script>
jQuery(document).ready(function($) {
	var uploader = new qq.FileUploader({
		element: document.getElementById(\'fe_user_image'.'\'),
		action: \''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=admin_ajax_upload&tx_multishop_pi1[uid]='.$user['uid']).'\',
		params: {
			file_type: \'fe_user_image'.'\'
		},
		template: \'<div class="qq-uploader">\' +
				  \'<div class="qq-upload-drop-area"><span>'.addslashes(htmlspecialchars($this->pi_getLL('admin_label_drop_files_here_to_upload'))).'</span></div>\' +
				  \'<div class="btn btn-primary btn-sm qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
				  \'<ul class="qq-upload-list"></ul>\' +
				  \'</div>\',
		onComplete: function(id, fileName, responseJSON){
			var filenameServer = responseJSON[\'filename\'];
			$("#ajax_fe_user_image").val(filenameServer);
		},
		debug: false
	});
	$("select").select2();
});
</script>';
// now lets load the users
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
if (is_array($groups) and count($groups)) {
	$customer_groups_input.='<div class="form-group multiselect_horizontal"><label>'.$this->pi_getLL('member_of').'</label><select id="groups" class="multiselect" multiple="multiple" name="tx_multishop_pi1[groups][]">'."\n";
	if ($erno) {
		$this->post['usergroup']=implode(",", $this->post['tx_multishop_pi1']['groups']);
	}
	foreach ($groups as $group) {
		$customer_groups_input.='<option value="'.$group['uid'].'"'.(mslib_fe::inUserGroup($group['uid'], $this->post['usergroup']) ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
	}
	$customer_groups_input.='</select></div>'."\n";
}
$login_as_this_user_link='';
if ($this->get['tx_multishop_pi1']['cid']) {
	$login_as_this_user_link='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers&login_as_customer=1&customer_id='.$this->get['tx_multishop_pi1']['cid']).'" target="_parent" class="btn btn-success">'.$this->pi_getLL('login_as_user').'</a>';
}
$subpartArray=array();
$subpartArray['###VALUE_REFERRER###']='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
}
// global fields
// VAT ID
$vat_input_block='<label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.ucfirst($this->pi_getLL('vat_id', 'VAT ID')).'</label>
<input type="text" name="tx_multishop_vat_id" class="form-control tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.htmlspecialchars($this->post['tx_multishop_vat_id']).'"/>';
//COC ID
$coc_input_block='<label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">'.ucfirst($this->pi_getLL('coc_id', 'KvK ID')).'</label>
<input type="text" name="tx_multishop_coc_id" class="form-control tx_multishop_coc_id" id="tx_multishop_coc_id" value="'.htmlspecialchars($this->post['tx_multishop_coc_id']).'"/>';
$subpartArray['###INPUT_VAT_ID###']=$vat_input_block;
$subpartArray['###INPUT_COC_ID###']=$coc_input_block;
$subpartArray['###LABEL_IMAGE###']=ucfirst($this->pi_getLL('image'));
$subpartArray['###VALUE_IMAGE###']=$images_tab_block;
$subpartArray['###CUSTOM_MARKER_BELOW_IMAGE_FORM_FIELD###']='';
$subpartArray['###LABEL_BUTTON_ADMIN_CANCEL###']=$this->pi_getLL('admin_cancel');
$subpartArray['###LINK_BUTTON_CANCEL###']=$subpartArray['###VALUE_REFERRER###'];
$subpartArray['###LABEL_BUTTON_ADMIN_SAVE###']=$this->pi_getLL('admin_save');
$subpartArray['###CUSTOMER_FORM_HEADING###']=$this->pi_getLL('admin_label_tabs_edit_customer');
$subpartArray['###MASTER_SHOP###']='';

$subpartArray['###CUSTOMER_EDIT_FORM_URL###']=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_customer&action='.$_REQUEST['action'].'&tx_multishop_pi1[cid]='.$_REQUEST['tx_multishop_pi1']['cid']);
// customer to shipping/payment method mapping
$shipping_payment_method='';
if ($this->ms['MODULES']['CUSTOMER_EDIT_METHOD_FILTER']) {
	$payment_methods=mslib_fe::loadPaymentMethods();
	// loading shipping methods eof
	$shipping_methods=mslib_fe::loadShippingMethods();
	if (count($payment_methods) or count($shipping_methods)) {
		// the value is are the negate value
		// negate 1 mean the shipping/payment are excluded
		$shipping_payment_method.='
<div class="form-horizontal">
						<div class="div_products_mappings toggle_advanced_option" id="msEditProductInputPaymentMethod">
							<label class="control-label col-md-2">'.$this->pi_getLL('admin_mapped_methods').'</label>
							<div class="col-md-10">
							<div class="innerbox_methods">
								<div class="innerbox_payment_methods">
									<p class="form-control-static"><strong>'.$this->pi_getLL('admin_payment_methods').'</strong></p>
									';
		// load mapped ids
		$method_mappings=array();
		if ($this->get['tx_multishop_pi1']['cid']) {
			$method_mappings=mslib_befe::getMethodsByCustomer($this->get['tx_multishop_pi1']['cid']);
		}
		$tr_type='';
		if (count($payment_methods)) {
			foreach ($payment_methods as $code=>$item) {
				if (!$tr_type or $tr_type=='even') {
					$tr_type='odd';
				} else {
					$tr_type='even';
				}
				$count++;
				$shipping_payment_method.='<div class="form-group" id="multishop_payment_method_'.$item['id'].'"><label class="control-label col-md-4">'.$item['name'].'</label><div class="col-md-8">';
				if ($price_wrap) {
					$tmpcontent.=$price_wrap;
				}
				$shipping_payment_method.='<div class="checkbox checkbox-success checkbox-inline"><input name="payment_method['.htmlspecialchars($item['id']).']" class="payment_method_cb" id="enable_payment_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="0"'.((is_array($method_mappings['payment']) && in_array($item['id'], $method_mappings['payment']) && !$method_mappings['payment']['method_data'][$item['id']]['negate']) ? ' checked' : '').' /><label for="enable_payment_method_'.$item['id'].'">'.$this->pi_getLL('enable').'</label></div>';
				$shipping_payment_method.='<div class="checkbox checkbox-success checkbox-inline"><input name="payment_method['.htmlspecialchars($item['id']).']" class="payment_method_cb" id="disable_payment_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="1"'.((is_array($method_mappings['payment']) && in_array($item['id'], $method_mappings['payment']) && $method_mappings['payment']['method_data'][$item['id']]['negate']>0) ? ' checked' : '').' /><label for="disable_payment_method_'.$item['id'].'">'.$this->pi_getLL('disable').'</label></div>';
				$shipping_payment_method.='</div></div>';
			}
		}
		$shipping_payment_method.='
								</div>
								</div>
								<div class="innerbox_shipping_methods" id="msEditProductInputShippingMethod">
									<p class="form-control-static"><strong>'.$this->pi_getLL('admin_shipping_methods').'</strong></p>
							 		';
		$count=0;
		$tr_type='';
		if (count($shipping_methods)) {
			foreach ($shipping_methods as $code=>$item) {
				$count++;
				$shipping_payment_method.='<div class="form-group" id="multishop_shipping_method"><label class="control-label col-md-4">'.$item['name'].'</label><div class="col-md-8">';
				if ($price_wrap) {
					$shipping_payment_method.=$price_wrap;
				}
				$shipping_payment_method.='<div class="checkbox checkbox-success checkbox-inline"><input name="shipping_method['.htmlspecialchars($item['id']).']" class="shipping_method_cb" id="enable_shipping_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="0"'.((is_array($method_mappings['shipping']) && in_array($item['id'], $method_mappings['shipping']) && !$method_mappings['shipping']['method_data'][$item['id']]['negate']) ? ' checked' : '').'  /><label for="enable_shipping_method_'.$item['id'].'">'.$this->pi_getLL('enable').'</label></div>';
				$shipping_payment_method.='<div class="checkbox checkbox-success checkbox-inline"><input name="shipping_method['.htmlspecialchars($item['id']).']" class="shipping_method_cb" id="disable_shipping_method_'.$item['id'].'" type="checkbox" rel="'.$item['id'].'" value="1"'.((is_array($method_mappings['shipping']) && in_array($item['id'], $method_mappings['shipping']) && $method_mappings['shipping']['method_data'][$item['id']]['negate']>0) ? ' checked' : '').'  /><label for="disable_shipping_method_'.$item['id'].'">'.$this->pi_getLL('disable').'</label></div>';
				$shipping_payment_method.='</div></div>';
			}
		}
		$shipping_payment_method.='

								</div>
							</div>
						</div></div>';
	}
}
switch ($_REQUEST['action']) {
	case 'edit_customer':
		if (is_numeric($user['uid']) && $user['uid']>0) {
			$subpartArray['###LABEL_USERNAME###']=ucfirst($this->pi_getLL('username')).'<span class="text-danger">*</span>';
			if ($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY']>0 || !isset($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY'])) {
				$subpartArray['###USERNAME_READONLY###']=(($this->get['action']=='edit_customer' && $this->get['tx_multishop_pi1']['cid']>0) ? 'readonly="readonly"' : '');
			} else {
				$subpartArray['###USERNAME_READONLY###']='';
			}
			$subpartArray['###VALUE_USERNAME###']=htmlspecialchars($this->post['username']);
			$subpartArray['###LABEL_PASSWORD###']=ucfirst($this->pi_getLL('password'));
			if ($this->masterShop) {
				$multishop_content_objects=mslib_fe::getActiveShop();
				if (count($multishop_content_objects)>1) {
					$counter=0;
					$total=count($multishop_content_objects);
					$selectContent.='<select name="page_uid"><option value="">'.ucfirst($this->pi_getLL('choose')).'</option>'."\n";
					foreach ($multishop_content_objects as $pageinfo) {
						$selectContent.='<option value="'.$pageinfo['uid'].'"'.($pageinfo['uid']==$this->post['page_uid'] ? ' selected' : '').'>'.htmlspecialchars($pageinfo['title']).'</option>';
						$counter++;
					}
					$selectContent.='</select>'."\n";
					if ($selectContent) {
						$subpartArray['###MASTER_SHOP###']='
						<div class="account-field">
							<label for="store" id="account-store">'.$this->pi_getLL('store').'</label>
							'.$selectContent.'
						</div>
					';
					}
				}
			}
			$subpartArray['###VALUE_PASSWORD###']='';
			$subpartArray['###LABEL_GENDER###']=ucfirst($this->pi_getLL('title'));
			$subpartArray['###GENDER_MR_CHECKED###']=(($this->post['gender']=='0') ? 'checked="checked"' : '');
			$subpartArray['###LABEL_GENDER_MR###']=ucfirst($this->pi_getLL('mr'));
			$subpartArray['###GENDER_MRS_CHECKED###']=(($this->post['gender']=='1') ? 'checked="checked"' : '');
			$subpartArray['###LABEL_GENDER_MRS###']=ucfirst($this->pi_getLL('mrs'));
			$subpartArray['###LABEL_FIRSTNAME###']=ucfirst($this->pi_getLL('first_name'));
			$subpartArray['###VALUE_FIRSTNAME###']=htmlspecialchars($this->post['first_name']);
			$subpartArray['###LABEL_MIDDLENAME###']=ucfirst($this->pi_getLL('middle_name'));
			$subpartArray['###VALUE_MIDDLENAME###']=htmlspecialchars($this->post['middle_name']);
			$subpartArray['###LABEL_LASTNAME###']=ucfirst($this->pi_getLL('last_name'));
			$subpartArray['###VALUE_LASTNAME###']=htmlspecialchars($this->post['last_name']);
			//
			$company_validation='';
			$subpartArray['###LABEL_COMPANY###']=ucfirst($this->pi_getLL('company'));
			if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY']) {
				$subpartArray['###LABEL_COMPANY###'].='*';
				$company_validation=' required="required" data-h5-errorid="invalid-company" title="'.$this->pi_getLL('company_is_required').'"';
			}
			$subpartArray['###COMPANY_VALIDATION###']=$company_validation;
			$subpartArray['###VALUE_COMPANY###']=htmlspecialchars($this->post['company']);
			//
			$subpartArray['###LABEL_STREET_ADDRESS###']=ucfirst($this->pi_getLL('street_address'));
			$subpartArray['###VALUE_STREET_ADDRESS###']=htmlspecialchars($this->post['street_name']);
			$subpartArray['###LABEL_STREET_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number'));
			$subpartArray['###VALUE_STREET_ADDRESS_NUMBER###']=htmlspecialchars($this->post['address_number']);
			$subpartArray['###LABEL_ADDRESS_EXTENTION###']=ucfirst($this->pi_getLL('address_extension'));
			$subpartArray['###VALUE_ADDRESS_EXTENTION###']=htmlspecialchars($this->post['address_ext']);
			$subpartArray['###LABEL_POSTCODE###']=ucfirst($this->pi_getLL('zip'));
			$subpartArray['###VALUE_POSTCODE###']=htmlspecialchars($this->post['zip']);
			$subpartArray['###LABEL_CITY###']=ucfirst($this->pi_getLL('city'));
			$subpartArray['###VALUE_CITY###']=htmlspecialchars($this->post['city']);
			$subpartArray['###COUNTRIES_INPUT###']=$countries_input;
			$subpartArray['###LABEL_EMAIL###']=ucfirst($this->pi_getLL('e-mail_address'));
			$subpartArray['###VALUE_EMAIL###']=htmlspecialchars($this->post['email']);
			$subpartArray['###LABEL_WEBSITE###']=ucfirst($this->pi_getLL('website'));
			$subpartArray['###VALUE_WEBSITE###']=htmlspecialchars($this->post['www']);
			$subpartArray['###LABEL_TELEPHONE###']=ucfirst($this->pi_getLL('telephone'));//.($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] ? '<span class="text-danger">*</span>' : '');
			$subpartArray['###VALUE_TELEPHONE###']=htmlspecialchars($this->post['telephone']);
			$subpartArray['###LABEL_MOBILE###']=ucfirst($this->pi_getLL('mobile'));
			$subpartArray['###VALUE_MOBILE###']=htmlspecialchars($this->post['mobile']);
			$subpartArray['###LABEL_BIRTHDATE###']=ucfirst($this->pi_getLL('birthday'));
			$subpartArray['###VALUE_VISIBLE_BIRTHDATE###']=($this->post['date_of_birth'] ? htmlspecialchars(strftime("%x", $this->post['date_of_birth'])) : '');
			$subpartArray['###VALUE_HIDDEN_BIRTHDATE###']=($this->post['date_of_birth'] ? htmlspecialchars(strftime("%F", $this->post['date_of_birth'])) : '');
			$subpartArray['###LABEL_DISCOUNT###']=ucfirst($this->pi_getLL('discount'));
			$subpartArray['###VALUE_DISCOUNT###']=($this->post['tx_multishop_discount']>0 ? htmlspecialchars($this->post['tx_multishop_discount']) : '');
			$subpartArray['###LABEL_PAYMENT_CONDITION###']=ucfirst($this->pi_getLL('payment_condition'));
			$subpartArray['###VALUE_PAYMENT_CONDITION###']=($this->post['tx_multishop_payment_condition']>0 ? htmlspecialchars($this->post['tx_multishop_payment_condition']) : '');
			$subpartArray['###CUSTOMER_GROUPS_INPUT###']=$customer_groups_input;
			$subpartArray['###VALUE_CUSTOMER_ID###']=$this->get['tx_multishop_pi1']['cid'];
			if ($_GET['action']=='edit_customer') {
				$subpartArray['###LABEL_BUTTON_SAVE###']=ucfirst($this->pi_getLL('update_account'));
			} else {
				$subpartArray['###LABEL_BUTTON_SAVE###']=ucfirst($this->pi_getLL('save'));
			}
			$subpartArray['###LOGIN_AS_THIS_USER_LINK###']=$login_as_this_user_link;
			$customer_details='';
			$markerArray=array();
			if ($this->post['image']) {
				$markerArray['CUSTOMER_IMAGE']='<div class="msAdminFeUserImage"><img src="uploads/pics/'.$this->post['image'].'" width="'.$size[0].'" /></div>';
			} else {
				$markerArray['CUSTOMER_IMAGE']='';
			}
			$customer_billing_address=mslib_fe::getFeUserTTaddressDetails($this->get['tx_multishop_pi1']['cid']);
			$customer_delivery_address=mslib_fe::getFeUserTTaddressDetails($this->get['tx_multishop_pi1']['cid'], 'delivery');
			if ($customer_billing_address['name'] && $customer_billing_address['phone'] && $customer_billing_address['email']) {
				$fullname=$customer_billing_address['name'];
				$telephone=$customer_billing_address['phone'];
				$email_address=$customer_billing_address['email'];
			} else {
				$fullname=$this->post['name'];
				$telephone=$this->post['telephone'];
				$email_address=$this->post['email'];
			}
			$company_name='';
			if ($customer_billing_address['address'] && $customer_billing_address['zip'] && $customer_billing_address['city']) {
				$company_name=$customer_billing_address['company'];
				$billing_street_address=$customer_billing_address['address'];
				$billing_postcode=$customer_billing_address['zip'].' '.$customer_billing_address['city'];
				$billing_country=ucwords(mslib_befe::strtolower($customer_billing_address['country']));
			} else {
				$company_name=$user['company'];
				$billing_street_address=$user['address'];
				$billing_postcode=$user['zip'].' '.$user['city'];
				$billing_country=ucwords(mslib_befe::strtolower($user['country']));
			}
			if ($customer_delivery_address['address'] && $customer_delivery_address['zip'] && $customer_delivery_address['city']) {
				$delivery_street_address=$customer_delivery_address['address'];
				$delivery_postcode=$customer_delivery_address['zip'].' '.$customer_delivery_address['city'];
				$delivery_country=ucwords(mslib_befe::strtolower($customer_delivery_address['country']));
			} else {
				$delivery_street_address=$user['address'];
				$delivery_postcode=$user['zip'].' '.$user['city'];
				$delivery_country=ucwords(mslib_befe::strtolower($user['country']));
			}
			$markerArray['DETAILS_COMPANY_NAME']=$company_name;
			$actionButtons=array();
			if (!$markerArray['DETAILS_COMPANY_NAME']) {
				$markerArray['DETAILS_COMPANY_NAME']=$fullname;
			}
			$markerArray['BILLING_COMPANY']='';
			if ($company_name) {
				$markerArray['BILLING_COMPANY']=$company_name.'<br/>';
			}
			$markerArray['BILLING_FULLNAME']=$fullname.'<br/>';
			$markerArray['BILLING_TELEPHONE']='';
			if ($telephone) {
				$markerArray['BILLING_TELEPHONE'].=ucfirst($this->pi_getLL('telephone')).': '.$telephone.'<br/>';
				$actionLink='callto:'.$telephone;
				$actionButtons['call']='<a href="'.$actionLink.'" class="btn btn-xs btn-default"><i class="fa fa-phone-square"></i> '.$this->pi_getLL('call').'</a>';
			}
			$markerArray['BILLING_EMAIL']='';
			if ($email_address) {
				$markerArray['BILLING_EMAIL'].=ucfirst($this->pi_getLL('e-mail_address')).': '.$email_address.'<br/>';
				$actionLink='mailto:'.$email_address;
				$actionButtons['email']='<a href="'.$actionLink.'" class="btn btn-xs btn-default"><i class="fa fa-envelope-o"></i> '.$this->pi_getLL('email').'</a>';
			}
			$address=array();
			$address[]=rawurlencode($user['address']);
			$address[]=rawurlencode($user['zip']);
			$address[]=rawurlencode($user['city']);
			$address[]=rawurlencode($user['country']);
			$actionLink='http://maps.google.com/maps?daddr='.implode('+',$address);
			$actionButtons['travel_guide']='<a href="'.$actionLink.'" rel="nofollow" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-map-marker"></i> '.$this->pi_getLL('travel_guide').'</a>';

			$markerArray['BILLING_COMPANY_ACTION_NAV']='';
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['actionButtonsBillingCompanyBoxPreProc'])) {
				$params=array(
						'actionButtons'=>&$actionButtons,
						'customer'=>&$user
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['actionButtonsBillingCompanyBoxPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eol
			if (count($actionButtons)) {
				$markerArray['BILLING_COMPANY_ACTION_NAV']='<div class="btn-group">';
				foreach ($actionButtons as $actionButton) {
					$markerArray['BILLING_COMPANY_ACTION_NAV'].=$actionButton;
				}
				$markerArray['BILLING_COMPANY_ACTION_NAV'].='</div>';
			}
			$markerArray['CUSTOMER_ID']=$this->pi_getLL('admin_customer_id').': '.$user['uid'].'<br/>';
			if ($user['crdate']>0) {
				$user['crdate']=strftime("%a. %x %X", $user['crdate']);
			} else {
				$user['crdate']='';
			}
			$markerArray['REGISTERED_DATE']=$this->pi_getLL('created').': '.$user['crdate'].'<br/>';
			if ($user['lastlogin']) {
				$user['lastlogin']=strftime("%a. %x %X", $user['lastlogin']);
			} else {
				$user['lastlogin']='-';
			}
			$markerArray['LAST_LOGIN']=$this->pi_getLL('latest_login').': '.$user['lastlogin'].'<br/>';
			$markerArray['BILLING_ADDRESS']=$billing_street_address.'<br/>'.$billing_postcode.'<br/>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $billing_country));
			$markerArray['DELIVERY_ADDRESS']=$delivery_street_address.'<br/>'.$delivery_postcode.'<br/>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $delivery_country));
			$markerArray['GOOGLE_MAPS_URL_QUERY']='//maps.google.com/maps?f=q&amp;source=s_q&amp;hl=nl&amp;geocode=&amp;q='.rawurlencode($billing_street_address).','.rawurlencode($billing_postcode).','.rawurlencode($billing_country).'&amp;z=14&amp;iwloc=A&amp;output=embed&amp;iwloc=';
			$markerArray['ADMIN_LABEL_CONTACT_INFO']=$this->pi_getLL('admin_label_contact_info');

			$markerArray['ADMIN_LABEL_BILLING_ADDRESS']=$this->pi_getLL('admin_label_billing_address');
			$markerArray['ADMIN_LABEL_DELIVERY_ADDRESS']=$this->pi_getLL('admin_label_delivery_address');

			// customers related orders listings
			$filter=array();
			$from=array();
			$having=array();
			$match=array();
			$orderby=array();
			$where=array();
			$select=array();
			$select[]='o.*';
			$filter[]='o.customer_id='.$user['uid'];
			$filter[]='o.page_uid='.$this->shop_pid;
			$orders_pageset=mslib_fe::getOrdersPageSet($filter, 0, 10000, array('orders_id desc'), $having, $select, $where, $from);
			$order_listing=$this->pi_getLL('no_orders_found');
			if ($orders_pageset['total_rows']>0) {
				$all_orders_status=mslib_fe::getAllOrderStatus($GLOBALS['TSFE']->sys_language_uid);
				$order_listing='<div class="table-responsive">
				<table id="product_import_table" class="table table-striped table-bordered no-mb msadmin_orders_listing">
					<thead><tr>
						<th width="50" align="right" class="cellID">'.$this->pi_getLL('orders_id').'</th>
						<th width="110" class="cellDate">'.$this->pi_getLL('order_date').'</th>
						<th width="50" class="cellPrice">'.$this->pi_getLL('amount').'</th>
						<th width="50" class="cell_shipping_method">'.$this->pi_getLL('shipping_method').'</th>
						<th width="50" class="cell_payment_method">'.$this->pi_getLL('payment_method').'</th>
						<th width="100" class="cell_status">'.$this->pi_getLL('order_status').'</th>
						<th width="110" class="cellDate">'.$this->pi_getLL('modified_on', 'Modified on').'</th>
						<th width="50" class="cellStatus">'.$this->pi_getLL('admin_paid').'</th>
					</tr></thead><tbody>';
				$tr_type='odd';
				foreach ($orders_pageset['orders'] as $order) {
					if (!isset($tr_type) || $tr_type=='odd') {
						$tr_type='even';
					} else {
						$tr_type='odd';
					}
					if ($order['is_proposal']>0) {
						$order_edit_url=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order&tx_multishop_pi1[is_proposal]=1');
					} else {
						$order_edit_url=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_order&orders_id='.$order['orders_id'].'&action=edit_order');
					}
					if (!$order['paid']) {
						$paid_status='<span class="admin_status_red" alt="'.$this->pi_getLL('has_not_been_paid').'" title="'.$this->pi_getLL('has_not_been_paid').'"></span>';
					} else {
						$paid_status='<span class="admin_status_green" alt="'.$this->pi_getLL('has_been_paid').'" title="'.$this->pi_getLL('has_been_paid').'"></span>';
					}
					$order_listing.='<tr>
							<td class="cellID"><a href="'.$order_edit_url.'" title="'.htmlspecialchars($this->pi_getLL('loading')).'" title="Loading" class="popover-link" rel="'.$order['orders_id'].'">'.$order['orders_id'].'</a></td>
							<td class="cellDate">'.strftime("%a. %x %X", $order['crdate']).'</td>
							<td class="cellPrice">'.mslib_fe::amount2Cents($order['grand_total'], 0).'</td>
							<td nowrap>'.$order['shipping_method_label'].'</td>
							<td nowrap>'.$order['payment_method_label'].'</td>
							<td align="left" nowrap>'.$all_orders_status[$order['status']]['name'].'</td>
							<td class="cellDate">'.($order['status_last_modified'] ? strftime("%a. %x %X", $order['status_last_modified']) : '').'</td>
							<td class="cellStatus">'.$paid_status.'</td>
						</tr>';
				}
				$order_listing.='</tbody><tfoot><tr>
						<th width="50" class="cellID">'.$this->pi_getLL('orders_id').'</th>
						<th width="110" class="cellDate">'.$this->pi_getLL('order_date').'</th>
						<th width="50" class="cellPrice">'.$this->pi_getLL('amount').'</th>
						<th width="50" class="cell_shipping_method">'.$this->pi_getLL('shipping_method').'</th>
						<th width="50" class="cell_payment_method">'.$this->pi_getLL('payment_method').'</th>
						<th width="100" class="cell_status">'.$this->pi_getLL('order_status').'</th>
						<th width="110" class="cellDate">'.$this->pi_getLL('modified_on', 'Modified on').'</th>
						<th width="50" class="cellStatus">'.$this->pi_getLL('admin_paid').'</th>
					</tr></tfoot>
				</table>
			</div>';
			}
			$customer_related_orders_listing='<div id="orders_details">';
			$customer_related_orders_listing.='<div class="panel panel-default">';
			$customer_related_orders_listing.='<div class="panel-heading"><h3>'.$this->pi_getLL('orders').'</h3></div>';
			$customer_related_orders_listing.='<div class="panel-body"><fieldset>';
			$customer_related_orders_listing.=$order_listing;
			$customer_related_orders_listing.='</fieldset></div>';
			$customer_related_orders_listing.='</div></div>';
			$markerArray['CUSTOMER_RELATED_ORDERS_LISTING']=$customer_related_orders_listing;
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerDashBoardMainTabPreProc'])) {
				$params=array(
						'markerArray'=>&$markerArray,
						'user'=>&$user
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerDashBoardMainTabPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom page hook that can be controlled by third-party plugin eof
			$customer_details.=$this->cObj->substituteMarkerArray($subparts['details'], $markerArray, '###|###');
			$subpartArray['###DETAILS_TAB###']='<li role="presentation"><a href="#view_customer" aria-controls="profile" role="tab" data-toggle="tab">'.$this->pi_getLL('admin_label_tabs_details').'</a></li>';
			$subpartArray['###DETAILS###']=$customer_details;
			$subpartArray['###INPUT_EDIT_SHIPPING_AND_PAYMENT_METHOD###']=$shipping_payment_method;
		}
		break;
	case 'add_customer':
	default:
		if ($this->post['gender']=='1') {
			$mr_checked='';
			$mrs_checked='checked="checked"';
		} else {
			$mr_checked='checked="checked"';
			$mrs_checked='';
		}
		$subpartArray['###LABEL_USERNAME###']=ucfirst($this->pi_getLL('username')).'<span class="text-danger">*</span>';
		if ($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY']>0 || !isset($this->ms['MODULES']['ADMIN_EDIT_CUSTOMER_USERNAME_READONLY'])) {
			$subpartArray['###USERNAME_READONLY###']=($this->get['action']=='edit_customer' ? 'readonly="readonly"' : '');
		} else {
			$subpartArray['###USERNAME_READONLY###']='';
		}
		$subpartArray['###VALUE_USERNAME###']=htmlspecialchars($this->post['username']);
		$subpartArray['###VALUE_PASSWORD###']=htmlspecialchars($this->post['password']);
		$subpartArray['###LABEL_PASSWORD###']=ucfirst($this->pi_getLL('password'));
		$subpartArray['###LABEL_GENDER###']=ucfirst($this->pi_getLL('title'));
		$subpartArray['###GENDER_MR_CHECKED###']=$mr_checked;
		$subpartArray['###LABEL_GENDER_MR###']=ucfirst($this->pi_getLL('mr'));
		$subpartArray['###GENDER_MRS_CHECKED###']=$mrs_checked;
		$subpartArray['###LABEL_GENDER_MRS###']=ucfirst($this->pi_getLL('mrs'));
		$subpartArray['###LABEL_FIRSTNAME###']=ucfirst($this->pi_getLL('first_name'));
		$subpartArray['###VALUE_FIRSTNAME###']=htmlspecialchars($this->post['first_name']);
		$subpartArray['###LABEL_MIDDLENAME###']=ucfirst($this->pi_getLL('middle_name'));
		$subpartArray['###VALUE_MIDDLENAME###']=htmlspecialchars($this->post['middle_name']);
		$subpartArray['###LABEL_LASTNAME###']=ucfirst($this->pi_getLL('last_name'));
		$subpartArray['###VALUE_LASTNAME###']=htmlspecialchars($this->post['last_name']);
		//
		$company_validation='';
		$subpartArray['###LABEL_COMPANY###']=ucfirst($this->pi_getLL('company'));
		if ($this->ms['MODULES']['CHECKOUT_REQUIRED_COMPANY']) {
			//$subpartArray['###LABEL_COMPANY###'].='<span class="text-danger">*</span>';
			$company_validation=' required="required" data-h5-errorid="invalid-company" title="'.$this->pi_getLL('company_is_required').'"';
		}
		$subpartArray['###COMPANY_VALIDATION###']=$company_validation;
		$subpartArray['###VALUE_COMPANY###']=htmlspecialchars($this->post['company']);
		//
		$subpartArray['###LABEL_STREET_ADDRESS###']=ucfirst($this->pi_getLL('street_address'));
		$subpartArray['###VALUE_STREET_ADDRESS###']=htmlspecialchars($this->post['street_name']);
		$subpartArray['###LABEL_STREET_ADDRESS_NUMBER###']=ucfirst($this->pi_getLL('street_address_number'));
		$subpartArray['###VALUE_STREET_ADDRESS_NUMBER###']=htmlspecialchars($this->post['address_number']);
		$subpartArray['###LABEL_ADDRESS_EXTENTION###']=ucfirst($this->pi_getLL('address_extension'));
		$subpartArray['###VALUE_ADDRESS_EXTENTION###']=htmlspecialchars($this->post['address_ext']);
		$subpartArray['###LABEL_POSTCODE###']=ucfirst($this->pi_getLL('zip'));
		$subpartArray['###VALUE_POSTCODE###']=htmlspecialchars($this->post['zip']);
		$subpartArray['###LABEL_CITY###']=ucfirst($this->pi_getLL('city'));
		$subpartArray['###VALUE_CITY###']=htmlspecialchars($this->post['city']);
		$subpartArray['###COUNTRIES_INPUT###']=$countries_input;
		$subpartArray['###LABEL_EMAIL###']=ucfirst($this->pi_getLL('e-mail_address'));
		$subpartArray['###VALUE_EMAIL###']=htmlspecialchars($this->post['email']);
		$subpartArray['###LABEL_WEBSITE###']=ucfirst($this->pi_getLL('website'));
		$subpartArray['###VALUE_WEBSITE###']=htmlspecialchars($this->post['www']);
		$subpartArray['###LABEL_TELEPHONE###']=ucfirst($this->pi_getLL('telephone'));//.($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'] ? '<span class="text-danger">*</span>' : '');
		$subpartArray['###VALUE_TELEPHONE###']=htmlspecialchars($this->post['telephone']);
		$subpartArray['###LABEL_MOBILE###']=ucfirst($this->pi_getLL('mobile'));
		$subpartArray['###VALUE_MOBILE###']=htmlspecialchars($this->post['mobile']);
		$subpartArray['###LABEL_BIRTHDATE###']=ucfirst($this->pi_getLL('birthday'));
		$subpartArray['###VALUE_VISIBLE_BIRTHDATE###']=($this->post['date_of_birth'] ? htmlspecialchars(strftime("%x", $this->post['date_of_birth'])) : '');
		$subpartArray['###VALUE_HIDDEN_BIRTHDATE###']=($this->post['date_of_birth'] ? htmlspecialchars(strftime("%F", $this->post['date_of_birth'])) : '');
		$subpartArray['###LABEL_DISCOUNT###']=ucfirst($this->pi_getLL('discount'));
		$subpartArray['###VALUE_DISCOUNT###']=($this->post['tx_multishop_discount']>0 ? htmlspecialchars($this->post['tx_multishop_discount']) : '');
		$subpartArray['###LABEL_PAYMENT_CONDITION###']=ucfirst($this->pi_getLL('payment_condition'));
		$subpartArray['###VALUE_PAYMENT_CONDITION###']=14;
		$subpartArray['###CUSTOMER_GROUPS_INPUT###']=$customer_groups_input;
		$subpartArray['###VALUE_CUSTOMER_ID###']='';
		$subpartArray['###LABEL_BUTTON_SAVE###']=ucfirst($this->pi_getLL('save'));
		$subpartArray['###LOGIN_AS_THIS_USER_LINK###']='';
		$subpartArray['###DETAILS_TAB###']='';
		$subpartArray['###DETAILS###']='';
		$subpartArray['###INPUT_EDIT_SHIPPING_AND_PAYMENT_METHOD###']=$shipping_payment_method;
		$subpartArray['###LABEL_PAYMENT_CONDITION###']=ucfirst($this->pi_getLL('payment_condition'));
		$subpartArray['###VALUE_PAYMENT_CONDITION###']=($this->post['tx_multishop_payment_condition']>0 ? htmlspecialchars($this->post['tx_multishop_payment_condition']) : 14);
		break;
}
// language input
$language_selectbox='';
foreach ($this->languages as $key=>$language) {
    $language['lg_iso_2']=strtolower($language['lg_iso_2']);
    if (empty($user['tx_multishop_language']) && $language['uid']===0) {
        $language_selectbox.='<option value="'.$language['lg_iso_2'].'" selected="selected">'.$language['title'].'</option>';
    } else {
        if (strtolower($user['tx_multishop_language'])==$language['lg_iso_2']) {
            $language_selectbox.='<option value="'.$language['lg_iso_2'].'" selected="selected">'.$language['title'].'</option>';
        } else {
            $language_selectbox.='<option value="'.$language['lg_iso_2'].'">'.$language['title'].'</option>';
        }
    }
}
if (!empty($language_selectbox)) {
    $language_selectbox='<select name="tx_multishop_language">'.$language_selectbox.'</select>';
}
$subpartArray['###LABEL_LANGUAGE###']=$this->pi_getLL('language');
$subpartArray['###LANGUAGE_SELECTBOX###']=$language_selectbox;
// language eol
// h5validate message
$subpartArray['###INVALID_FIRSTNAME_MESSAGE###']=$this->pi_getLL('first_name_required');
$subpartArray['###INVALID_LASTNAME_MESSAGE###']=$this->pi_getLL('surname_is_required');
$subpartArray['###INVALID_ADDRESS_MESSAGE###']=$this->pi_getLL('street_address_is_required');
$subpartArray['###INVALID_ADDRESSNUMBER_MESSAGE###']=$this->pi_getLL('street_number_is_required');
$subpartArray['###INVALID_ZIP_MESSAGE###']=$this->pi_getLL('zip_is_required');
$subpartArray['###INVALID_CITY_MESSAGE###']=$this->pi_getLL('city_is_required');
$subpartArray['###INVALID_EMAIL_MESSAGE###']=$this->pi_getLL('email_is_required');
$subpartArray['###INVALID_USERNAME_MESSAGE###']=$this->pi_getLL('username_is_required');
$subpartArray['###INVALID_PASSWORD_MESSAGE###']=$this->pi_getLL('password_is_required');
$telephone_validation='';
if ($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE']) {
	if (!$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER']) {
		$telephone_validation=' required="required" data-h5-errorid="invalid-telephone" title="'.$this->pi_getLL('telephone_is_required').'"';
	} else {
		$telephone_validation=' required="required" data-h5-errorid="invalid-telephone" title="'.$this->pi_getLL('telephone_is_required').'" pattern=".{'.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].'}"';
	}
}
$subpartArray['###TELEPHONE_VALIDATION###']=$telephone_validation;
$subpartArray['###ADMIN_LABEL_TABS_EDIT_CUSTOMER###']=$this->pi_getLL('admin_label_tabs_edit_customer');
// plugin marker place holder
if (!$this->ms['MODULES']['FIRSTNAME_AND_LASTNAME_UNREQUIRED_IN_ADMIN_CUSTOMER_PAGE']) {
	$subpartArray['###LABEL_FIRSTNAME###']=ucfirst($this->pi_getLL('first_name'));//.'<span class="text-danger">*</span>';
	$subpartArray['###LABEL_LASTNAME###']=ucfirst($this->pi_getLL('last_name'));//.'<span class="text-danger">*</span>';
	$subpartArray['###FIRSTNAME_VALIDATION###']=' required="required" data-h5-errorid="invalid-first_name" title="'.$this->pi_getLL('first_name_required').'"';
	$subpartArray['###LASTNAME_VALIDATION###']=' required="required" data-h5-errorid="invalid-last_name" title="'.$this->pi_getLL('last_name_required').'"';
} else {
	$subpartArray['###LABEL_FIRSTNAME###']=ucfirst($this->pi_getLL('first_name'));
	$subpartArray['###LABEL_LASTNAME###']=ucfirst($this->pi_getLL('last_name'));
}
$plugins_extra_tab=array();
$js_extra=array();
$plugins_extra_tab['tabs_header']=array();
$plugins_extra_tab['tabs_content']=array();
// custom page hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerTmplPreProc'])) {
	$params=array(
		'subpartArray'=>&$subpartArray,
		'user'=>&$user,
		'plugins_extra_tab'=>&$plugins_extra_tab,
		'js_extra'=>&$js_extra
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['adminEditCustomerTmplPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom page hook that can be controlled by third-party plugin eof
if (!count($plugins_extra_tab['tabs_header']) && !count($plugins_extra_tab['tabs_content'])) {
	$subpartArray['###LABEL_EXTRA_PLUGIN_TABS###']='';
	$subpartArray['###CONTENT_EXTRA_PLUGIN_TABS###']='';
} else {
	$subpartArray['###LABEL_EXTRA_PLUGIN_TABS###']=implode("\n", $plugins_extra_tab['tabs_header']);
	$subpartArray['###CONTENT_EXTRA_PLUGIN_TABS###']=implode("\n", $plugins_extra_tab['tabs_content']);
}
if (!count($js_extra['functions'])) {
	$subpartArray['###JS_FUNCTIONS_EXTRA###']='';
} else {
	$subpartArray['###JS_FUNCTIONS_EXTRA###']=implode("\n", $js_extra['functions']);
}
if (!count($js_extra['triggers'])) {
	$subpartArray['###JS_TRIGGERS_EXTRA###']='';
} else {
	$subpartArray['###JS_TRIGGERS_EXTRA###']=implode("\n", $js_extra['triggers']);
}
if ($customer_id) {
	$subpartArray['###HEADING_TITLE###']=$this->pi_getLL('admin_label_tabs_edit_customer');
} else {
	$subpartArray['###HEADING_TITLE###']=$this->pi_getLL('admin_new_customer');
}


// Instantiate admin interface object
$objRef = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface');
$objRef->init($this);
$objRef->setInterfaceKey('admin_edit_customer');

// Header buttons
$headerButtons=array();

$headingButton=array();
$headingButton['btn_class']='btn btn-primary';
$headingButton['fa_class']='fa fa-plus-circle';
$headingButton['title']=$this->pi_getLL('save');
$headingButton['href']='#';
$headingButton['attributes']='onclick="$(\'#admin_interface_form button[name=\\\'Submit\\\']\').click(); return false;"';
$headerButtons[]=$headingButton;

// Set header buttons through interface class so other plugins can adjust it
$objRef->setHeaderButtons($headerButtons);
// Get header buttons through interface class so we can render them
$interfaceHeaderButtons=$objRef->renderHeaderButtons();
// Get header buttons through interface class so we can render them
$subpartArray['###INTERFACE_HEADER_BUTTONS###']=$objRef->renderHeaderButtons();

$content.=$this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);

if ($this->get['tx_multishop_pi1']['cid']>0 && !is_numeric($user['uid'])) {
	$content=$this->pi_getLL('customer_not_found');
}
/*
if ($customer_id) {
	require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'pi1/classes/class.tx_mslib_dashboard.php');
	$mslib_dashboard=\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_dashboard');
	$mslib_dashboard->init($this);
	$mslib_dashboard->setSection('admin_edit_customer');
	$mslib_dashboard->renderWidgets();
	$content.=$mslib_dashboard->displayDashboard();
}
*/
?>