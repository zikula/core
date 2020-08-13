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
        if (isset($container->getExtensions()['php_translation'])) {
            $container->prependExtensionConfig('php_translation', [
                'locales' => $zikulaSettingsConfig['locales']
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
}
