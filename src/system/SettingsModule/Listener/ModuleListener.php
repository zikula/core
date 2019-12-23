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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

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
            CoreEvents::MODULE_DISABLE => ['moduleDeactivated']
        ];
    }

    /**
     * Handle module deactivated event.
     */
    public function moduleDeactivated(ModuleStateEvent $event): void
    {
        $module = $event->getModule();
        $moduleName = isset($module) ? $module->getName() : $event->getModInfo()['name'];
        $startController = $this->variableApi->getSystemVar('startController');
        list($startModule) = explode(':', $startController);

        if ($moduleName === $startModule) {
            // since the start module has been removed, set all related variables to ''
            $this->variableApi->set(VariableApi::CONFIG, 'startController');
            $this->variableApi->set(VariableApi::CONFIG, 'startargs');
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                $session->getFlashBag()->add('info', $this->translator->__('The startController was reset to a static frontpage.'));
            }
        }
    }
}
