CHANGELOG - ZIKULA 1.5.x
------------------------

* 1.5.0 (?)

 - BC Breaks:
    - PHP minimum version raised to >=5.5.9.
    - User Categories feature removed.
    - All categories configuration removed.
    - Many methods from CategoryUtil are no longer functional.
    - vierbergenlars/php-semver vendor removed, use composer/semver.
    - Global avatar management is moved to the Profile module.

 - Deprecated:
    - Search block templates have been modified. This will break existing overrides for
      `src/themes/BootstrapTheme/Resources/ZikulaSearchModule/views/Block/search.html.twig`
    - AbstractSearchable is deprecated. Use SearchableInterface instead.
    - CategoryApi deprecated. Use CategoryRepository instead.
    - CategoryRegistryApi deprecated. User CategoryRegistryRepository instead.
    - System vars 'timezone_server' and 'timezone_offset' are deprecated.
    - All contents of `src/javascript`, `src/style`, and `src/images` are deprecated.
        - Core Assets that will be maintained are copied to `CoreBundle/Resources/public/*`.
    - zikula/jquery-minicolors-bundle (which includes https://github.com/claviska/jquery-minicolors) is deprecated.
    - Metakeywords are deprecated as they are no longer considered 'good practice' for SEO (#3187).
    - Gedmo Doctrine extensions should be used by the stof listeners. The old listener names are deprecated.
    - Modernizr javascript library is deprecated and will not be included in Core-2.0.
    - Deprecate full path for bootstrap overrides and use zasset-style notation (#3357).
        - e.g. @AcmeFooModule:css/mybootstrap.css
    - Zikula\Core\Doctrine\Entity\AbstractEntityMetadata is deprecated.
    - Old Workflow feature with all related classes is deprecated.
    - Deprecate \Zikula\ExtensionsModule\Api\ExtensionApi and all constants within. 
        Use \Zikula\ExtensionsModule\Constant for constant values.
    - FilterUtil is deprecated. Use Doctrine's QueryBuilder directly instead (#3569).

 - Fixes:
    - Corrected path to legacy module's admin icons.
    - Made display names of Menu and Theme modules more readable (#3448).
    - Added a general purpose deletion form type (#3333).
    - Fixed initialisation of JavaScript polyfills (#3348, #3486).
    - Fixed wrong link to HTML information pages in security center configuration (#3489).
    - Fixed storage of lastlogin in user object (#3383).
    - Fixed inability to store sessions in files (#2001).
    - Re-enabled CSRF token protection in forms in installer (#2186).
    - CategoryPermissionApi now works but implementation has changed since Core-1.3. Read the docs.
    - Fix minor display issue with admin panel menu (#3449).

 - Features:
    - Added Permission-based controls for MenuModule menu items (#3314).
    - SearchModule refactored to Core-2.0 standards.
    - SearchableInterface adds a method `amendForm()` to amend the search form instead of the old method `getOptions()`
    - Added support for including module dependencies in composer execution using composer merge plugin (#3388, #3437).
    - Added support for Symfony workflow component (#2423).
    - Added WorkflowBundle providing an UI for workflow management (#2423).
    - Automatically initialise basic JavaScript polyfills for forms (#3348, #3486).
    - Added system var 'timezone' for setting the timezone for guest users (replaces 'timezone_offset').
    - Added HtmlFilterApi to filter html strings.
    - Added 'utcdatetime' doctrine column type for storage of utc datetimes (#3383).
    - Added 'Either' authentication type - allows users to enter either uname or email (#2951).
    - Added method to manually convert all users to new ZAuth authentication table (#3278).
    - Improved LocaleApi to localize displayed language names.
    - Add PageAssetApi to ease addition of page assets from controllers and non-templates.

 - Vendor updates:
    - composer/semver installed at 1.4.2
    - doctrine/cache updated from 1.5.4 to 1.6.1
    - doctrine/common updated from 2.5.3 to 2.6.2
    - doctrine/doctrine-bundle updated from 1.5.2 to 1.6.7
    - fduch/workflow-bundle installed at 2.0.2
    - gedmo/doctrine-extensions updated from 2.4.26 to 2.4.28
    - jms/translation-bundle updated from 1.3.1 to 1.3.2
    - jquery.mmenu updated from 5.7.8 to 6.0.2
    - liip/imagine-bundle updated from 1.7.2 to 1.7.4
    - matthiasnoback/symfony-console-form updated from 1.2.0 to 2.3.0
    - monolog/monolog updated from 1.22.0 to 1.22.1
    - paragonie/random_compat updated from 2.0.9 to 2.0.10
    - phpdocumentor/reflection-common installed at 1.0
    - phpdocumentor/type-resolver installed at 0.2.1
    - phpdocumentor/reflection-docblock updated from 2.0.4 to 3.1.1
    - sensio/framework-extra-bundle updated from 3.0.21 to 3.0.25
    - sensio/generator-bundle updated from 3.1.2 to 3.1.4
    - sensiolabs/security-checker updated from 4.0.0 to 4.0.4
    - swiftmailer/swiftmailer updated from v5.4.5 to v5.4.7
    - symfony updated from 2.8.17 to 2.8.19
    - symfony/security-acl update from 2.8.0 to 3.0.0
    - symfony/workflow installed at 3.2.7
    - twig updated from 1.31.0 to 1.33.2
    - vakata/jstree updated from 3.3.3 to 3.3.4
    - vierbergenlars/php-semver removed
    - webmozart/assert installed at 1.2.0
    - willdurand/js-translation-bundle updated from 2.6.3 to 2.6.4
    - wikimedia/composer-merge-plugin installed at dev-master 
    - zikula/andreas08-theme installed at 2.0.0
    - zikula/pagelock-module installed at 1.2.0
    - zikula/seabreeze-theme installed at 4.0.1
