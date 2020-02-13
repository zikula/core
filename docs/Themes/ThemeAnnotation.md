---
currentMenu: themes
---
# Theme annotation

The action method of any controller can alter the theme through the use of annotation.

This annotation is used in a controller method like so: 

```php
use Zikula\ThemeModule\Engine\Annotation\Theme;

// ...

/**
 * @Route("/view")
 * @Theme("admin")
 * @return Response
 */
```

Possible values are:

- "admin"
- "print"
- "atom"
- "rss"
- any valid theme name (e.g. "ZikulaAndreas08Theme")
