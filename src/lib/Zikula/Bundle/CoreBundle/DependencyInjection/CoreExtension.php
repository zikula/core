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

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Validation;
use Zikula\Common\Translator\Translator;
use Zikula\Core\LinkContainer\LinkContainerInterface;

/**
 * CoreExtension class.
 */
class CoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('symfony_overrides.yml');
        $loader->load('session.yml');
        $loader->load('services.yml');

        $container->registerForAutoconfiguration(LinkContainerInterface::class)
            ->addTag('zikula.link_container')
        ;

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerTranslatorConfiguration($config['translator'], $container);
    }

    /**
     * Loads the translator configuration.
     */
    protected function registerTranslatorConfiguration(array $config, ContainerBuilder $container): void
    {
        $translatorServiceDefinition = $container->findDefinition(Translator::class);
        $translatorServiceDefinition->addMethodCall('setFallbackLocales', [
            $config['fallbacks']
        ]);
        $container->setParameter('translator.logging', $config['logging']);

        // Discover translation directories
        $translationsFolder = '/Resources/translations';
        $dirs = [];
        if (class_exists(Validation::class)) {
            $r = new ReflectionClass(Validation::class);
            $dirs[] = dirname($r->getFileName()) . $translationsFolder;
        }
        if (class_exists(Form::class)) {
            $r = new ReflectionClass(Form::class);
            $dirs[] = dirname($r->getFileName()) . $translationsFolder;
        }
        if (class_exists(AuthenticationException::class)) {
            $r = new ReflectionClass(AuthenticationException::class);
            $dirs[] = dirname($r->getFileName()) . '/..' . $translationsFolder;
        }

        $appResourcesPath = $container->getParameter('kernel.project_dir') . '/app/Resources/';

        $overridePath = $appResourcesPath . '%s/translations';
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFileName()) . $translationsFolder)) {
                $dirs[] = $dir;
            }

            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $dirs[] = $dir;
            }
        }

        if (is_dir($dir = $appResourcesPath . 'translations')) {
            $dirs[] = $dir;
        }

        // Register translation resources
        if ($dirs) {
            foreach ($dirs as $dir) {
                $container->addResource(new DirectoryResource($dir));
            }

            $finder = Finder::create()->files()
                ->filter(static function(SplFileInfo $file) {
                    return 2 === mb_substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
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

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    public function getNamespace(): string
    {
        return 'http://symfony.com/schema/dic/symfony';
    }
}
