---
currentMenu: themes
---
# Theme attribute

The action method of any controller can alter the theme through the use of attribute.

This annotation is used in a controller method like so: 

```php
use Zikula\ThemeBundle\Engine\Annotation\Theme;

// …

#[Route('/view')]
#[Theme('admin')]
```

Possible values are:

- 'admin'
- any valid theme bundle name (e.g. 'ZikulaDefaultThemeBundle')
