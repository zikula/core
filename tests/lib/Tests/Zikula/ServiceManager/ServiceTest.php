<?php
require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * Service test case.
 */
class Tests_Zikula_ServiceManager_Service extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_ServiceManager_Service
     */
    private $service;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $this->service = new Zikula_ServiceManager_Service('test.service', $definition, true);
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
        $this->assertAttributeEquals(new Zikula_ServiceManager_Definition('StdClass'), 'definition', $this->service);
        $this->assertAttributeSame(true, 'shared', $this->service);
        $this->assertAttributeSame(null, 'service', $this->service);

        $definition = new Zikula_ServiceManager_Definition('\ArrayObject');
        $this->service = new Zikula_ServiceManager_Service('test.service2', $definition, false);
        $this->assertAttributeEquals('test.service2', 'id', $this->service);
        $this->assertAttributeEquals(new Zikula_ServiceManager_Definition('\ArrayObject'), 'definition', $this->service);
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
        $this->assertEquals(new Zikula_ServiceManager_Definition('StdClass'), $this->service->getDefinition());
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
        $arrayObj = new ArrayObject();
        $this->service->setService($arrayObj);
        $this->assertAttributeSame($arrayObj, 'service', $this->service);
        $this->assertAttributeEquals(null, 'definition', $this->service);
    }

    public function testHasService()
    {
        $this->assertFalse($this->service->hasService());
    }

}

