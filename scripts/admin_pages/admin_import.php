<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
if (!is_array($this->languages) || !count($this->languages)) {
	echo 'languages array not loaded';
	die();
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".multiselect").multiselect();
		$(\'.msadminRunImporter\').click(function(e) {
			e.preventDefault();
			var linkTarget=$(this).attr("href");
			ifConfirm($(this).attr("data-dialog-title"),$(this).attr("data-dialog-body"),function() {
				this.close();
				msAdminBlockUi();
				window.location.href=linkTarget;
			});
		});
		$(document).on("click", ".hide_advanced_import_radio", function() {
			$(".advanced_options").hide();
		});
		$(document).on("click", ".advanced_import_radio", function() {
			$(".advanced_options").show();
		});

	});
</script>';
$max_category_level=4;
if ($this->get['run_as_cron']) {
	$this->msLockFile=$this->DOCUMENT_ROOT.'uploads/tx_multishop/log/importer_is_running_'.$this->HTTP_HOST.'_'.$this->get['job_id'];
	// Clears file status cache
	if (file_exists($this->msLockFile)) {
		clearstatcache();
		$ss=@stat($this->msLockFile);
		$time_created=$ss['ctime'];
		$time=(time()-$time_created);
		if ($time>(60*60*12)) {
			@unlink($this->msLockFile);
		} else {
			die('lock '.$this->msLockFile.' is file enabled, meaning importer is already running.');
		}
	}
	$this->msLogFile=$this->DOCUMENT_ROOT.'uploads/tx_multishop/log/import_'.$this->HTTP_HOST.'_log.txt';
	@unlink($this->msLogFile);
	file_put_contents($this->msLogFile, $this->HTTP_HOST.' - importer started. (job '.$this->get['job_id'].') ('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
	file_put_contents($this->msLockFile, $this->HTTP_HOST.' - importer started. (job '.$this->get['job_id'].') ('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
	// start counter for incremental updates on the display
	$subtel=0;
}
$erno=array();
set_time_limit(86400);
ignore_user_abort(true);
$sortOrderArray=array();
$language_id=$GLOBALS['TSFE']->sys_language_uid;
// define the different columns
$coltypes=array();
// MULTILANGUAGE FIELDS
if (is_array($this->languages) && count($this->languages)) {
	foreach ($this->languages as $langKey=>$langTitle) {
		$suffix='';
		if ($langKey>0) {
			$suffix='_'.$langKey;
		}
		$coltypes['products_name'.$suffix]='Products name ('.$langTitle['title'].')';
		$coltypes['products_model'.$suffix]='Products model ('.$langTitle['title'].')';
		$coltypes['products_description'.$suffix]='Products description ('.$langTitle['title'].')';
		$coltypes['products_description_encoded'.$suffix]='Products description (encoded) ('.$langTitle['title'].')';
		$coltypes['products_shortdescription'.$suffix]='Products description (short description) ('.$langTitle['title'].')';
		$coltypes['products_meta_keywords'.$suffix]='Products meta keywords ('.$langTitle['title'].') ('.$langTitle['title'].')';
		$coltypes['products_deeplink'.$suffix]='Products deeplink ('.$langTitle['title'].') ('.$langTitle['title'].')';
		for ($x=1; $x<=$max_category_level; $x++) {
			$coltypes['categories_id'.$x.$suffix]='Categories id (level: '.$x.') ('.$langTitle['title'].')';
			$coltypes['categories_name'.$x.$suffix]='Categories name (level: '.$x.') ('.$langTitle['title'].')';
			$coltypes['categories_image'.$x.$suffix]='Categories image (level: '.$x.') ('.$langTitle['title'].')';
			$coltypes['categories_content'.$x.$suffix]='Categories content (level: '.$x.') ('.$langTitle['title'].')';
			$coltypes['categories_content_bottom'.$x.$suffix]='Categories content bottom (level: '.$x.') ('.$langTitle['title'].')';
		}
		if ($this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS']) {
			for ($x=1; $x<=$this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS']; $x++) {
				$coltypes['products_description_tab_title_'.$x.$suffix]='Products Description Tab '.$x.' title ('.$langTitle['title'].')';
				$coltypes['products_description_tab_content_'.$x.$suffix]='Products Description Tab '.$x.' content ('.$langTitle['title'].')';
			}
		}
		$coltypes['manufacturers_name'.$suffix]='Manufacturers name ('.$langTitle['title'].')';
		$coltypes['products_meta_title'.$suffix]='Products meta title ('.$langTitle['title'].')';
		$coltypes['products_meta_description'.$suffix]='Products meta description ('.$langTitle['title'].')';
		$coltypes['products_meta_keywords'.$suffix]='Products meta keywords ('.$langTitle['title'].')';
		$coltypes['products_delivery_time'.$suffix]='Products delivery time ('.$langTitle['title'].')';
		$coltypes['products_condition'.$suffix]='Products condition ('.$langTitle['title'].')';
		$coltypes['category_group'.$suffix]='Category group ('.$langTitle['title'].')';

		// causes key naming conflicts with "Attribute option values for option name"
		//$coltypes['attribute_option_name'.$suffix]='Attribute option name ('.$langTitle['title'].')';
		$coltypes['attribute_option_value'.$suffix]='Attribute option values (specify option name in the aux field or also define attribute option name field) ('.$langTitle['title'].')';
		$coltypes['attribute_option_value_including_vat'.$suffix]='Attribute option values incl. VAT (specify option name in the aux field or also define attribute option name field) ('.$langTitle['title'].')';
		$coltypes['products_order_unit_name'.$suffix]='Products order unit name ('.$langTitle['title'].')';
		$str="SELECT * FROM `tx_multishop_products_options` where language_id='0' order by products_options_id asc";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$coltypes['attribute_option_name_'.$row['products_options_id'].$suffix]='Attribute option values for option name: '.$row['products_options_name'].' ('.$langTitle['title'].')';
		}
	}
}
if ($this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER']) {
	// loading payment methods
	$payment_methods=mslib_fe::loadPaymentMethods();
	// loading shipping methods
	$shipping_methods=mslib_fe::loadShippingMethods();

	if (count($payment_methods) or count($shipping_methods)) {
		if (count($payment_methods)) {
			foreach ($payment_methods as $code => $item) {
				$coltypes['product_payment_methods_' . $code] = 'Products payment methods: ' . $item['name'] . ' ('.$code.')';
			}
		}
		if (count($shipping_methods)) {
			foreach ($shipping_methods as $code => $item) {
				$coltypes['product_shipping_methods_' . $code] = 'Products shipping methods: ' . $item['name'] . ' ('.$code.')';
			}
		}
	}
}
// MULTILANGUAGE FIELDS EOL
$coltypes['products_price']='Products price (normal price, excl. VAT)';
$coltypes['products_price_including_vat']='Products price (normal price, incl. VAT)';
$coltypes['products_old_price']='Products price (old price, excl. VAT)';
$coltypes['products_old_price_including_vat']='Products price (old price, incl. VAT)';
$coltypes['products_specials_price']='Products price (specials price, excl. VAT)';
$coltypes['products_specials_price_including_vat']='Products price (specials price, incl. VAT)';
$coltypes['products_sku']='Products SKU';
$coltypes['products_minimum_quantity']='Products minimum order quantity';
$coltypes['products_maximum_quantity']='Products maximum order quantity';
$coltypes['products_multiplication']='Products multiplication quantity';
$coltypes['products_ean']='Products EAN';
$coltypes['products_unique_identifier']='Products unique identifier (unique products id from feed)';
$coltypes['products_quantity']='Products stock quantity';
$coltypes['products_status']='Products status';
$coltypes['products_date_added']='Products date added';
$coltypes['products_date_available']='Products date available';
$coltypes['products_date_modified']='Products date modified';
$coltypes['products_vat_rate']='Products VAT rate';
$coltypes['products_special_price_expiry_date']='Specials price expiry date';
$coltypes['products_id']='Products id';
$coltypes['products_special_price_start_date']='Specials price start date';
$coltypes['products_weight']='Products weight';
$coltypes['products_sort_order']='Products sort order';
$coltypes['products_capital_price']='Products price (capital)';
$coltypes['products_staffel_price']='Products price (staffel price)';
$coltypes['products_specials_section']='Specials section';
$coltypes['products_order_unit_code']='Products order unit code';
$coltypes['products_order_unit_id']='Products order unit id';
$coltypes['alert_quantity_threshold']='Alert minimum stock quantity threshold';
if ($this->ms['MODULES']['DISPLAY_MANUFACTURERS_ADVICE_PRICE_INPUT']) {
	$coltypes['manufacturers_advice_price']='Manufacturers advice price';
}
for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
	$x2=$x;
	if ($x2==0) {
		$x2='';
	}
	$coltypes['products_image'.$x2]='Products image '.($x+1);
}
$coltypes['categories_id']='Categories id';
$coltypes['manufacturers_image']='Manufacturers image';
$coltypes['manufacturers_products_id']='Manufacturers products id';
//$total_static_coltypes=count($coltypes);
//$counter=$total_static_coltypes;
//hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_importer.php']['adminProductsImporterColtypesHook'])) {
	$params=array(
		'coltypes'=>&$coltypes
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_importer.php']['adminProductsImporterColtypesHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$total_coltypes=count($coltypes);
natsort($coltypes);
// define the different columns eof
if ($this->get['delete'] and is_numeric($this->get['job_id'])) {
	// delete job
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_import_jobs', 'id='.$this->get['job_id']);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'#tasks');
	exit();
}
if (isset($this->get['download']) && $this->get['download']=='task' && is_numeric($this->get['job_id'])) {
	$sql=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
		'tx_multishop_import_jobs ', // FROM ...
		'id= \''.$this->get['job_id'].'\'', // WHERE...
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
		$data=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$serial_value=array();
		foreach ($data as $key_idx=>$key_val) {
			if ($key_idx!='id' && $key_idx!='page_uid') {
				$serial_value[$key_idx]=$key_val;
			}
		}
		$serial_data='';
		if (count($serial_value)>0) {
			$serial_data=serialize($serial_value);
		}
		$filename='multishop_product_import_task_'.date('YmdHis').'_'.$this->get['job_id'].'.txt';
		$filepath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/'.$filename;
		file_put_contents($filepath, $serial_data);
		header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
		header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
		readfile($filepath); //Read and stream the file
		@unlink($filepath);
		exit();
	}
}
if (isset($this->get['upload']) && $this->get['upload']=='task' && $_FILES) {
	if (!$_FILES['task_file']['error']) {
		$filename=$_FILES['task_file']['name'];
		$target=$this->DOCUMENT_ROOT.'/uploads/tx_multishop'.$filename;
		if (move_uploaded_file($_FILES['task_file']['tmp_name'], $target)) {
			$task_content=file_get_contents($target);
			$unserial_task_data=unserialize($task_content);
			$insertArray=array();
			$insertArray['page_uid']=$this->showCatalogFromPage;
			foreach ($unserial_task_data as $col_name=>$col_val) {
				if ($col_name=='code') {
					$insertArray[$col_name]=md5(uniqid());
				} else if ($col_name=='name' && isset($this->post['new_cron_name']) && !empty($this->post['new_cron_name'])) {
					$insertArray[$col_name]=$this->post['new_cron_name'];
				} else if ($col_name=='prefix_source_name' && isset($this->post['new_prefix_source_name']) && !empty($this->post['new_prefix_source_name'])) {
					$insertArray[$col_name]=$this->post['new_prefix_source_name'];
				} else {
					$insertArray[$col_name]=$col_val;
				}
			}
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_import_jobs', $insertArray);
			if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
				$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
			}
			@unlink($target);
		}
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'#tasks');
	exit();
}
$this->ms['show_default_form']=1;
if ($this->post) {
	$this->ms['show_default_form']=0;
}
$this->ms['upload_productfeed_form']='<div id="upload_productfeed_form">';
$this->ms['upload_productfeed_form'].='
<div class="panel panel-default">
<div class="panel-heading"><h3>'.$this->pi_getLL('upload_product_feed').'</h3></div>
<div class="panel-body">
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('file').'</label>
	<div class="col-md-10">
		<input type="file" name="file" class="form-control" />
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">URL</label>
	<div class="col-md-10">
		<input name="file_url" type="text" class="form-control" />
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('database_table').'</label>
	<div class="col-md-10">
		<input name="database_name" type="text" class="form-control" />
	</div>
</div>
<div class="form-group">
	<label class="control-label col-md-2">'.$this->pi_getLL('mapped_to_category').'</label>
	<div class="col-md-10">
		<input type="hidden" name="cid" value="" class="importCategoryTargetTree" />
	</div>
</div>
';
// custom hook that can be controlled by third-party plugin
$importParserTemplateTypes=array();
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productImportParserTemplateTypesProc'])) {
	$params=array(
		'importParserTemplateTypes'=>&$importParserTemplateTypes,
		'prefix_source_name'=>$this->post['prefix_source_name']
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productImportParserTemplateTypesProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
$this->ms['upload_productfeed_form'].='<div class="extra_parameters"><hr>';
if (count($importParserTemplateTypes)) {
	$this->ms['upload_productfeed_form'].='<div class="form-group"><label class="control-label col-md-2">'.$this->pi_getLL('datafeed_parser_template').':</label><div class="col-md-10"><select name="parser_template" class="form-control"><option value="">'.$this->pi_getLL('generic').'</option>';
	foreach ($importParserTemplateTypes as $importParserTemplateType) {
		$this->ms['upload_productfeed_form'].='<option value="'.$importParserTemplateType['key'].'">'.$importParserTemplateType['label'].'</option>';
	}
	$this->ms['upload_productfeed_form'].='</select></div></div>';
}
$this->ms['upload_productfeed_form'].='<div class="form-group">
  <label class="control-label col-md-2">'.ucfirst($this->pi_getLL('format')).':</label>
  <div class="col-md-10">
  <div class="radio radio-success radio-inline">
  <input name="format" type="radio" value="excel" id="excel" checked class="hide_advanced_import_radio" /><label for="excel">Excel</label>
  </div>
  <div class="radio radio-success radio-inline">
  <input name="format" type="radio" value="xml" id="xml" class="hide_advanced_import_radio" /><label for="xml">XML</label>
  </div>
  <div class="radio radio-success radio-inline">
  <input name="format" type="radio" value="txt" id="txt" class="advanced_import_radio" /><label for="txt">TXT/CSV</label>
  </div>
  <div class="advanced_options col-md-12" style="display:none">
  <hr>
	<div class="form-group">
		<label for="delimiter" class="control-label col-md-2">'.$this->pi_getLL('delimited_by').'</label>
		<div class="col-md-10">
			<select name="delimiter" id="delimiter" class="form-control">
				<option value="dotcomma">'.$this->pi_getLL('dotcomma').'</option>
				<option value="comma">'.$this->pi_getLL('comma').'</option>
				<option value="tab">'.$this->pi_getLL('tab').'</option>
				<option value="dash">'.$this->pi_getLL('pipe').'</option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-12">
		<div class="checkbox checkbox-success checkbox-inline">
			<input name="backquotes" type="checkbox" value="1" id="backquotes" />
			<label for="backquotes">'.$this->pi_getLL('fields_are_enclosed_with_double_quotes').'</label>
		</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-12">
		<div class="checkbox checkbox-success checkbox-inline">
			<input type="checkbox" name="escape_first_line" id="escape_first_line" value="1" />
			<label for="escape_first_line">'.$this->pi_getLL('ignore_first_line').'</label>
		</div>
		<div class="checkbox checkbox-success checkbox-inline">
			<input type="checkbox" name="os" id="os" value="linux" />
			<label for="os">'.$this->pi_getLL('unix_file').'</label>
		</div>
		<div class="checkbox checkbox-success checkbox-inline">
			<input type="checkbox" name="consolidate" id="consolidate" value="1" />
			<label for="consolidate">'.$this->pi_getLL('consolidate').'</label>
		</div>
		</div>
	</div>
</div>




</div>
</div>
<div class="form-group">
	<div class="col-md-10 col-md-offset-2">
		<input type="submit" name="Submit" class="submit btn btn-success" id="cl_submit" value="'.$this->pi_getLL('upload').'" />
		<input name="action" type="hidden" value="product-import-preview" />
	</div>
</div>
</div>
<!-- <input name="cid" class="cid" type="hidden" value="0" /> -->
</div>
</div>
</div>
';
if ($this->get['update_category_for_job']) {
	foreach ($this->get['update_category_for_job'] as $key=>$value) {
		// update the target category of a job
		$updateArray=array();
		$updateArray['categories_id']=$value;
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=\''.$key.'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// update the target category of a job eof
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'#tasks');
	exit();
}
if (is_numeric($this->get['job_id']) and is_numeric($this->get['status'])) {
	// update the status of a job
	$updateArray=array();
	$updateArray['status']=$this->get['status'];
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=\''.$this->get['job_id'].'\'', $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	// update the status of a job eof
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'#tasks');
	exit();
}
$tabs=array();
/*
$html=mslib_fe::tep_get_categories_edit('',$GLOBALS['TSFE']->fe_user->user['uid']);
if (!$html) {
	$html=$this->pi_getLL('no_products_available');
}
$tabs['Update_by_Category']=array($this->pi_getLL('import_to_category'),$html.$ajax_html);
*/
if ($this->post['action']=='category-insert') {
	if ($this->post['name']) {
		$str="insert into tx_multishop_categories (parent_id,status,date_added,page_uid) VALUES ('".addslashes($this->post['parent'])."',1,".time().",".$this->showCatalogFromPage.")";
		$this->ms['sqls'][]=$str;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$this->ms['target-cid']=$GLOBALS['TYPO3_DB']->sql_insert_id();
		$str="insert into tx_multishop_categories_description (categories_id, language_id, categories_name) VALUES ('".$this->ms['target-cid']."','".$language_id."','".addslashes($this->post['name'])."')";
		$this->ms['sqls'][]=$str;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	}
} elseif ($this->post['action']=='product-import-preview' or (is_numeric($_REQUEST['job_id']) and $_REQUEST['action']=='edit_job')) {
	$this->ms['show_default_form']=0;
	if (is_numeric($_REQUEST['job_id'])) {
		$this->ms['mode']='edit';
		// load the job
		$str="SELECT * from tx_multishop_import_jobs where id='".$_REQUEST['job_id']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$data=unserialize($row['data']);
		// copy the previous post data to the current post so it can run the job again
		$this->post=$data[1];
		$this->post['cid']=$row['categories_id'];
		// enable file logging
		if ($this->get['relaxed_import']) {
			$this->post['relaxed_import']=$this->get['relaxed_import'];
		}
		$this->get['job_id']=$_REQUEST['job_id'];
		$this->post['job_id']=$_REQUEST['job_id'];
		// After file upload we have to force to preview to review
		$this->post['action']='product-import-preview';
		$this->get['job_id']=$_REQUEST['job_id'];
	}
	if ($this->post['database_name']) {
		$file_location=$this->post['database_name'];
	} elseif ($this->post['file_url']) {
		if (strstr($this->post['file_url'], "../")) {
			die();
		}
		$filename=time().'.import';
		if (preg_match("/\.gz$/", $this->post['file_url'])) {
			$filename.='.gz';
		}
		$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
		$file_content=mslib_fe::file_get_contents($this->post['file_url']);
		if (!$file_content or !file_put_contents($file_location, $file_content)) {
			if ($this->ms['mode']!='edit') {
				die('cannot save the file or the file is empty');
			}
		}
	} elseif ($this->ms['mode']=='edit') {
		if (!$_FILES['file']['tmp_name']) {
			$filename=$this->post['filename'];
			$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
		}
	}
	if ($_FILES['file']['tmp_name']) {
		$file=$_FILES['file']['tmp_name'];
		$filename=time().'.import';
		$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
		$this->post['filename']=$filename;
		move_uploaded_file($file, $file_location);
		if (preg_match("/\.gz$/", $_FILES['file']['name'])) {
			// lets uncompress realtime
			$str=mslib_fe::file_get_contents($file_location, 1);
			file_put_contents($file_location, $str);
		}
	}
	if ($this->ms['mode']=='edit' and is_array($data) and count($data) and $filename) {
		if ($filename) {
			$data[1]['filename']=$filename;
			$this->post['filename']=$filename;
		}
		$string=serialize($data);
		$updateArray=array();
		$updateArray['data']=$string;
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$_REQUEST['job_id'], $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if ($file_location and $this->ms['mode']=='edit') {
		// if file not exists then show form to upload new file
		if (!file_exists($file_location)) {
			$content.='<h2>Feed no longer available</h2>'.$file_location.' is not existing.';
		}
	}
	if ((file_exists($file_location) or $this->post['database_name']) and isset($this->post['cid'])) {
		if (!$this->post['database_name'] and $file_location) {
			$str=mslib_fe::file_get_contents($file_location);
		}
		if ($this->post['parser_template']) {
			$processed=0;
			$rows=array();
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productImportParserTemplateProc'])) {
				$params=array(
					'parser_template'=>&$this->post['parser_template'],
					'prefix_source_name'=>$this->post['prefix_source_name'],
					'str'=>$str,
					'file_location'=>&$file_location,
					'rows'=>&$rows,
					'table_cols'=>&$table_cols,
					'processed'=>&$processed
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productImportParserTemplateProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
		} else {
			if ($this->post['database_name']) {
				if ($this->ms['mode']=='edit') {
					$limit=10;
				} else {
					$limit='10';
				}
				if (strstr(mslib_befe::strtolower($this->post['database_name']), 'select ')) {
					// its not a table name, its a full query
					$this->databaseMode='query';
					$str=$this->post['database_name'].' LIMIT '.$limit;
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if ($this->conf['debugEnabled']=='1') {
						$logString='Load records for importer query: '.$str;
						\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', -1);
					}
					$datarows=array();
					while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
						$datarows[]=$row;
					}
				} else {
					$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->post['database_name'], '', '', '', $limit);
				}
				$i=0;
				$table_cols=array();
				foreach ($datarows as $datarow) {
					$s=0;
					foreach ($datarow as $colname=>$datacol) {
						$table_cols[$s]=$colname;
						if (!mb_detect_encoding($table_cols[$s], 'UTF-8', true)) {
							$table_cols[$s]=$table_cols[$s];
						}
						$rows[$i][$s]=$datacol;
						$s++;
					}
					$i++;
					if ($i==5) {
						break;
					}
				}
			} elseif ($this->post['format']=='excel') {
				if (!$this->ms['mode']=='edit') {
					$filename='tmp-file-'.$GLOBALS['TSFE']->fe_user->user['uid'].'-cat-'.$this->post['cid'].'-'.time().'.txt';
					if (!$handle=fopen($this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename, 'w')) {
						exit;
					}
					if (fwrite($handle, $str)===false) {
						exit;
					}
					fclose($handle);
				}
				// excel
				require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
				$phpexcel=PHPExcel_IOFactory::load($file_location);
				foreach ($phpexcel->getWorksheetIterator() as $worksheet) {
					$counter=0;
					foreach ($worksheet->getRowIterator() as $row) {
						$cellIterator=$row->getCellIterator();
						$cellIterator->setIterateOnlyExistingCells(false);
						foreach ($cellIterator as $cell) {
							$clean_products_data=ltrim(rtrim($cell->getCalculatedValue(), " ,"), " ,");
							$clean_products_data=trim($clean_products_data);
							if ($row->getRowIndex()>1) {
								$rows[$counter-1][]=$clean_products_data;
							} else {
								$table_cols[]=mslib_befe::strtolower($clean_products_data);
							}
						}
						$counter++;
						if ($counter==5) {
							break;
						}
					}
				}
				// excel eol
			} elseif ($this->post['format']=='xml') {
				// try the generic way
				if (!$this->ms['mode']=='edit') {
					$filename='tmp-file-'.$GLOBALS['TSFE']->fe_user->user['uid'].'-cat-'.$this->post['cid'].'-'.time().'.txt';
					if (!$handle=fopen($this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename, 'w')) {
						exit;
					}
					if (fwrite($handle, $str)===false) {
						exit;
					}
					fclose($handle);
				}
				// try the generic way
				$arrOutput=mslib_fe::xml2array($str);
				$i=0;
				$s=0;
				$rows=array();
				foreach ($arrOutput[0]['children'] as $item) {
					foreach ($item['children'] as $internalitem) {
						$rows[$i][$s]=$internalitem['tagData'];
						$s++;
					}
//					foreach ($item['attrs'] as $key => $value)
//					{
//						$rows[$i][$s] = $value;
//						$s++;
//					}
					$i++;
					$s=0;
					if ($i==5) {
						break;
					}
				}
			} else {
				if ($this->post['os']=='linux') {
					$splitter="\n";
				} else {
					$splitter="\r\n";
				}
				// csv
				if ($this->post['delimiter']=="tab") {
					$delimiter="\t";
				} elseif ($this->post['delimiter']=="dash") {
					$delimiter="|";
				} elseif ($this->post['delimiter']=="dotcomma") {
					$delimiter=";";
				} elseif ($this->post['delimiter']=="comma") {
					$delimiter=",";
				} else {
					$delimiter="\t";
				}
				if ($this->post['backquotes']) {
					$backquotes='"';
				} else {
					$backquotes='"';
				}
				if ($this->post['format']=='txt') {
					$row=1;
					$rows=array();
					if (($handle=fopen($file_location, "r"))!==false) {
						$counter=0;
						while (($data=fgetcsv($handle, '', $delimiter, $backquotes))!==false) {
							//print_r($data);
							if ($this->post['escape_first_line']) {
								if ($counter==0) {
									$table_cols=$data;
								} else {
									$rows[]=$data;
								}
							} else {
								$rows[]=$data;
							}
							$counter++;
							if ($counter==5) {
								break;
							}
						}
						fclose($handle);
					}
				}
				// csv
			}
			// try the generic way eol
		}
		$tmpcontent='';
		if (!$rows) {
			$tmpcontent.='<h3>'.$this->pi_getLL('no_products_available').'</h3>';
		} else {
			$tmpcontent.='<table id="product_import_table" class="table table-striped table-bordered">';
			$header='<thead><tr><th>'.$this->pi_getLL('target_column').'</th><th>'.$this->pi_getLL('source_column').'</th>';
			for ($x=1; $x<6; $x++) {
				$header.='<th>'.$this->pi_getLL('row').' '.$x.'</th>';
			}
			$header.='</tr></thead><tbody>';
			$tmpcontent.=$header;
			$cols=count($rows[0]);
			$preview_listing=array();
			for ($i=0; $i<$cols; $i++) {
				if ($switch=='odd') {
					$switch='even';
				} else {
					$switch='odd';
				}
				$tmpcontent.='
				<tr class="'.$switch.'">
					<td class="cellAux">
					<div class="form-inline">
					<select name="select['.$i.']" id="select['.$i.']" class="select_columns_fields">
						<option value="">'.$this->pi_getLL('skip').'</option>
						';
				foreach ($coltypes as $key=>$value) {
					$tmpcontent.='<option value="'.$key.'" '.($this->post['select'][$i]!='' && $this->post['select'][$i]==$key ? 'selected' : '').'>'.htmlspecialchars($value).'</option>';
				}
				$tmpcontent.='
						</select>&nbsp;<input name="advanced_settings" class="importer_advanced_settings btn btn-primary row-fluid" type="button" value="'.$this->pi_getLL('admin_advanced_settings').'" />
					</div>
					<div class="advanced_settings_container" style="display:none;">
						<div class="form-group no-mb">
							<div class="col-md-12">
								<label class="control-label">aux</label>
								<input name="input['.$i.']" class="form-control" type="text" value="'.htmlspecialchars($this->post['input'][$i]).'">
							</div>
						</div>
					</div>
				</td>
				<td class="column_name"><strong>'.htmlspecialchars($table_cols[$i]).'</strong></td>
				';
				/*
			<fieldset class="advanced_settings_container hide">
				<div class="hr"></div>
				<div class="ms_properties"></div>
				<input name="add_property" class="importer_add_property" type="button" value="add property" />
			</fieldset>
									<div class="form-field">
										<label>aux</label>
										<input name="input['.$i.']" type="text" style="width:150px;" value="'.htmlspecialchars($this->post['input'][$i]).'" />
									</div>
									*/
				// now 5 products
				$item_counter=0;
				foreach ($rows as $row) {
					foreach ($row as $key=>$col) {
						if (!mb_detect_encoding($col, 'UTF-8', true)) {
							$row[$key]=mslib_befe::convToUtf8($col);
						}
					}
					$item_counter++;
					$tmpitem=$row;
					$cols=count($tmpitem);
					if ($this->post['backquotes']) {
						$tmpitem[$i]=trim($tmpitem[$i], "\"");
					}
					$alt=$tmpitem[$i];
					if (strlen($tmpitem[$i])>15) {
						$tmpitem[$i]=substr($tmpitem[$i], 0, 15).'..';
					}
					$tmpcontent.='<td class="cellBreak product_'.$item_counter.' review_records"><div class="text_content" title="'.htmlspecialchars($alt).'">'.htmlspecialchars($tmpitem[$i]).'</div></td>';
					if ($item_counter==5 or $item_counter==count($rows)) {
						break;
					}
				}
				if ($item_counter<5) {
					// lets add few blank cells cause there are no 5 products to show
					for ($x=$item_counter; $x<5; $x++) {
						$tmpcontent.='<td class="cellBreak product_'.$x.'">&nbsp;</td>';
					}
				}
				// now 5 products eof
				$tmpcontent.='
				</tr>';
				/* prefix '.$i.': <input name="input['.$i.']" type="text" value="'.htmlspecialchars($this->post['input'][$i]).'" /> */
			}
			$importer_add_aux_input='
			<div class="form-field ms_dynamic_add_property">
				<label>type</label>
				<select name="type">
					<option value="append">append content with value</option>
					<option value="prepend">prepend content with value</option>
					<option value="find_and_replace">find and replace</option>
					<option value="custom_code">custom php code</option>
				</select>
				<label>aux</label>
				<input name="aux_input[]" type="text" value="'.htmlspecialchars($this->post['aux_input']).'" />
				<input name="delete" class="delete_property" type="button" value="delete" /><input name="disable" type="button" value="enable" />
			</div>
			';
			$importer_add_aux_input=str_replace(array(
				"\r\n",
				"\n"
			), '', $importer_add_aux_input);
			$tmpcontent.='
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				var add_property_html=\''.addslashes($importer_add_aux_input).'\';
				$(document).on("click", ".delete_property", function() {
					$(this).parent().hide("fast");
				});
				$(".importer_add_property").click(function(event) {
					$(this).prev().append(add_property_html);
				});
				$(".importer_advanced_settings").click(function(event) {
					$(this).parent().next().toggle();
				});
				$(\'.select_columns_fields\').select2({
					dropdownCssClass: "bigdropWider", // apply css that makes the dropdown taller
					width:\'100%\'
				});
			});
			</script>
			';
			$tmpcontent.=$header.'</tbody></table>';
		}
	} else {
		$tmpcontent.='<div class="alert alert-danger"><strong>Products cannot be retrieved.</strong></div>';
	}
	// print form
	$combinedContent='<div class="panel-body">
<form id="product_import_form" class="form-horizontal blockSubmitForm" name="form1" method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'">
	<input name="consolidate" type="hidden" value="'.$this->post['consolidate'].'" />
	<input name="os" type="hidden" value="'.$this->post['os'].'" />
	<input name="escape_first_line" type="hidden" value="'.$this->post['escape_first_line'].'" />
	<input name="parser_template" type="hidden" value="'.$this->post['parser_template'].'" />
	<input name="format" type="hidden" value="'.$this->post['format'].'" />
	<input name="action" type="hidden" value="product-import" />
	<input name="job_id" type="hidden" value="'.$this->get['job_id'].'" />
	<input name="cid" type="hidden"  value="'.$this->post['cid'].'" />
	<input name="delimiter" type="hidden"  value="'.$this->post['delimiter'].'" />
	<input name="backquotes" type="hidden"  value="'.$this->post['backquotes'].'" />
	<input name="filename" type="hidden" value="'.$filename.'" />
	<input name="file_url" type="hidden" value="'.$this->post['file_url'].'" />
	';
	if ($this->ms['mode']=='edit' or $this->post['preProcExistingTask']) {
		// if the existing import task is rerunned indicate it so we dont save the task double
		$combinedContent.='<input name="preProcExistingTask" type="hidden" value="1" />';
	}
//	if (!$this->get['action']=='edit_job') {
	if ($this->ms['mode']!='edit') {
		$combinedContent.='<input name="incremental_update" type="hidden" value="'.$this->post['incremental_update'].'" />';
	}
	$combinedContent.=$tmpcontent;
	$combinedContent.='
			<div class="panel panel-default">
			<div class="panel-heading"><h3>'.$this->pi_getLL('save_import_task').'</h3></div>
			<div class="panel-body">
			<div class="form-group">
				<label for="cron_name" class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
				<div class="col-md-10">
					<input name="cron_name" type="text" class="form-control" value="'.htmlspecialchars($this->post['cron_name']).'" />
				</div>
			</div>
		';
	if ($this->get['action']=='edit_job') {
		$combinedContent.='
			<div class="form-group">
				<label for="duplicate" class="control-label col-md-2">'.$this->pi_getLL('duplicate_task').'</label>
				<div class="col-md-10">
					<input name="skip_import" type="hidden" value="1" />
					<input name="job_id" type="hidden" value="'.$this->get['job_id'].'" />
					<div class="checkbox checkbox-success checkbox-inline">
						<input name="duplicate" id="duplicate" type="checkbox" value="1" /><label for="duplicate"></label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="duplicate" class="control-label col-md-2">URL</label>
				<div class="col-md-10">
					<input name="file_url" type="text" class="form-control" value="'.$this->post['file_url'].'" />
				</div>
			</div>
			';
	}
	$combinedContent.='
		<div class="form-group">
			<label for="cron_period" class="control-label col-md-2">'.$this->pi_getLL('schedule').'</label>
			<div class="col-md-10">
				<select name="cron_period" id="cron_period" class="form-control">
				<option value=""'.(!$this->post['cron_period'] ? ' selected' : '').'>'.$this->pi_getLL('manual').'</option>
				<option value="'.(3600*24).'"'.($this->post['cron_period']==(3600*24) ? ' selected' : '').'>'.$this->pi_getLL('daily').'</option>
				<option value="'.(3600*24*7).'"'.($this->post['cron_period']==(3600*24*7) ? ' selected' : '').'>'.$this->pi_getLL('weekly').'</option>
				<option value="'.(3600*24*30).'"'.($this->post['cron_period']==(3600*24*30) ? ' selected' : '').'>'.$this->pi_getLL('monthly').'</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="prefix_source_name" class="control-label col-md-2">'.$this->pi_getLL('source_name').'</label>
			<div class="col-md-10">
				<input name="prefix_source_name" class="form-control" type="text" value="'.htmlspecialchars($this->post['prefix_source_name']).'" />
			</div>
		</div>
		<div class="form-group multiselect_horizontal">
			<label for="locked_fields" class="control-label col-md-2">'.$this->pi_getLL('lock_following_fields_when_adjusted', 'lock the following fields if the product is being adjusted (in edit product)').'</label>
			<div class="col-md-10">
				<select id="groups" class="multiselect" multiple="multiple" name="tx_multishop_pi1[locked_fields][]">
		';
	$locked_fields=array();
	$locked_fields['categories_id']='Category';
	$locked_fields['products_price']='Products price';
	$locked_fields['products_vat_rate']='Products VAT rate';
	$locked_fields['products_name']='Products name';
	$locked_fields['products_quantity']='Products quantity';
	$locked_fields['products_description']='Products description';
	foreach ($locked_fields as $key=>$val) {
		if (is_array($this->post['tx_multishop_pi1']['locked_fields'])) {
			$combinedContent.='<option value="'.$key.'"'.(in_array($key, $this->post['tx_multishop_pi1']['locked_fields']) ? ' selected' : '').'>'.htmlspecialchars($val).'</option>'."\n";
		} else {
			$combinedContent.='<option value="'.$key.'">'.htmlspecialchars($val).'</option>'."\n";
		}
	}
	$combinedContent.='
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="" class="control-label col-md-2">'.$this->pi_getLL('default_vat_rate', 'Default VAT Rate').'</label>
			<div class="col-md-10">
				<select name="tx_multishop_pi1[default_vat_rate]" class="form-control"><option value="">skip</option>
		';
	$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$combinedContent.='<option value="'.$row['rules_group_id'].'"'.($this->post['tx_multishop_pi1']['default_vat_rate']==$row['rules_group_id'] ? ' selected' : '').'>'.htmlspecialchars($row['name']).'</option>';
	}
	$combinedContent.='
				</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-10 col-md-offset-2">
				<div class="checkbox checkbox-success checkbox-inline">
					<input name="incremental_update" id="incremental_update" type="checkbox" value="1" '.(($this->post['incremental_update']==1) ? 'checked' : '').' /><label for="incremental_update">'.$this->pi_getLL('import_incremental').' (only use this when you upload partial data. For example when you have 2 feeds that contains the values of 1 attribute then you have to enable this checkbox. Products will always be inserted on incremental basis, so you allmost never have to enable this checkbox)</label>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-10 col-md-offset-2">
				<div class="checkbox checkbox-success checkbox-inline">
					<input name="fetch_existing_product_by_direct_field" id="fetch_existing_product_by_direct_field" type="checkbox" value="1" '.(($this->post['fetch_existing_product_by_direct_field']==1) ? 'checked' : '').' /><label for="fetch_existing_product_by_direct_field"">'.$this->pi_getLL('fetch_existing_product_by_direct_field', 'Fetch existing product by db field (i.e. products_id, products_sku, products_ean) instead of hashed extid field.').'</label>
				</div>
			</div>
		</div>
		<input name="database_name" type="hidden" value="'.$this->post['database_name'].'" />
		<input name="cron_data" type="hidden" value="'.htmlspecialchars(serialize($this->post)).'" />
		</div>
		</div>
		<hr>
		<div class="clearfix">
			<div class="pull-right">
				<button type="submit" class="btn btn-success" name="AdSubmit" value=""><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-save fa-stack-1x"></i></span> '.($this->get['action']=='edit_job' ? $this->pi_getLL('save') : $this->pi_getLL('import')).'</button>
			</div>
		</div>
		';
	$combinedContent.='</form></div>';
	$content='<div class="panel panel-default">'.mslib_fe::shadowBox($combinedContent).'</div>';
} elseif ((is_numeric($this->get['job_id']) and $this->get['action']=='run_job') or ($this->post['action']=='product-import' and (($this->post['filename']) or $this->post['database_name']))) {
	// removed this:  and file_exists($this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$this->post['filename']) so we can also save the task if the file is not found
	if ((!$this->post['preProcExistingTask'] and $this->post['cron_name'] and !$this->post['skip_import'] and !$this->post['job_id']) or ($this->post['skip_import'] and $this->post['duplicate'])) {
//		print_r($this->post);
//		die();
		// we have to save the import job
		$updateArray=array();
		$updateArray['name']=$this->post['cron_name'];
		$updateArray['status']=1;
		$updateArray['last_run']=time();
		$updateArray['code']=md5(uniqid());
		$updateArray['period']=$this->post['cron_period'];
		$updateArray['prefix_source_name']=$this->post['prefix_source_name'];
		$cron_data=array();
		$cron_data[0]=unserialize($this->post['cron_period']);
		$this->post['cron_period']='';
		$cron_data[1]=$this->post;
		$updateArray['data']=serialize($cron_data);
		$updateArray['page_uid']=$this->showCatalogFromPage;
		$updateArray['categories_id']=$this->post['cid'];
		$updateArray['type']='';
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_import_jobs', $updateArray);
		if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
			$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
		}
		// we have to save the import job eof
		$this->ms['show_default_form']=1;
	} elseif ($this->post['skip_import']) {
		// we have to update the import job
		if (!$this->post['select']) {
			// something is wrong. repair the select of previous job
			$str="SELECT * from tx_multishop_import_jobs where id='".$this->post['job_id']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$data=unserialize($row['data']);
			// copy the previous post data to the current post so it can run the job again
			$this->post['select']=$data[1]['select'];
			$this->post['input']=$data[1]['input'];
		}
		$updateArray=array();
		$updateArray['name']=$this->post['cron_name'];
		$updateArray['status']=1;
		$updateArray['last_run']=time();
		$updateArray['period']=$this->post['cron_period'];
		$updateArray['prefix_source_name']=$this->post['prefix_source_name'];
		$cron_data=array();
		$cron_data[0]=unserialize($this->post['cron_period']);
		$this->post['cron_period']='';
		$this->post['cron_data']='';
		$cron_data[1]=$this->post;
		$updateArray['data']=serialize($cron_data);
//		$updateArray['categories_id']			=$this->post['cid'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$this->post['job_id'], $updateArray);
//		echo $query;
//		die();
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// we have to update the import job eof
		$this->ms['show_default_form']=1;
		header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'#tasks');
		exit();
	}
	if (!$this->post['skip_import']) {
		$stats=array();
		$stats['time_started']=time();
		$stats['products_added']=0;
		$stats['products_updated']=0;
		$stats['products_deleted']=0;
		$stats['categories_added']=0;
		if (is_numeric($this->get['job_id'])) {
			// load the job
			$str="SELECT * from tx_multishop_import_jobs where id='".$this->get['job_id']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$data=unserialize($row['data']);
			// copy the previous post data to the current post so it can run the job again
			$this->post=$data[1];
//			if ($row['categories_id']) $this->post['cid']=$row['categories_id'];
			$this->post['cid']=$row['categories_id'];
			if ($this->post['cid']>0) {
				// verify that the category is existing
				$strchk="SELECT categories_id from tx_multishop_categories c where c.categories_id='".$this->post['cid']."'";
				$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
				if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
					exit("Script halted, because the target category id of the import job is not existing.");
				}
			}
			//update the last run time
			$updateArray=array();
			$updateArray['last_run']=time();
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$row['id'], $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			//update the last run time eof
			if ($this->msLogFile) {
				file_put_contents($this->msLogFile, $this->HTTP_HOST.' - cron job settings loaded. ('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
			}
		}
		if ($this->post['file_url']) {
			if (strstr($this->post['file_url'], "../")) {
				die();
			}
			$filename=time().'.import';
			if (preg_match("/\.gz$/", $this->post['file_url'])) {
				$filename.='.gz';
			}
			$file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
			file_put_contents($file, mslib_fe::file_get_contents($this->post['file_url']));
		} else {
			if ($this->post['filename']) {
				$file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$this->post['filename'];
			}
		}
		$import_data_collector=array();
		if (($this->post['database_name'] or $file) and isset($this->post['cid'])) {
			if ($file) {
				$str=mslib_fe::file_get_contents($file);
			}
			if ($this->post['parser_template']) {
				$processed=0;
				$rows=array();
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productImportParserTemplateProc'])) {
					$params=array(
						'parser_template'=>&$this->post['parser_template'],
						'prefix_source_name'=>$this->post['prefix_source_name'],
						'str'=>&$str,
						'rows'=>&$rows,
						'file_location'=>&$file,
						'table_cols'=>&$table_cols,
						'processed'=>&$processed
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productImportParserTemplateProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
			} else {
				if ($this->post['database_name']) {
					// get primary key first
					$str="show index FROM ".$this->post['database_name'].' where Key_name = \'PRIMARY\'';
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					$primaryKeyColumn=$row['Column_name'];
					if ($this->msLogFile) {
						file_put_contents($this->msLogFile, $this->HTTP_HOST.' - loading random products. ('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
					}
					if (is_numeric($this->get['limit'])) {
						$limit=$this->get['limit'];
					} else {
						$limit=2000;
					}
					if (strstr(mslib_befe::strtolower($this->post['database_name']), 'select ')) {
						$this->databaseMode='query';
						// its not a table name, its a full query
						$this->databaseMode='query';
						$str=$this->post['database_name'].' LIMIT '.$limit;
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						if ($this->conf['debugEnabled']=='1') {
							$logString='Load records for importer query: '.$str;
							\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', -1);
						}
						$datarows=array();
						while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
							$datarows[]=$row;
						}
					} else {
						// get primary key first
						$str="show index FROM ".$this->post['database_name'].' where Key_name = \'PRIMARY\'';
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
						$primaryKeyColumn=$row['Column_name'];
						$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
							$this->post['database_name'], // FROM ...
							'', // WHERE.
							'', // GROUP BY...
							'', // ORDER BY...
							$limit // LIMIT ...
						);
						$qry=$GLOBALS['TYPO3_DB']->sql_query($query);
						$datarows=array();
						while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
							$datarows[]=$row;
							if ($primaryKeyColumn and isset($row[$primaryKeyColumn])) {
								$str2="delete from ".$this->post['database_name']." where ".$primaryKeyColumn."='".$row[$primaryKeyColumn]."'";
								$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
							}
						}
					}
					$total_datarows=count($datarows);
					/*
					// debug
					print_r($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
					$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = false;
					die();
					*/
					$i=0;
					$rows=array();
					foreach ($datarows as $datarow) {
						$s=0;
						foreach ($datarow as $datacol) {
							$rows[$i][$s]=$datacol;
							$s++;
						}
						$i++;
					}
				} elseif ($this->post['format']=='excel') {
					// excel
					require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
					$phpexcel=PHPExcel_IOFactory::load($file);
					foreach ($phpexcel->getWorksheetIterator() as $worksheet) {
						$counter=0;
						foreach ($worksheet->getRowIterator() as $row) {
							$cellIterator=$row->getCellIterator();
							$cellIterator->setIterateOnlyExistingCells(false);
							foreach ($cellIterator as $cell) {
								$clean_products_data=ltrim(rtrim($cell->getCalculatedValue(), " ,"), " ,");
								$clean_products_data=trim($clean_products_data);
								if ($row->getRowIndex()>1) {
									$rows[$counter-1][]=$clean_products_data;
								} else {
									$table_cols[]=mslib_befe::strtolower($clean_products_data);
								}
							}
							$counter++;
						}
					}
					// excel eof
				} elseif ($this->post['format']=='xml') {
					$arrOutput=mslib_fe::xml2array($str);
					$i=0;
					$s=0;
					$rows=array();
					foreach ($arrOutput[0]['children'] as $item) {
						// image
						foreach ($item['children'] as $internalitem) {
							$rows[$i][$s]=$internalitem['tagData'];
							$s++;
						}
						foreach ($item['attrs'] as $key=>$value) {
							$rows[$i][$s]=$value;
							$s++;
						}
						$i++;
						$s=0;
					}
				} else {
					if ($this->post['os']=='linux') {
						$splitter="\n";
					} else {
						$splitter="\r\n";
					}
					$str=trim($str, $splitter);
					if ($this->post['escape_first_line']) {
						$pos=strpos($str, $splitter);
						$str=substr($str, ($pos+strlen($splitter)));
					}
					// csv
					if ($this->post['delimiter']=="tab") {
						$delimiter="\t";
					} elseif ($this->post['delimiter']=="dash") {
						$delimiter="|";
					} elseif ($this->post['delimiter']=="dotcomma") {
						$delimiter=";";
					} elseif ($this->post['delimiter']=="comma") {
						$delimiter=",";
					} else {
						$delimiter="\t";
					}
					if ($this->post['backquotes']) {
						$backquotes='"';
					} else {
						$backquotes='"';
					}
					if ($this->post['format']=='txt') {
						$row=1;
						$rows=array();
						if (($handle=fopen($file, "r"))!==false) {
							$counter=0;
							while (($data=fgetcsv($handle, '', $delimiter, $backquotes))!==false) {
								if ($this->post['escape_first_line']) {
									if ($counter==0) {
										$table_cols=$data;
									} else {
										$rows[]=$data;
									}
								} else {
									$rows[]=$data;
								}
								$counter++;
							}
							fclose($handle);
						}
					}
					// csv
				}
			}
			$item_counter=0;
			$inserteditems=array();
			$global_start_time=microtime(true);
			$start_time=microtime(true);
			$total_datarows=count($rows);
			if ($this->msLogFile) {
				if ($total_datarows) {
					// sometimes the preload takes so long that the database connection is lost.
					$GLOBALS['TYPO3_DB']->connectDB();
					file_put_contents($this->msLogFile, $this->HTTP_HOST.' - products loaded, now starting the import. ('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
				} else {
					file_put_contents($this->msLogFile, $this->HTTP_HOST.' - no products needed to be imported'."\n", FILE_APPEND);
				}
			}
			// load default TAX rules
			if ($this->post['tx_multishop_pi1']['default_vat_rate']) {
				$default_iso_customer=mslib_fe::getCountryByName($this->tta_shop_info['country']);
				$default_tax_rate=mslib_fe::taxRuleSet($this->post['tx_multishop_pi1']['default_vat_rate'], 0, $default_iso_customer['cn_iso_nr'], 0);
			}
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['iteratorPreProc'])) {
				$params=array(
					'rows'=>&$rows
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['iteratorPreProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
			foreach ($rows as $row) {
				$skipRow=0;
				// custom hook that can be controlled by third-party plugin
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['itemIteratePreProc'])) {
					$params=array(
						'row'=>&$row,
						'prefix_source_name'=>$this->post['prefix_source_name'],
						'skipRow'=>&$skipRow
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['itemIteratePreProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				// custom hook that can be controlled by third-party plugin eof
				if ($skipRow) {
					continue;
				}
				$item=array();
				foreach ($row as $key=>$col) {
					if (!mb_detect_encoding($col, 'UTF-8', true)) {
						$row[$key]=mslib_befe::convToUtf8($col);
					}
					$row[$key]=trim($row[$key]);
				}
				// initialize array
				$this->ms['products_to_categories_array']=array();
				if (!isset($this->post['cid'])) {
					$this->post['cid']=$this->categoriesStartingPoint;
				}
				$this->ms['target-cid']=$this->post['cid'];
				$item_counter++;
//				if ( ($this->post['escape_first_line'] and $item_counter > 1) or !$this->post['escape_first_line']) {
				$tmpitem=$row;
				$cols=count($tmpitem);
				$flipped_select=array_flip($this->post['select']);
				//			if ($tmpitem[$this->post['select'][0]] and $cols > 0)
				//			{
				// if the source is a database table name add the unique id so we can delete it after the import
				if ($this->post['database_name']) {
					$item['table_unique_id']=$row[0];
				}
				// name
				for ($i=0; $i<$cols; $i++) {
					$char='';
					$tmpitem[$i]=trim($tmpitem[$i]);
					switch ($this->post['select'][$i]) {
						// trim the data and dynamically process/merge it with AUX
						case 'products_date_added':
						case 'products_date_available':
						case 'products_date_modified':
							// if aux is containing format structure, then convert it to US
							if ($this->post['input'][$i] and $tmpitem[$i]) {
								try {
									date_default_timezone_set('Europe/Amsterdam');
									$date=DateTime::createFromFormat($this->post['input'][$i], $tmpitem[$i]);
									if (is_object($date)) {
										$item[$this->post['select'][$i]]=(string)$date->format('Y-m-d');
									}
								} catch (Exception $e) {
//										echo $e->getMessage();
//										die();
//										$tmpitem[$i] = date("Y-m-d");
								}
							}
							break;
						case 'products_name':
							$char=" - ";
							$item[$this->post['select'][$i]].=$char.$tmpitem[$i];
							$item[$this->post['select'][$i]]=preg_replace("/^".$char."|".$char."$/is", '', $item[$this->post['select'][$i]]);
							break;
						case 'products_description':
							$char="<BR />";
							$item[$this->post['select'][$i]].=$char.$tmpitem[$i];
							$item[$this->post['select'][$i]]=preg_replace("/^<BR \/>|<BR \/>$/is", '', $item[$this->post['select'][$i]]);
							break;
						case 'products_description_encoded':
							$char="<BR />";
							$item[$this->post['select'][$i]].=$char.htmlspecialchars_decode($tmpitem[$i]);
							$item[$this->post['select'][$i]]=preg_replace("/^<BR \/>|<BR \/>$/is", '', $item[$this->post['select'][$i]]);
							break;
						case 'products_deeplink':
							$item[$this->post['select'][$i]]=$tmpitem[$i];
							if (strstr($this->post['input'][$i], "|")) {
								// PREPEND AUX VALUE TO URL
								$tmp=explode("|", $this->post['input'][$i]);
								$prependValue=$tmp[0];
								$item[$this->post['select'][$i]]=$prependValue.$item[$this->post['select'][$i]];
							} else {
								// APPEND AUX VALUE TO URL
								if ($this->post['input'][$i]) {
									$item[$this->post['select'][$i]].=$this->post['input'][$i];
								}
							}
							break;
						case 'products_meta_keywords':
							$char=",";
							$item[$this->post['select'][$i]].=$char.$tmpitem[$i];
							$item[$this->post['select'][$i]]=preg_replace("/^".$char."|".$char."$/is", '', $item[$this->post['select'][$i]]);
							break;
						case 'attribute_option_value':
						case 'attribute_option_value_including_vat':
							// attribute option value (with aux as option)
							// if aux is defined use that value as option name. else use the field option name.
							if ($this->post['input'][$i]) {
								if (strstr($this->post['input'][$i], "|")) {
									// sometimes aux is also containing a delimiter sign, so many values depending on one product can be send through. the sign for this is dash (|)
									$tmp=explode("|", $this->post['input'][$i]);
									$key=$tmp[0];
									$delimiter=$tmp[1];
									$subdelimiter=$tmp[2];
									if (!$delimiter) {
										$delimiter='|';
									}
									$option_values=explode($delimiter, $tmpitem[$i]);
									$total=count($option_values);
									if ($total>0) {
										$count=0;
										$internal_count=0;
										foreach ($option_values as $option_value) {
											if ($subdelimiter) {
												// when working with Multishop productfeed that contains attribute values with prices use AUX:
												// Example data: Yes::1.00||No::0.00
												// Use AUX to import: ||::
												// extreme setup: FORMAAT|#|;|$value|$price
												// example value: 15ml;0,00#350ml;17,45#1000ml;34,65
												$option_value2=explode($subdelimiter, $option_value);
												$option_value=$option_value2[0];
												$option_price=str_replace(",", ".", $option_value2[1]);
											} else {
												$option_price=0;
												if ($tmp[$internal_count]=='$key') {
													// sometimes the option name is inside the field value
													// example field value: Option_name#Option value#Option value 2
													// we can dynamically convert this by defining the aux field as: $key|#
													// so we use the first value (Option_name) as key
													$key=$option_value;
													$option_value='';
												}
												if ($tmp[$internal_count]=='$price') {
													// sometimes the option name is inside the field value
													// example field value: Option value#price
													// we can dynamically convert this by defining the aux field as: FORMAAT|#|;|$value|$price
													// FORMAAT|||;|$price
													// so we use the first value (Option_name) as key
													$option_price=$option_value;
												}
												$internal_count++;
												if ($internal_count==$total) {
													$internal_count=0;
												}
											}
											if ($key && $option_value) {
												$item[$this->post['select'][$i]][]=array(
													$key,
													$option_value,
													$option_price
												);
											}
											$count++;
										}
									}
								} else {
									// the values are multiple values (delimited by pipe). Lets explode them and add them individually
									$key=$this->post['input'][$i];
									if (strstr($tmpitem[$i], "|")) {
										$exploded_items=explode("|", $tmpitem[$i]);
										foreach ($exploded_items as $exploded_item) {
											$item['attribute_option_value'][]=array(
												$key,
												$exploded_item
											);
										}
									} else {
										$item['attribute_option_value'][]=array(
											$key,
											$tmpitem[$i]
										);
									}
								}
							} else {
								$key='';
								$item['attribute_option_value'][]=array(
									$key,
									$tmpitem[$i]
								);
							}
							break;
						default:
							$map_by_default_rule=1;
							// images
							for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
								$x2=$x;
								if ($x2==0) {
									$x2='';
								}
								$field='products_image'.$x2;
								if ($this->post['select'][$i]==$field) {
									// if aux contains prefixed path or url add it
									if ($this->post['input'][$i]) {
										$item[$this->post['select'][$i]]=$this->post['input'][$i].'/'.$tmpitem[$i];
										$map_by_default_rule=0;
									} else {
										$item[$this->post['select'][$i]]=$tmpitem[$i];
										$map_by_default_rule=0;
									}
								}
							}
							if ($map_by_default_rule) {
								$item[$this->post['select'][$i]]=$tmpitem[$i];
							}
							for ($x=1; $x<=$max_category_level; $x++) {
								$field='categories_image'.$x;
								if ($this->post['select'][$i]==$field) {
									// if aux contains prefixed path or url add it
									if ($this->post['input'][$i]) {
										$item[$this->post['select'][$i]]=$this->post['input'][$i].'/'.$tmpitem[$i];
									} else {
										$item[$this->post['select'][$i]]=$tmpitem[$i];
									}
								}
							}
							break;
					}
					if ($char and $item[$this->post['select'][$i]]==$char) {
						$item[$this->post['select'][$i]]='';
					}
				}
				/*
									// trick to quickly debug 1 item
									if ($item['products_ean']=='7610663702857') {
										print_r($item);
										die();
										//mslib_fe::file_get_contents($item['products_image']);
									} else {
										continue;
									}
				*/
				// unique products id. this field will be used for incremental updates
				if ($this->post['fetch_existing_product_by_direct_field']) {
					$fields=array();
					$fields['products_id']='products_id';
					$fields['sku_code']='products_sku';
					$fields['ean_code']='products_ean';
					foreach ($fields as $dbField=>$itemField) {
						if ($item[$itemField]) {
							$str="select products_id,extid from tx_multishop_products where page_uid=".$this->showCatalogFromPage." and ".$dbField."='".addslashes($item[$itemField])."'";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
								$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
								$item['extid']=$row['extid'];
							}
						}
						if ($item['extid']) {
							break;
						}
					}
				} else {
					if ($item['products_id']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['products_id']);
					} elseif ($item['products_unique_identifier']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['products_unique_identifier']);
					} elseif ($item['products_ean']) {
						$item['products_ean']=trim($item['products_ean']);
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['products_ean']);
					} elseif ($item['products_sku']) {
						$item['products_sku']=trim($item['products_sku']);
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['products_sku']);
					}
				}
				if (!$item['extid']) {
					// no sku or special key found. this makes it hard to update things. therefore we have added the prefix_source_name so we can merge it with the productsname and some other fields
					$make_our_own_fake_sku=serialize($item['products_name'].$item['products_model']);
					$item['extid']=md5($this->post['prefix_source_name'].'_'.$make_our_own_fake_sku);
				}
				if ($item['products_vat_rate'] and strstr($item['products_vat_rate'], '%')) {
					$item['products_vat_rate']=str_replace("%", "", $item['products_vat_rate']);
				}
				// custom hook that can be controlled by third-party plugin
				$skipItem=0;
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['itemIterateProc'])) {
					$params=array(
						'item'=>&$item,
						'prefix_source_name'=>$this->post['prefix_source_name'],
						'skipItem'=>&$skipItem
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['itemIterateProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				// custom hook that can be controlled by third-party plugin eof
				if (!$skipItem) {
					$hashed_id='';
					if ($this->ms['target-cid']=='') {
						$this->ms['target-cid']=$this->categoriesStartingPoint;
					}
					if ($this->ms['target-cid']=='') {
						$this->ms['target-cid']=0;
					}
					if ($this->ms['target-cid']) {
						$hashed_id=$this->ms['target-cid'];
					}
					for ($x=1; $x<=$max_category_level; $x++) {
						if ($item['categories_name'.$x]) {
							$item['categories_name'.$x]=trim($item['categories_name'.$x]);
							// sometimes importer can have &amp; characters as category name. lets convert it to plain text first
							$item['categories_name'.$x]=html_entity_decode($item['categories_name'.$x], ENT_QUOTES, 'UTF-8');
							if ($hashed_id) {
								$hashed_id.=' / ';
							}
							$hashed_id.=$item['categories_name'.$x];
							$strchk="SELECT categories_id from tx_multishop_categories c where c.hashed_id='".addslashes(md5($hashed_id))."' and c.page_uid='".$this->showCatalogFromPage."'";
							$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
							$qryRows=$GLOBALS['TYPO3_DB']->sql_num_rows($qrychk);
							if (!$qryRows) {
								$strchk="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where cd.categories_name='".addslashes($item['categories_name'.$x])."' and c.parent_id='".$this->ms['target-cid']."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$language_id."' and c.categories_id=cd.categories_id";
								$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
								if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
									// fix possible empty hash to make it backwards compatible
									$rowchk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
									$updateArray=array();
									$updateArray['hashed_id']=md5($hashed_id);
									$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', "categories_id=".$rowchk['categories_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									// now rerun original query
									$strchk="SELECT categories_id from tx_multishop_categories c where c.hashed_id='".addslashes(md5($hashed_id))."' and c.page_uid='".$this->showCatalogFromPage."'";
									$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
									$qryRows=$GLOBALS['TYPO3_DB']->sql_num_rows($qrychk);
								}
							}
							if ($qryRows) {
								$rowchk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
								$this->ms['target-cid']=$rowchk['categories_id'];
							} else {
								$str="insert into tx_multishop_categories (parent_id,status,date_added,page_uid,hashed_id) VALUES ('".$this->ms['target-cid']."',1,".time().",".$this->showCatalogFromPage.",'".addslashes(md5($hashed_id))."')";
								$this->ms['sqls'][]=$str;
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$this->ms['target-cid']=$GLOBALS['TYPO3_DB']->sql_insert_id();
								$updateArray=array();
								if (isset($item['categories_content'.$x])) {
									$updateArray['content']=$item['categories_content'.$x];
								}
								if (isset($item['categories_content_bottom'.$x])) {
									$updateArray['content_footer']=$item['categories_content_bottom'.$x];
								}
								$updateArray['categories_id']=$this->ms['target-cid'];
								$updateArray['language_id']=$language_id;
								$updateArray['categories_name']=$item['categories_name'.$x];
								$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
								$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray);
								if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query))  {
									$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
								}
								$this->ms['sqls'][]=$query;
								$stats['categories_added']++;
							}
							if ($this->ms['target-cid']) {
								$updateArray=array();
								if (isset($item['categories_content'.$x])) {
									$updateArray['content']=$item['categories_content'.$x];
								}
								if (isset($item['categories_content_bottom'.$x])) {
									$updateArray['content_footer']=$item['categories_content_bottom'.$x];
								}
								//$updateArray['categories_name']=trim($item['categories_name'.$x]);
								if (count($updateArray)) {
									$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', "language_id=".$language_id." and categories_id=".$this->ms['target-cid'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									$this->ms['sqls'][]=$query;
								}
							}
							// LANGUAGE OVERLAYS for categories description
							foreach ($this->languages as $langKey=>$langTitle) {
								if ($langKey>0) {
									$suffix='_'.$langKey;
									$updateArray2=array();
									if (isset($item['categories_content'.$x.$suffix]) && !empty($item['categories_content'.$x.$suffix])) {
										$updateArray2['content']=$item['categories_content'.$x.$suffix];
									} else {
										if (isset($item['categories_content'.$x])) {
											$updateArray2['content']=$item['categories_content'.$x];
										}
									}
									if (isset($item['categories_content_bottom'.$x.$suffix]) && !empty($item['categories_content_bottom'.$x.$suffix])) {
										$updateArray2['content_footer']=$item['categories_content_bottom'.$x.$suffix];
									} else {
										if (isset($item['categories_content_bottom'.$x])) {
											$updateArray2['content_footer']=$item['categories_content_bottom'.$x];
										}
									}
									if (isset($item['categories_name'.$x.$suffix]) && !empty($item['categories_name'.$x.$suffix])) {
										$updateArray2['categories_name']=$item['categories_name'.$x.$suffix];
									} else {
										if (isset($item['categories_name'.$x])) {
											$updateArray2['categories_name']=$item['categories_name'.$x];
										}
									}
									$updateArray2['categories_id']=$this->ms['target-cid'];
									$updateArray2['language_id']=$langKey;
									// get existing record
									$record=mslib_befe::getRecord($this->ms['target-cid'], 'tx_multishop_categories_description', 'categories_id', array(0=>'language_id='.$langKey));
									if ($record['categories_id']) {
										$updateArray2=mslib_befe::rmNullValuedKeys($updateArray2);
										$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories_description', 'categories_id='.$this->ms['target-cid'].' and language_id='.$langKey, $updateArray2);
										$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									} else {
										// add new record
										$updateArray2=mslib_befe::rmNullValuedKeys($updateArray2);
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray2);
										if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
											$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
										}
									}
								}
							}
							// LANGUAGE OVERLAYS for categories description EOL
						}
						if ($item['categories_image'.$x]) {
							$categories_name=$item['categories_name'.$x];
							$image=$item['categories_image'.$x];
							//$strchk="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where cd.categories_name='".addslashes($item['categories_name'.$x])."' and c.categories_id='".$this->ms['target-cid']."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$language_id."' and c.categories_id=cd.categories_id";
							$strchk="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id='".$this->ms['target-cid']."' and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$language_id."' and c.categories_id=cd.categories_id";
							$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
								$rowchk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
								// custom hook that can be controlled by third-party plugin
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['fetchCategoriesImagePreProc'])) {
									$params=array(
											'rowchk'=>&$rowchk,
											'item'=>&$item,
											'column'=>'categories_image'.$x
									);
									foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['fetchCategoriesImagePreProc'] as $funcRef) {
										\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
									}
								}
								// custom hook that can be controlled by third-party plugin eof
								if (!$rowchk['categories_image'] or ($rowchk['categories_image'] and !file_exists(PATH_site.$this->ms['image_paths']['categories']['original'].'/'.mslib_befe::getImagePrefixFolder($rowchk['categories_image']).'/'.$rowchk['categories_image']))) {
									// download image
									$data=mslib_fe::file_get_contents($image);
									if ($data) {
										$plaatje1_name=$this->ms['target-cid'].'-'.($colname).'-'.time();
										$tmpfile=PATH_site.'uploads/tx_multishop/tmp/'.$plaatje1_name;
										file_put_contents($tmpfile, $data);
										$plaatje1=$tmpfile;
										if (($extentie1=mslib_befe::exif_imagetype($plaatje1)) && $plaatje1_name<>'') {
											$extentie1=image_type_to_extension($extentie1, false);
											$ext=$extentie1;
											$ix=0;
											$filename=mslib_fe::rewritenamein($categories_name).'.'.$ext;
											$folder=mslib_befe::getImagePrefixFolder($filename);
											if (!is_dir(PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder)) {
												\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder);
											}
											$folder.='/';
											$target=PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
											if (file_exists($target)) {
												do {
													$filename=mslib_fe::rewritenamein($categories_name).($ix>0 ? '-'.$ix : '').'.'.$ext;
													$folder=mslib_befe::getImagePrefixFolder($filename);
													if (!is_dir(PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder)) {
														\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder);
													}
													$folder.='/';
													$target=PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
													$ix++;
												} while (file_exists($target));
											}
											// end
											$categories_image=$path.'/'.$naam;
											// backup original
											$target=PATH_site.$this->ms['image_paths']['categories']['original'].'/'.$folder.$filename;
											copy($tmpfile, $target);
											@unlink($tmpfile);
											// backup original eof
											$categories_image_name=mslib_befe::resizeCategoryImage($target, $filename, PATH_site.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey), 1);
											if ($categories_image_name) {
												$updateArray=array();
												$updateArray['categories_image']=$categories_image_name;
												$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
												$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', "categories_id=".$rowchk['categories_id'], $updateArray);
												$res=$GLOBALS['TYPO3_DB']->sql_query($query);
//													error_log($query);
											} else {
//													echo 'fail';
//													die();
											}
										}
										@unlink($tmpfile);
									}
								}
							}
						}
					}
					if ($item['category_group'] and $this->post['input'][$flipped_select['category_group']]) {
						if ($this->ms['target-cid']) {
							// Sometimes category_group is mixed with the standard category level 1-5 mapping. We wanna keep the normal category reference so we copy it first
							$this->ms['products_to_categories_array'][]=$this->ms['target-cid'];
						}
						// for supporting multiple paths you have to use aux field like this:
						// example multiple groups in column:
						// maincat>subcat|maincat>subcat2
						// then the aux must contain the following value: |;>
						$groupDelimiter='';
						$catDelimiter='';
						$tmp=explode(';', $this->post['input'][$flipped_select['category_group']]);
						if (count($tmp)==2) {
							$groupDelimiter=$tmp[0];
							$catDelimiter=$tmp[1];
						} elseif (count($tmp)==1) {
							$catDelimiter=$tmp[0];
						}
						if ($groupDelimiter) {
							$groups=explode($groupDelimiter, $item['category_group']);
						} else {
							$groups=array($item['category_group']);
						}
						$languageGroups=array();
						foreach ($this->languages as $langKey=>$langTitle) {
							if ($langKey>0) {
								if ($groupDelimiter) {
									$groups2=explode($groupDelimiter, $item['category_group_'.$langKey]);
								} else {
									$groups2=array($item['category_group_'.$langKey]);
								}
								$languageGroups[$langKey]=$groups2;
							}
						}
						$groupCounter=0;
						foreach ($groups as $group) {
							// first configure target-cid (back) to the root
							$this->ms['target-cid']=$this->post['cid'];
							$cats=explode($catDelimiter, $group);
							$languageCats=array();
							foreach ($this->languages as $langKey=>$langTitle) {
								if ($langKey>0) {
									$languageCats[$langKey]=explode($catDelimiter, $languageGroups[$langKey][$groupCounter]);
								}
							}
							$tel=0;
							$hashed_id=$this->ms['target-cid'];
							foreach ($cats as $cat) {
								$cat=trim($cat);
								if ($hashed_id) {
									$hashed_id.=' / ';
								}
								$hashed_id.=$cat;
								$strchk="SELECT categories_id from tx_multishop_categories c where c.hashed_id='".addslashes(md5($hashed_id))."' and c.page_uid='".$this->showCatalogFromPage."'";
								//$strchk="SELECT c.categories_id from tx_multishop_categories_description cd, tx_multishop_categories c where cd.categories_name='".addslashes($cat)."' and parent_id='".$this->ms['target-cid']."' and c.page_uid='".$this->showCatalogFromPage."' and c.categories_id=cd.categories_id";
								$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
								if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
									$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
									$this->ms['target-cid']=$row['categories_id'];
								} else {
									$insertArray=array();
									$insertArray['parent_id']=$this->ms['target-cid'];
									$insertArray['status']=1;
									$insertArray['date_added']=time();
									$insertArray['page_uid']=$this->showCatalogFromPage;
									$insertArray['hashed_id']=md5($hashed_id);
									$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories', $insertArray);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
									$this->ms['target-cid']=$GLOBALS['TYPO3_DB']->sql_insert_id();
									$updateArray=array();
									$updateArray['categories_id']=$this->ms['target-cid'];
									$updateArray['language_id']=$language_id;
									$updateArray['categories_name']=trim($cat);
									$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query))  {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
									$this->ms['sqls'][]=$str;
									$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
									$image='';
									$categories_image='';
									$stats['categories_added']++;
									// LANGUAGE OVERLAYS
									foreach ($this->languages as $langKey=>$langTitle) {
										if ($langKey>0) {
											$suffix='_'.$langKey;
											$updateArray2=$updateArray;
											if (isset($languageCats[$langKey][$tel]) && $languageCats[$langKey][$tel]!='') {
												$updateArray2['categories_name']=$languageCats[$langKey][$tel];
											}
											$updateArray2['language_id']=$langKey;
											// add new record
											$updateArray2=mslib_befe::rmNullValuedKeys($updateArray2);
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_categories_description', $updateArray2);
											if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
												$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
											}
										}
									}
									// LANGUAGE OVERLAYS EOL
								}
								$tel++;
							}
							// add the deepest categories id to the array, so later we can relate the product to all of these categories
							$this->ms['products_to_categories_array'][]=$this->ms['target-cid'];
							$groupCounter++;
						}
					} elseif ($item['categories_id']) {
						// deepest categories id is defined
						$this->ms['target-cid']=$item['categories_id'];
					}
					// manufacturer column
					if ($item['manufacturers_name']) {
						$item['manufacturers_name']=trim($item['manufacturers_name']);
						$strchk="SELECT manufacturers_id from tx_multishop_manufacturers where manufacturers_name='".addslashes($item['manufacturers_name'])."'";
						$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
							$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
							$item['manufacturers_id']=$row['manufacturers_id'];
						} else {
							$str="insert into tx_multishop_manufacturers (date_added,manufacturers_name, status) VALUES ('".time()."','".addslashes($item['manufacturers_name'])."',1)";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							$item['manufacturers_id']=$GLOBALS['TYPO3_DB']->sql_insert_id();
							if ($item['manufacturers_id']) {
								$str="insert into tx_multishop_manufacturers_cms (manufacturers_id,language_id) VALUES (".$item['manufacturers_id'].",".$language_id.")";
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$str="insert into tx_multishop_manufacturers_info (manufacturers_id, manufacturers_url) VALUES (".$item['manufacturers_id'].",'')";
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							}
						}
					}
					if ($item['manufacturers_image']) {
						$manufacturers_name=$item['manufacturers_image'];
						$image=$item['manufacturers_image'];
						$strchk="SELECT * from tx_multishop_manufacturers m where m.manufacturers_id='".$item['manufacturers_id']."'";
						$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
							$rowchk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['fetchManufacturersImagePreProc'])) {
								$params=array(
										'rowchk'=>&$rowchk,
										'item'=>&$item,
										'column'=>'manufacturers_image'
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['fetchManufacturersImagePreProc'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party plugin eof
							if (!$rowchk['manufacturers_image']) {
								// download image
								$data=mslib_fe::file_get_contents($image);
								if ($data) {
									$plaatje1_name=$item['manufacturers_id'].'-'.($colname).'-'.time();
									$tmpfile=PATH_site.'uploads/tx_multishop/tmp/'.$plaatje1_name;
									file_put_contents($tmpfile, $data);
									$plaatje1=$tmpfile;
									if (($extentie1=mslib_befe::exif_imagetype($plaatje1)) && $plaatje1_name<>'') {
										$extentie1=image_type_to_extension($extentie1, false);
										$ext=$extentie1;
										$ix=0;
										$filename=mslib_fe::rewritenamein($categories_name).'.'.$ext;
										$folder=mslib_befe::getImagePrefixFolder($filename);
										if (!is_dir(PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder)) {
											\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder);
										}
										$folder.='/';
										$target=PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
										if (file_exists($target)) {
											do {
												$filename=mslib_fe::rewritenamein($manufacturers_name).($ix>0 ? '-'.$ix : '').'.'.$ext;
												$folder=mslib_befe::getImagePrefixFolder($filename);
												if (!is_dir(PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder)) {
													\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder);
												}
												$folder.='/';
												$target=PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
												$ix++;
											} while (file_exists($target));
										}
										// end
										$manufacturers_image=$path.'/'.$naam;
										// backup original
										$target=PATH_site.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
										copy($tmpfile, $target);
										@unlink($tmpfile);
										// backup original eof
										$manufacturers_image_name=mslib_befe::resizeManufacturerImage($target, $filename, PATH_site.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey), 1);
										if ($manufacturers_image_name) {
											$updateArray=array();
											$updateArray['manufacturers_image']=$manufacturers_image_name;
											$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
											$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', "manufacturers_id=".$rowchk['manufacturers_id'], $updateArray);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										}
									}
									@unlink($tmpfile);
								}
							}
						}
					}
					/*******************
					 * // INSERT/UPDATE PRODUCT //
					 *******************/
					if ($this->post['consolidate']) {
						if (@in_array($item['products_name'], $inserteditems[$this->ms['target-cid']])) {
							$skip=1;
						} else {
							$skip=0;
						}
					} else {
						$skip=0;
					}
					if (!$skip) {
						if ($this->post['relaxed_import']) {
							sleep(35);
						}
						if ($item['products_name']) {
							// sometimes importer can have &amp; characters as products name. lets convert it to plain text first
							$item['products_name']=html_entity_decode($item['products_name'], ENT_QUOTES, 'UTF-8');
							// if productsname is supplied
							// if the date available is only a year, add the default month and day
							if ($item['products_date_added'] and strlen($item['products_date_added'])==4) {
								$item['products_date_added']=$item['products_date_added'].'-01-01';
							}
							if ($item['products_date_available'] and strlen($item['products_date_available'])==4) {
								$item['products_date_available']=$item['products_date_available'].'-01-01';
							}
							// if date added exists, but not date available copy date added to date available
							if ($item['products_date_added'] and !$item['products_date_available']) {
								$item['products_date_available']=$item['products_date_added'];
							}
							if (!$this->post['incremental_update']) {
								// if status is not defined put it to 1
								if (!isset($item['products_status'])) {
									$item['products_status']=1;
								}
								// if quantity is not defined put it to 999
								if (!isset($item['products_quantity'])) {
									$item['products_quantity']=999;
								}
								if (!$item['products_shortdescription'] && $item['products_description']) {
									$item['products_shortdescription']=$item['products_description'];
									if (!$this->ms['MODULES']['PRODUCTS_SHORT_DESCRIPTION_CONTAINS_HTML_MARKUP']) {
										$item['products_shortdescription']=strip_tags($item['products_shortdescription']);
									}
								}
							}
						}
						if ($item['products_order_unit_id']) {
							$item['order_unit_id']=$item['products_order_unit_id'];
						} elseif (isset($item['products_order_unit_name']) or isset($item['products_order_unit_code'])) {
							$str="SELECT o.id, o.code, od.name from tx_multishop_order_units o, tx_multishop_order_units_description od where o.page_uid='".$this->shop_pid."'";
							if ($item['products_order_unit_code']) {
								$str.=" and o.code='".addslashes($item['products_order_unit_code'])."'";
							} else {
								$str.=" and od.name='".addslashes($item['products_order_unit_name'])."'";
							}
							$str.=" and o.id=od.order_unit_id and od.language_id='0' order by o.id desc";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
								$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
								$item['order_unit_id']=$row['id'];
							} else {
								// lets add it
								if (!$item['products_order_unit_code'] and $item['products_order_unit_name']) {
									$item['products_order_unit_code']=$item['products_order_unit_name'];
								}
								if (!$item['products_order_unit_name'] and $item['products_order_unit_code']) {
									$item['products_order_unit_name']=$item['products_order_unit_code'];
								}
								if ($item['products_order_unit_name'] and $item['products_order_unit_code']) {
									$insertArray=array();
									$insertArray['code']=$item['products_order_unit_code'];
									$insertArray['page_uid']=$this->showCatalogFromPage;
									$insertArray['crdate']=time();
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units', $insertArray);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
									$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									$insertArray=array();
									$insertArray['name']=$item['products_order_unit_name'];
									$insertArray['language_id']=0;
									$insertArray['order_unit_id']=$id;
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_order_units_description', $insertArray);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
									$item['order_unit_id']=$id;
								}
							}
						}
						if ($item['extid']) {
							// we have a remote unique products id. lets check our local database to see if the product is already existing
							$str="select products_id from tx_multishop_products where page_uid=".$this->showCatalogFromPage." and extid='".addslashes($item['extid'])."'";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
								$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
								$item['updated_products_id']=$row['products_id'];
							}
						}
						if ($this->post['fetch_existing_product_by_direct_field'] && !$item['updated_products_id'] && $item['products_id']) {
							$item['updated_products_id']=$item['products_id'];
						}
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['iteratorItemProc'])) {
							$params=array(
								'item'=>&$item
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['iteratorItemProc'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						$products_id='';
						if (isset($item['products_vat_rate']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_vat_rate', $importedProductsLockedFields))))) {
							$taxGroupRow=mslib_fe::getTaxGroupByName($item['products_vat_rate']);
							$tax_id=$taxGroupRow['rules_group_id'];
							if (!isset($tax_id)) {
								$taxGroupRow=mslib_fe::getTaxGroupByName($item['products_vat_rate'].'%');
								$item['tax_id']=$taxGroupRow['rules_group_id'];
								// the vat uid is not found. lets add it dynamically
								//TODO: needs v3 update
								/*
																	$str="SELECT * FROM `static_taxes` WHERE `tx_country_iso_nr` ='".addslashes($this->ms['MODULES']['COUNTRY_ISO_NR'])."'";
																	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
																	$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
																	$row['tx_rate']=($item['products_vat_rate']/100);
																	unset($row['uid']);
																	$row['crdate']=time();
																	$query = $GLOBALS['TYPO3_DB']->INSERTquery('static_taxes', $row);
																	$res = $GLOBALS['TYPO3_DB']->sql_query($query);
																	$tax_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
								*/
							}
							if ($tax_id) {
								$item['tax_id']=$tax_id;
							} elseif ($this->post['tx_multishop_pi1']['default_vat_rate'] and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_vat_rate', $importedProductsLockedFields))))) {
								$item['tax_id']=$this->post['tx_multishop_pi1']['default_vat_rate'];
								$item['products_vat_rate']=$default_tax_rate['total_tax_rate'];
							}
						} elseif ($this->post['tx_multishop_pi1']['default_vat_rate'] and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_vat_rate', $importedProductsLockedFields))))) {
							$item['tax_id']=$this->post['tx_multishop_pi1']['default_vat_rate'];
							$item['products_vat_rate']=$default_tax_rate['total_tax_rate'];
						}
						// convert including vat price to excluding vat
						if ($item['products_old_price_including_vat'] and $item['products_vat_rate']) {
							$item['products_old_price']=number_format(($item['products_old_price_including_vat']/(100+$item['products_vat_rate'])*100), 14, '.', '');
						}
						if ($item['products_price_including_vat']) {
							if ($item['products_vat_rate']) {
								$item['products_price']=number_format(($item['products_price_including_vat']/(100+$item['products_vat_rate'])*100), 14, '.', '');
							} else {
								$item['products_price']=number_format($item['products_price_including_vat'], 14, '.', '');
							}
						}
						if ($item['products_specials_price_including_vat']) {
							if ($item['products_vat_rate']) {
								$item['products_specials_price']=number_format(($item['products_specials_price_including_vat']/(100+$item['products_vat_rate'])*100), 14, '.', '');
							} else {
								$item['products_specials_price']=number_format($item['products_specials_price'], 14, '.', '');
							}
						}
						if ($item['products_old_price']) {
							if ($item['products_price']<$item['products_old_price']) {
								$item['products_specials_price']=$item['products_price'];
							}
							$item['products_price']=$item['products_old_price'];
						}
						if (!$item['products_description'] and $item['products_shortdescription']) {
							$item['products_description']=nl2br($item['products_shortdescription']);
						}
						if (is_numeric($item['updated_products_id'])) {
							/***********************
							 * // UPDATE PRODUCT MODE /
							 ***********************/
							// define products_id
							$products_id=$item['updated_products_id'];
							// add product to the undo table first
							$old_product=mslib_befe::addUndo($item['updated_products_id'], 'tx_multishop_products');
							if ($old_product['imported_product']) {
								$item['imported_product']=1;
								$importedProductsLockedFields=mslib_befe::getImportedProductsLockedFields($products_id);
							}
							/*
							if ($old_product['imported_product'] and $old_product['lock_imported_product']) {
								// we define that this product is a locked product to protect the product and only update what is allowed
								$item['locked_product']=1;
							}
							*/
//								error_log('old_product: '.print_r($old_product,1));
							$updateArray=array();
							if (isset($item['tax_id']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_vat_rate', $importedProductsLockedFields))))) {
								$updateArray['tax_id']=$item['tax_id'];
							}
							if (isset($item['products_weight'])) {
								$updateArray['products_weight']=$item['products_weight'];
							}
							if (isset($item['alert_quantity_threshold'])) {
								$updateArray['alert_quantity_threshold']=$item['alert_quantity_threshold'];
							}
							if ($this->ms['MODULES']['DISPLAY_MANUFACTURERS_ADVICE_PRICE_INPUT'] && isset($item['manufacturers_advice_price'])) {
								$updateArray['manufacturers_advice_price']=$item['manufacturers_advice_price'];
							}
							if (isset($item['products_capital_price'])) {
								$updateArray['product_capital_price']=$item['products_capital_price'];
							}
							if (isset($item['products_condition'])) {
								$updateArray['products_condition']=$item['products_condition'];
							}
							if (isset($item['products_minimum_quantity'])) {
								$updateArray['minimum_quantity']=$item['products_minimum_quantity'];
							}
							if (isset($item['products_maximum_quantity'])) {
								$updateArray['maximum_quantity']=$item['products_maximum_quantity'];
							}
							if (isset($item['products_multiplication'])) {
								$updateArray['products_multiplication']=$item['products_multiplication'];
							}
							if (isset($item['products_ean'])) {
								$updateArray['ean_code']=$item['products_ean'];
							}
							if (isset($item['products_price']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_price', $importedProductsLockedFields))))) {
								$updateArray['products_price']=$item['products_price'];
							}
							if ($item['manufacturers_id']) {
								$updateArray['manufacturers_id']=$item['manufacturers_id'];
							}
							if (isset($item['products_staffel_price'])) {
								$updateArray['staffel_price']=$item['products_staffel_price'];
							}
							if (isset($item['products_quantity']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_quantity', $importedProductsLockedFields))))) {
								switch ($item['products_quantity']) {
									case 'Y':
										$item['products_quantity']=1;
										break;
									case 'N':
										$item['products_quantity']=0;
										break;
								}
								$updateArray['products_quantity']=$item['products_quantity'];
							}
							if ($item['products_model']) {
								$updateArray['products_model']=$item['products_model'];
							}
							if (isset($item['products_sku'])) {
								$updateArray['sku_code']=$item['products_sku'];
							}
							if (isset($item['manufacturers_products_id'])) {
								$updateArray['vendor_code']=$item['manufacturers_products_id'];
							}
							if (isset($item['order_unit_id'])) {
								$updateArray['order_unit_id']=$item['order_unit_id'];
							}
							if ($item['products_date_added']) {
								$updateArray['products_date_added']=strtotime($item['products_date_added']);
							}
							if ($item['products_date_available']) {
								$updateArray['products_date_available']=strtotime($item['products_date_available']);
							}
							if ($item['products_date_modified']) {
								$updateArray['products_last_modified']=strtotime($item['products_date_modified']);
							} else {
								$updateArray['products_last_modified']=time();
							}
							if (strstr($updateArray['products_price'], ",")) {
								$updateArray['products_price']=str_replace(",", '.', $updateArray['products_price']);
							}
							if (strstr($updateArray['product_capital_price'], ",")) {
								$updateArray['product_capital_price']=str_replace(",", '.', $updateArray['product_capital_price']);
							}
							if (isset($item['products_status'])) {
								$updateArray['products_status']=$item['products_status'];
							}
							if (isset($item['products_sort_order'])) {
								$updateArray['sort_order']=$item['products_sort_order'];
							}
							if (count($updateArray)) {
								// custom hook that can be controlled by third-party plugin
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateProductPreHook'])) {
									$params=array(
										'updateArray'=>&$updateArray,
										'item'=>&$item,
										'prefix_source_name'=>$this->post['prefix_source_name'],
										'old_product'=>&$old_product
									);
									foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateProductPreHook'] as $funcRef) {
										\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
									}
								}
								// custom hook that can be controlled by third-party plugin eof
								$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', "page_uid=".$this->showCatalogFromPage." and products_id=".$item['updated_products_id'], $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$stats['products_updated']++;
							}
							// check if the old product didnt had any images. Also verify that the file exists. if not clear the filename
							$import_product_images=0;
							for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
								$i=$x;
								if ($i==0) {
									$i='';
								}
								$name='products_image'.$i;
								if ($item[$name]) {
									$import_product_images=1;
									// custom hook that can be controlled by third-party plugin
									if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['fetchProductImagePreProc'])) {
										$params=array(
												'old_product'=>&$old_product,
												'item'=>&$item,
												'column'=>$name
										);
										foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['fetchProductImagePreProc'] as $funcRef) {
											\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
										}
									}
									// custom hook that can be controlled by third-party plugin eof
									if ($old_product[$name]) {
										$filename=$old_product[$name];
										$folder=mslib_befe::getImagePrefixFolder($filename);
										if (!file_exists($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$filename)) {
											$old_product[$name]='';
											if ($this->msLogFile) {
												file_put_contents($this->msLogFile, $this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$filename.' does not exist. Trying to re-import the product'.$x.' image.'."\n", FILE_APPEND);
											}
										}
									}
								}
							}
							if ($import_product_images) {
								if (!$item['products_name']) {
									// for partial feeds that dont provide products name, but we can find it in the DB
									$str="SELECT * FROM `tx_multishop_products_description` where products_id='".$item['updated_products_id']."'";
									$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
									$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
									$item['products_name']=$row['products_name'];
								}
								mslib_befe::saveImportedProductImages($item['updated_products_id'], $this->post['input'], $item, $old_product, $this->msLogFile);
								unset($item['img']);
							}
							$updateArray=array();
							if (isset($item['products_meta_title'])) {
								$updateArray['products_meta_title']=$item['products_meta_title'];
							}
							if (isset($item['products_meta_description'])) {
								$updateArray['products_meta_description']=$item['products_meta_description'];
							}
							if (isset($item['products_meta_keywords'])) {
								$updateArray['products_meta_keywords']=$item['products_meta_keywords'];
							}
							if (isset($item['products_name']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_name', $importedProductsLockedFields))))) {
								$updateArray['products_name']=$item['products_name'];
							}
							/* if ($item['products_description_encoded']) {
								$updateArray['products_description'] = $item['products_description_encoded'];
							} elseif ($item['products_description']) {
								$updateArray['products_description'] = $item['products_description'];
							} */
							if (isset($item['products_description_encoded']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_description', $importedProductsLockedFields))))) {
								$updateArray['products_description']=$item['products_description_encoded'];
							} elseif ($item['products_description'] and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_description', $importedProductsLockedFields))))) {
								$updateArray['products_description']=$item['products_description'];
							}
							if (isset($item['products_shortdescription'])) {
								$updateArray['products_shortdescription']=$item['products_shortdescription'];
							}
							if ($this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS']) {
								for ($x=1; $x<=$this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS']; $x++) {
									if (isset($item['products_description_tab_title_'.$x])) {
										$updateArray['products_description_tab_title_'.$x]=$item['products_description_tab_title_'.$x];
									}
									if (isset($item['products_description_tab_content_'.$x])) {
										$updateArray['products_description_tab_content_'.$x]=$item['products_description_tab_content_'.$x];
									}
								}
							}
							if (isset($item['products_deeplink'])) {
								$updateArray['products_url']=$item['products_deeplink'];
							}
							if (isset($item['products_delivery_time'])) {
								$updateArray['delivery_time']=$item['products_delivery_time'];
							}
							if (count($updateArray)) {
								// custom hook that can be controlled by third-party plugin
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateProductsDescriptionPreHook'])) {
									$params=array(
										'updateArray'=>&$updateArray,
										'item'=>&$item,
										'prefix_source_name'=>$this->post['prefix_source_name']
									);
									foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateProductsDescriptionPreHook'] as $funcRef) {
										\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
									}
								}
								// custom hook that can be controlled by third-party plugin eof
								$filter=array();
								$filter[]='language_id='.$language_id;
								$filter[]='(page_uid=0 or page_uid='.$this->showCatalogFromPage.')';
								$record=mslib_befe::getRecord($item['updated_products_id'], 'tx_multishop_products_description', 'products_id', $filter);
								if (is_array($record) && $record['products_id']) {
									$updateArray['page_uid']=$this->showCatalogFromPage;
									$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_description', 'products_id='.$item['updated_products_id'].' AND (page_uid=0 or page_uid=\''.$this->showCatalogFromPage.'\') and language_id='.$language_id, $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								} else {
									$updateArray['products_id']=$item['updated_products_id'];
									$updateArray['language_id']=$language_id;
									$updateArray['page_uid']=$this->showCatalogFromPage;
									$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $updateArray);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
								}
								// LANGUAGE OVERLAYS
								foreach ($this->languages as $langKey=>$langTitle) {
									if ($langKey>0) {
										$suffix='_'.$langKey;
										$updateArray2=$updateArray;
										foreach ($updateArray2 as $key=>$val) {
											if (isset($item[$key.$suffix]) && $item[$key.$suffix]!='') {
												$updateArray2[$key]=$item[$key.$suffix];
											}
										}
										$updateArray2['language_id']=$langKey;
										// get existing record
										$filter=array();
										$filter[]='language_id='.$langKey;
										$filter[]='(page_uid=0 or page_uid='.$this->showCatalogFromPage.')';
										$record=mslib_befe::getRecord($item['updated_products_id'], 'tx_multishop_products_description', 'products_id', $filter);
										if ($record['products_id']) {
											$updateArray2['page_uid']=$this->showCatalogFromPage;
											$updateArray2=mslib_befe::rmNullValuedKeys($updateArray2);
											$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_description', 'products_id='.$item['updated_products_id'].' AND (page_uid=0 or page_uid=\''.$this->showCatalogFromPage.'\') and language_id='.$langKey, $updateArray2);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										} else {
											// add new record
											$updateArray2['products_id']=$item['updated_products_id'];
											$updateArray2['page_uid']=$this->showCatalogFromPage;
											$updateArray2=mslib_befe::rmNullValuedKeys($updateArray2);
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $updateArray2);
											if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
												$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
											}
										}
									}
								}
								// LANGUAGE OVERLAYS EOL
							}
							if (isset($item['products_specials_price']) && ($item['products_specials_price']<$item['products_price'] && $item['products_specials_price']>0)) {
								$updateArray=array();
								$updateArray['specials_new_products_price']=$item['products_specials_price'];
								if (strstr($updateArray['specials_new_products_price'], ",")) {
									$updateArray['specials_new_products_price']=str_replace(",", '.', $updateArray['specials_new_products_price']);
								}
								$updateArray['specials_last_modified']=time();
								if (isset($item['products_special_price_start_date'])) {
									$time=0;
									if ($item['products_special_price_start_date'] >0 && (mslib_befe::isValidDateTime($item['products_special_price_start_date']) || mslib_befe::isValidDate($item['products_special_price_start_date']))) {
										$time=strtotime($item['products_special_price_start_date']);
									}
									$updateArray['start_date']=$time;
								}
								if (isset($item['products_special_price_expiry_date'])) {
									$time=0;
									if ($item['products_special_price_expiry_date'] >0 && (mslib_befe::isValidDateTime($item['products_special_price_expiry_date']) || mslib_befe::isValidDate($item['products_special_price_expiry_date']))) {
										$time=strtotime($item['products_special_price_expiry_date']);
									}
									$updateArray['expires_date']=$time;
								}
								// custom hook that can be controlled by third-party plugin
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateSpecialsPricePreHook'])) {
									$params=array(
										'updateArray'=>&$updateArray,
										'item'=>&$item,
										'prefix_source_name'=>$this->post['prefix_source_name']
									);
									foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateSpecialsPricePreHook'] as $funcRef) {
										\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
									}
								}
								// custom hook that can be controlled by third-party plugin eof
								$str="select 1 from tx_multishop_specials where products_id='".$item['updated_products_id']."'";
								$res=$GLOBALS['TYPO3_DB']->sql_query($str);
								if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
									$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_specials', 'products_id='.$item['updated_products_id'], $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								} else {
									$updateArray['products_id']=$item['updated_products_id'];
									$updateArray['specials_date_added']=time();
									$updateArray['page_uid']=$this->showCatalogFromPage;
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials', $updateArray);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
								}
								$str="select specials_id from tx_multishop_specials where products_id='".$item['updated_products_id']."'";
								$res=$GLOBALS['TYPO3_DB']->sql_query($str);
								if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
									$specials_id='';
									while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
										$specials_id=$row['specials_id'];
										$query2=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_specials_sections', 'specials_id='.$row['specials_id']);
										$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
									}
									if ($item['products_specials_section'] and $specials_id) {
										$sections=array();
										if ($this->post['input'][$i] && strstr($item['products_specials_section'], $this->post['input'][$i])) {
											$sections=explode($this->post['input'][$i], $item['products_specials_section']);
										} else {
											$sections[]=$item['products_specials_section'];
										}
										foreach ($sections as $section) {
											$updateArray=array();
											$updateArray['specials_id']=$specials_id;
											$updateArray['date']=time();
											$updateArray['name']=$section;
											$updateArray['status']=1;
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials_sections', $updateArray);
											if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
												$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
											}
										}
									}
								}
							} elseif ($item['products_price'] && $item['updated_products_id']) {
								// delete any special
								$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_specials', 'products_id='.$item['updated_products_id']);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
							$content.=ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_product'))).' "<strong>'.($item['products_name'] ? $item['products_name'] : $item['extid']).'</strong>" '.$this->pi_getLL('has_been_adjusted').'.<br />';
							if ($this->ms['target-cid'] && (!is_array($this->ms['products_to_categories_array']) || !count($this->ms['products_to_categories_array']))) {
								$this->ms['products_to_categories_array']=array();
								$this->ms['products_to_categories_array'][]=$this->ms['target-cid'];
							}
							if (count($this->ms['products_to_categories_array']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('categories_id', $importedProductsLockedFields))))) {
								if (!$this->post['incremental_update']) {
									$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_categories', 'products_id='.$item['updated_products_id']);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								}
								foreach ($this->ms['products_to_categories_array'] as $categories_id) {
									$updateArray=array();
									$updateArray['products_id']=$item['updated_products_id'];
									$updateArray['categories_id']=$categories_id;
									$updateArray['page_uid']=$this->showCatalogFromPage;
									if (isset($item['products_sort_order'])) {
										$updateArray['sort_order']=$item['products_sort_order'];
									} else {
										if (is_numeric($sortOrderArray['tx_multishop_products_to_categories']['sort_order'])) {
											$sortOrderArray['tx_multishop_products_to_categories']['sort_order']+=1;
										} else {
											$sortOrderArray['tx_multishop_products_to_categories']['sort_order']=time();
										}
										$updateArray['sort_order']=$sortOrderArray['tx_multishop_products_to_categories']['sort_order'];
									}
									/*$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);*/
									// create categories tree linking
									tx_mslib_catalog::linkCategoriesTreeToProduct($item['updated_products_id'], $categories_id, $updateArray);
								}
							}
						} elseif ($item['products_name']) {
							/***********************
							 * // INSERT PRODUCT MODE /
							 ***********************/
							if (!$item['products_date_added']) {
								$item['products_date_added']=date("Y-m-d G:i:s");
							}
							// date available
							if (!$item['products_date_available']) {
								$item['products_date_available']=date("Y-m-d G:i:s");
							}
							// date modified
							if (!$item['products_date_modified']) {
								$item['products_date_modified']=date("Y-m-d G:i:s");
							}
							// if status is not defined put it to 1
							if (!isset($item['products_status'])) {
								$item['products_status']=1;
							}
							// if quantity is not defined put it to 999
							if (!isset($item['products_quantity'])) {
								$item['products_quantity']=999;
							}
							if (!$item['products_shortdescription'] && $item['products_description']) {
								$item['products_shortdescription']=$item['products_description'];
								if (!$this->ms['MODULES']['PRODUCTS_SHORT_DESCRIPTION_CONTAINS_HTML_MARKUP']) {
									$item['products_shortdescription']=strip_tags($item['products_shortdescription']);
								}
							}
							if (!$item['products_description']) {
								$item['products_description']=nl2br($item['products_shortdescription']);
							}
							// lets add the new product to the products table
							$updateArray=array();
							if (isset($item['tax_id']) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_vat_rate', $importedProductsLockedFields))))) {
								$updateArray['tax_id']=$item['tax_id'];
							}
							if ($item['products_id']) {
								$updateArray['products_id']=$item['products_id'];
							}
							$updateArray['products_model']=$item['products_model'];
							$updateArray['products_status']=$item['products_status'];
							$updateArray['sku_code']=$item['products_sku'];
							if (isset($item['manufacturers_products_id'])) {
								$updateArray['vendor_code']=$item['manufacturers_products_id'];
							}
							if (isset($item['order_unit_id'])) {
								$updateArray['order_unit_id']=$item['order_unit_id'];
							}
							switch ($item['products_quantity']) {
								case 'Y':
									$item['products_quantity']=1;
									break;
								case 'N':
									$item['products_quantity']=0;
									break;
							}
							$updateArray['products_quantity']=$item['products_quantity'];
							$updateArray['extid']=$item['extid'];
							if ((isset($item['products_price']) or isset($item['products_old_price'])) and (!$item['imported_product'] or ($item['imported_product'] and (!is_array($importedProductsLockedFields) || is_array($importedProductsLockedFields) && !in_array('products_price', $importedProductsLockedFields))))) {
								if ($item['products_old_price']) {
									$updateArray['products_price']=$item['products_old_price'];
								} elseif ($item['products_price']) {
									$updateArray['products_price']=$item['products_price'];
								}
							}
							if (isset($item['products_staffel_price'])) {
								$updateArray['staffel_price']=$item['products_staffel_price'];
							}
							if ($item['products_weight']) {
								$updateArray['products_weight']=$item['products_weight'];
							}
							if (isset($item['alert_quantity_threshold'])) {
								$updateArray['alert_quantity_threshold']=$item['alert_quantity_threshold'];
							}
							if ($this->ms['MODULES']['DISPLAY_MANUFACTURERS_ADVICE_PRICE_INPUT'] && isset($item['manufacturers_advice_price'])) {
								$updateArray['manufacturers_advice_price']=$item['manufacturers_advice_price'];
							}
							if ($item['products_capital_price']) {
								$updateArray['product_capital_price']=$item['products_capital_price'];
							}
							if (isset($item['products_condition'])) {
								$updateArray['products_condition']=$item['products_condition'];
							}
							if ($item['products_minimum_quantity']) {
								$updateArray['minimum_quantity']=$item['products_minimum_quantity'];
							}
							if ($item['products_maximum_quantity']) {
								$updateArray['maximum_quantity']=$item['products_maximum_quantity'];
							}
							if ($item['products_multiplication']) {
								$updateArray['products_multiplication']=$item['products_multiplication'];
							}
							if (isset($item['products_ean'])) {
								$updateArray['ean_code']=$item['products_ean'];
							}
							if (strstr($updateArray['products_price'], ",")) {
								$updateArray['products_price']=str_replace(",", '.', $updateArray['products_price']);
							}
							if (strstr($updateArray['product_capital_price'], ",")) {
								$updateArray['product_capital_price']=str_replace(",", '.', $updateArray['product_capital_price']);
							}
							$updateArray['products_date_added']=strtotime($item['products_date_added']);
							$updateArray['products_date_available']=strtotime($item['products_date_available']);
							$updateArray['products_last_modified']=strtotime($item['products_date_modified']);
							$updateArray['page_uid']=$this->showCatalogFromPage;
							$updateArray['manufacturers_id']=$item['manufacturers_id'];
							$updateArray['imported_product']=1;
							if ($this->get['job_id']) {
								$updateArray['import_job_id']=$this->get['job_id'];
							}
							if ($item['products_unique_identifier']) {
								// save also the feed products_id, maybe we need it later
								$updateArray['foreign_products_id']=$item['products_unique_identifier'];
							}
							if ($this->post['prefix_source_name']) {
								// save also the feed source name, maybe we need it later
								$updateArray['foreign_source_name']=$this->post['prefix_source_name'];
							}
							if (isset($item['products_sort_order'])) {
								$updateArray['sort_order']=$item['products_sort_order'];
							}
							/*
							if (!isset($item['products_status'])) {
								// incremental updates must also have status=1
								$updateArray['products_status']=1;
							}
							*/
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertProductPreHook'])) {
								$params=array(
									'updateArray'=>&$updateArray,
									'item'=>&$item,
									'prefix_source_name'=>$this->post['prefix_source_name']
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertProductPreHook'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// TYPO3 6.2 NULL VALUE FIX
							if (!isset($updateArray['sku_code'])) {
								$updateArray['sku_code']='';
							}
							// custom hook that can be controlled by third-party plugin eof
							$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products', $updateArray);
							if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
								$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
							}
							$item['added_products_id']=$GLOBALS['TYPO3_DB']->sql_insert_id();
							$stats['products_added']++;
							$products_id=$item['added_products_id'];
							if ($products_id==0 and $this->get['run_as_cron']) {
								// error. lets print the error
								$message='ERROR QUERY FAILED: '.$query."\n";
								if ($this->msLogFile) {
									file_put_contents($this->msLogFile, $message, FILE_APPEND);
								}
							}
							// lets add the new product to the products description table
							$updateArray=array();
							$updateArray['language_id']=$language_id;
							$updateArray['products_id']=$item['added_products_id'];
							$updateArray['products_meta_title']=$item['products_meta_title'];
							$updateArray['products_meta_description']=$item['products_meta_description'];
							$updateArray['products_meta_keywords']=$item['products_meta_keywords'];
							$updateArray['products_name']=$item['products_name'];
							$updateArray['products_description']=$item['products_description'];
							if ($this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS']) {
								for ($x=1; $x<=$this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS']; $x++) {
									if ($item['products_description_tab_title_'.$x]) {
										$updateArray['products_description_tab_title_'.$x]=$item['products_description_tab_title_'.$x];
									}
									if ($item['products_description_tab_content_'.$x]) {
										$updateArray['products_description_tab_content_'.$x]=$item['products_description_tab_content_'.$x];
									}
								}
							}
							$updateArray['products_shortdescription']=$item['products_shortdescription'];
							$updateArray['products_url']=$item['products_deeplink'];
							$updateArray['delivery_time']=$item['products_delivery_time'];
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertProductsDescriptionPreHook'])) {
								$params=array(
									'updateArray'=>&$updateArray,
									'item'=>&$item,
									'prefix_source_name'=>$this->post['prefix_source_name']
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertProductsDescriptionPreHook'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party plugin eof
							$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $updateArray);
							if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
								$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
							}
							// LANGUAGE OVERLAYS
							foreach ($this->languages as $langKey=>$langTitle) {
								if ($langKey>0) {
									$suffix='_'.$langKey;
									$updateArray2=$updateArray;
									foreach ($updateArray2 as $key=>$val) {
										if (isset($item[$key.$suffix]) && $item[$key.$suffix]!='') {
											$updateArray2[$key]=$item[$key.$suffix];
										}
									}
									$updateArray2['language_id']=$langKey;
									// add new record
									$updateArray2=mslib_befe::rmNullValuedKeys($updateArray2);
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $updateArray2);
									if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
								}
							}
							// LANGUAGE OVERLAYS EOL
							// lets add the new product to the products to categories table
							if ($this->ms['target-cid'] && (!is_array($this->ms['products_to_categories_array']) || !count($this->ms['products_to_categories_array']))) {
								$this->ms['products_to_categories_array']=array();
								$this->ms['products_to_categories_array'][]=$this->ms['target-cid'];
							}
							if (count($this->ms['products_to_categories_array'])) {
								foreach ($this->ms['products_to_categories_array'] as $categories_id) {
									$updateArray=array();
									$updateArray['products_id']=$item['added_products_id'];
									$updateArray['categories_id']=$categories_id;
									$updateArray['page_uid']=$this->showCatalogFromPage;
									if (isset($item['products_sort_order'])) {
										$updateArray['sort_order']=$item['products_sort_order'];
									} else {
										if (is_numeric($sortOrderArray['tx_multishop_products_to_categories']['sort_order'])) {
											$sortOrderArray['tx_multishop_products_to_categories']['sort_order']+=1;
										} else {
											$sortOrderArray['tx_multishop_products_to_categories']['sort_order']=time();
										}
										$updateArray['sort_order']=$sortOrderArray['tx_multishop_products_to_categories']['sort_order'];
									}
									/*$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $updateArray);
									$res=$GLOBALS['TYPO3_DB']->sql_query($query);*/
									// create categories tree linking
									tx_mslib_catalog::linkCategoriesTreeToProduct($item['added_products_id'], $categories_id, $updateArray);
									$inserteditems[$categories_id][]=$item['products_name'];
								}
							}
							if ($item['products_specials_price'] and ($item['products_specials_price']<$item['products_price'])) {
								// product has a specials price, lets add it
								$updateArray=array();
								$updateArray['products_id']=$item['added_products_id'];
								$updateArray['specials_new_products_price']=$item['products_specials_price'];
								if (strstr($updateArray['specials_new_products_price'], ",")) {
									$updateArray['specials_new_products_price']=str_replace(",", '.', $updateArray['specials_new_products_price']);
								}
								$updateArray['specials_date_added']=time();
								if ($item['products_special_price_expiry_date']) {
									$updateArray['expires_date']=strtotime($item['products_special_price_expiry_date']);
								}
								if ($item['products_special_price_start_date']) {
									$updateArray['start_date']=strtotime($item['products_special_price_start_date']);
								}
								$updateArray['status']=1;
								$updateArray['page_uid']=$this->showCatalogFromPage;
								// custom hook that can be controlled by third-party plugin
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertSpecialsPricePreHook'])) {
									$params=array(
										'updateArray'=>&$updateArray,
										'item'=>&$item,
										'prefix_source_name'=>$this->post['prefix_source_name']
									);
									foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertSpecialsPricePreHook'] as $funcRef) {
										\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
									}
								}
								// custom hook that can be controlled by third-party plugin eof
								$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials', $updateArray);
								if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query))  {
									$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
								}
							}
							mslib_befe::saveImportedProductImages($item['added_products_id'], $this->post['input'], $item);
							// lets add this new product eof
						}
						// new attribute
						// first delete if any
						if (!$this->post['incremental_update'] and $products_id) {
							$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_attributes', 'products_id='.$products_id.' and (page_uid=0 or page_uid='.$this->showCatalogFromPage.')');
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
						if (is_array($item['attribute_option_value_including_vat'])) {
							// these attributes need further processing since the prices are including VAT
							// we will substract the VAT of each option value and then copy it to the traditional $item['attribute_option_value'] array
							foreach ($item['attribute_option_value_including_vat'] as $option_row) {
								if ($option_row[2]>0) {
									// if attribute option value has price
									if (substr_count($option_row[2], '.')>1) {
										// this amount has double dot (Excel issue). We need to strip the first dot.
										$option_row[2]=preg_replace("/\./", "", $option_row[2], 1);
									}
									$vatRate=0;
									if ($item['products_vat_rate']) {
										$vatRate=$item['products_vat_rate'];
									} else {
										/*
										// we have to find the product to get the right VAT rate
										if ($old_product) {
											print_r($old_product);
											die();
										}
										*/
									}
									if ($vatRate) {
										// reduce VAT from the price
										$price=($option_row[2]/(100+$vatRate)*100);
										$option_row[2]=number_format($price, 16, '.', '');
									}
								}
								// now add it to the attribute_option_value array for further processing
								$item['attribute_option_value'][]=$option_row;
							}
						}
						if (is_array($item['attribute_option_value'])) {
							$tx_multishop_products_attributes_sort_order_option_value=time();
							foreach ($item['attribute_option_value'] as $option_row) {
								$tx_multishop_products_attributes_sort_order_option_value++;
								$option_price='';
								if (isset($option_row[2])) {
									$option_price=$option_row[2];
								}
								// if option name is defined as an individual col (not through aux)
								if (!$option_row[0] and $item['attribute_option_name']) {
									$option_name=trim($item['attribute_option_name']);
								} else {
									$option_name=trim($option_row[0]);
								}
								$option_value=trim($option_row[1]);
								if ($option_name and $option_value) {
									// first chk if the option already exists and if not add it
									$sql_chk="select products_options_id from tx_multishop_products_options where products_options_name='".addslashes($option_name)."' and language_id='".$language_id."'";
									$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
									$rs_chk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
									if ($rs_chk['products_options_id']) {
										$products_options_id=$rs_chk['products_options_id'];
									} else {
										// add the option
										$insertArray=array();
										$insertArray['language_id']=$language_id;
										$insertArray['products_options_name']=$option_name;
										$insertArray['listtype']='pulldownmenu';
										$insertArray['attributes_values']='0';
										if ($sortOrderArray['tx_multishop_products_options']['sort_order']) {
											$sortOrderArray['tx_multishop_products_options']['sort_order']++;
										} else {
											$sortOrderArray['tx_multishop_products_options']['sort_order']=time();
										}
										$insertArray['sort_order']=$sortOrderArray['tx_multishop_products_options']['sort_order'];
										$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
										if (!$GLOBALS['TYPO3_DB']->sql_query($query))  {
											$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
										}
										$products_options_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									}
									// LANGUAGE OVERLAYS for products options
									foreach ($this->languages as $langKey=>$langTitle) {
										if ($langKey>0) {
											$insertArray=array();
											$insertArray['products_options_name']=$option_name;
											$insertArray['attributes_values']='0';
											if ($sortOrderArray['tx_multishop_products_options']['sort_order']) {
												$sortOrderArray['tx_multishop_products_options']['sort_order']++;
											} else {
												$sortOrderArray['tx_multishop_products_options']['sort_order']=time();
											}
											$insertArray['sort_order']=$sortOrderArray['tx_multishop_products_options']['sort_order'];
											$insertArray['products_options_id']=$products_options_id;
											$insertArray['language_id']=$langKey;
											// get existing record
											$record=mslib_befe::getRecord($products_options_id, 'tx_multishop_products_options', 'products_options_id', array(0=>'language_id='.$langKey));
											if ($record['products_options_id']) {
												$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
												$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id='.$products_options_id.' and language_id='.$langKey, $insertArray);
												$res=$GLOBALS['TYPO3_DB']->sql_query($query);
											} else {
												$insertArray['listtype']='pulldownmenu';
												// add new record
												$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
												$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
												if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
													$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
												}
											}
										}
									}
									// LANGUAGE OVERLAYS for products options EOL
									if ($products_options_id and $option_value) {
										$str2="SELECT pov.products_options_values_id from tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov where povp.products_options_id='".addslashes($products_options_id)."' and pov.products_options_values_name='".addslashes($option_value)."' and povp.products_options_values_id=pov.products_options_values_id";
										$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
										$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
										if ($rows2) {
											$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
											$option_value_id=$row2['products_options_values_id'];
										} else {
											$str2="SELECT products_options_values_id from tx_multishop_products_options_values where language_id='".$language_id."' and products_options_values_name='".addslashes($option_value)."'";
											$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
											$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
											if (!$rows2) {
												$insertArray=array();
												$insertArray['language_id']=$language_id;
												$insertArray['products_options_values_name']=$option_value;
												$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
												$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
												if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
													$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
												}
												$option_value_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
											} else {
												$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
												$option_value_id=$row2['products_options_values_id'];
											}
										}
										// LANGUAGE OVERLAYS for products options
										foreach ($this->languages as $langKey=>$langTitle) {
											if ($langKey>0) {
												$insertArray=array();
												$insertArray['products_options_values_id']=$option_value_id;
												$insertArray['language_id']=$language_id;
												$insertArray['products_options_values_name']=$option_value;
												$insertArray['language_id']=$langKey;
												// get existing record
												$record=mslib_befe::getRecord($option_value_id, 'tx_multishop_products_options_values', 'products_options_values_id', array(0=>'language_id='.$langKey));
												if ($record['products_options_values_id']) {
													$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
													$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', 'products_options_values_id='.$option_value_id.' and language_id='.$langKey, $insertArray);
													$res=$GLOBALS['TYPO3_DB']->sql_query($query);
												} else {
													// add new record
													$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
													$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
													if (!$res=$GLOBALS['TYPO3_DB']->sql_query($query)) {
														$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
													}
												}
											}
										}
										// LANGUAGE OVERLAYS for products options values EOL
										if ($products_options_id and $option_value_id) {
											if ($this->post['incremental_update'] and $products_id) {
												$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_attributes', 'products_id='.$products_id.' and options_id=\''.$products_options_id.'\' and options_values_id=\''.$option_value_id.'\' and (page_uid=0 or page_uid=\''.$this->showCatalogFromPage.'\')');
												$res=$GLOBALS['TYPO3_DB']->sql_query($query);
											}
											//$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values_to_products_options', 'products_options_id='.$products_options_id." and products_options_values_id='".$option_value_id."'");
											//$res=$GLOBALS['TYPO3_DB']->sql_query($query);
											$filter=array();
											$filter[]='products_options_values_id=\''.addslashes($option_value_id).'\'';
											if (!mslib_befe::ifExists($products_options_id, 'tx_multishop_products_options_values_to_products_options', 'products_options_id', $filter)) {
												$insertArray=array();
												$insertArray['products_options_id']=$products_options_id;
												$insertArray['products_options_values_id']=$option_value_id;
												if ($sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order']) {
													$sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order']++;
												} else {
													$sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order']=time();
												}
												$insertArray['sort_order']=$sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order'];
												$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
												$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values_to_products_options', $insertArray);
												if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
													$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
												}
											}
											if ($products_id and $products_options_id and $option_value_id) {
												$insertArray=array();
												$insertArray['products_id']=$products_id;
												$insertArray['options_id']=$products_options_id;
												$insertArray['options_values_id']=$option_value_id;
												$insertArray['options_values_price']=$option_price;
												$insertArray['price_prefix']='+';
												$insertArray['page_uid']=$this->showCatalogFromPage;
												$insertArray['sort_order_option_value']=$tx_multishop_products_attributes_sort_order_option_value;
												$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
												$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes', $insertArray);
												if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
													$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
												}
											}
										}
									}
								}
							}
						}
						// new attribute eof
						// predefined attribute option mappings
						$str="SELECT * FROM `tx_multishop_products_options` where language_id='".$language_id."' order by products_options_id asc";
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
							$s='attribute_option_name_'.$row['products_options_id'];
							if ($item[$s]) {
								$products_options_id=$row['products_options_id'];
								$option_value=$item[$s];
								$str2="SELECT pov.products_options_values_id from tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov where povp.products_options_id='".$products_options_id."' and pov.products_options_values_name='".addslashes($option_value)."' and povp.products_options_values_id=pov.products_options_values_id";
								$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
								$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
								if ($rows2) {
									$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
									$option_value_id=$row2['products_options_values_id'];
								} else {
									$str2="SELECT products_options_values_id from tx_multishop_products_options_values where language_id='".$language_id."' and products_options_values_name='".addslashes($option_value)."'";
									$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
									$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
									if (!$rows2) {
										$insertArray=array();
										$insertArray['language_id']=$language_id;
										$insertArray['products_options_values_name']=$option_value;
										$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $insertArray);
										if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
											$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
										}
										$option_value_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									} else {
										$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
										$option_value_id=$row2['products_options_values_id'];
									}
								}
								if ($option_value_id) {
									// now check if the option name and the option value has a valid pair
									$str2="SELECT pov.products_options_values_id from tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov where povp.products_options_id='".addslashes($products_options_id)."' and pov.products_options_values_id='".addslashes($option_value_id)."' and povp.products_options_values_id=pov.products_options_values_id";
									$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
									$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
									if (!$rows2) {
										$insertArray=array();
										$insertArray['products_options_id']=$products_options_id;
										$insertArray['products_options_values_id']=$option_value_id;
										if ($sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order']) {
											$sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order']++;
										} else {
											$sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order']=time();
										}
										$insertArray['sort_order']=$sortOrderArray['tx_multishop_products_options_values_to_products_options']['sort_order'];
										$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values_to_products_options', $insertArray);
										if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
											$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
										}
									}
									// added 2013-07-31 due to double records when re-importing the same partial feed
									if ($this->post['incremental_update'] and $products_id) {
										$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_attributes', 'products_id='.$products_id.' and options_id=\''.$products_options_id.'\' and options_values_id=\''.$option_value_id.'\' and (page_uid=0 or page_uid=\''.$this->showCatalogFromPage.'\')');
										$res=$GLOBALS['TYPO3_DB']->sql_query($query);
									}
									$insertArray=array();
									$insertArray['products_id']=$products_id;
									$insertArray['options_id']=$products_options_id;
									$insertArray['options_values_id']=$option_value_id;
									$insertArray['options_values_price']=$option_price;
									$insertArray['price_prefix']='+';
									$insertArray['page_uid']=$this->showCatalogFromPage;
									$insertArray['sort_order_option_value']=time();
									$insertArray=mslib_befe::rmNullValuedKeys($insertArray);
									$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes', $insertArray);
									if (!$GLOBALS['TYPO3_DB']->sql_query($query)) {
										$erno[]=$query.'<br/>'.$GLOBALS['TYPO3_DB']->sql_error();
									}
								}
							}
						}
						// predefined attribute option mappings eof
						// payment & shipping method mapped to product
						if ($this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER']) {
							$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_method_mappings', 'products_id=\''.$products_id.'\'');
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							// loading payment methods
							$payment_methods=mslib_fe::loadPaymentMethods();
							// loading shipping methods
							$shipping_methods=mslib_fe::loadShippingMethods();
							if (count($payment_methods) or count($shipping_methods)) {
								if (count($payment_methods)) {
									foreach ($payment_methods as $payment_code => $payment_item) {
										$s='product_payment_methods_' . $payment_code;
										if ($item[$s]!='') {
											$updateArray=array();
											$updateArray['products_id']=$products_id;
											$updateArray['method_id']=$payment_item['id'];
											$updateArray['type']='payment';
											$updateArray['negate']=(!$item[$s] ? 1 : 0);
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_method_mappings', $updateArray);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										}
									}
								}
								if (count($shipping_methods)) {
									foreach ($shipping_methods as $shipping_code => $shipping_item) {
										$s='product_shipping_methods_' . $shipping_code;
										if ($item[$s]!='') {
											$updateArray=array();
											$updateArray['products_id']=$products_id;
											$updateArray['method_id']=$shipping_item['id'];
											$updateArray['type']='shipping';
											$updateArray['negate']=(!$item[$s] ? 1 : 0);
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_method_mappings', $updateArray);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										}
									}
								}
							}
						}
						// update flat database
						if ($this->ms['MODULES']['FLAT_DATABASE'] or $this->ms['MODULES']['GLOBAL_MODULES']['FLAT_DATABASE']) {
							if (isset($item['products_status']) and $item['products_status']=='0' and is_numeric($products_id)) {
								$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_flat', 'products_id='.$products_id);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							} else {
								mslib_befe::convertProductToFlat($products_id, 'tx_multishop_products_flat');
							}
						}
						// lets notify plugin that we have update action in product
						tx_mslib_catalog::productsUpdateNotifierForPlugin($item);
						// update flat database eof
						if ($item['added_products_id']) {
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertProductPostHook'])) {
								$params=array(
									'products_id'=>$item['added_products_id'],
									'item'=>&$item,
									'prefix_source_name'=>$this->post['prefix_source_name']
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertProductPostHook'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party plugin eof
						} elseif ($item['updated_products_id']) {
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateProductPostHook'])) {
								$params=array(
									'products_id'=>$item['updated_products_id'],
									'item'=>&$item,
									'prefix_source_name'=>$this->post['prefix_source_name']
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['updateProductPostHook'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party plugin eof
						}
						// custom hook that can be controlled by third-party plugin
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertAndUpdateProductPostHook'])) {
							$params=array(
									'products_id'=>($item['added_products_id'] ? $item['added_products_id'] : $item['updated_products_id']),
									'import_data_collector'=>&$import_data_collector,
									'item'=>&$item,
									'prefix_source_name'=>$this->post['prefix_source_name']
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['insertAndUpdateProductPostHook'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						// add/update eof
						if ($this->get['run_as_cron']) {
							$subtel++;
							$message=($item['updated_products_id'] ? 'Updated: ' : 'Added: ').$item['products_name']." (products_id: ".$products_id.", hashed id: ".$item['extid'].")\n";
							if ($subtel==50) {
								if ($start_time) {
									$end_time=microtime(true);
									$message.="----------------------------------\n";
									$ms_string=number_format(($end_time-$start_time), 3, '.', '');
									// calculate progress in percentage
									$completed_percentage=($item_counter/$total_datarows*100);
									// time approximately left
									$global_ms_string=number_format(($end_time-$global_start_time), 3, '.', '');
									$running_seconds=round($global_ms_string);
									if ($running_seconds>60) {
										$running_minutes=($running_seconds/60);
										if ($running_minutes>60) {
											$time_running=number_format(($running_minutes/60), 0, '.', '').' hour(s)';
										} else {
											$time_running=number_format($running_minutes, 0, '.', '').' minute(s)';
										}
									} else {
										$time_running=number_format($running_seconds, 0, '.', '').' seconds';
									}
									$estimated_seconds=round((((($end_time-$global_start_time)/$completed_percentage)*(100-$completed_percentage))));
									/*
									$message.="\n";
									$message.='global_ms_string: '.$global_ms_string.', ';
									$message.='completed_percentage: '.$completed_percentage.', ';
									$message.='estimated: '.((($global_ms_string/100)*(100-$completed_percentage))).'.'."\n";
									*/
									//$estimated_seconds=round(((($global_ms_string/$completed_percentage)*(100-$completed_percentage))));
									if ($estimated_seconds>60) {
										// ETR in hours or minutes
										$estimated_minutes=($estimated_seconds/60);
										if ($estimated_minutes>60) {
											$estimated_time_remaining=number_format(($estimated_minutes/60), 0, '.', '').' hour(s)';
										} else {
											$estimated_time_remaining=number_format($estimated_minutes, 0, '.', '').' minute(s)';
										}
									} else {
										$estimated_time_remaining=number_format($estimated_seconds, 0, '.', '').' second(s)';
									}
									$message.='50 products processed in: '.$ms_string.'ms. '.number_format(($total_datarows-$item_counter), 0, '', '.').' of '.number_format($total_datarows, 0, '', '.').' product(s) waiting for import ('.round($completed_percentage).'% / '.number_format($item_counter, 0, '', '.').' products imported).'."\n".'Job is running: '.($time_running).' and the estimated time remaining is: '.$estimated_time_remaining.'.'."\n";
									$message.="----------------------------------\n";
								}
								// reset timer and subtel
								$subtel=0;
								$start_time=microtime(true);
							}
							if ($this->msLogFile) {
								file_put_contents($this->msLogFile, $message, FILE_APPEND);
							}
							$content='';
						}
					}
					/***********************
					 * // INSERT PRODUCT EOF //
					 ***********************/
					if ($item['added_products_id'] and !$skip) {
						$content.=ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_product'))).' "<strong>'.$item['products_name'].'</strong>" '.$this->pi_getLL('has_been_added').'.<br />';
					}
				}
				if ($this->msLogFile) {
					$content='';
				}
				// end foreach
			}
//			if ($file_location and file_exists($file_location)) @unlink($file_location);
		}
		$stats['time_finished']=time();
		// custom hook that can be controlled by third-party plugin
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productsImportPostProcHook'])) {
			$params=array(
				'import_data_collector'=>$import_data_collector,
				'prefix_source_name'=>$this->post['prefix_source_name'],
				'stats'=>&$stats
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_import.php']['productsImportPostProcHook'] as $funcRef) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
			}
		}
		// custom hook that can be controlled by third-party plugin eof
		if ($this->msLogFile) {
			$end_time=microtime(true);
			$global_ms_string=number_format(($end_time-$global_start_time), 3, '.', '');
			$running_seconds=round($global_ms_string);
			if ($running_seconds>60) {
				$time_running=number_format(($running_seconds/60), 0, '.', '').' minute(s)';
			} else {
				$time_running=number_format(($running_seconds), 0, '.', '').' seconds';
			}
			file_put_contents($this->msLogFile, print_r($stats, 1)."\n".'Import task completed on: '.date("Y-m-d G:i:s", time()).' and took: '.$time_running.".\n", FILE_APPEND);
		}
	}
}
if ($this->post['action']!='product-import-preview' && $this->get['action']!='edit_job') {
	$this->ms['show_default_form']=1;
	$tmptab='';
	if ($content) {
		$tmptab.=mslib_befe::bootstrapPanel('Result',$content,'success');
		$content='';
	}
	$tmptab.='
	<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'" method="post" enctype="multipart/form-data" name="form1" id="form1" class="form-horizontal blockSubmitForm">
	'.$this->ms['upload_productfeed_form'].'
				<!-- <input name="cid" type="hidden" value="0" /> -->
	</form>';
	$tabs['Upload_To_Root']=array(
		$this->pi_getLL('import_to_root'),
		$tmptab
	);
	// tabber
	if ($this->ms['show_default_form']) {
		// load the jobs templates
		$str="SELECT * from tx_multishop_import_jobs where page_uid='".$this->shop_pid."' and (type='' or type='products') order by prefix_source_name asc, id desc";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$jobs=array();
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$jobs[]=$row;
		}
		if (count($jobs)>0) {
			$schedule_content.='
			<div id="scheduled_import_jobs_form" class="panel panel-default">
			<div class="panel-heading"><h3>'.$this->pi_getLL('import_tasks').'</h3></div>
			<div class="panel-body">
			<table class="table table-striped table-bordered msadmin_border no-mb" id="msAdminProducsImport">
			<thead>
			<tr>
			<th>'.$this->pi_getLL('source_name').'</th>
			<th class="cellName">'.$this->pi_getLL('name').'</th>
			<th>'.$this->pi_getLL('mapped_to_category').'</th>
			<th class="cellDate">'.$this->pi_getLL('last_run').'</th>
			<th class="cellAction">'.$this->pi_getLL('action').'</th>
			<th class="cellStatus">'.ucfirst($this->pi_getLL('status')).'</th>
			<th>'.ucfirst($this->pi_getLL('delete')).'</th>
			<th>'.$this->pi_getLL('file_exists').'</th>
			<th>'.$this->pi_getLL('upload_file').'</th>';
			if ($this->ROOTADMIN_USER) {
				$schedule_content.='<th>'.$this->pi_getLL('download_import_task').'</th>';
			}
			$schedule_content.='</tr></thead>';
			$schedule_content.='<tbody>';
			$switch='';
			$jsSelect2InitialValue=array();
			$jsSelect2InitialValue[]='var categoriesIdTerm=[];';
			$jsSelect2InitialValue[]='categoriesIdTerm[0]={id:"0", text:"'.htmlentities($this->pi_getLL('admin_main_category')).'"};';
			foreach ($jobs as $job) {
				if ($switch=='odd') {
					$switch='even';
				} else {
					$switch='odd';
				}
				$schedule_content.='<tr class="'.$switch.'">';
				$schedule_content.='<td>'.$job['prefix_source_name'].'</td>
				<td class="cellName"><a class="blockAhrefLink" href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&job_id='.$job['id']).'&action=edit_job">'.$job['name'].'</a></td>';
				$category_name='
				<form method="get" action="index.php" id="updateCatForm'.$job['id'].'">
					<input name="id" type="hidden" value="'.$this->showCatalogFromPage.'" />
					<input name="type" type="hidden" value="2003" />
					<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_import" />
					<input type="hidden" name="update_category_for_job['.$job['id'].']" value="'.$job['categories_id'].'" class="importCategoryTargetTree" rel="'.$job['id'].'" />
				</form>';
				//mslib_fe::tx_multishop_draw_pull_down_menu('update_category_for_job['.$job['id'].']', mslib_fe::tx_multishop_get_category_tree('', '', '', '', false, false, $this->pi_getLL('admin_main_category')), $job['categories_id'], 'onchange="if (CONFIRM(\''.addslashes($this->pi_getLL('are_you_sure')).'?\')) this.form.submit();"')
				$schedule_content.='<td>'.$category_name.'</td>';
				$schedule_content.='<td class="cellDate">'.date("Y-m-d", $job['last_run']).'<br />'.date("G:i:s", $job['last_run']).'</td>';
				if (!$job['period']) {
					$schedule_content.='<td>manual<br /><a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&job_id='.$job['id'].'&action=run_job&limit=99999999').'" class="btn btn-success msadminRunImporter" data-dialog-title=\'Warning\' data-dialog-body="'.addslashes(htmlspecialchars($this->pi_getLL('are_you_sure_you_want_to_run_the_import_job').': '.$job['name'].'?')).'">'.$this->pi_getLL('run_now').'</a><br /><a href="" class="copy_to_clipboard" rel="'.htmlentities('/usr/bin/wget -O /dev/null --tries=1 --timeout=86400 -q "'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&job_id='.$job['id'].'&code='.$job['code'].'&action=run_job&run_as_cron=1&limit=99999999', 1).'" >/dev/null 2>&1').'" >'.$this->pi_getLL('run_by_crontab').'</a></td>';
				} else {
					$schedule_content.='<td>'.date("Y-m-d G:i:s", $job['last_run']+$job['period']).'</td>';
				}
				$schedule_content.='<td class="status_field" align="center">';
				if (!$job['status']) {
					$schedule_content.='<span class="admin_status_red" alt="Disable"></span>';
					$schedule_content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&job_id='.$job['id'].'&status=1').'"><span class="admin_status_green disabled" alt="Enabled"></span></a>';
				} else {
					$schedule_content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&job_id='.$job['id'].'&status=0').'"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
					$schedule_content.='<span class="admin_status_green" alt="Enable"></span>';
				}
				$schedule_content.='</td>
				<td align="center">
				<a href="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import&delete=1&job_id='.$job['id']).'&action=delete_job" onClick="return CONFIRM(\'Are you sure you want to delete the import job: '.htmlspecialchars($job['name']).'?\')" alt="Remove '.htmlspecialchars($job['name']).'" class="btn btn-danger btn-sm admin_menu_remove" title="Remove '.htmlspecialchars($job['name']).'"><i class="fa fa-trash-o"></i></a>
				</td>
				<td align="center">
					';
				$data=unserialize($job['data']);
				if ($data[1]['filename']) {
					$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$data[1]['filename'];
					if (file_exists($file_location)) {
						$schedule_content.='<a href="uploads/tx_multishop/tmp/'.$data[1]['filename'].'"><span class="admin_status_green" alt="Enable"></span></a>';
					} else {
						$schedule_content.='<span class="admin_status_red" alt="Disable"></span>';
					}
				}
				$schedule_content.='
				</td>
				<td>
					 <form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import').'" method="post" enctype="multipart/form-data" name="form1" id="form1" class="blockSubmitForm">
					 <div class="input-group">
						<input type="file" name="file" class="form-control" style="width:300px" />
						<input name="skip_import" type="hidden" value="1" />
						<input name="preProcExistingTask" type="hidden" value="1" />
						<input name="job_id" type="hidden" value="'.$job['id'].'" />
						<input name="action" type="hidden" value="edit_job" />
						<span class="input-group-btn">
						<input type="submit" name="Submit" class="submit btn btn-success" id="cl_submit" value="'.$this->pi_getLL('upload').'" />
						</span>
						</div>
					</form>
				</td>';
				if ($this->ROOTADMIN_USER) {
					$schedule_content.='<td>
						<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_import&download=task&job_id='.$job['id']).'" class="btn btn-success"><i class="fa fa-download"></i> '.$this->pi_getLL('download_import_task').'</a>
					</td>';
				}
				$schedule_content.='</tr>';
				// build the select2 cache
				$cats=mslib_fe::Crumbar($job['categories_id']);
				$cats=array_reverse($cats);
				$catpath=array();
				foreach ($cats as $cat) {
					$catpath[]=$cat['name'];
				}
				if (count($catpath)>0) {
					$jsSelect2InitialValue[]='categoriesIdTerm['.$job['categories_id'].']={id:"'.$job['categories_id'].'", text:"'.implode(' > ', $catpath).'"};';
				}
			}
			$schedule_content.='</tbody>';
			$schedule_content.='</table>
			</div>
			</div>
			';
			$schedule_content.='
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$(".copy_to_clipboard").click(function(event) {
					event.preventDefault();
					var string=$(this).attr("rel");
					$.blockUI({
						theme:     true,
						title:    \''.addslashes($this->pi_getLL('copy_below_text_and_add_it_to_crontab')).'\',
						message:  \'<p>\'+string+\'</p>\',
						timeout:   8000
					});
				});
			});
			</script>
			';
			$tmptab='';
		}
		$schedule_content.='
		<script type="text/javascript">
		jQuery(document).ready(function($) {
				'.implode("\n", $jsSelect2InitialValue).'
				var categoriesIdSearchTerm=[];
		$(\'.importCategoryTargetTree\').select2({
			placeholder: "'.$this->pi_getLL('admin_select_category').'",
			dropdownCssClass: "", // apply css that makes the dropdown taller
			width:\'100%\',
			minimumInputLength: 0,
			//multiple: true,
			//allowClear: true,
			query: function(query) {
				$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getFullTree').'\', {
					data: {
						q: query.term
					},
					dataType: "json"
				}).done(function(data) {
					categoriesIdSearchTerm[query.term]=data;
					query.callback({results: data});
				});
			},
			initSelection: function(element, callback) {
				var id=$(element).val();
				if (id!=="") {
					if (categoriesIdTerm[id]!==undefined) {
						callback(categoriesIdTerm[id]);
					} else {
						$.ajax(\''.mslib_fe::typolink($this->shop_pid.',2002', '&tx_multishop_pi1[page_section]=get_category_tree&tx_multishop_pi1[get_category_tree]=getValues').'\', {
							data: {
								preselected_id: id
							},
							dataType: "json"
						}).done(function(data) {
							categoriesIdTerm[data.id]={id: data.id, text: data.text};
							callback(data);
						});
					}
				}
			},
			formatResult: function(data){
				if (data.text === undefined) {
					$.each(data, function(i,val){
						return val.text;
					});
				} else {
					return data.text;
				}
			},
			formatSelection: function(data){
				if (data.text === undefined) {
					return data[0].text;
				} else {
					return data.text;
				}
			},
			escapeMarkup: function (m) { return m; }
		}).on("select2-selecting", function(e) {
			if (CONFIRM(\''.addslashes($this->pi_getLL('are_you_sure')).'?\')) {
				$(this).val(e.object.id);
				var formId=\'#updateCatForm\' + $(this).attr("rel");
				$(formId).submit();
			} else {
				$(this).select2("close");
				e.preventDefault();
			}
		});
		});
		</script>
		';
		if ($this->ROOTADMIN_USER) {
			$schedule_content.='<div id="scheduled_import_jobs_form" class="panel panel-default no-mb">
			<div class="panel-heading"><h3>'.$this->pi_getLL('upload_import_task').'</h3></div>
			<div class="panel-body">
				<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_import&upload=task').'" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="form-horizontal blockSubmitForm">
					<div class="form-group">
						<label for="new_cron_name" class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
						<div class="col-md-10">
							<input name="new_cron_name" type="text" value="" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="new_prefix_source_name" class="control-label col-md-2">'.$this->pi_getLL('source_name').'</label>
						<div class="col-md-10">
							<input name="new_prefix_source_name" type="text" value="" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label for="upload_task_file" class="control-label col-md-2">'.$this->pi_getLL('file').'</label>
						<div class="col-md-10">
							<input type="file" name="task_file" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							<input type="submit" name="upload_task_file" class="submit btn btn-success" id="upload_task_file" value="upload">
						</div>
					</div>
				</form>
			</div>
			</div>';
		}
		$tabs['tasks']=array(
			$this->pi_getLL('import_tasks'),
			$schedule_content
		);
		// load the jobs templates eof
		$content.='
		<div class="panel-heading"><h3>'.ucfirst(mslib_befe::strtolower($this->pi_getLL('admin_import_products'))).'</h3></div>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
var url = document.location.toString();
if (url.match("#")) {
    $(".nav-tabs a[href=#"+url.split("#")[1]+"]").tab("show") ;
} else {
		$(".nav-tabs a:first").tab("show");
	}

// Change hash for page-reload
	$(".nav-tabs a").on("shown.bs.tab", function (e) {
		window.location.hash = e.target.hash;
		$("body,html,document").scrollTop(0);
	})

		});
		</script>
		<div class="panel-body">
		<div id="tab-container">
			<ul class="nav nav-tabs" role="tablist">
		';
		$count=0;
		foreach ($tabs as $key=>$value) {
			$count++;
			$content.='<li'.(($count==1) ? ' class=""' : '').' role="presentation"><a href="#'.$key.'" aria-controls="profile" role="tab" data-toggle="tab">'.$value[0].'</a></li>';
		}
		$content.='
			</ul>
			<div class="tab-content">

			';
		$count=0;
		foreach ($tabs as $key=>$value) {
			$count++;
			$content.='
				<div id="'.$key.'" class="tab-pane" role="tabpanel">
					'.$value[1].'
				</div>
			';
		}
		$content.=$save_block.'
			</div>
		</div>
		';
		$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
		$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
	}
}
if (count($erno)) {
	if ($this->get['run_as_cron']) {
		//todo mail the importer errors
	} else {
		// Print the errors
		$returnMarkup='
		<div style="display:none" id="msAdminPostMessage">
		<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>Error</th>
		</tr>
		</thead>
		<tbody>
		';
		foreach ($erno as $item) {
			$returnMarkup.='<tr><td>'.$item.'</td></tr>'."\n";
		}
		$returnMarkup.='</tbody></table></div>';
		$content.=$returnMarkup;
		$GLOBALS['TSFE']->additionalHeaderData[]='<script type="text/javascript" data-ignore="1">
		jQuery(document).ready(function ($) {
			var content=$(\'#msAdminPostMessage\').html();
			$.confirm({
				title: \'\',
				content: content
			});
		});
		</script>
		';

	}
}
if ($this->get['run_as_cron']) {
	@unlink($this->msLockFile);
	die();
}
?>