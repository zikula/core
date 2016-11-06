<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Translator;

use Symfony\Component\Translation\IdentityTranslator as SymfonyIdentityTranslator;

/**
 * IdentityTranslator does not translate anything.
 *
 * Class IdentityTranslator
 */
class IdentityTranslator extends SymfonyIdentityTranslator implements TranslatorInterface
{
    /**
     * singular translation for modules.
     *
     * @param string $msg Message
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __($msg, $domain = null, $locale = null)
    {
        return $this->trans($msg, [], $domain, $locale);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular
     * @param string $m2 Plural
     * @param integer $n Count
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _n($m1, $m2, $n, $domain = null, $locale = null)
    {
        $message = $this->chooseMessage($m1, $m2, $n, $domain);

        return $this->transChoice($message, $n, [], $domain, $locale);
    }

    /**
     * Format translations for modules.
     *
     * @param string $msg Message
     * @param array $param Format parameters
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __f($msg, array $param, $domain = null, $locale = null)
    {
        return $this->trans($msg, $param, $domain, $locale);
    }

    /**
     * Format plural translations for modules.
     *
     * @param string $m1 Singular
     * @param string $m2 Plural
     * @param integer $n Count
     * @param array $param Format parameters
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _fn($m1, $m2, $n, array $param, $domain = null, $locale = null)
    {
        $message = $this->chooseMessage($m1, $m2, $n, $domain);

        return $this->transChoice($message, $n, $param, $domain, $locale);
    }

    /**
     * Choose message if no translation catalogue
     *
     * @param string $m1 Singular
     * @param string $m2 Plural
     * @param integer $n Count
     * @param string|null $domain
     * @return string
     */
    private function chooseMessage($m1, $m2, $n, $domain = null)
    {
        return $m2;
    }
}
