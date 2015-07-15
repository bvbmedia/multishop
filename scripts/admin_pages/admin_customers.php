<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$this->cObj->data['header']='Customers';
if ($GLOBALS['TSFE']->fe_user->user['uid'] and $this->get['login_as_customer'] && is_numeric($this->get['customer_id'])) {
	$user=mslib_fe::getUser($this->get['customer_id']);
	if ($user['uid']) {
		mslib_befe::loginAsUser($user['uid'], 'admin_customers');
	}
}
if ($this->post && isset($this->post['tx_multishop_pi1']['action']) && !empty($this->post['tx_multishop_pi1']['action'])) {
	switch ($this->post['tx_multishop_pi1']['action']) {
		case 'delete_selected_customers':
			if (is_array($this->post['selected_customers']) and count($this->post['selected_customers'])) {
				foreach ($this->post['selected_customers'] as $customer_id) {
					if (is_numeric($customer_id)) {
						mslib_befe::deleteCustomer($customer_id);
					}
				}
			}
			break;
		default:
			// post processing by third party plugins
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customers.php']['adminCustomersPostHookProc'])) {
				$params=array();
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customers.php']['adminCustomersPostHookProc'] as $funcRef) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
				}
			}
			break;
	}
	header('Location: '.$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_customers'));
	exit();
}
if (is_numeric($this->get['disable']) and is_numeric($this->get['customer_id'])) {
	if ($this->get['disable']) {
		mslib_befe::disableCustomer($this->get['customer_id']);
	} else {
		mslib_befe::enableCustomer($this->get['customer_id']);
	}
} else {
	if (is_numeric($this->get['delete']) and is_numeric($this->get['customer_id'])) {
		mslib_befe::deleteCustomer($this->get['customer_id']);
	}
}
$this->hideHeader=1;
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
$this->searchKeywords=array();
if ($this->get['tx_multishop_pi1']['searchByChar']) {
	switch ($this->get['tx_multishop_pi1']['searchByChar']) {
		case '0-9':
			for ($i=0; $i<10; $i++) {
				$this->searchKeywords[]=$i;
			}
			break;
		case '#':
			$this->searchKeywords[]='#';
			break;
		case 'all':
			break;
		default:
			$this->searchKeywords[]=$this->get['tx_multishop_pi1']['searchByChar'];
			break;
	}
	$this->searchMode='keyword%';
} elseif ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword']=trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword']=$GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'], true);
	$this->get['tx_multishop_pi1']['keyword']=mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}
if (is_numeric($this->get['p'])) {
	$p=$this->get['p'];
}
if ($p>0) {
	$offset=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$offset=0;
}
$user=$GLOBALS['TSFE']->fe_user->user;
$option_search=array(
	"f.company"=>$this->pi_getLL('admin_company'),
	"f.name"=>$this->pi_getLL('admin_customer_name'),
	"f.username"=>$this->pi_getLL('username'),
	"f.email"=>$this->pi_getLL('admin_customer_email'),
	"f.uid"=>$this->pi_getLL('admin_customer_id'),
	"f.city"=>$this->pi_getLL('admin_city'),
	//"f.country"=>ucfirst(strtolower($this->pi_getLL('admin_countries'))),
	"f.zip"=>$this->pi_getLL('admin_zip'),
	"f.telephone"=>$this->pi_getLL('telephone')
);
asort($option_search);
$option_item='';
foreach ($option_search as $key=>$val) {
	$option_item.='<option value="'.$key.'" '.($this->get['tx_multishop_pi1']['search_by']==$key ? "selected" : "").'>'.$val.'</option>';
}
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
$customer_groups_input='';
if (is_array($groups) and count($groups)) {
	$customer_groups_input.='<select id="groups" class="invoice_select2" name="usergroup">'."\n";
	$customer_groups_input.='<option value="0">'.$this->pi_getLL('all').' '.$this->pi_getLL('usergroup').'</option>'."\n";
	foreach ($groups as $group) {
		$customer_groups_input.='<option value="'.$group['uid'].'"'.($this->get['usergroup']==$group['uid'] ? ' selected="selected"' : '').'>'.$group['title'].'</option>'."\n";
	}
	$customer_groups_input.='</select>'."\n";
}
$searchCharNav='<div id="msAdminSearchByCharNav"><ul class="pagination">';
$chars=array();
$chars=array(
	'0-9',
	'a',
	'b',
	'c',
	'd',
	'e',
	'f',
	'g',
	'h',
	'i',
	'j',
	'k',
	'l',
	'm',
	'n',
	'o',
	'p',
	'q',
	'r',
	's',
	't',
	'u',
	'v',
	'w',
	'x',
	'y',
	'z',
	'#',
	'all'
);
foreach ($chars as $char) {
	$searchCharNav.='<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[searchByChar]='.$char.'&tx_multishop_pi1[page_section]=admin_customers').'">'.mslib_befe::strtoupper($char).'</a></li>';
}
$searchCharNav.='</ul></div>';
$user_countries=mslib_befe::getRecords('', 'fe_users f', '', array(), 'f.country', 'f.country asc');
$fe_user_country=array();
foreach ($user_countries as $user_country) {
	if (!empty($user_country['country'])) {
		$cn_localized_name=htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang, $user_country['country']));
		$fe_user_country[$cn_localized_name]=$fe_user_countries[]='<option value="'.mslib_befe::strtolower($user_country['country']).'" '.((mslib_befe::strtolower($this->get['country'])==mslib_befe::strtolower($user_country['country'])) ? 'selected' : '').'>'.$cn_localized_name.'</option>';
	}
}
ksort($fe_user_country);
$user_countries_sb='<select class="invoice_select2" name="country" id="country""><option value="">'.$this->pi_getLL('all').' '.$this->pi_getLL('countries').'</option>'.implode("\n", $fe_user_country).'</select>';
$formTopSearch='
<div id="search-orders" class="well">
	<div class="row formfield-container-wrapper">
		<input name="tx_multishop_pi1[do_search]" type="hidden" value="1" />
		<input name="id" type="hidden" value="'.$this->shop_pid.'" />
		<input name="type" type="hidden" value="2003" />
		<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_customers" />
		<div class="col-sm-4 formfield-wrapper">
			<div class="form-group">
				<label class="control-label">'.ucfirst($this->pi_getLL('keyword')).'</label>
				<input type="text" name="tx_multishop_pi1[keyword]" id="skeyword" class="form-control customers_skeyword" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['keyword']).'" />
			</div>
			<div class="form-group">
				<label class="control-label" for="type_search">'.$this->pi_getLL('search_for').'</label>
				<div class="form-inline">
					<select class="invoice_select2" name="tx_multishop_pi1[search_by]">
						<option value="all">'.$this->pi_getLL('all').'</option>
						'.$option_item.'
					</select>
					<p for="groups" class="help-block labelInbetween">'.$this->pi_getLL('usergroup').'</p>
					'.$customer_groups_input.'
				</div>
			</div>
		</div>
		<div class="col-sm-4 formfield-wrapper">
			<div class="form-group">
				<label class="control-label" for="order_date_from">'.$this->pi_getLL('from').':</label>
				<input class="form-control" type="text" name="crdate_from" id="crdate_from" value="'.$this->get['crdate_from'].'">
			</div>
			<div class="form-group">
				<label for="order_date_till" class="labelInbetween">'.$this->pi_getLL('to').':</label>
				<input class="form-control" type="text" name="crdate_till" id="crdate_till" value="'.$this->get['crdate_till'].'">
			</div>
			<div class="form-group">
				<label for="includeDeletedAccounts">'.$this->pi_getLL('show_deleted_accounts').'</label>
				<input type="checkbox" class="PrettyInput" id="includeDeletedAccounts" name="tx_multishop_pi1[show_deleted_accounts]" value="1"'.($this->get['tx_multishop_pi1']['show_deleted_accounts'] ? ' checked="checked"' : '').' />
			</div>
		</div>
		<div class="col-sm-4 formfield-wrapper">
			<div class="form-group">
				<label class="control-label" for="country">'.$this->pi_getLL('countries').'</label>
				'.$user_countries_sb.'
			</div>
			<div class="form-group">
				<label class="control-label" for="limit">'.$this->pi_getLL('limit_number_of_records_to').':</label>
				<select name="limit" id="limit" class="form-control">';
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
		</div>
	</div>
	<div class="row formfield-container-wrapper">
		<div class="col-sm-12 formfield-wrapper">
			<div class="pull-right">
			<input type="submit" name="Search" class="btn btn-success" value="'.$this->pi_getLL('search').'" />
			</div>
		</div>
	</div>
</div>
'.$searchCharNav.'
';
$filter=array();
$having=array();
$match=array();
$orderby=array();
$where=array();
$orderby=array();
$select=array();
if (strlen($this->get['tx_multishop_pi1']['keyword'])>0) {
	switch ($this->get['tx_multishop_pi1']['search_by']) {
		case 'f.uid':
			$filter[]="f.uid like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		case 'f.company':
			$filter[]="f.company like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		case 'f.name':
			$filter[]="f.name like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		case 'f.email':
			$filter[]="f.email like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		case 'f.username':
			$filter[]="f.username like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		case 'f.city':
			$filter[]="f.city like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		/*case 'f.country':
			$filter[]="f.country like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;*/
		case 'f.zip':
			$filter[]="f.zip like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		case 'f.telephone':
			$filter[]="f.telephone like '".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			break;
		default:
			$option_fields=$option_search;
			$items=array();
			foreach ($option_fields as $fields=>$label) {
				$items[]=$fields." LIKE '%".addslashes($this->get['tx_multishop_pi1']['keyword'])."%'";
			}
			$filter[]='('.implode(" or ", $items).')';
			break;
	}
} else {
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
				$keywordOr[]="f.company like '".$this->sqlKeyword."'";
				$keywordOr[]="f.name like '".$this->sqlKeyword."'";
				$keywordOr[]="f.email like '".$this->sqlKeyword."'";
				$keywordOr[]="f.username like '".$this->sqlKeyword."'";
				$keywordOr[]="f.city like '".$this->sqlKeyword."'";
				//$keywordOr[]="f.country like '".$this->sqlKeyword."'";
				$keywordOr[]="f.zip like '".$this->sqlKeyword."'";
				$keywordOr[]="f.telephone like '".$this->sqlKeyword."'";
			}
		}
		$filter[]="(".implode(" OR ", $keywordOr).")";
	}
}
switch ($this->get['tx_multishop_pi1']['order_by']) {
	case 'username':
		$order_by='f.username';
		break;
	case 'company':
		$order_by='f.company';
		break;
	case 'crdate':
		$order_by='f.crdate';
		break;
	case 'lastlogin':
		$order_by='f.lastlogin';
		break;
	case 'grand_total':
		$order_by='grand_total';
		break;
	case 'grand_total_this_year':
		$order_by='grand_total_this_year';
		break;
	case 'disable':
		$order_by='f.disable';
		break;
	case 'uid':
	default:
		$order_by='f.uid';
		break;
}
switch ($this->get['tx_multishop_pi1']['order']) {
	case 'a':
		$order='asc';
		$order_link='d';
		break;
	case 'd':
	default:
		$order='desc';
		$order_link='a';
		break;
}
$orderby[]=$order_by.' '.$order;
if (!$this->get['tx_multishop_pi1']['show_deleted_accounts']) {
	$filter[]='(f.deleted=0)';
}
if (!$this->masterShop) {
	$filter[]="f.page_uid='".$this->shop_pid."'";
}
if (!empty($this->get['crdate_from']) && !empty($this->get['crdate_till'])) {
	list($from_date, $from_time)=explode(" ", $this->get['crdate_from']);
	list($fd, $fm, $fy)=explode('/', $from_date);
	list($till_date, $till_time)=explode(" ", $this->get['crdate_till']);
	list($td, $tm, $ty)=explode('/', $till_date);
	$start_time=strtotime($fy.'-'.$fm.'-'.$fd.' '.$from_time);
	$end_time=strtotime($ty.'-'.$tm.'-'.$td.' '.$till_time);
	$column='f.crdate';
	$filter[]=$column." BETWEEN '".$start_time."' and '".$end_time."'";
}
if (isset($this->get['usergroup']) && $this->get['usergroup']>0) {
	$filter[]=$GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->get['usergroup'], 'fe_users');
}
if (isset($this->get['country']) && !empty($this->get['country'])) {
	$filter[]="f.country='".$this->get['country']."'";
}
if (!$this->masterShop) {
	$filter[]=$GLOBALS['TYPO3_DB']->listQuery('usergroup', $this->conf['fe_customer_usergroup'], 'fe_users');
}
// subquery to summarize grand total per customer
$select[]='(select sum(grand_total) from tx_multishop_orders where customer_id=f.uid) as grand_total';
// subquery to summarize grand total by year, per customer
$startTime=strtotime(date("Y-01-01 00:00:00"));
$endTime=strtotime(date("Y-12-31 23:59:59"));
$select[]='(select sum(grand_total) from tx_multishop_orders where customer_id=f.uid and crdate BETWEEN '.$startTime.' and '.$endTime.') as grand_total_this_year';
$pageset=mslib_fe::getCustomersPageSet($filter, $offset, $this->ms['MODULES']['PAGESET_LIMIT'], $orderby, $having, $select, $where);
$customers=$pageset['customers'];
if ($pageset['total_rows']>0 && isset($pageset['customers'])) {
	require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop').'scripts/admin_pages/includes/admin_customers_listing.php');
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
$tabs['CustomersListing']=array(
	$this->pi_getLL('customers'),
	$tmp
);
$tmp='';
$extra_selected_customers_action_js_filters='';
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customers.php']['adminCustomersExtraJSForSelectedActions'])) {
	$params=array('extra_selected_customers_action_js_filters'=>$extra_selected_customers_action_js_filters);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customers.php']['adminCustomersExtraJSForSelectedActions'] as $funcRef) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
	}
}
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
    jQuery(\'#crdate_from\').datetimepicker({
    	dateFormat: \'dd/mm/yy\',
        showSecond: true,
		timeFormat: \'HH:mm:ss\'
    });
	jQuery(\'#crdate_till\').datetimepicker({
    	dateFormat: \'dd/mm/yy\',
        showSecond: true,
		timeFormat: \'HH:mm:ss\'
    });
	jQuery(\'#check_all_1\').click(function() {
		checkAllPrettyCheckboxes(this,jQuery(\'.msadmin_orders_listing\'));
	});
	/*$(".tooltip").tooltip({
		position: "down",
		placement: \'auto\',
		html: true
	});
	var tooltip_is_shown=\'\';
	$(\'.tooltip\').on(\'show.bs.tooltip\', function () {
		var customer_id=$(this).attr(\'rel\');
		var that=$(this);
		if (tooltip_is_shown != customer_id) {
			tooltip_is_shown=customer_id;
			$.ajax({
				type:   "POST",
				url:    \''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=getAdminCustomersListingDetails&').'\',
				data:   \'tx_multishop_pi1[customer_id]=\'+customer_id,
				dataType: "json",
				success: function(data) {
					that.next().html(data.html);
					that.tooltip(\'show\', {
						position: \'down\',
						placement: \'auto\',
						html: true
					});
				}
			});
		}
	});*/
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
	$(".popover-link").popover({
		position: "down",
		placement: \'bottom\',
		html: true,
		trigger:"hover",
		delay: {show: 20, hide: 200}
	});
	var tooltip_is_shown=\'\';
	$(\'.popover-link\').on(\'show.bs.popover, mouseover\', function () {
		var customer_id=$(this).attr(\'rel\');
		var that=$(this);
		//if (tooltip_is_shown != customer_id) {
			tooltip_is_shown=customer_id;
			$.ajax({
				type:   "POST",
				url:    \''.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=getAdminCustomersListingDetails&').'\',
				data:   \'tx_multishop_pi1[customer_id]=\'+customer_id,
				dataType: "json",
				success: function(data) {
					if (data.html!="") {
						that.next().html(\'<div class="arrow"></div><h3 class="popover-title">Customers</h3><div class="popover-content">\' + data.html + \'</div>\');
						//that.next().popover("show");
						//$(that).popover(\'show\');
					} else {
						$(".popover").remove();
					}
					/*that.next().html(data.html);
					that.tooltip(\'show\', {
						position: \'down\',
						placement: \'auto\',
						html: true
					});*/
				}
			});
		//}
	});
	jQuery(document).on(\'submit\', \'#customers_listing\', function(){
		if (jQuery(\'#selected_customers_action\').val()==\'delete_selected_customers\') {
			if (confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')) {
				return true;
			}
			return false;
		}
		'.$extra_selected_customers_action_js_filters.'
	});
	$(".invoice_select2").select2();
});
</script>
';
foreach ($tabs as $key=>$value) {
	$content.='
		<h1>'.$value[0].'</h1>
		<form id="form1" name="form1" method="get" action="index.php">
		'.$formTopSearch.'
		</form>
		'.$value[1].'
	';
	break;
}
$content.='<hr><div class="clearfix"><a class="btn btn-success" href="'.mslib_fe::typolink().'"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-arrow-left fa-stack-1x"></i></span> '.mslib_befe::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></div>';
$content='<div class="panel panel-default">'.mslib_fe::shadowBox($content).'</div>';
?>