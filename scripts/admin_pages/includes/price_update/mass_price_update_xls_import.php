<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
function getOptionID($optname) {
	$sql_opt="select products_options_id from tx_multishop_products_options where products_options_name = '".addslashes($optname)."'";
	$qry_opt=$GLOBALS['TYPO3_DB']->sql_query($sql_opt) or die($sql_opt."<br/>".$GLOBALS['TYPO3_DB']->sql_error());
	$rs_opt=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opt);
	$option_id=$rs_opt['products_options_id'];
	if (empty($option_id) || $option_id==0) {
		return false;
	} else {
		return $option_id;
	}
}

function getOptionValueID($optval) {
	$sql_opt="select products_options_values_id from tx_multishop_products_options_values where products_options_values_name = '".addslashes($optval)."' and language_id = 0";
	$qry_opt=$GLOBALS['TYPO3_DB']->sql_query($sql_opt) or die($sql_opt."<br/>".$GLOBALS['TYPO3_DB']->sql_error());
	$rs_opt=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opt);
	$option_val_id=$rs_opt['products_options_values_id'];
	if (empty($option_val_id) || $option_val_id==0) {
		return false;
	} else {
		return $option_val_id;
	}
}

function insertOptionValue($optid, $optval) {
	$sql="INSERT INTO `tx_multishop_products_options_values`(`products_options_values_id` , `language_id` , `products_options_values_name` , `hide`) VALUES(NULL , '0', '".addslashes($optval)."', '0')";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	$optvalid=$GLOBALS['TYPO3_DB']->sql_insert_id();
	$sql2="INSERT INTO `tx_multishop_products_options_values_to_products_options`(`products_options_values_to_products_options_id` , `products_options_id` , `products_options_values_id`,`sort_order`) VALUES(NULL , '".$optid."', '".$optvalid."','".time()."')";
	$GLOBALS['TYPO3_DB']->sql_query($sql2);
	return $optvalid;
}

function newOptionID() {
	$sql="select products_options_id from tx_multishop_products_options order by products_options_id desc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	$last_id=$rs['products_options_id'];
	$GLOBALS['TYPO3_DB']->sql_free_result($qry);
	return $last_id+1;
}

function insertOption($optname, $hide=0) {
	$optid=newOptionID();
	$sql="INSERT INTO `tx_multishop_products_options`(`products_options_id`, `language_id`, `products_options_name`, `listtype`, `sort_order`, `description`, `hide`) VALUES('".$optid."', '0', '".addslashes($optname)."', 'pulldownmenu', NULL , NULL ,  '".$hide."')";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	return $optid;
}

function updateOption($optid, $hide=0) {
	$sql="update `tx_multishop_products_options` set hide = '".$hide."' where products_options_id = ".$optid." and  language_id = 0";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
}

function updateProductAttribute($pid, $paid, $optvalid, $optval_price=0, $hide=0, $sign='+') {
	$sql="UPDATE `tx_multishop_products_attributes` SET `options_values_id` = '".$optvalid."', options_values_price = '".$optval_price."', hide = ".$hide.", price_prefix = '".$sign."' WHERE `products_attributes_id` = '".$paid."' and products_id = ".$pid." LIMIT 1";
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
}

function insertProductAttribute($pid, $optid, $optvalid, $optval_price=0, $hide=0, $sign='+') {
	$sql="INSERT INTO `tx_multishop_products_attributes`(`products_attributes_id`, `products_id`, `options_id`, `options_values_id`, `options_values_price`, `price_prefix`, `products_stock`, `hide`) VALUES(NULL , '".$pid."', '".$optid."', '".$optvalid."', '".$optval_price."', '".$sign."', '0', '".$hide."')";
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
}

function removeProductAttribute($pid, $optid) {
	$sql="DELETE FROM `tx_multishop_products_attributes` WHERE products_id = ".$pid." and options_id = ".$optid;
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($sql.' --- '.$GLOBALS['TYPO3_DB']->sql_error());
}

function getProductAttributeID($pid, $optid, $optvalid) {
	$sql_opt="select products_attributes_id from tx_multishop_products_attributes where products_id = '".$pid."' and options_id = ".$optid." and options_values_id = ".$optvalid;
	$qry_opt=$GLOBALS['TYPO3_DB']->sql_query($sql_opt) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_opt);
	$rs_opt=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opt);
	$pa_id=$rs_opt['products_attributes_id'];
	if (empty($pa_id) || $pa_id==0) {
		return false;
	} else {
		return $pa_id;
	}
}

function getCategoryID($catname, $language_id, $parent_id=0) {
	$sql="select c.parent_id, cd.categories_id from tx_multishop_categories_description cd, tx_multishop_categories c where cd.categories_id = c.categories_id and c.parent_id = ".$parent_id." and cd.categories_name = '".addslashes($catname)."' and cd.language_id = ".$language_id;
	echo $sql."\n\n";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	$catid=$rs['categories_id'];
	$GLOBALS['TYPO3_DB']->sql_free_result($qry);
	if (!empty($catid) && $catid>0) {
		return $catid;
	} else {
		return false;
	}
}

function insertCategory($catname, $shop_pid, $language_id, $parent_id=0) {
	$sql="INSERT INTO `tx_multishop_categories`(`categories_id`, `parent_id`, `date_added`, `status`, `page_uid`) VALUES(NULL , '".$parent_id."', NOW(), '1', '".$shop_pid."')";
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	$catid=$GLOBALS['TYPO3_DB']->sql_insert_id();
	$sql2="INSERT INTO `tx_multishop_categories_description`(`categories_id`, `language_id`, `categories_name`, `shortdescription`) VALUES(".$catid." , '".$language_id."', '".addslashes($catname)."', '".addslashes($catname)."')";
	$GLOBALS['TYPO3_DB']->sql_query($sql2) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql2);
	return $catid;
}

function insertProduct($name, $model, $price) {
	$sql="INSERT INTO `tx_multishop_products`(`products_id` , `products_quantity` , `products_model` , `products_image` , `products_image1` , `products_image2` , `products_image3` , `products_image4` , `products_price` , `products_date_added` , `products_last_modified` , `products_date_available` , `products_weight` , `products_status` , `tax_id` , `manufacturers_id`) VALUES(NULL , '0', '".addslashes($model)."' , NULL , '', '', '', '', '".$price."', NOW() , NULL , NULL , '0.00', '1', '1', NULL)";
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	$pid=$GLOBALS['TYPO3_DB']->sql_insert_id();
	$sql2="INSERT INTO `tx_multishop_products_description`(`products_id` , `language_id` , `products_name` , `products_description` , `products_url` , `products_viewed` , `products_shortdescription` , `products_meta_keywords` , `ppc` , `form_code`) VALUES(".$pid." , '0', '".addslashes($name)."', '".addslashes($name)."' , NULL , '0', '".addslashes($name)."' , NULL , NULL , NULL)";
	$GLOBALS['TYPO3_DB']->sql_query($sql2) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql2);
	return $pid;
}

function productToCategories($pid, $cid, $update=false) {
	$sql_check="select * from tx_multishop_products_to_categories where products_id = ".$pid." and categories_id = ".$cid;
	$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)>0) {
		$sql="update `tx_multishop_products_to_categories` set categories_id = ".$cid." where products_id = ".$pid;
		$GLOBALS['TYPO3_DB']->sql_query($sql);
	} else {
		$sql="INSERT INTO `tx_multishop_products_to_categories`(`products_id` , `categories_id`) VALUES(".$pid." , ".$cid.")";
		$GLOBALS['TYPO3_DB']->sql_query($sql);
	}
	return $sql."<br />";
}

function checkMultiCat($pid) {
	$sql="select categories_id from tx_multishop_products_to_categories where products_id = ".$pid;
	echo $sql."<br />";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($sql.' --- '.$GLOBALS['TYPO3_DB']->sql_error());
	$multicats=array();
	while ($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
		$multicats[]=$rs['categories_id'];
	}
	$GLOBALS['TYPO3_DB']->sql_free_result($qry);
	return $multicats;
}

function removeCatReference($pid, $cid) {
	$sql="delete from tx_multishop_products_to_categories where products_id = ".$pid." and categories_id = ".$cid." limit 1";
	$GLOBALS['TYPO3_DB']->sql_query($sql);
}

function getOptionValueExtraID($optval) {
	$sql_opt="select products_options_values_extra_id from tx_multishop_products_options_values_extra where products_options_values_extra_name = '".addslashes($optval)."' and language_id = 0";
	$qry_opt=$GLOBALS['TYPO3_DB']->sql_query($sql_opt) or die($sql_opt."<br/>".$GLOBALS['TYPO3_DB']->sql_error());
	$rs_opt=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opt);
	$option_val_id=$rs_opt['products_options_values_extra_id'];
	if (empty($option_val_id) || $option_val_id==0) {
		return false;
	} else {
		return $option_val_id;
	}
}

function insertOptionExtraValue($optid, $optval) {
	$sql="INSERT INTO `tx_multishop_products_options_values_extra`(`products_options_values_extra_id` , `language_id` , `products_options_values_extra_name` , `hide`) VALUES(NULL , '0', '".addslashes($optval)."', '0')";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
	$optvalid=$GLOBALS['TYPO3_DB']->sql_insert_id();
	return $optvalid;
}

function getProductAttributeExtraID($pid, $optvalid) {
	$sql_opt="select products_attributes_extra_id from tx_multishop_products_attributes_extra where products_id = '".$pid."' and options_values_extra_id = ".$optvalid;
	$qry_opt=$GLOBALS['TYPO3_DB']->sql_query($sql_opt) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_opt);
	$rs_opt=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opt);
	$pa_id=$rs_opt['products_attributes_extra_id'];
	if (empty($pa_id) || $pa_id==0) {
		return false;
	} else {
		return $pa_id;
	}
}

function insertProductAttributeExtra($pid, $optvalid, $optval_price=0, $hide=0, $sign='+') {
	$sql="INSERT INTO `tx_multishop_products_attributes_extra`(`products_attributes_extra_id`, `products_id`, `options_values_extra_id`, `options_values_price`, `price_prefix`, `products_stock`, `hide`) VALUES(NULL , '".$pid."', '".$optvalid."', '".$optval_price."', '".$sign."', '0', '".$hide."')";
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
}

function updateProductAttributeExtra($pid, $paid, $optvalid, $optval_price=0, $hide=0, $sign='+') {
	$sql="UPDATE `tx_multishop_products_attributes_extra` SET `options_values_extra_id` = '".$optvalid."', options_values_price = '".$optval_price."', hide = ".$hide.", price_prefix = '".$sign."' WHERE `products_attributes_extra_id` = '".$paid."' and products_id = ".$pid." LIMIT 1";
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
}

function removeProductAttributeExtra($pid, $paid) {
	$sql="DELETE FROM `tx_multishop_products_attributes_extra` WHERE products_id = ".$pid;
	$GLOBALS['TYPO3_DB']->sql_query($sql) or die($sql.' --- '.$GLOBALS['TYPO3_DB']->sql_error());
}

if (!empty($filename)) {
	// +--------------------------------------------------------------------------------------------------------------------+	
	require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
	$phpexcel=PHPExcel_IOFactory::load($dest);
	foreach ($phpexcel->getWorksheetIterator() as $worksheet) {
		$colname=array();
		$colcontent=array();
		$row_counter=0;
		foreach ($worksheet->getRowIterator() as $row) {
			$cellIterator=$row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$col_counter=0;
			foreach ($cellIterator as $cell) {
				$clean_products_data=ltrim(rtrim($cell->getCalculatedValue(), " ,"), " ,");
				$clean_products_data=trim($clean_products_data);
				if ($row->getRowIndex()>1) {
					$colcontent[$row_counter][$col_counter]=$clean_products_data;
				} else {
					$colname[$col_counter]=mslib_befe::strtolower($clean_products_data);
				}
				$col_counter++;
			}
			$row_counter++;
		}
	}
	foreach ($colcontent as $row_key=>$row_val) {
		$pid='';
		$product_name='';
		$model='';
		$category='';
		$category2='';
		$price='';
		$sprice='';
		$weight='';
		$stock='';
		$short_desc='';
		$keyword='';
		$productfeed='';
		$cprice='';
		$dprice='';
		$morder='';
		$eancode='';
		$relation='';
		$status='';
		$neg_keyword='';
		$promotext='';
		$leverancier='';
		$image_url='';
		$extra_attributes='';
		$option=array();
		$optstat=array();
		foreach ($row_val as $col_key=>$col_val) {
			switch ($col_key) {
				case 0 :
					$pid=(int)$col_val;
					break;
				case 1 :
					$product_name=$col_val;
					break;
				case 2 :
					$model=$col_val;
					break;
				case 3 :
					$category=$col_val;
					break;
				case 4 :
					$price=$col_val;
					break;
				case 5 :
					$sprice=$col_val;
					break;
				case 6 :
					$weight=$col_val;
					break;
				case 7 :
					$stock=$col_val;
					break;
				case 8 :
					$short_desc=$col_val;
					break;
				case 9 :
					$keyword=$col_val;
					break;
				case 10 :
					$productfeed=$col_val;
					break;
				default :
					if ($colname[$col_key]=='capital price') {
						$cprice=$col_val;
					} else {
						if ($colname[$col_key]=='dealer price') {
							$dprice=$col_val;
						} else {
							if ($colname[$col_key]=='minimum order') {
								$morder=$col_val;
							} else {
								if ($colname[$col_key]=='ean') {
									$eancode=$col_val;
								} else {
									if ($colname[$col_key]=='relation') {
										$relation=$col_val;
									} else {
										if ($colname[$col_key]=='status') {
											$status=$col_val;
										} else {
											if ($colname[$col_key]=='neg. keywords') {
												$neg_keyword=$col_val;
											} else {
												if ($colname[$col_key]=='promotext') {
													$promotext=$col_val;
												} else {
													if ($colname[$col_key]=='leveranciers') {
														$leverancier=$col_val;
													} else {
														if ($colname[$col_key]=='image url') {
															$image_url=$col_val;
														} else {
															if ($colname[$col_key]=='extra attributes') {
																$extra_attributes=$col_val;
															} else {
																if (substr($colname[$col_key], 0, 2)=='a:') {
																	$tmp=explode(':', $colname[$col_key]);
																	$option[$tmp[1]]=$col_val;
																	$optstat[$tmp[1]]=$tmp[2];
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
					break;
			}
		}
		// product information extracted from excel file
		if (!empty($pid) && $pid>0) {
			if (empty($product_name) && empty($price)) {
				$sql_delete="delete from tx_multishop_products where products_id = ".$pid;
				$GLOBALS['TYPO3_DB']->sql_query($sql_delete) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_delete);
				$sql_delete="delete from tx_multishop_products_description where products_id = ".$pid;
				$GLOBALS['TYPO3_DB']->sql_query($sql_delete) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_delete);
				$sql_delete="delete from tx_multishop_products_to_categories where products_id = ".$pid;
				$GLOBALS['TYPO3_DB']->sql_query($sql_delete) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_delete);
				$sql_delete="delete from tx_multishop_products_attributes where products_id = ".$pid;
				$GLOBALS['TYPO3_DB']->sql_query($sql_delete) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_delete);
				$sql_delete="delete from tx_multishop_specials where products_id = ".$pid;
				$GLOBALS['TYPO3_DB']->sql_query($sql_delete) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_delete);
			} else {
				echo $product_name." --- ".$pid."<br />";
				$referred_cid=array(); // hold the refereed cid
				$multicats=checkMultiCat($pid);
				$category2=explode(';', $category);
				for ($c=0; $c<count($category2); $c++) {
					$catdata=explode('||', $category2[$c]);
					$catdata_id=array();
					for ($s=0; $s<count($catdata); $s++) {
						if ($s>0) {
							$cid=getCategoryID($catdata[$s], $this->sys_language_uid, $catdata_id[$s-1]);
						} else {
							$cid=getCategoryID($catdata[$s], $this->sys_language_uid);
						}
						if (!$cid) {
							if ($s>0) {
								$cid=insertCategory($catdata[$s], $this->shop_pid, $this->sys_language_uid, $catdata_id[$s-1]);
							} else {
								$cid=insertCategory($catdata[$s], $this->shop_pid, $this->sys_language_uid, 0);
							}
						}
						$catdata_id[$s]=$cid;
					}
					if (count($category2)>1) {
						echo '1 -- '.productToCategories($pid, $catdata_id[count($catdata_id)-1]);
						$referred_cid[]=$catdata_id[count($catdata_id)-1];
					} else {
						print_r($multicats);
						if (count($multicats)>0) {
							foreach ($multicats as $multicat) {
								if ($multicat==$catdata_id[count($catdata_id)-1]) {
									echo '2 -- '.productToCategories($pid, $catdata_id[count($catdata_id)-1], true);
								} else {
									echo 'removing cat ref '.$multicat." -- I<br />";
									removeCatReference($pid, $multicat);
								}
								echo 'MC -- '.$multicat.' ---- '.count($catdata_id)-1 ."<br />";
							}
						} else {
							echo '3 -- '.productToCategories($pid, $catdata_id[count($catdata_id)-1]);
						}
					}
				}
				if (count($category2)>1) {
					foreach ($multicats as $multicat) {
						if (!in_array($multicat, $referred_cid)) {
							echo 'removing cat ref '.$multicat." -- II<br />";
							removeCatReference($pid, $multicat);
						}
					}
				}
				if (!empty($price) && $price>0) {
					$sql="update tx_multishop_products set products_price = '".$price."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
				}
				if (!empty($sprice) && $sprice>0) {
					$sql_check="select products_id from tx_multishop_specials where products_id = ".$pid;
					$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)>0) {
						$sql_update="update tx_multishop_specials set specials_new_products_price = '".$sprice."' where products_id = ".$pid;
						$GLOBALS['TYPO3_DB']->sql_query($sql_update) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_update);
					} else {
						$sql_insert="insert into tx_multishop_specials(products_id, specials_new_products_price, status) values(".$pid.", '".$sprice."', 1)";
						$GLOBALS['TYPO3_DB']->sql_query($sql_insert);
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qry_check);
				} else {
					$sql_check="select products_id from tx_multishop_specials where products_id = ".$pid;
					$qry_check=$GLOBALS['TYPO3_DB']->sql_query($sql_check);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check)>0) {
						$sql_delete="delete from tx_multishop_specials where products_id = ".$pid;
						$GLOBALS['TYPO3_DB']->sql_query($sql_delete) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_delete);
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qry_check);
				}
				if (!empty($weight)) {
					$sql_upd="update tx_multishop_products set products_weight = '".$weight."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($product_name)) {
					$sql_upd="update tx_multishop_products_description set products_name = '".addslashes($product_name)."' where products_id = ".$pid." and language_id = 0";
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($model)) {
					$sql_upd="update tx_multishop_products set products_model = '".addslashes($model)."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products set products_model = '' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($stock)) {
					$sql_upd="update tx_multishop_products set products_quantity = '".$stock."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($short_desc)) {
					$sql_upd="update tx_multishop_products_description set products_shortdescription = '".addslashes($short_desc)."' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products_description set products_shortdescription = '' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($keyword)) {
					$sql_upd="update tx_multishop_products_description set products_meta_keywords = '".addslashes($keyword)."' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products_description set products_meta_keywords = '' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if ($productfeed==1 || $productfeed==0) {
					$sql_upd="update tx_multishop_products set productfeed = '".$productfeed."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($relation)) {
					$sql1="DELETE FROM tx_multishop_products_to_relative_products where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql1) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
					$sql="insert into tx_multishop_products_to_relative_products(products_to_relative_product_id, products_id, relative_product_id) values ";
					$values='';
					$relate_id=explode(';', $relation);
					for ($y=0; $y<count($relate_id); $y++) {
						if (!empty($relate_id[$y])) {
							if (empty($values)) {
								$values.="('', ".$pid.", ".$relate_id[$y].")";
							} else {
								$values.=",('', ".$pid.", ".$relate_id[$y].")";
							}
						}
					}
					if (!empty($values)) {
						$sql.=$values;
						$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
					}
				} else {
					$sql1="DELETE FROM tx_multishop_products_to_relative_products where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql1) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql1);
				}
				if (!empty($eancode)) {
					$sql_upd="update tx_multishop_products set extid = '".addslashes($eancode)."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($leverancier)) {
					$sql_man="select manufacturers_id from tx_multishop_manufacturers where manufacturers_name = '".addslashes($leverancier)."'";
					$qry_man=$GLOBALS['TYPO3_DB']->sql_query($sql_man);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_man)) {
						$rs_man=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_man);
						$manufacturer_id=$rs_man['manufacturers_id'];
					} else {
						$sql_iman="INSERT INTO tx_multishop_manufacturers(manufacturers_id, manufacturers_name, date_added, sort_order) VALUES(NULL , '".addslashes($leverancier)."', NOW() , '0')";
						$GLOBALS['TYPO3_DB']->sql_query($sql_iman);
						$manufacturer_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
					}
					$sql_upd="update tx_multishop_products set manufacturers_id = '".$manufacturer_id."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (count($option)>0) {
					foreach ($option as $optname=>$optval) {
						$hide=0;
						if ($optstat[$optname]=='hide') {
							$hide=1;
						}
						$optname=ucwords($optname);
						if (!empty($optname)) {
							$optid=getOptionID($optname);
							if (!$optid) {
								$optid=insertOption($optname, $hide);
							} else {
								updateOption($optid, $hide);
							}
							removeProductAttribute($pid, $optid);
							if (!empty($optval)) {
								$value_tmp=explode(';;', $optval);
								foreach ($value_tmp as $optval2) {
									$valstat=0;
									$price=0;
									list($optval3, $optvalprice)=explode('||', $optval2);
									if (substr($optvalprice, 0, 1)=='+') {
										$optvalprice=str_replace('+', '', $optvalprice);
										$pprefix='+';
									} else {
										if (substr($optvalprice, 0, 1)=='-') {
											$optvalprice=str_replace('-', '', $optvalprice);
											$pprefix='-';
										} else {
											if ($optvalprice!=0) {
												$pprefix='+';
											}
										}
									}
									if (!empty($optvalprice) && $optvalprice>0) {
										$price=$optvalprice;
									} else {
										$pprefix='';
									}
									if (strpos($optval3, ':hide')!==false) {
										$valstat=1;
										$optval3=str_replace(':hide', '', $optval3);
									} else {
										if (strpos($optval3, ':unhide')!==false) {
											$optval3=str_replace(':unhide', '', $optval3);
										}
									}
									if (!$optvalid=getOptionValueID($optval3)) {
										$optvalid=insertOptionValue($optid, $optval3);
									}
									if (!$paid=getProductAttributeID($pid, $optid, $optvalid)) {
										insertProductAttribute($pid, $optid, $optvalid, $price, $valstat, $pprefix);
									} else {
										updateProductAttribute($pid, $paid, $optvalid, $price, $valstat, $pprefix);
									}
								}
							} else {
								removeProductAttribute($pid, $optid);
							}
						}
					}
				}
				if (!empty($extra_attributes)) {
					$optval=$extra_attributes;
					removeProductAttributeExtra($pid);
					if (!empty($optval)) {
						$value_tmp=explode(';;', $optval);
						foreach ($value_tmp as $optval2) {
							$valstat=0;
							$price=0;
							list($optval3, $optvalprice)=explode('||', $optval2);
							if (substr($optvalprice, 0, 1)=='+') {
								$optvalprice=str_replace('+', '', $optvalprice);
								$pprefix='+';
							} else {
								if (substr($optvalprice, 0, 1)=='-') {
									$optvalprice=str_replace('-', '', $optvalprice);
									$pprefix='-';
								} else {
									if ($optvalprice!=0) {
										$pprefix='+';
									}
								}
							}
							if (!empty($optvalprice) && $optvalprice>0) {
								$price=$optvalprice;
							} else {
								$pprefix='';
							}
							if (!$optvalid=getOptionValueExtraID($optval3)) {
								$optvalid=insertOptionExtraValue($optid, $optval3);
							}
							if (!$paid=getProductAttributeExtraID($pid, $optvalid)) {
								insertProductAttributeExtra($pid, $optvalid, $price, $valstat, $pprefix);
							} else {
								updateProductAttributeExtra($pid, $paid, $optvalid, $price, $valstat, $pprefix);
							}
						}
					}
				} else {
					removeProductAttributeExtra($pid);
				}
			}
		} else {
			if ($product_name) {
				// if products name is defined add it
				$pid=insertProduct($product_name, $model, $price);
				$referred_cid=array(); // hold the refereed cid
				$multicats=checkMultiCat($pid);
				$category2=explode(';', $category);
				for ($c=0; $c<count($category2); $c++) {
					$catdata=explode('||', $category2[$c]);
					$catdata_id=array();
					for ($s=0; $s<count($catdata); $s++) {
						if ($s>0) {
							$cid=getCategoryID($catdata[$s], $this->sys_language_uid, $catdata_id[$s-1]);
						} else {
							$cid=getCategoryID($catdata[$s], $this->sys_language_uid);
						}
						if (!$cid) {
							if ($s>0) {
								$cid=insertCategory($catdata[$s], $this->shop_pid, $this->sys_language_uid, $catdata_id[$s-1]);
							} else {
								$cid=insertCategory($catdata[$s], $this->shop_pid, $this->sys_language_uid, 0);
							}
						}
						$catdata_id[$s]=$cid;
					}
					if (count($category2)>1) {
						productToCategories($pid, $catdata_id[count($catdata_id)-1]);
						$referred_cid[]=$catdata_id[count($catdata_id)-1];
					} else {
						productToCategories($pid, $catdata_id[count($catdata_id)-1]);
						$referred_cid[]=$catdata_id[count($catdata_id)-1];
					}
				}
				if (!empty($relation)) {
					$sql="insert into tx_multishop_products_to_relative_products(products_to_relative_product_id, products_id, relative_product_id) values ";
					$values='';
					$relate_id=explode(';', $relation);
					for ($y=0; $y<count($relate_id); $y++) {
						if (!empty($relate_id[$y])) {
							if (empty($values)) {
								$values.="('', ".$pid.", ".$relate_id[$y].")";
							} else {
								$values.=",('', ".$pid.", ".$relate_id[$y].")";
							}
						}
					}
					if (!empty($values)) {
						$sql.=$values;
						$GLOBALS['TYPO3_DB']->sql_query($sql) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql);
					}
				}
				if (!empty($sprice) && $sprice>0) {
					$sql_insert="insert into tx_multishop_specials(products_id, specials_new_products_price, status, home_item) values(".$pid.", '".$sprice."', 1, 1)";
					$GLOBALS['TYPO3_DB']->sql_query($sql_insert) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_insert);
				}
				if (!empty($weight)) {
					$sql_upd="update tx_multishop_products set products_weight = '".$weight."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($stock)) {
					$sql_upd="update tx_multishop_products set products_quantity = '".$stock."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($short_desc)) {
					$sql_upd="update tx_multishop_products_description set products_shortdescription = '".addslashes($short_desc)."' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products_description set products_shortdescription = '' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($keyword)) {
					$sql_upd="update tx_multishop_products_description set products_meta_keywords = '".addslashes($keyword)."' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products_description set products_meta_keywords = '' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if ($productfeed==1 || $productfeed==0) {
					$sql_upd="update tx_multishop_products set productfeed = '".$productfeed."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($cprice)) {
					$sql_upd="update tx_multishop_products set product_capital_price = '".$cprice."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if ($status!='') {
					if ($status==0) {
						$sql_upd="update tx_multishop_products set products_status = 0 where products_id = ".$pid;
						$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
					} else {
						if ($status==1) {
							$sql_upd="update tx_multishop_products set products_status = 1 where products_id = ".$pid;
							$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
						}
					}
				}
				if (!empty($morder)) {
					$sql_upd="update tx_multishop_products set minimum_order = '".$morder."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($eancode)) {
					$sql_upd="update tx_multishop_products set extid = '".$eancode."' where products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($neg_keyword)) {
					$sql_upd="update tx_multishop_products_description set products_negative_keywords = '".addslashes($neg_keyword)."' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products_description set products_negative_keywords = '' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (!empty($promotext)) {
					$sql_upd="update tx_multishop_products_description set promotext = '".addslashes($promotext)."' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				} else {
					$sql_upd="update tx_multishop_products_description set promotext = '' where language_id = 0 and products_id = ".$pid;
					$GLOBALS['TYPO3_DB']->sql_query($sql_upd) or die($HTTP_HOST.'-'.$_SERVER['PHP_SELF'].' --- '.$GLOBALS['TYPO3_DB']->sql_error()."\n\n".$sql_upd);
				}
				if (count($option)>0) {
					foreach ($option as $optname=>$optval) {
						$hide=0;
						if ($optstat[$optname]=='hide') {
							$hide=1;
						}
						$optname=ucwords($optname);
						if (!empty($optname)) {
							$optid=getOptionID($optname);
							if (!$optid) {
								$optid=insertOption($optname, $hide);
							} else {
								updateOption($optid, $hide);
							}
							if (!empty($optval)) {
								$value_tmp=explode(';;', $optval);
								foreach ($value_tmp as $optval2) {
									$valstat=0;
									$price=0;
									list($optval3, $optvalprice)=explode('||', $optval2);
									if (substr($optvalprice, 0, 1)=='+') {
										$optvalprice=str_replace('+', '', $optvalprice);
										$pprefix='+';
									} else {
										if (substr($optvalprice, 0, 1)=='-') {
											$optvalprice=str_replace('-', '', $optvalprice);
											$pprefix='-';
										} else {
											if ($optvalprice!=0) {
												$pprefix='+';
											}
										}
									}
									if (!empty($optvalprice) && $optvalprice>0) {
										$price=$optvalprice;
									} else {
										$pprefix='';
									}
									if (strpos($optval3, ':hide')!==false) {
										$valstat=1;
										$optval3=str_replace(':hide', '', $optval3);
									} else {
										if (strpos($optval3, ':unhide')!==false) {
											$optval3=str_replace(':unhide', '', $optval3);
										}
									}
									if (!$optvalid=getOptionValueID($optval3)) {
										$optvalid=insertOptionValue($optid, $optval3);
									}
									if (!$paid=getProductAttributeID($pid, $optid, $optvalid)) {
										insertProductAttribute($pid, $optid, $optvalid, $price, $valstat, $pprefix);
									} else {
										updateProductAttribute($pid, $paid, $optvalid, $price, $valstat, $pprefix);
									}
								}
							} else {
								removeProductAttribute($pid, $optid);
							}
						}
					}
				}
			} // if products name defined
		}
	}
	// +--------------------------------------------------------------------------------------------------------------------+
	@unlink($this->DOCUMENT_ROOT.'var/tmp/'.$filename);
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_products_search_and_edit&cid='.$this->post['cid'], 1));
}
?>