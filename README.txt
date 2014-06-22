Multishop is the e-commerce plugin for TYPO3.

It makes use of of jQuery 2. For optimal results install it on a webserver that uses PHP 5.4.X or higher and TYPO3 6.2.X LTS.

The latest version can be downloaded from Atlassian Bitbucket:
http://git.bvbmedia.nl/multishop

Are you curious about what Multishop can do for you? On the following sites you can find live shops that are running on Multishop:
http://www.typo3webshop.com/
https://www.typo3multishop.com/live-shops/

Do not forget to like us on Facebook:
https://www.facebook.com/typo3multishop
https://www.facebook.com/bvbmedianl

For the latest news and updates check:
https://www.typo3multishop.com
https://www.typo3multishop.com/roadmap/

JQUERY NOTICE
Multishop 3 now requires jQuery 2. Also enable the jQuery module: Migrate.

Login to your TYPO3 backend:

- Clear TYPO3 cache
- Go to extension manager. Select t3jquery plugin to configure it. Select TYPO in selectbox and configure:

jQuery Version - typo.jQueryVersion: 2.0.x
jQuery UI Version - typo.jQueryUiVersion: 1.10.x
jQuery TOOLS Version - typo.jQueryTOOLSVersion: 1.2.x

TYPO3 MULTISHOP DEMO PACKAGE
If you have difficulties with configuring Multishop you could download our demo package (which is configured on TYPO3 6.2.3 with Bootstrap 3). You can download it here:
https://www.typo3multishop.com/fileadmin/user_upload/multishop_demo_site.tar.gz

Steps to configure:
- Extract the file to the vhost folder (i.e. /var/www/domain.com/web/).
- Import the file database.sql to your database and remove it from the server.
- Adjust the MySQL credentials (host, database, username and password) in the file: typo3conf/LocalConfiguration.php
- Login to the TYPO3 backend (and also in front-end as fe_user) with the following credentials:
Username: typo3mslabAdmin
Password: testMultishop123!
- Do not forget to change the password!



