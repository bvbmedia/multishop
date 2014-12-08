<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel.php');
function getOptionValuesExtraName($optvalid) {
	$sql_opt="select products_options_values_extra_name from tx_multishop_products_options_values_extra where products_options_values_extra_id = ".$optvalid." and language_id = 0";
	$qry_opt=$GLOBALS['TYPO3_DB']->sql_query($sql_opt);
	$rs_opt=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_opt);
	$option_name=$rs_opt['products_options_values_extra_name'];
	return $option_name;
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

// -----------------------------------------------------------
if (isset($this->get['cid']) && $this->get['cid']>0) {
	$filename="producten_category_".mslib_befe::strtolower(mslib_fe::getNameCategoryById($this->get['cid']))."_".date('dmY').".xls";
	$sql="select p.products_id, pd.products_name, p.products_model, cd.categories_name, p.products_price, cd.categories_id, p.products_weight, p.products_quantity, pd.products_shortdescription, pd.products_meta_keywords from tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_categories_description cd, tx_multishop_categories c, tx_multishop_products_to_categories p2c where cd.categories_id = ".$this->get['cid']." and c.status = 1 and cd.language_id = 0 and pd.language_id = 0 and p.products_status = 1 and p.products_id = pd.products_id and pd.products_id = p2c.products_id and p2c.categories_id = cd.categories_id and cd.categories_id = c.categories_id group by p.products_id order by pd.products_name";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die('test: '.$sql);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)==0) {
		$show_cat_col=true;
		$catchild_id=mslib_fe::get_subcategory_ids($this->get['cid']);
		if (count($catchild_id)) {
			$sql_cat=" and (";
			$cc=1;
			foreach ($catchild_id as $catid) {
				if ($cc<count($catchild_id)) {
					$sql_cat.="cd.categories_id = ".$catid." OR ";
				} else {
					$sql_cat.="cd.categories_id = ".$catid;
				}
				$cc++;
			}
			$sql_cat.=")";
		}
		$sql="select p.products_id, pd.products_name, p.products_model, cd.categories_name, p.products_price, cd.categories_id, p.products_weight, p.products_quantity, pd.products_shortdescription, pd.products_meta_keywords from tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_categories_description cd, tx_multishop_categories c, tx_multishop_products_to_categories p2c where c.status = 1 and p.products_status = 1 and cd.language_id = 0 and pd.language_id = 0 ".$sql_cat." and p.products_id = pd.products_id and pd.products_id = p2c.products_id and p2c.categories_id = cd.categories_id and cd.categories_id = c.categories_id group by p.products_id order by pd.products_name";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die('test: '.$sql);
	}
} else {
	$filename="producten_all_".date('dmY').".xls";
	$sql="select p.products_id, pd.products_name, p.products_model, cd.categories_name, p.products_price, cd.categories_id, p.products_weight, p.products_quantity, pd.products_shortdescription, pd.products_meta_keywords from tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_categories_description cd, tx_multishop_categories c, tx_multishop_products_to_categories p2c where c.status = 1 and p.products_status = 1 and cd.language_id = 0 and pd.language_id = 0 and p.products_id = pd.products_id and pd.products_id = p2c.products_id and p2c.categories_id = cd.categories_id and cd.categories_id = c.categories_id group by p.products_id order by pd.products_name";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($sql) or die('test'.$GLOBALS['TYPO3_DB']->sql_error());
}
$dir=$this->DOCUMENT_ROOT;
$export_file=$dir."uploads/tx_multishop/tmp/".$filename;
// Creating a worksheet
if ($this->get['cid']>0) {
	$worksheet_name=mslib_befe::strtolower(mslib_fe::getNameCategoryById($this->get['cid']));
	$worksheet_name=str_replace(array(
		'/',
		'-',
		' '
	), '_', $worksheet_name);
} else {
	$worksheet_name='product_data';
}
// Creating a workbook
$phpexcel=new PHPExcel();
$phpexcel->getActiveSheet()->setTitle($worksheet_name);
$phpexcel->setActiveSheetIndex(0);
$header_style['font']=array('bold'=>true);
// write the header
$sheet_header[]='PID';
$sheet_header[]='Product';
$sheet_header[]='Model';
$sheet_header[]='Category';
$sheet_header[]='Price';
$sheet_header[]='Special Price';
$sheet_header[]='Weight';
$sheet_header[]='Quantity';
$sheet_header[]='Short Description';
$sheet_header[]='Keywords';
$sheet_header[]='Spider';
// write the header
$row_count=1;
$col_count=0;
$colwidth=array();
foreach ($sheet_header as $key=>$val) {
	$colwidth[$col_count]=strlen($val)+2;
	$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col_count, $row_count, $val);
	$col_count++;
}
$row_count++;
while ($rs=$GLOBALS['TYPO3_DB']->sql_fetch_row($qry)) {
	$sql_sp="select specials_new_products_price from tx_multishop_specials where products_id = ".$rs[0];
	$qry_sp=$GLOBALS['TYPO3_DB']->sql_query($sql_sp);
	$rs_sp=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_sp);
	if ($rs_sp['specials_new_products_price']==0 || empty($rs_sp['specials_new_products_price'])) {
		$rs_sp['specials_new_products_price']=0;
	}
	$GLOBALS['TYPO3_DB']->sql_free_result($qry_sp);
	$catpath_buffer=array();
	$sql2="select categories_id from tx_multishop_products_to_categories where products_id = ".$rs[0];
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($sql2);
	$multicats=array();
	while ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) {
		$multicats[]=$row2['categories_id'];
	}
	$multicats=array_unique($multicats);
	foreach ($multicats as $multicat) {
		$tmp_path='';
		$cats=mslib_fe::Crumbar($multicat);
		$cats=array_reverse($cats);
		$total_cats=count($cats);
		$pctr=1;
		foreach ($cats as $path) {
			$tmp_path.=$path['name'];
			if ($pctr<$total_cats) {
				$tmp_path.='||';
			}
			$pctr++;
		}
		$catpath_buffer[]=$tmp_path;
	}
	if (count($catpath_buffer)>1) {
		$rs[3]=implode(';', $catpath_buffer);
	} else {
		$rs[3]=$catpath_buffer[0];
	}
	$rs[5]=$rs_sp['specials_new_products_price'];
	foreach ($rs as $col=>$val) {
		if (strlen($val)+1>$colwidth[$col]) {
			$colwidth[$col]=strlen($val)+1;
		}
		$phpexcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row_count, $val);
	}
	$row_count++;
}
// set bold to the header title
$last_col=$phpexcel->getActiveSheet()->getHighestColumn();
$phpexcel->getActiveSheet()->getStyle('A1:'.$last_col.'1')->applyFromArray($header_style);
$col_id='A';
foreach ($colwidth as $col_key=>$col_val) {
	$phpexcel->getActiveSheet()->getColumnDimension($col_id)->setWidth($col_val);
	$col_id++;
}
// Let's send the file
// redirect output to client browser
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$objWriter=PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>