<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_time_limit(86400);
ignore_user_abort(true);
if ($this->get['delete'] and is_numeric($this->get['job_id'])) {
	// delete job
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_import_jobs', 'id='.$this->get['job_id'].' and type=\'customers\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
if (is_numeric($this->get['job_id']) and is_numeric($this->get['status'])) {
	// update the status of a job
	$updateArray=array();
	$updateArray['status']=$this->get['status'];
	$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=\''.$this->get['job_id'].'\' and type=\'customers\'', $updateArray);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	// update the status of a job eof
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
		$filename='multishop_customer_import_task_'.date('YmdHis').'_'.$this->get['job_id'].'.txt';
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
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			@unlink($target);
		}
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_customer_import').'#tasks');
	exit();
}
//$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
$default_country=$this->tta_shop_info['country'];
$GLOBALS['TSFE']->additionalHeaderData['tx_multishop_pi1_block_ui']=mslib_fe::jQueryBlockUI();
// define the different columns
$coltypes=array();
$coltypes['first_name']=$this->pi_getLL('first_name');
$coltypes['middle_name']=$this->pi_getLL('middle_name');
$coltypes['last_name']=$this->pi_getLL('last_name');
$coltypes['full_name']=$this->pi_getLL('full_name');
$coltypes['email']=$this->pi_getLL('email');
$coltypes['address']=$this->pi_getLL('address');
$coltypes['street_name']=$this->pi_getLL('street_address');
$coltypes['address_number']=$this->pi_getLL('street_address_number');
$coltypes['address_ext']=$this->pi_getLL('address_number_extension');
$coltypes['zip']=$this->pi_getLL('zip');
$coltypes['city']=$this->pi_getLL('city');
$coltypes['country']=$this->pi_getLL('country');
$coltypes['region']=$this->pi_getLL('region');
$coltypes['telephone']=$this->pi_getLL('telephone');
$coltypes['fax']=$this->pi_getLL('fax');
$coltypes['mobile']=$this->pi_getLL('mobile');
$coltypes['company_name']=$this->pi_getLL('company');
$coltypes['vat_id']=$this->pi_getLL('vat_id');
$coltypes['coc_id']=$this->pi_getLL('coc_id');
$coltypes['uid']=$this->pi_getLL('user_id');
$coltypes['gender']=$this->pi_getLL('gender');
$coltypes['password']=$this->pi_getLL('password');
$coltypes['password_hashed']=$this->pi_getLL('password_md5_hashed');
$coltypes['usergroup']=$this->pi_getLL('usergroup');
$coltypes['birthday']=$this->pi_getLL('birthday');
$coltypes['newsletter']=$this->pi_getLL('newsletter');
$coltypes['disable']=$this->pi_getLL('disable');
$coltypes['deleted']=$this->pi_getLL('deleted');
$coltypes['discount']=$this->pi_getLL('discount');
$coltypes['username']=$this->pi_getLL('username');
$coltypes['title']=$this->pi_getLL('job_title');
$coltypes['www']=$this->pi_getLL('website');
$coltypes['crdate']=$this->pi_getLL('creation_date');
$coltypes['language_code_2char_iso']=$this->pi_getLL('language_code', 'Language (2 char ISO code)');
$coltypes['tx_multishop_source_id']=$this->pi_getLL('customer_id_external_id_for_reference');
$coltypes['tx_multishop_payment_condition']=$this->pi_getLL('payment_condition', 'payment condition');
$coltypes['tx_multishop_customer_id']=$this->pi_getLL('tx_multishop_customer_id_field', 'tx_multishop_customer_id field');
// hook to let other plugins add more columns
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['adminCustomersImporterColtypesHook'])) {
	$params=array(
		'coltypes'=>&$coltypes
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['adminCustomersImporterColtypesHook'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
natsort($coltypes);
// define the different columns eof
if ($this->post['job_id']) {
	$this->get['job_id']=$this->post['job_id'];
}
if ($this->post['action']=='customer-import-preview' or (is_numeric($this->get['job_id']) and $_REQUEST['action']=='edit_job')) {
	// preview
	if (is_numeric($this->get['job_id'])) {
		$this->ms['mode']='edit';
		// load the job
		$str="SELECT * from tx_multishop_import_jobs where id='".$this->get['job_id']."' and type='customers'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$data=unserialize($row['data']);
		// copy the previous post data to the current post so it can run the job
		// again
		$this->post=$data[1];
		$this->post['cid']=$row['categories_id'];
		// enable file logging
		if ($this->get['relaxed_import']) {
			$this->post['relaxed_import']=$this->get['relaxed_import'];
		}
		// update the last run time
		$updateArray=array();
		$updateArray['last_run']=time();
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$row['id'], $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// update the last run time eof
	}
	if ($this->post['database_name']) {
		$file_location=$this->post['database_name'];
	} elseif ($this->post['file_url']) {
		if (strstr($this->post['file_url'], "../")) {
			die();
		}
		$filename=time();
		$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
		$file_content=mslib_fe::file_get_contents($this->post['file_url']);
		if (!$file_content or !file_put_contents($file_location, $file_content)) {
			die('cannot save the file or the file is empty');
		}
	} elseif ($this->ms['mode']=='edit') {
		$filename=$this->post['filename'];
		$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
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
	if ((file_exists($file_location) or $this->post['database_name'])) {
		if (!$this->post['database_name']) {
			$str=mslib_fe::file_get_contents($file_location);
		}
		if ($this->post['parser_template']) {
			if (strstr($this->post['parser_template'], "..")) {
				die();
			}
			// include a pre-defined xml to php array converter
			require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_import_parser_templates/'.$this->post['parser_template'].".php");
			// include a pre-defined xml to php array converter eof
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
						$rows[$i][$s]=$datacol;
						$s++;
					}
					$i++;
				}
			} else {
				if ($this->post['format']=='excel') {
					// try the generic way
					if (!$this->ms['mode']=='edit') {
						$filename='tmp-file-'.$GLOBALS['TSFE']->fe_user->user['uid'].'-cat-'.$this->post['cid'].'-'.time().'.txt';
						if (!$handle=fopen($this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename, 'w')) {
							exit();
						}
						if (fwrite($handle, $str)===false) {
							exit();
						}
						fclose($handle);
					}
					// excel
					$paths=array();
					$paths[]=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Resources/Private/Contributed/PHPExcel/IOFactory.php';
					$paths[]=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php';
					foreach ($paths as $path) {
						if (file_exists($path)) {
							require_once($path);
							break;
						}
					}
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
						}
					}
					// excel eof
				} else {
					if ($this->post['format']=='xml') {
						// try the generic way
						if (!$this->ms['mode']=='edit') {
							$filename='tmp-file-'.$GLOBALS['TSFE']->fe_user->user['uid'].'-cat-'.$this->post['cid'].'-'.time().'.txt';
							if (!$handle=fopen($this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename, 'w')) {
								exit();
							}
							if (fwrite($handle, $str)===false) {
								exit();
							}
							fclose($handle);
						}
						// try the generic way
						$objXML=new xml2Array();
						$arrOutput=$objXML->parse($str);
						$i=0;
						$s=0;
						$rows=array();
						foreach ($arrOutput[0]['children'] as $item) {
							foreach ($item['children'] as $internalitem) {
								$rows[$i][$s]=$internalitem['tagData'];
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
						// csv
						if ($this->post['delimiter']=="tab") {
							$delimiter="\t";
						} else {
							if ($this->post['delimiter']=="dash") {
								$delimiter="|";
							} else {
								if ($this->post['delimiter']=="dotcomma") {
									$delimiter=";";
								} else {
									if ($this->post['delimiter']=="comma") {
										$delimiter=",";
									} else {
										$delimiter="\t";
									}
								}
							}
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
			}
			// try the generic way eof
		}
		$tmpcontent='';
		$tmpcontent.='<div class="panel-body"><form id="product_import_form" class="form-horizontal" name="form1" method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import').'">
		<input name="consolidate" type="hidden" value="'.$this->post['consolidate'].'" />
		<input name="os" type="hidden" value="'.$this->post['os'].'" />
		<input name="escape_first_line" type="hidden" value="'.$this->post['escape_first_line'].'" />
		<input name="parser_template" type="hidden" value="'.$this->post['parser_template'].'" />
		<input name="format" type="hidden" value="'.$this->post['format'].'" />
		<input name="action" type="hidden" value="customer-import" />
		<input name="delimiter" type="hidden"  value="'.$this->post['delimiter'].'" />
		<input name="backquotes" type="hidden"  value="'.$this->post['backquotes'].'" />
		<input name="filename" type="hidden" value="'.$filename.'" />
		<input name="file_url" type="hidden" value="'.$this->post['file_url'].'" />
		';
		if ($this->ms['mode']=='edit' or $this->post['preProcExistingTask']) {
			// if the existing import task is rerunned indicate it so we dont save the task double
			$tmpcontent.='<input name="preProcExistingTask" type="hidden" value="1" />';
		}
		if (!$rows) {
			$tmpcontent.='<h1>No customers available.</h1>';
		} else {
			$tmpcontent.='<table id="product_import_table" class="table table-striped table-bordered">';
			$header='<thead><tr><th>'.$this->pi_getLL('target_column').'</th><th>'.$this->pi_getLL('source_column').'</th>';
			for ($x=1; $x<6; $x++) {
				$header.='<th>'.$this->pi_getLL('row').' '.$x.'</th>';
			}
			$header.='</tr></thead>';
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
					</select>&nbsp;<input name="advanced_settings" class="btn btn-primary importer_advanced_settings" type="button" value="'.$this->pi_getLL('admin_advanced_settings').'" />
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
				// now 5 customers
				$teller=0;
				foreach ($rows as $row) {
					foreach ($row as $key=>$col) {
						if (!mb_detect_encoding($col, 'UTF-8', true)) {
							$row[$key]=mslib_befe::convToUtf8($col);
						}
					}
					$teller++;
					$tmpitem=$row;
					$cols=count($tmpitem);
					if ($this->post['backquotes']) {
						$tmpitem[$i]=trim($tmpitem[$i], "\"");
					}
					if (strlen($tmpitem[$i])>100) {
						$tmpitem[$i]=substr($tmpitem[$i], 0, 100).'...';
					}
					$tmpcontent.='<td class="cellBreak product_'.$teller.'">'.htmlspecialchars($tmpitem[$i]).'</td>';
					if ($teller==5 or $teller==count($rows)) {
						break;
					}
				}
				if ($teller<5) {
					for ($x=$teller; $x<5; $x++) {
						$tmpcontent.='<td class="cellBreak product_'.$x.'">&nbsp;</td>';
					}
				}
				// now 5 products eof
				$tmpcontent.='
			</tr>';
				/*
				 * prefix '.$i.': <input name="input['.$i.']" type="text"
				 * value="'.htmlspecialchars($this->post['input'][$i]).'" />
				 */
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
			$importer_add_aux_input=str_replace("\r\n", '', $importer_add_aux_input);
			$importer_add_aux_input=str_replace("\n", '', $importer_add_aux_input);
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
				width:\'250px\'
			});
		});
		</script>
			';
			$tmpcontent.=$header.'
		</table>';
		}
		$tmpcontent.='
				<div class="panel panel-default">
					<div class="panel-heading"><h3>'.$this->pi_getLL('save_import_task').'</h3></div>
					<div class="panel-body">
					<div class="form-group">
						<label for="cron_name" class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
						<div class="col-md-10">
							<input name="cron_name" type="text" value="'.htmlspecialchars($this->post['cron_name']).'" class="form-control" />
						</div>
					</div>
';
		if ($this->get['action']=='edit_job') {
			$tmpcontent.='
							<div class="form-group">
								<label for="duplicate" class="control-label col-md-2">'.$this->pi_getLL('duplicate').'</label>
								<div class="col-md-10">
									<div class="checkbox checkbox-success checkbox-inline">
										<input name="duplicate" id="duplicate" type="checkbox" value="1" /><label for="duplicate"></label>
										<input name="skip_import" type="hidden" value="1" />
										<input name="job_id" type="hidden" value="'.$this->get['job_id'].'" />
										<input name="file_url" type="hidden" value="'.$this->post['file_url'].'" />
									</div>
								</div>
							</div>
			';
		}
		$tmpcontent.='
		<div class="form-group">
			<label for="cron_period" class="control-label col-md-2">'.$this->pi_getLL('schedule').'</label>
			<div class="col-md-10">
				<select name="cron_period" id="cron_period" class="form-control">
				<option value="" '.(!$this->post['cron_period'] ? 'selected' : '').'>'.$this->pi_getLL('manual').'</option>
				<option value="'.(3600*24).'" '.($this->post['cron_period']==(3600*24) ? 'selected' : '').'>'.$this->pi_getLL('daily').'</option>
				<option value="'.(3600*24*7).'" '.($this->post['cron_period']==(3600*24*7) ? 'selected' : '').'>'.$this->pi_getLL('weekly').'</option>
				<option value="'.(3600*24*30).'" '.($this->post['cron_period']==(3600*24*30) ? 'selected' : '').'>'.$this->pi_getLL('monthly').'</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="prefix_source_name" class="control-label col-md-2">'.$this->pi_getLL('source_name').'</label>
			<div class="col-md-10">
				<input name="prefix_source_name" type="text" value="'.htmlspecialchars($this->post['prefix_source_name']).'" class="form-control" />
				<input name="database_name" type="hidden" value="'.$this->post['database_name'].'" />
				<input name="cron_data" type="hidden" value="'.htmlspecialchars(serialize($this->post)).'" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-10 col-md-offset-2">
				<button type="submit" class="btn btn-success submit_block" id="cl_submit" name="AdSubmit" value=""><i class="fa fa-save"></i> '.($this->get['action']=='edit_job' ? $this->pi_getLL('save') : $this->pi_getLL('import')).'</button>
			</div>
		</div>
		</form>
		</div>
		</div>';
		$content=''.mslib_fe::shadowBox($tmpcontent).'';
		// $content='<div
		// class="fullwidth_div">'.mslib_fe::shadowBox($tmpcontent).'</div>';
	}
	// preview eof
} elseif ((is_numeric($this->get['job_id']) and $this->get['action']=='run_job') or ($this->post['action']=='customer-import' and (($this->post['filename'] and file_exists($this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$this->post['filename'])) or $this->post['database_name']))) {
	if ((!$this->post['preProcExistingTask'] and $this->post['cron_name'] and !$this->post['skip_import']) or ($this->post['skip_import'] and $this->post['duplicate'])) {
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
		$updateArray['page_uid']=$this->shop_pid;
		$updateArray['categories_id']=$this->post['cid'];
		$updateArray['type']='customers';
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_import_jobs', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// we have to save the import job eof
		$this->ms['show_default_form']=1;
	} elseif ($this->post['skip_import']) {
		// we have to update the import job
		$updateArray=array();
		$updateArray['name']=$this->post['cron_name'];
		$updateArray['status']=1;
		$updateArray['last_run']=time();
		$updateArray['period']=$this->post['cron_period'];
		$updateArray['prefix_source_name']=$this->post['prefix_source_name'];
		$cron_data=array();
		$cron_data[0]=unserialize($this->post['cron_period']);
		$this->post['cron_period']='';
		$cron_data[1]=$this->post;
		$updateArray['data']=serialize($cron_data);
		$updateArray['page_uid']=$this->shop_pid;
		$updateArray['categories_id']=$this->post['cid'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$this->post['job_id'], $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// we have to update the import job eof
		$this->ms['show_default_form']=1;
	}
	if (!$this->post['skip_import']) {
		if (is_numeric($this->get['job_id'])) {
			// load the job
			$str="SELECT * from tx_multishop_import_jobs where id='".$this->get['job_id']."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$data=unserialize($row['data']);
			// copy the previous post data to the current post so it can run the
			// job again
			$this->post=$data[1];
			if ($row['categories_id']) {
				$this->post['cid']=$row['categories_id'];
			}
			// update the last run time
			$updateArray=array();
			$updateArray['last_run']=time();
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$row['id'], $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// update the last run time eof
			if ($log_file) {
				file_put_contents($log_file, $this->FULL_HTTP_URL.' - cron job settings loaded.('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
			}
		}
		if ($this->post['file_url']) {
			if (strstr($this->post['file_url'], "../")) {
				die();
			}
			$filename=time();
			$file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
			file_put_contents($file, mslib_fe::file_get_contents($this->post['file_url']));
		}
		if ($this->post['filename']) {
			$file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$this->post['filename'];
		}
		if (($this->post['database_name'] or $file)) {
			if ($file) {
				$str=mslib_fe::file_get_contents($file);
			}
			if ($this->post['parser_template']) {
				// include a pre-defined xml to php array way
				require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_import_parser_templates/'.$this->post['parser_template'].".php");
				// include a pre-defined xml to php array way eof
			} else {
				if ($this->post['database_name']) {
					if ($log_file) {
						file_put_contents($log_file, $this->FULL_HTTP_URL.' - loading random products.('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
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
					if ($log_file) {
						if ($total_datarows) {
							file_put_contents($log_file, $this->FULL_HTTP_URL.' - random customer records loaded, now starting the import.('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
						} else {
							file_put_contents($log_file, $this->FULL_HTTP_URL.' - no customer records needed to be imported'."\n", FILE_APPEND);
						}
					}
					$i=0;
					foreach ($datarows as $datarow) {
						$s=0;
						foreach ($datarow as $datacol) {
							$rows[$i][$s]=$datacol;
							$s++;
						}
						$i++;
					}
				} else if ($this->post['format']=='excel') {
					$paths=array();
					$paths[]=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Resources/Private/Contributed/PHPExcel/IOFactory.php';
					$paths[]=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php';
					foreach ($paths as $path) {
						if (file_exists($path)) {
							require_once($path);
							break;
						}
					}
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
				} else if ($this->post['format']=='xml') {
					$objXML=new xml2Array();
					$arrOutput=$objXML->parse($str);
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
					} else if ($this->post['delimiter']=="dash") {
						$delimiter="|";
					} else if ($this->post['delimiter']=="dotcomma") {
						$delimiter=";";
					} else if ($this->post['delimiter']=="comma") {
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
			$teller=0;
			$inserteditems=array();
			// $global_start_time = microtime();
			foreach ($rows as $row) {
				foreach ($row as $key=>$col) {
					if (!mb_detect_encoding($col, 'UTF-8', true)) {
						if ($col=='NULL' || $col=='null') {
							$col='';
						}
						$row[$key]=mslib_befe::convToUtf8($col);
					}
				}
				$this->ms['target-cid']=$this->post['cid'];
				$teller++;
				if (($this->post['escape_first_line'] and $teller>1) or !$this->post['escape_first_line']) {
					$tmpitem=$row;
					$cols=count($tmpitem);
					$flipped_select=array_flip($this->post['select']);
					// if($tmpitem[$this->post['select'][0]] and $cols > 0)
					// {
					$item=array();
					// if the source is a database table name add the unique id
					// so we can delete it after the import
					if ($this->post['database_name']) {
						$item['table_unique_id']=$row[0];
					}
					// aux
					$input=array();
					// name
					for ($i=0; $i<$cols; $i++) {
						$tmpitem[$i]=trim($tmpitem[$i]);
						$char='';
						$item[$this->post['select'][$i]]=$tmpitem[$i];
						if ($item[$this->post['select'][$i]]==$char and $char) {
							$item[$this->post['select'][$i]]='';
						}
						$input[$this->post['select'][$i]]=$this->post['input'][$i];
					}
					if ($item['uid']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['uid']);
					} elseif ($item['tx_multishop_source_id']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['tx_multishop_source_id']);
					} elseif ($item['company']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['company']);
					} elseif ($item['full_name']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['full_name']);
					} elseif ($item['first_name'] && $item['middle_name'] && $item['last_name']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['first_name'].'_'.$item['middle_name'].'_'.$item['last_name']);
					} elseif ($item['first_name'] && $item['last_name']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['first_name'].'_'.$item['last_name']);
					} elseif ($item['last_name']) {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['last_name']);
					} elseif (isset($item['email']) && $item['email']!='') {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.$item['email']);
					} else {
						$item['extid']=md5($this->post['prefix_source_name'].'_'.serialize($item));
					}
					// custom hook that can be controlled by third-party plugin
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterItemIterateProc'])) {
						$params=array(
							'row'=>&$row,
							'item'=>&$item,
							'prefix_source_name'=>$this->post['prefix_source_name']
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterItemIterateProc'] as $funcRef) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
						}
					}
					// custom hook that can be controlled by third-party plugin
					// snippet to repair already existing data, for repairing partially
					/*
						$updateArray=array();
						switch($item['gender']) {
							case 'm':
								$updateArray['gender']=0;
								break;
							case 'f':
								$updateArray['gender']=1;
								break;
						}
						$updateArray['first_name']=$item['first_name'];
						$updateArray['middle_name']=$item['middle_name'];
						$updateArray['last_name']=$item['last_name'];
						$updateArray['name']=preg_replace('/\s+/', ' ', $updateArray['first_name'].' '.$updateArray['middle_name'].' '.$updateArray['last_name']);

						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$item['uid'], $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						echo $query.'<br/>';
						continue;
					*/
					if (!$item['email']) {
						$item['email']=uniqid().'@UNKNOWN';
					}
					if ($item['email']) {
						if (!$item['username']) {
							if ($item['uid']) {
								$username='';
								if ($item['company_name']) {
									$username.=str_replace('-', '', mslib_fe::rewritenamein($item['company_name']));
								}
								$username.=$item['uid'];
								$item['username']=$username;
							} else {
								$item['username']=$item['email'];
							}
							// Make sure the username is not in use by someone else
							// Prefix of the username
							$username=$item['username'];
							// Set output variable value to the prefix
							$finalUsername=$username;
							$filter=array();
							if ($item['uid']) {
								// We want to filter out the iterated user
								$filter[]='uid !=\''.addslashes($item['uid']).'\'';
							} elseif ($item['tx_multishop_source_id']) {
								// We want to filter out the iterated user
								$filter[]='tx_multishop_source_id != \''.addslashes($item['tx_multishop_source_id']).'\'';
								//$filter[]='tx_multishop_source_id !=\''.addslashes($item['tx_multishop_source_id']).'\'';
							}
							// Do a loop to increase the prefix number, but do the first loop with empty prefix
							$counter=0;
							do {
								$suffix='';
								if ($counter) {
									$suffix=$counter;
								}
								$finalUsername=$username.$suffix;
								$counter++;
							} while (mslib_befe::ifExists($finalUsername, 'fe_users', 'username', $filter));
							// Copy final username back to the $item array
							$item['username']=$finalUsername;
						}
						// first combine the values to 1 array
						$usergroups=array();
						$usergroups[]=$this->conf['fe_customer_usergroup'];
						if ($item['usergroup']) {
							// sometimes excel changes comma to dot
							if ($input['usergroup']) {
								// use aux
								$item['usergroup']=str_replace($input['usergroup'], ',', $item['usergroup']);
							} elseif (strstr($item['usergroup'], '.')) {
								$item['usergroup']=str_replace('.', ',', $item['usergroup']);
							}
							if (!strstr($item['usergroup'], ",") and !is_numeric($item['usergroup'])) {
								$groups=array();
								$groups[]=$item['usergroup'];
							} else {
								$groups=explode(',', $item['usergroup']);
							}
							foreach ($groups as $group) {
								if (is_numeric($group)) {
									$usergroups[]=$group;
								} else {
									$str="SELECT * from fe_groups where pid='".$this->conf['fe_customer_pid']."' and title='".addslashes($group)."'";
									$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
									if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
										$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
										$usergroups[]=$row['uid'];
									} else {
										$updateArray=array();
										$updateArray['pid']=$this->conf['fe_customer_pid'];
										$updateArray['title']=$group;
										$updateArray['crdate']=time();
										$updateArray['tstamp']=time();
										$updateArray=mslib_befe::rmNullValuedKeys($updateArray);
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('fe_groups', $updateArray);
										$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										$usergroups[]=$GLOBALS['TYPO3_DB']->sql_insert_id();
									}
								}
							}
						}
						$user=array();
						if (isset($item['tx_multishop_source_id'])) {
							$user['tx_multishop_source_id']=$item['tx_multishop_source_id'];
						}
						if ($item['uid']) {
							$user['uid']=$item['uid'];
							if (!$item['tx_multishop_source_id']) {
								$user['tx_multishop_source_id']=$item['uid'];
							}
						}
						if ($item['username']) {
							$user['username']=$item['username'];
						}
						if (is_array($usergroups)) {
							$user['usergroup']=implode(",", $usergroups);
						}
						if ($item['first_name']) {
							$item['first_name']=preg_replace('/\s+/', ' ', $item['first_name']);
							$user['first_name']=$item['first_name'];
						}
						if ($item['middle_name']) {
							$item['middle_name']=preg_replace('/\s+/', ' ', $item['middle_name']);
							$user['middle_name']=$item['middle_name'];
						}
						if ($item['last_name']) {
							$item['last_name']=preg_replace('/\s+/', ' ', $item['last_name']);
							$user['last_name']=$item['last_name'];
						}
						if (!$item['full_name'] && ($item['first_name'] || $item['last_name'])) {
							$fullname=array();
							if ($item['first_name']!='') {
								$fullname[]=$item['first_name'];
							}
							if ($item['middle_name']!='') {
								$fullname[]=$item['middle_name'];
							}
							if ($item['last_name']!='') {
								$fullname[]=$item['last_name'];
							}
							if (count($fullname)) {
								$item['full_name']=implode(' ', $fullname);
//								$item['full_name'] = preg_replace('/\s+/', ' ', $item['full_name']);
							}
						}
						if ($item['full_name'] && !isset($item['first_name']) && !isset($item['last_name'])) {
							$user['name']=$item['full_name'];
							$array=explode(' ', $item['full_name']);
							$user['first_name']=$array[0];
							unset($array[0]);
							$user['last_name']=implode(' ', $array);
						} else {
							if ($item['last_name']) {
								$user['name']=preg_replace('/\s+/', ' ', $item['first_name'].' '.$item['middle_name'].' '.$item['last_name']);
							}
						}
						if ($item['company_name']) {
							$user['company']=$item['company_name'];
						}
						if (isset($item['newsletter'])) {
							$user['tx_multishop_newsletter']=$item['newsletter'];
						}
						$user['status']='1';
						$user['disable']='0';
						if (isset($item['disable'])) {
							$user['disable']=$item['disable'];
						}
						if (isset($item['deleted'])) {
							$user['deleted']=$item['deleted'];
						}
						if (isset($item['tx_multishop_payment_condition'])) {
							$user['tx_multishop_payment_condition']=$item['tx_multishop_payment_condition'];
						}
						if (isset($item['language_code_2char_iso'])) {
							$user['tx_multishop_language']=$item['language_code_2char_iso'];
						}
						if (isset($item['tx_multishop_discount'])) {
							$user['tx_multishop_discount']=$item['discount'];
						}
						if ($item['vat_id']) {
							$user['tx_multishop_vat_id']=$item['vat_id'];
						}
						if ($item['coc_id']) {
							$user['tx_multishop_coc_id']=$item['coc_id'];
						}
						if (isset($item['tx_multishop_customer_id'])) {
							if ($item['tx_multishop_customer_id']=='0') {
								continue;
							}
							$user['tx_multishop_customer_id']=$item['tx_multishop_customer_id'];
						}
						if (isset($item['crdate'])) {
							$user['crdate']=strtotime($item['crdate']);
						}
						if (isset($item['gender']) && $item['gender']!='') {
							$user['gender']=$item['gender'];
						}
						if ($item['birthday']) {
							$user['date_of_birth']=$item['birthday'];
						}
						if ($item['title']) {
							$user['title']=$item['title'];
						}
						if ($item['zip']) {
							$user['zip']=$item['zip'];
						}
						if ($item['city']) {
							$user['city']=$item['city'];
						}
						if (isset($item['country']) && $item['country']!='') {
							if ($item['country']=='') {
								$item['country']=$default_country;
							} else {
								$englishCountryName='';
								if (strlen($item['country'])==2) {
									// 2CHAR ISO
									$englishCountryName=mslib_fe::getCountryByCode($item['country']);
								} else {
									// check if the country name is valid English name
									$englishCountryName=mslib_fe::getEnglishCountryNameByTranslatedName('en', $item['country']);
									if (!$englishCountryName) {
										// not english. hopefully its having a valid country name in the shops default language
										$englishCountryName=mslib_fe::getEnglishCountryNameByTranslatedName($this->lang, $item['country']);
									}
								}
								if ($englishCountryName and $englishCountryName!=$user['country']) {
									$user['country']=$englishCountryName;
								} else {
									$user['country']=$item['country'];
								}
							}
						}
						if ($item['www']) {
							$user['www']=$item['www'];
						}
						if ($item['street_name']) {
							$user['street_name']=$item['street_name'];
						}
						if ($item['address_number']) {
							$user['address_number']=$item['address_number'];
						}
						if ($item['address_ext']) {
							$user['address_ext']=$item['address_ext'];
						}
						if ($item['address']) {
							$user['address']=$item['address'];
						}
						if (!$user['address'] and ($user['street_name'] and $user['address_number'])) {
							$user['address']=$user['street_name'].' '.$user['address_number'];
							if ($user['address_ext']) {
								$user['address'].='-'.$user['address_ext'];
							}
						} elseif ($user['address'] && !$user['street_name'] && !$user['address_number']) {
							$street_address='';
							$house_number='';
							$addon_number='';
							$street_data=explode(' ', $user['address']);
							$house_number=$street_data[count($street_data)-1];
							if (!preg_match('/[0-9]/isUm', $house_number)) {
								$house_number=$street_data[count($street_data)-2].' '.$street_data[count($street_data)-1];
								unset($street_data[count($street_data)-1]);
								unset($street_data[count($street_data)-1]);
							} else {
								unset($street_data[count($street_data)-1]);
							}
							$street_address=implode(' ', $street_data);
							$addon_number='';
							$pattern_alpha='/([a-zA-Z])/isUm';
							preg_match_all($pattern_alpha, $house_number, $alpha_result);
							if (isset($alpha_result[1][0]) && !empty($alpha_result[1][0])) {
								$addon_number=implode('', $alpha_result[1]);
								$house_number=str_replace($addon_number, '', $house_number);
							}
							$user['street_name']=$street_address;
							$user['address_number']=$house_number;
							$user['address_ext']=$addon_number;
						}
						if ($item['telephone']) {
							$user['telephone']=$item['telephone'];
						}
						if ($item['fax']) {
							$user['fax']=$item['fax'];
						}
						if ($item['email']) {
							$user['email']=$item['email'];
						}
						if ($item['password_hashed']) {
							$user['password']=$item['password_hashed'];
						} elseif ($item['password']) {
							$item['password']=mslib_befe::getHashedPassword($item['password']);
						}
						$update=0;
						$user_check=array();
						if ($user['uid']) {
							$user_check=mslib_fe::getUser($user['uid'], "uid");
						} else {
							if (!$user_check && $user['tx_multishop_source_id']) {
								$user_check=mslib_fe::getUser($user['tx_multishop_source_id'], "tx_multishop_source_id");
							}
							if (!$user_check && $user['username']) {
								$user_check=mslib_fe::getUser($user['username'], "username");
							}
							if (!$user_check && $user['email']) {
								$user_check=mslib_fe::getUser($user['email'], "email");
							}
							if (!$user_check && $item['extid']) {
								$user_check=mslib_fe::getUser($item['extid'], 'extid');
							}
						}
						if ($user_check['uid']) {
							if (!$user['tx_multishop_source_id'] || ($user['tx_multishop_source_id']==$user_check['tx_multishop_source_id']) || ($user['uid']==$user_check['uid'])) {
								$update=1;
							}
						}
						// custom hook that can be controlled by third-party
						// plugin
						if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUpdateUserPreHook'])) {
							$params=array(
								'user'=>&$user,
								'item'=>&$item,
								'user_check'=>&$user_check,
								'prefix_source_name'=>$this->post['prefix_source_name'],
								'update'=>&$update
							);
							foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUpdateUserPreHook'] as $funcRef) {
								\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
							}
						}
						// custom hook that can be controlled by third-party
						// plugin eof
						$uid='';
						if ($update) {
							if (!$user['country']) {
								$user['country']=$default_country;
							}
							$skipRecord=0;
							// custom hook that can be controlled by third-party
							// plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterUpdateUserPreHook'])) {
								$params=array(
									'user'=>&$user,
									'item'=>&$item,
									'user_check'=>&$user_check,
									'prefix_source_name'=>$this->post['prefix_source_name'],
									'skipRecord'=>&$skipRecord
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterUpdateUserPreHook'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party
							// plugin eof
							if (!$skipRecord) {
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$user_check['uid'], $user);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$name=array();
								if ($user['company']!='') {
									$name[]=$user['company'];
								}
								if ($user['name']!='' and !in_array($user['name'], $name)) {
									$name[]=$user['name'];
								}
								if ($user['email']!='' and !in_array($user['email'], $name)) {
									$name[]='email: '.$user['email'];
								}
								$content.=implode(" / ", $name).' has been updated.<br />';
								$uid=$user_check['uid'];
								// custom hook that can be controlled by third-party
								// plugin
								if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterUpdateUserPostHook'])) {
									$params=array(
										'user'=>&$user,
										'item'=>&$item,
										'user_check'=>&$user_check,
										'prefix_source_name'=>$this->post['prefix_source_name']
									);
									foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterUpdateUserPostHook'] as $funcRef) {
										\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
									}
								}
								// custom hook that can be controlled by third-party
								// plugin eof
							}
						} else {
							if (!$user['password'] or $user['password']=='NULL') {
								// generate our own random password
								$user['password']=mslib_befe::getHashedPassword(mslib_befe::generateRandomPassword(10, $user['username']));
							}
							$user['tstamp']=time();
							if (!$user['crdate']) {
								$user['crdate']=time();
							}
							$user['tx_multishop_code']=md5(uniqid('', true));
							$user['pid']=$this->conf['fe_customer_pid'];
							$user['page_uid']=$this->shop_pid;
							$user['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
							if (!$user['country']) {
								$user['country']=$default_country;
							}
							$skipRecord=0;
							// custom hook that can be controlled by third-party
							// plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUserPreHook'])) {
								$params=array(
									'user'=>&$user,
									'item'=>&$item,
									'user_check'=>&$user_check,
									'prefix_source_name'=>$this->post['prefix_source_name'],
									'skipRecord'=>&$skipRecord
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUserPreHook'] as $funcRef) {
									\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party
							// plugin eof
							if (!$skipRecord) {
								if (!$user['gender']) {
									$user['gender']=0;
								}
								// T3 6.2 BUGFIXES
								$requiredCols=array();
								$requiredCols[]='title';
								$requiredCols[]='www';
								foreach ($requiredCols as $requiredCol) {
									if (!isset($user[$requiredCol])) {
										$user[$requiredCol]='';
									}
								}
								if (!isset($user['tx_multishop_source_id']) && $user['uid']) {
									$user['tx_multishop_source_id']=$user['uid'];
								}
								$user=mslib_befe::rmNullValuedKeys($user);
								$query=$GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $user);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
								if ($uid) {
									$user_check=mslib_fe::getUser($uid);
									$name=array();
									if ($user['company']!='') {
										$name[]=$user['company'];
									}
									if ($user['name']!='' and !in_array($user['name'], $name)) {
										$name[]=$user['name'];
									}
									if ($user['email']!='' and !in_array($user['email'], $name)) {
										$name[]='email: '.$user['email'];
									}
									$content.=implode(" / ", $name).' has been added.<br />';
									// custom hook that can be controlled by third-party
									// plugin
									if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUserPostHook'])) {
										$params=array(
											'user'=>&$user,
											'item'=>&$item,
											'user_check'=>&$user_check,
											'prefix_source_name'=>$this->post['prefix_source_name']
										);
										foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUserPostHook'] as $funcRef) {
											\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
										}
									}
									// custom hook that can be controlled by third-party
									// plugin eof
								} else {
									$content.=implode(" / ", $name).' has been FAILED. Query:<br />'.$query.'<br/>';
								}
							}
						}
						if ($uid) {
							$address=array();
							$address['tstamp']=time();
							$address['tx_multishop_customer_id']=$uid;
							$address['pid']=$this->conf['fe_customer_pid'];
							$address['first_name']=$user['first_name'];
							$address['middle_name']=$user['middle_name'];
							$address['last_name']=$user['last_name'];
							$address['name']=$user['name'];
							$address['gender']=$user['gender'];
							$address['birthday']=$user['birthday'];
							$address['email']=$user['email'];
							$address['phone']=$user['telephone'];
							$address['mobile']=$user['mobile'];
							$address['www']=$user['www'];
							$address['street_name']=$user['street_name'];
							$address['address']=$user['address'];
							$address['address_number']=$user['address_number'];
							$address['address_ext']=$user['address_ext'];
							$address['room']=$user['room'];
							$address['company']=$user['company'];
							$address['city']=$user['city'];
							$address['zip']=$user['zip'];
							$address['region']=$user['region'];
							$address['country']=$user['country'];
							$address['fax']=$user['fax'];
							$address['deleted']=0;
							$address['page_uid']=$this->shop_pid;
							if ($item['deleted']!='') {
								$address['deleted']=$item['deleted'];
							}
							$address['addressgroup']='';
							$str="SELECT tx_multishop_customer_id from tt_address where tx_multishop_customer_id='".$uid."'";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'tx_multishop_customer_id='.$uid, $address);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							} else {
								$address['tx_multishop_default']=1;
								$address['tx_multishop_address_type']='billing';
								$address=mslib_befe::rmNullValuedKeys($address);
								$query=$GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $address);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
							}
						}
					}
				}
				if ($log_file) {
					$content='';
				}
				// end foreach
			}
			// if($file_location and file_exists($file_location))
			// @unlink($file_location);
		}
	}
	// end import
} else {
	$this->ms['show_default_form']=1;
}
if ($this->ms['show_default_form']) {
	$this->ms['upload_customerfeed_form']='<div id="upload_customerfeed_form">';
	$this->ms['upload_customerfeed_form'].='
	<div class="panel panel-default">
	<div class="panel-heading"><h3>'.$this->pi_getLL('source').'</h3></div>
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

	';
	/*
	 * <li>URL <input name="file_url" type="text" /></li> <li>Database table
	 * <input name="database_name" type="text" /></li>
	 */
	$this->ms['upload_customerfeed_form'].='
	<hr>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(document).on("click", ".hide_advanced_import_radio", function() {
			$(".advanced_options").hide();
		});
		$(document).on("click", ".advanced_import_radio", function() {
			$(".advanced_options").show();
		});
	});
	</script>
	<div class="form-group">
	<label class="control-label col-md-2">'.ucfirst($this->pi_getLL('format')).'</label>
	<div class="col-md-10">
		<div class="radio radio-success radio-inline"><input name="format" type="radio" value="excel" checked id="hide_advanced_import_radio1" class="hide_advanced_import_radio" /><label for="hide_advanced_import_radio1">Excel</label></div>
  		<div class="radio radio-success radio-inline"><input name="format" type="radio" value="xml" id="hide_advanced_import_radio2" class="hide_advanced_import_radio" /><label for="hide_advanced_import_radio2">XML</label></div>
  		<div class="radio radio-success radio-inline"><input name="format" type="radio" value="txt" id="advanced_import_radio" class="advanced_import_radio" /><label for="advanced_import_radio">TXT/CSV</label></div>
		<div class="advanced_options offset-md-2" style="display:none">
			<div class="form-group">
				<label for="delimiter" class="col-md-2">'.$this->pi_getLL('delimited_by').'</label>
				<div class="col-md-10">
					<select name="delimiter" id="delimiter" class="form-control">
						<option value="dotcomma">'.$this->pi_getLL('dotcomma').'</option>
						<option value="comma">'.$this->pi_getLL('comma').'</option>
						<option value="tab">'.$this->pi_getLL('tab').'</option>
						<option value="dash">'.$this->pi_getLL('dash').'</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="checkbox checkbox-success checkbox-inline">
					<input name="backquotes" type="checkbox" value="1" id="backquotes" />
					<label for="backquotes">'.$this->pi_getLL('fields_are_enclosed_with_double_quotes').'</label>
				</div>
			</div>
			<div class="form-group">
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
	<div class="form-group">
		<div class="col-md-10 col-md-offset-2">
			<input type="submit" name="Submit" class="btn btn-success submit submit_block" id="cl_submit" value="'.$this->pi_getLL('upload').'" />
			<input name="action" type="hidden" value="customer-import-preview" />
		</div>
	</div>
	</div>
	</div>
	';
	$content.='<div class="panel-heading"><h3>Admin customer import - need translate</h3></div><div class="panel-body">
	 <form action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import').'" method="post" enctype="multipart/form-data" name="form1" id="form1" class="form-horizontal">
	 '.$this->ms['upload_customerfeed_form'].'
	</form>';
// load the jobs templates
	$str="SELECT * from tx_multishop_import_jobs where page_uid='".$this->shop_pid."' and type='customers' order by prefix_source_name asc, id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$jobs=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$jobs[]=$row;
	}
	if (count($jobs)>0) {
		$schedule_content.='
		<div class="panel panel-default" id="scheduled_import_jobs_form">
		<div class="panel-heading"><h3>'.$this->pi_getLL('import_tasks').'</h3></div>
		<div class="panel-body">
		<table class="table table-striped table-bordered msadmin_border no-mb" id="admin_modules_listing">
		<thead>
		<th>'.$this->pi_getLL('source_name').'</th>
		<th class="cellName">'.$this->pi_getLL('name').'</th>
		<th class="cellDate">'.$this->pi_getLL('last_run').'</th>
		<th>'.$this->pi_getLL('action').'</th>
		<th class="cellStatus">'.ucfirst($this->pi_getLL('status')).'</th>
		<th class="cellAction">'.ucfirst($this->pi_getLL('delete')).'</th>
		<th>'.$this->pi_getLL('file_exists').'</th>
		<th>'.$this->pi_getLL('upload_file').'</th>';
		if ($this->ROOTADMIN_USER) {
			$schedule_content.='<th>'.$this->pi_getLL('download_import_task').'</th>';
		}
		$schedule_content.='</thead>';
		$switch='';
		foreach ($jobs as $job) {
			if ($switch=='odd') {
				$switch='even';
			} else {
				$switch='odd';
			}
			$schedule_content.='<tr class="'.$switch.'">';
			$schedule_content.='<td>'.$job['prefix_source_name'].'</td>
			<td><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&job_id='.$job['id']).'&action=edit_job">'.$job['name'].'</a></td>
			';
			$schedule_content.='<td class="cellDate">'.date("Y-m-d", $job['last_run']).'<br />'.date("G:i:s", $job['last_run']).'</td>';
			if (!$job['period']) {
				$schedule_content.='<td>manual<br /><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&job_id='.$job['id'].'&action=run_job&limit=99999999').'" class="btn btn-success" onClick="return CONFIRM(\''.addslashes($this->pi_getLL('are_you_sure_you_want_to_run_the_import_job')).': '.htmlspecialchars(addslashes($job['name'])).'?\')">'.$this->pi_getLL('run_now').'</a><br /><a href="" class="copy_to_clipboard" rel="'.htmlentities('/usr/bin/wget -O /dev/null --tries=1 --timeout=30 -q "'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&job_id='.$job['id'].'&code='.$job['code'].'&action=run_job&run_as_cron=1&limit=99999999', 1).'" >/dev/null 2>&1').'" ><i>'.$this->pi_getLL('run_by_crontab').'</i></a></td>';
			} else {
				$schedule_content.='<td>'.date("Y-m-d G:i:s", $job['last_run']+$job['period']).'</td>';
			}
			$schedule_content.='<td class="status_field" align="center">';
			if (!$job['status']) {
				$schedule_content.='<span class="admin_status_red" alt="Disable"></span>';
				$schedule_content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&job_id='.$job['id'].'&status=1').'"><span class="admin_status_green disabled" alt="Enabled"></span></a>';
			} else {
				$schedule_content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&job_id='.$job['id'].'&status=0').'"><span class="admin_status_red disabled" alt="Disabled"></span></a>';
				$schedule_content.='<span class="admin_status_green" alt="Enable"></span>';
			}
			$schedule_content.='</td>
			<td class="cellAction">
			<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&delete=1&job_id='.$job['id']).'&action=delete_category" onClick="return CONFIRM(\'Are you sure you want to delete the import job: '.htmlspecialchars($job['name']).'?\')" alt="Remove '.htmlspecialchars($job['name']).'" class="btn btn-danger btn-sm admin_menu_remove" title="Remove '.htmlspecialchars($job['name']).'"><i class="fa fa-trash-o"></i></a>
			</td>
			<td class="cellStatus">
				';
			$data=unserialize($job['data']);
			if ($data[1]['filename']) {
				$file_location=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$data[1]['filename'];
				if (file_exists($file_location)) {
					$schedule_content.='<span class="admin_status_green" alt="Enable"></span>';
				} else {
					$schedule_content.='<span class="admin_status_red" alt="Disable"></span>';
				}
			}
			$schedule_content.='
			</td>
			<td>
			 	<form action="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import').'" method="post" enctype="multipart/form-data" name="form1" id="form1">
			 		<div class="input-group">
					<input type="file" name="file" class="form-control" style="width:300px;" />
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
					<a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customer_import&download=task&job_id='.$job['id']).'" class="btn btn-success"><i class="fa fa-download"></i> '.$this->pi_getLL('download_import_task').'</a>
				</td>';
			}
			$schedule_content.='</tr>';
		}
		$schedule_content.='</table>
		</div>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($)
		{
			$(".copy_to_clipboard").click(function(event)
			{
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
		$content.=$schedule_content;
		//$tabs['tasks']=array($this->pi_getLL('import_tasks'),$schedule_content);
	}
	// load the jobs templates eof
	if ($this->ROOTADMIN_USER) {
		$content.='<div class="panel panel-default" id="scheduled_import_jobs_form">
		<div class="panel-heading"><h3>'.$this->pi_getLL('upload_import_task').'</h3></div>
		<div class="panel-body">
			<form action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_customer_import&upload=task').'" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="form-horizontal blockSubmitForm">
				<div class="form-group">
					<label for="new_cron_name" class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
					<div class="col-md-10">
					<input name="new_cron_name" type="text" value="" class="form-control" >
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
			</form>
			</div>
		</div></div>';
	}
}
$content.='<hr><div class="clearfix"><a class="btn btn-success msAdminBackToCatalog" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>