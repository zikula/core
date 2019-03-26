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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ModuleListener constructor.
     * @param VariableApiInterface $variableApi
     * @param SessionInterface $session
     * @param TranslatorInterface $translator
     */
    public function __construct(VariableApiInterface $variableApi, SessionInterface $session, TranslatorInterface $translator)
    {
        $this->variableApi = $variableApi;
        $this->session = $session;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_DISABLE => ['moduleDeactivated']
        ];
    }

    /**
     * Handle module deactivated event CoreEvents::MODULE_DISABLE.
     *
     * @param ModuleStateEvent $event
     *
     * @return void
     */
    public function moduleDeactivated(ModuleStateEvent $event)
    {
        $module = $event->getModule();
        $moduleName = isset($module) ? $event->getModule()->getName() : $event->getModInfo()['name'];
        $startController = $this->variableApi->getSystemVar('startController');
        list($startModule) = explode(':', $startController);

        if ($moduleName === $startModule) {
            // since the start module has been removed, set all related variables to ''
            $this->variableApi->set(VariableApi::CONFIG, 'startController', '');
            $this->variableApi->set(VariableApi::CONFIG, 'startargs', '');
            $this->session->getFlashBag()->add('info', $this->translator->__('The startController was reset to a static frontpage.'));
        }
    }
}
