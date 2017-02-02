<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;
use Zikula\SearchModule\DependencyInjection\Compiler\SearchableModuleCollectorPass;

/**
 * Base module definition for the search module.
 */
class ZikulaSearchModule extends AbstractCoreModule
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SearchableModuleCollectorPass());
    }
}
