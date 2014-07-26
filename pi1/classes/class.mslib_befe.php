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
class mslib_befe {
	/*
		Back-end / Front-end functions
	*/
	// load local front-end module config
	public function loadConfiguration($multishop_page_uid='') {
		if (!$multishop_page_uid or $multishop_page_uid==$this->shop_pid) {
			static $settings;
			if (is_array($settings)) {
				// the settings are already loaded before so lets return them.
				return $settings;
			}
			// first check if we already loaded the configuration before
		}
		$settings=array();
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_configuration_values', // FROM ...
			'page_uid=\''.$multishop_page_uid.'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				if (isset($row['configuration_value']) and $row['configuration_value']!='') {
					$settings['LOCAL_MODULES'][$row['configuration_key']]=$row['configuration_value'];
				}
			}
		}
		// load local front-end module config eof
		// load global front-end module config
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_configuration', // FROM ...
			'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				if (isset($row['configuration_value'])) {
					$settings['GLOBAL_MODULES'][$row['configuration_key']]=$row['configuration_value'];
				}
			}
		}
		// load global front-end module config eof
		// merge global with local front-end module config
		foreach ($settings['GLOBAL_MODULES'] as $key=>$value) {
			if (isset($settings['LOCAL_MODULES'][$key])) {
				$settings[$key]=$settings['LOCAL_MODULES'][$key];
			} else {
				$settings[$key]=$value;
			}
		}
		// merge global with local front-end module config eof
		if ($this->tta_shop_info['cn_iso_nr']) {
			// pass the ISO number of the store country from tt address record to Multishop
			$settings['COUNTRY_ISO_NR']=$this->tta_shop_info['cn_iso_nr'];
		}
		if ($settings['COUNTRY_ISO_NR']) {
			$country=mslib_fe::getCountryByIso($settings['COUNTRY_ISO_NR']);
			$settings['CURRENCY_ARRAY']=mslib_befe::loadCurrency($country['cn_currency_iso_nr']);
			// if default currency is not set then define it to the store country currency
			if (!$settings['DEFAULT_CURRENCY']) {
				$settings['DEFAULT_CURRENCY']=$settings['CURRENCY_ARRAY']['cu_iso_3'];
			}
			switch ($settings['COUNTRY_ISO_NR']) {
				case '528':
				case '276':
					$settings['CURRENCY']='&#8364;';
					break;
				default:
					$settings['CURRENCY']=$settings['CURRENCY_ARRAY']['cu_symbol_left'];
					break;
			}
		}
		if (!$this->cookie['selected_currency']) {
			$this->cookie['selected_currency']=$settings['DEFAULT_CURRENCY'];
			if (TYPO3_MODE=='FE') {
				// add condition cause in TYPO3 4.7.4 the backend don't profile fe_user
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
				$GLOBALS['TSFE']->storeSessionData();
			}
		}
		if ($this->cookie['selected_currency']) {
			// load customer selected currency
			$settings['CUSTOMER_CURRENCY_ARRAY']=mslib_befe::loadCurrency($this->cookie['selected_currency'], 'cu_iso_3');
			$settings['CUSTOMER_CURRENCY']=$settings['CUSTOMER_CURRENCY_ARRAY']['cu_symbol_left'];
		}
		//hook to let other plugins further manipulate the settings
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loadConfiguration'])) {
			$params=array(
				'settings'=>&$settings,
				'this'=>&$this
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loadConfiguration'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		return $settings;
	}
	/* this method resizes the thumbnail images for the category
	example input:
	/var/www/vhosts/multishop.com/httpdocs/upload/tx_multishop/images/categories/original/my,
	my-photo.jpg,
	PATH_site.t3lib_extMgm::siteRelPath($this->extKey)
	example output: my-photo.jpg
	*/
	public function resizeCategoryImage($original_path, $filename, $module_path, $run_in_background=0) {
		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
			$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']=75;
		}
		if ($filename) {
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImage'])) {
				$params=array(
					'original_path'=>$original_path,
					'filename'=>&$filename,
					'module_path'=>$module_path,
					'run_in_background'=>$run_in_background
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImage'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			} else {
				if ($run_in_background) {
					$suffix_exec_param=' &> /dev/null & ';
				}
				$commands=array();
				$params='';
				if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5']=='im6') {
					$params.='-strip';
				}
				$maxwidth=$this->ms['category_image_formats']['normal']['width'];
				$maxheight=$this->ms['category_image_formats']['normal']['height'];
				$folder=mslib_befe::getImagePrefixFolder($filename);
				$dirs=array();
				$dirs[]=PATH_site.$this->ms['image_paths']['categories']['normal'].'/'.$folder;
				foreach ($dirs as $dir) {
					if (!is_dir($dir)) {
						t3lib_div::mkdir($dir);
					}
				}
				$target=PATH_site.$this->ms['image_paths']['categories']['normal'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				$commands[]=t3lib_div::imageMagickCommand('convert', $params.' -quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				if ($this->ms['MODULES']['CATEGORY_IMAGE_SHAPED_CORNERS'] and file_exists($GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite')) {
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthWest '.$module_path.'templates/images/curves/lb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthEast '.$module_path.'templates/images/curves/rb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthWest '.$module_path.'templates/images/curves/lo.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthEast '.$module_path.'templates/images/curves/ro.png "'.$target.'" "'.$target.'"';
				}
//				print_r($commands);
//				die();
				if (count($commands)) {
					// background running is not working on all boxes well, so we reverted it
					//				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
					//				t3lib_utility_Command::exec($final_command);
					foreach ($commands as $command) {
						exec($command);
					}
				}
			}
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImagePostProc'])) {
				$params=array(
					'original_path'=>$original_path,
					'folder'=>&$folder,
					'filename'=>&$filename,
					'target'=>$target,
					'module_path'=>$module_path,
					'commands'=>$commands
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImagePostProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			return $filename;
		}
	}
	public function resizeManufacturerImage($original_path, $filename, $module_path, $run_in_background=0) {
		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
			$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']=75;
		}
		if ($filename) {
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImage'])) {
				$params=array(
					'original_path'=>$original_path,
					'filename'=>&$filename,
					'module_path'=>$module_path,
					'run_in_background'=>$run_in_background
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImage'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			} else {
				if ($run_in_background) {
					$suffix_exec_param=' &> /dev/null & ';
				}
				$commands=array();
				$params='';
				if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5']=='im6') {
					$params.='-strip';
				}
				$maxwidth=$this->ms['manufacturer_image_formats']['enlarged']['width'];
				$maxheight=$this->ms['manufacturer_image_formats']['enlarged']['height'];
				$folder=mslib_befe::getImagePrefixFolder($filename);
				$dirs=array();
				$dirs[]=PATH_site.$this->ms['image_paths']['manufacturers']['normal'].'/'.$folder;
				foreach ($dirs as $dir) {
					if (!is_dir($dir)) {
						t3lib_div::mkdir($dir);
					}
				}
				$target=PATH_site.$this->ms['image_paths']['manufacturers']['normal'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				$commands[]=t3lib_div::imageMagickCommand('convert', $params.' -quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				if ($this->ms['MODULES']['CATEGORY_IMAGE_SHAPED_CORNERS'] and file_exists($GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite')) {
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthWest '.$module_path.'templates/images/curves/lb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthEast '.$module_path.'templates/images/curves/rb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthWest '.$module_path.'templates/images/curves/lo.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthEast '.$module_path.'templates/images/curves/ro.png "'.$target.'" "'.$target.'"';
				}
				if (count($commands)) {
					// background running is not working on all boxes well, so we reverted it
					//				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
					//				t3lib_utility_Command::exec($final_command);
					foreach ($commands as $command) {
						exec($command);
					}
				}
			}
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImagePostProc'])) {
				$params=array(
					'original_path'=>$original_path,
					'folder'=>&$folder,
					'filename'=>&$filename,
					'target'=>$target,
					'module_path'=>$module_path,
					'commands'=>$commands
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImagePostProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			return $filename;
		}
	}
	/* this method returns a relative path plus the inserted filename
	example input: 'my-photo.jpg','products',100
	example output: upload/tx_multishop/images/products/100/my/my-photo.jpg
	*/
	public function getImagePath($filename, $type, $width=100) {
		$folder=$this->ms['image_paths'][$type][$width].'/'.mslib_befe::getImagePrefixFolder($filename);
		return $folder.'/'.$filename;
	}
	/* this method returns a substracted prefix folder.
	example input: my-photo.jpg
	example output: my
	*/
	public function getImagePrefixFolder($filename) {
		$array=explode(".", $filename);
		$folder_name=substr(preg_replace("/\.+?$/is", "", trim($array[0])), 0, 3);
		$folder_name=preg_replace("/\-$/", "", $folder_name);
		return t3lib_div::strtolower($folder_name);
	}
	public function resizeProductImage($original_path, $filename, $module_path, $run_in_background=0) {
		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
			$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']=75;
		}
		if (file_exists($original_path) && $filename) {
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImage'])) {
				$params=array(
					'original_path'=>$original_path,
					'filename'=>&$filename,
					'module_path'=>$module_path,
					'run_in_background'=>$run_in_background
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImage'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			} else {
				if ($run_in_background) {
					$suffix_exec_param=' &> /dev/null & ';
				}
				$commands=array();
				$params='';
				if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5']=='im6') {
					$params.='-strip';
				}
				if (filesize($original_path) > 16384) {
					// IF ORIGINAL VARIANT IS BIGGER THAN 2 MBYTE RESIZE IT
					$command=t3lib_div::imageMagickCommand('convert', $params.' -quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "1500x1500>" "'.$original_path.'" "'.$original_path.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
					exec($command);
				}
				$folder=mslib_befe::getImagePrefixFolder($filename);
				$dirs=array();
				$dirs[]=PATH_site.$this->ms['image_paths']['products']['100'].'/'.$folder;
				$dirs[]=PATH_site.$this->ms['image_paths']['products']['200'].'/'.$folder;
				$dirs[]=PATH_site.$this->ms['image_paths']['products']['300'].'/'.$folder;
				$dirs[]=PATH_site.$this->ms['image_paths']['products']['50'].'/'.$folder;
				$dirs[]=PATH_site.$this->ms['image_paths']['products']['normal'].'/'.$folder;
				foreach ($dirs as $dir) {
					if (!is_dir($dir)) {
						t3lib_div::mkdir($dir);
					}
				}
				$target=PATH_site.$this->ms['image_paths']['products']['300'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				// 300 thumbnail settings
				$maxwidth=$this->ms['product_image_formats'][300]['width'];
				$maxheight=$this->ms['product_image_formats'][300]['height'];
				$commands[]=t3lib_div::imageMagickCommand('convert', $params.' -quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				if ($this->ms['MODULES']['PRODUCT_IMAGE_SHAPED_CORNERS']) {
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthWest '.$module_path.'templates/images/curves/lb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthEast '.$module_path.'templates/images/curves/rb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthWest '.$module_path.'templates/images/curves/lo.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthEast '.$module_path.'templates/images/curves/ro.png "'.$target.'" "'.$target.'"';
				}
				$target=PATH_site.$this->ms['image_paths']['products']['200'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				// 200 thumbnail settings
				$maxwidth=$this->ms['product_image_formats'][200]['width'];
				$maxheight=$this->ms['product_image_formats'][200]['height'];
				$commands[]=t3lib_div::imageMagickCommand('convert', '-quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				if ($this->ms['MODULES']['PRODUCT_IMAGE_SHAPED_CORNERS']) {
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthWest '.$module_path.'templates/images/curves/lb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity NorthEast '.$module_path.'templates/images/curves/rb.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthWest '.$module_path.'templates/images/curves/lo.png "'.$target.'" "'.$target.'"';
					$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -gravity SouthEast '.$module_path.'templates/images/curves/ro.png "'.$target.'" "'.$target.'"';
				}
				$target=PATH_site.$this->ms['image_paths']['products']['100'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				// 100 thumbnail settings
				$maxwidth=$this->ms['product_image_formats'][100]['width'];
				$maxheight=$this->ms['product_image_formats'][100]['height'];
				$commands[]=t3lib_div::imageMagickCommand('convert', '-quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				$target=PATH_site.$this->ms['image_paths']['products']['50'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				// 50 thumbnail settings
				$maxwidth=$this->ms['product_image_formats'][50]['width'];
				$maxheight=$this->ms['product_image_formats'][50]['height'];
				$commands[]=t3lib_div::imageMagickCommand('convert', '-quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
				$target=PATH_site.$this->ms['image_paths']['products']['normal'].'/'.$folder.'/'.$filename;
				copy($original_path, $target);
				// normal thumbnail settings
				$maxwidth=$this->ms['product_image_formats']['enlarged']['width'];
				$maxheight=$this->ms['product_image_formats']['enlarged']['height'];
				$commands[]=t3lib_div::imageMagickCommand('convert', '-quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);

				$params=array(
					'original_path'=>$original_path,
					'target'=>$target,
					'module_path'=>$module_path,
					'run_in_background'=>$run_in_background
				);
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImageWatermarkHook'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImageWatermarkHook'] as $funcRef) {
						t3lib_div::callUserFunction($funcRef, $params, $this);
					}
				} else {
					if (!$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT']) {
						$commands[]=t3lib_div::imageMagickCommand('convert', '-quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
					} else {
						exec(t3lib_div::imageMagickCommand('convert', '-quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target.'" "'.$target.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']));
						//t3lib_utility_Command::exec(t3lib_div::imageMagickCommand('convert', '-quality 90 -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target .'" "'.$target .'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']));
						$newsize=@getimagesize($target);
						$text_width=$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_WIDTH'];
						$text_height=$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_HEIGHT'];
						if ($newsize[0]>$maxwidth) {
							$final_width=$maxwidth;
						} else {
							$final_width=$newsize[0];
						}
						if ($newsize[1]>$maxheight) {
							$final_height=$maxheight;
						} else {
							$final_height=$newsize[1];
						}
						switch ($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_POSITION']) {
							case 'north-east':
								$pos_x=($final_width-$text_width);
								$pos_y=(25);
								break;
							case 'south-east':
								$pos_x=($final_width-$text_width);
								$pos_y=($final_height-5);
								break;
							case 'south-west':
								$pos_x='2';
								$pos_y=($final_height-5);
								break;
							case 'north-west':
								$pos_x='2';
								$pos_y=(25);
								break;
						}
						if (is_numeric($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_SIZE']) && $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'] && ($newsize[0]>$text_width && $newsize[1]>$text_height)) {
							$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT']=$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'];
							if (strstr($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'], "..")) {
								die('error in PRODUCT_IMAGE_WATERMARK_FONT_FILE value');
							} else {
								if (strstr($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'], "/")) {
									$font_file=PATH_site.$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'];
								} else {
									$font_file=$module_path.'templates/images/fonts/'.$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'];
								}
							}
							$savenametest=md5(rand(0, 99));
							$tmppath=$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache';
							// watermark bugfix
							// removing the width, to make it proportional width based on height, so the text are always visible in any image size
							$commands[]=t3lib_div::imageMagickCommand('convert', '-resize x'.$final_height.' xc:black -font "'.$font_file.'" -pointsize '.$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_SIZE'].' -fill white -draw "text '.$pos_x.','.$pos_y.' \''.$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'].'\'" -shade '.$text_width.'x'.($text_height-30).'  "'.$tmppath.'/beveled_'.$savenametest.'.jpg"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
							$commands[]=t3lib_div::imageMagickCommand('convert', '-resize x'.$final_height.' xc:black -font "'.$font_file.'" -pointsize '.$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_SIZE'].' -fill white -draw "text '.$pos_x.','.$pos_y.' \''.$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'].'\'" -shade '.$text_width.'x'.$text_height.'  -negate -normalize  "'.$tmppath.'/beveled_mask_'.$savenametest.'.jpg"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
							$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -compose CopyOpacity "'.$tmppath.'/beveled_mask_'.$savenametest.'.jpg'.'" "'.$tmppath.'/beveled_'.$savenametest.'.jpg'.'" "'.$tmppath.'/beveled_trans_'.$savenametest.'.png"';
							$commands[]=$GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"].'composite -quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' "'.$tmppath.'/beveled_trans_'.$savenametest.'.png" "'.$target.'" "'.$target.'"';
							$commands[]="rm -f ".$tmppath.'/beveled_'.$savenametest.'.jpg';
							$commands[]="rm -f ".$tmppath.'/beveled_mask_'.$savenametest.'.jpg';
							$commands[]="rm -f ".$tmppath.'/beveled_trans_'.$savenametest.'.png';
							/* echo '<img src="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/tmp/cache/beveled_'.$savenametest.'.jpg">';
							echo '<img src="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/tmp/cache/beveled_mask_'.$savenametest.'.jpg">';
							echo '<img src="'.$this->FULL_HTTP_URL.'uploads/tx_multishop/tmp/cache/beveled_trans_'.$savenametest.'.png">
							<img src="'.$this->FULL_HTTP_URL.''.$this->ms['image_paths']['products']['normal'].'/'.$folder.'/'.$filename.'">
							<BR>';
							echo $line1.'<BR><BR>';
							echo $line2.'<BR><BR>';
							echo $line3.'<BR><BR>';
							echo $line4.'<BR><BR>';
							die();*/
						}
					}
				}
				//print_r($commands);
				//die();
				if (count($commands)) {
					// background running is not working on all boxes well, so we reverted it
					//				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
					//				t3lib_utility_Command::exec($final_command);
					foreach ($commands as $command) {
						exec($command);
					}
				}
			}
			//hook to let other plugins further manipulate the method
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePostProc'])) {
				$params=array(
					'original_path'=>$original_path,
					'folder'=>&$folder,
					'filename'=>&$filename,
					'target'=>$target,
					'module_path'=>$module_path,
					'commands'=>$commands
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePostProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			return $filename;
		}
	}
	public function countProducts($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		}
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_products', 'page_uid=\''.$page_uid.'\'', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function countOrders($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		}
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_orders', 'page_uid=\''.$page_uid.'\'', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function countCustomerAddresses($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		}
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tt_address', 'pid=\''.$page_uid.'\'', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function countCustomers($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		}
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'fe_users', 'pid=\''.$page_uid.'\'', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function countCategories($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		}
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_categories', 'page_uid=\''.$page_uid.'\'', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function countManufacturers($page_uid) {
//		$data	= $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total','tx_multishop_manufacturers','page_uid=\''.$page_uid.'\'','');
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_manufacturers', '', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function countImportJobs($page_uid) {
		if (!is_numeric($page_uid)) {
			return false;
		}
		$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_import_jobs', 'page_uid=\''.$page_uid.'\'', '');
		$row=$data[0];
		if (!isset($row['total'])) {
			$row['total']=0;
		}
		return $row['total'];
	}
	public function deleteProductImage($file_name) {
		if ($file_name) {
			foreach ($this->ms['image_paths']['products'] as $key=>$value) {
				$folder_name=mslib_befe::getImagePrefixFolder($file_name);
				$path=PATH_site.$value.'/'.$folder_name.'/'.$file_name;
				if (file_exists($path)) {
					if (unlink($path)) {
						$path=PATH_site.$value.'/'.$folder_name.'/'.$file_name;
						@unlink($path);
					}
				}
			}
		}
	}
	public function enableProduct($products_id) {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (is_numeric($products_id)) {
			$updateArray=array();
			$updateArray['products_status']=1;
			$str=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$products_id.'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				// if the flat database module is enabled we have to sync the changes to the flat table
				mslib_befe::convertProductToFlat($products_id);
			}
		}
	}
	public function disableProduct($products_id) {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (is_numeric($products_id)) {
			$updateArray=array();
			$updateArray['products_status']=0;
			$str=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$products_id.'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_flat', 'products_id='.$products_id);
			}
		}
	}
	public function enableCustomer($uid) {
		if (is_numeric($uid)) {
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomer'])) {
				$params=array(
					'uid'=>&$uid,
					'this'=>&$this
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomer'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			} else {
				$disable=0;
				//hook to let other plugins further manipulate the create table query
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomerPreHook'])) {
					$params=array(
						'uid'=>$uid,
						'disable'=>&$disable
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomerPreHook'] as $funcRef) {
						t3lib_div::callUserFunction($funcRef, $params, $this);
					}
				}
				$updateArray=array();
				$updateArray['disable']=$disable;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\''.$uid.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
	public function disableCustomer($uid) {
		if (!is_numeric($uid)) {
			return false;
		}
		if (is_numeric($uid)) {
			$disable=1;
			//hook to let other plugins further manipulate the create table query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableCustomerPreHook'])) {
				$params=array(
					'uid'=>$uid,
					'disable'=>&$disable
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableCustomerPreHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			$updateArray=array();
			$updateArray['disable']=$disable;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\''.$uid.'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	public function deleteCustomer($uid) {
		if (!is_numeric($uid)) {
			return false;
		}
		if (is_numeric($uid)) {
			$deleted=1;
			//hook to let other plugins further manipulate the create table query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCustomerPreHook'])) {
				$params=array(
					'uid'=>$uid,
					'deleted'=>&$deleted
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCustomerPreHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			$updateArray['deleted']=$deleted;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\''.$uid.'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	public function deleteOrder($orders_id) {
		if (!is_numeric($orders_id)) {
			return false;
		}
		if (is_numeric($orders_id)) {
			$updateArray=array();
			$updateArray['deleted']=1;
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$orders_id.'\'', $updateArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	public function deleteProduct($products_id, $categories_id='') {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (is_numeric($products_id)) {
			$row=mslib_fe::getProduct($products_id, '', '', 1, 1);
			if (is_numeric($row['products_id'])) {
				if (is_numeric($categories_id)) {
					//hook to let other plugins further manipulate the create table query
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPreHook'])) {
						$params=array(
							'products_id'=>&$products_id,
							'categories_id'=>&$categories_id
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPreHook'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// just delete the relation to the category
					$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_categories', 'products_id='.$products_id.' and categories_id='.$categories_id);
					// count if there are relations left
					$str=$GLOBALS['TYPO3_DB']->SELECTquery('count(1)', // SELECT ...
						'tx_multishop_products_to_categories', // FROM ...
						"products_id='".$products_id."'", // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
					if ($row['total']) {
						// dont delete the product, cause there is another category that has relation
						return true;
					} else {
						$definitive_delete=1;
					}
				} else {
					$definitive_delete=1;
				}
				if ($definitive_delete) {
					//hook to let other plugins further manipulate the create table query
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPreHook'])) {
						$params=array(
							'products_id'=>$products_id,
							'categories_id'=>''
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPreHook'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
						$i=$x;
						if ($i==0) {
							$i='';
						}
						$filename=$row['products_image'.$i];
						mslib_befe::deleteProductImage($filename);
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
					//hook to let other plugins further manipulate the create table query
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPostHook'])) {
						$params=array(
							'products_id'=>$products_id
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPostHook'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					return 1;
				}
			}
		}
	}
	public function deleteCategory($categories_id) {
		if (!is_numeric($categories_id)) {
			return false;
		}
		if (is_numeric($categories_id)) {
			$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_categories', // FROM ...
				"categories_id='".$categories_id."'", // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				// first check if the category has subcategories to delete them as well
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('categories_id', // SELECT ...
					'tx_multishop_categories', // FROM ...
					"parent_id = '".$row['categories_id']."'", // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$subcategories_query=$GLOBALS['TYPO3_DB']->sql_query($str);
				while (($subcategory=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($subcategories_query))!=false) {
					mslib_befe::deleteCategory($subcategory['categories_id']);
				}
				// remove any found products
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('p.products_id', // SELECT ...
					'tx_multishop_products p, tx_multishop_products_to_categories p2c', // FROM ...
					"p2c.categories_id='".$categories_id."' and p.products_id=p2c.products_id", // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$products_query=$GLOBALS['TYPO3_DB']->sql_query($str);
				while (($product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_query))!=false) {
					mslib_befe::deleteProduct($product['products_id'], $categories_id);
				}
				// finally delete the category
				$filename=$row['categories_image'];
				mslib_befe::deleteCategoryImage($filename);
				$tables=array();
				$tables[]='tx_multishop_categories';
				$tables[]='tx_multishop_categories_description';
				$tables[]='tx_multishop_products_to_categories';
				foreach ($tables as $table) {
					$query=$GLOBALS['TYPO3_DB']->DELETEquery($table, 'categories_id='.$categories_id);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
			//hook to let other plugins further manipulate the create table query
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCategoryPostHook'])) {
				$params=array(
					'categories_id'=>$categories_id
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCategoryPostHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
		}
	}
	public function deleteCategoryImage($file_name) {
		foreach ($this->ms['image_paths']['categories'] as $key=>$value) {
			$path=PATH_site.$value.'/'.$file_name;
			if (unlink($path)) {
				return 1;
			}
		}
	}
	public function deleteManufacturerImage($file_name) {
		foreach ($this->ms['image_paths']['manufacturers'] as $key=>$value) {
			$path=PATH_site.$value.'/'.$file_name;
			if (unlink($path)) {
				return 1;
			}
		}
	}
	public function deleteManufacturer($id) {
		if (is_numeric($id)) {
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_manufacturers', 'manufacturers_id='.$id);
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_manufacturers_cms', 'manufacturers_id='.$id);
			$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_manufacturers_info', 'manufacturers_id='.$id);
		}
	}
	public function deltree($path) {
		if (is_dir($path)) {
			if (version_compare(PHP_VERSION, '5.0.0')<0) {
				$entries=array();
				if (($handle=opendir($path))!=false) {
					while (false!==($file=readdir($handle))) {
						$entries[]=$file;
					}
					closedir($handle);
				}
			} else {
				$entries=scandir($path);
				if ($entries===false) {
					$entries=array(); // just in case scandir fail...
				}
			}
			foreach ($entries as $entry) {
				if ($entry!='.' && $entry!='..') {
					mslib_befe::deltree($path.'/'.$entry);
				}
			}
			return rmdir($path);
		} else {
			return unlink($path);
		}
	}
	public function doesExist($table, $field, $value, $more='') {
		$query="SELECT * FROM ".$table." WHERE ".$field."='".addslashes($value)."' ".$more;
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
			return $row;
		}
	}
	/*
	Some PHP compilations doesnt have the exif_imagetype function. In that case we provide our own alternative
	*/
	public function exif_imagetype($filename) {
		if (function_exists('exif_imagetype')) {
			return exif_imagetype($filename);
		} else if ((list($width, $height, $type, $attr)=getimagesize($filename))!==false) {
			return $type;
		}
		return false;
	}
	public function convertConfiguration($ms) {
		// bit lame code, but this is for subdirectory hosted typo3 installations. Compatible for front and back-end.
		/*
		$paths=explode("/",$_SERVER['PHP_SELF']);
		if (count($paths) > 2) 			$prefix=$paths[1].'/';
		else							$prefix='';
		*/
		// not completely tested. reverting temporary
		$prefix='';
		$ms['image_paths']['products']['50']=$prefix.'uploads/tx_multishop/images/products/50';
		$ms['image_paths']['products']['100']=$prefix.'uploads/tx_multishop/images/products/100';
		$ms['image_paths']['products']['200']=$prefix.'uploads/tx_multishop/images/products/200';
		$ms['image_paths']['products']['300']=$prefix.'uploads/tx_multishop/images/products/300';
		$ms['image_paths']['products']['original']=$prefix.'uploads/tx_multishop/images/products/original';
		$ms['image_paths']['products']['normal']=$prefix.'uploads/tx_multishop/images/products/normal';
		$ms['image_paths']['categories']['original']=$prefix.'uploads/tx_multishop/images/categories/original';
		$ms['image_paths']['categories']['normal']=$prefix.'uploads/tx_multishop/images/categories/normal';
		$ms['image_paths']['manufacturers']['original']=$prefix.'uploads/tx_multishop/images/manufacturers/original';
		$ms['image_paths']['manufacturers']['normal']=$prefix.'uploads/tx_multishop/images/manufacturers/normal';
		$format=explode("x", $ms['MODULES']['CATEGORY_IMAGE_SIZE_NORMAL']);
		$ms['category_image_formats']['normal']['width']=$format[0];
		$ms['category_image_formats']['normal']['height']=$format[1];
		$format=explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_50']);
		$ms['product_image_formats'][50]['width']=$format[0];
		$ms['product_image_formats'][50]['height']=$format[1];
		$format=explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_100']);
		$ms['product_image_formats'][100]['width']=$format[0];
		$ms['product_image_formats'][100]['height']=$format[1];
		$format=explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_200']);
		$ms['product_image_formats'][200]['width']=$format[0];
		$ms['product_image_formats'][200]['height']=$format[1];
		$format=explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_300']);
		$ms['product_image_formats'][300]['width']=$format[0];
		$ms['product_image_formats'][300]['height']=$format[1];
		$format=explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_ENLARGED']);
		$ms['product_image_formats']['enlarged']['width']=$format[0];
		$ms['product_image_formats']['enlarged']['height']=$format[1];
		return $ms;
	}
	// method for logging changes to specific tables
	public function addUndo($id, $table) {
		if (is_numeric($id) and $table) {
			$undo_tables=array();
			$undo_tables['tx_multishop_products']='tx_multishop_undo_products';
			$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				$table, // FROM ...
				"products_id='".$id."'", // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$row['crdate']=time();
			$query=$GLOBALS['TYPO3_DB']->INSERTquery($undo_tables[$table], $row);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			return $row;
		}
	}
	public function ms_implode($char, $array, $fix='', $prefix, $addslashes=false) {
		$lem=array_keys($array);
		$char=htmlentities($char);
		$str='';
		for ($i=0; $i<sizeof($lem); $i++) {
			if ($addslashes) {
				if ($array[$lem[$i]]) {
					$str.=$prefix.$fix.(($i==sizeof($lem)-1) ? addslashes($array[$lem[$i]]).$fix : addslashes($array[$lem[$i]]).$fix.$char);
				}
			} else {
				if ($array[$lem[$i]]) {
					$str.=$prefix.$fix.(($i==sizeof($lem)-1) ? $array[$lem[$i]].$fix : $array[$lem[$i]].$fix.$char);
				}
			}
		}
		return $str;
	}
	// function for saving the importer products images
	public function saveImportedProductImages($products_id, $input, $item, $oldproduct=array(), $log_file='') {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (is_numeric($products_id)) {
			for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
				// hidden filename that is retrieved from the ajax upload
				$i=$x;
				if ($i==0) {
					$i='';
				}
				$colname='products_image'.$i;
				if (!$oldproduct[$colname]) {
					if ($item[$colname]) {
						$plaatje1=$item[$colname];
						$data=mslib_fe::file_get_contents($plaatje1);
						if ($data) {
							$plaatje1_name=$products_id.'-'.($colname).'-'.time();
							$tmpfile=PATH_site.'uploads/tx_multishop/tmp/'.$plaatje1_name;
							file_put_contents($tmpfile, $data);
							$plaatje1=$tmpfile;
							if (($extentie1=mslib_befe::exif_imagetype($plaatje1)) && $plaatje1_name<>'') {
								$extentie1=image_type_to_extension($extentie1, false);
								$ext=$extentie1;
								$ix=0;
								$filename=mslib_fe::rewritenamein($item['products_name']).'.'.$ext;
								$folder=mslib_befe::getImagePrefixFolder($filename);
								if (!is_dir(PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
									t3lib_div::mkdir(PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder);
								}
								$folder.='/';
								$target=PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
								if (file_exists($target)) {
									do {
										$filename=mslib_fe::rewritenamein($item['products_name']).($ix>0 ? '-'.$ix : '').'.'.$ext;
										$folder=mslib_befe::getImagePrefixFolder($filename);
										if (!is_dir(PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
											t3lib_div::mkdir(PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder);
										}
										$folder.='/';
										$target=PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
										$ix++;
									} while (file_exists($target));
								}
								// end
								$products_image=$path.'/'.$naam;
								// backup original
								$target=PATH_site.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
								copy($tmpfile, $target);
								// backup original eof
								$products_image_name=mslib_befe::resizeProductImage($target, $filename, PATH_site.t3lib_extMgm::siteRelPath($this->extKey), 1);
								$item['img'][$i]=$products_image_name;
								if ($log_file) {
									file_put_contents($log_file, 'Downloading product'.$i.' image ('.$item[$colname].') succeeded and has been resized to '.$item['img'][$i].'.'."\n", FILE_APPEND);
								}
							} else {
								$item['img'][$i]='';
							}
						} else {
							$item['img'][$i]='';
							if ($log_file) {
								file_put_contents($log_file, 'Downloading product'.$i.' image ('.$item[$colname].') failed.'."\n", FILE_APPEND);
							}
						}
						if ($tmpfile and file_exists($tmpfile)) {
							@unlink($tmpfile);
						}
					} else {
						$item['img'][$i]='';
					}
				} else {
					$item['img'][$i]='';
				}
			}
			if (count($item['img'])>0) {
				$array=array();
				for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
					$i=$x;
					if ($i==0) {
						$i='';
					}
					if ($item['img'][$i]) {
						$colname='products_image'.$i;
						$array[$colname]=$item['img'][$i];
					}
				}
				if (count($array)>0) {
					if ($array['products_image']) {
						$array['contains_image']=1;
					} else {
						$array['contains_image']=0;
					}
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id='.$products_id, $array);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
	}
	// method for adding a product to the flat table for maximum speed
	public function convertProductToFlat($products_id, $table_name='tx_multishop_products_flat') {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlat'])) {
			$params=array(
				'status'=>$status,
				'table'=>$table,
				'id'=>$id,
				'this'=>&$this,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlat'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		} else {
			if ($table_name=='tx_multishop_products_flat') {
				$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_flat', "products_id='".$products_id."'");
			}
			// retrieving the products record
			$select=array();
			$select[]='*';
			$select[]='s.status as special_status';
			$select[]='pd.language_id';
			$select[]='p2c.sort_order';
			$select[]='p.staffel_price as staffel_price';
			$select[]='o.code as order_unit_code';
			$select[]='od.name as order_unit_name';
			// old v2 code
			// $select[]='tr.tx_rate as tax_rate';
			$select[]='IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
			$select[]='p2c.sort_order';
			$from=array();
			// old v2 code
//			$from[]='tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id=m.manufacturers_id left join static_taxes tr on p.tax_id = tr.uid';
			$from[]='tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id=m.manufacturers_id left join tx_multishop_order_units o on p.order_unit_id=o.id left join tx_multishop_order_units_description od on o.id=od.order_unit_id and od.language_id=0 ';
			$from[]='tx_multishop_products_description pd';
			$from[]='tx_multishop_products_to_categories p2c';
			$from[]='tx_multishop_categories c';
			$from[]='tx_multishop_categories_description cd';
			$where=array();
			$where[]='c.status=1';
			$where[]='p.products_status=1';
			$where[]="p2c.products_id='".$products_id."'";
			$where[]='p.products_id=pd.products_id';
			$where[]='p.products_id=p2c.products_id';
			$where[]='p2c.categories_id=c.categories_id';
			$where[]='p2c.categories_id=cd.categories_id';
			$where[]='pd.language_id=cd.language_id';
			$orderby=array();
			$orderby[]='pd.language_id';
			$query_elements=array();
			$query_elements['select']=&$select;
			$query_elements['from']=&$from;
			$query_elements['where']=&$where;
			$query_elements['groupby']=&$groupby;
			$query_elements['orderby']=&$orderby;
			$query_elements['limit']=&$limit;
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreFetchProductHook'])) {
				$params=array(
					'products_id'=>&$products_id,
					'query_elements'=>&$query_elements
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreFetchProductHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			// custom hook that can be controlled by third-party plugin eof
			$str=$GLOBALS['TYPO3_DB']->SELECTquery((is_array($select) ? implode(",", $select) : ''), // SELECT ...
				(is_array($from) ? implode(",", $from) : ''), // FROM ...
				(is_array($where) ? implode(" AND ", $where) : ''), // WHERE...
				(is_array($groupby) ? implode(",", $groupby) : ''), // GROUP BY...
				(is_array($orderby) ? implode(",", $orderby) : ''), // ORDER BY...
				(is_array($limit) ? implode(",", $limit) : '') // LIMIT ...
			);
			if ($this->debug) {
				$logString=$str;
				t3lib_div::devLog($logString, 'multishop',0);
			}
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
			if ($this->conf['debugEnabled']=='1') {
				$logString='convertProductToFlat query: '.$str.'.';
				t3lib_div::devLog($logString, 'multishop',0);
			}
			if (!$rows) {
				$logString='convertProductToFlat fetch query returned zero results. Query: '.$str;
				t3lib_div::devLog($logString, 'multishop',3);
			}
			if ($rows) {
				while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
					// retrieving the categories path
					$flat_product=array();
					$flat_product['language_id']=$row['language_id'];
					$flat_product['products_id']=$products_id;
					$flat_product['products_condition']=$row['products_condition'];
					$flat_product['products_name']=$row['products_name'];
					$flat_product['products_model']=$row['products_model'];
					$flat_product['products_description']=$row['products_description'];
					$flat_product['products_shortdescription']=$row['products_shortdescription'];
					//$flat_product['products_extra_description']=$row['products_extra_description'];
					$flat_product['products_quantity']=$row['products_quantity'];
					$flat_product['products_price']=$row['products_price'];
					$flat_product['products_viewed']=$row['products_viewed'];
					$flat_product['staffel_price']=$row['staffel_price'];
					$flat_product['delivery_time']=$row['delivery_time'];
					$flat_product['order_unit_id']=$row['order_unit_id'];
					$flat_product['order_unit_code']=$row['order_unit_code'];
					$flat_product['order_unit_name']=$row['order_unit_name'];
					if ($row['specials_new_products_price'] && $row['special_status']>0) {
						$flat_product['final_price']=$row['specials_new_products_price'];
						$flat_product['sstatus']=1;
					} else {
						$flat_product['final_price']=$row['products_price'];
					}
					// now we are going to define the price filter start value, so we can search very fast on it
					$array=explode(";", $this->ms['MODULES']['PRICE_FILTER_BOX_STEPPINGS']);
					$total=count($array);
					$tel=0;
					foreach ($array as $item) {
						$tel++;
						$cols=explode("-", $item);
						if ($flat_product['final_price']<=$cols[1]) {
							$flat_product['price_filter']=$cols[0];
							break;
						}
						if ($tel==$total) {
							if ($flat_product['final_price']>$cols[1]) {
								$flat_product['price_filter']=$cols[1];
							}
						}
					}
					// now we are going to define the price filter start value, so we can search very fast on it eof
					$flat_product['products_multiplication']=$row['products_multiplication'];
					$flat_product['minimum_quantity']=$row['minimum_quantity'];
					$flat_product['maximum_quantity']=$row['maximum_quantity'];
					$flat_product['products_date_available']=$row['products_date_available'];
					$flat_product['products_last_modified']=$row['products_last_modified'];
					$flat_product['tax_id']=$row['tax_id'];
					$flat_product['categories_id']=$row['categories_id'];
					$flat_product['categories_name']=$row['categories_name'];
					$flat_product['manufacturers_id']=$row['manufacturers_id'];
					$flat_product['manufacturers_name']=$row['manufacturers_name'];
					$flat_product['products_negative_keywords']=$row['products_negative_keywords'];
					$flat_product['products_meta_title']=$row['products_meta_title'];
					$flat_product['products_meta_description']=$row['products_meta_description'];
					$flat_product['products_meta_keywords']=$row['products_meta_keywords'];
					$flat_product['products_url']=$row['products_url'];
					$flat_product['vendor_code']=$row['vendor_code'];
					$flat_product['sku_code']=$row['sku_code'];
					$flat_product['ean_code']=$row['ean_code'];
					$flat_product['language_id']=$row['language_id'];
					if ($flat_product['categories_id']) {
						// get all cats to generate multilevel fake url
						$level=0;
						$cats=mslib_fe::Crumbar($flat_product['categories_id']);
						$cats=array_reverse($cats);
						$where='';
						if (count($cats)>0) {
							$i=0;
							foreach ($cats as $cat) {
								$flat_product['categories_id_'.$i]=$cat['id'];
								$flat_product['categories_name_'.$i]=$cat['name'];
								$i++;
							}
						}
						// get all cats to generate multilevel fake url eof
					}
					for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
						$i=$x;
						if ($i==0) {
							$i='';
						}
						$flat_product['products_image'.$i]=$row['products_image'.$i];
					}
					if ($flat_product['products_image']) {
						$flat_product['contains_image']=1;
					} else {
						$flat_product['contains_image']=0;
					}
					$flat_product['products_date_added']=$row['products_date_added'];
					$flat_product['products_weight']=$row['products_weight'];
					$flat_product['sort_order']=$row['sort_order'];
					$flat_product['product_capital_price']=$row['product_capital_price'];
					$flat_product['page_uid']=$row['page_uid'];
					if ($this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS'] and is_array($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'])) {
						foreach ($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'] as $option_id=>$array) {
							if ($option_id) {
								$option_values=mslib_fe::getProductsOptionValues($option_id, $flat_product['products_id']);
								if ($option_values[0]['products_options_values_name']) {
									$flat_product[$array[0]]=$option_values[0]['products_options_values_name'];
								}
							}
						}
					}
					// custom hook that can be controlled by third-party plugin
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreInsert'])) {
						$params=array(
							'products_id'=>&$products_id,
							'flat_product'=>&$flat_product,
							'row'=>&$row,
							'table_name'=>&$table_name
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreInsert'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// custom hook that can be controlled by third-party plugin eof
					$query=$GLOBALS['TYPO3_DB']->INSERTquery($table_name, $flat_product);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if (!$res) {
						$logString='Query failed! Query: '.$query;
						t3lib_div::devLog($logString, 'multishop',3);
					}
					if ($this->debug) {
						error_log($query);
						$logString=$query;
						t3lib_div::devLog($logString, 'multishop',0);
					}
					// custom hook that can be controlled by third-party plugin
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatProcInsert'])) {
						$params=array(
							'products_id'=>&$products_id,
							'flat_product'=>&$flat_product,
							'row'=>&$row,
							'table_name'=>&$table_name
						);
						foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatProcInsert'] as $funcRef) {
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// custom hook that can be controlled by third-party plugin eof
				}
			}
			return $flat_product['products_id'];
		}
	}
	// method for scanning subfolders and retrieve their associated files
	public function listdir($dir='.') {
		if (!is_dir($dir)) {
			return false;
		}
		$files=array();
		mslib_befe::listdiraux($dir, $files);
		return $files;
	}
	public function listdiraux($dir, &$files) {
		$handle=opendir($dir);
		while (($file=readdir($handle))!==false) {
			if ($file=='.' || $file=='..') {
				continue;
			}
			$filepath=$dir=='.' ? $file : $dir.'/'.$file;
			if (is_link($filepath)) {
				continue;
			}
			if (is_file($filepath)) {
				$files[]=$filepath;
			} else if (is_dir($filepath)) {
				mslib_befe::listdiraux($filepath, $files);
			}
		}
		closedir($handle);
	}
	public function tep_get_categories_select($categories_id='0', $aid='', $level=0, $selectedid='') {
		$qry=$GLOBALS['TYPO3_DB']->SELECTquery('cd.categories_name, c.categories_id, c.parent_id', // SELECT ...
			'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
			'c.parent_id=\''.$categories_id.'\' and c.status=1 and c.categories_id=cd.categories_id and c.page_uid=\''.$this->shop_pid.'\'', // WHERE...
			'', // GROUP BY...
			'c.sort_order, cd.categories_name', // ORDER BY...
			'' // LIMIT ...
		);
		$parent_categories_query=$GLOBALS['TYPO3_DB']->sql_query($qry);
		$html='';
		while (($parent_categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($parent_categories_query))!=false) {
			$html.='<option value="'.$parent_categories['categories_id'].'" '.(($selectedid==$parent_categories['categories_id']) ? 'selected' : '').'>';
			for ($i=0; $i<$level; $i++) {
				$html.='--';
			}
			$html.=$parent_categories['categories_name'].'</option>';
			$strchk=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_categories', // FROM ...
				'c.parent_id=\''.$parent_categories['categories_id'].'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
				$html.=mslib_befe::tep_get_categories_select($parent_categories['categories_id'], $aid, ($level+1), $selectedid);
			}
		}
		if (!$categories_id) {
			$html.='</select>';
		}
		return $html;
	}
	public function tep_get_chained_categories_select($categories_id='0', $aid='', $level=0, $selectedid='', $page_uid='') {
		if (!$page_uid) {
			$page_uid=$this->shop_pid;
		}
		if (!$categories_id) {
			$categories_id=0;
		}
		$output=array();
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('cd.categories_name, c.categories_id, c.parent_id', // SELECT ...
			'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
			'c.parent_id=\''.$categories_id.'\' and c.status=1 and c.categories_id=cd.categories_id and c.page_uid=\''.$page_uid.'\'', // WHERE...
			'', // GROUP BY...
			'c.sort_order, cd.categories_name', // ORDER BY...
			'' // LIMIT ...
		);
		$parent_categories_query=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($parent_categories_query);
		while (($parent_categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($parent_categories_query))!=false) {
			$output[$level].='<option value="'.$parent_categories['categories_id'].'" '.(($selectedid==$parent_categories['categories_id']) ? 'selected' : '').' class="'.($level>0 ? ''.$parent_categories['parent_id'] : '').'">'.$parent_categories['categories_name'].'</option>';
			$strchk=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_categories c', // FROM ...
				'c.parent_id=\''.$parent_categories['categories_id'].'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qrychk=$GLOBALS['TYPO3_DB']->sql_query($strchk);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
				$tmp_array=mslib_befe::tep_get_chained_categories_select($parent_categories['categories_id'], $aid, ($level+1), $selectedid, $page_uid);
				foreach ($tmp_array as $key=>$value) {
					$output[$key].=$value;
				}
			}
		}
		return $output;
	}
	public function array2json($arr) {
		if (function_exists('json_encode')) {
			return json_encode($arr); //Lastest versions of PHP already has this functionality.
		}
		$parts=array();
		$is_list=false;
		//Find out if the given array is a numerical array
		$keys=array_keys($arr);
		$max_length=count($arr)-1;
		if (($keys[0]==0) and ($keys[$max_length]==$max_length)) { //See if the first key is 0 and last key is length - 1
			$is_list=true;
			for ($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
				if ($i!=$keys[$i]) { //A key fails at position check.
					$is_list=false; //It is an associative array.
					break;
				}
			}
		}
		foreach ($arr as $key=>$value) {
			if (is_array($value)) { //Custom handling for arrays
				if ($is_list) {
					$parts[]=array2json($value); /* :RECURSION: */
				} else {
					$parts[]='"'.$key.'":'.array2json($value); /* :RECURSION: */
				}
			} else {
				$str='';
				if (!$is_list) {
					$str='"'.$key.'":';
				}
				//Custom handling for multiple data types
				if (is_numeric($value)) {
					$str.=$value; //Numbers
				} else if ($value===false) {
					$str.='false'; //The booleans
				} else if ($value===true) {
					$str.='true';
				} else {
					$str.='"'.addslashes($value).'"'; //All other things
				}
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts[]=$str;
			}
		}
		if (is_array($parts)) {
			$json=implode(',', $parts);
			if ($is_list) {
				return '['.$json.']'; //Return numerical JSON
			}
			return '{'.$json.'}'; //Return associative JSON
		}
	}
	public function rebuildFlatDatabase() {
		$content='<div class="main-heading"><h1>Rebuild Flat Database</h1></div>';
		$str="DROP TABLE IF EXISTS `tx_multishop_products_flat_tmp`;";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$str="CREATE TABLE `tx_multishop_products_flat_tmp` (
			`products_id` int(11) NULL AUTO_INCREMENT,
			`language_id` tinyint(2)  NULL DEFAULT '0',
			`products_name` varchar(255) NULL,
			`products_model` varchar(128) NULL,
			`products_shortdescription` text NULL,
			`products_description` text NULL,
			`products_quantity` int(11) NULL,
			`products_price` decimal(24,14) NULL,
			`staffel_price` varchar(250) NULL,
			`ean_code` varchar(50) NULL,
			`sku_code` varchar(25) NULL,
			`specials_new_products_price` decimal(24,14) NULL,
			`final_price` decimal(24,14) NULL,
			`final_price_difference` decimal(24,14) NULL,
			`products_date_available` int(11) NULL,
			`products_last_modified` int(11) NULL,
			`tax_id` int(5) NULL,
			`categories_id` int(5) NULL,
			`categories_name` varchar(150) NULL,
			`manufacturers_id` int(5) NULL,
			`manufacturers_name` varchar(32) NULL,
			`categories_id_0` int(5) NULL,
			`categories_name_0` varchar(150) NULL,
			`categories_id_1` int(5) NULL,
			`categories_name_1` varchar(150) NULL,
			`categories_id_2` int(5) NULL,
			`categories_name_2` varchar(150) NULL,
			`categories_id_3` int(5) NULL,
			`categories_name_3` varchar(150) NULL,
			`categories_id_4` int(5) NULL,
			`categories_name_4` varchar(150) NULL,
			`categories_id_5` int(5) NULL,
			`categories_name_5` varchar(150) NULL,
			";
		for ($x=0; $x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
			$i=$x;
			if ($i==0) {
				$i='';
			}
			$str.='`products_image'.$i.'` varchar(250) NULL,'."\n";
		}
		$str.="	  `products_viewed` int(11) NULL,
		  `products_date_added` int(11) NULL,
		  `products_weight` decimal(5,2) NULL,
		  `sort_order` int(11) NULL,
		  `product_capital_price` decimal(24,14) NULL,
		  `page_uid` int(11) NULL,
		  `products_negative_keywords` varchar(254) NULL,
		  `products_meta_title` varchar(254) NULL,
		  `products_meta_description` varchar(254) NULL,
		  `products_meta_keywords` varchar(254) NULL,
		  `products_url` TEXT NULL,
		  `sstatus` tinyint(1) NULL,
		  `price_filter` int(4) NULL,
		  `contains_image` tinyint(1) NULL,
		  `products_multiplication` int(11) NULL DEFAULT '0',
		  `minimum_quantity` int(11) NULL DEFAULT '1',
		  `maximum_quantity` int(11) NULL,
		  `delivery_time` varchar(75) default '',
		  `order_unit_id` int(11) default '0',
		  `order_unit_code` varchar(15) default '',
		  `order_unit_name` varchar(25) default '',
		  `products_condition` varchar(20) default 'new',
		  `vendor_code` varchar(25) default '',
		";
		if ($this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS'] and is_array($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'])) {
			$additional_indexes='';
			foreach ($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'] as $option_id=>$array) {
				if ($array[0] and $array[1]) {
					$str.="		  `".$array[0]."` ".$array[1]." NULL,"."\n";
					$additional_indexes.="KEY `".$array[0]."` (`".$array[0]."`),"."\n";
				}
			}
		}
		//hook to let other plugins further manipulate the create table query
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabaseQueryProc'])) {
			$params=array(
				'str'=>&$str,
				'additional_indexes'=>&$additional_indexes
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabaseQueryProc'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$str.="PRIMARY KEY (`products_id`,`language_id`),
		  KEY `language_id` (`language_id`),
		  KEY `products_name_2` (`products_name`),
		  KEY `products_model` (`products_model`),
		  KEY `sstatus` (`sstatus`),
		  KEY `final_price_difference` (`final_price_difference`),
		  KEY `combined_cat_index` (`categories_id_0`,`categories_id_1`,`categories_id_2`,`categories_id_3`,`categories_id_4`,`categories_id_5`),
		  KEY `combined_specials` (`page_uid`,`sstatus`),
		  KEY `combined_categories_id` (`page_uid`,`categories_id`),
		  KEY `combined_one` (`page_uid`,`categories_id`,`sstatus`),
		  KEY `sort_order` (`sort_order`),
		  KEY `page_uid` (`page_uid`),
		  KEY `categories_id` (`categories_id`),
		  KEY `combined_three` (`page_uid`,`categories_id`,`sort_order`),
		  KEY `categories_id_0` (`categories_id_0`),
		  KEY `categories_id_3` (`categories_id_3`),
		  KEY `categories_id_2` (`categories_id_2`),
		  KEY `categories_id_1` (`categories_id_1`),
		  KEY `categories_id_4` (`categories_id_4`),
		  KEY `categories_id_5` (`categories_id_5`),
		  KEY `products_date_added` (`products_date_added`),
		  KEY `products_last_modified` (`products_last_modified`),
		  KEY `final_price` (`final_price`),
		  KEY `combined_four` (`sort_order`,`categories_id`),
		  KEY `combined_five` (`categories_id_0`,`categories_id_1`,`categories_id_2`,`categories_id_3`,`final_price`),
		  KEY `price_filter` (`price_filter`),
		  KEY `manufacturers_id` (`manufacturers_id`),
		  KEY `contains_image` (`contains_image`),
		  KEY `vendor_code` (`vendor_code`),
		  KEY `sku_code` (`sku_code`),
		  ".$additional_indexes."
		  FULLTEXT KEY `products_name` (`products_name`),
		  FULLTEXT KEY `products_model_2` (`products_model`),
		  FULLTEXT KEY `products_model_3` (`products_model`,`products_name`)

		) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
		";
		//hook to let other plugins further manipulate the create table query
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePreHook'])) {
			$params=array(
				'str'=>&$str
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePreHook'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if (!$qry || $this->conf['debugEnabled']=='1') {
			$logString='rebuildFlatDatabase CREATE TABLE failed query: '.$str;
			t3lib_div::devLog($logString, 'multishop',-1);
		}
		$products=array();
		//$str="truncate tx_multishop_products_flat";
		//$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('products_id', // SELECT ...
			'tx_multishop_products', // FROM ...
			'products_status=1', // WHERE...
			'', // GROUP BY...
			'sort_order '.$this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'], // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($this->conf['debugEnabled']=='1') {
			$logString='rebuildFlatDatabase query: '.$str;
			t3lib_div::devLog($logString, 'multishop',-1);
		}
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
			$products[]=$row['products_id'];
		}
		foreach ($products as $products_id) {
			mslib_befe::convertProductToFlat($products_id, 'tx_multishop_products_flat_tmp');
		}
		$str="ANALYZE TABLE `tx_multishop_products_flat_tmp`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$str="drop table `tx_multishop_products_flat`;";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		// convert to memory table
		//$str="CREATE TABLE tx_multishop_products_flat ENGINE=MEMORY AS SELECT * FROM tx_multishop_products_flat_tmp";
		//$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		//$str="drop table `tx_multishop_products_flat_tmp`;";
		//$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		// if not using memory table rename the tmp table
		$str="RENAME TABLE `tx_multishop_products_flat_tmp` TO `tx_multishop_products_flat`;";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		//hook to let other plugins further manipulate the create table query
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePostHook'])) {
			$params=array();
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePostHook'] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
		return $content;
	}
	public function convToUtf8($content) {
		if (!mb_check_encoding($content, 'UTF-8') or !($content===mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))) {
			$content=mb_convert_encoding($content, 'UTF-8');
			if (mb_check_encoding($content, 'UTF-8')) {
				// log('Converted to UTF-8');
			} else {
				// log('Could not convert to UTF-8');
			}
		}
		return $content;
	}
	public function detect_encoding($string) {
		static $list=array(
			'utf-8',
			'windows-1251'
		);
		foreach ($list as $item) {
			$sample=iconv($item, $item, $string);
			if (md5($sample)==md5($string)) {
				return $item;
			}
		}
		return null;
	}
	public function loadCurrency($value, $field='cu_iso_nr') {
		if ($value) {
			$data=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', ' static_currencies', $field.'=\''.addslashes($value).'\'', '');
			return $data[0];
		}
	}
	public function print_r($array_in, $title='') {
		if (is_object($array_in)) {
			$json=json_encode($array_in);
			$array_in=json_decode($json, true);
		}
		if (is_array($array_in)) {
			if (count($array_in)==0) {
//				$result .= '<tr><td><font face="Verdana,Arial" size="1"><strong>EMPTY!</strong></font></td></tr>';
			} else {
				$result='
				<table border="1" cellpadding="1" cellspacing="0" class="print_r_table" width="100%">';
				if ($title) {
					$result.='
					<tr>
					<th colspan="2">
					'.htmlspecialchars($title).'
					</th>
					</tr>
					';
				}
				foreach ($array_in as $key=>$val) {
					if ((string)$key or $val) {
						if (!$tr_type or $tr_type=='even') {
							$tr_type='odd';
						} else {
							$tr_type='even';
						}
						$result.='<tr class="'.$tr_type.'">
							<td valign="top" class="print_r_key">'.htmlspecialchars((string)$key).'</td>
							<td class="print_r_value">';
						if (is_array($val)) {
//							$result .= t3lib_utility_Debug::viewArray($val);
							$result.=mslib_befe::print_r($val);
						} elseif (is_object($val)) {
							$string='';
							if (method_exists($val, '__toString')) {
								$string.=get_class($val).': '.(string)$val;
							} else {
								$string.=print_r($val, true);
							}
							$result.=''.nl2br(htmlspecialchars($string)).'<br />';
						} else {
							if (gettype($val)=='object') {
								$string='Unknown object';
							} else {
								$string=(string)$val;
							}
							$result.=nl2br(htmlspecialchars($string)).'<br />';
						}
						$result.='</td>
						</tr>';
					}
				}
				$result.='</table>';
			}
		} else {
			$result='<table border="1" cellpadding="1" cellspacing="0" width="100%">
				<tr>
					<td><font face="Verdana,Arial" size="1" color="red">'.nl2br(htmlspecialchars((string)$array_in)).'<br /></font></td>
				</tr>
			</table>'; // Output it as a string.
		}
		return $result;
	}
	public function Week($week) {
		$year=date('Y');
		$lastweek=$week-1;
		if ($lastweek==0) {
			$week=52;
			$year--;
		}
		$lastweek=sprintf("%02d", $lastweek);
		for ($i=1; $i<=7; $i++) {
			$arrdays[]=strtotime("$year"."W$lastweek"."$i");
		}
		return $arrdays;
	}
	public function RunMultishopUpdate($multishop_page_uid='') {
		$settings=array();
		// attribute fixer
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products_options', // FROM ...
			'language_id=\'0\'', // WHERE...
			'', // GROUP BY...
			'sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				// now load the related values
				$str2=$GLOBALS['TYPO3_DB']->SELECTquery('povp.products_options_id,povp.products_options_values_id', // SELECT ...
					'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov', // FROM ...
					'povp.products_options_id=\''.$row['products_options_id'].'\' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id=\'0\' and povp.sort_order=0', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$rows2=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
				if ($rows2>1) {
					$fix_attribute_values=1;
					break;
				}
			}
		}
		if ($fix_attribute_values) {
			// attribute fixer
			$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_products_options', // FROM ...
				'language_id=\'0\'', // WHERE...
				'', // GROUP BY...
				'sort_order', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
			if ($rows) {
				while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
					// now load the related values
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('povp.products_options_id,povp.products_options_values_id', // SELECT ...
						'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov', // FROM ...
						'povp.products_options_id=\''.$row['products_options_id'].'\' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id=\'0\'', // WHERE...
						'', // GROUP BY...
						'povp.sort_order', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$counter=0;
					while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
						$where="products_options_id='".$row2['products_options_id']."' and products_options_values_id = ".$row2['products_options_values_id'];
						$updateArray=array(
							'sort_order'=>$counter
						);
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', $where, $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						$counter++;
					}
				}
			}
			// end attribute fixer
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_configuration_values', // FROM ...
			'page_uid="'.$multishop_page_uid.'"', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				if (isset($row['configuration_value']) and $row['configuration_value']!='') {
					$settings['LOCAL_MODULES'][$row['configuration_key']]=$row['configuration_value'];
				}
			}
		}
		// load local front-end module config eof
		// load global front-end module config
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_configuration', // FROM ...
			'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				if (isset($row['configuration_value'])) {
					$settings['GLOBAL_MODULES'][$row['configuration_key']]=$row['configuration_value'];
				}
			}
		}
		// load global front-end module config eof
		// merge global with local front-end module config
		foreach ($settings['GLOBAL_MODULES'] as $key=>$value) {
			if (isset($settings['LOCAL_MODULES'][$key])) {
				$settings[$key]=$settings['LOCAL_MODULES'][$key];
			} else {
				$settings[$key]=$value;
			}
		}
		// merge global with local front-end module config eof
		if ($settings['COUNTRY_ISO_NR']) {
			$country=mslib_fe::getCountryByIso($settings['COUNTRY_ISO_NR']);
			$settings['CURRENCY_ARRAY']=mslib_befe::loadCurrency($country['cn_currency_iso_nr']);
			switch ($settings['COUNTRY_ISO_NR']) {
				case '528':
				case '276':
					$settings['CURRENCY']='&#8364;';
					break;
				default:
					$settings['CURRENCY']=$settings['CURRENCY_ARRAY']['cu_symbol_left'];
					break;
			}
		}
		// check database
		$messages=array();
		$skip=0;
		$settings['update_database']=1;
		if ($settings['update_database']) {
			set_time_limit(86400);
			ignore_user_abort(true);
			require($this->DOCUMENT_ROOT_MS.'scripts/front_pages/includes/compare_database.php');
		} // check database eof
		// delete duplicates eof
		// load default vars for upgrade purposes eof
		if (!count($messages)) {
			$content=$this->pi_getLL('admin_label_nothing_updated_already_using_latest_version');
		} else {
			$content=addslashes(str_replace("\n", "", implode("<br />", $messages)));
		}
		return $content;
	}
	public function getMethodsByProduct($products_id) {
		if (is_numeric($products_id)) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_products_method_mappings', // FROM ...
				'products_id='.$products_id, // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$array=array();
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array[$row['type']][]=$row['method_id'];
				}
				return $array;
			}
		}
	}
	/**
	 * Gets information for an extension, eg. version and
	 * most-recently-edited-script
	 * @param    string        Path to local extension folder
	 * @param    string        Extension key
	 * @return    array        Information array (unless an error occured)
	 */
	public function getExtensionInfo($path, $extKey) {
		$file=$path.'/ext_emconf.php';
		if (@is_file($file)) {
			$_EXTKEY=$extKey;
			$EM_CONF=array();
			require($file);
			$eInfo=array();
			// Info from emconf:
			$eInfo['title']=$EM_CONF[$extKey]['title'];
			$eInfo['version']=$EM_CONF[$extKey]['version'];
			$eInfo['CGLcompliance']=$EM_CONF[$extKey]['CGLcompliance'];
			$eInfo['CGLcompliance_note']=$EM_CONF[$extKey]['CGLcompliance_note'];
			if (is_array($EM_CONF[$extKey]['constraints']) && is_array($EM_CONF[$extKey]['constraints']['depends'])) {
				$eInfo['TYPO3_version']=$EM_CONF[$extKey]['constraints']['depends']['typo3'];
			} else {
				$eInfo['TYPO3_version']=$EM_CONF[$extKey]['TYPO3_version'];
			}
			$filesHash=unserialize($EM_CONF[$extKey]['_md5_values_when_last_written']);
			$eInfo['manual']=@is_file($path.'/doc/manual.sxw');
			return $eInfo;
		} else {
			return 'ERROR: No emconf.php file: '.$file;
		}
	}
	public function canContainProducts($categories_id) {
		if (!is_numeric($categories_id)) {
			return false;
		}
		if (is_numeric($categories_id)) {
			$str=$GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
				'tx_multishop_categories', // FROM ...
				'parent_id=\''.$categories_id.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			if ($row['total']>0) {
				return false;
			} else {
				return true;
			}
		}
	}
	public function moveProduct($products_id, $target_categories_id, $old_categories_id='') {
		if (!is_numeric($products_id)) {
			return false;
		}
		if (!is_numeric($target_categories_id)) {
			return false;
		}
		if (is_numeric($products_id) and is_numeric($target_categories_id)) {
			if (is_numeric($old_categories_id)) {
				$qry=$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_categories', 'products_id='.$products_id.' and categories_id='.$old_categories_id);
			}
			$insertArray=array();
			$insertArray['categories_id']=$target_categories_id;
			$insertArray['products_id']=$products_id;
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($res) {
				// enable lock indicator if product is originally coming from the products importer
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('products_id', // SELECT ...
					'tx_multishop_products', // FROM ...
					'imported_product=1 and lock_imported_product=0 and products_id=\''.$products_id.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
					$updateArray=array();
					$updateArray['lock_imported_product']=1;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$this->post['pid'].'\'', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
				return true;
			}
		}
	}
	public function duplicateProduct($id_product, $target_categories_id) {
		if (!is_numeric($id_product)) {
			return false;
		}
		if (!is_numeric($target_categories_id)) {
			return false;
		}
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products', // FROM ...
			'products_id=\''.$id_product.'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)==0) {
			return false;
		} else {
			//insert into tx_multishop_products
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$product_arr_new=array();
			foreach ($row as $key_p=>$val_p) {
				if ($key_p!='products_id') {
					if ($key_p=='products_image' or $key_p=='products_image1' or $key_p=='products_image2' or $key_p=='products_image3' or $key_p=='products_image4') {
						if (!empty($val_p)) {
							$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
								'tx_multishop_products_description', // FROM ...
								'products_id=\''.$id_product.'\' and language_id=\''.$this->sys_language_uid.'\'', // WHERE...
								'', // GROUP BY...
								'', // ORDER BY...
								'' // LIMIT ...
							);
							$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
							$row_desc=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
							$file=mslib_befe::getImagePath($val_p, 'products', 'original');
							//echo $file;
							$imgtype=mslib_befe::exif_imagetype($file);
							if ($imgtype) {
								// valid image
								$ext=image_type_to_extension($imgtype, false);
								if ($ext) {
									$i=0;
									$filename=mslib_fe::rewritenamein($row_desc['products_name']).'.'.$ext;
									//echo $filename;
									$folder=mslib_befe::getImagePrefixFolder($filename);
									$array=explode(".", $filename);
									if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
										t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
									}
									$folder.='/';
									$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
									//echo $target;
									if (file_exists($target)) {
										do {
											$filename=mslib_fe::rewritenamein($row_desc['products_name']).($i>0 ? '-'.$i : '').'.'.$ext;
											$folder_name=mslib_befe::getImagePrefixFolder($filename);
											$array=explode(".", $filename);
											$folder=$folder_name;
											if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder)) {
												t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
											}
											$folder.='/';
											$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
											$i++;
											//echo $target . "<br/>";
										} while (file_exists($target));
									}
									if (copy($file, $target)) {
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
			if ($id_product_new) {
				// insert tx_multishop_products_description
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'tx_multishop_products_description', // FROM ...
					'products_id=\''.$id_product.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					$product_arr_new=$row;
					$product_arr_new['products_id']=$id_product_new;
					if (strpos($product_arr_new['products_name'], '(copy')===false) {
						$product_arr_new['products_name'].=' (copy '.$id_product_new.')';
					} else {
						if (strpos($product_arr_new['products_name'], '(copy '.$id_product.')')!==false) {
							$product_arr_new['products_name']=str_replace('(copy '.$id_product.')', ' (copy '.$id_product_new.')', $product_arr_new['products_name']);
						} else {
							$product_arr_new['products_name']=str_replace('(copy)', ' (copy '.$id_product_new.')', $product_arr_new['products_name']);
						}
					}
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $product_arr_new);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
				// insert tx_multishop_products_attributes
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'tx_multishop_products_attributes', // FROM ...
					'products_id=\''.$id_product.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_arr_new=$row;
						$product_arr_new['products_id']=$id_product_new;
						unset($product_arr_new['products_attributes_id']); //primary key
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes', $product_arr_new);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				// insert tx_multishop_specials
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'tx_multishop_specials', // FROM ...
					'products_id=\''.$id_product.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_arr_new=$row;
						$product_arr_new['products_id']=$id_product_new;
						unset($product_arr_new['specials_id']); //primary key
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials', $product_arr_new);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
				// insert tx_multishop_products_to_relative_products
				$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'tx_multishop_products_to_relative_products', // FROM ...
					'products_id=\''.$id_product.'\' or relative_product_id = \''.$id_product.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
					while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
						$product_arr_new=$row;
						if ($product_arr_new['products_id']==$id_product) {
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
					'categories_id'=>$target_categories_id,
					'sort_order'=>time()
				);
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if ($res) {
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						mslib_befe::convertProductToFlat($id_product);
					}
				} else {
					return false;
				}
			}
		}
	}
	/**
	 * This function creates a zip file
	 * Credits goes to Kraft Bernhard (kraftb@think-open.at)
	 * @param    string        File/Directory to pack
	 * @param    string        Zip-file target directory
	 * @param    string        Zip-file target name
	 * @return    array        Files packed
	 */
	public function zipPack($file, $targetFile) {
		if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
			return array();
		}
		$zip=$GLOBALS['TYPO3_CONF_VARS']['BE']['zip_path'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['zip_path'] : 'zip';
		$path=dirname($file);
		$file=basename($file);
		chdir($path);
		$cmd=$zip.' -r -9 '.escapeshellarg($targetFile).' '.escapeshellarg($file);
		exec($cmd, $list, $ret);
		if ($ret) {
			return array();
		}
		$result=mslib_befe::getFileResult($list, 'zip');
		return $result;
	}
	/**
	 * This function unpacks a zip file
	 * Credits goes to Kraft Bernhard (kraftb@think-open.at)
	 * @param    string        File to unpack
	 * @return    array        Files unpacked
	 */
	public function zipUnpack($file, $overwrite=0) {
		if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
			return array();
		}
		$path=dirname($file);
		chdir($path);
		// Unzip without overwriting existing files
		$unzip=$GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path'] : 'unzip';
		if ($overwrite) {
			$cmd=$unzip.' -o '.escapeshellarg($file);
		} else {
			$cmd=$unzip.' -n '.escapeshellarg($file);
		}
		exec($cmd, $list, $ret);
		if ($ret) {
			//return array();
		}
		$result=mslib_befe::getFileResult($list, 'unzip');
		return $result;
	}
	/**
	 * This method helps filtering the output of the various archive binaries to get a clean php array
	 * Credits goes to Kraft Bernhard (kraftb@think-open.at)
	 * @param    array        The output of the executed archive binary
	 * @param    string        The type/configuration for which to parse the output
	 * @return    array        A clean list of the filenames returned by the binary
	 */
	public function getFileResult($list, $type='zip') {
		$sc=$GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['split_char'];
		$pre=intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['pre_lines']);
		$post_lines=intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['post_lines']);
		$pos=intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['file_pos']);
		// Removing trailing lines
		while ($post_lines--) {
			array_pop($list);
		}
		// Only last lines
		if ($pre===-1) {
			$fl=array();
			while ($line=trim(array_pop($list))) {
				array_unshift($fl, $line);
			}
			$list=$fl;
		}
		// Remove preceeding lines
		if ($pre>0) {
			while ($pre--) {
				array_shift($list);
			}
		}
		$fl=array();
		foreach ($list as $file) {
			$parts=preg_split('/'.preg_quote($sc).'+/', $file);
			$fl[]=trim($parts[$pos]);
		}
		return $fl;
	}
	public function updateOrderStatus($orders_id, $orders_status, $mail_customer=0) {
		if (!is_numeric($orders_id)) {
			return false;
		}
		// dynamic variables
		$order=mslib_fe::getOrder($orders_id);
		$billing_address='';
		$delivery_address='';
		$full_customer_name=$order['billing_first_name'];
		if ($order['billing_middle_name']) {
			$full_customer_name.=' '.$order['billing_middle_name'];
		}
		if ($order['billing_last_name']) {
			$full_customer_name.=' '.$order['billing_last_name'];
		}
		$delivery_full_customer_name=$order['delivery_first_name'];
		if ($order['delivery_middle_name']) {
			$delivery_full_customer_name.=' '.$order['delivery_middle_name'];
		}
		if ($order['delivery_last_name']) {
			$delivery_full_customer_name.=' '.$order['delivery_last_name'];
		}
		$full_customer_name=preg_replace('/\s+/', ' ', $full_customer_name);
		$delivery_full_customer_name=preg_replace('/\s+/', ' ', $delivery_full_customer_name);
		if (!$order['delivery_address'] or !$order['delivery_city']) {
			$order['delivery_company']=$order['billing_company'];
			$order['delivery_address']=$order['billing_address'];
			$order['delivery_street_name']=$order['billing_street_name'];
			$order['delivery_address_number']=$order['billing_address_number'];
			$order['delivery_address_ext']=$order['billing_address_ext'];
			$order['delivery_building']=$order['delivery_building'];
			$order['delivery_zip']=$order['billing_zip'];
			$order['delivery_city']=$order['billing_city'];
			$order['delivery_telephone']=$order['billing_telephone'];
			$order['delivery_mobile']=$order['billing_mobile'];
		}
		if ($order['delivery_company']) {
			$delivery_address=$order['delivery_company']."<br />";
		}
		if ($delivery_full_customer_name) {
			$delivery_address.=$delivery_full_customer_name."<br />";
		}
		if ($order['delivery_address']) {
			$delivery_address.=$order['delivery_address']."<br />";
		}
		if ($order['delivery_building']) {
			$delivery_address.=$order['delivery_building']."<br />";
		}
		if ($order['delivery_zip'] and $order['delivery_city']) {
			$delivery_address.=$order['delivery_zip']." ".$order['delivery_city'];
		}
		//if ($order['delivery_telephone']) 		$delivery_address.=ucfirst($this->pi_getLL('telephone')).': '.$order['delivery_telephone']."<br />";
		//if ($order['delivery_mobile']) 			$delivery_address.=ucfirst($this->pi_getLL('mobile')).': '.$order['delivery_mobile']."<br />";
		if ($order['billing_company']) {
			$billing_address=$order['billing_company']."<br />";
		}
		if ($full_customer_name) {
			$billing_address.=$full_customer_name."<br />";
		}
		if ($order['billing_address']) {
			$billing_address.=$order['billing_address']."<br />";
		}
		if ($order['billing_zip'] and $order['billing_city']) {
			$billing_address.=$order['billing_zip']." ".$order['billing_city'];
		}
		//if ($order['billing_telephone']) 		$billing_address.=ucfirst($this->pi_getLL('telephone')).': '.$order['billing_telephone']."<br />";
		//if ($order['billing_mobile']) 			$billing_address.=ucfirst($this->pi_getLL('mobile')).': '.$order['billing_mobile']."<br />";
		$array1=array();
		$array2=array();
		$array1[]='###DELIVERY_FIRST_NAME###';
		$array2[]=$order['delivery_first_name'];
		$array1[]='###DELIVERY_LAST_NAME###';
		$array2[]=preg_replace('/\s+/', ' ', $order['delivery_middle_name'].' '.$order['delivery_last_name']);
		$array1[]='###BILLING_FIRST_NAME###';
		$array2[]=$order['billing_first_name'];
		$array1[]='###BILLING_LAST_NAME###';
		$array2[]=preg_replace('/\s+/', ' ', $order['billing_middle_name'].' '.$order['billing_last_name']);
		$array1[]='###BILLING_TELEPHONE###';
		$array2[]=$order['billing_telephone'];
		$array1[]='###DELIVERY_TELEPHONE###';
		$array2[]=$order['delivery_telephone'];
		$array1[]='###BILLING_MOBILE###';
		$array2[]=$order['billing_mobile'];
		$array1[]='###DELIVERY_MOBILE###';
		$array2[]=$order['delivery_mobile'];
		$array1[]='###FULL_NAME###';
		$array2[]=$full_customer_name;
		$array1[]='###DELIVERY_FULL_NAME###';
		$array2[]=$delivery_full_customer_name;
		$array1[]='###BILLING_NAME###';
		$array2[]=$order['billing_name'];
		$array1[]='###BILLING_EMAIL###';
		$array2[]=$order['billing_email'];
		$array1[]='###DELIVERY_EMAIL###';
		$array2[]=$order['delivery_email'];
		$array1[]='###DELIVERY_NAME###';
		$array2[]=$order['delivery_name'];
		$array1[]='###CUSTOMER_EMAIL###';
		$array2[]=$order['billing_email'];
		$array1[]='###STORE_NAME###';
		$array2[]=$this->ms['MODULES']['STORE_NAME'];
		$array1[]='###TOTAL_AMOUNT###';
		$array2[]=mslib_fe::amount2Cents($order['total_amount']);
		$ORDER_DETAILS=mslib_fe::printOrderDetailsTable($order, 'email');
		$array1[]='###ORDER_DETAILS###';
		$array2[]=$ORDER_DETAILS;
		$array1[]='###BILLING_ADDRESS###';
		$array2[]=$billing_address;
		$array1[]='###DELIVERY_ADDRESS###';
		$array2[]=$delivery_address;
		$array1[]='###CUSTOMER_ID###';
		$array2[]=$order['customer_id'];
		$array1[]='###SHIPPING_METHOD###';
		$array2[]=$order['shipping_method_label'];
		$array1[]='###PAYMENT_METHOD###';
		$array2[]=$order['payment_method_label'];
		$invoice=mslib_fe::getOrderInvoice($order['orders_id'], 0);
		$invoice_id='';
		$invoice_link='';
		if (is_array($invoice)) {
			$invoice_id=$invoice['invoice_id'];
			$invoice_link='<a href="'.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]='.$invoice['hash']).'">'.$invoice['invoice_id'].'</a>';
		}
		$array1[]='###INVOICE_NUMBER###';
		$array2[]=$invoice_id;
		$array1[]='###INVOICE_LINK###';
		$array2[]=$invoice_link;
		$time=$order['crdate'];
		$long_date=strftime($this->pi_getLL('full_date_format'), $time);
		$array1[]='###ORDER_DATE_LONG###'; // ie woensdag 23 juni, 2010
		$array2[]=$long_date;
		// backwards compatibility
		$array1[]='###LONG_DATE###'; // ie woensdag 23 juni, 2010
		$array2[]=$long_date;
		$time=time();
		$long_date=strftime($this->pi_getLL('full_date_format'), $time);
		$array1[]='###CURRENT_DATE_LONG###'; // ie woensdag 23 juni, 2010
		$array2[]=$long_date;
		$array1[]='###STORE_NAME###';
		$array2[]=$this->ms['MODULES']['STORE_NAME'];
		$array1[]='###TOTAL_AMOUNT###';
		$array2[]=mslib_fe::amount2Cents($order['total_amount']);
		$array1[]='###PROPOSAL_NUMBER###';
		$array2[]=$order['orders_id'];
		$array1[]='###ORDER_NUMBER###';
		$array2[]=$order['orders_id'];
		$array1[]='###ORDER_LINK###';
		$array2[]='';
		$array1[]='###CUSTOMER_ID###';
		$array2[]=$order['customer_id'];
		$array1[]='###MESSAGE###';
		$array2[]=$this->post['comments'];
		$array1[]='###OLD_ORDER_STATUS###';
		$array2[]=mslib_fe::getOrderStatusName($order['status'], $order['language_id']);
		$array1[]='###ORDER_STATUS###';
		$array2[]=mslib_fe::getOrderStatusName($orders_status, $order['language_id']);
		$array1[]='###EXPECTED_DELIVERY_DATE###';
		$array2[]=strftime("%x", $order['expected_delivery_date']);
		$array1[]='###TRACK_AND_TRACE_CODE###';
		$array2[]=$order['track_and_trace_code'];
		// dynamic variablese eof
		if ($this->post['comments']) {
			$this->post['comments']=str_replace($array1, $array2, $this->post['comments']);
		}
		$status_last_modified=time();
		$updateArray=array();
		$updateArray['orders_id']=$order['orders_id'];
		$updateArray['old_value']=$order['status'];
		$updateArray['comments']=$this->post['comments'];
		$updateArray['customer_notified']=$mail_customer;
		$updateArray['crdate']=$status_last_modified;
		$updateArray['new_value']=$orders_status;
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status_history', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		$updateArray=array();
		$updateArray['status']=$orders_status;
		$updateArray['status_last_modified']=$status_last_modified;
		$order['old_status']=$order['status'];
		$order['status']=$orders_status;
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$orders_id.'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		//send e-mail
		if ($mail_customer) {
			$subject=$this->ms['MODULES']['STORE_NAME'];
			$message=$this->post['comments'];
			if ($orders_status) {
				$orders_status_name=mslib_fe::getOrderStatusName($orders_status, 0);
				$keys=array();
				$keys[]='email_order_status_changed_'.t3lib_div::strtolower($orders_status_name);
				$keys[]='email_order_status_changed';
				foreach ($keys as $key) {
					//$page=mslib_fe::getCMScontent($key,$GLOBALS['TSFE']->sys_language_uid);
					$page=mslib_fe::getCMScontent($key, $order['language_id']);
					if ($page[0]) {
						if ($page[0]['content']) {
							$page[0]['content']=str_replace($array1, $array2, $page[0]['content']);
						}
						if ($page[0]['name']) {
							$page[0]['name']=str_replace($array1, $array2, $page[0]['name']);
						}
						$user=array();
						$user['email']=$order['billing_email'];
						$user['name']=$order['billing_name'];
						if ($user['email']) {
							mslib_fe::mailUser($user, $page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME']);
						}
						break;
					}
				}
			}
		}
	}
	public function updateOrderProductStatus($orders_id, $order_product_id, $orders_status) {
		if (!is_numeric($orders_id)) {
			return false;
		}
		if (!is_numeric($order_product_id)) {
			return false;
		}
		$updateArray=array();
		$updateArray['status']=$orders_status;
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'orders_id=\''.$orders_id.'\' and orders_products_id = \''.$order_product_id.'\'', $updateArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	public function getHashedPassword($password) {
		$objPHPass=null;
		if (t3lib_extMgm::isLoaded('t3sec_saltedpw')) {
			require_once(t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php');
			if (tx_t3secsaltedpw_div::isUsageEnabled()) {
				require_once(t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php');
				$objPHPass=t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
			}
		}
		if (!$objPHPass && t3lib_extMgm::isLoaded('saltedpasswords')) {
			if (tx_saltedpasswords_div::isUsageEnabled()) {
				$objPHPass=t3lib_div::makeInstance(tx_saltedpasswords_div::getDefaultSaltingHashingMethod());
			}
		}
		if ($objPHPass) {
			$password=$objPHPass->getHashedPassword($password);
		} else if (t3lib_extMgm::isLoaded('kb_md5fepw')) { //if kb_md5fepw is installed, crypt password
			$password=md5($password);
		}
		return $password;
	}
	public function generateRandomPassword($length=10, $string='', $type='pronounceable') {
		if (!$type and $string) {
			$type='pronounceable';
		} elseif (!$type) {
			$type='unpronounceable';
		}
		require_once(t3lib_extMgm::extPath('multishop').'res/Password.php');
		$suite=new Text_Password();
		switch ($type) {
			case 'pronounceable':
				$password=$suite->create($length, 'pronounceable', $string);
				$password.=rand(10, 99);
				break;
			case 'unpronounceable':
				$password=$suite->create($length, 'unpronounceable', $string.'0123456789!@#$%^&*(');
				break;
			case 'shuffle':
			default:
				$password=$suite->createFromLogin($string, 'shuffle');
				$password.=rand(10, 99);
				break;
		}
		return $password;
	}
	public function storeProductsKeywordSearch($keyword, $negative_results=0) {
		$insertArray=array();
		$insertArray['keyword']=$keyword;
		$insertArray['ip_address']=$this->REMOTE_ADDR;
		$insertArray['crdate']=time();
		$insertArray['negative_results']=$negative_results;
		$insertArray['http_host']=$this->HTTP_HOST;
		$insertArray['page_uid']=$this->shop_pid;
		if ($GLOBALS['TSFE']->fe_user->user['uid']) {
			$insertArray['customer_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
		}
		if ($this->get['categories_id']) {
			$insertArray['categories_id']=$this->get['categories_id'];
		}
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_search_log', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	public function storeCustomerCartContent($content, $customer_id='', $is_checkout=0) {
		if (!$customer_id && $GLOBALS['TSFE']->fe_user->user['uid']) {
			$customer_id=$GLOBALS['TSFE']->fe_user->user['uid'];
		}
		$insertArray=array();
		$insertArray['contents']=serialize($content);
		$insertArray['customer_id']=$customer_id;
		$insertArray['is_checkout']=$is_checkout;
		$insertArray['crdate']=time();
		$insertArray['session_id']=$GLOBALS['TSFE']->fe_user->id;
		$insertArray['ip_address']=$this->REMOTE_ADDR;
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_cart_contents', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	public function storeNotificationMessage($title='', $message='', $customer_id=0, $message_type='generic') {
		if (!$customer_id && $GLOBALS['TSFE']->fe_user->user['uid']) {
			$customer_id=$GLOBALS['TSFE']->fe_user->user['uid'];
		}
		$insertArray=array();
		$insertArray['title']=$title;
		$insertArray['message']=$message;
		$insertArray['message_type']=$message_type;
		$insertArray['unread']=1;
		$insertArray['crdate']=time();
		$insertArray['customer_id']=$customer_id;
		$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_notification', $insertArray);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	// get tree
	public function getPageTree($pid='0', $cates=array(), $times=0, $include_itself=0) {
		if ($include_itself) {
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,keywords,description', 'pages', 'hidden = 0 and deleted = 0 and (uid='.$pid.')', '');
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$cates[$row['uid']]=array(
					'|'.str_repeat("--", $times-1)."-[ ".$row['title'],
					$row
				);
				$cates=mslib_befe::getPageTree($row['uid'], $cates, $times);
			}
			$times++;
		}
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,keywords,description', 'pages', 'hidden = 0 and deleted = 0 and pid='.$pid.'', '');
		$times++;
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$cates[$row['uid']]=array(
				'|'.str_repeat("--", $times-1)."-[ ".$row['title'],
				$row
			);
			$cates=mslib_befe::getPageTree($row['uid'], $cates, $times);
		}
		$times--;
		return $cates;
	}
	// convert the string of URL to <a href="URL">URL</a>
	public function str2href($str) {
		$str=preg_replace('/\s(\w+:\/\/)(\S+)/', ' <a href="\\1\\2" target="_blank">\\1\\2</a>', $str);
	}
	public function str_highlight($text, $needle='', $options=null, $highlight=null) {
		$STR_HIGHLIGHT_SIMPLE=1;
		$STR_HIGHLIGHT_WHOLEWD=2;
		$STR_HIGHLIGHT_CASESENS=0;
		$STR_HIGHLIGHT_STRIPLINKS=8;
		if (!$needle) {
			return $text;
		}
		// Default highlighting
		if ($highlight===null) {
			$highlight='<strong class="highlight">\1</strong>';
		}
		// Select pattern to use
		if ($options&$STR_HIGHLIGHT_SIMPLE) {
			$pattern='#(%s)#';
		} else {
			$pattern='#(?!<.*?)(%s)(?![^<>]*?>)#';
			$sl_pattern='#<a\s(?:.*?)>(%s)</a>#';
		}
		// Case sensitivity
		/*
			if ($options ^ $STR_HIGHLIGHT_CASESENS) {

				$pattern .= 'i';
				$sl_pattern .= 'i';
			}
		*/
		$pattern.='i';
		$sl_pattern.='i';
		$needle=(array)$needle;
		foreach ($needle as $needle_s) {
			$needle_s=preg_quote($needle_s);
			// Escape needle with optional whole word check
			if ($options&$STR_HIGHLIGHT_WHOLEWD) {
				$needle_s='\b'.$needle_s.'\b';
			}
			// Strip links
			if ($options&$STR_HIGHLIGHT_STRIPLINKS) {
				$sl_regex=sprintf($sl_pattern, $needle_s);
				$text=preg_replace($sl_regex, '\1', $text);
			}
			$regex=sprintf($pattern, $needle_s);
			$text=preg_replace($regex, $highlight, $text);
		}
		return $text;
	}
	public function cacheLite($action='', $key, $timeout='', $serialized=0, $content='') {
		$options=array(
			'caching'=>true,
			'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime'=>$timeout
		);
		$Cache_Lite=new Cache_Lite($options);
//		$Cache_Lite = t3lib_div::makeInstance('Cache_Lite');
		$string=md5($key);
		switch ($action) {
			case 'get':
				// get cache
				$content=$Cache_Lite->get($string);
				if ($serialized) {
					$content=unserialize($content);
				}
				return $content;
				break;
			case 'save':
				// save cache
				if ($serialized) {
					$content=serialize($content);
				}
				$Cache_Lite->save($content, $string);
				break;
			case 'delete':
				// removes the cache
				switch ($string) {
					case 'clear_all':
						if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'] or $this->conf['cacheConfiguration']) {
							if ($this->DOCUMENT_ROOT and !strstr($this->DOCUMENT_ROOT, '..')) {
								$command="rm -rf ".$this->DOCUMENT_ROOT."uploads/tx_multishop/tmp/cache/*";
								exec($command);
							}
						}
						break;
					default:
						$Cache_Lite->remove($string);
						break;
				}
				break;
		}
	}
	public function loadLanguages() {
		$this->linkVars=$GLOBALS['TSFE']->linkVars;
		$useSysLanguageTitle=trim($this->conf['useSysLanguageTitle']) ? trim($this->conf['useSysLanguageTitle']) : 0;
		$useIsoLanguageCountryCode=trim($this->conf['useIsoLanguageCountryCode']) ? trim($this->conf['useIsoLanguageCountryCode']) : 0;
		$useIsoLanguageCountryCode=$useSysLanguageTitle ? 0 : $useIsoLanguageCountryCode;
		$useSelfLanguageTitle=trim($this->conf['useSelfLanguageTitle']) ? trim($this->conf['useSelfLanguageTitle']) : 0;
		$useSelfLanguageTitle=($useSysLanguageTitle || $useIsoLanguageCountryCode) ? 0 : $useSelfLanguageTitle;
		$tableA='sys_language';
		$tableB='static_languages';
		$languagesUidsList=trim($this->cObj->data['tx_srlanguagemenu_languages']) ? trim($this->cObj->data['tx_srlanguagemenu_languages']) : trim($this->conf['languagesUidsList']);
		$languages=array();
		$languagesLabels=array();
		// Set default language
		$defaultLanguageISOCode=trim($this->conf['defaultLanguageISOCode']) ? t3lib_div::strtoupper(trim($this->conf['defaultLanguageISOCode'])) : 'EN';
		$this->ms['MODULES']['COUNTRY_ISO_NR']=trim($this->conf['defaultCountryISOCode']) ? t3lib_div::strtoupper(trim($this->conf['defaultCountryISOCode'])) : '';
		$languages[]=t3lib_div::strtolower($defaultLanguageISOCode).($this->ms['MODULES']['COUNTRY_ISO_NR'] ? '_'.$this->ms['MODULES']['COUNTRY_ISO_NR'] : '');
		$this->languagesUids[]='0';
		// Get the language codes and labels for the languages set in the plugin list
		$selectFields=$tableA.'.uid, '.$tableA.'.title, '.$tableB.'.*';
		$table=$tableA.' LEFT JOIN '.$tableB.' ON '.$tableA.'.flag='.$tableB.'.cn_iso_2';
		// Ignore IN clause if language list is empty. This means that all languages found in the sys_language table will be used
		if (!empty($languagesUidsList)) {
			$whereClause=$tableA.'.uid IN ('.$languagesUidsList.') ';
		} else {
			$whereClause='1=1 ';
		}
		$whereClause.=$this->cObj->enableFields($tableA);
		$whereClause.=$this->cObj->enableFields($tableB);
		//$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $table, $whereClause);
		// If $languagesUidsList is not empty, the languages will be sorted in the order it specifies
		$languagesUidsArray=t3lib_div::trimExplode(',', $languagesUidsList, 1);
		$index=0;
		$str="select * from sys_language where hidden=0 order by title";
		$res=$GLOBALS['TYPO3_DB']->sql_query($str);
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$key++;
			$languages[$key]=$row['uid'];
			$languagesLabels[$key]['key']=$row['uid'];
			$languagesLabels[$key]['flag']=$row['flag'];
			if ($row['flag']) {
				if ($this->cookie['multishop_admin_language']==$row['uid']) {
					$this->cookie['multishop_admin_language']=$row['flag'];
				}
			}
			$languagesLabels[$key]['value']=$row['title'];
			$this->languages[$key]['value']=$row['title'];
			$this->languagesUids[$key]=$row['uid'];
		}
	}
	public function array_flatten($a, $f=array()) {
		if (!$a || !is_array($a)) {
			return '';
		}
		foreach ($a as $k=>$v) {
			if (is_array($v)) {
				$f=mslib_befe::array_flatten($v, $f);
			} else {
				$f[$k]=$v;
			}
		}
		return $f;
	}
	public function loginAsUser($uid, $section='') {
		if (!is_numeric($uid)) {
			return false;
		}
		$user=mslib_fe::getUser($uid);
		if ($user['uid']) {
			// auto login the user
			$loginData=array(
				'uname'=>$user['username'],
				//usernmae
				'uident'=>$user['password'],
				//password
				'status'=>'login'
			);
			$GLOBALS['TSFE']->fe_user->checkPid=0; //do not use a particular pid
			$info=$GLOBALS['TSFE']->fe_user->getAuthInfoArray();
			$user=$GLOBALS['TSFE']->fe_user->fetchUserRecord($info['db_user'], $loginData['uname']);
			$GLOBALS['TSFE']->fe_user->createUserSession($user);
			// auto login the user
			$redirect_url=$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid);
			//hook to let other plugins further manipulate the redirect link
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loginAsUserRedirectLinkPreProc'])) {
				$params=array(
					'user'=>$user,
					'redirect_url'=>&$redirect_url,
					'section'=>&$section
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loginAsUserRedirectLinkPreProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}
			if ($redirect_url) {
				header("Location: ".$redirect_url);
			}
			exit();
		}
	}
	public function ifExists($value='', $table, $field='', $additional_where=array()) {
		if ($table) {
			$filter=array();
			if ($field and $value) {
				$filter[]=$field.'="'.addslashes($value).'"';
			}
			if (count($additional_where)) {
				foreach ($additional_where as $item) {
					$filter[]=$item;
				}
			}
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('1', // SELECT ...
				$table, // FROM ...
				((is_array($filter) && count($filter)) ? implode(' AND ', $filter) : ''), // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'1' // LIMIT ...
			);
			if ($this->msDebug) {
				return $query;
			}
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				return true;
			}
		}
	}
	public function getCount($value='', $table, $field='', $additional_where=array()) {
		if ($table) {
			$queryArray=array();
			$queryArray['from']=$table;
			if ($value and $field) {
				$queryArray['where'][]=$field.'=\''.addslashes($value).'\'';
			}
			if (count($additional_where)) {
				foreach ($additional_where as $where) {
					if ($where) {
						$queryArray['where'][]=$where;
					}
				}
			}
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
				$queryArray['from'], // FROM ...
				((is_array($queryArray['where']) && count($queryArray['where'])) ? implode(' AND ', $queryArray['where']) : ''), // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			if ($this->msDebug) {
				return $query;
			}
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $row['total'];
		} else {
			return 0;
		}
	}
	public function getRecord($value='', $table, $field='', $additional_where=array()) {
		$queryArray=array();
		$queryArray['from']=$table;
		if (isset($value) and isset($field)) {
			$queryArray['where'][]=addslashes($field).'=\''.addslashes($value).'\'';
		}
		if (is_array($additional_where) && count($additional_where)) {
			foreach ($additional_where as $where) {
				if ($where) {
					$queryArray['where'][]=$where;
				}
			}
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			$queryArray['from'], // FROM ...
			((is_array($queryArray['where']) && count($queryArray['where'])) ? implode(' AND ', $queryArray['where']) : ''), // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		if ($this->msDebug) {
			return $query;
		}
		//error_log($query);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}
	}
	public function getRecords($value='', $table, $field='', $additional_where=array(), $groupBy='', $orderBy='', $limit='') {
		$queryArray=array();
		$queryArray['from']=$table;
		if (isset($value) and isset($field)) {
			$queryArray['where'][]=addslashes($field).'=\''.addslashes($value).'\'';
		}
		if (is_array($additional_where) && count($additional_where)) {
			foreach ($additional_where as $where) {
				if ($where) {
					$queryArray['where'][]=$where;
				}
			}
		}
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			$queryArray['from'], // FROM ...
			(is_array($queryArray['where'] && count($queryArray['where'])) ? implode(' AND ', $queryArray['where']) : ''), // WHERE...
			$groupBy, // GROUP BY...
			$orderBy, // ORDER BY...
			$limit // LIMIT ...
		);

		//echo $query;
		if ($this->msDebug) {
			return $query;
		}
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			$items=array();
			while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$items[]=$row;
			}
			return $items;
		}
	}
	public function updateImportedProductsLockedFields($products_id, $table, $updateArray) {
		$lockedFields=array();
		$lockedFields['tx_multishop_products'][]='products_price';
		$lockedFields['tx_multishop_products'][]='products_vat_rate';
		$lockedFields['tx_multishop_products'][]='products_name';
		$lockedFields['tx_multishop_products'][]='products_quantity';
		$lockedFields['tx_multishop_products'][]='products_model';
		$lockedFields['tx_multishop_products'][]='products_status';
		$lockedFields['tx_multishop_products_description'][]='products_name';
		$lockedFields['tx_multishop_products_description'][]='products_shortdescription';
		$lockedFields['tx_multishop_products_description'][]='products_description';
		$lockedFields['tx_multishop_products_to_categories'][]='categories_id';
		if (is_numeric($products_id) and $table) {
			// get fields that we need to take care of
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				$table, // FROM ...
				'products_id='.$products_id, // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				foreach ($lockedFields[$table] as $field_key) {
					if ($row[$field_key]!=$updateArray[$field_key]) {
						// add to locking table with original value
						$filter=array();
						$filter[]='products_id='.$row['products_id'];
						if (!mslib_befe::ifExists($field_key, 'tx_multishop_products_locked_fields', 'field_key', $filter)) {
							$insertArray=array();
							$insertArray['field_key']=$field_key;
							$insertArray['products_id']=$row['products_id'];
							$insertArray['crdate']=time();
							$insertArray['cruser_id']=$GLOBALS['TSFE']->fe_user->user['uid'];
							$insertArray['original_value']=$row[$field_key];
							$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_locked_fields', $insertArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						}
					}
				}
			}
		}
	}
	public function getImportedProductsLockedFields($products_id) {
		if (is_numeric($products_id)) {
			$query=$GLOBALS['TYPO3_DB']->SELECTquery('field_key', // SELECT ...
				'tx_multishop_products_locked_fields', // FROM ...
				'products_id='.$products_id, // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				$array=array();
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$array[]=$row['field_key'];
				}
				return $array;
			}
		}
	}
	public function readPageAccess($id, $perms_clause) {
		if ((string)$id!='') {
			$id=intval($id);
			if (!$id) {
				if ($GLOBALS['BE_USER']->isAdmin()) {
					$path='/';
					$pageinfo['_thePath']=$path;
					return $pageinfo;
				}
			} else {
				$pageinfo=t3lib_BEfunc::getRecord('pages', $id, '*', ($perms_clause ? ' AND '.$perms_clause : ''));
				if ($pageinfo['uid'] && $GLOBALS['BE_USER']->isInWebMount($id, $perms_clause)) {
					t3lib_BEfunc::workspaceOL('pages', $pageinfo);
					t3lib_BEfunc::fixVersioningPid('pages', $pageinfo);
					list($pageinfo['_thePath'], $pageinfo['_thePathFull'])=t3lib_BEfunc::getRecordPath(intval($pageinfo['uid']), $perms_clause, 15, 1000);
					return $pageinfo;
				}
			}
		}
		return false;
	}
	public function isValidDate($date) {
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
			if (checkdate($matches[2], $matches[3], $matches[1])) {
				return true;
			}
		}
		return false;
	}
	public function isValidDateTime($dateTime) {
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
			if (checkdate($matches[2], $matches[3], $matches[1])) {
				return true;
			}
		}
		return false;
	}
	// utf-8 support
	public function strtoupper($value) {
		return $GLOBALS['LANG']->csConvObj->conv_case($GLOBALS['LANG']->charSet, $value, 'toUpper');
	}
	// utf-8 support
	public function strtolower($value) {
		return $GLOBALS['LANG']->csConvObj->conv_case($GLOBALS['LANG']->charSet, $value, 'toLower');
	}
	// utf-8 support
	public function strlen($value) {
		return $GLOBALS['LANG']->csConvObj->strlen($GLOBALS['LANG']->charSet, $value);
	}
	// weight list for shipping costs page
	public function createSelectboxWeightsList($selected='', $start_value='', $weights_list=array()) {
		if (!count($weights_list)) {
			// default weights list
			$weights_list=array(
				'0.02'=>'0,02',
				'0.05'=>'0,05',
				'0.1'=>'0,1',
				'0.25'=>'0,25',
				'0.35'=>'0,35',
				'0.5'=>'0,5',
				'0.75'=>'0,75',
				'1'=>'1',
				'1.25'=>'1,25',
				'1.5'=>'1,5',
				'1.75'=>'1,75',
				'2'=>'2',
				'2.25'=>'2,25',
				'2.5'=>'2,5',
				'2.75'=>'2,75',
				'3'=>'3',
				'3.25'=>'3,25',
				'3.5'=>'3,5',
				'3.75'=>'3,75',
				'4'=>'4',
				'4.25'=>'4,25',
				'4.5'=>'4,5',
				'4.75'=>'4,75',
				'5'=>'5',
				'6'=>'6',
				'7'=>'7',
				'8'=>'8',
				'9'=>'9',
				'10'=>'10',
				'15'=>'15',
				'20'=>'20',
				'25'=>'25',
				'30'=>'30',
				'35'=>'35',
				'40'=>'40',
				'45'=>'45',
				'50'=>'50',
				'100'=>'100',
				'101'=>'End'
			);
		}
		$selectbox_options=array();
		foreach ($weights_list as $weight_value=>$weight_label) {
			if (!empty($start_value) && $start_value<101) {
				if ($weight_value>=$start_value) {
					if (empty($selected)) {
						if ($weight_value==101) {
							$selectbox_options[]='<option value="'.$weight_value.'" selected="selected">'.$weight_label.'</option>';
						} else {
							$selectbox_options[]='<option value="'.$weight_value.'">'.$weight_label.'</option>';
						}
					} else {
						if ($selected==$weight_value) {
							$selectbox_options[]='<option value="'.$weight_value.'" selected="selected">'.$weight_label.'</option>';
						} else {
							$selectbox_options[]='<option value="'.$weight_value.'">'.$weight_label.'</option>';
						}
					}
				}
			} else {
				if (empty($selected)) {
					if ($weight_value==101) {
						$selectbox_options[]='<option value="'.$weight_value.'" selected="selected">'.$weight_label.'</option>';
					} else {
						$selectbox_options[]='<option value="'.$weight_value.'">'.$weight_label.'</option>';
					}
				} else {
					if ($selected==$weight_value) {
						$selectbox_options[]='<option value="'.$weight_value.'" selected="selected">'.$weight_label.'</option>';
					} else {
						$selectbox_options[]='<option value="'.$weight_value.'">'.$weight_label.'</option>';
					}
				}
			}
		}
		$selectbox_str=implode("\n", $selectbox_options);
		return $selectbox_str;
	}
	function getStaticInfoTablesTaxesPluginVersion() {
		if (t3lib_extMgm::isLoaded('static_info_tables_taxes', 0)) {
			$plugin_ver=$GLOBALS['EM_CONF']['static_info_tables_taxes']['version'];
			$version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger($plugin_ver) : t3lib_div::int_from_ver($plugin_ver);
			$t3version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : t3lib_div::int_from_ver(TYPO3_version);
			if ($version>=6000000) {
			} else {
			}
		}
	}
	function printInvoiceOrderDetailsTable($order, $invoice_number, $prefix='') {
		$orders_tax_data=$order['orders_tax_data'];
		$tmpcontent='<table class="msadmin_border" width="100%" border="0" cellspacing="0" cellpadding="2" id="orderDetailsPDFInvoice">';
		$tmpcontent.='<tr>';
		$tmpcontent.='<td colspan="5" style="padding-bottom:10px"><strong>'.$this->pi_getLL('invoice_number').': '.$invoice_number.'</strong></td>';
		$tmpcontent.='</tr>';
		$tmpcontent.='<tr style="background-color:#000; color:#fff;">
					  <td align="right" class="cell_qty" style="padding-right:5px; width:5%">'.ucfirst($this->pi_getLL('qty')).'</td>
					  <td align="center" class="cell_products_name" style="padding-right:5px; width:50%">'.$this->pi_getLL('products_name').'</td>';
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$tmpcontent.='<td align="right" class="cell_products_vat">'.$this->pi_getLL('vat').'</td>
						  <td align="right" class="cell_products_normal_price">'.$this->pi_getLL('normal_price').'</td>
						  <td align="right" class="cell_products_final_price" style="padding-right:5px; width:20%">'.$this->pi_getLL('final_price_inc_vat').'</td>';
		} else {
			$tmpcontent.='<td class="cell_products_normal_price align_right">'.$this->pi_getLL('normal_price').'</td>
						  <td class="cell_products_vat align_right">'.$this->pi_getLL('vat').'</td>
						  <td class="cell_products_final_price align_right" style="padding-right:5px; width:20%">'.$this->pi_getLL('final_price_ex_vat').'</td>';
		}
		$tmpcontent.='</tr>';
		$total_tax=0;
		$tr_type='even';
		$od_rows_count=0;
		foreach ($order['products'] as $product) {
			$od_rows_count++;
			if (!$tr_type or $tr_type=='even') {
				$tr_type='odd';
			} else {
				$tr_type='even';
			}
			$tmpcontent.='<tr class="'.$tr_type.'">';
			$tmpcontent.='<td align="right" class="cell_products_qty">'.number_format($product['qty']).'</td>';
			$product_tmp=mslib_fe::getProduct($product['products_id']);
			if ($this->ms['MODULES']['DISPLAY_PRODUCT_IMAGE_IN_ADMIN_PACKING_SLIP'] and $product_tmp['products_image']) {
				$tmpcontent.='<td align="left" class="cell_products_name"><strong>';
				$tmpcontent.='<img src="'.mslib_befe::getImagePath($product_tmp['products_image'], 'products', '50').'"> ';
				$tmpcontent.=$product['products_name'];
			} else {
				$tmpcontent.='<td align="left" class="cell_products_name" style="padding-left:10px"><strong>'.$product['products_name'];
			}
			if ($product['products_article_number']) {
				$tmpcontent.=' ('.$product['products_article_number'].')';
			}
			$tmpcontent.='</strong>';
			if ($product['products_model']) {
				$tmpcontent.='<br/>Model: '.$product['products_model'];
			}
			if (!empty($product['ean_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_ean').': '.$product['ean_code'];
			}
			if (!empty($product['sku_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_sku').': '.$product['sku_code'];
			}
			if (!empty($product['vendor_code'])) {
				$tmpcontent.='<br/>'.$this->pi_getLL('admin_label_vendor_code').': '.$product['vendor_code'];
			}
			$tmpcontent.='</td>';
			if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
				$tmpcontent.='<td align="right" class="cell_products_vat">'.str_replace('.00', '', number_format($product['products_tax'], 2)).'%</td>';
				$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$prefix.' '.mslib_fe::amount2Cents($product['final_price']+$product['products_tax_data']['total_tax'], 0).'</td>';
				$tmpcontent.='<td align="right" class="cell_products_final_price">'.$prefix.' '.mslib_fe::amount2Cents(($product['qty']*($product['final_price']+$product['products_tax_data']['total_tax'])), 0).'</td>';
			} else {
				$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$prefix.' '.mslib_fe::amount2Cents($product['final_price'], 0).'</td>';
				$tmpcontent.='<td align="right" class="cell_products_vat">'.str_replace('.00', '', number_format($product['products_tax'], 2)).'%</td>';
				$tmpcontent.='<td align="right" class="cell_products_final_price">'.$prefix.' '.mslib_fe::amount2Cents(($product['qty']*$product['final_price']), 0).'</td>';
			}
			$tmpcontent.='</tr>';
			// start for new page
			if ($od_rows_count%20==0) {
				$tmpcontent.='</table><table class="msadmin_border" width="100%" border="0" cellspacing="0" cellpadding="2" style="page-break-before: always; margin-top:200px">';
				$tmpcontent.='<tr>';
				$tmpcontent.='<td colspan="5" style="padding-bottom:10px"><strong>'.$this->pi_getLL('invoice_number').': '.$invoice_number.'</strong></td>';
				$tmpcontent.='</tr>';
				$tmpcontent.='<tr style="background-color:#000; color:#fff;">
					  <td align="right" class="cell_qty" style="padding-right:5px; width:5%">'.ucfirst($this->pi_getLL('qty')).'</td>
					  <td align="center" class="cell_products_name" style="padding-right:5px; width:50%">'.$this->pi_getLL('products_name').'</td>';
				if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
					$tmpcontent.='<td align="right" class="cell_products_vat">'.$this->pi_getLL('vat').'</td>
						  <td align="right" class="cell_products_normal_price">'.$this->pi_getLL('normal_price').'</td>
						  <td align="right" class="cell_products_final_price" style="padding-right:5px; width:20%">'.$this->pi_getLL('final_price_inc_vat').'</td>';
				} else {
					$tmpcontent.='<td class="cell_products_normal_price align_right">'.$this->pi_getLL('normal_price').'</td>
						  <td class="cell_products_vat align_right">'.$this->pi_getLL('vat').'</td>
						  <td class="cell_products_final_price align_right" style="padding-right:5px; width:20%">'.$this->pi_getLL('final_price_ex_vat').'</td>';
				}
				$tmpcontent.='</tr>';
			}
			if (count($product['attributes'])) {
				foreach ($product['attributes'] as $tmpkey=>$options) {
					if ($options['products_options_values']) {
						$od_rows_count++;
						$tmpcontent.='<tr class="'.$tr_type.'"><td>&nbsp;</td><td align="left" style="padding-left:10px">'.$options['products_options'].': '.$options['products_options_values'].'</td>';
						$cell_products_normal_price='';
						$cell_products_vat='';
						$cell_products_final_price='';
						if ($options['options_values_price']>0) {
							if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
								$attributes_price=$options['price_prefix'].$options['options_values_price']+$options['attributes_tax_data']['tax'];
								$total_attributes_price=$attributes_price*$product['qty'];
								$cell_products_normal_price=$prefix.' '.mslib_fe::amount2Cents(($attributes_price), 0);
								$cell_products_final_price=$prefix.' '.mslib_fe::amount2Cents(($total_attributes_price), 0);
							} else {
								$cell_products_normal_price=$prefix.' '.mslib_fe::amount2Cents(($options['price_prefix'].$options['options_values_price']), 0);
								$cell_products_final_price=$prefix.' '.mslib_fe::amount2Cents(($options['price_prefix'].$options['options_values_price'])*$product['qty'], 0);
							}
						}
						if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
							$tmpcontent.='<td align="right" class="cell_products_vat">'.$cell_products_vat.'</td>';
							$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$cell_products_normal_price.'</td>';
							$tmpcontent.='<td align="right" class="cell_products_final_price">'.$cell_products_final_price.'</td>';
						} else {
							$tmpcontent.='<td align="right" class="cell_products_normal_price">'.$cell_products_normal_price.'</td>';
							$tmpcontent.='<td align="right" class="cell_products_vat">'.$cell_products_vat.'</td>';
							$tmpcontent.='<td align="right" class="cell_products_final_price">'.$cell_products_final_price.'</td>';
						}
						$tmpcontent.='</tr>';
						// start for new page
						if ($od_rows_count%20==0) {
							$tmpcontent.='</table><table class="msadmin_border" width="100%" border="0" cellspacing="0" cellpadding="2" style="page-break-before: always; margin-top:200px">';
							$tmpcontent.='<tr>';
							$tmpcontent.='<td colspan="5" style="padding-bottom:10px"><strong>'.$this->pi_getLL('invoice_number').': '.$invoice_number.'</strong></td>';
							$tmpcontent.='</tr>';
							$tmpcontent.='<tr style="background-color:#000; color:#fff;">
							<td align="right" class="cell_qty" style="padding-right:5px; width:5%">'.ucfirst($this->pi_getLL('qty')).'</td>
							<td align="center" class="cell_products_name" style="padding-right:5px; width:50%">'.$this->pi_getLL('products_name').'</td>';
							if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
								$tmpcontent.='<td align="right" class="cell_products_vat">'.$this->pi_getLL('vat').'</td>
						  		<td align="right" class="cell_products_normal_price">'.$this->pi_getLL('normal_price').'</td>
						  		<td align="right" class="cell_products_final_price" style="padding-right:5px; width:20%">'.$this->pi_getLL('final_price_inc_vat').'</td>';
							} else {
								$tmpcontent.='<td class="cell_products_normal_price align_right">'.$this->pi_getLL('normal_price').'</td>
						  		<td class="cell_products_vat align_right">'.$this->pi_getLL('vat').'</td>
						  		<td class="cell_products_final_price align_right" style="padding-right:5px; width:20%">'.$this->pi_getLL('final_price_ex_vat').'</td>';
							}
							$tmpcontent.='</tr>';
						}
					}
				}
			}
			// count the vat
			if ($order['final_price'] and $order['products_tax']) {
				$item_tax=$order['qty']*($order['final_price']*$order['products_tax']/100);
				$total_tax=$total_tax+$item_tax;
			}
		}
		$colspan=5;
		$tmpcontent.='<tr>';
		$tmpcontent.='<td align="right" colspan="2">&nbsp;</td>';
		$tmpcontent.='<td align="right" colspan="3"><hr/></td>';
		$tmpcontent.='</tr>';
		$tmpcontent.='<tr><td align="right" colspan="'.$colspan.'">';
		$tmpcontent.='<div class="order_total">';
		$tmpcontent.='<table width="100%" cellpadding="2" cellspacing="2">';
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$tmpcontent.='<tr>
						<td align="right"><label>'.$this->pi_getLL('sub_total').'</label></td>
						<td align="right" width="70px"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['orders_tax_data']['sub_total'], 0).'</span></td>
					</tr>';
			$content_vat='<tr>
						<td align="right"><label>'.$this->pi_getLL('included_vat_amount').'</label></td>
						<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</span></td>
					</tr>';
			if ($order['shipping_method_costs']>0) {
				$content_shipping_costs='<tr>
							<td align="right"><label>'.$this->pi_getLL('shipping_costs').'</label></td>
							<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['shipping_method_costs']+$order['orders_tax_data']['shipping_tax'], 0).'</span></td>
						</tr>';
			}
			if ($order['payment_method_costs']>0) {
				$content_payment_costs='<tr>
							<td align="right"><label>'.$this->pi_getLL('payment_costs').'</label></td>
							<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['payment_method_costs']+$order['orders_tax_data']['payment_tax'], 0).'</span></td>
						</tr>';
			}
		} else {
			$tmpcontent.='<tr>
						<td align="right"><label>'.$this->pi_getLL('sub_total').'</label></td>
						<td align="right" width="70px"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['subtotal_amount'], 0).'</span></td>
					</tr>';
			$content_vat='<tr>
						<td align="right"><label>'.$this->pi_getLL('vat').'</label></td>
						<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($orders_tax_data['total_orders_tax'], 0).'</span></td>
					</tr>';
			if ($order['shipping_method_costs']>0) {
				$content_shipping_costs='<tr>
							<td align="right"><label>'.$this->pi_getLL('shipping_costs').'</label></td>
							<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['shipping_method_costs'], 0).'</span></td>
						</tr>';
			}
			if ($order['payment_method_costs']>0) {
				$content_payment_costs='<tr>
							<td align="right"><label>'.$this->pi_getLL('payment_costs').'</label></td>
							<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['payment_method_costs'], 0).'</span></td>
						</tr>';
			}
		}
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$tmpcontent.=$content_shipping_costs;
			$tmpcontent.=$content_payment_costs;
		} else {
			if ($order['orders_tax_data']['shipping_tax'] || $order['orders_tax_data']['payment_tax']) {
				$tmpcontent.=$content_shipping_costs;
				$tmpcontent.=$content_payment_costs;
				$tmpcontent.=$content_vat;
			} else {
				$tmpcontent.=$content_vat;
				$tmpcontent.=$content_shipping_costs;
				$tmpcontent.=$content_payment_costs;
			}
		}
		if ($order['discount']>0) {
			$tmpcontent.='<tr>
					<td align="right"><label>'.$this->pi_getLL('discount').'</label></td>
					<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($order['discount'], 0).'</span></td>
				</tr>';
		}
		$tmpcontent.='<tr>
				<td align="right"><label><strong>'.$this->pi_getLL('total').'</strong></label></td>
				<td align="right"><span class="order_total_value">'.$prefix.' '.mslib_fe::amount2Cents($orders_tax_data['grand_total'], 0).'</span></td>
			</tr>';
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$tmpcontent.=$content_vat;
		}
		$tmpcontent.='</table>';
		$tmpcontent.='</div>';
		$tmpcontent.='</td></tr></table>';
		return $tmpcontent;
	}
	function strstr_array( $haystack, $needle ) {
		if ( !is_array( $haystack ) ) {
			return false;
		}
		foreach ( $haystack as $element ) {
			if ( strstr( $element, $needle ) ) {
				return $element;
			}
		}
	}
	function stristr_array( $haystack, $needle ) {
		if ( !is_array( $haystack ) ) {
			return false;
		}
		foreach ( $haystack as $element ) {
			if ( stristr( $element, $needle ) ) {
				return $element;
			}
		}
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_befe.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_befe.php"]);
}
?>