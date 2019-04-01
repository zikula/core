<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Api\ApiInterface;

interface LocaleApiInterface
{
    /**
     * Get array of supported locales.
     */
    public function getSupportedLocales(): array;

    /**
     * Get array of supported locales with their translated name.
     */
    public function getSupportedLocaleNames(string $region = null, string $displayLocale = null): array;

    /**
     * Detect languages preferred by browser and make best match to available provided languages.
     *
     * Adapted from StackOverflow response by Noel Whitemore
     * @see http://stackoverflow.com/a/26169603/2600812
     */
    public function getBrowserLocale(string $default = 'en'): string;
}
