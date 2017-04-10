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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Api\BlockFactoryApi;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\BlocksModule\Helper\ServiceNameHelper;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * BlockApiTest setup.
     */
    public function setUp()
    {
        $this->container = new Container();
        $this->container->set('translator.default', new IdentityTranslator());
        $this->container->set('foo.block', new FooBlock());
        $this->container->set('zikula_extensions_module.api.variable', new \stdClass());
        $this->api = new BlockFactoryApi($this->container);
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetBlockDefinedAsService()
    {
        $this->assertEquals($this->container->get('foo.block'), $this->api->getInstance('foo.block', new AcmeFooModule()));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDoesNotExistException()
    {
        $this->api->getInstance('BarModule\ZedBlock', new AcmeFooModule());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWrongInterfaceException()
    {
        $this->api->getInstance(WrongInterfaceBlock::class, new AcmeFooModule());
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetInstanceOfAbstractExtensionBlock()
    {
        $blockInstance = $this->api->getInstance(BarBlock::class, new AcmeFooModule());
        $this->assertNotEmpty($blockInstance);
        $this->assertInstanceOf(AbstractBlockHandler::class, $blockInstance);
        $this->assertEquals('Bar', $blockInstance->getType());
        $serviceNameHelper = new ServiceNameHelper();
        $blockServiceName = $serviceNameHelper->generateServiceNameFromClassName(BarBlock::class);
        $this->assertTrue($this->container->has($blockServiceName));
        $retrievedBlockService = $this->container->get($blockServiceName);
        $this->assertInstanceOf(BlockHandlerInterface::class, $retrievedBlockService);
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetInstanceOfInterfaceExtensionBlock()
    {
        $blockInstance = $this->api->getInstance(FooBlock::class, new AcmeFooModule());
        $this->assertNotEmpty($blockInstance);
        $this->assertInstanceOf(BlockHandlerInterface::class, $blockInstance);
        $this->assertEquals('FooType', $blockInstance->getType());
        $serviceNameHelper = new ServiceNameHelper();
        $blockServiceName = $serviceNameHelper->generateServiceNameFromClassName(FooBlock::class);
        $this->assertTrue($this->container->has($blockServiceName));
        $retrievedBlockService = $this->container->get($blockServiceName);
        $this->assertInstanceOf(BlockHandlerInterface::class, $retrievedBlockService);
    }
}
