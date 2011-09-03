<?php

namespace Zikula\ModulesBundle\Bundle;

/**
 *
 */
abstract class RuntimeBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function getContainerExtension()
    {
        return null;
    }
}
