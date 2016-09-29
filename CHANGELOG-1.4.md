CHANGELOG - ZIKULA 1.4.x
------------------------

* 1.4.4 (?)

 - BC Breaks:

 - Deprecated:

 - Fixes:
   - Fixed display of checkboxes in topnav login blocks and authentication method selector (#3044).
   - Removed permanent display of template information in html source (#3068).
   - Fixed wrong method call in PageLock locking api (#3089).

 - Features:
   - Added mailProtect filter for safe display of email addresses in Twig templates (#3041).

 - Core-2.0 Features:
   - Added display of template name as html comment in source when in dev environment (#3068).
   - Added shortcut method in variable api for retrieving system variables (#3077).

 - Vendor upgdates:
    - sensio distribution bundle updated from 5.0.8 to 5.0.9
    - sensio generator bundle updated from 3.0.7 to 3.0.8
    - symfony updated to from 2.8.9 to 2.8.11
    - twig updated from 1.24.1 to 1.24.2

* 1.4.3 (2016-09-02)

 - BC Breaks:
    - Assetic Bundle has been removed (#2939).
    - User block function removed. It is going to be added to the Profile module instead.
    - Old Authentication_Method_Api system has been completely removed.
    - Due to refactoring the UsersModule, some unknown BC Breaks may have occurred.
      - `subscriber.users.ui_hooks.login_block` hooks have been removed. use `subscriber.users.ui_hooks.login_screen`
    - Option for having no session for anonymous users has been removed. In Symfony every request has a session.

 - Deprecated:
    -

 - Fixes:
    - Fix error on creation of new ExtendedMenublock.
    - Fix display of blocks using theme overrides (#2872).
    - The legacy \Zikula_Core is now booted even if you use the Symfony Console.
    - Lengthen sessionId column in session table (#2840).
    - Added whitespace trimming functionality for Twig themes (#2911).
    - Fixed getPluralOffset in Zikula.js to return numbers instead of boolean values (#3011)
    - Corrected issue with hook admin url and legacy modules (#2999).
    - Imagine: Use Imagick or Gmagick in favour of Gd (#3016)
    - Add filter to LoginBlock so it doesn't appear on login page (#3021).
    - Improved Installer and Upgrader to check installation requirements before running (#2235, #3023).

 - Features:
    - Add help text, alert text and input groups to forms utilizing the provided form themes (#2846, #2847).
    - Automatically initialise official Twig extensions (#2866).
    - Extend mailer-related events (#2849)
      - MailerEvents::SEND_MESSAGE_START - Occurs when a new message should be sent.
      - MailerEvents::SEND_MESSAGE_PERFORM - Occurs right before a message is sent.
      - MailerEvents::SEND_MESSAGE_SUCCESS - Occurs after a message has been sent successfully.
      - MailerEvents::SEND_MESSAGE_FAILURE - Occurs when a message could not be sent.
    - Lengthen ip address fields for IPv6 support (#2893).
    - More intelligent handling of missing mail transport options (#2148).
    - Enable disabling of unique block wrappers and improve theme flexibility and documentation (#3013).

 - Core-2.0 Features:
    - AdminModule updated to Core-2.0 Spec (#2856, #2860).
    - CategoriesModule updated to Core-2.0 Spec (#2923).
    - GroupsModule updated to Core-2.0 Spec (#2907).
    - MailerModule updated to Core-2.0 Spec (#2866).
    - PageLockModule updated to Core-2.0 Spec (#2862).
    - PermissionsModule updated to Core-2.0 Spec (#2896).
    - RoutesModule updated to Core-2.0 Spec (#2921).
    - SearchModule updated to Core-2.0 Spec (#2853).
    - SecurityCenterModule updated to Core-2.0 Spec (#2890).
    - SettingsModule updated to Core-2.0 Spec (#2832).
    - UsersModule updated to Core-2.0 Spec (#2851)
      - New AuthenticationMethodInterface created.
      - Functionality split into UsersModule and new system module, ZAuthModule.

 - Vendor updates:
    - bootstrap updated from 3.3.6 to 3.3.7
    - font awesome updated from 4.5.0 to 4.6.3
    - jms translation bundle updated from 1.2.1 to 1.3.1
    - js routing bundle updated from 1.5.x to 1.6.0
    - jstree updated from 3.3.0 to 3.3.2
    - monolog updated from 1.18.1 to 1.21.0
    - php-markdown updated from 1.5.0 to 1.6.0
    - php-parser updated from 1.4.1 to 2.0.1
    - sensio distribution bundle updated from 5.0.5 to 5.0.8
    - symfony updated to from 2.8.4 to 2.8.9
    - swiftmailer updated from 5.4.1 to 5.4.3
    - components/jquery-ui updated from 1.11.4 to 1.12.0.1
    - gedmo/doctrine-extensions updated from 2.4.13 to 2.4.22

* 1.4.2 (2016-03-28)

 - BC Breaks:
    - n/a
 - Deprecated:
    - 'Theme switching' by users will not be updated to Core-2.0. Only Admins will be able to change themes.
    - Zikula\ThemeModule\Block\RenderBlock will not be updated to Core-2.0.
    - Zikula\ThemeModule\Block\ThemeswitcherBlock will not be updated to Core-2.0.
    - Zikula\ThemeModule\Controller\AjaxController will not be updated to Core-2.0.
      - the `theme.ajax_request` event is deprecated.
    - "Alternate Site View" feature (in theme settings) is deprecated. Use responsive design.
    - Zikula\Core\AbstractTheme is deprecated and aliased to Zikula\ThemeModule\AbstractTheme. Use the latter.
    - Zikula\Core\Theme\Annotation\Theme is deprecated and aliased to Zikula\ThemeModule\Engine\Annotation\Theme. Use the latter.
    - All Hooks-related classes and files have been moved to HookBundle and former namespaces deprecated (aliases provided for BC).
    - `DisplayHookResponse` has deprecated the third argument and now expects a rendered string instead of objects. (#2600)
    - The visibility of ModuleStateEvent::modinfo property will change from public to private in Core-2.0. Use getModInfo() method instead.
 - Fixes:
    - Fix module stylesheet not being loaded automatically for Core-2.0 modules.
    - Fix SearchModule not working for older modules required tables.php (#2643)
    - Fix core pager tag urls were encoded, now raw.
    - Fix corrupted javascript files (`categories_admin_edit.js`, `categories_admin_view.js`, `contextmenu.js`) caused by
      CI build/Yaml compressor. (#2702, #2707)
    - Fix error in BootstrapTheme where `pagetype` variable was required (#2681)
    - Fix legacy modules always using 'home' realm in Theme engine (#2691)
    - Fix minor display issues for new Symfony 2.8-style developer toolbar.
    - Fix Login block not functional (#2729)
    - Fix display problem with navbar in Bootstrap theme (#2662)
    - Streamlined log size by removing event channel (#2741)
    - Fix registration expiration error when expired user is deleted (#2696)
    - Fix notation for Modules in Menu block (#2654)
    - Fix structure of `categorizable` key in version spec.
        - use `"categorizable": {"entities": ["Acme\FooModule\Entity\FooEntity", "Acme\FooModule\Entity\BarEntity"]}`
        - BC with previous method maintained but deprecated, e.g. `"categorizable": ["Acme\FooModule\Entity\FooEntity", "Acme\FooModule\Entity\BarEntity"]`
    - Fix issues with dynamic url settings in ExtensionsModule
    - Fix problems with legacy Themes (#2777)
    - Fix post installation login (#2187)
    - Improved compatibility of zikula-specific bootstrap overrides with respect to navbars.
    - Improved handling of 'utility' themes via GET and add ability to restrict access via permissions on a more granular level.
    - Correct method arguments provided by BC block method when displaying block. (#2934)
    - Fix block module upgrade (#2957, #2835)
 - Features:
    - Add new advanced block filtering based on a combination of any query parameter or request attributes.
    - Add core routing for all legacy urls (both normal and 'shorturls').
    - Add option to set a controller (e.g. `ZikulaPagesModule:User:categories`) as start page settings instead of legacy method. (#2454)
    - Add theme information to Symfony developer toolbar.
    - Add functional login block to Bootstrap theme (#2730)
    - Add support for 'account' type links in the LinkContainerCollector (#2758)
    - Add collapseable blocks. This feature had disappeared since Core-1.3x (#2678)
    - Add Twig tag `modAvailable($moduleName)` (#2769)
    - Add CsrfTokenHandler service (`\Zikula\Core\Token\CsrfTokenHandler`)
    - Add 'info' type flash messages.
    - Add CLI Symfony Styleguide to CLI install and upgrade (#2667)
    - Add Vagrant support (#2814)
    - Change default theme to Bootstrap theme (new features added, blocks and permissions adjusted specific to theme)
    - Provide method for customizing Bootstrap path in Core-2.0 themes. See ZikulaBootstrapTheme for example.
 - Core-2.0 Features:
    - Add AdminInterfaceController and Twig tags - AdminModule
        - Refactored functions header, footer, breadcrumbs
        - menu - replace old categorymenu action - supports both categries and modules mode as well as panel and tabs templates.
        - add updatecheck, securityanalyzer, developernotices instead of notices
    - Add ExtensionsInterfaceController and Twig tags - ExtensionsModule
        - Refactor functions header, links
        - Introduce footer, breadcrumbs and help - not fully functional
    - Add `currentUser` global variable to twig templates.
    - Add (move) `Zikula\CategoriesModule\Entity\AbstractCategoryAssignment` and related documentation.
        - Replaces `Zikula\Core\Doctrine\Entity\AbstractEntityCategory` (aliased for BC).
        - Add `Zikula\CategoriesModule\Form\Type\CategoriesType` for easier category usage in Symfony Forms.
    - Implement new definition spec for Hook capabilities.
    - Implement new BlockApi and all corresponding methods.
        - Zikula\BlocksModule\BlockHandlerInterface
        - Zikula\BlocksModule\AbstractBlockHandler
        - Updated BlocksModule Admin UI.
    - BlocksModule updated to Core-2.0 Spec.
    - ThemeModule updated to Core-2.0 Spec.
    - ExtensionsModule updated to Core-2.0 Spec (except Plugin Handling).
    - Add AbstractExtensionInstaller for use by third-party developers.
    - Add ExtensionVariablesTrait for developers to insert into classes where Extension Variable management is needed.
    - Update Pending Content logic and definitions.
    - Classes from Zikula\Core\Theme have been moved to Zikula\ThemeModule\Engine.
    - Listener classes from Zikula\Bundle\CoreBundle\EventListener\Theme have been moved to Zikula\ThemeModule\EventListener.
    - Add Zikula\Common\Translator\TranslatorInterface to use as typehint when using `translator.default` service.
    - Add CapabilityApi to manage and define Extension Capabilities for Core-2.0 applications.
    - Update `\Zikula\Bundle\HookBundle\Hook\DisplayHookResponse` to allow response from non-Smarty sources. (#2600)
 - Vendor updates:
    - Symfony updated to 2.8.4
    - Font-Awesome updated to 4.5.0
    - Bootstrap updated to 3.3.6
    - Colorbox (jQuery lightbox plugin) updated from 1.3.20.2 to 1.6.3 (`src/javascript/plugins/colorbox`)
    - Doctrine/Common updated to 2.5.3 and limited to 2.5.x for php compatibility
    - Sensio/Distribution-Bundle updated to 5.0.*

* 1.4.1 (2015-11-23)

 - BC Breaks:
     - Removed `Zikula\Core\Api\AbstractApi` that was introduced only in 1.4.0 (#2494)
     - If you use the Imagine System plugin and add custom transformations with a priority greater than 50,
       these are now applied *after* the thumbnail is generated. (#2594)
     - Removed `app/CustomBundle` but likely this was not used by anyone. (#2622)
     - Removed GroupMembershipEntity. This is very unlikely to have been used outside the Core.
       Group membership is now available directly from GroupEntity::users. A user's memberships are available
       from UserEntity::groups.
     - The change to PSR-4 for system modules (see below) will require fixing template override paths in existing themes.
 - Deprecated:
     - Twig function `pageAddVar()` deprecated. Use `pageAddAsset()` or `pageSetVar()` instead.
 - Fixes:
    - Fixed 'Removetrailingslash' error (#2552)
    - Corrected variable name in BootstrapTheme template override (#2557)
    - Fixed `categories_admin_view.js` not present in 1.4.0 dist (#2637)
    - Routes Module updated to v1.0.1
        - Non-custom routes are no longer stored in the DB. This increases pageload speed and reduces need for reloading often.
    - Fixed 'auto-login' after user registration (#2646)
 - Features:
    - All system modules and themes updated to PSR-4 (#2563, #2424)
    - TranslatorTrait added (#2560)
    - Categorization of Entities enabled (#411)
    - Add vierbergenlars/php-semver vendor lib for version comparison (#2560)
    - Combined and customized bootstrap/font-awesome css using Less.
    - Improved multilingual UI in general settings (#2547)
    - [Imagine Plugin] Possibility to add transformations which are applied after the thumbnail
        is generated. (#2594)
    - Add umask support
 - Core-2.0 Features:
    - Add Twig-based theme engine (refs #1753)
         - Please note that the Blocks functionality of the theme engine is still in heavy development and shouldn't be
           relied upon as a permanent API. Method names and/or signature may change. The following are likely unstable:
            - Zikula\Core\Controller\AbstractBlockController (entire class)
            - Zikula\Core\AbstractTheme::generateThemedBlock
            - Zikula\Core\Theme\Engine::wrapBlockInTheme
    - Add `pageAddAsset()` Twig function and enable 'weighting' of assets to specify load order. (#2606, #2596, #1324)
    - Add `polyfill()` Twig tag to enable JS library inclusion. (#2629)
    - Core-2.0 Theme Specification finalized and enabled (#1753, #2500, #2560)
       - All core themes updated to new spec
       - ZikulaAndreas08Theme updated to new spec and bootstrap (#2428)
    - Core-2.0 Module Specification finalized and enabled (#2500, #2560)
    - Add VariableApi to manage Extension Vars for Core-2.0 applications.
    - Add PermissionApi to manage Rights/Access determination for Core-2.0 applications.
    - Implement method for a module to declare the Entities that are categorizable (#411 - was actually done in Core-1.4.0)
 - Vendor updates:
    - Symfony update to 2.7.7 (#2551, #2582)
        - 2.7.7 includes security fixes.
    - Gedmo Doctrine Extensions updated to version 2.4.x.
    - Twig Extensions updated to version ~1.3.0.
    - Doctrine ORM updated to 2.5.x. (#2613)
        - Includes 'minor' security fix for local access exploits. see http://www.doctrine-project.org/2015/08/31/doctrine_orm_2_5_1_and_2_4_8_released.html
    - Doctrine Bundle updated to 1.5.x (#2614)
    - JQuery MMenu updated to 5.5.1 to fix Prototype compatibility.
    - jsTree updated to latest 3.x version (#2616)
    - php-markdown updated to 1.5.x (#2617)


* 1.4.0 (2015-07-20)

 - BC Breaks:
    - Zikula 1.4.0 requires PHP >=5.4.1
    - Removed interactive installer from module specification.
    - Gedmo Doctrine Extensions Sluggable has changed. See dev docs for changes
    - Renamed the `$registrationInfo` field `nickname` to `uname` to be less OpenID specific and more general.
    - Sessions can no longer be stored in a file. This functionality may return in a later version.
    - Support for IE 7 and below has been removed.

 - Deprecated:
    - DoctrineExtensions Paginator has superseded by Doctrine ORM paginator
      http://docs.doctrine-project.org/en/latest/tutorials/pagination.html
    - Deprecated `Zikula_EventManager` for Symfony2 EventDispatcher component
    - Deprecated `Zikula_ServiceManager` for Symfony2 Dependency Injection component
    - `controller.method_not_found` event is not available in new AbstractController and therefore deprecated
    - Entire contents of `src/lib/legacy` are deprecated, even if not explicitly stated at code level
    - Many items shown at code level

 - Fixes:
    - Fixed Zikula_Doctrine2_Entity_Category::toArray fails when used on proxied category
    - Fixed not working password recovery process if using your email adress
    - Fixed System::queryStringSetVar() does not update the request object (#753).
    - Fixed category tree html encoding problem (#681)
    - Fixed extmenu drag and drop problem (#801)
    - Fixed setState module problem (#843)
    - Deprecated preg_replace() /e modifier in DataUtil replaced (#889)
    - Fixed SecurityCenter - warnings during installation (#880)
    - Fixed ModUtil::getName() inconsistencies (#848)
    - Fixed strip entry point root access (#936)
    - Fixed block filtering by module does an incorrect comparison (#339)
    - Fixed admin-tab problem with content module (#940)
    - Fixed Extensions module pager (#961)
    - Pass-meter was hidden, because no height was set (#997)
    - Do not show multi-lingual user settings if multi-lingual functionality is disabled (#1050)
    - Fixed Admin breadcrumbs does not work with system plugins (#1056)
    - Fixed wrong handling of MinDate in function.jquery_datepicker.php (#1361)
    - Added output sanitizing for authentication module/method in login form
    - Do not register hooks twice (#484).
    - Do not register eventhandlers twice (#727).
    - Several minor bugfixes.

 - Features:
    - Symfony (2.7 LTS version) set as primary library for Zikula
      - Switched to Symfony2 routing, including JS routes, multilingual routes and more (#1788, #1789, #1793, others)
      - Introduced Symfony Forms plugin offering integration helpers like a form type for Zikula categories.
      - Switched to Symfony error handling.
      - Switched to HttpKernel request cycle.
      - [FORWARD COMPAT] Added forward compatibility layer with Symfony2 HttpFoundation

        - `$request->isGet/Post()` should be replaced with `$request->isMethod('GET/POST')`.
        - The GET request is available from `$request->query->get()` and POST from
          `$request->request->get()`.
        - The routing request can be retrieved with `$request->attributes->get($key)`
          using the keys `_controller`, `_zkModule`, `_zkType`, and `_zkFunc`. You MUST NOT rely on `_zkModule`, `_zkType`,
          and `_zkFunc`. They are for core internals only and can be changed or removed at any time.
      - Removed DebugToolbar and replaced with Symfony Debug and Profile Toolbar
    - jQuery is used as primary scripting framework (makes Prototype obsolete) (#844, #1043, #1214, #1752, others)
      - jQuery and jQuery UI are now outsourced to their own bundles.
    - Bootstrap set as primary library for frontend (#845, #1036, #1052, #1073, #1092, #1123, #1149, #1230, #1378, #1706, #1751, #1759, others)
    - Added FontAwesome (#359, #1351)
    - The mailer module uses SwiftMailer instead of PHPMailer (#1717)
    - [FORWARD COMPAT] New module structure.
    - Switched to Composer dependency manager see http://getcomposer.org/ which causes
      dependencies now being managed in a file named `composer.json`
    - Update Smarty to 2.6.28
    - Update Mapstraction to 3.0.0
    - Show an error message if version number of a module is incorrect.
    - Added garbage collection to CSRF token generator
    - Controller methods need to be suffixed with the word 'Action'. Old methods will continue to work.
    - [FORWARD COMPAT] Merged `ajax.php` front controller into `index.php` - please use
      index.php?module=<modname>&type=ajax&func=<func> in AJAX calls.
    - Zikula Form - automatically set proper form enctype when upload input is used
    - Added ModUtil::getModuleImagePath() for getting the admin image of a module
    - Give possibility to set a global timezone_adjust default value.
    - Theme settings: mobile theme different then default; mobile domain; alternative site view
      theme and domain; set admin theme in theme settings section.
    - Give the profile module the possibility to change the profilelink.
    - Added viewplugin `nl2html`.
    - Added hook to Blocks module to allow for use with Html Block (only).
    - Blocks: added display function and preview button in blocks list.
    - Dont send an welcome email to new users function added (#731).
    - The password reminder can be turned off now.
    - The password reminder is turned off if a third-party auth-module is used.
    - 1.2.x to 1.3.x migration script converted to pure php script.
    - Reset start page module to static frontpage if it is deactivated (#104).
    - Added events if a module is activated and if a module is deactivated.
    - Implemented OpenSearch.
    - Added "hybrid" login option. The user can either provide his email address or user name and will be logged in.
    - Added system information page using `phpinfo()` to the settings module.
    - Params delivery from zikula html_select_* to smarty_function_html_options (#1031).
    - Added a third level of warning messages: LogUtil::registerWarning(). These override status messages and are
      overridden by error messages.
    - Added a `reason` key to module dependencies array in module version file.
    - add 'moduleBundle' template variable for 1.4.0-type modules (is NULL for legacy mods)
      instance of `\Zikula\Core\AbstractModule` for current module
    - add 'themeBundle' template variable for 1.4.0-type themes (is NULL for legacy mods)
        instance of `\Zikula\Core\AbstractTheme` for current module
    - Moved Categories to Doctrine2 and moved entities to module. Updated CategoryUtil & CategoryRegistryUtil to use new
    - Copy all category attributes data from `objectdata_attributes` to new `category_attributes` table and adjust
      internal methods to pull from new data source.
    - Removed Errors module, since error handling is now done using Symfony2 mechanisms.
    - Removed support for old function based controllers and APIs (pre-1.3.x style).
    - Development mode is now controlled by editing app/config/kernel.yml `kernel = dev` or `kernel = prod`
    - Removed old 1.2.x legacy (Smarty plugins, hooks etc, old module types).
    - Made it possible to hide the email adress field during registration for external auth modules.
    - Login provider now can specify the path to an icon or the name of a FontAwesome icon to display in the login buttons.
    - Added functionality for authentication modules to redirect the user to the registration screen if the given login
      information does not match an existing user account.
    - Increased SearchResultEntity:extra field from 100 to 1000 chars (#834).
    - Zikula_EntityAccess now also finds getter methods named `isField()` and not only `getField()`.
    - FilterUtil has been updated to work with Doctrine 2 (#118).
    - Added admin access for console commands (#1908).
    - Cookie Warning to ensure EU regulatory compliance in the LegalModule (#728)
    - `PageUtil::addVar()` has been updated to resolve Symfony-styled paths starting with `@MyModule`.
    - Rewrite Installer and Upgrader
      - Now a full-fledged Symfony Bundle (CoreInstallerBundle) using Forms & Twig
      - Includes CLI-based installer and upgrader
      - Auto-creates `custom_parameters.yml` and `personal_config.php`
    - Include Zikula/Wizard - a library to assist in multi-stage user interaction (used in installer)
    - Add Zikula/Component/SortableColumns - helps manage sortable column headers in datatables.
    - Added multilingual support for site name, site description and site meta tags (#2316).
    - Added view plugin {langchange} for switching language, function with shorturls enabled (#2364)
    - Added view plugin {moduleheader} to unify module headers and make styling at one place - moduleheader.tpl (#2372).
    - Added support Webshim, which is a polyfill library that enables you to reliably use HTML5 features across browsers, even if native support is lacking. (#2377)
    - Added mmenu js library and smarty template plugin to create a hidden admin panel
    - Added several Twig tags and filters to duplicate some legacy functionality
    - Added automatic form-theming (bootstrap-style) for Twig-based admin forms
    - Hooks methods moved from event Listener to standard Controller method and given a true route
    - Added support for translating using Symfony Translator.
