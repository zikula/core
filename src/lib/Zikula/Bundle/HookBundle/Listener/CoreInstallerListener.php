<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
