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
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // modules may define their workflows in: <bundlePath>/Resources/workflows/
        $bundleMetaData = $container->getParameter('kernel.bundles_metadata');
        foreach ($bundleMetaData as $bundleName => $metaData) {
            if ('Module' != substr($bundleName, -6)) {
                continue;
            }

            $workflowPath = $metaData['path'] . '/Resources/workflows';
            if (!file_exists($workflowPath)) {
                continue;
            }
            $this->workflowDirectories[] = $workflowPath;
        }

        // also it is possible to define custom workflows in: app/Resources/workflows/
        $this->workflowDirectories[] = $container->getParameter('kernel.root_dir') . '/Resources/workflows';

        $this->loadWorkflowDefinitions($container);
    }

    /**
     * Loads workflow files from given directories.
     *
     * @param ContainerBuilder $container
     */
    private function loadWorkflowDefinitions(ContainerBuilder $container)
    {
        try {
            $finder = new Finder();
            $finder->files()->name('*.yml')->in($this->workflowDirectories);
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
        } catch (\InvalidArgumentException $e) {
            // no module with a workflow directory exists, ignore
        }
    }
}
