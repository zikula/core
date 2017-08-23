CHANGELOG - ZIKULA 1.5.x
------------------------

* 1.5.1 (?)

 - Deprecated:
    - ?

 - Fixes:
    - Corrected conditional statement for searchable modules (#3752).
    - Corrected path to recent search results in pager (#3654, #3760).
    - Corrected access to category properties on edit (#3763, #3762).
    - Corrected visibility of properties in \Zikula\Core\Doctrine\Entity\AbstractEntityAttribute (#3765).
    - Improved collecting module workflow definitions (#3767).
    - Updated field lengths in HookRuntimeEntity to facilitate longer classnames and serviceIds.
    - Fixed two category issues with legacy modules (#3775, News#172, News#174).
    - Corrected pdo driver name for pgsql (#3783).

 - Vendor updates:
    - jquery.mmenu updated from 6.1.3 to 6.1.4
    - phpdocumentor/reflection-docblock updated from 3.2.0 to 3.2.1
    - phpdocumentor/type-resolver downgraded from 0.4.0 to 0.3.0
    - sensiolabs/security-checker updated from 4.1.1 to 4.1.3
    - symfony/symfony updated from 2.8.25 to 2.8.26
    - symfony/workflow updated from 3.3.5 to 3.3.6


* 1.5.0 (2017-08-05)

 - BC Breaks:
    - PHP minimum version raised to >=5.5.9.
    - User Categories feature removed.
    - All categories configuration removed.
    - Many methods from CategoryUtil are no longer functional.
    - `Zikula\CategoriesModule\Entity\CategoryRegistryEntity::setCategory_id()` removed. use `setCategory(CategoryEntity $c)` instead.
    - vierbergenlars/php-semver vendor removed, use composer/semver.
    - Global avatar management is moved to the Profile module.
    - typeahead.js removed, use jQuery UI.
    - Path to require.js changed (`web/require` instead of `web`) (#3669, #3671).

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
        - e.g. `'@AcmeFooModule:css/mybootstrap.css'`
    - `Zikula\Core\Doctrine\Entity\AbstractEntityMetadata` is deprecated.
    - Old Workflow feature with all related classes is deprecated.
    - Deprecate `\Zikula\ExtensionsModule\Api\ExtensionApi` and all constants within. 
        Use `\Zikula\ExtensionsModule\Constant` for constant values.
    - `FilterUtil` is deprecated. Use Doctrine's QueryBuilder directly instead (#3569).
    - Asset paths which are not using the bundle notation (starting with `@`) must not use a leading slash.
    - `PermissionApi::UNREGISTERED_USER` is deprecated. Use `UsersModule\Constant::USER_ID_ANONYMOUS`
    - `\Zikula\Common\ClassProperties` class has been deprecated.
    - "Hook-like" events in both UserEvents, RegistrationEvents and AccessEvents classes are deprecated
        - These are replaced with other events. Please see the docs.
    - Persisted Hooks are deprecated in favor on tagged, service-defined classes. See docs for more infomation.
        - The concept of hook 'subowners' is fully deprecated and will not be part of Core-2.0
    - `\Zikula\Bundle\HookBundle\Api\HookApi` is deprecated.
    - `\Zikula\Bundle\CoreBundle\DependencyInjection\Compiler\RegisterCoreListenersPass` is deprecated.
    - Special `ZIKULA` environment variables are deprecated.
        - In Core-2.0 you can use runtime environment variables instead:
          https://symfony.com/blog/new-in-symfony-3-2-runtime-environment-variables
    - `\Zikula\Component\SortableColumns\Column#setIsSortColumn()` is deprecated. Use `setSortColumn() instead.
    - The `MetaTagExtension` class is deprecated which includes the Twig function named `setMetaTag`. Use `pageSetVar` instead.
    - Using simple strings as form types in a theme's variables.yml is deprecated, use FqCN instead.
        - Form options are similarly modified, for example a `choices` array must be formatted according to Symfony's
            rules with respect to `choices_as_values`, etc. This changes from Core-1.5 to Core-2.0 which adopts Symfony 3

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
    - Fix handling of 'anonymous' user in PermissionApi (#2800). 

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
    - Added OAuthModule
    - Added `onlineSince(minutes)` twig filter.
    - Add much easier method of adding form children to user registration/creation/edit forms.
    - Added a form type for user name live search using auto completion (#710, #3610).
    - Added Core upgrade event (#2785).
    - New FormAwareHook category added.
    - New Non-persisted hooks added (#2784, #2262, #236).
    - Added avatar display method to ProfileModuleInterface.
    - Added auto-inclusion of theme stylesheets (#3548).
    - Readded possibility to define and manage custom routes (#3625).

 - Vendor updates:
    - behat/transliterator updated from 1.1.0 to 1.2.0
    - composer/installers updated from 1.2.0 to 1.3.0
    - composer/semver installed at 1.4.2
    - doctrine/cache updated from 1.5.4 to 1.6.2
    - doctrine/common updated from 2.5.3 to 2.6.2
    - doctrine/dbal updated from v2.5.12 to v2.5.13
    - doctrine/doctrine-bundle updated from 1.5.2 to 1.6.8
    - ezyang/htmlpurifier updated (dev-master)
    - fduch/workflow-bundle installed at 2.0.2
    - gedmo/doctrine-extensions updated from 2.4.26 to 2.4.30
    - guzzlehttp/guzzle  updated from 6.2.3 to 6.3.0
    - jms/translation-bundle updated from 1.3.1 to 1.3.2
    - jquery.mmenu updated from 5.7.8 to 6.1.3
    - league/oauth2-facebook updated from 1.4.4 to 1.4.5
    - liip/imagine-bundle updated from 1.7.2 to 1.8.0
    - matthiasnoback/symfony-console-form updated from 1.2.0 to 2.3.0
    - matthiasnoback/symfony-service-definition-validator updated from 1.2.6 to 1.2.6.1 (zikula fork)
    - monolog/monolog updated from 1.22.0 to 1.23.0
    - paragonie/random_compat updated from 2.0.9 to 2.0.10
    - phpdocumentor/reflection-common installed at 1.0
    - phpdocumentor/type-resolver installed at 0.2.1 and updated to 0.4.0
    - phpdocumentor/reflection-docblock updated from 2.0.4 to 3.2.0
    - phpunit/phpunit updated from 4.8.35 to 4.8.36
    - sebastian/diff updated from 1.4.1 to 1.4.3
    - sensio/distribution-bundle updated from 5.0.18 to 5.0.20
    - sensio/framework-extra-bundle updated from 3.0.21 to 3.0.26
    - sensio/generator-bundle updated from 3.1.2 to 3.1.6
    - sensiolabs/security-checker updated from 4.0.0 to 4.1.1
    - swiftmailer/swiftmailer updated from v5.4.5 to v5.4.8
    - symfony/polyfill-* updated from 1.3.0 to 1.4.0
    - symfony/symfony updated from 2.8.17 to 2.8.25
    - symfony/security-acl updated from 2.8.0 to 3.0.0
    - symfony/swiftmailer-bundle updated from 2.4.2 to 2.4.3
    - symfony/workflow installed at 3.2.8 and updated to 3.3.5
    - twig/twig updated from 1.31.0 to 1.34.4
    - vakata/jstree updated from 3.3.3 to 3.3.4
    - vierbergenlars/php-semver removed
    - webmozart/assert installed at 1.2.0
    - willdurand/js-translation-bundle updated from 2.6.3 to 2.6.5
    - wikimedia/composer-merge-plugin installed at v1.3.1 and updated to 1.4.1
    - zikula/andreas08-theme installed at 2.0.1 and updated to 2.0.2
    - zikula/bootstrap-bundle updated to 3.0.1
    - zikula/filesystem updated to 1.0.0
    - zikula/jquery-bundle updated to 1.0.0
    - zikula/jquery-minicolors-bundle updated to 1.0.0
    - zikula/jquery-ui-bundle updated to 1.0.0
    - zikula/fontawesome updated to 4.1.0
    - zikula/legal-module updated to 3.1.0
    - zikula/pagelock-module installed at 1.2.0 and updated to 1.2.3
    - zikula/profile-module updated to 3.0.0 and updated to 3.0.2
    - zikula/oauth-module installed at 1.0.1 and updated to 1.0.3
    - zikula/seabreeze-theme installed at 4.0.1 and updated to 4.0.2
