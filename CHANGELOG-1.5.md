CHANGELOG - ZIKULA 1.5.x
------------------------

* 1.5.0 (?)

 - BC Breaks:
    - PHP minimum version raised to >=5.5.9.
    - User Categories feature removed.
    - All categories configuration removed.
    - Many methods from CategoryUtil are no longer functional.

 - Deprecated:
    - Search block templates have been modified. This will break existing overrides for
      `src/themes/BootstrapTheme/Resources/ZikulaSearchModule/views/Block/search.html.twig`
    - AbstractSearchable is deprecated. Use SearchableInterface instead.
    - CategoryApi deprecated. Use CategoryRepository instead.
    - CategoryRegistryApi deprecated. User CategoryRegistryRepository instead.

 - Fixes:
    - Corrected path to legacy module's admin icons.
    - Made display names of Menu and Theme modules more readable (#3448).
    - Added a general purpose deletion form type (#3333).
    - Fixed initialisation of JavaScript polyfills (#3348, #3486).
    - Fixed wrong link to HTML information pages in security center configuration (#3489).

 - Features:
    - Added Permission-based controls for MenuModule menu items (#3314).
    - SearchModule refactored to Core-2.0 standards.
    - SearchableInterface adds a method `amendForm()` to amend the search form instead of the old method `getOptions()`
    - Added support for including module dependencies in composer execution using composer merge plugin (#3388, #3437).
    - Added support for Symfony workflow component (#2423).
    - Added WorkflowBundle providing an UI for workflow management (#2423).
    - Automatically initialise basic JavaScript polyfills for forms (#3348, #3486).

 - Vendor updates:
    - doctrine/cache updated from 1.5.4 to 1.6.1
    - doctrine/common updated from 2.5.3 to 2.6.2
    - fduch/workflow-bundle installed as 2.0.2
    - gedmo/doctrine-extensions updated from 2.4.26 to 2.4.27
    - jquery.mmenu updated from 5.7.8 to 6.0.0
    - liip/imagine-bundle updated from 1.7.2 to 1.7.4
    - monolog/monolog updated from 1.22.0 to 1.22.1
    - paragonie/random_compat updated from 2.0.9 to 2.0.10
    - phpdocumentor/reflection-common installed at 1.0
    - phpdocumentor/type-resolver installed at 0.2.1
    - phpdocumentor/reflection-docblock updated from 2.0.4 to 3.1.1
    - sensio/framework-extra-bundle updated from 3.0.21 to 3.0.25
    - sensio/generator-bundle updated from 3.1.2 to 3.1.4
    - sensiolabs/security-checker updated from 4.0.0 to 4.0.2
    - swiftmailer/swiftmailer updated from v5.4.5 to v5.4.6
    - symfony updated from 2.8.17 to 2.8.18
    - symfony/security-acl update from 2.8.0 to 3.0.0
    - symfony/workflow installed as 3.2.4 and updated to 3.2.5
    - twig updated from 1.31.0 to 1.33.0
    - webmozart/assert installed at 1.2.0
    - willdurand/js-translation-bundle updated from 2.6.3 to 2.6.4
    - wikimedia/composer-merge-plugin installed as dev-master 
