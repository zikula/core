---
currentMenu: templating
---
# Twig functions provided by Zikula Core

The following twig functions are available in templates. These are in addition to the standard functions provided
by the Twig package itself. See [Twig documentation](https://twig.symfony.com) for more information.
Also see [standard Symfony functions](https://symfony.com/doc/current/reference/twig_reference.html) for additional
functions, filters, tags, tests and global variables.

## Functions

### General

- hasPermission(component, instance, level)
- showflashes(params = [])
- siteName()
- siteSlogan()

### Themes and Assets

- localeSwitcher()
- pageAddAsset(type, value, weight = 100)
- pageGetVar(name, default = null)
- pageSetVar(name, value)
- zasset(path)

### Extensions and Variables

- getModVar(module, name, default = null)
- getSystemVar(name, default = null)
- defaultPath(extensionName, type = 'user')

### Admin Interface

- adminHeader()
- adminFooter()
- adminBreadcrumbs()
- adminMenu(mode, template)
- adminPanelMenu(mode)
  - this function is a short-cut to  `adminMenu(mode, 'panel')`
- adminDeveloperNotices()
- adminSecurityAnalyzer()
- adminUpdateCheck()

- moduleHeader(type, title, titlelink, set_page_title, insert_flashes, menufirst, image)
- moduleFooter()
- moduleBreadcrumbs()
- moduleLinks(type, links, modname, menuid, menuclass, itemclass, first, last)

### Users

#### Messages

- messageInboxLink($uid = null, $urlOnly = false, $text = '', $class = '')
- messageSendLink($uid = null, $urlOnly = false, $text = '', $class = '') (filter)
- messageCount($uid = null, $unreadOnly = false)

#### Online state

- onlineSince(UserEntity $userEntity = null, $minutes = 10) (filter)

#### Profiles

- userAvatar($uid = 0, array $parameters = [])
- profileLinkByUserId($userId, $class = '', $image = '', $maxLength = 0, $title = '') (filter)
- profileLinkByUserName($userName, $class = '', $image = '', $maxLength = 0, $title = '') (filter)

### System Specific

- adminPanelMenu()
- getPreviewImagePath(themeName, size = "medium")
