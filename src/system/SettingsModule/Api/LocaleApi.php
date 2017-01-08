<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Api;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Intl\Intl;

class LocaleApi
{
    /**
     * Locales with translations present
     * @var array
     */
    private $supportedLocales = [];

    /**
     * Get array of supported locales
     *
     * @return array
     */
    public function getSupportedLocales()
    {
        if (empty($this->supportedLocales)) {
            $this->supportedLocales[] = 'en';
            $finder = new Finder();
            if (is_dir('app/Resources/translations')) {
                $files = $finder->files()
                    ->in(['app/Resources/translations'])
                    ->depth(0)
                    ->name('*.po')
                    ->notName('*.template.*');
                foreach ($files as $file) {
                    $fileName = $file->getBasename('.po');
                    list($domain, $locale) = explode('.', $fileName);
                    if (!in_array($locale, $this->supportedLocales)) {
                        $this->supportedLocales[] = $locale;
                    }
                }
            }
            $this->addLegacyLocales(); // @deprecated remove at Core-2.0
        }

        return $this->supportedLocales;
    }

    /**
     * Get array of supported locales with their translated name
     *
     * @return array
     */
    public function getSupportedLocaleNames()
    {
        $locales = $this->getSupportedLocales();
        $namedLocales = [];
        foreach ($locales as $locale) {
            $namedLocales[Intl::getLanguageBundle()->getLanguageName($locale)] = $locale;
        }

        return $namedLocales;
    }

    /**
     * Detect languages preferred by browser and make best match to available provided languages.
     *
     * Adapted from StackOverflow response by Noel Whitemore
     * @see http://stackoverflow.com/a/26169603/2600812
     *
     * @param string $default
     * @return string
     */
    public function getBrowserLocale($default = 'en')
    {
        if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            return $default;
        }
        preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]), $matches, PREG_SET_ORDER);
        $availableLanguages = [];
        foreach ($matches as $match) {
            list($languageCode, $unusedVar) = explode('-', $match[1]) + ['', ''];
            $priority = isset($match[2]) ? (float) $match[2] : 1.0;
            $availableLanguages[][$languageCode] = $priority;
        }
        $defaultPriority = (float) 0;
        $matchedLanguage = '';
        foreach ($availableLanguages as $key => $value) {
            $languageCode = key($value);
            $priority = $value[$languageCode];
            $supportedLocales = $this->getSupportedLocales();
            if ($priority > $defaultPriority && array_key_exists($languageCode, $supportedLocales)) {
                $defaultPriority = $priority;
                $matchedLanguage = $languageCode;
            }
        }

        return $matchedLanguage != '' ? $matchedLanguage : $default;
    }

    /**
     * Read legacy locale.ini files and add those locales
     * @deprecated remove at Core-2.0
     */
    private function addLegacyLocales()
    {
        $legacyLocales = \ZLanguage::getInstalledLanguages();
        foreach ($legacyLocales as $locale) {
            if (!in_array($locale, $this->supportedLocales)) {
                $this->supportedLocales[] = $locale;
            }
        }
    }
}
