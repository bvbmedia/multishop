<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_include_path(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').PATH_SEPARATOR.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/');
if ($this->post) {
	$erno=array();
	if (!$this->post['name']) {
		$erno[]='No name defined';
	}
	if (!count($erno)) {
		if ($this->post['name']) {
			$insertArray=array();
			$insertArray['name']=$this->post['name'];
			$insertArray['status']=$this->post['status'];
			if (is_numeric($this->post['rules_group_id'])) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_tax_rule_groups', 'rules_group_id='.$this->post['rules_group_id'], $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$rules_group_id=$this->post['rules_group_id'];
			} else {
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_tax_rule_groups', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$rules_group_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			}
			if ($rules_group_id) {
				$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_tax_rules', 'rules_group_id='.$rules_group_id);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				foreach ($this->post['tax_id'] as $cn_iso_nr=>$array) {
					if ($cn_iso_nr) {
						foreach ($array as $zn_country_iso_nr=>$tax_id) {
							$insertArray=array();
							$insertArray['rules_group_id']=$rules_group_id;
							$insertArray['zn_country_iso_nr']=$zn_country_iso_nr;
							$insertArray['cn_iso_nr']=$cn_iso_nr;
							if ($tax_id) {
								$insertArray['tax_id']=$tax_id;
							}
							if ($zn_country_iso_nr && $this->post['state_modus'][$cn_iso_nr][$zn_country_iso_nr]==2) {
								$insertArray['country_tax_id']=$this->post['tax_id'][$cn_iso_nr][0];
							}
							if ($this->post['state_modus'][$cn_iso_nr][$zn_country_iso_nr]) {
								$insertArray['state_modus']=$this->post['state_modus'][$cn_iso_nr][$zn_country_iso_nr];
							}
							if ($insertArray['tax_id'] or $insertArray['state_modus']) {
								$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_tax_rules', $insertArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							}
						}
					}
				}
			}
		}
		unset($this->post);
	}
}
if (is_array($erno) and count($erno)>0) {
	$content.='<div class="alert alert-danger">';
	$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
	foreach ($erno as $item) {
		$content.='<li>'.$item.'</li>';
	}
	$content.='</ul>';
	$content.='</div>';
}
if ($this->get['tx_multishop_pi1']['action']) {
	switch ($this->get['tx_multishop_pi1']['action']) {
		case 'delete':
			$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_tax_rule_groups', 'rules_group_id=\''.$this->get['tx_multishop_pi1']['rules_group_id'].'\'');
			$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			break;
		case 'update_default_status':
			if (intval($this->get['tx_multishop_pi1']['rules_group_id'])) {
				$updateArray=array();
				$updateArray['default_status']=1;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_tax_rule_groups', 'rules_group_id=\''.$this->get['tx_multishop_pi1']['rules_group_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$updateArray=array();
				$updateArray['default_status']=0;
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_tax_rule_groups', 'rules_group_id <> \''.$this->get['tx_multishop_pi1']['rules_group_id'].'\'', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			break;
	}
}
if ($this->get['delete'] and is_numeric($this->get['rules_group_id'])) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_tax_rule_groups', 'rules_group_id=\''.$this->get['rules_group_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
} elseif (is_numeric($this->get['rules_group_id'])) {
	$tax_rules_group=mslib_fe::getTaxRulesGroup($this->get['rules_group_id']);
	$this->post['rules_group_id']=$tax_rules_group['rules_group_id'];
	$this->post['name']=$tax_rules_group['name'];
	$this->post['status']=$tax_rules_group['status'];
}
$content.='
<div class="panel panel-default">
<div class="panel-heading"><h3>ADD / UPDATE TAX RULES GROUP</h3></div>
<div class="panel-body">
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post" class="form-horizontal">
	<fieldset>
		
		<div class="form-group">
				<label for="" class="control-label col-md-2">Name</label>
				<div class="col-md-10">
				<input class="form-control" type="text" name="name" id="name" value="'.$this->post['name'].'">
				</div>
		</div>
		<div class="form-group">
				<label for="" class="control-label col-md-2">Status</label>
				<div class="col-md-10">
					<div class="radio-inline">
					<input name="status" type="radio" value="1" '.((!isset($this->post['status']) or $this->post['status']==1) ? 'checked' : '').' /> on
					</div>
					<div class="radio-inline">
					<input name="status" type="radio" value="0" '.((isset($this->post['status']) and $this->post['status']==0) ? 'checked' : '').' /> off
					</div>
				</div>
		</div>
		<div class="form-group">
				<label for="" class="control-label col-md-2">&nbsp;</label>
				<div class="col-md-10">
				<input name="rules_group_id" type="hidden" value="'.$this->post['rules_group_id'].'" />
				<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> '.$this->pi_getLL('save').'</button>
				</div>
		</div>
	</fieldset>
	<br>
';
if (is_array($tax_rules_group) and $tax_rules_group['rules_group_id']) {
	$state_modus_array=array();
	$state_modus_array[0]='Apply country tax only';
	$state_modus_array[1]='Apply state tax only';
	$state_modus_array[2]='Apply both taxes';
	$str="SELECT * from tx_multishop_zones order by name";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$zones=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$zones[]=$row;
	}
	$counter=0;
	// load taxes
	$str="SELECT * from tx_multishop_taxes order by name";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$taxes=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$taxes[]=$row;
	}
	foreach ($zones as $zone) {
		if ($zone['name']) {
			$counter++;
			$GLOBALS['TSFE']->additionalHeaderData[]='
			<script type="text/javascript">
					jQuery(function($) {
						$(".category_listing_ul_'.$counter.'").treeview({
							collapsed: true,
							animated: "medium",
							control:"#sidetreecontrol",
							persist: "location"
						});
					})
			</script>
			';
			$query_st="SELECT * from static_territories st";
			$res_st=$GLOBALS['TYPO3_DB']->sql_query($query_st);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_st)) {
				$tab_content.='<ul class="category_listing_ul_territories_'.$counter.'" id="msAdmin_category_listing_ul_territories">';
				while ($row_st=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_st)) {
					$query="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.zone_id='".$zone['id']."' and c.cn_parent_tr_iso_nr='".$row_st['tr_iso_nr']."' and c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
						$tab_content.='<li class="item_territories_'.$counter.'">';
						$tab_content.='<label class="tree_item_label">';
						$tab_content.=$row_st['tr_name_en'];
						$tab_content.='</label>';
						$tab_content.='	<ul class="category_listing_ul_'.$counter.'" id="msAdmin_category_listing_ul">';
						while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
							$tab_content.='<li class="item_'.$counter.'">';
							$tab_content.='<label class="tree_item_label">';
							$tab_content.=$row['cn_short_en'];
							$tab_content.='</label>';
							$tab_content.='<select name="tax_id['.$row['cn_iso_nr'].'][0]"><option value="">No TAX</option>';
							$query3=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
								'tx_multishop_tax_rules', // FROM ...
								"cn_iso_nr='".$row['cn_iso_nr']."' and zn_country_iso_nr='0' and rules_group_id	 = ".$this->get['rules_group_id'], // WHERE...
								'', // GROUP BY...
								'', // ORDER BY...
								'' // LIMIT ...
							);
							$res3=$GLOBALS['TYPO3_DB']->sql_query($query3);
							$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res3);
							foreach ($taxes as $tax) {
								$tab_content.='<option value="'.$tax['tax_id'].'"'.($tax['tax_id']==$row3['tax_id'] ? ' selected' : '').'>'.$tax['name'].'</option>'."\n";
							}
							$tab_content.='</select>';
							// now load the stated
							$query2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
								'static_country_zones', // FROM ...
								'zn_country_iso_nr='.$row['cn_iso_nr'], // WHERE...
								'', // GROUP BY...
								'', // ORDER BY...
								'' // LIMIT ...
							);
							$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($res2)>0) {
								$tab_content.='<div class="state_tax_sb_wrapper"><ul class="state_tax_sb">';
								while ($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
									$tab_content.='<li class="item_'.$counter.''.(!$row['status'] ? ' ' : '').'">';
									$tab_content.='<label class="tree_item_label">';
									$tab_content.=$row2['zn_name_local'];
									$tab_content.='</label>';
									$tab_content.='<select name="tax_id['.$row['cn_iso_nr'].']['.$row2['uid'].']"><option value="">No TAX</option>';
									$query3=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
										'tx_multishop_tax_rules', // FROM ...
										"cn_iso_nr='".$row['cn_iso_nr']."' and zn_country_iso_nr='".$row2['uid']."' and rules_group_id	 = ".$this->get['rules_group_id'], // WHERE...
										'', // GROUP BY...
										'', // ORDER BY...
										'' // LIMIT ...
									);
									$res3=$GLOBALS['TYPO3_DB']->sql_query($query3);
									$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res3);
									foreach ($taxes as $tax) {
										$tab_content.='<option value="'.$tax['tax_id'].'"'.($tax['tax_id']==$row3['tax_id'] ? ' selected' : '').'>'.$tax['name'].'</option>'."\n";
									}
									$tab_content.='</select>';
									$tab_content.='<select name="state_modus['.$row['cn_iso_nr'].']['.$row2['uid'].']">';
									foreach ($state_modus_array as $state_modus=>$label) {
										$tab_content.='<option value="'.$state_modus.'"'.($state_modus==$row3['state_modus'] ? ' selected' : '').'>'.htmlspecialchars($label).'</option>'."\n";
									}
									$tab_content.='</select>';
								}
								$tab_content.='</ul></div>';
							}
							$tab_content.='	</li>';
						}
						$tab_content.='</ul>';
						$tab_content.='</li>';
					}
				}
				$tab_content.='</ul>';
			}
			$tabs['zone_'.$counter]=array(
				$zone['name'],
				$tab_content
			);
			$tab_content='';
		}
	}
	$content.='
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
			jQuery(activeTab).show();
			return false;
		});
	});
	</script>
	<div id="tab-container">
		<ul class="tabs">
	';
	foreach ($tabs as $key=>$value) {
		$count++;
		$content.='<li'.(($count==1) ? ' class="active"' : '').'><a href="#'.$key.'">'.$value[0].'</a></li>';
	}
	$content.='
		</ul>
		<div class="tab_container">

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
	$content.=$save_block.'

		</div>
	</div>
	';
}
$content.='
</form>
';
if (!$this->get['edit']) {
	// load tax rules
	$str="SELECT * from tx_multishop_tax_rule_groups order by rules_group_id";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$tax_rules_groups=array();
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$tax_rules_groups[]=$row;
	}
	if (count($tax_rules_groups)) {
		$content.='<table class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">
		<thead><tr>
			<th class="cellID">ID</th>
			<th class="cellName">'.$this->pi_getLL('name').'</th>
			<th class="cellStatus">Status</th>
			<th class="cellStatus">'.$this->pi_getLL('default', 'Default').'</th>
			<th class="cellAction">'.$this->pi_getLL('action').'</th>
		</tr></thead>
		';
		foreach ($tax_rules_groups as $tax_rules_group) {
			$content.='
			<tr class="'.$tr_type.'">
				<td class="cellID">
					<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&rules_group_id='.$tax_rules_group['rules_group_id'].'&edit=1').'">'.$tax_rules_group['rules_group_id'].'</a>
				</td>
				<td class="cellName">
					<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&rules_group_id='.$tax_rules_group['rules_group_id'].'&edit=1').'">'.$tax_rules_group['name'].'</a>
				</td>
				<td class="cellStatus">';
			if (!$tax_rules_group['status']) {
				$content.='';
				$content.='<span class="admin_status_red" alt="'.$this->pi_getLL('disable').'"></span>';
			} else {
				$content.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
				$content.='';
			}
			$content.='</td>
				<td class="cellStatus">';
			if (!$tax_rules_group['default_status']) {
				$content.='';
				$content.='<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_default_status&tx_multishop_pi1[rules_group_id]='.$tax_rules_group['rules_group_id'].'&tx_multishop_pi1[status]=1').'"><span class="admin_status_green disabled" alt="'.$this->pi_getLL('enabled').'"></span></a>';
			} else {
				$content.='<span class="admin_status_green" alt="'.$this->pi_getLL('enable').'"></span>';
				$content.='';
			}
			$content.='
				</td>
				<td class="cellAction">
					<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=delete&tx_multishop_pi1[rules_group_id]='.$tax_rules_group['rules_group_id']).'" onclick="return confirm(\''.$this->pi_getLL('are_you_sure').'?\')" class="btn btn-danger btn-sm admin_menu_remove" alt="'.$this->pi_getLL('delete').'"><i class="fa fa-trash-o"></i></a>
				</td>
			</tr>
			';
		}
		$content.='</table>';
	}
}
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></div></div></div>';
$content=''.mslib_fe::shadowBox($content).'';
?>