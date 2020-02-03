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

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;

class ModuleListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionEvents::EXTENSION_DISABLE => ['extensionDeactivated']
        ];
    }

    /**
     * Handle extension deactivated event.
     */
    public function extensionDeactivated(ExtensionStateEvent $event): void
    {
        $extension = $event->getExtension();
        $extensionName = isset($extension) ? $extension->getName() : $event->getInfo()['name'];
        $startController = $this->variableApi->getSystemVar('startController');
        [$startModule] = explode(':', $startController);

        if ($extensionName === $startModule) {
            // since the start extension has been removed, set all related variables to ''
            $this->variableApi->set(VariableApi::CONFIG, 'startController');
            $this->variableApi->set(VariableApi::CONFIG, 'startargs');
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                $session->getFlashBag()->add('info', 'The startController was reset to a static frontpage.');
            }
        }
    }
}
