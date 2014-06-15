<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
window.onload = function(){
  var text_input = document.getElementById (\'configuration[local]\');
  text_input.focus ();
  text_input.select ();
}
</script>
';
$GLOBALS['TSFE']->additionalHeaderData[]='
<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery(".tab_content").hide();
	jQuery("ul.tabs li:first").addClass("active").show();
	jQuery(".tab_content:first").show();
	jQuery("ul.tabs li").click(function() {
		jQuery("ul.tabs li").removeClass("active");
		jQuery(this).addClass("active");
		jQuery(".tab_content").hide();
		var activeTab = jQuery(this).find("a").attr("href");
		jQuery(activeTab).fadeIn(0);
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
		header("Location: ".$this->post['tx_multishop_pi1']['referrer']);
		exit();
	} else {
		header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_modules', 1));
		exit();
	}
}
if ($configuration['id'] or $_REQUEST['action']=='edit_module') {
	$configuration['parent_id']=$this->get['cid'];
	$save_block='
		<div class="save_block">
			<input name="cancel" type="button" value="Cancel" onClick="parent.window.hs.close();" class="submit" />
			<input name="Submit" type="submit" value="Save" class="submit" />
		</div>		
	';
	$content.='
	<form class="admin_configuration_edit" name="admin_categories_edit_'.$configuration['categories_id'].'" id="admin_categories_edit_'.$configuration['categories_id'].'" method="post" action="'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=admin_ajax&module_id='.$configuration['id']).'" enctype="multipart/form-data">';
	$content.=$save_block;
	$content.='
		<div class="account-field">
			<label for="categories_name">Name</label>
			'.htmlspecialchars($configuration['configuration_title']).'
		</div>
		<div class="account-field">		
			<label>Description</label>
			'.htmlspecialchars($configuration['description']).'
		</div>		
		<div class="account-field">		
			<label>Key</label>
			'.htmlspecialchars($configuration['configuration_key']).'
		</div>		
		';
	$content.='
		<div class="account-field configuration_modules">
			<label for="value">Default value</label>	
';
	if ($configuration['set_function']) {
		eval('$value_field = mslib_fe::'.$configuration['set_function'].'\''.addslashes(htmlspecialchars($this->ms['MODULES']['GLOBAL_MODULES'][$configuration['configuration_key']])).'\',\'global\');');
	} else {
		$value_field=mslib_fe::tep_draw_input_field('configuration[global]', $this->ms['MODULES']['GLOBAL_MODULES'][$configuration['configuration_key']]);
	}
	$content.=$value_field.'

		</div>';
	$content.='
		<div class="account-field configuration_modules">
			<label for="value">Current value</label>	
';
	if ($configuration['set_function']) {
		eval('$value_field = mslib_fe::'.$configuration['set_function'].'\''.addslashes(htmlspecialchars($this->ms['MODULES'][$configuration['configuration_key']])).'\',\'local\');');
	} else {
		$value_field=mslib_fe::tep_draw_input_field('configuration[local]', $this->ms['MODULES'][$configuration['configuration_key']]);
	}
	/*
		if (tep_not_null($configuration['use_function']))
		{
			$cfgValue = $configuration['use_function']($configuration['value']);
		}
		else
		{
			$cfgValue = $configuration['value'];
		}
	*/
	$content.=$value_field.'

		</div>';
	$content.='
	<input name="configuration_key" type="hidden" value="'.$configuration['configuration_key'].'" />
	<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
	<input type="hidden" name="tx_multishop_pi1[referrer]" id="msAdminReferrer" value="'.$subpartArray['###VALUE_REFERRER###'].'" >
	</form>';
	$content.='
			<div id="ajax_message_'.$configuration['categories_id'].'" class="ajax_message"></div>
	';
	$tabs['module'.$configuration['gid']]=array(
		'Configuration',
		$content
	);
	$content='';
	$content='<div class="main-heading"><h2>Admin Modules</h2></div>';
	$content.='
<div id="tab-container">
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
}
?>