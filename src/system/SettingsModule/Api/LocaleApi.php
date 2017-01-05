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
}
