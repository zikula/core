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
 * Validation object for hooks.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\ValidationResponse
 */
class Zikula_Hook_ValidationResponse extends Zikula\Bundle\HookBundle\Hook\ValidationResponse
{
    public function __construct($key, $object)
    {
        @trigger_error('Old hook class is deprecated, please use Hook bundle instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Class %s is deprecated.', [__CLASS__], E_USER_DEPRECATED));
        parent::__construct($key, $object);
    }
}
