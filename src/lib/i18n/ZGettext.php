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
 * ZL10n Translation class.
 */
class ZGettext
{
    /**
     * Singleton instance.
     *
     * @var ZGettext
     * @static
     */
    private static $instance;

    /**
     * Locale.
     *
     * @var string
     */
    public $locale = 'en';

    /**
     * Locale category.
     *
     * @var constant
     */
    public $category = LC_MESSAGES;

    /**
     * LC Categories registry.
     *
     * @var array
     */
    public $LC_CATEGORIES;

    /**
     * Default domain.
     *
     * @var string
     */
    public $defaultDomain = 'zikula';

    /**
     * Text domains.
     *
     * @var array
     */
    public $textDomains = [];

    /**
     * Private construct for singleton.
     */
    private function __construct()
    {
        $this->LC_CATEGORIES = ['LC_CTYPE', 'LC_NUMERIC', 'LC_TIME', 'LC_COLLATE', 'LC_MONETARY', 'LC_MESSAGES', 'LC_ALL'];
    }

    /**
     * GetInstance of ZL10n singleton.
     *
     * @return ZGettext Instance.
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set locale.
     *
     * @param integer $category LC_CONSTANT.
     * @param string  $locale   Locale.
     *
     * @return string $locale.
     */
    public function setLocale($category, $locale)
    {
        $category = isset($category) ? $category : LC_MESSAGES;
        $this->locale = $locale;
        // The following is a hack because LC_* constants appear to have different
        // values on different systems #2952
        $this->category = LC_MESSAGES; //$this->translateCategory($category);

        return $locale;
    }

    /**
     * getLocale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * get LC_CATEGORY.
     *
     * @return constant
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Translate LC_CONSTANTS to internal form.
     *
     * @param constant $lc LC_CONSTANT.
     *
     * @return string LC_CONSTANT
     */
    public function translateCategory($lc)
    {
        return $this->LC_CATEGORIES[$lc];
    }

    /**
     * Bind text domain.
     *
     * @param string $domain Text domain.
     * @param string $path   Domain path.
     *
     * @return void
     */
    public function bindTextDomain($domain, $path)
    {
        $codeset = (version_compare(\PHP_VERSION, '5.6.0', '<')) ? ini_get('mbstring.internal_encoding') : ini_get('default_charset');

        $this->textDomains[$this->getLocale()][$this->getCategory()][$domain] = [
            'path' => $path . '/',
            'codeset' => $codeset,
            'reader' => null
        ];
    }

    /**
     * Bind text domain codeset.
     *
     * @param string $domain  Domain.
     * @param string $codeset Codeset.
     *
     * @return void
     */
    public function bindTextDomainCodeset($domain, $codeset = null)
    {
        $codeset = (version_compare(\PHP_VERSION, '5.6.0', '<')) ? ini_get('mbstring.internal_encoding') : ini_get('default_charset');

        $this->textDomains[$this->getLocale()][$this->getCategory()][$domain]['codeset'] = $codeset;
    }

    /**
     * Set default domain.
     *
     * @param string $domain Default domain.
     *
     * @return void
     */
    public function textDomain($domain)
    {
        $this->defaultDomain = $domain;
    }

    /**
     * Get reader for translation
     *
     * @param string   $domain   Domain.
     * @param constant $category A LC_CONSTANT.
     * @param boolean  $cache    Whether or not to cache the reader.
     *
     * @return ZMO Reader object.
     */
    public static function getReader($domain = null, $category = null, $cache = true)
    {
        // check if classes are included (which is not true for CLI commands)
        if (!class_exists('StreamReader_Abstract')) {
            if (file_exists('lib/StreamReader/Abstract.php')) {
                include_once 'lib/StreamReader/Abstract.php';
                include_once 'lib/StreamReader/String.php';
                include_once 'lib/StreamReader/CachedFile.php';
            } elseif (file_exists('src/lib/StreamReader/Abstract.php')) {
                // true when calling composer
                include_once 'src/lib/StreamReader/Abstract.php';
                include_once 'src/lib/StreamReader/String.php';
                include_once 'src/lib/StreamReader/CachedFile.php';
            }
        }

        $_this = self::getInstance();
        $domain = (isset($domain) ? $domain : $_this->defaultDomain);
        $category = isset($category) ? $category : $_this->getCategory();
        $categorypath = $_this->translateCategory($category);
        $locale = $_this->getLocale();

        if (!isset($_this->textDomains[$locale][$category][$domain])) {
            $codeset = (version_compare(\PHP_VERSION, '5.6.0', '<')) ? ini_get('mbstring.internal_encoding') : ini_get('default_charset');
            $_this->textDomains[$locale][$category][$domain] = [
                'path' => 'locale/',
                'codeset' => $codeset,
                'reader' => null
            ];
        }
        $textDomain = &$_this->textDomains[$locale][$category][$domain];

        if (!$textDomain['reader']) {
            //$path = realpath($textDomain['path']."$locale/$categorypath/$domain.mo");
            // hard coding the LC type due to strange differences on various platforms
            $path = realpath($textDomain['path']."$locale/LC_MESSAGES/$domain.mo");
            $reader = new StreamReader_CachedFile($path);
            $textDomain['reader'] = new ZMO($reader, $cache);
            $codeset = (isset($textDomain['codeset'])) ? $textDomain['codeset'] : ((version_compare(\PHP_VERSION, '5.6.0', '<')) ? ini_get('mbstring.internal_encoding') : ini_get('default_charset'));
            $textDomain['reader']->setEncoding($codeset);
        }

        return $textDomain['reader'];
    }
}
