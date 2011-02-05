<?php
require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Event test case.
 */
class Tests_Zikula_EventTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Event
     */
    private $Event;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->Event = new Zikula_Event('test.event', $this, array('name' => 'Event'), 'foo');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Event = null;
        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertEquals($this->Event, new Zikula_Event('test.event', $this, array('name' => 'Event'), 'foo'));
    }

    public function test__constructNameException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $event = new Zikula_Event('', $this, array());
    }

    /**
     * Tests Event->getArgs()
     */
    public function testGetArgs()
    {
        // test getting all
        $this->assertSame(array('name' => 'Event'), $this->Event->getArgs());
    }

    /**
     * Tests Event->getArg()
     */
    public function testGetArg()
    {
        // test getting key
        $this->assertEquals('Event', $this->Event->getArg('name'));

        // test getting invalid arg
        $this->setExpectedException('InvalidArgumentException');
        $this->assertFalse($this->Event->getArg('nameNotExist'));
    }

    /**
     * Tests Event->hasArg()
     */
    public function testHasArg()
    {
        $this->assertTrue($this->Event->hasArg('name'));
        $this->assertFalse($this->Event->hasArg('nameNotExist'));
    }

    /**
     * Tests Event->getSubject()
     */
    public function testGetSubject()
    {
        $this->assertSame($this, $this->Event->getSubject());
    }

    /**
     * Tests Event->getName()
     */
    public function testGetName()
    {
        $this->assertEquals('test.event', $this->Event->getName());
    }

    /**
     * Tests Event->getData()
     */
    public function testGetData()
    {
        $this->Event->setData("Don't drink and drive.");
        $this->assertEquals("Don't drink and drive.", $this->Event->getData());
    }

    /**
     * Tests Event->setNotified()
     */
    public function testSetNotified()
    {
        $this->Event->setNotified();
        $this->assertTrue($this->Event->hasNotified());
    }

    /**
     * Tests Event->setData()
     */
    public function testSetData()
    {
        $this->Event->setData("Don't drink and drive.");
        $this->assertEquals("Don't drink and drive.", $this->Event->getData());
    }

    /**
     * Tests Event->hasNotified()
     */
    public function testHasNotified()
    {
        $this->assertFalse($this->Event->hasNotified());
    }
}

