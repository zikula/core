<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2005 Steven Armstrong <sa at c-area dot ch>
 * @copyright (c) 2009, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL version 2 (or at your option, any later version).
 */

/**
 * ZL10n Translation class
 */
class ZGettext
{
    private static $instance;

    public $locale = 'en';
    public $category = LC_MESSAGES;
    public $LC_CATEGORIES;
    public $defaultDomain;
    public $textDomains = array();


    /**
     * private construct for singleton
     */
    private function __construct()
    {
        $this->LC_CATEGORIES = array('LC_CTYPE', 'LC_NUMERIC', 'LC_TIME', 'LC_COLLATE', 'LC_MONETARY', 'LC_MESSAGES', 'LC_ALL');
    }

    /**
     * getInstance of ZL10n singleton
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new ZGettext();
        }
        return self::$instance;
    }


    /**
     * setLocale
     *
     * @param $category
     * @param $locale
     *
     * @return $locale
     */
    public function setLocale($category=LC_MESSAGES, $locale)
    {
        $this->locale = $locale;
        $this->category = $this->translateCategory($category);
        return $locale;
    }


    /**
     * getLocale
     */
    public function getLocale()
    {
        return $this->locale;
    }


    /**
     * get LC_CATEGORY
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * Translate LC_CONSTANTS to internal form
     * @param $lc
     *
     * @return string LC_CONSTANT
     */
    public function translateCategory($lc)
    {
        return $this->LC_CATEGORIES[$lc];
    }


    /**
     * bind text domain
     *
     * @param $domain
     * @param $path
     */
    public function bindTextDomain($domain, $path)
    {
        $codeset = ini_get('mbstring.internal_encoding');
        if ($path[strlen($path) - 1] != '/') {
            $path .= '/';
        } elseif ($path[strlen($path) - 1] != '\\') {
            $path .= '\\';
        }
        $this->textDomains = array($this->getLocale() => array($this->getCategory() => array($domain => array('path' => $path, 'codeset' => $codeset, 'reader' => null))));
    }


    /**
     * bindTextDomainCodeset
     *
     * @param unknown_type $domain
     * @param unknown_type $codeset
     */
    public function bindTextDomainCodeset($domain, $codeset = null)
    {
        $codeset = ini_get('mbstring.internal_encoding');
        $this->textDomains[$this->getLocale()][$this->getCategory()][$domain]['codeset'] = $codeset;
    }


    /**
     * set default domain
     * @param $domain
     */
    public function textDomain($domain)
    {
        $this->defaultDomain = $domain;
    }


    /**
     * getReader for translation
     *
     * @param string $domain
     * @param constant $category (LC_CONSTANT)
     * @param bool $cache
     *
     * return object reader
     */
    public static function &getReader($domain = null, $category = null, $cache = true)
    {
        $_this = self::getInstance();
        $domain = (isset($domain) ? $domain : $_this->defaultDomain);
        $category = (isset($category) ? $_this->translateCategory($category) : $_this->getCategory());
        $locale = $_this->getLocale();
        $textDomain = & $_this->textDomains[$locale][$category][$domain];

        if(!$textDomain['reader']) {
            $path = $textDomain['path']."$locale/$category/$domain.mo";
            $reader = new StreamReader_CachedFile($path);
            $textDomain['reader'] = new ZMO($reader, $cache);
            $codeset = (isset($textDomain['codeset']) ? $textDomain['codeset'] : ini_get('mbstring.internal_encoding'));
            $textDomain['reader']->setEncoding($codeset);
        }

        return $textDomain['reader'];
    }
}
