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

namespace Zikula\BlocksModule\Tests\Helper;

use Zikula\BlocksModule\Helper\ServiceNameHelper;

class ServiceNameHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ServiceNameHelper
     */
    private $helper;

    /**
     * ServiceNameHelperTest setup.
     */
    protected function setUp()
    {
        $this->helper = new ServiceNameHelper();
    }

    /**
     * @dataProvider classNameProvider
     * @param $expected
     * @param $className
     */
    public function testGenerateServiceNameFromClassName($className, $expected)
    {
        $this->assertEquals($expected, $this->helper->generateServiceNameFromClassName($className));
    }

    public function classNameProvider()
    {
        return [
            ['Acme\FooBundle\Bar\FooBar', 'acme.foo_bundle.bar.foo_bar'],
            ['\Acme\FooBundle\Bar\FooBar', 'acme.foo_bundle.bar.foo_bar'],
            ['AcmeFooBundle\Bar\FooBarBaz', 'acme_foo_bundle.bar.foo_bar_baz'],
        ];
    }
}
