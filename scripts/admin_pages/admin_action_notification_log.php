<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$this->searchKeywords=array();
if ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword']=trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'], true);
	$this->get['tx_multishop_pi1']['keyword']=mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}
if ($this->get['Search'] and ($this->get['limit']!=$this->cookie['limit'])) {
	$this->cookie['limit']=$this->get['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
	$this->get['limit']=$this->cookie['limit'];
} else {
	$this->get['limit']=10;
}
$this->ms['MODULES']['PAGESET_LIMIT']=$this->get['limit'];
$formTopSearch='
<div id="search-orders">
	<table width="100%">
		<tr>
			<td nowrap valign="top">				
					<input name="tx_multishop_pi1[do_search]" type="hidden" value="1" />
					<input name="id" type="hidden" value="'.$this->shop_pid.'" />
					<input name="type" type="hidden" value="2003" />
					<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_action_notification_log" />
					<div class="formfield-container-wrapper">
					<div class="formfield-wrapper">
						<label>'.ucfirst($this->pi_getLL('keyword')).'</label><input type="text" name="tx_multishop_pi1[keyword]" id="skeyword" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['keyword']).'" />					
						<input type="submit" name="Search" class="btn btn-success" value="'.$this->pi_getLL('search').'" />
					</div>	
					</div>
			</td>
			<td nowrap valign="top" align="right" class="searchLimit">
				<div style="float:right;">			
					<label>'.$this->pi_getLL('limit_number_of_records_to').':</label>
					<select name="limit">';
$limits=array();
$limits[]='10';
$limits[]='15';
$limits[]='20';
$limits[]='25';
$limits[]='30';
$limits[]='40';
$limits[]='50';
$limits[]='100';
$limits[]='150';
$limits[]='200';
$limits[]='250';
$limits[]='300';
$limits[]='350';
$limits[]='400';
$limits[]='450';
$limits[]='500';
foreach ($limits as $limit) {
	$formTopSearch.='<option value="'.$limit.'"'.($limit==$this->get['limit'] ? ' selected="selected"' : '').'>'.$limit.'</option>';
}
$formTopSearch.='
					</select>
				</div>
			</td>			
		</tr>
	</table>
	'.$searchCharNav.'
</div>
';
$queryData=array();
$queryData['where']=array();
if (count($this->searchKeywords)) {
	$keywordOr=array();
	foreach ($this->searchKeywords as $searchKeyword) {
		if ($searchKeyword) {
			switch ($this->searchMode) {
				case 'keyword%':
					$this->sqlKeyword=addslashes($searchKeyword).'%';
					break;
				case '%keyword%':
				default:
					$this->sqlKeyword='%'.addslashes($searchKeyword).'%';
					break;
			}
			$keywordOr[]="n.message like '".$this->sqlKeyword."'";
			$keywordOr[]="f.name like '".$this->sqlKeyword."'";
			$keywordOr[]="f.company like '".$this->sqlKeyword."'";
		}
	}
	$queryData['where'][]="(".implode(" OR ", $keywordOr).")";
}
$queryData['select'][]='n.session_id, n.ip_address, n.title, n.message, n.message_type, n.crdate, n.customer_id, f.name, f.company, f.username';
$queryData['from'][]='tx_multishop_notification n left join fe_users f on n.customer_id=f.uid';
$queryData['order_by'][]='id desc';
$queryData['limit']=$this->ms['MODULES']['PAGESET_LIMIT'];
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p>0) {
	$queryData['offset']=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$queryData['offset']=0;
}
$pageset=mslib_fe::getRecordsPageSet($queryData);
if (!count($pageset['dataset'])) {
	$content.=$this->pi_getLL('no_records_found', 'No records found.').'.<br />';
} else {
	$tr_type='even';
	$headercol.='		
	<th width="100" nowrap>'.$this->pi_getLL('date').'</th>
	<th width="100" nowrap>'.$this->pi_getLL('title', 'Title').'</th>
	<th width="100" nowrap align="right">'.$this->pi_getLL('admin_customer_id').'</th>
	<th width="100" nowrap>'.$this->pi_getLL('admin_customer_name').'</th>
	<th width="100" nowrap align="right">'.$this->pi_getLL('ip_address').'</th>
	<th width="100" nowrap>'.$this->pi_getLL('session_id', 'Session ID').'</th>
	<th width="75" nowrap>'.$this->pi_getLL('type', 'Type').'</th>
	<th>'.$this->pi_getLL('content').'</th>
	';
	$content.='<table class="table table-striped table-bordered msadmin_orders_listing" id="product_import_table"><tr>'.$headercol.'</tr>';
	foreach ($pageset['dataset'] as $row) {
		if (!$tr_type or $tr_type=='even') {
			$tr_type='odd';
		} else {
			$tr_type='even';
		}
		$customer_edit_link='';
		if ($row['customer_id']) {
			$customer_edit_link=mslib_fe::typolink($this->shop_pid.',2003', '&tx_multishop_pi1[page_section]=edit_customer&tx_multishop_pi1[cid]='.$row['customer_id'].'&action=edit_customer', 1);
		}
		$content.='
		<tr class="'.$tr_type.'">
		<td valign="top" align="right" nowrap>'.strftime("%x %X", $row['crdate']).'</td>
		<td valign="top" nowrap>
			'.htmlspecialchars($row['title']).'
		</td>
		<td valign="top" nowrap align="right">
			'.($row['customer_id']>0 ? '<a href="'.$customer_edit_link.'">'.$row['customer_id'].'</a>' : '').'
		</td>
		<td valign="top" nowrap>
			'.($row['company'] ? '<a href="'.$customer_edit_link.'">'.htmlspecialchars($row['company']).'</a>' : htmlspecialchars($row['name'])).'
		</td>
		<td valign="top" nowrap align="right">
			'.htmlspecialchars($row['ip_address']).'
		</td>
		<td valign="top" nowrap>
			'.htmlspecialchars($row['session_id']).'
		</td>
		<td valign="top" nowrap>
			'.htmlspecialchars($row['message_type']).'
		</td>
		<td valign="top">
			'.$row['message'].'
		</td>	
		</tr>
		';
	}
	$content.='<tr>'.$headercol.'</tr></table>';
	// pagination
	if (!$this->ms['nopagenav'] and $pageset['total_rows']>$this->ms['MODULES']['PAGESET_LIMIT']) {
		require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');
		$content.=$tmp;
	}
	// pagination eof		
}
$tmp=$content;
$content='';
$tabs=array();
$tabs['actionNotificationLogListing']=array(
	htmlspecialchars($this->pi_getLL('admin_action_notification_log', 'Action notification log')),
	$tmp
);
$tmp='';
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
		jQuery(activeTab).fadeIn(0);
		return false;
	});
             		
    jQuery(\'#order_date_from\').datetimepicker({
    	dateFormat: \'dd/mm/yy\',
        showSecond: true,
		timeFormat: \'HH:mm:ss\'         		
    });
             		
	jQuery(\'#order_date_till\').datetimepicker({
    	dateFormat: \'dd/mm/yy\',
        showSecond: true,
		timeFormat: \'HH:mm:ss\'         		
    });
 
});
</script>
<div id="tab-container">
    <ul class="tabs" id="admin_orders">';
$count=0;
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
$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
$content.='<p class="extra_padding_bottom"><a class="btn btn-success" href="'.mslib_fe::typolink().'">'.$this->pi_getLL('admin_close_and_go_back_to_catalog').'</a></p>';

?>