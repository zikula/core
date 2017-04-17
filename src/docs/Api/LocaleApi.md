LocaleApi
=========

classname: \Zikula\SettingsModule\Api\LocaleApi

service id="zikula_settings_module.locale_api"

This class defines the locales that are supported based on the translations available in `app/Resources/translations`.

The class makes the following methods available:

    /**
     * Get array of supported locales
     *
     * @return array
     */
    public function getSupportedLocales();

    /**
     * Get array of supported locales with their translated name
     *
     * @param null $region
     * @param null $displayLocale
     * @return array
     */
    public function getSupportedLocaleNames($region = null, $displayLocale = null);

    /**
     * Detect languages preferred by browser and make best match to available provided languages.
     *
     * Adapted from StackOverflow response by Noel Whitemore
     * @see http://stackoverflow.com/a/26169603/2600812
     *
     * @param string $default
     * @return string
     */
    public function getBrowserLocale($default = 'en');

The class is fully tested.
