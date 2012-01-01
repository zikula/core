<?php
namespace Zikula\Tests\Common\ServiceManager;
use Zikula\Common\ServiceManager\Service;
use Zikula\Common\ServiceManager\Definition;
/**
 * Service test case.
 */
class ServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Service
     */
    private $service;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $definition = new Definition('\StdClass');
        $this->service = new Service('test.service', $definition, true);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->service = null;
        parent::tearDown();
    }

    /**
     * Tests Service->__construct()
     */
    public function test__construct()
    {
        $this->assertAttributeEquals('test.service', 'id', $this->service);
        $this->assertAttributeEquals(new Definition('\StdClass'), 'definition', $this->service);
        $this->assertAttributeSame(true, 'shared', $this->service);
        $this->assertAttributeSame(null, 'service', $this->service);

        $definition = new Definition('\ArrayObject');
        $this->service = new Service('test.service2', $definition, false);
        $this->assertAttributeEquals('test.service2', 'id', $this->service);
        $this->assertAttributeEquals(new Definition('\ArrayObject'), 'definition', $this->service);
        $this->assertAttributeSame(false, 'shared', $this->service);
    }

    /**
     * Tests Service->getId()
     */
    public function testGetId()
    {
        $this->assertEquals('test.service', $this->service->getId());
    }

    /**
     * Tests Service->getDefinition()
     */
    public function testGetDefinition()
    {
        $this->assertEquals(new Definition('\StdClass'), $this->service->getDefinition());
    }

    public function testIsShared()
    {
        $this->assertTrue($this->service->isShared());
    }

    public function testHasDefinition()
    {
        $this->assertTrue($this->service->hasDefinition());
    }

    public function testGetService()
    {
        $this->assertSame(null, $this->service->getService());
    }

    public function testSetService()
    {
        $arrayObj = new \ArrayObject();
        $this->service->setService($arrayObj);
        $this->assertAttributeSame($arrayObj, 'service', $this->service);
        $this->assertAttributeEquals(null, 'definition', $this->service);
    }

    public function testHasService()
    {
        $this->assertFalse($this->service->hasService());
    }

}

