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

namespace Zikula\SettingsModule\Helper;

use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class LocaleConfigHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

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
     * @var string
     */
    private $defaultLocale;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper,
        CacheClearer $cacheClearer,
        string $defaultLocale = 'en',
        string $installed = '0.0.0'
    ) {
        $this->kernel = $kernel;
        $this->variableApi = $variableApi;
        $this->configDumper = $configDumper;
        $this->cacheClearer = $cacheClearer;
        $this->defaultLocale = $defaultLocale;
        $this->installed = '0.0.0' !== $installed;
    }

    public function updateConfiguration(array $locales = [])
    {
        if (!$this->installed) {
            return;
        }

        $defaultLocale = $this->variableApi->getSystemVar('locale', $this->defaultLocale);
        if (!in_array($defaultLocale, $locales, true)) {
            // if the current default locale is not available, use the first available.
            $defaultLocale = array_values($locales)[0];
            $this->variableApi->set(VariableApi::CONFIG, 'locale', $defaultLocale);
        }
        if ($defaultLocale !== $this->defaultLocale) {
            // update locale parameter in config/services_custom.yaml
            $yamlManager = new YamlDumper($this->kernel->getProjectDir() . '/config');
            $yamlManager->setParameter('locale', $defaultLocale);
        }

        $parameterName = 'localisation.locales';
        $storedLocales = $this->configDumper->getParameter($parameterName);
        if (is_array($storedLocales)) {
            $diff1 = array_diff($storedLocales, $locales);
            $diff2 = array_diff($locales, $storedLocales);
            if (0 < count($diff1) || 0 < count($diff2)) {
                $this->configDumper->setParameter($parameterName, $locales);
            }
        }

        $this->cacheClearer->clear('symfony');
    }
}
