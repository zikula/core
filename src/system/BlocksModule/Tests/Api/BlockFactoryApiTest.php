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

namespace Zikula\BlocksModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Api\BlockFactoryApi;
use Zikula\BlocksModule\Tests\Api\Fixture\BarBlock;
use Zikula\BlocksModule\Tests\Api\Fixture\FooBlock;
use Zikula\BlocksModule\Tests\Api\Fixture\WrongInterfaceBlock;
use Zikula\Common\Translator\IdentityTranslator;

class BlockFactoryApiTest extends TestCase
{
    /**
     * @var BlockFactoryApiInterface
     */
    private $api;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->set('foo.block', new FooBlock());
        $this->container->set('zikula_extensions_module.api.variable', new stdClass());
        $this->api = new BlockFactoryApi($this->container, new IdentityTranslator());
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetBlockDefinedAsService(): void
    {
        $this->assertEquals($this->container->get('foo.block'), $this->api->getInstance('foo.block'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDoesNotExistException(): void
    {
        $this->api->getInstance('BarModule\ZedBlock');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWrongInterfaceException(): void
    {
        $this->api->getInstance(WrongInterfaceBlock::class);
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetInstance(): void
    {
        $blockInstance = $this->api->getInstance(FooBlock::class);
        $this->assertNotEmpty($blockInstance);
        $this->assertEquals('FooType', $blockInstance->getType());

        $blockInstance = $this->api->getInstance(BarBlock::class);
        $this->assertNotEmpty($blockInstance);
        $this->assertInstanceOf(AbstractBlockHandler::class, $blockInstance);
        $this->assertEquals('Bar', $blockInstance->getType());
    }
}
