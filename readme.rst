===============
Multishop
===============

Multishop is the e-commerce plugin for TYPO3.

Download official version from TYPO3.org
=====

`Multishop <http://typo3.org/extensions/repository/view/multishop>`_

Installation
============

Simply install the plugin in your TYPO3 installation.

Usage
=====

After the Plugin is installed the shop must be configured. How you can do this can be read typo3.org.

jQuery notice
=====
Multishop 3 requires jQuery 2. Also enable the jQuery module: Migrate.

Login to your TYPO3 backend:

- Clear TYPO3 cache
- Go to extension manager. Select t3jquery plugin to configure it. Select TYPO in selectbox and configure:

jQuery Version - typo.jQueryVersion: 2.0.x
jQuery UI Version - typo.jQueryUiVersion: 1.10.x
jQuery TOOLS Version - typo.jQueryTOOLSVersion: 1.2.x

TYPO3 MULTISHOP DEMO PACKAGE
=====
If you have difficulties with configuring Multishop you could download our demo package (which is configured on TYPO3 6.2.3 with Bootstrap 3). You can download it here:
https://www.typo3multishop.com/download/

Steps to configure:

- Extract the file to the vhost folder (i.e. /var/www/domain.com/web/).
- Import the file database.sql to your database and remove it from the server.
- Adjust the MySQL credentials (host, database, username and password) in the file: typo3conf/LocalConfiguration.php
- Login to the TYPO3 backend (and also in front-end as fe_user) with the following credentials:
Username: typo3mslabAdmin
Password: testMultishop123!
- Do not forget to change the password!


Credits
=====

This Plugin is created by `BVB Media <https://www.bvbmedia.com/>`_.

More information
=====

`TYPO3 Multishop <https://www.typo3multishop.com/>`_

