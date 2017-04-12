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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class LocaleApi implements LocaleApiInterface
{
    /**
     * Locales with translations present
     * @var array
     */
    private $supportedLocales = [];

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * LocaleApi constructor.
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedLocales()
    {
        if (empty($this->supportedLocales)) {
            $this->supportedLocales[] = 'en';
            $finder = new Finder();
            $translationPath = $this->kernel->getRootDir() . '/Resources/translations';
            if (is_dir($translationPath)) {
                $files = $finder->files()
                    ->in([$translationPath])
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
        }

        return $this->supportedLocales;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getBrowserLocale($default = 'en')
    {
        if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) || php_sapi_name() == "cli") {
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
}
