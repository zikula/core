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

namespace Zikula\SettingsModule\Helper;

use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class LocaleConfigHelper
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

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

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        string $projectDir,
        string $defaultLocale = 'en',
        string $installed = '0.0.0'
    ) {
        $this->variableApi = $variableApi;
        $this->cacheClearer = $cacheClearer;
        $this->projectDir = $projectDir;
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
        $configurator = new Configurator($this->projectDir);
        $configurator->loadPackages(['zikula_settings']);
        if ($defaultLocale !== $this->defaultLocale) {
            $configurator->set('zikula_settings', 'locale', $defaultLocale);
        }

        $storedLocales = $configurator->get('zikula_settings', 'locales');
        if (is_array($storedLocales)) {
            $diff1 = array_diff($storedLocales, $locales);
            $diff2 = array_diff($locales, $storedLocales);
            if (0 < count($diff1) || 0 < count($diff2)) {
                $configurator->set('zikula_settings', 'locales', $locales);
            }
        }

        $configurator->write();
        $this->cacheClearer->clear('symfony');
    }
}
