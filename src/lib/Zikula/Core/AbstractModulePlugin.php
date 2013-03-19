<?php

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractModulePlugin extends AbstractBundle
{
    protected function getNameType()
    {
        return 'ModulePlugin';
    }
}