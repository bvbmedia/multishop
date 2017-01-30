<?php
if ($this->get['tx_multishop_pi1']['action']) {
	switch($this->get['tx_multishop_pi1']['action']) {
		case 'delete':
			if ($this->get['tx_multishop_pi1']['dashboard_portled_id']) {
                $query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_dashboard_portlets', 'id='.addslashes($this->get['tx_multishop_pi1']['dashboard_portled_id']));
                $res=$GLOBALS['TYPO3_DB']->sql_query($query);
			}
			break;
        case 'enable':
        case 'disable':
            if ($this->get['tx_multishop_pi1']['dashboard_portled_id']) {
                $updateArray=array();
                $updateArray['status']=$this->get['tx_multishop_pi1']['action']=='enable' ? 1 : 0;
                $query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_dashboard_portlets', 'id='.addslashes($this->get['tx_multishop_pi1']['dashboard_portled_id']), $updateArray);
                $res=$GLOBALS['TYPO3_DB']->sql_query($query);
                header("Location: ".$this->FULL_HTTP_URL.mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_widget', 1));
                exit();
            }
            break;
	}
}
$conf=array();
// TITLE PRINTED ON THE TAB
$conf['title']=$this->pi_getLL('overview');
// DEFINE COLUMNS TO BE PRINTED IN THE TABLE
$conf['tableColumns']=array();
$conf['tableColumns']['dashboard']=array(
    'title'=>$this->pi_getLL('admin_label_dashboard'),
    'align'=>'left',
    'nowrap'=>0
);
$conf['tableColumns']['header_title']=array(
	'title'=>$this->pi_getLL('title'),
	'align'=>'left',
	'nowrap'=>0
);
$conf['tableColumns']['widget_key']=array(
    'title'=>$this->pi_getLL('admin_label_widget_key'),
    'align'=>'left',
    'nowrap'=>0
);
$conf['tableColumns']['colpos']=array(
    'title'=>$this->pi_getLL('admin_label_colpos'),
    'align'=>'left',
    'nowrap'=>0
);
$conf['tableColumns']['sort_order']=array(
        'title'=>$this->pi_getLL('admin_label_sort_order'),
        'align'=>'left',
        'nowrap'=>0
);
$conf['tableColumns']['crdate']=array(
    'title'=>$this->pi_getLL('creation_date'),
    'align'=>'right',
    'nowrap'=>1,
    'valueType'=>'timestamp_to_date'
);
$conf['tableColumns']['status']=array(
    'title'=>$this->pi_getLL('status'),
    'align'=>'center',
    'nowrap'=>1,
    'valueType'=>'booleanToggle',
    'hrefEnable'=>mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_widget&tx_multishop_pi1[action]=enable&tx_multishop_pi1[dashboard_portled_id]=###id###'),
    'hrefDisable'=>mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_widget&tx_multishop_pi1[action]=disable&tx_multishop_pi1[dashboard_portled_id]=###id###')
);
$conf['tableColumns']['custom_buttons']=array(
	'title'=>'',
	'align'=>'center',
	'nowrap'=>0,
	'content'=>'<a href="'.mslib_fe::typolink($this->shop_pid.',2003', mslib_fe::tep_get_all_get_params(array(
        'Submit',
        'tx_multishop_pi1[action]',
        'clearcache'
    )).'&tx_multishop_pi1[page_section]=admin_dashboards_widget&tx_multishop_pi1[dashboard_portled_id]=###id###&tx_multishop_pi1[action]=delete').'" class="btn btn-danger btn-sm"><i class="fa fa-remove"></i> Delete</a>
	<a href="index.php?id='.$this->shop_pid.'&type=2003&tx_multishop_pi1[page_section]=admin_dashboards_widget&tx_multishop_pi1[action]=edit&tx_multishop_pi1[dashboard_portled_id]=###id###" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Edit</a>',
	'valueType'=>'content'
);
// DEFINE QUERY ELEMENTS
$conf['query']['select']=array();
$conf['query']['select'][]='dp.*, d.header_title as dashboard';
$conf['query']['where'][]='dp.dashboard_id=d.id';
if (isset($this->get['tx_multishop_pi1']['dashboard_id']) && is_numeric($this->get['tx_multishop_pi1']['dashboard_id']) && $this->get['tx_multishop_pi1']['dashboard_id']>0) {
    $conf['query']['where'][]='dp.dashboard_id=' . $this->get['tx_multishop_pi1']['dashboard_id'];
}
$conf['query']['from']='tx_multishop_dashboard d, tx_multishop_dashboard_portlets dp';
$conf['query']['defaultOrderByColumns'][]='dp.colpos';
$conf['query']['defaultOrderByColumns'][]='dp.sort_order';
// ASC OR DESC
$conf['query']['defaultOrder']='asc';
$conf['settings']['skipTabMarkup']='1';
$conf['settings']['disableForm']='1';
// Heading buttons
$conf['settings']['headingButtons']=array();
$headingButton=array();
$headingButton['btn_class']='btn btn-primary';
$headingButton['fa_class']='fa fa-plus';
$headingButton['title']=$this->pi_getLL('add');
$headingButton['href']=mslib_fe::typolink($ref->shop_pid.',2003', '&tx_multishop_pi1[page_section]=admin_dashboards_widget&tx_multishop_pi1[action]=edit');
$conf['settings']['headingButtons'][]=$headingButton;

// HIDDEN FIELDS USED ON SEARCHFORM
$conf['searchForm']['hiddenFields']['id']=$this->shop_pid;
$conf['searchForm']['hiddenFields']['do_search']=1;
$conf['searchForm']['hiddenFields']['type']=2003;
$conf['searchForm']['hiddenFields']['tx_multishop_pi1[page_section]']='admin_dashboards_widget';
// ACTION URL ON THE POSTFORM
$conf['postForm']['actionUrl']=mslib_fe::typolink($this->shop_pid.',2003', 'tx_multishop_pi1[page_section]=admin_dashboards_widget');
// GRAND TOTALS PRINTED BELOW THE TABLE
$conf['summarizeData']['totalRecordsInTable']=number_format(mslib_befe::getCount('', 'tx_multishop_dashboard_portlets', ''), 0, '', '.');
$content=\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction('EXT:multishop/pi1/classes/class.tx_mslib_admin_interface.php:&tx_mslib_admin_interface->renderInterface', $conf, $this);

?>