<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
t3lib_div::loadTCA('tt_content');
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages";
t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/rootpage', 'MultiShop Root Page Setup');
t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/corepage', 'MultiShop Core Page Setup');
t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/tmenu', 'MultiShop Tmenu Setup');
//t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/custom_css','MultiShop Custom CSS Setup');
//t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/ajax','MultiShop Ajax Setup');
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
t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("fe_users", '--div--; Multishop, tx_multishop_discount;;;;1-1-1');
// EXTENDING ADDRESS WITH ADDRESS_NUMBER AND COMBINE THEM IN ONE NEW PALETTE CALLED "MULTISHOPADDRESS"
$TCA['fe_users']['palettes']['multishopaddress']=array(
	'showitem'=>'address,street_name,address_number,address_ext'
);
t3lib_extMgm::addToAllTCAtypes('fe_users', '--palette--;Address;multishopaddress', '', 'replace:address');
// ADDING MOBILE NUMBER AFTER TELEPHONE
t3lib_extMgm::addToAllTCAtypes('fe_users', 'mobile', '', 'after:telephone');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'gender', '', 'after:address');
// FE_USERS EOF
// PREPARE $TEMPCOLUMNS FOR FE_GROUPS
unset($tempColumns['mobile']);
unset($tempColumns['gender']);
unset($tempColumns['street_name']);
unset($tempColumns['address_number']);
unset($tempColumns['address_ext']);
t3lib_div::loadTCA("fe_groups");
t3lib_extMgm::addTCAcolumns("fe_groups", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("fe_groups", '--div--; Multishop, tx_multishop_discount;;;;1-1-1');
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
			"default"=>0
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
			"default"=>0
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
t3lib_div::loadTCA("tt_address");
t3lib_extMgm::addTCAcolumns("tt_address", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("tt_address", '--div--; Multishop, tx_multishop_discount;;;;1-1-1');
// EXTENDING ADDRESS WITH ADDRESS_NUMBER AND COMBINE THEM IN ONE NEW PALETTE CALLED "MULTISHOPADDRESS"
$TCA['tt_address']['palettes']['multishopaddress']=array(
	'showitem'=>'address,street_name,address_number,address_ext,tx_multishop_address_type'
);
t3lib_extMgm::addToAllTCAtypes('tt_address', '--palette--;Address;multishopaddress', '', 'replace:address');
// TT ADDRESS EOF
// ADD CUSTOM PAGE TYPE
t3lib_div::loadTCA('pages');
$TCA['pages']['columns']['module']['config']['items'][] = array('Multishop: core shop', 'mscore', t3lib_extMgm::extRelPath($_EXTKEY) . 'mod1/images/mscore_icon.gif');
t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-mscore', t3lib_extMgm::extRelPath($_EXTKEY) . 'mod1/images/mscore_icon.gif');
$ICON_TYPES['mscore']['icon'] = t3lib_extMgm::extRelPath($_EXTKEY) . 'mod1/images/mscore_icon.gif';
// ADD CUSTOM PAGE TYPE EOF



t3lib_extMgm::addPlugin(array(
	'LLL:EXT:multishop/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY.'_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif'
), 'list_type');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds.xml');
if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_multishop_pi1_wizicon']=t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_multishop_pi1_wizicon.php';
	t3lib_extMgm::addModulePath('web_txmultishopM1', t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	t3lib_extMgm::addModule('web', 'txmultishopM1', '', t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}
include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_multishop_addMiscFieldsToFlexForm.php');
?>