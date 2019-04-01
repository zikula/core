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

namespace Zikula\Common\Translator;

use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

/**
 * Typehint against this interface.
 */
interface TranslatorInterface extends SymfonyTranslatorInterface
{
    /**
     * Singular translation for modules.
     */
    public function __(string $msg, string $domain = null, string $locale = null): string;

    /**
     * Plural translations for modules.
     */
    public function _n(string $m1, string $m2, int $number, string $domain = null, string $locale = null): string;

    /**
     * Format translations for modules.
     */
    public function __f(string $msg, array $parameters = [], string $domain = null, string $locale = null): string;

    /**
     * Format plural translations for modules.
     */
    public function _fn(string $m1, string $m2, int $number, array $parameters = [], string $domain = null, string $locale = null): string;

    public function getDomain(): string;
}
