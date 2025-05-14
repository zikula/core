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

namespace Zikula\LegalBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\LegalBundle\Bundle\MetaData\LegalBundleMetaData;
use Zikula\LegalBundle\Controller\UserController;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\LegalBundle\Menu\ExtensionMenu;
use Zikula\LegalBundle\Twig\TwigExtension;

class ZikulaLegalBundle extends AbstractBundle implements MetaDataAwareBundleInterface
{
    public function getMetaData(): BundleMetaDataInterface
    {
        return $this->container->get(LegalBundleMetaData::class);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // configure services
        $services = $container->services();

        $services->get(UserController::class)
            ->arg('$legalConfig', $config);

        $services->get(AcceptPoliciesHelper::class)
            ->arg('$legalConfig', $config);

        $services->get(ExtensionMenu::class)
            ->arg('$legalConfig', $config);

        $services->get(TwigExtension::class)
            ->arg('$legalConfig', $config);
    }
}
