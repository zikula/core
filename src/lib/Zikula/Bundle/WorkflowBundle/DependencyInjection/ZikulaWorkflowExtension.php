<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
//use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ZikulaWorkflowExtension
 */
class ZikulaWorkflowExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
/*
        $workflowPath = __DIR__ . '/../../../../../app/config/workflows';
        $loader = new YamlFileLoader($container, new FileLocator($workflowPath));

        // @todo replace Finder usage by glob pattern in master branch
        // see http://symfony.com/blog/new-in-symfony-3-3-import-config-files-with-glob-patterns

        $finder = new Finder();
        $finder->files()->name('*.yml')->in($workflowPath);
        foreach ($finder as $file) {
            $loader->load($file->getFilename());
        }
*/
    }
}
