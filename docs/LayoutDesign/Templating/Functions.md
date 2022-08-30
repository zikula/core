---
currentMenu: templating
---
# Twig functions provided by Zikula Core

The following twig functions are available in templates. These are in addition to the standard functions provided
by the Twig package itself. See [Twig documentation](https://twig.symfony.com) for more information.
Also see [standard Symfony functions](https://symfony.com/doc/current/reference/twig_reference.html) for additional
functions, filters, tags, tests and global variables.

## Functions

### Themes and Site data

- localeSwitcher()
- siteDefinition()
- siteName()
- siteSlogan()
- siteBranding()
- siteImagePath()

### Users

#### Profiles

- userAvatar($uid = 0, array $parameters = [])
- profileLinkByUserId($userId, $class = '', $image = '', $maxLength = 0, $title = '') (filter)
- profileLinkByUserName($userName, $class = '', $image = '', $maxLength = 0, $title = '') (filter)

### Permissions

- hasPermission(component, instance, level)
