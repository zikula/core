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

namespace Zikula\SettingsModule\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;

class ZikulaSettingsExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $zikulaSettingsConfig = $this->processConfiguration(new Configuration(), $configs);
        if (isset($container->getExtensions()['framework'])) {
            $container->prependExtensionConfig('framework', [
                'default_locale' => $zikulaSettingsConfig['locale']
            ]);
        }
        if (isset($container->getExtensions()['bazinga_js_translation'])) {
            $container->prependExtensionConfig('bazinga_js_translation', [
                'locale_fallback' => $zikulaSettingsConfig['locale'],
                'active_locales' => $zikulaSettingsConfig['locales']
            ]);
        }
        if (isset($container->getExtensions()['jms_i18n_routing'])) {
            $container->prependExtensionConfig('jms_i18n_routing', [
                'default_locale' => $zikulaSettingsConfig['locale'],
                'locales' => $zikulaSettingsConfig['locales']
            ]);
        }
        if (isset($container->getExtensions()['translation'])) {
            $container->prependExtensionConfig('translation', [
                'locales' => $zikulaSettingsConfig['locales'],
                'configs' => $this->generatePhpTranslationConfigs($container)
            ]);
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('locale', $config['locale']);
        $container->setParameter('localisation.locales', $config['locales']);
    }

    private function generatePhpTranslationConfigs(ContainerBuilder $container): array
    {
        $transConfigNew = $this->getBaseConfig();

        if (file_exists($container->getParameter('kernel.project_dir') . '/src/system')) {
            // monorepo: core bundles and system modules are in "src/"
            $transConfigNew['zikula']['dirs'] = [
                '%kernel.project_dir%/templates',
                '%kernel.project_dir%/src/system',
                '%kernel.project_dir%/src/Zikula'
            ];
            // do not set in a distribution package when core components are in "vendor/"
        }

        $bundles = array_filter($container->getParameter('kernel.bundles'), function($bundleClassName, $name) use ($container) {
            if (ZikulaKernel::isCoreExtension($name)) {
                return false;
            }

            return $container->getReflectionClass($bundleClassName)->isSubclassOf(AbstractExtension::class);
        }, ARRAY_FILTER_USE_BOTH);
        foreach ($bundles as $name => $bundleClassName) {
            $bundleConfig = $this->getConfigTemplate();
            $bundleDir = dirname($container->getReflectionClass($bundleClassName)->getFileName());
            $translationDirectory = $bundleDir . '/Resources/translations';
            $bundleConfig['output_dir'] = $translationDirectory;
            $bundleConfig['external_translations_dir'] = $translationDirectory;
            $transConfigNew[mb_strtolower($name)] = $bundleConfig;
        }

        return $transConfigNew;
    }

    private function getBaseConfig(): array
    {
        $config = [
            'zikula' => $this->getConfigTemplate(),
            'extension' => $this->getConfigTemplate()
        ];
        $config['zikula']['output_dir'] = '%kernel.project_dir%/translations';
        $config['zikula']['excluded_dirs'] = ['vendor', 'cache', 'data', 'log'];
        $config['extension']['excluded_dirs'] = ['vendor'];

        return $config;
    }

    private function getConfigTemplate(): array
    {
        return [
            'excluded_names' => ['*TestCase.php', '*Test.php'],
            'excluded_dirs' => ['vendor'],
            'output_format' => 'yaml',
            'local_file_storage_options' => [
                'default_output_format' => 'yaml'
            ]
        ];
    }
}
