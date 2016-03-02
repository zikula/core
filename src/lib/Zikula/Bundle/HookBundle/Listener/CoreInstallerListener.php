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

namespace Zikula\Bundle\HookBundle\Listener;

use Zikula\Bundle\HookBundle\HookBundleInstaller;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CoreInstallerListener
 */
class CoreInstallerListener implements EventSubscriberInterface
{
    private $hookBundleInstaller;

    /**
     * CoreInstallerListener constructor.
     * @param HookBundleInstaller $hookBundleInstaller
     */
    public function __construct(HookBundleInstaller $hookBundleInstaller)
    {
        $this->hookBundleInstaller = $hookBundleInstaller;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::CORE_INSTALL_PRE_MODULE => 'installHookBundle',
        ];
    }

    public function installHookBundle(GenericEvent $event)
    {
        if (!$this->hookBundleInstaller->install()) {
            $event->stopPropagation();
        }
    }
}
