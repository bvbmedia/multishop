<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($this->ms['MODULES']['ACCORDION_SETUP_MODULES']) {
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
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".msadminTooltip").tooltip({
		position: "bottom"
	});
	$(".tab_content").hide();
	$("ul.tabs li:first").addClass("active").show();
	$(".tab_content:first").show();
	$("ul.tabs li").click(function() {
		$("ul.tabs li").removeClass("active");
		$(this).addClass("active");
		$(".tab_content").hide();
		var activeTab = $(this).find("a").attr("href");
		$(activeTab).fadeIn(0);
		return false;
	});
	var lochash=window.location.hash;
	if (lochash!="") {
		var li_this=$("ul > li").find("a[href=\'" + lochash + "\']").parent();
		if (li_this.length > 0) {
			$("ul.tabs li").removeClass("active");
			$(li_this).addClass("active");
			$(".tab_content").hide();
			$(lochash).fadeIn(0);
		}
	}

});
</script>
';
$tabs=array();
$colspan=4;
$content='';
$str="SELECT c.*, g.configuration_title as gtitle, g.id as gid from tx_multishop_configuration c, tx_multishop_configuration_group g where c.group_id=g.id group by group_id order by g.configuration_title asc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$categories=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$categories[]=$row;
}
$content.='<div id="accordion2">';
foreach ($categories as $cat) {
	$innerContent='';
	$innerContent.='<div>';
	$innerContent.='<table width="100%" border="0" align="center" class="msadmin_border msZebraTable" id="admin_modules_listing">';
	$innerContent.='<tr><td colspan="'.$colspan.'" class="module_heading">'.t3lib_div::strtoupper($cat['gtitle']).' (ID: '.$cat['gid'].')</div></td></tr>';
	$innerContent.='<tr>
	<th>'.$this->pi_getLL('name').'</th>
	<th>'.$this->pi_getLL('current_value').'</th>
	</tr>';
	$str="SELECT * from tx_multishop_configuration where group_id='".addslashes($cat['group_id'])."' order by configuration_key";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$tr_type='even';
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$maxchars=150;
		if (strlen($this->ms['MODULES'][$row['configuration_key']])>$maxchars) {
			$this->ms['MODULES'][$row['configuration_key']]=substr($this->ms['MODULES'][$row['configuration_key']], 0, $maxchars).'...';
		}
		if (strlen($this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']])>$maxchars) {
			$this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']]=substr($this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']], 0, $maxchars).'...';
		}
		$editLink=mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_ajax&tx_multishop_pi1[gid]='.$cat['gid'].'&module_id='.$row['id'].'&action=edit_module', 1);
//		$row['description']='';
		$innerContent.='<tr class="'.$tr_type.'">
		<td><strong><a href="'.$editLink.'" title="'.htmlspecialchars('<h3>'.$row['configuration_title'].'</h3>'.$row['description']).'" class="msadminTooltip">'.$row['configuration_key'].'</a></strong></td>
		<td><a href="'.$editLink.'">'.$this->ms['MODULES'][$row['configuration_key']].'</a></td>
		</tr>';
		//<td><a href="'.$editLink.'">'.$this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']].'</a></td>
		//$innerContent.='<tr class="'.$tr_type.'"><td colspan="'.$colspan.'">'.$row['description'].'</td></tr>';
	}
	$innerContent.='</table>';
	$innerContent.='</div>';
	$tabs['module'.$cat['gid']]=array(
		$cat['gtitle'],
		$innerContent
	);
	$tmp='';
}
$content.='</div>';
$content='<div class="main-heading"><h2>'.$this->pi_getLL('admin_multishop_settings').'</h2></div>';
$content.='
<div id="tab-container" class="msadminVerticalTabs">
    <ul class="tabs" id="admin_modules">';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='<li'.(($count==1) ? ' class="active"' : '').'><a href="#'.$key.'">'.$value[0].'</a></li>';
}
$content.='
    </ul>
    <div class="tab_container">
	<form id="form1" name="form1" method="get" action="index.php">
	'.$formTopSearch.'
	</form>
	';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
			'.$value[1].'
        </div>
	';
}
$content.='
    </div>
</div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>