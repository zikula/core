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

namespace Zikula\UsersBundle;

use Zikula\CoreBundle\AbstractModule;
use Zikula\CoreBundle\BundleInitializer\BundleInitializerInterface;
use Zikula\CoreBundle\BundleInitializer\InitializableBundleInterface;
use Zikula\UsersBundle\Initializer\UsersInitializer;

class ZikulaUsersBundle extends AbstractModule implements InitializableBundleInterface
{
    public function getInitializer(): BundleInitializerInterface
    {
        return $this->container->get(UsersInitializer::class);
    }
}
