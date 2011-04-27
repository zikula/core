<?php
require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Event test case.
 */
class Tests_Zikula_EventTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_Event
     */
    private $event;

    private $subject;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = new stdClass();
        $this->event = new Zikula_Event('test.event', $this->subject, array('name' => 'Event'), 'foo');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->event = null;
        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertEquals($this->event, new Zikula_Event('test.event', $this->subject, array('name' => 'Event'), 'foo'));
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
        $this->assertSame(array('name' => 'Event'), $this->event->getArgs());
    }

    public function testSetArgs()
    {
        $result = $this->event->setArgs(array('foo' => 'bar'));
        $this->assertAttributeSame(array('foo' => 'bar'), 'args', $this->event);
        $this->assertSame($this->event, $result);
    }

    public function testSetArg()
    {
        $result = $this->event->setArg('foo2', 'bar2');
        $this->assertAttributeSame(array('name' => 'Event', 'foo2' => 'bar2'), 'args', $this->event);
        $this->assertEquals($this->event, $result);
    }

    public function testOffsetSet()
    {
        $this->event['foo2'] = 'bar2';
        $this->assertAttributeSame(array('name' => 'Event', 'foo2' => 'bar2'), 'args', $this->event);
    }

    public function testOffsetUnset()
    {
        unset($this->event['name']);
        $this->assertAttributeSame(array(), 'args', $this->event);
    }

    /**
     * Tests Event->getArg()
     */
    public function testGetArg()
    {
        // test getting key
        $this->assertEquals('Event', $this->event->getArg('name'));

        // test getting invalid arg
        $this->setExpectedException('InvalidArgumentException');
        $this->assertFalse($this->event->getArg('nameNotExist'));
    }

    /**
     * Tests Event->offsetGet() ArrayAccess
     */
    public function testOffsetGet()
    {
        // test getting key
        $this->assertEquals('Event', $this->event['name']);

        // test getting invalid arg
        $this->setExpectedException('InvalidArgumentException');
        $this->assertFalse($this->event['nameNotExist']);
    }

    /**
     * Tests Event->hasArg()
     */
    public function testHasArg()
    {
        $this->assertTrue($this->event->hasArg('name'));
        $this->assertFalse($this->event->hasArg('nameNotExist'));
    }

    /**
     * Tests Event->offsetIsset() ArrayAccess
     */
    public function testOffsetIsset()
    {
        $this->assertTrue(isset($this->event['name']));
        $this->assertFalse(isset($this->event['nameNotExist']));
    }

    /**
     * Tests Event->getSubject()
     */
    public function testGetSubject()
    {
        $this->assertSame($this->subject, $this->event->getSubject());
    }

    /**
     * Tests Event->getName()
     */
    public function testGetName()
    {
        $this->assertEquals('test.event', $this->event->getName());
    }

    /**
     * Tests Event->getData()
     */
    public function testGetData()
    {
        $this->event->setData("Don't drink and drive.");
        $this->assertEquals("Don't drink and drive.", $this->event->getData());

        // test this is public
        $this->assertEquals("Don't drink and drive.", $this->event->data);
    }

    /**
     * Tests Event->setNotified()
     */
    public function testSetNotified()
    {
        $this->event->setNotified();
        $this->assertTrue($this->event->isStopped());
    }

    /**
     * Tests Event->setData()
     */
    public function testSetData()
    {
        $this->event->setData("Don't drink and drive.");
        $this->assertEquals("Don't drink and drive.", $this->event->getData());

        // test this is public
        $this->event->data = "Eat lots of green vegetables.";
        $this->assertEquals("Eat lots of green vegetables.", $this->event->getData());
    }

    /**
     * Tests Event->isStopped()
     */
    public function testisStopped()
    {
        $this->assertFalse($this->event->isStopped());
    }
}

