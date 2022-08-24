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

namespace Zikula\PermissionsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\PermissionsBundle\Controller\PermissionController;
use Zikula\PermissionsBundle\Menu\MenuBuilder;

class ZikulaPermissionsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(PermissionController::class)
            ->setArgument('$lockAdminRule', $config['lock_admin_rule'])
            ->setArgument('$adminRuleId', $config['admin_rule_id'])
            ->setArgument('$enableFiltering', $config['enable_filtering']);

        $container->getDefinition(MenuBuilder::class)
            ->setArgument('$lockAdminRule', $config['lock_admin_rule'])
            ->setArgument('$adminRuleId', $config['admin_rule_id']);
    }
}
