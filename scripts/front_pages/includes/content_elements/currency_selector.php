<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$this->box_class="multishop_currency_selector";
$this->cObj->data['header']=$this->pi_getLL('currency','currency selector');
if (!$this->ms['MODULES']['ENABLED_CURRENCIES']) $this->ms['MODULES']['ENABLED_CURRENCIES']='USD,EUR,GBP,DKK,UAH';
if ($this->ms['MODULES']['ENABLED_CURRENCIES'])
{
	$currencies=explode(",",$this->ms['MODULES']['ENABLED_CURRENCIES']);
	$items=array();
	$content.='
	<form name="multishop_currency_selector" action="index.php" method="get" id="multishop_currency_selector_form">
	<input name="id" type="hidden" value="'.$this->shop_pid.'" />
	<select name="tx_multishop_pi1[selected_currency]" id="multishop_currency_selector">'."\n";
	foreach ($currencies as $currency)
	{
		$currency=trim($currency);
		if ($currency)
		{
			$query = $GLOBALS['TYPO3_DB']->SELECTquery(
				'cu_iso_3,cu_name_en',         // SELECT ...
				'static_currencies',     // FROM ...
				'cu_iso_3=\''.addslashes($currency).'\'',    // WHERE...
				'',            // GROUP BY...
				'',    // ORDER BY...
				''            // LIMIT ...
			);	
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$items[$row['cu_iso_3']]='<option value="'.$row['cu_iso_3'].'"'.($this->cookie['selected_currency']==$row['cu_iso_3']?' selected':'').'>'.htmlspecialchars($row['cu_name_en']).'</option>'."\n";
		}
	}
	$content.=implode($items);
	$content.='
	</select>
	</form>
	';
	$GLOBALS['TSFE']->additionalHeaderData[]='
	<script>
		jQuery(document).ready(function($)
		{	
			$(\'#multishop_currency_selector\').change(function(){
				var selected_currency=$("#multishop_currency_selector option:selected").val();
				if (selected_currency)
				{
					$.ajax({ 
							type:   "POST", 
							url:    "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=update_currency').'",
							data:   "tx_multishop_pi1[selected_currency]="+selected_currency, 
							success: function(msg) { 	
								parent.window.location.reload();
							}
					});
				}
			});
		});
	</script>	
	';	
}
?>