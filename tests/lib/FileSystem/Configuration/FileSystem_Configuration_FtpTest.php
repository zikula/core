<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration/Ftp.php';


/**
 * FileSystem_Configuration_Ftp test case.
 */
class FileSystem_Configuration_FtpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Configuration_Ftp
     */
    private $FileSystem_Configuration_Ftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_Configuration_FtpTest::setUp()


        $this->FileSystem_Configuration_Ftp = new FileSystem_Configuration_Ftp(/* parameters */);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest::tearDown()


        $this->FileSystem_Configuration_Ftp = null;

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
     * Tests FileSystem_Configuration_Ftp->__construct()
     */
    public function test__construct()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");

        $this->FileSystem_Configuration_Ftp->__construct(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getUser()
     */
    public function testGetUser()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetUser()
        $this->markTestIncomplete("getUser test not implemented");

        $this->FileSystem_Configuration_Ftp->getUser(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getPass()
     */
    public function testGetPass()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetPass()
        $this->markTestIncomplete("getPass test not implemented");

        $this->FileSystem_Configuration_Ftp->getPass(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getHost()
     */
    public function testGetHost()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetHost()
        $this->markTestIncomplete("getHost test not implemented");

        $this->FileSystem_Configuration_Ftp->getHost(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getPort()
     */
    public function testGetPort()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetPort()
        $this->markTestIncomplete("getPort test not implemented");

        $this->FileSystem_Configuration_Ftp->getPort(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getDir()
     */
    public function testGetDir()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetDir()
        $this->markTestIncomplete("getDir test not implemented");

        $this->FileSystem_Configuration_Ftp->getDir(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getTimeout()
     */
    public function testGetTimeout()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetTimeout()
        $this->markTestIncomplete("getTimeout test not implemented");

        $this->FileSystem_Configuration_Ftp->getTimeout(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getSSL()
     */
    public function testGetSSL()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetSSL()
        $this->markTestIncomplete("getSSL test not implemented");

        $this->FileSystem_Configuration_Ftp->getSSL(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getPasv()
     */
    public function testGetPasv()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->testGetPasv()
        $this->markTestIncomplete("getPasv test not implemented");

        $this->FileSystem_Configuration_Ftp->getPasv(/* parameters */);

    }

}

