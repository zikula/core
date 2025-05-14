<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CoreBundle\Api\ApiInterface;

interface LocaleApiInterface
{
    /**
     * Whether the site is multilingual or not.
     */
    public function multilingual(): bool;

    /**
     * Get array of supported locales.
     */
    public function getSupportedLocales(bool $includeRegions = true): array;

    /**
     * Get array of supported locales with their translated name.
     */
    public function getSupportedLocaleNames(string $region = null, string $displayLocale = null, bool $includeRegions = true): array;
}
