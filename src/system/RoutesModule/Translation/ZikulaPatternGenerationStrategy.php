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

namespace Zikula\RoutesModule\Translation;

use JMS\I18nRoutingBundle\Router\PatternGenerationStrategyInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

/**
 * This strategy duplicates \JMS\I18nRoutingBundle\Router\DefaultPatternGenerationStrategy
 * adding only the Zikula module prefix as requested
 */
class ZikulaPatternGenerationStrategy implements PatternGenerationStrategyInterface
{
    public const STRATEGY_PREFIX = 'prefix';

    public const STRATEGY_PREFIX_EXCEPT_DEFAULT = 'prefix_except_default';

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var string
     */
    private $translationDomain;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $urlMap = [];

    public function __construct(
        string $strategy,
        TranslatorInterface $translator,
        ZikulaHttpKernelInterface $kernel,
        array $locales,
        string $cacheDir,
        string $translationDomain = 'routes',
        string $defaultLocale = 'en'
    ) {
        $this->strategy = $strategy;
        $this->translator = $translator;
        $this->kernel = $kernel;
        $this->translationDomain = $translationDomain;
        $this->locales = $locales;
        $this->cacheDir = $cacheDir;
        $this->defaultLocale = $defaultLocale;
    }

    public function generateI18nPatterns($routeName, Route $route)
    {
        $patterns = [];
        foreach ($route->getOption('i18n_locales') ?: $this->locales as $locale) {
            // Check if translation exists in the translation catalogue
            if ($this->translator instanceof TranslatorBagInterface) {
                // Check if route is translated.
                if (!$this->translator->getCatalogue($locale)->has($routeName, $this->translationDomain)) {
                    // No translation found.
                    $i18nPattern = $route->getPath();
                } else {
                    // Get translation.
                    $i18nPattern = $this->translator->trans(/** @Ignore */$routeName, [], $this->translationDomain, $locale);
                }
            } else {
                // if no translation exists, we use the current pattern
                if ($routeName === $i18nPattern = $this->translator->trans(/** @Ignore */$routeName, [], $this->translationDomain, $locale)) {
                    $i18nPattern = $route->getPath();
                }
            }

            ///////////////////////////////////////
            // Begin customizations

            // prefix with zikula module url if requested
            if ($route->hasDefault('_zkModule')) {
                $module = $route->getDefault('_zkModule');
                $zkNoBundlePrefix = $route->getOption('zkNoBundlePrefix');
                if (!isset($zkNoBundlePrefix) || !$zkNoBundlePrefix) {
                    $untranslatedPrefix = $this->getUrlString($module);
                    if ($this->translator->getCatalogue($locale)->has($untranslatedPrefix, strtolower($module))) {
                        $prefix = $this->translator->trans(/** @Ignore */$untranslatedPrefix, [], strtolower($module), $locale);
                    } else {
                        $prefix = $untranslatedPrefix;
                    }
                    $i18nPattern = '/' . $prefix . $i18nPattern;
                }
            }

            // End customizations
            ///////////////////////////////////////

            // prefix with locale if requested
            if (self::STRATEGY_PREFIX === $this->strategy
                || (self::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)) {
                $i18nPattern = '/' . $locale . $i18nPattern;
                if (null !== $route->getOption('i18n_prefix')) {
                    $i18nPattern = $route->getOption('i18n_prefix') . $i18nPattern;
                }
            }

            $patterns[$i18nPattern][] = $locale;
        }

        return $patterns;
    }

    public function addResources(RouteCollection $i18nCollection)
    {
        foreach ($this->locales as $locale) {
            $metadata = $this->cacheDir . '/translations/catalogue.' . $locale . '.php.meta';
            if (!file_exists($metadata)) {
                continue;
            }
            foreach (unserialize(file_get_contents($metadata)) as $resource) {
                $i18nCollection->addResource($resource);
            }
        }
    }

    /**
     * Customized method to cache the url string for modules.
     */
    private function getUrlString(string $extensionName): string
    {
        if (!isset($this->urlMap[$extensionName])) {
            $extension = $this->kernel->getBundle($extensionName);
            // get untranslated url from metaData.
            $this->urlMap[$extensionName] = $extension->getMetaData()->getUrl(false);
        }

        return $this->urlMap[$extensionName];
    }
}
