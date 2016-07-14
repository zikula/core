<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

use Zikula\BlocksModule\DependencyInjection\Compiler\BlockCollectorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;

/**
 * Base module definition for the blocks module.
 */
class ZikulaBlocksModule extends AbstractCoreModule
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BlockCollectorPass());
    }
}
