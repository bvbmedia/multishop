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
    public static function isSerializedString($string) {
        return (@unserialize($string) !== false);
    }
    /* this method resizes the thumbnail images for the category
	example input:
	/var/www/vhosts/multishop.com/httpdocs/upload/tx_multishop/images/categories/original/my,
	my-photo.jpg,
	PATH_site.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey)
	example output: my-photo.jpg
	*/
    public function loadConfiguration($multishop_page_uid = '') {
        if (!$multishop_page_uid or $multishop_page_uid == $this->shop_pid) {
            static $settings;
            if (is_array($settings)) {
                // the settings are already loaded before so lets return them.
                return $settings;
            }
            // first check if we already loaded the configuration before
        }
        $settings = array();
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_configuration_values', // FROM ...
                'page_uid=\'' . $multishop_page_uid . '\'', // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                if (isset($row['configuration_value']) and $row['configuration_value'] != '') {
                    $settings['LOCAL_MODULES'][$row['configuration_key']] = $row['configuration_value'];
                }
            }
        }
        // load local front-end module config eof
        // load global front-end module config
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_configuration', // FROM ...
                '', // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                if (isset($row['configuration_value'])) {
                    $settings['GLOBAL_MODULES'][$row['configuration_key']] = $row['configuration_value'];
                }
            }
        }
        // load global front-end module config eof
        // merge global with local front-end module config
        if (is_array($settings['GLOBAL_MODULES']) && count($settings['GLOBAL_MODULES'])) {
            foreach ($settings['GLOBAL_MODULES'] as $key => $value) {
                if (isset($settings['LOCAL_MODULES'][$key])) {
                    $settings[$key] = $settings['LOCAL_MODULES'][$key];
                } else {
                    $settings[$key] = $value;
                }
            }
        }
        // merge global with local front-end module config eof
        if ($this->tta_shop_info['cn_iso_nr']) {
            // pass the ISO number of the store country from tt address record to Multishop
            $settings['COUNTRY_ISO_NR'] = $this->tta_shop_info['cn_iso_nr'];
        }
        if ($settings['COUNTRY_ISO_NR']) {
            $country = mslib_fe::getCountryByIso($settings['COUNTRY_ISO_NR']);
            $settings['CURRENCY_ARRAY'] = mslib_befe::loadCurrency($country['cn_currency_iso_nr']);
            // if default currency is not set then define it to the store country currency
            if (!$settings['DEFAULT_CURRENCY']) {
                $settings['DEFAULT_CURRENCY'] = $settings['CURRENCY_ARRAY']['cu_iso_3'];
            }
            switch ($settings['COUNTRY_ISO_NR']) {
                case '528':
                case '276':
                    $settings['CURRENCY'] = '&#8364;';
                    break;
                default:
                    $settings['CURRENCY'] = $settings['CURRENCY_ARRAY']['cu_symbol_left'];
                    break;
            }
        }
        if (!$this->cookie['selected_currency']) {
            $this->cookie['selected_currency'] = $settings['DEFAULT_CURRENCY'];
            if (TYPO3_MODE == 'FE') {
                // add condition cause in TYPO3 4.7.4 the backend don't profile fe_user
                $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
                $GLOBALS['TSFE']->storeSessionData();
            }
        }
        if ($this->cookie['selected_currency']) {
            // load customer selected currency
            $settings['CUSTOMER_CURRENCY_ARRAY'] = mslib_befe::loadCurrency($this->cookie['selected_currency'], 'cu_iso_3');
            $settings['CUSTOMER_CURRENCY'] = $settings['CUSTOMER_CURRENCY_ARRAY']['cu_symbol_left'];
        }
        //hook to let other plugins further manipulate the settings
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loadConfiguration'])) {
            $params = array(
                    'settings' => &$settings,
                    'this' => &$this
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loadConfiguration'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        return $settings;
    }
    public function loadCurrency($value, $field = 'cu_iso_nr') {
        if ($value) {
            //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery=1;
            $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', ' static_currencies', $field . '=\'' . addslashes($value) . '\'', '');
            //echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery."\n";
            //die();
            return $data[0];
        }
    }
    /* this method returns a relative path plus the inserted filename
	example input: 'my-photo.jpg','products',100
	example output: upload/tx_multishop/images/products/100/my/my-photo.jpg
	*/
    public function resizeCategoryImage($original_path, $filename, $module_path, $run_in_background = 0) {
        if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] = 75;
        }
        if ($filename) {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImagePreProc'])) {
                $params = array(
                        'original_path' => &$original_path,
                        'filename' => &$filename,
                        'module_path' => &$module_path,
                        'run_in_background' => &$run_in_background
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImagePreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImage'])) {
                $params = array(
                        'original_path' => $original_path,
                        'filename' => &$filename,
                        'module_path' => $module_path,
                        'run_in_background' => $run_in_background
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImage'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            } else {
                if ($run_in_background) {
                    $suffix_exec_param = ' &> /dev/null & ';
                }
                $commands = array();
                $params = '';
                if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5'] == 'im6') {
                    $params .= '-auto-orient -strip';
                }
                $imgtype = mslib_befe::exif_imagetype($original_path);
                if ($imgtype) {
                    // valid image
                    $ext = image_type_to_extension($imgtype, false);
                    if ($ext) {
                        if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG']) {
                            switch ($ext) {
                                case 'png':
                                    // IMAGE IS PNG, BUT SOMETIMES JPEG IS REDUCING THE FILESIZE. LETS TRY
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('identify', ' -verbose "' . $original_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    $info = shell_exec($command);
                                    if (strstr($info, 'Alpha:')) {
                                        // THIS IMAGE HAS A TRANSPARANT BACKGROUND SO WE MAY NOT CONVERT IT
                                        break;
                                    }
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.jpg';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.jpg';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                                //case 'jpeg':
                                case 'gif':
                                    break;
                                default:
                                    // IMAGE IS NOT PNG. MAYBE CONVERTING IT TO PNG REDUCES THE FILESIZE. LETS TRY
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.png';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.png';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                } else {
                    return false;
                }
                $maxwidth = $this->ms['category_image_formats']['normal']['width'];
                $maxheight = $this->ms['category_image_formats']['normal']['height'];
                $folder = mslib_befe::getImagePrefixFolder($filename);
                $dirs = array();
                $dirs[] = PATH_site . $this->ms['image_paths']['categories']['normal'] . '/' . $folder;
                foreach ($dirs as $dir) {
                    if (!is_dir($dir)) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($dir);
                    }
                }
                $target = PATH_site . $this->ms['image_paths']['categories']['normal'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                if ($this->ms['MODULES']['CATEGORY_IMAGE_SHAPED_CORNERS'] and file_exists($GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite')) {
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthWest ' . $module_path . 'templates/images/curves/lb.png "' . $target . '" "' . $target . '"';
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthEast ' . $module_path . 'templates/images/curves/rb.png "' . $target . '" "' . $target . '"';
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthWest ' . $module_path . 'templates/images/curves/lo.png "' . $target . '" "' . $target . '"';
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthEast ' . $module_path . 'templates/images/curves/ro.png "' . $target . '" "' . $target . '"';
                }
//				print_r($commands);
//				die();
                if (count($commands)) {
                    // background running is not working on all boxes well, so we reverted it
                    //				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
                    //				\TYPO3\CMS\Core\Utility\CommandUtility::exec($final_command);
                    foreach ($commands as $command) {
                        exec($command);
                    }
                }
            }
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImagePostProc'])) {
                $params = array(
                        'original_path' => $original_path,
                        'folder' => &$folder,
                        'filename' => &$filename,
                        'target' => $target,
                        'module_path' => $module_path,
                        'commands' => $commands
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeCategoryImagePostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            return $filename;
        }
    }
    /* this method returns a substracted prefix folder.
	example input: my-photo.jpg
	example output: my
	*/
    public function exif_imagetype($filename) {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($filename);
        } else if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
            return $type;
        }
        return false;
    }
    public function getImagePrefixFolder($filename) {
        $array = explode(".", $filename);
        $folder_name = substr(preg_replace("/\\.+?$/is", "", trim($array[0])), 0, 3);
        $folder_name = preg_replace("/\\-$/", "", $folder_name);
        return mslib_befe::strtolower($folder_name);
    }
    public function strtolower($value) {
        $csConvObj = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->csConvObj : $GLOBALS['TSFE']->csConvObj);
        $charset = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->charSet : $GLOBALS['TSFE']->metaCharset);
        return $csConvObj->conv_case($charset, $value, 'toLower');
    }
    public function resizeManufacturerImage($original_path, $filename, $module_path, $run_in_background = 0) {
        if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] = 75;
        }
        if ($filename) {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImagePreProc'])) {
                $params = array(
                        'original_path' => &$original_path,
                        'filename' => &$filename,
                        'module_path' => &$module_path,
                        'run_in_background' => &$run_in_background
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImagePreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImage'])) {
                $params = array(
                        'original_path' => $original_path,
                        'filename' => &$filename,
                        'module_path' => $module_path,
                        'run_in_background' => $run_in_background
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImage'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            } else {
                if ($run_in_background) {
                    $suffix_exec_param = ' &> /dev/null & ';
                }
                $commands = array();
                $params = '';
                if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5'] == 'im6') {
                    $params .= '-auto-orient -strip';
                }
                $imgtype = mslib_befe::exif_imagetype($original_path);
                if ($imgtype) {
                    // valid image
                    $ext = image_type_to_extension($imgtype, false);
                    if ($ext) {
                        if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG']) {
                            switch ($ext) {
                                case 'png':
                                    // IMAGE IS PNG, BUT SOMETIMES JPEG IS REDUCING THE FILESIZE. LETS TRY
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('identify', ' -verbose "' . $original_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    $info = shell_exec($command);
                                    if (strstr($info, 'Alpha:')) {
                                        // THIS IMAGE HAS A TRANSPARANT BACKGROUND SO WE MAY NOT CONVERT IT
                                        break;
                                    }
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.jpg';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.jpg';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                                //case 'jpeg':
                                case 'gif':
                                    break;
                                default:
                                    // IMAGE IS NOT PNG. MAYBE CONVERTING IT TO PNG REDUCES THE FILESIZE. LETS TRY
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.png';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.png';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                } else {
                    return false;
                }
                $maxwidth = $this->ms['manufacturer_image_formats']['enlarged']['width'];
                $maxheight = $this->ms['manufacturer_image_formats']['enlarged']['height'];
                $folder = mslib_befe::getImagePrefixFolder($filename);
                $dirs = array();
                $dirs[] = PATH_site . $this->ms['image_paths']['manufacturers']['normal'] . '/' . $folder;
                foreach ($dirs as $dir) {
                    if (!is_dir($dir)) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($dir);
                    }
                }
                $target = PATH_site . $this->ms['image_paths']['manufacturers']['normal'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                if ($this->ms['MODULES']['CATEGORY_IMAGE_SHAPED_CORNERS'] and file_exists($GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite')) {
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthWest ' . $module_path . 'templates/images/curves/lb.png "' . $target . '" "' . $target . '"';
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthEast ' . $module_path . 'templates/images/curves/rb.png "' . $target . '" "' . $target . '"';
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthWest ' . $module_path . 'templates/images/curves/lo.png "' . $target . '" "' . $target . '"';
                    $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthEast ' . $module_path . 'templates/images/curves/ro.png "' . $target . '" "' . $target . '"';
                }
                if (count($commands)) {
                    // background running is not working on all boxes well, so we reverted it
                    //				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
                    //				\TYPO3\CMS\Core\Utility\CommandUtility::exec($final_command);
                    foreach ($commands as $command) {
                        exec($command);
                    }
                }
            }
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImagePostProc'])) {
                $params = array(
                        'original_path' => $original_path,
                        'folder' => &$folder,
                        'filename' => &$filename,
                        'target' => $target,
                        'module_path' => $module_path,
                        'commands' => $commands
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeManufacturerImagePostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            return $filename;
        }
    }
    public function cropImage($filesrc, $filesrc_original, $thumb_size, $coords_x, $coords_y, $coords_w, $coords_h, $image_type = 'products') {
        switch ($image_type) {
            case 'manufacturers':
                $format = explode("x", $this->ms['MODULES']['MANUFACTURER_IMAGE_SIZE_NORMAL']);
                break;
            case 'categories':
                $format = explode("x", $this->ms['MODULES']['CATEGORY_IMAGE_SIZE_NORMAL']);
                break;
            case 'products':
            default:
                $format = explode("x", $this->ms['MODULES']['PRODUCT_IMAGE_SIZE_' . mslib_befe::strtoupper($thumb_size)]);
                break;
        }
        $targ_w = (($coords_w < $format[0]) ? $coords_w : $format[0]); //$coords_w;
        $targ_h = (($coords_h < $format[1]) ? $coords_h : $format[1]); //$coords_h;
        $jpeg_quality = 90;
        switch (exif_imagetype($filesrc_original)) {
            case IMAGETYPE_GIF:
                $img_r = imagecreatefromgif($filesrc_original);
                break;
            case IMAGETYPE_PNG:
                $img_r = imagecreatefrompng($filesrc_original);
                break;
            case IMAGETYPE_JPEG:
            default:
                $img_r = imagecreatefromjpeg($filesrc_original);
                break;
        }
        $dst_r = imagecreatetruecolor($targ_w, $targ_h);
        imagecopyresampled($dst_r, $img_r, 0, 0, $coords_x, $coords_y, $targ_w, $targ_h, $coords_w, $coords_h);
        imagejpeg($dst_r, $filesrc, $jpeg_quality);
    }
    public function strtoupper($value) {
        $csConvObj = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->csConvObj : $GLOBALS['TSFE']->csConvObj);
        $charset = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->charSet : $GLOBALS['TSFE']->metaCharset);
        return $csConvObj->conv_case($charset, $value, 'toUpper');
    }
    public function countProducts($page_uid) {
        if (!is_numeric($page_uid)) {
            return false;
        }
        $qry = $GLOBALS['TYPO3_DB']->SELECTquery('products_id', // SELECT ...
                'tx_multishop_products_to_categories', // FROM ...
                'page_uid=\'' . $page_uid . '\' and is_deepest=1', // WHERE...
                'products_id', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($qry);
        $row['total'] = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function countOrders($page_uid) {
        if (!is_numeric($page_uid)) {
            return false;
        }
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_orders', 'page_uid=\'' . $page_uid . '\'', '');
        $row = $data[0];
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function countCustomerAddresses($page_uid) {
        if (!is_numeric($page_uid)) {
            return false;
        }
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tt_address', 'pid=\'' . $page_uid . '\'', '');
        $row = $data[0];
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function countCustomers($page_uid) {
        if (!is_numeric($page_uid)) {
            return false;
        }
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'fe_users', 'pid=\'' . $page_uid . '\'', '');
        $row = $data[0];
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function countCategories($page_uid) {
        if (!is_numeric($page_uid)) {
            return false;
        }
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_categories', 'page_uid=\'' . $page_uid . '\'', '');
        $row = $data[0];
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function countManufacturers($page_uid) {
//		$data	= $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total','tx_multishop_manufacturers','page_uid=\''.$page_uid.'\'','');
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_manufacturers', '', '');
        $row = $data[0];
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function countImportJobs($page_uid) {
        if (!is_numeric($page_uid)) {
            return false;
        }
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(1) as total', 'tx_multishop_import_jobs', 'page_uid=\'' . $page_uid . '\'', '');
        $row = $data[0];
        if (!isset($row['total'])) {
            $row['total'] = 0;
        }
        return $row['total'];
    }
    public function deleteAttributeValuesImage($file_name) {
        if ($file_name) {
            if (is_array($this->ms['image_paths']['attribute_values']) && count($this->ms['image_paths']['attribute_values'])) {
                foreach ($this->ms['image_paths']['attribute_values'] as $key => $value) {
                    $folder_name = mslib_befe::getImagePrefixFolder($file_name);
                    $path = PATH_site . $value . '/' . $folder_name . '/' . $file_name;
                    if (file_exists($path)) {
                        if (unlink($path)) {
                            $path = PATH_site . $value . '/' . $folder_name . '/' . $file_name;
                            @unlink($path);
                        }
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
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableProductPreHook'])) {
                $params = array(
                        'products_id' => &$products_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableProductPreHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            //hook to let other plugins further manipulate the create table query eol
            $updateArray = array();
            $updateArray['products_status'] = 1;
	        $updateArray['products_last_modified'] = time();
            $str = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $products_id . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($str);
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                // if the flat database module is enabled we have to sync the changes to the flat table
                mslib_befe::convertProductToFlat($products_id);
            }
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableProductPostHook'])) {
                $params = array(
                        'products_id' => &$products_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableProductPostHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            //hook to let other plugins further manipulate the create table query eol
        }
    }
    public function convertProductToFlat($products_id, $table_name = 'tx_multishop_products_flat') {
        if (!is_numeric($products_id)) {
            return false;
        }
	    if ($this->get['type'] == '2003' && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('multishop_flat_catalog')) {
		    // Force to update by disabling the cache when updating flat table
		    $this->ms['MODULES']['CACHE_FRONT_END'] = 0;
		    $this->ms['MODULES']['FORCE_CACHE_FRONT_END'] = 0;
	    }
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlat'])) {
            $params = array(
                    'status' => $status,
                    'table' => $table,
                    'id' => $id,
                    'this' => &$this,
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlat'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        } else {
            if ($table_name == 'tx_multishop_products_flat') {
                $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_flat', "products_id='" . $products_id . "'");
            }
            // retrieving the products record
            $select = array();
            $select[] = '*';
            $select[] = 's.status as special_status';
            $select[] = 'pd.language_id';
            $select[] = 'p2c.sort_order as p2c_sort_order';
            $select[] = 'p.staffel_price as staffel_price';
            $select[] = 'o.code as order_unit_code';
            $select[] = 'od.name as order_unit_name';
            // old v2 code
            // $select[]='tr.tx_rate as tax_rate';
            $select[] = 'IF(s.status, s.specials_new_products_price, p.products_price) as final_price';
            $select[] = 'p2c.sort_order';
            $from = array();
            // old v2 code
            $from[] = 'tx_multishop_products p left join tx_multishop_specials s on p.products_id = s.products_id left join tx_multishop_manufacturers m on p.manufacturers_id=m.manufacturers_id left join tx_multishop_order_units o on p.order_unit_id=o.id left join tx_multishop_order_units_description od on o.id=od.order_unit_id and od.language_id=0 ';
            $from[] = 'tx_multishop_products_description pd';
            $from[] = 'tx_multishop_products_to_categories p2c';
            $from[] = 'tx_multishop_categories c';
            $from[] = 'tx_multishop_categories_description cd';
            $where = array();
            $where[] = 'c.status=1';
            $where[] = 'p.products_status=1';
            $where[] = "p2c.products_id='" . $products_id . "'";
            $where[] = 'p2c.is_deepest=1';
            $where[] = 'p.products_id=pd.products_id';
            $where[] = 'p.products_id=p2c.products_id';
            $where[] = 'p2c.categories_id=c.categories_id';
            $where[] = 'p2c.categories_id=cd.categories_id';
            $where[] = 'pd.language_id=cd.language_id';
            $orderby = array();
            $orderby[] = 'pd.language_id';
            $query_elements = array();
            $query_elements['select'] =& $select;
            $query_elements['from'] =& $from;
            $query_elements['where'] =& $where;
            $query_elements['groupby'] =& $groupby;
            $query_elements['orderby'] =& $orderby;
            $query_elements['limit'] =& $limit;
            // custom hook that can be controlled by third-party plugin
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreFetchProductHook'])) {
                $params = array(
                        'products_id' => &$products_id,
                        'query_elements' => &$query_elements
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreFetchProductHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            // custom hook that can be controlled by third-party plugin eof
            $str = $GLOBALS['TYPO3_DB']->SELECTquery((is_array($select) ? implode(",", $select) : ''), // SELECT ...
                    (is_array($from) ? implode(",", $from) : ''), // FROM ...
                    (is_array($where) ? implode(" AND ", $where) : ''), // WHERE...
                    (is_array($groupby) ? implode(",", $groupby) : ''), // GROUP BY...
                    (is_array($orderby) ? implode(",", $orderby) : ''), // ORDER BY...
                    (is_array($limit) ? implode(",", $limit) : '') // LIMIT ...
            );
            if ($this->debug) {
                $logString = $str;
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
            }
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            $rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
            if ($this->conf['debugEnabled'] == '1') {
                $logString = 'convertProductToFlat query: ' . $str . '.';
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
            }
            if (!$rows) {
                $logString = 'convertProductToFlat fetch query returned zero results. Query: ' . $str;
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 3);
            }
            if ($rows) {
                while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                    // retrieving the categories path
                    $flat_product = array();
                    $flat_product['language_id'] = $row['language_id'];
                    $flat_product['products_id'] = $products_id;
                    $flat_product['products_condition'] = $row['products_condition'];
                    $flat_product['products_name'] = $row['products_name'];
                    $flat_product['products_model'] = $row['products_model'];
                    $flat_product['products_description'] = $row['products_description'];
                    $flat_product['products_shortdescription'] = $row['products_shortdescription'];
                    //$flat_product['products_extra_description']=$row['products_extra_description'];
                    $flat_product['products_quantity'] = $row['products_quantity'];
                    $flat_product['products_price'] = $row['products_price'];
                    $flat_product['products_viewed'] = $row['products_viewed'];
                    $flat_product['staffel_price'] = $row['staffel_price'];
                    $flat_product['delivery_time'] = $row['delivery_time'];
                    $flat_product['order_unit_id'] = $row['order_unit_id'];
                    $flat_product['order_unit_code'] = $row['order_unit_code'];
                    $flat_product['order_unit_name'] = $row['order_unit_name'];
                    if ($row['specials_new_products_price'] && $row['special_status'] > 0) {
                        $flat_product['final_price'] = $row['specials_new_products_price'];
                        $flat_product['sstatus'] = 1;
                    } else {
                        $flat_product['final_price'] = $row['products_price'];
                    }
                    // now we are going to define the price filter start value, so we can search very fast on it
                    $array = explode(";", $this->ms['MODULES']['PRICE_FILTER_BOX_STEPPINGS']);
                    if (is_array($array) && count($array)) {
                        $total = count($array);
                        $tel = 0;
                        foreach ($array as $item) {
                            $tel++;
                            $cols = explode("-", $item);
                            if ($flat_product['final_price'] <= $cols[1]) {
                                $flat_product['price_filter'] = $cols[0];
                                break;
                            }
                            if ($tel == $total) {
                                if ($flat_product['final_price'] > $cols[1]) {
                                    $flat_product['price_filter'] = $cols[1];
                                }
                            }
                        }
                    }
                    // now we are going to define the price filter start value, so we can search very fast on it eof
                    $flat_product['products_multiplication'] = $row['products_multiplication'];
                    $flat_product['minimum_quantity'] = $row['minimum_quantity'];
                    $flat_product['maximum_quantity'] = $row['maximum_quantity'];
                    $flat_product['products_date_available'] = $row['products_date_available'];
                    $flat_product['products_last_modified'] = $row['products_last_modified'];
                    $flat_product['tax_id'] = $row['tax_id'];
                    $flat_product['categories_id'] = $row['categories_id'];
                    $flat_product['categories_name'] = $row['categories_name'];
                    $flat_product['manufacturers_id'] = $row['manufacturers_id'];
                    $flat_product['manufacturers_name'] = $row['manufacturers_name'];
                    $flat_product['products_negative_keywords'] = $row['products_negative_keywords'];
                    $flat_product['products_meta_title'] = $row['products_meta_title'];
                    $flat_product['products_meta_description'] = $row['products_meta_description'];
                    $flat_product['products_meta_keywords'] = $row['products_meta_keywords'];
                    $flat_product['products_url'] = $row['products_url'];
                    $flat_product['vendor_code'] = $row['vendor_code'];
                    $flat_product['sku_code'] = $row['sku_code'];
                    $flat_product['ean_code'] = $row['ean_code'];
                    $flat_product['language_id'] = $row['language_id'];
                    if ($flat_product['categories_id']) {
                        // get all cats to generate multilevel fake url
                        $level = 0;
                        if ($row['page_uid']) {
                            $cats = mslib_fe::Crumbar($flat_product['categories_id'], '', array(), $row['page_uid']);
                        } else {
                            $cats = mslib_fe::Crumbar($flat_product['categories_id']);
                        }
                        if (is_array($cats) && count($cats)) {
                            $cats = array_reverse($cats);
                            $where = '';
                            if (count($cats) > 0) {
                                $i = 0;
                                foreach ($cats as $cat) {
                                    $flat_product['categories_id_' . $i] = $cat['id'];
                                    $flat_product['categories_name_' . $i] = $cat['name'];
                                    $i++;
                                }
                            }
                            // get all cats to generate multilevel fake url eof
                        }
                    }
                    for ($x = 0; $x < $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
                        $i = $x;
                        if ($i == 0) {
                            $i = '';
                        }
                        $flat_product['products_image' . $i] = $row['products_image' . $i];
                    }
                    if ($flat_product['products_image']) {
                        $flat_product['contains_image'] = 1;
                    } else {
                        $flat_product['contains_image'] = 0;
                    }
                    $flat_product['products_date_added'] = $row['products_date_added'];
                    $flat_product['products_weight'] = $row['products_weight'];
                    $flat_product['sort_order'] = $row['p2c_sort_order'];
                    $flat_product['product_capital_price'] = $row['product_capital_price'];
                    $flat_product['page_uid'] = $row['page_uid'];
                    $flat_product['starttime'] = $row['starttime'];
                    $flat_product['endtime'] = $row['endtime'];
                    $flat_product['ignore_stock_level'] = $row['ignore_stock_level'];
                    if ($this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS'] and is_array($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS']) && count($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'])) {
                        foreach ($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'] as $option_id => $array) {
                            if ($option_id) {
                                $option_values = mslib_fe::getProductsOptionValues($option_id, $flat_product['products_id']);
                                if (is_array($option_values)) {
                                    $values = array();
                                    foreach ($option_values as $option_value) {
                                        $values[] = $option_value['products_options_values_name'];
                                    }
                                    $flat_product[$array[0]] = implode('·', $values);
                                }
                            }
                        }
                    }
                    // custom hook that can be controlled by third-party plugin
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreInsert'])) {
                        $params = array(
                                'products_id' => &$products_id,
                                'flat_product' => &$flat_product,
                                'row' => &$row,
                                'table_name' => &$table_name
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatPreInsert'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    // custom hook that can be controlled by third-party plugin eof
                    $flat_product = mslib_befe::rmNullValuedKeys($flat_product);
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery($table_name, $flat_product);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    if (!$res) {
                        $logString = 'Query failed! Query: ' . $query;
                        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 3);
                    }
                    if ($this->debug) {
                        //error_log($query);
                        $logString = $query;
                        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', 0);
                    }
                    // custom hook that can be controlled by third-party plugin
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatProcInsert'])) {
                        $params = array(
                                'products_id' => &$products_id,
                                'flat_product' => &$flat_product,
                                'row' => &$row,
                                'table_name' => &$table_name
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['convertProductToFlatProcInsert'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    // custom hook that can be controlled by third-party plugin eof
                }
            }
            return $flat_product['products_id'];
        }
    }
    function rmNullValuedKeys($array) {
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if (is_null($array[$key])) {
                    $array[$key] = '';
                }
            }
            return $array;
        }
    }
    public function disableProduct($products_id) {
        if (!is_numeric($products_id)) {
            return false;
        }
        if (is_numeric($products_id)) {
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableProductPreHook'])) {
                $params = array(
                        'products_id' => &$products_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableProductPreHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            //hook to let other plugins further manipulate the create table query eol
            $updateArray = array();
            $updateArray['starttime'] = 0;
            $updateArray['endtime'] = 0;
            $updateArray['products_status'] = 0;
	        $updateArray['products_last_modified'] = time();
            $str = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $products_id . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($str);
            if ($this->ms['MODULES']['FLAT_DATABASE']) {
                $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_flat', 'products_id=' . $products_id);
            }
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableProductPostHook'])) {
                $params = array(
                        'products_id' => &$products_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableProductPostHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            //hook to let other plugins further manipulate the create table query eol
        }
    }
    public function enableCustomer($uid) {
        if (is_numeric($uid)) {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomer'])) {
                $params = array(
                        'uid' => &$uid,
                        'this' => &$this
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomer'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            } else {
                $disable = 0;
                //hook to let other plugins further manipulate the create table query
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomerPreHook'])) {
                    $params = array(
                            'uid' => $uid,
                            'disable' => &$disable
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['enableCustomerPreHook'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                $updateArray = array();
                $updateArray['disable'] = $disable;
	            $updateArray['last_updated_at'] = time();
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\'' . $uid . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
        }
    }
    public function disableCustomer($uid) {
        if (!is_numeric($uid)) {
            return false;
        }
        if (is_numeric($uid)) {
            $disable = 1;
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableCustomerPreHook'])) {
                $params = array(
                        'uid' => $uid,
                        'disable' => &$disable
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['disableCustomerPreHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            $updateArray = array();
            $updateArray['disable'] = $disable;
	        $updateArray['last_updated_at'] = time();
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\'' . $uid . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        }
    }
    public function deleteCustomer($uid) {
        if (!is_numeric($uid)) {
            return false;
        }
        if (is_numeric($uid)) {
            $deleted = 1;
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCustomerPreHook'])) {
                $params = array(
                        'uid' => $uid,
                        'deleted' => &$deleted
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCustomerPreHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            $updateArray['deleted'] = $deleted;
	        $updateArray['last_updated_at'] = time();
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=\'' . $uid . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        }
    }
    public function deleteOrder($orders_id) {
        if (!is_numeric($orders_id)) {
            return false;
        }
        if (is_numeric($orders_id)) {
            $updateArray = array();
            $updateArray['deleted'] = 1;
            $updateArray['orders_last_modified'] = time();
            $updateArray['deleted_by_uid'] = $GLOBALS['TSFE']->fe_user->user['uid'];
            $updateArray['deleted_tstamp'] = time();
            $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $orders_id . '\'', $updateArray);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        }
    }
    public function deleteCategory($categories_id) {
        if (!is_numeric($categories_id)) {
            return false;
        }
        if (is_numeric($categories_id)) {
            $str = $GLOBALS['TYPO3_DB']->SELECTquery('categories_id,categories_image,parent_id', // SELECT ...
                    'tx_multishop_categories', // FROM ...
                    "categories_id='" . $categories_id . "'", // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
                while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                    // remove any found products
                    $str = $GLOBALS['TYPO3_DB']->SELECTquery('p.products_id, p2c.is_deepest, p2c.crumbar_identifier', // SELECT ...
                            'tx_multishop_products p, tx_multishop_products_to_categories p2c', // FROM ...
                            "p2c.categories_id='" . $categories_id . "' and p.products_id=p2c.products_id", // WHERE...
                            '', // GROUP BY...
                            '', // ORDER BY...
                            '' // LIMIT ...
                    );
                    $products_query = $GLOBALS['TYPO3_DB']->sql_query($str);
                    if ($GLOBALS['TYPO3_DB']->sql_num_rows($products_query)) {
                        while (($product = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($products_query)) != false) {
                            if ($product['is_deepest'] > 0) {
                                mslib_befe::deleteProduct($product['products_id'], $categories_id, true);
                                if (!empty($product['crumbar_identifier'])) {
                                    //$qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_categories', "crumbar_identifier='" . $product['crumbar_identifier'] . "'");
                                }
                            }
                        }
                    }
                    // finally delete the category
                    $filename = $row['categories_image'];
                    $filter = array();
                    $filter[] = 'categories_image=\'' . addslashes($filename) . '\'';
                    $count = mslib_befe::getCount('', 'tx_multishop_categories', '', $filter);
                    if ($count < 2) {
                        // Only delete the file is we have found 1 category using it
                        mslib_befe::deleteCategoryImage($filename);
                    }
                    $tables = array();
                    $tables[] = 'tx_multishop_categories';
                    $tables[] = 'tx_multishop_categories_description';
                    //$tables[]='tx_multishop_products_to_categories';
                    if ($this->ms['MODULES']['ENABLE_CATEGORIES_TO_CATEGORIES']) {
                        $tables[] = 'tx_multishop_categories_to_categories';
                    }
                    foreach ($tables as $table) {
                        if ($table == 'tx_multishop_categories_description') {
                            $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, 'categories_id=' . $categories_id);
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        } else if ($table == 'tx_multishop_categories_to_categories') {
                            $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, 'categories_id=' . $categories_id . ' and page_uid=\'' . $this->shop_pid . '\'');
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                            // foreign
                            $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, 'foreign_categories_id=' . $categories_id . ' and foreign_page_uid=\'' . $this->shop_pid . '\'');
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        } else {
                            $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, 'categories_id=' . $categories_id . ' and page_uid=\'' . $this->shop_pid . '\'');
                            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        }
                    }
                    //
                    // first check if the category has subcategories to delete them as well
                    $str = $GLOBALS['TYPO3_DB']->SELECTquery('categories_id', // SELECT ...
                            'tx_multishop_categories', // FROM ...
                            "parent_id = '" . $row['categories_id'] . "'", // WHERE...
                            '', // GROUP BY...
                            '', // ORDER BY...
                            '' // LIMIT ...
                    );
                    $subcategories_query = $GLOBALS['TYPO3_DB']->sql_query($str);
                    if ($GLOBALS['TYPO3_DB']->sql_num_rows($subcategories_query)) {
                        while (($subcategory = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($subcategories_query)) != false) {
                            mslib_befe::deleteCategory($subcategory['categories_id']);
                        }
                    }
                }
            }
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCategoryPostHook'])) {
                $params = array(
                        'categories_id' => $categories_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteCategoryPostHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
        }
    }
    public function deleteProduct($products_id, $categories_id = '', $use_page_uid = false, $delete_all_cat_relation = false) {
        if (!is_numeric($products_id)) {
            return false;
        }
        if (is_numeric($products_id)) {
            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPreHook'])) {
                $params = array(
                        'products_id' => &$products_id,
                        'categories_id' => &$categories_id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPreHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            $productRow = mslib_fe::getProduct($products_id, '', '', 1, 1);
            if (is_numeric($products_id)) {
                if (is_numeric($categories_id)) {
                    if (!$delete_all_cat_relation) {
                        // just delete the relation to the category
                        $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_categories', 'products_id=' . $products_id . ' and categories_id=' . $categories_id);
                        // count if there are relations left
                        $str = $GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
                                'tx_multishop_products_to_categories', // FROM ...
                                "products_id='" . $products_id . "' and is_deepest=1", // WHERE...
                                '', // GROUP BY...
                                '', // ORDER BY...
                                '' // LIMIT ...
                        );
                        //var_dump($str);
                        //die();
                        //
                        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
                        if ($row['total']) {
                            // dont delete the product, cause there is another category that has relation
                            return true;
                        } else {
                            $definitive_delete = 1;
                        }
                    } else {
                        $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_categories', 'products_id=' . $products_id);
                        $definitive_delete = 1;
                    }
                } else {
                    $definitive_delete = 1;
                }
                if ($definitive_delete) {
                    for ($x = 0; $x < $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
                        $i = $x;
                        if ($i == 0) {
                            $i = '';
                        }
                        $filename = $productRow['products_image' . $i];
                        if ($filename) {
                            $orFilter = array();
                            for ($i = 0; $i < $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $i++) {
                                $s = '';
                                if ($i > 0) {
                                    $s = $i;
                                }
                                $orFilter[] = 'products_image' . $s . '=\'' . addslashes($filename) . '\'';
                            }
                            $filter = array();
                            $filter[] = '(' . implode(' OR ', $orFilter) . ')';
                            $count = mslib_befe::getCount('', 'tx_multishop_products', '', $filter);
                            if ($count < 2) {
                                // Only delete the file is we have found 1 product using it
                                mslib_befe::deleteProductImage($filename);
                            }
                        }
                    }
                    $tables = array();
                    $tables[] = 'tx_multishop_products';
                    if ($this->ms['MODULES']['FLAT_DATABASE']) {
                        $tables[] = 'tx_multishop_products_flat';
                    }
                    $tables[] = 'tx_multishop_products_description';
                    $tables[] = 'tx_multishop_products_to_categories';
                    $tables[] = 'tx_multishop_products_attributes';
                    $tables[] = 'tx_multishop_specials';
                    $tables[] = 'tx_multishop_undo_products';
                    $tables[] = 'tx_multishop_products_faq';
                    $tables[] = 'tx_multishop_products_to_extra_options';
                    foreach ($tables as $table) {
                        if ($use_page_uid) {
                            if ($table == 'tx_multishop_products' || $table == 'tx_multishop_products_description' || $table == 'tx_multishop_products_to_categories' || $table == 'tx_multishop_products_attributes') {
                                $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, 'products_id=' . $products_id . ' and page_uid=\'' . $this->shop_pid . '\'');
                            }
                        } else {
                            $query = $GLOBALS['TYPO3_DB']->DELETEquery($table, 'products_id=' . $products_id);
                        }
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                    $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_relative_products', 'products_id=' . $products_id . ' or relative_product_id=' . $products_id);
                    //hook to let other plugins further manipulate the create table query
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPostHook'])) {
                        $params = array(
                                'products_id' => $products_id
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteProductPostHook'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    return 1;
                }
            }
        }
    }
    // remove list, redundant functionality with getRecord method
    public function getCount($value = '', $table, $field = '', $additional_where = array()) {
        if ($table) {
            $queryArray = array();
            $queryArray['from'] = $table;
            if (isset($value) and isset($field) && $field != '') {
                $queryArray['where'][] = $field . '=\'' . addslashes($value) . '\'';
            }
            if ($additional_where && is_array($additional_where) && count($additional_where)) {
                foreach ($additional_where as $where) {
                    if ($where) {
                        $queryArray['where'][] = $where;
                    }
                }
            } elseif ($additional_where) {
                $queryArray['where'][] = $additional_where;
            }
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
                    $queryArray['from'], // FROM ...
                    ((is_array($queryArray['where']) && count($queryArray['where'])) ? implode(' AND ', $queryArray['where']) : ''), // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            if ($this->msDebug) {
                return $query;
            }
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            return $row['total'];
        } else {
            return 0;
        }
    }
    /*
	Some PHP compilations doesnt have the exif_imagetype function. In that case we provide our own alternative
	*/
    public function deleteProductImage($file_name) {
        if ($file_name) {
            if (is_array($this->ms['image_paths']['products']) && count($this->ms['image_paths']['products'])) {
                foreach ($this->ms['image_paths']['products'] as $key => $value) {
                    $folder_name = mslib_befe::getImagePrefixFolder($file_name);
                    $path = PATH_site . $value . '/' . $folder_name . '/' . $file_name;
                    if (file_exists($path)) {
                        if (unlink($path)) {
                            //@unlink($path);
                        }
                    }
                }
            }
        }
    }
    public function deleteCategoryImage($file_name) {
        if (is_array($this->ms['image_paths']['categories']) && count($this->ms['image_paths']['categories'])) {
            foreach ($this->ms['image_paths']['categories'] as $key => $value) {
                $path = PATH_site . $value . '/' . $file_name;
                if (@unlink($path)) {
                    return 1;
                }
            }
        }
    }
    // method for logging changes to specific tables
    public function deleteManufacturer($id) {
        if (is_numeric($id)) {
            $record = mslib_befe::getRecord($id, 'tx_multishop_manufacturers', 'manufacturers_id');
            if ($record['manufacturers_image']) {
                $filter = array();
                $filter[] = 'manufacturers_image=\'' . addslashes($record['manufacturers_image']) . '\'';
                $count = mslib_befe::getCount('', 'tx_multishop_manufacturers', '', $filter);
                if ($count < 2) {
                    // Only delete the file is we have found 1 category using it
                    mslib_befe::deleteManufacturerImage($record['manufacturers_image']);
                }
            }
            $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_manufacturers', 'manufacturers_id=' . $id);
            $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_manufacturers_cms', 'manufacturers_id=' . $id);
            $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_manufacturers_info', 'manufacturers_id=' . $id);

            //hook to let other plugins further manipulate the create table query
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteManufacturerPostHook'])) {
                $params = array(
                        'manufacturers_id' => $id
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['deleteManufacturerPostHook'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
        }
    }
    public function getRecord($value = '', $table, $field = '', $additional_where = array(), $select = '*', $groupBy = '', $orderBy = '', $limit = '') {
        $queryArray = array();
        $queryArray['from'] = $table;
        if (isset($value) && isset($field) && $field != '') {
            $queryArray['where'][] = addslashes($field) . '=\'' . addslashes($value) . '\'';
        }
        if (is_array($additional_where) && count($additional_where)) {
            foreach ($additional_where as $where) {
                if ($where) {
                    $queryArray['where'][] = $where;
                }
            }
        }
        if (is_array($select)) {
            $select = implode(', ', $select);
        }
        $query = $GLOBALS['TYPO3_DB']->SELECTquery($select, // SELECT ...
                $queryArray['from'], // FROM ...
                ((is_array($queryArray['where']) && count($queryArray['where'])) ? implode(' AND ', $queryArray['where']) : ''), // WHERE...
                (isset($groupBy) ? $groupBy : ''), // GROUP BY...
                (isset($orderBy) ? $orderBy : ''), // ORDER BY...
                $limit // LIMIT ...
        );
        if ($this->msDebug) {
            return $query;
        }
        //error_log($query);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        }
    }
    // function for saving the importer products images
    public function deleteManufacturerImage($file_name) {
        if (is_array($this->ms['image_paths']['manufacturers']) && count($this->ms['image_paths']['manufacturers'])) {
            foreach ($this->ms['image_paths']['manufacturers'] as $key => $value) {
                $path = PATH_site . $value . '/' . $file_name;
                if (unlink($path)) {
                    return 1;
                }
            }
        }
    }
    // method for adding a product to the flat table for maximum speed
    public function deltree($path) {
        if (is_dir($path)) {
            if (version_compare(PHP_VERSION, '5.0.0') < 0) {
                $entries = array();
                if (($handle = opendir($path)) != false) {
                    while (false !== ($file = readdir($handle))) {
                        $entries[] = $file;
                    }
                    closedir($handle);
                }
            } else {
                $entries = scandir($path);
                if ($entries === false) {
                    $entries = array(); // just in case scandir fail...
                }
            }
            if (is_array($entries) && count($entries)) {
                foreach ($entries as $entry) {
                    if ($entry != '.' && $entry != '..') {
                        mslib_befe::deltree($path . '/' . $entry);
                    }
                }
            }
            return rmdir($path);
        } else {
            return @unlink($path);
        }
    }
    // method for scanning subfolders and retrieve their associated files
    public function doesExist($table, $field, $value, $more = '') {
        $query = "SELECT * FROM " . $table . " WHERE " . $field . "='" . addslashes($value) . "' " . $more;
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
            return $row;
        }
    }
    public function convertConfiguration($ms) {
        // bit lame code, but this is for subdirectory hosted typo3 installations. Compatible for front and back-end.
        /*
		$paths=explode("/",$_SERVER['PHP_SELF']);
		if (count($paths) > 2) 			$prefix=$paths[1].'/';
		else							$prefix='';
		*/
        // not completely tested. reverting temporary
        $prefix = '';
        $ms['image_paths']['products']['50'] = $prefix . 'uploads/tx_multishop/images/products/50';
        $ms['image_paths']['products']['100'] = $prefix . 'uploads/tx_multishop/images/products/100';
        $ms['image_paths']['products']['200'] = $prefix . 'uploads/tx_multishop/images/products/200';
        $ms['image_paths']['products']['300'] = $prefix . 'uploads/tx_multishop/images/products/300';
        $ms['image_paths']['products']['original'] = $prefix . 'uploads/tx_multishop/images/products/original';
        $ms['image_paths']['products']['normal'] = $prefix . 'uploads/tx_multishop/images/products/normal';
        $ms['image_paths']['categories']['original'] = $prefix . 'uploads/tx_multishop/images/categories/original';
        $ms['image_paths']['categories']['normal'] = $prefix . 'uploads/tx_multishop/images/categories/normal';
        $ms['image_paths']['manufacturers']['original'] = $prefix . 'uploads/tx_multishop/images/manufacturers/original';
        $ms['image_paths']['manufacturers']['normal'] = $prefix . 'uploads/tx_multishop/images/manufacturers/normal';
        $ms['image_paths']['attribute_values']['original'] = $prefix . 'uploads/tx_multishop/images/attribute_values/original';
        $ms['image_paths']['attribute_values']['normal'] = $prefix . 'uploads/tx_multishop/images/attribute_values/normal';
        $ms['image_paths']['attribute_values']['small'] = $prefix . 'uploads/tx_multishop/images/attribute_values/small';
        $format = explode("x", $ms['MODULES']['CATEGORY_IMAGE_SIZE_NORMAL']);
        $ms['category_image_formats']['normal']['width'] = $format[0];
        $ms['category_image_formats']['normal']['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['ATTRIBUTE_VALUES_IMAGE_SIZE_NORMAL']);
        $ms['attribute_values_image_formats']['normal']['width'] = $format[0];
        $ms['attribute_values_image_formats']['normal']['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['ATTRIBUTE_VALUES_IMAGE_SIZE_SMALL']);
        $ms['attribute_values_image_formats']['small']['width'] = $format[0];
        $ms['attribute_values_image_formats']['small']['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_50']);
        $ms['product_image_formats'][50]['width'] = $format[0];
        $ms['product_image_formats'][50]['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_100']);
        $ms['product_image_formats'][100]['width'] = $format[0];
        $ms['product_image_formats'][100]['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_200']);
        $ms['product_image_formats'][200]['width'] = $format[0];
        $ms['product_image_formats'][200]['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_300']);
        $ms['product_image_formats'][300]['width'] = $format[0];
        $ms['product_image_formats'][300]['height'] = $format[1];
        $format = explode("x", $ms['MODULES']['PRODUCT_IMAGE_SIZE_ENLARGED']);
        $ms['product_image_formats']['enlarged']['width'] = $format[0];
        $ms['product_image_formats']['enlarged']['height'] = $format[1];
        return $ms;
    }
    public function addUndo($id, $table) {
        if (is_numeric($id) and $table) {
            $undo_tables = array();
            $undo_tables['tx_multishop_products'] = 'tx_multishop_undo_products';
            $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                    $table, // FROM ...
                    "products_id='" . $id . "'", // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
            $row['crdate'] = time();
            $query = $GLOBALS['TYPO3_DB']->INSERTquery($undo_tables[$table], $row);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            return $row;
        }
    }
    public function ms_implode($char, $array, $fix = '', $prefix, $addslashes = false) {
        $lem = array_keys($array);
        $char = htmlentities($char);
        $str = '';
        for ($i = 0; $i < sizeof($lem); $i++) {
            if ($addslashes) {
                if ($array[$lem[$i]]) {
                    $str .= $prefix . $fix . (($i == sizeof($lem) - 1) ? addslashes($array[$lem[$i]]) . $fix : addslashes($array[$lem[$i]]) . $fix . $char);
                }
            } else {
                if ($array[$lem[$i]]) {
                    $str .= $prefix . $fix . (($i == sizeof($lem) - 1) ? $array[$lem[$i]] . $fix : $array[$lem[$i]] . $fix . $char);
                }
            }
        }
        return $str;
    }
    public function saveImportedProductImages($products_id, $input, $item, $oldproduct = array(), $log_file = '') {
        if (!is_numeric($products_id)) {
            return false;
        }
        if (is_numeric($products_id)) {
            for ($x = 0; $x < $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
                // hidden filename that is retrieved from the ajax upload
                $i = $x;
                if ($i == 0) {
                    $i = '';
                }
                $colname = 'products_image' . $i;
                if (!$oldproduct[$colname]) {
                    if ($item[$colname]) {
                        $plaatje1 = $item[$colname];
                        $data = mslib_fe::file_get_contents($plaatje1, 0, 10);
                        if ($data) {
                            $plaatje1_name = $products_id . '-' . ($colname) . '-' . time();
                            $tmpfile = PATH_site . 'uploads/tx_multishop/tmp/' . $plaatje1_name;
                            file_put_contents($tmpfile, $data);
                            $plaatje1 = $tmpfile;
                            if (($extentie1 = mslib_befe::exif_imagetype($plaatje1)) && $plaatje1_name <> '') {
                                $extentie1 = image_type_to_extension($extentie1, false);
                                $ext = $extentie1;
                                $ix = 0;
                                $filename = mslib_fe::rewritenamein($item['products_name']) . '.' . $ext;
                                $folder = mslib_befe::getImagePrefixFolder($filename);
                                if (!is_dir(PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder)) {
                                    \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder);
                                }
                                $folder .= '/';
                                $target = PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder . $filename;
                                if (file_exists($target)) {
                                    do {
                                        $filename = mslib_fe::rewritenamein($item['products_name']) . ($ix > 0 ? '-' . $ix : '') . '.' . $ext;
                                        $folder = mslib_befe::getImagePrefixFolder($filename);
                                        if (!is_dir(PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder)) {
                                            \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder);
                                        }
                                        $folder .= '/';
                                        $target = PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder . $filename;
                                        $ix++;
                                    } while (file_exists($target));
                                }
                                // end
                                $products_image = $path . '/' . $naam;
                                // backup original
                                $target = PATH_site . $this->ms['image_paths']['products']['original'] . '/' . $folder . $filename;
                                copy($tmpfile, $target);
                                // backup original eof
                                $products_image_name = mslib_befe::resizeProductImage($target, $filename, PATH_site . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey), 1);
                                $item['img'][$i] = $products_image_name;
                                if ($log_file) {
                                    file_put_contents($log_file, 'Downloading product' . $i . ' image (' . $item[$colname] . ') succeeded and has been resized to ' . $item['img'][$i] . '.' . "\n", FILE_APPEND);
                                }
                            } else {
                                $item['img'][$i] = '';
                                if ($log_file) {
                                    $errorMessage='Downloading product' . $i . ' image (' . $item[$colname] . ') failed. Unknown filetype (tmp file: ' . $plaatje1 . ').';
                                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImageFailedErnoProc'])) {
                                        $conf = array(
                                                'errorMessage' => &$errorMessage
                                        );
                                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImageFailedErnoProc'] as $funcRef) {
                                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $conf, $this);
                                        }
                                    }
                                    file_put_contents($log_file,  $errorMessage . "\n", FILE_APPEND);
                                }
                            }
                        } else {
                            $item['img'][$i] = '';
                            if ($log_file) {
                                file_put_contents($log_file, 'Downloading product' . $i . ' image (' . $item[$colname] . ') failed.' . "\n", FILE_APPEND);
                            }
                        }
                        if ($tmpfile and file_exists($tmpfile)) {
                            @unlink($tmpfile);
                        }
                    } else {
                        $item['img'][$i] = '';
                    }
                } else {
                    $item['img'][$i] = '';
                }
            }
            if (count($item['img']) > 0) {
                $array = array();
                for ($x = 0; $x < $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
                    $i = $x;
                    if ($i == 0) {
                        $i = '';
                    }
                    $colname = 'products_image' . $i;
                    if ($item['img'][$i]) {
                        $array[$colname] = $item['img'][$i];
                    }
                    if ($oldproduct[$colname] && isset($item[$colname]) && $item[$colname] == '') {
                        // In database we have an image already existing, but in the uploaded the feed the image is set to empty so we have to reset it in the database
                        $array[$colname] = '';
                    }
                }
                if (count($array) > 0) {
                    if ($array['products_image']) {
                        $array['contains_image'] = 1;
                    } else {
                        $array['contains_image'] = 0;
                    }
	                $array['products_last_modified'] = time();
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=' . $products_id, $array);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
            }
        }
    }
    public function resizeProductImage($original_path, $filename, $module_path, $run_in_background = 0) {
        if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] = 75;
        }
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePreProc'])) {
            $params = array(
                    'original_path' => &$original_path,
                    'filename' => &$filename,
                    'module_path' => $module_path,
                    'run_in_background' => $run_in_background
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if (file_exists($original_path) && $filename) {
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImage'])) {
                $params = array(
                        'original_path' => $original_path,
                        'filename' => &$filename,
                        'module_path' => $module_path,
                        'run_in_background' => $run_in_background
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImage'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            } else {
                if ($run_in_background) {
                    $suffix_exec_param = ' &> /dev/null & ';
                }
                $commands = array();
                $imParams = '';
                if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5'] == 'im6') {
                    $imParams .= '-auto-orient -strip';
                }
                $imgtype = mslib_befe::exif_imagetype($original_path);
                if ($imgtype) {
                    // valid image
                    $ext = image_type_to_extension($imgtype, false);
                    if ($ext) {
                        if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG']) {
                            switch ($ext) {
                                case 'png':
                                    // IMAGE IS PNG, BUT SOMETIMES JPEG IS REDUCING THE FILESIZE. LETS TRY
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('identify', ' -verbose "' . $original_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    $info = shell_exec($command);
                                    if (strstr($info, 'Alpha:')) {
                                        // THIS IMAGE HAS A TRANSPARANT BACKGROUND SO WE MAY NOT CONVERT IT
                                        break;
                                    }
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.jpg';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.jpg';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $imParams . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                                //case 'jpeg':
                                //case 'gif':
                                //	break;
                                default:
                                    // IMAGE IS NOT PNG. MAYBE CONVERTING IT TO PNG REDUCES THE FILESIZE. LETS TRY
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.png';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.png';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $imParams . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                } else {
                    return false;
                }
                //hook to let other plugins further manipulate the method
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePreProc'])) {
                    $params = array(
                            'original_path' => $original_path,
                            'newOriginal_path' => $newOriginal_path,
                            'filename' => &$filename,
                            'module_path' => $module_path,
                            'run_in_background' => $run_in_background,
                            'imParams' => &$imParams
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePreProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                /*
				if (filesize($original_path)>16384) {
					// IF ORIGINAL VARIANT IS BIGGER THAN 2 MBYTE RESIZE IT FIRST
					$command=\TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $imParams.' -quality '.$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'].' -resize "3840x2160>" "'.$original_path.'" "'.$original_path.'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
					exec($command);
				}
				*/
                $folder = mslib_befe::getImagePrefixFolder($filename);
                $dirs = array();
                $dirs[] = PATH_site . $this->ms['image_paths']['products']['100'] . '/' . $folder;
                $dirs[] = PATH_site . $this->ms['image_paths']['products']['200'] . '/' . $folder;
                $dirs[] = PATH_site . $this->ms['image_paths']['products']['300'] . '/' . $folder;
                $dirs[] = PATH_site . $this->ms['image_paths']['products']['50'] . '/' . $folder;
                $dirs[] = PATH_site . $this->ms['image_paths']['products']['normal'] . '/' . $folder;
                foreach ($dirs as $dir) {
                    if (!is_dir($dir)) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($dir);
                    }
                }
                $target = PATH_site . $this->ms['image_paths']['products']['300'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // 300 thumbnail settings
                $maxwidth = $this->ms['product_image_formats'][300]['width'];
                $maxheight = $this->ms['product_image_formats'][300]['height'];
                if ($ext != 'gif') {
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $imParams . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                    if ($this->ms['MODULES']['PRODUCT_IMAGE_SHAPED_CORNERS']) {
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthWest ' . $module_path . 'templates/images/curves/lb.png "' . $target . '" "' . $target . '"';
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthEast ' . $module_path . 'templates/images/curves/rb.png "' . $target . '" "' . $target . '"';
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthWest ' . $module_path . 'templates/images/curves/lo.png "' . $target . '" "' . $target . '"';
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthEast ' . $module_path . 'templates/images/curves/ro.png "' . $target . '" "' . $target . '"';
                    }
                } else {
                    $temp_gif = PATH_site . '/typo3temp/temporary_300.gif';
                    @unlink($temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $target . ' -coalesce ' . $temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $temp_gif . ' -resize ' . $maxwidth . 'x' . $maxheight . ' "' . $target . '"');
                }
                $target = PATH_site . $this->ms['image_paths']['products']['200'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // 200 thumbnail settings
                $maxwidth = $this->ms['product_image_formats'][200]['width'];
                $maxheight = $this->ms['product_image_formats'][200]['height'];
                if ($ext != 'gif') {
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                    if ($this->ms['MODULES']['PRODUCT_IMAGE_SHAPED_CORNERS']) {
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthWest ' . $module_path . 'templates/images/curves/lb.png "' . $target . '" "' . $target . '"';
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity NorthEast ' . $module_path . 'templates/images/curves/rb.png "' . $target . '" "' . $target . '"';
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthWest ' . $module_path . 'templates/images/curves/lo.png "' . $target . '" "' . $target . '"';
                        $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -gravity SouthEast ' . $module_path . 'templates/images/curves/ro.png "' . $target . '" "' . $target . '"';
                    }
                } else {
                    $temp_gif = PATH_site . '/typo3temp/temporary_200.gif';
                    @unlink($temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $target . ' -coalesce ' . $temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $temp_gif . ' -resize ' . $maxwidth . 'x' . $maxheight . ' "' . $target . '"');
                }
                $target = PATH_site . $this->ms['image_paths']['products']['100'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // 100 thumbnail settings
                $maxwidth = $this->ms['product_image_formats'][100]['width'];
                $maxheight = $this->ms['product_image_formats'][100]['height'];
                if ($ext != 'gif') {
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                } else {
                    $temp_gif = PATH_site . '/typo3temp/temporary_100.gif';
                    @unlink($temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $target . ' -coalesce ' . $temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $temp_gif . ' -resize ' . $maxwidth . 'x' . $maxheight . ' "' . $target . '"');
                }
                $target = PATH_site . $this->ms['image_paths']['products']['50'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // 50 thumbnail settings
                $maxwidth = $this->ms['product_image_formats'][50]['width'];
                $maxheight = $this->ms['product_image_formats'][50]['height'];
                if ($ext != 'gif') {
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                } else {
                    $temp_gif = PATH_site . '/typo3temp/temporary_50.gif';
                    @unlink($temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $target . ' -coalesce ' . $temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $temp_gif . ' -resize ' . $maxwidth . 'x' . $maxheight . ' "' . $target . '"');
                }
                $target = PATH_site . $this->ms['image_paths']['products']['normal'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // normal thumbnail settings
                $maxwidth = $this->ms['product_image_formats']['enlarged']['width'];
                $maxheight = $this->ms['product_image_formats']['enlarged']['height'];
                if ($ext != 'gif') {
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                } else {
                    $temp_gif = PATH_site . '/typo3temp/temporary_normal.gif';
                    @unlink($temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $target . ' -coalesce ' . $temp_gif);
                    $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $temp_gif . ' -resize ' . $maxwidth . 'x' . $maxheight . ' "' . $target . '"');
                }
                $params = array(
                        'original_path' => $original_path,
                        'target' => $target,
                        'module_path' => $module_path,
                        'run_in_background' => $run_in_background,
                        'folder' => &$folder,
                        'filename' => &$filename
                );
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImageWatermarkHook'])) {
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImageWatermarkHook'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                } else {
                    if ($ext != 'gif') {
                        if (!$this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT']) {
                            $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"',
                                    $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                        } else {
                            exec(\TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"',
                                    $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']));
                            //\TYPO3\CMS\Core\Utility\CommandUtility::exec( \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality 90 -resize "'.$maxwidth.'x'.$maxheight.'>" "'.$target .'" "'.$target .'"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']));
                            $newsize = @getimagesize($target);
                            $text_width = $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_WIDTH'];
                            $text_height = $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_HEIGHT'];
                            if ($newsize[0] > $maxwidth) {
                                $final_width = $maxwidth;
                            } else {
                                $final_width = $newsize[0];
                            }
                            if ($newsize[1] > $maxheight) {
                                $final_height = $maxheight;
                            } else {
                                $final_height = $newsize[1];
                            }
                            switch ($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_POSITION']) {
                                case 'north-east':
                                    $pos_x = ($final_width - $text_width);
                                    $pos_y = (25);
                                    break;
                                case 'south-east':
                                    $pos_x = ($final_width - $text_width);
                                    $pos_y = ($final_height - 5);
                                    break;
                                case 'south-west':
                                    $pos_x = '2';
                                    $pos_y = ($final_height - 5);
                                    break;
                                case 'north-west':
                                    $pos_x = '2';
                                    $pos_y = (25);
                                    break;
                            }
                            if (is_numeric($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_SIZE']) && $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'] && ($newsize[0] > $text_width && $newsize[1] > $text_height)) {
                                if (strstr($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'], "..")) {
                                    die('error in PRODUCT_IMAGE_WATERMARK_FONT_FILE value');
                                } else {
                                    if (strstr($this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'], "/")) {
                                        $font_file = PATH_site . $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'];
                                    } else {
                                        $font_file = $module_path . 'templates/images/fonts/' . $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_FILE'];
                                    }
                                }
                                $savenametest = md5(rand(0, 99));
                                $tmppath = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/cache';
                                // watermark bugfix
                                // removing the width, to make it proportional width based on height, so the text are always visible in any image size
                                $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert',
                                        '-resize x' . $final_height . ' xc:black -font "' . $font_file . '" -pointsize ' . $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_SIZE'] . ' -fill white -draw "text ' . $pos_x . ',' . $pos_y . ' \'' . $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'] . '\'" -shade ' . $text_width . 'x' . ($text_height - 30) . '  "' . $tmppath . '/beveled_' . $savenametest . '.jpg"',
                                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert',
                                        '-resize x' . $final_height . ' xc:black -font "' . $font_file . '" -pointsize ' . $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_FONT_SIZE'] . ' -fill white -draw "text ' . $pos_x . ',' . $pos_y . ' \'' . $this->ms['MODULES']['PRODUCT_IMAGE_WATERMARK_TEXT'] . '\'" -shade ' . $text_width . 'x' . $text_height . '  -negate -normalize  "' . $tmppath . '/beveled_mask_' . $savenametest . '.jpg"',
                                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -compose CopyOpacity "' . $tmppath . '/beveled_mask_' . $savenametest . '.jpg' . '" "' . $tmppath . '/beveled_' . $savenametest . '.jpg' . '" "' . $tmppath . '/beveled_trans_' . $savenametest . '.png"';
                                $commands[] = $GLOBALS['TYPO3_CONF_VARS']['GFX']["im_path"] . 'composite -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' "' . $tmppath . '/beveled_trans_' . $savenametest . '.png" "' . $target . '" "' . $target . '"';
                                $commands[] = "rm -f " . $tmppath . '/beveled_' . $savenametest . '.jpg';
                                $commands[] = "rm -f " . $tmppath . '/beveled_mask_' . $savenametest . '.jpg';
                                $commands[] = "rm -f " . $tmppath . '/beveled_trans_' . $savenametest . '.png';
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
                }
                //print_r($commands);
                //die();
                if (count($commands)) {
                    // background running is not working on all boxes well, so we reverted it
                    //				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
                    //				\TYPO3\CMS\Core\Utility\CommandUtility::exec($final_command);
                    foreach ($commands as $command) {
                        exec($command);
                    }
                }
            }
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePostProc'])) {
                $params = array(
                        'original_path' => $original_path,
                        'folder' => &$folder,
                        'filename' => &$filename,
                        'target' => $target,
                        'module_path' => $module_path,
                        'commands' => $commands
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductImagePostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            return $filename;
        }
    }
    public function resizeProductAttributeValuesImage($original_path, $filename, $module_path, $run_in_background = 0) {
        if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']) {
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] = 75;
        }
        if (file_exists($original_path) && $filename) {
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductAttributeValuesImage'])) {
                $params = array(
                        'original_path' => $original_path,
                        'filename' => &$filename,
                        'module_path' => $module_path,
                        'run_in_background' => $run_in_background
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductAttributeValuesImage'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            } else {
                if ($run_in_background) {
                    $suffix_exec_param = ' &> /dev/null & ';
                }
                $commands = array();
                $params = '';
                if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_version_5'] == 'im6') {
                    $params .= '-auto-orient -strip';
                }
                $imgtype = mslib_befe::exif_imagetype($original_path);
                if ($imgtype) {
                    // valid image
                    $ext = image_type_to_extension($imgtype, false);
                    if ($ext) {
                        if ($this->ms['MODULES']['ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG']) {
                            switch ($ext) {
                                case 'png':
                                    // IMAGE IS PNG, BUT SOMETIMES JPEG IS REDUCING THE FILESIZE. LETS TRY
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('identify', ' -verbose "' . $original_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    $info = shell_exec($command);
                                    if (strstr($info, 'Alpha:')) {
                                        // THIS IMAGE HAS A TRANSPARANT BACKGROUND SO WE MAY NOT CONVERT IT
                                        break;
                                    }
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.jpg';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.jpg';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                                //case 'jpeg':
                                //case 'gif':
                                //	break;
                                default:
                                    // IMAGE IS NOT PNG. MAYBE CONVERTING IT TO PNG REDUCES THE FILESIZE. LETS TRY
                                    $fileArray = pathinfo($original_path);
                                    $newFilename = $fileArray['filename'] . '.png';
                                    $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                    if (file_exists($newOriginal_path)) {
                                        do {
                                            $newFilename = $fileArray['filename'] . ($i > 0 ? '-' . $i : '') . '.png';
                                            $newOriginal_path = $fileArray['dirname'] . '/' . $newFilename;
                                            $i++;
                                        } while (file_exists($newOriginal_path));
                                    }
                                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $newOriginal_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                                    exec($command);
                                    if (file_exists($newOriginal_path)) {
                                        if (filesize($original_path) > filesize($newOriginal_path)) {
                                            @unlink($original_path);
                                            $original_path = $newOriginal_path;
                                            $filename = $newFilename;
                                        } else {
                                            @unlink($newOriginal_path);
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                } else {
                    return false;
                }
                if (filesize($original_path) > 16384) {
                    // IF ORIGINAL VARIANT IS BIGGER THAN 2 MBYTE RESIZE IT FIRST
                    $command = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "3840x2160>" "' . $original_path . '" "' . $original_path . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                    exec($command);
                }
                $folder = mslib_befe::getImagePrefixFolder($filename);
                $dirs = array();
                $dirs[] = PATH_site . $this->ms['image_paths']['attribute_values']['normal'] . '/' . $folder;
                $dirs[] = PATH_site . $this->ms['image_paths']['attribute_values']['small'] . '/' . $folder;
                foreach ($dirs as $dir) {
                    if (!is_dir($dir)) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($dir);
                    }
                }
                $target = PATH_site . $this->ms['image_paths']['attribute_values']['normal'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // normal thumbnail settings
                $maxwidth = $this->ms['attribute_values_image_formats']['normal']['width'];
                $maxheight = $this->ms['attribute_values_image_formats']['normal']['height'];
                $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', $params . ' -quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                //
                $target = PATH_site . $this->ms['image_paths']['attribute_values']['small'] . '/' . $folder . '/' . $filename;
                copy($original_path, $target);
                // 200 thumbnail settings
                $maxwidth = $this->ms['attribute_values_image_formats']['small']['width'];
                $maxheight = $this->ms['attribute_values_image_formats']['small']['height'];
                $commands[] = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-quality ' . $GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'] . ' -resize "' . $maxwidth . 'x' . $maxheight . '>" "' . $target . '" "' . $target . '"', $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path_lzw']);
                if (count($commands)) {
                    // background running is not working on all boxes well, so we reverted it
                    //				$final_command="(".implode($commands," && ").") ".$suffix_exec_param;
                    //				\TYPO3\CMS\Core\Utility\CommandUtility::exec($final_command);
                    foreach ($commands as $command) {
                        exec($command);
                    }
                }
            }
            //hook to let other plugins further manipulate the method
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductAttributeValuesImagePostProc'])) {
                $params = array(
                        'original_path' => $original_path,
                        'folder' => &$folder,
                        'filename' => &$filename,
                        'target' => $target,
                        'module_path' => $module_path,
                        'commands' => $commands
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['resizeProductAttributeValuesImagePostProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            return $filename;
        }
    }
    public function listdir($dir = '.') {
        if (!is_dir($dir)) {
            return false;
        }
        $files = array();
        mslib_befe::listdiraux($dir, $files);
        return $files;
    }
    public function listdiraux($dir, &$files) {
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $filepath = $dir == '.' ? $file : $dir . '/' . $file;
            if (is_link($filepath)) {
                continue;
            }
            if (is_file($filepath)) {
                $files[] = $filepath;
            } else if (is_dir($filepath)) {
                mslib_befe::listdiraux($filepath, $files);
            }
        }
        closedir($handle);
    }
    public function tep_get_categories_select($categories_id = '0', $aid = '', $level = 0, $selectedid = '') {
        $qry = $GLOBALS['TYPO3_DB']->SELECTquery('cd.categories_name, c.categories_id, c.parent_id', // SELECT ...
                'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
                'c.parent_id=\'' . $categories_id . '\' and c.status=1 and c.page_uid=\'' . $this->shop_pid . '\' and c.categories_id=cd.categories_id', // WHERE...
                '', // GROUP BY...
                'c.sort_order, cd.categories_name', // ORDER BY...
                '' // LIMIT ...
        );
        $parent_categories_query = $GLOBALS['TYPO3_DB']->sql_query($qry);
        $html = '';
        while (($parent_categories = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($parent_categories_query)) != false) {
            $html .= '<option value="' . $parent_categories['categories_id'] . '" ' . (($selectedid == $parent_categories['categories_id']) ? 'selected' : '') . '>';
            for ($i = 0; $i < $level; $i++) {
                $html .= '--';
            }
            $html .= $parent_categories['categories_name'] . '</option>';
            $strchk = $GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
                    'tx_multishop_categories', // FROM ...
                    'c.parent_id=\'' . $parent_categories['categories_id'] . '\'', // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qrychk = $GLOBALS['TYPO3_DB']->sql_query($strchk);
            $rowTotal = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qrychk);
            if ($rowTotal['total']) {
                $html .= mslib_befe::tep_get_categories_select($parent_categories['categories_id'], $aid, ($level + 1), $selectedid);
            }
        }
        if (!$categories_id) {
            $html .= '</select>';
        }
        return $html;
    }
    public function tep_get_chained_categories_select($categories_id = '0', $aid = '', $level = 0, $selectedid = '', $page_uid = '') {
        if (!$page_uid) {
            $page_uid = $this->shop_pid;
        }
        if (!$categories_id) {
            $categories_id = 0;
        }
        $output = array();
        $str = $GLOBALS['TYPO3_DB']->SELECTquery('cd.categories_name, c.categories_id, c.parent_id', // SELECT ...
                'tx_multishop_categories c, tx_multishop_categories_description cd', // FROM ...
                'c.parent_id=\'' . $categories_id . '\' and c.status=1 and c.page_uid=\'' . $page_uid . '\' and c.categories_id=cd.categories_id', // WHERE...
                '', // GROUP BY...
                'c.sort_order, cd.categories_name', // ORDER BY...
                '' // LIMIT ...
        );
        $parent_categories_query = $GLOBALS['TYPO3_DB']->sql_query($str);
        $rows = $GLOBALS['TYPO3_DB']->sql_num_rows($parent_categories_query);
        while (($parent_categories = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($parent_categories_query)) != false) {
            $output[$level] .= '<option value="' . $parent_categories['categories_id'] . '" ' . (($selectedid == $parent_categories['categories_id']) ? 'selected' : '') . ' class="' . ($level > 0 ? '' . $parent_categories['parent_id'] : '') . '">' . $parent_categories['categories_name'] . '</option>';
            $strchk = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                    'tx_multishop_categories c', // FROM ...
                    'c.parent_id=\'' . $parent_categories['categories_id'] . '\'', // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qrychk = $GLOBALS['TYPO3_DB']->sql_query($strchk);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qrychk)) {
                $tmp_array = mslib_befe::tep_get_chained_categories_select($parent_categories['categories_id'], $aid, ($level + 1), $selectedid, $page_uid);
                if (is_array($tmp_array) && count($tmp_array)) {
                    foreach ($tmp_array as $key => $value) {
                        $output[$key] .= $value;
                    }
                }
            }
        }
        return $output;
    }
    public function array2json($arr) {
        if (function_exists('json_encode')) {
            return json_encode($arr); //Lastest versions of PHP already has this functionality.
        }
        $parts = array();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys($arr);
        $max_length = count($arr) - 1;
        if (($keys[0] == 0) and ($keys[$max_length] == $max_length)) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for ($i = 0; $i < count($keys); $i++) { //See if each key correspondes to its position
                if ($i != $keys[$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        if (is_array($arr) && count($arr)) {
            foreach ($arr as $key => $value) {
                if (is_array($value)) { //Custom handling for arrays
                    if ($is_list) {
                        $parts[] = array2json($value); /* :RECURSION: */
                    } else {
                        $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
                    }
                } else {
                    $str = '';
                    if (!$is_list) {
                        $str = '"' . $key . '":';
                    }
                    //Custom handling for multiple data types
                    if (is_numeric($value)) {
                        $str .= $value; //Numbers
                    } else if ($value === false) {
                        $str .= 'false'; //The booleans
                    } else if ($value === true) {
                        $str .= 'true';
                    } else {
                        $str .= '"' . addslashes($value) . '"'; //All other things
                    }
                    // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                    $parts[] = $str;
                }
            }
        }
        if (is_array($parts)) {
            $json = implode(',', $parts);
            if ($is_list) {
                return '[' . $json . ']'; //Return numerical JSON
            }
            return '{' . $json . '}'; //Return associative JSON
        }
    }
    public function rebuildFlatDatabase() {
        $content = '<div class="main-heading"><h1>Rebuild Flat Database</h1></div>';
        $str = "DROP TABLE IF EXISTS `tx_multishop_products_flat_tmp`;";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $str = "CREATE TABLE `tx_multishop_products_flat_tmp` (
			`id` int(11) auto_increment,
			`products_id` int(11),
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
			`manufacturers_name` varchar(64) NULL,
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
        for ($x = 0; $x < $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']; $x++) {
            $i = $x;
            if ($i == 0) {
                $i = '';
            }
            $str .= '`products_image' . $i . '` varchar(250) NULL,' . "\n";
        }
        $str .= "	  `products_viewed` int(11) NULL,
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
		  `products_multiplication` decimal(6,2) default '0.00',
		  `minimum_quantity` decimal(6,2) default '1.00',
		  `maximum_quantity` decimal(6,2) default NULL,
		  `delivery_time` varchar(75) default '',
		  `order_unit_id` int(11) default '0',
		  `order_unit_code` varchar(15) default '',
		  `order_unit_name` varchar(25) default '',
		  `products_condition` varchar(20) default 'new',
		  `vendor_code` varchar(25) default '',
		  `starttime` int(11) default '0',
		  `endtime` int(11) default '0',
		  `ignore_stock_level` tinyint(1) default '0',
		";
        $additionalColumns = array();
        if ($this->ms['MODULES']['FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS'] and is_array($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS']) && count($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'])) {
            $additional_indexes = '';
            foreach ($this->ms['FLAT_DATABASE_ATTRIBUTE_OPTIONS'] as $option_id => $array) {
                if ($array[0] and $array[1]) {
                    $str .= "		  `" . $array[0] . "` " . $array[1] . " NULL," . "\n";
                    $additional_indexes .= "KEY `" . $array[0] . "` (`" . $array[0] . "`)," . "\n";
                }
            }
        }
        //hook to let other plugins further manipulate the create table query
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabaseQueryProc'])) {
            $params = array(
                    'str' => &$str,
                    'additional_indexes' => &$additional_indexes,
                    'additionalColumns' => &$additionalColumns
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabaseQueryProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if (count($additionalColumns)) {
            foreach ($additionalColumns as $additionColumn) {
                if ($additionColumn['column_name']) {
                    $str .= "		  `" . $additionColumn['column_name'] . "` " . $additionColumn['column_type'] . " NULL," . "\n";
                }
                if ($additionColumn['enable_index']) {
                    $additional_indexes .= "KEY `" . $additionColumn['column_name'] . "` (`" . $additionColumn['column_name'] . "`)," . "\n";
                }
            }
        }
        $str .= "PRIMARY KEY (`id`),
		  UNIQUE KEY (`products_id`,`language_id`),
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
		  KEY `starttime` (`starttime`),
		  KEY `endtime` (`endtime`),
		  " . $additional_indexes . "
		  FULLTEXT KEY `products_name` (`products_name`),
		  FULLTEXT KEY `products_model_2` (`products_model`),
		  FULLTEXT KEY `products_model_3` (`products_model`,`products_name`)
		) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
		";
        //ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
        //hook to let other plugins further manipulate the create table query
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePreHook'])) {
            $params = array(
                    'str' => &$str
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePreHook'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        if (!$qry || $this->conf['debugEnabled'] == '1') {
            $logString = 'rebuildFlatDatabase CREATE TABLE failed query: ' . $str;
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', -1);
            echo $str;
            exit();
        }
        $products = array();
        //$str="truncate tx_multishop_products_flat";
        //$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
        /*
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('products_id', // SELECT ...
			'tx_multishop_products', // FROM ...
			'products_status=1', // WHERE...
			'', // GROUP BY...
			'sort_order '.$this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'], // ORDER BY...
			'' // LIMIT ...
		);
		*/
        $str = $GLOBALS['TYPO3_DB']->SELECTquery('p.products_id', // SELECT ...
                'tx_multishop_products p, tx_multishop_categories c, tx_multishop_products_to_categories p2c', // FROM ...
                'p.products_status=1 and p2c.is_deepest=1 AND c.status=1 and p.products_id=p2c.products_id and c.categories_id=p2c.categories_id', // WHERE...
                '', // GROUP BY...
                'p2c.sort_order ' . $this->ms['MODULES']['PRODUCTS_LISTING_SORT_ORDER_OPTION'], // ORDER BY...
                '' // LIMIT ...
        );
        //error_log($str);
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        if ($this->conf['debugEnabled'] == '1') {
            $logString = 'rebuildFlatDatabase query: ' . $str;
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($logString, 'multishop', -1);
        }
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
            $products[] = $row['products_id'];
        }
        if (count($products)) {
            foreach ($products as $products_id) {
                mslib_befe::convertProductToFlat($products_id, 'tx_multishop_products_flat_tmp');
            }
        }
        $str = "ANALYZE TABLE `tx_multishop_products_flat_tmp`";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        //hook to let other plugins further manipulate the create table query
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePreRenameProc'])) {
            $params = array();
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePreRenameProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        $str = "drop table `tx_multishop_products_flat`;";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        // convert to memory table
        //$str="CREATE TABLE tx_multishop_products_flat ENGINE=MEMORY AS SELECT * FROM tx_multishop_products_flat_tmp";
        //$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
        //$str="drop table `tx_multishop_products_flat_tmp`;";
        //$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
        // if not using memory table rename the tmp table
        $str = "RENAME TABLE `tx_multishop_products_flat_tmp` TO `tx_multishop_products_flat`;";
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        //hook to let other plugins further manipulate the create table query
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePostHook'])) {
            $params = array();
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['rebuildFlatDatabasePostHook'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        return $content;
    }
    public function convToUtf8($content) {
        if (!mb_check_encoding($content, 'UTF-8') or !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))) {
            $content = mb_convert_encoding($content, 'UTF-8');
            if (mb_check_encoding($content, 'UTF-8')) {
                // log('Converted to UTF-8');
            } else {
                // log('Could not convert to UTF-8');
            }
        }
        return $content;
    }
    public function detect_encoding($string) {
        static $list = array(
                'utf-8',
                'windows-1251'
        );
        foreach ($list as $item) {
            $sample = iconv($item, $item, $string);
            if (md5($sample) == md5($string)) {
                return $item;
            }
        }
        return null;
    }
    public function Week($week) {
        $year = date('Y');
        $lastweek = $week - 1;
        if ($lastweek == 0) {
            $week = 52;
            $year--;
        }
        $lastweek = sprintf("%02d", $lastweek);
        for ($i = 1; $i <= 7; $i++) {
            $arrdays[] = strtotime("$year" . "W$lastweek" . "$i");
        }
        return $arrdays;
    }
    public function RunMultishopUpdate($multishop_page_uid = '') {
        $settings = array();
        // attribute fixer
        $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_products_options', // FROM ...
                'language_id=\'0\'', // WHERE...
                '', // GROUP BY...
                'sort_order', // ORDER BY...
                '' // LIMIT ...
        );
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        $rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
        if ($rows) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                // now load the related values
                $str2 = $GLOBALS['TYPO3_DB']->SELECTquery('povp.products_options_id,povp.products_options_values_id', // SELECT ...
                        'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov', // FROM ...
                        'povp.products_options_id=\'' . $row['products_options_id'] . '\' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id=\'0\' and povp.sort_order=0', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
                $rows2 = $GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
                if ($rows2 > 1) {
                    $fix_attribute_values = 1;
                    break;
                }
            }
        }
        if ($fix_attribute_values) {
            // attribute fixer
            $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                    'tx_multishop_products_options', // FROM ...
                    'language_id=\'0\'', // WHERE...
                    '', // GROUP BY...
                    'sort_order', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            $rows = $GLOBALS['TYPO3_DB']->sql_num_rows($qry);
            if ($rows) {
                while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
                    // now load the related values
                    $str2 = $GLOBALS['TYPO3_DB']->SELECTquery('povp.products_options_id,povp.products_options_values_id', // SELECT ...
                            'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov', // FROM ...
                            'povp.products_options_id=\'' . $row['products_options_id'] . '\' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id=\'0\'', // WHERE...
                            '', // GROUP BY...
                            'povp.sort_order', // ORDER BY...
                            '' // LIMIT ...
                    );
                    $qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
                    $counter = 0;
                    while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
                        $where = "products_options_id='" . $row2['products_options_id'] . "' and products_options_values_id = " . $row2['products_options_values_id'];
                        $updateArray = array(
                                'sort_order' => $counter
                        );
                        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', $where, $updateArray);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                        $counter++;
                    }
                }
            }
            // end attribute fixer
        }
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_configuration_values', // FROM ...
                'page_uid="' . $multishop_page_uid . '"', // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                if (isset($row['configuration_value']) and $row['configuration_value'] != '') {
                    $settings['LOCAL_MODULES'][$row['configuration_key']] = $row['configuration_value'];
                }
            }
        }
        // load local front-end module config eof
        // load global front-end module config
        $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_configuration', // FROM ...
                '', // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                if (isset($row['configuration_value'])) {
                    $settings['GLOBAL_MODULES'][$row['configuration_key']] = $row['configuration_value'];
                }
            }
        }
        // load global front-end module config eof
        // merge global with local front-end module config
        if (is_array($settings['GLOBAL_MODULES']) && count($settings['GLOBAL_MODULES'])) {
            foreach ($settings['GLOBAL_MODULES'] as $key => $value) {
                if (isset($settings['LOCAL_MODULES'][$key])) {
                    $settings[$key] = $settings['LOCAL_MODULES'][$key];
                } else {
                    $settings[$key] = $value;
                }
            }
        }
        // merge global with local front-end module config eof
        if ($settings['COUNTRY_ISO_NR']) {
            $country = mslib_fe::getCountryByIso($settings['COUNTRY_ISO_NR']);
            $settings['CURRENCY_ARRAY'] = mslib_befe::loadCurrency($country['cn_currency_iso_nr']);
            switch ($settings['COUNTRY_ISO_NR']) {
                case '528':
                case '276':
                    $settings['CURRENCY'] = '&#8364;';
                    break;
                default:
                    $settings['CURRENCY'] = $settings['CURRENCY_ARRAY']['cu_symbol_left'];
                    break;
            }
        }
        // check database
        $messages = array();
        $skip = 0;
        $settings['update_database'] = 1;
        if ($settings['update_database']) {
            set_time_limit(86400);
            ignore_user_abort(true);
            require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'scripts/front_pages/includes/compare_database.php');
        } // check database eof
        // delete duplicates eof
        // load default vars for upgrade purposes eof
        if (!count($messages)) {
            $content = $this->pi_getLL('admin_label_nothing_updated_already_using_latest_version');
        } else {
            $content = addslashes(str_replace("\n", "", implode("<br />", $messages)));
        }
        return $content;
    }
    public function getMethodsByProduct($products_id) {
        if (is_numeric($products_id)) {
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                    'tx_multishop_products_method_mappings', // FROM ...
                    'products_id=' . $products_id, // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                $array = array();
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                    $array[$row['type']][] = $row['method_id'];
                    $array[$row['type']]['method_data'][$row['method_id']] = $row;
                }
                return $array;
            }
        }
    }
    public function getMethodsByCustomer($customer_id) {
        if (is_numeric($customer_id)) {
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                    'tx_multishop_customers_method_mappings', // FROM ...
                    'customers_id=' . $customer_id, // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                $array = array();
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                    $array[$row['type']][] = $row['method_id'];
                    $array[$row['type']]['method_data'][$row['method_id']] = $row;
                }
                return $array;
            }
        }
    }
    public function getMethodsByGroup($group_id) {
        if (is_numeric($group_id)) {
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                    'tx_multishop_customers_groups_method_mappings', // FROM ...
                    'customers_groups_id=' . $group_id, // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                $array = array();
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                    $array[$row['type']][] = $row['method_id'];
                    $array[$row['type']]['method_data'][$row['method_id']] = $row;
                }
                return $array;
            }
        }
    }
    /**
     * Gets information for an extension, eg. version and
     * most-recently-edited-script
     * @param string        Path to local extension folder
     * @param string        Extension key
     * @return    array        Information array (unless an error occured)
     */
    public function getExtensionInfo($path, $extKey) {
        $file = $path . '/ext_emconf.php';
        if (@is_file($file)) {
            $_EXTKEY = $extKey;
            $EM_CONF = array();
            require($file);
            $eInfo = array();
            // Info from emconf:
            $eInfo['title'] = $EM_CONF[$extKey]['title'];
            $eInfo['version'] = $EM_CONF[$extKey]['version'];
            $eInfo['CGLcompliance'] = $EM_CONF[$extKey]['CGLcompliance'];
            $eInfo['CGLcompliance_note'] = $EM_CONF[$extKey]['CGLcompliance_note'];
            if (is_array($EM_CONF[$extKey]['constraints']) && is_array($EM_CONF[$extKey]['constraints']['depends'])) {
                $eInfo['TYPO3_version'] = $EM_CONF[$extKey]['constraints']['depends']['typo3'];
            } else {
                $eInfo['TYPO3_version'] = $EM_CONF[$extKey]['TYPO3_version'];
            }
            $filesHash = unserialize($EM_CONF[$extKey]['_md5_values_when_last_written']);
            $eInfo['manual'] = @is_file($path . '/doc/manual.sxw');
            return $eInfo;
        } else {
            return 'ERROR: No emconf.php file: ' . $file;
        }
    }
    public function canContainProducts($categories_id) {
        if (!is_numeric($categories_id)) {
            return false;
        }
        if (is_numeric($categories_id)) {
            $str = $GLOBALS['TYPO3_DB']->SELECTquery('count(1) as total', // SELECT ...
                    'tx_multishop_categories', // FROM ...
                    'parent_id=\'' . $categories_id . '\'', // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
            if ($row['total'] > 0) {
                return false;
            } else {
                return true;
            }
        }
    }
    public function moveProduct($products_id, $target_categories_id, $old_categories_id = '') {
        if (!is_numeric($products_id)) {
            return false;
        }
        if (!is_numeric($target_categories_id)) {
            return false;
        }
        if (is_numeric($products_id) and is_numeric($target_categories_id)) {
            if (is_numeric($old_categories_id)) {
                $qry = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_multishop_products_to_categories', 'products_id=' . $products_id . ' and categories_id=' . $old_categories_id);
            }
            $insertArray = array();
            $insertArray['categories_id'] = $target_categories_id;
            $insertArray['products_id'] = $products_id;
            $insertArray['page_uid'] = $this->showCatalogFromPage;
            //$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
            //$res=$GLOBALS['TYPO3_DB']->sql_query($query);
            // create categories tree linking
            $res = tx_mslib_catalog::linkCategoriesTreeToProduct($products_id, $target_categories_id, $insertArray);
            if ($res) {
                // enable lock indicator if product is originally coming from the products importer
                $str = $GLOBALS['TYPO3_DB']->SELECTquery('products_id', // SELECT ...
                        'tx_multishop_products', // FROM ...
                        'imported_product=1 and lock_imported_product=0 and products_id=\'' . $products_id . '\'', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                    $updateArray = array();
                    $updateArray['lock_imported_product'] = 1;
	                $updateArray['products_last_modified'] = time();
                    $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\'' . $this->post['pid'] . '\'', $updateArray);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
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
        $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                'tx_multishop_products', // FROM ...
                'products_id=\'' . $id_product . '\'', // WHERE...
                '', // GROUP BY...
                '', // ORDER BY...
                '' // LIMIT ...
        );
        $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) == 0) {
            return false;
        } else {
            //insert into tx_multishop_products
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
            $product_arr_new = array();
            if (is_array($row) && count($row)) {
                foreach ($row as $key_p => $val_p) {
                    if ($key_p != 'products_id') {
                        if ($key_p == 'products_image' or $key_p == 'products_image1' or $key_p == 'products_image2' or $key_p == 'products_image3' or $key_p == 'products_image4') {
                            if (!empty($val_p)) {
                                $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                                        'tx_multishop_products_description', // FROM ...
                                        'products_id=\'' . $id_product . '\' and language_id=\'' . $this->sys_language_uid . '\'', // WHERE...
                                        '', // GROUP BY...
                                        '', // ORDER BY...
                                        '' // LIMIT ...
                                );
                                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                                $row_desc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
                                $file = mslib_befe::getImagePath($val_p, 'products', 'original');
                                //echo $file;
                                $imgtype = mslib_befe::exif_imagetype($file);
                                if ($imgtype) {
                                    // valid image
                                    $ext = image_type_to_extension($imgtype, false);
                                    if ($ext) {
                                        $i = 0;
                                        $filename = mslib_fe::rewritenamein($row_desc['products_name']) . '.' . $ext;
                                        //echo $filename;
                                        $folder = mslib_befe::getImagePrefixFolder($filename);
                                        $array = explode(".", $filename);
                                        if (!is_dir($this->DOCUMENT_ROOT . $this->ms['image_paths']['products']['original'] . '/' . $folder)) {
                                            \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT . $this->ms['image_paths']['products']['original'] . '/' . $folder);
                                        }
                                        $folder .= '/';
                                        $target = $this->DOCUMENT_ROOT . $this->ms['image_paths']['products']['original'] . '/' . $folder . $filename;
                                        //echo $target;
                                        if (file_exists($target)) {
                                            do {
                                                $filename = mslib_fe::rewritenamein($row_desc['products_name']) . ($i > 0 ? '-' . $i : '') . '.' . $ext;
                                                $folder_name = mslib_befe::getImagePrefixFolder($filename);
                                                $array = explode(".", $filename);
                                                $folder = $folder_name;
                                                if (!is_dir($this->DOCUMENT_ROOT . $this->ms['image_paths']['products']['original'] . '/' . $folder)) {
                                                    \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($this->DOCUMENT_ROOT . $this->ms['image_paths']['products']['original'] . '/' . $folder);
                                                }
                                                $folder .= '/';
                                                $target = $this->DOCUMENT_ROOT . $this->ms['image_paths']['products']['original'] . '/' . $folder . $filename;
                                                $i++;
                                                //echo $target . "<br/>";
                                            } while (file_exists($target));
                                        }
                                        if (copy($file, $target)) {
                                            $target_origineel = $target;
                                            $update_product_images = mslib_befe::resizeProductImage($target_origineel, $filename, $this->DOCUMENT_ROOT . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey));
                                        }
                                    }
                                }
                                $product_arr_new[$key_p] = $update_product_images;
                            } else {
                                $product_arr_new[$key_p] = $val_p;
                            }
                        } else {
                            if ($key_p == 'extid' && !empty($val_p)) {
                                $val_p = md5(uniqid());
                            }
                            $product_arr_new[$key_p] = $val_p;
                        }
                    }
                }
            }
            $product_arr_new['sort_order'] = time();
	        $product_arr_new['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
	        $product_arr_new['products_date_added'] = time();
	        $product_arr_new['products_last_modified'] = time();
	        //hook to let other plugins further manipulate
	        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['duplicateProductPreProc'])) {
		        $params = array(
			        'product_arr_new' => &$product_arr_new
		        );
		        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['duplicateProductPreProc'] as $funcRef) {
			        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		        }
	        }
            $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products', $product_arr_new);
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $id_product_new = $GLOBALS['TYPO3_DB']->sql_insert_id();
            unset($product_arr_new);
            if ($id_product_new) {
                // insert tx_multishop_products_description
                $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                        'tx_multishop_products_description', // FROM ...
                        'products_id=\'' . $id_product . '\'', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                    $product_arr_new = $row;
                    $product_arr_new['products_id'] = $id_product_new;
                    if (strpos($product_arr_new['products_name'], '(copy') === false) {
                        $product_arr_new['products_name'] .= ' (copy ' . $id_product_new . ')';
                    } else {
                        if (strpos($product_arr_new['products_name'], '(copy ' . $id_product . ')') !== false) {
                            $product_arr_new['products_name'] = str_replace('(copy ' . $id_product . ')', ' (copy ' . $id_product_new . ')', $product_arr_new['products_name']);
                        } else {
                            $product_arr_new['products_name'] = str_replace('(copy)', ' (copy ' . $id_product_new . ')', $product_arr_new['products_name']);
                        }
                    }
                    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $product_arr_new);
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                }
                // insert tx_multishop_products_attributes
                $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                        'tx_multishop_products_attributes', // FROM ...
                        'products_id=\'' . $id_product . '\' and page_uid=\'' . $this->showCatalogFromPage . '\'', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                        $product_arr_new = $row;
                        $product_arr_new['products_id'] = $id_product_new;
                        $product_arr_new['page_uid'] = $this->showCatalogFromPage;
                        unset($product_arr_new['products_attributes_id']); //primary key
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes', $product_arr_new);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
                // insert tx_multishop_specials
                $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                        'tx_multishop_specials', // FROM ...
                        'products_id=\'' . $id_product . '\'', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                        $product_arr_new = $row;
                        $product_arr_new['products_id'] = $id_product_new;
                        unset($product_arr_new['specials_id']); //primary key
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials', $product_arr_new);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
                // insert tx_multishop_products_to_relative_products
                $str = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                        'tx_multishop_products_to_relative_products', // FROM ...
                        'products_id=\'' . $id_product . '\' or relative_product_id = \'' . $id_product . '\'', // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
                        $product_arr_new = $row;
                        if ($product_arr_new['products_id'] == $id_product) {
                            $product_arr_new['products_id'] = $id_product_new;
                        } else {
                            $product_arr_new['relative_product_id'] = $id_product_new;
                        }
                        unset($product_arr_new['products_to_relative_product_id']); //primary key
                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $product_arr_new);
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
                // insert into tx_multishop_products_to_categories
                $insertArray = array(
                        'products_id' => $id_product_new,
                        'categories_id' => $target_categories_id,
                        'sort_order' => time(),
                        'page_uid' => $this->showCatalogFromPage
                );
                //$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories', $insertArray);
                //$res=$GLOBALS['TYPO3_DB']->sql_query($query);
                // create categories tree linking
                $res = tx_mslib_catalog::linkCategoriesTreeToProduct($id_product_new, $target_categories_id, $insertArray);
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
    public function getImagePath($filename, $type, $width = 100) {
        $folder = $this->ms['image_paths'][$type][$width] . '/' . mslib_befe::getImagePrefixFolder($filename);
        return $folder . '/' . $filename;
    }
    /**
     * This function creates a zip file
     * Credits goes to Kraft Bernhard (kraftb@think-open.at)
     * @param string        File/Directory to pack
     * @param string        Zip-file target directory
     * @param string        Zip-file target name
     * @return    array        Files packed
     */
    public function zipPack($file, $targetFile) {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char'] = ':';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines'] = '1';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines'] = '0';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos'] = '1';
        }
        if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
            return array();
        }
        $zip = $GLOBALS['TYPO3_CONF_VARS']['BE']['zip_path'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['zip_path'] : 'zip';
        if (is_dir($file)) {
            chdir($file);
            $cmd = $zip . ' -r -9 ' . escapeshellarg($targetFile) . ' \'./\'';
        } else {
            $path = dirname($file);
            $file = basename($file);
            chdir($path);
            $cmd = $zip . ' -r -9 ' . escapeshellarg($targetFile) . ' ' . escapeshellarg($file);
        }
        exec($cmd, $list, $ret);
        if ($ret) {
            return array();
        }
        $result = mslib_befe::getFileResult($list, 'zip');
        return $result;
    }
    /**
     * This method helps filtering the output of the various archive binaries to get a clean php array
     * Credits goes to Kraft Bernhard (kraftb@think-open.at)
     * @param array        The output of the executed archive binary
     * @param string        The type/configuration for which to parse the output
     * @return    array        A clean list of the filenames returned by the binary
     */
    public function getFileResult($list, $type = 'zip') {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['split_char'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['split_char'] = '';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['pre_lines'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['pre_lines'] = '3';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['post_lines'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['post_lines'] = '2';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['file_pos'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['file_pos'] = '3';
        }
        $sc = $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['split_char'];
        $pre = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['pre_lines']);
        $post_lines = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['post_lines']);
        $pos = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip'][$type]['file_pos']);
        // Removing trailing lines
        while ($post_lines--) {
            array_pop($list);
        }
        // Only last lines
        if ($pre === -1) {
            $fl = array();
            while ($line = trim(array_pop($list))) {
                array_unshift($fl, $line);
            }
            $list = $fl;
        }
        // Remove preceeding lines
        if ($pre > 0) {
            while ($pre--) {
                array_shift($list);
            }
        }
        $fl = array();
        if (is_array($list) && count($list)) {
            foreach ($list as $file) {
                $parts = preg_split('/' . preg_quote($sc) . '+/', $file);
                $fl[] = trim($parts[$pos]);
            }
        }
        return $fl;
    }
    /**
     * This function unpacks a zip file
     * Credits goes to Kraft Bernhard (kraftb@think-open.at)
     * @param string        File to unpack
     * @return    array        Files unpacked
     */
    public function zipUnpack($file, $overwrite = 0) {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char'] = ':';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines'] = '1';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines'] = '0';
        }
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos'])) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos'] = '1';
        }
        if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
            return array();
        }
        if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) && isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
            return array();
        }
        $currentDir = getcwd();
        $path = dirname($file);
        chdir($path);
        // Unzip without overwriting existing files
        $unzip = $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path'] : 'unzip';
        if ($overwrite) {
            $cmd = $unzip . ' -o ' . escapeshellarg($file);
        } else {
            $cmd = $unzip . ' -n ' . escapeshellarg($file);
        }
        exec($cmd, $list, $ret);
        if ($ret) {
            //return array();
        }
        $result = mslib_befe::getFileResult($list, 'unzip');
        chdir($currentDir);
        return $result;
    }
    public function updateOrderStatus($orders_id, $orders_status, $mail_customer = 0, $action_call = '') {
        if (!is_numeric($orders_id)) {
            return false;
        }
        if (empty($action_call)) {
            $extra_data = array();
            $extra_data['get'] = $this->get;
            $extra_data['post'] = $this->post;
            $action_call = serialize($extra_data);
        }
        $continue = 1;
        //hook to let other plugins further manipulate
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusPreProc'])) {
            $params = array(
                    'orders_id' => &$orders_id,
                    'orders_status' => &$orders_status,
                    'mail_customer' => &$mail_customer,
                    'action_call' => &$action_call,
                    'continue' => &$continue
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if ($continue) {
            $order = mslib_fe::getOrder($orders_id);
            if ($order['orders_id']) {
                // dynamic variables
                if (isset($order['language_id'])) {
                    // Switch to language that is stored in the order
                    mslib_befe::setSystemLanguage($order['language_id']);
                }
                $billing_address = '';
                $delivery_address = '';
                $full_customer_name = $order['billing_first_name'];
                if ($order['billing_middle_name']) {
                    $full_customer_name .= ' ' . $order['billing_middle_name'];
                }
                if ($order['billing_last_name']) {
                    $full_customer_name .= ' ' . $order['billing_last_name'];
                }
                $delivery_full_customer_name = $order['delivery_first_name'];
                if ($order['delivery_middle_name']) {
                    $delivery_full_customer_name .= ' ' . $order['delivery_middle_name'];
                }
                if ($order['delivery_last_name']) {
                    $delivery_full_customer_name .= ' ' . $order['delivery_last_name'];
                }
                $full_customer_name = preg_replace('/\s+/', ' ', $full_customer_name);
                $delivery_full_customer_name = preg_replace('/\s+/', ' ', $delivery_full_customer_name);
                if (!$order['delivery_address'] or !$order['delivery_city']) {
                    $order['delivery_company'] = $order['billing_company'];
                    $order['delivery_address'] = $order['billing_address'];
                    $order['delivery_street_name'] = $order['billing_street_name'];
                    $order['delivery_address_number'] = $order['billing_address_number'];
                    $order['delivery_address_ext'] = $order['billing_address_ext'];
                    $order['delivery_building'] = $order['billing_building'];
                    $order['delivery_zip'] = $order['billing_zip'];
                    $order['delivery_city'] = $order['billing_city'];
                    $order['delivery_telephone'] = $order['billing_telephone'];
                    $order['delivery_mobile'] = $order['billing_mobile'];
                }
                if ($order['delivery_company']) {
                    $delivery_address = $order['delivery_company'] . "<br />";
                }
                if ($delivery_full_customer_name) {
                    $delivery_address .= $delivery_full_customer_name . "<br />";
                }
                if ($order['delivery_building']) {
                    $delivery_address .= $order['delivery_building'] . "<br />";
                }
                if ($order['delivery_address']) {
                    $delivery_address .= $order['delivery_address'] . "<br />";
                }
                if ($order['delivery_zip'] and $order['delivery_city']) {
                    $delivery_address .= $order['delivery_zip'] . " " . $order['delivery_city'];
                }
                //if ($order['delivery_telephone']) 		$delivery_address.=ucfirst($this->pi_getLL('telephone')).': '.$order['delivery_telephone']."<br />";
                //if ($order['delivery_mobile']) 			$delivery_address.=ucfirst($this->pi_getLL('mobile')).': '.$order['delivery_mobile']."<br />";
                if ($order['billing_company']) {
                    $billing_address = $order['billing_company'] . "<br />";
                }
                if ($full_customer_name) {
                    $billing_address .= $full_customer_name . "<br />";
                }
                if ($order['billing_building']) {
                    $billing_address .= $order['billing_building'] . "<br />";
                }
                if ($order['billing_address']) {
                    $billing_address .= $order['billing_address'] . "<br />";
                }
                if ($order['billing_zip'] and $order['billing_city']) {
                    $billing_address .= $order['billing_zip'] . " " . $order['billing_city'];
                }
                //if ($order['billing_telephone']) 		$billing_address.=ucfirst($this->pi_getLL('telephone')).': '.$order['billing_telephone']."<br />";
                //if ($order['billing_mobile']) 			$billing_address.=ucfirst($this->pi_getLL('mobile')).': '.$order['billing_mobile']."<br />";
                $array1 = array();
                $array2 = array();
                $array1[] = '###GENDER_SALUTATION###';
                $array2[] = mslib_fe::genderSalutation($order['billing_gender']);
                $array1[] = '###DELIVERY_FIRST_NAME###';
                $array2[] = $order['delivery_first_name'];
                $array1[] = '###DELIVERY_LAST_NAME###';
                $array2[] = preg_replace('/\s+/', ' ', $order['delivery_middle_name'] . ' ' . $order['delivery_last_name']);
                $array1[] = '###BILLING_FIRST_NAME###';
                $array2[] = $order['billing_first_name'];
                $array1[] = '###BILLING_LAST_NAME###';
                $array2[] = preg_replace('/\s+/', ' ', $order['billing_middle_name'] . ' ' . $order['billing_last_name']);
                $array1[] = '###BILLING_TELEPHONE###';
                $array2[] = $order['billing_telephone'];
                $array1[] = '###DELIVERY_TELEPHONE###';
                $array2[] = $order['delivery_telephone'];
                $array1[] = '###BILLING_MOBILE###';
                $array2[] = $order['billing_mobile'];
                $array1[] = '###DELIVERY_MOBILE###';
                $array2[] = $order['delivery_mobile'];
                $array1[] = '###BILLING_FULL_NAME###';
                $array2[] = $full_customer_name;
                $array1[] = '###FULL_NAME###';
                $array2[] = $full_customer_name;
                $array1[] = '###DELIVERY_FULL_NAME###';
                $array2[] = $delivery_full_customer_name;
                $array1[] = '###BILLING_NAME###';
                $array2[] = $order['billing_name'];
                $array1[] = '###BILLING_EMAIL###';
                $array2[] = $order['billing_email'];
                $array1[] = '###DELIVERY_EMAIL###';
                $array2[] = $order['delivery_email'];
                $array1[] = '###DELIVERY_NAME###';
                $array2[] = $order['delivery_name'];
                $array1[] = '###CUSTOMER_EMAIL###';
                $array2[] = $order['billing_email'];
                $array1[] = '###STORE_NAME###';
                $array2[] = $this->ms['MODULES']['STORE_NAME'];
                $array1[] = '###TOTAL_AMOUNT###';
                $array2[] = mslib_fe::amount2Cents($order['total_amount']);
                $array1[] = '###TOTAL_AMOUNT_RAW###';
                $array2[] = number_format($order['total_amount'], '2', '.', '');
                $ORDER_DETAILS = mslib_fe::printOrderDetailsTable($order, 'email');
                $array1[] = '###ORDER_DETAILS###';
                $array2[] = $ORDER_DETAILS;
                $array1[] = '###BILLING_ADDRESS###';
                $array2[] = $billing_address;
                $array1[] = '###DELIVERY_ADDRESS###';
                $array2[] = $delivery_address;
                $array1[] = '###CUSTOMER_ID###';
                $array2[] = $order['customer_id'];
                $array1[] = '###SHIPPING_METHOD###';
                $array2[] = $order['shipping_method_label'];
                $array1[] = '###PAYMENT_METHOD###';
                $array2[] = $order['payment_method_label'];
                $invoice = mslib_fe::getOrderInvoice($order['orders_id'], 0);
                $invoice_id = '';
                $invoice_link = '';
                if (is_array($invoice)) {
                    $invoice_id = $invoice['invoice_id'];
                    $invoice_link = '<a href="' . $this->FULL_HTTP_URL . mslib_fe::typolink($this->shop_pid . ',2002', 'tx_multishop_pi1[page_section]=download_invoice&tx_multishop_pi1[hash]=' . $invoice['hash']) . '">' . $invoice['invoice_id'] . '</a>';
                }
                $array1[] = '###INVOICE_NUMBER###';
                $array2[] = $invoice_id;
                $array1[] = '###INVOICE_LINK###';
                $array2[] = $invoice_link;
                $time = $order['crdate'];
                $long_date = strftime($this->pi_getLL('full_date_format'), $time);
                $array1[] = '###ORDER_DATE_LONG###'; // ie woensdag 23 juni, 2010
                $array2[] = $long_date;
                // backwards compatibility
                $array1[] = '###ORDER_DATE###'; // 21-12-2010 in localized format
                $array2[] = strftime("%x", $time);
                $array1[] = '###LONG_DATE###'; // ie woensdag 23 juni, 2010
                $array2[] = $long_date;
                $time = time();
                $long_date = strftime($this->pi_getLL('full_date_format'), $time);
                $array1[] = '###CURRENT_DATE_LONG###'; // ie woensdag 23 juni, 2010
                $array2[] = $long_date;
                $array1[] = '###CURRENT_DATE###'; // 21-12-2010 in localized format
                $array2[] = strftime("%x");
                $array1[] = '###TOTAL_AMOUNT###';
                $array2[] = mslib_fe::amount2Cents($order['total_amount']);
                $array1[] = '###TOTAL_AMOUNT_RAW###';
                $array2[] = number_format($order['total_amount'], '2', '.', '');
                $array1[] = '###PROPOSAL_NUMBER###';
                $array2[] = $order['orders_id'];
                $array1[] = '###ORDER_NUMBER###';
                $array2[] = $order['orders_id'];
                $array1[] = '###ORDER_LINK###';
                $array2[] = '';
                $array1[] = '###CUSTOMER_ID###';
                $array2[] = $order['customer_id'];
                $array1[] = '###CUSTOMER_COMMENTS###';
                $array2[] = $order['customer_comments'];
                $array1[] = '###MESSAGE###';
                $array2[] = $this->post['comments'];
                $array1[] = '###OLD_ORDER_STATUS###';
                $array2[] = mslib_fe::getOrderStatusName($order['status'], $order['language_id']);
                $array1[] = '###ORDER_STATUS###';
                $array2[] = mslib_fe::getOrderStatusName($orders_status, $order['language_id']);
                $array1[] = '###EXPECTED_DELIVERY_DATE###';
                if ($order['expected_delivery_date'] > 0) {
                    if ($this->ms['MODULES']['ADD_HOURS_TO_EDIT_ORDER_EXPECTED_DELIVERY_DATE'] == '1') {
                        $array2[] = strftime("%x %T", $order['expected_delivery_date']);
                    } else {
                        $array2[] = strftime("%x", $order['expected_delivery_date']);
                    }
                } else {
                    $array2[] = '';
                }
                $array1[] = '###EXPECTED_DELIVERY_DATE_LONG###';
                if ($order['expected_delivery_date'] > 0) {
                    if ($this->ms['MODULES']['ADD_HOURS_TO_EDIT_ORDER_EXPECTED_DELIVERY_DATE'] == '1') {
                        $array2[] = strftime("%x %T", $order['expected_delivery_date']);
                    } else {
                        $array2[] = strftime($this->pi_getLL('full_date_no_time_format'), $order['expected_delivery_date']);
                    }
                } else {
                    $array2[] = '';
                }
                $array1[] = '###TRACK_AND_TRACE_CODE###';
                $array2[] = $order['track_and_trace_code'];
                $array1[] = '###TRACK_AND_TRACE_LINK###';
                $array2[] = $order['track_and_trace_link'];
                $array1[] = '###BILLING_STREET_NAME###';
                $array2[] = $order['billing_street_name'];
                $array1[] = '###BILLING_ADDRESS_NUMBER###';
                $array2[] = $order['billing_address_number'];
                $array1[] = '###BILLING_ADDRESS_EXT###';
                $array2[] = $order['billing_address_ext'];
                $array1[] = '###BILLING_ZIP###';
                $array2[] = $order['billing_zip'];
                $array1[] = '###BILLING_CITY###';
                $array2[] = $order['billing_city'];
                $array1[] = '###BILLING_COUNTRY###';
                $array2[] = mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['billing_country']);
                $array1[] = '###BILLING_COUNTRY_CODE###';
                $array2[] = mslib_fe::getCountryCnIsoByEnglishName($order['billing_country']);
                $array1[] = '###DELIVERY_STREET_NAME###';
                $array2[] = $order['delivery_street_name'];
                $array1[] = '###DELIVERY_ADDRESS_NUMBER###';
                $array2[] = $order['delivery_address_number'];
                $array1[] = '###DELIVERY_ADDRESS_EXT###';
                $array2[] = $order['delivery_address_ext'];
                $array1[] = '###DELIVERY_ZIP###';
                $array2[] = $order['delivery_zip'];
                $array1[] = '###DELIVERY_CITY###';
                $array2[] = $order['delivery_city'];
                $array1[] = '###DELIVERY_COUNTRY###';
                $array2[] = mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $order['delivery_country']);
                $array1[] = '###DELIVERY_COUNTRY_CODE###';
                $array2[] = mslib_fe::getCountryCnIsoByEnglishName($order['delivery_country']);
                // dynamic variablese eof
                //hook to let other plugins further manipulate
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusMarkerReplacerProc'])) {
                    $params = array(
                            'array1' => &$array1,
                            'array2' => &$array2,
                            'order' => &$order,
                            'action_call' => &$action_call
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusMarkerReplacerProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                if ($this->post['comments']) {
                    $this->post['comments'] = str_replace($array1, $array2, $this->post['comments']);
                }
                $status_last_modified = time();
                $updateArray = array();
                $updateArray['orders_id'] = $order['orders_id'];
                $updateArray['old_value'] = $order['status'];
                $updateArray['comments'] = (!empty($this->post['comments']) ? $this->post['comments'] : '');
                $updateArray['customer_notified'] = $mail_customer;
                $updateArray['crdate'] = $status_last_modified;
                $updateArray['new_value'] = $orders_status;
                $updateArray['requester_ip_addr'] = $this->REMOTE_ADDR;
                $updateArray['action_call'] = $action_call;
                $updateArray['cruser_id'] = 0;
                if ($GLOBALS['TSFE']->fe_user->user['uid']) {
                    $updateArray['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
                }
                //hook to let other plugins further manipulate
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusInsertHistory'])) {
                    $params = array(
                            'updateArray' => &$updateArray
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusInsertHistory'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                $updateArray = mslib_befe::rmNullValuedKeys($updateArray);
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_orders_status_history', $updateArray);
                if ($orders_status == $order['status']) {
                    if (!empty($this->post['comments']) && $mail_customer) {
                        // always save the order status history even when order status is the same as the old one when e-mail to client is filled
                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                    $returnTrue = 0;
                } else {
                    // save if new order status history is different than the old status
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    $returnTrue = 1;
                }
                $updateArray = array();
                $updateArray['status'] = $orders_status;
                $updateArray['status_last_modified'] = $status_last_modified;
                $order['old_status'] = $order['status'];
                $order['status'] = $orders_status;
                $updateArray['orders_last_modified'] = time();
                $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\'' . $orders_id . '\'', $updateArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                //send e-mail
                if ($mail_customer) {
                    $subject = $this->ms['MODULES']['STORE_NAME'];
                    $message = $this->post['comments'];
                    if ($orders_status) {
                        $orders_status_name = mslib_fe::getOrderStatusName($orders_status, 0);
                        $keys = array();
                        $keys[] = 'email_order_status_changed_' . mslib_befe::strtolower($orders_status_name);
                        $keys[] = 'email_order_status_changed';
                        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusCMSKeysPostProc'])) {
                            $params = array(
                                    'keys' => &$keys,
                                    'orders_status' => $orders_status,
                                    'order' => &$order,
                                    'action_call' => &$action_call
                            );
                            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusCMSKeysPostProc'] as $funcRef) {
                                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                            }
                        }
                        foreach ($keys as $key) {
                            //$page=mslib_fe::getCMScontent($key,$GLOBALS['TSFE']->sys_language_uid);
                            $page = mslib_fe::getCMScontent($key, $order['language_id']);
                            if ($page[0]) {
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusMarkerReplacerPostProc'])) {
                                    $params = array(
                                            'array1' => &$array1,
                                            'array2' => &$array2,
                                            'page' => &$page,
                                            'action_call' => &$action_call
                                    );
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusMarkerReplacerPostProc'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                }
                                if ($page[0]['content']) {
                                    $page[0]['content'] = str_replace($array1, $array2, $page[0]['content']);
                                }
                                if ($page[0]['name']) {
                                    $page[0]['name'] = str_replace($array1, $array2, $page[0]['name']);
                                }
                                $user = array();
                                $user['email'] = $order['billing_email'];
                                $user['name'] = $order['billing_name'];
                                $user['customer_id'] = $order['customer_id'];
                                $mail_attachments = array();
                                $options = array();
                                //hook to let other plugins further manipulate
                                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusMailSendPreProc'])) {
                                    $params = array(
                                            'mail_attachments' => &$mail_attachments,
                                            'options' => &$options,
                                            'user' => &$user,
                                            'order' => $order,
                                            'orders_status' => $orders_status,
                                            'page' => $page
                                    );
                                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusMailSendPreProc'] as $funcRef) {
                                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                                    }
                                }
                                if ($user['email']) {
                                    mslib_fe::mailUser($user, $page[0]['name'], $page[0]['content'], $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME'], $mail_attachments, $options);
                                }
                                break;
                            }
                        }
                    }
                }
                if (isset($order['language_id'])) {
                    // Switch back to default language
                    mslib_befe::resetSystemLanguage();
                }
                //hook to let other plugins further manipulate
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusPostProc'])) {
                    $params = array(
                            'orders_id' => &$orders_id,
                            'orders_status' => &$orders_status,
                            'mail_customer' => &$mail_customer,
                            'order' => &$order,
                            'array1' => &$array1,
                            'array2' => &$array2,
                            'action_call' => &$action_call
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateOrderStatusPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
                if ($returnTrue) {
                    return true;
                }
            }
        }
    }
    function setSystemLanguage($sys_language_uid) {
        if (is_numeric($sys_language_uid)) {
            if (!is_array($this->defaultLanguageArray)) {
                mslib_befe::setDefaultSystemLanguage();
            }
            $language_code = mslib_befe::getLanguageIso2ByLanguageUid($sys_language_uid);
            if ($language_code != '') {
                $language_code = strtolower($language_code);
                $this->lang = $language_code;
                $this->LLkey = $language_code;
                /*
                if ($language_code=='en') {
                    // default because otherwise some locallang.xml have a language node default and also en, very annoying if it uses en, since we want it to use the default which must be english
                    $this->LLkey='default';
                }
                */
                $this->config['config']['language'] = $language_code;
                $GLOBALS['TSFE']->config['config']['language'] = $language_code;
                $GLOBALS['TSFE']->config['config']['sys_language_uid'] = $sys_language_uid;
                $GLOBALS['TSFE']->sys_language_uid = $sys_language_uid;
                $this->sys_language_uid = $sys_language_uid;
                $GLOBALS['TSFE']->sys_language_content = $this->sys_language_uid;
                $GLOBALS['TSFE']->config['config']['locale_all'] = $this->pi_getLL('locale_all');
                setlocale(LC_TIME, $GLOBALS['TSFE']->config['config']['locale_all']);
            }
        }
    }
    // get tree
    function setDefaultSystemLanguage() {
        if ($this->LLkey) {
            $this->defaultLanguageArray = array();
            $this->defaultLanguageArray['lang'] = $this->lang;
            $this->defaultLanguageArray['LLkey'] = $this->LLkey;
            $this->defaultLanguageArray['config']['config']['language'] = $this->config['config']['language'];
            $this->defaultLanguageArray['config']['config']['sys_language_uid'] = $this->sys_language_uid;
            $this->defaultLanguageArray['config']['config']['locale_all'] = $GLOBALS['TSFE']->config['config']['locale_all'];
        }
    }
    // convert the string of URL to <a href="URL">URL</a>
    function getLanguageIso2ByLanguageUid($id) {
        if (!is_numeric($id)) {
            return false;
        }
        //$this->msDebug=1;
        $record = mslib_befe::getRecord($id, 'sys_language syslang, static_languages statlang', 'syslang.uid', array('syslang.hidden=0 and syslang.static_lang_isocode=statlang.uid'), 'statlang.lg_iso_2');
        if ($record['lg_iso_2']) {
            return $record['lg_iso_2'];
        }
    }
    function resetSystemLanguage() {
        // reset to default
        if (is_array($this->defaultLanguageArray)) {
            $this->lang = $this->defaultLanguageArray['lang'];
            $this->LLkey = $this->defaultLanguageArray['LLkey'];
            $this->config['config']['language'] = $this->defaultLanguageArray['config']['config']['language'];
            $GLOBALS['TSFE']->config['config']['language'] = $this->defaultLanguageArray['config']['config']['language'];
            $GLOBALS['TSFE']->config['config']['sys_language_uid'] = $this->defaultLanguageArray['config']['config']['sys_language_uid'];
            $GLOBALS['TSFE']->sys_language_uid = $this->defaultLanguageArray['config']['config']['sys_language_uid'];
            setlocale(LC_TIME, $this->defaultLanguageArray['config']['config']['locale_all']);
            $this->sys_language_uid = $this->defaultLanguageArray['config']['config']['sys_language_uid'];
            $GLOBALS['TSFE']->sys_language_content = $this->sys_language_uid;
        }
    }
    public function updateOrderProductStatus($orders_id, $order_product_id, $orders_status) {
        if (!is_numeric($orders_id)) {
            return false;
        }
        if (!is_numeric($order_product_id)) {
            return false;
        }
        $updateArray = array();
        $updateArray['status'] = $orders_status;
        $query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', 'orders_id=\'' . $orders_id . '\' and orders_products_id = \'' . $order_product_id . '\'', $updateArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    public function getHashedPassword($password) {
        $objPHPass = null;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('t3sec_saltedpw')) {
            require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3sec_saltedpw') . 'res/staticlib/class.tx_t3secsaltedpw_div.php');
            if (tx_t3secsaltedpw_div::isUsageEnabled()) {
                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('t3sec_saltedpw') . 'res/lib/class.tx_t3secsaltedpw_phpass.php');
                $objPHPass = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_t3secsaltedpw_phpass');
            }
        }
        if (!$objPHPass && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
            if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled()) {
                $objPHPass = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::getDefaultSaltingHashingMethod());
            }
        }
        if ($objPHPass) {
            $password = $objPHPass->getHashedPassword($password);
        } else if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('kb_md5fepw')) { //if kb_md5fepw is installed, crypt password
            $password = md5($password);
        }
        return $password;
    }
    public function generateRandomPassword($length = 10, $string = '', $type = 'pronounceable') {
        if (!$type and $string) {
            $type = 'pronounceable';
        } elseif (!$type) {
            $type = 'unpronounceable';
        }
        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'res/Password.php');
        $suite = new Text_Password();
        switch ($type) {
            case 'pronounceable':
                $password = $suite->create($length, 'pronounceable', $string);
                $password .= rand(10, 99);
                break;
            case 'unpronounceable':
                $password = $suite->create($length, 'unpronounceable', $string . '0123456789!@#$%^&*(');
                break;
            case 'shuffle':
            default:
                $password = $suite->createFromLogin($string, 'shuffle');
                $password .= rand(10, 99);
                break;
        }
        return $password;
    }
    public function storeProductsKeywordSearch($keyword, $negative_results = 0, $categories_id = 0) {
        $continue = true;
        //hook to let other plugins further manipulate the redirect link
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['storeProductsKeywordSearchPreProc'])) {
            $params = array(
                    'continue' => &$continue
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['storeProductsKeywordSearchPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if ($continue) {
            $insertArray = array();
            $insertArray['keyword'] = $keyword;
            $insertArray['ip_address'] = $this->REMOTE_ADDR;
            $insertArray['crdate'] = time();
            $insertArray['negative_results'] = $negative_results;
            $insertArray['http_host'] = $this->HTTP_HOST;
            $insertArray['page_uid'] = $this->shop_pid;
            if ($GLOBALS['TSFE']->fe_user->user['uid']) {
                $insertArray['customer_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
            }
			if ($this->conf['user_id']) {
				$insertArray['customer_id'] = $this->conf['user_id'];
			}
            if (!$categories_id && is_numeric($this->get['categories_id']) && $this->get['categories_id'] > 0) {
                $categories_id = $this->get['categories_id'];
            }
            if (is_numeric($categories_id) && $categories_id > 0) {
                $insertArray['categories_id'] = $categories_id;
            }
            $filter = array();
            $filter[] = 'ip_address=\'' . addslashes($this->REMOTE_ADDR) . '\'';
            $record = mslib_befe::getRecord($keyword, 'tx_multishop_products_search_log', 'keyword', $filter);
            if (!is_array($record) || (time() - $record['crdate']) > 180) {
                $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_search_log', $insertArray);
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            }
        }
    }
    public function storeCustomerCartContent($content, $customer_id = '', $is_checkout = 0) {
        if (!$customer_id && $GLOBALS['TSFE']->fe_user->user['uid']) {
            $customer_id = $GLOBALS['TSFE']->fe_user->user['uid'];
        }
        $insertArray = array();
        $insertArray['contents'] = serialize($content);
        $insertArray['customer_id'] = $customer_id;
        $insertArray['is_checkout'] = $is_checkout;
        $insertArray['crdate'] = time();
        $insertArray['session_id'] = $GLOBALS['TSFE']->fe_user->id;
        $insertArray['ip_address'] = $this->REMOTE_ADDR;
        $insertArray['page_uid'] = $this->shop_pid;
        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_cart_contents', $insertArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    public function storeNotificationMessage($title = '', $message = '', $customer_id = 0, $message_type = 'generic') {
        if (!$customer_id && $GLOBALS['TSFE']->fe_user->user['uid']) {
            $customer_id = $GLOBALS['TSFE']->fe_user->user['uid'];
        }
        $insertArray = array();
        $insertArray['title'] = $title;
        $insertArray['message'] = $message;
        $insertArray['message_type'] = $message_type;
        $insertArray['unread'] = 1;
        $insertArray['crdate'] = time();
        $insertArray['customer_id'] = $customer_id;
        $insertArray['ip_address'] = $this->REMOTE_ADDR;
        $insertArray['session_id'] = $GLOBALS['TSFE']->fe_user->id;
        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_notification', $insertArray);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    }
    public function getPageTree($pid = '0', $cates = array(), $times = 0, $include_itself = 0) {
        if ($include_itself) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,keywords,description', 'pages', 'hidden = 0 and deleted = 0 and (uid=' . $pid . ')', '');
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $cates[$row['uid']] = array(
                        '|' . str_repeat("--", $times - 1) . "-[ " . $row['title'],
                        $row
                );
                $cates = mslib_befe::getPageTree($row['uid'], $cates, $times);
            }
            $times++;
        }
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,keywords,description', 'pages', 'hidden = 0 and deleted = 0 and pid=' . $pid . '', '');
        $times++;
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $cates[$row['uid']] = array(
                    '|' . str_repeat("--", $times - 1) . "-[ " . $row['title'],
                    $row
            );
            $cates = mslib_befe::getPageTree($row['uid'], $cates, $times);
        }
        $times--;
        return $cates;
    }
    public function str2href($str) {
        $str = preg_replace('/\s(\w+:\/\/)(\S+)/', ' <a href="\\1\\2" target="_blank">\\1\\2</a>', $str);
    }
    public function str_highlight($text, $needle = '', $options = null, $highlight = null) {
        $STR_HIGHLIGHT_SIMPLE = 1;
        $STR_HIGHLIGHT_WHOLEWD = 2;
        $STR_HIGHLIGHT_CASESENS = 0;
        $STR_HIGHLIGHT_STRIPLINKS = 8;
        if (!$needle) {
            return $text;
        }
        // Default highlighting
        if ($highlight === null) {
            $highlight = '<strong class="highlight">\1</strong>';
        }
        // Select pattern to use
        if ($options & $STR_HIGHLIGHT_SIMPLE) {
            $pattern = '#(%s)#';
        } else {
            $pattern = '#(?!<.*?)(%s)(?![^<>]*?>)#';
            $sl_pattern = '#<a\s(?:.*?)>(%s)</a>#';
        }
        // Case sensitivity
        /*
			if ($options ^ $STR_HIGHLIGHT_CASESENS) {

				$pattern .= 'i';
				$sl_pattern .= 'i';
			}
		*/
        $pattern .= 'i';
        $sl_pattern .= 'i';
        $needle = (array)$needle;
        if (is_array($needle) && count($needle)) {
            foreach ($needle as $needle_s) {
                $needle_s = preg_quote($needle_s);
                // Escape needle with optional whole word check
                if ($options & $STR_HIGHLIGHT_WHOLEWD) {
                    $needle_s = '\b' . $needle_s . '\b';
                }
                // Strip links
                if ($options & $STR_HIGHLIGHT_STRIPLINKS) {
                    $sl_regex = sprintf($sl_pattern, $needle_s);
                    $text = preg_replace($sl_regex, '\1', $text);
                }
                $regex = sprintf($pattern, $needle_s);
                $text = preg_replace($regex, $highlight, $text);
            }
        }
        return $text;
    }
    public function cacheLite($action = '', $key = '', $timeout = '', $serialized = 0, $content = '', $options = array()) {
        if (!count($options)) {
            // Default cache lite configuration
            $options = array(
                    'caching' => true,
                    'cacheDir' => PATH_site . 'uploads/tx_multishop/tmp/cache/',
                    'lifeTime' => $timeout
            );
        }
        if (!$options['cacheDir'] || !is_dir($options['cacheDir'])) {
            echo 'Cache lite directory does not exist: '.$options['cacheDir'];
            die();
        }
        if ($action == 'delete_all') {
            if ($this->conf['debugPurgeCacheLite']) {
                $subject = '[cacheLite] ' . $this->HTTP_HOST . ' delete cache requested by ' . htmlspecialchars($this->REMOTE_ADDR);
                $body = '';
                $body .= '<strong>IP address:</strong><br/>' . $this->REMOTE_ADDR . '<br/><br/>';
                $body .= '<strong>Browser:</strong><br/>' . htmlspecialchars($this->server['HTTP_USER_AGENT']) . '<br/><br/>';
                $body .= '<strong>Referer:</strong><br/>' . htmlspecialchars($this->server['HTTP_REFERER']) . '<br/><br/>';
                $body .= '<strong>Time:</strong><br/>' . ucfirst(strftime($this->pi_getLL('full_date_format'))) . '<br/><br/>';
                $body .= '<strong>Options:</strong><br/><pre>' . print_r($options,1) . '</pre><br/><br/>';
                $body .= '<strong>Backtrace:</strong><br/>';
                $body .= mslib_befe::print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                mslib_befe::mailDev($subject, $body);
            }
            if ($options['cacheDir'] and !strstr($options['cacheDir'], '..') && is_dir($options['cacheDir'])) {
                // We use find to only delete files and not directories. This is to prevent bugs with concurrent sessions
                $command = 'find '.$options['cacheDir'].' -type f -delete';
                // Old way that causes sometimes error "Cache_Lite : Unable to write cache file" in concurrent session:
                //$command = 'rm -rf ' . $options['cacheDir'].'/*';
                exec($command);
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['cacheLiteDeleteAllPostProc'])) {
                    $params = array();
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['cacheLiteDeleteAllPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                    }
                }
            }
        } else {
            if (!class_exists('Cache_Lite')) {
                require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'res/Cache_Lite-1.7.16/Cache/Lite.php');
            }
            $Cache_Lite = new Cache_Lite($options);
            //$Cache_Lite =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Cache_Lite');
            //$string = md5($key);
            $string = $key;
            switch ($action) {
                case 'get':
                    // get cache
                    $content = $Cache_Lite->get($string);
                    if ($serialized) {
                        $content = unserialize($content);
                    }
                    return $content;
                    break;
                case 'save':
                    // save cache
                    if ($serialized) {
                        $content = serialize($content);
                    }
                    $Cache_Lite->save($content, $string);
                    break;
                case 'delete':
                    // removes the cache
                    // somehow the get method always return false, disabled by Widy 11/09/2019
                    //if ($Cache_Lite->get($string)) {
                    $Cache_Lite->remove($string);
                    //}
                    break;
            }
        }
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['cacheLitePostProc'])) {
            $params = array(
                    'action' => $action
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['cacheLitePostProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
    }
    public function print_r($array_in, $title = '') {
        if (is_object($array_in)) {
            $json = json_encode($array_in);
            $array_in = json_decode($json, true);
        }
        if (is_array($array_in)) {
            if (count($array_in) == 0) {
//				$result .= '<tr><td><font face="Verdana,Arial" size="1"><strong>EMPTY!</strong></font></td></tr>';
            } else {
                $result = '
				<table class="table table-striped table-bordered">';
                if ($title) {
                    $result .= '
					<tr>
					<th colspan="2">
					' . htmlspecialchars($title) . '
					</th>
					</tr>
					';
                }
                if (is_array($array_in) && count($array_in)) {
                    foreach ($array_in as $key => $val) {
                        if ((string)$key or $val) {
                            if (!$tr_type or $tr_type == 'even') {
                                $tr_type = 'odd';
                            } else {
                                $tr_type = 'even';
                            }
                            $result .= '<tr class="' . $tr_type . '">
							<td valign="top" class="print_r_key">' . htmlspecialchars((string)$key) . '</td>
							<td class="print_r_value">';
                            if (is_array($val)) {
//							$result .= t3lib_utility_Debug::viewArray($val);
                                $result .= mslib_befe::print_r($val);
                            } elseif (is_object($val)) {
                                $string = '';
                                if (method_exists($val, '__toString')) {
                                    $string .= get_class($val) . ': ' . (string)$val;
                                } else {
                                    $string .= print_r($val, true);
                                }
                                $result .= '' . nl2br(htmlspecialchars($string)) . '<br />';
                            } else {
                                if (gettype($val) == 'object') {
                                    $string = 'Unknown object';
                                } else {
                                    $string = (string)$val;
                                }
                                $result .= nl2br(htmlspecialchars($string)) . '<br />';
                            }
                            $result .= '</td>
						</tr>';
                        }
                    }
                }
                $result .= '</table>';
            }
        } else {
            $result = '<table class="table table-striped table-bordered">
				<tr>
					<td>' . nl2br(htmlspecialchars((string)$array_in)) . '</td>
				</tr>
			</table>'; // Output it as a string.
        }
        return $result;
    }
    function mailDev($subject, $body, $overrideUsers = array()) {
        $sendEmail = 1;
        //hook to let other plugins further manipulate the settings
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['mailDevPreProc'])) {
            $params = array(
                    'subject' => &$subject,
                    'body' => &$body,
                    'sendEmail' => &$sendEmail,
                    'overrideUsers' => &$overrideUsers
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['mailDevPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        $mailTo = array();
        if (is_array($overrideUsers) && count($overrideUsers)) {
            if (isset($overrideUsers['email'])) {
                // Send only to one user
                $mailTo[] = $overrideUsers;
            } else {
                // Send to multiple users
                $mailTo = $overrideUsers;
            }
        } elseif ($this->conf['developer_email']) {
            $user = array();
            $user['name'] = $this->conf['developer_email'];
            $user['email'] = $this->conf['developer_email'];
            $mailTo[] = $user;
        } else {
            if (isset($this->ms['MODULES']['DEVELOPER_EMAIL'])) {
                $user = array();
                $user['name'] = $this->ms['MODULES']['DEVELOPER_EMAIL'];
                $user['email'] = $this->ms['MODULES']['DEVELOPER_EMAIL'];
                $mailTo[] = $user;
            }
        }
        if ($sendEmail && count($mailTo)) {
            foreach ($mailTo as $mailuser) {
                mslib_fe::mailUser($mailuser, $subject, $body, $this->ms['MODULES']['STORE_EMAIL'], $this->ms['MODULES']['STORE_NAME']);
            }
        }
    }
    public function loadLanguages() {
        $this->linkVars = $GLOBALS['TSFE']->linkVars;
        $useSysLanguageTitle = trim($this->conf['useSysLanguageTitle']) ? trim($this->conf['useSysLanguageTitle']) : 0;
        $useIsoLanguageCountryCode = trim($this->conf['useIsoLanguageCountryCode']) ? trim($this->conf['useIsoLanguageCountryCode']) : 0;
        $useIsoLanguageCountryCode = $useSysLanguageTitle ? 0 : $useIsoLanguageCountryCode;
        $useSelfLanguageTitle = trim($this->conf['useSelfLanguageTitle']) ? trim($this->conf['useSelfLanguageTitle']) : 0;
        $useSelfLanguageTitle = ($useSysLanguageTitle || $useIsoLanguageCountryCode) ? 0 : $useSelfLanguageTitle;
        $tableA = 'sys_language';
        $tableB = 'static_languages';
        $languagesUidsList = trim($this->cObj->data['tx_srlanguagemenu_languages']) ? trim($this->cObj->data['tx_srlanguagemenu_languages']) : trim($this->conf['languagesUidsList']);
        $languages = array();
        $languagesLabels = array();
        // Set default language
        $defaultLanguageISOCode = trim($this->conf['defaultLanguageISOCode']) ? mslib_befe::strtoupper(trim($this->conf['defaultLanguageISOCode'])) : 'EN';
        $this->ms['MODULES']['COUNTRY_ISO_NR'] = trim($this->conf['defaultCountryISOCode']) ? mslib_befe::strtoupper(trim($this->conf['defaultCountryISOCode'])) : '';
        $languages[] = mslib_befe::strtolower($defaultLanguageISOCode) . ($this->ms['MODULES']['COUNTRY_ISO_NR'] ? '_' . $this->ms['MODULES']['COUNTRY_ISO_NR'] : '');
        $this->languagesUids[] = '0';
        // Get the language codes and labels for the languages set in the plugin list
        $selectFields = $tableA . '.uid, ' . $tableA . '.title, ' . $tableB . '.*';
        $table = $tableA . ' LEFT JOIN ' . $tableB . ' ON ' . $tableA . '.flag=' . $tableB . '.cn_iso_2';
        // Ignore IN clause if language list is empty. This means that all languages found in the sys_language table will be used
        if (!empty($languagesUidsList)) {
            $whereClause = $tableA . '.uid IN (' . $languagesUidsList . ') ';
        } else {
            $whereClause = '1=1 ';
        }
        $whereClause .= $this->cObj->enableFields($tableA);
        $whereClause .= $this->cObj->enableFields($tableB);
        //$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selectFields, $table, $whereClause);
        // If $languagesUidsList is not empty, the languages will be sorted in the order it specifies
        $languagesUidsArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $languagesUidsList, 1);
        $index = 0;
        $str = "select * from sys_language where hidden=0 order by title";
        $res = $GLOBALS['TYPO3_DB']->sql_query($str);
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $key++;
            $languages[$key] = $row['uid'];
            $languagesLabels[$key]['key'] = $row['uid'];
            $languagesLabels[$key]['flag'] = $row['flag'];
            if ($row['flag']) {
                if ($this->cookie['multishop_admin_language'] == $row['uid']) {
                    $this->cookie['multishop_admin_language'] = $row['flag'];
                }
            }
            $languagesLabels[$key]['value'] = $row['title'];
            $this->languages[$key]['value'] = $row['title'];
            $this->languagesUids[$key] = $row['uid'];
        }
    }
    public function array_flatten($a, $f = array()) {
        if (!$a || !is_array($a)) {
            return '';
        }
        if (is_array($a) && count($a)) {
            foreach ($a as $k => $v) {
                if (is_array($v)) {
                    $f = mslib_befe::array_flatten($v, $f);
                } else {
                    $f[$k] = $v;
                }
            }
        }
        return $f;
    }
    // utf-8 support
    function getNestedItems($input, $level = array()) {
        $output = array();
        foreach ($input as $key => $item) {
            $level[] = $key;
            if (is_array($item)) {
                $output = (array)$output + (array)mslib_befe::getNestedItems($item, $level);
            } else {
                $output[mslib_fe::rewritenamein((string)implode('_', $level), '', 1)] = $item;
            }
            array_pop($level);
        }
        return $output;
    }
    // utf-8 support
    public function loginAsUser($uid, $section = '') {
        if (!is_numeric($uid)) {
            return false;
        }
        $user = mslib_fe::getUser($uid);
        if ($user['uid']) {
            $GLOBALS['TSFE']->fe_user->logoff();
            $GLOBALS['TSFE']->loginUser = 0;
            $fe_user = $GLOBALS['TSFE']->fe_user;
            $fe_user->createUserSession(array('uid' => $uid));
            $fe_user->user = $fe_user->getRawUserByUid($uid);
            $fe_user->fetchGroupData();
            $GLOBALS['TSFE']->loginUser = 1;
            /*
			 * Old style, dont use this anymore. use above approach which uses the $uid
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
			*/
            // auto login the user
            if (is_numeric($this->conf['login_as_customer_target_pid'])) {
                $targetPid = $this->conf['login_as_customer_target_pid'];
            } else {
                $targetPid = $this->shop_pid;
            }
            $redirect_url = $this->FULL_HTTP_URL . mslib_fe::typolink($targetPid);
            //hook to let other plugins further manipulate the redirect link
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loginAsUserRedirectLinkPreProc'])) {
                $params = array(
                        'user' => $user,
                        'redirect_url' => &$redirect_url,
                        'section' => &$section
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['loginAsUserRedirectLinkPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            if ($redirect_url) {
                header("Location: " . $redirect_url);
            }
            exit();
        }
    }
    // utf-8 support
    public function updateImportedProductsLockedFields($products_id, $table, $updateArray) {
        $lockedFields = array();
        $lockedFields['tx_multishop_products'][] = 'sku_code';
        $lockedFields['tx_multishop_products'][] = 'products_price';
        $lockedFields['tx_multishop_products'][] = 'products_vat_rate';
        $lockedFields['tx_multishop_products'][] = 'products_name';
        $lockedFields['tx_multishop_products'][] = 'products_model';
        $lockedFields['tx_multishop_products'][] = 'products_quantity';
        $lockedFields['tx_multishop_products'][] = 'products_status';
        $lockedFields['tx_multishop_products_description'][] = 'products_name';
        $lockedFields['tx_multishop_products_description'][] = 'products_shortdescription';
        $lockedFields['tx_multishop_products_description'][] = 'products_description';
        $lockedFields['tx_multishop_products_to_categories'][] = 'categories_id';
        $lockedFields['tx_multishop_specials'][] = 'specials_new_products_price';
        $skip = 0;
        //hook to let other plugins further manipulate the settings
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateImportedProductsLockedFieldsPreProc'])) {
            $params = array(
                    'products_id' => &$products_id,
                    'table' => &$table,
                    'updateArray' => &$updateArray,
                    'lockedFields' => &$lockedFields,
                    'skip' => &$skip
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['updateImportedProductsLockedFieldsPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        if (!$skip) {
            if (is_numeric($products_id) and $table) {
                // get fields that we need to take care of
                $query = $GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
                        $table, // FROM ...
                        'products_id=' . $products_id, // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                    if (is_array($lockedFields[$table]) && count($lockedFields[$table])) {
                        foreach ($lockedFields[$table] as $field_key) {
                            $enableLock = 0;
                            $fieldsToLock = array();
                            if ($row[$field_key] != $updateArray[$field_key]) {
                                $fieldsToLock[] = $field_key;
                            }
                            if (count($fieldsToLock)) {
                                foreach ($fieldsToLock as $field_key) {
                                    // add to locking table with original value
                                    $filter = array();
                                    $filter[] = 'products_id=' . $row['products_id'];
                                    if (!mslib_befe::ifExists($field_key, 'tx_multishop_products_locked_fields', 'field_key', $filter)) {
                                        $insertArray = array();
                                        $insertArray['field_key'] = $field_key;
                                        $insertArray['products_id'] = $row['products_id'];
                                        $insertArray['crdate'] = time();
                                        $insertArray['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
                                        $insertArray['original_value'] = $row[$field_key];
                                        $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_locked_fields', $insertArray);
                                        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    // utf-8 support
    public function ifExists($value = '', $table, $field = '', $additional_where = array()) {
        if ($table) {
            $filter = array();
            if (isset($value) and isset($field) && $field != '') {
                $filter[] = $field . '="' . addslashes($value) . '"';
            }
            if (is_array($additional_where) && count($additional_where)) {
                foreach ($additional_where as $item) {
                    $filter[] = $item;
                }
            }
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('1', // SELECT ...
                    $table, // FROM ...
                    ((is_array($filter) && count($filter)) ? implode(' AND ', $filter) : ''), // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '1' // LIMIT ...
            );
            if ($this->msDebug) {
                return $query;
            }
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                return true;
            }
        }
    }
    // weight list for shipping costs page
    public function getImportedProductsLockedFields($products_id) {
        if (is_numeric($products_id)) {
            $skip = 0;
            //hook to let other plugins further manipulate the settings
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['getImportedProductsLockedFieldsPreProc'])) {
                $params = array(
                        'products_id' => &$products_id,
                        'skip' => &$skip
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['getImportedProductsLockedFieldsPreProc'] as $funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                }
            }
            if (!$skip) {
                $query = $GLOBALS['TYPO3_DB']->SELECTquery('field_key', // SELECT ...
                        'tx_multishop_products_locked_fields', // FROM ...
                        'products_id=' . $products_id, // WHERE...
                        '', // GROUP BY...
                        '', // ORDER BY...
                        '' // LIMIT ...
                );
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
                    $array = array();
                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                        $array[] = $row['field_key'];
                    }
                    return $array;
                }
            }
        }
    }
    public function readPageAccess($id, $perms_clause) {
        if ((string)$id != '') {
            $id = intval($id);
            if (!$id) {
                if ($GLOBALS['BE_USER']->isAdmin()) {
                    $path = '/';
                    $pageinfo['_thePath'] = $path;
                    return $pageinfo;
                }
            } else {
                $pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('pages', $id, '*', ($perms_clause ? ' AND ' . $perms_clause : ''));
                if ($pageinfo['uid'] && $GLOBALS['BE_USER']->isInWebMount($id, $perms_clause)) {
                    \TYPO3\CMS\Backend\Utility\BackendUtility::workspaceOL('pages', $pageinfo);
                    \TYPO3\CMS\Backend\Utility\BackendUtility::fixVersioningPid('pages', $pageinfo);
                    list($pageinfo['_thePath'], $pageinfo['_thePathFull']) = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordPath(intval($pageinfo['uid']), $perms_clause, 15, 1000);
                    return $pageinfo;
                }
            }
        }
        return false;
    }
    public function isValidDate($date) {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }
        return false;
    }
    public function isValidDateTime($dateTime) {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $dateTime, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }
        return false;
    }
    public function convertLocaleDateToInternationalDateFormat($date_input) {
        if (!empty($date_input)) {
            $have_time = false;
            $date_delimeter = '-';
            if (strpos($date_input, '/') !== false) {
                $date_delimeter = '/';
            }
            if (strpos($date_input, '.') !== false) {
                $date_delimeter = '.';
            }
            if (strpos($date_input, ':') !== false) {
                $have_time = true;
            }
            $time = '';
            $date = $date_input;
            if ($have_time) {
                list($date, $time) = explode(" ", $date_input);
            }
            list($d, $m, $y) = explode($date_delimeter, $date);
            $date_input = $y . '-' . $m . '-' . $d;
            if ($have_time) {
                $date_input .= ' ' . $time;
            }
            return $date_input;
        }
        return false;
    }
    public function ucfirst($value) {
        $csConvObj = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->csConvObj : $GLOBALS['TSFE']->csConvObj);
        $charset = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->charSet : $GLOBALS['TSFE']->metaCharset);
        return $csConvObj->convCaseFirst($charset, $value, 'toUpper');
    }
    public function strlen($value) {
        $csConvObj = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->csConvObj : $GLOBALS['TSFE']->csConvObj);
        $charset = (TYPO3_MODE == 'BE' ? $GLOBALS['LANG']->charSet : $GLOBALS['TSFE']->metaCharset);
        return $csConvObj->strlen($charset, $value);
    }
    public function createSelectboxWeightsList($selected = '', $start_value = '', $weights_list = array()) {
        if (!count($weights_list)) {
            // default weights list
            $weights_list = array(
                    '0.02' => '0,02',
                    '0.05' => '0,05',
                    '0.1' => '0,1',
                    '0.25' => '0,25',
                    '0.35' => '0,35',
                    '0.5' => '0,5',
                    '0.75' => '0,75',
                    '1' => '1',
                    '1.25' => '1,25',
                    '1.5' => '1,5',
                    '1.75' => '1,75',
                    '2' => '2',
                    '2.25' => '2,25',
                    '2.5' => '2,5',
                    '2.75' => '2,75',
                    '3' => '3',
                    '3.25' => '3,25',
                    '3.5' => '3,5',
                    '3.75' => '3,75',
                    '4' => '4',
                    '4.25' => '4,25',
                    '4.5' => '4,5',
                    '4.75' => '4,75',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                    '15' => '15',
                    '20' => '20',
                    '25' => '25',
                    '30' => '30',
                    '35' => '35',
                    '40' => '40',
                    '45' => '45',
                    '50' => '50',
                    '100' => '100',
                    '101' => 'End'
            );
        }
        $selectbox_options = array();
        if (is_array($weights_list) && count($weights_list)) {
            foreach ($weights_list as $weight_value => $weight_label) {
                if (!empty($start_value) && $start_value < 101) {
                    if ($weight_value >= $start_value) {
                        if (empty($selected)) {
                            if ($weight_value == 101) {
                                $selectbox_options[] = '<option value="' . $weight_value . '" selected="selected">' . $weight_label . '</option>';
                            } else {
                                $selectbox_options[] = '<option value="' . $weight_value . '">' . $weight_label . '</option>';
                            }
                        } else {
                            if ($selected == $weight_value) {
                                $selectbox_options[] = '<option value="' . $weight_value . '" selected="selected">' . $weight_label . '</option>';
                            } else {
                                $selectbox_options[] = '<option value="' . $weight_value . '">' . $weight_label . '</option>';
                            }
                        }
                    }
                } else {
                    if (empty($selected)) {
                        if ($weight_value == 101) {
                            $selectbox_options[] = '<option value="' . $weight_value . '" selected="selected">' . $weight_label . '</option>';
                        } else {
                            $selectbox_options[] = '<option value="' . $weight_value . '">' . $weight_label . '</option>';
                        }
                    } else {
                        if ($selected == $weight_value) {
                            $selectbox_options[] = '<option value="' . $weight_value . '" selected="selected">' . $weight_label . '</option>';
                        } else {
                            $selectbox_options[] = '<option value="' . $weight_value . '">' . $weight_label . '</option>';
                        }
                    }
                }
            }
        }
        $selectbox_str = implode("\n", $selectbox_options);
        return $selectbox_str;
    }
    function printInvoiceOrderDetailsTable($order, $invoice_number, $prefix = '', $display_currency_symbol = 1, $table_type = 'invoice') {
        $template = '';
        switch ($table_type) {
            case 'invoice':
                if ($this->conf['order_details_table_invoice_pdf_tmpl_path']) {
                    $template = $this->cObj->fileResource($this->conf['order_details_table_invoice_pdf_tmpl_path']);
                } else {
                    $template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop') . 'templates/order_details_table_invoice_pdf.tmpl');
                }
                break;
            case 'packingslip':
                if ($this->conf['order_details_table_packingslip_pdf_tmpl_path']) {
                    $template = $this->cObj->fileResource($this->conf['order_details_table_packingslip_pdf_tmpl_path']);
                } else {
                    $template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('multishop') . 'templates/order_details_table_packingslip_pdf.tmpl');
                }
                break;
        }
        //hook to let other plugins further manipulate the replacers
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsPreProc'])) {
            $params_internal = array(
                    'template' => &$template,
                    'order' => &$order,
                    'invoice_number' => &$invoice_number,
                    'prefix' => &$prefix,
                    'display_currency_symbol' => &$display_currency_symbol,
                    'table_type' => &$table_type
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
            }
        }
        if (is_array($order['products']) && count($order['products'])) {
            $contentItem = '';
            $displayDiscountColumn = 0;
            // First check if orders products rows have an discount amount
            foreach ($order['products'] as $product) {
                if ($product['discount_amount'] > 0) {
                    $displayDiscountColumn = 1;
                    break;
                }
            }
        }
        $customer_currency = 1;
        // Extract the subparts from the template
        $subparts = array();
        $subparts['template'] = $this->cObj->getSubpart($template, '###TEMPLATE###');
        $subparts['HEADER_NORMAL_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###HEADER_NORMAL_WRAPPER###');
        $subparts['HEADER_INCLUDE_VAT_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###HEADER_INCLUDE_VAT_WRAPPER###');
        $subparts['HEADER_EXCLUDE_VAT_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###HEADER_EXCLUDE_VAT_WRAPPER###');
        // items wrapper
        $subparts['ITEMS_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###ITEMS_WRAPPER###');
        $subparts['ITEM_WRAPPER'] = $this->cObj->getSubpart($subparts['ITEMS_WRAPPER'], '###ITEM_WRAPPER###');
        $subparts['ITEM_ATTRIBUTES_WRAPPER'] = $this->cObj->getSubpart($subparts['ITEMS_WRAPPER'], '###ITEM_ATTRIBUTES_WRAPPER###');
        //bottom row
        $subparts['SUBTOTAL_INCLUDE_VAT_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###SUBTOTAL_INCLUDE_VAT_WRAPPER###');
        $subparts['SUBTOTAL_EXCLUDE_VAT_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###SUBTOTAL_EXCLUDE_VAT_WRAPPER###');
        $subparts['DISCOUNT_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###DISCOUNT_WRAPPER###');
        $subparts['NEWSUBTOTAL_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###NEWSUBTOTAL_WRAPPER###');
        $subparts['TOTAL_VAT_ROW_INCLUDE_VAT'] = $this->cObj->getSubpart($subparts['template'], '###TOTAL_VAT_ROW_INCLUDE_VAT###');
        // single packing, shipping, payment costs line
        $subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'] = $this->cObj->getSubpart($subparts['template'], '###SINGLE_SHIPPING_PACKING_COSTS_WRAPPER###');
        // parsing
        $subpartArray = array();
        //ITEMS_HEADER_WRAPPER
        $markerArray = array();
        $markerArray['LABEL_HEADER_QTY'] = ucfirst($this->pi_getLL('qty'));
        $markerArray['LABEL_HEADER_PRODUCT_NAME'] = $this->pi_getLL('products_name');
        $markerArray['LABEL_HEADER_SKU'] = $this->pi_getLL('sku_number', 'SKU');
        $markerArray['LABEL_HEADER_QUANTITY'] = $this->pi_getLL('qty');
        $markerArray['LABEL_HEADER_TOTAL'] = $this->pi_getLL('total');
        $markerArray['LABEL_HEADER_PRICE'] = $this->pi_getLL('price');
        //hook to let other plugins further manipulate the replacers
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableHeaderNormalPostProc'])) {
            $params_internal = array(
                    'markerArray' => &$markerArray,
                    'table_type' => $table_type,
                    'order' => &$order
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableHeaderNormalPostProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
            }
        }
        $subpartArray['###HEADER_NORMAL_WRAPPER###'] = $this->cObj->substituteMarkerArray($subparts['HEADER_NORMAL_WRAPPER'], $markerArray, '###|###');
        $markerArray = array();
        $markerArray['LABEL_HEADER_VAT'] = $this->pi_getLL('vat');
        $markerArray['LABEL_HEADER_ITEM_NORMAL_PRICE'] = $this->pi_getLL('normal_price');
        $markerArray['LABEL_HEADER_ITEM_DISCOUNT'] = '';
        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
            $markerArray['LABEL_HEADER_ITEM_FINAL_PRICE'] = $this->pi_getLL('final_price_inc_vat');
        } else {
            $markerArray['LABEL_HEADER_ITEM_FINAL_PRICE'] = $this->pi_getLL('final_price_ex_vat');
        }
        //hook to let other plugins further manipulate the replacers
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableHeaderIncludeExcludeVatPostProc'])) {
            $params_internal = array(
                    'markerArray' => &$markerArray,
                    'table_type' => $table_type,
                    'order' => &$order
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableHeaderIncludeExcludeVatPostProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
            }
        }
        if ($displayDiscountColumn) {
            $markerArray['LABEL_HEADER_ITEM_DISCOUNT'] = '<th align="right" class="cell_products_normal_price">' . $this->pi_getLL('discount') . '</th>';
        }
        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
            $subpartArray['###HEADER_INCLUDE_VAT_WRAPPER###'] = $this->cObj->substituteMarkerArray($subparts['HEADER_INCLUDE_VAT_WRAPPER'], $markerArray, '###|###');
        } else {
            $subpartArray['###HEADER_EXCLUDE_VAT_WRAPPER###'] = $this->cObj->substituteMarkerArray($subparts['HEADER_EXCLUDE_VAT_WRAPPER'], $markerArray, '###|###');
        }
        // template wrapper
        // removal start
        $subpartsTemplateWrapperRemove = array();
        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
            $subpartsTemplateWrapperRemove['###HEADER_EXCLUDE_VAT_WRAPPER###'] = '';
            $subpartsTemplateWrapperRemove['###SUBTOTAL_EXCLUDE_VAT_WRAPPER###'] = '';
        } else {
            $subpartsTemplateWrapperRemove['###HEADER_INCLUDE_VAT_WRAPPER###'] = '';
            $subpartsTemplateWrapperRemove['###SUBTOTAL_INCLUDE_VAT_WRAPPER###'] = '';
            $subpartsTemplateWrapperRemove['###TOTAL_VAT_ROW_INCLUDE_VAT###'] = '';
            if ($order['orders_tax_data']['shipping_tax'] || $order['orders_tax_data']['payment_tax']) {
                $subpartsTemplateWrapperRemove['###TOTAL_VAT_ROW_EXCLUDE_VAT_NO_SHIPPING_PAYMENT_TAX###'] = '';
            } else {
                $subpartsTemplateWrapperRemove['###TOTAL_VAT_ROW_EXCLUDE_VAT_HAVE_SHIPPING_PAYMENT_TAX###'] = '';
            }
        }
        if (!$order['discount'] || ($order['discount'] > -1 && $order['discount'] < 0.01)) {
            $subpartsTemplateWrapperRemove['###DISCOUNT_WRAPPER###'] = '';
            $subpartsTemplateWrapperRemove['###NEWSUBTOTAL_WRAPPER###'] = '';
        }
        if (!empty($subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'])) {
            $subpartsTemplateWrapperRemove['###SHIPPING_COSTS_WRAPPER###'] = '';
            $subpartsTemplateWrapperRemove['###PAYMENT_COSTS_WRAPPER###'] = '';
        }
        if (!$order['shipping_method_costs']) {
            // If shipping method costs are zero, then remove the whole subpart
            $subpartsTemplateWrapperRemove['###SHIPPING_COSTS_WRAPPER###'] = '';
        }
        if (!$order['payment_method_costs']) {
            // If payment method costs are zero, then remove the whole subpart
            $subpartsTemplateWrapperRemove['###PAYMENT_COSTS_WRAPPER###'] = '';
        }
        $subparts['template'] = $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartsTemplateWrapperRemove);
        // items wrapper
        $subpartsItemsWrapperRemove = array();
        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
            $subpartsItemsWrapperRemove['###ITEM_EXCLUDE_VAT_WRAPPER###'] = '';
        } else {
            $subpartsItemsWrapperRemove['###ITEM_INCLUDE_VAT_WRAPPER###'] = '';
        }
        $subparts['ITEM_WRAPPER'] = $this->cObj->substituteMarkerArrayCached($subparts['ITEM_WRAPPER'], array(), $subpartsItemsWrapperRemove);
        // item attributes wrapper
        $subpartsItemAttributesWrapperRemove = array();
        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
            $subpartsItemAttributesWrapperRemove['###ITEM_ATTRIBUTE_EXCLUDE_VAT_WRAPPER###'] = '';
        } else {
            $subpartsItemAttributesWrapperRemove['###ITEM_ATTRIBUTE_INCLUDE_VAT_WRAPPER###'] = '';
        }
        $subparts['ITEM_ATTRIBUTES_WRAPPER'] = $this->cObj->substituteMarkerArrayCached($subparts['ITEM_ATTRIBUTES_WRAPPER'], array(), $subpartsItemAttributesWrapperRemove);
        // removal eol
        // tax subparts
        $subparts['TOTAL_VAT_ROW_EXCLUDE_VAT_NO_SHIPPING_PAYMENT_TAX'] = $this->cObj->getSubpart($subparts['template'], '###TOTAL_VAT_ROW_EXCLUDE_VAT_NO_SHIPPING_PAYMENT_TAX###');
        $subparts['TOTAL_VAT_ROW_EXCLUDE_VAT_HAVE_SHIPPING_PAYMENT_TAX'] = $this->cObj->getSubpart($subparts['template'], '###TOTAL_VAT_ROW_EXCLUDE_VAT_HAVE_SHIPPING_PAYMENT_TAX###');
        $subparts['TOTAL_VAT_ROW_INCLUDE_VAT'] = $this->cObj->getSubpart($subparts['template'], '###TOTAL_VAT_ROW_INCLUDE_VAT###');
        //print_r($subparts);
        //die();
        $total_tax = 0;
        $tr_type = 'even';
        $od_rows_count = 0;
        $product_counter = 1;
        $real_prefix = $prefix;
        if (is_array($order['products']) && count($order['products'])) {
            $contentItem = '';
            foreach ($order['products'] as $product) {
                $markerArray = array();
                $markerArray['ITEM_COUNTER'] = $product_counter;
                $od_rows_count++;
                if (!$tr_type or $tr_type == 'even') {
                    $tr_type = 'odd';
                } else {
                    $tr_type = 'even';
                }
                $markerArray['ITEM_ROW_TYPE'] = $tr_type;
                $markerArray['ITEM_PRODUCT_QTY'] = round($product['qty'], 2);
                $product_tmp = mslib_fe::getProduct($product['products_id']);
                $product_name = htmlspecialchars($product['products_name']);
                if (empty($product['products_name']) && !empty($product_tmp['products_name'])) {
                    $product_name = htmlspecialchars($product_tmp['products_name']);
                }
                if ($product['products_article_number']) {
                    $product_name .= ' (' . htmlspecialchars($product['products_article_number']) . ')';
                }
                if ($this->ms['MODULES']['DISPLAY_SKU_IN_ORDER_DETAILS'] == '1' && !empty($product['sku_code'])) {
                    $product_name .= '<br/>' . htmlspecialchars($this->pi_getLL('admin_label_sku')) . ': ' . htmlspecialchars($product['sku_code']);
                }
                if ($this->ms['MODULES']['DISPLAY_PRODUCTS_MODEL_IN_ORDER_DETAILS'] == '1' && !empty($product['products_model'])) {
                    $product_name .= '<br/>Model: ' . htmlspecialchars($product['products_model']);
                }
                if ($product['products_description']) {
                    $product_name .= '<br/>' . nl2br(htmlspecialchars($product['products_description']));
                }
                if ($this->ms['MODULES']['DISPLAY_EAN_IN_ORDER_DETAILS'] == '1' && !empty($product['ean_code'])) {
                    $product_name .= '<br/>' . htmlspecialchars($this->pi_getLL('admin_label_ean')) . ': ' . htmlspecialchars($product['ean_code']);
                }
                if ($this->ms['MODULES']['DISPLAY_VENDOR_IN_ORDER_DETAILS'] == '1' && !empty($product['vendor_code'])) {
                    $product_name .= '<br/>' . htmlspecialchars($this->pi_getLL('admin_label_vendor_code')) . ': ' . htmlspecialchars($product['vendor_code']);
                }
                $markerArray['ITEM_PRODUCT_NAME'] = $product_name;
                // Seperate marker version
                $markerArray['ITEM_SEPERATE_PRODUCTS_NAME'] = htmlspecialchars($product['products_name']);
                $markerArray['ITEM_SEPERATE_SKU_CODE'] = '';
                $product['sku_code'] = trim($product['sku_code']);
                if (!empty($product['sku_code'])) {
                    $markerArray['ITEM_SEPERATE_SKU_CODE'] = '<br/>' . htmlspecialchars($this->pi_getLL('admin_label_sku')) . ': ' . htmlspecialchars($product['sku_code']);
                }
                $markerArray['ITEM_SEPERATE_PRODUCTS_DESCRIPTION'] = '';
                $product['products_description'] = trim($product['products_description']);
                if (!empty($product['products_description'])) {
                    $markerArray['ITEM_SEPERATE_PRODUCTS_DESCRIPTION'] = '<br/>' . nl2br(htmlspecialchars($product['products_description']));
                }
                $markerArray['ITEM_SEPERATE_PRODUCTS_MODEL'] = htmlspecialchars($product['products_model']);
                // Seperate marker version eol
                $markerArray['ITEM_VAT'] = str_replace('.00', '', number_format($product['products_tax'], 2)) . '%';
                $markerArray['ITEM_ORDER_UNIT'] = $product['order_unit_name'];
                // ITEM IMAGE
                $image_path = mslib_befe::getImagePath($product_tmp['products_image'], 'products', '50');
                if (isset($product_tmp['products_image']) && !empty($product_tmp['products_image'])) {
                    if (!strstr(mslib_befe::strtolower($product_tmp['products_image']), 'http://') and !strstr(mslib_befe::strtolower($product_tmp['products_image']), 'https://')) {
                        $product_tmp['products_image'] = $image_path;
                    }
                    $markerArray['ITEM_IMAGE'] = '<img src="' . $product_tmp['products_image'] . '" alt="' . htmlspecialchars($product['products_name']) . '">';
                } else {
                    $markerArray['ITEM_IMAGE'] = '<div class="no_image_50"></div>';
                }
                if ($table_type == 'invoice' && $prefix == '-') {
                    if (strpos($product['final_price'], '-') !== false) {
                        $product['final_price'] = str_replace('-', '', $product['final_price']);
                    } else {
                        $product['final_price'] = $prefix . $product['final_price'];
                    }
                }
                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                    $markerArray['ITEM_NORMAL_PRICE'] = mslib_fe::amount2Cents(($product['final_price'] + $product['products_tax_data']['total_tax']), $customer_currency, $display_currency_symbol, 0);
                    $markerArray['ITEM_FINAL_PRICE'] = mslib_fe::amount2Cents(($product['qty'] * ($product['final_price'] + $product['products_tax_data']['total_tax'])), $customer_currency, $display_currency_symbol, 0);
                } else {
                    $markerArray['ITEM_NORMAL_PRICE'] = mslib_fe::amount2Cents(($product['final_price']), $customer_currency, $display_currency_symbol, 0);
                    $markerArray['ITEM_FINAL_PRICE'] = mslib_fe::amount2Cents(($product['qty'] * $product['final_price']), $customer_currency, $display_currency_symbol, 0);
                }
                $markerArray['ITEM_DISCOUNT_AMOUNT'] = '';
                if ($displayDiscountColumn) {
                    $markerArray['ITEM_DISCOUNT_AMOUNT'] = '<td align="right" class="cell_products_normal_price">' . mslib_fe::amount2Cents($product['discount_amount'], 0) . '</td>';
                    if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                        $markerArray['ITEM_DISCOUNT_AMOUNT'] = '<td align="right" class="cell_products_normal_price">' . mslib_fe::amount2Cents($product['discount_amount'] + (($product['discount_amount'] * $product['products_tax']) / 100), 0) . '</td>';
                        $markerArray['ITEM_NORMAL_PRICE'] = mslib_fe::amount2Cents(($product['products_price'] + (($product['products_price'] * $product['products_tax']) / 100)), $customer_currency, $display_currency_symbol, 0);
                        //$markerArray['ITEM_FINAL_PRICE'] = mslib_fe::amount2Cents($prefix . (($product['final_price'] - $product['discount_amount']) + $product['products_tax_data']['total_tax']), $customer_currency, $display_currency_symbol, 0);
                        $markerArray['ITEM_FINAL_PRICE'] = mslib_fe::amount2Cents(($product['qty'] * ($product['final_price'] + $product['products_tax_data']['total_tax'])), $customer_currency, $display_currency_symbol, 0);
                    } else {
                        $markerArray['ITEM_NORMAL_PRICE'] = mslib_fe::amount2Cents(($product['products_price']), $customer_currency, $display_currency_symbol, 0);
                        //$markerArray['ITEM_FINAL_PRICE'] = mslib_fe::amount2Cents($prefix . ($product['qty'] * ($product['final_price'] - $product['discount_amount'])), $customer_currency, $display_currency_symbol, 0);
                        $markerArray['ITEM_FINAL_PRICE'] = mslib_fe::amount2Cents(($product['qty'] * $product['final_price']), $customer_currency, $display_currency_symbol, 0);
                    }
                }
                //hook to let other plugins further manipulate the replacers
                if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableProductIteratorPostProc'])) {
                    $params_internal = array(
                            'markerArray' => &$markerArray,
                            'table_type' => $table_type,
                            'prefix' => $prefix,
                            'product' => $product_tmp,
                            'order_product' => $product,
                            'customer_currency' => $customer_currency,
                            'display_currency_symbol' => $display_currency_symbol
                    );
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableProductIteratorPostProc'] as $funcRef) {
                        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
                    }
                }
                $append_attributes_label_to_product_name = false;
                if (empty($subparts['ITEM_ATTRIBUTES_WRAPPER'])) {
                    $append_attributes_label_to_product_name = true;
                } else {
                    $contentItem .= $this->cObj->substituteMarkerArray($subparts['ITEM_WRAPPER'], $markerArray, '###|###');
                }
                if (is_array($product['attributes']) && count($product['attributes'])) {
                    foreach ($product['attributes'] as $tmpkey => $options) {
                        if ($options['products_options_values']) {
                            if ($table_type == 'invoice' && $prefix == '-') {
                                if (strpos($options['options_values_price'], '-') !== false) {
                                    $options['options_values_price'] = str_replace('-', '', $options['options_values_price']);
                                } else {
                                    $options['options_values_price'] = $prefix . $options['options_values_price'];
                                }
                            }
                            $attributeMarkerArray = array();
                            $attributeMarkerArray['ITEM_ATTRIBUTE_ROW_TYPE'] = $tr_type;
                            $attributeMarkerArray['ITEM_ATTRIBUTE'] = '';
                            if ($options['products_options'] && $options['products_options_values']) {
                                if ($append_attributes_label_to_product_name) {
                                    $markerArray['ITEM_PRODUCT_NAME'] .= ' <br />' . $options['products_options'] . ': ' . $options['products_options_values'];
                                }
                                $attributeMarkerArray['ITEM_ATTRIBUTE'] = htmlspecialchars($options['products_options']) . ': ' . htmlspecialchars($options['products_options_values']);
                            }
                            $attributeMarkerArray['ITEM_ATTRIBUTE_VAT'] = '';
                            // calculating
                            $od_rows_count++;
                            $cell_products_normal_price = '';
                            $cell_products_vat = '';
                            $cell_products_final_price = '';
                            if ($options['options_values_price'] > 0) {
                                if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                                    $attributes_price = $options['price_prefix'] . $options['options_values_price'] + $options['attributes_tax_data']['tax'];
                                    $total_attributes_price = $attributes_price * $product['qty'];
                                    $cell_products_normal_price = mslib_fe::amount2Cents(($attributes_price), $customer_currency, $display_currency_symbol, 0);
                                    $cell_products_final_price = mslib_fe::amount2Cents(($total_attributes_price), $customer_currency, $display_currency_symbol, 0);
                                } else {
                                    $cell_products_normal_price = mslib_fe::amount2Cents(($options['price_prefix'] . $options['options_values_price']), $customer_currency, $display_currency_symbol, 0);
                                    $cell_products_final_price = mslib_fe::amount2Cents(($options['price_prefix'] . $options['options_values_price']) * $product['qty'], $customer_currency, $display_currency_symbol, 0);
                                }
                            }
                            $attributeMarkerArray['ITEM_ATTRIBUTE_NORMAL_PRICE'] = $cell_products_normal_price;
                            $attributeMarkerArray['ITEM_ATTRIBUTE_DISCOUNT_AMOUNT'] = '';
                            if ($displayDiscountColumn) {
                                $attributeMarkerArray['ITEM_ATTRIBUTE_DISCOUNT_AMOUNT'] = '<td align="right" class="cell_products_normal_price">&nbsp;</td>';
                            }
                            $attributeMarkerArray['ITEM_ATTRIBUTE_FINAL_PRICE'] = $cell_products_final_price;
                            //hook to let other plugins further manipulate the replacers
                            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableProductAttributesIteratorPostProc'])) {
                                $params_internal = array(
                                        'attributeMarkerArray' => &$attributeMarkerArray,
                                        'table_type' => $table_type,
                                        'product' => $product,
                                        'options' => $options,
                                );
                                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsTableProductAttributesIteratorPostProc'] as $funcRef) {
                                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
                                }
                            }
                            $contentItem .= $this->cObj->substituteMarkerArray($subparts['ITEM_ATTRIBUTES_WRAPPER'], $attributeMarkerArray, '###|###');
                        }
                    }
                }
                if (empty($subparts['ITEM_ATTRIBUTES_WRAPPER'])) {
                    $contentItem .= $this->cObj->substituteMarkerArray($subparts['ITEM_WRAPPER'], $markerArray, '###|###');
                }
                $subpartArray['###ITEM_ATTRIBUTES_WRAPPER###'] = '';
                // count the vat
                if ($order['final_price'] and $order['products_tax']) {
                    $item_tax = $order['qty'] * ($order['final_price'] * $order['products_tax'] / 100);
                    $total_tax = $total_tax + $item_tax;
                }
                $product_counter++;
            }
        } else {
            $subpartArray['###ITEM_ATTRIBUTES_WRAPPER###'] = '';
        }
        $subpartArray['###ITEM_WRAPPER###'] = $contentItem;
        if ($table_type == 'invoice' && $prefix == '-') {
            if (strpos($order['shipping_method_costs'], '-') !== false) {
                $prefix = '';
                $order['shipping_method_costs'] = str_replace('-', '', $order['shipping_method_costs']);
                $order['orders_tax_data']['shipping_tax'] = str_replace('-', '', $order['orders_tax_data']['shipping_tax']);
            } else {
                $prefix = '-';
            }
            if (strpos($order['payment_method_costs'], '-') !== false) {
                $prefix = '';
                $order['payment_method_costs'] = str_replace('-', '', $order['payment_method_costs']);
                $order['orders_tax_data']['payment_tax'] = str_replace('-', '', $order['orders_tax_data']['payment_tax']);
            } else {
                $prefix = '-';
            }
            if (strpos($order['orders_tax_data']['sub_total'], '-') !== false) {
                $prefix = '';
                $order['orders_tax_data']['sub_total'] = str_replace('-', '', $order['orders_tax_data']['sub_total']);
            } else {
                $prefix = '-';
            }
            if (strpos($order['subtotal_amount'], '-') !== false) {
                $prefix = '';
                $order['subtotal_amount'] = str_replace('-', '', $order['subtotal_amount']);
            } else {
                $prefix = '-';
            }
            if (strpos($order['discount'], '-') !== false) {
                $prefix = '';
                $order['discount'] = str_replace('-', '', $order['discount']);
            } else {
                $prefix = '-';
            }
            if (strpos($order['orders_tax_data']['grand_total'], '-') !== false) {
                $prefix = '';
                $order['orders_tax_data']['grand_total'] = str_replace('-', '', $order['orders_tax_data']['grand_total']);
            } else {
                $prefix = '-';
            }
        }
        if (!empty($subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'])) {
            /*
			 * special subparts
			 <!-- ###SINGLE_SHIPPING_PACKING_COSTS_WRAPPER### begin -->
				<tr class="###ITEM_SHIPPING_PAYMENT_COSTS_ROW_TYPE###">
					<td align="right" class="cell_products_counter valign_top">###ITEM_SHIPPING_PAYMENT_COSTS_COUNTER###</td>
					<td align="left" class="cell_products_name valign_top">###ITEM_SHIPPING_PAYMENT_COSTS_LABEL###</td>
					<td align="right" class="cell_products_normal_price valign_top">###ITEM_SHIPPING_PAYMENT_COSTS_NORMAL_PRICE###</td>
					<td align="right" class="cell_products_vat valign_top">###ITEM_SHIPPING_PAYMENT_COSTS_VAT###</td>
					<td align="right" class="cell_products_final_price valign_top">###ITEM_SHIPPING_PAYMENT_COSTS_FINAL_PRICE###</td>
				</tr>
				<!-- ###SINGLE_SHIPPING_PACKING_COSTS_WRAPPER### end -->
			 */
            $shipping_payment_costs_line = '';
            // payment costs
            if (!$tr_type or $tr_type == 'even') {
                $tr_type = 'odd';
            } else {
                $tr_type = 'even';
            }
            $payment_tax_rate = '-';
            if (!empty($order['orders_tax_data']['payment_total_tax_rate'])) {
                $payment_tax_rate = ($order['orders_tax_data']['payment_total_tax_rate'] * 100) . '%';
            }
            $markerArray = array();
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_COUNTER'] = $product_counter;
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_LABEL'] = $this->pi_getLL('payment_costs') . ' (' . $order['payment_method_label'] . ')';
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_ROW_TYPE'] = $tr_type;
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_VAT'] = $payment_tax_rate;
            $payment_costs = '0';
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                if ($order['payment_method_costs'] !== 0) {
                    $payment_costs = $prefix . ($order['payment_method_costs'] + $order['orders_tax_data']['payment_tax']);
                }
            } else {
                if ($order['payment_method_costs'] !== 0) {
                    $payment_costs = $prefix . ($order['payment_method_costs']);
                }
            }
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_NORMAL_PRICE'] = mslib_fe::amount2Cents($payment_costs, $customer_currency, $display_currency_symbol, 0);
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_FINAL_PRICE'] = mslib_fe::amount2Cents($payment_costs, $customer_currency, $display_currency_symbol, 0);
            $shipping_payment_costs_line .= $this->cObj->substituteMarkerArray($subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'], $markerArray, '###|###');
            $product_counter++;
            // shipping costs
            if (!$tr_type or $tr_type == 'even') {
                $tr_type = 'odd';
            } else {
                $tr_type = 'even';
            }
            $shipping_tax_rate = '0%';
            if (!empty($order['orders_tax_data']['shipping_total_tax_rate'])) {
                $shipping_tax_rate = ($order['orders_tax_data']['shipping_total_tax_rate'] * 100) . '%';
            }
            $markerArray = array();
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_COUNTER'] = $product_counter;
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_LABEL'] = $this->pi_getLL('shipping_costs') . ' (' . $order['shipping_method_label'] . ')';
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_ROW_TYPE'] = $tr_type;
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_VAT'] = $shipping_tax_rate;
            $shipping_costs = '0';
            if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
                if ($order['shipping_method_costs'] !== 0) {
                    $shipping_costs = $prefix . ($order['shipping_method_costs'] + $order['orders_tax_data']['shipping_tax']);
                }
            } else {
                if ($order['shipping_method_costs'] !== 0) {
                    $shipping_costs = $prefix . ($order['shipping_method_costs']);
                }
            }
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_NORMAL_PRICE'] = mslib_fe::amount2Cents($shipping_costs, $customer_currency, $display_currency_symbol, 0);
            $markerArray['ITEM_SHIPPING_PAYMENT_COSTS_FINAL_PRICE'] = mslib_fe::amount2Cents($shipping_costs, $customer_currency, $display_currency_symbol, 0);
            $shipping_payment_costs_line .= $this->cObj->substituteMarkerArray($subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'], $markerArray, '###|###');
            $product_counter++;
            $subpartArray['###SINGLE_SHIPPING_PACKING_COSTS_WRAPPER###'] = $shipping_payment_costs_line;
        }
        // bottom row
        // taxes row renderer
        $vat_wrapper_keys = array();
        $vat_wrapper_keys[] = 'TOTAL_VAT_ROW_INCLUDE_VAT';
        $vat_wrapper_keys[] = 'TOTAL_VAT_ROW_EXCLUDE_VAT_NO_SHIPPING_PAYMENT_TAX';
        $vat_wrapper_keys[] = 'TOTAL_VAT_ROW_EXCLUDE_VAT_HAVE_SHIPPING_PAYMENT_TAX';
        foreach ($vat_wrapper_keys as $vat_wrapper_key) {
            if (!empty($subparts[$vat_wrapper_key])) {
                $vatItem = '';
                if (isset($order['orders_tax_data']['tax_separation']) && count($order['orders_tax_data']['tax_separation']) && !$order['discount']) {
                    foreach ($order['orders_tax_data']['tax_separation'] as $tax_sep_rate => $tax_sep_data) {
                        $markerArray = array();
                        if (isset($tax_sep_rate)) {
                            // If TAX seperation has only 1 entry always print it (0% TAX for example)
                            // Else only print the current TAX rate if it is higher than zero
                            if (count($order['orders_tax_data']['tax_separation']) == 1 || (count($order['orders_tax_data']['tax_separation']) > 1 && $tax_sep_rate > 0)) {
                                if (empty($tax_sep_data['shipping_tax'])) {
                                    $tax_sep_data['shipping_tax'] = 0;
                                }
                                if (empty($tax_sep_data['payment_tax'])) {
                                    $tax_sep_data['payment_tax'] = 0;
                                }
                                if ($table_type == 'invoice' && $real_prefix == '-') {
                                    if (strpos($tax_sep_data['products_total_tax'], '-') !== false) {
                                        $prefix = '';
                                        $tax_sep_data['products_total_tax'] = str_replace('-', '', $tax_sep_data['products_total_tax']);
                                        $tax_sep_data['shipping_tax'] = str_replace('-', '', $tax_sep_data['shipping_tax']);
                                        $tax_sep_data['payment_tax'] = str_replace('-', '', $tax_sep_data['payment_tax']);
                                    } else {
                                        $prefix = '-';
                                    }
                                }
                                $tax_sep_total = $prefix . ($tax_sep_data['products_total_tax'] + $tax_sep_data['shipping_tax'] + $tax_sep_data['payment_tax']);
                                if (count($order['orders_tax_data']['tax_separation']) == 1 || $tax_sep_total > 0) {
                                    // only print TAX rate if there is only 1 seperation OR if there are multiple seperations where each seperation amount is higher than 0
                                    if ($vat_wrapper_key == 'TOTAL_VAT_ROW_INCLUDE_VAT') {
                                        $markerArray['LABEL_INCLUDED_VAT_AMOUNT'] = $this->pi_getLL('included_vat_amount') . ' ' . $tax_sep_rate . '%';
                                    } else {
                                        // todo: add typoscript constant to enable/disable the view
                                        // Show the taken amount for the seperated VAT (i.e. BTW 21% from 10 Euro)
                                        //$markerArray['LABEL_VAT']=sprintf($this->pi_getLL('vat_nn_from_subtotal_nn'), $tax_sep_rate.'%', ($display_currency_symbol ? '' : 'EUR ').mslib_fe::amount2Cents($prefix.($tax_sep_data['products_sub_total_excluding_vat']+$tax_sep_data['shipping_costs']+$tax_sep_data['payment_costs']), $customer_currency, $display_currency_symbol, 0));
                                        // Show traditional label (i.e. BTW 21%)
                                        $markerArray['LABEL_VAT'] = $this->pi_getLL('vat') . ' ' . $tax_sep_rate . '%';
                                    }
                                    $markerArray['TOTAL_VAT'] = mslib_fe::amount2Cents($tax_sep_total, $customer_currency, $display_currency_symbol, 0);
                                    $vatItem .= $this->cObj->substituteMarkerArray($subparts[$vat_wrapper_key], $markerArray, '###|###');
                                }
                            }
                        }
                    }
                } else {
                    $markerArray = array();
                    if ($vat_wrapper_key == 'TOTAL_VAT_ROW_INCLUDE_VAT') {
                        $markerArray['LABEL_INCLUDED_VAT_AMOUNT'] = $this->pi_getLL('included_vat_amount');
                    } else {
                        $markerArray['LABEL_VAT'] = $this->pi_getLL('vat');
                    }
                    if ($table_type == 'invoice' && $real_prefix == '-') {
                        if (strpos($order['orders_tax_data']['total_orders_tax'], '-') !== false) {
                            $prefix = '';
                            $order['orders_tax_data']['total_orders_tax'] = str_replace('-', '', $order['orders_tax_data']['total_orders_tax']);
                        } else {
                            $prefix = '-';
                        }
                    }
                    $markerArray['TOTAL_VAT'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['total_orders_tax']), $customer_currency, $display_currency_symbol, 0);
                    $vatItem .= $this->cObj->substituteMarkerArray($subparts[$vat_wrapper_key], $markerArray, '###|###');
                }
                $subpartArray['###' . $vat_wrapper_key . '###'] = $vatItem;
                break;
            }
        }
        $hr_colspan = 3;
        $colspan = 5;
        if ($displayDiscountColumn) {
            $hr_colspan = 4;
            $colspan = 6;
        }
        $subpartArray['###INVOICE_HR_COLSPAN###'] = $hr_colspan;
        $subpartArray['###INVOICE_TOTAL_COLSPAN###'] = $colspan;
        $subpartArray['###LABEL_SUBTOTAL###'] = $this->pi_getLL('sub_total');
        //$subpartArray['###LABEL_VAT###']=$this->pi_getLL('vat');
        $subpartArray['###LABEL_SHIPPING_COSTS###'] = $this->pi_getLL('shipping_costs');
        $subpartArray['###LABEL_PAYMENT_COSTS###'] = $this->pi_getLL('payment_costs');
        $subpartArray['###LABEL_PAYMENT_COSTS###'] = $this->pi_getLL('payment_costs');
        if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
            if (!empty($subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'])) {
                $subpartArray['###SUBTOTAL###'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['sub_total'] + $shipping_costs + $payment_costs), $customer_currency, $display_currency_symbol, 0);
                $subpartArray['###SUBTOTAL_EXTRA###'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['sub_total'] + $shipping_costs + $payment_costs), $customer_currency, $display_currency_symbol, 0);
            } else {
                $subpartArray['###SUBTOTAL###'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['sub_total']), $customer_currency, $display_currency_symbol, 0);
                $subpartArray['###SUBTOTAL_EXTRA###'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['sub_total']), $customer_currency, $display_currency_symbol, 0);
            }
            //$subpartArray['###TOTAL_VAT###']=mslib_fe::amount2Cents($prefix.($order['orders_tax_data']['total_orders_tax']), 0,$display_currency_symbol,0);
            $subpartArray['###TOTAL_SHIPPING_COSTS###'] = mslib_fe::amount2Cents($prefix . ($order['shipping_method_costs'] + $order['orders_tax_data']['shipping_tax']), $customer_currency, $display_currency_symbol, 0);
            $subpartArray['###TOTAL_PAYMENT_COSTS###'] = mslib_fe::amount2Cents($prefix . ($order['payment_method_costs'] + $order['orders_tax_data']['payment_tax']), $customer_currency, $display_currency_symbol, 0);
        } else {
            if (!empty($subparts['SINGLE_SHIPPING_PACKING_COSTS_WRAPPER'])) {
                $subpartArray['###SUBTOTAL###'] = mslib_fe::amount2Cents($prefix . ($order['subtotal_amount'] + $shipping_costs + $payment_costs), $customer_currency, $display_currency_symbol, 0);
                $subpartArray['###SUBTOTAL_EXTRA###'] = mslib_fe::amount2Cents($prefix . ($order['subtotal_amount'] + $shipping_costs + $payment_costs), $customer_currency, $display_currency_symbol, 0);
            } else {
                $subpartArray['###SUBTOTAL###'] = mslib_fe::amount2Cents($prefix . ($order['subtotal_amount']), $customer_currency, $display_currency_symbol, 0);
                $subpartArray['###SUBTOTAL_EXTRA###'] = mslib_fe::amount2Cents($prefix . ($order['subtotal_amount']), $customer_currency, $display_currency_symbol, 0);
            }
            //$subpartArray['###TOTAL_VAT###']=mslib_fe::amount2Cents($prefix.($order['orders_tax_data']['total_orders_tax']), 0,$display_currency_symbol,0);
            $subpartArray['###TOTAL_SHIPPING_COSTS###'] = mslib_fe::amount2Cents($prefix . ($order['shipping_method_costs']), $customer_currency, $display_currency_symbol, 0);
            $subpartArray['###TOTAL_PAYMENT_COSTS###'] = mslib_fe::amount2Cents($prefix . ($order['payment_method_costs']), $customer_currency, $display_currency_symbol, 0);
        }
        if ($order['discount'] < 0 || $order['discount'] > 0) {
            $subpartArray['###LABEL_DISCOUNT###'] = $this->pi_getLL('discount');
            $subpartArray['###TOTAL_DISCOUNT###'] = mslib_fe::amount2Cents($prefix . ($order['discount']), $customer_currency, $display_currency_symbol, 0);
            //
            $subpartArray['###PRODUCTS_NEWSUB_TOTAL_PRICE_LABEL###'] = $this->pi_getLL('subtotal');
            $subpartArray['###PRODUCTS_NEWTOTAL_PRICE###'] = mslib_fe::amount2Cents($order['subtotal_amount'] - $order['discount'], $customer_currency, $display_currency_symbol, 0);
        }
        //$subpartArray['###LABEL_INCLUDED_VAT_AMOUNT###']=$this->pi_getLL('included_vat_amount');
        $subpartArray['###LABEL_GRAND_TOTAL_EXCLUDING_VAT###'] = $this->pi_getLL('grand_total_excluding_vat') . ' ';
        $subpartArray['###GRAND_TOTAL_EXCLUDING_VAT###'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['grand_total_excluding_vat']), $customer_currency, $display_currency_symbol, 0);
        $subpartArray['###LABEL_GRAND_TOTAL###'] = $this->pi_getLL('total');
        $subpartArray['###GRAND_TOTAL###'] = mslib_fe::amount2Cents($prefix . ($order['orders_tax_data']['grand_total']), $customer_currency, $display_currency_symbol, 0);
        //hook to let other plugins further manipulate the replacers
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsSummaryPreProc'])) {
            $params_internal = array(
                    'subparts' => &$subparts,
                    'subpartArray' => &$subpartArray,
                    'order' => &$order,
                    'table_type' => $table_type,
                    'real_prefix' => $real_prefix,
                    'prefix' => $prefix,
                    'customer_currency' => $customer_currency,
                    'display_currency_symbol' => $display_currency_symbol
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsSummaryPreProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
            }
        }
        $tmpcontent = $this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsSummaryPostProc'])) {
            $params_internal = array(
                    'tmpcontent' => &$tmpcontent,
                    'order' => &$order,
                    'table_type' => $table_type,
                    'real_prefix' => $real_prefix,
                    'prefix' => $prefix,
                    'customer_currency' => $customer_currency,
                    'display_currency_symbol' => $display_currency_symbol
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['printInvoiceOrderDetailsSummaryPostProc'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params_internal, $this);
            }
        }
        return $tmpcontent;
    }
    function strstr_array($string, $needleArray) {
        if (!is_array($needleArray)) {
            return false;
        }
        if (is_array($needleArray) && count($needleArray)) {
            foreach ($needleArray as $needle) {
                if (strstr($string, $needle)) {
                    return $needle;
                }
            }
        }
    }
    function stristr_array($string, $needleArray) {
        if (!is_array($needleArray)) {
            return false;
        }
        if (is_array($needleArray) && count($needleArray)) {
            foreach ($needleArray as $needle) {
                if (stristr($string, $needle)) {
                    return $needle;
                }
            }
        }
    }
    function natksort(&$array) {
        $keys = array_keys($array);
        natcasesort($keys);
        if (is_array($keys) && count($keys)) {
            foreach ($keys as $k) {
                $new_array[$k] = $array[$k];
            }
        }
        $array = $new_array;
        return true;
    }
    function hex2dompdf($hex) {
        if ($hex) {
            $array = mslib_befe::hex2rgb($hex);
            $array = mslib_befe::rgb2dompdf($array);
            return $array;
        }
    }
    function hex2rgb($hex) {
        if ($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            $rgb = array(
                    $r,
                    $g,
                    $b
            );
            return $rgb;
        }
    }
    function rgb2dompdf($array) {
        if (is_array($array) && count($array)) {
            for ($i = 0; $i < 3; $i++) {
                $array[$i] = ($array[$i] / 255);
            }
            return $array;
        }
    }
    /**
     * Puts a key - element pair first into an array, while preserving
     * the keys.
     * @param array The array to shift into
     * @param mixed The new key
     * @param mixed The new element
     * @return int Number of elements in the new array
     */
    function array_unshift_assoc(&$array, $key, $element) {
        $array = array_reverse($array, true);
        $array[$key] = $element;
        $array = array_reverse($array, true);
        return count($array);
    }
    function getLanguageRecordByIsoString($twoChars) {
        if ($twoChars) {
            $record = mslib_befe::getRecord(strtoupper($twoChars), 'static_languages', 'lg_iso_2');
            if (is_array($record) && $record['uid']) {
                return $record;
            }
        }
    }
    function getSysLanguageUidByIsoString($twoChars) {
        switch (strtolower($twoChars)) {
            case 'en':
                $static_lang_isocode = 30;
                break;
            case 'nl':
                $static_lang_isocode = 29;
                break;
            case 'de':
                $static_lang_isocode = 43;
                break;
        }
        if ($static_lang_isocode) {
            $record = mslib_befe::getRecord($static_lang_isocode, 'sys_language', 'static_lang_isocode');
            if (is_array($record) && $record['uid']) {
                return $record['uid'];
            }
        } else {
            return mslib_befe::getSysLanguageUidByIso2($twoChars);
        }
    }
    function getSysLanguageUidByIso2($iso2) {
        if (!$iso2) {
            return false;
        }
        $record = mslib_befe::getRecord($iso2, 'sys_language syslang, static_languages statlang', 'statlang.lg_iso_2', array('syslang.hidden=0 and syslang.static_lang_isocode=statlang.uid'), 'syslang.uid');
        if ($record['uid']) {
            return $record['uid'];
        }
    }
    function getSysLanguageUidByFlagString($flag) {
        if ($flag) {
            $record = mslib_befe::getRecord($flag, 'sys_language', 'flag');
            if (is_array($record) && $record['uid']) {
                return $record['uid'];
            }
        }
    }
    function getTableColumnNames($table) {
        if ($table) {
            $query = "SHOW COLUMNS FROM " . $table;
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $fields = array();
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $fields[] = $row['Field'];
            }
            return $fields;
        }
    }
    function xml_entities($string = '') {
        if ($string) {
            return str_replace(array(
                    "&",
                    "<",
                    ">",
                    '"',
                    "'"
            ), array(
                    "&amp;",
                    "&lt;",
                    "&gt;",
                    "&quot;",
                    "&apos;"
            ), $string);
        }
    }
    function bootstrapPanel($heading = '', $body = '', $panelClass = 'default', $footer = '', $panelHeadingClass = '', $panelId = '', $enableCollapse = 0, $collapsed = '0', $headingButtons = array(), $options = array()) {
        if (!$options['headingCollapseFontAwesomeClass']) {
            $options['headingCollapseFontAwesomeClass'] = 'fa fa-file-text-o';
        }
        if ($enableCollapse) {
            if ($collapsed) {
                $panelHeadingClasses[] = 'collapsed';
            }
            $heading = '<a role="button"' . ($options['headingCollapseLinkTitle'] ? ' title="' . htmlspecialchars($options['headingCollapseLinkTitle']) . '"' : '') . ' data-toggle="collapse" href="#' . $panelId . 'Body"><i class="' . $options['headingCollapseFontAwesomeClass'] . '"></i> ' . $heading . '</a>';
            $panelHeadingParams .= 'data-toggle="collapse" data-target="#' . $panelId . 'Body" aria-expanded="true"';
        }
        $content = '<div' . ($panelId ? ' id="' . $panelId . '"' : '') . ' class="panel panel-' . $panelClass . '">';
        if ($heading) {
            $content .= '<div class="panel-heading' . ($panelHeadingClass ? ' ' . $panelHeadingClass : '') . '"' . ($panelHeadingParams ? ' ' . $panelHeadingParams : '') . '>';
            $content .= '<h3 class="panel-title">' . $heading . '</h3>';
            if (is_array($headingButtons) && count($headingButtons)) {
                $content .= '<div class="form-inline">';
                foreach ($headingButtons as $headingButton) {
                    $content .= '<a href="' . $headingButton['href'] . '" class="' . $headingButton['btn_class'] . '"' . ($headingButton['attributes'] ? ' ' . $headingButton['attributes'] : '') . '><i class="' . $headingButton['fa_class'] . '"></i> ' . htmlspecialchars($headingButton['title']) . '</a> ';
                }
                $content .= '</div>';
            }
            $content .= '</div>';
        }
        if ($body) {
            if ($enableCollapse) {
                if (!$collapsed) {
                    $collapseState = 'in';
                }
                $content .= '<div id="' . $panelId . 'Body" class="panel-collapse collapse ' . $collapseState . '" aria-expanded="true">';
            }
            $content .= '<div class="panel-body">' . $body . '</div>';
            if ($enableCollapse) {
                $content .= '</div>';
            }
        }
        if ($footer) {
            $content .= '<div class="panel-footer">' . $footer . '</div>';
        }
        $content .= '</div>';
        return $content;
    }
    function antiXSS($val, $mode = '') {
        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'res/htmlpurifier-4.7.0/HTMLPurifier.auto.php');
        if (is_array($val)) {
            foreach ($val as $key => $subVal) {
                $val[$key] = mslib_befe::antiXSS($subVal, $mode);
            }
            return $val;
        } else {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
            $config->set('HTML.Doctype', 'XHTML 1.0 Transitional'); // replace with your doctype
            $config->set('Cache.SerializerPath', $this->DOCUMENT_ROOT . 'uploads/tx_multishop');
            switch ($mode) {
                case 'html':
                    $config->set('HTML.Allowed', 'table,tr,th,td,tbody,thead,tfood,h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],h7[style],style,font[style],iframe[style|frameborder|allowfullscreen|width|height|src],a[href],img[alt|src|unselectable],div,span,p,i,a,b,br,hr,u,strike,strong,em,ul,ol,li,del,ins,strike'); // Allow basic HTML
                    $config->set("HTML.Nofollow", TRUE);
                    $config->set('HTML.TargetBlank', TRUE);
                    $config->set('HTML.SafeIframe', true);
                    $config->set('URI.SafeIframeRegexp', '%^(//|http://|https://)(www.youtube.com/embed/|player.vimeo.com/video/)%');
                    $config->set('Cache.SerializerPath', $this->DOCUMENT_ROOT . 'uploads/tx_multishop');
                    $purifier = new HTMLPurifier($config);
                    return $purifier->purify($val);
                    break;
                case 'html_no_img':
                    $config->set('HTML.Allowed', 'table,tr,th,td,tbody,thead,tfood,h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],h7[style],style,font[style],iframe[style|frameborder|allowfullscreen|width|height|src],a[href],div,span,p,i,a,b,br,hr,u,strike,strong,em,ul,ol,li,del,ins,strike'); // Allow basic HTML
                    $config->set("HTML.Nofollow", TRUE);
                    $config->set('HTML.TargetBlank', TRUE);
                    $config->set('HTML.SafeIframe', true);
                    $config->set('URI.SafeIframeRegexp', '%^(//|http://|https://)(www.youtube.com/embed/|player.vimeo.com/video/)%');
                    $config->set('Cache.SerializerPath', $this->DOCUMENT_ROOT . 'uploads/tx_multishop');
                    $purifier = new HTMLPurifier($config);
                    return $purifier->purify($val);
                    break;
                case 'strip_tags':
                    $config->set('HTML.Allowed', ''); // Allow Nothing
                    $config->set('Cache.SerializerPath', $this->DOCUMENT_ROOT . 'uploads/tx_multishop');
                    $purifier = new HTMLPurifier($config);
                    return $purifier->purify($val);
                    break;
                default:
                    $config->set('HTML.Allowed', 'p,div,span,p,i,a,b,br,hr,u,strike,strong,em,ul,ol,li,del,ins,strike'); // Allow basic HTML
                    $config->set("HTML.Nofollow", TRUE);
                    $config->set('HTML.TargetBlank', TRUE);
                    $config->set('Cache.SerializerPath', $this->DOCUMENT_ROOT . 'uploads/tx_multishop');
                    //hook to let other plugins further manipulate the settings
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['antiXSSPreProc'])) {
                        $params = array(
                                'mode' => &$mode,
                                'config' => &$config
                        );
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['antiXSSPreProc'] as $funcRef) {
                            \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
                        }
                    }
                    $purifier = new HTMLPurifier($config);
                    return $purifier->purify($val);
                    break;
            }
        }
    }
    function getLocalLanguageNameByIso2($iso2) {
        if (!$iso2) {
            return false;
        }
        $record = mslib_befe::getRecord($iso2, 'static_languages statlang', 'statlang.lg_iso_2');
        if ($record['uid']) {
            return $record['lg_name_local'];
        }
    }
    public function getPaymentMethodLabelByCode($code, $sys_language_id = 0) {
        if ($code) {
            $select = array();
            $select[] = 'pd.name';
            $from = array();
            $from[] = 'tx_multishop_payment_methods p';
            $from[] = 'tx_multishop_payment_methods_description pd';
            $where = array();
            $orderby = array();
            $where[] = 'p.code=\'' . addslashes($code) . '\'';
            $where[] = 'pd.language_id=\'' . $this->sys_language_uid . '\'';
            $where[] = 'p.id=pd.id';
            $str = $GLOBALS['TYPO3_DB']->SELECTquery(implode(', ', $select), // SELECT ...
                    implode(', ', $from), // FROM ...
                    implode(' and ', $where), // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
                return $row['name'];
            }
        }
    }
    public function getShippingMethodLabelByCode($code, $sys_language_id = 0) {
        if ($code) {
            $select = array();
            $select[] = 'pd.name';
            $from = array();
            $from[] = 'tx_multishop_shipping_methods p';
            $from[] = 'tx_multishop_shipping_methods_description pd';
            $where = array();
            $orderby = array();
            $where[] = 'p.code=\'' . addslashes($code) . '\'';
            $where[] = 'pd.language_id=\'' . $this->sys_language_uid . '\'';
            $where[] = 'p.id=pd.id';
            $str = $GLOBALS['TYPO3_DB']->SELECTquery(implode(', ', $select), // SELECT ...
                    implode(', ', $from), // FROM ...
                    implode(' and ', $where), // WHERE...
                    '', // GROUP BY...
                    '', // ORDER BY...
                    '' // LIMIT ...
            );
            $qry = $GLOBALS['TYPO3_DB']->sql_query($str);
            if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
                return $row['name'];
            }
        }
    }
    public function customerAddressFormat($address_data, $address_type = 'billing', $address_format = 'default') {
        $address_format_setting = $this->ms['MODULES']['ADDRESS_FORMAT'];
        //hook to let other plugins further manipulate the settings
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['customerAddressFormatSetting'])) {
            $params = array(
                    'address_format_setting' => &$address_format_setting,
                    'address_data' => &$address_data,
                    'address_type' => &$address_type,
                    'address_format' => &$address_format,
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/pi1/classes/class.mslib_befe.php']['customerAddressFormatSetting'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        $array1 = array();
        $array2 = array();
        $array1[] = '###STREET_NAME###';
        $array2[] = $address_data['street_name'];
        if (!empty($address_data['building'])) {
            $array1[] = '###BUILDING###';
            $array2[] = $address_data['building'];
        } else {
            if (strpos($address_format_setting, '###BUILDING###<br/>') !== false) {
                $array1[] = '###BUILDING###<br/>';
                $array2[] = '';
            } else {
                $array1[] = '###BUILDING###';
                $array2[] = '';
            }
        }
        if (strpos($address_format_setting, '###BUILDING###') === false && $address_data['building']) {
            $array1[] = '###ADDRESS###';
            $array2[] = $address_data['building'] . '<br/>' . $address_data['address'];
        } else {
            $array1[] = '###ADDRESS###';
            $array2[] = $address_data['address'];
        }
        $array1[] = '###ZIP###';
        $array2[] = $address_data['zip'];
        $array1[] = '###CITY###';
        $array2[] = $address_data['city'];
        $array1[] = '###COUNTRY###';
        $array2[] = mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $address_data['country']);
        $array1[] = '###STATE###';
        $array2[] = $address_data['state'];
        $array1[] = '###FULL_NAME###';
        $array2[] = $address_data['name'];
        $array1[] = '###FIRST_NAME###';
        $array2[] = $address_data['first_name'];
        $array1[] = '###LAST_NAME###';
        $array2[] = $address_data['last_name'];
        $array1[] = '###EMAIL###';
        $array2[] = $address_data['email'];
        $array1[] = '###TELEPHONE###';
        $array2[] = $address_data['telephone'];
        $array1[] = '###FAX###';
        $array2[] = $address_data['fax'];
        $array1[] = '###VAT_ID###';
        $array2[] = $address_data['vat_id'];
        $array1[] = '###COC_ID###';
        $array2[] = $address_data['coc_id'];
        $address_format_value = '';
        if ($address_format_setting) {
            $address_format_value = str_replace($array1, $array2, $address_format_setting);
        }
        return $address_format_value;
    }
    function fileSizeConvert($bytes) {
        $bytes = floatval($bytes);
        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
    }
    function moveArrayKeyToTop(&$array, $key) {
        $temp = array($key => $array[$key]);
        unset($array[$key]);
        $array = $temp + $array;
    }
    function moveArrayKeyToBottom(&$array, $key) {
        $value = $array[$key];
        unset($array[$key]);
        $array[$key] = $value;
    }
    /**
     * Find the position of the Xth occurrence of a substring in a string
     * @param $haystack
     * @param $needle
     * @param $number integer > 0
     * @return int
     */
    function strposX($haystack, $needle, $number) {
        if ($number == '1') {
            return strpos($haystack, $needle);
        } elseif ($number > '1') {
            return strpos($haystack, $needle, mslib_befe::strposX($haystack, $needle, $number - 1) + strlen($needle));
        } else {
            return error_log('Error: Value for parameter $number is out of range');
        }
    }
    function setProductDefaultCrumpath($product_id) {
        $p2c_records = mslib_befe::getRecords($product_id, 'tx_multishop_products_to_categories', 'products_id', array('is_deepest=1'), '', 'products_to_categories_id asc');
        if (is_array($p2c_records) && count($p2c_records) > 1) {
            $set_default_path = true;
            foreach ($p2c_records as $p2c_record) {
                if ($p2c_record['default_path'] > 0) {
                    $set_default_path = false;
                    break;
                }
            }
            if ($set_default_path) {
                $updateArray = array();
                $updateArray['default_path'] = 1;
	            $updateArray['last_updated_at'] = time();
                $queryProduct = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_to_categories', 'products_to_categories_id=\'' . $p2c_records[0]['products_to_categories_id'] . '\' and categories_id=\'' . $p2c_records[0]['categories_id'] . '\' and products_id=\'' . $p2c_records[0]['products_id'] . '\'', $updateArray);
                $GLOBALS['TYPO3_DB']->sql_query($queryProduct);
            }
        }
    }
    public function getRecords($value = '', $table, $field = '', $additional_where = array(), $groupBy = '', $orderBy = '', $limit = '', $select = array(), $havingClause = '') {
        if ($select && !is_array($select)) {
            $select = array($select);
        }
        if (!count($select)) {
            $select = array();
            $select[] = '*';
        }
        $queryArray = array();
        $queryArray['from'] = $table;
        if (isset($value) && isset($field) && $field != '') {
            $queryArray['where'][] = addslashes($field) . '=\'' . addslashes($value) . '\'';
        }
        if (is_array($additional_where) && count($additional_where)) {
            foreach ($additional_where as $where) {
                if ($where) {
                    $queryArray['where'][] = $where;
                }
            }
        }
        $query = $GLOBALS['TYPO3_DB']->SELECTquery(implode(',', $select), // SELECT ...
                $queryArray['from'], // FROM ...
                ((is_array($queryArray['where']) && count($queryArray['where'])) ? implode(' AND ', $queryArray['where']) : ''), // WHERE...
                $groupBy, // GROUP BY...
                $orderBy, // ORDER BY...
                $limit // LIMIT ...
        );
        if ($havingClause) {
            $query = str_replace('ORDER BY', $havingClause . ' ORDER BY', $query);
        }
        if ($this->msDebug) {
            return $query;
        }
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
            $items = array();
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $items[] = $row;
            }
            return $items;
        }
    }
    function formatNumbersToMysql($numbers) {
        if ($this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'] != '.') {
            $thousand_array = array();
            $decimal = '00';
            $thousands = explode($this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_thousands_point'], $numbers);
            foreach ($thousands as $thousand) {
                if (strpos($thousand, $this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point']) === false) {
                    $thousand_array[] = $thousand;
                } else {
                    list($last_thousand, $decimal) = explode($this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_decimal_point'], $thousand);
                    $thousand_array[] = $last_thousand;
                }
            }
            $full_number = 0;
            if (count($thousand_array)) {
                $full_number = implode('', $thousand_array) . '.' . $decimal;
            }
            return $full_number;
        } else {
            $numbers = str_replace($this->ms['MODULES']['CUSTOMER_CURRENCY_ARRAY']['cu_thousands_point'], '', $numbers);
            return $numbers;
        }
    }
    function getDefaultOrderStatus() {
        $filter = array();
        $filter[] = 'default_status=1';
        $filter[] = '(o.page_uid=\'0\' or o.page_uid=\'' . $this->showCatalogFromPage . '\') and o.deleted=0 and o.id=od.orders_status_id and od.language_id=\'0\'';
        $record = mslib_befe::getRecord('', 'tx_multishop_orders_status o, tx_multishop_orders_status_description od', '', $filter);
        return $record;
    }
    function getSpecificOrderStatusHistoryByOrdersId($orders_id, $status_id) {
        if (is_numeric($orders_id)) {
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('crdate, new_value', // SELECT ...
                    'tx_multishop_orders_status_history', // FROM ...
                    'orders_id=\'' . $orders_id . '\' and new_value=\'' . $status_id . '\'', // WHERE.
                    '', // GROUP BY...
                    'orders_status_history_id desc', // ORDER BY...
                    '1' // LIMIT ...
            );
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $order_status_history_items = array();
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                $order_status_history_items[] = $row;
            }
            return $order_status_history_items;
        }
    }
    function getOrderStatusHistoryByOrdersId($orders_id) {
        if (is_numeric($orders_id)) {
            $query = $GLOBALS['TYPO3_DB']->SELECTquery('new_value', // SELECT ...
                    'tx_multishop_orders_status_history', // FROM ...
                    'orders_id=\'' . $orders_id . '\'', // WHERE.
                    '', // GROUP BY...
                    'orders_status_history_id desc', // ORDER BY...
                    '' // LIMIT ...
            );
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $order_status_history_items = array();
            while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
                $order_status_history_items[] = $row['new_value'];
            }
            return $order_status_history_items;
        }
    }
    public function getProductsCategoriesCollection($categories_id) {
        if (is_numeric($categories_id)) {
            $subcats_array = array();
            $filterTmp = array();
            $filterTmp[] = 'node_id=' . $categories_id;
            $subcats_array = mslib_befe::getRecords('', 'tx_multishop_products_to_categories', '', $filterTmp, 'node_id');
            if (is_array($subcats_array) && count($subcats_array)) {
                return $subcats_array;
            }
            return false;
        }
        return false;
    }
    public function arrayToTable($rows, $idName = '', $settings = array()) {
        if (is_array($rows) && count($rows)) {
            $maxCellCounter = 0;
            foreach ($rows as $row) {
                $cellCounter = 0;
                $newMaxCellCounter=0;
                foreach ($row as $col => $val) {
                    $cellCounter++;
                    if ($cellCounter >= $newMaxCellCounter) {
                        $newMaxCellCounter++;
                    }
                }
                if ($newMaxCellCounter > $maxCellCounter) {
                    $maxCellCounter=$newMaxCellCounter;
                }
                break;
            }
            $inlineStyle = '';
            if (isset($settings['inlineStyles']['table']) && is_array($settings['inlineStyles']['table'])) {
                $inlineStyle .= implode(' ', $settings['inlineStyles']['table']);
            }
            $tblClasses=[];
            $tblClasses[]='table';
            $tblClasses[]='table-striped';
            $tblClasses[]='table-bordered';
            $tblClasses[]='tablesorter';
            if (isset($settings['tableClasses']) && is_array($settings['tableClasses'])) {
                foreach ($settings['tableClasses'] as $tableClasses) {
                    $tblClasses[]=$tableClasses;
                }
            }
            $content .= '<table' . ($idName ? ' id="' . $idName . '"' : '') . ' class="'.implode(' ', $tblClasses).'"' . ($inlineStyle ? ' ' . $inlineStyle : '') . '>';
            // If we do not want to parse a th then set skipCellHeading to 1
            if (!$settings['skipTableHeadings']) {
	            if (isset($settings['emptyTfootAfterThead'])) {
		            $emptyTfoots = array();
		            $emptyTfoots[] = '<tfoot><tr>';
	            }
                $content .= '<thead><tr>';
                if ($settings['keyNameAsHeadingTitle']) {
                    $cellCounter = 0;
                    foreach ($rows[0] as $colName => $colVal) {
                        $inlineStyle = '';
                        if (count($rows[0]) == ($cellCounter + 1) && count($rows[0]) < ($maxCellCounter)) {
                            $inlineStyle = ' colspan="' . ($maxCellCounter - ($cellCounter + 1)) . '"';
                        }
                        if (isset($settings['inlineStyles']['th'][$cellCounter]) && is_array($settings['inlineStyles']['th'][$cellCounter])) {
                            $inlineStyle .= ' ' . implode(' ', $settings['inlineStyles']['th'][$cellCounter]);
                        }
                        $classes = array();
                        if (is_array($settings['cellClasses']) && isset($settings['cellClasses'][$cellCounter])) {
                            $classes[] = $settings['cellClasses'][$cellCounter];
                        }
                        $classes[] = 'cell' . ($cellCounter + 1);
                        $content .= '<th' . $inlineStyle . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '>' . $colName . '</th>';
	                    $emptyTfoots[] = '<td></td>';
                        $cellCounter++;
                    }
                } else {
                    $cellCounter = 0;
                    foreach ($rows[0] as $colName => $colVal) {
                        $inlineStyle = '';
                        if (count($rows[0]) == ($cellCounter + 1) && count($rows[0]) < ($maxCellCounter)) {
                            $inlineStyle = ' colspan="' . ($maxCellCounter - ($cellCounter + 1)) . '"';
                        }
                        if (isset($settings['inlineStyles']['th'][$cellCounter]) && is_array($settings['inlineStyles']['th'][$cellCounter])) {
                            $inlineStyle .= ' ' . implode(' ', $settings['inlineStyles']['th'][$cellCounter]);
                        }
                        $classes = array();
                        if (is_array($settings['cellClasses']) && isset($settings['cellClasses'][$cellCounter])) {
                            $classes[] = $settings['cellClasses'][$cellCounter];
                        }
                        $classes[] = 'cell' . ($cellCounter + 1);
                        $content .= '<th' . $inlineStyle . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '>' . $colVal . '</th>';
	                    $emptyTfoots[] = '<td></td>';
                        $cellCounter++;
                    }
                }
                $content .= '</tr></thead>';
	            if (isset($settings['emptyTfootAfterThead'])) {
		            $emptyTfoots[] = '</tr></tfoot>';
					$content .= implode('', $emptyTfoots);
	            }
                $rowCounter = 0;
                if ($settings['keyNameAsHeadingTitle']) {
                    $rowCounter = 1;
                }
            } else {
                $rowCounter = 0;
            }
            $content .= '<tbody>';
            $odd = '1';
            foreach ($rows as $row) {
                if ($rowCounter || (!$rowCounter && $settings['skipTableHeadings'])) {
                    if ($odd) {
                        $odd = 0;
                    } else {
                        $odd = 1;
                    }
                    $trClass = array();
                    if (is_array($settings['trClassClass']) && $settings['trClassClass'][($rowCounter + 1)]) {
                        $trClass = array();
                        $trClass[] = $settings['trClassClass'][($rowCounter + 1)];
                    }
                    $inlineStyle = '';
                    if (isset($settings['inlineStyles']['trOddEven'][$odd]) && is_array($settings['inlineStyles']['td'][$odd])) {
                        $inlineStyle .= ' ' . implode(' ', $settings['inlineStyles']['trOddEven'][$odd]);
                    }
                    $content .= '<tr' . (count($trClass) ? ' class="' . implode(' ', $trClass) . '"' : '') . ($inlineStyle ? ' ' . $inlineStyle : '') . '>';
                    $cellCounter = 0;
                    foreach ($row as $col => $val) {
                        $classes = array();
                        if (is_array($settings['cellClasses']) && isset($settings['cellClasses'][$cellCounter])) {
                            $classes[] = $settings['cellClasses'][$cellCounter];
                        }
                        $classes[] = 'cell' . ($cellCounter + 1);
                        $inlineStyle = '';
                        if (count($row) == ($cellCounter + 1) && count($row) < ($maxCellCounter)) {
                            $inlineStyle = ' colspan="' . ($maxCellCounter - ($cellCounter + 1)) . '"';
                        }
                        if (isset($settings['inlineStyles']['td'][$cellCounter]) && is_array($settings['inlineStyles']['td'][$cellCounter])) {
                            $inlineStyle .= ' ' . implode(' ', $settings['inlineStyles']['td'][$cellCounter]);
                        }
                        $content .= '<td' . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . $inlineStyle . '>' . $val . '</td>';
                        $cellCounter++;
                    }
                    $content .= '</tr>';
                }
                $rowCounter++;
            }
            $content .= '</tbody>';
            if ($settings['sorter']) {
                $GLOBALS['TSFE']->additionalHeaderData['tablesorter_js_' . $idName] = '<script data-ignore="true">
                jQuery(document).ready(function($) {
                        $(\'#' . $idName . '\').tablesorter();
                    });
                </script>
                ';
            }
            if ($settings['sumTr']) {
                $GLOBALS['TSFE']->additionalHeaderData['sumtr_js_' . $idName] = '<script data-ignore="true">
                jQuery(document).ready(function($) {
                        $(\'#' . $idName . '\').sumtr({
                            readValue : function(e) {
                                return Math.round(e.html().toString().replace(/[^\d.-]/g, \'\') * 100) / 100; return !isNaN(r) ? r : 0;
                            },
                            formatValue : function(val) { return Math.round(val*100)/100; }
                        });
                    });
                </script>
                ';
                $content .= '
                <tfoot>
                <tr class="summary">
                    <td class="text-right">Total:</td>
                ';
                $rowCounter = 0;
                foreach ($rows[0] as $colName => $colVal) {
                    if ($rowCounter) {
                        $content .= '<td class="text-right grandTotal"></td>';
                    }
                    $rowCounter++;
                }
                $content .= '
                <tr>
                </tfoot>
                ';
            }
            $content .= '</table>';
            return $content;
        }
    }
    function bootstrapGrids($gridCols, $columns = 3, $gridClass = '') {
        if (is_array($gridCols) && count($gridCols) && is_numeric($columns)) {
            $array = array_chunk($gridCols, ceil(count($gridCols) / $columns));
            $col_size = ceil((12 / $columns));
            if ($columns >= 12) {
                $col_size = 2;
            }
            $content .= '<div class="row">';
            foreach ($array as $col => $colArray) {
                $content .= '<div class="col-md-' . $col_size . (!empty($gridClass) ? ' ' . $gridClass : '') . '">';
                $content .= implode('', $colArray);
                $content .= '</div>';
            }
            $content .= '</div>';
            return $content;
        }
    }
    function bootstrapTabs($tabsArray, $bodyContent = '', $activeKey = '') {
        if (is_array($tabsArray) && count($tabsArray)) {
            $content .= '<ul class="tabs nav nav-tabs" role="tablist">';
            $counter = 0;
            foreach ($tabsArray as $col => $tabArray) {
                $classes = array();
                if ($activeKey && $tabArray['key'] == $activeKey) {
                    $classes[] = 'active';
                } else {
                    if (!$counter && !$activeKey) {
                        $classes[] = 'active';
                    }
                }
                $link = '#' . $tabArray['key'];
                if ($tabArray['tabLink'] != '') {
                    $link = $tabArray['tabLink'];
                    $content .= '<li role="presentation"' . (is_array($classes) && count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '><a href="' . $link . '"><span>' . htmlspecialchars($tabArray['title']) . '</span></a></li>';
                } else {
                    $content .= '<li role="presentation"' . (is_array($classes) && count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '><a href="' . $link . '" aria-controls="1" role="tab" data-toggle="tab"><span>' . htmlspecialchars($tabArray['title']) . '</span></a></li>';
                }
                $counter++;
            }
            $content .= '</ul>';
            $content .= '<div class="tab-content">';
            if ($bodyContent) {
                $content .= $bodyContent;
            } else {
                $counter = 0;
                foreach ($tabsArray as $col => $tabArray) {
                    $classes = array();
                    $classes[] = 'tab-pane';
                    if (!$counter) {
                        $classes[] = 'active';
                    }
                    $content .= '<div role="tabpanel"' . (is_array($classes) && count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . ' id="' . $tabArray['key'] . '">' . $tabArray['content'] . '</div>';
                    $counter++;
                }
            }
            $content .= '</div>';
            return $content;
        }
    }
    function br2nl($html) {
        if ($html) {
            return preg_replace('/<br\s?\/?>/i', "\r\n", $html);
        }
    }
    function tableExists($tableName) {
        if ($tableName) {
            $query = 'SELECT 1 FROM ' . addslashes($tableName) . ' LIMIT 1;';
            if ($res = $GLOBALS['TYPO3_DB']->sql_query($query)) {
                return true;
            }
            return false;
        }
    }
    function getCategoryCrumString($categories_id) {
        if (is_numeric($categories_id)) {
            $cats = mslib_fe::Crumbar($categories_id);
            if (is_array($cats)) {
                $cats = array_reverse($cats);
                $items = array();
                if (count($cats) > 0) {
                    foreach ($cats as $cat) {
                        $items[] = $cat['name'];
                    }
                }
                return implode(' > ', $items);
            }
        }
    }
    // Convert duration to human friendly string
    function humanFriendlyDuration($seconds) {
        // Define time units
        $units = array(
                "year"   => 365*24*3600,
                "month"  => 30*24*3600,
                "week"   => 7*24*3600,
                "day"    => 24*3600,
                "hour"   => 3600,
                "minute" => 60,
                "second" => 1,
        );

        // Create an empty array to hold the result
        $result = array();

        // Calculate the number of each unit in the duration
        foreach ($units as $name => $divisor) {
            $quotient = floor($seconds / $divisor);
            if ($quotient) {
                $result[] = $quotient . ' ' . $name . ($quotient > 1 ? 's' : '');
                $seconds -= $quotient * $divisor;
            }
        }

        // Combine the result array into a string
        return implode(', ', $result);
    }
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_befe.php"]) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/multishop/pi1/classes/class.mslib_befe.php"]);
}
