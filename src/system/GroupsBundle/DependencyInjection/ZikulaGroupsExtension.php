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

namespace Zikula\GroupsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\GroupsBundle\Controller\GroupController;
use Zikula\GroupsBundle\Helper\DefaultHelper;
use Zikula\GroupsBundle\Menu\MenuBuilder;

class ZikulaGroupsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(GroupController::class)
            ->setArgument('$groupsPerPage', $config['groups_per_page'])
            ->setArgument('$defaultGroupId', $config['default_group'])
            ->setArgument('$hideClosedGroups', $config['hide_closed_groups'])
            ->setArgument('$hidePrivateGroups', $config['hide_private_groups']);

        $container->getDefinition(DefaultHelper::class)
            ->setArgument('$defaultGroupId', $config['default_group']);
    }
}
