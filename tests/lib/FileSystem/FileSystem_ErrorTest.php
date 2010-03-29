<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Ftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Facade/Ftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration/Ftp.php';

/**
 * FileSystem_Error test case.
 */
class FileSystem_ErrorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Error
     */
    private $FileSystem_Error;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new FileSystem_Configuration_Ftp();
        $this->FileSystem_Ftp = new FileSystem_Ftp($config);
        $this->FileSystem_Ftp->errorRegister('Error',1);
        $this->FileSystem_Ftp->errorRegister('Error2',2);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_ErrorTest::tearDown()


        $this->FileSystem_Error = null;

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
     * Tests FileSystem_Error->error_get_last()
     */
    public function testErrorGetLast()
    {
        $this->assertType('array', $this->FileSystem_Ftp->errorGetLast());
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $this->assertEquals(false, $fs->errorGetLast());
        $fs = new FileSystem_Ftp($config);
        $fs->errorRegister('Error',1);
        $fs->errorRegister('Error2',2);
        $this->assertType('array', $fs->errorGetLast(true));
        $this->assertType('array', $fs->errorGetLast(true));
        $this->assertEquals(false, $fs->errorGetLast(true));
    }

    /**
     * Tests FileSystem_Error->error_count()
     */
    public function testErrorCount()
    {
         $this->assertEquals(2, $this->FileSystem_Ftp->errorCount());
    }

    /**
     * Tests FileSystem_Error->error_get_all()
     */
    public function testErrorGetAll()
    {
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $fs->errorRegister('Error',1);
        $fs->errorRegister('Error2',2);
        $this->assertType('array', $fs->errorGetAll(true));
        $this->assertEquals(array(), $fs->errorGetAll(true));
    }

    /**
     * Tests FileSystem_Error->error_clear_all()
     */
    public function testError_clear_all()
    {
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $fs->errorRegister('Error',1);
        $fs->errorRegister('Error2',2);
        $fs->errorClearAll();
        $this->assertEquals(false, $fs->errorGetLast(true));
    }

    /**
     * Tests FileSystem_Error->error_handler()
     */
    public function testError_handler()
    {
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $fs->errorHandler(0,'Error','1','2');
        $this->assertType('array', $fs->errorGetAll(false));
        $this->assertEquals(1, $fs->errorCount(true));
    }

}