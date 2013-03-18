<?php

namespace Zikula\Core;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class AbstractTheme extends Bundle
{
//    public abstract function getVersion();

    public function getServiceIds()
    {
        return array();
    }
}
