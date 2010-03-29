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
    public function testError_get_last()
    {
        $this->FileSystem_Ftp->errorRegister('Error',1);

        AssertType('aray', $this->FileSystem_Ftp->error_get_last());

    }

    /**
     * Tests FileSystem_Error->error_count()
     */
    public function testError_count()
    {
        // TODO Auto-generated FileSystem_ErrorTest->testError_count()
        $this->markTestIncomplete("error_count test not implemented");

        $this->FileSystem_Error->error_count(/* parameters */);

    }

    /**
     * Tests FileSystem_Error->error_get_all()
     */
    public function testError_get_all()
    {
        // TODO Auto-generated FileSystem_ErrorTest->testError_get_all()
        $this->markTestIncomplete("error_get_all test not implemented");

        $this->FileSystem_Error->error_get_all(/* parameters */);

    }

    /**
     * Tests FileSystem_Error->error_clear_all()
     */
    public function testError_clear_all()
    {
        // TODO Auto-generated FileSystem_ErrorTest->testError_clear_all()
        $this->markTestIncomplete("error_clear_all test not implemented");

        $this->FileSystem_Error->error_clear_all(/* parameters */);

    }

    /**
     * Tests FileSystem_Error->start_handler()
     */
    public function testStart_handler()
    {
        // TODO Auto-generated FileSystem_ErrorTest->testStart_handler()
        $this->markTestIncomplete("start_handler test not implemented");

        $this->FileSystem_Error->start_handler(/* parameters */);

    }

    /**
     * Tests FileSystem_Error->stop_handler()
     */
    public function testStop_handler()
    {
        // TODO Auto-generated FileSystem_ErrorTest->testStop_handler()
        $this->markTestIncomplete("stop_handler test not implemented");

        $this->FileSystem_Error->stop_handler(/* parameters */);

    }

    /**
     * Tests FileSystem_Error->error_handler()
     */
    public function testError_handler()
    {
        // TODO Auto-generated FileSystem_ErrorTest->testError_handler()
        $this->markTestIncomplete("error_handler test not implemented");

        $this->FileSystem_Error->error_handler(/* parameters */);

    }

}

