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

namespace Zikula\Bundle\HookBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zikula\Bundle\HookBundle\Controller\HookController;
use Zikula\Bundle\HookBundle\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Entity\HookRuntimeEntity;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\HookEventListenerInterface;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

/**
 * ZikulaHookExtension class.
 */
class ZikulaHookExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // start deprecated code - remove at Core 4.0.0
        $container->registerForAutoconfiguration(HookProviderInterface::class)
            ->addTag('zikula.hook_provider')
        ;
        $container->registerForAutoconfiguration(HookSubscriberInterface::class)
            ->addTag('zikula.hook_subscriber')
        ;
        // end deprecated code

        $container->registerForAutoconfiguration(HookEvent::class)
            ->addTag('zikula.hook_event')
        ;
        $container->registerForAutoconfiguration(HookEventListenerInterface::class)
            ->addTag('zikula.hook_event_listener')
        ;

        $this->addAnnotatedClassesToCompile([
            HookController::class,
            HookBindingEntity::class,
            HookRuntimeEntity::class
        ]);
    }
}
