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

use Exception;
use PHPUnit\Framework\TestCase;
use Zikula\BlocksModule\Helper\InstallerHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\Core\AbstractModule;

class InstallerHelperTest extends TestCase
{
    /**
     * @var InstallerHelper
     */
    private $helper;

    protected function setUp(): void
    {
        $this->helper = new InstallerHelper();
    }

    /**
     * @covers InstallerHelper::upgradeFilterArray
     * @dataProvider filterProvider
     */
    public function testUpgradeFilterArray(array $initialFilter, array $expected): void
    {
        $this->assertEquals($expected, $this->helper->upgradeFilterArray($initialFilter));
    }

    /**
     * @covers InstallerHelper::upgradeBkeyToFqClassname
     * @dataProvider bKeyProvider
     */
    public function testUpgradeBkeyToFqClassname(string $moduleName, string $oldBkey, string $expected): void
    {
        $kernel = $this
            ->getMockBuilder(ZikulaHttpKernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $kernel
            ->method('getModule')
            ->willReturnCallback(function ($moduleName) {
                if ('ExceptionModule' === $moduleName) {
                    // mocks situation where module is not namespaced.
                    throw new Exception();
                }
                $module = $this
                    ->getMockForAbstractClass(AbstractModule::class);
                $module
                    ->method('getNamespace')
                    ->willReturn('Zikula\BlocksModule\Tests\Helper\\' . $moduleName);

                return $module;
            })
        ;
        $moduleEntity = $this
            ->getMockBuilder(ExtensionEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleEntity
            ->method('getName')
            ->willReturn($moduleName);
        $blockEntity = $this
            ->getMockBuilder(BlockEntity::class)
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

    public function bKeyProvider(): array
    {
        return [
            ['MockModule', 'ShortNameBlock', 'MockModule:Zikula\BlocksModule\Tests\Helper\MockModule\Block\ShortNameBlock'],
            ['ExceptionModule', 'ShortNameBlock', 'ExceptionModule:\ExceptionModule_Block_ShortNameBlock'],
            ['ExceptionModule', 'ShortName', 'ExceptionModule:\ExceptionModule_Block_ShortName'],
            ['Fixture', 'TestBlock', 'Fixture:Zikula\BlocksModule\Tests\Helper\Fixture\Block\TestBlock'],
            ['Fixture', 'Test', 'Fixture:Zikula\BlocksModule\Tests\Helper\Fixture\Block\TestBlock'],
        ];
    }

    public function filterProvider(): array
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
                    ['_zkFunc', '==', 'bar']
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
                    ['e', '==', 'f']
                ]
            ],
            [
                [
                    ['module' => 'FooModule'],
                    ['module' => 'BarModule'],
                    ['module' => 'BazModule']
                ],
                [
                    ['_zkModule', 'in_array', ['FooModule', 'BarModule', 'BazModule']]
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
                    ['_zkFunc', 'in_array', ['bar', 'baz']]
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
                    ['i', 'in_array', ['j']]
                ]
            ]
        ];
    }
}
