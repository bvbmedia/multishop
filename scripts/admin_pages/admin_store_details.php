<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$referrer='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$referrer=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$referrer=$_SERVER['HTTP_REFERER'];
}
if ($this->post) {
	$erno=array();
	if (!$this->post['tx_multishop_cooluri_pi1']['url']) {
	//	$erno[]='url cannot be empty';
	}
	if (!count($erno)) {
		$this->post['store_details']['name']='Store';//preg_replace('/\s+/', ' ', $this->post['store_details']['first_name'].' '.$this->post['store_details']['middle_name'].' '.$this->post['store_details']['last_name']);
		$this->post['store_details']['address']=$this->post['store_details']['street_name'].' '.$this->post['store_details']['address_number'];
		if ($user['address_ext']) {
			$this->post['store_details']['address'].='-'.$this->post['store_details']['address_ext'];
		}
		$this->post['store_details']['address']=preg_replace('/\s+/', ' ', $this->post['store_details']['address']);
		// Create/Update Company
		$updateArray=array();
		foreach ($this->post['store_details'] as $db_col => $db_value) {
			$updateArray[$db_col]=$db_value;
		}
        if($_FILES['store_details']['error']['image']==0 && $_FILES['store_details']['tmp_name']['image']) {
            @unlink($this->DOCUMENT_ROOT.'uploads/pics/'.$updateArray['image_filename']);
            $name=$this->post['store_details']['company'];
            if (!$name) {
                $name=$this->post['store_details']['first_name'] . ' ' . $this->post['store_details']['middle_name'] . ' ' . $this->post['store_details']['last_name'];
                $name=preg_replace('/\s+/', ' ', $name);
            }
            $imgtype=exif_imagetype($_FILES['store_details']['tmp_name']['image']);
            if($imgtype) {
                // valid image
                $ext=image_type_to_extension($imgtype, false);
                if($ext) {
                    $i=0;
                    $filename=mslib_fe::rewritenamein($name).'.'.$ext;
                    $target=$this->DOCUMENT_ROOT.'uploads/pics/'.$filename;
                    if(file_exists($target)) {
                        while(file_exists($target)) {
                            $filename=mslib_fe::rewritenamein($name).($i > 0 ? '-'.$i : '').'.'.$ext;
                            $target=$this->DOCUMENT_ROOT.'uploads/pics/'.$filename;
                            $i++;
                        }
                    }
                    if(move_uploaded_file($_FILES['store_details']['tmp_name']['image'], $target)) {
                        $updateArray['image_filename']=$filename;
                    }
                }
            }
        }
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'uid = '.$this->conf['tt_address_record_id_store'], $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($this->post['tx_multishop_pi1']['referrer']) {
			header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
			exit();
		} else {
			header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_store_details', 1));
			exit();
		}
	}
}
// load enabled countries to array
$str2="SELECT * from static_countries sc, tx_multishop_countries_to_zones c2z, tx_multishop_shipping_countries c where c.page_uid='".$this->showCatalogFromPage."' and sc.cn_iso_nr=c.cn_iso_nr and c2z.cn_iso_nr=sc.cn_iso_nr group by c.cn_iso_nr order by sc.cn_short_en";
//$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
$enabled_countries=array();
while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
	$enabled_countries[]=$row2;
}
if (is_array($this->tta_shop_info) && $this->tta_shop_info['tt_uid']==$this->conf['tt_address_record_id_store']) {
	$rs_store_details=$this->tta_shop_info;
	// build countries selectbox
	if (count($enabled_countries)==1) {
		$countries_input='<input name="country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		$countries_input.='<input name="delivery_country" type="hidden" value="'.mslib_befe::strtolower($enabled_countries[0]['cn_short_en']).'" />';
	} else {
		$billing_countries_option=array();
		$delivery_countries_option=array();
		foreach ($enabled_countries as $country) {
			$cn_localized_name=htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $country['cn_short_en']));
			$billing_countries_option[$cn_localized_name]='<option value="'.mslib_befe::strtolower($country['cn_short_en']).'" '.((mslib_befe::strtolower($rs_store_details['country'])==mslib_befe::strtolower($country['cn_short_en'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
		}
		ksort($billing_countries_option);
		$tmpcontent_con=implode("\n", $billing_countries_option);
		if ($tmpcontent_con) {
			$countries_input='<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'</label>
			<select name="country" id="country" class="country" autocomplete="off" style="width:349px">
			<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
			'.$tmpcontent_con.'
			</select>
			';
		}
	}
	$content.='<div class="panel panel-default"><div class="panel-heading"><h3>'.$this->pi_getLL('edit_store_address').'</h3></div><div class="panel-body">';
	if (is_array($erno) && count($erno)) {
		$content.='<div class="alert alert-danger"><h3>The following errors occurred</h3><ul>';
		foreach ($erno as $item) {
			$content.='<li>';
			$content.=$item;
			$content.='</li>';
		}
		$content.='</ul></div>';
	}
	$content.='<form method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_store_details').'" class="edit_customer" id="admin_interface_form" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-8">
				<div class="form-group">
                    <div class="row">
                        '.($rs_store_details['image_filename'] ? '
                        <div class="col-md-4">
                            <img src="'.$this->FULL_HTTP_URL.'uploads/pics/'.$rs_store_details['image_filename'].'" width="150px" />
                        </div>
                        <div class="col-md-8">
                            <label for="image" id="account-image">'.ucfirst($this->pi_getLL('admin_image')).'</label>
                            <input type="file" name="store_details[image]" class="form-control image" id="image">
                            <input type="hidden" name="store_details[image_filename]" value="'.$rs_store_details['image_filename'].'">
                        </div>
                        ' : '
                        <div class="col-md-12">
                            <label for="image" id="account-image">'.ucfirst($this->pi_getLL('admin_image')).'</label>
                            <input type="file" name="store_details[image]" class="form-control image" id="image">
                        </div>
                        ').'
                    </div>
                </div>
				<div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
                            <input type="text" name="store_details[company]" class="form-control company" id="company" value="'.$rs_store_details['company'].'">
                        </div>
                    </div>
                </div>
				<div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'</label>
                            <input type="text" name="store_details[first_name]" class="form-control first-name" id="first_name" value="'.$rs_store_details['first_name'].'">
                        </div>
                        <div class="col-md-4">
                            <label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
                            <input type="text" name="store_details[middle_name]" id="middle_name" class="form-control middle_name" value="'.$rs_store_details['middle_name'].'">
                        </div>
                        <div class="col-md-4">
                            <label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'</label>
                            <input type="text" name="store_details[last_name]" id="last_name" class="form-control last-name" value="'.$rs_store_details['last_name'].'">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'</label>
                            <input type="text" name="store_details[street_name]" id="address" class="form-control address" value="'.$rs_store_details['street_name'].'">
                        </div>
                        <div class="col-md-4">
                            <label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'</label>
                            <input type="text" name="store_details[address_number]" id="address_number" class="form-control address-number" value="'.$rs_store_details['address_number'].'">
                        </div>
                        <div class="col-md-4">
                            <label class="account-address_ext" for="address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
                            <input type="text" name="store_details[address_ext]" id="address_ext" class="form-control address_ext" value="'.$rs_store_details['address_ext'].'">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'</label>
                            <input type="text" name="store_details[zip]" id="zip" class="form-control zip ui-state-valid" value="'.$rs_store_details['zip'].'">
                        </div>
                        <div class="col-md-4">
                            <label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'</label>
                            <input type="text" name="store_details[city]" id="city" class="form-control city" value="'.$rs_store_details['city'].'">
                        </div>
                        <div class="col-md-4">
						'.$countries_input.'
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'</label>
                            <input type="text" name="store_details[phone]" id="telephone" class="form-control telephone" value="'.$rs_store_details['phone'].'">
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
                            <input type="text" name="store_details[mobile]" id="mobile" class="form-control mobile" value="'.$rs_store_details['mobile'].'">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="email" id="account-email">'.ucfirst($this->pi_getLL('email')).'</label>
                            <input type="text" name="store_details[email]" id="email" class="form-control email" value="'.$rs_store_details['email'].'">
                        </div>
                        <div class="col-md-6">
                            <label for="www" id="account-www">Website</label>
                            <input type="text" name="store_details[www]" id="www" class="form-control www" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="tx_multishop_bank_name" id="account-tx_multishop_bank_name">'.$this->pi_getLL('bank_name').'</label>
                            <input type="text" name="store_details[tx_multishop_bank_name]" class="form-control tx_multishop_bank_name" id="tx_multishop_bank_name" value="'.$rs_store_details['tx_multishop_bank_name'].'">
                        </div>
                        <div class="col-md-4">
                            <label for="tx_multishop_iban" id="account-tx_multishop_iban">'.$this->pi_getLL('iban').'</label>
                            <input type="text" name="store_details[tx_multishop_iban]" class="form-control tx_multishop_iban" id="tx_multishop_iban" value="'.$rs_store_details['tx_multishop_iban'].'">
                        </div>
                        <div class="col-md-4">
                            <label for="tx_multishop_bic" id="account-tx_multishop_bic">'.$this->pi_getLL('bic').'</label>
                            <input type="text" name="store_details[tx_multishop_bic]" class="form-control tx_multishop_bic" id="tx_multishop_bic" value="'.$rs_store_details['tx_multishop_bic'].'">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.$this->pi_getLL('vat_id2').'</label>
                            <input type="text" name="store_details[tx_multishop_vat_id]" class="form-control tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.$rs_store_details['tx_multishop_vat_id'].'">
                        </div>
                        <div class="col-md-4">
                            <label for="tx_multishop_vat_id" id="account-tx_multishop_vat_id">'.$this->pi_getLL('vat_number').'</label>
                            <input type="text" name="store_details[tx_multishop_vat_number]" class="form-control tx_multishop_vat_id" id="tx_multishop_vat_id" value="'.$rs_store_details['tx_multishop_vat_number'].'">
                        </div>
                        <div class="col-md-4">
                            <label for="tx_multishop_coc_id" id="account-tx_multishop_coc_id">'.$this->pi_getLL('coc_id').'</label>
                            <input type="text" name="store_details[tx_multishop_coc_id]" class="form-control tx_multishop_coc_id" id="tx_multishop_coc_id" value="'.$rs_store_details['tx_multishop_coc_id'].'">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="tx_multishop_paypal_account" id="account-tx_multishop_paypal_account">'.$this->pi_getLL('paypal_account').'</label>
                            <input type="text" name="store_details[tx_multishop_paypal_account]" class="form-control tx_multishop_paypal_account" id="tx_multishop_paypal_account" value="'.$rs_store_details['tx_multishop_paypal_account'].'">
                        </div>
                    </div>
                </div>
			</div>
		</div>
		<div id="bottom-navigation">
			<span class="msFrontButton continueState arrowRight arrowPosLeft pull-right" id="submit"><input type="submit" class=" btn btn-success" value="'.ucfirst($this->pi_getLL('update')).'"/></span>
		</div>
	</form>
	</div>
	<script>
	jQuery(document).ready(function($) {
			$("select").select2();
	});
	</script>
	';
} else {
	$content .= $this->pi_getLL('store_address_record_not_found');
}
?>