<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Helper;

use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class MultilingualRoutingHelper
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper,
        CacheClearer $cacheClearer,
        LocaleApiInterface $localeApi,
        ZikulaHttpKernelInterface $kernel,
        $locale,
        $installed
    ) {
        $this->variableApi = $variableApi;
        $this->configDumper = $configDumper;
        $this->cacheClearer = $cacheClearer;
        $this->localeApi = $localeApi;
        $this->kernel = $kernel;
        $this->locale = $locale;
        $this->installed = $installed;
    }

    /**
     * Reloads the multilingual routing settings by reading system variables
     * and checking installed languages.
     *
     * @return bool
     */
    public function reloadMultilingualRoutingSettings()
    {
        $supportedLocales = $this->localeApi->getSupportedLocales();

        // update the custom_parameters.yml file
        $defaultLocale = $this->installed
            ? $this->variableApi->getSystemVar('language_i18n', $this->locale)
            : $this->locale
        ;
        if (!in_array($defaultLocale, $supportedLocales, true)) {
            // if the current default locale is not available, use the first available.
            $defaultLocale = array_values($supportedLocales)[0];
            if ($this->installed) {
                $this->variableApi->set(VariableApi::CONFIG, 'language_i18n', $defaultLocale);
                $this->variableApi->set(VariableApi::CONFIG, 'locale', $defaultLocale);
            }
        }
        if ($this->installed) {
            $yamlManager = new YamlDumper($this->kernel->getProjectDir() . '/app/config');
            $yamlManager->setParameter('locale', $defaultLocale);
        }

        $isRequiredLangParameter = $this->installed
            ? $this->variableApi->getSystemVar('languageurl', 0)
            : 0
        ;

        $this->configDumper->setConfiguration('jms_i18n_routing', [
            'default_locale' => $defaultLocale,
            'locales' => $supportedLocales,
            'strategy' => $isRequiredLangParameter ? 'prefix' : 'prefix_except_default'
        ]);

        $this->cacheClearer->clear('symfony');

        return true;
    }
}
