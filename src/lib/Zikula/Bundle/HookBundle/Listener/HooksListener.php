<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * Class HooksListener
 */
class HooksListener implements EventSubscriberInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionsApi;

    /**
     * @var HookCollectorInterface
     */
    private $hookCollector;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $hookCollector,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->permissionsApi = $permissionApi;
        $this->hookCollector = $hookCollector;
        $this->router = $router;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'zikula.link_collector' => 'linkCollectorResponder'
        ];
    }

    /**
     * Respond to zikula.link_collector events.
     *
     * Create a BC Layer for the zikula.link_collector event to gather Hook-related links.
     *
     * @param GenericEvent $event
     */
    public function linkCollectorResponder(GenericEvent $event): void
    {
        $event->setArgument('modname', $event->getSubject());
        $event->setArgument('modfunc', [1 => 'getLinks']);
        $event->setArgument('api', true);

        // return if not collection admin links
        if (LinkContainerInterface::TYPE_ADMIN !== $event->getArgument('type')) {
            return;
        }
        if (!$this->permissionsApi->hasPermission($event['modname'] . '::Hooks', '::', ACCESS_ADMIN)) {
            return;
        }
        // return if module is not subscriber or provider capable
        if (!$this->hookCollector->isCapable($event['modname'], HookCollectorInterface::HOOK_SUBSCRIBER)
            && !$this->hookCollector->isCapable($event['modname'], HookCollectorInterface::HOOK_PROVIDER)
        ) {
            return;
        }
        $event->data[] = [
            'url' => $this->router->generate('zikula_hook_hook_edit', ['moduleName' => $event['modname']]),
            'text' => $this->translator->trans('Hooks'),
            'icon' => 'paperclip'
        ];
    }
}
