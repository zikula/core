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

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\CoreBundle\Bundle\Initializer\BundleInitializerInterface;
use Zikula\CoreBundle\Bundle\Initializer\InitializableBundleInterface;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\UsersBundle\Bundle\Initializer\UsersInitializer;
use Zikula\UsersBundle\Bundle\MetaData\UsersBundleMetaData;

class ZikulaUsersBundle extends Bundle implements InitializableBundleInterface, MetaDataAwareBundleInterface
{
    public function getMetaData(): BundleMetaDataInterface
    {
        return $this->container->get(UsersBundleMetaData::class);
    }
    public function getInitializer(): BundleInitializerInterface
    {
        return $this->container->get(UsersInitializer::class);
    }
}
