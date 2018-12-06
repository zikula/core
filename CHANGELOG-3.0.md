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

 - Features:

 - Vendor updates:
    - symfony/phpunit-bridge installed in 3.4.14 and updated to 3.4.20

