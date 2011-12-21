<?php
namespace Zikula\Tests\Common\ServiceManager;
use Zikula\Common\ServiceManager\Definition;


/**
 * Definition test case.
 */
class DefinitionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_ServiceManager_Definition
     */
    private $definition;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->definition = new Definition('\StdClass');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->definition = null;
        parent::tearDown();
    }

    /**
     * Tests Definition->__construct()
     */
    public function test__construct()
    {
        $this->assertAttributeEquals('\StdClass', 'className', $this->definition);
        $this->assertAttributeEquals(array(), 'constructorArgs', $this->definition);
        $this->assertAttributeEquals(array(), 'methods', $this->definition);
    }

    /**
     * Tests Definition->getClassName()
     */
    public function testGetClassName()
    {
        $this->assertEquals('\StdClass', '\StdClass', $this->definition->getClassName());
    }

    /**
     * Tests Definition->getConstructorArgs()
     */
    public function testGetConstructorArgs()
    {
        $this->assertEquals(array(), $this->definition->getConstructorArgs());
        $this->definition->setConstructorArgs(array(1, 2, 3, 4));
        $this->assertEquals(array(1, 2, 3, 4), $this->definition->getConstructorArgs());
    }

    /**
     * Tests Definition->setConstructorArgs()
     */
    public function testSetConstructorArgs()
    {
        $this->definition->setConstructorArgs(array(1, 2, 3, 4));
        $this->assertAttributeEquals(array(1, 2, 3, 4), 'constructorArgs', $this->definition);
    }

    /**
     * Tests Definition->getMethods()
     */
    public function testGetMethods()
    {
        $this->assertEquals(array(), $this->definition->getMethods());
    }

    /**
     * Tests Definition->getMethods()
     */
    public function testHasConstructorArgs()
    {
        $this->assertFalse($this->definition->hasConstructorArgs());
        $args = array();
        $args['setup'][] = array('var1' => 'var2');
        $defintion = new Definition('StdClass', $args);
        $this->assertTrue($defintion->hasConstructorArgs());
    }

    /**
     * Tests Definition->setMethods()
     */
    public function testSetMethods()
    {
        $methods = array('foo' => array('bar'));
        $this->definition->setMethods($methods);
        $this->assertAttributeEquals($methods, 'methods', $this->definition);
    }

    /**
     * Tests Definition->addMethod()
     */
    public function testAddMethod()
    {
        $methods = array();
        $methods['foo'][] = array('bar');
        $this->definition->addMethod('foo', array('bar'));
        $this->assertAttributeEquals($methods, 'methods', $this->definition);

        $methods = array();
        $methods['foo'][] = array('bar');
        $methods['boo'][] = array('bar');
        $this->definition->addMethod('boo', array('bar'));
        $this->assertAttributeEquals($methods, 'methods', $this->definition);
    }

    /**
     * Tests Definition->hasMethods()
     */
    public function testHasMethods()
    {
        $this->assertFalse($this->definition->hasMethods());
        $this->definition->addMethod('foo', array('bar'));
        $this->assertTrue($this->definition->hasMethods());
        $this->definition->addMethod('test', array('ing'));
        $this->assertTrue($this->definition->hasMethods());
    }

}

