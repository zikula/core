CHANGELOG - ZIKULA 2.0.x
------------------------

* 2.0.1 (?)

 - Fixes:
    - Corrected missing sortable column css class definitions (#3757).

 - Vendor updates:
    - jquery.mmenu updated from 6.1.3 to 6.1.4
    - matthiasnoback/symfony-service-definition-validator updated from dev-master to 1.2.8
    - sensiolabs/security-checker updated from 4.1.1 to 4.1.3
    - symfony/symfony updated from 3.3.5 to 3.3.6


* 2.0.0 (2017-08-05)

 - BC Breaks:
    - Removed all @deprecated items from Core-1.x.
    - Removed all legacy plugins. Use Symfony Bundles instead.
    - The parameter `framework.session.cookie_httponly` is now set to `true` (#2716).
    - Removed old custom Doctrine extensions listener. Use listeners from stof bundle instead.
    - Removed custom service aliases for Doctrine classes. Retrieve them directly using the entity manager.

 - Deprecated:

 - Fixes:

 - Features:
    - See feature docs

 - Vendor updates:
    - matthiasnoback/symfony-console-form updated from 1.2.0 to 2.0.2
    - symfony updated from 2.8.x to 3.3.x (#3027, #2099, #2639).
