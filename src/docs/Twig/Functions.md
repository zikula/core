Twig Functions Provided by Zikula Core
======================================

The following twig functions are available in templates. These are in addition to the standard functions provided
by the Twig package itself. See [Twig Documentation](http://twig.sensiolabs.org/documentation) for more information.
Also see [standard Symfony functions](http://symfony.com/doc/current/reference/twig_reference.html) for additional
functions, filters, tags, tests and global variables.

#### Note:
Zikula Core provides a `zikula_default` package for use in the `asset()` twig function which allows the developer
to access the core root directory e.g. `asset('images/logo_with_title.png', 'zikula_default')`

Functions
---------

### Useful

 * array_unset(array, key)
 * callFunc(callable, params = [])
 * defaultPath(extensionName, type = "user")
 * dispatchEvent($name, GenericEvent $providedEvent = null, $subject = null, array $arguments = [], $data = null)
 * getModVar(module, name, default = null)
 * getSystemVar(name, default = null)
 * hasPermission(component, instance, level)
 * modAvailable(modname, force = false)
 * pageAddAsset(type, value, weight = 100)
 * pageAddVar(name, value)
 * pageGetVar(name, default = null)
 * pageSetVar(name, value)
 * pager(params)
 * polyfill(features = ["forms"])
 * setMetaTag(name, value)
 * showflashes(params = [])
 * stateLabel(extensionEntity, upgradedExtensions = null)
 * zasset(path)

### Hooks

 * notifyDisplayHooks(eventName, id = null, urlObject = null)

### Translation/Language

 * __(message, domain = null, locale = null)
 * __f(message, params, domain = null, locale = null)
 * __fp(context, message, params, domain = null)
 * __p(context, message, domain = null)
 * _fn(singular, plural, count, params, domain = null, locale = null)
 * _fnp(context, singular, plural, count, params, domain = null)
 * _n(singular, plural, count, domain = null, locale = null)
 * no__(msgid)
 * lang(fs = false)
 * langdirection()

### Admin Interface

 * adminHeader()
 * adminFooter()
 * adminBreadcrumbs()
 * adminMenu(mode, template)
 * adminPanelMenu(mode)
     - this function is a short-cut to  `adminMenu(mode, 'panel')`
 * adminDeveloperNotices()
 * adminSecurityAnalyzer()
 * adminUpdateCheck()

 * moduleHeader(type, title, titlelink, set_page_title, insert_flashes, menufirst, image)
 * moduleFooter()
 * moduleBreadcrumbs()
 * moduleHelp(type)
 * moduleLinks(type, links, modname, menuid, menuclass, itemclass, first, last)

### System Specific

 * adminPanelMenu()
 * extensionActions(extension)
 * getPreviewImagePath(themeName, size = "medium")

#### Blocks

 * positionavailable(name)
 * showblock(block, positionName = "")
 * showblockposition(positionName, implode = true)

#### Atom Theme

 * atomFeedLastUpdated()
 * atomId()
