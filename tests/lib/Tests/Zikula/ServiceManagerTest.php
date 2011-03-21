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
        $property = new ReflectionProperty($this->serviceManager, 'services');
        $property->setAccessible(true);
        $this->services = $property->setValue($this->serviceManager, array());//$property->getValue($this->ServiceManager);
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
        $this->setExpectedException('Exception');
        $this->serviceManager->attachService('test.stdclass1', $class1);
    }

    /**
     * Tests ServiceManager->attachService()
     */
    public function testAttachServiceException()
    {
        $this->setExpectedException('Exception');
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
        $expected = new Zikula_ServiceManager_Service('test.stdclass2', null);
        $expected->setService($class2);
        $this->assertAttributeEquals(array('test.stdclass2' => $expected), 'services', $this->serviceManager);
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
     */
    public function testGetServiceException()
    {
        $this->setExpectedException('InvalidArgumentException');
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

    /**
     * Tests ServiceManager->createService()
     * @dataProvider providerCreateService
     */
    public function testCreateService($test, $input, $expectedOutput)
    {
        // unprotected the createService() method.
        $method = new ReflectionMethod('Zikula_ServiceManager', 'createService');
        $method->setAccessible(true);
        $this->$test($expectedOutput, $method->invokeArgs($this->serviceManager, array($input)));
    }

    public function providerCreateService()
    {
        $store = new Store('store');
        $store2 = new Store('store2');

        $inner = new Store(new StdClass);
        $inner->setFoo(new ArrayObject);
        $nestedStore = new Store(new Store($inner));

        return array(
            array('assertEquals', new Zikula_ServiceManager_Definition('StdClass', array(), array()), new StdClass()),
            array('assertEquals', new Zikula_ServiceManager_Definition('ArrayObject', array(), array()), new ArrayObject()),
            array('assertEquals', new Zikula_ServiceManager_Definition('EmptyIterator', array(), array()), new EmptyIterator()),

            // nasty recursive test on definitions.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('StdClass'))))))), new Store(new Store(new Store(new StdClass)))),

            // test parameterless method call.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array('store'), array('touchFlag' => array())), $store->touchFlag()),

            // test method call with param.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array('ok'), array('setData' => array('ok'))), new Store('ok')),

            // test multiple method calls.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array('store2'), array('touchFlag' => array(), 'setFoo' => array('bar'))), $store2->setFoo('bar')->touchFlag()),

            // test method call with Definition as method param.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array('ok'), array('setData' => array(new Zikula_ServiceManager_Definition('\ArrayObject')))), new Store(new ArrayObject())),

            // nasty recursive test on definitions with Definition as method param.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('Zikula\Tests\Common\ServiceManager\Store', array(new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('StdClass')), array('setFoo' => array(new Zikula_ServiceManager_Definition('ArrayObject')))))))), new Store(new Store($inner))),
            );
    }

    /**
     * Tests ServiceManager->createService()
     * Test createServices with Service containers in method params.
     * @dataProvider providerCreateServiceService
     */
    public function testCreateServiceService($test, $input, $expectedOutput, $service, $id)
    {
        // setup expected service
        $this->serviceManager->attachService($id, $service);

        // unprotected the createService() method.
        $method = new ReflectionMethod('Zikula_ServiceManager', 'createService');
        $method->setAccessible(true);
        $this->$test($expectedOutput, $method->invokeArgs($this->serviceManager, array($input)));
    }

    public function providerCreateServiceService()
    {
        $store1 = new Store('store1');
        $store2 = new ArrayObject();

        $inner = new Store(new StdClass);
        $inner->setFoo($store2);
        $nestedStore = new Store(new Store($inner));

        return array(
            // test method call with Service reference in method param.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array('ok'), array('setData' => array(new Zikula_ServiceManager_Service('store1')))), new Store($store1), $store1, 'store1'),

            // nasty recursive test on definitions with Service as method param.
            array('assertEquals', new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('Store', array(new Zikula_ServiceManager_Definition('StdClass')), array('setFoo' => array(new Zikula_ServiceManager_Service('store2')))))))), new Store(new Store($inner)), $store2, 'store2'),
            );
    }

    /**
     * Tests ServiceManager->compileArguments()
     * @dataProvider providerCompileArguments
     */
    public function testCompileArguments($test, $input, $expectedOutput, $services)
    {
        // unprotected the compileArgs() method.
        $method = new ReflectionMethod('Zikula_ServiceManager', 'compileArguments');
        $method->setAccessible(true);

        if (is_array($services)) {
            foreach ($services as $k => $v) {
                $this->serviceManager->attachService($k, $v);
            }
        }

        $this->$test($expectedOutput, $method->invokeArgs($this->serviceManager, array($input)));
    }

    public function providerCompileArguments()
    {
        // definition classes
        $def1 = new Zikula_ServiceManager_Definition('StdClass');
        $def2 = new Zikula_ServiceManager_Definition('ArrayObject');
        $def3 = new Zikula_ServiceManager_Definition('EmptyIterator');

        // created services
        $std1 = new StdClass();
        $std2 = new ArrayObject();
        $std3 = new EmptyIterator();

        // service references
        $srv1 = new Zikula_ServiceManager_Reference('test.1');
        $srv2 = new Zikula_ServiceManager_Reference('test.2');
        $srv3 = new Zikula_ServiceManager_Reference('test.3');

        // service arguments
        $arg1 = new Zikula_ServiceManager_Argument('arg.1');
        $arg2 = new Zikula_ServiceManager_Argument('arg.2');
        $arg3 = new Zikula_ServiceManager_Argument('arg.3');

        // plain variables
        $var1 = 'string';
        $var2 = 123;
        $var3 = true;

        return array(
            array('assertSame', array(1,2,3,4,5), array(1,2,3,4,5), null),
            array('assertNotSame', array(1,2,3,4,5), array(1,2,3), null),
            array('assertEquals', array('a', 'b', $def1), array('a', 'b', $std1), null),
            array('assertEquals', array($def1, 'b', $def2), array($std1, 'b', $std2), null),
            array('assertNotEquals', array('a', 'b', $def1), array('a', 'b', 'c'), null),
            array('assertEquals', array($def1, $def2, $def3), array($std1, $std2, $std3), null),
            array('assertEquals', array($def1, $def2, $def3), array($std1, $std2, $std3), null),
            array('assertNotSame', array($srv1, $srv2, $srv3), array($std1, $std1, $std1), array('test.1' => $std1,'test.2' => $std2,'test.3' => $std3)),
            array('assertSame', array($srv1, $srv2, $srv3), array($std1, $std2, $std3), array('test.1' => $std1,'test.2' => $std2,'test.3' => $std3)),
            array('assertSame', array($srv1, $srv2, 1), array($std1, $std2, 1), array('test.1' => $std1,'test.2' => $std2)),
            );
    }

    public function testregisterService()
    {
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $service = new Zikula_ServiceManager_Service('test.service', $definition);
        $this->serviceManager->registerService('test.service', $definition);
        $this->assertAttributeSame(array('test.service' => $service), 'services', $this->serviceManager);
    }

    public function testRegisterServiceExceptionAlreadyRegistered()
    {
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $service = new Zikula_ServiceManager_Service('test.service', $definition);
        $this->setExpectedException('Exception');
        $this->serviceManager->registerService('test.service', $definition);
        $this->serviceManager->registerService('test.service', $definition);
    }

    public function testRegisterServiceExceptionNoDefinition()
    {
        $this->setExpectedException('Exception');
        $this->serviceManager->registerService('test.service');
    }

    public function testunregisterService()
    {
        $definition = new Zikula_ServiceManager_Definition('StdClass');
        $service1 = new Zikula_ServiceManager_Service('test.service1', $definition);
        $service2 = new Zikula_ServiceManager_Service('test.service2', $definition);
        $this->serviceManager->registerService('test.service1', $definition);
        $this->serviceManager->registerService('test.service2', $definition);
        $this->serviceManager->unregisterService('test.service1');
        $this->assertAttributeSame(array('test.service2' => $service2), 'services', $this->serviceManager);
    }

    public function testUnregisterServiceException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->serviceManager->unregisterService('thisshouldcallanexceptionbecauseitdoestnexist');
    }

}

