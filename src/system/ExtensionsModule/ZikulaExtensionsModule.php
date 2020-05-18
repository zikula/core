<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\ExtensionsModule\DependencyInjection\Compiler\InstallerPass;

class ZikulaExtensionsModule extends AbstractCoreModule
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new InstallerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 30);
    }
}
