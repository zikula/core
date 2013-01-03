<?php

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractTheme extends Bundle
{
//    public abstract function getVersion();

    public function getServiceIds()
    {
        return array();
    }
}
