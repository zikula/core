<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Tests\Helper;

use Zikula\BlocksModule\Helper\InstallerHelper;

class InstallerHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InstallerHelper
     */
    private $helper;

    /**
     * InstallerHelperTest setup.
     */
    public function setUp()
    {
        $this->helper = new InstallerHelper();
    }

    /**
     * @covers       InstallerHelper::upgradeFilterArray
     * @dataProvider filterProvider
     * @param $initialFilter
     * @param $expected
     */
    public function testUpgradeFilterArray($initialFilter, $expected)
    {
        $this->assertEquals($expected, $this->helper->upgradeFilterArray($initialFilter));
    }

    /**
     * @covers       InstallerHelper::upgradeBkeyToFqClassname
     * @dataProvider bKeyProvider
     * @param $moduleName
     * @param $oldBkey
     * @param $expected
     */
    public function testUpgradeBkeyToFqClassname($moduleName, $oldBkey, $expected)
    {
        $kernel = $this
            ->getMockBuilder('\Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel')
            ->disableOriginalConstructor()
            ->getMock();
        $kernel
            ->method('getModule')
            ->will($this->returnCallback(function ($moduleName) {
                if ('ExceptionModule' == $moduleName) {
                    // mocks situation where module is not namespaced.
                    throw new \Exception();
                }
                $module = $this
                    ->getMockBuilder('Zikula\Core\AbstractModule')
                    ->disableOriginalConstructor()
                    ->getMock();
                $module
                    ->method('getNamespace')
                    ->willReturn('Zikula\BlocksModule\Tests\Helper\\' . $moduleName);

                return $module;
            }));
        $moduleEntity = $this
            ->getMockBuilder('\Zikula\ExtensionsModule\Entity\ExtensionEntity')
            ->disableOriginalConstructor()
            ->getMock();
        $moduleEntity
            ->method('getName')
            ->willReturn($moduleName);
        $blockEntity = $this
            ->getMockBuilder('\Zikula\BlocksModule\Entity\BlockEntity')
            ->disableOriginalConstructor()
            ->getMock();
        $blockEntity
            ->method('getModule')
            ->willReturn($moduleEntity);
        $blockEntity
            ->method('getBkey')
            ->willReturn($oldBkey);
        $FqClassName = $this->helper->upgradeBkeyToFqClassname($kernel, $blockEntity);

        $this->assertEquals($expected, $FqClassName);
    }

    public function bKeyProvider()
    {
        return [
            ['MockModule', 'ShortNameBlock', 'MockModule:Zikula\BlocksModule\Tests\Helper\MockModule\Block\ShortNameBlock'],
            ['ExceptionModule', 'ShortNameBlock', 'ExceptionModule:\ExceptionModule_Block_ShortNameBlock'],
            ['ExceptionModule', 'ShortName', 'ExceptionModule:\ExceptionModule_Block_ShortName'],
            ['Fixture', 'TestBlock', 'Fixture:Zikula\BlocksModule\Tests\Helper\Fixture\Block\TestBlock'],
            ['Fixture', 'Test', 'Fixture:Zikula\BlocksModule\Tests\Helper\Fixture\Block\TestBlock'],
        ];
    }

    /**
     * @return array
     */
    public function filterProvider()
    {
        return [
            [[], []],
            [
                [
                    ['module' => 'FooModule']
                ],
                [
                    ['_zkModule', '==', 'FooModule']
                ]
            ],
            [
                [
                    ['module' => 'FooModule', 'ftype' => 'user', 'fname' => 'bar']
                ],
                [
                    ['_zkModule', '==', 'FooModule'],
                    ['_zkType', '==', 'user'],
                    ['_zkFunc', '==', 'bar'],
                ]
            ],
            [
                [
                    ['module' => 'FooModule', 'ftype' => 'user', 'fname' => 'bar', 'fargs' => 'a=b&c=d&e=f']
                ],
                [
                    ['_zkModule', '==', 'FooModule'],
                    ['_zkType', '==', 'user'],
                    ['_zkFunc', '==', 'bar'],
                    ['a', '==', 'b'],
                    ['c', '==', 'd'],
                    ['e', '==', 'f'],
                ]
            ],
            [
                [
                    ['module' => 'FooModule'],
                    ['module' => 'BarModule'],
                    ['module' => 'BazModule'],
                ],
                [
                    ['_zkModule', 'in_array', ['FooModule', 'BarModule', 'BazModule']],
                ]
            ],
            [
                [
                    ['module' => 'FooModule', 'ftype' => 'user', 'fname' => 'bar'],
                    ['module' => 'BarModule', 'ftype' => 'user', 'fname' => 'baz']
                ],
                [
                    ['_zkModule', 'in_array', ['FooModule', 'BarModule']],
                    ['_zkType', 'in_array', ['user']],
                    ['_zkFunc', 'in_array', ['bar', 'baz']],
                ]
            ],
            [
                [
                    ['module' => 'FooModule', 'ftype' => 'user', 'fname' => 'bar', 'fargs' => 'a=b&c=d&e=f'],
                    ['module' => 'BarModule', 'ftype' => 'user', 'fname' => 'baz', 'fargs' => 'a=z&g=h&i=j']
                ],
                [
                    ['_zkModule', 'in_array', ['FooModule', 'BarModule']],
                    ['_zkType', 'in_array', ['user']],
                    ['_zkFunc', 'in_array', ['bar', 'baz']],
                    ['a', 'in_array', ['b', 'z']],
                    ['c', 'in_array', ['d']],
                    ['e', 'in_array', ['f']],
                    ['g', 'in_array', ['h']],
                    ['i', 'in_array', ['j']],
                ]
            ],
        ];
    }
}
