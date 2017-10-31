<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
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
     * @deprecated
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

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

    /**
     * ExtensionServicesListener constructor.
     * @param PermissionApiInterface $permissionApi
     * @param CapabilityApiInterface $capabilityApi
     * @param HookCollectorInterface $hookCollector
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        CapabilityApiInterface $capabilityApi,
        HookCollectorInterface $hookCollector,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->permissionsApi = $permissionApi;
        $this->capabilityApi = $capabilityApi;
        $this->hookCollector = $hookCollector;
        $this->router = $router;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'zikula.link_collector' => 'linkCollectorResponder',
            'module_dispatch.postexecute' => 'addHooksLink' // deprecated event
        ];
    }

    /**
     * Dynamically add Hooks link to administration.
     *
     * Listens for 'module_dispatch.postexecute' events.
     * @deprecated remove at Core-2.0
     * move logic to linkCollectorResponder
     *
     * @param \Zikula_Event $event The event handler
     * @return void
     */
    public function addHooksLink(\Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getLinks' && 'admin' == $event['type'] && true == $event['api'])) {
            return;
        }

        // return if not collection admin links
        if (LinkContainerInterface::TYPE_ADMIN != $event->getArgument('type')) {
            return;
        }
        if (!$this->permissionsApi->hasPermission($event['modname'] . '::Hooks', '::', ACCESS_ADMIN)) {
            return;
        }

        // return if module is not subscriber or provider capable
        if (!$this->hookCollector->isCapable($event['modname'], CapabilityApiInterface::HOOK_SUBSCRIBER)
            && !$this->hookCollector->isCapable($event['modname'], CapabilityApiInterface::HOOK_PROVIDER)
            && !$this->capabilityApi->isCapable($event['modname'], CapabilityApiInterface::HOOK_SUBSCRIBER) // @deprecated
            && !$this->capabilityApi->isCapable($event['modname'], CapabilityApiInterface::HOOK_PROVIDER) // @deprecated
        ) {
            return;
        }

        $event->data[] = [
            'url' => $this->router->generate('zikula_hook_hook_edit', ['moduleName' => $event['modname']]),
            'text' => $this->translator->__('Hooks'),
            'icon' => 'paperclip'
        ];
    }

    /**
     * Respond to zikula.link_collector events.
     *
     * Create a BC Layer for the zikula.link_collector event to gather Hook-related links.
     *
     * @param GenericEvent $event
     */
    public function linkCollectorResponder(GenericEvent $event)
    {
        $event->setArgument('modname', $event->getSubject());
        $event->setArgument('modfunc', [1 => 'getLinks']);
        $event->setArgument('api', true);
        $this->addHooksLink($event);
    }
}
