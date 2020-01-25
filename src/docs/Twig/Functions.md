# Twig functions provided by Zikula Core

The following twig functions are available in templates. These are in addition to the standard functions provided
by the Twig package itself. See [Twig documentation](https://twig.symfony.com) for more information.
Also see [standard Symfony functions](https://symfony.com/doc/current/reference/twig_reference.html) for additional
functions, filters, tags, tests and global variables.

**Note:** Zikula Core provides a `zikula_default` package for use in the `asset()` twig function which allows the developer
to access the core root directory e.g. `asset('images/logo_with_title.png', 'zikula_default')`

## Functions

### Useful

 * array_unset(array, key)
 * callFunc(callable, params = [])
 * defaultPath(extensionName, type = 'user')
 * dispatchEvent($name, GenericEvent $providedEvent = null, $subject = null, array $arguments = [], $data = null)
 * getModVar(module, name, default = null)
 * getSystemVar(name, default = null)
 * hasPermission(component, instance, level)
 * pageAddAsset(type, value, weight = 100)
 * pageGetVar(name, default = null)
 * pageSetVar(name, value)
 * pager(params)
 * pagerabc(params)
 * showflashes(params = [])
 * stateLabel(extensionEntity, upgradedExtensions = null)
 * zasset(path)

### Hooks

 * notifyDisplayHooks(eventName, id = null, urlObject = null)
 * routeUrl(routeName, routeParameters = [], fragment = null)

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

### Users

#### Messages

 * messageInboxLink($uid = null, $urlOnly = false, $text = '', $class = '')
 * messageSendLink($uid = null, $urlOnly = false, $text = '', $class = '') (filter)
 * messageCount($uid = null, $unreadOnly = false)

#### Online state

 * onlineSince(UserEntity $userEntity = null, $minutes = 10) (filter)

#### Profiles

 * userAvatar($uid = 0, array $parameters = [])
 * profileLinkByUserId($userId, $class = '', $image = '', $maxLength = 0, $title = '') (filter)
 * profileLinkByUserName($userName, $class = '', $image = '', $maxLength = 0, $title = '') (filter)

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
