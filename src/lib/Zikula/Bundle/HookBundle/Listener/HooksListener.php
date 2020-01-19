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
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuEvent;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
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
            ExtensionMenuEvent::class => 'addHookMenu'
        ];
    }

    public function addHookMenu(ExtensionMenuEvent $event): void
    {
        // return if not collection admin links
        if (ExtensionMenuInterface::TYPE_ADMIN !== $event->getMenuType()) {
            return;
        }
        if (!$this->permissionsApi->hasPermission($event->getBundleName() . '::Hooks', '::', ACCESS_ADMIN)) {
            return;
        }
        // return if module is not subscriber or provider capable
        if (!$this->hookCollector->isCapable($event->getBundleName(), HookCollectorInterface::HOOK_SUBSCRIBER)
            && !$this->hookCollector->isCapable($event->getBundleName(), HookCollectorInterface::HOOK_PROVIDER)
        ) {
            return;
        }
        $event->getMenu()->addChild('Hooks', [
            'route' => 'zikula_hook_hook_edit',
            'routeParameters' => ['moduleName' => $event->getBundleName()]
            ])->setAttribute('icon', 'fas fa-paperclip');
    }
}
