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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Api\BlockFactoryApi;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\BlocksModule\Tests\Api\Fixture\AcmeFooModule;
use Zikula\BlocksModule\Tests\Api\Fixture\BarBlock;
use Zikula\BlocksModule\Tests\Api\Fixture\FooBlock;
use Zikula\BlocksModule\Tests\Api\Fixture\WrongInterfaceBlock;
use Zikula\Common\Translator\IdentityTranslator;

class BlockFactoryApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockFactoryApiInterface
     */
    private $api;

    /**
     * @var FooBlock
     */
    private $fooBlock;

    /**
     * BlockApiTest setup.
     */
    public function setUp()
    {
        $this->fooBlock = new FooBlock();
        $container = $this
            ->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->method('has')
            ->willReturnCallback(function ($string) {
                return $string == 'foo.block';
            });
//        $container
//            ->method('set')
//            ->willReturn(true);
        $container
            ->method('get')
            ->willReturnCallback(function ($string) {
                $a = [
                    'translator.default' => new IdentityTranslator(),
                    'foo.block' => $this->fooBlock,
                    'zikula_extensions_module.api.variable' => new \stdClass()
                ];
                return $a[$string];
            });
        $this->api = new BlockFactoryApi($container);
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetBlockDefinedAsService()
    {
        $this->assertEquals($this->fooBlock, $this->api->getInstance('foo.block'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDoesNotExistException()
    {
        $this->api->getInstance('BarModule\ZedBlock');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWrongInterfaceException()
    {
        $this->api->getInstance(WrongInterfaceBlock::class);
    }

    /**
     * NOTE: This test will need to be adapted in Core-2.0 as the constructor will fail without the moduleBundle
     * @expectedException \LogicException
     */
    public function testUnsetModuleException()
    {
        $this->api->getInstance(BarBlock::class);
    }

    public function testGetInstanceOfAbstractExtensionBlock()
    {
        $blockInstance = $this->api->getInstance(BarBlock::class, new AcmeFooModule());
        $this->assertNotEmpty($blockInstance);
        $this->assertInstanceOf(BlockHandlerInterface::class, $blockInstance);
        $this->assertEquals('Bar', $blockInstance->getType());
    }

    public function testGetInstanceOfInterfaceExtensionBlock()
    {
        $blockInstance = $this->api->getInstance(FooBlock::class);
        $this->assertNotEmpty($blockInstance);
        $this->assertInstanceOf(BlockHandlerInterface::class, $blockInstance);
        $this->assertEquals('FooType', $blockInstance->getType());
    }
}
