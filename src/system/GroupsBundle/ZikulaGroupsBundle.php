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

namespace Zikula\GroupsBundle;

use Zikula\ExtensionsBundle\AbstractModule;
use Zikula\ExtensionsBundle\Initializer\BundleInitializerInterface;
use Zikula\ExtensionsBundle\Initializer\InitializableBundleInterface;
use Zikula\GroupsBundle\Initializer\GroupsInitializer;

class ZikulaGroupsBundle extends AbstractModule implements InitializableBundleInterface
{
    public function getInitializer(): BundleInitializerInterface
    {
        return $this->container->get(GroupsInitializer::class);
    }
}
