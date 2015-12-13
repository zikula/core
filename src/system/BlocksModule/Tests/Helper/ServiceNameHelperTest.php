<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Tests\Helper;

use Zikula\BlocksModule\Helper\ServiceNameHelper;

class ServiceNameHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceNameHelper
     */
    private $helper;

    /**
     * ServiceNameHelperTest setup.
     */
    public function setUp()
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
            ['Acme_Bar_FooBar', 'acme.bar.foo_bar'],
            ['\Acme_Bar_FooBar', 'acme.bar.foo_bar'],
        ];
    }
}
