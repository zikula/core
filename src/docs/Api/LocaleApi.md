# LocaleApi

classname: `\Zikula\SettingsModule\Api\LocaleApi`

This class defines the locales that are supported based on the translations available in `app/Resources/translations`.

The class makes the following methods available:

```php
/**
 * Get array of supported locales
 */
public function getSupportedLocales($includeRegions = true): array;

/**
 * Get array of supported locales with their translated name
 */
public function getSupportedLocaleNames(string $region = null, string $displayLocale = null, $includeRegions = true): array;

/**
 * Detect languages preferred by browser and make best match to available provided languages.
 */
public function getBrowserLocale(string $default = 'en'): string;
```

The class is fully tested.
