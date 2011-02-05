<?php

require_once __DIR__ . '/../../../../bootstrap.php';


/**
 * Definition test case.
 */
class Tests_Zikula_ServiceManager_DefinitionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Definition
     */
    private $Definition;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->Definition = new Zikula_ServiceManager_Definition('StdClass');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Definition = null;
        parent::tearDown();
    }

    /**
     * Tests Definition->__construct()
     */
    public function test__construct()
    {
        $this->assertAttributeEquals('StdClass', 'className', $this->Definition);
        $this->assertAttributeEquals(array(), 'constructorArgs', $this->Definition);
        $this->assertAttributeEquals(array(), 'methods', $this->Definition);
    }

    /**
     * Tests Definition->getClassName()
     */
    public function testGetClassName()
    {
        $this->assertEquals('StdClass', 'StdClass', $this->Definition->getClassName());
    }

    /**
     * Tests Definition->getConstructorArgs()
     */
    public function testGetConstructorArgs()
    {
        $this->assertEquals(array(), $this->Definition->getConstructorArgs());
        $this->Definition->setConstructorArgs(array(1, 2, 3, 4));
        $this->assertEquals(array(1, 2, 3, 4), $this->Definition->getConstructorArgs());
    }

    /**
     * Tests Definition->setConstructorArgs()
     */
    public function testSetConstructorArgs()
    {
        $this->Definition->setConstructorArgs(array(1, 2, 3, 4));
        $this->assertAttributeEquals(array(1, 2, 3, 4), 'constructorArgs', $this->Definition);
    }

    /**
     * Tests Definition->getMethods()
     */
    public function testGetMethods()
    {
        $this->assertEquals(array(), $this->Definition->getMethods());
    }

    /**
     * Tests Definition->setMethods()
     */
    public function testSetMethods()
    {
        $methods = array('foo' => array('bar'));
        $this->Definition->setMethods($methods);
        $this->assertAttributeEquals($methods, 'methods', $this->Definition);
    }

    /**
     * Tests Definition->addMethod()
     */
    public function testAddMethod()
    {
        $methods = array('foo' => array('bar'));
        $this->Definition->addMethod('foo', array('bar'));
        $this->assertAttributeEquals($methods, 'methods', $this->Definition);

        $methods = array('foo' => array('bar'), 'boo' => array('bar'));
        $this->Definition->addMethod('boo', array('bar'));
        $this->assertAttributeEquals($methods, 'methods', $this->Definition);
    }

    /**
     * Tests Definition->hasMethods()
     */
    public function testHasMethods()
    {
        $this->assertFalse($this->Definition->hasMethods());
        $this->Definition->addMethod('foo', array('bar'));
        $this->assertTrue($this->Definition->hasMethods());
        $this->Definition->addMethod('test', array('ing'));
        $this->assertTrue($this->Definition->hasMethods());
    }

}

