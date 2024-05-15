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

namespace Zikula\LegalBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zikula\LegalBundle\Controller\UserController;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\LegalBundle\Menu\ExtensionMenu;
use Zikula\LegalBundle\Twig\TwigExtension;

/**
 * Dependency injection extension for the Legal bundle.
 */
class ZikulaLegalExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(UserController::class)
            ->setArgument('$legalConfig', $config);
        $container->getDefinition(AcceptPoliciesHelper::class)
            ->setArgument('$legalConfig', $config);
        $container->getDefinition(ExtensionMenu::class)
            ->setArgument('$legalConfig', $config);
        $container->getDefinition(TwigExtension::class)
            ->setArgument('$legalConfig', $config);
    }
}
