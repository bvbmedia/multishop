<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	var originalLeave = $.fn.popover.Constructor.prototype.leave;
	$.fn.popover.Constructor.prototype.leave = function(obj){
	  var self = obj instanceof this.constructor ? obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data(\'bs.\' + this.type)
	  var container, timeout;
	  originalLeave.call(this, obj);
	  if(obj.currentTarget) {
		container = $(obj.currentTarget).siblings(\'.popover\')
		timeout = self.timeout;
		container.one(\'mouseenter\', function(){
		  //We entered the actual popover – call off the dogs
		  clearTimeout(timeout);
		  //Let\'s monitor popover content instead
		  container.one(\'mouseleave\', function(){
			  $.fn.popover.Constructor.prototype.leave.call(self, self);
			  $(".popover-link").popover("hide");
		  });
		})
	  }
	};
	$(".msadminTooltip").popover({
		placement: "right",
		html: true,
		trigger:"hover",
		delay: {show: 20, hide: 200}
	});

	$(".nav-tabs a:first").tab("show");

	var lochash=window.location.hash;
	if (lochash!="") {
		var li_this=$("ul.nav-tabs > .tab-pane").find("a[href=\'" + lochash + "\']").parent();
		if (li_this.length > 0) {
			$("ul.nav-tabs li").removeClass("active");
			$(li_this).addClass("active");
			$(".tab-pane").hide();
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
	$innerContent.='<table width="100%" border="0" align="center" class="msadmin_border table table-striped table-bordered" id="admin_modules_listing">';
	$innerContent.='<thead><tr><th colspan="'.$colspan.'" class="module_heading">'.$cat['gtitle'].' (ID: '.$cat['gid'].')</th></tr></thead>';
	$innerContent.='<thead><tr>
	<th>'.$this->pi_getLL('title').'</th>
	<th>'.$this->pi_getLL('current_value').'</th>
	</tr></thead>';
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
		$editLink=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_module&tx_multishop_pi1[gid]='.$cat['gid'].'&module_id='.$row['id'].'&action=edit_module', 1);
//		$row['description']='';
		$innerContent.='<tr class="'.$tr_type.'">
		<td><strong><a href="'.$editLink.'" title="'.htmlspecialchars('<h3>'.$row['configuration_title'].'</h3><p>'.$row['description']).'</p>Key: '.$row['configuration_key'].'" class="msadminTooltip">'.$row['configuration_title'].'</a></strong></td>
		<td><a href="'.$editLink.'">'.$this->ms['MODULES'][$row['configuration_key']].'</a></td>
		</tr>';
		//<td><a href="'.$editLink.'">'.$this->ms['MODULES']['GLOBAL_MODULES'][$row['configuration_key']].'</a></td>
		//$innerContent.='<tr class="'.$tr_type.'"><td colspan="'.$colspan.'">'.$row['description'].'</td></tr>';
	}
	$innerContent.='</table>';
	$tabs['module'.$cat['gid']]=array(
		$cat['gtitle'],
		$innerContent
	);
	$tmp='';
}
$content.='</div>';
$content='<div class="panel-heading"><h3>'.$this->pi_getLL('admin_multishop_settings').'</h3></div>';
$content.='<div class="panel-body">
<div id="tab-container" class="msadminVerticalTabs">
    <ul class="nav nav-tabs" role="tablist" id="admin_modules">';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='<li'.(($count==1) ? '' : '').' role="presentation"><a href="#'.$key.'" aria-controls="profile" role="tab" data-toggle="tab">'.$value[0].'</a></li>';
}
$content.='
    </ul>
    <div class="tab-content">
	';
$count=0;
foreach ($tabs as $key=>$value) {
	$count++;
	$content.='
        <div id="'.$key.'" class="tab-pane" role="tabpanel">
        	<form id="form1" name="form1" method="get" action="index.php">
			'.$formTopSearch.'
			</form>
			'.$value[1].'
        </div>
	';
}
$content.='
    </div>
</div>';
$content.='<hr><div class="clearfix"><div class="pull-right"><a class="btn btn-success" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>