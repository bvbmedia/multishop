<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
/***************************************************************
 *  Copyright notice
 *  (c) 2010 BVB Media BV - Bas van Beek <bvbmedia@gmail.com>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 * Hint: use extdeveval to insert/update function index above.
 */
class tx_mslib_admin_import extends tslib_pibase {
	function renderInterface($params, &$that) {
		if ($that->get['delete'] and is_numeric($that->get['job_id'])) {
			// delete job
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_import_jobs', 'id='.$that->get['job_id'].' and type=\''.addslashes($params['importKey']).'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
		if (is_numeric($that->get['job_id']) and is_numeric($that->get['status'])) {
			// update the status of a job
			$updateArray=array();
			$updateArray['status']=$that->get['status'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=\''.$that->get['job_id'].'\' and type=\''.addslashes($params['importKey']).'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			// update the status of a job eof
		}
		if (isset($that->get['download']) && $that->get['download']=='task' && is_numeric($that->get['job_id'])) {
			$sql=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_import_jobs ', // FROM ...
				'id= \''.$that->get['job_id'].'\'', // WHERE...
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
				$filename='multishop_'.$params['importKey'].'_import_task_'.date('YmdHis').'_'.$that->get['job_id'].'.txt';
				$filepath=$that->DOCUMENT_ROOT.'uploads/tx_multishop/'.$filename;
				file_put_contents($filepath, $serial_data);
				header("Content-disposition: attachment; filename={$filename}"); //Tell the filename to the browser
				header('Content-type: application/octet-stream'); //Stream as a binary file! So it would force browser to download
				readfile($filepath); //Read and stream the file
				@unlink($filepath);
				exit();
			}
		}
		if (isset($that->get['upload']) && $that->get['upload']=='task' && $_FILES) {
			if (!$_FILES['task_file']['error']) {
				$filename=$_FILES['task_file']['name'];
				$target=$that->DOCUMENT_ROOT.'/uploads/tx_multishop'.$filename;
				if (move_uploaded_file($_FILES['task_file']['tmp_name'], $target)) {
					$task_content=file_get_contents($target);
					$unserial_task_data=unserialize($task_content);
					$insertArray=array();
					$insertArray['page_uid']=$that->showCatalogFromPage;
					foreach ($unserial_task_data as $col_name=>$col_val) {
						if ($col_name=='code') {
							$insertArray[$col_name]=md5(uniqid());
						} else if ($col_name=='name' && isset($that->post['new_cron_name']) && !empty($that->post['new_cron_name'])) {
							$insertArray[$col_name]=$that->post['new_cron_name'];
						} else if ($col_name=='prefix_source_name' && isset($that->post['new_prefix_source_name']) && !empty($that->post['new_prefix_source_name'])) {
							$insertArray[$col_name]=$that->post['new_prefix_source_name'];
						} else {
							$insertArray[$col_name]=$col_val;
						}
					}
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_import_jobs', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					@unlink($target);
				}
			}
			header('Location: '.$that->FULL_HTTP_URL.$params['postForm']['actionUrl'].'#tasks');
		}
		$GLOBALS['TSFE']->additionalHeaderData['tx_multishop_pi1_block_ui']=mslib_fe::jQueryBlockUI();
		if ($that->post['job_id']) {
			$that->get['job_id']=$that->post['job_id'];
		}
		if ($that->post['action']=='import-preview' or (is_numeric($that->get['job_id']) and $_REQUEST['action']=='edit_job')) {
			// preview
			if (is_numeric($that->get['job_id'])) {
				$that->ms['mode']='edit';
				// load the job
				$str="SELECT * from tx_multishop_import_jobs where id='".$that->get['job_id']."' and type='".addslashes($params['importKey'])."'";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
				$data=unserialize($row['data']);
				// copy the previous post data to the current post so it can run the job
				// again
				$that->post=$data[1];
				$that->post['cid']=$row['categories_id'];
				// enable file logging
				if ($that->get['relaxed_import']) {
					$that->post['relaxed_import']=$that->get['relaxed_import'];
				}
				// update the last run time
				$updateArray=array();
				$updateArray['last_run']=time();
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$row['id'], $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				// update the last run time eof
			}
			if ($that->post['database_name']) {
				$file_location=$that->post['database_name'];
			} elseif ($that->post['file_url']) {
				if (strstr($that->post['file_url'], "../")) {
					die();
				}
				$filename=time();
				$file_location=$that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
				$file_content=mslib_fe::file_get_contents($that->post['file_url']);
				if (!$file_content or !mslib_fe::file_put_contents($file_location, $file_content)) {
					die('cannot save the file or the file is empty');
				}
			} elseif ($that->ms['mode']=='edit') {
				$filename=$that->post['filename'];
				$file_location=$that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
			}
			if ($_FILES['file']['tmp_name']) {
				$file=$_FILES['file']['tmp_name'];
				$filename=time().'.import';
				$file_location=$that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
				$that->post['filename']=$filename;
				move_uploaded_file($file, $file_location);
				if (preg_match("/\.gz$/", $_FILES['file']['name'])) {
					// lets uncompress realtime
					$str=mslib_fe::file_get_contents($file_location, 1);
					file_put_contents($file_location, $str);
				}
			}
			if ((file_exists($file_location) or $that->post['database_name'])) {
				if (!$that->post['database_name']) {
					$str=mslib_fe::file_get_contents($file_location);
				}
				if ($that->post['parser_template']) {
					if (strstr($that->post['parser_template'], "..")) {
						die();
					}
					// include a pre-defined xml to php array converter
					require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_import_parser_templates/'.$that->post['parser_template'].".php");
					// include a pre-defined xml to php array converter eof
				} else {
					if ($that->post['database_name']) {
						if ($that->ms['mode']=='edit') {
							$limit=10;
						} else {
							$limit='10';
						}
						$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $that->post['database_name'], '', '', '', $limit);
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
						if ($that->post['format']=='excel') {
							// try the generic way
							if (!$that->ms['mode']=='edit') {
								$filename='tmp-file-'.$GLOBALS['TSFE']->fe_user->user['uid'].'-cat-'.$that->post['cid'].'-'.time().'.txt';
								if (!$handle=fopen($that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename, 'w')) {
									exit();
								}
								if (fwrite($handle, $str)===false) {
									exit();
								}
								fclose($handle);
							}
							// excel
							require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
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
											$table_cols[]=t3lib_div::strtolower($clean_products_data);
										}
									}
									$counter++;
								}
							}
							// excel eof
						} else {
							if ($that->post['format']=='xml') {
								// try the generic way
								if (!$that->ms['mode']=='edit') {
									$filename='tmp-file-'.$GLOBALS['TSFE']->fe_user->user['uid'].'-cat-'.$that->post['cid'].'-'.time().'.txt';
									if (!$handle=fopen($that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename, 'w')) {
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
								if ($that->post['os']=='linux') {
									$splitter="\n";
								} else {
									$splitter="\r\n";
								}
								// csv
								if ($that->post['delimiter']=="tab") {
									$delimiter="\t";
								} else {
									if ($that->post['delimiter']=="dash") {
										$delimiter="|";
									} else {
										if ($that->post['delimiter']=="dotcomma") {
											$delimiter=";";
										} else {
											if ($that->post['delimiter']=="comma") {
												$delimiter=",";
											} else {
												$delimiter="\t";
											}
										}
									}
								}
								if ($that->post['backquotes']) {
									$backquotes='"';
								} else {
									$backquotes='"';
								}
								if ($that->post['format']=='txt') {
									$row=1;
									$rows=array();
									if (($handle=fopen($file_location, "r"))!==false) {
										$counter=0;
										while (($data=fgetcsv($handle, '', $delimiter, $backquotes))!==false) {
											if ($that->post['escape_first_line']) {
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
				$tmpcontent.='<form id="product_import_form" class="" name="form1" method="post" action="'.$params['postForm']['actionUrl'].'">
		<input name="consolidate" type="hidden" value="'.$that->post['consolidate'].'" />
		<input name="os" type="hidden" value="'.$that->post['os'].'" />
		<input name="escape_first_line" type="hidden" value="'.$that->post['escape_first_line'].'" />
		<input name="parser_template" type="hidden" value="'.$that->post['parser_template'].'" />
		<input name="format" type="hidden" value="'.$that->post['format'].'" />
		<input name="action" type="hidden" value="import" />
		<input name="delimiter" type="hidden"  value="'.$that->post['delimiter'].'" />
		<input name="backquotes" type="hidden"  value="'.$that->post['backquotes'].'" />
		<input name="filename" type="hidden" value="'.$filename.'" />
		<input name="file_url" type="hidden" value="'.$that->post['file_url'].'" />
		';
				if ($that->ms['mode']=='edit' or $that->post['preProcExistingTask']) {
					// if the existing import task is rerunned indicate it so we dont save the task double
					$tmpcontent.='<input name="preProcExistingTask" type="hidden" value="1" />';
				}
				if (!$rows) {
					$tmpcontent.='<h1>No data available.</h1>';
				} else {
					$tmpcontent.='<table id="product_import_table" class="msZebraTable" cellpadding="0" cellspacing="0" border="0">';
					$header='<tr><th>'.$that->pi_getLL('target_column').'</th><th>'.$that->pi_getLL('source_column').'</th>';
					for ($x=1; $x<6; $x++) {
						$header.='<th>'.$that->pi_getLL('row').' '.$x.'</th>';
					}
					$header.='</tr>';
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
					<td class="first">
					<div class="msAdminSelect2Wrapper bigdropWider">
					<select name="select['.$i.']" id="select['.$i.']" class="select_columns_fields">
						<option value="">'.$that->pi_getLL('skip').'</option>
						';
						foreach ($params['importColumns'] as $key=>$value) {
							$tmpcontent.='<option value="'.$key.'" '.($that->post['select'][$i]!='' && $that->post['select'][$i]==$key ? 'selected' : '').'>'.htmlspecialchars($value).'</option>';
						}
						$tmpcontent.='
					</select>
					</div>
					<input name="advanced_settings" class="importer_advanced_settings" type="button" value="'.$that->pi_getLL('admin_advanced_settings').'" />
					<fieldset class="advanced_settings_container hide">
						<div class="form-field">
							aux
							<input name="input['.$i.']" type="text" style="width:150px;" value="'.htmlspecialchars($that->post['input'][$i]).'" />
						</div>
					</fieldset>
				</td>
				<td class="column_name"><strong>'.htmlspecialchars($table_cols[$i]).'</strong></td>
				';
						// now 5 records
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
							if ($that->post['backquotes']) {
								$tmpitem[$i]=trim($tmpitem[$i], "\"");
							}
							if (strlen($tmpitem[$i])>100) {
								$tmpitem[$i]=substr($tmpitem[$i], 0, 100).'...';
							}
							$tmpcontent.='<td class="product_'.$teller.'">'.htmlspecialchars($tmpitem[$i]).'</td>';
							if ($teller==5 or $teller==count($rows)) {
								break;
							}
						}
						if ($teller<5) {
							for ($x=$teller; $x<5; $x++) {
								$tmpcontent.='<td class="product_'.$x.'">&nbsp;</td>';
							}
						}
						// now 5 products eof
						$tmpcontent.='
			</tr>';
						/*
						 * prefix '.$i.': <input name="input['.$i.']" type="text"
						 * value="'.htmlspecialchars($that->post['input'][$i]).'" />
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
				<input name="aux_input[]" type="text" value="'.htmlspecialchars($that->post['aux_input']).'" />
				<input name="delete" class="delete_property" type="button" value="delete" /><input name="disable" type="button" value="enable" />
			</div>
			';
					$importer_add_aux_input=str_replace("\r\n", '', $importer_add_aux_input);
					$importer_add_aux_input=str_replace("\n", '', $importer_add_aux_input);
					$tmpcontent.=$header.'</table>';
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
							$(this).next().toggle();
						});
						$(\'.select_columns_fields\').select2({
							dropdownCssClass: "bigdropWider", // apply css that makes the dropdown taller
							width:\'220px\'
						});
					});
					</script>
						';
				}
				$tmpcontent.='
				<fieldset>
					<legend>'.$that->pi_getLL('save_import_task').'</legend>
					<div class="account-field">
						<label for="cron_name">'.$that->pi_getLL('name').'</label>
						<input name="cron_name" type="text" value="'.htmlspecialchars($that->post['cron_name']).'" />
					</div>
';
				if ($that->get['action']=='edit_job') {
					$tmpcontent.='
							<div class="account-field">
								<label for="duplicate">'.$that->pi_getLL('duplicate').'</label>
								<input name="duplicate" type="checkbox" value="1" />
								<input name="skip_import" type="hidden" value="1" />
								<input name="job_id" type="hidden" value="'.$that->get['job_id'].'" />
								<input name="file_url" type="hidden" value="'.$that->post['file_url'].'" />
							</div>
			';
				}
				$tmpcontent.='
		<div class="account-field">
		<label for="cron_period">'.$that->pi_getLL('schedule').'</label>
		<select name="cron_period" id="cron_period">
		<option value="" '.(!$that->post['cron_period'] ? 'selected' : '').'>'.$that->pi_getLL('manual').'</option>
		<option value="'.(3600*24).'" '.($that->post['cron_period']==(3600*24) ? 'selected' : '').'>'.$that->pi_getLL('daily').'</option>
		<option value="'.(3600*24*7).'" '.($that->post['cron_period']==(3600*24*7) ? 'selected' : '').'>'.$that->pi_getLL('weekly').'</option>
		<option value="'.(3600*24*30).'" '.($that->post['cron_period']==(3600*24*30) ? 'selected' : '').'>'.$that->pi_getLL('monthly').'</option>
		</select>
		</div>
		<div class="account-field">
		<label for="prefix_source_name">'.$that->pi_getLL('source_name').'</label>
		<input name="prefix_source_name" type="text" value="'.htmlspecialchars($that->post['prefix_source_name']).'" />
		</div>
		<input name="database_name" type="hidden" value="'.$that->post['database_name'].'" />
		<input name="cron_data" type="hidden" value="'.htmlspecialchars(serialize($that->post)).'" />
		</fieldset>
		<table cellspacing="0" id="nositenav" width="100%">
		<tr>
		<td align="right" ><input type="submit" class="submit_block" id="cl_submit" name="AdSubmit" value="'.($that->get['action']=='edit_job' ? $that->pi_getLL('save') : $that->pi_getLL('import')).'"></td>
		</tr>
		</table>
		<p class="extra_padding_bottom"></p>
		</form>

		';
				$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($tmpcontent).'</div>';
				// $content='<div
				// class="fullwidth_div">'.mslib_fe::shadowBox($tmpcontent).'</div>';
			}
			// preview eof
		} elseif ((is_numeric($that->get['job_id']) and $that->get['action']=='run_job') or ($that->post['action']=='import' and (($that->post['filename'] and file_exists($that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$that->post['filename'])) or $that->post['database_name']))) {
			if ((!$that->post['preProcExistingTask'] and $that->post['cron_name'] and !$that->post['skip_import']) or ($that->post['skip_import'] and $that->post['duplicate'])) {
				// we have to save the import job
				$updateArray=array();
				$updateArray['name']=$that->post['cron_name'];
				$updateArray['status']=1;
				$updateArray['last_run']=time();
				$updateArray['code']=md5(uniqid());
				$updateArray['period']=$that->post['cron_period'];
				$updateArray['prefix_source_name']=$that->post['prefix_source_name'];
				$cron_data=array();
				$cron_data[0]=unserialize($that->post['cron_period']);
				$that->post['cron_period']='';
				$cron_data[1]=$that->post;
				$updateArray['data']=serialize($cron_data);
				$updateArray['page_uid']=$that->shop_pid;
				$updateArray['categories_id']=$that->post['cid'];
				$updateArray['type']=$params['importKey'];
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_import_jobs', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				// we have to save the import job eof
				$that->ms['show_default_form']=1;
			} elseif ($that->post['skip_import']) {
				// we have to update the import job
				$updateArray=array();
				$updateArray['name']=$that->post['cron_name'];
				$updateArray['status']=1;
				$updateArray['last_run']=time();
				$updateArray['period']=$that->post['cron_period'];
				$updateArray['prefix_source_name']=$that->post['prefix_source_name'];
				$cron_data=array();
				$cron_data[0]=unserialize($that->post['cron_period']);
				$that->post['cron_period']='';
				$cron_data[1]=$that->post;
				$updateArray['data']=serialize($cron_data);
				$updateArray['page_uid']=$that->shop_pid;
				$updateArray['categories_id']=$that->post['cid'];
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$that->post['job_id'].' and type=\''.addslashes($params['importKey']).'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				// we have to update the import job eof
				$that->ms['show_default_form']=1;
			}
			if (!$that->post['skip_import']) {
				if (is_numeric($that->get['job_id'])) {
					// load the job
					$str="SELECT * from tx_multishop_import_jobs where id='".$that->get['job_id']."'".' and type=\''.addslashes($params['importKey']).'\'';
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					$data=unserialize($row['data']);
					// copy the previous post data to the current post so it can run the
					// job again
					$that->post=$data[1];
					if ($row['categories_id']) {
						$that->post['cid']=$row['categories_id'];
					}
					// update the last run time
					$updateArray=array();
					$updateArray['last_run']=time();
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id='.$row['id'].' and type=\''.addslashes($params['importKey']).'\'', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					// update the last run time eof
					if ($log_file) {
						file_put_contents($log_file, $that->FULL_HTTP_URL.' - cron job settings loaded.('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
					}
				}
				if ($that->post['file_url']) {
					if (strstr($that->post['file_url'], "../")) {
						die();
					}
					$filename=time();
					$file=$that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$filename;
					file_put_contents($file, mslib_fe::file_get_contents($that->post['file_url']));
				}
				if ($that->post['filename']) {
					$file=$that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$that->post['filename'];
				}
				if (($that->post['database_name'] or $file)) {
					if ($file) {
						$str=mslib_fe::file_get_contents($file);
					}
					if ($that->post['parser_template']) {
						// include a pre-defined xml to php array way
						require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_import_parser_templates/'.$that->post['parser_template'].".php");
						// include a pre-defined xml to php array way eof
					} else {
						if ($that->post['database_name']) {
							if ($log_file) {
								file_put_contents($log_file, $that->FULL_HTTP_URL.' - loading random products.('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
							}
							if (is_numeric($that->get['limit'])) {
								$limit=$that->get['limit'];
							} else {
								$limit=2000;
							}
							$datarows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $that->post['database_name'], '', '', '', $limit);
							$total_datarows=count($datarows);
							if ($log_file) {
								if ($total_datarows) {
									file_put_contents($log_file, $that->FULL_HTTP_URL.' - random products loaded, now starting the import.('.date("Y-m-d G:i:s").")\n", FILE_APPEND);
								} else {
									file_put_contents($log_file, $that->FULL_HTTP_URL.' - no products needed to be imported'."\n", FILE_APPEND);
								}
							}
							$i=0;
							foreach ($datarows as $datarow) {
								$s=0;
								foreach ($datarow as $datacol) {
									$rows[$i][$s]=$datacol;
									$s++;
								}
								// delete here
								// get first column name
								$str="delete from ".$that->post['database_name']." where internal_id='".$rows[$i][0]."'";
								$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
								$i++;
							}
						} else if ($that->post['format']=='excel') {
							require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
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
											$table_cols[]=t3lib_div::strtolower($clean_products_data);
										}
									}
									$counter++;
								}
							}
							// excel eof
						} else if ($that->post['format']=='xml') {
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
							if ($that->post['os']=='linux') {
								$splitter="\n";
							} else {
								$splitter="\r\n";
							}
							$str=trim($str, $splitter);
							if ($that->post['escape_first_line']) {
								$pos=strpos($str, $splitter);
								$str=substr($str, ($pos+strlen($splitter)));
							}
							// csv
							if ($that->post['delimiter']=="tab") {
								$delimiter="\t";
							} else if ($that->post['delimiter']=="dash") {
								$delimiter="|";
							} else if ($that->post['delimiter']=="dotcomma") {
								$delimiter=";";
							} else if ($that->post['delimiter']=="comma") {
								$delimiter=",";
							} else {
								$delimiter="\t";
							}
							if ($that->post['backquotes']) {
								$backquotes='"';
							} else {
								$backquotes='"';
							}
							if ($that->post['format']=='txt') {
								$row=1;
								$rows=array();
								if (($handle=fopen($file, "r"))!==false) {
									$counter=0;
									while (($data=fgetcsv($handle, '', $delimiter, $backquotes))!==false) {
										if ($that->post['escape_first_line']) {
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
						$that->ms['target-cid']=$that->post['cid'];
						$teller++;
						if (($that->post['escape_first_line'] and $teller>1) or !$that->post['escape_first_line']) {
							$tmpitem=$row;
							$cols=count($tmpitem);
							$flipped_select=array_flip($that->post['select']);
							// if($tmpitem[$that->post['select'][0]] and $cols > 0)
							// {
							$item=array();
							// if the source is a database table name add the unique id
							// so we can delete it after the import
							if ($that->post['database_name']) {
								$item['table_unique_id']=$row[0];
							}
							// aux
							$input=array();
							// name
							for ($i=0; $i<$cols; $i++) {
								$tmpitem[$i]=trim($tmpitem[$i]);
								$char='';
								$item[$that->post['select'][$i]]=$tmpitem[$i];
								if ($item[$that->post['select'][$i]]==$char and $char) {
									$item[$that->post['select'][$i]]='';
								}
								$input[$that->post['select'][$i]]=$that->post['input'][$i];
							}
							// custom hook that can be controlled by third-party plugin
							if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_import.php']['msAdminImportItemIterateProc'])) {
								$params=array(
									'importKey'=>&$params['importKey'],
									'row'=>&$row,
									'item'=>&$item,
									'prefix_source_name'=>$that->post['prefix_source_name'],
									'params'=>&$params,
									'content'=>&$content
								);
								foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.tx_mslib_admin_import.php']['msAdminImportItemIterateProc'] as $funcRef) {
									t3lib_div::callUserFunction($funcRef, $params, $that);
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
			$that->ms['show_default_form']=1;
		}
		if ($that->ms['show_default_form']) {
			$that->ms['upload_'.$params['importKey'].'feed_form']='<div id="upload_'.$params['importKey'].'feed_form">';
			$that->ms['upload_'.$params['importKey'].'feed_form'].='
	<fieldset>
	<legend>'.$that->pi_getLL('source').'</legend>
	<fieldset style="margin-top:5px;"><legend>'.$that->pi_getLL('file').'</legend>
	<ul>
	<li><input type="file" name="file" /></li>
	<li>URL <input name="file_url" type="text" /></li>
	<li>'.$that->pi_getLL('database_table').' <input name="database_name" type="text" /></li>
	</ul>
	</fieldset>
	';
			/*
			 * <li>URL <input name="file_url" type="text" /></li> <li>Database table
			 * <input name="database_name" type="text" /></li>
			 */
			$that->ms['upload_'.$params['importKey'].'feed_form'].='
	<fieldset><legend>'.ucfirst($that->pi_getLL('format')).'</legend>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(document).on("click", ".hide_advanced_import_radio", function() {
			$(this).parent().find(".hide").hide();
		});
		$(document).on("click", ".advanced_import_radio", function() {
			$(this).parent().find(".hide").show();
		});
	});
	</script>
  <input name="format" type="radio" value="excel" checked class="hide_advanced_import_radio" /> Excel
  <input name="format" type="radio" value="xml" class="hide_advanced_import_radio" /> XML
  <input name="format" type="radio" value="txt" class="advanced_import_radio" /> TXT/CSV
<div class="hide">
	'.$that->pi_getLL('delimited_by').': <select name="delimiter" id="delimiter">
	  <option value="dotcomma">'.$that->pi_getLL('dotcomma').'</option>
	  <option value="comma">'.$that->pi_getLL('comma').'</option>
	  <option value="tab">'.$that->pi_getLL('tab').'</option>
	  <option value="dash">'.$that->pi_getLL('dash').'</option>
	</select>
	<BR /><input name="backquotes" type="checkbox" value="1" /> '.$that->pi_getLL('fields_are_enclosed_with_double_quotes').'<BR />
	<input type="checkbox" name="escape_first_line" id="checkbox" value="1" /> '.$that->pi_getLL('ignore_first_line').'
	<input type="checkbox" name="os" id="os" value="linux" /> '.$that->pi_getLL('unix_file').'
	<input type="checkbox" name="consolidate" id="consolidate" value="1" /> '.$that->pi_getLL('consolidate').'
</div>
	<input type="submit" name="Submit" class="submit submit_block" id="cl_submit" value="'.$that->pi_getLL('upload').'" />
	<input name="action" type="hidden" value="import-preview" />
	</fieldset>
	</div>
	';
			$content.='
	 <form action="'.$params['postForm']['actionUrl'].'" method="post" enctype="multipart/form-data" name="form1" id="form1">
	 '.$that->ms['upload_'.$params['importKey'].'feed_form'].'
	</form>';
// load the jobs templates
			$str="SELECT * from tx_multishop_import_jobs where page_uid='".$that->shop_pid."' and type='".addslashes($params['importKey'])."' order by prefix_source_name asc, id desc";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$jobs=array();
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$jobs[]=$row;
			}
			if (count($jobs)>0) {
				$schedule_content.='
		<fieldset id="scheduled_import_jobs_form"><legend>'.$that->pi_getLL('import_tasks').'</legend>
		<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">
		<th>'.$that->pi_getLL('source_name').'</th>
		<th>'.$that->pi_getLL('name').'</th>
		<th>'.$that->pi_getLL('last_run').'</th>
		<th>'.$that->pi_getLL('action').'</th>
		<th>'.ucfirst($that->pi_getLL('status')).'</th>
		<th>'.ucfirst($that->pi_getLL('delete')).'</th>
		<th>'.$that->pi_getLL('file_exists').'</th>
		<th>'.$that->pi_getLL('upload_file').'</th>
		<th>'.$that->pi_getLL('download_import_task').'</th>';
				$switch='';
				foreach ($jobs as $job) {
					if ($switch=='odd') {
						$switch='even';
					} else {
						$switch='odd';
					}
					$schedule_content.='<tr class="'.$switch.'">';
					$schedule_content.='<td>'.$job['prefix_source_name'].'</td>
			<td><a href="'.$params['postForm']['actionUrl'].'&job_id='.$job['id'].'&action=edit_job">'.$job['name'].'</a></td>
			';
					$schedule_content.='<td nowrap align="right">'.date("Y-m-d", $job['last_run']).'<br />'.date("G:i:s", $job['last_run']).'</td>';
					if (!$job['period']) {
						$schedule_content.='<td>manual<br /><a href="'.$params['postForm']['actionUrl'].'&job_id='.$job['id'].'&action=run_job&limit=99999999" class="msadmin_button" onClick="return CONFIRM(\''.addslashes($that->pi_getLL('are_you_sure_you_want_to_run_the_import_job')).': '.htmlspecialchars(addslashes($job['name'])).'?\')"><i>'.$that->pi_getLL('run_now').'</i></a><br /><a href="" class="copy_to_clipboard" rel="'.htmlentities('/usr/bin/wget -O /dev/null --tries=1 --timeout=30 -q "'.$that->FULL_HTTP_URL.$params['postForm']['actionUrl'].'&job_id='.$job['id'].'&code='.$job['code'].'&action=run_job&run_as_cron=1&limit=99999999" >/dev/null 2>&1').'" ><i>'.$that->pi_getLL('run_by_crontab').'</i></a></td>';
					} else {
						$schedule_content.='<td>'.date("Y-m-d G:i:s", $job['last_run']+$job['period']).'</td>';
					}
					$schedule_content.='<td class="status_field" align="center">';
					if (!$job['status']) {
						$schedule_content.='<span class="admin_status_red" alt="Disable"></span>';
						$schedule_content.='<a href="'.$params['postForm']['actionUrl'].'&job_id='.$job['id'].'&status=1"><span class="admin_status_green_disable" alt="Enabled"></span></a>';
					} else {
						$schedule_content.='<a href="'.$params['postForm']['actionUrl'].'&job_id='.$job['id'].'&status=0"><span class="admin_status_red_disable" alt="Disabled"></span></a>';
						$schedule_content.='<span class="admin_status_green" alt="Enable"></span>';
					}
					$schedule_content.='</td>
			<td align="center">
			<a href="'.$params['postForm']['actionUrl'].'&job_id='.$job['id'].'&action=delete_category" onClick="return CONFIRM(\'Are you sure you want to delete the import job: '.htmlspecialchars($job['name']).'?\')" alt="Remove '.htmlspecialchars($job['name']).'" class="admin_menu_remove" title="Remove '.htmlspecialchars($job['name']).'"></a>
			</td>
			<td align="center">
				';
					$data=unserialize($job['data']);
					if ($data[1]['filename']) {
						$file_location=$that->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/'.$data[1]['filename'];
						if (file_exists($file_location)) {
							$schedule_content.='<span class="admin_status_green" alt="Enable"></span>';
						} else {
							$schedule_content.='<span class="admin_status_red" alt="Disable"></span>';
						}
					}
					$schedule_content.='
			</td>
			<td>
			 	<form action="'.$params['postForm']['actionUrl'].'" method="post" enctype="multipart/form-data" name="form1" id="form1">
					<input type="file" name="file" />
					<input type="submit" name="Submit" class="submit msadmin_button" id="cl_submit" value="'.$that->pi_getLL('upload').'" />
					<input name="skip_import" type="hidden" value="1" />
					<input name="preProcExistingTask" type="hidden" value="1" />
					<input name="job_id" type="hidden" value="'.$job['id'].'" />
					<input name="action" type="hidden" value="edit_job" />
				</form>
			</td>
			<td>
				<a href="'.$params['postForm']['actionUrl'].'&download=task&job_id='.$job['id'].'" class="msadmin_button"><i>'.$that->pi_getLL('download_import_task').'</i></a>
			</td>
			';
					$schedule_content.='</tr>';
				}
				$schedule_content.='</table>
		</fieldset>
		<script type="text/javascript">
		jQuery(document).ready(function($)
		{
			$(".copy_to_clipboard").click(function(event)
			{
				event.preventDefault();
				var string=$(this).attr("rel");
				$.blockUI({
				theme:     true,
				title:    \''.addslashes($that->pi_getLL('copy_below_text_and_add_it_to_crontab')).'\',
				message:  \'<p>\'+string+\'</p>\',
				timeout:   8000
				});
			});
		});
		</script>
		';
				$tmptab='';
				$content.=$schedule_content;
				//$tabs['tasks']=array($that->pi_getLL('import_tasks'),$schedule_content);
			}
			// load the jobs templates eof
			$content.='<fieldset id="scheduled_import_jobs_form"><legend>'.$that->pi_getLL('upload_import_task').'</legend>
		<form action="'.$params['postForm']['actionUrl'].'&upload=task" method="post" enctype="multipart/form-data" name="upload_task" id="upload_task" class="blockSubmitForm">
			<div class="account-field">
				<label for="new_cron_name">'.$that->pi_getLL('name').'</label>
				<input name="new_cron_name" type="text" value="" size="125">
			</div>
			<div class="account-field">
				<label for="new_prefix_source_name">'.$that->pi_getLL('source_name').'</label>
				<input name="new_prefix_source_name" type="text" value="" />
			</div>
			<div class="account-field">
				<label for="upload_task_file">'.$that->pi_getLL('file').'</label>
				<input type="file" name="task_file">&nbsp;<input type="submit" name="upload_task_file" class="submit msadmin_button" id="upload_task_file" value="upload">
			</div>
		</form>
	</fieldset>';
		}
		$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($that->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
		$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
		return $content;
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_admin_import.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.tx_mslib_admin_import.php"]);
}
?>