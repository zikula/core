<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Api;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Locales;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\SettingsModule\Helper\LocaleConfigHelper;

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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var LocaleConfigHelper
     */
    private $localeConfigHelper;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var string
     */
    private $translationPath;

    /**
     * @var string
     */
    private $sectionKey;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        RequestStack $requestStack,
        LocaleConfigHelper $localeConfigHelper,
        string $defaultLocale = 'en',
        bool $installed = false
    ) {
        $this->supportedLocales = [
            'withRegions' => [],
            'withoutRegions' => []
        ];
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
        $this->localeConfigHelper = $localeConfigHelper;
        $this->defaultLocale = $defaultLocale;
        $this->installed = $installed;
        $this->translationPath = $this->kernel->getProjectDir() . '/translations';
    }

    public function getSupportedLocales(bool $includeRegions = true): array
    {
        $this->sectionKey = $includeRegions ? 'withRegions' : 'withoutRegions';

        if (!empty($this->supportedLocales[$this->sectionKey])) {
            return $this->supportedLocales[$this->sectionKey];
        }

        $this->supportedLocales[$this->sectionKey][] = $this->defaultLocale;
        if (!$this->installed) {
            return $this->supportedLocales[$this->sectionKey];
        }

        if (!is_dir($this->translationPath)) {
            return $this->supportedLocales[$this->sectionKey];
        }

        // read in locales from translation path
        $this->collectLocales($includeRegions);

        // ensure config file is still in sync
        $this->localeConfigHelper->updateConfiguration($this->supportedLocales[$this->sectionKey], $includeRegions);

        return $this->supportedLocales[$this->sectionKey];
    }

    public function getSupportedLocaleNames(string $region = null, string $displayLocale = null, bool $includeRegions = true): array
    {
        $locales = $this->getSupportedLocales($includeRegions);
        $namedLocales = [];
        foreach ($locales as $locale) {
            $localeName = Locales::getName($locale, $displayLocale);
            $namedLocales[$localeName] = $locale;
        }

        return $namedLocales;
    }

    public function getBrowserLocale(string $default = 'en'): string
    {
        $request = null !== $this->requestStack ? $this->requestStack->getCurrentRequest() : null;
        if (null === $request || 'cli' === PHP_SAPI) {
            return $default;
        }

        return $request->getPreferredLanguage($this->getSupportedLocales()) ?? $default;
    }

    private function getTranslationFiles(): Finder
    {
        $finder = new Finder();
        $files = $finder->files()
            ->in([$this->translationPath])
            ->depth(0)
            ->name(['*.csv', '*.dat', '*.ini', '*.mo', '*.php', '*.po', '*.qt', '*.xlf', '*.json', '*.yaml', '*.yml'])
            ->notName('*.template.*')
        ;

        return $files;
    }

    private function collectLocales(bool $includeRegions = true): void
    {
        $files = $this->getTranslationFiles();
        foreach ($files as $file) {
            $fileName = $file->getBasename($file->getExtension());
            if (false === mb_strpos($fileName, '.')) {
                continue;
            }
            list(, $locale) = explode('.', $fileName);
            if (!$includeRegions && false !== mb_strpos($locale, '_')) {
                $localeParts = explode('_', $locale);
                $locale = $localeParts[0];
            }
            if (!in_array($locale, $this->supportedLocales[$this->sectionKey], true)) {
                $this->supportedLocales[$this->sectionKey][] = $locale;
            }
        }
    }
}
