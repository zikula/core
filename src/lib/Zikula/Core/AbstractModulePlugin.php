<?php

namespace Zikula\Core;

abstract class AbstractModulePlugin extends AbstractBundle
{
    public function getNameType()
    {
        return 'ModulePlugin';
    }
}
