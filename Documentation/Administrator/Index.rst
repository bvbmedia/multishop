.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _changelog:

Administrator
=========
Multishop is working on the latest PHP 5.6. But you need to suppress the deprecation errors. You can do this by adding the following configuration to the php.ini file (.htaccess won't work):
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT


