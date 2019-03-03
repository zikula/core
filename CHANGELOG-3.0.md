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
    - Cosmetical corrections for account link graphics.
    - Prevent exception caused by modification of uninitialised extensions.

 - Features:
    - Centralised dynamic form field handling from Profile module in FormExtensionsBundle (#3945).
    - Allow zasset syntax for relative assets also for normal bundles.

 - Vendor updates:
    - components/bootstrap updated from 3.3.7 to 3.4.0
    - composer/ca-bundle updated from 1.1.3 to 1.1.4
    - elao/web-profiler-extra-bundle updated from 2.3.5 to 2.3.6
    - jquery.mmenu updated from 7.2.2 to 7.3.2
    - paragonie/random_compat updated from 2.0.17 to 2.0.18
    - sensio/distribution-bundle updated from 5.0.23 to 5.0.24
    - sensiolabs/security-checker updated from 5.0.1 to 5.0.3
    - symfony/phpunit-bridge installed in 3.4.14 and updated to 3.4.23
    - symfony/symfony updated from 3.4.20 to 3.4.23
    - twig/twig updated from 1.35.4 to 1.37.1
    - zikula/profile-module updated from 3.0.5 to 3.0.6

