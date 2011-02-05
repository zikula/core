<?php
require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * Service test case.
 */
class ZTests_Zikula_ServiceManager_Service extends PHPUnit_Framework_TestCase
{

    /**
     * @var Service
     */
    private $Service;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $this->Service = new Zikula_ServiceManager_Service('test.service', $definition, true);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Service = null;
        parent::tearDown();
    }

    /**
     * Tests Service->__construct()
     */
    public function test__construct()
    {
        $this->assertAttributeEquals('test.service', 'id', $this->Service);
        $this->assertAttributeEquals(new Zikula_ServiceManager_Definition('StdClass'), 'definition', $this->Service);
        $this->assertAttributeSame(true, 'shared', $this->Service);
        $this->assertAttributeSame(null, 'service', $this->Service);

        $definition = new Zikula_ServiceManager_Definition('\ArrayObject');
        $this->Service = new Zikula_ServiceManager_Service('test.service2', $definition, false);
        $this->assertAttributeEquals('test.service2', 'id', $this->Service);
        $this->assertAttributeEquals(new Zikula_ServiceManager_Definition('\ArrayObject'), 'definition', $this->Service);
        $this->assertAttributeSame(false, 'shared', $this->Service);
    }

    /**
     * Tests Service->getId()
     */
    public function testGetId()
    {
        $this->assertEquals('test.service', $this->Service->getId());
    }

    /**
     * Tests Service->getDefinition()
     */
    public function testGetDefinition()
    {
        $this->assertEquals(new Zikula_ServiceManager_Definition('StdClass'), $this->Service->getDefinition());
    }

    public function testIsShared()
    {
        $this->assertTrue($this->Service->isShared());
    }

    public function testHasDefinition()
    {
        $this->assertTrue($this->Service->hasDefinition());
    }

    public function testGetService()
    {
        $this->assertSame(null, $this->Service->getService());
    }

    public function testSetService()
    {
        $arrayObj = new ArrayObject();
        $this->Service->setService($arrayObj);
        $this->assertAttributeSame($arrayObj, 'service', $this->Service);
        $this->assertAttributeEquals(null, 'definition', $this->Service);
    }

    public function testHasService()
    {
        $this->assertFalse($this->Service->hasService());
    }

}

