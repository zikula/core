<?php
/**
 * Copyright 2016 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Class MultisitesListener
 * @todo move this to the Multisites module
 */
class MultisitesListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $multisites;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var PermissionApi
     */
    private $permissionApi;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * MultisitesListener constructor.
     * @param array $multisitesParameters
     */
    public function __construct(array $multisitesParameters, RequestStack $requestStack, PermissionApi $permissionApi, TranslatorInterface $translator)
    {
        $this->multisites = $multisitesParameters;
        $this->requestStack = $requestStack;
        $this->permissionApi = $permissionApi;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionEvents::REGENERATE_VETO => 'checkAllowed',
            ExtensionEvents::UPDATE_STATE => 'updateState',
            ExtensionEvents::REMOVE_VETO => 'remove',
            ExtensionEvents::INSERT_VETO => 'checkAllowed'
        ];
    }

    public function checkAllowed(GenericEvent $event)
    {
        if (!\ModUtil::available('ZikulaMultisitesModule') || !$this->multisites['enabled'] || $this->isAllowed()) {
            return;
        }

        $event->stopPropagation();
    }

    public function updateState(GenericEvent $event)
    {
        if (\ModUtil::available('ZikulaMultisitesModule') && $this->multisites['enabled'] && $event->getArgument('state') == ExtensionApi::STATE_UNINITIALISED) {
            if (!$this->permissionApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new \RuntimeException($this->translator->__('Error! Invalid module state transition.'));
            }
        }
    }

    public function remove(GenericEvent $event)
    {
        if (!\ModUtil::available('ZikulaMultisitesModule') || !$this->multisites['enabled'] || $this->isAllowed()) {
            return;
        }
        $currentState = $event->getSubject()->getState();
        if (in_array($currentState, [ExtensionApi::STATE_NOTALLOWED, ExtensionApi::STATE_MISSING, ExtensionApi::STATE_INVALID])) {
            return;
        }

        $event->stopPropagation();
    }

    private function isAllowed()
    {
        $request = $this->requestStack->getMasterRequest();
        if (($this->multisites['mainsiteurl'] == $request->query->get('sitedns', null)
                && $this->multisites['based_on_domains'] == false)
            || ($this->multisites['mainsiteurl'] == $request->server->get('HTTP_HOST')
                && $this->multisites['based_on_domains'] == true)
        ) {
            return true;
        }

        return false;
    }
}
