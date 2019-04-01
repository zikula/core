<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class BaseTestCase extends WebTestCase
{
    protected static function createKernel(array $options = []): ZikulaHttpKernelInterface
    {
        return new AppKernel(
            $options['config'] ?? 'default.yml'
        );
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
