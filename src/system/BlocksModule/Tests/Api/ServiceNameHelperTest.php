<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Tests\Api;

use Zikula\BlocksModule\Helper\ServiceNameHelper;

class ServiceNameHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider classNamesProvider
     */
    public function testGetServiceNamefromClassName($expected, $actual)
    {
        $helper = new ServiceNameHelper();
        $this->assertEquals($expected, $helper->generateServiceNameFromClassName($actual));
    }

    public function classNamesProvider()
    {
        return [
            ['foo.bar', 'Foo\Bar'],
            ['foo.bar', 'Foo\\Bar'],
            ['foo.bar.baz.fee', 'Foo\Bar\Baz\Fee'],
            ['foo.bar', 'Foo\Bar\\'],
            ['foo.bar', '\Foo\Bar'],
            ['foo.bar', 'Foo\Bar    '],
            ['foo.bar', 'Foo\Bar '],
            ['foo.bar', 'Foo\Bar._
            '],
            // @deprecated tests:
            ['foo.bar', 'Foo_Bar'],
            ['foo.bar.baz.fee', 'Foo_Bar_Baz_Fee'],
        ];
    }
}
