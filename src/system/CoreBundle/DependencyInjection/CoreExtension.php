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

namespace Zikula\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Zikula\CoreBundle\Api\LocaleApi;
use Zikula\CoreBundle\EventSubscriber\ClickjackProtectionSubscriber;
use Zikula\CoreBundle\EventSubscriber\SiteOffSubscriber;
use Zikula\CoreBundle\Site\SiteDefinition;

class CoreExtension extends Extension implements PrependExtensionInterface
{
    private array $workflowDirectories = [];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('data_directory', $config['datadir']);

        $container->getDefinition(ClickjackProtectionSubscriber::class)
            ->setArgument('$xFrameOptions', $config['x_frame_options']);

        $container->getDefinition(SiteOffSubscriber::class)
            ->setArgument('$maintenanceModeEnabled', $config['maintenance_mode']['enabled'])
            ->setArgument('$maintenanceReason', $config['maintenance_mode']['reason']);

        $container->setParameter('enable_mail_logging', $config['enable_mail_logging']);

        $container->getDefinition(LocaleApi::class)
            ->setArgument('$multiLingualEnabled', $config['multilingual']);

        $container->getDefinition(SiteDefinition::class)
            ->setArgument('$siteData', $config['site_data']);
    }

    public function getNamespace(): string
    {
        return 'http://symfony.com/schema/dic/symfony';
    }

    public function prepend(ContainerBuilder $container): void
    {
        // bundles may define their workflows in: <bundlePath>/Resources/workflows/
        $bundleMetaData = $container->getParameter('kernel.bundles_metadata');
        foreach ($bundleMetaData as $bundleName => $metaData) {
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
    private function loadWorkflowDefinitions(ContainerBuilder $container): void
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
        } catch (\InvalidArgumentException) {
            // no bundle with a workflow directory exists, ignore
        }
    }
}
