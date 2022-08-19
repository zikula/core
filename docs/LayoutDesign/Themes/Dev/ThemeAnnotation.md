---
currentMenu: themes
---
# Theme annotation

The action method of any controller can alter the theme through the use of annotation.

This annotation is used in a controller method like so: 

```php
use Zikula\ThemeBundle\Engine\Annotation\Theme;

// …

/**
 * @Route("/view")
 * @Theme("admin")
 * @return Response
 */
```

Possible values are:

- "admin"
- any valid theme bundle name (e.g. "ZikulaDefaultThemeBundle")
