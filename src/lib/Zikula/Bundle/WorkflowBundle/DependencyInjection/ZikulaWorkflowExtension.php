<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Workflow;

/**
 * Class ZikulaWorkflowExtension
 */
class ZikulaWorkflowExtension extends Extension implements PrependExtensionInterface
{
    private $workflowDirectories = [];

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // unrequired
        //$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        //$loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // central workflows in the core system are placed in: lib/Zikula/Bundle/CoreBundle/Resources/workflows/
        $this->workflowDirectories[] = __DIR__ . '/../Resources/workflows';

        $rootDirectory = $container->getParameter('kernel.root_dir');

        // Modules can define their own workflows in: modules/Acme/MyBundle/Resources/workflows/
        $this->workflowDirectories[] = $rootDirectory . '/../modules/*/*/Resources/workflows';

        // also it is possible to define custom workflows (or override existing ones) in: app/Resources/workflows/
        $this->workflowDirectories[] = $rootDirectory . '/Resources/workflows';

        $this->loadWorkflowDefinitions($container);
    }

    /**
     * Loads workflow files from given directories.
     *
     * @param ContainerBuilder $container
     *
     * @todo replace Finder usage by glob pattern in master branch
     * @see http://symfony.com/blog/new-in-symfony-3-3-import-config-files-with-glob-patterns
     */
    private function loadWorkflowDefinitions(ContainerBuilder $container)
    {
        try {
            $finder = new Finder();
            $finder->files()->name('*.yml')->in($this->workflowDirectories);
            foreach ($finder as $file) {
                $loader = new YamlFileLoader($container, new FileLocator($file->getPath()));
                $loader->load($file->getFilename());
            }

            $finder = new Finder();
            $finder->files()->name('*.xml')->in($this->workflowDirectories);
            foreach ($finder as $file) {
                $loader = new XmlFileLoader($container, new FileLocator($file->getPath()));
                $loader->load($file->getFilename());
            }
        } catch (\InvalidArgumentException $e) {
            // no module with a workflow directory exists, ignore
        }
    }
}
