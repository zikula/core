# CHANGELOG - ZIKULA 2.0.x

## 2.0.16 (unreleased)

- Fixes:
  - ...

## 2.0.15 (2019-11-18)

- Fixes:
  - Prevent exception caused by invalid return URL during login.
  - Updated listener priorities in Settings module to fix non-working variable localisation (#3934).
  - Fixed regression in settings page (#3983).
  - Updated obsolete HTML Purifier constant names.

## 2.0.14 (2019-11-13)

- Security fixes from Symfony:
  - Use constant time comparison in UriSigner (CVE-2019-18887).
  - Prevent argument injection in a MimeTypeGuesser (CVE-2019-18888).

- Fixes:
  - Improved setting meta data for start page settings (#3929, #3932).
  - Clear cache after changing active authentication methods (#3936).
  - Prevent exception caused by modification of uninitialised extensions.
  - Fixed invalid reference to "use compression" option in general settings form.
  - Escape `groups` table name since it became a reserved word in MySQL 8.
  - Improved input value checks for `yesNo` Twig filter.
  - Dynamically determine available locales from locale api in custom locale form type.
  - Increased amount of letters for top level domains in email address validation pattern (#3980).

- Vendor updates:
  - components/bootstrap updated from 3.4.0 to 3.4.1
  - components/jquery updated from 3.3.1 to 3.4.1
  - composer/ca-bundle updated from 1.1.4 to 1.2.4
  - composer/installers updated from v1.6.0 to v1.7.0
  - doctrine/lexer updated from v1.0.1 to 1.0.2
  - guzzlehttp/guzzle updated from 6.3.3 to 6.4.1
  - guzzlehttp/psr7 updated from 1.5.2 to 1.6.1
  - monolog/monolog updated from 1.24.0 to 1.25.2
  - phpspec/prophecy updated from 1.8.0 to 1.9.0
  - psr/log updated from 1.1.0 to 1.1.2
  - sensio/distribution-bundle updated from v5.0.24 to v5.0.25
  - symfony/polyfill-apcu updated from v1.11.0 to v1.12.0
  - symfony/polyfill-ctype updated from v1.11.0 to v1.12.0
  - symfony/polyfill-intl-icu updated from v1.11.0 to v1.12.0
  - symfony/polyfill-mbstring updated from v1.11.0 to v1.12.0
  - symfony/polyfill-php56 updated from v1.11.0 to v1.12.0
  - symfony/polyfill-php70 updated from v1.11.0 to v1.12.0
  - symfony/polyfill-util updated from v1.11.0 to v1.12.0
  - symfony/symfony updated from v3.4.26 to v3.4.35
  - twig/twig updated from v1.39.1 to v1.42.4
  - vakata/jstree updated from 3.3.7 to 3.3.8
  - webmozart/assert updated from 1.4.0 to 1.5.0

## 2.0.13 (2019-04-17)

- Security fixes from Symfony:
  - Check service IDs are valid (CVE-2019-10910).
  - Fix XSS issues in the form theme of the PHP templating engine (CVE-2019-10909).
  - Prevent destructors with side-effects from being unserialized (CVE-2019-10912).
  - Add a separator in the remember me cookie hash (CVE-2019-10911).
  - Reject invalid method override (CVE-2019-10913).

- Vendor updates:
  - components/bootstrap updated from 3.3.7 to 3.4.0
  - composer/ca-bundle updated from 1.1.3 to 1.1.4
  - composer/semver updated from 1.4.2 to 1.5.0
  - elao/web-profiler-extra-bundle updated from 2.3.5 to 2.3.6
  - gedmo/doctrine-extensions updated from 2.4.36 to 2.4.37
  - jquery.mmenu updated from 7.2.2 to 7.3.3
  - paragonie/random_compat updated from 2.0.17 to 2.0.18
  - sensio/distribution-bundle updated from 5.0.23 to 5.0.24
  - sensiolabs/security-checker updated from 5.0.1 to 5.0.3
  - symfony/polyfill-apcu updated from 1.10.0 to 1.11.0
  - symfony/polyfill-ctype updated from 1.10.0 to 1.11.0
  - symfony/polyfill-intl-icu updated from 1.10.0 to 1.11.0
  - symfony/polyfill-mbstring updated from 1.10.0 to 1.11.0
  - symfony/polyfill-php56 updated from 1.10.0 to 1.11.0
  - symfony/polyfill-php70 updated from 1.10.0 to 1.11.0
  - symfony/polyfill-util updated from 1.10.0 to 1.11.0
  - symfony/symfony updated from 3.4.20 to 3.4.26
  - twig/twig updated from 1.35.4 to 1.39.1
  - webmozart/assert updated from 1.3.0 to 1.4.0
  - zikula/profile-module updated from 3.0.5 to 3.0.6

## 2.0.12 (2018-12-06)

- Security fixes from Symfony:
  - Disclosure of uploaded files full path (CVE-2018-19789).
  - Open Redirect Vulnerability when using Security\Http (CVE-2018-19790).

- Fixes:
  - Fixed broken support for custom block templates in themes.

- Vendor updates:
  - composer/ca-bundle updated from 1.1.2 to 1.1.3
  - composer/installers updated from 1.5.0 to 1.6.0
  - doctrine/doctrine-cache-bundle updated from 1.3.3 to 1.3.5
  - guzzlehttp/psr7 updated from 1.4.2 to 1.5.2
  - jquery.mmenu updated from 7.0.6 to 7.2.2
  - monolog/monolog updates from 1.23.0 to 1.24.0
  - psr/log updated from 1.0.2 to 1.1.0
  - ralouphie/getallheaders installed in 2.0.5
  - sensio/distribution-bundle updated from 5.0.22 to 5.0.23
  - sensiolabs/security-checker updated from 4.1.8 to 5.0.1
  - symfony/polyfill-* updated from 1.9.0 to 1.10.0
  - symfony/symfony updated from 3.4.14 to 3.4.20
  - vakata/jstree updated from 3.3.5 to 3.3.7

## 2.0.11 (2018-08-23)

- Fixes:
  - Avoid JS error if webshim is not available.
  - Resolved dependency conflict re-adding webshims polyfill.

## 2.0.10 (2018-08-18)

- Fixes:
  - Fixed exception if no return url is given during login after upgrade (#3922).
  - Added missing redirect after completed auto login after successful registration.
  - Minor improvements in `CategoryEntity` accessors.

- Features:
  - Added post configuration event for amending or extending menus. See `docs/Menu/MenuEvents.md` for the details.
  - Added common content types interfaces for extending the Content module.

- Vendor updates:
  - composer/ca-bundle updated from 1.1.1 to 1.1.2
  - phpspec/prophecy updated from 1.7.6 to 1.8.0
  - symfony/phpunit-bridge updated from 3.4.13 to 3.4.14
  - symfony/polyfill-* updated from 1.8.0 to 1.9.0

## 2.0.9 (2018-08-06)

- Fixes:
  - Fixed invalid parameter update for frozen container in the upgrader.

## 2.0.8 (2018-08-05)

- Security fixes from Symfony:
  - Remove support for legacy and risky HTTP headers (CVE-2018-14773).
  - Possible host header injection when using HttpCache (CVE-2018-14774).

- Deprecated:
  - bootstrap-plus/bootstrap-jqueryui is deprecated and will be removed in 2.1. Use jQuery UI directly.

- Fixes:
  - Upgraded monolog for PHP 7.2+ compatibility (#3906).
  - Unset `upgrading` flag after successful upgrade (#3899).
  - Fixed invalid request access in hook controller.
  - Changed default storage engine in CLI installer to `InnoDB` (#3909).
  - Avoid linking to user registration page if registration functionality is disabled.
  - Use localised date format in user administration list.
  - Show user account menu on login page (like on registration and forgot xy pages, too).
  - Moved JavaScript code in several templates into footer area to ensure jQuery is available.
  - Added `maxlength` constraint to username field in registration form.
  - Ensure jQuery UI is loaded before bootstrap (#3912).
  - Suppress warning in PHP 7.2 if session is accessed before it is regenerated (e.g. during a login) (#3898, #3914).
  - Fixed wrong modvar reference in ZAuth validator (#3913).
  - Explicitly specify translation domain in pager templates (#3917).
  - Explicitly specify translation domain in user mail helper for calls from external modules (#3918).
  - Simplified workflow editor controller (#3749).
  - Fixed broken user search in Groups administration.

- Vendor updates:
  - gedmo/doctrine-extensions updated from 2.4.35 to 2.4.36
  - jquery.mmenu updated from 7.0.3 to 7.0.6
  - paragonie/random_compat updated from 2.0.12 to 2.0.17
  - sensio/distribution-bundle updated from 5.0.21 to 5.0.22
  - swiftmailer/swiftmailer updated from v5.4.9 to v5.4.12
  - symfony/monolog-bundle updated from 2.12.1 to 3.2.0
  - symfony/symfony updated from 3.4.11 to 3.4.14
  - twig/twig updated from 1.35.3 to 1.35.4
  - zikula/jquery-bundle updated from 1.0.0 to 2.0.0 (includes update from jQuery 2.1.4 to 3.3.1)
  - zikula/profile-module updated from 3.0.3 to 3.0.5
  - zikula/seabreeze-theme updated from 4.0.2 to 4.0.3

## 2.0.7 (2018-05-28)

- Vendor updates:
  - gedmo/doctrine-extensions updated from 2.4.33 to 2.4.35
  - guzzlehttp/guzzle updated from 6.3.2 to 6.3.3
  - phpspec/prophecy updated from 1.7.5 to 1.7.6
  - symfony/polyfill-* updated from 1.7.0 to 1.8.0
  - symfony/symfony updated from 3.4.8 to 3.4.11

## 2.0.6 (2018-04-13)

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

## 2.0.5 (2018-02-24)

- BC Breaks:
  - Removed matthiasnoback/symfony-service-definition-validator (#3885).
  - Removed \Zikula\Core\AbstractExtension::getBasePath() method. Use getPath() instead (#3862).

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

## 2.0.4 (2017-12-16)

- BC Breaks:
  - CSRF tokens are now different for HTTP and HTTPS (#3856). See [this announcement](https://symfony.com/blog/cve-2017-16653-csrf-protection-does-not-use-different-tokens-for-http-and-https) for more information.

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

## 2.0.3 (2017-11-04)

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

## 2.0.2 (2017-10-03)

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

## 2.0.1 (2017-09-01)

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

## 2.0.0 (2017-08-05)

- BC Breaks:
  - Removed all @deprecated items from Core-1.x.
  - Removed all legacy plugins. Use Symfony Bundles instead.
  - The parameter `framework.session.cookie_httponly` is now set to `true` (#2716).
  - Removed old custom Doctrine extensions listener. Use listeners from stof bundle instead.
  - Removed custom service aliases for Doctrine classes. Retrieve them directly using the entity manager.

- Features:
  - See feature docs

- Vendor updates:
  - matthiasnoback/symfony-console-form updated from 1.2.0 to 2.0.2
  - symfony updated from 2.8.x to 3.3.x (#3027, #2099, #2639).
