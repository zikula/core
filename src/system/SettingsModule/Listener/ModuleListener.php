<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Core\CoreEvents;
use Zikula\ExtensionsModule\Api\VariableApi;

class ModuleListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * ModuleListener constructor.
     * @param VariableApi $variableApi
     * @param SessionInterface $session
     */
    public function __construct(VariableApi $variableApi, SessionInterface $session)
    {
        $this->variableApi = $variableApi;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MODULE_DISABLE => ['moduleDeactivated']
        ];
    }

    /**
     * Handle module deactivated event "installer.module.deactivated".
     * Receives $modinfo as $args
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function moduleDeactivated(GenericEvent $event)
    {
        $modname = $event['name'];
        $startModule = $this->variableApi->getSystemVar('startpage');

        if ($modname == $startModule) {
            // since the start module has been removed, set all related variables to ''
            $this->variableApi->set(VariableApi::CONFIG, 'startpage', '');
            $this->variableApi->set(VariableApi::CONFIG, 'starttype', '');
            $this->variableApi->set(VariableApi::CONFIG, 'startfunc', '');
            $this->variableApi->set(VariableApi::CONFIG, 'startargs', '');
            $this->session->getFlashBag()->add('info', __('The start module was reset to a static frontpage.'));
        }
    }
}
