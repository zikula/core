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
 * Hook validation collection
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\ValidationProviders
 */
class Zikula_Hook_ValidationProviders extends Zikula\Bundle\HookBundle\Hook\ValidationProviders
{
    public function __construct($name = 'validation', ArrayObject $collection = null)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', [__CLASS__], E_USER_DEPRECATED));
        parent::__construct($name, $collection);
    }
}
