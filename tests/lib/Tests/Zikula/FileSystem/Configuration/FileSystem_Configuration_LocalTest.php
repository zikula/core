<?php
require_once dirname(__FILE__) . '/../../../../../bootstrap.php';

/**
 * Zikula_FileSystem_Configuration_Local test case.
 */
class Zikula_FileSystem_Configuration_LocalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_FileSystem_Configuration_Local
     */
    private $Zikula_FileSystem_Configuration_Local;
    private $Zikula_FileSystem_Configuration_Local2;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated Zikula_FileSystem_Configuration_LocalTest::setUp()


        $this->Zikula_FileSystem_Configuration_Local = new Zikula_FileSystem_Configuration_Local('dir');
        $this->Zikula_FileSystem_Configuration_Local2 = new Zikula_FileSystem_Configuration_Local();

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Zikula_FileSystem_Configuration_LocalTest::tearDown()


        $this->Zikula_FileSystem_Configuration_Local = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Local->__construct()
     */
    public function test__construct()
    {
        $this->assertInternalType('Zikula_FileSystem_Configuration',$this->Zikula_FileSystem_Configuration_Local);
	    $this->assertInternalType('Zikula_FileSystem_Configuration_Local',$this->Zikula_FileSystem_Configuration_Local);

    }

    /**
     * Tests Zikula_FileSystem_Configuration_Local->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('dir',$this->Zikula_FileSystem_Configuration_Local->getDir());
        $this->assertEquals('',$this->Zikula_FileSystem_Configuration_Local2->getDir());
    }

}

