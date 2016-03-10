<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package I18n
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula_Request_Http as Request;

/**
 * ZLanguage class.
 */
class ZLanguage
{
    /**
     * Singleton instance.
     *
     * @var ZLanguage
     */
    private static $instance;

    /**
     * Language for this request.
     *
     * @var string
     */
    public $langRequested;

    /**
     * Language for this session.
     *
     * @var string
     */
    public $langSession;

    /**
     * The language_detect config variable.
     *
     * @var integer
     */
    public $langDetect;

    /**
     * The system's default language.
     *
     * @var string
     */
    public $langSystemDefault;

    /**
     * Altered session language.
     *
     * @var string
     */
    public $langFixSession;

    /**
     * Database Charset.
     *
     * @var string
     */
    public $dbCharset;

    /**
     * Encoding.
     *
     * @var string
     */
    public $encoding;

    /**
     * Language code.
     *
     * @var string
     */
    public $languageCode;

    /**
     * Browser language preferences.
     *
     * @var string
     */
    public $browserLanguagePref;

    /**
     * Domain cache.
     *
     * @var array
     */
    public $domainCache = array();

    /**
     * Multilingual capable.
     *
     * @var boolean
     */
    public $multiLingualCapable;

    /**
     * Langurlrule config variable.
     *
     * @var integer
     */
    public $langUrlRule;

    /**
     * Errors.
     *
     * @var array
     */
    public $errors = array();

    /**
     * Locale.
     *
     * @var string
     */
    public $locale = false;

    /**
     * ZI18n object.
     *
     * @var ZI18n
     */
    public $i18n;

    public $languageCodeLegacy;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->langSession = SessionUtil::getVar('language', null);
        $this->langSystemDefault = System::getVar('language_i18n', 'en');
        $this->languageCode = $this->langSystemDefault;
        $this->langFixSession = preg_replace('#[^a-z-].#', '', FormUtil::getPassedValue('setsessionlanguage', null, 'POST'));
        $this->multiLingualCapable = System::getVar('multilingual');
        $this->langUrlRule = System::getVar('languageurl', 0);
        $this->langDetect = System::getVar('language_detect', 0);
        $this->setDBCharset();
        $this->setEncoding();
    }

    /**
     * Setup.
     *
     * @return void
     */
    public function setup(Request $request)
    {
        $this->langRequested = preg_replace('#[^a-z-].#', '', FormUtil::getPassedValue('lang', null, 'GET')); // language for this request
        $this->detectLanguage();
        $this->validate();
        $this->fixLanguageToSession();
        ModUtil::setupMultilingual();
        $this->setLocale($this->languageCode);
        $request->setLocale($this->languageCode);
        $request->setDefaultLocale('en');
        $this->bindCoreDomain();
        $this->processErrors();
    }

    /**
     * Get singleton instance.
     *
     * @return ZLanguage
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Fix language to session.
     *
     * @return void
     */
    private function fixLanguageToSession()
    {
        if ($this->langFixSession) {
            SessionUtil::setVar('language', $this->languageCode);
        }
    }

    /**
     * Detect language.
     *
     * @return void
     */
    private function detectLanguage()
    {
        if ($this->langFixSession) {
            $this->langRequested = $this->langFixSession;
        }

        if (!$this->multiLingualCapable) {
            // multi lingual option is disabled only set system language
            if ($this->langRequested) {
                if ($this->langRequested != $this->langSystemDefault) {
                    // can't directly issue error yet since we haven't initialised gettext yet
                    $this->registerError(__f("Error! Multi-lingual functionality is not enabled. This page cannot be displayed in %s language.", $this->langRequested));
                }
            }
            $this->languageCode = $this->langSystemDefault;

            return;
        }

        if ($this->langRequested) {
            $this->languageCode = $this->langRequested;
        } else {
            if ($this->langSession) {
                $this->languageCode = $this->langSession;
            } elseif ($this->langDetect) {
                $this->languageCode = $this->discoverBrowserPrefs();
            } else {
                $this->languageCode = $this->langSystemDefault;
            }
        }
    }

    /**
     * Validate.
     *
     * @return void
     */
    private function validate()
    {
        $available = $this->getInstalledLanguages();
        if (!in_array($this->languageCode, $available)) {
            $this->registerError(__f("Error! The requested language %s is not available.", $this->languageCode));
            $this->languageCode = $this->langSystemDefault;
        }
    }

    /**
     * Register an error.
     *
     * @param string $msg Error message.
     *
     * @return void
     */
    private function registerError($msg)
    {
        $this->errors[] = $msg;
    }

    /**
     * Process errors.
     *
     * @return void
     */
    private function processErrors()
    {
        if (count($this->errors) == 0) {
            return;
        }

        // fatal errors require 404
        header('HTTP/1.1 404 Not Found');
        foreach ($this->errors as $error) {
            LogUtil::addErrorPopup($error);
        }
    }

    /**
     * Set locale.
     *
     * @param string   $locale Locale.
     * @param integer  $lc     LC_CONSTANT.
     *
     * @return void
     */
    public static function setLocale($locale, $lc = LC_MESSAGES)
    {
        $_this = self::getInstance();
        $_this->languageCode = $locale; // on purpose
        $_this->languageCodeLegacy = $_this->lookupLegacyCode($_this->languageCode);
        $_this->locale = self::transformInternal(ZGettext::getInstance()->setLocale($lc, self::transformFS($locale)));
        $_this->i18n = ZI18n::getInstance($locale);
        if (!array_key_exists($locale, $_this->domainCache)) {
            $_this->domainCache[$locale] = array();
        }
    }

    /**
     * Get locale.
     *
     * @return ZLocale
     */
    public static function getLocale()
    {
        return self::getInstance()->locale;
    }

    /**
     * Set text domain.
     *
     * @return void
     */
    private function setTextDomain()
    {
        ZGettext::getInstance()->textDomain('zikula');
    }

    /**
     * Get language code.
     *
     * @return string
     */
    public static function getLanguageCode()
    {
        return self::getInstance()->languageCode;
    }

    /**
     * Get legacy language code.
     *
     * @return string
     */
    public static function getLanguageCodeLegacy()
    {
        return self::getInstance()->languageCodeLegacy;
    }

    /**
     * Get database Charset.
     *
     * @return string
     */
    public static function getDBCharset()
    {
        return self::getInstance()->dbCharset;
    }

    /**
     * Get encoding.
     *
     * @return string
     */
    public static function getEncoding()
    {
        return self::getInstance()->encoding;
    }

    /**
     * Bind domain.
     *
     * @param string $domain Gettext domain.
     * @param string $path   Domain path.
     *
     * @return boolean
     */
    public static function bindDomain($domain, $path)
    {
        $_this = self::getInstance();
        $locale = $_this->getLocale();

        if (!$locale) {
            // fallback solution to be replaced by proper routing
            $defaultLocale = System::getVar('language_i18n', 'en');
            if (System::getVar('shorturls')) {
                // we need to extract the language code from current url, since it is not ensured
                // that System::queryStringDecode() has been executed already
                $customentrypoint = System::getVar('entrypoint');
                $expectEntrypoint = !System::getVar('shorturlsstripentrypoint');
                $root = empty($customentrypoint) ? 'index.php' : $customentrypoint;

                // get base path to work out our current url
                $parsedURL = parse_url(System::getCurrentUri());

                $tobestripped = array(System::getBaseUri(), "$root");
                $path = str_replace($tobestripped, '', $parsedURL['path']);
                $path = trim($path, '/');

                // split the path into a set of argument strings
                $args = explode('/', rtrim($path, '/'));

                // ensure that each argument is properly decoded
                foreach ($args as $k => $v) {
                    $args[$k] = urldecode($v);
                }

                if (isset($args[0]) && self::isLangParam($args[0]) && in_array($args[0], self::getInstalledLanguages())) {
                    $defaultLocale = $args[0];
                }
            }
            $_this->setLocale($defaultLocale);
            $locale = $_this->getLocale();
        }

        // exit if the language system hasnt yet fully initialised
        if (!$locale) {
            return false;
        }

        // prevent double loading
        if (array_key_exists($domain, $_this->domainCache[$locale])) {
            return true;
        }

        ZGettext::getInstance()->bindTextDomain($domain, $path);
        ZGettext::getInstance()->bindTextDomainCodeset($domain, $_this->encoding);
        $_this->domainCache[$locale][$domain] = true;

        return $_this->domainCache[$locale][$domain];
    }

    /**
     * Bind theme domain.
     *
     * @param string $themeName Theme name.
     *
     * @return boolean
     */
    public static function bindThemeDomain($themeName)
    {
        $_this  = self::getInstance();
        $domain = self::getThemeDomain($themeName);
        $theme = ThemeUtil::getTheme($themeName);
        if (null !== $theme) {
            $path = $_this->searchOverrides($domain, $theme->getPath().'/Resources/locale');
        } else {
            $path = $_this->searchOverrides($domain, "themes/$themeName/locale");
        }

        return self::bindDomain($domain, $path);
    }

    /**
     * Bind module domain.
     *
     * @param string $modName Module name.
     *
     * @return boolean
     */
    public static function bindModuleDomain($modName)
    {
        // system modules are in the zikula domain
        $module = ModUtil::getInfoFromName($modName);
        if (ModUtil::isCore($modName)) {
            return 'zikula';
        }

        $_this  = self::getInstance();
        if (!$_this->locale) {
            $request = ServiceUtil::get('request');
            $_this->setup($request);
        }

        $domain = self::getModuleDomain($modName);
        $module = ModUtil::getModule($modName);
        if (null !== $module) {
            $path = $_this->searchOverrides($domain, $module->getPath().'/Resources/locale');
        } else {
            $path = $_this->searchOverrides($domain, 'modules/'.$modName.'/locale');
        }

        return self::bindDomain($domain, $path);
    }

    /**
     * Bind module plugin domain.
     *
     * @param string $moduleName Module name.
     * @param string $pluginName Plugin name.
     *
     * @return boolean
     */
    public static function bindModulePluginDomain($moduleName, $pluginName)
    {
        // system modules are in the zikula domain
        $module = ModUtil::getInfoFromName($moduleName);
        if ($module['type'] == ModUtil::TYPE_SYSTEM) {
            return 'zikula';
        }

        $_this  = self::getInstance();
        $domain = self::getModulePluginDomain($moduleName, $pluginName);
        if (is_dir("modules/$moduleName/plugins/$pluginName/Resource/locale")) {
            $path = $_this->searchOverrides($domain, "modules/$moduleName/plugins/$pluginName/Resource/locale");
        } else {
            $path = $_this->searchOverrides($domain, "modules/$moduleName/plugins/$pluginName/locale");
        }

        return self::bindDomain($domain, $path);
    }

    /**
     * Bind system plugin domain.
     *
     * @param string $pluginName Plugin name.
     *
     * @return boolean
     */
    public static function bindSystemPluginDomain($pluginName)
    {
        $_this  = self::getInstance();
        $domain = self::getSystemPluginDomain($pluginName);
        if (is_dir("plugins/$pluginName/Resources/locale")) {
            $path = $_this->searchOverrides($domain, "plugins/$pluginName/Resources/locale");
        } else {
            $path = $_this->searchOverrides($domain, "plugins/$pluginName/locale");
        }

        return self::bindDomain($domain, $path);
    }

    /**
     * Bind core domain.
     *
     * @return void
     */
    public static function bindCoreDomain()
    {
        $_this = self::getInstance();
        $_this->bindDomain('zikula', $_this->searchOverrides('zikula', 'app/Resources/locale')); // bind system domain
        $_this->setTextDomain('zikula');
    }

    /**
     * Search overrides.
     *
     * @param string $domain Gettext domain name.
     * @param string $path   Domain path.
     *
     * @return string
     */
    private function searchOverrides($domain, $path)
    {
        $lang = self::transformFS($this->languageCode);
        $override = realpath('config/locale/'.$lang.'/LC_MESSAGES/'.$domain.'.mo');

        return $override ? realpath('config/locale') : realpath($path);
    }

    /**
     * Get module domain.
     *
     * @param string $name Module name.
     *
     * @return string
     */
    public static function getModuleDomain($name)
    {
        $module = ModUtil::getModule($name);

        return (null === $module) ? strtolower('module_'.$name) : $module->getTranslationDomain();
    }

    /**
     * Get module plugin domain.
     *
     * @param string $modName    Module name.
     * @param string $pluginName Plugin name.
     *
     * @return string
     */
    public static function getModulePluginDomain($modName, $pluginName)
    {
        return strtolower("moduleplugin_{$modName}_{$pluginName}");
    }

    /**
     * Get system plugin domain.
     *
     * @param string $pluginName Plugin name.
     *
     * @return string
     */
    public static function getSystemPluginDomain($pluginName)
    {
        return strtolower("systemplugin_$pluginName");
    }

    /**
     * Get theme domain.
     *
     * @param string $name Theme name.
     *
     * @return string
     */
    public static function getThemeDomain($name)
    {
        $theme = ThemeUtil::getTheme($name);

        return (null === $theme) ? strtolower("theme_$name") : $theme->getTranslationDomain();
    }

    /**
     * Get language Url rule.
     *
     * @return integer
     */
    public static function getLangUrlRule()
    {
        return self::getInstance()->langUrlRule;
    }

    /**
     * Whether or not the lang parameter is required.
     *
     * @return boolean
     */
    public static function isRequiredLangParam()
    {
        $_this = self::getInstance();
        if ($_this->langUrlRule) {
            // always append
            return true;
        } else {
            // append only when current language and system language are different
            return ($_this->langSystemDefault != $_this->languageCode) ? true : false;
        }
    }

    /**
     * Discovers the browser's preferenced language.
     *
     * @return string
     */
    private function discoverBrowserPrefs()
    {
        $available = $this->getInstalledLanguages();
        $detector = new ZLanguageBrowser($available);
        $this->browserLanguagePref = $detector->discover($this->langSystemDefault);

        return $this->browserLanguagePref;
    }

    /**
     * Get array of installed languages by code.
     *
     * @return array
     */
    public static function getInstalledLanguages()
    {
        static $localeArray;

        if (isset($localeArray)) {
            return $localeArray;
        }

        // search for locale and config overrides
        $localeArray = array();
        $search = array('config/locale', 'app/Resources/locale');
        foreach ($search as $k) {
            // get only the directories of the search paths
            $locales = FileUtil::getFiles($k, false, true, null, 'd');
            foreach ($locales as $locale) {
                $localeArray[] = self::transformInternal($locale);
            }
        }
        $localeArray = array_unique($localeArray);

        return $localeArray;
    }

    /**
     * Get array of language names by code.
     *
     * @return array
     */
    public static function getInstalledLanguageNames()
    {
        $locales = self::getInstalledLanguages();
        $languagesArray = array();
        foreach ($locales as $locale) {
            $name = self::getLanguageName($locale);
            if ($name) {
                $languagesArray[$locale] = $name;
            }
        }

        return $languagesArray;
    }

    /**
     * Set encoding.
     *
     * @return void
     */
    private function setEncoding()
    {
        if (preg_match('#utf([-]{0,1})8#', $this->dbCharset)) {
            $this->encoding = 'utf-8';

            return;
        } elseif (preg_match('#^latin([0-9]{1,2})#', $this->dbCharset)) {
            $this->encoding = preg_replace('#latin([0-9]{1,2})#', 'iso-8859-$1', $this->dbCharset);

            return;
        } elseif (System::isInstalling()) {
            $this->encoding = 'utf-8';
        } else {
            $this->registerError(__f("Error! Could not set encoding based on database character set '%s'.", $this->dbCharset));
        }
    }

    /**
     * Set database charset.
     *
     * @return void
     */
    private function setDBCharset($charset = 'utf8')
    {
        $this->dbCharset = $charset;
    }

    /**
     * Whether or not the given string is a language parameter.
     *
     * @param string $lang Language to test.
     *
     * @return boolean
     */
    public static function isLangParam($lang)
    {
        if (self::getInstance()->langUrlRule) {
            return true;
        } else {
            // check if it LOOKS like a language param
            if (preg_match('#(^[a-z]{2,3}$)|(^[a-z]{2,3}-[a-z]{2,3}$)|(^[a-z]{2,3}-[a-z]{2,3}-[a-z]{2,3}$)#', $lang)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get language direction.
     *
     * @return string
     */
    public static function getDirection()
    {
        return self::getInstance()->i18n->locale['language_direction'];
    }

    /**
     * Lookup the legacy language code.
     *
     * @param string $lookup Language code.
     *
     * @return string Legacy language code.
     */
    public static function lookupLegacyCode($lookup)
    {
        $map = self::_cnvlanguagelist();

        return isset($map[$lookup]) ? $map[$lookup] : 'eng';
    }

    /**
     * Translate old lang requests into new code.
     *
     * TODO D [deprecate along with self::handleLegacy() when language defines deprecate] (drak).
     *
     * @param string $code Legacy language $code.
     *
     * @return string language code
     */
    public static function translateLegacyCode($code)
    {
        $map = self::legacyCodeMap();

        return isset($map[$code]) ? $map[$code] : false;
    }

    /**
     * Transform for internal usage.
     *
     * @param string $m String to transform.
     *
     * @return string
     */
    public static function transformInternal($m)
    {
        return preg_replace('/_/', '-', strtolower($m));
    }

    /**
     * Transform for file system.
     *
     * @param string $m String to transform.
     *
     * @return string
     */
    public static function transformFS($m)
    {
        $lang = substr($m, 0, (strpos($m, '-') ? strpos($m, '-') : strlen($m)));
        $country = ($lang != $m ? strtoupper(str_replace("$lang-", '', $m)) : false);

        return $country ? "{$lang}_{$country}" : $lang;
    }

    /**
     * Get legacy language name.
     *
     * @param string $language Language code.
     *
     * @return string
     */
    public static function getLegacyLanguageName($language)
    {
        $map = self::legacyMap();
        if (isset($map[$language])) {
            return $map[$language];
        }

        // strange behaviour but required for legacy
        return false;
    }

    /**
     * Get country name.
     *
     * @param string $country Country code.
     *
     * @return string
     */
    public static function getCountryName($country)
    {
        $country = strtolower($country);
        $sort = false;
        $map = self::countryMap($sort);
        if (isset($map[$country])) {
            return $map[$country];
        }

        // strange behaviour but required for legacy
        return false;
    }

    /**
     * Get language name.
     *
     * @param string $language Language code.
     *
     * @return string
     */
    public static function getLanguageName($language)
    {
        $language = self::transformInternal($language);
        $map = self::languageMap();
        if (isset($map[$language])) {
            return $map[$language];
        }

        // strange behaviour but required for legacy
        return false;
    }

    /**
     * Legacy language map.
     *
     * @return array
     */
    public static function legacyMap()
    {
        return array(
                'aar' => __('Afar'),
                'abk' => __('Abkhazian'),
                'ave' => __('Avestan'),
                'afr' => __('Afrikaans'),
                'aka' => __('Akan'),
                'amh' => __('Amharic'),
                'arg' => __('Aragonese'),
                'ara' => __('Arabic'),
                'asm' => __('Assamese'),
                'ava' => __('Avaric'),
                'aym' => __('Aymara'),
                'aze' => __('Azerbaijani'),
                'bak' => __('Bashkir'),
                'bel' => __('Belarusian'),
                'bul' => __('Bulgarian'),
                'bih' => __('Bihari'),
                'bis' => __('Bislama'),
                'bam' => __('Bambara'),
                'ben' => __('Bengali'),
                'bod' => __('Tibetan'),
                'bre' => __('Breton'),
                'bos' => __('Bosnian'),
                'cat' => __('Catalan'),
                'che' => __('Chechen'),
                'cha' => __('Chamorro'),
                'cos' => __('Corsican'),
                'cre' => __('Cree'),
                'ces' => __('Czech'),
                'chu' => __('Church Slavic'),
                'chv' => __('Chuvash'),
                'cym' => __('Welsh'),
                'dan' => __('Danish'),
                'deu' => __('German'),
                'div' => __('Divehi'),
                'dzo' => __('Dzongkha'),
                'ewe' => __('Ewe'),
                'ell' => __('Greek'),
                'eng' => __('English'),
                'epo' => __('Esperanto'),
                'spa' => __('Spanish'),
                'est' => __('Estonian'),
                'eus' => __('Basque'),
                'fas' => __('Persian'),
                'ful' => __('Fulah'),
                'fin' => __('Finnish'),
                'fij' => __('Fijian'),
                'fao' => __('Faroese'),
                'fra' => __('French'),
                'fry' => __('Frisian'),
                'gle' => __('Irish'),
                'gla' => __('Scottish Gaelic'),
                'glg' => __('Galician'),
                'grn' => __('Guarani'),
                'guj' => __('Gujarati'),
                'glv' => __('Manx'),
                'hau' => __('Hausa'),
                'haw' => __('Hawaiian'),
                'heb' => __('Hebrew'),
                'hin' => __('Hindi'),
                'hmo' => __('Hiri Motu'),
                'hrv' => __('Croatian'),
                'hat' => __('Haitian'),
                'hun' => __('Hungarian'),
                'hye' => __('Armenian'),
                'her' => __('Herero'),
                'ina' => __('Interlingua'),
                'ind' => __('Indonesian'),
                'ile' => __('Interlingue'),
                'ibo' => __('Igbo'),
                'iii' => __('Sichuan Yi'),
                'ipk' => __('Inupiaq'),
                'ido' => __('Ido'),
                'isl' => __('Icelandic'),
                'ita' => __('Italian'),
                'iku' => __('Inuktitut'),
                'jav' => __('Javanese'),
                'jpn' => __('Japanese'),
                'kat' => __('Georgian'),
                'kon' => __('Kongo'),
                'kik' => __('Kikuyu'),
                'kua' => __('Kwanyama'),
                'kaz' => __('Kazakh'),
                'kal' => __('Kalaallisut'),
                'khm' => __('Khmer'),
                'kan' => __('Kannada'),
                'kor' => __('Korean'),
                'kau' => __('Kanuri'),
                'kas' => __('Kashmiri'),
                'kur' => __('Kurdish'),
                'kom' => __('Komi'),
                'cor' => __('Cornish'),
                'kir' => __('Kirghiz'),
                'lat' => __('Latin'),
                'ltz' => __('Luxembourgish'),
                'lug' => __('Ganda'),
                'lim' => __('Limburgish'),
                'lin' => __('Lingala'),
                'lao' => __('Lao'),
                'lit' => __('Lithuanian'),
                'lub' => __('Luba-Katanga'),
                'lav' => __('Latvian'),
                'mlg' => __('Malagasy'),
                'mah' => __('Marshallese'),
                'mri' => __('Maori'),
                'mkd' => __('Macedonian'),
                'mal' => __('Malayalam'),
                'mon' => __('Mongolian'),
                'mar' => __('Marathi'),
                'msa' => __('Malay'),
                'mlt' => __('Maltese'),
                'mya' => __('Burmese'),
                'nau' => __('Nauru'),
                'nob' => __('Norwegian Bokmal'),
                'nde' => __('North Ndebele'),
                'nep' => __('Nepali'),
                'ndo' => __('Ndonga'),
                'nld' => __('Dutch'),
                'nno' => __('Norwegian Nynorsk'),
                'nor' => __('Norwegian'),
                'nbl' => __('South Ndebele'),
                'nav' => __('Navajo'),
                'nya' => __('Chichewa'),
                'oci' => __('Occitan'),
                'oji' => __('Ojibwa'),
                'orm' => __('Oromo'),
                'ori' => __('Oriya'),
                'oss' => __('Ossetian'),
                'pan' => __('Panjabi'),
                'pli' => __('Pali'),
                'pol' => __('Polish'),
                'pus' => __('Pushto'),
                'por' => __('Portuguese'),
                'que' => __('Quechua'),
                'roh' => __('Raeto-Romance'),
                'run' => __('Rundi'),
                'ron' => __('Romanian'),
                'rus' => __('Russian'),
                'kin' => __('Kinyarwanda'),
                'san' => __('Sanskrit'),
                'srd' => __('Sardinian'),
                'snd' => __('Sindhi'),
                'sme' => __('Northern Sami'),
                'sag' => __('Sango'),
                'sin' => __('Sinhalese'),
                'slk' => __('Slovak'),
                'slv' => __('Slovenian'),
                'smo' => __('Samoan'),
                'sna' => __('Shona'),
                'som' => __('Somali'),
                'sqi' => __('Albanian'),
                'srp' => __('Serbian'),
                'ssw' => __('Swati'),
                'sot' => __('Southern Sotho'),
                'sun' => __('Sundanese'),
                'swe' => __('Swedish'),
                'swa' => __('Swahili'),
                'tam' => __('Tamil'),
                'tel' => __('Telugu'),
                'tgk' => __('Tajik'),
                'tha' => __('Thai'),
                'tir' => __('Tigrinya'),
                'tuk' => __('Turkmen'),
                'tgl' => __('Tagalog'),
                'tsn' => __('Tswana'),
                'ton' => __('Tonga'),
                'tur' => __('Turkish'),
                'tso' => __('Tsonga'),
                'tat' => __('Tatar'),
                'twi' => __('Twi'),
                'tah' => __('Tahitian'),
                'uig' => __('Uighur'),
                'ukr' => __('Ukrainian'),
                'urd' => __('Urdu'),
                'uzb' => __('Uzbek'),
                'ven' => __('Venda'),
                'vie' => __('Vietnamese'),
                'vol' => __('Volapuk'),
                'wln' => __('Walloon'),
                'wol' => __('Wolof'),
                'xho' => __('Xhosa'),
                'yid' => __('Yiddish'),
                'yor' => __('Yoruba'),
                'zha' => __('Zhuang'),
                'zho' => __('Chinese'),
                'zul' => __('Zulu'));
    }

    /**
     * Map of l2 country names.
     *
     * @return array
     */
    public static function countryMap($sort = true)
    {
        $countryMap = array(
                'ad' => __('Andorra'),
                'ae' => __('United Arab Emirates'),
                'af' => __('Afghanistan'),
                'ag' => __('Antigua & Barbuda'),
                'ai' => __('Anguilla'),
                'al' => __('Albania'),
                'am' => __('Armenia'),
                'an' => __('Netherlands Antilles'),
                'ao' => __('Angola'),
                'aq' => __('Antarctica'),
                'ar' => __('Argentina'),
                'as' => __('American Samoa'),
                'at' => __('Austria'),
                'au' => __('Australia'),
                'aw' => __('Aruba'),
                'ax' => __('Aland Islands'),
                'az' => __('Azerbaijan'),
                'ba' => __('Bosnia and Herzegovina'),
                'bb' => __('Barbados'),
                'bd' => __('Bangladesh'),
                'be' => __('Belgium'),
                'bf' => __('Burkina Faso'),
                'bg' => __('Bulgaria'),
                'bh' => __('Bahrain'),
                'bi' => __('Burundi'),
                'bj' => __('Benin'),
                'bm' => __('Bermuda'),
                'bn' => __('Brunei Darussalam'),
                'bo' => __('Bolivia'),
                'br' => __('Brazil'),
                'bs' => __('Bahama'),
                'bt' => __('Bhutan'),
                'bv' => __('Bouvet Island'),
                'bw' => __('Botswana'),
                'by' => __('Belarus'),
                'bz' => __('Belize'),
                'ca' => __('Canada'),
                'cc' => __('Cocos (Keeling) Islands'),
                'cf' => __('Central African Republic'),
                'cg' => __('Congo'),
                'ch' => __('Switzerland'),
                'ci' => __("Cote d'Ivoire (Ivory Coast)"),
                'ck' => __('Cook Islands'),
                'cl' => __('Chile'),
                'cm' => __('Cameroon'),
                'cn' => __('China'),
                'co' => __('Colombia'),
                'cr' => __('Costa Rica'),
                'cu' => __('Cuba'),
                'cv' => __('Cape Verde'),
                'cx' => __('Christmas Island'),
                'cy' => __('Cyprus'),
                'cz' => __('Czech Republic'),
                'de' => __('Germany'),
                'dj' => __('Djibouti'),
                'dk' => __('Denmark'),
                'dm' => __('Dominica'),
                'do' => __('Dominican Republic'),
                'dz' => __('Algeria'),
                'ec' => __('Ecuador'),
                'ee' => __('Estonia'),
                'eg' => __('Egypt'),
                'eh' => __('Western Sahara'),
                'er' => __('Eritrea'),
                'es' => __('Spain'),
                'et' => __('Ethiopia'),
                'fi' => __('Finland'),
                'fj' => __('Fiji'),
                'fk' => __('Falkland Islands (Malvinas)'),
                'fm' => __('Micronesia'),
                'fo' => __('Faroe Islands'),
                'fr' => __('France'),
                'fx' => __('France, Metropolitan'),
                'ga' => __('Gabon'),
                'gb' => __('United Kingdom (Great Britain)'),
                'gd' => __('Grenada'),
                'ge' => __('Georgia'),
                'gf' => __('French Guiana'),
                'gh' => __('Ghana'),
                'gi' => __('Gibraltar'),
                'gl' => __('Greenland'),
                'gm' => __('Gambia'),
                'gn' => __('Guinea'),
                'gp' => __('Guadeloupe'),
                'gq' => __('Equatorial Guinea'),
                'gr' => __('Greece'),
                'gs' => __('South Georgia and the South Sandwich Islands'),
                'gt' => __('Guatemala'),
                'gu' => __('Guam'),
                'gw' => __('Guinea-Bissau'),
                'gy' => __('Guyana'),
                'hk' => __('Hong Kong'),
                'hm' => __('Heard & McDonald Islands'),
                'hn' => __('Honduras'),
                'hr' => __('Croatia'),
                'ht' => __('Haiti'),
                'hu' => __('Hungary'),
                'id' => __('Indonesia'),
                'ie' => __('Ireland'),
                'il' => __('Israel'),
                'im' => __('Isle of Man'),
                'in' => __('India'),
                'io' => __('British Indian Ocean Territory'),
                'iq' => __('Iraq'),
                'ir' => __('Islamic Republic of Iran'),
                'is' => __('Iceland'),
                'it' => __('Italy'),
                'jm' => __('Jamaica'),
                'jo' => __('Jordan'),
                'jp' => __('Japan'),
                'ke' => __('Kenya'),
                'kg' => __('Kyrgyzstan'),
                'kh' => __('Cambodia'),
                'ki' => __('Kiribati'),
                'km' => __('Comoros'),
                'kn' => __('St. Kitts and Nevis'),
                'ko' => __('Kosovo'), // unoffially allocated yet by ISO - drak
                'kp' => __("Korea, Democratic People's Republic of"),
                'kr' => __('Korea, Republic of'),
                'kw' => __('Kuwait'),
                'ky' => __('Cayman Islands'),
                'kz' => __('Kazakhstan'),
                'la' => __("Lao People's Democratic Republic"),
                'lb' => __('Lebanon'),
                'lc' => __('Saint Lucia'),
                'li' => __('Liechtenstein'),
                'lk' => __('Sri Lanka'),
                'lr' => __('Liberia'),
                'ls' => __('Lesotho'),
                'lt' => __('Lithuania'),
                'lu' => __('Luxembourg'),
                'lv' => __('Latvia'),
                'ly' => __('Libyan Arab Jamahiriya'),
                'ma' => __('Morocco'),
                'mc' => __('Monaco'),
                'md' => __('Moldova, Republic of'),
                'mg' => __('Madagascar'),
                'mh' => __('Marshall Islands'),
                'mk' => __('Macedonia, the form Republic of'),
                'ml' => __('Mali'),
                'mn' => __('Mongolia'),
                'mm' => __('Myanmar'),
                'mo' => __('Macau'),
                'mp' => __('Northern Mariana Islands'),
                'mq' => __('Martinique'),
                'mr' => __('Mauritania'),
                'ms' => __('Monserrat'),
                'mt' => __('Malta'),
                'mu' => __('Mauritius'),
                'mv' => __('Maldives'),
                'mw' => __('Malawi'),
                'mx' => __('Mexico'),
                'my' => __('Malaysia'),
                'mz' => __('Mozambique'),
                'na' => __('Namibia'),
                'nc' => __('New Caledonia'),
                'ne' => __('Niger'),
                'nf' => __('Norfolk Island'),
                'ng' => __('Nigeria'),
                'ni' => __('Nicaragua'),
                'nl' => __('Netherlands'),
                'no' => __('Norway'),
                'np' => __('Nepal'),
                'nr' => __('Nauru'),
                'nu' => __('Niue'),
                'nz' => __('New Zealand'),
                'om' => __('Oman'),
                'pa' => __('Panama'),
                'pe' => __('Peru'),
                'pf' => __('French Polynesia'),
                'pg' => __('Papua New Guinea'),
                'ph' => __('Philippines'),
                'pk' => __('Pakistan'),
                'pl' => __('Poland'),
                'pm' => __('St. Pierre & Miquelon'),
                'pn' => __('Pitcairn'),
                'pr' => __('Puerto Rico'),
                'pt' => __('Portugal'),
                'pw' => __('Palau'),
                'py' => __('Paraguay'),
                'qa' => __('Qatar'),
                're' => __('Reunion'),
                'ro' => __('Romania'),
                'rs' => __('Serbia'),
                'ru' => __('Russian Federation'),
                'rw' => __('Rwanda'),
                'sa' => __('Saudi Arabia'),
                'sb' => __('Solomon Islands'),
                'sc' => __('Seychelles'),
                'sd' => __('Sudan'),
                'se' => __('Sweden'),
                'sg' => __('Singapore'),
                'sh' => __('St. Helena'),
                'si' => __('Slovenia'),
                'sj' => __('Svalbard & Jan Mayen Islands'),
                'sk' => __('Slovakia'),
                'sl' => __('Sierra Leone'),
                'sm' => __('San Marino'),
                'sn' => __('Senegal'),
                'so' => __('Somalia'),
                'sr' => __('Suriname'),
                'st' => __('Sao Tome & Principe'),
                'sy' => __('Syrian Arab Republic'),
                'sz' => __('Swaziland'),
                'tc' => __('Turks & Caicos Islands'),
                'td' => __('Chad'),
                'tf' => __('French Southern Territories'),
                'tg' => __('Togo'),
                'th' => __('Thailand'),
                'tj' => __('Tajikistan'),
                'tk' => __('Tokelau'),
                'tm' => __('Turkmenistan'),
                'tn' => __('Tunisia'),
                'to' => __('Tonga'),
                'tp' => __('East Timor'),
                'tr' => __('Turkey'),
                'tt' => __('Trinidad & Tobago'),
                'tv' => __('Tuvalu'),
                'tw' => __('Taiwan, Province of China'),
                'tz' => __('Tanzania, United Republic of'),
                'ua' => __('Ukraine'),
                'ug' => __('Uganda'),
                'um' => __('United States Minor Outlying Islands'),
                'us' => __('United States of America'),
                'uy' => __('Uruguay'),
                'uz' => __('Uzbekistan'),
                'va' => __('Vatican City State (Holy See)'),
                'vc' => __('St. Vincent & the Grenadines'),
                've' => __('Venezuela'),
                'vg' => __('British Virgin Islands'),
                'vi' => __('United States Virgin Islands'),
                'vn' => __('Vietnam'),
                'vu' => __('Vanuatu'),
                'wf' => __('Wallis & Futuna Islands'),
                'ws' => __('Samoa'),
                'ye' => __('Yemen'),
                'yt' => __('Mayotte'),
                'za' => __('South Africa'),
                'zm' => __('Zambia'),
                'zr' => __('Zaire'),
                'zw' => __('Zimbabwe'),
                'zz' => __('Unknown or unspecified country')
        );
        if ($sort) {
            asort($countryMap);
        }

        return $countryMap;
    }

    /**
     * Map of language codes.
     *
     * @return array
     */
    public static function languageMap()
    {
        // TODO A [make list complete - this is just a start] (drak)
        return array(
                'af' => __('Afrikaans'),
                'ar' => __('Arabic'),
                'ar-ae' => __('Arabic (United Arab Emirates)'),
                'ar-bh' => __('Arabic (Bahrain)'),
                'ar-dz' => __('Arabic (Algeria)'),
                'ar-eg' => __('Arabic (Egypt)'),
                'ar-iq' => __('Arabic (Iraq)'),
                'ar-jo' => __('Arabic (Jordan)'),
                'ar-kw' => __('Arabic (Kuwait)'),
                'ar-lb' => __('Arabic (Lebanon)'),
                'ar-ly' => __('Arabic (Libya)'),
                'ar-ma' => __('Arabic (Morocco)'),
                'ar-om' => __('Arabic (Oman)'),
                'ar-qa' => __('Arabic (Qatar)'),
                'ar-sa' => __('Arabic (Saudi Arabia)'),
                'ar-sd' => __('Arabic (Sudan)'),
                'ar-sy' => __('Arabic (Syria)'),
                'ar-tn' => __('Arabic (Tunisia)'),
                'ar-ye' => __('Arabic (Yemen)'),
                'be' => __('Belarusian'),
                'be-by' => __('Belarusian (Belarus)'),
                'bg' => __('Bulgarian'),
                'bg-bg' => __('Bulgarian (Bulgaria)'),
                'bn-in' => __('Bengali (India)'),
                'ca' => __('Catalan'),
                'ca-es' => __('Catalan (Spain)'),
                'cs' => __('Czech'),
                'cs-cz' => __('Czech (Czech Republic)'),
                'da' => __('Danish'),
                'da-dk' => __('Danish (Denmark)'),
                'de' => __('German'),
                'de-at' => __('German (Austria)'),
                'de-ch' => __('German (Switzerland)'),
                'de-de' => __('German (Germany)'),
                'de-lu' => __('German (Luxembourg)'),
                'el' => __('Greek'),
                'el-cy' => __('Greek (Cyprus)'),
                'el-gr' => __('Greek (Greece)'),
                'en' => __('English'),
                'en-au' => __('English (Australia)'),
                'en-ca' => __('English (Canada)'),
                'en-gb' => __('English (United Kingdom)'),
                'en-ie' => __('English (Ireland)'),
                'en-in' => __('English (India)'),
                'en-mt' => __('English (Malta)'),
                'en-nz' => __('English (New Zealand)'),
                'en-ph' => __('English (Philippines)'),
                'en-sg' => __('English (Singapore)'),
                'en-us' => __('English (United States)'),
                'en-za' => __('English (South Africa)'),
                'es' => __('Spanish'),
                'es-ar' => __('Spanish (Argentina)'),
                'es-bo' => __('Spanish (Bolivia)'),
                'es-cl' => __('Spanish (Chile)'),
                'es-co' => __('Spanish (Colombia)'),
                'es-cr' => __('Spanish (Costa Rica)'),
                'es-do' => __('Spanish (Dominican Republic)'),
                'es-ec' => __('Spanish (Ecuador)'),
                'es-es' => __('Spanish (Spain)'),
                'es-gt' => __('Spanish (Guatemala)'),
                'es-hn' => __('Spanish (Honduras)'),
                'es-mx' => __('Spanish (Mexico)'),
                'es-ni' => __('Spanish (Nicaragua)'),
                'es-pa' => __('Spanish (Panama)'),
                'es-pe' => __('Spanish (Peru)'),
                'es-pr' => __('Spanish (Puerto Rico)'),
                'es-py' => __('Spanish (Paraguay)'),
                'es-sv' => __('Spanish (El Salvador)'),
                'es-us' => __('Spanish (United States)'),
                'es-uy' => __('Spanish (Uruguay)'),
                'es-ve' => __('Spanish (Venezuela)'),
                'et' => __('Estonian'),
                'et-ee' => __('Estonian (Estonia)'),
                'eu' => __('Basque'),
                'fa' => __('Persian'),
                'fi' => __('Finnish'),
                'fi-fi' => __('Finnish (Finland)'),
                'fr' => __('French'),
                'fr-be' => __('French (Belgium)'),
                'fr-ca' => __('French (Canada)'),
                'fr-ch' => __('French (Switzerland)'),
                'fr-fr' => __('French (France)'),
                'fr-lu' => __('French (Luxembourg)'),
                'fur' => __('Friulian'),
                'ga' => __('Irish'),
                'ga-ie' => __('Irish (Ireland)'),
                'gl' => __('Galician'),
                'hi' => __('Hindi'),
                'hi-in' => __('Hindi (India)'),
                'hr' => __('Croatian'),
                'hr-hr' => __('Croatian (Croatia)'),
                'hu' => __('Hungarian'),
                'hu-hu' => __('Hungarian (Hungary)'),
                'id' => __('Indonesian'),
                'in' => __('Indonesian'),
                'in-id' => __('Indonesian (Indonesia)'),
                'is' => __('Icelandic'),
                'is-is' => __('Icelandic (Iceland)'),
                'it' => __('Italian'),
                'it-ch' => __('Italian (Switzerland)'),
                'it-it' => __('Italian (Italy)'),
                'iw' => __('Hebrew'),
                'iw-il' => __('Hebrew (Israel)'),
                'ja' => __('Japanese'),
                'ja-jp' => __('Japanese (Japan)'),
                'ka' => __('Georgian'),
                'ko' => __('Korean'),
                'ko-kr' => __('Korean (South Korea)'),
                'lt' => __('Lithuanian'),
                'lt-lt' => __('Lithuanian (Lithuania)'),
                'lv' => __('Latvian'),
                'lv-lv' => __('Latvian (Latvia)'),
                'mk' => __('Macedonian'),
                'mk-mk' => __('Macedonian (Macedonia)'),
                'ml' => __('Malayalam'),
                'ms' => __('Malay'),
                'ms-my' => __('Malay (Malaysia)'),
                'mt' => __('Maltese'),
                'mt-mt' => __('Maltese (Malta)'),
                'nds' => __('German (Luxembourg)'),
                'ne' => __('Nepali'),
                'nl' => __('Dutch'),
                'nl-be' => __('Dutch (Belgium)'),
                'nl-nl' => __('Dutch (Netherlands)'),
                'no' => __('Norwegian'),
                'no-no' => __('Norwegian (Norway)'),
                'no-no-ny' => __('Norwegian (Norway, Nynorsk)'),
                'pl' => __('Polish'),
                'pl-pl' => __('Polish (Poland)'),
                'pt' => __('Portuguese'),
                'pt-br' => __('Portuguese (Brazil)'),
                'pt-pt' => __('Portuguese (Portugal)'),
                'ro' => __('Romanian'),
                'ro-ro' => __('Romanian (Romania)'),
                'ru' => __('Russian'),
                'ru-ru' => __('Russian (Russia)'),
                'sk' => __('Slovak'),
                'sk-sk' => __('Slovak (Slovakia)'),
                'sl' => __('Slovenian'),
                'sl-si' => __('Slovenian (Slovenia)'),
                'sq' => __('Albanian'),
                'sq-al' => __('Albanian (Albania)'),
                'sr' => __('Serbian'),
                'sr-ba' => __('Serbian (Bosnia and Herzegovina)'),
                'sr-cs' => __('Serbian (Serbia and Montenegro)'),
                'sr-me' => __('Serbian (Montenegro)'),
                'sr-rs' => __('Serbian (Serbia)'),
                'st' => __('Sotho, Southern'),
                'sv' => __('Swedish'),
                'sv-se' => __('Swedish (Sweden)'),
                'th' => __('Thai'),
                'th-th' => __('Thai (Thailand)'),
                'th-th-th' => __('Thai (Thailand, TH)'),
                'tr' => __('Turkish'),
                'tr-tr' => __('Turkish (Turkey)'),
                'uk' => __('Ukrainian'),
                'uk-ua' => __('Ukrainian (Ukraine)'),
                'vi' => __('Vietnamese'),
                'vi-vn' => __('Vietnamese (Vietnam)'),
                'wo' => __('Wolof'),
                'zh' => __('Chinese'),
                'zh-cn' => __('Chinese (China)'),
                'zh-hk' => __('Chinese (Hong Kong)'),
                'zh-sg' => __('Chinese (Singapore)'),
                'zh-tw' => __('Chinese (Taiwan)'));
    }

    /**
     * Legacy to l2 mapping.
     *
     * @return array
     */
    public static function legacyCodeMap()
    {
        return array(
                'aar' => 'aa',
                'abk' => 'ab',
                'ave' => 'ae',
                'afr' => 'af',
                'aka' => 'ak',
                'amh' => 'am',
                'arg' => 'an',
                'ara' => 'ar',
                'asm' => 'as',
                'ava' => 'av',
                'aym' => 'ay',
                'aze' => 'az',
                'bak' => 'ba',
                'bel' => 'be',
                'bul' => 'bg',
                'bih' => 'bh',
                'bis' => 'bi',
                'bam' => 'bm',
                'ben' => 'bn',
                'bod' => 'bo',
                'bre' => 'br',
                'bos' => 'bs',
                'cat' => 'ca',
                'che' => 'ce',
                'cha' => 'ch',
                'cos' => 'co',
                'cre' => 'cr',
                'ces' => 'cs',
                'chu' => 'cu',
                'chv' => 'cv',
                'cym' => 'cy',
                'dan' => 'da',
                'deu' => 'de',
                'div' => 'dv',
                'dzo' => 'dz',
                'ewe' => 'ee',
                'ell' => 'el',
                'eng' => 'en',
                'enu' => 'eu',
                'spa' => 'es',
                'est' => 'et',
                'eus' => 'eu',
                'fas' => 'fa',
                'ful' => 'ff',
                'fin' => 'fi',
                'fij' => 'fj',
                'fao' => 'fo',
                'fra' => 'fr',
                'fry' => 'fy',
                'gle' => 'ga',
                'gla' => 'gd',
                'glg' => 'gl',
                'grn' => 'gn',
                'guj' => 'gu',
                'glv' => 'gv',
                'hau' => 'ha',
                'heb' => 'he',
                'hin' => 'hi',
                'hmo' => 'ho',
                'hrv' => 'hr',
                'hat' => 'ht',
                'hun' => 'hu',
                'hye' => 'hy',
                'her' => 'hz',
                'ina' => 'ia',
                'ind' => 'id',
                'ile' => 'ie',
                'ibo' => 'ig',
                'iii' => 'ii',
                'ipk' => 'ik',
                'ido' => 'io',
                'isl' => 'is',
                'ita' => 'it',
                'iku' => 'iu',
                'jpn' => 'ja',
                'jav' => 'jv',
                'kat' => 'ka',
                'kon' => 'kg',
                'kik' => 'ki',
                'kua' => 'kj',
                'kaz' => 'kk',
                'kal' => 'kl',
                'khm' => 'km',
                'kan' => 'kn',
                'kor' => 'ko',
                'kau' => 'kr',
                'kas' => 'ks',
                'kur' => 'ku',
                'kom' => 'kv',
                'cor' => 'kw',
                'kir' => 'ky',
                'lat' => 'la',
                'ltz' => 'lb',
                'lug' => 'lg',
                'lim' => 'li',
                'lin' => 'ln',
                'lao' => 'lo',
                'lit' => 'lt',
                'lub' => 'lu',
                'lav' => 'lv',
                'mlg' => 'mg',
                'mah' => 'mh',
                'mri' => 'mi',
                'mkd' => 'mk',
                'mal' => 'ml',
                'mon' => 'mn',
                'mar' => 'mr',
                'msa' => 'ms',
                'mlt' => 'mt',
                'mya' => 'my',
                'nau' => 'na',
                'nob' => 'nb',
                'nde' => 'nd',
                'nds' => 'nds',
                'nep' => 'ne',
                'ndo' => 'ng',
                'nld' => 'nl',
                'nno' => 'nn',
                'nor' => 'no',
                'nbl' => 'nr',
                'nav' => 'nv',
                'nya' => 'ny',
                'oci' => 'oc',
                'oji' => 'oj',
                'orm' => 'om',
                'ori' => 'or',
                'oss' => 'os',
                'pan' => 'pa',
                'pli' => 'pi',
                'pol' => 'pl',
                'pus' => 'ps',
                'por' => 'pt',
                'que' => 'qu',
                'roh' => 'rm',
                'run' => 'rn',
                'ron' => 'ro',
                'rus' => 'ru',
                'kin' => 'rw',
                'san' => 'sa',
                'srd' => 'sc',
                'snd' => 'sd',
                'sme' => 'se',
                'sag' => 'sg',
                'sin' => 'si',
                'slk' => 'sk',
                'slv' => 'sl',
                'smo' => 'sm',
                'sna' => 'sn',
                'som' => 'so',
                'sqi' => 'sq',
                'srp' => 'sr',
                'ssw' => 'ss',
                'sot' => 'st',
                'sun' => 'su',
                'swe' => 'sv',
                'swa' => 'sw',
                'tam' => 'ta',
                'tel' => 'te',
                'tgk' => 'tg',
                'tha' => 'th',
                'tir' => 'ti',
                'tuk' => 'tk',
                'tgl' => 'tl',
                'tsn' => 'tn',
                'ton' => 'to',
                'tur' => 'tr',
                'tso' => 'ts',
                'tat' => 'tt',
                'twi' => 'tw',
                'tah' => 'ty',
                'uig' => 'ug',
                'ukr' => 'uk',
                'urd' => 'ur',
                'uzb' => 'uz',
                'ven' => 've',
                'vie' => 'vi',
                'vol' => 'vo',
                'wln' => 'wa',
                'wol' => 'wo',
                'xho' => 'xh',
                'yid' => 'yi',
                'yor' => 'yo',
                'zha' => 'za',
                'zho' => 'zh',
                'zul' => 'zu');
    }

    /**
     * CNV language list.
     *
     * @return array
     */
    private static function _cnvlanguagelist()
    {
        $cnvlang = array();
        $cnvlang['KOI8-R'] = 'rus';
        $cnvlang['af'] = 'eng';
        $cnvlang['ar'] = 'ara';
        $cnvlang['ar-ae'] = 'ara';
        $cnvlang['ar-bh'] = 'ara';
        $cnvlang['ar-bh'] = 'ara';
        $cnvlang['ar-dj'] = 'ara';
        $cnvlang['ar-dz'] = 'ara';
        $cnvlang['ar-eg'] = 'ara';
        $cnvlang['ar-iq'] = 'ara';
        $cnvlang['ar-jo'] = 'ara';
        $cnvlang['ar-km'] = 'ara';
        $cnvlang['ar-kw'] = 'ara';
        $cnvlang['ar-lb'] = 'ara';
        $cnvlang['ar-ly'] = 'ara';
        $cnvlang['ar-ma'] = 'ara';
        $cnvlang['ar-mr'] = 'ara';
        $cnvlang['ar-om'] = 'ara';
        $cnvlang['ar-qa'] = 'ara';
        $cnvlang['ar-sa'] = 'ara';
        $cnvlang['ar-sd'] = 'ara';
        $cnvlang['ar-so'] = 'ara';
        $cnvlang['ar-sy'] = 'ara';
        $cnvlang['ar-tn'] = 'ara';
        $cnvlang['ar-ye'] = 'ara';
        $cnvlang['be'] = 'eng';
        $cnvlang['bg'] = 'bul';
        $cnvlang['bo'] = 'tib';
        $cnvlang['ca'] = 'eng';
        $cnvlang['cs'] = 'ces';
        $cnvlang['da'] = 'dan';
        $cnvlang['de'] = 'deu';
        $cnvlang['de-at'] = 'deu';
        $cnvlang['de-ch'] = 'deu';
        $cnvlang['de-de'] = 'deu';
        $cnvlang['de-li'] = 'deu';
        $cnvlang['de-lu'] = 'deu';
        $cnvlang['el'] = 'ell';
        $cnvlang['en'] = 'eng';
        $cnvlang['en-au'] = 'eng';
        $cnvlang['en-bz'] = 'eng';
        $cnvlang['en-ca'] = 'eng';
        $cnvlang['en-gb'] = 'eng';
        $cnvlang['en-ie'] = 'eng';
        $cnvlang['en-jm'] = 'eng';
        $cnvlang['en-nz'] = 'eng';
        $cnvlang['en-ph'] = 'eng';
        $cnvlang['en-tt'] = 'eng';
        $cnvlang['en-us'] = 'eng';
        $cnvlang['en-za'] = 'eng';
        $cnvlang['en-zw'] = 'eng';
        $cnvlang['es'] = 'spa';
        $cnvlang['es-ar'] = 'spa';
        $cnvlang['es-bo'] = 'spa';
        $cnvlang['es-cl'] = 'spa';
        $cnvlang['es-co'] = 'spa';
        $cnvlang['es-cr'] = 'spa';
        $cnvlang['es-do'] = 'spa';
        $cnvlang['es-ec'] = 'spa';
        $cnvlang['es-es'] = 'spa';
        $cnvlang['es-gt'] = 'spa';
        $cnvlang['es-hn'] = 'spa';
        $cnvlang['es-mx'] = 'spa';
        $cnvlang['es-ni'] = 'spa';
        $cnvlang['es-pa'] = 'spa';
        $cnvlang['es-pe'] = 'spa';
        $cnvlang['es-pr'] = 'spa';
        $cnvlang['es-py'] = 'spa';
        $cnvlang['es-sv'] = 'spa';
        $cnvlang['es-uy'] = 'spa';
        $cnvlang['es-ve'] = 'spa';
        $cnvlang['eu'] = 'eng';
        $cnvlang['fi'] = 'fin';
        $cnvlang['fo'] = 'eng';
        $cnvlang['fr'] = 'fra';
        $cnvlang['fr-be'] = 'fra';
        $cnvlang['fr-ca'] = 'fra';
        $cnvlang['fr-ch'] = 'fra';
        $cnvlang['fr-fr'] = 'fra';
        $cnvlang['fr-lu'] = 'fra';
        $cnvlang['fr-mc'] = 'fra';
        $cnvlang['ga'] = 'eng';
        $cnvlang['gd'] = 'eng';
        $cnvlang['gl'] = 'eng';
        $cnvlang['hr'] = 'cro';
        $cnvlang['hu'] = 'hun';
        $cnvlang['in'] = 'ind';
        $cnvlang['is'] = 'isl';
        $cnvlang['it'] = 'ita';
        $cnvlang['it-ch'] = 'ita';
        $cnvlang['it-it'] = 'ita';
        $cnvlang['ja'] = 'jpn';
        $cnvlang['ka'] = 'kat';
        $cnvlang['ko'] = 'kor';
        $cnvlang['mk'] = 'mkd';
        $cnvlang['nl'] = 'nld';
        $cnvlang['nl-be'] = 'nld';
        $cnvlang['nl-nl'] = 'nld';
        $cnvlang['no'] = 'nor';
        $cnvlang['pl'] = 'pol';
        $cnvlang['pt'] = 'por';
        $cnvlang['pt-br'] = 'por';
        $cnvlang['pt-pt'] = 'por';
        $cnvlang['ro'] = 'ron';
        $cnvlang['ro-mo'] = 'ron';
        $cnvlang['ro-ro'] = 'ron';
        $cnvlang['ru'] = 'rus';
        $cnvlang['ru-mo'] = 'rus';
        $cnvlang['ru-ru'] = 'rus';
        $cnvlang['sk'] = 'slv';
        $cnvlang['sl'] = 'slv';
        $cnvlang['sq'] = 'eng';
        $cnvlang['sr'] = 'eng';
        $cnvlang['sv'] = 'swe';
        $cnvlang['sv-fi'] = 'swe';
        $cnvlang['sv-se'] = 'swe';
        $cnvlang['th'] = 'tha';
        $cnvlang['tr'] = 'tur';
        $cnvlang['uk'] = 'ukr';
        $cnvlang['zh-cn'] = 'zho';
        $cnvlang['zh-tw'] = 'zho';

        return $cnvlang;
    }
}
