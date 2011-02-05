<?php

require_once __DIR__ . '/bootstrap.php';

class ClassLoaderTest extends PHPUnit_Framework_TestCase
{
    private $classLoader;

    public function setUp()
    {
        parent::setUp();
        $this->classLoader = new ClassLoader('test');
    }

    public function tearDown()
    {
        $this->classLoader = null;
        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertAttributeEquals('test', 'namespace', $this->classLoader);
        $this->assertAttributeEquals('', 'path', $this->classLoader);
        $this->assertAttributeEquals('\\', 'separator', $this->classLoader);
    }
	// getters

    public function testGetPath()
    {
    	$autoloader = new ClassLoader('', 'testpath');
    	$this->assertEquals('testpath', $autoloader->getPath());
    }

    public function testGetSeparator()
    {
        $this->assertEquals('\\', $this->classLoader->getSeparator());
    }

    // setters

    public function testSetPath()
    {
    	$this->classLoader->setPath('nottestpath');
        $this->assertAttributeEquals('nottestpath', 'path', $this->classLoader);
    }

    public function testSetSeparator()
    {
        $this->classLoader->setSeparator('_');
        $this->assertAttributeEquals('_', 'separator', $this->classLoader);
    }

    // methods

    public function testUnregister()
    {
    	$this->classLoader->unregister();
    	$this->assertTrue(true);
    }


    public function testRegister()
    {
        $this->classLoader->register();
        $this->assertTrue(true);
    }

    /**
     * @dataProvider providerGetClassIncludePath
     */
    public function testGetClassIncludePath($namespace, $path, $separator, $class, $expected)
    {
        $autoloader = new ClassLoader($namespace, $path);
        $autoloader->setSeparator($separator);
        $this->assertEquals($expected, $autoloader->getClassIncludePath($class));
    }

    public function providerGetClassIncludePath()
    {
        // $namespace, $path, $separator, $class, $expectedResult
        // used to construct new ClassLoader($namespace, $path).
        // which then tries to load $class, and we should get back $expectedResult
        return array(
            array(
                // Normal namespace
                'Zikula\Core',
                '',
                '\\',
                'Zikula\Core\Test',
                'Zikula' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Test.php'),
            array(
                // Normal namespace, requesting class from different namespace
                'Zikula\Core',
                'lib',
                '\\',
                'NamespaceTotallyDoesntExist\Core\Test',
                false),
            array(
                // namespace doesnt exist
                'Zikula',
                'lib',
                '\\',
                'NamespaceTotallyDoesntExist\Zikula\Core',
                false),
            array(
                // namespace doesnt exist
                'Zikula',
                'lib',
                '\\',
                'NamespaceTotallyDoesntExist\Zikula',
                false),
            array(
                // test with paths
                'Zikula',
                'lib',
                '\\',
                'Zikula\Test',
                'lib' . DIRECTORY_SEPARATOR . 'Zikula' . DIRECTORY_SEPARATOR . 'Test.php'),
            array(
                // test with namespace in classname
                'Zikula',
                'lib',
                '\\',
                'Extensions\A00Zikula',
                false),
            array(
                'Extensions',
                '',
                '\\',
                'Extensions\A00Zikula\Init',
                'Extensions' . DIRECTORY_SEPARATOR . 'A00Zikula' . DIRECTORY_SEPARATOR . 'Init.php'),
            array(
                'Zikula',
                'lib',
                '\\',
                'Zikula\Zikula\Test',
                'lib' . DIRECTORY_SEPARATOR . 'Zikula' . DIRECTORY_SEPARATOR . 'Zikula' . DIRECTORY_SEPARATOR . 'Test.php'),
            array(
                'Zikula\Core',
                'lib',
                '\\',
                'TotallyDoesntExistsFakeFakeFake',
                false),
            array(
                // Test PEAR style libraries
                'Dwoo',
                'lib/vendor',
                '_',
                'Dwoo_Template',
                'lib' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Dwoo' . DIRECTORY_SEPARATOR . 'Template.php'),
            array(
                // Test PEAR style libraries
                'Dwoo',
                'lib',
                '_',
                'Dwoo_Template_Compiler',
                'lib' . DIRECTORY_SEPARATOR . 'Dwoo' . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'Compiler.php'),
            array(
                // interesting case for PEAR style libs where top class lives outside the containing folder e.g.
                // Foo.php
                // Dwoo/Exception.php
                'Foo',
                'lib',
                '_',
                'Foo',
                'lib' . DIRECTORY_SEPARATOR . 'Foo.php'),
            array(
                // PEAR style libs
                'Foo',
                'lib',
                '_',
                'Foo_Exception',
                'lib' . DIRECTORY_SEPARATOR . 'Foo/Exception.php'),
            array(
                '',
                '',
                '\\',
                'Zikula',
                'Zikula.php'),
            array(
                '',
                '',
                '_',
                'Zikula_Test',
                'Zikula/Test.php'),
            array(
                '',
                '',
                '\\',
                'Zikula\Core\Test',
                'Zikula' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Test.php'));
    }

}
