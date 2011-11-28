<?php
require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Provider class for tests
 */
class Store
{
    protected $data;
    protected $foo;
    protected $flag;

    public function __construct($data) {
        $this->data = $data;
        $this->flag = false;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;
        return $this;
    }

    public function touchFlag()
    {
        $this->flag = true;
        return $this;
    }
}


/**
 * ServiceManager test case.
 */
class Tests_Zikula_ServiceManagerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_ServiceManager
     */
    private $serviceManager;
    private $services;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->serviceManager = new Zikula_ServiceManager();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->serviceManager = null;
        parent::tearDown();
    }

    /**
     * Tests ServiceManager->attachService()
     */
    public function testAttachService()
    {
        $class1 = new StdClass();
        $class2 = new StdClass();
        $this->serviceManager->attachService('test.stdclass1', $class1);
        $this->serviceManager->attachService('test.stdclass2', $class2);
        $this->assertSame($class1, $this->serviceManager->getService('test.stdclass1'));
        $this->assertSame($class2, $this->serviceManager->getService('test.stdclass2'));
    }

    /**
     * Tests ServiceManager->attachService()
     *
     * @expectedException InvalidArgumentException
     */
    public function testAttachServiceException()
    {
        $class1 = new StdClass();
        $this->serviceManager->attachService('test.stdclass1', $class1);
        $this->serviceManager->attachService('test.stdclass1', $class1);
    }

    /**
     * Tests ServiceManager->detachService()
     */
    public function testDetachService()
    {
        $class1 = new StdClass();
        $class2 = new StdClass();
        $this->serviceManager->attachService('test.stdclass1', $class1);
        $this->serviceManager->attachService('test.stdclass2', $class2);
        $this->serviceManager->detachService('test.stdclass1');
        $this->assertFalse($this->serviceManager->hasService('test.stdclass1'));
        $this->assertTrue($this->serviceManager->hasService('test.stdclass2'));
    }

    /**
     * Tests ServiceManager->detachService()
     */
    public function testDetachServiceException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->serviceManager->detachService('test.stdclass1');
    }

    /**
     * Tests ServiceManager->getService()
     *
     * @expectdException InvalidArgumentException
     */
    public function testGetServiceException()
    {
        $this->serviceManager->getService('test.stdclass');
    }

    /**
     * Tests ServiceManager->getService()
     */
    public function testGetService()
    {
        $class1 = new StdClass();
        $class2 = new StdClass();
        $this->serviceManager->attachService('test.stdclass1', $class1);
        $this->serviceManager->attachService('test.stdclass2', $class2);
        $this->assertSame($class2, $this->serviceManager->getService('test.stdclass2'));

        $stdDefSingle = new Zikula_ServiceManager_Definition('StdClass');
        $this->serviceManager->registerService('test.singleinstance', $stdDefSingle);
        $service0 = $this->serviceManager->getService('test.singleinstance');
        $this->assertSame($service0, $this->serviceManager->getService('test.singleinstance'));
        $this->assertTrue($service0 instanceof StdClass);

        $stdDefMultiple = new Zikula_ServiceManager_Definition('StdClass');
        $this->serviceManager->registerService('test.multipleinstance', $stdDefMultiple, false);
        $service1 = $this->serviceManager->getService('test.multipleinstance');
        $service2 = $this->serviceManager->getService('test.multipleinstance');
        $this->assertNotSame($service1, $service2);
        $this->assertNotSame($service1, $this->serviceManager->getService('test.multipleinstance'));
        $this->assertNotSame($service1, $this->serviceManager->getService('test.multipleinstance'));
        $this->assertNotSame($service2, $this->serviceManager->getService('test.multipleinstance'));
        $this->assertTrue($service1 instanceof StdClass);
        $this->assertTrue($service2 instanceof StdClass);
    }

    public function testGetServiceTestClone()
    {
        $class1 = new StdClass();
        $this->serviceManager->attachService('test.clone', $class1, false);
        $clone = $this->serviceManager->getService('test.clone');
        // should be equal (same class).
        $this->assertEquals($class1, $clone);
        // but not the same (since it's a clone).
        $this->assertNotSame($class1, $clone);
    }

    /**
     * Tests ServiceManager->hasService()
     */
    public function testHasService()
    {
        $this->assertFalse($this->serviceManager->hasService('will.fail'));
        $this->serviceManager->attachService('will.pass', new StdClass());
        $this->assertTrue($this->serviceManager->hasService('will.pass'));
    }

    public function testRegisterService()
    {
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $service = new Zikula_ServiceManager_Service('test.service', $definition);
        $this->serviceManager->registerService('test.service', $definition);
        $this->assertTrue($this->serviceManager->getService('test.service') instanceof StdClass);
    }

    public function testRegisterServiceExceptionAlreadyRegistered()
    {
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $service = new Zikula_ServiceManager_Service('test.service', $definition);
        $this->setExpectedException('InvalidArgumentException');
        $this->serviceManager->registerService('test.service', $definition);
        $this->serviceManager->registerService('test.service', $definition);
    }

    public function testRegisterServiceExceptionNoDefinition()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->serviceManager->registerService('test.service');
        $this->serviceManager->registerService('test.service');
    }

    public function testUnregisterService()
    {
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $service1 = new Zikula_ServiceManager_Service('test.service1', $definition);
        $service2 = new Zikula_ServiceManager_Service('test.service2', $definition);
        $this->serviceManager->registerService('test.service1', $definition);
        $this->serviceManager->registerService('test.service2', $definition);
        $this->serviceManager->unregisterService('test.service1');
        $this->assertTrue($this->serviceManager->hasService('test.service2'));
        $this->assertFalse($this->serviceManager->hasService('test.service1'));
    }

    public function testUnregisterServiceException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->serviceManager->unregisterService('thisshouldcallanexceptionbecauseitdoestnexist');
    }

}

