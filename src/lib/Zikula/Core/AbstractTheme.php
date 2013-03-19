<?php

namespace Zikula\Core;

abstract class AbstractTheme extends AbstractBundle
{
//    public abstract function getVersion();

    public function getNameType()
    {
        return 'Theme';
    }
    public function getServiceIds()
    {
        return array();
    }
}
