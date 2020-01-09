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

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Api\BlockFactoryApi;
use Zikula\BlocksModule\Tests\Api\Fixture\BarBlock;
use Zikula\BlocksModule\Tests\Api\Fixture\FooBlock;
use Zikula\BlocksModule\Tests\Api\Fixture\WrongInterfaceBlock;

class BlockFactoryApiTest extends KernelTestCase
{
    /**
     * @var BlockFactoryApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->api = new BlockFactoryApi(self::$container, new IdentityTranslator());
    }

    /**
     * @covers BlockFactoryApiInterface::getInstance()
     */
    public function testGetBlockDefinedAsService(): void
    {
        $this->assertEquals(self::$container->get(FooBlock::class), $this->api->getInstance(FooBlock::class));
    }

    public function testDoesNotExistException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->api->getInstance('BarModule\ZedBlock');
    }

    public function testWrongInterfaceException(): void
    {
        $this->expectException(\RuntimeException::class);
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
        $this->assertInstanceOf(AbstractBlockHandler::class, $blockInstance);
        $this->assertEquals('Bar', $blockInstance->getType());
    }
}
