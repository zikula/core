CHANGELOG - ZIKULA 2.0.x
------------------------

* 2.0.4 (?)

 - Fixes:
    - Use display name in category selection form type (#3828).
    - Provide showRegistryLabels option in category selection form type to show a label for each single selector based on the base category assigned in the corresponding registry.
    - Disabled CSRF protection for search results (#3831).

 - Vendor updates:
    - composer/ca-bundle updated from 1.0.8 to 1.0.9
    - symfony/symfony updated from 3.3.10 to 3.3.13
    - zikula/oauth-module updated from 1.0.3 to 1.0.4


* 2.0.3 (2017-11-04)

 - Fixes:
    - Fixed getAttributeValue error in case attribute does not exist.
    - Added missing action icons to admin menu sub entries and admin panel module links.
    - Fixed admin category creation issues (#3826, #3827).
    - Fixed several category editing problems (#3833, #3834).
    - Explicitly set template names in template annotations (#3835, #3836).
    - Fixed wrong argument in avatar detection during ajax-based user search.
    - Properly reset avatar image if user is removed after ajax-based user search.

 - Vendor updates:
    - doctrine/doctrine-bundle updated from 1.6.8 to 1.6.13
    - doctrine/doctrine-cache-bundle updated from 1.3.0 to 1.3.2
    - doctrine/orm updated from 2.5.10 to 2.5.12
    - gedmo/doctrine-extensions updated from 2.4.30 to 2.4.31
    - jquery.mmenu updated from 6.1.5 to 6.1.8
    - liip/imagine-bundle updated from 1.8.0 to 1.9.1
    - phpdocumentor/reflection-common updated from 1.0 to 1.0.1
    - sensiolabs/security-checker updated from 4.1.5 to 4.1.6
    - symfony/polyfill-* updated from 1.5.0 to 1.6.0
    - symfony/symfony updated from 3.3.9 to 3.3.10
    - twig/twig updated from 1.34.4 to 1.35.0
    - zikula/generatorbundle updated from 2.0.0 to 2.0.1
    - zikula/legal-module updated from 3.1.0 to 3.1.1
    - zikula/profile-module updated from 3.0.2 to 3.0.3


* 2.0.2 (2017-10-03)

 - Fixes:
    - Allow hooks to be managed in display (#3793).
    - Improved detection and naming of available PDO drivers (#3785).
    - Use uncached JS routes (#3807).

 - Vendor updates:
    - composer/ca-bundle updated from 1.0.7 to 1.0.8
    - imagine/imagine updated from 0.6.3 to 0.7.1
    - jquery.mmenu updated from 6.1.5 to 6.1.6
    - liip/imagine-bundle updated from 1.8.0 to 1.9.1
    - phpspec/prophecy updated from 1.7.0 to 1.7.2
    - symfony/symfony updated from 3.3.8 to 3.3.9


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
