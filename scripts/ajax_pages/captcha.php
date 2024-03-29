<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
// Add little delay so the session wont conflict with the main script (bypass issue with TYPO3 7.6.15)
sleep(1);
$randomnr = rand(100000, 900000);
$session = array();
$session['captcha_code'] = md5($randomnr);
$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_captcha', $session);
$GLOBALS['TSFE']->storeSessionData();
$im = imagecreatetruecolor(75, 17);
$white = imagecolorallocate($im, 255, 255, 255);
$grey = imagecolorallocate($im, 128, 128, 128);
$black = imagecolorallocate($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, 200, 15, $black);
$font = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'res/fonts/verdana.ttf';
// add shadow
$teller = rand(48, 60);
imagettftext($im, 12, 0, 10, 15, $grey, $font, $randomnr);
imagettftext($im, 12, 0, 15, $teller, $grey, $font, $randomnr);
// add random number
imagettftext($im, 12, 0, 5, 15, $white, $font, $randomnr);
header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: image/gif");
imagegif($im);
imagedestroy($im);
exit();
