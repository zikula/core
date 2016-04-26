<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\HookBundle\Hook\ValidationProviders;

/**
 * Content validation hook.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Bundle\HookBundle\Hook\DisplayHook
 */
class Zikula_ValidationHook extends Zikula\Bundle\HookBundle\Hook\ValidationHook
{
    /**
     * @param ValidationProviders $validators
     */
    public function __construct($name, ValidationProviders $validators)
    {
        LogUtil::log(__f('Warning! Class %s is deprecated.', [__CLASS__], E_USER_DEPRECATED));
        $this->setName($name);
        parent::__construct($validators);
    }
}
