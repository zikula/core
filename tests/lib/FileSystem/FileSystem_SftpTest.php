<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/SFtp.php';

/**
 * FileSystem_SFtp test case.
 */
class FileSystem_SFtpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_SFtp
     */
    private $FileSystem_SFtp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_SFtpTest::setUp()


        $this->FileSystem_SFtp = new FileSystem_SFtp(/* parameters */);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_SFtpTest::tearDown()


        $this->FileSystem_SFtp = null;

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
     * Tests FileSystem_SFtp->connect()
     */
    public function testConnect()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testConnect()
        $this->markTestIncomplete("connect test not implemented");

        $this->FileSystem_SFtp->connect(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->put()
     */
    public function testPut()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testPut()
        $this->markTestIncomplete("put test not implemented");

        $this->FileSystem_SFtp->put(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->fput()
     */
    public function testFput()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testFput()
        $this->markTestIncomplete("fput test not implemented");

        $this->FileSystem_SFtp->fput(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->get()
     */
    public function testGet()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testGet()
        $this->markTestIncomplete("get test not implemented");

        $this->FileSystem_SFtp->get(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->fget()
     */
    public function testFget()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testFget()
        $this->markTestIncomplete("fget test not implemented");

        $this->FileSystem_SFtp->fget(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->chmod()
     */
    public function testChmod()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testChmod()
        $this->markTestIncomplete("chmod test not implemented");

        $this->FileSystem_SFtp->chmod(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->ls()
     */
    public function testLs()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testLs()
        $this->markTestIncomplete("ls test not implemented");

        $this->FileSystem_SFtp->ls(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->cd()
     */
    public function testCd()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testCd()
        $this->markTestIncomplete("cd test not implemented");

        $this->FileSystem_SFtp->cd(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->mv()
     */
    public function testMv()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testMv()
        $this->markTestIncomplete("mv test not implemented");

        $this->FileSystem_SFtp->mv(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->cp()
     */
    public function testCp()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testCp()
        $this->markTestIncomplete("cp test not implemented");

        $this->FileSystem_SFtp->cp(/* parameters */);

    }

    /**
     * Tests FileSystem_SFtp->rm()
     */
    public function testRm()
    {
        // TODO Auto-generated FileSystem_SFtpTest->testRm()
        $this->markTestIncomplete("rm test not implemented");

        $this->FileSystem_SFtp->rm(/* parameters */);

    }

}

