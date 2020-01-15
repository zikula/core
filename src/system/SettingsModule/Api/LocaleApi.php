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
use Symfony\Component\Intl\Languages;
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
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(ZikulaHttpKernelInterface $kernel, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
    }

    public function getSupportedLocales(): array
    {
        if (!empty($this->supportedLocales)) {
            return $this->supportedLocales;
        }

        $this->supportedLocales[] = 'en';
        $finder = new Finder();
        $translationPath = $this->kernel->getProjectDir() . '/translations';
        if (is_dir($translationPath)) {
            $files = $finder->files()
                ->in([$translationPath])
                ->depth(0)
                ->name(['*.csv', '*.dat', '*.ini', '*.mo', '*.php', '*.po', '*.qt', '*.xlf', '*.json', '*.yaml', '*.yml'])
                ->notName('*.template.*')
            ;
            foreach ($files as $file) {
                $fileName = $file->getBasename($file->getExtension());
                if (false === mb_strpos($fileName, '.')) {
                    continue;
                }
                list(, $locale) = explode('.', $fileName);
                if (!in_array($locale, $this->supportedLocales, true)) {
                    $this->supportedLocales[] = $locale;
                }
            }
        }

        return $this->supportedLocales;
    }

    public function getSupportedLocaleNames(string $region = null, string $displayLocale = null): array
    {
        $locales = $this->getSupportedLocales();
        $namedLocales = [];
        foreach ($locales as $locale) {
            // no way to set region
            $namedLocales[Languages::getName($locale, $displayLocale)] = $locale;
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
}
