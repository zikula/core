---
currentMenu: developer-api
---
# PageAssetApi

classname: `\Zikula\ThemeModule\Api\PageAssetApi`.

The PageAssetApi allows page assets to be added to a page outside of the template.
Also available is a twig template function that calls this same function.

The class makes the following method available:

```php
/**
 * Zikula allows only the following asset types
 * <ul>
 *  <li>stylesheet</li>
 *  <li>javascript</li>
 *  <li>header</li>
 *  <li>footer</li>
 * </ul>
 */
public function add(string $type, string $value, int $weight = AssetBag::WEIGHT_DEFAULT): void;
```

The class is fully tested.
