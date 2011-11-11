<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Debug;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface TraceableEventDispatcherInterface
{
    /**
     * Gets the called listeners.
     *
     * @return array An array of called listeners
     */
    function getCalledListeners();

    /**
     * Gets the not called listeners.
     *
     * @return array An array of not called listeners
     */
    function getNotCalledListeners();
}
