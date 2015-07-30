<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	if ($(\'#configuration[local]\').length) {
		var text_input = $(\'#configuration[local]\');
  		text_input.focus ();
  		text_input.select ();
  	}
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
});
</script>
';
$tabs=array();
$subpartArray=array();
$subpartArray['###VALUE_REFERRER###']='';
if ($this->post['tx_multishop_pi1']['referrer']) {
	$subpartArray['###VALUE_REFERRER###']=$this->post['tx_multishop_pi1']['referrer'];
} else {
	$subpartArray['###VALUE_REFERRER###']=$_SERVER['HTTP_REFERER'];
}
if ($_REQUEST['action']=='edit_module') {
	$str="SELECT * from tx_multishop_configuration where id='".$_REQUEST['module_id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$configuration=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	$str="SELECT * from tx_multishop_configuration_values where configuration_key='".addslashes($configuration['configuration_key'])."' and page_uid='".$this->shop_pid."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$configuration_values=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
}
if ($this->post and $_REQUEST['action']=='edit_module') {
	$array=array();
	$row=mslib_befe::doesExist("tx_multishop_configuration_values", 'configuration_key', addslashes($this->post['configuration_key']), "and page_uid='".$this->shop_pid."'");
	if (isset($this->post['configuration']['local'])) {
		if ($row['configuration_key']) {
			$array['configuration_value']=$this->post['configuration']['local'];
			$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_configuration_values', 'configuration_key=\''.addslashes($this->post['configuration_key']).'\' and page_uid=\''.$this->shop_pid.'\'', $array);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		} else {
			$array['configuration_value']=$this->post['configuration']['local'];
			$array['configuration_key']=$this->post['configuration_key'];
			$array['page_uid']=$this->shop_pid;
			$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_configuration_values', $array);
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	if (isset($this->post['configuration']['global'])) {
		$array=array();
		$array['configuration_value']=$this->post['configuration']['global'];
		$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_configuration', 'configuration_key=\''.addslashes($this->post['configuration_key']).'\'', $array);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	}
	if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END'] or $this->conf['cacheConfiguration']) {
		if ($this->DOCUMENT_ROOT and !strstr($this->DOCUMENT_ROOT, '..')) {
			$command="rm -rf ".$this->DOCUMENT_ROOT."uploads/tx_multishop/tmp/cache/*";
			exec($command);
			$content.='<br /><p><strong>'.$this->pi_getLL('admin_label_multishop_cache_has_been_cleared').'</strong></p>';
		} else {
			$content.='<br /><p><strong>'.$this->pi_getLL('admin_label_cache_not_cleared_something_is_wrong_with_configuration_document_root_is_not_set_directly').'</strong></p>';
		}
	}
	if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END']) {
		mslib_befe::cacheLite('clear_all', 'delete');
	}
	$string='loadConfiguration_'.$this->shop_pid;
	if ($this->post['tx_multishop_pi1']['referrer']) {
		header("Location: ".$this->post['tx_multishop_pi1']['referrer']."#module".$this->post['tx_multishop_pi1']['gid']);
		exit();
	} else {
		header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_modules&gid='.$this->post['gid'], 1));
		exit();
	}
}
if ($configuration['id'] or $_REQUEST['action']=='edit_module') {
	$configuration['parent_id']=$this->get['cid'];
	$save_block='
<div class="clearfix">
		<div class="pull-right">
			<a href="'.$subpartArray['###VALUE_REFERRER###'].'" class="btn btn-danger"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-remove fa-stack-1x"></i></span> '.$this->pi_getLL('cancel').'</a>
			<button name="Submit" type="submit" value="" class="btn btn-success"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-check fa-stack-1x"></i></span> '.$this->pi_getLL('save').'</button>
		</div>
		</div>
		<hr>
	';
	$content.='
	<form class="form-horizontal admin_configuration_edit" name="admin_categories_edit_'.$configuration['categories_id'].'" id="admin_categories_edit_'.$configuration['categories_id'].'" method="post" action="'.mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]='.$_REQUEST['action'].'&module_id='.$configuration['id']).'" enctype="multipart/form-data">';
	$content.=$save_block;
	$content.='
		<div class="form-group">
			<label for="categories_name" class="control-label col-md-2">'.$this->pi_getLL('title').'</label>
			<div class="col-md-10">
			<p class="form-control-static">'.htmlspecialchars($configuration['configuration_title']).'</p>
			</div>
		</div>
		<div class="form-group">		
			<label class="control-label col-md-2">'.$this->pi_getLL('description').'</label>
			<div class="col-md-10">
			<p class="form-control-static">'.htmlspecialchars($configuration['description']).'</p>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2">'.$this->pi_getLL('name').'</label>
			<div class="col-md-10">
			<p class="form-control-static">'.htmlspecialchars($configuration['configuration_key']).'</p>
			</div>
		</div>
		';
	$content.='
		<div class="form-group configuration_modules">
			<label for="value" class="control-label col-md-2">'.$this->pi_getLL('default_value').'</label><div class="col-md-10">
';
	if ($configuration['set_function']) {
		eval('$value_field = mslib_fe::'.$configuration['set_function'].'\''.addslashes(htmlspecialchars($this->ms['MODULES']['GLOBAL_MODULES'][$configuration['configuration_key']])).'\',\'global\');');
	} else {
		$value_field=mslib_fe::tep_draw_input_field('configuration[global]', $this->ms['MODULES']['GLOBAL_MODULES'][$configuration['configuration_key']], 'class="form-control"');
	}
	$content.=$value_field.'
		</div></div>';
	$content.='
		<div class="form-group configuration_modules">
			<label for="value" class="control-label col-md-2">'.$this->pi_getLL('current_value').'</label><div class="col-md-10">
';
	if ($configuration['set_function']) {
		eval('$value_field = mslib_fe::'.$configuration['set_function'].'\''.addslashes(htmlspecialchars($this->ms['MODULES'][$configuration['configuration_key']])).'\',\'local\');');
	} else {
		$value_field=mslib_fe::tep_draw_input_field('configuration[local]', $this->ms['MODULES'][$configuration['configuration_key']], 'class="form-control"');
	}
	$content.=$value_field.'
		</div></div>';
	$content.='
	<input name="configuration_key" type="hidden" value="'.$configuration['configuration_key'].'" />
	<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
	<input name="tx_multishop_pi1[gid]" type="hidden" value="'.$this->get['tx_multishop_pi1']['gid'].'" />
	<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
	</form>';
	$content.='
			<div id="ajax_message_'.$configuration['categories_id'].'" class="ajax_message"></div>
	';
	$tabs['module'.$configuration['gid']]=array(
		$this->pi_getLL('configuration'),
		$content
	);
	$content='';
	$content='<div class="panel-heading"><h3>'.$this->pi_getLL('admin_multishop_settings').' INSERT DUMMY HEADING HERE</h3></div>';
	$content.='
<div class="panel-body">';
	$content.='
	<form id="form1" name="form1" method="get" action="index.php">
	'.$formTopSearch.'
	</form>
	';
	$count=0;
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.=$value[1];
	}
	$content.='
';
	$content.='<hr><div class="clearfix"><div class="pull-right"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div></div>';
	$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
}
?>