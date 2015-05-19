<?php
/*
//QUICK LANGUAGE UID FIXER:

$str='
	tx_multishop_undo_products
	tx_multishop_categories
	tx_multishop_categories_description
	tx_multishop_cms
	tx_multishop_cms_description
	tx_multishop_configuration_values
	tx_multishop_manufacturers
	tx_multishop_manufacturers_cms
	tx_multishop_manufacturers_info
	tx_multishop_orders
	tx_multishop_orders_products
	tx_multishop_orders_products_attributes
	tx_multishop_orders_status_history
	tx_multishop_payment_methods
	tx_multishop_payment_shipping_mappings
	tx_multishop_products
	tx_multishop_products_attributes
	tx_multishop_products_attributes_download
	tx_multishop_products_attributes_extra
	tx_multishop_products_description
	tx_multishop_products_faq
	tx_multishop_products_options
	tx_multishop_products_options_values
	tx_multishop_products_options_values_extra
	tx_multishop_products_options_values_to_products_options
	tx_multishop_products_to_categories
	tx_multishop_products_to_extra_options
	tx_multishop_products_to_relative_products
	tx_multishop_product_wishlist
	tx_multishop_reviews
	tx_multishop_reviews_description
	tx_multishop_shipping_countries
	tx_multishop_countries_to_zones
	tx_multishop_shipping_methods
	tx_multishop_shipping_methods_costs
	tx_multishop_shipping_options
	tx_multishop_zones
	tx_multishop_specials
';
$tables=explode("\n",$str);
foreach ($tables as $table) {
	$table=trim($table);
	if ($table) {
		$query='update '.$table.' set language_id=0 where language_id=6';
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
//		echo $query.'<br>';
		$query='update '.$table.' set language_id=2 where language_id=8';
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
	}
}
//die();
*/
set_time_limit(86400);
ignore_user_abort(true);
switch ($_REQUEST['action']) {
	case 'configuration_actions':
		if ($_FILES['restore_configuration_file']['tmp_name']) {
			$string=mslib_fe::file_get_contents($_FILES['restore_configuration_file']['tmp_name']);
			$rows=unserialize($string);
			$target_pid=$_REQUEST['target_pid'];
			if (count($rows)>0 and $target_pid) {
				foreach ($rows as $row) {
					$array=array();
					foreach ($row as $key=>$val) {
						if ($key=='page_uid') {
							$array[$key]=$target_pid;
						} elseif ($key!='id') {
							$array[$key]=$val;
						}
					}
					$records[]=$array;
				}
				if (count($records)>0) {
					$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'page_uid='.$target_pid);
					foreach ($records as $record) {
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration_values', $record);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
					$content.='<h2>Configuration settings is imported</h2>';
				}
			}
		}
		if (strstr($this->post['configuration_copier_pid'], "_")) {
			$pids=explode("_", $_REQUEST['configuration_copier_pid']);
			if (count($pids)==2) {
				$source_pid=$pids[0];
				$target_pid=$pids[1];
				$records=array();
				$str="SELECT * from tx_multishop_configuration_values where page_uid='".$source_pid."'";
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
					$array=array();
					foreach ($row as $key=>$val) {
						if ($key=='page_uid') {
							$array[$key]=$target_pid;
						} elseif ($key!='id') {
							$array[$key]=$val;
						}
					}
					$records[]=$array;
				}
				if ($this->post['Download']) {
					ob_end_clean();
					header('Content-Disposition: attachment; filename="'.$source_pid.'_settings.t3xms"');
					echo serialize($records);
					exit();
				} else {
					if (count($records)>0) {
						$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'page_uid='.$target_pid);
						foreach ($records as $record) {
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration_values', $record);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
						$content.='<h2>Configuration settings is copied</h2>';
					}
				}
			}
		}
		break;
	case 'image_resizer':
		$this->ms['MODULES']=mslib_befe::loadConfiguration($_GET['page_uid']);
		$this->ms=mslib_befe::convertConfiguration($this->ms);
		$format=explode("x", $this->ms['MODULES']['CATEGORY_IMAGE_SIZE_NORMAL']);
		$this->ms['category_image_formats']['normal']['width']=$format[0];
		$this->ms['category_image_formats']['normal']['height']=$format[1];
		$format=explode("x", $this->ms['MODULES']['MANUFACTURER_IMAGE_SIZE_NORMAL']);
		$this->ms['manufacturer_image_formats']['normal']['width']=$format[0];
		$this->ms['manufacturer_image_formats']['normal']['height']=$format[1];
		$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_50']);
		$this->ms['product_image_formats']['50']['width']=$format[0];
		$this->ms['product_image_formats']['50']['height']=$format[1];
		$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_100']);
		$this->ms['product_image_formats']['100']['width']=$format[0];
		$this->ms['product_image_formats']['100']['height']=$format[1];
		$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_200']);
		$this->ms['product_image_formats']['200']['width']=$format[0];
		$this->ms['product_image_formats']['200']['height']=$format[1];
		$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_300']);
		$this->ms['product_image_formats']['300']['width']=$format[0];
		$this->ms['product_image_formats']['300']['height']=$format[1];
		$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_ENLARGED']);
		$this->ms['product_image_formats']['enlarged']['width']=$format[0];
		$this->ms['product_image_formats']['enlarged']['height']=$format[1];
		if (!$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']) {
			$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']=5;
		}
		$content.='<h2>Log</h2>';
		$data=array();
		$data['categories']=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('categories_id,categories_image', 'tx_multishop_categories', 'page_uid=\''.$_GET['page_uid'].'\'', '');
		$data['manufacturers']=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('manufacturers_id,manufacturers_image', 'tx_multishop_manufacturers');
		$fields=array();
		$fields[]='products_id';
		for ($i=0; $i<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $i++) {
			if (!$i) {
				$s='';
			} else {
				$s=$i;
			}
			$fields[]='products_image'.$s;
		}
		$data['products']=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows(implode(",", $fields), 'tx_multishop_products', 'page_uid=\''.$_GET['page_uid'].'\'', '');
		foreach($data as $type => $items) {
			$content.='<h2>'.$type.'</h2>';
			switch($type) {
				case 'categories':
					foreach ($items as $item) {
						$dbFilename=$item['categories_image'];
						$folder=mslib_befe::getImagePrefixFolder($dbFilename);
						$newFilename=mslib_befe::resizeCategoryImage($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.'/'.$dbFilename, $dbFilename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath('multishop'), 1);
						if ($newFilename) {
							$content.=$newFilename.'<BR>';
							if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG'] && $newFilename != $dbFilename) {
								// FILE IS ALSO CONVERTED. LETS UPDATE THE DATABASE
								$content.='<i>('.$dbFilename.' has been converted to: '.$newFilename.')</i><br/>';
								$updateArray=array();
								$updateArray['categories_image']=$newFilename;
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_categories', 'categories_id=\''.$item['categories_id'].'\'', $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
						}
						if ($this->ms['MODULES']['ADMIN_CROP_CATEGORIES_IMAGES']) {
							$crop_images_data=mslib_befe::getRecords($item['categories_id'], 'tx_multishop_categories_crop_image_coordinate', 'categories_id');
							if (is_array($crop_images_data) && count($crop_images_data)) {
								foreach ($crop_images_data as $crop_image_data) {
									$src_image_size=($crop_image_data['image_size']=='enlarged' ? 'normal' : $crop_image_data['image_size']);
									$src=$this->DOCUMENT_ROOT.mslib_befe::getImagePath($crop_image_data['image_filename'], 'categories', $src_image_size);
									$src_original=$this->DOCUMENT_ROOT.mslib_befe::getImagePath($crop_image_data['image_filename'], 'categories', 'original');
									// backup original
									copy($src, $src.'-ori-'.$image_size);
									mslib_befe::cropImage($src, $src_original, $crop_image_data['image_size'], $crop_image_data['coordinate_x'], $crop_image_data['coordinate_y'], $crop_image_data['coordinate_w'], $crop_image_data['coordinate_h'], 'categories');
								}
							}
						}
					}
					break;
				case 'manufacturers':
					foreach ($items as $item) {
						$dbFilename=$item['manufacturers_image'];
						$folder=mslib_befe::getImagePrefixFolder($dbFilename);
						$newFilename=mslib_befe::resizeManufacturerImage($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.'/'.$dbFilename, $dbFilename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath('multishop'), 1);
						if ($newFilename) {
							$content.=$newFilename.'<BR>';
							if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG'] && $newFilename != $dbFilename) {
								// FILE IS ALSO CONVERTED. LETS UPDATE THE DATABASE
								$content.='<i>('.$dbFilename.' has been converted to: '.$newFilename.')</i><br/>';
								$updateArray=array();
								$updateArray['manufacturers_image']=$newFilename;
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$item['manufacturers_id'].'\'', $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
						}
						if ($this->ms['MODULES']['ADMIN_CROP_MANUFACTURERS_IMAGES']) {
							$crop_images_data=mslib_befe::getRecords($item['manufacturers_id'], 'tx_multishop_manufacturers_crop_image_coordinate', 'manufacturers_id');
							if (is_array($crop_images_data) && count($crop_images_data)) {
								foreach ($crop_images_data as $crop_image_data) {
									$src_image_size=($crop_image_data['image_size']=='enlarged' ? 'normal' : $crop_image_data['image_size']);
									$src=$this->DOCUMENT_ROOT.mslib_befe::getImagePath($crop_image_data['image_filename'], 'manufacturers', $src_image_size);
									$src_original=$this->DOCUMENT_ROOT.mslib_befe::getImagePath($crop_image_data['image_filename'], 'manufacturers', 'original');
									// backup original
									copy($src, $src.'-ori-'.$image_size);
									mslib_befe::cropImage($src, $src_original, $crop_image_data['image_size'], $crop_image_data['coordinate_x'], $crop_image_data['coordinate_y'], $crop_image_data['coordinate_w'], $crop_image_data['coordinate_h'], 'manufacturers');
								}
							}
						}
					}
					break;
				case 'products':
					foreach ($items as $item) {
						$updateArray=array();
						for ($i=0; $i<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $i++) {
							if (!$i) {
								$s='';
							} else {
								$s=$i;
							}
							$col='products_image'.$s;

							$dbFilename=$item[$col];
							$folder=mslib_befe::getImagePrefixFolder($dbFilename);
							$newFilename=mslib_befe::resizeProductImage($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$dbFilename, $dbFilename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath('multishop'), 1);
							if ($newFilename) {
								$content.=$newFilename.'<BR>';
								if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG'] && $newFilename != $dbFilename) {
									// FILE IS ALSO CONVERTED. LETS UPDATE THE DATABASE
									$content.='<i>('.$dbFilename.' has been converted to: '.$newFilename.')</i><br/>';
									$updateArray[$col]=$newFilename;
								}
							}
						}
						if ($this->ms['MODULES']['ADMIN_CROP_PRODUCT_IMAGES']) {
							$crop_images_data=mslib_befe::getRecords($item['products_id'], 'tx_multishop_product_crop_image_coordinate', 'products_id');
							if (is_array($crop_images_data) && count($crop_images_data)) {
								foreach ($crop_images_data as $crop_image_data) {
									$src_image_size=($crop_image_data['image_size']=='enlarged' ? 'normal' : $crop_image_data['image_size']);
									$src=$this->DOCUMENT_ROOT.mslib_befe::getImagePath($crop_image_data['image_filename'], 'products', $src_image_size);
									$src_original=$this->DOCUMENT_ROOT.mslib_befe::getImagePath($crop_image_data['image_filename'], 'products', 'original');
									// backup original
									copy($src, $src.'-ori-'.$image_size);
									mslib_befe::cropImage($src, $src_original, $crop_image_data['image_size'], $crop_image_data['coordinate_x'], $crop_image_data['coordinate_y'], $crop_image_data['coordinate_w'], $crop_image_data['coordinate_h'], 'products');
								}
							}
						}
						if (count($updateArray)) {
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$item['products_id'].'\'', $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							if ($this->ms['MODULES']['FLAT_DATABASE']) {
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_flat', 'products_id=\''.$item['products_id'].'\'', $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
						}
					}
					break;
			}
		}
		break;
	case 'clearMultishopCache':
		if ($this->DOCUMENT_ROOT and !strstr($this->DOCUMENT_ROOT, '..')) {
			//$command="rm -rf ".$this->DOCUMENT_ROOT."uploads/tx_multishop/tmp/cache/*";
			//exec($command);
			mslib_befe::cacheLite('delete_all');
			$content.='<br /><p><strong>Multishop cache has been cleared.</strong></p>';
		} else {
			$content.='<br /><p><strong>Cache not cleared. Something is wrong with your configuration (DOCUMENT_ROOT is not set correctly).</strong></p>';
		}
		break;
	case 'full_erase':
		// clears any multishop data
		$string='
	tx_multishop_undo_products
	tx_multishop_categories
	tx_multishop_categories_description
	tx_multishop_cms
	tx_multishop_cms_description
	tx_multishop_configuration_values
	tx_multishop_manufacturers
	tx_multishop_manufacturers_cms
	tx_multishop_manufacturers_info
	tx_multishop_orders
	tx_multishop_orders_products
	tx_multishop_orders_products_attributes
	tx_multishop_orders_status_history
	tx_multishop_payment_methods
	tx_multishop_payment_shipping_mappings
	tx_multishop_products
	tx_multishop_products_attributes
	tx_multishop_products_attributes_download
	tx_multishop_products_attributes_extra
	tx_multishop_products_description
	tx_multishop_products_faq
	tx_multishop_products_options
	tx_multishop_products_options_values
	tx_multishop_products_options_values_extra
	tx_multishop_products_options_values_to_products_options
	tx_multishop_products_to_categories
	tx_multishop_products_to_extra_options
	tx_multishop_products_to_relative_products
	tx_multishop_product_wishlist
	tx_multishop_reviews
	tx_multishop_reviews_description
	tx_multishop_shipping_countries
	tx_multishop_countries_to_zones
	tx_multishop_shipping_methods
	tx_multishop_shipping_methods_costs
	tx_multishop_shipping_options
	tx_multishop_zones
	tx_multishop_specials
	';
		$array=explode("\n", $string);
		foreach ($array as $table) {
			$str='TRUNCATE '.trim($table);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		}
		$content.='<fieldset><h2>Log</h2><ul><li>All data has been erased.</li></ul></fieldset>';
		foreach ($this->ms['image_paths']['products'] as $key=>$path) {
			if ($path) {
				$return=$this->deltree($this->DOCUMENT_ROOT.$path);
			}
		}
		foreach ($this->ms['image_paths']['categories'] as $key=>$path) {
			if ($path) {
				$return=$this->deltree($this->DOCUMENT_ROOT.$path);
			}
		}
		foreach ($this->ms['image_paths']['manufacturers'] as $key=>$path) {
			if ($path) {
				$return=$this->deltree($this->DOCUMENT_ROOT.$path);
			}
		}
		// clears any multishop data eof
		$paths=array();
		$paths[]=PATH_site.'uploads/tx_multishop';
		$paths[]=PATH_site.'uploads/tx_multishop/tmp';
		$paths[]=PATH_site.'uploads/tx_multishop/images';
		$paths[]=PATH_site.'uploads/tx_multishop/images/cmt_images';
		$paths[]=PATH_site.'uploads/tx_multishop/images/cmsimages';
		$paths[]=PATH_site.'uploads/tx_multishop/images/cmsfiles';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products/50';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products/100';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products/200';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products/300';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products/original';
		$paths[]=PATH_site.'uploads/tx_multishop/images/products/normal';
		$paths[]=PATH_site.'uploads/tx_multishop/images/categories';
		$paths[]=PATH_site.'uploads/tx_multishop/images/categories/normal';
		$paths[]=PATH_site.'uploads/tx_multishop/images/categories/original';
		$paths[]=PATH_site.'uploads/tx_multishop/images/manufacturers';
		$paths[]=PATH_site.'uploads/tx_multishop/images/manufacturers/normal';
		$paths[]=PATH_site.'uploads/tx_multishop/images/manufacturers/original';
		foreach ($paths as $path) {
			if (!is_dir($path)) {
				t3lib_div::mkdir($path, 0766);
			}
		}
		break;
	case 'erase':
		// clears the current multishop
		$data=array();
		if (is_numeric($_GET['page_uid'])) {
			$content.='<fieldset><h2>Log</h2><ul>';
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_undo_products', 'page_uid='.$_GET['page_uid']);
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('categories_id', 'tx_multishop_categories', 'page_uid='.$_GET['page_uid'], '');
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res))!=false) {
				$data['categories_id'][]=$row[0];
			}
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('products_id', 'tx_multishop_products', 'page_uid='.$_GET['page_uid'], '');
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res))!=false) {
				$data['products_id'][]=$row[0];
			}
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('manufacturers_id', 'tx_multishop_manufacturers', 'page_uid='.$_GET['page_uid'], '');
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res))!=false) {
				$data['manufacturers_id'][]=$row[0];
			}
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_configuration_values', 'page_uid='.$_GET['page_uid']);
			$content.='<li>Settings removed</li>';
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_specials', 'page_uid='.$_GET['page_uid']);
			$content.='<li>Specials removed</li>';
			foreach ($data['categories_id'] as $id) {
				$tmp=mslib_befe::deleteCategory($id);
			}
			$content.='<li>Categories removed</li>';
			foreach ($data['products_id'] as $id) {
				$tmp=mslib_befe::deleteProduct($id);
			}
			$content.='<li>Products removed</li>';
			foreach ($data['manufacturers_id'] as $id) {
				$tmp=mslib_befe::deleteManufacturer($id);
			}
			$content.='<li>Manufacturers removed</li>';
			$content.='</ul></fieldset>';
		}
		// clears the current multishop data eof
		break;
	case 'restore':
		// restore database
		if ($this->post['action']=='restore' and is_numeric($this->post['page_uid']) and ($_FILES['restore_file']['tmp_name'] or $this->post['custom_file'])) {
			// unzip first
			if (!$_FILES['restore_file']['error'] or $this->post['custom_file']) {
				$backup_folder='restore_'.date("Y-m-d_G-i-s").'-'.md5(uniqid());
				$fullpath=PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder;
				t3lib_div::mkdir($fullpath);
				$content.='Restoring:<BR>'.$fullpath;
				if ($_FILES['restore_file']['tmp_name']) {
					move_uploaded_file($_FILES['restore_file']['tmp_name'], $fullpath.'/'.$_FILES['restore_file']['name']);
					$source_file=$fullpath.'/'.$_FILES['restore_file']['name'];
				} else {
					copy(PATH_site.'fileadmin/multishop_backup/'.$this->post['custom_file'], $fullpath.'/'.$this->post['custom_file']);
					$source_file=$fullpath.'/'.$this->post['custom_file'];
				}
				$files=$this->zipUnpack($source_file);

				$import_files=array();
				if (in_array('bvbshop.txt', $files)) {
					$mode='bvbshop';
				} else {
					$mode='multishop';
				}
				foreach ($files as $path) {
					$paths=explode('/', $path);
					if ($paths[1]=='data.sql') {
						$restore_files['data']=$fullpath.'/'.$path;
					}
					if (!$this->post['skip_images']) {
//					if (strstr($path,'chinese'))
//					{
						if ($paths[1]=='images' && $paths[2]=='products' && $paths[3]) {
							$restore_files['products'][$paths[3]]=$fullpath.'/'.$path;
						} elseif ($paths[1]=='images' && $paths[2]=='products_extra_images' && $paths[3]) {
							$restore_files['products'][$paths[3]]=$fullpath.'/'.$path;
						} elseif ($paths[1]=='images' && $paths[2]=='categories' && $paths[3]) {
							$restore_files['categories'][$paths[3]]=$fullpath.'/'.$path;
						} elseif ($paths[1]=='images' && $paths[2]=='manufacturers' && $paths[3]) {
							$restore_files['manufacturers'][$paths[3]]=$fullpath.'/'.$path;
						}
//					}
					}
				}
				$this->ms['MODULES']=mslib_befe::loadConfiguration($this->post['page_uid']);
				$this->ms=mslib_befe::convertConfiguration($this->ms);
				$format=explode("x", $this->ms['MODULES']['CATEGORY_IMAGE_SIZE_NORMAL']);
				$this->ms['category_image_formats']['normal']['width']=$format[0];
				$this->ms['category_image_formats']['normal']['height']=$format[1];
				$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_50']);
				$this->ms['product_image_formats'][50]['width']=$format[0];
				$this->ms['product_image_formats'][50]['height']=$format[1];
				$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_100']);
				$this->ms['product_image_formats'][100]['width']=$format[0];
				$this->ms['product_image_formats'][100]['height']=$format[1];
				$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_200']);
				$this->ms['product_image_formats'][200]['width']=$format[0];
				$this->ms['product_image_formats'][200]['height']=$format[1];
				$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_300']);
				$this->ms['product_image_formats'][300]['width']=$format[0];
				$this->ms['product_image_formats'][300]['height']=$format[1];
				$format=explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_ENLARGED']);
				$this->ms['product_image_formats']['enlarged']['width']=$format[0];
				$this->ms['product_image_formats']['enlarged']['height']=$format[1];
				if (count($restore_files['products'])>0) {
					foreach ($restore_files['products'] as $filename=>$path) {
						// backup original
						$folder=mslib_befe::getImagePrefixFolder($filename);
						if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
							t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
						}
						$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$filename;
						if (copy($path, $target)) {
							if ($this->post['resize_images']) {
								$tmp=mslib_befe::resizeProductImage($target, $filename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath('multishop'));
							}
						}
					}
				}
				if (count($restore_files['categories'])>0) {
					foreach ($restore_files['categories'] as $filename=>$path) {
						// backup original
						$folder=mslib_befe::getImagePrefixFolder($filename);
						if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder)) {
							t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder);
						}
						$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.'/'.$filename;
						if (copy($path, $target)) {
							if ($this->post['resize_images']) {
								$tmp=mslib_befe::resizeCategoryImage($target, $filename, $this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath('multishop'));
							}
						}
					}
				}
			}
			$GLOBALS['TYPO3_DB']->connectDB();
			// unzip first eof
			$content.='<fieldset><h2>Log</h2>';
			if ($restore_files['data']) {
				$database=unserialize(mslib_fe::file_get_contents($restore_files['data']));
				if ($mode=='bvbshop') {
					if (is_array($database['orders_status']) and count($database['orders_status'])) {
						$database['orders_status_description']=$database['orders_status'];
					}
					if (is_array($database['customers']) and count($database['customers'])) {
						$tx_multishop_customer_ids=array();
						// lets import the old customers
						foreach ($database['orders_status_history'] as &$row) {
							$row['crdate']=strtotime($row['date_added']);
						}
						foreach ($database['customers'] as $row) {
							$user=mslib_fe::getUser($row['customers_email_address'], 'email');
							if ($user['uid']) {
								$tx_multishop_customer_ids[$row['customers_id']]=$user['uid'];
							} else {
								// add user
								$insertArray=array();
								$insertArray['username']=$row['customers_email_address'];
								$insertArray['email']=$row['customers_email_address'];
								$insertArray['password']=$row['customers_password'];
								$insertArray['name']=$row['customers_firstname'].' '.$row['customers_lastname'];
								$insertArray['first_name']=$row['customers_firstname'];
								$insertArray['middle_name']=$row['customers_middlename'];
								$insertArray['last_name']=$row['customers_lastname'];
								if ($row['customers_dob'] and $row['customers_dob']!='0000-00-00 00:00:00') {
									$insertArray['date_of_birth']=strtotime($row['customers_dob']);
								}
								$insertArray['country']=$row['countries_name'];
								$insertArray['telephone']=$row['customers_telephone'];
								$insertArray['fax']=$row['customers_fax'];
								$insertArray['tx_multishop_newsletter']=$row['customers_newsletter'];
								$insertArray['tx_multishop_discount']=$row['customers_korting'];
								$insertArray['mobile']=$row['customers_mobile'];
								$insertArray['gender']=$row['entry_gender'];
								$insertArray['company']=$row['entry_company'];
								if ($row['entry_street_address']) {
									$street_address='';
									$house_number='';
									$addon_number='';
									$street_data=explode(' ', $row['entry_street_address']);
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
									$insertArray['address']=$row['entry_street_address'];
									$insertArray['street_name']=$street_address;
									$insertArray['address_number']=$house_number;
									$insertArray['address_ext']=$addon_number;
								}
								$insertArray['zip']=$row['entry_postcode'];
								$insertArray['city']=$row['entry_city'];
								$insertArray['tstamp']=time();
								$insertArray['tx_multishop_code']=md5(uniqid('', true));
								$insertArray['page_uid']=$this->post['page_uid'];
								$insertArray['usergroup']='';
								$insertArray['pid']='';
								$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertArray);
								$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
								$tx_multishop_customer_ids[$row['customers_id']]=$new_id;
							}
						}
					}
					// we go to convert the database first to the database model of Multishop.
					// here we tell which bvbshop table name has to map to the right multishop table
					$db_ms2bvb=array();
					$db_ms2bvb['categories']='tx_multishop_categories';
					$db_ms2bvb['categories_description']='tx_multishop_categories_description';
//				$db_ms2bvb['cms']									='tx_multishop_cms';
//				$db_ms2bvb['cms_description']						='tx_multishop_cms_description';
					$db_ms2bvb['manufacturers']='tx_multishop_manufacturers';
					$db_ms2bvb['manufacturers_cms']='tx_multishop_manufacturers_cms';
					$db_ms2bvb['manufacturers_info']='tx_multishop_manufacturers_info';
					$db_ms2bvb['orders']='tx_multishop_orders';
					$db_ms2bvb['orders_products']='tx_multishop_orders_products';
					$db_ms2bvb['orders_products_attributes']='tx_multishop_orders_products_attributes';
					$db_ms2bvb['orders_status']='tx_multishop_orders_status';
					$db_ms2bvb['orders_status_description']='tx_multishop_orders_status_description';
					$db_ms2bvb['orders_status_history']='tx_multishop_orders_status_history';
					$db_ms2bvb['products']='tx_multishop_products';
					$db_ms2bvb['products_description']='tx_multishop_products_description';
					$db_ms2bvb['products_to_categories']='tx_multishop_products_to_categories';
					$db_ms2bvb['products_attributes']='tx_multishop_products_attributes';
					$db_ms2bvb['products_attributes_download']='tx_multishop_products_attributes_download';
					$db_ms2bvb['products_attributes_extra']='tx_multishop_products_attributes_extra';
					$db_ms2bvb['products_faq']='tx_multishop_products_faq';
					$db_ms2bvb['products_options']='tx_multishop_products_options';
					$db_ms2bvb['products_options_values']='tx_multishop_products_options_values';
					$db_ms2bvb['products_options_values_extra']='tx_multishop_products_options_values_extra';
					$db_ms2bvb['products_options_values_to_products_options']='tx_multishop_products_options_values_to_products_options';
					$db_ms2bvb['products_to_extra_options']='tx_multishop_products_to_extra_options';
					$db_ms2bvb['products_to_relative_products']='tx_multishop_products_to_relative_products';
					$db_ms2bvb['product_wishlist']='tx_multishop_product_wishlist';
					$db_ms2bvb['reviews']='tx_multishop_reviews';
					$db_ms2bvb['reviews_description']='tx_multishop_reviews_description';
					$db_ms2bvb['specials']='tx_multishop_specials';
					// here we will flip the keys with the values so we can easily compare 2 ways
					$db_bvb2ms=array_flip($db_ms2bvb);
					// first we load the column names of the multishop tables so we can pre-setup every new record with those columns
					$final_db=array();
					foreach ($db_ms2bvb as $bvb_table=>$multishop_table) {
						$sql="describe ".$multishop_table;
						$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
						while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
							if ($rs['Field']) {
								$final_db[$multishop_table][$rs['Field']]='';
							}
						}
					}
					// now that we know the needed columns we can try to map them automatically
					$final_database=array();
					if (is_array($database['orders']) and count($database['orders'])) {
						if (is_array($database['orders_status']) and count($database['orders_status'])) {
							$tables=array();
							$tables[]='orders_status';
							$tables[]='orders_status_description';
							foreach ($tables as $table) {
								foreach ($database[$table] as $key=>$record) {
									if (!$language_key and $database[$table][$key]['language_id']==6) {
										$language_key=6;
									} elseif (!$language_key and $database[$table][$key]['language_id']==6) {
										$language_key=6;
									}
									if ($database[$table][$key]['language_id']==$language_key) {
										if ($database[$table][$key]['orders_status_name']) {
											if (!mb_detect_encoding($database[$table][$key]['orders_status_name'], 'UTF-8', true)) {
												$database[$table][$key]['orders_status_name']=mslib_befe::convToUtf8($database[$table][$key]['orders_status_name']);
											}
										}
										$database[$table][$key]['name']=$database[$table][$key]['orders_status_name'];
										switch ($table) {
											case 'orders_status':
												$database[$table][$key]['crdate']=0;
												$database[$table][$key]['id']=$database[$table][$key]['orders_status_id'];
												$database[$table][$key]['page_uid']=$this->post['page_uid'];
												break;
											case 'orders_status_description':
												$database[$table][$key]['orders_status_id']=$database[$table][$key]['orders_status_id'];
												$database[$table][$key]['language_id']=0;
												break;
										}
//									unset($database[$table][$key]['orders_status_name']);
//									unset($database[$table][$key]['language_id']);
									} else {
										unset($database[$table][$key]);
									}
								}
							}
						}
						$ordersProductsTabBufferArray=array();
						if (count($database['orders_products'])) {
							foreach ($database['orders_products'] as $key=>$record) {
								$ordersProductsTabBufferArray[$database['orders_products'][$key]['orders_products_id']]=$database['orders_products'][$key]['products_tax'];
								$database['orders_products'][$key]['qty']=$database['orders_products'][$key]['products_quantity'];
								// calculate price excluding vat
								$database['orders_products'][$key]['products_price']=($database['orders_products'][$key]['products_price']/(100+$database['orders_products'][$key]['products_tax'])*100);
								$database['orders_products'][$key]['final_price']=($database['orders_products'][$key]['final_price']/(100+$database['orders_products'][$key]['products_tax'])*100);
								unset($database['orders_products'][$key]['products_quantity']);
								// tmp bugfix. the final_price in oscommerce also contains the attribute price. since Multishop saves that independent lets overwrite the final_price with the products_price
								$database['orders_products'][$key]['final_price']=$database['orders_products'][$key]['products_price'];
							}
						}
						if (count($database['orders_products_attributes'])) {
							foreach ($database['orders_products_attributes'] as $key=>$record) {
								// calculate price excluding vat
								$database['orders_products_attributes'][$key]['options_values_price']=($database['orders_products_attributes'][$key]['options_values_price']/(100+$ordersProductsTabBufferArray[$record['orders_products_id']])*100);
							}
						}
						/*
						// substract attribute price from orders_products
						if (count($database['orders_products'])) {
							foreach ($database['orders_products'] as $key=>$record) {
								foreach ($database['orders_products_attributes'] as $key2=>$record2) {
									if ($database['orders_products'][$key]['orders_products_id']==$record2['orders_products_id']) {
										$database['orders_products'][$key]['products_price']=$database['orders_products'][$key]['products_price']-$record2['options_values_price'];
										$database['orders_products'][$key]['final_price']=$database['orders_products'][$key]['final_price']-$record2['options_values_price'];
									}
								}
							}
						}
						*/
						foreach ($database['orders'] as $key=>$record) {
							$user=array();
							$customer_id='';
							if (count($tx_multishop_customer_ids)) {
								$customer_id=$tx_multishop_customer_ids[$record['customers_id']];
								$user=mslib_fe::getUser($customer_id, 'uid');
							} else {
								$user=mslib_fe::getUser($record['customers_email_address'], 'email');
							}
							if ($user['uid']) {
								$row=array();
								$row['page_uid']=$this->post['page_uid'];
								$row['customer_id']=$user['uid'];
								$row['billing_name']=$user['name'];
								$row['billing_first_name']=$user['first_name'];
								$row['billing_last_name']=$user['last_name'];
								$row['billing_company']=$user['company'];
								$row['billing_address']=$user['address'];
								$row['billing_address_number']=$user['address_number'];
								$row['billing_address_ext']=$user['address_ext'];
								$row['billing_city']=$user['city'];
								$row['billing_zip']=$user['zip'];
//							$row['billing_state']				= $record['customers_state'];
								$row['billing_country']=$user['country'];
								$row['billing_telephone']=$user['telephone'];
								$row['billing_mobile']=$user['mobile'];
								$row['billing_email']=$user['email'];
								$row['delivery_telephone']=$user['telephone'];
								$row['delivery_mobile']=$user['mobile'];
								$row['delivery_email']=$user['email'];
								// fixer
								$row['orders_id']=$record['orders_id'];
								// fixer eof
								$row['delivery_name']=$record['delivery_name'];
//							$row['delivery_first_name']			= $record['delivery_name'];
								$row['delivery_last_name']=$record['delivery_name'];
								$row['delivery_company']=$record['delivery_company'];
								$row['delivery_address']=$record['delivery_street_address'];
								// address
								$street_address='';
								$house_number='';
								$addon_number='';
								$street_data=explode(' ', $row['delivery_address']);
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
								$row['delivery_address']=$street_address;
								$row['delivery_address_number']=$house_number;
								$row['delivery_address_ext']=$addon_number;
								// address eof
								$row['delivery_city']=$record['delivery_city'];
								$row['delivery_zip']=$record['delivery_postcode'];
								$row['delivery_country']=$record['delivery_country'];
								$this->sys_language_uid=0;
								if ($record['payment_method']) {
									$payment_method=mslib_fe::getPaymentMethod($record['payment_method'], 'd.name');
									if (!$payment_method['code']) {
										// insert
										$insertArray=array();
										$insertArray['code']=mslib_fe::rewritenamein($record['payment_method']);
										$insertArray['handling_costs']=0;
										$insertArray['sort_order']=0;
										$insertArray['date']=time();
										$insertArray['status']=1;
										$insertArray['provider']='cod';
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods', $insertArray);
										$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
										if ($id) {
											$updateArray=array();
											$updateArray['name']=$record['payment_method'];
											$updateArray['description']='';
											$updateArray['id']=$id;
											$updateArray['language_id']=0;
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_payment_methods_description', $updateArray);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
											$payment_method=mslib_fe::getPaymentMethod($record['payment_method'], 'd.name');
										}
									}
									$row['payment_method']=$payment_method['code'];
									$row['payment_method_label']=$payment_method['name'];
								}
								if ($record['shipping_method']) {
									$shipping_method=mslib_fe::getShippingMethod($record['shipping_method'], 'd.name');
									if (!$shipping_method['code']) {
										// insert
										$insertArray=array();
										$insertArray['code']=mslib_fe::rewritenamein($record['shipping_method']);
										$insertArray['handling_costs']=0;
										$insertArray['sort_order']=0;
										$insertArray['date']=time();
										$insertArray['status']=1;
										$insertArray['provider']='cod';
										$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods', $insertArray);
										$res=$GLOBALS['TYPO3_DB']->sql_query($query);
										$id=$GLOBALS['TYPO3_DB']->sql_insert_id();
										if ($id) {
											$updateArray=array();
											$updateArray['name']=$record['shipping_method'];
											$updateArray['description']='';
											$updateArray['id']=$id;
											$updateArray['language_id']=0;
											$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_shipping_methods_description', $updateArray);
											$res=$GLOBALS['TYPO3_DB']->sql_query($query);
											$shipping_method=mslib_fe::getShippingMethod($record['shipping_method'], 'd.name');
										}
									}
									$row['shipping_method']=$shipping_method['code'];
									$row['shipping_method_label']=$shipping_method['name'];
								}
								// $row['billing_name']=$record['last_modified'];
								$row['crdate']=strtotime($record['date_purchased']);
								if ($record['shipping_cost']) {
									// calculate price excluding vat
									$record['shipping_cost']=($record['shipping_cost']/(100+$this->ms['MODULES']['INCLUDE_VAT_OVER_METHOD_COSTS'])*100);
									$row['shipping_method_costs']=$record['shipping_cost'];
								}
								if ($record['payment_cost']) {
									// calculate price excluding vat
									$record['payment_cost']=($record['payment_cost']/(100+$this->ms['MODULES']['INCLUDE_VAT_OVER_METHOD_COSTS'])*100);
									$row['payment_method_costs']=$record['payment_cost'];
								}
								$row['status']=$record['orders_status'];
								$row['customer_comments']=$record['comments'];
								//								$row['billing_name']=$record['currency'];
								//								$row['billing_name']=$record['currency_value'];
								//								$row['billing_name']=$record['HTTP_REFERER'];
								//								$row['billing_name']=$record['order_pdf_invoice'];
								$row['order_memo']=$record['order_memo'];
								$row['by_phone']=$record['by_phone'];
								//								$row['billing_name']=$record['by_phone_operator'];
								//								$row['billing_name']=$record['date_delivered'];
								//								$row['billing_name']=$record['coupon_discount'];
								//								$row['billing_name']=$record['coupon_discount_code'];
								//								$row['billing_name']=$record['billed'];
								$row['deleted']=0;
								$row['hash']=md5(uniqid('', true));
								$database['orders'][$key]=$row;
							}
						}
					}
					foreach ($database as $table=>$records) {
						if (in_array($table, $db_bvb2ms)) {
							$record_count=0;
							foreach ($records as $record) {
								$final_database[$db_ms2bvb[$table]][$record_count]=$final_db[$db_ms2bvb[$table]];
								$colcount=0;
								foreach ($record as $col_key=>$col_value) {
									// disable this when u want to import multilanguage:
									//if ($col_key=='language_id') $col_value=0;
									if (isset($final_database[$db_ms2bvb[$table]][$record_count][$col_key])) {
										// we already mapped the same col name so lets add the value to it
										$final_database[$db_ms2bvb[$table]][$record_count][$col_key]=$col_value;
									}
									$colcount++;
								}
								$record_count++;
							}
						}
					}
					unset($database);
					$database=$final_database;
				}
//			print_r($database['tx_multishop_orders_status_description']);
//			die();
				foreach ($database as $key=>$records) {
					// first we load the column names of the multishop tables so we can pre-setup every new record with those columns
					$final_db=array();
					$sql="describe ".$key;
					$qry=$GLOBALS['TYPO3_DB']->sql_query($sql);
					while (($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
						if ($rs['Field']) {
							$final_db[$key][$rs['Field']]='';
						}
					}

					$insert_records=0;
					$content.='<strong>'.$key.'</strong>';
					// FULL IMPORT
					if ($this->post['full_restore']) {
						// CLEAR TABLES
						$str="TRUNCATE TABLE ".$key;
						$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
						if ($qry) {
							$content.='<BR>Table cleared and is now empty.';
						}
						// CLEAR TABLES EOF
						foreach ($records as $record) {
							if (array_key_exists('page_uid', $record)) {
								$record['page_uid']=$this->post['page_uid'];
							}
							switch ($key) {
								case 'tx_multishop_products':
									if ($record['products_date_added']>0) {
										$record['products_date_added']=strtotime($record['products_date_added']);
									}
									if ($record['products_last_modified']>0) {
										$record['products_last_modified']=strtotime($record['products_last_modified']);
									}
									if ($record['products_date_available']>0) {
										$record['products_date_available']=strtotime($record['products_date_available']);
									}
									break;
							}
//						if (array_key_exists('customer_id',$record)) 	$record['customer_id']=$tx_multishop_customer_ids[$record['customer_id']];
							if ($key=='tx_multishop_orders') {
//							echo mslib_befe::print_r($record);
//							die();
								/*
									$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
									$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($key,$record);
									echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
									die();
								*/
							}
							// older multishop versions has sometimes columnes that are not existing in the newer version. lets filter them out
							foreach ($record as $col_key=>$col_val) {
								if (!isset($final_db[$key][$col_key])) {
									unset($record[$col_key]);
								}
							}
							// older multishop versions has sometimes columnes that are not existing in the newer version. lets filter them out EOF
							$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=true;
							$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);

							if ($GLOBALS['TYPO3_DB']->sql_insert_id() or $GLOBALS['TYPO3_DB']->sql_affected_rows()) {
								$insert_records++;
							} else {
								t3lib_utility_Debug::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, ' failed');
							}
							$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=false;
						}
					} // FULL IMPORT EOF
					else {
						// ADD IMPORT, REMAINS THE CURRENT DATA AND ADDS THE BACKUP DATA
						switch ($key) {
							case 'tx_multishop_categories':
								$tx_multishop_categories_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['categories_id'];
									$record['categories_id']='';
									$record['status']=1;
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_categories_ids[$old_id]=$new_id;
										$insert_records++;
									} else {
										echo $GLOBALS['TYPO3_DB']->sql_error();
										die();
									}
								}
								break;
							case 'tx_multishop_categories_description':
								foreach ($records as $record) {
									$record['categories_id']=$tx_multishop_categories_ids[$record['categories_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_cms':
								$tx_multishop_cms_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['id'];
									$record['id']='';
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_cms_ids[$old_id]=$new_id;
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_cms_description':
								foreach ($records as $record) {
									$record['id']=$tx_multishop_cms_ids[$record['id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_configuration':
							case 'tx_multishop_configuration_group':
								// the global configuration table and group will be skipped
								break;
							case 'tx_multishop_configuration_values':
								foreach ($records as $record) {
									$record['id']='';
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_manufacturers':
								$tx_multishop_manufacturers_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['manufacturers_id'];
									$record['manufacturers_id']='';
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_manufacturers_ids[$old_id]=$new_id;
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_manufacturers_description':
								foreach ($records as $record) {
									$record['manufacturers_id']=$tx_multishop_manufacturers_ids[$record['manufacturers_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_manufacturers_info':
								foreach ($records as $record) {
									$record['manufacturers_id']=$tx_multishop_manufacturers_ids[$record['manufacturers_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_orders':
								$tx_multishop_orders_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['orders_id'];
									$record['orders_id']='';
									$record['language_id']='0';
									if ($tx_multishop_customer_ids[$record['customer_id']]) {
										$record['customer_id']=$tx_multishop_customer_ids[$record['customer_id']];
									}
									$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=true;
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_orders_ids[$old_id]=$new_id;
										$insert_records++;
									} else {
										t3lib_utility_Debug::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'last built query');
									}
									$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=false;
								}
								break;
							case 'tx_multishop_orders_products':
								foreach ($records as $record) {
									$record['orders_id']=$tx_multishop_orders_ids[$record['orders_id']];
									$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=true;
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_orders_products_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_orders_products_id) {
										$insert_records++;
									} else {
										t3lib_utility_Debug::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'last built query');
									}
									$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=false;
								}
								break;
							case 'tx_multishop_orders_status_history':
								foreach ($records as $record) {
									$record['orders_id']=$tx_multishop_orders_ids[$record['orders_id']];
									$record['crdate']=strtotime($record['orders_id']);
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_payment_methods':
							case 'tx_multishop_payment_shipping_mappings':
								// skip at this moment
								break;
							case 'tx_multishop_products':
								$tx_multishop_products_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['products_id'];
									$record['products_id']='';
									$record['products_status']=1;
									$res =$GLOBALS['TYPO3_DB']->INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_products_ids[$old_id]=$new_id;
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_products_description':
							case 'tx_multishop_products_attributes':
							case 'tx_multishop_products_attributes_extra':
							case 'tx_multishop_products_faq':
								foreach ($records as $record) {
									$record['products_id']=$tx_multishop_products_ids[$record['products_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_products_attributes_download':
								// skip
								break;
							case 'tx_multishop_products_options':
								$tx_multishop_products_options_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['products_options_id'];
									$record['products_options_id']='';
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_products_options_ids[$old_id]=$new_id;
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_products_options_values':
								$tx_multishop_products_options_values_ids=array();
								foreach ($records as $record) {
									$record['page_uid']=$this->post['page_uid'];
									$old_id=$record['products_options_values_id'];
									$record['products_options_values_id']='';
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$tx_multishop_products_options_values_ids[$old_id]=$new_id;
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_products_options_values_extra':
								// skip
								break;
							case 'tx_multishop_products_options_values_to_products_options':
								foreach ($records as $record) {
									$record['products_options_values_to_products_options_id']='';
									$record['products_options_id']=$tx_multishop_products_options_ids[$record['products_options_id']];
									$record['products_options_values_id']=$tx_multishop_products_options_values_ids[$record['products_options_values_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$new_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
									if ($new_id) {
										$insert_records++;
									}
								}
								break;
							case 'tx_multishop_products_to_categories':
								foreach ($records as $record) {
									$record['products_id']=$tx_multishop_products_ids[$record['products_id']];
									$record['categories_id']=$tx_multishop_categories_ids[$record['categories_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_products_to_relative_products':
								foreach ($records as $record) {
									$record['products_to_relative_product_id']='';
									$record['products_id']=$tx_multishop_products_ids[$record['products_id']];
									$record['relative_product_id']=$tx_multishop_products_ids[$record['relative_product_id']];
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
							case 'tx_multishop_product_wishlist':
							case 'tx_multishop_reviews':
							case 'tx_multishop_reviews_description':
							case 'tx_multishop_shipping_countries':
							case 'tx_multishop_countries_to_zones':
							case 'tx_multishop_shipping_methods':
							case 'tx_multishop_shipping_methods_costs':
							case 'tx_multishop_shipping_options':
							case 'tx_multishop_zones':
							case 'tx_multishop_tax_rates':
								// skip at this moment
								break;
							case 'tx_multishop_specials':
								foreach ($records as $record) {
									$record['specials_id']='';
									$record['products_id']=$tx_multishop_products_ids[$record['products_id']];
									$record['page_uid']=$this->post['page_uid'];
									if ($record['specials_date_added']>0) {
										$record['specials_date_added']=strtotime($record['specials_date_added']);
									}
									if ($record['specials_last_modified']>0) {
										$record['specials_last_modified']=strtotime($record['specials_last_modified']);
									}
									if ($record['start_date']>0) {
										$record['start_date']=strtotime($record['start_date']);
									}
									if ($record['expires_date']>0) {
										$record['expires_date']=strtotime($record['expires_date']);
									}
									if ($record['date_status_change']>0) {
										$record['date_status_change']=strtotime($record['date_status_change']);
									}
									$res =$GLOBALS['TYPO3_DB']->exec_INSERTquery($key, $record);
									$insert_records++;
								}
								break;
						}
						// ADD IMPORT, REMAINS THE CURRENT DATA AND ADDS THE BACKUP DATA EOF
					}
					$content.='<BR><strong>'.$insert_records.'</strong> '.(($insert_records==1) ? 'record' : 'records').' added.<hr><BR>';
				}
			} else {
				$content.='File is not valid. Restore didn\'t succeed.';
			}
			$content.='</fieldset>';
			// now remove the backup folder
			if ($fullpath) {
				$tmp=$this->deltree($fullpath);
			}
		}
		// restore database	eof
		break;
	case 'backup':
		$this->ms['MODULES']=mslib_befe::loadConfiguration($_GET['page_uid']);
		$this->ms=mslib_befe::convertConfiguration($this->ms);
		// backup database
		if ($_GET['action']=='backup' and is_numeric($_GET['page_uid'])) {
			$tables=array();
			if (in_array('customers', $_GET['tx_multishop_pi']['selected_tables']) or !count($_GET['tx_multishop_pi']['selected_tables'])) {
				$tables[]='tt_address';
				$tables[]='fe_users';
			}
			if (in_array('cms', $_GET['tx_multishop_pi']['selected_tables']) or !count($_GET['tx_multishop_pi']['selected_tables'])) {
				$tables[]='tx_multishop_cms';
				$tables[]='tx_multishop_cms_description';
			}
			if (in_array('configuration', $_GET['tx_multishop_pi']['selected_tables']) or !count($_GET['tx_multishop_pi']['selected_tables'])) {
				$tables[]='tx_multishop_configuration';
				$tables[]='tx_multishop_configuration_values';
			}
			if (in_array('catalog', $_GET['tx_multishop_pi']['selected_tables']) or !count($_GET['tx_multishop_pi']['selected_tables'])) {
				$tables[]='tx_multishop_categories';
				$tables[]='tx_multishop_categories_description';
				// manufacturers
				$tables[]='tx_multishop_manufacturers';
				$tables[]='tx_multishop_manufacturers_cms';
				$tables[]='tx_multishop_manufacturers_info';
				// products
				$tables[]='tx_multishop_modules';
				$tables[]='tx_multishop_product_wishlist';
				$tables[]='tx_multishop_products';
				$tables[]='tx_multishop_products_attributes';
				$tables[]='tx_multishop_products_attributes_download';
				$tables[]='tx_multishop_products_attributes_extra';
				$tables[]='tx_multishop_products_description';
				$tables[]='tx_multishop_products_faq';
				$tables[]='tx_multishop_products_options';
				$tables[]='tx_multishop_products_options_values';
				$tables[]='tx_multishop_products_options_values_extra';
				$tables[]='tx_multishop_products_options_values_to_products_options';
				$tables[]='tx_multishop_products_to_categories';
				$tables[]='tx_multishop_products_to_extra_options';
				$tables[]='tx_multishop_products_to_relative_products';
				$tables[]='tx_multishop_reviews';
				$tables[]='tx_multishop_reviews_description';
				$tables[]='tx_multishop_specials';
				$tables[]='tx_multishop_tax_rates';
			}
			if (in_array('orders', $_GET['tx_multishop_pi']['selected_tables']) or !count($_GET['tx_multishop_pi']['selected_tables'])) {
				$tables[]='tx_multishop_orders';
				$tables[]='tx_multishop_orders_products';
				$tables[]='tx_multishop_orders_products_attributes';
				$tables[]='tx_multishop_orders_status';
				$tables[]='tx_multishop_orders_status_history';
			}
			if (in_array('methods', $_GET['tx_multishop_pi']['selected_tables'])) {
				$tables[]='tx_multishop_payment_methods';
				$tables[]='tx_multishop_payment_methods_description';
				$tables[]='tx_multishop_payment_shipping_mappings';
				$tables[]='tx_multishop_shipping_countries';
				$tables[]='tx_multishop_countries_to_zones';
				$tables[]='tx_multishop_shipping_methods';
				$tables[]='tx_multishop_shipping_methods_description';
				$tables[]='tx_multishop_shipping_methods_costs';
				//tx_multishop_payment_transactions
				$tables[]='tx_multishop_shipping_options';
				$tables[]='tx_multishop_zones';
			}
			$data=array();
			foreach ($tables as $table) {
				$data[$table]=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, '', '');
			}
			$output=serialize($data);
			$backup_folder=date("Y-m-d_G-i-s").'-'.md5(uniqid());
			t3lib_div::mkdir(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder);
			t3lib_div::mkdir(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/images');
			t3lib_div::mkdir(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/images/categories');
			t3lib_div::mkdir(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/images/products');
			if (in_array('images', $_GET['tx_multishop_pi']['selected_tables']) or !count($_GET['tx_multishop_pi']['selected_tables'])) {
				// copy the category images to the backup folder
				foreach ($data['tx_multishop_categories'] as $record) {
					if ($record['categories_image']) {
						$folder=mslib_befe::getImagePrefixFolder($record['categories_image']);
						$source_file=$this->DOCUMENT_ROOT.$this->ms['image_paths']['categories']['original'].'/'.$folder.'/'.$record['categories_image'];
						if (file_exists($source_file)) {
							copy($source_file, PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/images/categories/'.$record['categories_image']);
						}
					}
				}
				// copy the category images to the backup folder eof
				// copy the products images to the backup folder
				foreach ($data['tx_multishop_products'] as $record) {
					for ($i=0; $i<5; $i++) {
						if ($i==0) {
							$cur='';
						} else {
							$cur=$i;
						}
						if ($record['products_image'.$cur]) {
							$folder=mslib_befe::getImagePrefixFolder($record['products_image'.$cur]);
							$source_file=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.'/'.$record['products_image'.$cur];
							if (file_exists($source_file)) {
								copy($source_file, PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/images/products/'.$record['products_image'.$cur]);
							}
						}
					}
				}
				// copy the products images to the backup folder eof
			}
			if (!file_put_contents(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/data.sql', $output)) {
				die(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/data.sql is not writable.');
			} else {
				$backup_file=date("Y-m-d_G-i-s");
				if ($this->zipPack(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/', PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/'.$backup_file.'.zip')) {
					$output=mslib_fe::file_get_contents(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder.'/'.$backup_file.'.zip');
					$content=ob_get_contents();
					// now remove the backup folder
					if ($backup_folder) {
						$tmp=$this->deltree(PATH_site.'uploads/tx_multishop/tmp/'.$backup_folder);
					}
					ob_end_clean();
					header('Content-Disposition: attachment; filename="'.$backup_file.'.t3xms"');
					echo $output;
					exit();
				} else {
					die('Backupping failed (can\'t create the compressed backup file).');
				}
			}
		}
		// backup database eof
		break;
}
$shops=array();
$orphan=array();
$str="select count(1) as total, page_uid from tx_multishop_configuration_values group by page_uid";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$shops=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$shops[$row['page_uid']]=$row['total'];
	/*
		$multishop_content_objects = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tt_content',
			'pid=\''.$row['page_uid'].'\' and list_type = \'multishop_pi1\' and pi_flexform like \'%coreshop%\'',
			'pid'
		);
		if (count($multishop_content_objects) == 0)
		{
			$pageinfo = t3lib_BEfunc::readPageAccess($row['page_uid'],'');
			if (is_numeric($pageinfo['uid']))
			{
				echo 'ba';
				print_r($pageinfo);
				die();
			}
			else
			{
				$orphan[$row['page_uid']]=1;
			}
		}
	*/
}
// now grab the active shops
/*
// old string search approach
$multishop_content_objects = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
	'*',
	'tt_content',
	'list_type = \'multishop_pi1\' and pi_flexform like \'%<value index="vDEF">coreshop</value>%\' and pi_flexform like \'%<field index="page_uid">
                    <value index="vDEF"></value>
                </field>%\'',
	''
);
*/
// mysql 5 supports searching inside xml string
//$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
/*
$multishop_content_objects = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
	'*',
	'tt_content',
	'list_type = \'multishop_pi1\' and extractvalue(pi_flexform, \'/T3FlexForms/data/sheet[@index="sDEFAULT"]/language[@index="lDEF"]/field[@index="method"]/value[@index="vDEF"]\')=\'coreshop\' and extractvalue(pi_flexform, \'/T3FlexForms/data/sheet[@index="sDEFAULT"]/language[@index="lDEF"]/field[@index="page_uid"]/value[@index="vDEF"]\')=\'\'',
	''
);
*/
$multishop_content_objects=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', 'deleted=0 and hidden=0 and module = \'mscore\'', '');
//$multishop_content_objects=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', 'deleted=0 and hidden=0 and sys_language_uid=0 and list_type = \'multishop_pi1\' and pi_flexform like \'%<value index="vDEF">coreshop</value>%\'', '');
if (count($multishop_content_objects)>0) {
	foreach ($multishop_content_objects as $content_object) {
		$pageinfo=t3lib_BEfunc::readPageAccess($content_object['uid'], '');
		if (is_numeric($pageinfo['uid'])) {
			if (!$shops[$pageinfo['uid']]) {
				$shops[$pageinfo['uid']]=0;
			}
		}
	}
}
if (count($shops)>0) {
	$options='';
	foreach ($shops as $page_uid=>$total) {
		$pageinfo=t3lib_BEfunc::readPageAccess($page_uid, '');
		if ($pageinfo['uid']) {
			$label=$pageinfo['_thePathFull'];
		} else {
			$label='Unknown (pid: '.$page_uid.')';
		}
		foreach ($shops as $page_uid2=>$total2) {
			$pageinfo2=t3lib_BEfunc::readPageAccess($page_uid2, '');
			if ($pageinfo2['uid']) {
				$label2=$pageinfo2['_thePathFull'];
			} else {
				$label2='Unknown (pid: '.$page_uid2.')';
			}
			if ($page_uid2!=$page_uid and $total>0) {
				$options.='<option value="'.$page_uid.'_'.$page_uid2.'">Copy ['.$label.': '.$total.' records] to ['.$label2.': '.$total2.' records]</option>'."\n";
			}
		}
	}
	if ($options) {
		$typoLink=$t3lib_BEfuncAlias::getModuleUrl('web_txmultishopM1');
		$tmpcontent='';
		$tmpcontent.='<select name="configuration_copier_pid"><option value="">Choose</option>'."\n".$options."\n".'</select>'."\n";
		$content.='
		<fieldset><legend>Multishop Configuration</legend>
		<fieldset><legend>Downloader / Duplicator</legend>
		<form action="'.$typoLink.'" method="post" enctype="multipart/form-data">
		<input name="action" type="hidden" value="configuration_actions" />
		'.$tmpcontent.'
			<table>
				<tr>
					<td>
						<input name="Submit" type="submit" value="Copy to target shop" onClick="return CONFIRM(\'Are you sure you want to copy/replace the settings?\')" />
					</td>
					<td>
						<input name="Download" type="submit" value="Download settings of the source shop" />
					</td>
				</tr>
			</table>
		</form>
		</fieldset>
		';
	}
	if (count($shops)) {
		$options='';
		foreach ($shops as $page_uid=>$total) {
			$pageinfo=t3lib_BEfunc::readPageAccess($page_uid, '');
			if ($pageinfo['uid']) {
				$label=$pageinfo['_thePathFull'];
				$options.='<option value="'.$page_uid.'">'.$label.': '.$total.' records</option>'."\n";
			}
		}
		if ($options) {
			$typoLink=$t3lib_BEfuncAlias::getModuleUrl('web_txmultishopM1');
			$content.='
			<fieldset><legend>Uploader</legend>
			<form action="'.$typoLink.'" method="post" enctype="multipart/form-data">
				Target shop
		';
			$content.='<select name="target_pid"><option value="">Choose</option>'."\n";
			$content.=$options;
			$content.='</select>'."\n";
			$content.='
			<input name="action" type="hidden" value="configuration_actions" />
				<br /><input name="restore_configuration_file" type="file" /> <input name="Submit" type="submit" value="Restore" />
			</form>
			</fieldset>
			';
		}
	}
}
$multishop_content_objects=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', 'deleted=0 and hidden=0 and module = \'mscore\'', '');
//$multishop_content_objects=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', 'deleted=0 and hidden=0 and sys_language_uid=0 and list_type = \'multishop_pi1\' and pi_flexform like \'%<value index="vDEF">coreshop</value>%\'', 'pid');
/*
				$multishop_content_objects = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'*',
					'tt_content',
					'list_type = \'multishop_pi1\' and extractvalue(pi_flexform, \'/T3FlexForms/data/sheet[@index="sDEFAULT"]/language[@index="lDEF"]/field[@index="method"]/value[@index="vDEF"]\')=\'coreshop\' and extractvalue(pi_flexform, \'/T3FlexForms/data/sheet[@index="sDEFAULT"]/language[@index="lDEF"]/field[@index="page_uid"]/value[@index="vDEF"]\')=\'\'',
					''
				);
*/
if (count($multishop_content_objects)>0) {
	$content.='
					<fieldset class="mod1MultishopFieldset"><legend>Global Features</legend>
						<ul>
							<li><a class="buttons" href="'.t3lib_div::linkThisScript().'&page_uid='.$content_object['uid'].'&action=clearMultishopCache">Clear Multishop Cache</a></li>
							<li><a class="buttons" href="'.t3lib_div::linkThisScript().'&page_uid='.$content_object['uid'].'&action=full_erase" onClick="return CONFIRM(\'WARNING THIS IS UNREVERSABLE AND WILL DESTROY ALL MULTISHOP DATA.\n\nAre you sure you want to delete the products, categories, orders, cms pages and settings of every Multishop?\')">Clear All Multishop Data</a></li>
						</ul>
					</fieldset>
					';
	foreach ($multishop_content_objects as $content_object) {
		$pageinfo=t3lib_BEfunc::readPageAccess($content_object['uid'], '');
		if (is_numeric($pageinfo['uid'])) {
			$typoLink=$t3lib_BEfuncAlias::getModuleUrl('web_txmultishopM1');
			$content.='
							<form action="'.$typoLink.'" method="post" enctype="multipart/form-data">
							<div class="shadow_bottom">
							<fieldset>
							<legend><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($content_object['uid'], $this->backPath, t3lib_BEfunc::BEgetRootLine($content_object['uid']), '', '')).'">'.trim($pageinfo['_thePathFull'], '/').'</a> <a title="View" href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($content_object['uid'], $this->backPath, t3lib_BEfunc::BEgetRootLine($content_object['uid']), '', '')).'">'.$this->Typo3Icon('actions-document-view', 'View').'</a> <a title="Delete" href="'.t3lib_div::linkThisScript().'&page_uid='.$content_object['uid'].'&action=erase" onClick="return CONFIRM(\'Are you sure you want to delete the products, categories, orders, cms pages and settings of: '.$pageinfo['_thePathFull'].'?\')">'.$this->Typo3Icon('actions-edit-delete', 'Delete').'</a>
							</legend>';
			$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'fe_users', '', '');
			$row=$data[0];
			if (!isset($row['total'])) {
				$row['total']=0;
			}
			$customers=$row['total'];
			$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tt_address', '', '');
			$row=$data[0];
			if (!isset($row['total'])) {
				$row['total']=0;
			}
			$customer_addresses=$row['total'];
			$categories=mslib_befe::countCategories($pageinfo['uid']);
			$products=mslib_befe::countProducts($pageinfo['uid']);
			$manufacturers=mslib_befe::countManufacturers($pageinfo['uid']);
			$orders=mslib_befe::countOrders($pageinfo['uid']);
			$importjobs=mslib_befe::countImportJobs($pageinfo['uid']);
			$content.='
<ul>
	<li>'.$this->Typo3Icon('apps-filetree-folder-default', 'customers').number_format($customers, 0, '', '.').' '.(($customers==1) ? 'customer' : 'customers').' / '.$this->Typo3Icon('apps-filetree-folder-default').number_format($customer_addresses, 0, '', '.').' '.(($customer_addresses==1) ? 'address' : 'addresses').'</li>
	<li>'.$this->Typo3Icon('apps-filetree-folder-default', 'categories').number_format($categories, 0, '', '.').' '.(($categories==1) ? 'category' : 'categories').'</li>
	<li>'.$this->Typo3Icon('apps-filetree-folder-default', 'products').number_format($products, 0, '', '.').' '.(($products==1) ? 'product' : 'products').'</li>
	<li>'.$this->Typo3Icon('apps-filetree-folder-default', 'manufacturers').number_format($manufacturers, 0, '', '.').' '.(($manufacturers==1) ? 'manufacturer' : 'manufacturers').'</li>
	<li>'.$this->Typo3Icon('apps-filetree-folder-default', 'orders').number_format($orders, 0, '', '.').' '.(($orders==1) ? 'order' : 'orders').'</li>
	<li>'.$this->Typo3Icon('apps-filetree-folder-default', 'import jobs').number_format($importjobs, 0, '', '.').' '.(($importjobs==1) ? 'import job' : 'import jobs').'</li>
</ul>

<fieldset>
	<legend>Maintenance</legend>
	<table>
		<tr>
			<td>
				<a class="buttons" href="'.t3lib_div::linkThisScript().'&page_uid='.$pageinfo['uid'].'&action=image_resizer" onClick="return CONFIRM(\'Are you sure you want to resize all images?\')">Resize Images</a>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset id="fieldset_coreshop_backup_restore"><legend>Backup / Restore</legend>
';
			$backup_types=array();
			$backup_types[]='customers';
			$backup_types[]='images';
			$backup_types[]='cms';
			$backup_types[]='configuration';
			$backup_types[]='catalog';
			$backup_types[]='orders';
			$backup_types[]='methods';
			asort($backup_types);
			$content.='
<ul>
';
			foreach ($backup_types as $type) {
				$content.='<li><input name="tx_multishop_pi[selected_tables][]" class="selected_tables" type="checkbox" value="'.$type.'" /> '.$type.'</li>'."\n";
			}
			$content.='
	<li><strong><a class="buttons_db backup_subshop_btn" href="'.t3lib_div::linkThisScript().'&page_uid='.$content_object['uid'].'&action=backup">Download Backup</a></strong></li>
</ul>
<script type="text/javascript">
	$(function(){
		$(\'.backup_subshop_btn\').click(function(e){
			e.preventDefault();
			var link=\''.$this->FULL_HTTP_URL.'\'+$(this).attr("href");
			var checkboxes=$(this).parent().parent().parent().find(".selected_tables").serialize();
			document.location.href=link+"&"+checkboxes;
		});
	});
</script>
							<h3>Restore</h3>
							';
			if (is_dir(PATH_site.'fileadmin/multishop_backup')) {
				if (($handle=opendir(PATH_site.'fileadmin/multishop_backup'))!=false) {
					$content.='<select name="custom_file"><option value="">choose</option>';
					while (false!==($file=readdir($handle))) {
						if ($file!="." && $file!="..") {
							$content.='<option value="'.$file.'">'.$file.'</option>'."\n";
						}
					}
					closedir($handle);
					$content.='</select> OR ';
				}
			}
			$content.='

							<input name="restore_file" type="file" /> <input name="Submit" type="submit" value="RESTORE" />
							<BR>
							<input name="skip_images" type="checkbox" value="1" /> Skip images<BR>
							<input name="resize_images" type="checkbox" value="1" /> Resize images (if unchecked the images are restored, but you have to run the resize thumbnails command afterwards.<BR>
							<input name="full_restore" type="checkbox" value="1" /> Full Restore (careful, cause it will erase all Multishop data on this TYPO3 installation and imports the backup)
							<input name="action" type="hidden" value="restore" />
							<input name="page_uid" type="hidden" value="'.$content_object['uid'].'" />
							</fieldset>
							</fieldset>
							</div>
							</form>
							';
		}
	}
} else {
	$content.='<fieldset>
					<legend>WELCOME TO TYPO3 MULTISHOP</legend>
					At this moment there are no Multishops deployed within this TYPO3 installation.
					</fieldset>';
}
$this->content.=$this->doc->section('Multishop Administration', $content, 0, 1);

?>