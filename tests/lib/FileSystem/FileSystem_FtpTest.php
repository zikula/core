<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Ftp.php';

/**
 * FileSystem_Ftp test case.
 */
class FileSystem_FtpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Ftp
     */
    private $FileSystem_Ftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_FtpTest::setUp()


        $this->FileSystem_Ftp = new FileSystem_Ftp(/* parameters */);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_FtpTest::tearDown()


        $this->FileSystem_Ftp = null;

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
     * Tests FileSystem_Ftp->connect()
     */
    public function testConnect()
    {
        // TODO Auto-generated FileSystem_FtpTest->testConnect()
        $this->markTestIncomplete("connect test not implemented");

        $this->FileSystem_Ftp->connect(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->put()
     */
    public function testPut()
    {
        // TODO Auto-generated FileSystem_FtpTest->testPut()
        $this->markTestIncomplete("put test not implemented");

        $this->FileSystem_Ftp->put(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->fput()
     */
    public function testFput()
    {
        // TODO Auto-generated FileSystem_FtpTest->testFput()
        $this->markTestIncomplete("fput test not implemented");

        $this->FileSystem_Ftp->fput(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->get()
     */
    public function testGet()
    {
        // TODO Auto-generated FileSystem_FtpTest->testGet()
        $this->markTestIncomplete("get test not implemented");

        $this->FileSystem_Ftp->get(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->fget()
     */
    public function testFget()
    {
        // TODO Auto-generated FileSystem_FtpTest->testFget()
        $this->markTestIncomplete("fget test not implemented");

        $this->FileSystem_Ftp->fget(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->chmod()
     */
    public function testChmod()
    {
        // TODO Auto-generated FileSystem_FtpTest->testChmod()
        $this->markTestIncomplete("chmod test not implemented");

        $this->FileSystem_Ftp->chmod(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->ls()
     */
    public function testLs()
    {
        // TODO Auto-generated FileSystem_FtpTest->testLs()
        $this->markTestIncomplete("ls test not implemented");

        $this->FileSystem_Ftp->ls(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->cd()
     */
    public function testCd()
    {
        // TODO Auto-generated FileSystem_FtpTest->testCd()
        $this->markTestIncomplete("cd test not implemented");

        $this->FileSystem_Ftp->cd(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->mv()
     */
    public function testMv()
    {
        // TODO Auto-generated FileSystem_FtpTest->testMv()
        $this->markTestIncomplete("mv test not implemented");

        $this->FileSystem_Ftp->mv(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->cp()
     */
    public function testCp()
    {
        // TODO Auto-generated FileSystem_FtpTest->testCp()
        $this->markTestIncomplete("cp test not implemented");

        $this->FileSystem_Ftp->cp(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->rm()
     */
    public function testRm()
    {
        // TODO Auto-generated FileSystem_FtpTest->testRm()
        $this->markTestIncomplete("rm test not implemented");

        $this->FileSystem_Ftp->rm(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->isAlive()
     */
    public function testIsAlive()
    {
        // TODO Auto-generated FileSystem_FtpTest->testIsAlive()
        $this->markTestIncomplete("isAlive test not implemented");

        $this->FileSystem_Ftp->isAlive(/* parameters */);

    }

    /**
     * Tests FileSystem_Ftp->error_codes()
     */
    public function testError_codes()
    {
        // TODO Auto-generated FileSystem_FtpTest->testError_codes()
        $this->markTestIncomplete("error_codes test not implemented");

        $this->FileSystem_Ftp->error_codes(/* parameters */);

    }

}

