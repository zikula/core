<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractModule extends AbstractBundle
{
    private $serviceIds = [];

    public function getNameType()
    {
        return 'Module';
    }

    public function build(ContainerBuilder $container)
    {
        // modules have to use DI Extensions
    }

    public function getServiceIds()
    {
        return $this->serviceIds;
    }
}
