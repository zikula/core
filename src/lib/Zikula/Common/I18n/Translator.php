<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\I18n;

/**
 * Zikula Translate helper.
 */
class Translator implements TranslatableInterface
{
    /**
     * Translation domain.
     *
     * @var string
     */
    private $domain;

    /**
     * Constructor.
     *
     * @param string $domain Gettext domain
     */
    public function __construct($domain = null)
    {
        $this->domain = strtolower($domain);
    }

    /**
     * Set the translation domain.
     *
     * @param string $domain Gettext domain
     *
     * @return void
     */
    public function setDomain($domain = null)
    {
        $this->domain = $domain;
    }

    /**
     * Get translation domain.
     *
     * @return string $this->domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message
     *
     * @return string
     */
    public function __($msg)
    {
        return __($msg, $this->domain);
    }

    /**
     * Plural translations for modules.
     *
     * @param string  $m1 Singular
     * @param string  $m2 Plural
     * @param integer $n  Count
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _n($m1, $m2, $n, $this->domain);
    }

    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message
     * @param string|array $param Format parameters
     *
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->domain);
    }

    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular
     * @param string       $m2    Plural
     * @param integer      $n     Count
     * @param string|array $param Format parameters
     *
     * @return string
     */
    public function _fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->domain);
    }
}
