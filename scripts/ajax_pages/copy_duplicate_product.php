<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$id_category=$_REQUEST['idcategory'];
$id_product=$_REQUEST['pid'];
$type_copy=$_REQUEST['type_copy'];
if($id_category == 0) {
	echo "<p>Please select category,  main category it's not allowed</p>";
} else {
	if($type_copy == "copy") {
		$str="SELECT * from tx_multishop_products_to_categories where products_id = $id_product AND categories_id = $id_category";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
			echo "<p>Products already existing on this category</p>";
		} else {
			$insertArray=array(
				'products_id'=>$id_product,
				'categories_id'=>$id_category);
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			echo "<p>Product succesfully copied</p>";
			if($this->ms['MODULES']['FLAT_DATABASE']) {
				// if the flat database module is enabled we have to sync the changes to the flat table
				mslib_befe::convertProductToFlat($id_product);
			}
		}
	}
	if($type_copy == "duplicate") {
		$str="SELECT * from tx_multishop_products where products_id = $id_product";
		//echo $str;
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if($GLOBALS['TYPO3_DB']->sql_num_rows($qry) == 0) {
			echo "<p>Product doesn't exist</p>";
		} else {
			//insert into tx_multishop_products
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$product_arr_new=array();
			foreach($row as $key_p=>$val_p) {
				if($key_p != 'products_id') {
					if($key_p == 'products_image' or $key_p == 'products_image1' or $key_p == 'products_image2' or $key_p == 'products_image3' or $key_p == 'products_image4') {
						if(!empty($val_p)) {
							$str="SELECT * from tx_multishop_products_description where products_id = $id_product and language_id='".$this->sys_language_uid."'";
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							$row_desc=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
							$file=mslib_befe::getImagePath($val_p, 'products', 'original');
							//echo $file;
							$imgtype=mslib_befe::exif_imagetype($file);
							if($imgtype) {
								// valid image
								$ext=image_type_to_extension($imgtype, false);
								if($ext) {
									$i=0;
									$filename=mslib_fe::rewritenamein($row_desc['products_name']).'.'.$ext;
									//echo $filename;
									$folder=mslib_befe::getImagePrefixFolder($filename);
									$array=explode(".", $filename);
									if(!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
										t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
									}
									$folder.='/';
									$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
									//echo $target;
									if(file_exists($target)) {
										do {
											$filename=mslib_fe::rewritenamein($row_desc['products_name']).($i > 0 ? '-'.$i : '').'.'.$ext;
											$folder_name=mslib_befe::getImagePrefixFolder($filename);
											$array=explode(".", $filename);
											$folder=$folder_name;
											if(!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
												t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
											}
											$folder.='/';
											$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
											$i++;
											//echo $target . "<br/>";
										} while(file_exists($target));
									}
									if(copy($file, $target)) {
										$target_origineel=$target;
										$update_product_images=mslib_befe::resizeProductImage($target_origineel, $filename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath($this->extKey));
									}
								}
							}
							$product_arr_new[$key_p]=$update_product_images;
						} else {
							$product_arr_new[$key_p]=$val_p;
						}
					} else {
						$product_arr_new[$key_p]=$val_p;
					}
				}
			}
			$product_arr_new['sort_order']=time();
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products', $product_arr_new);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$id_product_new=$GLOBALS['TYPO3_DB']->sql_insert_id();
			unset($product_arr_new);
			if($id_product_new) {
				// insert tx_multishop_products_description
				$str="SELECT * from tx_multishop_products_description where products_id = $id_product";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					$product_arr_new=$row;
					$product_arr_new['products_id']=$id_product_new;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $product_arr_new);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
				// insert tx_multishop_products_attributes
				$str="SELECT * from tx_multishop_products_attributes where products_id = $id_product";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
					while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_arr_new=$row;
						$product_arr_new['products_id']=$id_product_new;
						unset($product_arr_new['products_attributes_id']); //primary key 
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes', $product_arr_new);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				// insert tx_multishop_specials
				$str="SELECT * from tx_multishop_specials where products_id = $id_product";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
					while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_arr_new=$row;
						$product_arr_new['products_id']=$id_product_new;
						unset($product_arr_new['specials_id']); //primary key 
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials', $product_arr_new);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				// insert tx_multishop_products_to_relative_products
				$str="SELECT * from tx_multishop_products_to_relative_products where products_id = $id_product or relative_product_id = $id_product";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
					while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_arr_new=$row;
						if($product_arr_new['products_id'] == $id_product) {
							$product_arr_new['products_id']=$id_product_new;
						} else {
							$product_arr_new['relative_product_id']=$id_product_new;
						}
						unset($product_arr_new['products_to_relative_product_id']); //primary key 
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $product_arr_new);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				// insert into tx_multishop_products_to_categories
				$insertArray=array(
					'products_id'=>$id_product_new,
					'categories_id'=>$id_category,
					'sort_order'=>time());
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if($res) {
					echo "<p>Product succesfully duplicated</p>";
					if($this->ms['MODULES']['FLAT_DATABASE']) {
						// if the flat database module is enabled we have to sync the changes to the flat table
						mslib_befe::convertProductToFlat($id_product_new);
					}
				} else {
					echo "<p>Product not duplicated</p>";
				}
			}
		}
	}
}
exit();
?>