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

namespace Zikula\Bundle\CoreBundle\Tests\Twig;

use Twig\Test\IntegrationTestCase;
use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;

class IntegrationTest extends IntegrationTestCase
{
    public function getExtensions()
    {
        return [
            new CoreExtension()
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__ . '/Fixtures/';
    }
}
