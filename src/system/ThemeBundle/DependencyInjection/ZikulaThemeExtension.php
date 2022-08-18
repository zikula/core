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

namespace Zikula\ThemeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\ThemeBundle\Engine\Asset\CssResolver;
use Zikula\ThemeBundle\Engine\Asset\JsResolver;
use Zikula\ThemeBundle\Engine\Asset\Merger;
use Zikula\ThemeBundle\Engine\AssetFilter;
use Zikula\ThemeBundle\EventListener\DefaultPageAssetSetterListener;
use Zikula\ThemeBundle\EventListener\ExtensionInstallationListener;
use Zikula\ThemeBundle\EventListener\ResponseTransformerListener;

class ZikulaThemeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(AssetFilter::class)
            ->setArgument('$scriptPosition', $config['script_position']);
        $container->getDefinition(ResponseTransformerListener::class)
            ->setArgument('$trimWhitespace', $config['trimwhitespace']);

        $container->getDefinition(JsResolver::class)
            ->setArgument('$combine', $config['asset_manager']['combine']);
        $container->getDefinition(CssResolver::class)
            ->setArgument('$combine', $config['asset_manager']['combine']);

        $merger = $container->getDefinition(Merger::class);
        $skipFiles = $merger->getArgument('$skipFiles');
        $skipFiles[] = $config['bootstrap']['css_path'];
        $skipFiles[] = $config['font_awesome_path'];
        $merger->setArgument('$lifetime', $config['asset_manager']['lifetime'])
            ->setArgument('$minify', $config['asset_manager']['minify'])
            ->setArgument('$compress', $config['asset_manager']['compress'])
            ->setArgument('$skipFiles', $skipFiles);

        $container->getDefinition(DefaultPageAssetSetterListener::class)
            ->setArgument('$bootstrapJavascriptPath', $config['bootstrap']['js_path'])
            ->setArgument('$bootstrapStylesheetPath', $config['bootstrap']['css_path'])
            ->setArgument('$fontAwesomePath', $config['font_awesome_path']);
    }
}
