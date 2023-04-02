---
currentMenu: localisation
---
# LocaleApi

Interface: `\Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface`.  
Class: `\Zikula\Bundle\CoreBundle\Api\LocaleApi`.

This class defines the locales that are supported based on the translations available in `/translations`.

The class makes the following methods available:

```php
/**
 * Whether the site is multilingual or not.
 */
public function multilingual(): bool;

/**
 * Get array of supported locales.
 */
public function getSupportedLocales($includeRegions = true): array;

/**
 * Get array of supported locales with their translated name.
 */
public function getSupportedLocaleNames(string $region = null, string $displayLocale = null, $includeRegions = true): array;
```

The class is fully tested.
