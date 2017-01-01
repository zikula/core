<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ZL10n class.
 * @deprecated remove at Core-2.0
 */
class ZL10n implements Zikula_TranslatableInterface
{
    /**
     * Singleton instance.
     *
     * @var ZL10n
     */
    private static $instances;

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
    private function __construct($domain = null)
    {
        $this->setDomain($domain);
    }

    /**
     * One instance per translation domain.
     *
     * @param string $domain Gettext domain
     *
     * @return ZL10n instance
     */
    public static function getInstance($domain = null)
    {
        if (!isset(self::$instances[$domain])) {
            self::$instances[$domain] = new self($domain);
        }

        return self::$instances[$domain];
    }

    /**
     * Set the translation domain.
     *
     * @param string $domain Gettext domain
     *
     * @return void
     */
    protected function setDomain($domain = null)
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
        return __($this->domain, $msg);
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
        return _n($this->domain, $m1, $m2, $n);
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
     *
     * @deprecated Use _fn instead!
     */
    public function __fn($m1, $m2, $n, $param)
    {
        return $this->_fn($m1, $m2, $n, $param);
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
