<?php
/**
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
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * CoreExtension class.
 */
class CoreExtension extends Extension
{
    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('session.xml');
        $loader->load('services.xml');
        $loader->load('core.xml');
        $loader->load('twig.xml');
        $loader->load('translation.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // @todo - temporary - remove at Core-2.0, also need to redeligate some
        // of this to other's responsibility
        $cacheDir = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/ztemp');
        $dirs = [
            'doctrinemodels',
            'idsTmp',
            'purifierCache',
            'doctrinemodels',
            'Theme_cache',
            'Theme_compiled',
            'Theme_Config',
            'view_cache',
            'view_compiled',
            'error_logs'
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($cacheDir . '/' . $dir)) {
                mkdir($cacheDir . '/' . $dir, 0777, true);
            }
        }

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
    private function registerTranslatorConfiguration(array $config, ContainerBuilder $container)
    {
        $translator = $container->findDefinition('translator.default');
        $translator->addMethodCall('setFallbackLocales', [
            $config['fallbacks']
        ]);
        $container->setParameter('translator.logging', $config['logging']);

        // Discover translation directories
        $dirs = [];

        if (class_exists('Symfony\Component\Validator\Validator')) {
            $r = new \ReflectionClass('Symfony\Component\Validator\Validator');
            $dirs[] = dirname($r->getFilename()) . '/Resources/translations';
        }
        if (class_exists('Symfony\Component\Form\Form')) {
            $r = new \ReflectionClass('Symfony\Component\Form\Form');
            $dirs[] = dirname($r->getFilename()) . '/Resources/translations';
        }
        if (class_exists('Symfony\Component\Security\Core\Exception\AuthenticationException')) {
            $r = new \ReflectionClass('Symfony\Component\Security\Core\Exception\AuthenticationException');
            $dirs[] = dirname($r->getFilename()) . '/../Resources/translations';
        }

        $overridePath = $container->getParameter('kernel.root_dir') . '/Resources/%s/translations';
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()) . '/Resources/translations')) {
                $dirs[] = $dir;
            }

            if (is_dir($dir = dirname($reflection->getFilename()) . '/Resources/locale')) {
                $dirs[] = $dir;
            }

            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $dirs[] = $dir;
            }
        }

        if (is_dir($dir = $container->getParameter('kernel.root_dir') . '/Resources/translations')) {
            $dirs[] = $dir;
        }

        if (is_dir($dir = $container->getParameter('kernel.root_dir') . '/Resources/locale')) {
            $dirs[] = $dir;
        }

        // Register translation resources
        if ($dirs) {
            foreach ($dirs as $dir) {
                $container->addResource(new DirectoryResource($dir));
            }

            // nativ
            $finder = Finder::create()->files()
                ->filter(function (\SplFileInfo $file) {
                    return 2 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
                })
                ->in($dirs);

            foreach ($finder as $file) {
                // filename is domain.locale.format
                list($domain, $locale, $format) = explode('.', $file->getBasename(), 3);
                $translator->addMethodCall('addResource', [
                    $format,
                    (string) $file,
                    $locale,
                    $domain
                ]);
            }

            // zikula
            $zfinder = Finder::create()->files()
                ->filter(function (\SplFileInfo $file) {
                    return 1 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
                })
                ->in($dirs);

            foreach ($zfinder as $file) {
                // filepath/name is locale/catalogue/domain.format
                $path_arr = explode('/', $file->getRelativePath());
                if (count($path_arr) == 2) {
                    $locale = $path_arr[0];
                    list($domain, $format) = explode('.', $file->getBasename(), 2);
                    // todo add $config['zk_loader'] config.xml translator when mo files handling will be working
                    if ($format == 'po') {
                        $translator->addMethodCall('addResource', [
                            $format,
                            (string) $file,
                            $locale,
                            $domain
                        ]);
                    }
                }
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
