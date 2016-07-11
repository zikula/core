<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\Core\AbstractModule;

abstract class AbstractCoreModule extends AbstractModule
{
    public function getState()
    {
        return self::STATE_ACTIVE;
    }

    public function getTranslationDomain()
    {
        return 'zikula';
    }
}
