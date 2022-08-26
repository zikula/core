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

namespace Zikula\PermissionsBundle;

use Zikula\Bundle\CoreBundle\AbstractModule;
use Zikula\Bundle\CoreBundle\BundleInitializer\BundleInitializerInterface;
use Zikula\Bundle\CoreBundle\BundleInitializer\InitializableBundleInterface;
use Zikula\PermissionsBundle\Initializer\PermissionsInitializer;

class ZikulaPermissionsBundle extends AbstractModule implements InitializableBundleInterface
{
    public function getInitializer(): BundleInitializerInterface
    {
        return $this->container->get(PermissionsInitializer::class);
    }
}
