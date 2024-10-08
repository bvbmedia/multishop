<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
// Configuration group ids
// 1 Homepage
// 2 Image Settings
// 3 Webshop Settings
// 4 Webshop Plugins
// 5 Categories Listing
// 6 Products Listing
// 7 Products Detail Page
// 8 Checkout Settings
// 9 Orders Settings
// 10 Products Stock Settings
// 11 Admin Settings
// 12 Invoice Settings
// 14 Performance Settings
$records = array();
$records[] = array(
        'configuration_title' => 'Automatically convert uploaded images to PNG format',
        'configuration_key' => 'ADMIN_AUTO_CONVERT_UPLOADED_IMAGES_TO_PNG',
        'configuration_value' => '0',
        'description' => 'Automatically convert uploaded images for products, categories, and manufaturers to PNG format',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable layered custom product description',
        'configuration_key' => 'ENABLE_LAYERED_PRODUCTS_DESCRIPTION',
        'configuration_value' => '0',
        'description' => 'different product description for each linked categories',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable categories to categories linking',
        'configuration_key' => 'ENABLE_CATEGORIES_TO_CATEGORIES',
        'configuration_value' => '0',
        'description' => 'Enable categories to categories linking',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Minimum order amount',
        'configuration_key' => 'MINIMUM_ORDER_AMOUNT',
        'configuration_value' => '',
        'description' => 'Minimum order amount',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Maximum order amount',
        'configuration_key' => 'MAXIMUM_ORDER_AMOUNT',
        'configuration_value' => '',
        'description' => 'Maximum order amound',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable the attributes values image',
        'configuration_key' => 'ENABLE_ATTRIBUTE_VALUE_IMAGES',
        'configuration_value' => '0',
        'description' => 'Enable the attributes values image',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display subscribe to newsletter in checkout',
        'configuration_key' => 'DISPLAY_SUBSCRIBE_TO_NEWSLETTER_IN_CHECKOUT',
        'configuration_value' => '1',
        'description' => 'Enable the subscribe to newsletter in checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display subscribe to newsletter in create account',
        'configuration_key' => 'DISPLAY_SUBSCRIBE_TO_NEWSLETTER_IN_CREATE_ACCOUNT',
        'configuration_value' => '1',
        'description' => 'Enable the subscribe to newsletter in create account',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Edit Customer Username Readonly',
        'configuration_key' => 'ADMIN_EDIT_CUSTOMER_USERNAME_READONLY',
        'configuration_value' => '1',
        'description' => 'Enable the readonly field for customer username in admin edit customer',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Use Flat database also in admin products search and edit',
        'configuration_key' => 'USE_FLAT_DATABASE_ALSO_IN_ADMIN_PRODUCTS_SEARCH_AND_EDIT',
        'configuration_value' => '0',
        'description' => 'When flat database is enabled, this setting will improve performance when searching on the admin products search and edit page.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Multishop Encryption key',
        'configuration_key' => 'MULTISHOP_ENCRYPTION_KEY',
        'configuration_value' => md5(uniqid('', true)),
        'description' => 'This security key is used for the system.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show Specials Box',
        'configuration_key' => 'HOME_SPECIALS_BOX',
        'configuration_value' => '0',
        'description' => 'This module enables the Specials Box on the Homepage.',
        'group_id' => '1',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show Products Listing',
        'configuration_key' => 'HOME_PRODUCTS_LISTING',
        'configuration_value' => '1',
        'description' => 'This module enables the Category Listing on the Homepage.',
        'group_id' => '1',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show Specials Box on Products Listing page',
        'configuration_key' => 'PRODUCTS_LISTING_SPECIALS',
        'configuration_value' => '0',
        'description' => 'Show Specials Box on Products Listing page',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
/*
$records[]=array(
		'configuration_title'=>'Webshop Country ISO Number',
		'configuration_key'=>'COUNTRY_ISO_NR',
		'configuration_value'=>'528',
		'description'=>'The country where the webstore is located. Used to determine the local VAT classes.',
		'group_id'=>'3',
		'use_function'=>'',
		'set_function'=>'tep_country_select_option(',
		'depend_on_configuration_key'=>'',
		'use_function'=>'');
*/
$records[] = array(
        'configuration_title' => 'Store Name',
        'configuration_key' => 'STORE_NAME',
        'configuration_value' => 'TYPO3 Multishop Store',
        'description' => 'The name of the store.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Store E-mail',
        'configuration_key' => 'STORE_EMAIL',
        'configuration_value' => 'storemail@yourdomain.com',
        'description' => 'The e-mail address of the store owner.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Category image size',
        'configuration_key' => 'CATEGORY_IMAGE_SIZE_NORMAL',
        'configuration_value' => '168x168',
        'description' => 'The maximum image size in pixels for the category image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product image size (50)',
        'configuration_key' => 'PRODUCT_IMAGE_SIZE_50',
        'configuration_value' => '50x50',
        'description' => 'The maximum  in pixels for the 50 image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product image size (100)',
        'configuration_key' => 'PRODUCT_IMAGE_SIZE_100',
        'configuration_value' => '168x170',
        'description' => 'The maximum  in pixels for the 100 image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product image size (200)',
        'configuration_key' => 'PRODUCT_IMAGE_SIZE_200',
        'configuration_value' => '168x170',
        'description' => 'The maximum  in pixels for the 200 image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product image size (300)',
        'configuration_key' => 'PRODUCT_IMAGE_SIZE_300',
        'configuration_value' => '355x355',
        'description' => 'The maximum  in pixels for the 300 image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product image size (enlarged)',
        'configuration_key' => 'PRODUCT_IMAGE_SIZE_ENLARGED',
        'configuration_value' => '600x600',
        'description' => 'The maximum  in pixels for the enlarged image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Meta Title',
        'configuration_key' => 'META_TITLE',
        'configuration_value' => 'TYPO3 Multishop Store',
        'description' => 'The title of the webshop, used for the Meta Tags.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Meta Description',
        'configuration_key' => 'META_DESCRIPTION',
        'configuration_value' => 'TYPO3 Multishop Store.',
        'description' => 'The description of the webshop, used for the Meta Tags.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Meta Keywords',
        'configuration_key' => 'META_KEYWORDS',
        'configuration_value' => 'TYPO3 Multishop Store',
        'description' => 'The search words that are related to the webshop, used for the Meta Tags.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Listing Limiting',
        'configuration_key' => 'PRODUCTS_LISTING_LIMIT',
        'configuration_value' => '18',
        'description' => 'The total number of displayed products per page.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Categories Listing Type',
        'configuration_key' => 'CATEGORIES_LISTING_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the categories listing.',
        'group_id' => '5',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Listing Type',
        'configuration_key' => 'PRODUCTS_LISTING_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the products listing.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Page pagination type',
        'configuration_key' => 'PRODUCTS_LISTING_PAGINATION_TYPE',
        'configuration_value' => 'with page number button',
        'description' => 'The pagination type multishop will use.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'default\', \'with page number button\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Pagination',
        'configuration_key' => 'PRODUCTS_DETAIL_PAGE_PAGINATION',
        'configuration_value' => '1',
        'description' => 'Show\'s a button Previous and Next on the products detail page to go to the next or previous product within the same category.',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Watermark Text',
        'configuration_key' => 'PRODUCT_IMAGE_WATERMARK_TEXT',
        'configuration_value' => '',
        'description' => 'Add a watermark to enlarged product images. Example value: typo3multishop.com',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Watermark Font-size',
        'configuration_key' => 'PRODUCT_IMAGE_WATERMARK_FONT_SIZE',
        'configuration_value' => '30',
        'description' => 'Specify the font-size (in pt) of the watermark. Example value: 16',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Watermark Position',
        'configuration_key' => 'PRODUCT_IMAGE_WATERMARK_POSITION',
        'configuration_value' => 'south-west',
        'description' => 'Choose the position of the watermark.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'north-east\', \'south-east\',\'south-west\',\'north-west\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Shaped Corners',
        'configuration_key' => 'PRODUCT_IMAGE_SHAPED_CORNERS',
        'configuration_value' => '0',
        'description' => 'Adds shaped corners to every thumbnailed product image.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Category Image Shaped Corners',
        'configuration_key' => 'CATEGORY_IMAGE_SHAPED_CORNERS',
        'configuration_value' => '0',
        'description' => 'Adds shaped corners to every category image.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Checkout Type',
        'configuration_key' => 'CHECKOUT_TYPE',
        'configuration_value' => 'multistep',
        'description' => 'Choose which checkout process should be used (ie: multistep).',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show product prices with and without VAT',
        'configuration_key' => 'SHOW_PRICES_WITH_AND_WITHOUT_VAT',
        'configuration_value' => '0',
        'description' => 'If products contain VAT the price including and excluding VAT will be shown.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Allow merchant to create manual/telephone order',
        'configuration_key' => 'MANUAL_ORDER',
        'configuration_value' => '1',
        'description' => 'This module allows the merchant to create a telephone order for the customer.',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'This module enable to print invoice order',
        'configuration_key' => 'INVOICE_PRINT',
        'configuration_value' => '1',
        'description' => 'This module enable to print invoice order.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable print order packing list',
        'configuration_key' => 'PACKING_LIST_PRINT',
        'configuration_value' => '1',
        'description' => 'This module enable to print order packing list.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Invoice prefix',
        'configuration_key' => 'INVOICE_PREFIX',
        'configuration_value' => '',
        'description' => 'Invoice prefix.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Detail Type',
        'configuration_key' => 'PRODUCTS_DETAIL_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the products detail page.',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Staffel Prices',
        'configuration_key' => 'STAFFEL_PRICE_MODULE',
        'configuration_value' => '0',
        'description' => 'Enable staffel prices to your webshop.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'yes_with_stepping\', \'yes_without_stepping\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Coupon Code',
        'configuration_key' => 'COUPONS',
        'configuration_value' => '0',
        'description' => 'Activate coupon code.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Order Edit',
        'configuration_key' => 'ORDER_EDIT',
        'configuration_value' => '1',
        'description' => 'Enables you to edit orders.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Cache front-end',
        'configuration_key' => 'CACHE_FRONT_END',
        'configuration_value' => '0',
        'description' => 'This setting enables optimal caching features to the front-end. This feature is created for large catalogs.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Cache time-out for categories/products listing page',
        'configuration_key' => 'CACHE_TIME_OUT_LISTING_PAGES',
        'configuration_value' => '120',
        'description' => 'Specify the expiry time of the categories and products listing page cache files. Default: 3600. To turn this caching feature off specify: 0.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Cache time-out for search page',
        'configuration_key' => 'CACHE_TIME_OUT_SEARCH_PAGES',
        'configuration_value' => '120',
        'description' => 'Specify the expiry time of the search page cache files. Default: 3600. To turn this caching feature off specify: 0.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Cache time-out for products detail page',
        'configuration_key' => 'CACHE_TIME_OUT_PRODUCTS_DETAIL_PAGES',
        'configuration_value' => '120',
        'description' => 'Specify the expiry time of the products detail page cache files. Default: 3600. To turn this caching feature off specify: 0.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Cache time-out for the categories navigation menu',
        'configuration_key' => 'CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU',
        'configuration_value' => '360',
        'description' => 'Specify the expiry time of the categories navigation menu cache files. Default: 3600. To turn this caching feature off specify: 0.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Flat Database',
        'configuration_key' => 'FLAT_DATABASE',
        'configuration_value' => '0',
        'description' => 'This module creates flat products and categories database tables for maximum speed. An essential module for webshops that contain more than 250.000 products.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Length telephone number',
        'configuration_key' => 'CHECKOUT_LENGTH_TELEPHONE_NUMBER',
        'configuration_value' => '0',
        'description' => 'The total chars of a valid telephone number.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Validate Checkout Form',
        'configuration_key' => 'CHECKOUT_VALIDATE_FORM',
        'configuration_value' => '1',
        'description' => 'Validate Checkout Form by JavaScript.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Telephone Required',
        'configuration_key' => 'CHECKOUT_REQUIRED_TELEPHONE',
        'configuration_value' => '0',
        'description' => 'Validate Checkout Form by JavaScript.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Orders Custom Export Script',
        'configuration_key' => 'ORDERS_CUSTOM_EXPORT_SCRIPT',
        'configuration_value' => '',
        'description' => 'Optionally you can process the newly created orders by your custom script.',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Listing Autocorrection Type',
        'configuration_key' => 'PRODUCTS_LISTING_AUTO_COMPLETE_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the products listing in the autocorrection module.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show prices including VAT',
        'configuration_key' => 'SHOW_PRICES_INCLUDING_VAT',
        'configuration_value' => '1',
        'description' => 'This setting defines whether we should show product prices including VAT or without.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products short description contains HTML markup',
        'configuration_key' => 'PRODUCTS_SHORT_DESCRIPTION_CONTAINS_HTML_MARKUP',
        'configuration_value' => '0',
        'description' => 'Defines whether we should save the content as plain text or by HTML markup.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show products with image first',
        'configuration_key' => 'SHOW_PRODUCTS_WITH_IMAGE_FIRST',
        'configuration_value' => '0',
        'description' => 'If this setting is enabled the products that contains an image will be shown first.',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable state',
        'configuration_key' => 'CHECKOUT_ENABLE_STATE',
        'configuration_value' => '0',
        'description' => 'Show or hide the state/region inputfield.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable birthday',
        'configuration_key' => 'CHECKOUT_ENABLE_BIRTHDAY',
        'configuration_value' => '0',
        'description' => 'Show or hide the birthday inputfield.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Flat Database extra attribute option columns',
        'configuration_key' => 'FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS',
        'configuration_value' => '',
        'description' => 'If you use the flat database module and you need the (single) attribute values inside a seperate table field in the flat table then define the attribute option id\'s here (example: 1:varchar(10);4:int(10);5:varchar(10);10:varchar(10)).',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show stock level as boolean (yes/no)',
        'configuration_key' => 'SHOW_STOCK_LEVEL_AS_BOOLEAN',
        'configuration_value' => 'yes_with_image',
        'description' => 'Instead of showing the total count of the products stock level, show stock as: yes or no.',
        'group_id' => '10',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'yes_without_image\',\'yes_with_image\', \'no\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Watermark Width',
        'configuration_key' => 'PRODUCT_IMAGE_WATERMARK_WIDTH',
        'configuration_value' => '265',
        'description' => 'The width of the text. Example value: 100',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Watermark Height',
        'configuration_key' => 'PRODUCT_IMAGE_WATERMARK_HEIGHT',
        'configuration_value' => '160',
        'description' => 'The height of the text. Example value: 60',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product Image Watermark Font File',
        'configuration_key' => 'PRODUCT_IMAGE_WATERMARK_FONT_FILE',
        'configuration_value' => 'arial.ttf',
        'description' => 'Specify the font file for the watermarking. Example value: nasaliza.ttf',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Orders Paid Custom Script',
        'configuration_key' => 'ORDERS_PAID_CUSTOM_SCRIPT',
        'configuration_value' => '',
        'description' => 'Optionally you can process the paid order by your custom script.',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Specials Listing Type',
        'configuration_key' => 'SPECIALS_LISTING_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the listing of the specials',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Price Filter Box Steppings',
        'configuration_key' => 'PRICE_FILTER_BOX_STEPPINGS',
        'configuration_value' => '0-5;5-10;10-15;15-20;20-25;25-30;30-35;35-50;50-100;100-200;200-3000',
        'description' => 'Optional field. Defines the steppings of the price filter box.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Send Order Confirmation Letters also to',
        'configuration_key' => 'SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO',
        'configuration_value' => '',
        'description' => 'Send the order confirmation letter also to the following e-mail addresses.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable product attributes tab in products editor',
        'configuration_key' => 'DISABLE_PRODUCT_ATTRIBUTES_TAB_IN_EDITOR',
        'configuration_value' => '0',
        'description' => 'Disables the product attributes tab in the products editor',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products description',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION',
        'configuration_value' => '1',
        'description' => 'This enables the search-engine to also search in the products description.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show specials box till categories sublevel',
        'configuration_key' => 'CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL',
        'configuration_value' => '1',
        'description' => 'This value defines till which categories sublevel the specials scroller should be shown. Choose 0 to show the specials on each categories level.',
        'group_id' => '5',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Ultrasearch Fields',
        'configuration_key' => 'ULTRASEARCH_FIELDS',
        'configuration_value' => '',
        'description' => 'Define which fields you\'d like to show in the ultrasearch form. (example: 1:list;2:radio;3:checkbox;price_filter:0-1000)',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Relatives Type',
        'configuration_key' => 'PRODUCTS_RELATIVES_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the products relatives on the products detail page.',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Product edit method filter',
        'configuration_key' => 'PRODUCT_EDIT_METHOD_FILTER',
        'configuration_value' => '0',
        'description' => 'Enables the shipping / payment methods filter on the product edit page.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Customer edit method filter',
        'configuration_key' => 'CUSTOMER_EDIT_METHOD_FILTER',
        'configuration_value' => '0',
        'description' => 'Enables the shipping / payment methods filter on the customer edit page.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Group edit method filter',
        'configuration_key' => 'GROUP_EDIT_METHOD_FILTER',
        'configuration_value' => '0',
        'description' => 'Enables the shipping / payment methods filter on the group edit page.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Edit Order Type',
        'configuration_key' => 'ADMIN_EDIT_ORDER_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin edit order page. Example value: fileadmin/scripts/admin_edit_order)',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Products Edit Type',
        'configuration_key' => 'ADMIN_PRODUCTS_EDIT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin products edit page. Example value: fileadmin/scripts/admin_products_edit)',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Shopping cart Type',
        'configuration_key' => 'SHOPPING_CART_TYPE',
        'configuration_value' => 'default',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the shopping cart page. Example value: fileadmin/scripts/shopping_cart)',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Create Account Type',
        'configuration_key' => 'CREATE_ACCOUNT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the create account page. Example value: fileadmin/scripts/create_account',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Edit Account Type',
        'configuration_key' => 'EDIT_ACCOUNT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the edit account page. Example value: fileadmin/scripts/edit_account',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Invoice PDF header image',
        'configuration_key' => 'INVOICE_PDF_HEADER_IMAGE',
        'configuration_value' => '',
        'description' => 'Full URL to header image of the PDF invoice.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Invoice PDF footer image',
        'configuration_key' => 'INVOICE_PDF_FOOTER_IMAGE',
        'configuration_value' => '',
        'description' => 'Full URL to footer image of the PDF invoice.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Multishop version',
        'configuration_key' => 'MULTISHOP_VERSION',
        'configuration_value' => '3.0.0',
        'description' => 'Database version.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Subtract Stock',
        'configuration_key' => 'SUBTRACT_STOCK',
        'configuration_value' => '1',
        'description' => 'Enable the subtraction of the products stock level.',
        'group_id' => '10',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Detail Tabs',
        'configuration_key' => 'PRODUCTS_DETAIL_NUMBER_OF_TABS',
        'configuration_value' => '0',
        'description' => 'Optional field. Number of tabs used on the products detail page.',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Basket Content Element Type',
        'configuration_key' => 'BASKET_TYPE',
        'configuration_value' => 'default',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the cart contents element. Example value: fileadmin/scripts/basket_content_element',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products id',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_ID',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search for the products id.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
/*
$records[]=array(
		'configuration_title'=>'Disable VAT rate when cross borders',
		'configuration_key'=>'DISABLE_VAT_RATE_WHEN_CROSS_BORDERS',
		'configuration_value'=>'0',
		'description'=>'When a customer is from a different country than the store owner calculate zero tax.',
		'group_id'=>'3',
		'use_function'=>'',
		'set_function'=>'tep_cfg_select_option(array(\'0\',\'1\'),',
		'depend_on_configuration_key'=>'',
		'use_function'=>'');
*/
$records[] = array(
        'configuration_title' => 'Crumbar Type',
        'configuration_key' => 'CRUMBAR_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the crumbar. Example value: fileadmin/scripts/crumbar',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable crumbar navigation menu',
        'configuration_key' => 'DISABLE_CRUMBAR',
        'configuration_value' => '0',
        'description' => 'Disable internal crumbar navigation menu.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Include products description database field',
        'configuration_key' => 'INCLUDE_PRODUCTS_DESCRIPTION_DB_FIELD_IN_PRODUCTS_LISTING',
        'configuration_value' => '0',
        'description' => 'Include products description database field in the products listing page.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Download Invoice Type',
        'configuration_key' => 'DOWNLOAD_INVOICE_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the PDF invoice. Example value: fileadmin/scripts/download_invoice',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Categories Type',
        'configuration_key' => 'CATEGORIES_TYPE',
        'configuration_value' => 'default',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the categories navigation box content element. Example value: fileadmin/scripts/categories_nav',
        'group_id' => '5',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Account Order History',
        'configuration_key' => 'ORDER_HISTORY_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the accounts order history page. Example value: fileadmin/scripts/order_history',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Customers Import Type',
        'configuration_key' => 'ADMIN_CUSTOMERS_IMPORT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the customers import module. Example value: fileadmin/scripts/admin_customers_import',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Specials Section Listing Type',
        'configuration_key' => 'SPECIALS_SECTION_LISTING_TYPE',
        'configuration_value' => 'default',
        'description' => 'The lay-out type for displaying the listing of the specials (based on section)',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable Product When Negative Stock',
        'configuration_key' => 'DISABLE_PRODUCT_WHEN_NEGATIVE_STOCK',
        'configuration_value' => '0',
        'description' => 'Automatically turn off the product when the stock level gets negatave.',
        'group_id' => '10',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Orders Type',
        'configuration_key' => 'ADMIN_ORDERS_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin orders listing page. Example value: fileadmin/scripts/admin_orders)',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Lock Order After Creating Invoice',
        'configuration_key' => 'LOCK_ORDER_AFTER_CREATING_INVOICE',
        'configuration_value' => '1',
        'description' => 'When this setting is enabled the order will be locked after creating the invoice.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Invoice System',
        'configuration_key' => 'ADMIN_INVOICE_MODULE',
        'configuration_value' => '1',
        'description' => 'Enable the invoice system.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Cart session page uid',
        'configuration_key' => 'CART_PAGE_UID',
        'configuration_value' => '',
        'description' => 'Normally the cart of your customer is shared with all shop in shops you create. By giving this setting a custom number the cart is only used on the current shop.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Listing Sort-Order Option',
        'configuration_key' => 'PRODUCTS_LISTING_SORT_ORDER_OPTION',
        'configuration_value' => 'desc',
        'description' => 'Normally the products in the products listing page gets sorted ascending. By this setting you can switch it to descending.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'asc\',\'desc\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products New: number of days',
        'configuration_key' => 'PRODUCTS_NEW_NUMBER_OF_DAYS',
        'configuration_value' => '60',
        'description' => 'The number of days that a product should be listed on the latest products page.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Manufacturers Type',
        'configuration_key' => 'MANUFACTURERS_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the manufacturers listing page. Example value: fileadmin/scripts/manufacturers_listing',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable Multishop Warning System',
        'configuration_key' => 'DISABLE_MULTISHOP_CONFIGURATION_VALIDATION',
        'configuration_value' => '0',
        'description' => 'If enabled Multishop will validate the shop configuration and warn if some basic configurations are missing.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Products Import Type',
        'configuration_key' => 'ADMIN_PRODUCTS_IMPORT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the products import module. Example value: fileadmin/scripts/admin_import',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Edit Order Print Type',
        'configuration_key' => 'ADMIN_EDIT_ORDER_PRINT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin edit order print page. Example value: fileadmin/scripts/admin_edit_order_print)',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Categories Edit Type',
        'configuration_key' => 'ADMIN_CATEGORIES_EDIT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin categories edit page. Example value: fileadmin/scripts/admin_categories_edit)',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enabled Currencies',
        'configuration_key' => 'ENABLED_CURRENCIES',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this to display the currencies in the multi currency selector dropdownmenu. Example value: USD,EUR,GBP',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Default Currency',
        'configuration_key' => 'DEFAULT_CURRENCY',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this to select the default currency in the multi currency selector. Example value: USD',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Manufacturers Edit Type',
        'configuration_key' => 'ADMIN_MANUFACTURERS_EDIT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the manufacturers products listing page. Example value: fileadmin/scripts/admin_manufacturers_edit',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Manufacturers Products Listing Type',
        'configuration_key' => 'MANUFACTURERS_PRODUCTS_LISTING_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the manufacturers products listing page. Example value: fileadmin/scripts/manufacturers_products_listing',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Customers Edit Type',
        'configuration_key' => 'ADMIN_CUSTOMERS_EDIT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the edit customer form. Example value: fileadmin/scripts/admin_edit_customer',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Number of Product Images',
        'configuration_key' => 'NUMBER_OF_PRODUCT_IMAGES',
        'configuration_value' => '5',
        'description' => 'Define how many product images you want to use. Default value: 5',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Add to Cart - Redirect Back to Products Detail Page',
        'configuration_key' => 'REDIRECT_BACK_TO_PRODUCTS_DETAIL_PAGE_AFTER_ADD_TO_CART',
        'configuration_value' => '0',
        'description' => 'When this module is enabled the user will be redirected back to the products detail page, after adding the product to the cart.',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enabled languages',
        'configuration_key' => 'ENABLED_LANGUAGES',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to enable all TYPO3 enabled languages). Enable only specific languages. Example value: nl,de,es',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Ultrasearch Server Type',
        'configuration_key' => 'ULTRASEARCH_SERVER_TYPE',
        'configuration_value' => 'default',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the ultrasearch server (that generates the ajax products listing). Example value: fileadmin/scripts/ultrasearch_server',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin Products Search and Edit',
        'configuration_key' => 'ADMIN_PRODUCTS_SEARCH_AND_EDIT',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin products search and edit page. Example value: fileadmin/scripts/admin_products_search_and_edit',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Manufacturer image size',
        'configuration_key' => 'MANUFACTURER_IMAGE_SIZE_NORMAL',
        'configuration_value' => '176x140',
        'description' => 'The maximum image size in pixels for the manufacturer image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
/*
$records[]=array(
		'configuration_title'=>'Attributes Stock',
		'configuration_key'=>'PRODUCT_ATTRIBUTES_STOCK',
		'configuration_value'=>'0',
		'description'=>'Product stock based on attributes option pairing',
		'group_id'=>'10',
		'use_function'=>'',
		'set_function'=>'tep_cfg_select_option(array(\'0\', \'1\'),',
		'depend_on_configuration_key'=>'',
		'use_function'=>'');
$records[]=array(
		'configuration_title'=>'Attributes Stock front view',
		'configuration_key'=>'PRODUCT_ATTRIBUTES_STOCK_FRONT_TABLE_VIEW',
		'configuration_value'=>'0',
		'description'=>'Table view for attributes stock in front product details',
		'group_id'=>'10',
		'use_function'=>'',
		'set_function'=>'tep_cfg_select_option(array(\'0\', \'1\'),',
		'depend_on_configuration_key'=>'',
		'use_function'=>'');
*/
$records[] = array(
        'configuration_title' => 'Admin notification',
        'configuration_key' => 'DISPLAY_REALTIME_NOTIFICATION_MESSAGES',
        'configuration_value' => '1',
        'description' => 'Get notified when visitor order/search on your shop.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display specials above products listing',
        'configuration_key' => 'DISPLAY_SPECIALS_ABOVE_PRODUCTS_LISTING',
        'configuration_value' => '1',
        'description' => 'Enable to display product specials above the products listing page.',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable Full-Text Search (MySQL MATCH) in products search',
        'configuration_key' => 'ENABLE_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH',
        'configuration_value' => '0',
        'description' => 'Enable Full-Text Search (MySQL MATCH) in products search.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Minimum number of chars to use Full-Text Search (MySQL MATCH).',
        'configuration_key' => 'FULLTEXT_SEARCH_MIN_CHARS',
        'configuration_value' => '3',
        'description' => 'Minimum number of chars to use Full-Text Search (MySQL MATCH).',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Search Fallback Search',
        'configuration_key' => 'PRODUCTS_SEARCH_FALLBACK_SEARCH',
        'configuration_value' => '0',
        'description' => 'Enables fall back search. This means the search engine tries a different query when the normal query returns zero results.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Ultrasearch Content Element',
        'configuration_key' => 'ULTRASEARCH_TYPE',
        'configuration_value' => 'ultrasearch',
        'description' => 'The script name of the Ultrasearch content element. Default value: ultrasearch.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in vendor code',
        'configuration_key' => 'SEARCH_ALSO_IN_VENDOR_CODE',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column vendor code.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search wildcard operator (%) mode',
        'configuration_key' => 'REGULAR_SEARCH_MODE',
        'configuration_value' => '%keyword%',
        'description' => 'Search wildcard operator (%) mode',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'%keyword%\',\'%keyword\', \'keyword%\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in categories name',
        'configuration_key' => 'SEARCH_ALSO_IN_CATEGORIES_NAME',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column categories name.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in SKU code',
        'configuration_key' => 'SEARCH_ALSO_IN_SKU_CODE',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column SKU code.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in EAN code',
        'configuration_key' => 'SEARCH_ALSO_IN_EAN_CODE',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column EAN code.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products model',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_MODEL',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column products model.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable checkout for guests',
        'configuration_key' => 'DISABLE_CHECKOUT_FOR_GUESTS',
        'configuration_value' => '0',
        'description' => 'Allow or disallow checkout for guests.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products attributes values',
        'configuration_key' => 'SEARCH_ALSO_IN_ATTRIBUTE_OPTION_IDS',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the product attribute option values table. Provide the option id(s) here. Example value: 1,2,3.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in manufacturers name',
        'configuration_key' => 'SEARCH_ALSO_IN_MANUFACTURERS_NAME',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column manufacturers name.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable order status per entry of the ordered products',
        'configuration_key' => 'ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS',
        'configuration_value' => '0',
        'description' => 'Enable the status on order product level',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Allow customers to order products that are out of stock',
        'configuration_key' => 'ALLOW_ORDER_OUT_OF_STOCK_PRODUCT',
        'configuration_value' => '1',
        'description' => 'Allow customers to make a reservation order for out of stock product(s), by enabling this setting the \'Disable Product When Negative Stock\' module will not functioning properly even when the module turned on.',
        'group_id' => '10',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable out of stock message in checkout',
        'configuration_key' => 'DISABLE_OUT_OF_STOCK_PRODUCT_WARNING_MESSAGE',
        'configuration_value' => '0',
        'description' => 'Disables the out of stock warning on the shopping cart page',
        'group_id' => '10',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Create invoice after creating order',
        'configuration_key' => 'CREATE_INVOICE_DIRECTLY_AFTER_CREATING_ORDER',
        'configuration_value' => '0',
        'description' => 'This setting forces to create the invoice, directly after creating the order.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display product image in admin order details',
        'configuration_key' => 'DISPLAY_PRODUCT_IMAGE_IN_ADMIN_ORDER_DETAILS',
        'configuration_value' => '0',
        'description' => 'This setting display product image in admin order details.',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display product image in admin packing slip',
        'configuration_key' => 'DISPLAY_PRODUCT_IMAGE_IN_ADMIN_PACKING_SLIP',
        'configuration_value' => '0',
        'description' => 'This setting display product image in admin packing slip.',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Page title delimeter',
        'configuration_key' => 'PAGE_TITLE_DELIMETER',
        'configuration_value' => ' - ',
        'description' => 'Delimeter for <title>pagename - pagetitle</title>',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin customers export/import data',
        'configuration_key' => 'CUSTOMERS_DATA_EXPORT_IMPORT',
        'configuration_value' => '0',
        'description' => 'Enable export/import data of the customers.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display VAT id input in checkout',
        'configuration_key' => 'CHECKOUT_DISPLAY_VAT_ID_INPUT',
        'configuration_value' => '0',
        'description' => 'This setting display VAT id input in checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display CoC id input in checkout',
        'configuration_key' => 'CHECKOUT_DISPLAY_COC_ID_INPUT',
        'configuration_value' => '0',
        'description' => 'This setting display COC id input in checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display the selectbox for products listing limit',
        'configuration_key' => 'PRODUCTS_LISTING_DISPLAY_PAGINATION_FORM',
        'configuration_value' => '0',
        'description' => 'This setting display the selectbox for customers to choose the limit for products listing',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display the "sort by" for the products listing',
        'configuration_key' => 'PRODUCTS_LISTING_DISPLAY_ORDERBY_FORM',
        'configuration_value' => '0',
        'description' => 'This setting display the "sort by" for products listing',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products negative keywords',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_NEGATIVE_KEYWORDS',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the column products_negative_keywords.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Attributes options group',
        'configuration_key' => 'ENABLE_ATTRIBUTES_OPTIONS_GROUP',
        'configuration_value' => '0',
        'description' => 'Enabling the attributes options grouping',
        'group_id' => '15',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Rename uploaded images for products, categories, and manufaturers',
        'configuration_key' => 'ADMIN_AUTORENAME_UPLOADED_IMAGES',
        'configuration_value' => '1',
        'description' => 'Automaticly rename images filename for the products, categories, and manufacturers',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Crop product images',
        'configuration_key' => 'ADMIN_CROP_PRODUCT_IMAGES',
        'configuration_value' => '0',
        'description' => 'Crop product images',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Crop categories images',
        'configuration_key' => 'ADMIN_CROP_CATEGORIES_IMAGES',
        'configuration_value' => '0',
        'description' => 'Crop categories images',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Crop manufacturers images',
        'configuration_key' => 'ADMIN_CROP_MANUFACTURERS_IMAGES',
        'configuration_value' => '0',
        'description' => 'Crop manufacturers images',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display popup link in details page to show shipping cost',
        'configuration_key' => 'DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_DETAIL_PAGE',
        'configuration_value' => '0',
        'description' => 'Display popup link in details page to show shipping cost',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display shipping cost in shopping cart page',
        'configuration_key' => 'DISPLAY_SHIPPING_COSTS_ON_SHOPPING_CART_PAGE',
        'configuration_value' => '0',
        'description' => 'Display shipping cost in shopping cart page',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Gender input required',
        'configuration_key' => 'GENDER_INPUT_REQUIRED',
        'configuration_value' => '1',
        'description' => 'Gender input required',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display manufacturers advice price input in edit product',
        'configuration_key' => 'DISPLAY_MANUFACTURERS_ADVICE_PRICE_INPUT',
        'configuration_value' => '0',
        'description' => 'Display manufacturers advice price input in edit product',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable edit order products description field',
        'configuration_key' => 'ENABLE_EDIT_ORDER_PRODUCTS_DESCRIPTION_FIELD',
        'configuration_value' => '0',
        'description' => 'Enable edit order products description field',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable edit order payment condition field',
        'configuration_key' => 'ENABLE_EDIT_ORDER_PAYMENT_CONDITION_FIELD',
        'configuration_value' => '0',
        'description' => 'Enable edit order payment condition field',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable manual order custom order products name for existing products',
        'configuration_key' => 'ENABLE_MANUAL_ORDER_CUSTOM_ORDER_PRODUCTS_NAME',
        'configuration_value' => '0',
        'description' => 'Enable manual order custom order products name for existing products',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Allow duplicate e-mail address for different customers',
        'configuration_key' => 'ADMIN_ALLOW_DUPLICATE_CUSTOMERS_EMAIL_ADDRESS',
        'configuration_value' => '0',
        'description' => 'Allow duplicate e-mail address for different customers',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display exclude from feed input in edit product',
        'configuration_key' => 'DISPLAY_EXCLUDE_FROM_FEED_INPUT',
        'configuration_value' => '0',
        'description' => 'Display exclude from feed input in edit product',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display mobile number input in checkout',
        'configuration_key' => 'SHOW_MOBILE_NUMBER_INPUT_IN_CHECKOUT',
        'configuration_value' => '1',
        'description' => 'Display mobile number input in checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable VAT rate for foreign customers',
        'configuration_key' => 'DISABLE_VAT_FOR_FOREIGN_CUSTOMERS_WITH_COMPANY_VAT_ID',
        'configuration_value' => '0',
        'description' => 'When a customer is coming from a foreign country then the store calculate zero tax.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display popup link in listing page to show shipping cost',
        'configuration_key' => 'DISPLAY_SHIPPING_COSTS_ON_PRODUCTS_LISTING_PAGE',
        'configuration_value' => '0',
        'description' => 'Display popup link in listing page to show shipping cost',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Force checkout to display price included vat',
        'configuration_key' => 'FORCE_CHECKOUT_SHOW_PRICES_INCLUDING_VAT',
        'configuration_value' => '0',
        'description' => 'Always show price including vat in checkout even when the SHOW_PRICES_INCLUDING_VAT is disabled',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable realtime starttime/endtime check on products',
        'configuration_key' => 'DISABLE_REALTIME_CHECK_PRODUCTS_STARTTIME_ENDTIME',
        'configuration_value' => '0',
        'description' => 'Disable realtime check for starttime/endtime field in products table.',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Company Required',
        'configuration_key' => 'CHECKOUT_REQUIRED_COMPANY',
        'configuration_value' => '0',
        'description' => 'Validate Checkout Form by JavaScript.',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Create account disclaimer',
        'configuration_key' => 'CREATE_ACCOUNT_DISCLAIMER',
        'configuration_value' => '0',
        'description' => 'Create account disclaimer',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Right of withdrawal checkbox in checkout',
        'configuration_key' => 'RIGHT_OF_WITHDRAWAL_CHECKBOX_IN_CHECKOUT',
        'configuration_value' => '0',
        'description' => 'Right of withdrawal checkbox in checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Download Packing slip Type',
        'configuration_key' => 'DOWNLOAD_PACKINGSLIP_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the PDF packing slip. Example value: fileadmin/scripts/download_packingslip',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Packing slip PDF header image',
        'configuration_key' => 'PACKINGSLIP_PDF_HEADER_IMAGE',
        'configuration_value' => '',
        'description' => 'Full URL to header image of the PDF packing slip.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Packing slip PDF footer image',
        'configuration_key' => 'PACKINGSLIP_PDF_FOOTER_IMAGE',
        'configuration_value' => '',
        'description' => 'Full URL to footer image of the PDF packing slip.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Privacy statement link on checkout page',
        'configuration_key' => 'DISPLAY_PRIVACY_STATEMENT_LINK_ON_CHECKOUT_PAGE',
        'configuration_value' => '0',
        'description' => 'Display privacy statement link in checkout page',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Privacy statement link on create account page',
        'configuration_key' => 'DISPLAY_PRIVACY_STATEMENT_LINK_ON_CREATE_ACCOUNT_PAGE',
        'configuration_value' => '0',
        'description' => 'Display privacy statement link in create account page',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable checkout customer info link',
        'configuration_key' => 'ENABLE_CHECKOUT_CUSTOMER_INFO_LINK',
        'configuration_value' => '0',
        'description' => 'Enable checkout customer info link',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Use RTE (Rich Text Editor) in admin attribute description editor',
        'configuration_key' => 'USE_RTE_IN_ADMIN_ATTRIBUTE_DESCRIPTION_EDITOR',
        'configuration_value' => '0',
        'description' => 'Use RTE (Rich Text Editor) in admin attribute description editor',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display staffel price table in product details page',
        'configuration_key' => 'DISPLAY_STAFFEL_PRICE_TABLE_IN_PRODUCT_DETAILS_PAGE',
        'configuration_value' => '0',
        'description' => 'Display staffel price table in product details page',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Invoice PDF direct link from orders listing',
        'configuration_key' => 'INVOICE_PDF_DIRECT_LINK_FROM_ORDERS_LISTING',
        'configuration_value' => '1',
        'description' => 'Invoice PDF direct link from orders listing',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Packingslip PDF direct link from orders listing',
        'configuration_key' => 'PACKINGSLIP_PDF_DIRECT_LINK_FROM_ORDERS_LISTING',
        'configuration_value' => '1',
        'description' => 'Packingslip PDF direct link from orders listing',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Generate invoice id after order set to paid',
        'configuration_key' => 'GENERATE_INVOICE_ID_AFTER_ORDER_SET_TO_PAID',
        'configuration_value' => '1',
        'description' => 'Generate invoice id after order set to paid',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable customer comments per entry of the ordered products',
        'configuration_key' => 'ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_CUSTOMER_COMMENTS',
        'configuration_value' => '0',
        'description' => 'Display customer comments on order product level',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Packingslip PDF page numbering settings',
        'configuration_key' => 'PACKINGSLIP_PDF_PAGE_NUMBERING_SETTINGS',
        'configuration_value' => '',
        'description' => 'Change the default page numbering settings (font,weight,size,x-pos,y-pos). Default: arial,bold,11,500,795.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Invoice PDF page numbering settings',
        'configuration_key' => 'INVOICE_PDF_PAGE_NUMBERING_SETTINGS',
        'configuration_value' => '',
        'description' => 'Change the default page numbering settings (font,weight,size,x-pos,y-pos). Default: arial,bold,11,500,795.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Hide shipping costs line when cost are zero',
        'configuration_key' => 'CHECKOUT_HIDE_ZERO_SHIPPING_COSTS_IN_SUMMARY',
        'configuration_value' => '0',
        'description' => 'Hide shipping costs line when cost are zero',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Hide payment costs line when cost are zero',
        'configuration_key' => 'CHECKOUT_HIDE_ZERO_PAYMENT_COSTS_IN_SUMMARY',
        'configuration_value' => '0',
        'description' => 'Hide payment costs line when cost are zero',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Attribute values normal image size',
        'configuration_key' => 'ATTRIBUTE_VALUES_IMAGE_SIZE_NORMAL',
        'configuration_value' => '400x400',
        'description' => 'The normal image size in pixels for the attribute values image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Attribute values small image size',
        'configuration_key' => 'ATTRIBUTE_VALUES_IMAGE_SIZE_SMALL',
        'configuration_value' => '200x200',
        'description' => 'The small image size in pixels for the attribute values image folder.',
        'group_id' => '2',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display checkbox of accept general condition in create account page',
        'configuration_key' => 'DISPLAY_ACCEPT_GENERAL_CONDITIONS_IN_CREATE_ACCOUNT',
        'configuration_value' => '0',
        'description' => 'Display checkbox of accept general condition in create account page',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Right of withdrawal checkbox in create account',
        'configuration_key' => 'RIGHT_OF_WITHDRAWAL_CHECKBOX_IN_CREATE_ACCOUNT',
        'configuration_value' => '0',
        'description' => 'Right of withdrawal checkbox in create account',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display appended EAN code to product name in order e-mail, and edit order',
        'configuration_key' => 'DISPLAY_EAN_IN_ORDER_DETAILS',
        'configuration_value' => '1',
        'description' => 'Display appended EAN code to product name in order e-mail, and edit order',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display appended SKU code to product name in order e-mail, and edit order',
        'configuration_key' => 'DISPLAY_SKU_IN_ORDER_DETAILS',
        'configuration_value' => '1',
        'description' => 'Display appended SKU code to product name in order e-mail, and edit order',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display appended vendor code to product name in order e-mail, and edit order',
        'configuration_key' => 'DISPLAY_VENDOR_IN_ORDER_DETAILS',
        'configuration_value' => '1',
        'description' => 'Display appended vendor code to product name in order e-mail, and edit order',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display appended products model to product name in order e-mail, and edit order',
        'configuration_key' => 'DISPLAY_PRODUCTS_MODEL_IN_ORDER_DETAILS',
        'configuration_value' => '1',
        'description' => 'Display appended products model to product name in order e-mail, and edit order',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable re-order feature in account order history page',
        'configuration_key' => 'ENABLE_REORDER_FEATURE_IN_ACCOUNT_ORDER_HISTORY',
        'configuration_value' => '1',
        'description' => 'Enables the customer to quickly re-order the ordered products in the account order history page',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable adding product not in catalog database',
        'configuration_key' => 'DISABLE_EDIT_ORDER_ADD_MANUAL_PRODUCT',
        'configuration_value' => '0',
        'description' => 'Disable adding product not in catalog database',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable virtual products',
        'configuration_key' => 'ENABLE_VIRTUAL_PRODUCTS',
        'configuration_value' => '1',
        'description' => 'This module enables you to sell virtual products (i.e. Ebooks).',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\', \'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable hybrid search - Full-Text Search (MySQL MATCH) with standard query in products search',
        'configuration_key' => 'ENABLE_HYBRID_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH',
        'configuration_value' => '0',
        'description' => 'Enable Full-Text Search (MySQL MATCH) in products search.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Delete PDF invoice from disk after being downloaded',
        'configuration_key' => 'DELETE_PDF_INVOICE_AFTER_BEING_DOWNLOADED',
        'configuration_value' => '1',
        'description' => 'Delete PDF invoice from disk after being downloaded.',
        'group_id' => '12',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Add Reply-To e-mail address',
        'configuration_key' => 'STORE_REPLY_TO_EMAIL',
        'configuration_value' => '',
        'description' => 'Add Reply-To e-mail address for every e-mail sent by multishop.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Hide password field in edit customer page',
        'configuration_key' => 'HIDE_PASSWORD_FIELD_IN_EDIT_CUSTOMER',
        'configuration_value' => '0',
        'description' => 'Hide password field in edit customer page.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products meta title',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_META_TITLE',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the products meta title.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products meta keywords',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_META_KEYWORDS',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the products meta keywords.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Search also in products meta description',
        'configuration_key' => 'SEARCH_ALSO_IN_PRODUCTS_META_DESCRIPTION',
        'configuration_value' => '0',
        'description' => 'This enables the search-engine to also search in the products meta description.',
        'group_id' => '13',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Default payment condition value',
        'configuration_key' => 'DEFAULT_PAYMENT_CONDITION_VALUE',
        'configuration_value' => '14',
        'description' => 'Defined in admin edit customer and admin edit order.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'E-mail address of the web developer',
        'configuration_key' => 'DEVELOPER_EMAIL',
        'configuration_value' => '',
        'description' => 'The e-mail address of the web developer. For debugging purposes.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'E-mail address of the finance department',
        'configuration_key' => 'FINANCE_EMAIL',
        'configuration_value' => '',
        'description' => 'The e-mail address of the finance department. When PSP notification handler is having an issue it will be mailed to this email.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Use reguler username input in create account',
        'configuration_key' => 'CREATE_ACCOUNT_REGULAR_USERNAME',
        'configuration_value' => '0',
        'description' => 'Use reguler username for registration instead using e-mail address as username',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Hide general conditions checkbox',
        'configuration_key' => 'HIDE_GENERAL_CONDITIONS_CHECKBOX_ON_CHECKOUT',
        'configuration_value' => '0',
        'description' => 'Hide general conditions checkbox on checkout page',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Delete packingslip PDF after downloading',
        'configuration_key' => 'DELETE_PDF_PACKING_SLIP_AFTER_BEING_DOWNLOADED',
        'configuration_value' => '1',
        'description' => 'Delete packingslip PDF after downloading',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable discount on edit product level',
        'configuration_key' => 'ENABLE_DISCOUNT_ON_EDIT_ORDER_PRODUCT',
        'configuration_value' => '0',
        'description' => 'Enable discount on edit product level',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Address format',
        'configuration_key' => 'ADDRESS_FORMAT',
        'configuration_value' => '###ADDRESS###<br/>###ZIP### ###CITY###<br/>###COUNTRY###',
        'description' => 'Address format.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Make Chamber Of Commerce (COC) field in admin create/edit customer required',
        'configuration_key' => 'ADMIN_COC_ID_FIELD_REQUIRED',
        'configuration_value' => '0',
        'description' => 'Make Chamber Of Commerce (COC) field in admin create/edit customer required.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Make Value Added Tax (VAT) field in admin create/edit customer required',
        'configuration_key' => 'ADMIN_VAT_ID_FIELD_REQUIRED',
        'configuration_value' => '0',
        'description' => 'Make Value Added Tax (VAT) field in admin create/edit customer required.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable the default crumpath for products have multiple categories',
        'configuration_key' => 'ENABLE_DEFAULT_CRUMPATH',
        'configuration_value' => '0',
        'description' => 'Enable the default crumpath for products have multiple categories',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Attach Invoice PDF in paid letter e-mail',
        'configuration_key' => 'ATTACH_INVOICE_PDF_IN_PAID_LETTER_EMAIL',
        'configuration_value' => '0',
        'description' => 'Attach Invoice PDF in paid letter e-mail',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Checkout newsletter checkbox checked on default',
        'configuration_key' => 'CHECKOUT_NEWSLETTER_CHECKBOX_CHECKED_ON_DEFAULT',
        'configuration_value' => '0',
        'description' => 'Checkout newsletter checkbox checked on default',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Always display shipping costs',
        'configuration_key' => 'ALWAYS_DISPLAY_SHIPPING_COSTS',
        'configuration_value' => '0',
        'description' => 'Always display shipping costs in checkout even when it\'s cost 0',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable fe_groups discount percentage',
        'configuration_key' => 'ENABLE_FE_GROUP_DISCOUNT_PERCENTAGE',
        'configuration_value' => '0',
        'description' => 'Enable fe_groups discount percentage',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Price Filter without category query string',
        'configuration_key' => 'PRICE_FILTER_WITHOUT_CATEGORY_QUERY_STRING',
        'configuration_value' => '0',
        'description' => 'Optional field.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Include attributes in product feed',
        'configuration_key' => 'INCLUDE_ATTRIBUTES_IN_PRODUCT_FEED',
        'configuration_value' => '1',
        'description' => 'Optional field.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
/*
$records[]=array(
    'configuration_title'=>'Right of revocation link in checkout',
    'configuration_key'=>'RIGHT_OF_REVOCATION_LINK_IN_CHECKOUT',
    'configuration_value'=>'0',
    'description'=>'Right of revocation link in checkout',
    'group_id'=>'8',
    'use_function'=>'',
    'set_function'=>'tep_cfg_select_option(array(\'0\',\'1\'),',
    'depend_on_configuration_key'=>'',
    'use_function'=>''
);
*/
$records[] = array(
        'configuration_title' => 'Disable birthdate in admin add/edit customer',
        'configuration_key' => 'DISABLE_BIRTHDATE_IN_ADMIN_CUSTOMER_FORM',
        'configuration_value' => '0',
        'description' => 'Disable birthdate in admin add/edit customer',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Add link to product name in checkout review',
        'configuration_key' => 'ADD_LINK_TO_PRODUCT_NAME_IN_CHECKOUT_REVIEW',
        'configuration_value' => '0',
        'description' => 'Add link to product name in checkout review',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display capital price column in edit order product',
        'configuration_key' => 'ENABLE_CAPITAL_PRICE_ON_EDIT_ORDER_PRODUCT',
        'configuration_value' => '0',
        'description' => 'Display capital price column in edit order product',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Default payment method code',
        'configuration_key' => 'DEFAULT_PAYMENT_METHOD_CODE',
        'configuration_value' => '',
        'description' => 'Defined in admin edit customer and admin edit order.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Validate checkout on disabled products',
        'configuration_key' => 'VALIDATE_CHECKOUT_ON_DISABLED_PRODUCTS',
        'configuration_value' => '1',
        'description' => 'Validate checkout on disabled products',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable edit order customer details update validation',
        'configuration_key' => 'DISABLE_EDIT_ORDER_CUSTOMER_DETAILS_VALIDATION',
        'configuration_value' => '0',
        'description' => 'Disable edit order customer details update validation',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Always fold on open for foreign language panel',
        'configuration_key' => 'FOLD_FOREIGN_LANGUAGE_INPUT_FIELDS',
        'configuration_value' => '1',
        'description' => 'Always fold on open for foreign language panel',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show departmnent input field in admin edit customer',
        'configuration_key' => 'SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER',
        'configuration_value' => '0',
        'description' => 'Show departmnent input field in admin edit customer',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show departmnent input field in admin edit customer',
        'configuration_key' => 'SHOW_DEPARTMENT_INPUT_FIELD_IN_ADMIN_EDIT_CUSTOMER',
        'configuration_value' => '0',
        'description' => 'Show departmnent input field in admin edit customer',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Edit order select2 product minimum character',
        'configuration_key' => 'EDIT_ORDER_SELECT2_PRODUCT_MINIMUM_CHARACTER',
        'configuration_value' => '0',
        'description' => 'Minimum character to type before select2 give results for admin edit order product',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display gender input in edit order customer details form',
        'configuration_key' => 'DISPLAY_GENDER_INPUT_IN_EDIT_ORDER_CUSTOMER_DETAILS',
        'configuration_value' => '0',
        'description' => 'Display gender input in edit order customer details form',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable input autofill in customer create/edit account',
        'configuration_key' => 'DISABLE_INPUT_AUTOFILL_IN_CUSTOMER_CREATE_EDIT_ACCOUNT',
        'configuration_value' => '1',
        'description' => 'Disable input autofill in customer create/edit account',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable input autofill in checkout',
        'configuration_key' => 'DISABLE_INPUT_AUTOFILL_IN_CHECKOUT',
        'configuration_value' => '1',
        'description' => 'Disable input autofill in checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable auto shipping costs in edit order',
        'configuration_key' => 'ENABLE_AUTO_SHIPPING_COSTS_IN_EDIT_ORDER',
        'configuration_value' => '0',
        'description' => 'Enable auto shipping costs in edit order',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Create new order from edit order use same client details',
        'configuration_key' => 'CREATE_NEW_ORDER_FROM_EDIT_ORDER',
        'configuration_value' => '0',
        'description' => 'Create new order from edit order use same client details',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin New Manual Order Type',
        'configuration_key' => 'ADMIN_NEW_MANUAL_ORDER_FORM_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin new manual order page. Example value: fileadmin/scripts/admin_new_order',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Clear guest user session data after checkout',
        'configuration_key' => 'CLEAR_GUEST_USER_SESSION_DATA_AFTER_CHECKOUT',
        'configuration_value' => '0',
        'description' => 'Clear guest user session data after checkout',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Relate product to products across multiple shop',
        'configuration_key' => 'CROSS_SHOP_PRODUCT_RELATION',
        'configuration_value' => '0',
        'description' => 'Relate product to products across multiple shop',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Limit initialization catalog (categories and products) select2 results',
        'configuration_key' => 'LIMIT_CATALOG_SELECT2_INIT_RESULTS',
        'configuration_value' => '0',
        'description' => 'Limit initialization catalog (categories and products) select2 results ',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Auto checked mail for paid status change',
        'configuration_key' => 'AUTO_CHECKED_MAIL_SEND_PAID_STATUS_CHANGE',
        'configuration_value' => '1',
        'description' => 'Auto checked mail for paid status change',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable captcha in create account',
        'configuration_key' => 'DISABLE_CAPTCHA_IN_CREATE_ACCOUNT',
        'configuration_value' => '0',
        'description' => 'Disable captcha in create account',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show Qty Delivered',
        'configuration_key' => 'SHOW_QTY_DELIVERED',
        'configuration_value' => '0',
        'description' => 'Show Qty Delivered',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Custom ajax get admin orders listing processor',
        'configuration_key' => 'GET_ADMIN_ORDERS_LISTING_DETAILS_TYPE',
        'configuration_value' => '',
        'description' => 'Custom ajax get admin orders listing processor',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Subtract product stock when order is paid',
        'configuration_key' => 'SUBTRACT_PRODUCT_STOCK_WHEN_ORDER_PAID',
        'configuration_value' => '0',
        'description' => 'Subtract product stock when order is paid',
        'group_id' => '10',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Automatically clear Multishop cache on catalog changes',
        'configuration_key' => 'AUTOMATICALLY_CLEAR_MULTISHOP_CACHE_ON_CATALOG_CHANGES',
        'configuration_value' => '1',
        'description' => 'Automatically clear Multishop cache on catalog changes',
        'group_id' => '14',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Add Hours details to expected delivery date in edit order',
        'configuration_key' => 'ADD_HOURS_TO_EDIT_ORDER_EXPECTED_DELIVERY_DATE',
        'configuration_value' => '0',
        'description' => 'Add Hours details to expected delivery date in edit order',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Allow purchase free products',
        'configuration_key' => 'ALLOW_PURCHASE_FREE_PRODUCTS',
        'configuration_value' => '0',
        'description' => 'Allow purchase free products',
        'group_id' => '8',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Automatically checked subscribe newsletter in create account',
        'configuration_key' => 'AUTOMATICALLY_CHECKED_NEWSLETTER_SUBSCRIBE',
        'configuration_value' => '0',
        'description' => 'Automatically checked subscribe newsletter in create account',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Always hide disabled products',
        'configuration_key' => 'ALWAYS_HIDE_DISABLED_PRODUCTS',
        'configuration_value' => '0',
        'description' => 'Always hide disabled products',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Make first level of stepping price editable',
        'configuration_key' => 'MAKE_FIRST_LEVEL_OF_STEPPING_PRICE_EDITABLE',
        'configuration_value' => '0',
        'description' => 'Make first level of stepping price editable',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Products Listing Processor Type',
        'configuration_key' => 'PRODUCTS_LISTING_PROCESSOR_TYPE',
        'configuration_value' => 'default',
        'description' => 'The processort for displaying categories/products listing lay-out type ',
        'group_id' => '6',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show Product Options Description',
        'configuration_key' => 'SHOW_PRODUCT_OPTIONS_DESCRIPTION',
        'configuration_value' => '0',
        'description' => 'Show Product Options Description',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Show Product Options Description in Tooltip',
        'configuration_key' => 'SHOW_PRODUCT_OPTIONS_DESCRIPTION_IN_TOOLTIP',
        'configuration_value' => '0',
        'description' => 'Show Product Options Description in Tooltip',
        'group_id' => '7',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Always open extend search box in orders listing',
        'configuration_key' => 'ALWAYS_OPEN_EXTEND_SEARCH_IN_ORDERS_LISTING',
        'configuration_value' => '0',
        'description' => 'Always open extend search box in orders listing',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display prepended products model to product name in edit order',
        'configuration_key' => 'DISPLAY_PRODUCTS_MODEL_IN_EDIT_ORDER',
        'configuration_value' => '0',
        'description' => 'Display prepended products model to product name in edit order',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Display appended products id to product name in edit order',
        'configuration_key' => 'DISPLAY_PRODUCTS_ID_IN_EDIT_ORDER',
        'configuration_value' => '0',
        'description' => 'Display appended products id to product name in edit order',
        'group_id' => '9',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Send Order Confirmation Letters to',
        'configuration_key' => 'SEND_ORDER_CONFIRMATION_LETTER_TO',
        'configuration_value' => '',
        'description' => 'Send the order confirmation letter to the following e-mail addresses.',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Enable foreign source name advanced search dropdown on products search and edit.',
        'configuration_key' => 'ENABLE_FOREIGN_SOURCE_NAME_IN_ADMIN_PRODUCTS_SEARCH_AND_EDIT',
        'configuration_value' => '0',
        'description' => 'Enable foreign source name advanced search dropdown on products search and edit.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable select2 dropdown for top categories input in product search and edit overview.',
        'configuration_key' => 'PRODUCT_SEARCH_AND_EDIT_DISABLE_SELECT2_FOR_TOP_CAT_INPUT',
        'configuration_value' => '1',
        'description' => 'Disable select2 dropdown for top categories input in product search and edit overview.',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Disable send order confirmation letter to STORE_EMAIL',
        'configuration_key' => 'DISABLE_SEND_ORDER_CONFIRMATION_LETTER_TO_STORE_EMAIL',
        'configuration_value' => '0',
        'description' => 'Disable send order confirmation letter to STORE_EMAIL',
        'group_id' => '3',
        'use_function' => '',
        'set_function' => 'tep_cfg_select_option(array(\'0\',\'1\'),',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
        'configuration_title' => 'Admin CMS Edit Type',
        'configuration_key' => 'ADMIN_CMS_EDIT_TYPE',
        'configuration_value' => '',
        'description' => 'Optional field (leave empty to use the default). Use this for customizing the edit cms form. Example value: fileadmin/scripts/admin_edit_cms',
        'group_id' => '11',
        'use_function' => '',
        'set_function' => '',
        'depend_on_configuration_key' => '',
        'use_function' => ''
);
$records[] = array(
    'configuration_title' => 'Admin Invoices Type',
    'configuration_key' => 'ADMIN_INVOICES_TYPE',
    'configuration_value' => '',
    'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin invoices page. Example value: fileadmin/scripts/admin_invoices',
    'group_id' => '11',
    'use_function' => '',
    'set_function' => '',
    'depend_on_configuration_key' => '',
    'use_function' => ''
);
$records[] = array(
    'configuration_title' => 'Admin Product Attributes Type',
    'configuration_key' => 'ADMIN_PRODUCT_ATTRIBUTES_TYPE',
    'configuration_value' => '',
    'description' => 'Optional field (leave empty to use the default). Use this for customizing the admin product attributes page. Example value: fileadmin/scripts/admin_product_attributes',
    'group_id' => '11',
    'use_function' => '',
    'set_function' => '',
    'depend_on_configuration_key' => '',
    'use_function' => ''
);
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/configuration/tx_multishop_configuration.php']['addConfigurationRecordsPreHook'])) {
    $params = array(
            'records' => &$records
    );
    foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/configuration/tx_multishop_configuration.php']['addConfigurationRecordsPreHook'] as $funcRef) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
    }
}
// custom hook that can be controlled by third-party plugin eof
?>
