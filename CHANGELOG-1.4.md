CHANGELOG - ZIKULA 1.4.0
------------------------

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
- Symfony has been updated to the 2.6 version (#1755, #2076)
- Show an error message if version number of a module is incorrect.
- jQuery is used as primary scripting framework (makes Prototype obsolete) (#844, #1043, #1214, #1752, others)
- Switched to Symfony2 routing, including JS routes, multilingual routes and more (#1788, #1789, #1793, others)
- Added FontAwesome (#359, #1351)
- Complete adaption of Bootstrap for frontend (#845, #1036, #1052, #1073, #1092, #1123, #1149, #1230, #1378, #1706, #1751, #1759, others)
- Added garbage collection to CSRF token generator
- The mailer module uses SwiftMailer instead of PHPMailer (#1717)
- Introduced Symfony2 Forms plugin offering integration helpers like a form type for Zikula categories.

- Controller methods need to be suffixed with the word 'Action'. Old methods will continue to work.
- Deprecated `Zikula_EventManager` for Symfony2 EventDispatcher component
- Deprecated `Zikula_ServiceManager` for Symfony2 Dependency Injection component
- Switched to Composer dependency manager see http://getcomposer.org/ which causes
  dependencies now being managed in a file named `composer.json`
- [FORWARD COMPAT] Added forward compatibility layer with Symfony2 HttpFoundation

  - `$request->isGet/Post()` should be replaced with `$request->isMethod('GET/POST')`.
  - The GET request is available from `$request->query->get()` and POST from
    `$request->request->get()`.
  - The routing request can be retrieved with `$request->attributes->get($key)`
    using the keys `_controller`, `_zkModule`, `_zkType`, and `_zkFunc`. You MUST NOT rely on `_zkModule`, `_zkType`,
    and `_zkFunc`. They are for core internals only and can be changed or removed at any time.

- [FORWARD COMPAT] Merged `ajax.php` front controller into `index.php` - please use
  index.php?module=<modname>&type=ajax&func=<func> in AJAX calls.
- [FORWARD COMPAT] New module structure.
- Added ability to configure a mobile viewing URL, like m.example.com
- Update jQuery-UI to 1.9.2
- Zikula Form - automatically set proper form enctype when upload input is used
- Added ModUtil::getModuleImagePath() for getting the admin image of a module
- Update Smarty to 2.6.27
- Give possibility to set a global timezone_adjust default value.
- Theme settings: mobile theme different then default; mobile domain; alternative site view
  theme and domain; set admin theme in theme settings section.
- Select if the mobile theme shall be applied for smartphones, tablets or both of them.
- Give the profile module the possibility to change the profilelink.
- Added viewplugin `nl2html`.
- Added hook to Blocks module to allow for use with Html Block (only).
- [BC BREAK] DoctrineExtensions Paginator has been removed, use Doctrine ORM paginator
  instead http://docs.doctrine-project.org/en/latest/tutorials/pagination.html
- Blocks: added display function and preview button in blocks list.
- [BC BREAK] Removed interactive installer from module specification.
- Update JqueryMobile to 1.3.0
- Update Mapstraction to 3.0.0
- Mobile Theme now has an configurable block position for startpage.
- Dont send an welcome email to new users function added (#731).
- The password reminder can be turned off now.
- The password reminder is turned off if a third-party auth-module is used.
- 1.2.x to 1.3.x migration script converted to pure php script.
- Reset start page module to static frontpage if it is deactivated (#104).
- jQuery and jQuery UI are now outsourced to their own bundles.
- Added events if a module is activated and if a module is deactivated.
- Implemented OpenSearch.
- Added "hybrid" login option. The user can either provide his email address or user name and will be logged in.
- Added system information page using `phpinfo()` to the settings module.
- Params delivery from zikula html_select_* to smarty_function_html_options (#1031).
- Added a third level of warning messages: LogUtil::registerWarning(). These override status messages and are
  overridden by error messages.
- Added a `reason` key to module dependendies array in module version file.
- add 'moduleBundle' template variable for 1.4.0-type modules (is NULL for legacy mods)
  instance of `\Zikula\Core\AbstractModule` for current module
- add 'themeBundle' template variable for 1.4.0-type themes (is NULL for legacy mods)
    instance of `\Zikula\Core\AbstractTheme` for current module
- Moved Categories to Doctrine2 and moved entities to module. Updated CategoryUtil & CategoryRegistryUtil to use new
- Copy all category attributes data from `objectdata_attributes` to new `category_attributes` table and adjust
  internal methods to pull from new data source.
- Removed DebugToolbar and replaced with Symfony Debug and Profile Toolbar
- Switched to Symfony error handling.
- Switched to HttpKernel request cycle.
- Removed Errors module, since error handling is now done using Symfony2 mechanisms.
- Removed support for old function based controllers and APIs (pre-1.3.x style).
- Removed events: systemerror, setup.errorreporting, frontcontroller.exception.
- Development mode is now controlled by editing app/config/kernel.yml `kernel = dev` or `kernel = prod`
- Removed old legacy (Smarty plugins, hooks etc, old module types).
- Made it possible to hide the email adress field during registration for external auth modules.
- [BC BREAK] Renamed the `$registrationInfo` field `nickname` to `uname` to be less OpenID specific and more general.
- Login provider now can specify the path to an icon or the name of a FontAwesome icon to display in the login buttons.
- Added functionality for authentication modules to redirect the user to the registration screen if the given login
  information does not match an existing user account.
- Increased SearchResultEntity:extra field from 100 to 1000 chars (#834).
- Zikula_EntityAccess now also finds getter methods named `isField()` and not only `getField()`.
- FilterUtil has been updated to work with Doctrine 2 (#118).
- Added admin access for console commands (#1908).
- Cookie Warning to ensure EU regulatory compliance in the LegalModule (#728)
- `PageUtil::addVar()` has been updated to resolve Symfony-styled paths starting with `@MyModule`.

