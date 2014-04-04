<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
mslib_befe::loadLanguages();
if (isset($this->post['new_options_groups_name']) && !empty($this->post['new_options_groups_name'])) {
	$sql_chk="select attributes_options_groups_id from tx_multishop_attributes_options_groups where attributes_options_groups_name = '".addslashes($this->post['new_options_groups_name'])."' and language_id = '".$this->sys_language_uid."' order by sort_order";
	$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
	if (!$GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk)) {
		$sql_chk="select attributes_options_groups_id from tx_multishop_attributes_options_groups order by attributes_options_groups_id desc limit 1";
		$qry_chk=$GLOBALS['TYPO3_DB']->sql_query($sql_chk);
		$rs_chk=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
		$max_optid=$rs_chk['attributes_options_groups_id']+1;
		$sql_ins="insert into tx_multishop_attributes_options_groups (attributes_options_groups_id, language_id, attributes_options_groups_name, sort_order) values ('".$max_optid."', '0', '".addslashes($this->post['new_options_groups_name'])."', '".$max_optid."')";
		$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
	}
	header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups'));
}
if (is_array($this->post['option_groups_names']) and count($this->post['option_groups_names'])) {
	foreach ($this->post['option_groups_names'] as $attributes_options_groups_id=>$array) {
		foreach ($array as $language_id=>$value) {
			$updateArray=array();
			$updateArray['language_id']=$language_id;
			$updateArray['attributes_options_groups_id']=$attributes_options_groups_id;
			$updateArray['attributes_options_groups_name']=$value;
			$str="select 1 from tx_multishop_attributes_options_groups where attributes_options_groups_id='".$attributes_options_groups_id."' and language_id='".$language_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_attributes_options_groups', 'attributes_options_groups_id=\''.$attributes_options_groups_id.'\' and language_id=\''.$language_id.'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_attributes_options_groups', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
		}
	}
	header('Location: ' . $this->FULL_HTTP_URL . mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups'));
}
$str="select * from tx_multishop_attributes_options_groups where language_id='0' order by sort_order";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
if ($rows) {
	$content.='
	<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups').'" method="post" name="admin_attributes_options_groups">
	<ul class="attribute_options_groups_sortable">';
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$content.='<li id="options_groups_'.$row['attributes_options_groups_id'].'">
		<h2><span class="option_group_id">Option group name: <input name="option_groups_names['.$row['attributes_options_groups_id'].'][0]" type="text" value="'.htmlspecialchars($row['attributes_options_groups_name']).'"  /></span>';
		foreach ($this->languages as $key=>$language) {
			if ($key>0) {
				$str3="select attributes_options_groups_name from tx_multishop_attributes_options_groups where attributes_options_groups_id='".$row['attributes_options_groups_id']."' and language_id='".$key."'";
				$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
				while (($row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3))!=false) {
					if ($row3['attributes_options_groups_name']) {
						$value=htmlspecialchars($row3['attributes_options_groups_name']);
					}
				}
				$content.=$this->languages[$key]['title'].' <input name="option_groups_names['.$row['attributes_options_groups_id'].']['.$key.']" type="text" value="'.$value.'"  />';
			}
		}
		$content.='<a href="#" class="delete_options admin_menu_remove" rel="'.$row['attribbutes_options_groups_id'].'">delete</a>&nbsp;';
		$content.='</h2>
		</li>';
	}
	$content.='</ul>
	<br /><input name="Submit" type="submit" value="Save" class="msadmin_button" />
	</form>';
	// now load the sortables jQuery code
	$content.='<script type="text/javascript">
		var result 	= jQuery(".attribute_options_groups_sortable").sortable({
			cursor:     "move", 
			//axis:       "y", 
			update: function(e, ui) { 
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=update_attributes_options_groups_sortable&tx_multishop_pi1[type]=options_groups').'";
				jQuery(this).sortable("refresh"); 
				sorted = jQuery(this).sortable("serialize","id"); 
				jQuery.ajax({ 
					type:   "POST",
					url:    href,
					data:   sorted,
					success: function(msg) {
						//do something with the sorted data
					}
				}); 
			} 
		});
	  </script>';
} else {
	$content.='<h1>No attributes options groups defined yet</h1>';
	$content.='You can add attributes options groups below.';
}
$content.='<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]=admin_attributes_options_groups').'" method="post" name="new_attributes_options_groups" id="add_new_options_groups">
<div class="new_options_groups_name_input"><label for="new_options_groups_name">Add new options group:</label> <input type="text" name="new_options_groups_name" id="new_options_groups_name" /> <input type="submit" name="add_new_options_groups" value="Add"></div>
</form>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>