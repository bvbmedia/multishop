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

JQUERY 2 NOTICES
Multishop requires jQuery 2. Currently rzcolorbox and t3jquery have some issues. To bypass this problem we have shared some patched versions on Bitbucket.

Steps to do:
mkdir /sources

git clone https://bitbucket.org/bvbmedia/rzcolorbox_jquery2.git
git clone https://bitbucket.org/bvbmedia/t3jquery_jquery2.git

ln -s /sources/rzcolorbox_jquery2 /var/www/yourdomain.nl/web/typo3conf/ext/rzcolorbox
ln -s /sources/t3jquery_jquery2/t3jquery /var/www/yourdomain.nl/web/typo3conf/ext/t3jquery
ln -s /sources/t3jquery_jquery2/uploads /var/www/yourdomain.nl/web/uploads/tx_t3jquery

Login to your TYPO3 backend:

- Clear TYPO3 cache
- Go to extension manager. Select t3jquery plugin to configure it. Select TYPO in selectbox and configure:

jQuery Version - typo.jQueryVersion: 2.0.x
jQuery UI Version - typo.jQueryUiVersion: 1.10.x
jQuery TOOLS Version - typo.jQueryTOOLSVersion: 1.2.x
