# TYPO3 Extension Manager dump 1.1
#
# Host: localhost    Database: subtest
#--------------------------------------------------------


#
# Table structure for table "tx_multishop_configuration"
#
CREATE TABLE tx_multishop_configuration (
  id int(5) NOT NULL auto_increment,
  configuration_title varchar(64) NOT NULL default '',
  configuration_key varchar(64) NOT NULL default '',
  configuration_value text,
  description varchar(255) default '',
  group_id int(5) NOT NULL default '0',
  sort_order int(5) default '0',
  last_modified int(11) default '0',
  date_added int(11) default '0',
  use_function varchar(255) default '',
  set_function varchar(255) default '',
  depend_on_configuration_key varchar(64) default '',
  PRIMARY KEY (id),
  KEY configuration_key (configuration_key),
  KEY configuration_group_id (group_id),
  KEY sort_order (sort_order),
  KEY configuration_title (configuration_title),
  KEY admin_search (configuration_title,configuration_key)
) ENGINE=MyISAM;


INSERT INTO tx_multishop_configuration VALUES ('1', 'Show Specials Box', 'HOME_SPECIALS_BOX', '0', 'This module enables the Specials Box on the Homepage.', '0', NULL, NULL, '0', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('2', 'Show Products Listing', 'HOME_PRODUCTS_LISTING', '1', 'This module enables the Category Listing on the Homepage.', '1', NULL, NULL, '0', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('3', 'Show Specials Box on Products Listing page', 'PRODUCTS_LISTING_SPECIALS', '0', 'Show Specials Box on Products Listing page', '1', NULL, NULL, '0', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('28', 'Affiliate Shop', 'AFFILIATE_SHOP', '0', 'This module enables the webshop as affiliate shop (hiding the order/basket system).', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('7', 'Webshop Country ISO Number', 'COUNTRY_ISO_NR', '528', 'The country where the webstore is located. Used to determine the local VAT classes.', '3', NULL, NULL, '0', NULL, 'tep_country_select_option(', '');
INSERT INTO tx_multishop_configuration VALUES ('8', 'Show the footer navigation in the middle content', 'SHOW_INNER_FOOTER_NAV', '0', 'This module enables the inner footer navigation.', '1', NULL, NULL, '0', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('9', 'Store Name', 'STORE_NAME', '', 'The name of the store.', '3', NULL, NULL, '0', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('10', 'Store E-mail', 'STORE_EMAIL', 'info@typo3multishop.com', 'The e-mail address of the store owner.', '3', NULL, NULL, '0', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('11', 'Category image size', 'CATEGORY_IMAGE_SIZE_NORMAL', '176x140', 'The maximum image size in pixels for the category image folder.', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('12', 'Product image size (50)', 'PRODUCT_IMAGE_SIZE_50', '50x50', 'The maximum  in pixels for the 50 image folder.', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('13', 'Product image size (100)', 'PRODUCT_IMAGE_SIZE_100', '170x123', 'The maximum  in pixels for the 100 image folder.', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('14', 'Product image size (200)', 'PRODUCT_IMAGE_SIZE_200', '140x150', 'The maximum  in pixels for the 200 image folder.', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('15', 'Product image size (300)', 'PRODUCT_IMAGE_SIZE_300', '350x300', 'The maximum  in pixels for the 300 image folder.', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('16', 'Product image size (enlarged)', 'PRODUCT_IMAGE_SIZE_ENLARGED', '600x600', 'The maximum  in pixels for the enlarged image folder.', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('17', 'Meta Title', 'META_TITLE', 'TYPO3 MULTISHOP', 'The title of the webshop, used for the Meta Tags.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('18', 'Meta Description', 'META_DESCRIPTION', '', 'The description of the webshop, used for the Meta Tags.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('19', 'Meta Keywords', 'META_KEYWORDS', 'typo3 webshop, webshop, typo3 shop, typo3 multishop, typo3 multistore', 'The search words that are related to the webshop, used for the Meta Tags.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('20', 'Google Analytics Account', 'GOOGLE_ANALYTICS_ACCOUNT', '', 'Google Analytics is the enterprise-class web analytics solution that gives you rich insights into your website traffic and marketing effectiveness.', '4', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('21', 'Addthis Account', 'ADDTHIS_ACCOUNT', '', 'Add a Addthis button to your webshop. Addthis.com makes sharing your website urls easy.', '4', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('22', 'Google Adwords Conversion Code', 'GOOGLE_ADWORDS_CONVERSION_CODE', '', 'Add a Google Adwords Conversion Code to the thank you page of the webshop. This makes it possible to calculate the success-ratio of your Google Adwords campaign.', '4', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('23', 'Products Listing Limiting', 'PRODUCTS_LISTING_LIMIT', '16', 'The total number of displayed products per page.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('25', 'Categories Listing Type', 'CATEGORIES_LISTING_TYPE', 'default', 'The lay-out type for displaying the categories listing.', '5', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('26', 'Products Listing Type', 'PRODUCTS_LISTING_TYPE', 'default', 'The lay-out type for displaying the products listing.', '6', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('27', 'Product Pagination', 'PRODUCTS_DETAIL_PAGE_PAGINATION', '1', 'Show\'s a button Previous and Next on the products detail page to go to the next or previous product within the same category.', '7', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('49', 'Product Image Watermark Text', 'PRODUCT_IMAGE_WATERMARK_TEXT', 'TYPO3 MULTISHOP', 'Add a watermark to enlarged product images. Example value: typo3multishop.com', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('50', 'Product Image Watermark Font-size', 'PRODUCT_IMAGE_WATERMARK_FONT_SIZE', '32', 'Specify the font-size (in pt) of the watermark. Example value: 16', '2', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('51', 'Product Image Watermark Position', 'PRODUCT_IMAGE_WATERMARK_POSITION', 'north-west', 'Choose the position of the watermark.', '2', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'north-east\', \'south-east\',\'south-west\',\'north-west\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('52', 'Product Image Shaped Corners', 'PRODUCT_IMAGE_SHAPED_CORNERS', '0', 'Adds shaped corners to every thumbnailed product image.', '2', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('53', 'Category Image Shaped Corners', 'CATEGORY_IMAGE_SHAPED_CORNERS', '0', 'Adds shaped corners to every category image.', '2', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('54', 'Checkout Type', 'CHECKOUT_TYPE', 'multistep', 'Choose which checkout process should be used (ie: multistep).', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('55', 'Show product prices with and without VAT', 'SHOW_PRICES_WITH_AND_WITHOUT_VAT', '0', 'If products contain VAT the price including and excluding VAT will be shown.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('57', 'Enable the manual order', 'MANUAL_ORDER', '0', 'This module enables the manual order.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('58', 'This module enable to print invoice order', 'INVOICE_PRINT', '1', 'This module enable to print invoice order.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('59', 'This module enable to print order packing list', 'PACKING_LIST_PRINT', '1', 'This module enable to print order packing list.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('60', 'Invoice prefix', 'INVOICE_PREFIX', '', 'Invoice prefix.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('61', 'Accordion Menu', 'ACCORDION_MENU', '0', 'Accordion menu.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('62', 'Accordion Setup Modules', 'ACCORDION_SETUP_MODULES', '0', 'Accordion for setup modules.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('63', 'Products Detail Type', 'PRODUCTS_DETAIL_TYPE', 'default', 'The lay-out type for displaying the products detail page.', '7', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('64', 'Staffel Prices', 'STAFFEL_PRICE_MODULE', '1', 'Enable staffel prices to your webshop.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'yes_with_stepping\', \'yes_without_stepping\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('94', 'Products Relatives Type', 'PRODUCTS_RELATIVES_TYPE', 'default', 'The lay-out type for displaying the products relatives on the products detail page.', '7', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('66', 'Coupon Code', 'COUPONS', '1', 'Activate coupon code.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('67', 'Admin Order Edit', 'ORDER_EDIT', '1', 'Enables you to edit orders.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('68', 'Cache front-end', 'CACHE_FRONT_END', '0', 'This setting enables optimal caching features to the front-end. This is especially for big stores.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('69', 'Cache time out for categories/products listing page', 'CACHE_TIME_OUT_LISTING_PAGES', '9999999999999', 'Specify the expiry time of the categories and products listing page cache files. Default: 3600. To turn this caching feature off specify: 0.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('70', 'Cache time out for search page', 'CACHE_TIME_OUT_SEARCH_PAGES', '9999999999999', 'Specify the expiry time of the search page cache files. Default: 3600. To turn this caching feature off specify: 0.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('71', 'Cache time out for products detail page', 'CACHE_TIME_OUT_PRODUCTS_DETAIL_PAGES', '9999999999999', 'Specify the expiry time of the products detail page cache files. Default: 3600. To turn this caching feature off specify: 0.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('72', 'Cache time out for the categories navigation menu', 'CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU', '9999999999999', 'Specify the expiry time of the categories navigation menu cache files. Default: 3600. To turn this caching feature off specify: 0.', '3', NULL, NULL, '2010', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('73', 'Flat Database', 'FLAT_DATABASE', '0', 'This module creates flat products and categories database tables for maximum speed. An essential module for webshops that contain more than 250.000 products.', '3', NULL, NULL, '2010', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('74', 'Length telephone number', 'CHECKOUT_LENGTH_TELEPHONE_NUMBER', '10', 'The total chars of a valid telephone number.', '8', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('75', 'Validate Checkout Form', 'CHECKOUT_VALIDATE_FORM', '1', 'Validate Checkout Form by JavaScript.', '8', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('76', 'Telephone Required', 'CHECKOUT_REQUIRED_TELEPHONE', '1', 'Validate Checkout Form by JavaScript.', '8', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('77', 'Orders Custom Export Script', 'ORDERS_CUSTOM_EXPORT_SCRIPT', '', 'Optionally you can process the newly created orders by your custom script.', '9', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('78', 'Products Listing Auto Complete Type', 'PRODUCTS_LISTING_AUTO_COMPLETE_TYPE', 'default', 'The lay-out type for displaying the products listing in the auto complete module.', '6', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('79', 'Show prices including VAT', 'SHOW_PRICES_INCLUDING_VAT', '1', 'This setting defines whether we should show product prices including VAT or without.', '3', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('80', 'Products short description contains HTML markup', 'PRODUCTS_SHORT_DESCRIPTION_CONTAINS_HTML_MARKUP', '0', 'Defines whether we should save the content as plain text or by HTML markup.', '3', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('81', 'Show products with image first', 'SHOW_PRODUCTS_WITH_IMAGE_FIRST', '0', 'If this setting is enabled the products that contains an image will be shown first.', '7', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('82', 'Enable state', 'CHECKOUT_ENABLE_STATE', '0', 'Show or hide the state/region inputfield.', '8', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('83', 'Enable birthday', 'CHECKOUT_ENABLE_BIRTHDAY', '0', 'Show or hide the birthday inputfield.', '8', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('84', 'Flat Database extra attribute option columns', 'FLAT_DATABASE_EXTRA_ATTRIBUTE_OPTION_COLUMNS', '', 'If you use the flat database module and you need the (single) attribute values inside a seperate table field in the flat table then define the attribute option id\'s here (example: 1:varchar(10);4:int(10);5:varchar(10);10:varchar(10)).', '3', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('85', 'Show stock level as boolean (yes/no)', 'SHOW_STOCK_LEVEL_AS_BOOLEAN', '0', 'Instead of showing the total count of the products stock level, show stock as: yes or no.', '10', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'yes_without_image\',\'yes_with_image\', \'no\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('86', 'Product Image Watermark Width', 'PRODUCT_IMAGE_WATERMARK_WIDTH', '265', 'The width of the text. Example value: 100', '2', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('87', 'Product Image Watermark Height', 'PRODUCT_IMAGE_WATERMARK_HEIGHT', '80', 'The height of the text. Example value: 60', '2', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('88', 'Product Image Watermark Font File', 'PRODUCT_IMAGE_WATERMARK_FONT_FILE', 'arial.ttf', 'Specify the font file for the watermarking. Example value: nasaliza.ttf', '2', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('89', 'Orders Paid Custom Script', 'ORDERS_PAID_CUSTOM_SCRIPT', '', 'Optionally you can process the paid order by your custom script.', '9', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('90', 'Specials Listing Type', 'SPECIALS_LISTING_TYPE', 'default', 'The lay-out type for displaying the listing of the specials', '6', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('91', 'Price Filter Box Steppings', 'PRICE_FILTER_BOX_STEPPINGS', '0-10;10-25;25-50;50-100;100-250;250-500;500-1000;1000-2000;2000-3000', 'Optional field. Defines the steppings of the price filter box.', '3', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('92', 'Send Order Confirmation Letters also to', 'SEND_ORDER_CONFIRMATION_LETTER_ALSO_TO', '', 'Send the order confirmation letter also to the following e-mail addresses.', '3', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('93', 'Disable product attributes tab in products editor', 'DISABLE_PRODUCT_ATTRIBUTES_TAB_IN_EDITOR', '0', 'Disables the product attributes tab in the products editor', '3', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\',\'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('96', 'Search also in products description', 'SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION', '0', 'This enables the search-engine to also search in the products description.', '3', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\',\'0\'),', NULL);
INSERT INTO tx_multishop_configuration VALUES ('97', 'Show specials box till categories sublevel', 'CATEGORIES_LISTING_SPECIALS_CATEGORIES_SUBLEVEL', '2', 'This value defines till which categories sublevel the specials scroller should be shown. Choose 0 to show the specials on each categories level.', '3', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('98', 'Ultrasearch Fields', 'ULTRASEARCH_FIELDS', '', 'Define which fields you\'d like to show in the ultrasearch form. (example: 1:list;2:radio;3:checkbox;price_filter:0-1000)', '3', NULL, NULL, '2011', NULL, '', NULL);
INSERT INTO tx_multishop_configuration VALUES ('99', 'Product edit method filter', 'PRODUCT_EDIT_METHOD_FILTER', '0', 'Enables the shipping / payment methods filter on the product edit page.', '11', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('100', 'Products Detail Tabs', 'PRODUCTS_DETAIL_NUMBER_OF_TABS', '0', 'Optional field. Number of tabs used on the products detail page.', '11', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('101', 'Basket Content Element Type', 'BASKET_TYPE', '', 'Optional field (leave empty to use the default). Use this for customizing the cart contents element. Example value: fileadmin/scripts/basket_content_element', '11', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('102', 'Create Account Type', 'CREATE_ACCOUNT_TYPE', '', 'Optional field (leave empty to use the default). Use this for customizing the create account page. Example value: fileadmin/scripts/create_account', '11', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('103', 'Edit Account Type', 'EDIT_ACCOUNT_TYPE', '', 'Optional field (leave empty to use the default). Use this for customizing the edit account page. Example value: fileadmin/scripts/edit_account', '11', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('104', 'Invoice PDF header image', 'INVOICE_PDF_HEADER_IMAGE', '', 'Full URL to header image of the PDF invoice.', '12', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('105', 'Invoice PDF footer image', 'INVOICE_PDF_FOOTER_IMAGE', '', 'Full URL to footer image of the PDF invoice.', '12', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('106', 'Multishop version', 'MULTISHOP_VERSION', '', 'Database version.', '3', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('107', 'Subtract Stock', 'SUBTRACT_STOCK', '0', 'Enable the subtraction of the products stock level.', '10', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('108', 'Stores Module', 'STORES_MODULE', '0', 'Enable multiple stores (warehouses).', '11', NULL, NULL, '2011', NULL, 'tep_cfg_select_option(array(\'1\', \'0\'),', '');
INSERT INTO tx_multishop_configuration VALUES ('109', 'Admin Edit Order Type', 'ADMIN_EDIT_ORDER_TYPE', '', 'Optional field (leave empty to use the default). Use this for customizing the admin edit order page. Example value: fileadmin/scripts/admin_edit_order)', '11', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('110', 'Admin Products Edit Type', 'ADMIN_PRODUCTS_EDIT_TYPE', '', 'Optional field (leave empty to use the default). Use this for customizing the admin products edit page. Example value: fileadmin/scripts/admin_products_edit)', '11', NULL, NULL, '2011', NULL, '', '');
INSERT INTO tx_multishop_configuration VALUES ('111', 'Shopping cart Type', 'SHOPPING_CART_TYPE', '', 'Optional field (leave empty to use the default). Use this for customizing the shopping cart page. Example value: fileadmin/scripts/shopping_cart)', '11', NULL, NULL, '2011', NULL, '', '');


# TYPO3 Extension Manager dump 1.1
#
# Host: localhost    Database: subtest
#--------------------------------------------------------


#
# Table structure for table "tx_multishop_configuration_group"
#
CREATE TABLE tx_multishop_configuration_group (
  id int(5) NOT NULL auto_increment,
  configuration_title varchar(64) NOT NULL default '',
  description varchar(255) default '',
  sort_order int(5) default '0',
  visible int(1) default '1',
  PRIMARY KEY (id),
  KEY sort_order (sort_order),
  KEY visible (visible)
) ENGINE=MyISAM;


INSERT INTO tx_multishop_configuration_group VALUES ('1', 'Homepage', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('2', 'Image Settings', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('3', 'Webshop Settings', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('4', 'Webshop Plugins', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('5', 'Categories Listing', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('6', 'Products Listing', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('7', 'Products Detail Page', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('8', 'Checkout Settings', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('9', 'Orders Settings', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('10', 'Products Stock Settings', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('11', 'Admin Settings', '', NULL, '1');
INSERT INTO tx_multishop_configuration_group VALUES ('12', 'Invoice Settings', '', NULL, '1');

