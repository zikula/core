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

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\Controller\MainController;

class CoreExtension extends Extension implements PrependExtensionInterface
{
    private array $workflowDirectories = [];

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('session.yaml');
        $loader->load('services.yaml');
        $loader->load('translation.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('datadir', $config['datadir']);

        $this->addAnnotatedClassesToCompile([
            MainController::class,
            'Zikula\\*Bundle\\Controller\\',
            'Zikula\\*Bundle\\Entity\\',
        ]);
    }

    public function getNamespace(): string
    {
        return 'http://symfony.com/schema/dic/symfony';
    }

    public function prepend(ContainerBuilder $container)
    {
        // modules may define their workflows in: <bundlePath>/Resources/workflows/
        $bundleMetaData = $container->getParameter('kernel.bundles_metadata');
        foreach ($bundleMetaData as $bundleName => $metaData) {
            // TODO recheck
            /*if (!s($bundleName)->endsWith('Module')) {
                continue;
            }*/
            $workflowPath = $metaData['path'] . '/Resources/workflows';
            if (!file_exists($workflowPath)) {
                continue;
            }
            $this->workflowDirectories[] = $workflowPath;
        }

        // also it is possible to define custom workflows in: config/workflows/
        $this->workflowDirectories[] = $container->getParameter('kernel.project_dir') . '/config/workflows';

        $this->loadWorkflowDefinitions($container);
    }

    /**
     * Loads workflow files from given directories.
     */
    private function loadWorkflowDefinitions(ContainerBuilder $container)
    {
        try {
            $finder = new Finder();
            $finder->files()->name(['*.yml', '*.yaml'])->in($this->workflowDirectories);
            foreach ($finder as $file) {
                $filePath = $file->getPath();
                $loader = new YamlFileLoader($container, new FileLocator($filePath));
                $loader->load($file->getFilename());
            }

            $finder = new Finder();
            $finder->files()->name('*.xml')->in($this->workflowDirectories);
            foreach ($finder as $file) {
                $loader = new XmlFileLoader($container, new FileLocator($file->getPath()));
                $loader->load($file->getFilename());
            }
        } catch (InvalidArgumentException) {
            // no module with a workflow directory exists, ignore
        }
    }
}
