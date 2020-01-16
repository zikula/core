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

class TranslationConfigHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        DynamicConfigDumper $configDumper,
        CacheClearer $cacheClearer,
        bool $installed = false
    ) {
        $this->kernel = $kernel;
        $this->configDumper = $configDumper;
        $this->cacheClearer = $cacheClearer;
        $this->installed = $installed;
    }

    public function updateConfiguration()
    {
        if (!$this->installed) {
            return;
        }

        $configName = 'translation';
        $transConfigOld = $this->configDumper->getConfiguration($configName);
        $transConfigNew = [
            'configs' => [
                'zikula' => $transConfigOld['configs']['zikula'],
                'extension' => $transConfigOld['configs']['extension']
            ]
        ];
        $configTemplate = [
            'excluded_names' => ['*TestCase.php', '*Test.php'],
            'excluded_dirs' => ['vendor'],
            'output_format' => 'yaml'
        ];
        foreach ($this->kernel->getModules() as $bundle) {
            if ($this->kernel->isCoreModule($bundle->getName())) {
                continue;
            }
            $bundleConfig = $configTemplate;
            $bundleConfig['external_translations_dir'] = $bundle->getPath() . '/Resources/translations';
            $transConfigNew['configs'][mb_strtolower($bundle->getName())] = $bundleConfig;
        }
        foreach ($this->kernel->getThemes() as $bundle) {
            if (in_array($bundle->getName(), ['ZikulaBootstrapTheme', 'ZikulaAtomTheme', 'ZikulaPrinterTheme', 'ZikulaRssTheme'], true)) {
                continue;
            }
            $bundleConfig = $configTemplate;
            $bundleConfig['external_translations_dir'] = $bundle->getPath() . '/Resources/translations';
            $transConfigNew['configs'][mb_strtolower($bundle->getName())] = $bundleConfig;
        }

        $this->configDumper->setConfiguration($configName, $transConfigNew);

        $this->cacheClearer->clear('symfony');
    }
}
