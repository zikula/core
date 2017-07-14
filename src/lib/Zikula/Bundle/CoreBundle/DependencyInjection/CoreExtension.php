<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * CoreExtension class.
 */
class CoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('symfony_overrides.yml');
        $loader->load('session.yml');
        $loader->load('services.yml');
        $loader->load('listeners.yml');
        $loader->load('core.yml');
        $loader->load('twig.yml');
        $loader->load('translation.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerTranslatorConfiguration($config['translator'], $container);
    }

    /**
     * Loads the translator configuration.
     *
     * @param array $config
     *            A translator configuration array
     * @param ContainerBuilder $container
     *            A ContainerBuilder instance
     */
    protected function registerTranslatorConfiguration(array $config, ContainerBuilder $container)
    {
        $translatorServiceDefinition = $container->findDefinition('translator.default');
        $translatorServiceDefinition->addMethodCall('setFallbackLocales', [
            $config['fallbacks']
        ]);
        $container->setParameter('translator.logging', $config['logging']);

        // Discover translation directories
        $dirs = [];

        if (class_exists('Symfony\Component\Validator\Validator')) {
            $r = new \ReflectionClass('Symfony\Component\Validator\Validator');
            $dirs[] = dirname($r->getFileName()) . '/Resources/translations';
        }
        if (class_exists('Symfony\Component\Form\Form')) {
            $r = new \ReflectionClass('Symfony\Component\Form\Form');
            $dirs[] = dirname($r->getFileName()) . '/Resources/translations';
        }
        if (class_exists('Symfony\Component\Security\Core\Exception\AuthenticationException')) {
            $r = new \ReflectionClass('Symfony\Component\Security\Core\Exception\AuthenticationException');
            $dirs[] = dirname($r->getFileName()) . '/../Resources/translations';
        }

        $appResourcesPath = $container->getParameter('kernel.root_dir') . '/Resources/';

        $overridePath = $appResourcesPath . '%s/translations';
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFileName()) . '/Resources/translations')) {
                $dirs[] = $dir;
            }

            if (is_dir($dir = dirname($reflection->getFileName()) . '/Resources/locale')) {
                $dirs[] = $dir;
            }

            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $dirs[] = $dir;
            }
        }

        if (is_dir($dir = $appResourcesPath . 'translations')) {
            $dirs[] = $dir;
        }

        if (is_dir($dir = $appResourcesPath . 'locale')) {
            $dirs[] = $dir;
        }

        // Register translation resources
        if ($dirs) {
            foreach ($dirs as $dir) {
                $container->addResource(new DirectoryResource($dir));
            }

            $finder = Finder::create()->files()
                ->filter(function(\SplFileInfo $file) {
                    return 2 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
                })
                ->in($dirs);

            foreach ($finder as $file) {
                // filename is domain.locale.format
                list($domain, $locale, $format) = explode('.', $file->getBasename(), 3);
                $translatorServiceDefinition->addMethodCall('addResource', [
                    $format,
                    (string)$file,
                    $locale,
                    $domain
                ]);
            }
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/symfony';
    }
}
