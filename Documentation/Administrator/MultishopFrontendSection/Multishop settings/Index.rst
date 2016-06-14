.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _multishop_settings:

==================
Multishop settings
==================

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
Go to SYSTEM / SHIPPING AND PAYMENT and click COUNTRIES.

Enable the countries to which you are able to deliver to.

After the countries are enabled it's time to create shipping zones. A shipping zone could contain one or more countries. This makes configuring the shipping costs easier.

Go to SYSTEM / SHIPPING AND PAYMENT and click ZONES.

Shipping costs are configured to zones. A zone contains 1 or more countries. In our example we have a shop that is located in the Netherlands and supports shipping to the Netherlands, Belgium and Germany.

For this we would create 2 zones:

Zone 1
- Netherlands

Zone 2
- Germany
- Belgium

Now we can create the shipping method(s) and connect them to the shipping zones. After that you can open SHIPPING COSTS and configure the shipping costs per zone and per shipping method. Multishop supports flat rate shipping costs and weight based shipping costs. More options can be implemented by hooking your own plugin to Multishop.


