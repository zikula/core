<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Tests\Api\Fixtures\ExtensionVarStubRepository;
use Zikula\ExtensionsModule\Tests\Fixtures\BaseBundle\BaseBundle;

class VariableApiTest extends TestCase
{
    /**
     * @var VariableApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        $kernel = $this
            ->getMockBuilder(ZikulaHttpKernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $kernel
            ->expects($this->atMost(1))
            ->method('getBundles')
            ->willReturn(['BaseBundle' => new BaseBundle()])
        ;

        $repo = new ExtensionVarStubRepository();
        $this->api = new VariableApi('3.0.0', $repo, $kernel, ['protected.systemvars' => []]);
    }

    /**
     * @covers VariableApi::has
     */
    public function testHas(): void
    {
        $this->assertFalse($this->api->has('BaseBundle', 'test'));
        $this->assertTrue($this->api->has('FooExtension', 'bar'));
    }

    /**
     * @covers VariableApi::get
     */
    public function testGet(): void
    {
        $this->assertEquals($this->api->get('FooExtension', 'bar'), 'test');
        $this->assertEquals($this->api->get('BarExtension', 'bar'), 7);
        $this->assertFalse($this->api->get('FooExtension', 'nonExistentVariable'));
        $this->assertEquals($this->api->get('FooExtension', 'nonExistentVariable', 'defaultValue'), 'defaultValue');
    }

    /**
     * @covers VariableApi::getSystemVar
     */
    public function testGetSystemVar(): void
    {
        $this->assertEquals($this->api->get('ZConfig', 'systemvar'), 'abc');
        $this->assertFalse($this->api->get('ZConfig', 'nonExistentVariable'));
        $this->assertEquals($this->api->get('ZConfig', 'nonExistentVariable', 'defaultValue'), 'defaultValue');
    }

    /**
     * @covers VariableApi::getAll
     */
    public function testGetAll(): void
    {
        $this->assertCount(1, $this->api->getAll('FooExtension'));
        $this->assertArrayHasKey('bar', $this->api->getAll('FooExtension'));
    }

    /**
     * @covers VariableApi::set
     */
    public function testSetAndGet(): void
    {
        $this->assertTrue($this->api->set('TestSet', 'int', 8));
        $this->assertEquals($this->api->get('TestSet', 'int'), 8);
    }

    /**
     * @covers VariableApi::set
     */
    public function testSetEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->api->set('', '', 5);
    }

    /**
     * @covers VariableApi::setAll
     */
    public function testSetAllAndGetAll(): void
    {
        $variables = [
            'int' => 9,
            'name' => 'john',
            'string' => 'aabbccdd'
        ];
        $this->assertTrue($this->api->setAll('TestSet', $variables));
        $this->assertEquals($this->api->getAll('TestSet'), $variables);
    }

    /**
     * @covers VariableApi::del
     */
    public function testDel(): void
    {
        $this->assertCount(3, $this->api->getAll('BarExtension'));
        $this->assertTrue($this->api->has('BarExtension', 'name'));
        $this->assertTrue($this->api->del('BarExtension', 'name'));
        $this->assertFalse($this->api->has('BarExtension', 'name'));
        $this->assertCount(2, $this->api->getAll('BarExtension'));
    }

    /**
     * @covers VariableApi::delAll
     */
    public function testDelAll(): void
    {
        $this->assertCount(3, $this->api->getAll('BarExtension'));
        $this->assertTrue($this->api->delAll('BarExtension'));
        $this->assertEquals([], $this->api->getAll('BarExtension'));
    }
}
