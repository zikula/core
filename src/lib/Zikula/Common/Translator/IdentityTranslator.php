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

use Symfony\Component\Translation\IdentityTranslator as SymfonyIdentityTranslator;

/**
 * IdentityTranslator does not translate anything.
 */
class IdentityTranslator extends SymfonyIdentityTranslator implements TranslatorInterface
{
    public function __(string $msg, string $domain = null, string $locale = null): string
    {
        return $this->trans($msg, [], $domain, $locale);
    }

    public function _n(string $m1, string $m2, int $number, string $domain = null, string $locale = null): string
    {
        $message = $this->chooseMessage($m1, $m2, $number, $domain);

        return $this->trans($message, ['%count%' => $number], $domain, $locale);
    }

    public function __f(string $msg, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->trans($msg, $parameters, $domain, $locale);
    }

    public function _fn(string $m1, string $m2, int $number, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $message = $this->chooseMessage($m1, $m2, $number, $domain);

        return $this->trans($message, ['%count%' => $number] + $parameters, $domain, $locale);
    }

    private function chooseMessage(string $m1, string $m2, int $number, string $domain = null): string
    {
        return $m2;
    }

    public function getDomain(): string
    {
        return 'dummy';
    }
}
