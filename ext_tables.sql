CREATE TABLE `fe_groups` (
 `tx_multishop_discount` int(2) default '0',
 PRIMARY KEY (`uid`),
 KEY `parent` (`pid`),
 KEY `hidden` (`hidden`),
 KEY `deleted` (`deleted`),
 KEY `tx_multishop_discount` (`tx_multishop_discount`)
);

CREATE TABLE `fe_users` (
 `status` int(11) default '0',
 `date_of_birth` int(11) default '0',
 `comments` text,
 `address_number` varchar(150) default '',
 `mobile` varchar(150) default '',
 `gender` varchar(1) default '',
 `tx_multishop_discount` int(2) default '0',
 `tx_multishop_newsletter` tinyint(1) default '0',
 `tx_multishop_code` varchar(50) default '',
 `tx_multishop_optin_ip` varchar(50) default '',
 `tx_multishop_optin_crdate` int(10) default '0',
 `tx_multishop_payment_condition` varchar(50) default '',
 `address_ext` varchar(10) default '',
 `tx_multishop_source_id` varchar(50) default '',
 `page_uid` int(11) default '0',
 `street_name` varchar(75) default '',
 `http_referer` text,
 `ip_address` varchar(150) default '',
 `tx_multishop_vat_id` varchar(127) default '',
 `tx_multishop_coc_id` varchar(127) default '',
 `tx_multishop_quick_checkout` tinyint(1) default '0',
 `tx_multishop_customer_id` int(11) default '0',
 `tx_multishop_language` varchar(127) default '',
 KEY `username` (`username`),
 KEY `is_online` (`is_online`),
 KEY `pid` (`pid`,`username`),
 KEY `parent` (`pid`,`username`),
 KEY `tx_multishop_discount` (`tx_multishop_discount`),
 KEY `tx_multishop_newsletter` (`tx_multishop_newsletter`),
 KEY `tx_multishop_payment_condition` (`tx_multishop_payment_condition`),
 KEY `disable` (`disable`),
 KEY `deleted` (`deleted`),
 KEY `crdate` (`crdate`),
 KEY `lastlogin` (`lastlogin`),
 KEY `admin_search` (`company`,`name`,`email`),
 KEY `tx_multishop_code` (`tx_multishop_code`),
 KEY `tx_multishop_source_id` (`tx_multishop_source_id`),
 KEY `page_uid` (`page_uid`),
 KEY `email` (`email`),
 KEY `name` (`name`),
 KEY `company` (`company`),
 KEY `starttime` (`starttime`),
 KEY `endtime` (`endtime`),
 KEY `country` (`country`),
 KEY `gender` (`gender`),
 KEY `telephone` (`telephone`),
 KEY `mobile` (`mobile`),
 KEY `first_name` (`first_name`),
 KEY `middle_name` (`middle_name`),
 KEY `last_name` (`last_name`),
 KEY `vat_id` (`tx_multishop_vat_id`),
 KEY `coc_id` (`tx_multishop_coc_id`),
 KEY `tx_multishop_quick_checkout` (`tx_multishop_quick_checkout`),
 KEY `tx_multishop_customer_id` (`tx_multishop_customer_id`),
 KEY `tx_multishop_language` (`tx_multishop_language`)
);

CREATE TABLE `tx_multishop_cart_contents` (
 `id` int(11) NOT NULL auto_increment ,
 `contents` text,
 `customer_id` int(11) default '0',
 `is_checkout` tinyint(1) default '0',
 `crdate` int(11) default '0',
 `session_id` varchar(150) default '',
 `ip_address` varchar(150) default '',
 `page_uid` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `customer_id` (`customer_id`),
 KEY `crdate` (`crdate`),
 KEY `ip_address` (`ip_address`),
 KEY `is_checkout` (`is_checkout`),
 KEY `page_uid` (`page_uid`)
);

CREATE TABLE `tx_multishop_categories` (
 `categories_id` int(5) NOT NULL auto_increment,
 `categories_image` varchar(150) default '',
 `parent_id` int(5) default '0',
 `sort_order` int(11) default '0',
 `date_added` int(11) default '0',
 `last_modified` int(11) default '0',
 `status` int(1) default '1',
 `show_description` tinyint(1) default '0',
 `extid` varchar(100) default '0',
 `members_only` tinyint(1) default '0',
 `shortcut` varchar(75) default '',
 `categories_discount` decimal(4,2) default '0.00',
 `hide` tinyint(1) default '0',
 `categories_extra_cost` decimal(8,2) default '0.00',
 `cid` int(11) default '0',
 `did` int(11) default '0',
 `uncatid` varchar(255) default '',
 `categories_shippingcost` decimal(8,2) default '0.00',
 `page_uid` int(11) default '0',
 `option_attributes` varchar(254) default '',
 `categories_url` text,
 `custom_settings` text,
 `google_taxonomy_id` int(11) default '0',
 `hashed_id` varchar(32) default '',
 `hide_in_menu` tinyint(1) default '0',
 `col_position` tinyint(1) default '0',
 PRIMARY KEY (`categories_id`),
 KEY `idx_categories_parent_id` (`parent_id`),
 KEY `status` (`status`),
 KEY `extid` (`extid`),
 KEY `sort_order` (`sort_order`),
 KEY `members_only` (`members_only`),
 KEY `page_uid` (`page_uid`),
 KEY `combined_one` (`page_uid`,`status`),
 KEY `combined_two` (`page_uid`,`status`,`categories_id`),
 KEY `combined_three` (`page_uid`,`status`,`parent_id`),
 KEY `google_taxonomy_id` (`google_taxonomy_id`),
 KEY `hashed_id` (`hashed_id`),
 KEY `hide_in_menu` (`hide_in_menu`),
 KEY `col_position` (`col_position`)
);

CREATE TABLE `tx_multishop_categories_description` (
 `categories_id` int(5) NOT NULL default '0',
 `language_id` int(5) NOT NULL default '0',
 `categories_name` varchar(150) default '',
 `shortdescription` text,
 `keywords` varchar(250) default '',
 `content` text,
 `content_footer` text,
 `template_file` varchar(150) default '',
 `cid` int(11) default '0',
 `did` int(11) default '0',
 `meta_title` varchar(254) default NULL,
 `meta_description` text,
 `meta_keywords` text,
 PRIMARY KEY (`categories_id`,`language_id`),
 KEY `idx_categories_name` (`categories_name`),
 KEY `categories_id` (`categories_id`),
 KEY `language_id` (`language_id`),
 KEY `combined_one` (`language_id`,`categories_name`),
 KEY `combined_two` (`language_id`,`categories_id`),
 KEY `combined_three` (`language_id`,`categories_id`,`categories_name`)
);

CREATE TABLE `tx_multishop_cms` (
 `id` int(11) NOT NULL auto_increment,
 `status` tinyint(1) default '1',
 `html` int(1) default '0',
 `type` varchar(254) default '',
 `inmenu` int(1) default '0',
 `domain_id` tinyint(4) default '0',
 `form` tinyint(1) default '0',
 `sort_order` int(11) default '0',
 `url` varchar(150) default '',
 `topmenu` tinyint(1) default '0',
 `lytebox` tinyint(1) default '0',
 `link` varchar(250) default '',
 `manufacturers_id` int(11) default '0',
 `categories_id` int(11) default '0',
 `parent_id` int(11) default '0',
 `page_uid` int(11) default '0',
 `crdate` int(11) default '0',
 `hash` varchar(50) default '',
 PRIMARY KEY (`id`),
 KEY `domain_id` (`domain_id`),
 KEY `topmenu` (`topmenu`),
 KEY `inmenu` (`inmenu`),
 KEY `type` (`type`),
 KEY `sort_order` (`sort_order`),
 KEY `status` (`status`),
 KEY `parent_id` (`parent_id`),
 KEY `hash` (`hash`),
 KEY `page_uid` (`page_uid`)
);

CREATE TABLE `tx_multishop_cms_description` (
 `id` int(1) NOT NULL default '0',
 `language_id` tinyint(2) NOT NULL default '0',
 `name` varchar(150) default '',
 `content` text,
 `form_code` text,
 `extra_heading` varchar(127) default '',
 `negative_keywords` text,
 `sqlstr` text,
	PRIMARY KEY (`id`,`language_id`),
 KEY `pagina` (`name`),
 KEY `id` (`id`),
 KEY `language_id` (`language_id`),
 KEY `negative_keywords` (`negative_keywords`(250))
);

CREATE TABLE `tx_multishop_configuration` (
 `id` int(5) NOT NULL auto_increment,
 `configuration_title` varchar(64) default '',
 `configuration_key` varchar(64) default '',
 `configuration_value` text,
 `description` varchar(255) default '',
 `group_id` int(5) default '0',
 `sort_order` int(5) default '0',
 `last_modified` int(11) default '0',
 `date_added` int(11) default '0',
 `use_function` varchar(255) default '',
 `set_function` varchar(255) default '',
 `depend_on_configuration_key` varchar(64) default '',
 PRIMARY KEY (`id`),
 KEY `configuration_key` (`configuration_key`),
 KEY `configuration_group_id` (`group_id`),
 KEY `sort_order` (`sort_order`),
 KEY `configuration_title` (`configuration_title`),
 KEY `admin_search` (`configuration_title`,`configuration_key`),
 KEY `configuration_value` (`configuration_value`(250))
);

CREATE TABLE `tx_multishop_configuration_group` (
 `id` int(5) NOT NULL auto_increment,
 `configuration_title` varchar(64) default '',
 `description` varchar(255) default '',
 `sort_order` int(5) default '0',
 `visible` int(1) default '1',
 PRIMARY KEY (`id`),
 KEY `sort_order` (`sort_order`),
 KEY `visible` (`visible`)
);

CREATE TABLE `tx_multishop_configuration_values` (
 `id` int(11) NOT NULL auto_increment,
 `configuration_key` varchar(64) default '',
 `page_uid` int(11) default '0',
 `configuration_value` text,
 PRIMARY KEY (`id`),
 KEY `configuration_key` (`configuration_key`),
 KEY `page_uid` (`page_uid`),
 KEY `admin_search` (`configuration_key`,`page_uid`),
 KEY `configuration_value` (`configuration_value`(250))
);

CREATE TABLE `tx_multishop_countries_to_zones` (
 `id` int(11) NOT NULL auto_increment,
 `zone_id` int(4) default '0',
 `cn_iso_nr` int(11) default '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `cn_iso_nr` (`cn_iso_nr`),
 UNIQUE KEY `zone_id` (`zone_id`,`cn_iso_nr`)
);

CREATE TABLE `tx_multishop_coupons` (
 `id` int(11) NOT NULL auto_increment,
 `code` varchar(250) default '',
 `discount` decimal(5,2) default '0.00',
 `status` tinyint(1) default '0',
 `startdate` int(11) default '0',
 `enddate` int(11) default '0',
 `times_used` int(5) default '0',
 `crdate` int(11) default '0',
 `max_usage` int(11) default '0',
 `discount_type` varchar(25) default 'percentage',
 PRIMARY KEY (`id`),
 UNIQUE KEY `code` (`code`),
 KEY `status` (`status`),
 KEY `startdate` (`startdate`),
 KEY `enddate` (`enddate`),
 KEY `times_used` (`times_used`)
);

CREATE TABLE `tx_multishop_import_jobs` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(254) default '',
 `period` int(11) default '0',
 `last_run` int(11) default '0',
 `data` text,
 `predefined_variables` text,
 `status` tinyint(1) default '0',
 `page_uid` int(11) default '0',
 `categories_id` int(5) default '0',
 `code` varchar(32) default '',
 `prefix_source_name` varchar(50) default '',
 `type` varchar(154) default '',
 PRIMARY KEY (`id`),
 KEY `last_run` (`last_run`,`status`,`page_uid`,`categories_id`),
 KEY `code` (`code`),
 KEY `prefix_source_name` (`prefix_source_name`)
);

CREATE TABLE `tx_multishop_invoices` (
 `id` int(11) NOT NULL auto_increment,
 `invoice_id` varchar(255) default '',
 `invoice_inc` varchar(11) default '',
 `orders_id` int(11) default '0',
 `status` int(11) default '0',
 `customer_id` int(11) default '0',
 `crdate` int(11) default '0',
 `due_date` int(11) default '0',
 `reference` varchar(150) default '',
 `ordered_by` varchar(50) default '',
 `invoice_to` varchar(50) default '',
 `payment_condition` varchar(50) default '',
 `currency` varchar(5) default '',
 `discount` double(10,4) default '0.0000',
 `amount` double(10,4) default '0.0000',
 `page_uid` int(11) default '0',
 `paid` tinyint(1) default '0',
 `hash` varchar(50) default '0',
 `reversal_invoice` tinyint(1) default '0',
 `reversal_related_id` int(11) default '0',
 `store_currency` char(3) default '',
 `customer_currency` char(3) default '',
 `currency_rate` varchar(15) default '1',
 `date_mail_last_sent` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `orders_id` (`orders_id`),
 KEY `status` (`status`),
 KEY `invoice_id` (`invoice_id`),
 KEY `date` (`crdate`),
 KEY `paid` (`paid`),
 KEY `hash` (`hash`),
 KEY `customer_id` (`customer_id`),
 KEY `reversal_invoice` (`reversal_invoice`),
 KEY `reversal_related_id` (`reversal_related_id`),
 KEY `date_mail_last_sent` (`date_mail_last_sent`)
);

CREATE TABLE `tx_multishop_manufacturers` (
 `manufacturers_id` int(5) NOT NULL auto_increment,
 `manufacturers_name` varchar(64) default '',
 `manufacturers_image` varchar(64) default '',
 `date_added` int(11) default '0',
 `last_modified` int(11) default '0',
 `sort_order` int(11) default '0',
 `extid` varchar(100) default '',
 `icecat_mid` int(5) default '0',
 `manufacturers_extra_cost` decimal(24,14) default '0.00000000000000',
 `status` tinyint(1) default '1',
 PRIMARY KEY (`manufacturers_id`),
 KEY `IDX_MANUFACTURERS_NAME` (`manufacturers_name`),
 KEY `sort_order` (`sort_order`),
 KEY `status` (`status`)
);

CREATE TABLE `tx_multishop_manufacturers_cms` (
 `manufacturers_id` int(11) default '0',
 `language_id` tinyint(2) default '0',
 `content` text,
 `content_footer` text,
 `shortdescription` text,
 `negative_keywords` text,
 `meta_title` varchar(254) default NULL,
 `meta_description` text,
 `meta_keywords` text,
 KEY `combined` (`manufacturers_id`,`language_id`),
 KEY `content` (`content`(250)),
 KEY `negative_keywords` (`negative_keywords`(250))
);

CREATE TABLE `tx_multishop_manufacturers_info` (
 `manufacturers_id` int(5) NOT NULL default '0',
 `language_id` tinyint(2) NOT NULL default '0',
 `manufacturers_url` varchar(255) default '',
 `url_clicked` int(5) default '0',
 `date_last_click` int(11) default '0',
 PRIMARY KEY (`manufacturers_id`,`language_id`),
 KEY `languages_id` (`language_id`),
 KEY `language_id` (`language_id`)
);

CREATE TABLE `tx_multishop_modules` (
 `id` int(6) NOT NULL auto_increment,
 `code` varchar(50) default '',
 `name` varchar(254) default '',
 `description` text,
 `date` int(11) default '0',
 `status` tinyint(1) default '0',
 `category` varchar(254) default '',
 PRIMARY KEY (`id`),
 KEY `name` (`name`,`date`,`status`),
 KEY `category` (`category`),
 KEY `code` (`code`)
);

CREATE TABLE `tx_multishop_notification` (
 `id` int(11) NOT NULL auto_increment,
 `title` varchar(150) default '',
 `message` text,
 `customer_id` int(11) default '0',
 `unread` tinyint(1) default '0',
 `message_type` varchar(35) default '',
 `crdate` int(11) default '0',
 `ip_address` varchar(150) default '',
 `session_id` varchar(150) default '',
 PRIMARY KEY (`id`),
 KEY `unread` (`unread`),
 KEY `customer_id` (`customer_id`),
 KEY `crdate` (`crdate`),
 KEY `message_type` (`message_type`),
 KEY `ip_address` (`ip_address`),
 KEY `session_id` (`session_id`)
);

CREATE TABLE `tx_multishop_orders` (
 `orders_id` int(11) NOT NULL auto_increment,
 `customer_id` int(11) default '0',
 `page_uid` int(11) default '0',
 `billing_first_name` varchar(150) default '',
 `billing_middle_name` varchar(150) default '',
 `billing_last_name` varchar(150) default '',
 `billing_company` varchar(150) default '',
 `billing_name` varchar(150) default '',
 `billing_address` varchar(150) default '',
 `billing_building` varchar(150) default '',
 `billing_room` varchar(150) default '',
 `billing_city` varchar(150) default '',
 `billing_zip` varchar(150) default '',
 `billing_region` varchar(150) default '',
 `billing_country` varchar(150) default '',
 `billing_telephone` varchar(150) default '',
 `billing_mobile` varchar(150) default '',
 `billing_fax` varchar(150) default '',
 `billing_vat_id` varchar(150) default '',
 `billing_coc_id` varchar(150) default '',
 `delivery_first_name` varchar(150) default '',
 `delivery_middle_name` varchar(150) default '',
 `delivery_last_name` varchar(150) default '',
 `delivery_company` varchar(150) default '',
 `delivery_name` varchar(150) default '',
 `delivery_address` varchar(150) default '',
 `delivery_building` varchar(150) default '',
 `delivery_room` varchar(150) default '',
 `delivery_city` varchar(150) default '',
 `delivery_zip` varchar(150) default '',
 `delivery_region` varchar(150) default '',
 `delivery_country` varchar(150) default '',
 `delivery_telephone` varchar(150) default '',
 `delivery_mobile` varchar(150) default '',
 `delivery_fax` varchar(150) default '',
 `delivery_vat_id` varchar(150) default '',
 `status` int(3) default '1',
 `crdate` int(11) default '0',
 `ordercreated` tinyint(1) default '0',
 `bill` tinyint(1) default '1',
 `html` text,
 `mailed` tinyint(1) default '0',
 `ignore_subscription_orders` tinyint(1) default '0',
 `shipping_method` varchar(254) default '',
 `shipping_method_costs` decimal(24,14) default '0.00000000000000',
 `payment_method` varchar(254) default '',
 `payment_method_costs` decimal(24,14) default '0.00000000000000',
 `payment_condition` varchar(50) default '',
 `order_memo` text,
 `paid` tinyint(1) default '0',
 `billing_gender` char(1) default '',
 `billing_birthday` int(11) default '0',
 `billing_email` varchar(254) default '',
 `delivery_gender` char(1) default '',
 `delivery_email` varchar(254) default '',
 `by_phone` tinyint(1) default '0',
 `deleted` tinyint(1) default '0',
 `billing_address_number` varchar(10) default '',
 `delivery_address_number` varchar(10) default '',
 `shipping_method_label` varchar(150) default '',
 `payment_method_label` varchar(150) default '',
 `discount` decimal(24,14) default '0.00000000000000',
 `customer_comments` text,
 `is_locked` tinyint(1) default '0',
 `billing_address_ext` varchar(10) default '',
 `delivery_address_ext` varchar(10) default '',
 `hash` varchar(50) default '0',
 `cruser_id` int(11) default '0',
 `is_proposal` tinyint(1) default '0',
 `track_and_trace_code` varchar(50) default '',
 `expected_delivery_date` int(11) default '0',
 `store_currency` char(3) default '',
 `customer_currency` char(3) default '',
 `currency_rate` varchar(15) default '1',
 `language_id` int(5) default '0',
 `orders_tax_data` text,
 `status_last_modified` int(11) default '0',
 `reminder_sent` tinyint(1) default '0',
 `date_mail_last_sent` int(11) default '0',
 `grand_total` decimal(24,14) default '0.00000000000000',
 `billing_street_name` varchar(75) default '',
 `delivery_street_name` varchar(75) default '',
 `ip_address` varchar(150) default '',
 `http_referer` text,
 `user_agent` varchar(255) default '',
 `coupon_code` varchar(255) default '',
 `coupon_discount_type` varchar(25) default 'percentage',
 `coupon_discount_value` DECIMAL(24,14) default '0.00000000000000',
 `orders_last_modified` int(11) default '0',
 `track_and_trace_link` varchar(255) default '',
 `orders_paid_timestamp` int(11) default '0',
 `credit_order` tinyint(1) default '0',
 PRIMARY KEY (`orders_id`),
 KEY `customer_id` (`customer_id`),
 KEY `bu` (`page_uid`),
 KEY `status` (`status`),
 KEY `ordercreated` (`ordercreated`),
 KEY `factureren` (`bill`),
 KEY `paid` (`paid`),
 KEY `by_phone` (`by_phone`),
 KEY `deleted` (`deleted`),
 KEY `crdate` (`crdate`),
 KEY `hash` (`hash`),
 KEY `is_proposal` (`is_proposal`),
 KEY `expected_delivery_date` (`expected_delivery_date`),
 KEY `billing_company` (`billing_company`),
 KEY `billing_name` (`billing_name`),
 KEY `billing_email` (`billing_email`),
 KEY `billing_address` (`billing_address`),
 KEY `billing_zip` (`billing_zip`),
 KEY `billing_city` (`billing_city`),
 KEY `billing_telephone` (`billing_telephone`),
 KEY `billing_mobile` (`billing_mobile`),
 KEY `delivery_company` (`delivery_company`),
 KEY `delivery_name` (`delivery_name`),
 KEY `delivery_email` (`delivery_email`),
 KEY `delivery_address` (`delivery_address`),
 KEY `delivery_zip` (`delivery_zip`),
 KEY `delivery_city` (`delivery_city`),
 KEY `delivery_telephone` (`delivery_telephone`),
 KEY `delivery_mobile` (`delivery_mobile`),
 KEY `shipping_method` (`shipping_method`),
 KEY `payment_method` (`payment_method`),
 KEY `status_last_modified` (`status_last_modified`),
 KEY `email_sent` (`reminder_sent`),
 KEY `ip_address` (`ip_address`),
 KEY `user_agent` (`user_agent`),
 KEY `coupon_code` (`coupon_code`),
 KEY `coupon_discount_type` (`coupon_discount_type`),
 KEY `coupon_discount_value` (`coupon_discount_value`),
 KEY `date_mail_last_sent` (`date_mail_last_sent`),
 KEY `orders_paid_timestamp` (`orders_paid_timestamp`),
 KEY `credit_order` (`credit_order`)
) COMMENT='Ordersysteem';

CREATE TABLE `tx_multishop_orders_products` (
 `orders_products_id` int(11) NOT NULL auto_increment,
 `orders_id` int(11) default '0',
 `products_id` int(11) default '0',
 `qty` decimal(8,2) default '1.00',
 `products_name` varchar(254) default '',
 `products_description` text,
 `products_price` decimal(24,14) default '0.00000000000000',
 `final_price` decimal(24,14) default '0.00000000000000',
 `products_tax` decimal(8,2) default '0.00',
 `comments` varchar(150) default '',
 `status` int(3) default '1',
 `type` char(1) default 'P',
 `bill` tinyint(1) default '1',
 `products_model` varchar(250) default '',
 `file_label` varchar(250) default '',
 `file_location` varchar(250) default '',
 `file_downloaded` int(11) default '0',
 `file_download_code` varchar(32) default '0',
 `file_locked` tinyint(1) default '0',
 `qty_delivered` int(4) default '0',
 `qty_not_deliverable` int(4) default '0',
 `file_remote_location` text,
 `file_number_of_downloads` int(11) default '0',
 `products_tax_data` text,
 `order_unit_id` int(11) default '0',
 `order_unit_name` varchar(100) default '',
 `order_unit_code` varchar(15) default '',
 `categories_id` int(11) default '0',
 `manufacturers_id` int(11) default '0',
 `categories_id_0` int(5) default '0',
 `categories_name_0` varchar(150) default '',
 `categories_id_1` int(5) default '0',
 `categories_name_1` varchar(150) default '',
 `categories_id_2` int(5) default '0',
 `categories_name_2` varchar(150) default '',
 `categories_id_3` int(5) default '0',
 `categories_name_3` varchar(150) default '',
 `categories_id_4` int(5) default '0',
 `categories_name_4` varchar(150) default '',
 `categories_id_5` int(5) default '0',
 `categories_name_5` varchar(150) default '',
 `ean_code` varchar(50) default '',
 `sku_code` varchar(50) default '',
 `vendor_code` varchar(50) default '',
 `sort_order` int(11) default '0',
 `customer_comments` text,
 PRIMARY KEY (`orders_products_id`),
 KEY `orders_id` (`orders_id`),
 KEY `type` (`type`),
 KEY `bill` (`bill`),
 KEY `products_name` (`products_name`),
 KEY `file_download_code` (`file_download_code`),
 KEY `order_unit_id` (`order_unit_id`),
 KEY `categories_id` (`categories_id`),
 KEY `manufacturers_id` (`manufacturers_id`),
 KEY `ean_code` (`ean_code`),
 KEY `sku_code` (`sku_code`),
 KEY `vendor_code` (`vendor_code`),
 KEY `sort_order` (`sort_order`)
) COMMENT='Orderregels';

CREATE TABLE `tx_multishop_orders_products_attributes` (
 `orders_products_attributes_id` int(11) NOT NULL auto_increment,
 `orders_id` int(11) default '0',
 `orders_products_id` int(11) default '0',
 `products_options` varchar(250) default '',
 `products_options_values` varchar(250) default '',
 `options_values_price` decimal(24,14) default '0.00000000000000',
 `price_prefix` char(1) default '',
 `attributes_values` text,
 `products_options_id` int(11) default '0',
 `products_options_values_id` int(11) default '0',
 `attributes_tax_data` text,
 PRIMARY KEY (`orders_products_attributes_id`),
 KEY `orders_id` (`orders_id`),
 KEY `orders_id_2` (`orders_id`),
 KEY `orders_products_id` (`orders_products_id`)
) ;

CREATE TABLE `tx_multishop_orders_products_downloads` (
 `orders_products_id` int(11) NOT NULL default '0',
 `orders_id` int(11) default '0',
 `ip_address` varchar(150) default '',
 `date_of_download` int(11) default '0',
 PRIMARY KEY (`orders_products_id`),
 KEY `date_of_download` (`date_of_download`),
 KEY `orders_id` (`orders_id`)
);

CREATE TABLE `tx_multishop_orders_status` (
 `id` int(3) NOT NULL auto_increment,
 `deleted` tinyint(1) default '0',
 `crdate` int(11) default '0',
 `default_status` tinyint(1) default '0',
 `page_uid` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `default_status` (`default_status`),
 KEY `page_uid` (`page_uid`)
) COMMENT='Order Statussen';

CREATE TABLE `tx_multishop_orders_status_description` (
 `id` int(11) NOT NULL auto_increment,
 `orders_status_id` int(11) default '0',
 `language_id` int(5) default '0',
 `name` varchar(50) default '',
 PRIMARY KEY (`id`),
 KEY `orders_status_id` (`orders_status_id`),
 KEY `name` (`name`),
 KEY `language_id` (`language_id`)
);

CREATE TABLE `tx_multishop_orders_status_history` (
 `orders_status_history_id` int(11) NOT NULL auto_increment,
 `orders_id` int(11) default '0',
 `new_value` int(5) default '0',
 `old_value` int(5) default '0',
 `crdate` int(11) default '0',
 `customer_notified` int(1) default '0',
 `comments` text,
 PRIMARY KEY (`orders_status_history_id`),
 KEY `orders_id` (`orders_id`),
 KEY `crdate` (`crdate`),
 KEY `crdate_2` (`crdate`)
);

CREATE TABLE `tx_multishop_order_units` (
 `id` int(11) NOT NULL auto_increment,
 `code` varchar(15) default '',
 `crdate` int(11) default '0',
 `page_uid` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `code` (`code`),
 KEY `page_uid` (`page_uid`)
);

CREATE TABLE `tx_multishop_order_units_description` (
 `id` int(11) NOT NULL auto_increment,
 `order_unit_id` int(11) default '0',
 `language_id` int(5) default '0',
 `name` varchar(50) default '',
 PRIMARY KEY (`id`),
 KEY `order_unit_id` (`order_unit_id`),
 KEY `name` (`name`),
 KEY `language_id` (`language_id`)
);

CREATE TABLE `tx_multishop_orphan_files` (
 `id` int(11) NOT NULL auto_increment,
 `type` varchar(50) default '',
 `path` text,
 `file` varchar(255) default '',
 `orphan` tinyint(1) default '0',
 `checked` tinyint(1) default '0',
 `crdate` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `type` (`type`),
 KEY `crdate` (`crdate`),
 KEY `file` (`file`),
 KEY `orphan` (`orphan`),
 KEY `checked` (`checked`),
 KEY `path` (`path`(250))
);

CREATE TABLE `tx_multishop_payment_log` (
 `id` int(11) NOT NULL auto_increment,
 `orders_id` int(11) default '0',
 `multishop_transaction_id` varchar(127) default '',
 `provider_transaction_id` varchar(127) default '',
 `provider` varchar(127) default '',
 `ip_address` varchar(150) default '',
 `crdate` int(11) default '0',
 `title` varchar(127) default '',
 `description` text,
 `is_error` tinyint(1) default '0',
 `status_type` varchar(127) default '',
 `raw_data` mediumtext,
 PRIMARY KEY (`id`),
 KEY `orders_id` (`orders_id`),
 KEY `multishop_transaction_id` (`multishop_transaction_id`)
);

CREATE TABLE `tx_multishop_payment_methods` (
 `id` int(4) NOT NULL auto_increment,
 `code` varchar(50) default '',
 `provider` varchar(50) default '',
 `date` int(11) default '0',
 `status` tinyint(1) default '0',
 `vars` text,
 `handling_costs` varchar(25) default '0.00000000000000',
 `sort_order` int(11) default '0',
 `page_uid` int(11) default '0',
 `zone_id` int(11) default '0',
 `tax_id` int(11) default '0',
 `enable_on_default` tinyint(1) default '1',
 PRIMARY KEY (`id`),
 KEY `code` (`code`),
 KEY `isp` (`provider`),
 KEY `date` (`date`),
 KEY `status` (`status`),
 KEY `sort_order` (`sort_order`),
 KEY `page_uid` (`page_uid`,`zone_id`),
 KEY `enable_on_default` (`enable_on_default`)
);

CREATE TABLE `tx_multishop_payment_methods_description` (
 `id` int(4) NOT NULL default '0',
 `language_id` int(5) NOT NULL default '0',
 `name` varchar(255) default '',
 `description` text,
 PRIMARY KEY (`id`,`language_id`),
 KEY `name` (`name`),
 KEY `id` (`id`),
 KEY `language_id` (`language_id`),
 KEY `combined_two` (`language_id`,`id`)
);

CREATE TABLE `tx_multishop_payment_methods_to_zones` (
 `id` int(11) NOT NULL auto_increment,
 `zone_id` int(4) default '0',
 `payment_method_id` int(11) default '0',
 `sort_order` int(11) default '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `zone_id` (`zone_id`,`payment_method_id`),
 KEY `sort_order` (`sort_order`)
);

CREATE TABLE `tx_multishop_payment_shipping_mappings` (
 `id` int(11) NOT NULL auto_increment,
 `payment_method` int(4) default '0',
 `shipping_method` int(4) default '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `payment_method` (`payment_method`,`shipping_method`)
);

CREATE TABLE `tx_multishop_payment_transactions` (
 `id` int(11) NOT NULL auto_increment,
 `orders_id` int(11) default '0',
 `transaction_id` varchar(150) default '',
 `psp` varchar(25) default '',
 `crdate` int(11) default '0',
 `status` tinyint(1) default '0',
 `code` varchar(35) default '',
 PRIMARY KEY (`id`),
 KEY `orders_id` (`orders_id`,`transaction_id`,`crdate`,`status`)
);

CREATE TABLE `tx_multishop_products` (
 `products_id` int(11) NOT NULL auto_increment,
 `products_quantity` int(4) default '0',
 `products_model` varchar(128) default '',
 `products_image` varchar(250) default '',
 `products_image1` varchar(250) default '',
 `products_image2` varchar(250) default '',
 `products_image3` varchar(250) default '',
 `products_image4` varchar(250) default '',
 `products_price` decimal(24,14) default '0.00000000000000',
 `products_date_added` int(11) default '0',
 `products_last_modified` int(11) default '0',
 `products_date_available` int(11) default '0',
 `products_weight` decimal(5,2) default '0.00',
 `products_status` tinyint(1) default '0',
 `tax_id` int(5) default '0',
 `manufacturers_id` int(5) default '0',
 `products_pdf` varchar(250) default '',
 `sort_order` int(11) default '0',
 `extid` varchar(32) default '',
 `staffel_price` varchar(250) default '',
 `product_capital_price` decimal(24,14) default '0.00000000000000',
 `vendor_code` varchar(255) default '',
 `ean_code` varchar(50) default '',
 `page_uid` int(11) default '0',
 `contains_image` tinyint(1) default '0',
 `custom_settings` text,
 `products_multiplication` decimal(6,2) default '0.00',
 `minimum_quantity` decimal(6,2) default '1.00',
 `maximum_quantity` decimal(6,2) default NULL,
 `sku_code` varchar(25) default '',
 `products_condition` varchar(20) default 'new',
 `file_number_of_downloads` int(11) default '0',
 `order_unit_id` int(11) default '0',
 `imported_product` tinyint(1) default '0',
 `foreign_source_name` varchar(30) default '',
 `foreign_products_id` varchar(30) default '',
 `lock_imported_product` tinyint(1) default '0',
 `google_taxonomy_id` int(11) default '0',
 `import_job_id` int(11) default '0',
 `alert_quantity_threshold` int(11) default '0',
 `cruser_id` int(11) default '0',
 `starttime` int(11) default '0',
 `endtime` int(11) default '0',
 `specials_price_percentage` varchar(4) default '0',
 `manufacturers_advice_price` decimal(24,14) default '0.00000000000000',
 PRIMARY KEY (`products_id`),
 KEY `products_price` (`products_price`),
 KEY `products_model` (`products_model`),
 KEY `products_status` (`products_status`),
 KEY `manufacturers_id` (`manufacturers_id`),
 KEY `extid` (`extid`),
 KEY `page_uid` (`page_uid`),
 KEY `products_image` (`products_image`),
 KEY `products_image1` (`products_image1`),
 KEY `products_image2` (`products_image2`),
 KEY `products_image3` (`products_image3`),
 KEY `products_image4` (`products_image4`),
 KEY `contains_image` (`contains_image`),
 KEY `sku_code` (`sku_code`),
 KEY `imported_product` (`imported_product`),
 KEY `lock_imported_product` (`lock_imported_product`),
 KEY `vendor_code` (`vendor_code`),
 KEY `google_taxonomy_id` (`google_taxonomy_id`),
 KEY `import_job_id` (`import_job_id`),
 KEY `alert_quantity_threshold` (`alert_quantity_threshold`),
 KEY `cruser_id` (`cruser_id`),
 KEY `starttime` (`starttime`),
 KEY `endtime` (`endtime`),
 KEY `products_date_added` (`products_date_added`),
 KEY `products_last_modified` (`products_last_modified`),
 KEY `products_date_available` (`products_date_available`),
 KEY `foreign_source_name` (`foreign_source_name`),
 KEY `foreign_products_id` (`foreign_products_id`),
 KEY `specials_price_percentage` (`specials_price_percentage`),
 KEY `manufacturers_advice_price` (`manufacturers_advice_price`)
) ;


CREATE TABLE `tx_multishop_products_attributes` (
 `products_attributes_id` int(5) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `options_id` int(5) default '0',
 `options_values_id` int(5) default '0',
 `options_values_price` decimal(24,14) default '0.00000000000000',
 `price_prefix` char(1) default '',
 `dealer_price` decimal(24,14) default '0.00000000000000',
 `products_stock` mediumint(4) default '0',
 `hide` tinyint(1) default '0',
 `price_group_id` int(11) default '0',
 `attribute_image` varchar(150) default '',
 `sort_order_option_name` int(11) default '0',
 `sort_order_option_value` int(11) default '0',
 PRIMARY KEY (`products_attributes_id`),
 KEY `products_id` (`products_id`),
 KEY `options_id` (`options_id`),
 KEY `options_values_id` (`options_values_id`),
 KEY `sort_order_option_name` (`sort_order_option_name`),
 KEY `sort_order_option_value` (`sort_order_option_value`)
) ;

CREATE TABLE `tx_multishop_products_attributes_download` (
 `products_attributes_id` int(5) NOT NULL default '0',
 `products_attributes_filename` varchar(255) default '',
 `products_attributes_maxdays` int(2) default '0',
 `products_attributes_maxcount` int(2) default '0',
 PRIMARY KEY (`products_attributes_id`)
);

CREATE TABLE `tx_multishop_products_description` (
 `products_id` int(11) NOT NULL default '0',
 `page_uid` int(11) NOT NULL default '0',
 `language_id` int(5) NOT NULL default '0',
 `products_name` varchar(255) default '',
 `products_description` text,
 `products_url` text,
 `products_viewed` int(5) default '0',
 `products_shortdescription` text,
 `products_meta_keywords` varchar(254) default NULL,
 `ppc` tinyint(1) default '0',
 `form_code` text,
 `products_negative_keywords` varchar(255) default '',
 `promotext` varchar(255) default '',
 `products_meta_title` varchar(254) default NULL,
 `products_meta_description` varchar(254) default NULL,
 `file_label` varchar(250) default '',
 `file_location` varchar(250) default '',
 `delivery_time` varchar(75) default '',
 `products_description_tab_content_1` text,
 `products_description_tab_title_1` varchar(50) default '',
 `products_description_tab_content_2` text,
 `products_description_tab_title_2` varchar(50) default '',
 `products_description_tab_content_3` text,
 `products_description_tab_title_3` varchar(50) default '',
 `products_description_tab_content_4` text,
 `products_description_tab_title_4` varchar(50) default '',
 `file_remote_location` text,
 `layered_categories_id` int(11) NOT NULL default '0',
 PRIMARY KEY (`products_id`,`language_id`,`page_uid`,`layered_categories_id`),
 KEY `products_name` (`products_name`),
 KEY `products_id` (`products_id`),
 KEY `page_uid` (`page_uid`),
 KEY `language_id` (`language_id`),
 KEY `ppc` (`ppc`),
 KEY `combined_one` (`language_id`,`products_name`),
 KEY `combined_two` (`language_id`,`products_id`),
 KEY `combined_three` (`language_id`,`products_id`,`products_name`),
 KEY `combined_seven` (`language_id`,`products_meta_keywords`),
 KEY `products_description` (`products_description`(250)),
 KEY `products_negative_keywords` (`products_negative_keywords`(250)),
 KEY `promotext` (`promotext`),
 KEY `layered_categories_id` (`layered_categories_id`)
);

# remove fulltext index cause innodb does not support this
ALTER TABLE tx_multishop_products_description DROP INDEX combined_four;

CREATE TABLE `tx_multishop_products_faq` (
 `products_faq_id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `language_id` int(11) default '0',
 `question` varchar(255) default '',
 `answer` text,
 `sort_order` int(11) default '0',
 PRIMARY KEY (`products_faq_id`)
);

CREATE TABLE `tx_multishop_products_method_mappings` (
 `id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `method_id` int(11) default '0',
 `type` varchar(25) default '',
 `negate` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `negate` (`negate`),
 KEY `products_id` (`products_id`),
 KEY `method_id` (`method_id`),
 KEY `type` (`type`)
);

CREATE TABLE `tx_multishop_customers_method_mappings` (
 `id` int(11) NOT NULL auto_increment,
 `customers_id` int(11) default '0',
 `method_id` int(11) default '0',
 `type` varchar(25) default '',
 `negate` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `negate` (`negate`),
 KEY `customers_id` (`customers_id`),
 KEY `method_id` (`method_id`),
 KEY `type` (`type`)
);

CREATE TABLE `tx_multishop_customers_groups_method_mappings` (
 `id` int(11) NOT NULL auto_increment,
 `customers_groups_id` int(11) default '0',
 `method_id` int(11) default '0',
 `type` varchar(25) default '',
 `negate` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `negate` (`negate`),
 KEY `customers_groups_id` (`customers_groups_id`),
 KEY `method_id` (`method_id`),
 KEY `type` (`type`)
);

CREATE TABLE `tx_multishop_products_options` (
 `products_options_id` int(11) NOT NULL auto_increment,
 `language_id` int(5) NOT NULL default '0',
 `products_options_name` varchar(64) default '',
 `listtype` varchar(15) default 'pulldownmenu',
 `description` text,
 `sort_order` int(11) default '0',
 `price_group_id` int(11) default '0',
 `hide` tinyint(1) default '0',
 `attributes_values` tinyint(1) default '0',
 `hide_in_cart` tinyint(1) default '0',
 `required` tinyint(1) default '0',
 PRIMARY KEY (`products_options_id`,`language_id`),
 KEY `products_options_name` (`products_options_name`),
 KEY `products_options_id` (`products_options_id`),
 KEY `listtype` (`listtype`),
 KEY `sort_order` (`sort_order`),
 KEY `hide_in_cart` (`hide_in_cart`)
);

CREATE TABLE `tx_multishop_products_options_values` (
 `products_options_values_id` int(11) NOT NULL auto_increment,
 `language_id` int(5) NOT NULL default '0',
 `products_options_values_name` varchar(64) default '',
 `hide` tinyint(1) default '0',
 PRIMARY KEY (`products_options_values_id`,`language_id`),
 KEY `products_options_values_id` (`products_options_values_id`),
 KEY `products_options_values_name` (`products_options_values_name`),
 KEY `combined_one` (`language_id`,`products_options_values_name`)
);

CREATE TABLE `tx_multishop_products_options_values_extra` (
 `products_options_values_extra_id` int(11) NOT NULL default '0',
 `language_id` int(5) NOT NULL default '0',
 `products_options_values_extra_name` varchar(64) default '',
 `hide` tinyint(1) default '0',
 `sort_orders` int(11) default '0',
 PRIMARY KEY (`products_options_values_extra_id`,`language_id`),
 KEY `products_options_values_extra_id` (`products_options_values_extra_id`)
);

CREATE TABLE `tx_multishop_products_options_values_to_products_options` (
 `products_options_values_to_products_options_id` int(11) NOT NULL auto_increment,
 `products_options_id` int(5) default '0',
 `products_options_values_id` int(5) default '0',
 `products_options_values_image` varchar(255) default '',
 `sort_order` int(11) default '0',
 PRIMARY KEY (`products_options_values_to_products_options_id`),
 KEY `products_options_id` (`products_options_id`),
 KEY `products_options_values_id` (`products_options_values_id`),
 KEY `products_options_values_image` (`products_options_values_image`),
 KEY `sort_order` (`sort_order`)
);

CREATE TABLE `tx_multishop_products_search_log` (
 `id` int(11) NOT NULL auto_increment,
 `keyword` varchar(150) default '',
 `ip_address` varchar(150) default '',
 `http_host` varchar(60) default '',
 `crdate` int(11) default '0',
 `customer_id` int(11) default '0',
 `page_uid` int(11) default '0',
 `categories_id` int(11) default '0',
 `negative_results` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `keyword` (`keyword`),
 KEY `crdate` (`crdate`),
 KEY `http_host` (`http_host`),
 KEY `page_uid` (`page_uid`),
 KEY `categories_id` (`categories_id`),
 KEY `customer_id` (`customer_id`),
 KEY `negative_results` (`negative_results`)
) ;

CREATE TABLE `tx_multishop_products_to_categories` (
 `products_id` int(11) NOT NULL default '0',
 `categories_id` int(5) NOT NULL default '0',
 `sort_order` int(11) default '0',
 `page_uid` int(11) default '0',
 `related_to` int(11) default '0',
 `is_deepest` tinyint(1) default '0',
 PRIMARY KEY (`products_id`,`categories_id`),
 KEY `page_uid` (`page_uid`),
 KEY `products_id` (`products_id`),
 KEY `sort_order` (`sort_order`),
 KEY `categories_id` (`categories_id`),
 KEY `related_to` (`related_to`),
 KEY `is_deepest` (`is_deepest`)
);

CREATE TABLE `tx_multishop_categories_to_categories` (
 `id` int(11) NOT NULL auto_increment,
 `categories_id` int(11) default '0',
 `foreign_categories_id` int(11) default '0',
 `page_uid` int(11) default '0',
 `foreign_page_uid` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `categories_id` (`categories_id`),
 KEY `foreign_categories_id` (`foreign_categories_id`),
 KEY `page_uid` (`page_uid`),
 KEY `foreign_page_uid` (`foreign_page_uid`)
);


CREATE TABLE `tx_multishop_products_to_extra_options` (
 `id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `extra_options_id` int(11) default '0',
 PRIMARY KEY (`id`)
);

CREATE TABLE `tx_multishop_products_to_relative_products` (
 `products_to_relative_product_id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `relative_product_id` int(11) default '0',
 `relation_types` varchar(15) default 'cross-sell',
 PRIMARY KEY (`products_to_relative_product_id`),
 KEY `products_id` (`products_id`),
 KEY `relative_product_id` (`relative_product_id`),
 KEY `pid_to_relative_id` (`products_id`,`relative_product_id`),
 KEY `relative_to_pid_id` (`relative_product_id`,`products_id`),
 KEY `relation_types` (`relation_types`)
);


CREATE TABLE `tx_multishop_product_feeds` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(75) default '',
 `page_uid` int(11) default '0',
 `crdate` int(11) default '0',
 `utm_source` varchar(75) default '',
 `utm_medium` varchar(75) default '',
 `utm_term` varchar(75) default '',
 `utm_content` varchar(75) default '',
 `utm_campaign` varchar(75) default '',
 `fields` text,
 `format` varchar(25) default '',
 `delimiter` varchar(10) default '',
 `code` varchar(150) default '',
 `status` tinyint(1) default '0',
 `include_header` tinyint(1) default '0',
 `include_disabled` tinyint(1) default '0',
 `feed_type` varchar(50) default '',
 `post_data` text,
 `plain_text` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `code` (`code`)
);

CREATE TABLE `tx_multishop_orders_export` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(75) default '',
 `page_uid` int(11) default '0',
 `crdate` int(11) default '0',
 `fields` text,
 `post_data` text,
 `code` varchar(150) default '',
 `status` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `code` (`code`)
);

CREATE TABLE `tx_multishop_invoices_export` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(75) default '',
 `page_uid` int(11) default '0',
 `crdate` int(11) default '0',
 `fields` text,
 `post_data` text,
 `code` varchar(150) default '',
 `status` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `code` (`code`)
);

CREATE TABLE `tx_multishop_product_wishlist` (
 `product_wishlist_id` int(11) NOT NULL auto_increment,
 `wishlist_id` int(11) default '0',
 `product_id` int(11) default '0',
 `ordered` tinyint(1) default '0',
 PRIMARY KEY (`product_wishlist_id`)
);

CREATE TABLE `tx_multishop_reviews` (
 `reviews_id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `customers_id` int(5) default '0',
 `customers_name` varchar(64) default '',
 `reviews_rating` int(1) default '0',
 `date_added` int(11) default '0',
 `last_modified` int(11) default '0',
 `reviews_read` int(5) default '0',
 `message` text,
 `language_id` int(2) default '0',
 `ipaddress` varchar(127) default '',
 `status` tinyint(1) default '0',
 PRIMARY KEY (`reviews_id`),
 KEY `products_id` (`products_id`)
);

CREATE TABLE `tx_multishop_reviews_description` (
 `reviews_id` int(11) NOT NULL default '0',
 `language_id` tinyint(2) NOT NULL default '0',
 `reviews_text` text,
 PRIMARY KEY (`reviews_id`,`language_id`)
);

CREATE TABLE `tx_multishop_shipping_countries` (
 `id` int(11) NOT NULL auto_increment,
 `page_uid` int(11) default '0',
 `cn_iso_nr` int(11) default '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `cn_iso_nr` (`cn_iso_nr`,`page_uid`)
);

CREATE TABLE `tx_multishop_shipping_methods` (
 `id` int(4) NOT NULL auto_increment,
 `code` varchar(50) default '',
 `provider` varchar(50) default '',
 `date` int(11) default '0',
 `status` tinyint(1) default '0',
 `handling_costs` varchar(25) default '0.00000000000000',
 `shipping_costs_type` varchar(25) default '',
 `sort_order` int(11) default '0',
 `vars` text,
 `page_uid` int(11) default '0',
 `zone_id` int(11) default '0',
 `tax_id` int(11) default '0',
 `enable_on_default` tinyint(1) default '1',
 PRIMARY KEY (`id`),
 KEY `code` (`code`),
 KEY `date` (`date`),
 KEY `status` (`status`),
 KEY `provider` (`provider`),
 KEY `sort_order` (`sort_order`),
 KEY `page_uid` (`page_uid`,`zone_id`),
 KEY `enable_on_default` (`enable_on_default`)
);

CREATE TABLE `tx_multishop_shipping_methods_costs` (
 `id` int(11) NOT NULL auto_increment,
 `shipping_method_id` int(4) default '0',
 `zone_id` int(4) default '0',
 `price` text,
 PRIMARY KEY (`id`),
 KEY `shipping_method_id` (`shipping_method_id`,`zone_id`)
);

CREATE TABLE `tx_multishop_shipping_methods_description` (
 `id` int(4) NOT NULL default '0',
 `language_id` int(5) NOT NULL default '0',
 `name` varchar(255) default '',
 `description` text,
 PRIMARY KEY (`id`,`language_id`),
 KEY `name` (`name`),
 KEY `id` (`id`),
 KEY `language_id` (`language_id`),
 KEY `combined_two` (`language_id`,`id`)
);

CREATE TABLE `tx_multishop_shipping_options` (
 `id` int(4) NOT NULL auto_increment,
 `name` varchar(50) default '',
 `price` decimal(24,14) default '0.00000000000000',
 `date` int(11) default '0',
 `status` tinyint(1) default '1',
 PRIMARY KEY (`id`),
 KEY `status` (`status`)
);

CREATE TABLE `tx_multishop_specials` (
 `specials_id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `specials_new_products_price` decimal(24,14) default '0.00000000000000',
 `specials_date_added` int(11) default '0',
 `specials_last_modified` int(11) default '0',
 `start_date` int(11) default '0',
 `expires_date` int(11) default '0',
 `date_status_change` int(11) default '0',
 `status` int(1) default '1',
 `news_item` tinyint(1) default '0',
 `home_item` tinyint(1) default '0',
 `scroll_item` tinyint(1) default '0',
 `sort_order` int(11) default '0',
 `staffel_price` varchar(250) default '',
 `specials_stock` int(3) default '0',
 `specials_vanaf_price` decimal(24,14) default '0.00000000000000',
 `page_uid` int(11) default '0',
 PRIMARY KEY (`specials_id`),
 KEY `products_id` (`products_id`),
 KEY `status` (`status`),
 KEY `expires_date` (`expires_date`),
 KEY `home_item` (`home_item`),
 KEY `news_item` (`news_item`),
 KEY `scroll_item` (`scroll_item`),
 KEY `sort_order` (`sort_order`),
 KEY `page_uid` (`page_uid`),
 KEY `start_date` (`start_date`)
);

CREATE TABLE `tx_multishop_specials_sections` (
 `id` int(11) NOT NULL auto_increment,
 `specials_id` int(11) default '0',
 `date` int(11) default '0',
 `name` varchar(30) default '',
 `sort_order` int(11) default '0',
 `status` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `date` (`date`),
 KEY `specials_id` (`specials_id`),
 KEY `name` (`name`),
 KEY `sort_order` (`sort_order`),
 KEY `status` (`status`)
);

CREATE TABLE `tx_multishop_stores` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(150) default '',
 `email` varchar(150) default '',
 `www` varchar(150) default '',
 `crdate` int(11) default '0',
 `address` varchar(150) default '',
 `city` varchar(60) default '',
 `zip` varchar(15) default '',
 `country` varchar(60) default '',
 `telephone` varchar(20) default '',
 `fax` varchar(20) default '',
 `status` tinyint(1) default '0',
 `lng` double(18,14) default '0.00000000000000',
 `lat` double(18,14) default '0.00000000000000',
 PRIMARY KEY (`id`),
 KEY `lat` (`lat`,`lng`),
 KEY `status` (`status`)
);

CREATE TABLE `tx_multishop_stores_description` (
 `id` int(11) NOT NULL auto_increment,
 `language_id` int(5) NOT NULL default '0',
 `description` text,
 PRIMARY KEY (`id`,`language_id`),
 KEY `language_id` (`language_id`)
);

CREATE TABLE `tx_multishop_taxes` (
 `tax_id` int(4) NOT NULL auto_increment,
 `name` varchar(50) default '',
 `rate` decimal(6,4) default '0.0000',
 `status` tinyint(1) default '1',
 PRIMARY KEY (`tax_id`)
);

CREATE TABLE `tx_multishop_tax_rules` (
 `rule_id` int(11) NOT NULL auto_increment,
 `status` tinyint(1) default '1',
 `rules_group_id` int(11) default '0',
 `cn_iso_nr` int(11) default '0',
 `zn_country_iso_nr` int(11) default '0',
 `tax_id` int(11) default '0',
 `state_modus` tinyint(1) default '0',
 `county_modus` tinyint(1) default '0',
 `country_tax_id` int(11) default '0',
 PRIMARY KEY (`rule_id`)
);

CREATE TABLE `tx_multishop_tax_rule_groups` (
 `rules_group_id` int(11) NOT NULL auto_increment,
 `name` varchar(50) default '',
 `status` tinyint(1) default '1',
 PRIMARY KEY (`rules_group_id`)
);

CREATE TABLE `tx_multishop_undo_products` (
 `id` int(11) NOT NULL auto_increment,
 `crdate` int(11) default '0',
 `products_id` int(11) default '0',
 `products_quantity` int(4) default '0',
 `products_model` varchar(128) default '',
 `products_image` varchar(250) default '',
 `products_image1` varchar(250) default '',
 `products_image2` varchar(250) default '',
 `products_image3` varchar(250) default '',
 `products_image4` varchar(250) default '',
 `products_price` decimal(24,14) default '0.00000000000000',
 `products_date_added` int(11) default '0',
 `products_last_modified` int(11) default '0',
 `products_date_available` int(11) default '0',
 `products_weight` decimal(5,2) default '0.00',
 `products_status` tinyint(1) default '0',
 `tax_id` int(5) default '0',
 `manufacturers_id` int(5) default '0',
 `products_pdf` varchar(250) default '',
 `products_pdf1` varchar(250) default '',
 `products_pdf2` varchar(250) default '',
 `sort_order` int(11) default '0',
 `extid` varchar(32) default '',
 `staffel_price` varchar(250) default '',
 `pid` int(11) default '0',
 `did` int(11) default '0',
 `product_capital_price` decimal(24,14) default '0.00000000000000',
 `productfeed` tinyint(1) default '0',
 `vendor_code` varchar(255) default '',
 `ean_code` varchar(13) default '',
 `sku_code` varchar(25) default '',
 `page_uid` int(11) default '0',
 `contains_image` tinyint(1) default '0',
 `custom_settings` text,
 `products_multiplication` int(11) default '0',
 `minimum_quantity` decimal(6,2) default '1.00',
 `maximum_quantity` decimal(6,2) default NULL,
 PRIMARY KEY (`id`)
);

CREATE TABLE `tx_multishop_zones` (
 `id` int(4) NOT NULL auto_increment,
 `name` varchar(50) default '',
 PRIMARY KEY (`id`)
);

CREATE TABLE tt_address (
 uid int(11) unsigned default '0' NOT NULL auto_increment,
 pid int(11) unsigned default '0',
 tstamp int(11) unsigned default '0',
 hidden tinyint(4) unsigned default '0',
 name tinytext,
 gender varchar(1) default '',
 first_name tinytext,
 middle_name tinytext,
 last_name tinytext,
 birthday int(11) default '0',
 title varchar(40) default '',
 email varchar(80) default '',
 phone varchar(30) default '',
 mobile varchar(30) default '',
 www varchar(80) default '',
 address tinytext,
 building varchar(20) default '',
 room varchar(15) default '',
 company varchar(80) default '',
 city varchar(80) default '',
 zip varchar(20) default '',
 region varchar(100) default '',
 country varchar(100) default '',
 image tinyblob,
 fax varchar(30) default '',
 deleted tinyint(3) default '0',
 description text,
 addressgroup int(11) default '0',
 address_number varchar(10) default '',
 tx_multishop_customer_id int(11) default '0',
 tx_multishop_default tinyint(1) default '0',
 address_ext varchar(10) default '',
 page_uid int(11) default '0',
 street_name varchar(75) default '',
 tx_multishop_address_type varchar(9) default 'billing',
 PRIMARY KEY (uid),
 KEY parent (pid),
 KEY pid (pid,email),
 KEY tx_multishop_customer_id (tx_multishop_customer_id),
 KEY tx_multishop_default (tx_multishop_default),
 KEY `page_uid` (`page_uid`)
);

CREATE TABLE `tx_multishop_products_locked_fields` (
 `id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `field_key` varchar(50) default '',
 `crdate` int(11) default '0',
 `cruser_id` int(11) default '0',
 `original_value` text,
 PRIMARY KEY (`id`),
 KEY cruser_id (cruser_id),
 KEY crdate (crdate),
 KEY field_key (field_key),
 KEY products_id (products_id)
);

CREATE TABLE `tx_multishop_attributes_options_groups` (
 `attributes_options_groups_id` int(11) NOT NULL default '0',
 `language_id` int(5) NOT NULL default '0',
 `attributes_options_groups_name` varchar(64) default '',
 `sort_order` int(11) default '0',
 PRIMARY KEY (attributes_options_groups_id,language_id),
 KEY `attributes_options_groups_name` (`attributes_options_groups_name`),
 KEY `attributes_options_groups_id` (`attributes_options_groups_id`),
 KEY `sort_order` (`sort_order`)
);

CREATE TABLE `tx_multishop_attributes_options_groups_to_products_options` (
 `attributes_options_groups_to_products_options_id` int(11) NOT NULL auto_increment,
 `attributes_options_groups_id` int(11) default '0',
 `products_options_id` int(11) default '0',
 PRIMARY KEY (`attributes_options_groups_to_products_options_id`)
);

CREATE TABLE `tx_multishop_shipping_methods_to_zones` (
 `id` int(11) NOT NULL auto_increment,
 `zone_id` int(4) default '0',
 `shipping_method_id` int(11) default '0',
 `sort_order` int(11) default '0',
 PRIMARY KEY (`id`),
 UNIQUE unique_zone_id (zone_id,shipping_method_id),
 KEY `sort_order` (`sort_order`)
);

CREATE TABLE `tx_multishop_feeds_excludelist` (
 `id` int(11) NOT NULL auto_increment,
 `feed_id` int(11) default '0',
 `exclude_id` int(11) default '0',
 `exclude_type` varchar(11) default 'categories',
 PRIMARY KEY (`id`),
 KEY `feed_id` (`feed_id`),
 KEY `exclude_id` (`exclude_id`),
 KEY `exclude_type` (`exclude_type`)
);

CREATE TABLE `tx_multishop_feeds_stock_excludelist` (
 `id` int(11) NOT NULL auto_increment,
 `feed_id` int(11) default '0',
 `exclude_id` int(11) default '0',
 `exclude_type` varchar(11) default 'categories',
 PRIMARY KEY (`id`),
 KEY `feed_id` (`feed_id`),
 KEY `exclude_id` (`exclude_id`),
 KEY `exclude_type` (`exclude_type`)
);

CREATE TABLE `tx_multishop_sessions` (
 `id` int(11) NOT NULL auto_increment,
 `customer_id` int(11) default '0',
 `crdate` int(11) default '0',
 `session_id` varchar(150) default '',
 `page_uid` int(11) default '0',
 `ip_address` varchar(150) default '',
 `http_host` varchar(150) default '',
 `query_string` text,
 `http_user_agent` text,
 `http_referer` text,
 `url` text,
 `segment_type` varchar(50) default '',
 `segment_id` varchar(50) default '',
 PRIMARY KEY (`id`),
 KEY `customer_id` (`customer_id`),
 KEY `crdate` (`crdate`),
 KEY `page_uid` (`page_uid`),
 KEY `session_id` (`session_id`),
 KEY `ip_address` (`ip_address`),
 KEY `http_host` (`http_host`),
 KEY `segment_type` (`segment_type`),
 KEY `segment_id` (`segment_id`)
);

CREATE TABLE `tx_multishop_product_crop_image_coordinate` (
 `id` int(11) NOT NULL auto_increment,
 `products_id` int(11) default '0',
 `image_filename` varchar(255) default '',
 `image_size` varchar(10) default '',
 `coordinate_x` int(11) default '0',
 `coordinate_y` int(11) default '0',
 `coordinate_w` int(11) default '0',
 `coordinate_h` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `products_id` (`products_id`),
 KEY `image_filename` (`image_filename`),
 KEY `image_size` (`image_size`),
 KEY `coordinate_x` (`coordinate_x`),
 KEY `coordinate_y` (`coordinate_y`),
 KEY `coordinate_w` (`coordinate_w`),
 KEY `coordinate_h` (`coordinate_h`)
);

CREATE TABLE `tx_multishop_categories_crop_image_coordinate` (
 `id` int(11) NOT NULL auto_increment,
 `categories_id` int(11) default '0',
 `image_filename` varchar(255) default '',
 `image_size` varchar(10) default '',
 `coordinate_x` int(11) default '0',
 `coordinate_y` int(11) default '0',
 `coordinate_w` int(11) default '0',
 `coordinate_h` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `categories_id` (`categories_id`),
 KEY `image_filename` (`image_filename`),
 KEY `image_size` (`image_size`),
 KEY `coordinate_x` (`coordinate_x`),
 KEY `coordinate_y` (`coordinate_y`),
 KEY `coordinate_w` (`coordinate_w`),
 KEY `coordinate_h` (`coordinate_h`)
);

CREATE TABLE `tx_multishop_manufacturers_crop_image_coordinate` (
 `id` int(11) NOT NULL auto_increment,
 `manufacturers_id` int(11) default '0',
 `image_filename` varchar(255) default '',
 `image_size` varchar(10) default '',
 `coordinate_x` int(11) default '0',
 `coordinate_y` int(11) default '0',
 `coordinate_w` int(11) default '0',
 `coordinate_h` int(11) default '0',
 PRIMARY KEY (`id`),
 KEY `manufacturers_id` (`manufacturers_id`),
 KEY `image_filename` (`image_filename`),
 KEY `image_size` (`image_size`),
 KEY `coordinate_x` (`coordinate_x`),
 KEY `coordinate_y` (`coordinate_y`),
 KEY `coordinate_w` (`coordinate_w`),
 KEY `coordinate_h` (`coordinate_h`)
);

CREATE TABLE `tx_multishop_customers_export` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(75) default '',
 `page_uid` int(11) default '0',
 `crdate` int(11) default '0',
 `fields` text,
 `post_data` text,
 `code` varchar(150) default '',
 `status` tinyint(1) default '0',
 PRIMARY KEY (`id`),
 KEY `code` (`code`)
);
