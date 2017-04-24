<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Api\ApiInterface;

interface LocaleApiInterface
{
    /**
     * Get array of supported locales
     *
     * @param bool $enableLegacy @deprecated
     * @return array
     */
    public function getSupportedLocales($enableLegacy = true);

    /**
     * Get array of supported locales with their translated name
     *
     * @param null $region
     * @param null $displayLocale
     * @param bool $enableLegacy @deprecated
     * @return array
     */
    public function getSupportedLocaleNames($region = null, $displayLocale = null, $enableLegacy = true);

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
}
