CHANGELOG - ZIKULA 2.0.x
------------------------

* 2.0.2 (?)

 - Fixes:
    - ?

 - Vendor updates:
    - ?


* 2.0.1 (2017-09-01)

 - Fixes:
    - Corrected conditional statement for searchable modules (#3752).
    - Corrected path to recent search results in pager (#3654, #3760).
    - Corrected access to category properties on edit (#3763, #3762).
    - Corrected missing sortable column css class definitions (#3757).
    - Update upgrade instructions (#3761, #3764).
    - Corrected visibility of properties in \Zikula\Core\Doctrine\Entity\AbstractEntityAttribute (#3765).
    - Improved collecting module workflow definitions (#3767).
    - Updated field lengths in HookRuntimeEntity to facilitate longer classnames and serviceIds.
    - Introduce override of `Kernel::getProjectDir()` (#3773).
    - Do not clear start controller if it is available (#3780, #3782).
    - Corrected pdo driver name for pgsql (#3783).
    - Notify admin on registration if admin email is not empty (#3725).
    - Correct paging of search results (#3754).

 - Vendor updates:
    - composer/installers updated from 1.3.0 to v1.4.0
    - doctrine/orm updated from 2.5.6 to 2.5.10
    - elao/web-profiler-extra-bundle updated from 2.3.3 to 2.3.4
    - friendsofsymfony/jsrouting-bundle updated from 1.6.0 to 1.6.3
    - jquery.mmenu updated from 6.1.3 to 6.1.5
    - matthiasnoback/symfony-service-definition-validator updated from dev-master to 1.2.8
    - phpdocumentor/reflection-docblock updated from 3.1.1 to 3.2.2
    - phpdocumentor/type-resolver updated from 0.2.1 to 0.3.0
    - sensiolabs/security-checker updated from 4.1.1 to 4.1.5
    - sensio/distribution-bundle updated from 5.0.20 to 5.0.21
    - symfony/polyfill-* updated from 1.4.0 to 1.5.0
    - symfony/symfony updated from 3.3.5 to 3.3.8


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
