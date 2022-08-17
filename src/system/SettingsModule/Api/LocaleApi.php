<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Api;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Locales;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\SettingsModule\Helper\LocaleConfigHelper;

class LocaleApi implements LocaleApiInterface
{
    /**
     * Locales with translations present
     */
    private array $supportedLocales = [];

    private bool $installed;

    private string $translationPath;

    private string $sectionKey;

    public function __construct(
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly RequestStack $requestStack,
        private readonly VariableApiInterface $variableApi,
        private readonly LocaleConfigHelper $localeConfigHelper,
        private readonly string $defaultLocale = 'en',
        string $installed = '0.0.0'
    ) {
        $this->supportedLocales = [
            'withRegions' => [],
            'withoutRegions' => []
        ];
        $this->installed = '0.0.0' !== $installed;
        $this->translationPath = $this->kernel->getProjectDir() . '/translations';
    }

    public function getSupportedLocales(bool $includeRegions = true, bool $syncConfig = true): array
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

        $multiLingualEnabled = (bool) $this->variableApi->get(VariableApi::CONFIG, 'multilingual', 1);
        if ($multiLingualEnabled) {
            // read in locales from translation path
            $this->collectLocales($includeRegions);
        }

        // ensure config file is still in sync
        if (true === $includeRegions) {
            if (null !== $this->requestStack) {
                $request = $this->requestStack->getCurrentRequest();
                if (null === $request) {
                    $syncConfig = false;
                } elseif ($request->isXmlHttpRequest()) {
                    $syncConfig = false;
                } elseif ($request !== $this->requestStack->getMainRequest()) {
                    $syncConfig = false;
                }
            }
            if ($syncConfig) {
                $this->localeConfigHelper->updateConfiguration($this->supportedLocales[$this->sectionKey]);
            }
        }

        return $this->supportedLocales[$this->sectionKey];
    }

    public function getSupportedLocaleNames(string $region = null, string $displayLocale = null, bool $includeRegions = true): array
    {
        $locales = $this->getSupportedLocales($includeRegions, false);
        $namedLocales = [];
        foreach ($locales as $locale) {
            $localeName = Locales::getName($locale, $displayLocale);
            $namedLocales[ucfirst($localeName)] = $locale;
        }
        ksort($namedLocales);

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
