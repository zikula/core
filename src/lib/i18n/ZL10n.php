<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class ZL10n
{
    private static $instances;
    private $translationDomain;

    private function __construct($domain=null)
    {
        $this->setTranslationDomain($domain);
        // determine the type of domain
        $parts = explode('_', $domain);
        $bindMethod = "bind{$parts[0]}Domain";
        $name = (count($parts) == 2 ? $parts[1] : "{$parts[1]}_{$parts[2]}");
        ZLanguage::$bindMethod($name);
    }

    /**
     * One instance per translation domain
     *
     * @param $domain
     * @return instance of ZL10n
     */
    public static function getInstance($domain='null')
    {
        if(!isset(self::$instances[$domain])) {
            self::$instances[$domain] = new self($domain);
        }

        return self::$instances[$domain];
    }

    /**
     * Set the translation domain
     */
    protected function setTranslationDomain($domain='null')
    {
        $this->translationDomain = ($domain == 'null' ? null : $domain);
    }

    /**
     * Get translation domain
     *
     * @return string $this->domain
     */
    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    /**
     * singular translation for modules
     *
     * @param string $msg
     * @return string
     */
    public function __($msg)
    {
        return _dgettext($this->translationDomain, $msg);
    }

    /**
     * plural translations for modules
     *
     * @param singular string $m1
     * @param plural string $m2
     * @param count int $n
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _dngettext($this->translationDomain, $m1, $m2, $n);
    }

    /**
     * format translations for modules
     *
     * @param string $msg
     * @param string or array $param
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->translationDomain);
    }

    /**
     * Format pural translations for modules
     *
     * @param singular string $m1
     * @param plural string $m2
     * @param count int $n
     * @param string or array $param
     * @return string
     */
    public function __fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->translationDomain);
    }

}