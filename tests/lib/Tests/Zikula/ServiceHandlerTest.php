<?php
require_once __DIR__ . '/../../../bootstrap.php';
/**
 * ServiceHandler test case.
 */
class Tests_Zikula_ServiceHandlerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ServiceHandler
     */
    private $ServiceHandler;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->ServiceHandler = new Zikula_ServiceHandler('id', 'method');

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->ServiceHandler = null;
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function test__construct()
    {
        $this->assertAttributeEquals('id', 'id', $this->ServiceHandler);
        $this->assertAttributeEquals('method', 'methodName', $this->ServiceHandler);
    }

    public function testGetId()
    {
        $this->assertEquals('id', $this->ServiceHandler->getId());
    }

    public function testGetMethodName()
    {
        $this->assertEquals('method', $this->ServiceHandler->getMethodName());
    }

}

