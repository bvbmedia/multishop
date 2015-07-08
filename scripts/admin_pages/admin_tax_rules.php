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
			if (is_numeric($this->post['rule_id'])) {
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_tax_rules', 'rule_id='.$this->post['rule_id'], $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
			} else {
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_tax_rules', $insertArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
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
if ($this->get['delete'] and is_numeric($this->get['rule_id'])) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_tax_rules', 'rule_id=\''.$this->get['rule_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
} elseif (is_numeric($this->get['rule_id'])) {
	$tax_rule=mslib_fe::getTaxRule($this->get['rule_id']);
	$this->post['rule_id']=$tax_rule['rule_id'];
	$this->post['name']=$tax_rule['name'];
	$this->post['status']=$tax_rule['status'];
}
$content.='
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post">
	<fieldset>
		<legend>ADD / UPDATE TAX RULE</legend>
		<div class="account-field">
				<label for="">Name</label>
				<input type="text" name="name" id="name" value="'.$this->post['name'].'"> (name that will be displayed on the invoice. Example: VAT)		
		</div>
		<div class="account-field">
				<label for="">Status</label>
				<input name="rule_id" type="radio" value="1" '.((!isset($this->post['status']) or $this->post['status']==1) ? 'checked' : '').' /> on
				<input name="status" type="radio" value="0" '.((isset($this->post['status']) and $this->post['status']==0) ? 'checked' : '').' /> off
		</div>		
		<div class="account-field">
				<label for="">&nbsp;</label>
				<input name="rule_id" type="hidden" value="'.$this->post['rule_id'].'" />
				<input name="Submit" type="submit" value="'.$this->pi_getLL('save').'" class="btn btn-success" />
		</div>
	</fieldset>
</form>
';
// load tax rules
$str="SELECT * from tx_multishop_tax_rules order by rule_id";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$tax_rules=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$tax_rules[]=$row;
}
if (count($tax_rules)) {
	$content.='<table width="100%" border="0" align="center" class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Status</th>
		<th>Action</th>
	</tr>
	';
	foreach ($tax_rules as $tax_rule) {
		$content.='
		<tr class="'.$tr_type.'">
			<td>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&rule_id='.$tax_rule['rule_id'].'&edit=1').'">'.$tax_rule['rule_id'].'</a>
			</td>
			<td>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&rule_id='.$tax_rule['rule_id'].'&edit=1').'">'.$tax_rule['name'].'</a>
			</td>
			<td>
				'.$tax_rule['status'].'
			</td>			
			<td>
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&rule_id='.$tax_rule['rule_id'].'&delete=1').'">delete</a>
			</td>
		</tr>
		';
	}
	$content.='</table>';
}
$content.='<p class="extra_padding_bottom"><a class="btn btn-success" href="'.mslib_fe::typolink().'">'.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>