<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
// \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('tt_content');
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages";
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'pi1/static/rootpage', 'MultiShop Root Page Setup');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'pi1/static/corepage', 'MultiShop Core Page Setup');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'pi1/static/tmenu', 'MultiShop Tmenu Setup');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'pi1/static/custom_css','MultiShop Custom CSS Setup');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'pi1/static/ajax','MultiShop Ajax Setup');
// EXTEND FE_USERS TABLE
$tempColumns=array(
	"tx_multishop_discount"=>array(
		"exclude"=>1,
		"label"=>"Discount percentage:",
		"config"=>array(
			"type"=>"input",
			"size"=>"2",
			"max"=>"2",
			"eval"=>"int",
			"checkbox"=>"0",
			"range"=>array(
				"upper"=>"100",
				"lower"=>"0"
			),
			"default"=>0
		)
	),
	"street_name"=>array(
		"exclude"=>1,
		"label"=>"Street name:",
		"config"=>array(
			"type"=>"input",
			"size"=>"25",
			"max"=>"75",
			"checkbox"=>"0",
			"default"=>""
		)
	),
	"address_number"=>array(
		"exclude"=>1,
		"label"=>"Number:",
		"config"=>array(
			"type"=>"input",
			"size"=>"10",
			"max"=>"20",
			"checkbox"=>"0",
			"default"=>""
		)
	),
	"address_ext"=>array(
		"exclude"=>1,
		"label"=>"Number extension:",
		"config"=>array(
			"type"=>"input",
			"size"=>"5",
			"max"=>"5",
			"checkbox"=>"0",
			"default"=>""
		)
	),
	'mobile'=>array(
		'exclude'=>1,
		'label'=>'Mobile:',
		'config'=>array(
			'type'=>'input',
			'eval'=>'trim',
			'size'=>'20',
			'max'=>'20'
		)
	),
	'page_uid'=>array(
		'exclude'=>1,
		'label'=>'Core shop pid:',
		'config'=>array(
			'type'=>'input',
			'eval'=>'trim',
			'size'=>'20',
			'max'=>'20'
		)
	),
	'gender'=>array(
		'exclude'=>1,
		'label'=>'Gender:',
		'config'=>array(
			'type'=>'select',
			'items'=>array(
				array(
					'Male',
					0
				),
				array(
					'Female',
					1
				)
			)
		)
	)
);
// \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA("fe_users");
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("fe_users", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("fe_users", '--div--; Multishop, tx_multishop_discount, page_uid;;;;1-1-1');
// EXTENDING ADDRESS WITH ADDRESS_NUMBER AND COMBINE THEM IN ONE NEW PALETTE CALLED "MULTISHOPADDRESS"
$TCA['fe_users']['palettes']['multishopaddress']=array(
	'showitem'=>'address,street_name,address_number,address_ext'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--palette--;Address;multishopaddress', '', 'replace:address');
// ADDING MOBILE NUMBER AFTER TELEPHONE
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobile', '', 'after:telephone');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender', '', 'after:address');
// FE_USERS EOF
// PREPARE $TEMPCOLUMNS FOR FE_GROUPS
unset($tempColumns['page_uid']);
unset($tempColumns['mobile']);
unset($tempColumns['gender']);
unset($tempColumns['street_name']);
unset($tempColumns['address_number']);
unset($tempColumns['address_ext']);
// \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA("fe_groups");
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("fe_groups", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("fe_groups", '--div--; Multishop, tx_multishop_discount;;;;1-1-1');
// EXTEND TT_ADDRESS TABLE
$tempColumns=array(
	"street_name"=>array(
		"exclude"=>1,
		"label"=>"Street name:",
		"config"=>array(
			"type"=>"input",
			"size"=>"25",
			"max"=>"75",
			"checkbox"=>"0",
			"default"=>''
		)
	),
	"address_number"=>array(
		"exclude"=>1,
		"label"=>"Number:",
		"config"=>array(
			"type"=>"input",
			"size"=>"10",
			"max"=>"20",
			"checkbox"=>"0",
			"default"=>''
		)
	),
	"address_ext"=>array(
		"exclude"=>1,
		"label"=>"Number extension:",
		"config"=>array(
			"type"=>"input",
			"size"=>"5",
			"max"=>"5",
			"checkbox"=>"0",
			"default"=>''
		)
	),
	"tx_multishop_customer_id"=>array(
		"exclude"=>1,
		"label"=>"Multishop customer id:",
		"config"=>array(
			"type"=>"input",
			"size"=>"5",
			"max"=>"11",
			"checkbox"=>"0",
			"default"=>0
		)
	),
	"tx_multishop_address_type"=>array(
		'exclude'=>1,
		'label'=>'Multishop address type:',
		'config'=>array(
			'type'=>'select',
			'items'=>array(
				array(
					'Default',
					''
				),
				array(
					'Store address',
					'store'
				),
				array(
					'Billing address',
					'billing'
				),
				array(
					'Delivery address',
					'delivery'
				),
			)
		)
	),
);
// \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA("tt_address");
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("tt_address", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("tt_address", '--div--; Multishop, tx_multishop_address_type, tx_multishop_customer_id;;;;1-1-1');
// EXTENDING ADDRESS WITH ADDRESS_NUMBER AND COMBINE THEM IN ONE NEW PALETTE CALLED "MULTISHOPADDRESS"
$TCA['tt_address']['palettes']['multishopaddress']=array(
	'showitem'=>'address,street_name,address_number,address_ext'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', '--palette--;Address;multishopaddress', '', 'replace:address');
// TT ADDRESS EOF
// ADD CUSTOM PAGE TYPE
// \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('pages');
$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][]=array(
	'Multishop: core shop',
	'mscore',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'mod1/images/mscore_icon.gif'
);
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-mscore', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'mod1/images/mscore_icon.gif');
// ADD CUSTOM PAGE TYPE EOF
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:multishop/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY.'_pi1',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'ext_icon.gif'
), 'list_type');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds.xml');
if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_multishop_pi1_wizicon']=\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'pi1/class.tx_multishop_pi1_wizicon.php';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath('web_txmultishopM1', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod1/');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'txmultishopM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod1/');
}
include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'class.tx_multishop_addMiscFieldsToFlexForm.php');
?>