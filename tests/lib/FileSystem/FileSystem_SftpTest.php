<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Sftp.php';

/**
 * FileSystem_Sftp test case.
 */
class FileSystem_SftpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Sftp
     */
    private $FileSystem_Sftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_SftpTest::setUp()


        $this->FileSystem_Sftp = new FileSystem_Sftp(/* parameters */);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_SftpTest::tearDown()


        $this->FileSystem_Sftp = null;

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
     * Tests FileSystem_Sftp->connect()
     */
    public function testConnect()
    {
        // TODO Auto-generated FileSystem_SftpTest->testConnect()
        $this->markTestIncomplete("connect test not implemented");

        $this->FileSystem_Sftp->connect(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->put()
     */
    public function testPut()
    {
        // TODO Auto-generated FileSystem_SftpTest->testPut()
        $this->markTestIncomplete("put test not implemented");

        $this->FileSystem_Sftp->put(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->fput()
     */
    public function testFput()
    {
        // TODO Auto-generated FileSystem_SftpTest->testFput()
        $this->markTestIncomplete("fput test not implemented");

        $this->FileSystem_Sftp->fput(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->get()
     */
    public function testGet()
    {
        // TODO Auto-generated FileSystem_SftpTest->testGet()
        $this->markTestIncomplete("get test not implemented");

        $this->FileSystem_Sftp->get(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->fget()
     */
    public function testFget()
    {
        // TODO Auto-generated FileSystem_SftpTest->testFget()
        $this->markTestIncomplete("fget test not implemented");

        $this->FileSystem_Sftp->fget(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->chmod()
     */
    public function testChmod()
    {
        // TODO Auto-generated FileSystem_SftpTest->testChmod()
        $this->markTestIncomplete("chmod test not implemented");

        $this->FileSystem_Sftp->chmod(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->ls()
     */
    public function testLs()
    {
        // TODO Auto-generated FileSystem_SftpTest->testLs()
        $this->markTestIncomplete("ls test not implemented");

        $this->FileSystem_Sftp->ls(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->cd()
     */
    public function testCd()
    {
        // TODO Auto-generated FileSystem_SftpTest->testCd()
        $this->markTestIncomplete("cd test not implemented");

        $this->FileSystem_Sftp->cd(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->mv()
     */
    public function testMv()
    {
        // TODO Auto-generated FileSystem_SftpTest->testMv()
        $this->markTestIncomplete("mv test not implemented");

        $this->FileSystem_Sftp->mv(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->cp()
     */
    public function testCp()
    {
        // TODO Auto-generated FileSystem_SftpTest->testCp()
        $this->markTestIncomplete("cp test not implemented");

        $this->FileSystem_Sftp->cp(/* parameters */);

    }

    /**
     * Tests FileSystem_Sftp->rm()
     */
    public function testRm()
    {
        // TODO Auto-generated FileSystem_SftpTest->testRm()
        $this->markTestIncomplete("rm test not implemented");

        $this->FileSystem_Sftp->rm(/* parameters */);

    }

}

