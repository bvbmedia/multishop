<?php
########################################################################
# Extension Manager/Repository config file for ext "multishop".
#
# Auto generated 02-04-2012 13:21
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################
$EM_CONF[$_EXTKEY] = array(
        'title' => 'Multishop',
        'description' => 'TYPO3 Multishop is an E-Commerce plugin for the TYPO3 CMS which supports front-end editing and multiple web shops within the same pagetree.',
        'category' => 'plugin',
        'shy' => '',
        'dependencies' => 'static_info_tables,tt_address',
        'conflicts' => '',
        'priority' => '',
        'module' => '',
        'state' => 'stable',
        'internal' => '',
        'uploadfolder' => 1,
        'createDirs' => '',
        'modify_tables' => 'tx_multishop_products_flat,fe_users,fe_groups,tt_address',
        'clearCacheOnLoad' => 0,
        'lockType' => '',
        'author' => 'Bas van Beek (BVB Media)',
        'author_email' => 'bvbmedia@gmail.com',
        'author_company' => '<a href="http://www.bvbmedia.com/?utm_source=Typo3&utm_medium=cpc&utm_term=multishop+module&utm_content=Listing&utm_campaign=Typo3" target="_blank">BVB Media</a>',
        'version' => '5.1.101',
        'constraints' => array(
                'depends' => array(
                        'php' => '5.3.15-5.6.99',
                        'typo3' => '6.2.5-7.9.99',
                ),
                'conflicts' => array(
                        'dbal' => '0.0.0'
                ),
                'suggests' => array(
                        't3jquery' => '2.7.1-3.9.99',
                        'rzcolorbox_jquery2' => '1.0.5-1.9.99',
                        'phpexcel_service' => '1.7.6-1.8.99',
                        'tt_address' => '2.3.5-3.2.99',
                        'static_info_tables' => '6.2.1-6.4.99',
                )
        ),
        '_md5_values_when_last_written' => ''
);
