<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_include_path(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').PATH_SEPARATOR.\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/');
if ($this->post) {
	$erno=array();
	if (!$this->post['tax_name']) {
		$erno[]='No name defined';
	}
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['postTaxesFormValidationPreProc'])) {
		$params=array(
			'erno'=>&$erno
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['postTaxesFormValidationPreProc'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	if (!count($erno)) {
		if ($this->post['tax_name']) {
			$updateArray=array();
			$updateArray['name']=$this->post['tax_name'];
			$updateArray['rate']=$this->post['tax_rate'];
			$updateArray['status']=$this->post['tax_status'];
			if (is_numeric($this->post['tax_id'])) {
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['updateTaxesPreProc'])) {
					$params=array(
						'tax_id'=>&$this->post['tax_id'],
						'updateArray'=>&$updateArray
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['updateTaxesPreProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_taxes', 'tax_id='.$this->post['tax_id'], $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['updateTaxesPostProc'])) {
					$params=array(
						'tax_id'=>&$this->post['tax_id'],
						'updateArray'=>&$updateArray
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['updateTaxesPostProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
			} else {
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['insertTaxesPreProc'])) {
					$params=array(
						'updateArray'=>&$updateArray
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['insertTaxesPreProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
					}
				}
				$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_taxes', $updateArray);
				$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				$tax_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['insertTaxesPostProc'])) {
					$params=array(
						'tax_id'=>&$tax_id,
						'updateArray'=>&$updateArray
					);
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['insertTaxesPostProc'] as $funcRef) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
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
if ($this->get['delete'] and is_numeric($this->get['tax_id'])) {
	$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_taxes', 'tax_id=\''.$this->get['tax_id'].'\'');
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['deleteTaxesPostProc'])) {
		$params=array(
			'tax_id'=>&$this->get['tax_id']
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['deleteTaxesPostProc'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
} elseif (is_numeric($this->get['tax_id'])) {
	$taxRow=mslib_fe::getTaxes($this->get['tax_id']);
	$this->post['tax_id']=$taxRow['tax_id'];
	$this->post['tax_name']=$taxRow['name'];
	$this->post['tax_rate']=$taxRow['rate'];
	$this->post['tax_status']=$taxRow['status'];
}
$formfields=array();
$formfields[]='<div class="form-group">
				<label for="" class="control-label col-md-2">TAX name</label>
				<div class="col-md-10">
				<input class="form-control" type="text" name="tax_name" id="tax_name" value="'.$this->post['tax_name'].'"> (name that will be displayed on the invoice. Example: VAT)
				</div>
		</div>';
$formfields[]='<div class="form-group">
				<label for="" class="control-label col-md-2">TAX rate</label>
				<div class="col-md-10">
				<input class="form-control" type="text" name="tax_rate" id="tax_rate" value="'.$this->post['tax_rate'].'"> (example: 19 for 19%)
				</div>
		</div>';
$formfields[]='<div class="form-group">
				<label for="" class="control-label col-md-2">Status</label>
				<div class="col-md-10">
				<div class="radio-inline">
				<input name="tax_status" type="radio" value="1" '.((!isset($this->post['tax_status']) or $this->post['tax_status']==1) ? 'checked' : '').' /> on
				</div>
				<div class="radio-inline">
				<input name="tax_status" type="radio" value="0" '.((isset($this->post['tax_status']) and $this->post['tax_status']==0) ? 'checked' : '').' /> off
				</div>
				</div>
		</div>';
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['renderInsertEditTaxesFormPreProc'])) {
	$params=array(
		'formfields'=>&$formfields,
		'taxRow'=>&$taxRow
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_taxes.php']['renderInsertEditTaxesFormPreProc'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
$content.='
<div class="panel panel-default">
<div class="panel-heading"><h3>ADD / UPDATE TAX</h3></div>
<div class="panel-body">
<form action="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page']).'" method="post" class="form-horizontal">
	<fieldset>
		'.implode('', $formfields).'
		<div class="form-group">
				<label for="" class="control-label col-md-2">&nbsp;</label>
				<div class="col-md-10">
				<input name="tax_id" type="hidden" value="'.$this->post['tax_id'].'" />
				<button name="Submit" type="submit" value="" class="btn btn-success"><i class="fa fa-save"></i> '.$this->pi_getLL('save').'</button>
				</div>
		</div>
	</fieldset>
</form>
<br>
';
// load taxes
$str="SELECT * from tx_multishop_taxes order by tax_id";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
$taxes=array();
while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
	$taxes[]=$row;
}
if (count($taxes)) {
	$content.='<table class="table table-striped table-bordered msadmin_border" id="admin_modules_listing">
	<thead><tr>
		<th class="cellID">ID</th>
		<th class="cellName">Name</th>
		<th class="cellTax">Rate</th>
		<th class="cellStatus">Status</th>
		<th class="cellAction">Action</th>
	</tr></thead>
	';
	foreach ($taxes as $tax) {
		$content.='
		<tr class="'.$tr_type.'">
			<td class="cellID">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tax_id='.$tax['tax_id'].'&edit=1').'">'.$tax['tax_id'].'</a>
			</td>
			<td class="cellName">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tax_id='.$tax['tax_id'].'&edit=1').'">'.$tax['name'].'</a>
			</td>
			<td class="cellTax">
				'.round($tax['rate'], 4).'%
			</td>
			<td class="cellStatus">
				'.$tax['status'].'
			</td>			
			<td class="cellAction">
				<a href="'.mslib_fe::typolink(',2003', '&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tax_id='.$tax['tax_id'].'&delete=1').'" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></a>
			</td>
		</tr>
		';
	}
	$content.='</table>';
}
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></div></div></div>';
$content=''.mslib_fe::shadowBox($content).'';
?>