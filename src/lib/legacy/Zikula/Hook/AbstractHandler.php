<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Custom Hook Handler interface.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\AbstractHookListener
 */
abstract class Zikula_Hook_AbstractHandler extends Zikula\Bundle\HookBundle\Hook\AbstractHookListener
{
    public function __construct(EventDispatcherInterface $eventManager)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', [__CLASS__], E_USER_DEPRECATED));
        parent::__construct($eventManager);
    }

    /**
     * Get eventManager.
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->getDispatcher();
    }
}
