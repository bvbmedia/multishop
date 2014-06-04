<?php
$typo3Version=class_exists('t3lib_utility_VersionNumber') ? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) : t3lib_div::int_from_ver(TYPO3_version);
if ($typo3Version>=6000000) {
	include_once(t3lib_extMgm::extPath('multishop').'mod1/pages/includes/use.php');
} else {
	$t3lib_BEfuncAlias='t3lib_BEfunc';
}
$title='Help';
$content.='
	<div class="shadow_bottom">
		<fieldset>
				<center>
					<object width="480" height="385" > 
					<param name="movie" value="http://www.youtube.com/p/C0AB2CCEBAF06530?version=3&hl=nl_NL&fs="></param> 
					<param name="wmode" value="transparent"></param> 
					<embed src="http://www.youtube.com/p/C0AB2CCEBAF06530?version=3&hl=nl_NL&fs=" type="application/x-shockwave-flash" wmode="transparent" width="480" height="385" ></embed>
					</object> 
				</center>
			<strong>	
				<ul>
					<li><a href="http://www.typo3multishop.com/forum/" target="_blank">'.$this->Typo3Icon('actions-document-view', 'Forum').' Forum</a></li>
					<li><a href="http://www.typo3multishop.com/help/" target="_blank">'.$this->Typo3Icon('actions-document-view', 'Help').' Help</a></li>
					<li><a href="http://www.typo3multishop.com/contact-us/" target="_blank">'.$this->Typo3Icon('actions-document-view', 'Contact us').' Contact Us</a></li>
				</ul>			
			</strong>	
		</fieldset>
	</div>
	';
$this->content.=$this->doc->section($title, $content, 0, 1);
?>