CHANGELOG - ZIKULA 1.3.11
-------------------------

- Load correct theme in case Mobile theme is enabled and Mobile detect is enabled (#2583)
- Add filter parameter to Menutree in order to work better with Clip (#2597)
- Fix user block is not shown and not editable (#2891)


CHANGELOG - ZIKULA 1.3.10
-------------------------

- Added missing hook area registration for HTML blocks
- Show registration errors in current language
- Improved language detection when using short urls
- Fixed error messages with new module that is not compatible (#1641)
- Corrected behaviour of CategorySelector if no category is selected.
- Added optional loading of newer jQuery 1.11.2 with jquery-migrate (#2223)
- Changed loading via pagevar to make sure prototype is always loaded before jQuery (#2170)
- Updated Imagine lib to 0.6.2 and plugin is enhanced with width/height autoscale and jpg/png quality (#2174, #1644)
- New view plugin {langchange} for switching language, also function with shorturls enabled (#2356)
- Multilingual site name, site description and site meta tags (#2358)
- Added view plugin {moduleheader} to unify module headers and make styling at one place - moduleheader.tpl (#2371).

CHANGELOG - ZIKULA 1.3.9
------------------------

- Added output sanitizing for authentication method module in login form error message


CHANGELOG - ZIKULA 1.3.8
------------------------

- Enabled transactions (#796, #1729)
- Updated Smarty to v2.6.28 (#1613)
- Accept "0" as a non-empty value in ValidationUtil (#1627)
- Added csrf token to user export function (#1630)
- Fixed php strict message in admin panel (#1632)
- Fixed spelling error in Imagine plugin (#1646)
- Fixed conflict between Bootstrap and Prototype (#1723)
- Added display hook area for html blocks (required for using Scribite) (#1726, #1743)
- Fixed invalid output of useravatar plugin (#1734)
- Fixed DBUtil regression bug introduced in 1.3.7 (#1810, #1815)
- Added output sanitizing for authentication module/method in login form


CHANGELOG - ZIKULA 1.3.7
------------------------

- Fixed unsanitized inputs (Secunia Advisory SA56274)
- Fixed deprecated /e modifier to preg_replace (PHP 5.5) (#889)
- Fixed Workflow handling (needed for MOST-Modules) (#704)
- Fixed dragging and dropping in extmenu (#924)
- Fixed Zikula_Form_Plugin_PostBackFunction name capitalization (#916)
- Fixed Block filtering (#516, #938)
- Fixed CSS for displaying selection boxes (#775)
- Fixed allowing jpeg files next to jpg in userdata (#783)
- Fixed siteoff problems in 1.3.6 (#1486)
- Fixed flush during category selection (#1561, #1566)
- Fixed login block issue due to template comment inclusion with IE<10 (#666)
- Fix for admin panel and extmenu (#80, #665, #924)
- Fixed Zikula_Doctrine2_Entity_Category::toArray fails when used on proxied category (#598)
- Smarty Error when no admin.png is available (#593)
- Get modimage by ModUtil::getModuleImagePath() (#607)
- Fix E_NOTICES in Extensions/templates/extensions_hookui_hooks.tpl
- Fixed bugs in DBObject (#616, #617, #618)
- Fixed Formutil z-formrow - label - input type file looks bad on Safari (webkit) (#621)
- Added fileupload input styling Webkit (#622)
- Fix developer notices (#564)
- Fixed decoding of Block content which made problems on some servers


CHANGELOG - ZIKULA 1.3.6
------------------------

Fixes:
- Fixed variable sanitization in templates in Users module #1353


CHANGELOG - ZIKULA 1.3.5
------------------------

Fixes:
- Fixed return of DBUtil::selectExpandedObjectCount(), should not return false for 0 count (#512)
- Fixed error related to wrong case use in the Mobile theme (#529)
- Fixed ValidationUtil::validateField() return if a validation error for given field already exists (#534)
- Fixed JSCCUtil attempting (incorrectly) to combine remote scripts/styles (refs #435)
- Fixed minor typo in UserUtil::loginUsing() that prevented correct usage of function
- Fixed issue with setting active panel by element ID in Zikula.UI.Panels
- Fixed display of translated date in jQuery datepicker

Features:
- Added development/debug email mode
- Added basic setup for jQuery ajax to pass csfr token checks
- Added new option for Zikula UI Windows - autoClose
- Update jQuery-UI to 1.9.1
- Update jQuery to 1.8.3
- Mark active item in modulelinks
- Mobile Theme: Changed blockposition in home.tpl to 'mobile'
- Added thumbnail management features to Imagine plugin
- Refactor Mailer module to use Zikula Form
- Zikula Form - automatically set proper form enctype when upload input is used


CHANGELOG - ZIKULA 1.3.4
------------------------

- Update jQuery-UI to 1.8.23
- Update jQuery to 1.8.1
- Fixed invalid URL send in Password recovery (#443)
- Fixed users module failing to send password recovery (admin) (#459)
- Fixed category management migration 1.2.6 -> 1.3. The display_name and display_desc are updated fine during upgrade from Zikula 12x to Zikula 134 (#69)
- Fixed checks in DateUtil::getDateFormatData (#198)
- Added message about clearing the database when re-install is needed (#200)
- Updated config.php for PHP 5.4 compatibility with explicit E_STRICT excluding in production (#451)
- Fixed php warning with array usage in Users_Controller_Admin (#440)
- Added a param minLength to the Textinput form plugin (#445)
- Added mandatory Param for checkboxes to the Checkbox form plugin (#456, #457))
- Adjusted form contextmenu to work again in IE browsers for submitting from a hidden div (module Content uses this a lot) (#423)
- Fixed access to admin in IE8 (#420)
- Fixed no redirect from session expired page (#416)
- Fixed JS error in IE8 when clicking a submit button (#451)
- Fixed normalized paths in Menutree, especially for custom templates/styles (#414)
- Fixed formcategorycheckboxlist plugin to enable multiple category selection with Doctrine2 (#403, #404)
- Fixed creating a category via right click (#316)
- Added ability to edit empty menuitems (with a space char) in Menutree, although using stylesheets is the recommmended way (#264)
- Fixed Contextmenu in admin area to work in Opera browser (#258)
- Fixed error message when uninstalling a module (#231)
- Fixed incoherent permissions in Users_Controller_Admin (#167)
- Added ability to generate an overrides.yml file (#136)
- Extended DateUtil::getdatetime with an option for not translating dates, backwards compatible (#81)
- Fixed mailer smtp password being stored too short (#441)
- Added correct Categories in the ExampleDoctrine module (#401)
- Fixed using setSelectedValue in CategorySelector if there is no selected category (#479)
- Added option that a theme can avoid the loading of core.css, e.g. Mobile themes (#453)
- Fixed typo causing interactive installers not being recognised (#448)
- Fixed blocks migration from Zikula 1.2 to Zikula 1.3 (#406)
- Added reimplementation of a core solution for pagetitles/sitenames, (#237)
- Added check for outdated config.php in upgrade.php (#143)
- Fixed url of preview theme link (#493)
- Fixed formfloatinput plugin localisation (#462)
- Fixed localisation of formdateinput (#496)
- Fixed broken dateinput handling with time (#199)
- Fixed in IE8, clicking on a submit button, gives js error 'Object required' (#415)
- Added a default mobile theme (#436)
- Fixed strict standards: Non-static method ZLanguage::getLanguageCodeLegacy() should not be called statically (#442)
- Fixed ModulePlugins do not add the right template directory (#452)
- Fixed incorrect object field reference in DBObject.php (#494)
- Fixed module.users.ui.logout.succeeded logout event (#508)


CHANGELOG - ZIKULA 1.3.3
------------------------

- Change order in auto generated cache_id for theme caching to: module / type / function / customargs|homepage/startpageargs / uid_X|guest.
- In Admin -> Themes -> Settings: added possibility to delete theme cache for specific module or homepage.
- In Admin -> Themes -> Settings -> 'Length of time to keep cached theme pages' current value is for home page, added other value for modules.
- Addition of jQuery-UI
- update jQuery to latest stable version
- Conversion of Admin and Blocks modules to Doctrine 2


CHANGELOG - ZIKULA 1.3.1
------------------------

- Fixed issue with FileUtil::getFiles() not respecting nestedData arg (#3139).
- Updated DoctrineExtensions.
- Added Imagine image manipulation library - https://github.com/avalanche123/Imagine
- Added standard-fields, attributable, category and metadata extensions for Doctrine 2.
- Fixed DoctineUtil::renameColumn() - was corrupting TEXT to VARCHAR(255) and TINYINT(1) for TINYINT(4) when renaming.
- Added support for DateTime objects to DateUtil.
- Fixed XSS issues in Theme module.
- Clean up remaining table prefix reference closes #77.
- Increased hook name field lengths from 60 to 100 characters.
- Upgraded jQuery to 1.6.3.
- Fixed issue with DoctrineExtensions plugin and short URLs - issue #91.
- Adjusted installer to handle multilingual custom SQL files.
- Upgraded PHPIDS to 0.7.0.
- Fixed issue where restoring defauls for a module, the localization wasn't used (#71)
- Fixed issue where pager wasn't able to parse array with three dimensions (#111)


CHANGELOG - ZIKULA 1.3.0
------------------------

- Moved minimum PHP version to 5.3.2.
- Removed support for PHP register_globals and magic_quotes_gpc.
- Created new OO MVC module format.
- Introduced event notification system and added various events through out the core execution cycle for easy customisation and extension.
- Added system-wide and module specific event based plugin system (called 'system plugins' and 'module plugins'.
- Introduced a completely new module hooks system.
- Deprecated the old hooks system.
- Removed ADODB.
- Introduced Doctrine 1.2.4 and shifted entire object library to use Doctrine.
- Introduced Doctrine 2.0.5 as a plugin (was introduced after the PHP requirements were changed mid development).
- Introduced SwiftMailer as a plugin service.
- Upgraded to PHPMailer 5.1.
- Replaced SafeHTML with HTMLPurifier.
- Added PHPIDS intrusion protection support.
- Introduced LivePipe UI library.
- Added support for jQuery 1.6.1. with noConflict automatically set.
- Created a new Zikula.UI ajax interface and library.
- Large overhaul of interfaces to introduce AJAX into administration and give a more modern look and feel in the administration areas.
- Replaced CSRF protection system with new token system to make false hits less frequent and does not break with tabbed browsing.  Introduced an additional option which does not interfere the browser back button.
- Improved installer - less steps, less fiddling required and no need to
delete installation/upgrade scripts and less post install/upgrade steps like the requirement to delete install- and upgrade.php to access
administration.
- Merged all templating and rendering control to Theme module and overhauled caching.

Users Module
------------

There has been a significant overhaul of Users module:

  - Admin can now resend a verification e-mail for a pending registration.

  - Admin can now send a password reset e-mail to a user.

  - Improved password recovery workflow. (#1631).

  - Added user name recovery. (#243).

  - Removed old Authentication API.

  - Changed old password checker js sytem to "passwordchecker" library (#1841).

  - Introduced new Authentication API capable of dealing with modern
authentication (login) methods.

  - The Users module configuration variable 'changepassword', which
indicated that the Users module did not manage an account's password, has
been deprecated. To provide alternate management, override the appropriate
template(s) and/or function(s). To provide full authentication services,
implement the Authentication API in a module.

  - The mechanics of the authentication (login) process have changed, and
the templates and functions have been updated accordingly. Templates that override the standard templates used for the login process will need to be
updated, or removed.

  - Added the ability to register a new account that is associated with an
authentication method other than a username and password (Users module
authentication).

  - The mechanics of the registration process have changed, and the
templates and functions have been updated accordingly. Templates that
override the standard templates used for the login process will need to be
updated, or removed.

  - Several functions throughout the Users module have been either
deprecated or removed entirely, in order to support the new log-in and
registration paradigms. Customizations at the function level must be
updated accordingly.

  - Elements of the Legal module that were found in the Users module have
been removed, and their functionality replaced with hooks or events. The
age-check during registration has moved to the Legal module.

  - Removed MD5 as a valid hash method for user passwords.

  - Permission checks in the Users module were made more consistent
throughout (#1872).

  - All e-mail messages sent by the Users module can now be
multi-part/alternative messages, containing both a plain-text version and
an HTML version of the message. Templates for both are provided and
automatically used if present.

  - The subject line of all e-mail messages sent by the Users module can now be set from within the e-mail message template. See the templates provided for examples. If multi-part/alternative messages are to be sent, the subject from the HTML version of the message is used (however, the subject should not use HTML).

  - The template file names for all e-mail messages sent from the Users
module have changed. If a site upgrading from 1.2 has created custom
templates for these messages, they should be converted to the new names,
and the ability to send multi-part/alternative messages should be
accounted for.

  - If an attempt is made to log into an account that is pending
registration (either because it is awaiting approval, or is awaiting
verification, or both), the site admin can elect to display that status to
the user in the error message on log-in failure.

  - If an attempt is made to log into an account that is inactive (either
because or is awaiting legacy activation, or because the admin set that
activated state for the user), the site admin can elect to display that
status to the user in the error message on log-in failure.

  - The password recovery work flow has been improved to be clearer to the
user.

  - The new account registration process now collects a password reminder,
and displays this reminder to the user as part of the password recovery
process. For existing sites upgrading to 1.3, existing users will be asked
for a password reminder when they change their password.

  - The option to have the system generate a password and send it to a newly
registering user has been removed as a verification option. Sites
upgrading to 1.3 who have this option set will find this option changed to
verification with a user-selected password.

  - Except in one case, passwords are no longer sent via e-mail to newly
registering users. The one exception is when an admin creates a user
account, sets a password for that account himself, and specifically elects
to send the password via e-mail.

  - Confirmation or verification codes for registration verification,
verification of change of e-mail address, and for password reset requests
all now use the same mechanism and are handled consistently.

  - Requests to change e-mail addresses that go unverified can be set to
expire and be removed a specified number of days after the request is
made.

  - Requests to register a new account where e-mail address verification is required can be set to expire and be removed a specified number of days
after the e-mail verification message is sent. Registrations whose e-mail
verification expires will be removed from the system. Registrations
awaiting approval will not expire until after they are approved.

  - If the registration process is configured for both moderation (admin
approval) and e-mail verification, then the order in which these occur can
be set. Verification can be required before approval, after approval, or
at any time before or after. The admin can override the order and cause
one or the other process to occur at any time.

  - Error checking of registration and user account fields has been improved and unified.

  - All user names and e-mail addresses are now stored in (and thus
displayed in) lower case.

  - New user names are required to consist of only letters, numbers,
underscores and periods. Accented and other non-ASCII characters are
permitted as long as their Unicode type is set to the letter or digit
group.

  - When retrieving user accounts (for any purpose, including for logging
in) by e-mail address, a duplicate e-mail address check is performed even
if the Users module is set to require unique e-mail addresses. This is to
prevent the wrong account from being returned if duplicate e-mail
addresses were allowed at any point or were added by the administrator.

  - A user with rights to delete user accounts is prevented from deleting
his own account.

  - The registration date, last login date, and all other dates saved to the database tables by the Users module are now guaranteed to be UTC
date/times in the database. They might be adjusted for display by other
functions, however.

---

- Introduced new module AJAX workflow.

- Added advanced feature for themes to process AJAX via their own native controllers.

- Removed and replaced debugging architecture.

- Added debugging toolbar (configurable in config.php).

- Added database caching capabilities to DBUtil.

- Changed default template plugin delimiter to curl brackets.

- Fixed UserUtil::getVars & UserUtil::getIDFromName functions can retrieve
by all-numeric username.

- Removed on-the-fly JS minifier for performance reasons.

- Removed the need to 'regenerate' module list, this is now done
automatically each time the module list is viewed.  It will also
automatically remove invalid modules that have been removed from the file
system.

- Fixed problem with deleting group via ajax with insufficient permissions
(#1568).

- Fixed an issue where an incorrect group type was displayed when editing a 
group through the administration panel (#2993).

- Allow module to specify core version requirements.

- Added missing icons for blocks collapsible function. (#1847).

- Moved search procedure to api function. (#1859).

- Admin panel icons are shown to a user if that user has edit access for
at least one instance. (#1026).

- When accessing the site through the admin.php entry point with no module
specified, if the user is not logged in then he is redirected to a login
screen. (#1729).

- Corrected minor defects that generated notices (#1901, #1902).

- The Mailer module can now send multipart/alternative e-mails with the
specification of a plain-text altbody (#1768).

- Fix a problem with the encodeurl function of the Search module (issue
#1866).

- Added {modulelinks} navigation plugin (#1238).

- Added new {helplink} plugin for documentation with ability to use Zikula.UI.

- Searchbox toggle (#1810).

- Link to help page for each security alert (#1692).

- Removed the requirement for block templates to specify language domain manually.

- Added ability to check all radios in HTML Settings (#1551).

- New APIs DataUtil::decodeNVP(), DataUtil::encodeNVP(),
DataUtil::encodeNVPArray().

- Adapted the Extensions module for a Zikula multisites system with
multiple domains (#1968).

- Modify html maxlength of block title to reflect the database structure.
(#1980).

- Addition of Zikula_FileSystem class libraries to allow easy interaction with
file systems via local/ftp(s)/sftp (#1517).

- Ability to export CSV file from users module added. (#1954).

- Escaped illegal char in pagelock template (#2004).

- Added minute based refresh times for blocks (#1999).

- Added ability to administrate the "Admin Panel" with using AJAX, drag and drop to move modules to different categories, create/edit/delete admin categories via right click (#1919).

- Improved and simplified `.htaccess` rulesets.

- Altered ZLanguage::countryMap() for Sweden from sv to se.  Note the
language code should be so, but the country code should be se. (issue
#2017).

- Blocks module admin section now has filtering options for
block-position, module, language and active-status. The allows remove of
the old showall/showinactive link in the admin section. (#2012,
#2020)

- Blocks module now has sorting options for the main columns in the admin
view. (#2012).

- Fixed Zikula_Form_View::registerPlugin() in environments where the
installation is not in the server document root.

- Overhauled Category administration interface with Zikula.UI.

- Added adapter to support the illegal use of DBUtil::executeSQL()
processing the ADODB object manually (without the use of
DBUtil::marshallObjects().

- Fixed theme list not sorted correctly on Theme view (#1974).

- Fixed pager plugin images always in english (#1883).

- Fixed wrong contents in modvar 'permareplace' (#2044).

- Updated css messages (#2043, #2030).

- Added support for HTTP 500 response in Errors module.

- Fixed issue with system vars and modvars that prevented retrieval of a
stored NULL value which would return the default value instead.

- Added exception support in front controller and module controllers.

- Added const render plugin and modifier to allow class constants in
templates.

- Added front controller exception handling.

- Removed Zikula_View singleton pattern, this is now handled via
Zikula_ServiceManager, one instance per module or plugin.

- Removed need to specify domain= in template {gt} calls.

- Improved StringUtil::highlightWords() (patch by Gabriele Pohl).

- When short URLs are enabled, 3-letter or 2-letters can't be used
anymore. This also fixes issues with the RSS theme. (#1800).

- Fixed validation of directory based short URLs to produce 404 if target
not found (#923).

- Removed support for filebased short URL rewriting.

- Deprecated FileUtil::mkdirs(), use native PHP mkdir() with $recursion
flag set true instead.

- Merged ObjectData and Workflow table definitions to Settings and deleted
the modules - don't need separate modules just to provide table
definitions.

- tables.php: $module_column is now unnecessary if the there is no column
prefix, i.e. if name => name.

- DataUtil::formatForOS() not Windows file path compatible (#1838).

- New location for core stylesheet in /style/core.css (#2211).

- Deprecated ZFeed and SimplePie from the core.

- New location for core stylesheet in /style/core.css (#2211).

- Added @import handling to css combiner (#1801).

- New button styling (#1574).

- Relocated system fatal error templates (siteoff.tpl, notinstalled.tpl,
dbconnectionerror.tpl and sessionfailed.tpl) to
system/Theme/templates/system.

- Add date in Zikula error log file (#2209).

- Allow override of style/core.css with config/style/core.css.

- Provide 'pageutil.addvar_filter' event to override anything added by
PageUtil::addVar, {pageaddvar}, or {pageaddvarblock}.  This allows for
complete override freedom.

- Added new default pager style (#2264)

- Streamlined user frontend of Themes module (#2279, #3034).

- Added Upgrade All options, and Module API to upgrade all modules with
one click.  This will work for all non-complicated upgrades of Core also.

- Fixed LogUtil errors in CategoryUtil (#2276).

- Admin icons update of the system modules. (#2300).

- tables.php now supports index options in the _column_idx array.
array(inxname, array('columns' => array(fld1, fld2..), 'options' =>
'unique') (#1885).

- Added the Menutree into the Blocks module.

- Added horizontal and vertical drop down menu examples for Menutree. (#2313).

- Added htmlentities modifier to properly convert utf8 chars to html
entities.

- Replaced SetEnvIf with FilesMatch in .htaccess files. Removed extensions
tif, flv, ico, cur from all .htaccess, swf from all except modules/ and
html from all except system/Theme/includes (#2334).

- Added FileUtil::exportCSV() to simplify data export to csv files.

- Themes can now process ajax request natively (#2326).

- New block with User account links (#2374).

- Deprecated DataUtil::parseIniFile(), use native PHP parse_ini_file()
instead.

- The Mailer module can now send text-only, HTML-only, and
multi-part/alternative e-mail messages. Multi-part/alternative messages
contain both a plain text message and an HTML-formatted message. If a
recipient's e-mail client does not support HTML messages, then the
plain-text message will be displayed to him.

- Added option to translate the language changer block option into native
language. (#2119).

- Added {gettext} block (#2414).

- Changed the label for the user's account activated status from
'Activated' to 'Status' in the administrator's Users manager.

- The users list in the administrator's Users manager is now sortable by
user name, uid (internal ID), registration date, date last logged in, and
status.

- Added markdown and markdown-extra support and with Smarty modifiers
(#2487).

- Added doctrine support to DropdownRelationlist form view plugin. (issue
#2442).

- Added more HTML5 tags to allowed HTML settings page (#2139 and #2460).

- Fixed conflict in search module short-urls url (#2494).

- Fixed minor issue in uploadinput form plugin (#2551).

- Added missing member var for display area format in dateinput form
plugin (#2552).

- Added doctrine support to selectmodobject(Array) view plugins (issue
#2542).

- Included generic url routing classes (#2557)

- Added a new attribute, precision, to the formfloatinput Form plugin,
which controls the number of digits after the decimal (#2616)

- The {userprofilelink} Smarty modifier was splitted on profilelinkbyuid and {profilelinkbyuname} to avoid problems with numeric usernames (#2971).

- Fix bad icon transparency.

- Recoded all icons to PNG format (#2831).

- Fix inconsistency with styles/ vs. style/ folders (#2805).

- Cleanup RSS icons (#2830).

- Altered style for number fields(#2757).

- Activate new Categories by default (#2748).

- Switchdisplaystate rendering Bug in IE7 and Chrome (#2714,
#2707).

- CSS issues in IE and Chrome (#2685, #2702).

- Removed smilies removed (#2640).

- New color picker solution (#2634).

- Created easy drop-menu for module (admin) links (#2646).

- Dropdown menus (#2649).

- Added a new template block function
{pageaddvarblock}...{/pageaddvarblock} to allow inline scripts, styles,
etc. to be placed in the page's head section or near the closing body tag.

- Renamed the page variable 'rawtext' to 'header' (Used with PageUtil,
{pageaddvar}, {pagesetvar}, etc.). If operating in legacy mode, 'rawtext'
will map to 'header', if not then 'rawtext' will be treated as just
another page variable name, and will NOT render to the page.

- Enabled support of persistent data on Zikula_Form_View to let Form
Handlers store data through the process (#3023).

- Theme config .ini files now can handle their own variables, overriding
the themevariables.ini general ones (#3034).

- Forms framework has been extended to allow multiple forms on a single page and does not break in a tabbed environment.

- improved functionality and usability of advanced block filtering (#2875)

- Fixed CSS/JS combination not working if using personal_config.php (#2916)

- Fixed Render and Theme cache (#3030)

- Theme Admin Panel fixed. Theme config .ini files now can handle their own variables, overriding the themevariables.ini general ones. (#3034)

- Changed template override system from 'scan everywhere' to explicit override mapping.

- Added indexes to object_attribution (#3048).

- Clear theme cache automatically when it gets disabled (#2743).

- Optimization of the admin templates (#3088).
