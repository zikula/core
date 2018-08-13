CHANGELOG - ZIKULA 2.1.x
========================

2.1.0 (unreleased)
------------------

 - BC Breaks:
    - Removed bootstrap-plus/bootstrap-jqueryui. Use jQuery UI directly.

 - Deprecated:

 - Fixes:
    - Fixed exception if no return url is given during login after upgrade (#3922).
    - Added missing redirect after completed auto login after successful registration.

 - Features:

 - Vendor updates:
    - composer/ca-bundle updated from 1.1.1 to 1.1.2
    - symfony/phpunit-bridge updated from 3.4.13 to 3.4.14
    - symfony/polyfill-* updated from 1.8.0 to 1.9.0
