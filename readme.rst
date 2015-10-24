=========
Multishop
=========

Multishop is the e-commerce plugin for TYPO3.

Download official version from TYPO3.org
========================================

`Multishop <http://typo3.org/extensions/repository/view/multishop>`_

Installation
============

Simply install the plugin in your TYPO3 installation.

Usage
=====

After the plugin has been installed the shop must be configured. How you can do this can be read on typo3.org.

jQuery / t3jquery notice
========================
Multishop requires jQuery 2. Also enable the jQuery module: Migrate + all the Bootstrap modules.

Bootstrap 3 required
====================
Multishop expects the website to have Twitter Bootstrap loaded. You will have to load the JavaScript library (with i.e. t3jquery) yourself and manually include Bootstrap.css with TypoScript.

Load bootstrap.css by TypoScript example:
::

page {
	includeCSS.bootstrapCss = https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css
	includeCSS.bootstrapCss {
		media =
		import = 0
		compress=1
	}
}

Login to your TYPO3 backend:

- Clear TYPO3 cache
- Go to extension manager. Select t3jquery plugin to configure it. Select TYPO in selectbox and configure:

jQuery Version - typo.jQueryVersion: 2.x
jQuery UI Version - typo.jQueryUiVersion: 1.10.x
jQuery TOOLS Version - typo.jQueryTOOLSVersion: 1.2.x
jQuery Bootstrap version - 3.x.x

TYPO3 MULTISHOP DEMO PACKAGE
============================
If you have difficulties with configuring Multishop you could download our demo package (which is configured on TYPO3 6.2.3 with Bootstrap 3). You can download it here:
https://www.typo3multishop.com/download/

Steps to configure:

- Extract the file to the vhost folder (i.e. /var/www/domain.com/web/).
- Import the file database.sql to your database and remove it from the server.
- Adjust the MySQL credentials (host, database, username and password) in the file: typo3conf/LocalConfiguration.php
- Login to the TYPO3 backend (and also in front-end as fe_user) with the following credentials:
-- Username: typo3mslabAdmin
-- Password: testMultishop123!
- Do not forget to change the password!


Credits
=======

This Plugin is created by `BVB Media <https://www.bvbmedia.com/>`_.

More information
================

`TYPO3 Multishop <https://www.typo3multishop.com/>`_

