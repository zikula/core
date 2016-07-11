<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * EventManagerInterface interface.
 *
 * @deprecated
 */
interface Zikula_EventManagerInterface
{
    public function attach($name, $handler, $priority = 10);

    public function detach($name, $handler);

    public function notify(Zikula_EventInterface $event);

    public function flushHandlers();
}
