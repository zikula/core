<?php

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractModulePlugin extends AbstractBundle
{
    public function getNameType()
    {
        return 'ModulePlugin';
    }
}