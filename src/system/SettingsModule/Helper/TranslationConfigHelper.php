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
            'output_format' => 'yaml',
            'local_file_storage_options' => [
                'default_output_format' => 'yaml'
            ]
        ];
        foreach ($this->kernel->getModules() as $bundle) {
            if ($this->kernel->isCoreExtension($bundle->getName())) {
                continue;
            }
            $bundleConfig = $configTemplate;
            $translationDirectory = $bundle->getPath() . '/Resources/translations';
            $bundleConfig['output_dir'] = $translationDirectory;
            $bundleConfig['external_translations_dir'] = $translationDirectory;
            $transConfigNew['configs'][mb_strtolower($bundle->getName())] = $bundleConfig;
        }
        foreach ($this->kernel->getThemes() as $bundle) {
            // lets include core themes as they need translation as all other themes, too
            // (/system is included in "zikula" config while /themes is not)
            /*if (in_array($bundle->getName(), ['ZikulaBootstrapTheme', 'ZikulaAtomTheme', 'ZikulaPrinterTheme', 'ZikulaRssTheme'], true)) {
                continue;
            }*/
            $bundleConfig = $configTemplate;
            $translationDirectory = $bundle->getPath() . '/Resources/translations';
            $bundleConfig['output_dir'] = $translationDirectory;
            $bundleConfig['external_translations_dir'] = $translationDirectory;
            $transConfigNew['configs'][mb_strtolower($bundle->getName())] = $bundleConfig;
        }

        $this->configDumper->setConfiguration($configName, $transConfigNew);

        $this->cacheClearer->clear('symfony');
    }
}
