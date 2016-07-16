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
 * ZLanguageBrowser class.
 */
class ZLanguageBrowser
{
    /**
     * Available languages.
     *
     * @var array
     */
    private $available;

    /**
     * Constructor.
     *
     * @param array $langList Available languages
     */
    public function __construct($langList)
    {
        $this->available = $langList;
    }

    /**
     * Discover preferred language.
     *
     * @param string $default
     * @return string
     */
    public function discover($default = 'en')
    {
        return $this->matchBrowserLanguage($this->available, $default);
    }

    /**
     * Detect languages preferred by browser and make best match to available provided languages.
     *
     * Adapted from StackOverflow response by Noel Whitemore
     * @see http://stackoverflow.com/a/26169603/2600812
     *
     * @param array $supportedLanguages for example: ["en", "nl", "de"]
     * @param string $default
     * @return mixed|string
     */
    private function matchBrowserLanguage(array $supportedLanguages, $default = 'en')
    {
        $supportedLanguages = array_flip($supportedLanguages);
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
            if ($priority > $defaultPriority && array_key_exists($languageCode, $supportedLanguages)) {
                $defaultPriority = $priority;
                $matchedLanguage = $languageCode;
            }
        }

        return $matchedLanguage != '' ? $matchedLanguage : $default;
    }
}
