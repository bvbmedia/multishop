.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _faq:

======
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

How to automate the update of the Google sitemap
""""""""""""""""""""""""""""""""""""""""""""""""
Setup a cronjob like this:
# Update every night sitemap of webshop catalog
0 0 * * * /usr/bin/wget -O /dev/null --tries=1 --timeout=86400 -q "http://webshop.nl/index.php?id=10&type=2002&tx_multishop_pi1[page_section]=sitemap_generator&tx_multishop_pi1[encryptionKey]=c23568e03414ec10f385ac955f8717dd12dd84g36" >/dev/null 2>&1