<?php
require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * Test class for Zikula_ServiceManager_Reference.
 */
class Tests_Zikula_ServiceManager_ReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zikula_ServiceManager_Reference
     */
    protected $reference;
    
    protected function setUp()
    {
        $this->reference = new Zikula_ServiceManager_Reference('apple');
    }

    protected function tearDown()
    {
        $this->reference = null;
        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertAttributeSame('apple', 'id', $this->reference);
    }

    public function testGetId()
    {
        $this->assertSame('apple', $this->reference->getId());
    }
}
