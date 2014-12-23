<?php

namespace Zikula\Core;

abstract class AbstractModulePlugin extends AbstractBundle
{
    protected function getNameType()
    {
        return 'ModulePlugin';
    }
}
