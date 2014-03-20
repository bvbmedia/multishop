Multishop is the e-commerce plugin for TYPO3.

For the latest updates open http://www.typo3multishop.com/roadmap/

JQUERY 2 NOTICES
Multishop requires jQuery 2. Currently rzcolorbox and t3jquery have a few issues. To bypass this problem we have shared patched versions on Bitbucket.

Steps to use the:

mkdir /sources

git clone https://basvanbeek@bitbucket.org/bvbmedia/rzcolorbox_jquery2.git
git clone https://basvanbeek@bitbucket.org/bvbmedia/t3jquery_jquery2.git

ln -s /sources/rzcolorbox_jquery2 /var/www/yourdomain.nl/web/typo3conf/ext/rzcolorbox
ln -s /sources/t3jquery_jquery2 /var/www/yourdomain.nl/web/typo3conf/ext/t3jquery
ln -s /sources/t3jquery_jquery2/uploads /var/www/yourdomain.nl/web/uploads/tx_t3jquery

Now login to TYPO3 backend:

- Clear TYPO3 cache
- Go to extension manager. Select t3jquery plugin to configure it. Select TYPO in selectbox and configure:

jQuery Version - typo.jQueryVersion: 2.0.x
jQuery UI Version - typo.jQueryUiVersion: 1.10.x
jQuery TOOLS Version - typo.jQueryTOOLSVersion: 1.2.x



