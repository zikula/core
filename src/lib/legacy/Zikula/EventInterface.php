<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * EventInterface interface.
 *
 * @deprecated
 */
interface Zikula_EventInterface
{
    public function getName();

    public function getEventManager();

    public function setEventManager(Zikula_EventManagerInterface $eventManager);

    public function stop();

    public function isStopped();
}
