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
			$content.='<br /><p><strong>Multishop cache has been cleared.</strong></p>';
		} else {
			$content.='<br /><p><strong>Cache not cleared. Something is wrong with your configuration (DOCUMENT_ROOT is not set correctly).</strong></p>';
		}
	}
	if ($this->ms['MODULES']['GLOBAL_MODULES']['CACHE_FRONT_END']) {
		mslib_befe::cacheLite('clear_all', 'delete');
	}
	$string='loadConfiguration_'.$this->shop_pid;
	echo '
	<script>
	parent.window.location.reload();
	</script>
	';
	die();
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
	</form>';
	$content.='
			<div id="ajax_message_'.$configuration['categories_id'].'" class="ajax_message"></div>
			';
}
?>