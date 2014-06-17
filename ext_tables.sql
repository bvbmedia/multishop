
CREATE TABLE `fe_groups` (
  `uid` int(11) NULL auto_increment,
  `pid` int(11) NULL default '0',
  `tstamp` int(11) NULL default '0',
  `crdate` int(11) NULL default '0',
  `cruser_id` int(11) NULL default '0',
  `title` varchar(50) NULL DEFAULT '',
  `hidden` tinyint(3) NULL default '0',
  `lockToDomain` varchar(50) NULL DEFAULT '',
  `deleted` tinyint(3) NULL default '0',
  `description` text,
  `subgroup` tinytext,
  `TSconfig` text,
  `tx_extbase_type` varchar(255) NULL DEFAULT '',
  `felogin_redirectPid` tinytext,
  `tx_multishop_discount` int(2) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `tx_multishop_discount` (`tx_multishop_discount`)
) ENGINE=InnoDB ;

CREATE TABLE `fe_users` (
  `uid` int(11) NULL auto_increment,
  `pid` int(11) NULL default '0',
  `tstamp` int(11) NULL default '0',
  `username` varchar(50) NULL DEFAULT '',
  `password` varchar(60) DEFAULT '',
  `usergroup` tinytext,
  `disable` tinyint(4) NULL default '0',
  `starttime` int(11) NULL default '0',
  `endtime` int(11) NULL default '0',
  `name` varchar(100) DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `telephone` varchar(20) NULL DEFAULT '',
  `fax` varchar(20) NULL DEFAULT '',
  `email` varchar(80) NULL DEFAULT '',
  `crdate` int(11) NULL default '0',
  `cruser_id` int(11) NULL default '0',
  `lockToDomain` varchar(50) NULL DEFAULT '',
  `deleted` tinyint(3) NULL default '0',
  `uc` blob NULL,
  `title` varchar(40) NULL DEFAULT '',
  `zip` varchar(20) DEFAULT '',
  `city` varchar(50) NULL DEFAULT '',
  `country` varchar(60) DEFAULT '',
  `www` varchar(80) NULL DEFAULT '',
  `company` varchar(80) NULL DEFAULT '',
  `image` tinytext,
  `TSconfig` text,
  `fe_cruser_id` int(10) NULL default '0',
  `lastlogin` int(10) NULL default '0',
  `is_online` int(10) NULL default '0',
  `felogin_redirectPid` tinytext,
  `felogin_forgotHash` varchar(80) DEFAULT '',
  `static_info_country` char(3) NULL DEFAULT '',
  `zone` varchar(45) NULL DEFAULT '',
  `language` char(2) NULL DEFAULT '',
  `gender` varchar(1) DEFAULT '',
  `cnum` varchar(50) NULL DEFAULT '',
  `first_name` varchar(50) NULL DEFAULT '',
  `last_name` varchar(50) NULL DEFAULT '',
  `status` int(11) NULL default '0',
  `date_of_birth` int(11) NULL DEFAULT '0',
  `comments` text NULL,
  `by_invitation` tinyint(4) NULL default '0',
  `module_sys_dmail_html` tinyint(3) NULL default '0',
  `middle_name` varchar(50) NULL DEFAULT '',
  `tx_extbase_type` varchar(255) NULL DEFAULT '',
  `address_number` varchar(150) DEFAULT '',
  `mobile` varchar(150) DEFAULT '',
  `tx_multishop_discount` int(2) DEFAULT '0',
  `tx_multishop_newsletter` tinyint(1) NULL DEFAULT '0',
  `tx_multishop_code` varchar(50) DEFAULT '',
  `tx_multishop_optin_ip` varchar(50) DEFAULT '',
  `tx_multishop_optin_crdate` int(10) NULL default '0',
  `address_ext` varchar(10) NULL DEFAULT '',
  `tx_multishop_source_id` varchar(50) NULL DEFAULT '',
  `page_uid` int(11) NULL DEFAULT '0',
  `street_name` varchar(75) NULL DEFAULT '',
  `http_referer` text NULL,
  `ip_address` text NULL,
  `tx_multishop_vat_id` varchar(127) DEFAULT '',
  `tx_multishop_coc_id` varchar(127) DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`),
  KEY `is_online` (`is_online`),
  KEY `pid` (`pid`,`username`),
  KEY `parent` (`pid`,`username`),
  KEY `tx_multishop_discount` (`tx_multishop_discount`),
  KEY `tx_multishop_newsletter` (`tx_multishop_newsletter`),
  KEY `disable` (`disable`),
  KEY `deleted` (`deleted`),
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
  KEY `coc_id` (`tx_multishop_coc_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_cart_contents` (
  `id` int(11) NULL auto_increment,
  `contents` text,
  `customer_id` int(11) NULL DEFAULT '0',
  `is_checkout` tinyint(1) NULL DEFAULT '0',
  `crdate` int(11) NULL DEFAULT '0',
  `session_id` varchar(150) NULL DEFAULT '',
  `ip_address` varchar(150) NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `crdate` (`crdate`),
  KEY `ip_address` (`ip_address`),
  KEY `is_checkout` (`is_checkout`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_categories` (
  `categories_id` int(5) NULL auto_increment,
  `categories_image` varchar(150) DEFAULT '',
  `parent_id` int(5) NULL DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `date_added` int(11) DEFAULT '0',
  `last_modified` int(11) DEFAULT '0',
  `status` int(1) DEFAULT '1',
  `show_description` tinyint(1) DEFAULT '0',
  `extid` varchar(100) DEFAULT '0',
  `members_only` tinyint(1) DEFAULT '0',
  `shortcut` varchar(75) DEFAULT '',
  `categories_discount` decimal(4,2) DEFAULT '0.00',
  `hide` tinyint(1) DEFAULT '0',
  `categories_extra_cost` decimal(8,2) DEFAULT '0.00',
  `cid` int(11) DEFAULT '0',
  `did` int(11) DEFAULT '0',
  `uncatid` varchar(255) DEFAULT '',
  `categories_shippingcost` decimal(8,2) DEFAULT '0.00',
  `page_uid` int(11) NULL DEFAULT '0',
  `option_attributes` varchar(254) NULL DEFAULT '',
  `categories_url` text,
  `custom_settings` text,
  `google_taxonomy_id` int(11) DEFAULT '0',
  `hashed_id` varchar(32) NULL DEFAULT '',
  `hide_in_menu` TINYINT(1) NULL DEFAULT '0',
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
  KEY `hide_in_menu` (`hide_in_menu`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_categories_description` (
  `categories_id` int(5) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `categories_name` varchar(150) DEFAULT '',
  `shortdescription` text,
  `keywords` varchar(250) DEFAULT '',
  `content` text NULL,
  `content_footer` text NULL,
  `template_file` varchar(150) DEFAULT '',
  `cid` int(11) DEFAULT '0',
  `did` int(11) DEFAULT '0',
  `meta_title` varchar(254) DEFAULT '',
  `meta_description` text NULL,
  `meta_keywords` text NULL,
  PRIMARY KEY (`categories_id`,`language_id`),
  KEY `idx_categories_name` (`categories_name`),
  KEY `categories_id` (`categories_id`),
  KEY `language_id` (`language_id`),
  KEY `combined_one` (`language_id`,`categories_name`),
  KEY `combined_two` (`language_id`,`categories_id`),
  KEY `combined_three` (`language_id`,`categories_id`,`categories_name`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_cms` (
  `id` int(3) NULL auto_increment,
  `status` tinyint(1) NULL DEFAULT '1',
  `html` int(1) NULL DEFAULT '0',
  `type` varchar(254) DEFAULT '',
  `inmenu` int(1) NULL DEFAULT '0',
  `domain_id` tinyint(4) NULL DEFAULT '0',
  `form` tinyint(1) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `url` varchar(150) DEFAULT '',
  `topmenu` tinyint(1) DEFAULT '0',
  `lytebox` tinyint(1) DEFAULT '0',
  `link` varchar(250) DEFAULT '',
  `manufacturers_id` int(11) DEFAULT '0',
  `categories_id` int(11) DEFAULT '0',
  `parent_id` int(11) DEFAULT '0',
  `page_uid` int(11) NULL DEFAULT '0',
  `crdate` int(11) NULL DEFAULT '0',
  `hash` varchar(50) NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `domain_id` (`domain_id`),
  KEY `topmenu` (`topmenu`),
  KEY `inmenu` (`inmenu`),
  KEY `type` (`type`),
  KEY `sort_order` (`sort_order`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_cms_description` (
  `id` int(3) DEFAULT '0',
  `language_id` tinyint(2) NULL DEFAULT '0',
  `name` varchar(150) DEFAULT '',
  `content` text NULL,
  `form_code` text,
  `extra_heading` varchar(127) DEFAULT '',
  `negative_keywords` text,
  `sqlstr` text,
  KEY `pagina` (`name`),
  KEY `id` (`id`),
  KEY `language_id` (`language_id`),
  KEY `negative_keywords` (`negative_keywords`(250))
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_configuration` (
  `id` int(5) NULL auto_increment,
  `configuration_title` varchar(64) NULL DEFAULT '',
  `configuration_key` varchar(64) NULL DEFAULT '',
  `configuration_value` text,
  `description` varchar(255) NULL DEFAULT '',
  `group_id` int(5) NULL DEFAULT '0',
  `sort_order` int(5) DEFAULT '0',
  `last_modified` int(11) DEFAULT '0',
  `date_added` int(11) DEFAULT '0',
  `use_function` varchar(255) DEFAULT '',
  `set_function` varchar(255) DEFAULT '',
  `depend_on_configuration_key` varchar(64) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `configuration_key` (`configuration_key`),
  KEY `configuration_group_id` (`group_id`),
  KEY `sort_order` (`sort_order`),
  KEY `configuration_title` (`configuration_title`),
  KEY `admin_search` (`configuration_title`,`configuration_key`),
  KEY `configuration_value` (`configuration_value`(250))
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_configuration_group` (
  `id` int(5) NULL auto_increment,
  `configuration_title` varchar(64) NULL DEFAULT '',
  `description` varchar(255) NULL DEFAULT '',
  `sort_order` int(5) DEFAULT '0',
  `visible` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`),
  KEY `visible` (`visible`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_configuration_values` (
  `id` int(11) NULL auto_increment,
  `configuration_key` varchar(64) DEFAULT '',
  `page_uid` int(11) DEFAULT '0',
  `configuration_value` text,
  PRIMARY KEY (`id`),
  KEY `configuration_key` (`configuration_key`),
  KEY `page_uid` (`page_uid`),
  KEY `admin_search` (`configuration_key`,`page_uid`),
  KEY `configuration_value` (`configuration_value`(250))
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_countries_to_zones` (
  `id` int(11) NULL auto_increment,
  `zone_id` int(4) DEFAULT '0',
  `cn_iso_nr` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cn_iso_nr` (`cn_iso_nr`),
  UNIQUE KEY `zone_id` (`zone_id`,`cn_iso_nr`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_coupons` (
  `id` int(11) NULL auto_increment,
  `code` varchar(250) DEFAULT '',
  `discount` decimal(5,2) NULL DEFAULT '0.00',
  `status` tinyint(1) NULL DEFAULT '0',
  `startdate` int(11) DEFAULT '0',
  `enddate` int(11) DEFAULT '0',
  `times_used` int(5) DEFAULT '0',
  `crdate` int(11) NULL DEFAULT '0',
  `max_usage` int(11) NULL DEFAULT '0',
  `discount_type` varchar(25) NULL DEFAULT 'percentage',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `startdate` (`startdate`),
  KEY `enddate` (`enddate`),
  KEY `times_used` (`times_used`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_import_jobs` (
  `id` int(11) NULL auto_increment,
  `name` varchar(254) DEFAULT '',
  `period` int(11) DEFAULT '0',
  `last_run` int(11) DEFAULT '0',
  `data` text NULL,
  `status` tinyint(1) DEFAULT '0',
  `page_uid` int(11) DEFAULT '0',
  `categories_id` int(5) DEFAULT '0',
  `code` varchar(32) NULL DEFAULT '',
  `prefix_source_name` varchar(50) DEFAULT '',
  `type` varchar(32) NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `last_run` (`last_run`,`status`,`page_uid`,`categories_id`),
  KEY `code` (`code`),
  KEY `prefix_source_name` (`prefix_source_name`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_invoices` (
  `id` int(11) NULL auto_increment,
  `invoice_id` varchar(255) DEFAULT '',
  `invoice_inc` varchar(11) DEFAULT '',
  `orders_id` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `customer_id` int(11) NULL DEFAULT '0',
  `crdate` int(11) DEFAULT '0',
  `due_date` int(11) NULL DEFAULT '0',
  `reference` varchar(150) NULL DEFAULT '',
  `ordered_by` varchar(50) NULL DEFAULT '',
  `invoice_to` varchar(50) NULL DEFAULT '',
  `payment_condition` varchar(50) NULL DEFAULT '',
  `currency` varchar(5) NULL DEFAULT '',
  `discount` double(10,4) NULL DEFAULT '0.0000',
  `amount` double(10,4) NULL DEFAULT '0.0000',
  `page_uid` int(11) NULL DEFAULT '0',
  `paid` tinyint(1) NULL DEFAULT '0',
  `hash` varchar(50) NULL DEFAULT '0',
  `reversal_invoice` tinyint(1) NULL DEFAULT '0',
  `reversal_related_id` int(11) NULL DEFAULT '0',
  `store_currency` char(3) NULL DEFAULT '',
  `customer_currency` char(3) NULL DEFAULT '',
  `currency_rate` varchar(15) NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `orders_id` (`orders_id`),
  KEY `status` (`status`),
  KEY `invoice_id` (`invoice_id`),
  KEY `date` (`crdate`),
  KEY `payed` (`paid`),
  KEY `hash` (`hash`),
  KEY `customer_id` (`customer_id`),
  KEY `reversal_invoice` (`reversal_invoice`),
  KEY `reversal_related_id` (`reversal_related_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_manufacturers` (
  `manufacturers_id` int(5) NULL auto_increment,
  `manufacturers_name` varchar(32) NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT '',
  `date_added` int(11) DEFAULT '0',
  `last_modified` int(11) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `extid` varchar(100) DEFAULT '',
  `icecat_mid` int(5) DEFAULT '0',
  `manufacturers_extra_cost` decimal(24,14) DEFAULT '0.00000000000000',
  `status` tinyint(1) NULL DEFAULT '1',
  PRIMARY KEY (`manufacturers_id`),
  KEY `IDX_MANUFACTURERS_NAME` (`manufacturers_name`),
  KEY `sort_order` (`sort_order`),
  KEY `status` (`status`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_manufacturers_cms` (
  `manufacturers_id` int(11) DEFAULT '0',
  `language_id` tinyint(2) NULL DEFAULT '0',
  `content` text,
  `content_footer` text,
  `shortdescription` text,
  `negative_keywords` text,
  `meta_title` varchar(254) DEFAULT '',
  `meta_description` text NULL,
  `meta_keywords` text NULL,
  KEY `combined` (`manufacturers_id`,`language_id`),
  KEY `content` (`content`(250)),
  KEY `negative_keywords` (`negative_keywords`(250))
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_manufacturers_info` (
  `manufacturers_id` int(5) NULL DEFAULT '0',
  `language_id` tinyint(2) NULL DEFAULT '0',
  `manufacturers_url` varchar(255) NULL DEFAULT '',
  `url_clicked` int(5) NULL DEFAULT '0',
  `date_last_click` int(11) DEFAULT '0',
  PRIMARY KEY (`manufacturers_id`,`language_id`),
  KEY `languages_id` (`language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_modules` (
  `id` int(6) NULL auto_increment,
  `code` varchar(50) DEFAULT '',
  `name` varchar(254) DEFAULT '',
  `description` text NULL,
  `date` int(11) DEFAULT '0',
  `status` tinyint(1) NULL DEFAULT '0',
  `category` varchar(254) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`date`,`status`),
  KEY `category` (`category`),
  KEY `code` (`code`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_notification` (
  `id` int(11) NULL auto_increment,
  `title` varchar(150) DEFAULT '',
  `message` text,
  `customer_id` int(11) NULL DEFAULT '0',
  `unread` tinyint(1) NULL DEFAULT '0',
  `message_type` varchar(35) DEFAULT '',
  `crdate` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `unread` (`unread`),
  KEY `customer_id` (`customer_id`),
  KEY `message_type` (`message_type`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_orders` (
  `orders_id` int(11) NULL auto_increment,
  `customer_id` int(11) DEFAULT '0',
  `page_uid` int(11) NULL DEFAULT '0',
  `billing_first_name` varchar(150) DEFAULT '',
  `billing_middle_name` varchar(150) DEFAULT '',
  `billing_last_name` varchar(150) DEFAULT '',
  `billing_company` varchar(150) DEFAULT '',
  `billing_name` varchar(150) DEFAULT '',
  `billing_address` varchar(150) DEFAULT '',
  `billing_building` varchar(150) DEFAULT '',
  `billing_room` varchar(150) DEFAULT '',
  `billing_city` varchar(150) DEFAULT '',
  `billing_zip` varchar(150) DEFAULT '',
  `billing_region` varchar(150) DEFAULT '',
  `billing_country` varchar(150) DEFAULT '',
  `billing_telephone` varchar(150) DEFAULT '',
  `billing_mobile` varchar(150) DEFAULT '',
  `billing_fax` varchar(150) DEFAULT '',
  `billing_vat_id` varchar(150) DEFAULT '',
  `delivery_first_name` varchar(150) DEFAULT '',
  `delivery_middle_name` varchar(150) DEFAULT '',
  `delivery_last_name` varchar(150) DEFAULT '',
  `delivery_company` varchar(150) DEFAULT '',
  `delivery_name` varchar(150) DEFAULT '',
  `delivery_address` varchar(150) DEFAULT '',
  `delivery_building` varchar(150) DEFAULT '',
  `delivery_room` varchar(150) DEFAULT '',
  `delivery_city` varchar(150) DEFAULT '',
  `delivery_zip` varchar(150) DEFAULT '',
  `delivery_region` varchar(150) DEFAULT '',
  `delivery_country` varchar(150) DEFAULT '',
  `delivery_telephone` varchar(150) DEFAULT '',
  `delivery_mobile` varchar(150) DEFAULT '',
  `delivery_fax` varchar(150) DEFAULT '',
  `delivery_vat_id` varchar(150) DEFAULT '',
  `status` int(3) NULL DEFAULT '1',
  `crdate` int(11) DEFAULT '0',
  `ordercreated` tinyint(1) NULL DEFAULT '0',
  `bill` tinyint(1) NULL DEFAULT '1',
  `html` text,
  `mailed` tinyint(1) DEFAULT '0',
  `ignore_subscription_orders` tinyint(1) NULL DEFAULT '0',
  `shipping_method` varchar(254) DEFAULT '',
  `shipping_method_costs` decimal(24,14) DEFAULT '0.00000000000000',
  `payment_method` varchar(254) DEFAULT '',
  `payment_method_costs` decimal(24,14) DEFAULT '0.00000000000000',
  `order_memo` text NULL,
  `paid` tinyint(1) NULL DEFAULT '0',
  `billing_gender` char(1) DEFAULT '',
  `billing_birthday` int(11) DEFAULT '0',
  `billing_email` varchar(254) DEFAULT '',
  `delivery_gender` char(1) DEFAULT '',
  `delivery_email` varchar(254) DEFAULT '',
  `by_phone` tinyint(1) NULL DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `billing_address_number` varchar(10) DEFAULT '',
  `delivery_address_number` varchar(10) DEFAULT '',
  `shipping_method_label` varchar(150) DEFAULT '',
  `payment_method_label` varchar(150) DEFAULT '',
  `discount` decimal(24,14) DEFAULT '0.00000000000000',
  `customer_comments` text,
  `is_locked` tinyint(1) NULL DEFAULT '0',
  `billing_address_ext` varchar(10) NULL DEFAULT '',
  `delivery_address_ext` varchar(10) NULL DEFAULT '',
  `hash` varchar(50) NULL DEFAULT '0',
  `cruser_id` int(11) NULL DEFAULT '0',
  `is_proposal` tinyint(1) NULL DEFAULT '0',
  `track_and_trace_code` varchar(50) NULL DEFAULT '',
  `expected_delivery_date` int(11) NULL DEFAULT '0',
  `store_currency` char(3) NULL DEFAULT '',
  `customer_currency` char(3) NULL DEFAULT '',
  `currency_rate` varchar(15) NULL DEFAULT '1',
  `language_id` int(5) NULL DEFAULT '0',
  `orders_tax_data` text,
  `status_last_modified` int(11) NULL DEFAULT '0',
  `reminder_sent` tinyint(1) NULL DEFAULT '0',
  `grand_total` decimal(24,14) DEFAULT '0.00000000000000',
  `billing_street_name` varchar(75) NULL DEFAULT '',
  `delivery_street_name` varchar(75) NULL DEFAULT '',
  `ip_address` varchar(100) NULL DEFAULT '',
  `http_referer` text NULL,
  `user_agent` varchar(255) NULL DEFAULT '',
  PRIMARY KEY (`orders_id`),
  KEY `klanten_id` (`customer_id`),
  KEY `bu` (`page_uid`),
  KEY `status` (`status`),
  KEY `ordercreated` (`ordercreated`),
  KEY `factureren` (`bill`),
  KEY `payed` (`paid`),
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
  KEY `user_agent` (`user_agent`)
) ENGINE=InnoDB  COMMENT='Ordersysteem';

CREATE TABLE `tx_multishop_orders_products` (
  `orders_products_id` int(11) NULL auto_increment,
  `orders_id` int(11) NULL DEFAULT '0',
  `products_id` int(11) DEFAULT '0',
  `project_id` int(5) DEFAULT '0',
  `qty` decimal(8,2) NULL DEFAULT '1.00',
  `products_name` varchar(254) NULL DEFAULT '',
  `products_description` text,
  `products_price` decimal(24,14) DEFAULT '0.00000000000000',
  `final_price` decimal(24,14) DEFAULT '0.00000000000000',
  `products_tax` decimal(8,2) DEFAULT '0.00',
  `comments` varchar(150) NULL DEFAULT '',
  `status` int(3) NULL DEFAULT '1',
  `type` char(1) NULL DEFAULT 'P',
  `bill` tinyint(1) NULL DEFAULT '1',
  `products_model` varchar(250) DEFAULT '',
  `file_label` varchar(250) NULL DEFAULT '',
  `file_location` varchar(250) NULL DEFAULT '',
  `file_downloaded` int(11) NULL DEFAULT '0',
  `file_download_code` varchar(32) NULL DEFAULT '0',
  `file_locked` tinyint(1) NULL DEFAULT '0',
  `qty_delivered` int(4) NULL DEFAULT '0',
  `qty_not_deliverable` int(4) NULL DEFAULT '0',
  `file_remote_location` text NULL,
  `file_number_of_downloads` int(11) NULL DEFAULT '0',
  `products_tax_data` text NULL,
  `order_unit_id` int(11) NULL DEFAULT '0',
  `order_unit_name` varchar(100) NULL DEFAULT '',
  `order_unit_code` varchar(15) NULL DEFAULT '',
  `categories_id` int(11) NULL DEFAULT '0',
  `manufacturers_id` int(11) NULL DEFAULT '0',
  `categories_id_0` int(5) NULL DEFAULT '0',
  `categories_name_0` varchar(150) NULL DEFAULT '',
  `categories_id_1` int(5) NULL DEFAULT '0',
  `categories_name_1` varchar(150) NULL DEFAULT '',
  `categories_id_2` int(5) NULL DEFAULT '0',
  `categories_name_2` varchar(150) NULL DEFAULT '',
  `categories_id_3` int(5) NULL DEFAULT '0',
  `categories_name_3` varchar(150) NULL DEFAULT '',
  `categories_id_4` int(5) NULL DEFAULT '0',
  `categories_name_4` varchar(150) NULL DEFAULT '',
  `categories_id_5` int(5) NULL DEFAULT '0',
  `categories_name_5` varchar(150) NULL DEFAULT '',
  `ean_code`    VARCHAR(50) NULL DEFAULT '',
  `sku_code`    VARCHAR(50) NULL DEFAULT '',
  `vendor_code` VARCHAR(50) NULL DEFAULT '',
  PRIMARY KEY (`orders_products_id`),
  KEY `orders_id` (`orders_id`),
  KEY `type` (`type`),
  KEY `projecten_id` (`project_id`),
  KEY `factureren` (`bill`),
  KEY `products_name` (`products_name`),
  KEY `file_download_code` (`file_download_code`),
  KEY `order_unit_id` (`order_unit_id`),
  KEY `categories_id` (`categories_id`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `ean_code` (`ean_code`),
  KEY `sku_code` (`sku_code`),
  KEY `vendor_code` (`vendor_code`)
) ENGINE=InnoDB  COMMENT='Orderregels';

CREATE TABLE `tx_multishop_orders_products_attributes` (
  `orders_products_attributes_id` int(11) NULL auto_increment,
  `orders_id` int(11) DEFAULT '0',
  `orders_products_id` int(11) DEFAULT '0',
  `products_options` varchar(250) DEFAULT '',
  `products_options_values` varchar(250) DEFAULT '',
  `options_values_price` decimal(24,14) DEFAULT '0.00000000000000',
  `price_prefix` char(1) NULL DEFAULT '',
  `attributes_values` text,
  `products_options_id` int(11) NULL DEFAULT '0',
  `products_options_values_id` int(11) NULL DEFAULT '0',
  `attributes_tax_data` text,
  PRIMARY KEY (`orders_products_attributes_id`),
  KEY `orders_id` (`orders_id`),
  KEY `orders_id_2` (`orders_id`),
  KEY `orders_products_id` (`orders_products_id`)
) ENGINE=MyISAM ;

CREATE TABLE `tx_multishop_orders_products_downloads` (
  `orders_products_id` int(11) NULL default '0',
  `orders_id` int(11) NULL default '0',
  `ip_address` varchar(255) NULL default '',
  `date_of_download` int(11) NULL default '0',
  PRIMARY KEY (`orders_products_id`),
  KEY `date_of_download` (`date_of_download`),
  KEY `orders_id` (`orders_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_orders_status` (
  `id` int(3) NULL auto_increment,
  `deleted` tinyint(1) NULL DEFAULT '0',
  `crdate` int(11) NULL DEFAULT '0',
  `default_status` tinyint(1) NULL DEFAULT '0',
  `page_uid` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `default_status` (`default_status`),
  KEY `page_uid` (`page_uid`)
) ENGINE=InnoDB  COMMENT='Order Statussen';

CREATE TABLE `tx_multishop_orders_status_description` (
  `id` int(11) NULL auto_increment,
  `orders_status_id` int(11) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `name` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `orders_status_id` (`orders_status_id`),
  KEY `name` (`name`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_orders_status_history` (
  `orders_status_history_id` int(11) NULL auto_increment,
  `orders_id` int(11) NULL default '0',
  `new_value` int(5) NULL DEFAULT '0',
  `old_value` int(5) DEFAULT '0',
  `crdate` int(11) NULL DEFAULT '0',
  `customer_notified` int(1) DEFAULT '0',
  `comments` TEXT NULL DEFAULT '',
  PRIMARY KEY (`orders_status_history_id`),
  KEY `orders_id` (`orders_id`),
  KEY `crdate` (`crdate`),
  KEY `crdate_2` (`crdate`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_order_units` (
  `id` int(11) NULL auto_increment,
  `code` varchar(15) NULL DEFAULT '',
  `crdate` int(11) NULL DEFAULT '0',
  `page_uid` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `page_uid` (`page_uid`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_order_units_description` (
  `id` int(11) NULL auto_increment,
  `order_unit_id` int(11) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `name` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_unit_id` (`order_unit_id`),
  KEY `name` (`name`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_orphan_files` (
  `id` int(11) NULL auto_increment,
  `type` varchar(50) DEFAULT '',
  `path` text NULL,
  `file` varchar(255) DEFAULT '',
  `orphan` tinyint(1) NULL DEFAULT '0',
  `checked` tinyint(1) NULL DEFAULT '0',
  `crdate` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `crdate` (`crdate`),
  KEY `file` (`file`),
  KEY `orphan` (`orphan`),
  KEY `checked` (`checked`),
  KEY `path` (`path`(250))
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_payment_log` (
  `id` int(11) NULL auto_increment,
  `orders_id` int(11) NULL DEFAULT '0',
  `multishop_transaction_id` varchar(127) DEFAULT '',
  `provider_transaction_id` varchar(127) DEFAULT '',
  `provider` varchar(127) DEFAULT '',
  `ip_address` varchar(127) DEFAULT '',
  `crdate` int(11) DEFAULT '0',
  `title` varchar(127) DEFAULT '',
  `description` text,
  `is_error` tinyint(1) DEFAULT '0',
  `status_type` varchar(127) DEFAULT '',
  `raw_data` mediumtext NULL,
  PRIMARY KEY (`id`),
  KEY `orders_id` (`orders_id`),
  KEY `multishop_transaction_id` (`multishop_transaction_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_payment_methods` (
  `id` int(4) NULL auto_increment,
  `code` varchar(50) DEFAULT '',
  `provider` varchar(50) DEFAULT '',
  `date` int(11) DEFAULT '0',
  `status` tinyint(1) NULL DEFAULT '0',
  `vars` text NULL,
  `handling_costs` decimal(24,14) DEFAULT '0.00000000000000',
  `sort_order` int(11) DEFAULT '0',
  `page_uid` int(11) NULL DEFAULT '0',
  `zone_id` int(11) NULL DEFAULT '0',
  `tax_id` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `isp` (`provider`),
  KEY `date` (`date`),
  KEY `status` (`status`),
  KEY `sort_order` (`sort_order`),
  KEY `page_uid` (`page_uid`,`zone_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_payment_methods_description` (
  `id` int(4) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `name` varchar(255) DEFAULT '',
  `description` text,
  PRIMARY KEY (`id`,`language_id`),
  KEY `name` (`name`),
  KEY `id` (`id`),
  KEY `language_id` (`language_id`),
  KEY `combined_two` (`language_id`,`id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_payment_methods_to_zones` (
  `id` int(11) NULL auto_increment,
  `zone_id` int(4) DEFAULT '0',
  `payment_method_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `zone_id` (`zone_id`,`payment_method_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_payment_shipping_mappings` (
  `id` int(11) NULL auto_increment,
  `payment_method` int(4) DEFAULT '0',
  `shipping_method` int(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_method` (`payment_method`,`shipping_method`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_payment_transactions` (
  `id` int(11) NULL auto_increment,
  `orders_id` int(11) DEFAULT '0',
  `transaction_id` varchar(150) DEFAULT '',
  `psp` varchar(25) DEFAULT '',
  `crdate` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `code` varchar(35) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `orders_id` (`orders_id`,`transaction_id`,`crdate`,`status`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_products` (
  `products_id` int(11) NULL auto_increment,
  `products_quantity` int(4) NULL DEFAULT '0',
  `products_model` varchar(128) DEFAULT '',
  `products_image` varchar(250) DEFAULT '',
  `products_image1` varchar(250) DEFAULT '',
  `products_image2` varchar(250) DEFAULT '',
  `products_image3` varchar(250) DEFAULT '',
  `products_image4` varchar(250) DEFAULT '',
  `products_price` decimal(24,14) DEFAULT '0.00000000000000',
  `products_date_added` int(11) DEFAULT '0',
  `products_last_modified` int(11) DEFAULT '0',
  `products_date_available` int(11) DEFAULT '0',
  `products_weight` decimal(5,2) NULL DEFAULT '0.00',
  `products_status` tinyint(1) NULL DEFAULT '0',
  `tax_id` int(5) NULL DEFAULT '0',
  `manufacturers_id` int(5) DEFAULT '0',
  `products_pdf` varchar(250) DEFAULT '',
  `sort_order` int(11) DEFAULT '0',
  `extid` varchar(32) DEFAULT '',
  `staffel_price` varchar(250) DEFAULT '',
  `product_capital_price` decimal(24,14) DEFAULT '0.00000000000000',
  `vendor_code` varchar(255) DEFAULT '',
  `ean_code` varchar(50) DEFAULT '',
  `page_uid` int(11) NULL DEFAULT '0',
  `contains_image` tinyint(1) DEFAULT '0',
  `custom_settings` text,
  `products_multiplication` int(11) DEFAULT '0',
  `minimum_quantity` int(11) DEFAULT '1',
  `maximum_quantity` int(11) DEFAULT '0',
  `sku_code` varchar(25) NULL DEFAULT '',
  `products_condition` varchar(20) NULL DEFAULT 'new',
  `file_number_of_downloads` int(11) NULL DEFAULT '0',
  `order_unit_id` int(11) NULL DEFAULT '0',
  `imported_product` tinyint(1) NULL DEFAULT '0',
  `foreign_source_name` varchar(30) NULL DEFAULT '',
  `foreign_products_id` varchar(30) NULL DEFAULT '',
  `lock_imported_product` tinyint(1) NULL DEFAULT '0',
  `google_taxonomy_id` int(11) DEFAULT '0',
  `import_job_id` int(11) NULL DEFAULT '0',
  `alert_quantity_threshold` int(11) NULL DEFAULT '0',
  `cruser_id` int(11) NULL default '0',
  `starttime` int(11) NULL default '0',
  `endtime` int(11) NULL default '0',
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
  KEY `products_date_added` (`products_date_added`),
  KEY `products_last_modified` (`products_last_modified`),
  KEY `products_date_available` (`products_date_available`),
  KEY `foreign_source_name` (`foreign_source_name`),
  KEY `foreign_products_id` (`foreign_products_id`)
) ENGINE=MyISAM ;


CREATE TABLE `tx_multishop_products_attributes` (
  `products_attributes_id` int(5) NULL auto_increment,
  `products_id` int(11) NULL DEFAULT '0',
  `options_id` int(5) NULL DEFAULT '0',
  `options_values_id` int(5) NULL DEFAULT '0',
  `options_values_price` decimal(24,14) DEFAULT '0.00000000000000',
  `price_prefix` char(1) NULL DEFAULT '',
  `dealer_price` decimal(24,14) DEFAULT '0.00000000000000',
  `products_stock` mediumint(4) DEFAULT '0',
  `hide` tinyint(1) DEFAULT '0',
  `price_group_id` int(11) DEFAULT '0',
  `attribute_image` varchar(150) DEFAULT '',
  `sort_order_option_name` int(11) DEFAULT '0',
  `sort_order_option_value` int(11) DEFAULT '0',
  PRIMARY KEY (`products_attributes_id`),
  KEY `products_id` (`products_id`),
  KEY `options_id` (`options_id`),
  KEY `options_values_id` (`options_values_id`),
  KEY `sort_order_option_name` (`sort_order_option_name`),
  KEY `sort_order_option_value` (`sort_order_option_value`)
) ENGINE=MyISAM ;

CREATE TABLE `tx_multishop_products_attributes_download` (
  `products_attributes_id` int(5) NULL DEFAULT '0',
  `products_attributes_filename` varchar(255) NULL DEFAULT '',
  `products_attributes_maxdays` int(2) DEFAULT '0',
  `products_attributes_maxcount` int(2) DEFAULT '0',
  PRIMARY KEY (`products_attributes_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_products_description` (
  `products_id` int(11) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `products_name` varchar(255) DEFAULT '',
  `products_description` text,
  `products_url` text,
  `products_viewed` int(5) DEFAULT '0',
  `products_shortdescription` text,
  `products_meta_keywords` varchar(254) NULL DEFAULT '',
  `ppc` tinyint(1) DEFAULT '0',
  `form_code` text,
  `products_negative_keywords` varchar(255) DEFAULT '',
  `promotext` varchar(255) DEFAULT '',
  `products_meta_title` varchar(254) NULL DEFAULT '',
  `products_meta_description` varchar(254) NULL DEFAULT '',
  `file_label` varchar(250) NULL DEFAULT '',
  `file_location` varchar(250) NULL DEFAULT '',
  `delivery_time` varchar(75) DEFAULT '',
  `products_description_tab_content_1` text,
  `products_description_tab_title_1` varchar(50) DEFAULT '',
  `products_description_tab_content_2` text,
  `products_description_tab_title_2` varchar(50) DEFAULT '',
  `products_description_tab_content_3` text,
  `products_description_tab_title_3` varchar(50) DEFAULT '',
  `products_description_tab_content_4` text,
  `products_description_tab_title_4` varchar(50) DEFAULT '',
  `file_remote_location` text NULL,
  PRIMARY KEY (`products_id`,`language_id`),
  KEY `products_name` (`products_name`),
  KEY `products_id` (`products_id`),
  KEY `language_id` (`language_id`),
  KEY `ppc` (`ppc`),
  KEY `combined_one` (`language_id`,`products_name`),
  KEY `combined_two` (`language_id`,`products_id`),
  KEY `combined_three` (`language_id`,`products_id`,`products_name`),
  KEY `combined_seven` (`language_id`,`products_meta_keywords`),
  KEY `products_description` (`products_description`(250)),
  KEY `products_negative_keywords` (`products_negative_keywords`(250)),
  KEY `promotext` (`promotext`)
) ENGINE=MyISAM;

# remove fulltext index cause innodb does not support this
ALTER TABLE tx_multishop_products_description DROP INDEX combined_four;

CREATE TABLE `tx_multishop_products_faq` (
  `products_faq_id` int(11) NULL auto_increment,
  `products_id` int(11) DEFAULT '0',
  `language_id` int(11) DEFAULT '0',
  `question` varchar(255) DEFAULT '',
  `answer` text NULL,
  `sort_order` int(11) DEFAULT '0',
  PRIMARY KEY (`products_faq_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_products_method_mappings` (
  `id` int(11) NULL auto_increment,
  `products_id` int(11) DEFAULT '0',
  `method_id` int(11) DEFAULT '0',
  `type` varchar(25) DEFAULT '',
  `negate` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `negate` (`negate`),
  KEY `products_id` (`products_id`,`method_id`,`type`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_products_options` (
  `products_options_id` int(11) NULL auto_increment,
  `language_id` int(5) NULL DEFAULT '0',
  `products_options_name` varchar(64) DEFAULT '',
  `listtype` varchar(15) DEFAULT 'pulldownmenu',
  `description` text,
  `sort_order` int(11) DEFAULT '0',
  `price_group_id` int(11) DEFAULT '0',
  `hide` tinyint(1) DEFAULT '0',
  `attributes_values` tinyint(1) NULL DEFAULT '0',
  `hide_in_cart` tinyint(1) NULL DEFAULT '0',
  `required` tinyint(1) NULL DEFAULT '0',
  PRIMARY KEY (`products_options_id`,`language_id`),
  KEY `products_options_name` (`products_options_name`),
  KEY `products_options_id` (`products_options_id`),
  KEY `listtype` (`listtype`),
  KEY `sort_order` (`sort_order`),
  KEY `hide_in_cart` (`hide_in_cart`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_products_options_values` (
  `products_options_values_id` int(11) NULL auto_increment,
  `language_id` int(5) NULL DEFAULT '0',
  `products_options_values_name` varchar(64) DEFAULT '',
  `hide` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`products_options_values_id`,`language_id`),
  KEY `products_options_values_id` (`products_options_values_id`),
  KEY `products_options_values_name` (`products_options_values_name`),
  KEY `combined_one` (`language_id`,`products_options_values_name`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_products_options_values_extra` (
  `products_options_values_extra_id` int(11) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `products_options_values_extra_name` varchar(64) NULL DEFAULT '',
  `hide` tinyint(1) DEFAULT '0',
  `sort_orders` int(11) DEFAULT '0',
  PRIMARY KEY (`products_options_values_extra_id`,`language_id`),
  KEY `products_options_values_extra_id` (`products_options_values_extra_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_products_options_values_to_products_options` (
  `products_options_values_to_products_options_id` int(11) NULL auto_increment,
  `products_options_id` int(5) NULL DEFAULT '0',
  `products_options_values_id` int(5) NULL DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  PRIMARY KEY (`products_options_values_to_products_options_id`),
  KEY `products_options_id` (`products_options_id`),
  KEY `products_options_values_id` (`products_options_values_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_products_search_log` (
  `id` int(11) NULL auto_increment,
  `keyword` varchar(150) NULL default '',
  `ip_address` varchar(150) NULL default '',
  `http_host` varchar(60) NULL default '',
  `crdate` int(11) NULL DEFAULT '0',
  `customer_id` int(11) NULL DEFAULT '0',
  `page_uid` int(11) NULL DEFAULT '0',
  `categories_id` int(11) NULL DEFAULT '0',
  `negative_results` tinyint(1) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`),
  KEY `crdate` (`crdate`),
  KEY `http_host` (`http_host`),
  KEY `page_uid` (`page_uid`),
  KEY `categories_id` (`categories_id`),
  KEY `customer_id` (`customer_id`),
  KEY `negative_results` (`negative_results`)
) ENGINE=MyISAM ;

CREATE TABLE `tx_multishop_products_to_categories` (
  `products_id` int(11) NULL DEFAULT '0',
  `categories_id` int(5) NULL DEFAULT '0',
  `sort_order` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`products_id`,`categories_id`),
  KEY `categories_id` (`categories_id`)
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_products_to_extra_options` (
  `id` int(11) NULL auto_increment,
  `products_id` int(11) DEFAULT '0',
  `extra_options_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_products_to_relative_products` (
  `products_to_relative_product_id` int(11) NULL auto_increment,
  `products_id` int(11) NULL DEFAULT '0',
  `relative_product_id` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`products_to_relative_product_id`),
  KEY `products_id` (`products_id`),
  KEY `relative_product_id` (`relative_product_id`),
  KEY `pid_to_relative_id` (`products_id`,`relative_product_id`),
  KEY `relative_to_pid_id` (`relative_product_id`,`products_id`)
) ENGINE=MyISAM;


CREATE TABLE `tx_multishop_product_feeds` (
  `id` int(11) NULL auto_increment,
  `name` varchar(75) DEFAULT '',
  `page_uid` int(11) DEFAULT '0',
  `crdate` int(11) DEFAULT '0',
  `utm_source` varchar(75) DEFAULT '',
  `utm_medium` varchar(75) DEFAULT '',
  `utm_term` varchar(75) DEFAULT '',
  `utm_content` varchar(75) DEFAULT '',
  `utm_campaign` varchar(75) DEFAULT '',
  `fields` text NULL,
  `format` varchar(25) DEFAULT '',
  `delimiter` varchar(10) DEFAULT '',
  `code` varchar(150) DEFAULT '',
  `status` tinyint(1) NULL DEFAULT '0',
  `include_header` tinyint(1) NULL DEFAULT '0',
  `feed_type` varchar(50) NULL DEFAULT '',
  `post_data` text,
  `plain_text` TINYINT(0) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_product_wishlist` (
  `product_wishlist_id` int(11) NULL auto_increment,
  `wishlist_id` int(11) DEFAULT '0',
  `product_id` int(11) DEFAULT '0',
  `ordered` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`product_wishlist_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_reviews` (
  `reviews_id` int(11) NULL auto_increment,
  `products_id` int(11) NULL DEFAULT '0',
  `customers_id` int(5) DEFAULT '0',
  `customers_name` varchar(64) NULL DEFAULT '',
  `reviews_rating` int(1) DEFAULT '0',
  `date_added` int(11) DEFAULT '0',
  `last_modified` int(11) DEFAULT '0',
  `reviews_read` int(5) NULL DEFAULT '0',
  `message` text NULL,
  `language_id` int(2) DEFAULT '0',
  `ipaddress` varchar(127) DEFAULT '',
  `status` tinyint(1) NULL DEFAULT '0',
  PRIMARY KEY (`reviews_id`),
  KEY `products_id` (`products_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_reviews_description` (
  `reviews_id` int(11) NULL DEFAULT '0',
  `language_id` tinyint(2) NULL DEFAULT '0',
  `reviews_text` text NULL,
  PRIMARY KEY (`reviews_id`,`language_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_shipping_countries` (
  `id` int(11) NULL auto_increment,
  `page_uid` int(11) DEFAULT '0',
  `cn_iso_nr` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cn_iso_nr` (`cn_iso_nr`,`page_uid`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_shipping_methods` (
  `id` int(4) NULL auto_increment,
  `code` varchar(50) DEFAULT '',
  `provider` varchar(50) DEFAULT '',
  `date` int(11) DEFAULT '0',
  `status` tinyint(1) NULL DEFAULT '0',
  `handling_costs` decimal(24,14) DEFAULT '0.00000000000000',
  `shipping_costs_type` varchar(25) DEFAULT '',
  `sort_order` int(11) DEFAULT '0',
  `vars` text NULL,
  `page_uid` int(11) NULL DEFAULT '0',
  `zone_id` int(11) NULL DEFAULT '0',
  `tax_id` int(11) NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `date` (`date`),
  KEY `status` (`status`),
  KEY `provider` (`provider`),
  KEY `sort_order` (`sort_order`),
  KEY `page_uid` (`page_uid`,`zone_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_shipping_methods_costs` (
  `id` int(11) NULL auto_increment,
  `shipping_method_id` int(4) DEFAULT '0',
  `zone_id` int(4) DEFAULT '0',
  `price` text NULL,
  PRIMARY KEY (`id`),
  KEY `shipping_method_id` (`shipping_method_id`,`zone_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_shipping_methods_description` (
  `id` int(4) NULL DEFAULT '0',
  `language_id` int(5) NULL DEFAULT '0',
  `name` varchar(255) DEFAULT '',
  `description` text,
  PRIMARY KEY (`id`,`language_id`),
  KEY `name` (`name`),
  KEY `id` (`id`),
  KEY `language_id` (`language_id`),
  KEY `combined_two` (`language_id`,`id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_shipping_options` (
  `id` int(4) NULL auto_increment,
  `name` varchar(50) DEFAULT '',
  `price` decimal(24,14) DEFAULT '0.00000000000000',
  `date` int(11) DEFAULT '0',
  `status` tinyint(1) NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_specials` (
  `specials_id` int(11) NULL auto_increment,
  `products_id` int(11) NULL DEFAULT '0',
  `specials_new_products_price` decimal(24,14) DEFAULT '0.00000000000000',
  `specials_date_added` int(11) DEFAULT '0',
  `specials_last_modified` int(11) DEFAULT '0',
  `start_date` int(11) DEFAULT '0',
  `expires_date` int(11) DEFAULT '0',
  `date_status_change` int(11) DEFAULT '0',
  `status` int(1) DEFAULT '1',
  `news_item` tinyint(1) DEFAULT '0',
  `home_item` tinyint(1) DEFAULT '0',
  `scroll_item` tinyint(1) NULL DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `staffel_price` varchar(250) DEFAULT '',
  `specials_stock` int(3) DEFAULT '0',
  `specials_vanaf_price` decimal(24,14) DEFAULT '0.00000000000000',
  `page_uid` int(11) NULL DEFAULT '0',
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
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_specials_sections` (
  `id` int(11) NULL auto_increment,
  `specials_id` int(11) DEFAULT '0',
  `date` int(11) DEFAULT '0',
  `name` varchar(30) DEFAULT '',
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `specials_id` (`specials_id`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_stores` (
  `id` int(11) NULL auto_increment,
  `name` varchar(150) NULL DEFAULT '',
  `email` varchar(150) NULL DEFAULT '',
  `www` varchar(150) NULL DEFAULT '',
  `crdate` int(11) NULL DEFAULT '0',
  `address` varchar(150) NULL DEFAULT '',
  `city` varchar(60) NULL DEFAULT '',
  `zip` varchar(15) NULL DEFAULT '',
  `country` varchar(60) NULL DEFAULT '',
  `telephone` varchar(20) NULL DEFAULT '',
  `fax` varchar(20) NULL DEFAULT '',
  `status` tinyint(1) NULL DEFAULT '0',
  `lng` double(18,14) NULL DEFAULT '0.00000000000000',
  `lat` double(18,14) NULL DEFAULT '0.00000000000000',
  PRIMARY KEY (`id`),
  KEY `lat` (`lat`,`lng`),
  KEY `status` (`status`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_stores_description` (
  `id` int(11) NULL auto_increment,
  `language_id` int(5) NULL DEFAULT '0',
  `description` text NULL,
  PRIMARY KEY (`id`,`language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_taxes` (
  `tax_id` int(4) NULL auto_increment,
  `name` varchar(50) NULL default '',
  `rate` decimal(6,4) NULL default '0.0000',
  `status` tinyint(1) NULL DEFAULT '1',
  PRIMARY KEY (`tax_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_tax_rules` (
  `rule_id` int(11) NULL auto_increment,
  `status` tinyint(1) NULL DEFAULT '1',
  `rules_group_id` int(11) NULL default '0',
  `cn_iso_nr` int(11) NULL default '0',
  `zn_country_iso_nr` int(11) NULL default '0',
  `tax_id` int(11) NULL default '0',
  `state_modus` tinyint(1) NULL default '0',
  `county_modus` tinyint(1) NULL default '0',
  `country_tax_id` int(11) NULL default '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_tax_rule_groups` (
  `rules_group_id` int(11) NULL auto_increment,
  `name` varchar(50) DEFAULT '',
  `status` tinyint(1) NULL DEFAULT '1',
  PRIMARY KEY (`rules_group_id`)
) ENGINE=InnoDB ;

CREATE TABLE `tx_multishop_undo_products` (
  `id` int(11) NULL auto_increment,
  `crdate` int(11) DEFAULT '0',
  `products_id` int(11) NULL DEFAULT '0',
  `products_quantity` int(4) NULL DEFAULT '0',
  `products_model` varchar(128) DEFAULT '',
  `products_image` varchar(250) DEFAULT '',
  `products_image1` varchar(250) DEFAULT '',
  `products_image2` varchar(250) DEFAULT '',
  `products_image3` varchar(250) DEFAULT '',
  `products_image4` varchar(250) DEFAULT '',
  `products_price` decimal(24,14) DEFAULT '0.00000000000000',
  `products_date_added` int(11) DEFAULT '0',
  `products_last_modified` int(11) DEFAULT '0',
  `products_date_available` int(11) DEFAULT '0',
  `products_weight` decimal(5,2) NULL DEFAULT '0.00',
  `products_status` tinyint(1) NULL DEFAULT '0',
  `tax_id` int(5) NULL DEFAULT '0',
  `manufacturers_id` int(5) DEFAULT '0',
  `products_pdf` varchar(250) DEFAULT '',
  `products_pdf1` varchar(250) DEFAULT '',
  `products_pdf2` varchar(250) DEFAULT '',
  `sort_order` int(11) DEFAULT '0',
  `extid` varchar(32) DEFAULT '',
  `staffel_price` varchar(250) DEFAULT '',
  `pid` int(11) DEFAULT '0',
  `did` int(11) DEFAULT '0',
  `product_capital_price` decimal(24,14) DEFAULT '0.00000000000000',
  `productfeed` tinyint(1) DEFAULT '0',
  `vendor_code` varchar(255) DEFAULT '',
  `ean_code` varchar(13) DEFAULT '',
  `sku_code` varchar(25) DEFAULT '',
  `page_uid` int(11) NULL DEFAULT '0',
  `contains_image` tinyint(1) DEFAULT '0',
  `custom_settings` text,
  `products_multiplication` int(11) DEFAULT '0',
  `minimum_quantity` int(11) DEFAULT '1',
  `maximum_quantity` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_zones` (
  `id` int(4) NULL auto_increment,
  `name` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE tt_address (
  tx_tcdirectmail_bounce int(11) NULL default '0',
  uid int(11) unsigned NULL auto_increment,
  pid int(11) unsigned NULL default '0',
  tstamp int(11) unsigned NULL default '0',
  hidden tinyint(4) unsigned NULL default '0',
  name tinytext NULL,
  gender varchar(1) NULL default '',
  first_name tinytext NULL,
  middle_name tinytext NULL,
  last_name tinytext NULL,
  birthday int(11) NULL default '0',
  title varchar(40) NULL default '',
  email varchar(80) NULL default '',
  phone varchar(30) NULL default '',
  mobile varchar(30) NULL default '',
  www varchar(80) NULL default '',
  address tinytext NULL,
  building varchar(20) NULL default '',
  room varchar(15) NULL default '',
  company varchar(80) NULL default '',
  city varchar(80) NULL default '',
  zip varchar(20) NULL default '',
  region varchar(100) NULL default '',
  country varchar(100) NULL default '',
  image tinyblob NULL,
  fax varchar(30) NULL default '',
  deleted tinyint(3) default '0',
  description text NULL,
  addressgroup int(11) NULL default '0',
  address_number varchar(10) NULL default '',
  tx_multishop_customer_id int(11) NULL default '0',
  tx_multishop_default tinyint(1) NULL default '0',
  address_ext varchar(10) NULL default '',
  page_uid int(11) NULL default '0',
  street_name varchar(75) NULL DEFAULT '',
  tx_multishop_address_type varchar(9) NULL DEFAULT 'billing',
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY pid (pid,email),
  KEY tx_multishop_customer_id (tx_multishop_customer_id),
  KEY tx_multishop_default (tx_multishop_default),
  KEY `page_uid` (`page_uid`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_products_locked_fields` (
  `id` int(11) NULL auto_increment,
  `products_id` int(11) DEFAULT '0',
  `field_key` varchar(50) DEFAULT '',
  `crdate` int(11) NULL default '0',
  `cruser_id` int(11) NULL default '0',
  `original_value` text,
  PRIMARY KEY (`id`),
  KEY cruser_id (cruser_id),  
  KEY crdate (crdate),  
  KEY field_key (field_key),  
  KEY products_id (products_id)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_attributes_options_groups` (
  `attributes_options_groups_id`   INT(11)     NULL AUTO_INCREMENT,
  `language_id`                    INT(5)      NULL DEFAULT '0',
  `attributes_options_groups_name` VARCHAR(64) NULL DEFAULT '',
  `sort_order`                     INT(11)     NULL DEFAULT '0',
  PRIMARY KEY (`attributes_options_groups_id`, `language_id`),
  KEY `attributes_options_groups_name` (`attributes_options_groups_name`),
  KEY `attributes_options_groups_id` (`attributes_options_groups_id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_attributes_options_groups_to_products_options` (
  `attributes_options_groups_to_products_options_id` INT(11) NULL AUTO_INCREMENT,
  `attributes_options_groups_id`                     INT(11) NULL DEFAULT '0',
  `products_options_id`                              INT(11) NULL DEFAULT '0',
  PRIMARY KEY (`attributes_options_groups_to_products_options_id`)
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_shipping_methods_to_zones` (
  `id`                 INT(11) NULL AUTO_INCREMENT,
  `zone_id`            INT(4) DEFAULT '0',
  `shipping_method_id` INT(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `zone_id` (`zone_id`, `shipping_method_id`)
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_feeds_excludelist` (
  `id`           INT(11)     NULL AUTO_INCREMENT,
  `feed_id`      INT(11)     NULL DEFAULT '0',
  `exclude_id`   INT(11)     NULL DEFAULT '0',
  `exclude_type` VARCHAR(11) NULL DEFAULT 'categories',
  PRIMARY KEY (`id`),
  KEY `feed_id` (`feed_id`),
  KEY `exclude_id` (`exclude_id`),
  KEY `exclude_type` (`exclude_type`)
) ENGINE=InnoDB;

CREATE TABLE `tx_multishop_feeds_stock_excludelist` (
  `id`           INT(11)     NULL AUTO_INCREMENT,
  `feed_id`      INT(11)     NULL DEFAULT '0',
  `exclude_id`   INT(11)     NULL DEFAULT '0',
  `exclude_type` VARCHAR(11) NULL DEFAULT 'categories',
  PRIMARY KEY (`id`),
  KEY `feed_id` (`feed_id`),
  KEY `exclude_id` (`exclude_id`),
  KEY `exclude_type` (`exclude_type`)
) ENGINE=InnoDB;
