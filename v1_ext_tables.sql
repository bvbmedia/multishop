# TYPO3 Extension Manager dump 1.1
#
# Host: localhost    Database: subtest
#--------------------------------------------------------

# renaming
RENAME TABLE `tx_multishop_shipping_zones` TO `tx_multishop_zones`
RENAME TABLE `tx_multishop_shipping_countries_to_zones` TO `tx_multishop_countries_to_zones`

#
# Table structure for table "tx_multishop_products_method_mappings"
#
CREATE TABLE tx_multishop_products_method_mappings (
  id int(11) NOT NULL auto_increment,
  products_id int(11) default '0',
  method_id int(11) default '0',
  type varchar(25) default '',
  negate tinyint(1) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY products_id (products_id,method_id,type),
  KEY negate (negate)
) ENGINE=MyISAM;


#
# Table structure for table "fe_groups"
#
CREATE TABLE fe_groups (
  tx_multishop_discount int(2) default '0',
  KEY tx_multishop_discount (tx_multishop_discount)
) ENGINE=MyISAM;


#
# Table structure for table "fe_users"
#
CREATE TABLE fe_users (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL default '0',
  tstamp int(11) unsigned NOT NULL default '0',
  username varchar(50) default '',
  password varchar(60) default '',
  usergroup tinytext,
  disable tinyint(4) unsigned NOT NULL default '0',
  starttime int(11) unsigned NOT NULL default '0',
  endtime int(11) unsigned NOT NULL default '0',
  name varchar(100) default '',
  address varchar(255) default '',
  telephone varchar(20) default '',
  fax varchar(20) default '',
  email varchar(80) default '',
  crdate int(11) unsigned NOT NULL default '0',
  cruser_id int(11) unsigned NOT NULL default '0',
  lockToDomain varchar(50) default '',
  deleted tinyint(3) unsigned NOT NULL default '0',
  uc blob,
  title varchar(40) default '',
  zip varchar(20) default '',
  city varchar(50) default '',
  country varchar(60) default '',
  www varchar(80) default '',
  company varchar(80) default '',
  image tinytext,
  TSconfig text,
  fe_cruser_id int(10) unsigned NOT NULL default '0',
  lastlogin int(10) unsigned NOT NULL default '0',
  is_online int(10) unsigned NOT NULL default '0',
  tx_tcdirectmail_bounce int(11) NOT NULL default '0',
  felogin_redirectPid tinytext,
  felogin_forgotHash varchar(80) default '',
  static_info_country char(3) NOT NULL default '',
  zone varchar(45) NOT NULL default '',
  language char(2) NOT NULL default '',
  gender varchar(1) NOT NULL default '',
  cnum varchar(50) NOT NULL default '',
  first_name varchar(50) default '',
  last_name varchar(50) default '',
  status int(11) unsigned NOT NULL default '0',
  date_of_birth int(11) NOT NULL default '0',
  comments text,
  by_invitation tinyint(4) unsigned NOT NULL default '0',
  module_sys_dmail_html tinyint(3) unsigned NOT NULL default '0',
  middle_name varchar(50) default '',
  tx_extbase_type varchar(255) default '',
  address_number varchar(150) default '',
  mobile varchar(150) default '',
  tx_multishop_discount int(2) default '0',
  tx_multishop_newsletter tinyint(1) NOT NULL default '0',
  tx_multishop_code varchar(50) default '',
  tx_multishop_optin_ip varchar(50) default '',
  tx_multishop_optin_crdate int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (uid),
  KEY username (username),
  KEY is_online (is_online),
  KEY pid (pid,username),
  KEY parent (pid,username),
  KEY tx_multishop_discount (tx_multishop_discount),
  KEY tx_multishop_newsletter (tx_multishop_newsletter),
  KEY tx_multishop_code (tx_multishop_code)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_categories"
#
CREATE TABLE tx_multishop_categories (
  categories_id int(5) NOT NULL auto_increment,
  categories_image varchar(150) default '',
  parent_id int(5) NOT NULL default '0',
  sort_order int(11) default '0',
  date_added int(11) default '0',
  last_modified int(11) default '0',
  status int(1) default '1',
  show_description tinyint(1) default '0',
  extid varchar(100) default '0',
  members_only tinyint(1) default '0',
  shortcut varchar(75) default '',
  categories_discount decimal(4,2) default '0.00',
  hide tinyint(1) default '0',
  cid int(11) default '0',
  page_uid int(11) NOT NULL default '0',
  categories_url text,
  custom_settings text,
  option_attributes varchar(254) NOT NULL default '',
  PRIMARY KEY (categories_id),
  KEY idx_categories_parent_id (parent_id),
  KEY status (status),
  KEY extid (extid),
  KEY sort_order (sort_order),
  KEY members_only (members_only),
  KEY page_uid (page_uid),
  KEY combined_one (page_uid,status),
  KEY combined_two (page_uid,status,categories_id),
  KEY combined_three (page_uid,status,parent_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_categories_description"
#
CREATE TABLE tx_multishop_categories_description (
  categories_id int(5) NOT NULL default '0',
  language_id int(5) NOT NULL default '1',
  categories_name varchar(150) default '',
  shortdescription text,
  keywords varchar(250) default '',
  content text NOT NULL,
  content_footer text NOT NULL,
  template_file varchar(150) default '',
  cid int(11) default '0',
  did int(11) default '0',
  meta_title varchar(254) default '',
  meta_description text NOT NULL,
  meta_keywords text NOT NULL,
  PRIMARY KEY (categories_id,language_id),
  KEY idx_categories_name (categories_name),
  KEY categories_id (categories_id),
  KEY language_id (language_id),
  KEY combined_one (language_id,categories_name),
  KEY combined_two (language_id,categories_id),
  KEY combined_three (language_id,categories_id,categories_name)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_cms"
#
CREATE TABLE tx_multishop_cms (
  id int(3) NOT NULL auto_increment,
  status tinyint(1) NOT NULL default '1',
  html int(1) NOT NULL default '0',
  type varchar(254) default '',
  inmenu int(1) NOT NULL default '0',
  domain_id tinyint(4) NOT NULL default '0',
  form tinyint(1) default '0',
  sort_order int(11) default '0',
  url varchar(150) default '',
  topmenu tinyint(1) default '0',
  lytebox tinyint(1) default '0',
  link varchar(250) default '',
  manufacturers_id int(11) default '0',
  categories_id int(11) default '0',
  parent_id int(11) default '0',
  page_uid int(11) NOT NULL default '0',
  crdate int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY domain_id (domain_id),
  KEY topmenu (topmenu),
  KEY inmenu (inmenu),
  KEY type (type),
  KEY sort_order (sort_order),
  KEY status (status),
  KEY parent_id (parent_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_cms_description"
#
CREATE TABLE tx_multishop_cms_description (
  id int(3) default '0',
  language_id tinyint(2) NOT NULL default '0',
  name varchar(150) default '',
  content text NOT NULL,
  form_code text,
  extra_heading varchar(127) default '',
  negative_keywords text,
  sqlstr text,
  KEY pagina (name),
  KEY id (id),
  KEY language_id (language_id)
) ENGINE=MyISAM;


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


#
# Table structure for table "tx_multishop_configuration_group"
#
CREATE TABLE tx_multishop_configuration_group (
  id int(5) NOT NULL auto_increment,
  configuration_title varchar(64) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  sort_order int(5) default '0',
  visible int(1) default '1',
  PRIMARY KEY (id),
  KEY sort_order (sort_order),
  KEY visible (visible)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_configuration_values"
#
CREATE TABLE tx_multishop_configuration_values (
  id int(11) NOT NULL auto_increment,
  configuration_key varchar(64) default '',
  page_uid int(11) default '0',
  configuration_value text,
  PRIMARY KEY (id),
  KEY configuration_key (configuration_key),
  KEY page_uid (page_uid),
  KEY admin_search (configuration_key,page_uid)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_coupons"
#
CREATE TABLE tx_multishop_coupons (
  id int(11) NOT NULL auto_increment,
  code varchar(250) default '',
  discount decimal(5,2) NOT NULL default '0.00',
  status tinyint(1) NOT NULL default '0',
  startdate int(11) default '0',
  enddate int(11) default '0',
  times_used int(5) default '0',
  crdate int(11) NOT NULL default '0',
  max_usage int(11) NOT NULL default '0',
  discount_type varchar(25) NOT NULL default 'percentage',
  PRIMARY KEY (id),
  UNIQUE code (code),
  KEY status (status),
  KEY startdate (startdate),
  KEY enddate (enddate),
  KEY times_used (times_used)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_import_jobs"
#
CREATE TABLE tx_multishop_import_jobs (
  id int(11) NOT NULL auto_increment,
  name varchar(254) default '',
  period int(11) default '0',
  last_run int(11) default '0',
  data text NOT NULL,
  status tinyint(1) default '0',
  page_uid int(11) default '0',
  categories_id int(5) default '0',
  code varchar(32) default '',
  prefix_source_name varchar(50) default '',
  PRIMARY KEY (id),
  KEY last_run (last_run,status,page_uid,categories_id),
  KEY code (code),
  KEY prefix_source_name (prefix_source_name)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_invoices"
#
CREATE TABLE tx_multishop_invoices (
  id int(11) NOT NULL auto_increment,
  invoice_id varchar(255) default '',
  invoice_inc varchar(11) default '',
  orders_id int(11) default '0',
  status int(11) default '0',
  customer_id int(11) NOT NULL default '0',
  crdate int(11) default '0',
  due_date int(11) NOT NULL default '0',
  reference varchar(150) NOT NULL default '',
  ordered_by varchar(50) NOT NULL default '',
  invoice_to varchar(50) NOT NULL default '',
  payment_condition varchar(50) NOT NULL default '',
  currency varchar(5) NOT NULL default '',
  discount double(10,4) NOT NULL default '0.0000',
  amount double(10,4) NOT NULL default '0.0000',
  page_uid int(11) NOT NULL default '0',
  paid tinyint(1) NOT NULL default '0',
  hash varchar(50) NOT NULL default '0',
  reversal_invoice tinyint(1) NOT NULL default '0',
  reversal_related_id int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY orders_id (orders_id),
  KEY status (status),
  KEY invoice_id (invoice_id),
  KEY date (crdate),
  KEY paid (paid),
  KEY hash (hash),
  KEY customer_id (customer_id),
  KEY reversal_invoice (reversal_invoice),
  KEY reversal_related_id (reversal_related_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_manufacturers"
#
CREATE TABLE tx_multishop_manufacturers (
  manufacturers_id int(5) NOT NULL auto_increment,
  manufacturers_name varchar(32) NOT NULL default '',
  manufacturers_image varchar(64) default '',
  date_added int(11) default '0',
  last_modified int(11) default '0',
  sort_order int(11) default '0',
  extid varchar(100) default '',
  icecat_mid int(5) default '0',
  manufacturers_extra_cost decimal(24,14) default '0.0000',
  status tinyint(1) NOT NULL default '1',
  PRIMARY KEY (manufacturers_id),
  KEY IDX_MANUFACTURERS_NAME (manufacturers_name),
  KEY sort_order (sort_order),
  KEY status (status)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_manufacturers_cms"
#
CREATE TABLE tx_multishop_manufacturers_cms (
  manufacturers_id int(11) default '0',
  language_id tinyint(2) NOT NULL default '0',
  content text,
  shortdescription text,
  negative_keywords text,
  KEY combined (manufacturers_id,language_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_manufacturers_info"
#
CREATE TABLE tx_multishop_manufacturers_info (
  manufacturers_id int(5) NOT NULL default '0',
  language_id tinyint(2) NOT NULL default '0',
  manufacturers_url varchar(255) NOT NULL default '',
  url_clicked int(5) NOT NULL default '0',
  date_last_click int(11) default '0',
  PRIMARY KEY (manufacturers_id,language_id),
  KEY language_id (language_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_modules"
#
CREATE TABLE tx_multishop_modules (
  id int(6) NOT NULL auto_increment,
  code varchar(50) default '',
  name varchar(254) default '',
  description text NOT NULL,
  date int(11) default '0',
  status tinyint(1) NOT NULL default '0',
  category varchar(254) default '',
  PRIMARY KEY (id),
  KEY name (name,date,status),
  KEY category (category),
  KEY code (code)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_orders"
#
CREATE TABLE tx_multishop_orders (
  orders_id int(5) NOT NULL auto_increment,
  customer_id int(5) NOT NULL default '0',
  page_uid int(11) NOT NULL default '0',
  billing_first_name varchar(150) default '',
  billing_middle_name varchar(150) default '',
  billing_last_name varchar(150) default '',
  billing_company varchar(150) default '',
  billing_name varchar(150) default '',
  billing_address varchar(150) default '',
  billing_building varchar(150) default '',
  billing_room varchar(150) default '',
  billing_city varchar(150) default '',
  billing_zip varchar(150) default '',
  billing_region varchar(150) default '',
  billing_country varchar(150) default '',
  billing_telephone varchar(150) default '',
  billing_mobile varchar(150) default '',
  billing_fax varchar(150) default '',
  billing_vat_id varchar(150) default '',
  delivery_first_name varchar(150) default '',
  delivery_middle_name varchar(150) default '',
  delivery_last_name varchar(150) default '',
  delivery_company varchar(150) default '',
  delivery_name varchar(150) default '',
  delivery_address varchar(150) default '',
  delivery_building varchar(150) default '',
  delivery_room varchar(150) default '',
  delivery_city varchar(150) default '',
  delivery_zip varchar(150) default '',
  delivery_region varchar(150) default '',
  delivery_country varchar(150) default '',
  delivery_telephone varchar(150) default '',
  delivery_mobile varchar(150) default '',
  delivery_fax varchar(150) default '',
  delivery_vat_id varchar(150) default '',
  status int(3) NOT NULL default '1',
  crdate int(11) default '0',
  ordercreated tinyint(1) NOT NULL default '0',
  bill tinyint(1) NOT NULL default '1',
  html text,
  mailed tinyint(1) default '0',
  ignore_subscription_orders tinyint(1) NOT NULL default '0',
  shipping_method varchar(254) default '',
  shipping_method_costs decimal(24,14) default '0.0000',
  payment_method varchar(254) default '',
  payment_method_costs decimal(24,14) default '0.0000',
  order_memo text NOT NULL,
  paid tinyint(1) NOT NULL default '0',
  billing_gender char(1) default '',
  billing_birthday int(11) default '0',
  billing_email varchar(254) default '',
  delivery_gender char(1) default '',
  delivery_email varchar(254) default '',
  by_phone tinyint(1) NOT NULL default '0',
  deleted tinyint(1) default '0',
  billing_address_number varchar(10) default '',
  delivery_address_number varchar(10) default '',
  shipping_method_label varchar(150) NOT NULL default '',
  payment_method_label varchar(150) NOT NULL default '',
  discount decimal(24,14) NOT NULL default '0.0000',
  customer_comments text NOT NULL,
  is_locked tinyint(1) NOT NULL default '0',
  hash varchar(50) NOT NULL default '0',
  PRIMARY KEY (orders_id),
  KEY klanten_id (customer_id),
  KEY bu (page_uid),
  KEY status (status),
  KEY ordercreated (ordercreated),
  KEY factureren (bill),
  KEY paid (paid),
  KEY by_phone (by_phone),
  KEY deleted (deleted),
  KEY crdate (crdate),
  KEY hash (hash)  
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_orders_products"
#
CREATE TABLE tx_multishop_orders_products (
  orders_products_id int(11) NOT NULL auto_increment,
  orders_id int(11) NOT NULL default '0',
  products_id int(11) default '0',
  project_id int(5) default '0',
  qty decimal(8,2) NOT NULL default '1.00',
  products_name varchar(254) NOT NULL default '',
  products_description text,
  products_price varchar(15) NOT NULL default '',
  final_price varchar(15) default '',
  products_tax decimal(8,2) default '0.00',
  comments varchar(150) NOT NULL default '',
  status int(3) NOT NULL default '1',
  type char(1) NOT NULL default 'P',
  bill tinyint(1) NOT NULL default '1',
  products_model varchar(128) default '',
  file_label varchar(250) default '',
  file_location varchar(250) default '',
  file_downloaded int(11) default '0',
  file_download_code varchar(32) default '0',
  file_locked tinyint(1) default '0',
  PRIMARY KEY (orders_products_id),
  KEY orders_id (orders_id),
  KEY type (type),
  KEY projecten_id (project_id),
  KEY factureren (bill),
  KEY products_name (products_name),
  KEY file_download_code (file_download_code)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_orders_products_attributes"
#
CREATE TABLE tx_multishop_orders_products_attributes (
  orders_products_attributes_id int(5) NOT NULL auto_increment,
  orders_id int(5) NOT NULL default '0',
  orders_products_id int(5) NOT NULL default '0',
  products_options varchar(64) default '',
  products_options_values varchar(64) default '',
  options_values_price decimal(24,14) NOT NULL default '0.0000',
  price_prefix char(1) NOT NULL default '',
  attributes_values text,  
  products_options_id int(11) NOT NULL default '0',
  products_options_values_id int(11) NOT NULL default '0',  
  PRIMARY KEY (orders_products_attributes_id),
  KEY orders_id (orders_id),
  KEY orders_products_id (orders_products_id)
) ENGINE=MyISAM;


CREATE TABLE tx_multishop_orders_status (
  id int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  default_status tinyint(1) NOT NULL DEFAULT '0',
  page_uid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY default_status (default_status),
  KEY page_uid (page_uid)
) ENGINE=MyISAM;

CREATE TABLE tx_multishop_orders_status_description (
  id int(11) NOT NULL AUTO_INCREMENT,
  orders_status_id int(11) NOT NULL DEFAULT '0',
  language_id int(5) NOT NULL DEFAULT '1',
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY orders_status_id (orders_status_id),
  KEY `name` (`name`),
  KEY language_id (language_id)
) ENGINE=MyISAM;

#
# Table structure for table "tx_multishop_orders_status_history"
#
CREATE TABLE tx_multishop_orders_status_history (
  orders_status_history_id int(5) NOT NULL auto_increment,
  orders_id int(5) NOT NULL default '0',
  new_value int(5) NOT NULL default '0',
  old_value int(5) default '0',
  crdate int(11) NOT NULL default '0',
  customer_notified int(1) default '0',
  comments text NOT NULL,
  PRIMARY KEY (orders_status_history_id),
  KEY orders_id (orders_id),
  KEY crdate (crdate)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_orphan_files"
#
CREATE TABLE tx_multishop_orphan_files (
  id int(11) NOT NULL auto_increment,
  type varchar(50) default '',
  path text NOT NULL,
  file varchar(255) default '',
  orphan tinyint(1) NOT NULL default '0',
  checked tinyint(1) NOT NULL default '0',
  crdate int(11) default '0',
  PRIMARY KEY (id),
  KEY type (type),
  KEY crdate (crdate),
  KEY file (file),
  KEY orphan (orphan),
  KEY checked (checked)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_payment_methods"
#
CREATE TABLE tx_multishop_payment_methods (
  id int(4) NOT NULL auto_increment,
  code varchar(50) default '',
  provider varchar(50) default '',
  date int(11) default '0',
  status tinyint(1) NOT NULL default '0',
  vars text NOT NULL,
  handling_costs varchar(10) default '',
  sort_order int(11) default '0',
  PRIMARY KEY (id),
  KEY code (code),
  KEY isp (provider),
  KEY date (date),
  KEY status (status),
  KEY sort_order (sort_order)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_payment_shipping_mappings"
#
CREATE TABLE tx_multishop_payment_shipping_mappings (
  id int(11) NOT NULL auto_increment,
  payment_method int(4) default '0',
  shipping_method int(4) default '0',
  PRIMARY KEY (id),
  UNIQUE payment_method (payment_method,shipping_method)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_payment_transactions"
#
CREATE TABLE tx_multishop_payment_transactions (
  id int(11) NOT NULL auto_increment,
  orders_id int(11) default '0',
  transaction_id varchar(150) default '',
  psp varchar(25) default '',
  crdate int(11) default '0',
  status tinyint(1) default '0',
  code varchar(35) default '',
  PRIMARY KEY (id),
  KEY orders_id (orders_id,transaction_id,crdate,status)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products"
#
CREATE TABLE tx_multishop_products (
  products_id int(11) NOT NULL auto_increment,
  products_quantity int(4) NOT NULL default '0',
  products_model varchar(128) default '',
  products_image varchar(250) default '',
  products_image1 varchar(250) default '',
  products_image2 varchar(250) default '',
  products_image3 varchar(250) default '',
  products_image4 varchar(250) default '',
  products_price decimal(24,14) NOT NULL default '0.0000',
  products_date_added int(11) default '0',
  products_last_modified int(11) default '0',
  products_date_available int(11) default '0',
  products_weight decimal(5,2) NOT NULL default '0.00',
  products_status tinyint(1) NOT NULL default '0',
  tax_id int(5) NOT NULL default '0',
  manufacturers_id int(5) default '0',
  products_pdf varchar(250) default '',
  products_pdf1 varchar(250) default '',
  products_pdf2 varchar(250) default '',
  sort_order int(11) default '0',
  extid varchar(32) default '',
  staffel_price varchar(250) default '',
  product_capital_price decimal(24,14) default '0.0000',
  vendor_code varchar(255) default '',
  ean_code varchar(13) default '',
  sku_code varchar(25) default '',
  page_uid int(11) NOT NULL default '0',
  contains_image tinyint(1) default '0',
  custom_settings text,
  products_multiplication int(11) default '0',
  minimum_quantity int(11) default '1',
  maximum_quantity int(11) default '0',
  products_condition varchar(20) NOT NULL default 'new',
  PRIMARY KEY (products_id),
  KEY sku_code (sku_code),
  KEY products_price (products_price),
  KEY products_model (products_model),
  KEY products_status (products_status),
  KEY manufacturers_id (manufacturers_id),
  KEY extid (extid),
  KEY page_uid (page_uid),
  KEY combined_one (page_uid,products_status),
  KEY combined_two (page_uid,products_status,products_id),
  KEY combined_three (page_uid,products_status,sort_order),
  KEY combined_four (page_uid,products_status,products_model),
  KEY combined_five (page_uid,products_status,products_date_added),
  KEY combined_six (page_uid,products_status,products_last_modified),
  KEY combined_seven (page_uid,products_status,products_date_available),
  KEY combined_eight (page_uid,products_status,extid),
  KEY combined_nine (page_uid,products_status,products_price),
  KEY products_image (products_image),
  KEY products_image1 (products_image1),
  KEY products_image2 (products_image2),
  KEY products_image3 (products_image3),
  KEY products_image4 (products_image4),
  KEY contains_image (contains_image)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_attributes"
#
CREATE TABLE tx_multishop_products_attributes (
  products_attributes_id int(5) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  options_id int(5) NOT NULL default '0',
  options_values_id int(5) NOT NULL default '0',
  options_values_price decimal(24,14) NOT NULL default '0.0000',
  price_prefix char(1) NOT NULL default '',
  products_stock mediumint(4) default '0',
  hide tinyint(1) default '0',
  attribute_image varchar(150) default '',
  PRIMARY KEY (products_attributes_id),
  KEY products_id (products_id),
  KEY options_id (options_id),
  KEY options_values_id (options_values_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_attributes_download"
#
CREATE TABLE tx_multishop_products_attributes_download (
  products_attributes_id int(5) NOT NULL default '0',
  products_attributes_filename varchar(255) NOT NULL default '',
  products_attributes_maxdays int(2) default '0',
  products_attributes_maxcount int(2) default '0',
  PRIMARY KEY (products_attributes_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_attributes_extra"
#
CREATE TABLE tx_multishop_products_attributes_extra (
  products_attributes_extra_id int(5) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  options_values_extra_id int(5) NOT NULL default '0',
  options_values_price decimal(24,14) NOT NULL default '0.0000',
  price_prefix char(1) NOT NULL default '',
  products_stock mediumint(4) default '0',
  hide tinyint(1) default '0',
  PRIMARY KEY (products_attributes_extra_id),
  KEY products_id (products_id),
  KEY options_values_extra_id (options_values_extra_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_description"
#
CREATE TABLE tx_multishop_products_description (
  products_id int(11) NOT NULL default '0',
  language_id int(5) NOT NULL default '1',
  products_name varchar(255) default '',
  products_description text,
  products_url text,
  products_viewed int(5) default '0',
  products_shortdescription text default '',
  products_meta_keywords varchar(254) NOT NULL default '',
  ppc tinyint(1) default '0',
  form_code text,
  products_negative_keywords varchar(255) default '',
  promotext varchar(255) default '',
  products_meta_title varchar(254) NOT NULL default '',
  products_meta_description varchar(254) NOT NULL default '',
  file_label varchar(250) default '',
  file_location varchar(250) default '',
  delivery_time varchar(75) default '',
  products_description_tab_content_1 text,
  products_description_tab_title_1 varchar(50) default '',
  products_description_tab_content_2 text,
  products_description_tab_title_2 varchar(50) default '',
  products_description_tab_content_3 text,
  products_description_tab_title_3 varchar(50) default '',
  products_description_tab_content_4 text,
  products_description_tab_title_4 varchar(50) default '',  
  PRIMARY KEY (products_id,language_id),
  KEY products_name (products_name),
  KEY products_id (products_id),
  KEY language_id (language_id),
  KEY ppc (ppc),
  KEY combined_one (language_id,products_name),
  KEY combined_two (language_id,products_id),
  KEY combined_three (language_id,products_id,products_name),
  KEY combined_seven (language_id,products_meta_keywords)
) ENGINE=MyISAM;

#  FULLTEXT products_description (products_description),
#  FULLTEXT products_negative_keywords (products_negative_keywords),
#  FULLTEXT promotext (promotext),
#  FULLTEXT combined_four (products_name,products_description)

#
# Table structure for table "tx_multishop_products_faq"
#
CREATE TABLE tx_multishop_products_faq (
  products_faq_id int(11) NOT NULL auto_increment,
  products_id int(11) default '0',
  language_id int(11) default '0',
  question varchar(255) default '',
  answer text NOT NULL,
  sort_order int(11) default '0',
  PRIMARY KEY (products_faq_id)
) ENGINE=MyISAM;

#
# Table structure for table "tx_multishop_products_options"
#
CREATE TABLE tx_multishop_products_options (
  products_options_id int(11) NOT NULL auto_increment,
  language_id int(5) NOT NULL default '1',
  products_options_name varchar(64) default '',
  listtype varchar(15) default 'pulldownmenu',
  description text,
  sort_order int(11) default '0',
  hide tinyint(1) default '0',
  attributes_values tinyint(1) NOT NULL default '0',
  hide_in_cart tinyint(1) NOT NULL default '0',
  PRIMARY KEY (products_options_id,language_id),
  KEY products_options_name (products_options_name),
  KEY products_options_id (products_options_id),
  KEY listtype (listtype),
  KEY sort_order (sort_order),
  KEY hide_in_cart (hide_in_cart)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_options_values"
#
CREATE TABLE tx_multishop_products_options_values (
  products_options_values_id int(11) NOT NULL auto_increment,
  language_id int(5) NOT NULL default '1',
  products_options_values_name varchar(64) default '',
  hide tinyint(1) default '0',
  PRIMARY KEY (products_options_values_id,language_id),
  KEY products_options_values_id (products_options_values_id),
  KEY products_options_values_name (products_options_values_name),
  KEY combined_one (language_id,products_options_values_name)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_options_values_extra"
#
CREATE TABLE tx_multishop_products_options_values_extra (
  products_options_values_extra_id int(11) NOT NULL default '0',
  language_id int(5) NOT NULL default '1',
  products_options_values_extra_name varchar(64) NOT NULL default '',
  hide tinyint(1) default '0',
  sort_orders int(11) default '0',
  PRIMARY KEY (products_options_values_extra_id,language_id),
  KEY products_options_values_extra_id (products_options_values_extra_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_options_values_to_products_options"
#
CREATE TABLE tx_multishop_products_options_values_to_products_options (
  products_options_values_to_products_options_id int(11) NOT NULL auto_increment,
  products_options_id int(5) NOT NULL default '0',
  products_options_values_id int(5) NOT NULL default '0',
  sort_order int(11) NOT NULL default '0',
  PRIMARY KEY (products_options_values_to_products_options_id),
  KEY products_options_id (products_options_id),
  KEY products_options_values_id (products_options_values_id),
  KEY sort_order (sort_order)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_to_categories"
#
CREATE TABLE tx_multishop_products_to_categories (
  products_id int(11) NOT NULL default '0',
  categories_id int(5) NOT NULL default '0',
  sort_order int(11) NOT NULL default '0',
  PRIMARY KEY (products_id,categories_id),
  KEY categories_id (categories_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_to_extra_options"
#
CREATE TABLE tx_multishop_products_to_extra_options (
  id int(11) NOT NULL auto_increment,
  products_id int(11) default '0',
  extra_options_id int(11) default '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_products_to_relative_products"
#
CREATE TABLE tx_multishop_products_to_relative_products (
  products_to_relative_product_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  relative_product_id int(11) NOT NULL default '0',
  PRIMARY KEY (products_to_relative_product_id),
  KEY products_id (products_id),
  KEY relative_product_id (relative_product_id),
  KEY pid_to_relative_id (products_id,relative_product_id),
  KEY relative_to_pid_id (relative_product_id,products_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_product_wishlist"
#
CREATE TABLE tx_multishop_product_wishlist (
  product_wishlist_id int(11) NOT NULL auto_increment,
  wishlist_id int(11) default '0',
  product_id int(11) default '0',
  ordered tinyint(1) default '0',
  PRIMARY KEY (product_wishlist_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_reviews"
#
CREATE TABLE tx_multishop_reviews (
  reviews_id int(11) NOT NULL default '0',
  products_id int(11) NOT NULL default '0',
  customers_id int(5) default '0',
  customers_name varchar(64) NOT NULL default '',
  reviews_rating int(1) default '0',
  date_added int(11) default '0',
  last_modified int(11) default '0',
  reviews_read int(5) NOT NULL default '0',
  PRIMARY KEY (reviews_id),
  KEY products_id (products_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_reviews_description"
#
CREATE TABLE tx_multishop_reviews_description (
  reviews_id int(11) NOT NULL default '0',
  language_id tinyint(2) NOT NULL default '0',
  reviews_text text NOT NULL,
  PRIMARY KEY (reviews_id,language_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_shipping_countries"
#
CREATE TABLE tx_multishop_shipping_countries (
  id int(11) NOT NULL auto_increment,
  page_uid int(11) default '0',
  cn_iso_nr int(11) default '0',
  PRIMARY KEY (id),
  UNIQUE cn_iso_nr (cn_iso_nr,page_uid)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_countries_to_zones"
#
CREATE TABLE tx_multishop_countries_to_zones (
  id int(11) NOT NULL auto_increment,
  zone_id int(4) default '0',
  cn_iso_nr int(11) default '0',
  PRIMARY KEY (id),
  UNIQUE cn_iso_nr (cn_iso_nr),
  UNIQUE zone_id (zone_id,cn_iso_nr)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_shipping_methods"
#
CREATE TABLE tx_multishop_shipping_methods (
  id int(4) NOT NULL auto_increment,
  code varchar(50) default '',
  provider varchar(50) default '',
  date int(11) default '0',
  status tinyint(1) NOT NULL default '0',
  handling_costs decimal(24,14) default '0.0000',
  shipping_costs_type varchar(25) default '',
  sort_order int(11) default '0',
  vars text NOT NULL,
  PRIMARY KEY (id),
  KEY code (code),
  KEY date (date),
  KEY status (status),
  KEY provider (provider),
  KEY sort_order (sort_order)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_shipping_methods_costs"
#
CREATE TABLE tx_multishop_shipping_methods_costs (
  id int(11) NOT NULL auto_increment,
  shipping_method_id int(4) default '0',
  zone_id int(4) default '0',
  price text NOT NULL,
  PRIMARY KEY (id),
  KEY shipping_method_id (shipping_method_id,zone_id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_shipping_options"
#
CREATE TABLE tx_multishop_shipping_options (
  id int(4) NOT NULL auto_increment,
  name varchar(50) default '',
  price decimal(24,14) default '0.0000',
  date int(11) default '0',
  status tinyint(1) NOT NULL default '1',
  PRIMARY KEY (id),
  KEY status (status)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_zones"
#
CREATE TABLE tx_multishop_zones (
  id int(4) NOT NULL auto_increment,
  name varchar(50) default '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_specials"
#
CREATE TABLE tx_multishop_specials (
  specials_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL default '0',
  specials_new_products_price decimal(24,14) NOT NULL default '0.0000',
  specials_date_added int(11) default '0',
  specials_last_modified int(11) default '0',
  start_date int(11) default '0',
  expires_date int(11) default '0',
  date_status_change int(11) default '0',
  status int(1) default '1',
  news_item tinyint(1) default '0',
  home_item tinyint(1) default '0',
  scroll_item tinyint(1) NOT NULL default '0',
  sort_order int(11) default '0',
  staffel_price varchar(250) default '',
  specials_stock int(3) default '0',
  specials_vanaf_price decimal(24,14) default '0.0000',
  page_uid int(11) NOT NULL default '0',
  PRIMARY KEY (specials_id),
  KEY products_id (products_id),
  KEY status (status),
  KEY expires_date (expires_date),
  KEY home_item (home_item),
  KEY news_item (news_item),
  KEY scroll_item (scroll_item),
  KEY sort_order (sort_order),
  KEY page_uid (page_uid),
  KEY combined_one (page_uid,status),
  KEY combined_two (page_uid,status,specials_new_products_price),
  KEY combined_three (page_uid,status,expires_date),
  KEY combined_four (page_uid,status,expires_date,specials_new_products_price),
  KEY start_date (start_date)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_specials_sections"
#
CREATE TABLE tx_multishop_specials_sections (
  id int(11) NOT NULL auto_increment,
  specials_id int(11) default '0',
  date int(11) default '0',
  name varchar(30) default '',
  status tinyint(1) default '0',
  PRIMARY KEY (id),
  KEY date (date),
  KEY specials_id (specials_id),
  KEY name (name),
  KEY status (status)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_undo_products"
#
CREATE TABLE tx_multishop_undo_products (
  id int(11) NOT NULL auto_increment,
  crdate int(11) default '0',
  products_id int(11) NOT NULL default '0',
  products_quantity int(4) NOT NULL default '0',
  products_model varchar(128) default '',
  products_image varchar(250) default '',
  products_image1 varchar(250) default '',
  products_image2 varchar(250) default '',
  products_image3 varchar(250) default '',
  products_image4 varchar(250) default '',
  products_price decimal(24,14) NOT NULL default '0.0000',
  products_date_added int(11) default '0',
  products_last_modified int(11) default '0',
  products_date_available int(11) default '0',
  products_weight decimal(5,2) NOT NULL default '0.00',
  products_status tinyint(1) NOT NULL default '0',
  tax_id int(5) NOT NULL default '0',
  manufacturers_id int(5) default '0',
  products_pdf varchar(250) default '',
  products_pdf1 varchar(250) default '',
  products_pdf2 varchar(250) default '',
  sort_order int(11) default '0',
  extid varchar(32) default '',
  staffel_price varchar(250) default '',
  pid int(11) default '0',
  did int(11) default '0',
  product_capital_price decimal(24,14) default '0.0000',
  productfeed tinyint(1) default '0',
  vendor_code varchar(255) default '',
  ean_code varchar(13) default '',
  sku_code varchar(25) default '',
  page_uid int(11) NOT NULL default '0',
  contains_image tinyint(1) default '0',
  custom_settings text,
  products_multiplication int(11) default '0',
  minimum_quantity int(11) default '1',
  maximum_quantity int(11) default '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_product_feeds"
#
CREATE TABLE tx_multishop_product_feeds (
  id int(11) NOT NULL auto_increment,
  name varchar(75) NOT NULL default '',
  page_uid int(11) NOT NULL default '0',
  crdate int(11) NOT NULL default '0',
  utm_source varchar(75) NOT NULL default '',
  utm_medium varchar(75) NOT NULL default '',
  utm_term varchar(75) NOT NULL default '',
  utm_content varchar(75) NOT NULL default '',
  utm_campaign varchar(75) NOT NULL default '',
  fields text NOT NULL,
  format varchar(25) NOT NULL default '',
  delimiter varchar(10) NOT NULL default '',
  code varchar(150) NOT NULL default '',
  status tinyint(1) NOT NULL default '0',
  include_header tinyint(1) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY code (code)
) ENGINE=MyISAM;



#
# Table structure for table "tx_multishop_payment_methods_description"
#
CREATE TABLE tx_multishop_payment_methods_description (
  id int(4) NOT NULL default '0',
  language_id int(5) NOT NULL default '1',
  name varchar(255) default '',
  description text,
  PRIMARY KEY (id,language_id),
  KEY name (name),
  KEY id (id),
  KEY language_id (language_id),
  KEY combined_two (language_id,id)
) ENGINE=MyISAM;


#
# Table structure for table "tx_multishop_shipping_methods_description"
#
CREATE TABLE tx_multishop_shipping_methods_description (
  id int(4) NOT NULL default '0',
  language_id int(5) NOT NULL default '1',
  name varchar(255) default '',
  description text,
  PRIMARY KEY (id,language_id),
  KEY name (name),
  KEY id (id),
  KEY language_id (language_id),
  KEY combined_two (language_id,id)
) ENGINE=MyISAM;


#
# Table structure for table "tt_address"
#
CREATE TABLE tt_address (
  tx_tcdirectmail_bounce int(11) NOT NULL default '0',
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned NOT NULL default '0',
  tstamp int(11) unsigned NOT NULL default '0',
  hidden tinyint(4) unsigned NOT NULL default '0',
  name tinytext NOT NULL,
  gender varchar(1) NOT NULL default '',
  first_name tinytext NOT NULL,
  middle_name tinytext NOT NULL,
  last_name tinytext NOT NULL,
  birthday int(11) NOT NULL default '0',
  title varchar(40) NOT NULL default '',
  email varchar(80) NOT NULL default '',
  phone varchar(30) NOT NULL default '',
  mobile varchar(30) NOT NULL default '',
  www varchar(80) NOT NULL default '',
  address tinytext NOT NULL,
  building varchar(20) NOT NULL default '',
  room varchar(15) NOT NULL default '',
  company varchar(80) NOT NULL default '',
  city varchar(80) NOT NULL default '',
  zip varchar(20) NOT NULL default '',
  region varchar(100) NOT NULL default '',
  country varchar(100) NOT NULL default '',
  image tinyblob NOT NULL,
  fax varchar(30) NOT NULL default '',
  deleted tinyint(3) default '0',
  description text NOT NULL,
  addressgroup int(11) NOT NULL default '0',
  address_number varchar(10) NOT NULL default '',
  tx_multishop_customer_id int(11) NOT NULL default '0',
  tx_multishop_default tinyint(1) NOT NULL default '0',
  address_ext varchar(10) NOT NULL default '',  
  page_uid int(11) NOT NULL default '0',
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY pid (pid,email),
  KEY tx_multishop_customer_id (tx_multishop_customer_id),
  KEY tx_multishop_default (tx_multishop_default),
  KEY `page_uid` (`page_uid`)  
) ENGINE=MyISAM;

CREATE TABLE `tx_multishop_products_flat` (
  `products_id` int(11) NOT NULL auto_increment,
  `language_id` tinyint(2) NOT NULL default '0',
  `products_name` varchar(255) NOT NULL default '',
  `products_model` varchar(128) NOT NULL default '',
  `products_shortdescription` text NOT NULL default '',
  `products_description` text,
  `products_quantity` int(11) NOT NULL default '0',
  `products_price` decimal(24,14) NOT NULL default '0.0000',
  `staffel_price` varchar(250) NOT NULL default '',
  `ean_code` varchar(13) NOT NULL default '',
  `sku_code` varchar(25) NOT NULL default '',
  `specials_new_products_price` decimal(24,14) NOT NULL default '0.0000',
  `final_price` decimal(24,14) NOT NULL default '0.0000',
  `final_price_difference` decimal(24,14) NOT NULL default '0.0000',
  `products_date_available` int(11) NOT NULL default '0',
  `products_last_modified` int(11) NOT NULL default '0',
  `tax_id` int(5) NOT NULL default '0',
  `tax_rate` decimal(4,3) NOT NULL default '0.000',
  `categories_id` int(5) NOT NULL default '0',
  `categories_name` varchar(150) NOT NULL default '',
  `manufacturers_id` int(5) NOT NULL default '0',
  `manufacturers_name` varchar(32) NOT NULL default '',
  `categories_id_0` int(5) NOT NULL default '0',
  `categories_name_0` varchar(150) NOT NULL default '',
  `categories_id_1` int(5) NOT NULL default '0',
  `categories_name_1` varchar(150) NOT NULL default '',
  `categories_id_2` int(5) NOT NULL default '0',
  `categories_name_2` varchar(150) NOT NULL default '',
  `categories_id_3` int(5) NOT NULL default '0',
  `categories_name_3` varchar(150) NOT NULL default '',
  `categories_id_4` int(5) NOT NULL default '0',
  `categories_name_4` varchar(150) NOT NULL default '',
  `categories_id_5` int(5) NOT NULL default '0',
  `categories_name_5` varchar(150) NOT NULL default '',
  `products_image` varchar(250) NOT NULL default '',
  `products_image1` varchar(250) NOT NULL default '',
  `products_image2` varchar(250) NOT NULL default '',
  `products_image3` varchar(250) NOT NULL default '',
  `products_image4` varchar(250) NOT NULL default '',
  `products_viewed` int(11) NOT NULL default '0',
  `products_date_added` int(11) NOT NULL default '0',
  `products_weight` decimal(5,2) NOT NULL default '0.00',
  `sort_order` int(11) NOT NULL default '0',
  `product_capital_price` decimal(24,14) NOT NULL default '0.0000',
  `page_uid` int(11) NOT NULL default '0',
  `products_extra_description` text,
  `products_negative_keywords` varchar(254) NOT NULL default '',
  `products_meta_title` varchar(254) NOT NULL default '',
  `products_meta_description` varchar(254) NOT NULL default '',
  `products_meta_keywords` varchar(254) NOT NULL default '',
  `products_url` text,
  `sstatus` tinyint(1) NOT NULL default '0',
  `price_filter` int(4) NOT NULL default '0',
  `contains_image` tinyint(1) NOT NULL default '0',
  `products_multiplication` int(11) NOT NULL default '0',
  `minimum_quantity` int(11) NOT NULL default '1',
  `maximum_quantity` int(11) NOT NULL default '0',
  `delivery_time` varchar(75) default '',
  `products_condition` varchar(20) default 'new',
  PRIMARY KEY (`products_id`),
  KEY `language_id` (`language_id`),
  KEY `products_name_2` (`products_name`),
  KEY `products_model` (`products_model`),
  KEY `sstatus` (`sstatus`),
  KEY `final_price_difference` (`final_price_difference`),
  KEY `combined_cat_index` (`categories_id_0`,`categories_id_1`,`categories_id_2`,`categories_id_3`,`categories_id_4`,`categories_id_5`),
  KEY `combined_specials` (`page_uid`,`sstatus`),
  KEY `combined_categories_id` (`page_uid`,`categories_id`),
  KEY `combined_one` (`page_uid`,`categories_id`,`sstatus`),
  KEY `sort_order` (`sort_order`),
  KEY `page_uid` (`page_uid`),
  KEY `categories_id` (`categories_id`),
  KEY `combined_three` (`page_uid`,`categories_id`,`sort_order`),
  KEY `categories_id_0` (`categories_id_0`),
  KEY `categories_id_3` (`categories_id_3`),
  KEY `categories_id_2` (`categories_id_2`),
  KEY `categories_id_1` (`categories_id_1`),
  KEY `categories_id_4` (`categories_id_4`),
  KEY `categories_id_5` (`categories_id_5`),
  KEY `products_date_added` (`products_date_added`),
  KEY `products_last_modified` (`products_last_modified`),
  KEY `final_price` (`final_price`),
  KEY `combined_four` (`sort_order`,`categories_id`),
  KEY `combined_five` (`categories_id_0`,`categories_id_1`,`categories_id_2`,`categories_id_3`,`final_price`),
  KEY `price_filter` (`price_filter`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `contains_image` (`contains_image`),
  KEY `sku_code` (`sku_code`)
) ENGINE=MyISAM;

#  FULLTEXT `products_name` (`products_name`),
#  FULLTEXT `products_model_2` (`products_model`),
#  FULLTEXT `products_model_3` (`products_model`,`products_name`)

CREATE TABLE tx_multishop_order_units (
  id int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL DEFAULT '',
  crdate int(11) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY `code` (`code`)
) ENGINE=MyISAM;

CREATE TABLE tx_multishop_order_units_description (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_unit_id int(11) NOT NULL DEFAULT '0',
  language_id int(5) NOT NULL DEFAULT '1',
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY order_unit_id (order_unit_id),
  KEY `name` (`name`),
  KEY language_id (language_id)
) ENGINE=MyISAM;