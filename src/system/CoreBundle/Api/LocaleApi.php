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

namespace Zikula\CoreBundle\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Locales;
use Zikula\CoreBundle\Api\ApiInterface\LocaleApiInterface;

class LocaleApi implements LocaleApiInterface
{
    /**
     * Locales with translations present
     */
    private array $supportedLocales = [];

    private string $translationPath;

    private string $sectionKey;

    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly bool $multiLingualEnabled,
        #[Autowire('%kernel.default_locale%')]
        private readonly string $defaultLocale
    ) {
        $this->supportedLocales = [
            'withRegions' => [],
            'withoutRegions' => []
        ];
        $this->translationPath = $this->projectDir . '/translations';
    }

    public function multilingual(): bool
    {
        return $this->multiLingualEnabled;
    }

    public function getSupportedLocales(bool $includeRegions = true): array
    {
        $this->sectionKey = $includeRegions ? 'withRegions' : 'withoutRegions';

        if (!empty($this->supportedLocales[$this->sectionKey])) {
            return $this->supportedLocales[$this->sectionKey];
        }

        $this->supportedLocales[$this->sectionKey][] = $this->defaultLocale;

        if (!is_dir($this->translationPath)) {
            return $this->supportedLocales[$this->sectionKey];
        }

        if ($this->multiLingualEnabled) {
            // read in locales from translation path
            $this->collectLocales($includeRegions);
        }

        return $this->supportedLocales[$this->sectionKey];
    }

    public function getSupportedLocaleNames(string $region = null, string $displayLocale = null, bool $includeRegions = true): array
    {
        $locales = $this->getSupportedLocales($includeRegions);
        $namedLocales = [];
        foreach ($locales as $locale) {
            $localeName = Locales::getName($locale, $displayLocale);
            $namedLocales[ucfirst($localeName)] = $locale;
        }
        ksort($namedLocales);

        return $namedLocales;
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
