<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='0%';
// first check group discount
if($GLOBALS["TSFE"]->fe_user->user['uid']) {
	$discount_percentage=mslib_fe::getUserGroupDiscount($GLOBALS["TSFE"]->fe_user->user['uid']);
	if($discount_percentage) {
		$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		$cart['coupon_code']='';
		$cart['discount']=$discount_percentage;
		$cart['discount_type']='percentage';
		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
		$content=number_format($discount_percentage).'%';
	}
}
//if(!$discount_percentage)
if($_POST['code']) {
	$code=mslib_fe::RemoveXSS(t3lib_div::strtolower($_POST['code']));
	$time=time();
	$str="SELECT * from tx_multishop_coupons where code = '".addslashes($code)."' and status = 1 and (startdate <= '".$time."' and enddate >= '".$time."')";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	if($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		if($row['max_usage'] > 0) {
			if($row['times_used'] >= $row['max_usage']) {
				$content="0%";
				echo $content;
				exit();
			}
		}
		$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		switch($row['discount_type']) {
			case 'percentage':
				$content=number_format($row['discount']).'%';
				break;
			case 'price':
				$total_price=mslib_fe::countCartTotalPrice(1, 1);
				if($total_price < $row['discount']) {
					$row['discount']=$total_price;
				}
				$content=mslib_fe::amount2Cents($row['discount']);
				break;
		}
		$cart['coupon_code']=$code;
		$cart['discount']=$row['discount'];
		$cart['discount_type']=$row['discount_type'];
		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	} else {
		$cart=$GLOBALS['TSFE']->fe_user->getKey('ses', $this->cart_page_uid);
		$cart['coupon_code']='';
		$cart['discount']='';
		$cart['discount_type']='';
		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->cart_page_uid, $cart);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
		$content="0%";
	}
}
echo $content;
exit();
?>