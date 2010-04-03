<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration/Local.php';

/**
 * FileSystem_Configuration_Local test case.
 */
class FileSystem_Configuration_LocalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Configuration_Local
     */
    private $FileSystem_Configuration_Local;
    private $FileSystem_Configuration_Local2;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_Configuration_LocalTest::setUp()


        $this->FileSystem_Configuration_Local = new FileSystem_Configuration_Local('dir');
        $this->FileSystem_Configuration_Local2 = new FileSystem_Configuration_Local();

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_Configuration_LocalTest::tearDown()


        $this->FileSystem_Configuration_Local = null;

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
     * Tests FileSystem_Configuration_Local->__construct()
     */
    public function test__construct()
    {
        $this->assertType('FileSystem_Configuration',$this->FileSystem_Configuration_Local);
	    $this->assertType('FileSystem_Configuration_Local',$this->FileSystem_Configuration_Local);

    }

    /**
     * Tests FileSystem_Configuration_Local->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('dir',$this->FileSystem_Configuration_Local->getDir());
        $this->assertEquals('',$this->FileSystem_Configuration_Local2->getDir());
    }

}

