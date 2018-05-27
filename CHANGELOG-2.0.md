CHANGELOG - ZIKULA 2.0.x
========================

2.0.7 (unreleased)
------------------

 - Fixes:
    - ?

 - Vendor updates:
    - gedmo/doctrine-extensions updated from 2.4.33 to 2.4.35
    - guzzlehttp/guzzle updated from 6.3.2 to 6.3.3
    - phpspec/prophecy updated from 1.7.5 to 1.7.6
    - symfony/polyfill-* updated from 1.7.0 to 1.8.0
    - symfony/symfony updated from 3.4.8 to 3.4.11


2.0.6 (2018-04-13)
------------------

 - BC Breaks:
    - Removed custom container builder bridge, so array access for the container is not available anymore (#3894).
    - Removed legacy code to enable `cookie_httponly` setting for cookies (#3895).

 - Fixes:
    - Fixed session regeneration warning with PHP 7 (#3886).
    - Reduced priority of click jack protection listener to execute it later.

 - Vendor updates:
    - composer/ca-bundle updated from 1.1.0 to 1.1.1
    - doctrine/doctrine-cache-bundle updated from 1.3.2 to 1.3.3
    - guzzlehttp/guzzle updated from 6.3.0 to 6.3.2
    - paragonie/random_compat updated from 2.0.11 to 2.0.12
    - psr/simple-cache updated from 1.0.0 to 1.0.1
    - sensiolabs/security-checker updated from 4.1.7 to 4.1.8
    - symfony/symfony updated from 3.4.4 to 3.4.8
    - twig/twig updated from 1.35.0 to 1.35.3


2.0.5 (2018-02-24)
------------------

 - BC Breaks:
    - Removed matthiasnoback/symfony-service-definition-validator (#3885).
    - Removed \Zikula\Core\AbstractBundle::getBasePath() method. Use getPath() instead (#3862).

 - Fixes:
    - Fixed wrong request service call in GroupsModule menu (#3874).
    - Fixed fetching module url from metadata when untranslated (#3876).
    - Activated translatable fallback for proper handling of content with missing translations.
    - Added fallback for missing user real names.
    - Avoid exposure of server pathes in JS assets merger (#3883, #3890).
    - Added hints about minimum password length (#3884, #3891).
    - Fixed broken password strength meter usage in ZAuth administration (#3891).

 - Vendor updates:
    - composer/installers updated from 1.4.0 to 1.5.0
    - doctrine/orm updated from 2.5.13 to 2.5.14
    - gedmo/doctrine-extensions updated from 2.4.31 to 2.4.33
    - jquery.mmenu updated from 6.1.8 to 7.0.3
    - sensiolabs/security-checker updated from 4.1.6 to 4.1.7
    - swiftmailer/swiftmailer updated from v5.4.8 to v5.4.9
    - symfony/symfony updated from 3.4.2 to 3.4.4
    - vakata/jstree updated from 3.3.4 to 3.3.5
    - zikula/andreas08-theme updated from 3.0.1 to 3.0.2


2.0.4 (2017-12-16)
------------------

 - BC Breaks:
    - CSRF tokens are now different for HTTP and HTTPS (#3856). See http://symfony.com/blog/cve-2017-16653-csrf-protection-does-not-use-different-tokens-for-http-and-https for more information.

 - Fixes:
    - Use display name in category selection form type (#3828).
    - Provide showRegistryLabels option in category selection form type to show a label for each single selector based on the base category assigned in the corresponding registry.
    - Disabled CSRF protection for search results (#3831).
    - Allowing audio files with the .mp3 and .wav extensions to be accessible via the modules and themes folders. (#3858).
    - Fixed logical issue in Users module's mass mailing helper (#3863).
    - Fixed wrong deletion of Theme variables (#3857).

 - Vendor updates:
    - composer/ca-bundle updated from 1.0.8 to 1.1.0
    - doctrine/orm updated from 2.5.12 to 2.5.13
    - elao/web-profiler-extra-bundle updated from 2.3.4 to 2.3.5
    - phpspec/prophecy updated from 1.7.2 to 1.7.3
    - phpunit/php-file-iterator updated from 1.4.2 to 1.4.5
    - phpunit/php-token-stream updated from 1.4.11 to 1.4.12
    - sensio/generator-bundle updated from 3.1.6 to 3.1.7
    - symfony/symfony updated from 3.3.10 to 3.4.2
    - zikula/legal-module updated from 3.1.1 to 3.1.2
    - zikula/oauth-module updated from 1.0.3 to 1.0.4


2.0.3 (2017-11-04)
------------------

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


2.0.2 (2017-10-03)
------------------

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


2.0.1 (2017-09-01)
------------------

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


2.0.0 (2017-08-05)
------------------

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
