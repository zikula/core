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

namespace Zikula\ExtensionsModule\Tests\Api;

use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Tests\Api\Fixtures\ExtensionVarStubRepository;
use Zikula\ExtensionsModule\Tests\Fixtures\BaseBundle\BaseBundle;

class VariableApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VariableApi
     */
    private $api;

    /**
     * VariableApiTest constructor.
     */
    public function setUp()
    {
        $kernel = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Kernel')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array('BaseBundle' => new BaseBundle())))
        ;

        $repo = new ExtensionVarStubRepository();
        $this->api = new VariableApi($repo, $kernel);
    }

    /**
     * @covers VariableApi::has
     */
    public function testHas()
    {
        $this->assertFalse($this->api->has('BaseBundle', 'test'));
        $this->assertTrue($this->api->has('FooExtension', 'bar'));
    }

    /**
     * @covers VariableApi::get
     */
    public function testGet()
    {
        $this->assertEquals($this->api->get('FooExtension', 'bar'), 'test');
        $this->assertEquals($this->api->get('BarExtension', 'bar'), 7);
        $this->assertFalse($this->api->get('FooExtension', 'nonExistentVariable'));
        $this->assertEquals($this->api->get('FooExtension', 'nonExistentVariable', 'defaultValue'), 'defaultValue');
    }

    /**
     * @covers VariableApi::getAll
     */
    public function testGetAll()
    {
        $this->assertInternalType('array', $this->api->getAll('FooExtension'));
        $this->assertCount(1, $this->api->getAll('FooExtension'));
        $this->assertArrayHasKey('bar', $this->api->getAll('FooExtension'));
    }

    /**
     * @covers VariableApi::set
     */
    public function testSetAndGet()
    {
        $this->assertTrue($this->api->set('TestSet', 'int', 8));
        $this->assertEquals($this->api->get('TestSet', 'int'), 8);
    }

    /**
     * @covers VariableApi::setAll
     */
    public function testSetAllAndGetAll()
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
    public function testDel()
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
    public function testDelAll()
    {
        $this->assertCount(3, $this->api->getAll('BarExtension'));
        $this->assertTrue($this->api->delAll('BarExtension'));
        $this->assertEquals([], $this->api->getAll('BarExtension'));
    }
}
