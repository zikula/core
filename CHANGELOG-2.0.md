CHANGELOG - ZIKULA 2.0.x
------------------------

* 2.0.0-alpha1 (?)

 - BC Breaks:
    - Removed all @deprecated items from Core-1.x.
    - Removed all legacy plugins. Use Symfony Bundles instead.
    - The parameter `framework.session.cookie_httponly` is now set to `true` (#2716).
    - Removed old custom Doctrine extensions listener. Use listeners from stof bundle instead.
    - Removed custom service aliases for Doctrine classes. Retrieve them directory using the entity manager.

 - Deprecated:

 - Fixes:

 - Features:

 - Core-2.0 Features:

 - Vendor updates:
    - matthiasnoback/symfony-console-form updated from 1.2.0 to 2.0.2
    - symfony updated from 2.8.x to 3.3.x (#3027, #2099, #2639).
