<?php
if(!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if($this->ms['MODULES']['ACCORDION_SETUP_MODULES']) {
	$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(function(){
	//jQuery("#accordion").accordion({ header: "h3" });
	jQuery("#accordion2 .boxes-heading").next().hide();
	jQuery("#accordion2 .boxes-heading").click(function () {
		jQuery(this).toggleClass("active").next().slideToggle("slow");
		}); 
	
});
</script>
';
}
$colspan=4;
$content.='<div class="main-heading"><h2>Admin Modules</h2></div>';
$str="SELECT c.*, g.configuration_title as gtitle, g.id as gid from tx_multishop_configuration c, tx_multishop_configuration_group g where c.group_id=g.id group by group_id order by g.id asc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$categories=array();
while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
	$categories[]=$row;
}
$content.='<div id="accordion2">';
$content.='<table width="100%" border="0" align="center" class="msadmin_border msZebraTable" id="admin_modules_listing">';
foreach($categories as $cat) {
	$content.='<div>';
	$content.='<tr><td colspan="'.$colspan.'" class="module_heading">'.t3lib_div::strtoupper($cat['gtitle']).' (ID: '.$cat['gid'].')</div></td></tr>';
	$content.='<tr>
	<th>Key</th>
	<th>Title</th>
	<th>Default Setting</th>
	<th>Current Setting</th>
	</tr>';
	$str="SELECT * from tx_multishop_configuration where group_id='".addslashes($cat['group_id'])."' order by id ";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$tr_type='even';
	while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
		if(!$tr_type or $tr_type == 'even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$maxchars=150;
		if(strlen($this->ms['MODULES'][$row['configuration_key']]) > $maxchars) {
			$this->ms['MODULES'][$row['configuration_key']]=substr($this->ms['MODULES'][$row['configuration_key']], 0, $maxchars).'...';
		}
		if(strlen($this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']]) > $maxchars) {
			$this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']]=substr($this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']], 0, $maxchars).'...';
		}
//		$row['description']='';
		$content.='<tr class="'.$tr_type.'">
		<td><strong><a href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&module_id='.$row['id']).'&action=edit_module" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$row['configuration_key'].'</a></strong></td>
		<td><strong><a href="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&module_id='.$row['id']).'&action=edit_module" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$row['configuration_title'].'</a></strong></td>
		<td>'.$this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']].'</td>
		<td>'.$this->ms['MODULES'][$row['configuration_key']].'</td>
		</tr>';
		$content.='<tr class="'.$tr_type.'"><td colspan="'.$colspan.'">'.$row['description'].'</td></tr>';
	}
	$content.='</div>';
}
$content.='</table>';
$content.='</div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>