<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if (!$this->ms['image_paths']['products']['original']) {
	die('Protection. $ms image_paths products original variable is empty');
}
set_time_limit(86400);
ignore_user_abort(true);
if ($_GET['update_dates']) {
	$str="SELECT * from tx_multishop_products order by products_id asc";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$array=array("products_date_available"=>$row['products_date_added']);
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id='.$row['products_id'], $array);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
}
$content.='<div class="main-heading"><h1>Consistency Checker</h1></div>';
$str="SELECT products_id, products_status from tx_multishop_products";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$products=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if (!$row['products_status']) {
		// also delete disabled products?
//		$products[$row['products_id']]=$row;
	} else {
		$str2="select products_id from tx_multishop_products_description where products_id='".$row['products_id']."'";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
			$products[$row['products_id']]=$row;
		} else {
			$str2="select products_id,categories_id from tx_multishop_products_to_categories where products_id='".$row['products_id']."'";
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
				$products[$row['products_id']]=$row;
			} else {
				$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
				if (!$row2['categories_id']) {
					$products[$row['products_id']]=$row;
				}
			}
		}
	}
}
// vice versa
$str="SELECT products_id from tx_multishop_products_description";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select products_id from tx_multishop_products where products_id='".$row['products_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
		$products[$row['products_id']]=$row;
	}
}
$str="SELECT products_id from tx_multishop_products_attributes";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select products_id from tx_multishop_products where products_id='".$row['products_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
		$products[$row['products_id']]=$row;
	}
}
$str="SELECT products_id from tx_multishop_specials";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select products_id from tx_multishop_products where products_id='".$row['products_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
		$products[$row['products_id']]=$row;
	}
}
$content.='<div class="main-heading"><h2>Check 1</h2></div>';
if (count($products)>0) {
	$content.='<ul id="products_listing">';
	foreach ($products as $row) {
		$products_id=$row['products_id'];
		if (is_numeric($row['products_id'])) {
			for ($x=0; $x<=4; $x++) {
				$i=$x;
				if ($i==0) {
					$i='';
				}
				$filename=$row['products_image'.$i];
				if ($filename) {
					mslib_befe::deleteProductImage($filename);
				}
			}
			$tables=array();
			$tables[]='tx_multishop_products';
			$tables[]='tx_multishop_products_flat';
			$tables[]='tx_multishop_products_description';
			$tables[]='tx_multishop_products_to_categories';
			$tables[]='tx_multishop_products_attributes';
			$tables[]='tx_multishop_specials';
			$tables[]='tx_multishop_undo_products';
			$tables[]='tx_multishop_products_faq';
			$tables[]='tx_multishop_products_to_extra_options';
			foreach ($tables as $table) {
				$query=$GLOBALS['TYPO3_DB']->DELETEquery($table, 'products_id='.$products_id);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_relative_products', 'products_id='.$products_id.' or relative_product_id='.$products_id);
			$res=$GLOBALS['TYPO3_DB']->sql_query($qry);
		}
		$content.='<li>'.$row['products_name'].'</li>';
	}
	$content.='</ul>';
}
$content.='<strong>'.count($products).'</strong> products has been deleted.<br />';
// chk 2
$str="SELECT products_id from tx_multishop_products_to_categories";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$products=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select products_id from tx_multishop_products where products_id='".$row['products_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
		$products[$row['products_id']]=$row;
	}
}
$content.='<div class="main-heading"><h2>Check 2</h2></div>';
foreach ($products as $row) {
	$products_id=$row['products_id'];
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_categories', 'products_id='.$products_id);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
}
$content.='<strong>'.count($products).'</strong> orphaned products_to_categories relations has been deleted.<br />';
$str="SELECT categories_id from tx_multishop_categories";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$cats=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	if (!mslib_fe::hasProducts($row['categories_id']) and !mslib_fe::hasCats($row['categories_id'])) {
		$cats[$row['categories_id']]=$row;
	}
}
foreach ($cats as $row) {
	if ($row['categories_id']) {
		mslib_befe::deleteCategory($row['categories_id']);
	}
}
$content.='<strong>'.count($cats).'</strong> orphanned categories has been deleted.<br />';
// chk 3 the unused options
$content.='<div class="main-heading"><h2>Check 3</h2></div>';
$str="SELECT products_options_values_id from tx_multishop_products_options_values";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$option_values=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select products_id from tx_multishop_products_attributes where options_values_id='".$row['products_options_values_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)==0) {
		$option_values[]=$row['products_options_values_id'];
	}
}
// delete unused chains
$option_values_chains=0;
$str="SELECT * from tx_multishop_products_options ";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$str2="select * from tx_multishop_products_options_values_to_products_options where products_options_id='".$row['products_options_id']."'";
	$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$str3="select * from tx_multishop_products_attributes where options_id='".$row['products_options_id']."' and options_values_id='".$row2['products_options_values_id']."'";
			$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry3)) {
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values_to_products_options', 'products_options_values_to_products_options_id='.$row2['products_options_values_to_products_options_id']);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$option_values_chains++;
			}
		}
	}
}
$content.='<strong>'.$option_values_chains.'</strong> attribute option to value chains has been deleted.<br />';
// delete unused chains eof
if (count($option_values)) {
	foreach ($option_values as $products_options_values_id) {
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values', 'products_options_values_id='.$products_options_values_id);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_options_values_to_products_options', 'products_options_values_id='.$products_options_values_id);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
}
$content.='<strong>'.count($option_values).'</strong> orphanned option values has been deleted.<br />';
// chk 4 missing product images
$content.='<div class="main-heading"><h2>Check 4 (not existing product images)</h2></div>';
$unmapped_images=0;
$str="SELECT * from tx_multishop_products";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$updateArray=array();
	for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
		$x2=$x;
		if ($x2==0) {
			$x2='';
		}
		$filename=$row['products_image'.$x2];
		if ($filename) {
			$folder=mslib_befe::getImagePrefixFolder($filename);
			if (!file_exists($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$filename)) {
				// file no longer available. lets update the database
				$updateArray['products_image'.$x2]='';
				if (!$x2) {
					// first image
					$updateArray['contains_image']='0';
				}
			}
		}
	}
	if (count($updateArray)) {
		$unmapped_images++;
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id='.$row['products_id'], $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		// update flat database
		if ($this->ms['MODULES']['GLOBAL_MODULES']['FLAT_DATABASE']) {
			mslib_befe::convertProductToFlat($row['products_id'], 'tx_multishop_products_flat');
		}
	}
}
if ($unmapped_images) {
	$content.='<strong>'.$unmapped_images.'</strong> missing related images has been adjust to the database.<br />';
} else {
	$content.='Everything is fine.';
}
?>