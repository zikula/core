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

- `'admin'` for the configured admin dashboard controller
- any full qualified dashboard controller class (e.g. `'Zikula\ThemeBundle\Controller\Dashboard\AdminDashboardController'`)
