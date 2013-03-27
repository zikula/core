<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\Core\AbstractModule;

abstract class AbstractCoreModule extends AbstractModule
{
    public function getTranslationDomain()
    {
        return 'zikula';
    }
}
