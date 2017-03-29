<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\RoutesModule\Translation;

use JMS\I18nRoutingBundle\Router\PatternGenerationStrategyInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This strategy duplicates \JMS\I18nRoutingBundle\Router\DefaultPatternGenerationStrategy
 * adding only the Zikula module prefix as requested
 */
class ZikulaPatternGenerationStrategy implements PatternGenerationStrategyInterface
{
    const STRATEGY_PREFIX = 'prefix';
    const STRATEGY_PREFIX_EXCEPT_DEFAULT = 'prefix_except_default';
    const STRATEGY_CUSTOM = 'custom';

    private $strategy;

    private $translator;

    private $translationDomain;

    private $locales;

    private $cacheDir;

    private $defaultLocale;

    private $modUrlMap = [];

    public function __construct($strategy, TranslatorInterface $translator, array $locales, $cacheDir, $translationDomain = 'routes', $defaultLocale = 'en')
    {
        $this->strategy = $strategy;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->locales = $locales;
        $this->cacheDir = $cacheDir;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritDoc
     */
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
                    $untranslatedPrefix = $this->getModUrlString($module);
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
                    $i18nPattern = $route->getOption('i18n_prefix').$i18nPattern;
                }
            }

            $patterns[$i18nPattern][] = $locale;
        }

        return $patterns;
    }

    /**
     * @inheritDoc
     */
    public function addResources(RouteCollection $i18nCollection)
    {
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir . '/translations/catalogue.' . $locale . '.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $i18nCollection->addResource($resource);
                }
            }
        }
    }

    /**
     * Customized method to cache the url string for modules.
     *
     * @param $moduleName
     * @return string
     */
    private function getModUrlString($moduleName)
    {
        if (!isset($this->modUrlMap[$moduleName])) {
            /** @var \ZikulaKernel $kernel */
            $kernel = $GLOBALS['kernel'];
            $module = $kernel->getModule($moduleName);
            // First get untranslated url from metaData.
            $url = $module->getMetaData()->getUrl(false);
            $this->modUrlMap[$moduleName] = $url;
        }

        return $this->modUrlMap[$moduleName];
    }
}
