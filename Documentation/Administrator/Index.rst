.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _administrator:

=============
Administrator
=============

Installation requirements
=========================
To run Multishop properly on your TYPO3 web site make sure that:

- Your web server is running PHP 5.3.3 (5.4.27 or later is recommended)
- You use MySQL 5 as database server (5.5.0 or later is recommended)
- The PHP memory limit is higher than 256MB. For optimal functionality larger is recommended.
- PHP is compiled with mbstrings
- CURL is enabled, which is required by some payment service providers
- SimpleXML module is enabled for reading XML strings as object
- TYPO3 is updated to 6.2.19 (or newer) / 7.2.2 (or newer)

Multishop is working on the latest PHP 5.6. But you need to suppress the deprecation errors.
You can do this by adding the following configuration to the php.ini file (.htaccess won't work):

- error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

If you run your TYPO3 web site on a shared host and you are not able to adjust the server settings you can try to add the following lines to the .htaccess file that is located in the root folder of the web site:

- php_value max_execution_time 240
- php_value max_input_time 240
- php_value max_input_vars 10000
- php_value memory_limit 256MB
- php_value post_max_size 25M
- php_value upload_max_filesize 25M


jQuery / t3jquery notice
========================
Multishop requires jQuery 2. Also enable the jQuery module: Migrate + all the Bootstrap modules.

Bootstrap 3 required
====================
Multishop expects the website to have Twitter Bootstrap loaded (manually or by using t3jquery). You will have to load the JavaScript library (with i.e. t3jquery) yourself.

Multishop provides bootstrap.css and will load it automatically through TypoScript:

- page.includeCSS.bootstrapCss
- multishop_admin_page.includeCSS.bootstrapCss

GhostScript required
====================
To be able to download multiple invoices/packingslips as one single PDF file the webserver must have GhostScript installed. Make sure the version is decent. We use: GPL Ghostscript 9.07 (2013-02-14).

You can install it by running:

CENTOS:
	yum install gs

Other Linux distributions:
	apt-get install gs



Installation instructions
=========================
TYPO3 Multishop works intensively with jQuery. Because of this we decided to depend Multishop on the great extensions T3jQuery and rzcolorbox (developed by Jürgen Furrer). T3jQuery is providing jQuery. This makes it possible to run many plugins that needs jQuery without having the issue that plugins load the jQuery library multiple times. Also it's possible to update jQuery without having to change Multishop.
Make sure you install and configure the required plugins properly or else Multishop will not work correctly.

Configuring the extension T3jQuery
""""""""""""""""""""""""""""""""""
After you have installed the plugin go to the extension manager and press the plugin T3jQuery to configure it. We suggest to configure it as follows:

- "Always integrate jQuery": enable this checkbox
- "jQuery Version": select 2.0.x
- "jQuery UI Version": select 1.10.x
- "jQuery TOOLS Version": select 1.2.x (or later)

Do not enable the checkbox "Integrate jQuery to footer" because many interfaces of Multishop renders inline JavaScript (most of it is actually attached to the head tag). If you active this checkbox than this code would then be executed before the browser loads the jQuery library.

Finally press Update to save the T3jQuery configuration.

Configuring components T3jQuery
"""""""""""""""""""""""""""""""
Now you have to configure which components of jQuery should be loaded. At the left panel of TYPO3 press on the menu item "T3 jQuery".
On the top right there is a dropdown menu. Change the selection to: "Process & Analyze t3jquery.txt in extensions".
Press "Select all" to select all extensions and then press "Check".

Now the list of jQuery components will be displayed. Browse to the bottom of the page and press "Use".

The required components are now automatically selected. Browse to the bottom of the page and press "Create jQuery Library".

Configuring phpexcel_service to download the phpexcel library
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
When phpexcel_service installed you have to let it download the library. You can do this by opening the Extension manager and press the update icon:

Front-end Login
"""""""""""""""
In our example we have used the page tree that is shipped inside the introduction package of TYPO3. This is a very suitable setup for testing purposes and for explaining how to integrate Multishop to your web site.

Create a new page that will contain the frontend users. Configure this page to use the plugin Website users.

Because Multishop needs to know to which usergroup administration permissions should be granted we need to create a new usergroup. Also we will create a admin user that is member of the created admin usergroup.

Adding the front-end customer, admin and rootadmin usergroups
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
- At the left panel press List
- Click the page Webusers
- Click the + icon to create a new Website Usergroup
- In the "Group Title" field define: Customers
- Click the save with X icon to add the usergroup and go back to the previous screen.
- Repeat the steps to create the usergroup Admin and Rootadmin.

Now hover your mouse pointer over each usergroup to see the id number. Write them down, because you will need to define these numbers later when you configure the constants inside the TYPO3 template.

Adding the front-end admin user
"""""""""""""""""""""""""""""""
At the left panel press List
- Click the page Webusers
- Click the + icon to create a new Website User
- In the "Username" and "Password" field define your desired user credentials
- In the Groups form (Available Items) click the Admin usergroup
- Click the save with X icon to add the usergroup and go back to the previous screen.
- Repeat the steps to also create the rootadmin user, but make this user member of Admin users and Rootadmin users.

Creating the shop tt_address record
"""""""""""""""""""""""""""""""""""
For an accurate working of the tax system, Multishop must know in which country the store is located. To do this you have to create a tt_address record and define the English country name. This record must also be saved in the page where you have stored the admin user.

- At the left panel press List
- Click the page Webusers
- Click the + icon to create a new Address
- Define the name: Store
- Country: your english Country, example: Germany
- Select Multishop address type to: Store address
- The other form fields are optional, but it is good to define them as well (like address, city and region of the store)

Creating the web shop page
""""""""""""""""""""""""""
First we need to create a new page that contains the web shop. This is a page that is a sub page of the root page. In this example we called the page Shop, as you can see in the illustration below. If you hover your mouse on the page name you will see the id number.

Write the number down, because you need to define this number also as constant value inside the TYPO3 template.

Define the web shop page as Multishop core shop
"""""""""""""""""""""""""""""""""""""""""""""""
Since Multishop 3 you also have to define this page as a Multishop core shop page. You can do this by editing the page and select Multishop: core shop in the "contains plugin" form field.

Creating the content element on the web shop page
"""""""""""""""""""""""""""""""""""""""""""""""""
Now that the page is created it's time to add a content element on the middle column that will display the shop. This is called the "Core Shop" content element.

- At the left panel press "Page"
- Click on the page "Shop"
- In the middle column press the icon to create a new content element
- Click the tab "Plugins"
- Choose "Multishop"
- Press the tab "Multishop"

Now you see the Multishop Flexform. On default the form activated the first tab which is "Module". This section tells which type of content element you want to add.

- In "Choose section" select "Core Shop".
- Click the save with X icon to add the content element and go back to the previous screen.

Now it is time to configure the constants inside the TYPO3 root template.

Including the static templates
""""""""""""""""""""""""""""""
We arrived at the last step of the setup, which is one of the most important things to do. Configuring the TYPO3 templates by including the required static templates!

We need to include two static templates. One for the root of the web site. The other one that is saved on the web shop page itself.

Web shop page
"""""""""""""
At the left panel press "Template"

- Click the page "Shop"
- Click "Click here to create an extension template."
- Press the tab "Includes"
- Inside "Include static (from extensions)" add the static template "Multishop Core Page Setup (multishop)"
- Click the save with X icon to add the content element and go back to the previous screen.

Root page
"""""""""
- At the left panel press "Template"
- Click the root page
- Click "Edit the whole template record"
- Press the tab "Includes"
- Inside "Include static (from extensions)" add the following static templates:

  - Multishop Root Page Setup (multishop)
  - 4.5 jQuery ColorBox Base for t3jquery
  - jQuery ColorBox Style 1 (or any other style that you prefer)
- Click the save with X icon to add the content element and go back to the previous screen.

Configuring the TYPO3 template
""""""""""""""""""""""""""""""
- At the left panel press "Template"
- Click the root page
- In the third frame of the TYPO3 admin panel on top press the dropdown menu and select "Constant Editor"
- After the "Category" label open the dropdown menu and select: "PLUGIN.MULTISHOP – MAIN"

Now you see a form with all the Multishop constants that makes Multishop run properly. We explain the required constants:

plugin.multishop – main
-----------------------
plugin.multishop.exampleCSS
An optional constant, but definitely recommended to enable if you use Multishop for the first time.

This includes CSS to properly displays the edit and delete pencil icon in the products/categories listing and detail page. Also the create account, edit account, order history and checkout pages are looking fairly good.

plugin.multishop.tt_address_record_id_store
-------------------------------------------
The id number of the tt_address record of the store. Used by the TAX system.

plugin.multishop.fe_customer_usergroup
--------------------------------------
The id number of the customers usergroup. When Multishop has to create a new customer (fe_user) the user will be made member of this usergroup.

plugin.multishop.fe_customer_pid
--------------------------------
The id number of the page that contains the front-end users and usergroups. In the introduction it's the id of the page "Frontend users and groups".

plugin.multishop.fe_rootadmin_usergroup
---------------------------------------
The id number of the rootadmin usergroup. Members of the rootadmin usergroup have the ability to clear the whole catalog with just one single mouse click. Not really the right permission for "normal" admin users. This usergroup is meant for the the developer.

plugin.multishop.shop_pid
-------------------------
This constant tells Multishop where the products are saved to. This number is also used to create the deeplinks inside the shop. This should reflect the id of the "Core Shop" page, which is in our case the page "Shop".

plugin.multishop.fe_admin_usergroup
-----------------------------------
The id number of the admin usergroup. Members of the admin usergroup will see the Multishop admin panel and are able to manage the catalog.

plugin.multishop – optional
"""""""""""""""""""""""""""
plugin.multishop.enableAdminPanelSystem
---------------------------------------
When this checkbox is enabled the SYSTEM menu in the front-end admin panel will be shown to admin users.

plugin.multishop.includejAutocomplete
-------------------------------------
Shows direct results when a user is typing a keyword in the products searchform.

plugin.multishop.disableMetatags
--------------------------------
Multishop provides it's own META tags this can give conflicts with the default TYPO3 META tags. If you enable this boolean the META tags of Multishop are not rendered.

plugin.multishop.hideFacebookInAdminInterface
---------------------------------------------
Hide Facebook like box in admin interface

plugin.multishop.show_powered_by_multishop
------------------------------------------
Show the Powered by TYPO3 Multishop logo in the front of the web shop.

plugin.multishop.includejCarousel
---------------------------------
Loads the JavaScript library to render the specials box carousel in products listing.

plugin.multishop.enableAdminPanelRebuildFlatDatabase
----------------------------------------------------
Normally only rootadmin users are allowed to rebuild the flat database. By enabling this constant also the regular admin user can see this menu item (in the SYSTEM menu item).

plugin.multishop.enableAdminPanelSortCatalog
--------------------------------------------
Normally only rootadmin users are allowed to re-order the catalog with one click. By enabling this constant also the regular admin user can see this menu item (in the SYSTEM menu item).

plugin.multishop.enableAdminPanelSettings
-----------------------------------------
Normally only rootadmin users are allowed to change the Multishop settings. By enabling this constant also the regular admin user can see this menu item (in the SYSTEM menu item).

plugin.multishop.includeCSS
---------------------------
Include global CSS files needed for a good working shop.

plugin.multishop.includeJqueryUiTheme
-------------------------------------
If you want to include your own jQuery UI theme disable this checkbox.

plugin.multishop.includeJS
--------------------------
Include all internal JavaScript libraries (BlockUI, jQuery Cookie, jQuery Hotkeys, PrettyCheckboxes etc). Needed for a proper working shop.

plugin.multishop.admin_development_company_logo
-----------------------------------------------
Whitelabel your shop by providing your own logo.

plugin.multishop.admin_development_company_name
-----------------------------------------------
Whitelabel your shop by providing your own company name.

plugin.multishop.admin_development_company_url
----------------------------------------------
Whitelabel your shop by providing your own company website URL.

plugin.multishop.admin_template_folder
--------------------------------------
Folder of the admin skin template. Example: EXT:multishop/templates/admin_multishop.

plugin.multishop.admin_help_url
-------------------------------
Whitelabel your shop by providing your own company help page URL.

plugin.multishop.products_listing_page_pid
------------------------------------------
On default all Multishop pages are shared on the same pid. Sometimes it can be useful to seperate this. In that case you can create a new page, put a Multishop content element on it that is configured as product, products_listing. Then configure the UID of this page to this constant.

plugin.multishop.products_detail_page_pid
-----------------------------------------
On default all Multishop pages are shared on the same pid. Sometimes it can be useful to seperate this. In that case you can create a new page, put a Multishop content element on it that is configured as product, products_detail. Then configure the UID of this page to this constant.

plugin.multishop.create_account_pid]
------------------------------------
The ID number of the page where the guest user can create an account.

plugin.multishop.categoriesStartingPoint
----------------------------------------
Sometimes it can be useful to simulate the shop to start on different main category than the root. If you define the categories id of a category that category will be the root.

plugin.multishop.checkout_page_pid
----------------------------------
On default all Multishop pages are shared on the same pid. Sometimes it can be useful to seperate this. In that case you can create a new page, put a Multishop content element on it that is configured as miscellaneous, checkout. Then configure the UID of this page to this constant.

plugin.multishop.shoppingcart_page_pid
--------------------------------------
On default all Multishop pages are shared on the same pid. Sometimes it can be useful to seperate this. In that case you can create a new page, put a Multishop content element on it that is configured as miscellaneous, shopping_cart. Then configure the UID of this page to this constant.

plugin.multishop.search_page_pid
--------------------------------
On default all Multishop pages are shared on the same pid. Sometimes it can be useful to seperate this. In that case you can create a new page, put a Multishop content element on it that is configured as search. Then configure the UID of this page to this constant.

plugin.multishop.ajax_pagetype_id_server
----------------------------------------
The page type of Multishop ajax scripts. Must be 2002 at this moment. But later can be changed here if it gives conflicts with other plugins.

plugin.multishop.catalog_shop_pid
---------------------------------
If you want to use a different catalog as source (when creating slave shops), here you can fill in the ID number of the page that contains the catalog.

plugin.multishop.login_pid
--------------------------
The ID number of the page where the (felogin) login content element is put on.

plugin.multishop.logout_pid
---------------------------
The ID number of the page where the logout content element is put on.

plugin.multishop.edit_account_pid
---------------------------------
The ID number of the page where the front end user can adjust his user details.

plugin.multishop - optional admin usergroups
""""""""""""""""""""""""""""""""""""""""""""
plugin.multishop.fe_storesadmin_usergroup
-----------------------------------------
The uid number of the website usergroup Stores Admin. This usergroup can switch from shops by the Stores menu in the in the front-end.

plugin.multishop.fe_searchadmin_usergroup
-----------------------------------------
The uid number of the website usergroup Search Admin. This usergroup can search in the top right area of the admin panel.

plugin.multishop.fe_systemadmin_usergroup
-----------------------------------------
The uid number of the website usergroup System Admin. This usergroup can see the system menu item of the admin panel.

plugin.multishop.fe_cmsadmin_usergroup
--------------------------------------
The uid number of the website usergroup CMS Admin. This usergroup can maintain the CMS pages in the front-end.

plugin.multishop.fe_statisticsadmin_usergroup
---------------------------------------------
The uid number of the website usergroup Statistics Admin. This usergroup can view the statistics.

plugin.multishop.fe_ordersadmin_usergroup
-----------------------------------------
The uid number of the website usergroup Orders Admin. This usergroup can maintain the orders.

plugin.multishop.fe_customersadmin_usergroup
--------------------------------------------
The uid number of the website usergroup Customers Admin. This usergroup can maintain the customers.

plugin.multishop.fe_catalogadmin_usergroup
------------------------------------------
The uid number of the website usergroup Catalog Admin. This usergroup can maintain the catalog.

plugin.multishop – templates
""""""""""""""""""""""""""""
plugin.multishop.admin_cms_tmpl_path
------------------------------------
Optionally specify the location of the template file that is being used for building up the admin cms listing.

plugin.multishop.admin_categories_tmpl_path
-------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin categories.

plugin.multishop.admin_edit_manufacturer_tmpl_path
--------------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin edit manufacturer.

plugin.multishop.admin_manufacturers_tmpl_path
----------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin manufacturers listing.

plugin.multishop.admin_customers_listing_tmpl_path
--------------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin customers listing.

plugin.multishop.admin_customer_groups_listing_tmpl_path
--------------------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin edit customer groups listing.

plugin.multishop.admin_edit_customer_group_tmpl_path
----------------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin edit customer group.

plugin.multishop.admin_edit_customer_tmpl_path
----------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin edit customer.

plugin.multishop.admin_edit_category_tmpl_path
----------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin edit category.

plugin.multishop.admin_home_tmpl_path
-------------------------------------
Optionally specify the location of the template file that is being used for building up the admin home.

plugin.multishop.order_details_table_adminNotificationPopup_tmpl_path
---------------------------------------------------------------------
Optionally specify the location of the template file that is being used for building up the order details.

plugin.multishop.admin_useragents_tmpl_path
-------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin user-agents statistic.

plugin.multishop.admin_orders_tmpl_path
---------------------------------------
Optionally specify the location of the template file that is being used for building up the admin orders.

plugin.multishop.admin_edit_product_tmpl_path
---------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin edit product.

plugin.multishop.admin_products_search_and_edit_tmpl_path
---------------------------------------------------------
Optionally specify the location of the template file that is being used for building up the admin products search and edit.

plugin.multishop.shopping_cart_tmpl_path
----------------------------------------
Optionally specify the location of the template file that is being used for building up the shopping cart.

plugin.multishop.manufacturers_listing_tmpl_path
------------------------------------------------
Optionally specify the location of the template file that is being used for building up the manufacturers listing.

plugin.multishop.specials_sections_products_listing_tmpl_path
-------------------------------------------------------------
Location of the HTML-template file of the products listing page. Use this option OR use the Template File option. If both are filled the Template File Path will overrule.

plugin.multishop.email_tmpl_path
--------------------------------
Optionally specify the location of the HTML e-mail template file. Example: fileadmin/templates/multishop/email_template.tmpl.

plugin.multishop.products_listing_tmpl_path
-------------------------------------------
Location of the HTML-template file of the products listing page. Use this option OR use the Template File option. If both are filled the Template File Path will overrule.

plugin.multishop.product_detail_tmpl_path
-----------------------------------------
Location of the HTML-template file of the products detail page. Use this option OR use the Template File option. If both are filled the Template File Path will overrule.

plugin.multishop.html_box_tmpl_path
-----------------------------------
Location of the HTML-template file of the HTML box element. Use this option OR use the Template File option. If both are filled the Template File Path will overrule.

plugin.multishop.order_details_table_site_tmpl_path
---------------------------------------------------
Optionally specify the location of the template file that is being used for building up the order details.

plugin.multishop.searchform_tmpl_path
-------------------------------------
Optionally specify the location of the template file that is being used for building up the searchform.

plugin.multishop.crumbar_tmpl_path
----------------------------------
Optionally specify the location of the template file that is being used for building up the crumbar.

plugin.multishop.basket_default_tmpl_path
-----------------------------------------
Optionally specify the location of the template file that is being used for building up the basket.

plugin.multishop.categories_listing_tmpl_path
---------------------------------------------
Optionally specify the location of the template file that is being used for building up the categories listing.

plugin.multishop.order_details_table_email_tmpl_path
----------------------------------------------------
Optionally specify the location of the template file that is being used for building up the order details.

plugin.multishop.products_relatives_tmpl_path
---------------------------------------------
Optionally specify the location of the template file that is being used for building up the products relatives table.

plugin.multishop – advanced
"""""""""""""""""""""""""""
plugin.multishop.masterShop
---------------------------
When this constant is enabled the admin will see the orders of all shops that exists in the page tree.

plugin.multishop.cacheConfiguration
-----------------------------------
Cache the Multishop settings for faster pages.

plugin.multishop.disableFeFromCalculatingVatPrices
--------------------------------------------------
This will improve performance, but the shop will not work with VAT rates.

plugin.multishop.crumbar_rootline_title
---------------------------------------
Change the root title in the crumbar.

plugin.multishop.cart_uid
-------------------------
The unique identifier of the cart. This way you can seperate carts from shops.

Update instructions
===================
When there is a new version of Multishop available and you want to update your shop you need to take the following point in account.

Multishop updates the database fields internally. When you open the web shop in the front-end (after installing the newer version) it will detect the update and tries to compare the database changes. The reason why we developed it this way is because we update Multishop on our own sites completely automatic (not through the extension manager).

If TYPO3 is suggesting to change database fields don't allow it yet. It is better to open Multishop than first in the front and login as admin user. Then select SYSTEM / COMPARE DATABASE to let Multishop compare the database.

After you have processed the Multishop compare database utility go back to the extension manager and press Multishop. If it still warns about field changes you should accept the changes.

Configuration instructions
==========================
Now that you have finished the installation procedure and have the Multishop admin panel in front of you it's time to configure Multishop.

In the top right section of the admin panel you can do a wild search. This search engine searches for:

- Multishop settings
- Customers
- Categories
- Products
- Manufacturers
- Orders
- Invoices

We advice you to use the search engine as much as possible to win a lot of time. If you want to see a complete list of settings open SYSTEM / SETTINGS in the bottom right panel.

Define the store name, e-mail address and location
""""""""""""""""""""""""""""""""""""""""""""""""""
* Now press "Store Name" and change the CURRENT VALUE


Define the enabled countries, zones, shipping and payment methods
-----------------------------------------------------------------
Go to SYSTEM / SHIPPING AND PAYMENT and press COUNTRIES.

Define here to which countries are able to deliver.

After the countries are enabled it's time to create shipping zones. A shipping zone could contain one or more countries. This makes configuring the shipping costs easier.

F.A.Q.
======
The admin panel is not showing in the front
"""""""""""""""""""""""""""""""""""""""""""
First of all make sure you are logged in with your front-end username and that this user is member of the admin usergroup. In the TYPO3 template the constants field "plugin.multishop.fe_admin_usergroup" should contain the id number of the admin usergroup.

If this is all true than most likely you have a jQuery issue. Open FireBug in FireFox and login the front-end. Check the console for any errors. It happens a lot that developers load jQuery double cause of included plugins that ship their own jQuery library. Make sure that other plugins are configured so that they don't include their own jQuery library and validate that T3jQuery is configured properly.

Locallang labels are missing
""""""""""""""""""""""""""""
It could be that your installation is using an old locallized version of Multishop. To test this you could temporary remove the folder: typo3conf/l10n/XX/multishop, where XX corresponds the language id.

When I'm opening CUSTOMERS and/or ORDERS in the admin panel I don't see any expected records.
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
Maybe the page_uid is not correctly defined. You could install the phpmyadmin plugin to analyse your database. Example queries to fix it:

- update fe_users set page_uid=206
- update fe_users set pid=262
- update fe_users set usergroup=1
- update tx_multishop_orders set page_uid=206

Explanation
-----------

- page_uid = the value of plugin.multishop.shop_pid
- pid = the value of plugin.multishop.fe_customer_pid
- usergroup = the value of plugin.multishop.fe_customer_usergroup

