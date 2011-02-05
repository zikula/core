<?php

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Test class for Zikula_Exception.
 */
class Zikula_ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zikula_Exception
     */
    protected $exception;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        try {
            throw new Zikula_Exception('Message', 123, array('foo'));
        } catch (Zikula_Exception $e) {
            $this->exception = $e;
        }
        
    }

    protected function  tearDown()
    {
        $this->exception = null;
        parent::tearDown();
    }

    /**
     * @todo Implement testGetDebug().
     */
    public function testGetDebug()
    {
        // Remove the following lines when you implement this test.
        $this->assertEquals(array('foo'), $this->exception->getDebug());
    }

    public function testException()
    {
        $this->assertEquals('Message', $this->exception->getMessage());
        $this->assertEquals(123, $this->exception->getCode());
    }
}
