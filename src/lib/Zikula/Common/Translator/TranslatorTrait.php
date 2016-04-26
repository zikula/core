<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Translator;

/**
 * Class TranslatorTrait
 * @package Zikula\Common\Translator
 */
trait TranslatorTrait
{
    /**
     * @var \Zikula\Common\Translator\Translator
     */
    private $translator;

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function __($msg, $domain = null, $locale = null)
    {
        return $this->translator->__($msg, $domain, $locale);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param string|null $domain
     * @param string| null $locale
     * @return string
     */
    public function _n($m1, $m2, $n, $domain = null, $locale = null)
    {
        return $this->translator->_n($m1, $m2, $n, $domain, $locale);
    }

    /**
     * Format translations for modules.
     *
     * @param string $msg Message.
     * @param string|array $param Format parameters.
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function __f($msg, $param, $domain = null, $locale = null)
    {
        return $this->translator->__f($msg, $param, $domain, $locale);
    }

    /**
     * Format plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param string|array $param Format parameters.
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function _fn($m1, $m2, $n, $param, $domain = null, $locale = null)
    {
        return $this->translator->_fn($m1, $m2, $n, $param, $domain, $locale);
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param $translator
     */
    abstract public function setTranslator($translator);
}
