<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\ThemeModule\AbstractTheme;

abstract class AbstractCoreTheme extends AbstractTheme
{
    public function getTranslationDomain()
    {
        return 'zikula';
    }
}
