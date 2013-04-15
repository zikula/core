<?php

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
