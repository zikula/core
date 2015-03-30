CHANGELOG - ZIKULA 1.4.0
------------------------

BC Breaks:
- Removed interactive installer from module specification.
- Gedmo Doctrine Extensions Sluggable has changed. See dev docs for changes
- Renamed the `$registrationInfo` field `nickname` to `uname` to be less OpenID specific and more general.
- Sessions can no longer be stored in a file. This functionality may return in a later version.
- Support for IE 7 and below has been removed.

Deprecated:
- DoctrineExtensions Paginator has superseded by Doctrine ORM paginator
  http://docs.doctrine-project.org/en/latest/tutorials/pagination.html
- Deprecated `Zikula_EventManager` for Symfony2 EventDispatcher component
- Deprecated `Zikula_ServiceManager` for Symfony2 Dependency Injection component
- Many items shown at code level

Fixes:
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

Features:
- Symfony (2.6 version) set as primary library for Zikula
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
- Added multilingual support for site name, site description and site meta tags (#2316).
- Added view plugin {langchange} for switching language, function with shorturls enabled (#2364)
- Added view plugin {moduleheader} to unify module headers and make styling at one place - moduleheader.tpl (#2372).
- Added support Webshim, which is a polyfill library that enables you to reliably use HTML5 features across browsers, even if native support is lacking. (#2377)
