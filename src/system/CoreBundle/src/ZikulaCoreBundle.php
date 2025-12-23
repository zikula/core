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

namespace Zikula\CoreBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zikula\CoreBundle\EventSubscriber\ClickjackProtectionSubscriber;
use Zikula\CoreBundle\EventSubscriber\SiteOffSubscriber;
use Zikula\CoreBundle\Site\SiteDefinition;

class ZikulaCoreBundle extends AbstractBundle
{
    private array $workflowDirectories = [];

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // bundles may define their workflows in: <bundlePath>/workflows/
        $bundleMetaData = $builder->getParameter('kernel.bundles_metadata');
        foreach ($bundleMetaData as $bundleName => $metaData) {
            $workflowPath = $metaData['path'] . '/workflows';
            if (!file_exists($workflowPath)) {
                continue;
            }
            $this->workflowDirectories[] = $workflowPath;
        }

        // also it is possible to define custom workflows in: config/workflows/
        $this->workflowDirectories[] = $builder->getParameter('kernel.project_dir') . '/config/workflows';

        $this->loadWorkflowDefinitions($builder);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // set parameters
        $container->parameters()
            ->set('data_directory', $config['datadir'])
            ->set('enable_mail_logging', $config['enable_mail_logging']);

        // configure services
        $services = $container->services();

        $services->get(ClickjackProtectionSubscriber::class)
            ->arg('$xFrameOptions', $config['x_frame_options']);

        $services->get(SiteOffSubscriber::class)
            ->arg('$maintenanceModeEnabled', $config['maintenance_mode']['enabled'])
            ->arg('$maintenanceReason', $config['maintenance_mode']['reason']);

        $services->get(SiteDefinition::class)
            ->arg('$siteData', $config['site_data']);
    }

    /**
     * Loads workflow files from given directories.
     */
    private function loadWorkflowDefinitions(ContainerBuilder $builder): void
    {
        try {
            $finder = new Finder();
            $finder->files()->name(['*.yml', '*.yaml'])->in($this->workflowDirectories);
            foreach ($finder as $file) {
                $filePath = $file->getPath();
                $loader = new YamlFileLoader($builder, new FileLocator($filePath));
                $loader->load($file->getFilename());
            }

            $finder = new Finder();
            $finder->files()->name('*.xml')->in($this->workflowDirectories);
            foreach ($finder as $file) {
                $loader = new XmlFileLoader($builder, new FileLocator($file->getPath()));
                $loader->load($file->getFilename());
            }
        } catch (\InvalidArgumentException) {
            // no bundle with a workflow directory exists, ignore
        }
    }
}
