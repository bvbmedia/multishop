<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($_REQUEST['skeyword']) {
	$this->get['tx_multishop_pi1']['zip']=$_REQUEST['skeyword'];
	$this->get['tx_multishop_pi1']['zip']=trim($this->get['tx_multishop_pi1']['zip']);
	$this->get['tx_multishop_pi1']['zip']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['zip'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['zip']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['zip'], true);
	$this->get['tx_multishop_pi1']['zip']=mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['zip']);
}
$title='Store Locator';
$content.='<div class="main-heading"><h2>'.$title.'</h2></div>';
$content.='
<form action="'.mslib_fe::typolink().'" method="get" name="stores_searchform" id="stores_searchform">
	<input name="id" type="hidden" value="'.$GLOBALS["TSFE"]->id.'" />
	<div class="form-fieldset">
		<label for="countries_dropdown_menu">'.htmlspecialchars($this->pi_getLL('country')).'</label>
		<select name="tx_multishop_pi1[country]" id="countries_dropdown_menu">
			<option value="">'.htmlspecialchars(ucfirst($this->pi_getLL('choose'))).'</option>
		';
$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
$str3="SELECT sc.cn_iso_2, sc.cn_short_local from tx_multishop_stores mss, static_countries sc where mss.country=sc.cn_iso_2 group by sc.cn_iso_2";
$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
while ($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3)) {
	if (!$this->get['tx_multishop_pi1']['country']) {
		if ($default_country['cn_iso_2']==$row3['cn_iso_2']) {
			$this->get['tx_multishop_pi1']['country']=$row3['cn_iso_2'];
		}
	}
	$content.='<option value="'.mslib_befe::strtolower($row3['cn_iso_2']).'" '.((mslib_befe::strtolower($this->get['tx_multishop_pi1']['country'])==mslib_befe::strtolower($row3['cn_iso_2'])) ? 'selected' : '').'>'.htmlspecialchars($row3['cn_short_local']).'</option>'."\n";
}
$content.='</select>
</div>
<div class="form-fieldset">
		<label>'.htmlspecialchars(ucfirst($this->pi_getLL('zip'))).'</label>
		<input id="postalcode" name="tx_multishop_pi1[zip]" type="text" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['zip']).'" maxlength="7" />
		<input name="Submit" type="submit" value="vind" class="msfront_button" />
		<input id="latitude" name="tx_multishop_pi1[lat]" size="12" type="hidden" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['lat']).'">
		<input id="longitude" name="tx_multishop_pi1[lng]" size="12" type="hidden" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['lng']).'">
		<input name="do_search" id="do_search" type="hidden" value="0" />
</div>
<script>
$("#stores_searchform").submit(function(e) {
	if ($("#do_search").val()=="0")
	{
		var url=\'http://ws.geonames.org/postalCodeLookupJSON?username='.$this->ms['MODULES']['GEONAMES_USERNAME'].'&postalcode=\' + $("#postalcode").val()  + \'&country=\' + $("#countries_dropdown_menu").val()  + \'&style=long\';
		$.ajax({
		  url: url,
		  dataType: "jsonp",
		  data: "",
		  success: function(data){
			if (data.postalcodes[0].lat) $("#latitude").val(data.postalcodes[0].lat);
			if (data.postalcodes[0].lng) $("#longitude").val(data.postalcodes[0].lng);
			$("#do_search").val("1");
			$("#stores_searchform").submit();
		  }
		});
		return false;
	}
	else
	{
		return true;
	}
	return;
});
</script>
'."\n";
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
	);
	$Cache_Lite=new Cache_Lite($options);
	$string=md5($this->cObj->data['uid'].'_'.$this->HTTP_HOST.'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING']);
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string))) {
	if ($p>0) {
		$extrameta=' (page '.$p.')';
	} else {
		$extrameta='';
	}
	if (!$this->conf['disableMetatags']) {
		$GLOBALS['TSFE']->additionalHeaderData['title']='<title>Store Locator :: '.$this->ms['MODULES']['STORE_NAME'].'</title>';
		$GLOBALS['TSFE']->additionalHeaderData['description']='<meta name="description" content="Store Locator." />';
	}
	if ($p>0) {
		$offset=(((($p)*$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])));
	} else {
		$p=0;
		$offset=0;
	}
	if ($this->get['tx_multishop_pi1']['zip']) {
		$do_search=1;
	}
	if ($do_search) {
		// store search
		$filter=array();
		$having=array();
		$match=array();
		$orderby=array();
		$where=array();
		$orderby=array();
		$select=array();
//		$Xcoor=str_replace(",",".",$GLOBALS['TSFE']->fe_user->user['tx_datingsite_current_lat']);
//		$Ycoor=str_replace(",",".",$GLOBALS['TSFE']->fe_user->user['tx_datingsite_current_lng']);	
		$Xcoor=str_replace(",", ".", $this->get['tx_multishop_pi1']['lat']);
		$Ycoor=str_replace(",", ".", $this->get['tx_multishop_pi1']['lng']);
		if (!$_GET['distance']) {
			$_GET['distance']=1000;
		}
		if ($Xcoor and $Ycoor and $_GET['distance']) {
			$select[]="ROUND( DEGREES( ACOS( SIN( RADIANS( ".$Xcoor." )  )  * SIN( RADIANS( mss.lat )  )  + COS( RADIANS( ".$Xcoor." )  )  * COS( RADIANS( mss.lat )  )  * COS( RADIANS( ".$Ycoor." - mss.lng )  )  )  ) / 360 * 40041 ) AS distance";
			$having[]='distance < '.$_GET['distance'];
		}
		$orderby[]='distance asc';
		$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=2;
		$pageset=mslib_fe::getStoresPageSet($filter, $offset, $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'], $orderby, $having, $select, $where);
		$stores=$pageset['stores'];
		if ($pageset['total_rows']>0) {
			if (strstr($this->ms['MODULES']['STORES_LISTING_TYPE'], "..")) {
				die('error in STORES_LISTING_TYPE value');
			} else {
				if (!$this->ms['MODULES']['STORES_LISTING_TYPE']) {
					$this->ms['MODULES']['STORES_LISTING_TYPE']='default';
				}
				if (strstr($this->ms['MODULES']['STORES_LISTING_TYPE'], "/")) {
					require($this->DOCUMENT_ROOT.$this->ms['MODULES']['STORES_LISTING_TYPE'].'.php');
				} else {
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/stores_listing/'.$this->ms['MODULES']['STORES_LISTING_TYPE'].'.php');
				}
			}
			// pagination
			if (!$this->hidePagination and $pageset['total_rows']>$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']) {
//				require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');	
			}
			// pagination eof
		} else {
			$content.='<div class="main-heading"><h2>'.$this->pi_getLL('no_stores_found_heading').'</h2></div>'."\n";
			$content.='<p>'.$this->pi_getLL('no_stores_found_description').'</p>'."\n";
		}
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($content);
	}
}
?>