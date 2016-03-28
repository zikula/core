<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
  *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
        return array(
            // @todo convert to CoreEvent::MODULE_DISABLE at Core-2.0
            'installer.module.deactivated' => array('moduleDeactivated'),
        );
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
        $startModule = $this->variableApi->get(VariableApi::CONFIG, 'startpage');

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
