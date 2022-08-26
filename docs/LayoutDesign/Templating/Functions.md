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

- showflashes(params = [])

### Themes and Assets

- localeSwitcher()
- pageAddAsset(type, value, weight = 100)
- pageGetVar(name, default = null)
- pageSetVar(name, value)
- zasset(path)
- siteDefinition()
- siteName()
- siteSlogan()
- siteBranding()
- siteImagePath()
- getPreviewImagePath(themeName, size = 'medium')

### Users

#### Messages

- messageInboxLink($uid = null, $urlOnly = false, $text = '', $class = '')
- messageSendLink($uid = null, $urlOnly = false, $text = '', $class = '') (filter)
- messageCount($uid = null, $unreadOnly = false)

#### Profiles

- userAvatar($uid = 0, array $parameters = [])
- profileLinkByUserId($userId, $class = '', $image = '', $maxLength = 0, $title = '') (filter)
- profileLinkByUserName($userName, $class = '', $image = '', $maxLength = 0, $title = '') (filter)

### Permissions

- hasPermission(component, instance, level)
