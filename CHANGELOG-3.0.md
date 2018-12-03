CHANGELOG - ZIKULA 3.0.x
========================

3.0.0 (unreleased)
------------------

 - BC Breaks:
    - Removed bootstrap-plus/bootstrap-jqueryui. Use jQuery UI directly.

 - Deprecated:

 - Fixes:
    - Check if verification record is already deleted when confirming a changed mail address.
    - Updated listener priorities in Settings module to fix non-working variable localisation (#3934).
    - Fixed broken functionality of hiding submit button in search block.
    - Improved setting meta data for start page settings (#3929, #3932).
    - Provide more kernel information in coredata (#3651).
    - Clear cache after changing active authentication methods (#3936).
    - Fixed broken support for custom block templates in themes.

 - Features:

 - Vendor updates:
    - composer/ca-bundle updated from 1.1.2 to 1.1.3
    - composer/installers updated from 1.5.0 to 1.6.0
    - doctrine/doctrine-cache-bundle updated from 1.3.3 to 1.3.5
    - jquery.mmenu updated from 7.0.6 to 7.2.2
    - monolog/monolog updates from 1.23.0 to 1.24.0
    - psr/log updated from 1.0.2 to 1.1.0
    - sensio/distribution-bundle updated from 5.0.22 to 5.0.23
    - sensiolabs/security-checker updated from 4.1.8 to 5.0.1
    - symfony/phpunit-bridge updated from 3.4.14 to 3.4.17
    - symfony/polyfill-* updated from 1.9.0 to 1.10.0
    - symfony/symfony updated from 3.4.14 to 3.4.19
    - vakata/jstree updated from 3.3.5 to 3.3.7

