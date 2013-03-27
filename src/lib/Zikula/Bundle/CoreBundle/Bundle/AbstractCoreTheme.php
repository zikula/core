<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\Core\AbstractTheme;

abstract class AbstractCoreTheme extends AbstractTheme
{
    public function getTranslationDomain()
    {
        return 'zikula';
    }
}
