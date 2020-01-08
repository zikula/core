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

/**
 * Class TranslatorTrait
 */
trait TranslatorTrait
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Singular translation for modules.
     */
    public function __(string $msg, string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($msg, $domain, $locale);
    }

    /**
     * Plural translations for modules.
     */
    public function _n(string $m1, string $m2, int $number, string $domain = null, string $locale = null): string
    {
        return $this->translator->_n($m1, $m2, $number, $domain, $locale);
    }

    /**
     * Format translations for modules.
     */
    public function __f(string $msg, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($msg, $parameters, $domain, $locale);
    }

    /**
     * Format plural translations for modules.
     */
    public function _fn(string $m1, string $m2, int $number, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->_fn($m1, $m2, $number, $parameters, $domain, $locale);
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    abstract public function setTranslator(TranslatorInterface $translator): void;
}
